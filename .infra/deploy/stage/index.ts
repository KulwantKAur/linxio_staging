import App from './src/App';
import { Defs } from './src/Defs';
import moment from 'moment';

console.log('Start');

const env = {
  DOCKER_IMAGE_TAG: process.env.DOCKER_IMAGE_TAG || 'master-15d6a50e' || 'staging-16b2n42f',

  ECR_APP_FRONT: process.env.ECR_APP_FRONT || '296258661150.dkr.ecr.ap-southeast-2.amazonaws.com/linxio-common-app-front',
 
  ECR_TRACKER_TRACCAR: process.env.ECR_TRACKER_TRACCAR || '296258661150.dkr.ecr.ap-southeast-2.amazonaws.com/linxio-common-tracker-traccar',
  ECR_CENTRIFUGO: process.env.ECR_CENTRIFUGO || '296258661150.dkr.ecr.ap-southeast-2.amazonaws.com/linxio-common-centrifugo',

  ECR_APP_API_USERS_PHP_FPM: process.env.ECR_APP_API_USERS_PHP_FPM || '296258661150.dkr.ecr.ap-southeast-2.amazonaws.com/linxio-common-app-api-users-php-fpm',
  ECR_APP_API_USERS_NGINX: process.env.ECR_APP_API_USERS_NGINX || '296258661150.dkr.ecr.ap-southeast-2.amazonaws.com/linxio-common-app-api-users-nginx',

  ECR_APP_API_TRACKERS_PHP_FPM: process.env.ECR_APP_API_TRACKERS_PHP_FPM || '296258661150.dkr.ecr.ap-southeast-2.amazonaws.com/linxio-common-app-api-trackers-php-fpm',
  ECR_APP_API_TRACKERS_NGINX: process.env.ECR_APP_API_TRACKERS_NGINX || '296258661150.dkr.ecr.ap-southeast-2.amazonaws.com/linxio-common-app-api-trackers-nginx',

  ECS_CLUSTER: process.env.ECS_CLUSTER || 'linxio-prod-main' || 'linxio-stage-main',
  ECS_CLUSTER_TRACKER: process.env.ECS_CLUSTER_TRACKER || 'linxio-prod-main' || 'linxio-stage-main',
};

let queueConsumerNames = [
  'tracker-voltage-1',
  'areas-1',
  'tracker-towing',
  'tracker-panic-button',
  'events',
  'sms',
  'email',
  'webapp',
  'mobileapp',
  'tracker-engine-on-time-1',
  'tracker-overspeeding-1',
  'tracker-sensor-event',
  'update-driver',
  'tracker-io-1',
  'routes-post-handle-1',
  'tracker-mv-without-driver-1',
  'tracker-battery-1',
  'tracker-long-driving-1',
  'tracker-long-standing-1',
  'tracker-moving-1',
  'tracker-today-data-1',
  'tracker-streamax',
  'tracker-streamax-postponed',
  'tracker-streamax-proxy',
];

let cronJobNames = [
  'notifications-send',
  'reminder-update-statuses',
  'document-update-statuses',
  'export-vehicle-data',
  't-calculate-routes-1',
  't-calculate-idling-1',
  't-calculate-speeding-1',
  't-update-route-location',
  't-update-wrong-routes',
  't-recalculate-wrong-routes',
  'scheduled-report',
  't-update-device-sensor-st',
  't-asset-missed',
  't-clear-temp-history',
  'send-fleetio-data',
  'send-vwork-data',
  'vehicle-update-data',
  'device-update-data',
  'user-unet-status',
  'chat-update-data',
  'gearbox-data',
  'db-pg-cron-management',
  'jimi-get-data',
  't-exceed-speed-limit-1',
];

const api = {
  ecs: {
    [`${ env.ECS_CLUSTER }-app-api-trackers`]: {
      containers: {
        'php-fpm-trackers': `${ env.ECR_APP_API_TRACKERS_PHP_FPM }:${ env.DOCKER_IMAGE_TAG }`,
        'metrics-trackers': `${ env.ECR_APP_API_TRACKERS_PHP_FPM }:${ env.DOCKER_IMAGE_TAG }`,
        'nginx-trackers': `${ env.ECR_APP_API_TRACKERS_NGINX }:${ env.DOCKER_IMAGE_TAG }`,
      },
      hooks: {
        before: [
          {
            'php-fpm-trackers': [ 'php', 'bin/console', '--no-interaction', 'doctrine:migrations:sync-metadata-storage' ],
          },
          {
            'php-fpm-trackers': [ 'php', 'bin/console', '--no-interaction', 'doctrine:migrations:migrate' ],
          },
          {
            'php-fpm-trackers': [ 'php', 'bin/console', '--no-interaction', 'doctrine:fixtures:load', '--append', '--group=global' ],
          },
          {
            'php-fpm-trackers': [ 'php', 'bin/console', '--no-interaction', 'cache:clear' ],
          },
          {
            'php-fpm-trackers': [ 'php', 'bin/console', '--no-interaction', 'db:procedures:insert' ],
          },
        ],
      },
    },
    [`${ env.ECS_CLUSTER }-app-api-users`]: {
      containers: {
        'php-fpm-users': `${ env.ECR_APP_API_USERS_PHP_FPM }:${ env.DOCKER_IMAGE_TAG }`,
        'metrics-users': `${ env.ECR_APP_API_USERS_PHP_FPM }:${ env.DOCKER_IMAGE_TAG }`,
        'nginx-users': `${ env.ECR_APP_API_USERS_NGINX }:${ env.DOCKER_IMAGE_TAG }`,
      },
      hooks: {
        before: [
          {
            'php-fpm-users': [ 'php', 'bin/console', '--no-interaction', 'doctrine:migrations:sync-metadata-storage' ],
          },
          {
            'php-fpm-users': [ 'php', 'bin/console', '--no-interaction', 'doctrine:migrations:migrate' ],
          },
          {
            'php-fpm-users': [ 'php', 'bin/console', '--no-interaction', 'doctrine:fixtures:load', '--append', '--group=global' ],
          },
          {
            'php-fpm-users': [ 'php', 'bin/console', '--no-interaction', 'cache:clear' ],
          },
          {
            'php-fpm-users': [ 'php', 'bin/console', '--no-interaction', 'db:procedures:insert' ],
          },
        ],
      },
    },
    ...queueConsumerNames.reduce((result, item) => {
      result[`${ env.ECS_CLUSTER }-app-queue-${ item }`] = {
        containers: {
          'php-queue-consumer': `${ env.ECR_APP_API_TRACKERS_PHP_FPM }:${ env.DOCKER_IMAGE_TAG }`,
        },
      };

      return result;
    }, {}),
  },
  cron: cronJobNames.reduce((result, item) => {
    result[`${ env.ECS_CLUSTER }-app-cron-${ item }`] = {
      containers: {
        'php-cronjob': `${ env.ECR_APP_API_TRACKERS_PHP_FPM }:${ env.DOCKER_IMAGE_TAG }`,
      },
    };

    return result;
  }, {}),
};

const traccar = {
  ecs: {
    [`${ env.ECS_CLUSTER_TRACKER }-tracker-traccar`]: {
      containers: {
        'tracker-traccar': `${ env.ECR_TRACKER_TRACCAR }:${ env.DOCKER_IMAGE_TAG }`,
      },
    },
    [`${ env.ECS_CLUSTER_TRACKER }-tracker-traccar-meitrack`]: {
      containers: {
        'tracker-traccar': `${ env.ECR_TRACKER_TRACCAR }:${ env.DOCKER_IMAGE_TAG }`,
      },
    },
    [`${ env.ECS_CLUSTER_TRACKER }-tracker-traccar-web`]: {
      containers: {
        'tracker-traccar': `${ env.ECR_TRACKER_TRACCAR }:${ env.DOCKER_IMAGE_TAG }`,
      },
    },
  },
  cron: {},
};

const defs: Defs = { api, traccar };

const app = new App(moment(), env.ECS_CLUSTER, defs);
app.run(process.argv[2] || 'api');