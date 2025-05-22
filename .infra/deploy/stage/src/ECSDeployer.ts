import { DefEcs } from './Defs';
import * as utils from './utils';
import { Limiter } from './utils';
import AWS from 'aws-sdk';
import _ from 'lodash';
import chalk from 'chalk';
import moment, { Moment } from 'moment';

enum HookType {
  Before = 'before',
  After = 'after',
}

export default class ECSDeployer {
  private readonly ecs: AWS.ECS;
  private readonly limiter: Limiter;
  private readonly cluster: string;

  constructor(cluster: string, ecs: AWS.ECS, limiter: Limiter) {
    this.cluster = cluster;
    this.ecs = ecs;
    this.limiter = limiter;
  }

  public async deploy(defs: DefEcs) {
    const serviceNames = Object.keys(defs);
    for (let i = 0; i < serviceNames.length; i += 10) {
      let chunk = serviceNames.slice(i, i + 10);

      const services = await this.limiter(this.ecs.describeServices({
        cluster: this.cluster,
        services: chunk,
      }));

      if (services.failures.length > 0) {
        console.log(services.failures);
      }

      for (const service of services.services) {
        await utils.runWithRetries(async () => {
          console.log(chalk`Process service {cyan ${ service.serviceName }}`);

          let taskDefTemplate = await utils.createTaskDef(this.ecs, this.limiter, service.taskDefinition, defs[service.serviceName].containers);

          await this.before(defs, service, _.cloneDeep(taskDefTemplate));

          const taskDefResult = await utils.updateTaskDef(this.ecs, this.limiter, taskDefTemplate);

          console.log(chalk`Update service {cyan ${ service.serviceName }} set task definition to {cyan ${ taskDefResult.taskDefinitionArn }}`);
          await this.limiter(this.ecs.updateService({
            cluster: this.cluster,
            service: service.serviceName,
            taskDefinition: taskDefResult.taskDefinitionArn,
          }));
          await this.after(defs, service, _.cloneDeep(taskDefTemplate));
        });
      }
    }
  }

  async wait(startedAt: Moment, defs: DefEcs) {
    const serviceNames = Object.keys(defs);

    for (const serviceName of serviceNames) {
      console.log(chalk`{yellow.bold Wait for service "${ serviceName }" to stabilize...}`);

      let last = startedAt.clone();
      let monitor = async () => {
        let services = await this.ecs.describeServices({
          cluster: this.cluster,
          services: [ serviceName ],
        }).promise();

        let service = services.services.shift();

        service.deployments
          .filter((e) => last.isSameOrBefore(e.updatedAt))
          .forEach((d) => {
            const time = moment(d.updatedAt);
            console.log(chalk` - {cyan [${ time.format('HH:mm:ss') }] Deployment}: ${ d.status }→${ d.rolloutState } "${ d.rolloutStateReason }"`);
            console.log(`   Running tasks ${ d.runningCount }/${ d.desiredCount }. Pending: ${ d.pendingCount }. Failed: ${ d.failedTasks }`);
          });

        service.events
          .filter((e) => last.isSameOrBefore(e.createdAt))
          .reverse()
          .forEach((e) => {
            const time = moment(e.createdAt);
            console.log(chalk` - {green [${ time.format('HH:mm:ss') }] Event}: ${ e.message }`);
          });

        last = moment();
      };
      monitor();
      let interval = setInterval(monitor, 10 * 1000);

      await this.ecs
        .waitFor('servicesStable', {
          cluster: this.cluster,
          services: [ serviceName ],
          $waiter: {
            maxAttempts: 120,
          },
        })
        .promise()
        .finally(() => {
          clearInterval(interval);
          monitor();
        });

      console.log(chalk`{green Service "${ serviceName }" is stable}`);
    }
  }

  private async hook(type: HookType, defs: DefEcs, service: AWS.ECS.Service, originTaskDef: AWS.ECS.RegisterTaskDefinitionRequest) {
    let cmds: Array<{ [key: string]: string[] }> = _.get(defs, [ service.serviceName, 'hooks', type ], []);

    if (cmds.length === 0) {
      return;
    }

    for (const cmdIndex in cmds) {
      const cmd = cmds[cmdIndex];

      console.log(chalk`{yellow Run ${ type }[${ cmdIndex }] hook}`);
      for (const [ container, command ] of Object.entries(cmd)) {
        console.log(chalk` - ${ container }: {yellow ${ command.join(' ') }}`);
      }

      let taskDef: AWS.ECS.RegisterTaskDefinitionRequest = _.cloneDeep(originTaskDef);
      taskDef.containerDefinitions = taskDef.containerDefinitions.filter((def) => Object.keys(cmd).includes(def.name));
      taskDef.containerDefinitions.forEach((def) => {
        def.command = cmd[def.name];
        def.mountPoints = [];
        def.portMappings = [];
        def.cpu = 256;
        def.memoryReservation = 512;
        delete def.healthCheck;
      });
      taskDef.cpu = _.sum(taskDef.containerDefinitions.map((def) => def.cpu)) + '';
      taskDef.memory = _.sum(taskDef.containerDefinitions.map((def) => def.memoryReservation)) + '';
      taskDef.family = `${ taskDef.family }-${ type }`;
      taskDef.networkMode = 'bridge';
      taskDef.volumes = [];

      const taskDefResult = await this.limiter(this.ecs.registerTaskDefinition(taskDef));

      const runTask = await this.limiter(this.ecs.runTask({
        cluster: this.cluster,
        taskDefinition: taskDefResult.taskDefinition.taskDefinitionArn,
      }));

      if (runTask.tasks.length === 0) {
        let reasons = runTask.failures.map((failure) => failure.reason).join(', ');

        throw new Error('Task cannot be launched. Reasons: ' + reasons);
      }

      let taskLink = `https://console.aws.amazon.com/ecs/home?region=${ AWS.config.region }#/clusters/${ this.cluster }/tasks/${ runTask.tasks[0].taskArn.split('/').pop() }/details`;
      console.log(chalk` // Go to {blue.bold ${ taskLink }} to watch the progress`);

      const taskCompleteResult = await this.ecs.waitFor('tasksStopped', {
        cluster: this.cluster,
        tasks: runTask.tasks.map((task) => task.taskArn),
        $waiter: {
          maxAttempts: 240,
          delay: 10,
        },
      }).promise();

      const failedTasks = taskCompleteResult.tasks.filter((task) => task.containers.filter((container) => container.exitCode !== 0).length > 0);

      await this.limiter(this.ecs.deregisterTaskDefinition({ taskDefinition: taskDefResult.taskDefinition.taskDefinitionArn }));

      const task = taskCompleteResult.tasks.pop();

      const printTaskInfo = (task: AWS.ECS.Task) => {
        console.log(` - Task: ${ task.taskArn }`);
        console.log(` - Stop Code: ${ task.stopCode }`);
        console.log(` - Stopped Reason: ${ task.stoppedReason }`);
        console.log(` - Containers:`);

        task.containers.forEach((container) => {
          console.log(`   - ${ container.name } exited with ${ container.exitCode }`);
        });
      };

      if (failedTasks.length > 0) {
        console.log(chalk`{red Task finished with no zero code!}`);
        printTaskInfo(task);

        throw new Error(`${ type } hook is failed. Task complete with non zero exit code`);
      }

      console.log(chalk`{green Hook successfully finished}`);
      printTaskInfo(task);
    }
  }

  private before(defs: DefEcs, service: AWS.ECS.Service, taskDef: AWS.ECS.RegisterTaskDefinitionRequest) {
    return this.hook(HookType.Before, defs, service, taskDef);
  }

  private after(defs: DefEcs, service: AWS.ECS.Service, taskDef: AWS.ECS.RegisterTaskDefinitionRequest) {
    return this.hook(HookType.After, defs, service, taskDef);
  }
}
