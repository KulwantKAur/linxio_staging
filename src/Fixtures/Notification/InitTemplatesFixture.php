<?php

namespace App\Fixtures\Notification;

use App\Entity\Device;
use App\Entity\Notification\Transport;
use App\Entity\Notification\Template;
use App\Entity\Vehicle;
use App\Fixtures\BaseFixture;
use App\Fixtures\FixturesTypes;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManager;

/**
 * @deprecated
 */
class InitTemplatesFixture extends BaseFixture implements DependentFixtureInterface, FixtureGroupInterface
{

    public const SUBJECT_PREFIX = 'Alerts - ';

    public const DATA = [
        /* user created - user*/
        [
            'name' => Template::USER_CREATED_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'New user created',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>User ${user_name} <b>${user_email}</b> was created.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - user ${user_name} <b>${user_email}</b> (team - ${team}) was created by ${triggered_by}.</p>'
                    . '<p>User page: ${data_url}</p>'
                    . '<br/><p>${comment}</p>',

            ],
        ],
        [
            'name' => Template::USER_CREATED_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => ['body' => 'User created: ${user_email}. ${event_time} ${comment}'],
        ],
        [
            'name' => Template::USER_CREATED_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'New user created',
                'body' => 'User created: ${user_email}. ${comment}'
            ],
        ],
        [
            'name' => Template::USER_CREATED_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'New user created',
                'body' => 'User created: ${user_email} (${user_name}). ${comment}'
            ],
        ],
        /* user password reset - user*/
        [
            'name' => Template::USER_PSW_RESET_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'User reset password',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Password reset triggered for user ${user_name} <b>${user_email}</b></p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - password reset was triggered by ${triggered_by} for user ${user_name} <b>${user_email}</b> (team - ${team}).</p>'
                    . '<p>User page: ${data_url}</p>'
                    . '<br/><p>${comment}</p>',
            ],
        ],
        [
            'name' => Template::USER_PSW_RESET_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => ['body' => 'Password reset: ${user_email}. ${event_time} ${comment}'],
        ],
        [
            'name' => Template::USER_PSW_RESET_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'User reset password',
                'body' => 'Password reset: ${user_email}. ${comment}'
            ],
        ],
        [
            'name' => Template::USER_PSW_RESET_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'User reset password',
                'body' => 'Password reset: ${user_email} (${user_name}). ${comment}'
            ],
        ],
        /* user created - system */
        [
            'name' => Template::USER_CREATED_SYSTEM_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'New user created (system)',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>User ${user_name} <b>${user_email}</b> was created.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - user ${user_name} <b>${user_email}</b> (team - ${team}) was created by ${triggered_by}.</p>'
                    . '<p>User page: ${data_url}</p>'
                    . '<br/><p>${comment}</p>',

            ],
        ],
        /* user blocked  - user */
        [
            'name' => Template::USER_BLOCKED_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'User blocked',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>User ${user_name} <b>${user_email}</b> was blocked.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - ${triggered_by} have blocked user ${user_name} <b>${user_email}</b> [team: ${team}]</p>'
                    . '<p>Blocking message: ${data_message}</p>'
                    . '<p>User page: ${data_url}</p>'
                    . '<br/><p>${comment}</p>',
            ],
        ],
        [
            'name' => Template::USER_BLOCKED_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => ['body' => 'User blocked: ${user_email}. ${event_time} ${comment}'],
        ],
        [
            'name' => Template::USER_BLOCKED_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'User blocked',
                'body' => 'User blocked: ${user_email}. ${comment}'
            ],
        ],
        [
            'name' => Template::USER_BLOCKED_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'User blocked',
                'body' => 'User blocked: ${user_email} (${user_name}). ${comment}'
            ],
        ],
        /* user deleted  - user */
        [
            'name' => Template::USER_DELETED_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'User deleted',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>User ${user_name} <b>${user_email}</b> was deleted.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - ${triggered_by} have deleted user ${user_name} <b>${user_email}</b> [team: ${team}]</p>'
                    . '<p>User page: ${data_url}</p>'
                    . '<br/><p>${comment}</p>',
            ],
        ],
        [
            'name' => Template::USER_DELETED_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => ['body' => 'User deleted: ${user_email}. ${event_time} ${comment}'],
        ],
        [
            'name' => Template::USER_DELETED_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'User deleted',
                'body' => 'User deleted: ${user_email}. ${comment}'
            ],
        ],
        [
            'name' => Template::USER_DELETED_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'User deleted',
                'body' => 'User deleted: ${user_email} (${user_name}). ${comment}'
            ],
        ],
        /* user changed  - user */
        [
            'name' => Template::USER_CHANGED_NAME_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'User changed name',
                'body' => 'User changed name: ${user_name} (${user_email}). ${comment}'
            ],
        ],
        [
            'name' => Template::USER_CHANGED_NAME_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'User changed name',
                'body' => 'User changed name: ${user_name} (${user_email}). ${comment}'
            ],
        ],
        [
            'name' => Template::USER_CHANGED_NAME_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => ['body' => 'User changed name: ${user_name} (${user_email}). ${event_time} ${comment}'],
        ],
        [
            'name' => Template::USER_CHANGED_NAME_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'User changed name',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Name changed for user <b>${user_name} (${user_email})</b></p><br/>'
                    . '<p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - ${triggered_by} have renamed user from "${old_value}" to "${user_name}".</p>'
                    . '<p>User page: ${data_url}</p>'
                    . '<br/><p>${comment}</p>',
            ],
        ],
        [
            'name' => Template::USER_CHANGED_SURNAME_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'User changed surname',
                'body' => 'User changed surname: ${user_name} (${user_email). ${comment}'
            ],
        ],
        [
            'name' => Template::USER_CHANGED_SURNAME_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'User changed surname',
                'body' => 'User changed surname: ${user_name} (${user_email). ${comment}'
            ],
        ],
        [
            'name' => Template::USER_CHANGED_SURNAME_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => ['body' => 'User changed surname: ${user_name} (${user_email). ${event_time} ${comment}'],
        ],
        [
            'name' => Template::USER_CHANGED_SURNAME_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'User changed surname',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>User changed surname: <b>${user_name} (${user_email)</b></p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - user ${user_name} was renamed by ${triggered_by}.</p>'
                    . '<br/><p>${comment}</p>',
            ],
        ],
        /* client created - user */
        [
            'name' => Template::CLIENT_CREATED_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'New client created',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Client <b>${client_name}</b> was created.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - client ${client_name} was created by ${triggered_by}.</p>'
                    . '<p>${data_url}</p>'
                    . '<br/><p>${comment}</p>',
            ],
        ],
        [
            'name' => Template::CLIENT_CREATED_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => ['body' => 'Client created: ${client_name}. ${event_time} ${comment}'],
        ],
        [
            'name' => Template::CLIENT_CREATED_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'New client created',
                'body' => 'Client created: ${client_name}. ${comment}'
            ],
        ],
        [
            'name' => Template::CLIENT_CREATED_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'New client created',
                'body' => 'Client created: ${client_name}. ${comment}'
            ],
        ],
        /* client blocked  - user */
        [
            'name' => Template::CLIENT_BLOCKED_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Client blocked',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Client <b>${client_name}</b> was blocked.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - client ${client_name} from ${team} was blocked by ${triggered_by}.</p>'
                    . '<p>${data_url}</p>'
                    . '<br/><p>${comment}</p>'

            ],
        ],
        [
            'name' => Template::CLIENT_BLOCKED_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => ['body' => 'Client blocked: ${client_name}. ${event_time} ${comment}'],
        ],
        [
            'name' => Template::CLIENT_BLOCKED_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Client blocked',
                'body' => 'Client blocked: ${client_name}. ${comment}'
            ],
        ],
        [
            'name' => Template::CLIENT_BLOCKED_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Client blocked',
                'body' => 'Client blocked: ${client_name}. ${comment}'
            ],
        ],
        /* client demo expired  - user */
        [
            'name' => Template::CLIENT_DEMO_EXPIRED_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Client demo expired',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Demo period expired for the client <b>${client_name}</b>.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - client ${client_name} demo period expired.</p>'
                    . '<p>${data_url}</p>'
                    . '<br/><p>${comment}</p>',
            ],
        ],
        [
            'name' => Template::CLIENT_DEMO_EXPIRED_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => ['body' => 'Client demo expired: ${client_name}. ${event_time} ${comment}'],
        ],
        [
            'name' => Template::CLIENT_DEMO_EXPIRED_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Client demo expired',
                'body' => 'Client demo expired: ${client_name}. ${comment}'
            ],
        ],
        [
            'name' => Template::CLIENT_DEMO_EXPIRED_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Client demo expired',
                'body' => 'Client demo expired: ${client_name}. ${comment}'
            ],
        ],

        /* vehicle created - user */
        [
            'name' => Template::VEHICLE_CREATED_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Vehicle created',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>New vehicle created - ${reg_no_with_model}.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - ${triggered_by} have created vehicle vehicle ${reg_no_with_model} [team: ${team}]</p>'
                    . '<p>${vehicle_url}</p>'
                    . '<br/><p>${comment}</p>',
            ],
        ],
        [
            'name' => Template::VEHICLE_CREATED_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => ['body' => 'Vehicle created: ${reg_no}. ${event_time} ${comment}'],
        ],
        [
            'name' => Template::VEHICLE_CREATED_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Vehicle created',
                'body' => 'Vehicle created: ${reg_no}. ${comment}'
            ],
        ],
        [
            'name' => Template::VEHICLE_CREATED_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Vehicle created',
                'body' => 'Vehicle created: ${reg_no}. ${comment}'
            ],
        ],
        /* vehicle deleted - user */
        [
            'name' => Template::VEHICLE_DELETED_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Vehicle deleted',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Vehicle ${reg_no_with_model} was deleted.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - ${triggered_by} have deleted vehicle ${reg_no_with_model}.</p>'
                    . '<p>${vehicle_url}</p>'
                    . '<br/><p>${comment}</p>',
            ],
        ],
        [
            'name' => Template::VEHICLE_DELETED_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => ['body' => 'Vehicle deleted: ${reg_no}. ${event_time} ${comment}'],
        ],
        [
            'name' => Template::VEHICLE_DELETED_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Vehicle deleted',
                'body' => 'Vehicle deleted: ${reg_no}. ${comment}'
            ],
        ],
        [
            'name' => Template::VEHICLE_DELETED_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Vehicle deleted',
                'body' => 'Vehicle deleted: ${reg_no}. ${comment}'
            ],
        ],
        [
            'name' => Template::VEHICLE_CHANGED_REGNO_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Vehicle regNo was changed',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>RegNo changed for vehicle ${reg_no_with_model}.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - ${triggered_by} have changed vehicle RegNo from "${old_value}" to "${reg_no}".</p>'
                    . '<p>${vehicle_url}</p>'
                    . '<br/><p>${comment}</p>'
            ],
        ],
        [
            'name' => Template::VEHICLE_CHANGED_REGNO_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => ['body' => 'Vehicle regNo was changed: ${reg_no}. ${event_time} ${comment}'],
        ],
        [
            'name' => Template::VEHICLE_CHANGED_REGNO_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Vehicle regNo was changed',
                'body' => 'Vehicle regNo was changed: ${reg_no}. ${comment}'
            ],
        ],
        [
            'name' => Template::VEHICLE_CHANGED_REGNO_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Vehicle regNo was changed',
                'body' => 'Vehicle regNo was changed: ${reg_no}. ${comment}'
            ],
        ],
        [
            'name' => Template::VEHICLE_CHANGED_MODEL_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Vehicle model was changed',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Vehicle model changed for ${reg_no}.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - ${triggered_by} have changed vehicle model from "${old_value}" to "${model}".</p>'
                    . '<p>${vehicle_url}</p>'
                    . '<br/><p>${comment}</p>'
            ],
        ],
        [
            'name' => Template::VEHICLE_CHANGED_MODEL_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => ['body' => 'Vehicle model was changed: ${model}. ${event_time} ${comment}'],
        ],
        [
            'name' => Template::VEHICLE_CHANGED_MODEL_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Vehicle model was changed',
                'body' => 'Vehicle model was changed: ${model}. ${comment}'
            ],
        ],
        [
            'name' => Template::VEHICLE_CHANGED_MODEL_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Vehicle model was changed',
                'body' => 'Vehicle model was changed: ${model}. ${comment}'
            ],
        ],
        /* vehicle unavailable - user */
        [
            'name' => Template::VEHICLE_UNAVAILABLE_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Vehicle changed status: ' . Vehicle::STATUS_UNAVAILABLE,
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Vehicle ${reg_no_with_model} status changed to "${status}".</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - ${triggered_by} changed status for vehicle ${reg_no_with_model} with driver ${driver} to "${status}"</p>'
                    . '<p>${vehicle_url}</p>'
                    . '<br/><p>${comment}</p>',
            ],
        ],
        [
            'name' => Template::VEHICLE_UNAVAILABLE_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Vehicle changed status: ' . Vehicle::STATUS_UNAVAILABLE,
                'body' => 'Vehicle ${reg_no} has changed status: ${status}. ${comment}'
            ],
        ],
        [
            'name' => Template::VEHICLE_UNAVAILABLE_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Vehicle ${reg_no} has changed status: ${status}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::VEHICLE_UNAVAILABLE_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Vehicle changed status: ' . Vehicle::STATUS_UNAVAILABLE,
                'body' => 'Vehicle ${reg_no} has changed status: ${status}. ${comment}'
            ],
        ],

        /* vehicle offline - user */
        [
            'name' => Template::VEHICLE_OFFLINE_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Vehicle changed status: ' . Vehicle::STATUS_OFFLINE,
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Vehicle ${reg_no_with_model} status changed to "${status}".</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - Vehicle ${reg_no_with_model} with driver ${driver} changed status to "${status}"</p>'
                    . '<p>${vehicle_url}</p>'
                    . '<br/><p>${comment}</p>',
            ],
        ],
        [
            'name' => Template::VEHICLE_OFFLINE_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Vehicle changed status: ' . Vehicle::STATUS_OFFLINE,
                'body' => 'Vehicle ${reg_no} has changed status: ${status}. ${comment}'
            ],
        ],
        [
            'name' => Template::VEHICLE_OFFLINE_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Vehicle ${reg_no} has changed status: ${status}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::VEHICLE_OFFLINE_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Vehicle changed status: ' . Vehicle::STATUS_OFFLINE,
                'body' => 'Vehicle ${reg_no} has changed status: ${status} ${comment}'
            ],
        ],

        /* vehicle reassigned - user */
        [
            'name' => Template::VEHICLE_REASSIGNED_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Vehicle reassigned',
                'body' => 'Vehicle ${reg_no} has been re-assigned to driver ${driver}. ${comment}'
            ],
        ],
        [
            'name' => Template::VEHICLE_REASSIGNED_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Vehicle reassigned',
                'body' => 'Vehicle ${reg_no} has been re-assigned to driver ${driver}. ${comment}'
            ],
        ],
        [
            'name' => Template::VEHICLE_REASSIGNED_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Vehicle reassigned',
                'body' => 'Vehicle ${reg_no} has been re-assigned to driver ${driver}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::VEHICLE_REASSIGNED_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Vehicle reassigned',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Vehicle ${reg_no} re-assigned to driver ${driver}.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - Vehicle ${reg_no} re-assigned ${old_value} to "${driver}".</p>'
                    . '<p>${vehicle_url}</p>'
                    . '<p>${driver_url}</p>'
                    . '<br/><p>${comment}</p>'
            ],
        ],
        [
            'name' => Template::VEHICLE_OVER_SPEEDING_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Overspeeding',
                'body' => 'Vehicle ${reg_no} is driving more than ${avg_speed}km/h. ${comment}'
            ],
        ],
        [
            'name' => Template::VEHICLE_OVER_SPEEDING_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Vehicle ${reg_no} is driving more than ${avg_speed}km/h. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::VEHICLE_OVER_SPEEDING_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Overspeeding',
                'body' => 'Vehicle ${reg_no} is driving more than ${avg_speed}km/h. ${comment}'
            ],
        ],
        [
            'name' => Template::VEHICLE_OVER_SPEEDING_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Overspeeding',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Vehicle ${reg_no} driving >${avg_speed}km/h.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - Vehicle ${reg_no_with_model} with driver ${driver} driving more than ${avg_speed}km/h, speed limit is set to ${overSpeed}km/h</p>'
                    . '<p>${vehicle_url}</p>'
                    . '<p>${driver_url}</p>'
                    . '<br/><p>${comment}</p>'
            ],
        ],
        [
            'name' => Template::VEHICLE_OVER_SPEEDING_INSIDE_GEOFENCE_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Overspeeding inside area',
                'body' => 'Vehicle ${reg_no} is driving more than ${avg_speed}km/h to area: ${area}. ${comment}'
            ],
        ],
        [
            'name' => Template::VEHICLE_OVER_SPEEDING_INSIDE_GEOFENCE_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Vehicle ${reg_no} is driving more than ${avg_speed}km/h to area: ${area}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::VEHICLE_OVER_SPEEDING_INSIDE_GEOFENCE_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Overspeeding inside area',
                'body' => 'Vehicle ${reg_no} is driving more than ${avg_speed}km/h to area: ${area}. ${comment}'
            ],
        ],
        [
            'name' => Template::VEHICLE_OVER_SPEEDING_INSIDE_GEOFENCE_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Overspeeding inside area',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Vehicle ${reg_no} driving >${avg_speed}km/h in the area "${area}"</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - Vehicle ${reg_no_with_model} with driver ${driver} driving more than ${avg_speed}km/h in the area "${area}", speed limit is set to ${overSpeed}km/h</p>'
                    . '<p>${vehicle_url}</p>'
                    . '<p>${driver_url}</p>'
                    . '<br/><p>${comment}</p>'
            ],
        ],
        [
            'name' => Template::VEHICLE_GEOFENCE_ENTER_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Vehicle area alerts',
                'body' => 'Vehicle ${reg_no} entered to area: ${area}. ${comment}'
            ],
        ],
        [
            'name' => Template::VEHICLE_GEOFENCE_ENTER_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Vehicle ${reg_no} entered to area: ${area}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::VEHICLE_GEOFENCE_ENTER_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Vehicle area alerts',
                'body' => 'Vehicle ${reg_no} entered to area: ${area}. ${comment}'
            ],
        ],
        [
            'name' => Template::VEHICLE_GEOFENCE_ENTER_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Vehicle area alerts',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Vehicle ${reg_no} entered to area: ${area}.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - Vehicle ${reg_no_with_model} with driver ${driver} entered the area "${area}"</p>'
                    . '<p>${vehicle_url}</p>'
                    . '<p>${driver_url}</p>'
                    . '<br/><p>${comment}</p>'
            ],
        ],
        [
            'name' => Template::VEHICLE_GEOFENCE_LEAVE_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Vehicle area alerts',
                'body' => 'Vehicle ${reg_no} is departed from area: ${area}. ${comment}'
            ],
        ],
        [
            'name' => Template::VEHICLE_GEOFENCE_LEAVE_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Vehicle ${reg_no} is departed from area: ${area}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::VEHICLE_GEOFENCE_LEAVE_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Vehicle area alerts',
                'body' => 'Vehicle ${reg_no} is departed from area: ${area}. ${comment}'
            ],
        ],
        [
            'name' => Template::VEHICLE_GEOFENCE_LEAVE_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Vehicle area alerts',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Vehicle ${reg_no} is departed from area: ${area}.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - Vehicle ${reg_no_with_model} with driver ${driver} departed from the area "${area}"</p>'
                    . '<p>${vehicle_url}</p>'
                    . '<p>${driver_url}</p>'
                    . '<br/><p>${comment}</p>'
            ],
        ],
        [
            'name' => Template::VEHICLE_DRIVING_WITHOUT_DRIVER_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Vehicle driving',
                'body' => 'Vehicle is driving without driver: ${reg_no}. ${comment}'
            ],
        ],
        [
            'name' => Template::VEHICLE_DRIVING_WITHOUT_DRIVER_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Vehicle is driving without driver: ${reg_no}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::VEHICLE_DRIVING_WITHOUT_DRIVER_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Vehicle driving',
                'body' => 'Vehicle is driving without driver: ${reg_no}. ${comment}'
            ],
        ],
        [
            'name' => Template::VEHICLE_DRIVING_WITHOUT_DRIVER_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Vehicle driving',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Vehicle ${reg_no} is driving without driver.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - Vehicle ${reg_no_with_model} is driving without driver.</p>'
                    . '<p>${vehicle_url}</p>'
                    . '<br/><p>${comment}</p>'
            ],
        ],
        [
            'name' => Template::VEHICLE_TOWING_EVENT_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Vehicle towing event',
                'body' => 'Vehicle has its engine off, but is actually moving: ${reg_no}. ${comment}'
            ],
        ],
        [
            'name' => Template::VEHICLE_TOWING_EVENT_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Vehicle has its engine off, but is actually moving: ${reg_no}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::VEHICLE_TOWING_EVENT_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Vehicle towing event',
                'body' => 'Vehicle has its engine off, but is actually moving: ${reg_no}. ${comment}'
            ],
        ],
        [
            'name' => Template::VEHICLE_TOWING_EVENT_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Vehicle towing event',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Vehicle has its engine off, but is actually moving: ${reg_no}.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>Date: ${event_time}</p>'
                    . '<br/><p>${comment}</p>'
            ],
        ],
        [
            'name' => Template::VEHICLE_LONG_STANDING_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Vehicle long standing',
                'body' => 'Vehicle is stopped for more than ${duration}: ${reg_no}. ${comment}'
            ],
        ],
        [
            'name' => Template::VEHICLE_LONG_STANDING_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Vehicle is stopped for more than ${duration}: ${reg_no}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::VEHICLE_LONG_STANDING_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Vehicle long standing',
                'body' => 'Vehicle is stopped for more than ${duration}: ${reg_no}. ${comment}'
            ],
        ],
        [
            'name' => Template::VEHICLE_LONG_STANDING_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Vehicle long standing',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Vehicle ${reg_no} is stopped >${duration}.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - Vehicle ${reg_no_with_model} with driver ${driver} is stopped >${duration}</p>'
                    . '<p>${vehicle_url}</p>'
                    . '<p>${driver_url}</p>'
                    . '<br/><p>${comment}</p>'
            ],
        ],
        [
            'name' => Template::VEHICLE_LONG_DRIVING_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Vehicle long driving',
                'body' => 'Vehicle is driving for more than ${duration} continuously and has driven for more than ${distance}: ${reg_no}. ${comment}'
            ],
        ],
        [
            'name' => Template::VEHICLE_LONG_DRIVING_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Vehicle is driving for more than ${duration} continuously: ${reg_no}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::VEHICLE_LONG_DRIVING_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Vehicle long driving',
                'body' => 'Vehicle is driving for more than ${duration} continuously and has driven for more than ${distance}: ${reg_no}. ${comment}'
            ],
        ],
        [
            'name' => Template::VEHICLE_LONG_DRIVING_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Vehicle long driving',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Vehicle ${reg_no} is driving continuously >${duration}</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - Vehicle ${reg_no_with_model} with driver ${driver} is driving continuously >${duration}</p>'
                    . '<p>${vehicle_url}</p>'
                    . '<p>${driver_url}</p>'
                    . '<br/><p>${comment}</p>'
            ],
        ],
        [
            'name' => Template::VEHICLE_MOVING_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Vehicle moving',
                'body' => 'Vehicle starts to move: ${reg_no}. ${comment}'
            ],
        ],
        [
            'name' => Template::VEHICLE_MOVING_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Vehicle starts to move: ${reg_no}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::VEHICLE_MOVING_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Vehicle moving',
                'body' => 'Vehicle starts to move: ${reg_no}. ${comment}'
            ],
        ],
        [
            'name' => Template::VEHICLE_MOVING_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Vehicle moving',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Vehicle ${reg_no} with driver ${driver} starts moving.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - Vehicle ${reg_no_with_model} with driver ${driver} starts moving</p>'
                    . '<p>${vehicle_url}</p>'
                    . '<p>${driver_url}</p>'
                    . '<br/><p>${comment}</p>'
            ],
        ],
        [
            'name' => Template::VEHICLE_EXCESSING_IDLING_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Vehicle excessive idling',
                'body' => '${reg_no_or_device} is idling more than ${duration}. ${comment}'
            ],
        ],
        [
            'name' => Template::VEHICLE_EXCESSING_IDLING_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => '${reg_no_or_device} is idling more than ${duration}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::VEHICLE_EXCESSING_IDLING_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Vehicle excessive idling',
                'body' => '${reg_no_or_device} is idling more than ${duration}. ${comment}'
            ],
        ],
        [
            'name' => Template::VEHICLE_EXCESSING_IDLING_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Vehicle excessive idling',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>${reg_no_or_device} is idling more than ${duration}.</p>'
                    . '<p>${event_time} - ${reg_no_with_model_or_device} ${driver} is idling more than ${duration}</p>'
                    . '<p style="color: red;">${note}</p>'
                    . '<p>${vehicle_url}</p>'
                    . '<p>${driver_url}</p>'
                    . '<br/><p>${comment}</p>'
            ],
        ],
        [
            'name' => Template::VEHICLE_ONLINE_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Vehicle changed status: ' . Vehicle::STATUS_ONLINE,
                'body' => 'Vehicle ${reg_no} has changed status: ${status}. ${comment}'
            ],
        ],
        [
            'name' => Template::VEHICLE_ONLINE_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Vehicle ${reg_no} has changed status: ${status}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::VEHICLE_ONLINE_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Vehicle changed status: ' . Vehicle::STATUS_ONLINE,
                'body' => 'Vehicle ${reg_no} has changed status: ${status}. ${comment}'
            ],
        ],
        [
            'name' => Template::VEHICLE_ONLINE_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Vehicle changed status: ' . Vehicle::STATUS_ONLINE,
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Vehicle ${reg_no_with_model} status changed to "${status}".</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - Vehicle ${reg_no_with_model} with driver ${driver} changed status to "${status}"</p>'
                    . '<p>${vehicle_url}</p>'
                    . '<br/><p>${comment}</p>'
            ],
        ],
        /* document record service */
        [
            'name' => Template::DOCUMENT_EXPIRED_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Document expired',
                'body' => 'Document expired: ${title}. ${comment}'
            ],
        ],
        [
            'name' => Template::DOCUMENT_EXPIRED_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Document expired: ${title}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::DOCUMENT_EXPIRED_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Document expired',
                'body' => 'Document expired: ${title}. ${comment}'
            ],
        ],
        [
            'name' => Template::DOCUMENT_EXPIRED_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Document expired',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Document expired ${data_by_type}.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${exp_date} - expired document - "${title}" ${data_by_type}.</p>'
                    . '<p>${data_url}</p>'
                    . '<br/><p>${comment}</p>'
            ],
        ],
        [
            'name' => Template::DOCUMENT_EXPIRE_SOON_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Document expire soon',
                'body' => 'Document soon to expire: ${title}. ${comment}'
            ],
        ],
        [
            'name' => Template::DOCUMENT_EXPIRE_SOON_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Document soon to expire: ${title}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::DOCUMENT_EXPIRE_SOON_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Document expire soon',
                'body' => 'Document soon to expire: ${title}. ${comment}'
            ],
        ],
        [
            'name' => Template::DOCUMENT_EXPIRE_SOON_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Document expire soon',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Document soon to expire ${data_by_type}.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - document "${title}" ${data_by_type} will expire on ${expiration_date}.</p>'
                    . '<p>${data_url}</p>'
                    . '<br/><p>${comment}</p>',

            ],
        ],
        [
            'name' => Template::DOCUMENT_DELETED_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Document deleted',
                'body' => 'Document deleted: ${title}. ${comment}'
            ],
        ],
        [
            'name' => Template::DOCUMENT_DELETED_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Document deleted: ${title}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::DOCUMENT_DELETED_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Document deleted',
                'body' => 'Document deleted: ${title}. ${comment}'
            ],
        ],
        [
            'name' => Template::DOCUMENT_DELETED_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Document deleted',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Document deleted ${data_by_type}.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - ${triggered_by} have deleted document "${title}" ${data_by_type}</p>'
                    . '<p>${data_url}</p>'
                    . '<br/><p>${comment}</p>'
            ],
        ],
        [
            'name' => Template::DOCUMENT_RECORD_ADDED_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Document record added',
                'body' => 'Document record added: ${title}. ${comment}'
            ],
        ],
        [
            'name' => Template::DOCUMENT_RECORD_ADDED_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Document record added: ${title}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::DOCUMENT_RECORD_ADDED_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Document record added',
                'body' => 'Document record added: ${title}. ${comment}'
            ],
        ],
        [
            'name' => Template::DOCUMENT_RECORD_ADDED_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Document record added',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Document record added ${data_by_type}.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - ${triggered_by} have added new record to the document "${title}" ${data_by_type}</p>'
                    . '<p>${data_url}</p>'
                    . '<br/><p>${comment}</p>'
            ],
        ],
        /* device - user */
        [
            'name' => Template::DEVICE_IN_STOCK_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Device in stock',
                'body' => 'Device in stock: ${device_imei}. ${comment}'
            ],
        ],
        [
            'name' => Template::DEVICE_IN_STOCK_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Device in stock: ${device_imei}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::DEVICE_IN_STOCK_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Device in stock',
                'body' => 'Device in stock: ${device_imei}. ${comment}'
            ],
        ],
        [
            'name' => Template::DEVICE_IN_STOCK_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Device in stock',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Device in stock: ${device_imei}.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - Device ${device_imei} changed status to "in stock"</p>'
                    . '<p>${data_url}</p>'
                    . '<br/><p>${comment}</p>',
            ],
        ],
        [
            'name' => Template::DEVICE_OFFLINE_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Device changed status: ' . Device::STATUS_OFFLINE,
                'body' => 'Device ${device_imei} has changed status: ' . Device::STATUS_OFFLINE . '. ${comment}'
            ],
        ],
        [
            'name' => Template::DEVICE_OFFLINE_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Device ${device_imei} has changed status: ' . Device::STATUS_OFFLINE . '. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::DEVICE_OFFLINE_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Device changed status: ' . Device::STATUS_OFFLINE,
                'body' => 'Device ${device_imei} has changed status: ' . Device::STATUS_OFFLINE . '. ${comment}'
            ],
        ],
        [
            'name' => Template::DEVICE_OFFLINE_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Device changed status: ' . Device::STATUS_OFFLINE,
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Device ${device_imei} status changed to "' . Device::STATUS_OFFLINE . '"</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - Device ${device_imei} changed status to "' . Device::STATUS_OFFLINE . '"</p>'
                    . '<p>${data_url}</p>'
                    . '<br/><p>${comment}</p>',
            ],
        ],
        [
            'name' => Template::DEVICE_UNAVAILABLE_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Device changed status: ' . Device::STATUS_UNAVAILABLE,
                'body' => 'Device ${device_imei} has changed status: ' . Device::STATUS_UNAVAILABLE . '. ${comment}'
            ],
        ],
        [
            'name' => Template::DEVICE_UNAVAILABLE_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Device ${device_imei} has changed status: ' . Device::STATUS_UNAVAILABLE . '. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::DEVICE_UNAVAILABLE_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Device changed status: ' . Device::STATUS_UNAVAILABLE,
                'body' => 'Device ${device_imei} has changed status: ' . Device::STATUS_UNAVAILABLE . '. ${comment}'
            ],
        ],
        [
            'name' => Template::DEVICE_UNAVAILABLE_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Device changed status: ' . Device::STATUS_UNAVAILABLE,
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Device ${device_imei} status changed to "' . Device::STATUS_UNAVAILABLE . '"</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - Device ${device_imei} changed status to "' . Device::STATUS_UNAVAILABLE . '"</p>'
                    . '<p>${data_url}</p>'
                    . '<br/><p>${comment}</p>',
            ],
        ],
        [
            'name' => Template::DEVICE_DELETED_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Device deleted',
                'body' => 'Device deleted:  ${device_imei}. ${comment}'
            ],
        ],
        [
            'name' => Template::DEVICE_DELETED_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Device deleted:  ${device_imei}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::DEVICE_DELETED_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Device deleted',
                'body' => 'Device deleted:  ${device_imei}. ${comment}'
            ],
        ],
        [
            'name' => Template::DEVICE_DELETED_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Device deleted',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Device ${device_imei} was deleted.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - ${triggered_by} have deleted device ${device_imei}.</p>'
                    . '<p>${data_url}</p>'
                    . '<br/><p>${comment}</p>',
            ],
        ],
        [
            'name' => Template::DEVICE_UNKNOWN_DETECTED_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Device unknown detected',
                'body' => 'Device unknown detected:  ${device_imei}. ${comment}'
            ],
        ],
        [
            'name' => Template::DEVICE_UNKNOWN_DETECTED_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Device unknown detected:  ${device_imei}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::DEVICE_UNKNOWN_DETECTED_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Device unknown detected',
                'body' => 'Device unknown detected:  ${device_imei}. ${comment}'
            ],
        ],
        [
            'name' => Template::DEVICE_UNKNOWN_DETECTED_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Device unknown detected',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Device unknown detected:  ${device_imei}</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>Date: ${event_time}</p>'
                    . '<br/><p>${comment}</p>'
            ],
        ],
        /* service reminder */
        [
            'name' => Template::SERVICE_REMINDER_SOON_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Service reminder soon',
                'body' => 'Service reminder soon: ${title}. ${comment}'
            ],
        ],
        [
            'name' => Template::SERVICE_REMINDER_SOON_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Service reminder soon: ${title}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::SERVICE_REMINDER_SOON_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Service reminder soon',
                'body' => 'Service reminder soon: ${title}. ${comment}'
            ],
        ],
        [
            'name' => Template::SERVICE_REMINDER_SOON_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Service reminder soon',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Service reminder soon (${entity}).</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - service reminder "${title}" (${entity}) will expire on ${expiration_date}.</p>'
                    . '<p>${data_url}</p>'
                    . '<br/><p>${comment}</p>'
            ],
        ],
        [
            'name' => Template::SERVICE_REMINDER_EXPIRED_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Service reminder expired',
                'body' => 'Service reminder expired: ${title}. ${comment}'
            ],
        ],
        [
            'name' => Template::SERVICE_REMINDER_EXPIRED_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Service reminder expired: ${title}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::SERVICE_REMINDER_EXPIRED_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Service reminder expired',
                'body' => 'Service reminder expired: ${title}. ${comment}'
            ],
        ],
        [
            'name' => Template::SERVICE_REMINDER_EXPIRED_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Service reminder expired',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Service reminder expired (${entity}).</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - service reminder "${title}" (${entity}) expired.</p>'
                    . '<p>${data_url}</p>'
                    . '<br/><p>${comment}</p>'
            ],
        ],
        [
            'name' => Template::SERVICE_REMINDER_DONE_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Service reminder done',
                'body' => 'Service reminder done: ${title}. ${comment}'
            ],
        ],
        [
            'name' => Template::SERVICE_REMINDER_DONE_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Service reminder done: ${title}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::SERVICE_REMINDER_DONE_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Service reminder done',
                'body' => 'Service reminder done: ${title}. ${comment}'
            ],
        ],
        [
            'name' => Template::SERVICE_REMINDER_DONE_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Service reminder done',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Service reminder done (${entity}).</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - ${triggered_by} have done service reminder "${title}" (${entity}).</p>'
                    . '<p>${data_url}</p>'
                    . '<br/><p>${comment}</p>'
            ],
        ],
        [
            'name' => Template::SERVICE_REMINDER_DELETED_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Service reminder deleted',
                'body' => 'Service reminder deleted: ${title}. ${comment}'
            ],
        ],
        [
            'name' => Template::SERVICE_REMINDER_DELETED_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Service reminder deleted: ${title}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::SERVICE_REMINDER_DELETED_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Service reminder deleted',
                'body' => 'Service reminder deleted: ${title}. ${comment}'
            ],
        ],
        [
            'name' => Template::SERVICE_REMINDER_DELETED_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Service reminder deleted',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Service reminder deleted (${entity}).</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - ${triggered_by} deleted service reminder "${title}" (${entity}).</p>'
                    . '<p>${data_url}</p>'
                    . '<br/><p>${comment}</p>'
            ],
        ],
        /* service reminder */
        [
            'name' => Template::SERVICE_RECORD_ADDED_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Service record added',
                'body' => 'Service record added: ${title}. ${comment}'
            ],
        ],
        [
            'name' => Template::SERVICE_RECORD_ADDED_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Service record added: ${title}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::SERVICE_RECORD_ADDED_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Service record added',
                'body' => 'Service record added: ${title}. ${comment}'
            ],
        ],
        [
            'name' => Template::SERVICE_RECORD_ADDED_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Service record added',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Service record added (${entity}).</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - ${triggered_by} have added new service record "${title}" (${entity}).</p>'
                    . '<p>${data_url}</p>'
                    . '<br/><p>${comment}</p>'
            ],
        ],
        [
            'name' => Template::SERVICE_REPAIR_ADDED_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Service repair added',
                'body' => 'Service repair added: ${title}. ${comment}'
            ],
        ],
        [
            'name' => Template::SERVICE_REPAIR_ADDED_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Service repair added: ${title}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::SERVICE_REPAIR_ADDED_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Service repair added',
                'body' => 'Service repair added: ${title}. ${comment}'
            ],
        ],
        [
            'name' => Template::SERVICE_REPAIR_ADDED_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Service repair added',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Service repair added (${entity}).</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - ${triggered_by} have added new service repair "${title}" (${entity}).</p>'
                    . '<p>${data_url}</p>'
                    . '<br/><p>${comment}</p>'
            ],
        ],
        /* tracker */
        [
            'name' => Template::TRACKER_VOLTAGE_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Voltage',
                'body' => 'Supply voltage to tracker is ${battery_voltage}V on the vehicle: ${vehicle}. ${comment}'
            ],
        ],
        [
            'name' => Template::TRACKER_VOLTAGE_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Supply voltage to tracker is ${battery_voltage}V on the vehicle: ${vehicle}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::TRACKER_VOLTAGE_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Voltage Alerts',
                'body' => 'Supply voltage to tracker is ${battery_voltage}V on the vehicle: ${vehicle}. ${comment}'
            ],
        ],
        [
            'name' => Template::TRACKER_VOLTAGE_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Voltage',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Tracker Supply voltage is ${battery_voltage}V in the vehicle ${vehicle}.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - Tracker supply voltage in the vehicle ${reg_no_with_model} with driver ${driver} is ${battery_voltage}V, voltage limit is set to ${deviceVoltage}</p>'
                    . '<p>${vehicle_url}</p>'
                    . '<br/><p>${comment}</p>'
            ],
        ],
        /* tracker jammer */
        [
            'name' => Template::TRACKER_JAMMER_STARTED_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Jammer',
                'body' => 'Jammer started on ${reg_no_or_device}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::TRACKER_JAMMER_STARTED_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Jammer started on ${reg_no_or_device}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::TRACKER_JAMMER_STARTED_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Jammer started - ${reg_no_or_device}',
                'body' => 'Jammer started on ${reg_no_or_device}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::TRACKER_JAMMER_STARTED_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Jammer',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Jammer started ${reg_no_or_device}. ${event_time} ${comment}.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - Jammer started on ${reg_no_or_device} with driver ${driver}.</p>'
                    . '<p>${vehicle_url}</p>'
                    . '<br/><p>${comment}</p>'
            ],
        ],
        /* tracker accident */
        [
            'name' => Template::TRACKER_ACCIDENT_HAPPENED_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Accident',
                'body' => 'Accident happened on ${reg_no_or_device}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::TRACKER_ACCIDENT_HAPPENED_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Accident happened on ${reg_no_or_device}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::TRACKER_ACCIDENT_HAPPENED_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Accident happened - ${reg_no_or_device}',
                'body' => 'Accident happened on ${reg_no_or_device}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::TRACKER_ACCIDENT_HAPPENED_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Accident',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Accident happened ${reg_no_or_device}. ${event_time} ${comment}.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - Accident happened on ${reg_no_or_device} with driver ${driver}.</p>'
                    . '<p>${vehicle_url}</p>'
                    . '<br/><p>${comment}</p>'
            ],
        ],

        /* sos button - user */
        [
            'name' => Template::PANIC_BUTTON_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => '[${from_company} SOS button - ${reg_no_or_device}',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>SOS button pressed on ${reg_no_or_device}.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - SOS button pressed by driver: ${driver} on ${reg_no_with_model_or_device}.</p>'
                    . '<p>${vehicle_url}</p>'
                    . '<p>${driver_url}</p>'
                    . '<br/><p>${comment}</p>',
            ],
        ],
        [
            'name' => Template::PANIC_BUTTON_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'SOS button pressed by driver: ${driver} on ${reg_no_or_device}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::PANIC_BUTTON_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'SOS button - ${reg_no_or_device}',
                'body' => 'SOS button pressed by driver: ${driver} on ${reg_no_or_device}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::PANIC_BUTTON_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'SOS button - ${reg_no_or_device}',
                'body' => 'SOS button pressed by driver: ${driver} on ${reg_no_or_device}. ${comment}'
            ],
        ],
        /* user activity - user */
        [
            'name' => Template::LOGIN_AS_CLIENT_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'User activity',
                'body' => 'Login as client: ${client_email} ${client_name}. ${comment}'
            ],
        ],
        [
            'name' => Template::LOGIN_AS_CLIENT_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Login as client: ${client_email} ${client_name}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::LOGIN_AS_CLIENT_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'User activity',
                'body' => 'Login as client: ${client_email} ${client_name}. ${comment}'
            ],
        ],
        [
            'name' => Template::LOGIN_AS_CLIENT_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'User activity',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Login as client: ${client_email} ${client_name}.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>Date: ${event_time}.</p>'
                    . '<br/><p>${comment}</p>'
            ],
        ],
        [
            'name' => Template::LOGIN_AS_USER_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'User activity',
                'body' => 'Login as user: ${user_email} ${user_name}. ${comment}'
            ],
        ],
        [
            'name' => Template::LOGIN_AS_USER_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'User activity',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Login as user: ${user_email} ${user_name}</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>Date: ${event_time}.</p>'
                    . '<br/><p>${comment}</p>'
            ],
        ],
        [
            'name' => Template::LOGIN_AS_USER_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Login as user: ${user_email} ${user_name}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::LOGIN_AS_USER_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'User activity',
                'body' => 'Login as user: ${user_email} ${user_name}. ${comment}'
            ],
        ],
        [
            'name' => Template::ODOMETER_CORRECTED_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Odometer corrected',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Odometer set to ${new_value} for ${reg_no}</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - ${user_name} corrected odometer for ${reg_no}; old value - ${old_value}, new value - ${new_value}.</p>'
                    . '<br/><p>${comment}</p>',
            ],
        ],
        [
            'name' => Template::ODOMETER_CORRECTED_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => ['body' => 'Odometer set to ${new_value} for ${reg_no}. ${event_time} ${comment}'],
        ],
        [
            'name' => Template::ODOMETER_CORRECTED_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Odometer corrected - ${reg_no}',
                'body' => 'Odometer set to ${new_value} for ${reg_no}. ${comment}'
            ],
        ],
        [
            'name' => Template::ODOMETER_CORRECTED_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Odometer corrected',
                'body' => 'Odometer set to ${new_value} for ${reg_no}. ${comment}'
            ],
        ],

        /* vehicle deleted - user */
        [
            'name' => Template::DIGITAL_FORM_WITH_FAIL_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Form completed with a fail',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Form was completed with a fail by driver: ${driver} to vehicle: ${reg_no}.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - Form: ${form_title} was completed with a fail by driver: ${driver} to vehicle: ${reg_no}</p>'
                    . '<p>${data_url}</p>'
                    . '<br/><p>${comment}</p>',
            ],
        ],
        [
            'name' => Template::DIGITAL_FORM_WITH_FAIL_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Form was completed with a fail by driver: ${driver} to vehicle: ${reg_no}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::DIGITAL_FORM_WITH_FAIL_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Form completed with a fail',
                'body' => 'Form was completed with a fail by driver: ${driver} to vehicle: ${reg_no}. ${comment}'
            ],
        ],
        [
            'name' => Template::DIGITAL_FORM_WITH_FAIL_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Form completed with a fail',
                'body' => 'Form was completed with a fail by driver: ${driver} to vehicle: ${reg_no}. ${comment}'
            ],
        ],
        /* digital form - user */
        [
            'name' => Template::DIGITAL_FORM_IS_NOT_COMPLETED_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Form is not completed',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Form is not completed by driver: ${driver} to vehicle: ${reg_no}.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - Form is not completed by driver: ${driver} to vehicle: ${reg_no}.</p>'
                    . '<p>${vehicle_url}</p>'
                    . '<p>${driver_url}</p>'
                    . '<br/><p>${comment}</p>',
            ],
        ],
        [
            'name' => Template::DIGITAL_FORM_IS_NOT_COMPLETED_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Form is not completed by driver: ${driver} to vehicle: ${reg_no}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::DIGITAL_FORM_IS_NOT_COMPLETED_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Form is not completed',
                'body' => 'Form is not completed by driver: ${driver} to vehicle: ${reg_no}. ${comment}'
            ],
        ],
        [
            'name' => Template::DIGITAL_FORM_IS_NOT_COMPLETED_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Form is not completed',
                'body' => 'Form is not completed by driver: ${driver} to vehicle: ${reg_no}. ${comment}'
            ],
        ],
        [
            'name' => Template::DIGITAL_FORM_IS_NOT_COMPLETED_SYSTEM_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Form is not completed',
                'body' => 'You need to complete an inspection form for vehicle ${reg_no}.'
            ],
        ],
        [
            'name' => Template::DIGITAL_FORM_IS_NOT_COMPLETED_SYSTEM_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Form is not completed',
                'body' => 'You need to complete an inspection form for vehicle ${reg_no}.'
            ],
        ],
        [
            'name' => Template::SENSOR_TEMPERATURE_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Sensor temperature',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Sensor ${sensor_id}${label} detected temp value ${temperature}.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - sensor ${sensor_id}${label}, installed in ${reg_no}, detected temperature value ${sensor_temperature}${temperature}.</p>'
                    . '<br/><p>${comment}</p>',
            ],
        ],
        [
            'name' => Template::SENSOR_TEMPERATURE_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => ['body' => 'Sensor ${sensor_id} detected temp value ${sensor_temperature}${temperature}. ${event_time}'],
        ],
        [
            'name' => Template::SENSOR_TEMPERATURE_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Sensor Temperature - ${reg_no}',
                'body' => 'Sensor ${sensor_id} detected temp value ${sensor_temperature}${temperature}.'
            ],
        ],
        [
            'name' => Template::SENSOR_TEMPERATURE_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Sensor Temperature',
                'body' => 'Sensor ${sensor_id} detected temp value ${sensor_temperature}${temperature}.'
            ],
        ],
        [
            'name' => Template::SENSOR_HUMIDITY_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Sensor humidity',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Sensor ${sensor_id}${label} detected hum value ${sensor_humidity}.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - sensor ${sensor_id}${label}, installed in ${reg_no}, detected humidity value ${sensor_humidity}${humidity}.</p>'
                    . '<br/><p>${comment}</p>',
            ],
        ],
        [
            'name' => Template::SENSOR_HUMIDITY_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => ['body' => 'Sensor ${sensor_id} detected humidity value ${sensor_humidity}${humidity}. ${event_time}'],
        ],
        [
            'name' => Template::SENSOR_HUMIDITY_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Sensor Humidity - ${reg_no}',
                'body' => 'Sensor ${sensor_id} detected num value ${sensor_humidity}${humidity}.'
            ],
        ],
        [
            'name' => Template::SENSOR_HUMIDITY_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Sensor Humidity',
                'body' => 'Sensor ${sensor_id} detected hum value ${sensor_humidity}${humidity}.'
            ],
        ],
        [
            'name' => Template::SENSOR_LIGHT_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Sensor light',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Sensor ${sensor_id}${label} detected light status ${sensor_light_status}.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - sensor ${sensor_id}${label}, installed in ${reg_no}, detected light status changed to ${sensor_light_status}.</p>'
                    . '<br/><p>${comment}</p>',
            ],
        ],
        [
            'name' => Template::SENSOR_LIGHT_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => ['body' => 'Sensor ${sensor_id} detected light status changed to ${sensor_light_status}. ${event_time}'],
        ],
        [
            'name' => Template::SENSOR_LIGHT_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Sensor light - ${reg_no}',
                'body' => 'Sensor ${sensor_id} detected light status changed to ${sensor_light_status}.'
            ],
        ],
        [
            'name' => Template::SENSOR_LIGHT_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Sensor light',
                'body' => 'Sensor ${sensor_id} detected light status changed to ${sensor_light_status}.'
            ],
        ],
        [
            'name' => Template::SENSOR_BATTERY_LEVEL_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Sensor battery level',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Sensor ${sensor_id}${label} detected battery level ${sensor_battery_level}.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - sensor ${sensor_id}${label}, installed in ${reg_no}, detected battery level ${sensor_battery_level} ${batteryLevel}.</p>'
                    . '<br/><p>${comment}</p>',
            ],
        ],
        [
            'name' => Template::SENSOR_BATTERY_LEVEL_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => ['body' => 'Sensor ${sensor_id} detected battery level ${sensor_battery_level} ${batteryLevel}. ${event_time}'],
        ],
        [
            'name' => Template::SENSOR_BATTERY_LEVEL_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Sensor battery level - ${reg_no}',
                'body' => 'Sensor ${sensor_id} detected battery level ${sensor_battery_level} ${batteryLevel}.'
            ],
        ],
        [
            'name' => Template::SENSOR_BATTERY_LEVEL_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Sensor battery level',
                'body' => 'Sensor ${sensor_id} detected battery level ${sensor_battery_level} ${batteryLevel}.'
            ],
        ],
        [
            'name' => Template::SENSOR_STATUS_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Sensor status',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Sensor ${sensor_id}${label} is now ${sensor_status}.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - sensor ${sensor_id}${label}, installed in ${reg_no}, changed status to ${sensor_status}.</p>'
                    . '<br/><p>${comment}</p>',
            ],
        ],
        [
            'name' => Template::SENSOR_STATUS_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => ['body' => 'Sensor ${sensor_id} changed status to ${sensor_status}. ${event_time}'],
        ],
        [
            'name' => Template::SENSOR_STATUS_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Sensor status - ${reg_no}',
                'body' => 'Sensor ${sensor_id} changed status to ${sensor_status}.'
            ],
        ],
        [
            'name' => Template::SENSOR_STATUS_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Sensor status',
                'body' => 'Sensor ${sensor_id} changed status to ${sensor_status}.'
            ],
        ],
        [
            'name' => Template::DRIVER_DOCUMENT_EXPIRE_SOON_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Document expire soon',
                'body' => 'Document soon to expire: ${title}. ${comment}'
            ],
        ],
        [
            'name' => Template::DRIVER_DOCUMENT_EXPIRE_SOON_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Document soon to expire: ${title}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::DRIVER_DOCUMENT_EXPIRE_SOON_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Document expire soon',
                'body' => 'Document soon to expire: ${title}. ${comment}'
            ],
        ],
        [
            'name' => Template::DRIVER_DOCUMENT_EXPIRE_SOON_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Document expire soon',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Document soon to expire ${data_by_type}.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - document "${title}" ${data_by_type} will expire on ${expiration_date}.</p>'
                    . '<p>${data_url}</p>'
                    . '<br/><p>${comment}</p>',

            ],
        ],
        [
            'name' => Template::DRIVER_DOCUMENT_EXPIRED_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Document expired',
                'body' => 'Document expired: ${title}. ${comment}'
            ],
        ],
        [
            'name' => Template::DRIVER_DOCUMENT_EXPIRED_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Document expired: ${title}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::DRIVER_DOCUMENT_EXPIRED_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Document expired',
                'body' => 'Document expired: ${title}. ${comment}'
            ],
        ],
        [
            'name' => Template::DRIVER_DOCUMENT_EXPIRED_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Document expired',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Document expired ${data_by_type}.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${exp_date} - expired document - "${title}" ${data_by_type}.</p>'
                    . '<p>${data_url}</p>'
                    . '<br/><p>${comment}</p>'
            ],
        ],
        [
            'name' => Template::DRIVER_DOCUMENT_DELETED_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Document deleted',
                'body' => 'Document deleted: ${title}. ${comment}'
            ],
        ],
        [
            'name' => Template::DRIVER_DOCUMENT_DELETED_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Document deleted: ${title}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::DRIVER_DOCUMENT_DELETED_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Document deleted',
                'body' => 'Document deleted: ${title}. ${comment}'
            ],
        ],
        [
            'name' => Template::DRIVER_DOCUMENT_DELETED_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Document deleted',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Document deleted ${data_by_type}.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - ${triggered_by} have deleted document "${title}" ${data_by_type}</p>'
                    . '<p>${data_url}</p>'
                    . '<br/><p>${comment}</p>'
            ],
        ],
        [
            'name' => Template::DRIVER_DOCUMENT_RECORD_ADDED_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Document record added',
                'body' => 'Document record added: ${title}. ${comment}'
            ],
        ],
        [
            'name' => Template::DRIVER_DOCUMENT_RECORD_ADDED_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Document record added: ${title}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::DRIVER_DOCUMENT_RECORD_ADDED_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Document record added',
                'body' => 'Document record added: ${title}. ${comment}'
            ],
        ],
        [
            'name' => Template::DRIVER_DOCUMENT_RECORD_ADDED_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Document record added',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Document record added ${data_by_type}.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - ${triggered_by} have added new record to the document "${title}" ${data_by_type}</p>'
                    . '<p>${data_url}</p>'
                    . '<br/><p>${comment}</p>'
            ],
        ],
        [
            'name' => Template::ASSET_DOCUMENT_EXPIRE_SOON_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Document expire soon',
                'body' => 'Document soon to expire: ${title}. ${comment}'
            ],
        ],
        [
            'name' => Template::ASSET_DOCUMENT_EXPIRE_SOON_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Document soon to expire: ${title}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::ASSET_DOCUMENT_EXPIRE_SOON_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Document expire soon',
                'body' => 'Document soon to expire: ${title}. ${comment}'
            ],
        ],
        [
            'name' => Template::ASSET_DOCUMENT_EXPIRE_SOON_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Document expire soon',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Document soon to expire ${data_by_type}.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - document "${title}" ${data_by_type} will expire on ${expiration_date}.</p>'
                    . '<p>${data_url}</p>'
                    . '<br/><p>${comment}</p>',

            ],
        ],
        [
            'name' => Template::ASSET_DOCUMENT_EXPIRED_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Document expired',
                'body' => 'Document expired: ${title}. ${comment}'
            ],
        ],
        [
            'name' => Template::ASSET_DOCUMENT_EXPIRED_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Document expired: ${title}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::ASSET_DOCUMENT_EXPIRED_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Document expired',
                'body' => 'Document expired: ${title}. ${comment}'
            ],
        ],
        [
            'name' => Template::ASSET_DOCUMENT_EXPIRED_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Document expired',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Document expired ${data_by_type}.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${exp_date} - expired document - "${title}" ${data_by_type}.</p>'
                    . '<p>${data_url}</p>'
                    . '<br/><p>${comment}</p>'
            ],
        ],
        [
            'name' => Template::ASSET_DOCUMENT_DELETED_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Document deleted',
                'body' => 'Document deleted: ${title}. ${comment}'
            ],
        ],
        [
            'name' => Template::ASSET_DOCUMENT_DELETED_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Document deleted: ${title}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::ASSET_DOCUMENT_DELETED_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Document deleted',
                'body' => 'Document deleted: ${title}. ${comment}'
            ],
        ],
        [
            'name' => Template::ASSET_DOCUMENT_DELETED_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Document deleted',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Document deleted ${data_by_type}.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - ${triggered_by} have deleted document "${title}" ${data_by_type}</p>'
                    . '<p>${data_url}</p>'
                    . '<br/><p>${comment}</p>'
            ],
        ],
        [
            'name' => Template::ASSET_DOCUMENT_RECORD_ADDED_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Document record added',
                'body' => 'Document record added: ${title}. ${comment}'
            ],
        ],
        [
            'name' => Template::ASSET_DOCUMENT_RECORD_ADDED_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Document record added: ${title}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::ASSET_DOCUMENT_RECORD_ADDED_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Document record added',
                'body' => 'Document record added: ${title}. ${comment}'
            ],
        ],
        [
            'name' => Template::ASSET_DOCUMENT_RECORD_ADDED_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Document record added',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Document record added ${data_by_type}.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - ${triggered_by} have added new record to the document "${title}" ${data_by_type}</p>'
                    . '<p>${data_url}</p>'
                    . '<br/><p>${comment}</p>'
            ],
        ],
        [
            'name' => Template::SENSOR_IO_STATUS_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Digital I/O status - ${sensor_status}',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Digital I/O ${sensor_io_type}  in ${device_or_vehicle} changed status to ${sensor_status}.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - Digital I/O ${sensor_io_type} in device ${device_imei}, ${reg_no} changed status to ${sensor_status}.</p>'
                    . '<p>${vehicle_url}</p>'
                    . '<br/><p>${comment}</p>',
            ],
        ],
        [
            'name' => Template::SENSOR_IO_STATUS_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Digital I/O ${sensor_io_type} in ${device_or_vehicle} changed status to ${sensor_status}. ${event_time}'
            ],
        ],
        [
            'name' => Template::SENSOR_IO_STATUS_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Digital I/O status - ${sensor_status}',
                'body' => 'Digital I/O ${sensor_io_type} in ${device_or_vehicle} changed status to ${sensor_status}.'
            ],
        ],
        [
            'name' => Template::SENSOR_IO_STATUS_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Digital I/O status - ${sensor_status}',
                'body' => 'Digital I/O ${sensor_io_type} in ${device_or_vehicle} changed status to ${sensor_status}.'
            ],
        ],
        [
            'name' => Template::ASSET_CREATED_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Asset created',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Asset created: ${asset_name}</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - ${triggered_by} have created asset ${asset_name} [team: ${team}].</p>'
                    . '<br/><p>${comment}</p>',
            ],
        ],
        [
            'name' => Template::ASSET_CREATED_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Asset created: ${asset_name}. ${event_time}'
            ],
        ],
        [
            'name' => Template::ASSET_CREATED_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Asset created: ${asset_name}',
                'body' => '${event_time} - ${triggered_by} have created asset ${asset_name} [team: ${team}].'
            ],
        ],
        [
            'name' => Template::ASSET_CREATED_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Asset created: ${asset_name}',
                'body' => '${event_time} - ${triggered_by} have created asset ${asset_name} [team: ${team}].'
            ],
        ],
        [
            'name' => Template::ASSET_DELETED_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Asset deleted',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Asset deleted: ${asset_name}</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - ${triggered_by} have deleted asset ${asset_name} [team: ${team}].</p>'
                    . '<br/><p>${comment}</p>',
            ],
        ],
        [
            'name' => Template::ASSET_DELETED_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Asset deleted: ${asset_name}. ${event_time}'
            ],
        ],
        [
            'name' => Template::ASSET_DELETED_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Asset deleted: ${asset_name}',
                'body' => '${event_time} - ${triggered_by} have deleted asset ${asset_name} [team: ${team}].'
            ],
        ],
        [
            'name' => Template::ASSET_DELETED_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Asset deleted: ${asset_name}',
                'body' => '${event_time} - ${triggered_by} have deleted asset ${asset_name} [team: ${team}].'
            ],
        ],
        [
            'name' => Template::ASSET_MISSED_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Asset missed',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Asset missed: ${asset_name}</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - asset missed: ${asset_name}.</p>'
                    . '<br/><p>${comment}</p>',
            ],
        ],
        [
            'name' => Template::ASSET_MISSED_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Asset deleted: ${asset_name}. ${event_time}'
            ],
        ],
        [
            'name' => Template::ASSET_MISSED_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Asset missed: ${asset_name}',
                'body' => '${event_time} - asset missed: ${asset_name}.'
            ],
        ],
        [
            'name' => Template::ASSET_MISSED_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Asset missed: ${asset_name}',
                'body' => '${event_time} - asset missed: ${asset_name}.'
            ],
        ],
        [
            'name' => Template::TRACKER_BATTERY_PERCENTAGE_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Internal Battery Voltage',
                'body' => 'Internal battery voltage percentage to tracker is ${battery_percentage}% on the vehicle: ${vehicle}. ${comment}'
            ],
        ],
        [
            'name' => Template::TRACKER_BATTERY_PERCENTAGE_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Internal battery voltage percentage to tracker is ${battery_percentage}% on the vehicle: ${vehicle}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::TRACKER_BATTERY_PERCENTAGE_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Internal Battery Voltage Alerts',
                'body' => 'Internal battery voltage percentage to tracker is ${battery_percentage}% on the vehicle: ${vehicle}. ${comment}'
            ],
        ],
        [
            'name' => Template::TRACKER_BATTERY_PERCENTAGE_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Internal Battery Voltage',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Internal battery voltage percentage to tracker is ${battery_percentage}% in the vehicle ${vehicle}.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - Internal battery voltage percentage in the vehicle ${reg_no_with_model} with driver ${driver} is ${battery_percentage}%, limit is set to ${deviceBatteryPercentage}</p>'
                    . '<p>${vehicle_url}</p>'
                    . '<br/><p>${comment}</p>'
            ],
        ],
        [
            'name' => Template::STRIPE_INTEGRATION_ERROR_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [],
        ],
        [
            'name' => Template::STRIPE_INTEGRATION_ERROR_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [],
        ],
        [
            'name' => Template::STRIPE_INTEGRATION_ERROR_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [],
        ],
        [
            'name' => Template::STRIPE_INTEGRATION_ERROR_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [],
        ],
        [
            'name' => Template::STRIPE_PAYMENT_FAILED_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [],
        ],
        [
            'name' => Template::STRIPE_PAYMENT_FAILED_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [],
        ],
        [
            'name' => Template::STRIPE_PAYMENT_FAILED_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [],
        ],
        [
            'name' => Template::STRIPE_PAYMENT_FAILED_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [],
        ],
        [
            'name' => Template::STRIPE_PAYMENT_SUCCESSFUL_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [],
        ],
        [
            'name' => Template::STRIPE_PAYMENT_SUCCESSFUL_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [],
        ],
        [
            'name' => Template::STRIPE_PAYMENT_SUCCESSFUL_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [],
        ],
        [
            'name' => Template::STRIPE_PAYMENT_SUCCESSFUL_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [],
        ],
        [
            'name' => Template::XERO_INTEGRATION_ERROR_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [],
        ],
        [
            'name' => Template::XERO_INTEGRATION_ERROR_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [],
        ],
        [
            'name' => Template::XERO_INTEGRATION_ERROR_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [],
        ],
        [
            'name' => Template::XERO_INTEGRATION_ERROR_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [],
        ],
        [
            'name' => Template::XERO_INVOICE_CREATION_ERROR_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [],
        ],
        [
            'name' => Template::XERO_INVOICE_CREATION_ERROR_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [],
        ],
        [
            'name' => Template::XERO_INVOICE_CREATION_ERROR_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [],
        ],
        [
            'name' => Template::XERO_INVOICE_CREATION_ERROR_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [],
        ],
        [
            'name' => Template::XERO_INVOICE_CREATED_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [],
        ],
        [
            'name' => Template::XERO_INVOICE_CREATED_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [],
        ],
        [
            'name' => Template::XERO_INVOICE_CREATED_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [],
        ],
        [
            'name' => Template::XERO_INVOICE_CREATED_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [],
        ],
        [
            'name' => Template::XERO_PAYMENT_CREATION_ERROR_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [],
        ],
        [
            'name' => Template::XERO_PAYMENT_CREATION_ERROR_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [],
        ],
        [
            'name' => Template::XERO_PAYMENT_CREATION_ERROR_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [],
        ],
        [
            'name' => Template::XERO_PAYMENT_CREATION_ERROR_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [],
        ],
        [
            'name' => Template::XERO_PAYMENT_CREATED_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [],
        ],
        [
            'name' => Template::XERO_PAYMENT_CREATED_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [],
        ],
        [
            'name' => Template::XERO_PAYMENT_CREATED_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [],
        ],
        [
            'name' => Template::XERO_PAYMENT_CREATED_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [],
        ],
        [
            'name' => Template::INVOICE_CREATED_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [],
        ],
        [
            'name' => Template::INVOICE_CREATED_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [],
        ],
        [
            'name' => Template::INVOICE_CREATED_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [],
        ],
        [
            'name' => Template::INVOICE_CREATED_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [ ],
        ],
        [
            'name' => Template::PAYMENT_FAILED_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [],
        ],
        [
            'name' => Template::PAYMENT_FAILED_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [],
        ],
        [
            'name' => Template::PAYMENT_FAILED_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [],
        ],
        [
            'name' => Template::PAYMENT_FAILED_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [],
        ],
        [
            'name' => Template::PAYMENT_SUCCESSFUL_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [],
        ],
        [
            'name' => Template::PAYMENT_SUCCESSFUL_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [],
        ],
        [
            'name' => Template::PAYMENT_SUCCESSFUL_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [],
        ],
        [
            'name' => Template::PAYMENT_SUCCESSFUL_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [],
        ],
        [
            'name' => Template::INVOICE_OVERDUE_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [],
        ],
        [
            'name' => Template::INVOICE_OVERDUE_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [],
        ],
        [
            'name' => Template::INVOICE_OVERDUE_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [],
        ],
        [
            'name' => Template::INVOICE_OVERDUE_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [],
        ],
        [
            'name' => Template::INVOICE_OVERDUE_BLOCKED_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [],
        ],
        [
            'name' => Template::INVOICE_OVERDUE_BLOCKED_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [],
        ],
        [
            'name' => Template::INVOICE_OVERDUE_BLOCKED_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [],
        ],
        [
            'name' => Template::INVOICE_OVERDUE_BLOCKED_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [],
        ],
        [
            'name' => Template::INVOICE_OVERDUE_PARTIALLY_BLOCKED_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [],
        ],
        [
            'name' => Template::INVOICE_OVERDUE_PARTIALLY_BLOCKED_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [],
        ],
        [
            'name' => Template::INVOICE_OVERDUE_PARTIALLY_BLOCKED_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [],
        ],
        [
            'name' => Template::INVOICE_OVERDUE_PARTIALLY_BLOCKED_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [],
        ],
        [
            'name' => Template::DEVICE_CONTRACT_EXPIRED_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [],
        ],
        [
            'name' => Template::DEVICE_CONTRACT_EXPIRED_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [],
        ],
        [
            'name' => Template::DEVICE_CONTRACT_EXPIRED_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [],
        ],
        [
            'name' => Template::DEVICE_CONTRACT_EXPIRED_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [],
        ],
        [
            'name' => Template::DEVICE_REPLACED_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Device replaced',
                'body' => 'Device replaced:  ${device_imei}. ${comment}'
            ],
        ],
        [
            'name' => Template::DEVICE_REPLACED_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [
                'body' => 'Device replaced:  ${device_imei}. ${event_time} ${comment}'
            ],
        ],
        [
            'name' => Template::DEVICE_REPLACED_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [
                'subject' => 'Device replaced',
                'body' => 'Device replaced:  ${device_imei}. ${comment}'
            ],
        ],
        [
            'name' => Template::DEVICE_REPLACED_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [
                'subject' => self::SUBJECT_PREFIX . 'Device replaced',
                'body' => '<h3>New notification from the ${from_company} system:</h3>'
                    . '<p>Device ${device_imei} was replaced.</p>'
                    . '<br/><p><b>Detailed information:</b></p>'
                    . '<p>${event_time} - ${triggered_by} have replaced device ${device_imei}.</p>'
                    . '<p>${data_url}</p>'
                    . '<br/><p>${comment}</p>',
            ],
        ],
        [
            'name' => Template::EXCEEDING_SPEED_LIMIT_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [],
        ],
        [
            'name' => Template::EXCEEDING_SPEED_LIMIT_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [],
        ],
        [
            'name' => Template::EXCEEDING_SPEED_LIMIT_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [],
        ],
        [
            'name' => Template::EXCEEDING_SPEED_LIMIT_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [],
        ],
        [
            'name' => Template::INTEGRATION_ENABLED_USER_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [],
        ],
        [
            'name' => Template::INTEGRATION_ENABLED_USER_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [],
        ],
        [
            'name' => Template::INTEGRATION_ENABLED_USER_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [],
        ],
        [
            'name' => Template::INTEGRATION_ENABLED_USER_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [],
        ],
        [
            'name' => Template::ACCESS_LEVEL_CHANGED_WEB,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_WEB_APP,
            'body' => [],
        ],
        [
            'name' => Template::ACCESS_LEVEL_CHANGED_SMS,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_SMS,
            'body' => [],
        ],
        [
            'name' => Template::ACCESS_LEVEL_CHANGED_MOBILE,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_MOBILE_APP,
            'body' => [],
        ],
        [
            'name' => Template::ACCESS_LEVEL_CHANGED_EMAIL,
            'type' => Template::TYPE_DEFAULT,
            'transport' => Transport::TRANSPORT_EMAIL,
            'body' => [],
        ],
    ];

    public function getDependencies(): array
    {
        return [
            InitTransportsFixture::class,
        ];
    }

    /**
     * @return array
     */
    public static function getGroups(): array
    {
        return [FixturesTypes::GLOBAL];
    }

    public function load(ObjectManager $manager): void
    {
        /** @var EntityManager $manager */
        $manager = $this->prepareEntityManager($manager);

        foreach (self::DATA as $templateData) {
            /** @var Transport $refTransport */
            $refTransport = $this->getReference($templateData['transport']);

            /** @var Template $template */
            $template = $manager->getRepository(Template::class)
                ->findOneBy(
                    [
                        'name' => $templateData['name'],
                        'type' => $templateData['type'],
                        'transport' => $refTransport,
                    ]
                );
            if (!$template) {
                $template = new Template();
                $template
                    ->setName($templateData['name'])
                    ->setType($templateData['type'])
                    ->setTransport($refTransport)
                    ->setBody($templateData['body']);

                $manager->persist($template);
            } else {
                $template->setBody($templateData['body']);
            }

            $this->setReference($templateData["name"], $template);
        }

        $manager->flush();
    }
}
