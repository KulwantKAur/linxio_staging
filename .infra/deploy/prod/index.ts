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
  'tracker-voltage-2',
  'tracker-voltage-3',
  'areas-1',
  'areas-2',
  'areas-3',
  'areas-4',
  'tracker-towing-1',
  'tracker-towing-2',
  'tracker-panic-button',
  'events',
  'sms',
  'email',
  'webapp',
  'mobileapp',
  'tracker-engine-on-time-1',
  'tracker-engine-on-time-2',
  'tracker-overspeeding-1',
  'tracker-overspeeding-2',
  'tracker-overspeeding-3',
  'tracker-sensor-event',
  'update-driver',
  'tracker-io-1',
  'tracker-io-2',
  'tracker-io-3',
  'tracker-io-4',
  'tracker-io-5',
  'tracker-io-6',
  'routes-post-handle-1',
  'routes-post-handle-2',
  'routes-post-handle-3',
  'routes-post-handle-4',
  'routes-post-handle-5',
  'routes-post-handle-6',
  'tracker-mv-without-driver-1',
  'tracker-mv-without-driver-2',
  'tracker-battery-1',
  'tracker-battery-2',
  'tracker-long-driving-1',
  'tracker-long-driving-2',
  'tracker-long-standing-1',
  'tracker-long-standing-2',
  'tracker-moving-1',
  'tracker-moving-2',
  'tracker-today-data-1',
  'tracker-today-data-2',
  'tracker-today-data-3',
  'tracker-engine-history-1',
  'tracker-engine-history-2',
  'tracker-ex-speed-limit-1',
  'tracker-ex-speed-limit-2',
  'tracker-ex-speed-limit-3',
  'tracker-streamax',
  'tracker-streamax-postponed',
  'tracker-streamax-proxy',
  'route-area-1',
  'route-area-2',
  'route-area-3',
];

let cronJobNames = [
  'notifications-send',
  'reminder-update-statuses',
  'document-update-statuses',
  'export-vehicle-data',
  't-calculate-routes-1',
  't-calculate-routes-2',
  't-calculate-routes-3',
  't-calculate-routes-4',
  't-calculate-routes-5',
  't-calculate-routes-6',
  't-calculate-routes-7',
  't-calculate-routes-8',
  't-calculate-routes-9',
  't-calculate-routes-10',
  't-calculate-routes-11',
  't-calculate-routes-12',
  't-calculate-idling-1',
  't-calculate-idling-2',
  't-calculate-idling-3',
  't-calculate-idling-4',
  't-calculate-idling-5',
  't-calculate-speeding-1',
  't-calculate-speeding-2',
  't-calculate-speeding-3',
  't-calculate-speeding-4',
  't-calculate-speeding-5',
  't-update-route-location',
  't-update-wrong-routes',
  't-recalculate-wrong-routes',
  'scheduled-report',
  't-update-device-sensor-status',
  't-asset-missed',
  't-clear-temp-history',
  'send-fleetio-data',
  'send-vwork-data',
  'vehicle-update-data-1',
  'vehicle-update-data-2',
  'device-update-data',
  'user-unet-status',
  'chat-update-data',
  'invoice-export',
  'invoice-sync-from-xero',
  'invoice-sync-to-xero',
  'invoice-generate',
  'invoice-overdue',
  'invoice-pay',
  'download-fuel-file',
  'device-contract-expired',
  'vehicle-driver-logout',
  'fuse-data',
  'fuel-station',
  'asset-today-data',
  'gearbox-data',
  'db-pg-cron-management',
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
            'php-fpm-trackers': [ 'php', '-d', 'memory_limit=1024M', 'bin/console', '--no-interaction', 'doctrine:fixtures:load', '--append', '--group=global' ],
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
            'php-fpm-users': [ 'php', '-d', 'memory_limit=1024M', 'bin/console', '--no-interaction', 'doctrine:fixtures:load', '--append', '--group=global' ],
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
