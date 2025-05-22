import { DefCron } from './Defs';
import * as utils from './utils';
import { Limiter } from './utils';
import AWS from 'aws-sdk';
import chalk from 'chalk';

export default class CronDeployer {
  private readonly ecs: AWS.ECS;
  private readonly cw: AWS.CloudWatchEvents;
  private readonly limiter: Limiter;

  constructor(ecs: AWS.ECS, cw: AWS.CloudWatchEvents, limiter: Limiter) {
    this.ecs = ecs;
    this.cw = cw;
    this.limiter = limiter;
  }

  public async deploy(defs: DefCron) {
    const ruleNames = Object.keys(defs);
    for (const ruleName of ruleNames) {
      await utils.runWithRetries(async () => {
        console.log(chalk`Process rule {cyan ${ ruleName }}`);
        const targets = await this.limiter(this.cw.listTargetsByRule({ Rule: ruleName }));

        if (targets.Targets.length !== 1) {
          console.error('There is more or less then 1 target for rule', ruleName);

          return;
        }

        const target = targets.Targets[0];

        const taskDefTemplate = await utils.createTaskDef(this.ecs, this.limiter, target.EcsParameters.TaskDefinitionArn, defs[ruleName].containers);

        const taskDefResult = await utils.updateTaskDef(this.ecs, this.limiter, taskDefTemplate);

        console.log(chalk`Update rule {cyan ${ ruleName }} set task definition to {cyan ${ taskDefResult.taskDefinitionArn }}`);
        await this.limiter(this.cw.removeTargets({ Rule: ruleName, Ids: [ target.Id ] }));
        await this.limiter(this.cw.putTargets({
          Rule: ruleName,
          Targets: [
            {
              ...target,
              EcsParameters: {
                ...target.EcsParameters,
                TaskDefinitionArn: taskDefResult.taskDefinitionArn,
              },
            },
          ],
        }));
      });
    }
  }
}


