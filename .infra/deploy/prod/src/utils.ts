import AWS from 'aws-sdk';
import { PromiseResult } from 'aws-sdk/lib/request';

export function sleep(timeout) {
  return new Promise((resolve) => setTimeout(resolve, timeout));
}

export async function runWithRetries(promise) {
  let attempt = 1;
  while (attempt <= 5) {
    try {
      await promise();

      return;
    } catch (e) {
      console.log('Error occurred', 'attempt', attempt, e);

      attempt++;
      await sleep(1000 * attempt);
    }
  }

  throw new Error('No attempts left');
}

const taskDefKeysWhitelist = [
  'family',
  'taskRoleArn',
  'executionRoleArn',
  'networkMode',
  'containerDefinitions',
  'volumes',
  'placementConstraints',
  'requiresCompatibilities',
  'cpu',
  'memory',
  'tags',
  'pidMode',
  'ipcMode',
  'proxyConfiguration',
  'inferenceAccelerators',
];

function cleanupTaskDefinition(taskDefinition: Partial<AWS.ECS.TaskDefinition>): Partial<AWS.ECS.RegisterTaskDefinitionRequest> {
  return Object.fromEntries(
    Object.entries(taskDefinition)
      .filter(([ key ]) => taskDefKeysWhitelist.includes(key)),
  );
}

export type Limiter = <D, E> (awsCall: AWS.Request<D, E>) => Promise<D>

export function limiter(limit = 9, window = 1000): Limiter {
  let calls = [];

  return async function <D, E>(awsCall: AWS.Request<D, E>): Promise<PromiseResult<D, E>> {
    if (calls.length >= limit) {
      calls = calls.slice(-limit);
      const toSleep = window - (calls[limit - 1] - calls[0]);
      if (toSleep > 0) {
        await sleep(toSleep);
      }
    }

    calls.push(Date.now());
    return await awsCall.promise();
  };
}


export async function createTaskDef(ecs: AWS.ECS, limiter: Limiter, taskDefinitionArn: string, containers: { [key: string]: string }): Promise<AWS.ECS.RegisterTaskDefinitionRequest> {
  const taskDefOriginal = await limiter(ecs.describeTaskDefinition({ taskDefinition: taskDefinitionArn }));

  const taskDef = cleanupTaskDefinition(taskDefOriginal.taskDefinition);

  for (const [ container, image ] of Object.entries(containers)) {
    const containerDefinition = taskDef.containerDefinitions.find(({ name }) => name === container);

    if (containerDefinition) {
      containerDefinition.image = image;
    }
  }

  return taskDef as AWS.ECS.RegisterTaskDefinitionRequest;
}

export async function updateTaskDef(ecs: AWS.ECS, limiter: Limiter, taskDefinition: AWS.ECS.Types.RegisterTaskDefinitionRequest) {
  const taskDefResult = await limiter(ecs.registerTaskDefinition(taskDefinition));

  let activeTaskDefs = await limiter(ecs.listTaskDefinitions({
    familyPrefix: taskDefResult.taskDefinition.family,
    status: 'ACTIVE',
  }));

  let nonActualTaskDefs = activeTaskDefs.taskDefinitionArns.filter((arn) => arn !== taskDefResult.taskDefinition.taskDefinitionArn);

  console.log(`Deregister ${ nonActualTaskDefs.length } old task definition revisions`);
  for (const nonActualTaskDef of nonActualTaskDefs) {
    await limiter(ecs.deregisterTaskDefinition({ taskDefinition: nonActualTaskDef }));
  }

  return taskDefResult.taskDefinition;
}
