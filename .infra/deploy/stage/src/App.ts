import CronDeployer from './CronDeployer';
import * as utils from './utils';
import ECSDeployer from './ECSDeployer';
import chalk from 'chalk';
import AWS from 'aws-sdk';
import { Defs } from './Defs';
import { Moment } from 'moment';

export default class App {
  private readonly cluster: string;
  private readonly defs: Defs;
  private readonly startedAt: Moment;

  constructor(startedAt: Moment, cluster: string, defs: Defs) {
    this.startedAt = startedAt;
    this.cluster = cluster;
    this.defs = defs;
  }

  async run(mod: string) {
    const def = this.defs[mod];

    if (def === undefined) {
      throw new Error(`Module ${ mod } not declared`);
    }

    const ecs = new AWS.ECS({ maxRetries: 10 });
    const cw = new AWS.CloudWatchEvents({ maxRetries: 10 });
    const limiter = utils.limiter();

    const ecsDeployer = new ECSDeployer(this.cluster, ecs, limiter);
    const cronDeployer = new CronDeployer(ecs, cw, limiter);

    await ecsDeployer.deploy(def.ecs);
    await cronDeployer.deploy(def.cron);
    await ecsDeployer.wait(this.startedAt, def.ecs);

    console.log(chalk`{green.bold Deployment Complete!}`);
  }
}
