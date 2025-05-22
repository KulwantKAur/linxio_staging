Feature: Notifications

  Scenario: I want get transports
    Given I signed in as "super_admin" team "admin"
    Then I want get transports
    Then response code is 200
    And I see field "0/name" filled with "Sms"
    And I see field "0/alias" filled with "sms"
    And I see field "0/active" filled with true
    And I see field "1/name" filled with "E-Mail"
    And I see field "1/alias" filled with "email"
    And I see field "1/active" filled with true
    And I see field "2/name" filled with "In App"
    And I see field "2/alias" filled with "inApp"
    And I see field "2/active" filled with true
    Then I want set transport setting 'email' with 0
    Then I want set transport setting 'inApp' with 0
    Then I want get transports
    Then response code is 200
    And I see field "0/active" filled with true
    And I see field "1/active" filled with false
    And I see field "2/active" filled with false

  Scenario: I want get event types
    Given I signed in as "super_admin" team "admin"
    Then I want get event types
    Then response code is 200
    And I see field "0/name" filled with "VEHICLE_CREATED"
    And I see field "0/scopes/0/name" filled with "any"
    And I see field "0/scopes/0/subType" filled with "any"
    And I see field "0/scopes/1/name" filled with "vehicle"
    And I see field "0/scopes/1/subType" filled with "vehicle"
    And I see field "0/scopes/2/name" filled with "depot"
    And I see field "0/scopes/2/subType" filled with "depot"
    And I see field "0/scopes/3/name" filled with "group"
    And I see field "0/scopes/3/subType" filled with "group"
    And I see field "0/scopes/4/name" filled with "team"
    And I see field "0/scopes/4/subType" filled with "team"
    And I see field "15/name" filled with "USER_CREATED"
    And I see field "15/scopes/0/name" filled with "any"
    And I see field "15/scopes/0/subType" filled with "any"
    And I see field "15/scopes/1/name" filled with "user"
    And I see field "15/scopes/1/subType" filled with "user"
    And I see field "15/scopes/2/name" filled with "role"
    And I see field "15/scopes/2/subType" filled with "role"
    And I see field "15/scopes/3/name" filled with "team"
    And I see field "15/scopes/3/subType" filled with "team"
    And I see field "39/name" filled with "VEHICLE_GEOFENCE_LEAVE"
    And I see field "39/scopes/0/name" filled with "any"
    And I see field "39/scopes/0/subType" filled with "any"
    And I see field "39/scopes/1/name" filled with "vehicle"
    And I see field "39/scopes/1/subType" filled with "vehicle"
    And I see field "39/scopes/2/name" filled with "depot"
    And I see field "39/scopes/2/subType" filled with "depot"
    And I see field "39/scopes/3/name" filled with "group"
    And I see field "39/scopes/3/subType" filled with "group"
    And I see field "39/additionalScopes/0/name/" filled with "any"
    And I see field "39/additionalScopes/0/subType/" filled with "any"
    And I see field "39/additionalScopes/1/name/" filled with "area"
    And I see field "39/additionalScopes/1/subType/" filled with "area"
    And I see field "39/additionalScopes/2/name/" filled with "area_group"
    And I see field "39/additionalScopes/2/subType/" filled with "area_group"

  Scenario: I want get notifications
    Given Elastica populate
    Given I signed in as "super_admin" team "admin"
    Then I want get notifications
    Then response code is 200
    And I see field "data/0/id"
    And I see field "data/0/title"
    And I see field "data/0/status" filled with "enabled"
    And I see field "data/0/importance"
    And I see field "data/0/event/id"
    And I see field "data/0/event/name" filled with "USER_CREATED"
    And I see field "data/0/scope/subtype"
    And I see field "data/0/scope/value"
    And I see field "data/0/recipients/0/type" filled with "role"
    And I see field "data/0/recipients/0/value/0/team" filled with "admin"
    And I see field "data/0/recipients/0/value/0/displayName" filled with "SuperAdmin"
    And I see field "data/0/transports/0" filled with "sms"
    And I do not see field "0/transports/1"
    And I see field "data/0/ownerTeamId"
    And I see field "data/0/listenerTeamId"
    And I see field "data/0/createdAt"
    And I see field "data/0/createdById"
    And I see field "data/0/updatedById"
    And I see field "data/1/id"
    And I see field "data/1/title"
    And I see field "data/1/status" filled with "enabled"
    And I see field "data/1/importance"
    And I see field "data/1/event/id"
    And I see field "data/1/event/name" filled with "USER_BLOCKED"
    And I see field "data/1/scope/subtype"
    And I see field "data/1/scope/value"
    And I see field "data/1/recipients/0/type" filled with "role"
    And I see field "data/1/recipients/0/value/0/team" filled with "admin"
    And I see field "data/1/recipients/0/value/0/displayName" filled with "SuperAdmin"
    And I see field "data/1/ownerTeamId"
    And I see field "data/1/listenerTeamId"
    And I see field "data/1/createdAt"
    And I see field "data/1/createdById"
    And I see field "data/1/updatedById"
    And I do not see field "2"
    Then I want fill "fields.0" field with "title"
    And I want get notifications
    Then response code is 200
    And I see field "data/0/id"
    And I see field "data/0/title"
    And I do not see field 'data/0/event'
    And I do not see field 'data/0/scope'
    And I do not see field 'data/0/recipients'
    And I do not see field 'data/0/transports'
    And I see field "data/1/id"
    And I see field "data/1/title"
    And I do not see field 'data/1/event'
    And I do not see field 'data/1/scope'
    And I do not see field 'data/1/recipients'
    And I do not see field 'data/1/transports'
    Then I want clean filled data
    Then I want fill "transportType" field with "inApp"
    And I want get notifications
    Then response code is 200
    And I see field "total" filled with 3
    And I see field "data/0/id"
    And I see field "data/0/transports/2" filled with "inApp"
    And I see field "data/1/id"
    And I see field "data/1/transports/0" filled with "inApp"
    And I see field "data/2/id"
    And I see field "data/2/transports/0" filled with "inApp"
    Then I want clean filled data
    Then I want fill "importance" field with "business_hours"
    And I want get notifications
    Then response code is 200
    And I see field "total" filled with 0
    Then I want clean filled data
    Then I want fill "importance" field with "immediately"
    And I want get notifications
    Then response code is 200
    And I see field "total" filled with 16

  Scenario: I want create notification with invalid data
    Given I signed in as "super_admin" team "admin"
    Then I want fill "title" field with "Test#1"
    Then I want create notification
    Then response code is 400
    And I want fill "status" field with "enabled"
    Then I want create notification
    Then response code is 400
    And I want fill "importance" field with "immediately"
    Then I want create notification
    Then response code is 400
    And I want fill "eventId" field with "event(VEHICLE_CREATED, user)"
    Then I want create notification
    Then response code is 400
    And I want fill "scope.subtype" field with "any"
    Then I want create notification
    Then response code is 400
    And I want fill "scope.value" field with null
    Then I want create notification
    Then response code is 400
    And I want fill "recipients.0.type" field with 'users_list'
    Then I want create notification
    Then response code is 400
    And I want fill "recipients.0.value.0" field with 'user(linxio-dev@ocsico.com)'
    Then I want create notification
    Then response code is 400
    And I want fill "eventTrackingTimeFrom" field with 'test'
    Then I want create notification
    Then response code is 400
    And I see field "errors/0/detail/eventTrackingTimeFrom/wrong_format" filled with "Wrong format"
    And I want fill "eventTrackingTimeUntil" field with 'test'
    Then I want create notification
    Then response code is 400
    And I see field "errors/0/detail/eventTrackingTimeUntil/wrong_format" filled with "Wrong format"
    And I want fill "eventTrackingDays.0" field with 'test'
    Then I want create notification
    Then response code is 400
    And I see field "errors/0/detail/eventTrackingDays.0/wrong_value" filled with "Wrong value"
    And I want fill "transports.0" field with 'sms'
    And I want fill "eventTrackingTimeFrom" field with '00:00'
    And I want fill "eventTrackingTimeUntil" field with '23:59'
    And I want fill "eventTrackingDays.0" field with 'monday'
    Then I want create notification
  Then response code is 200

  Scenario: I want create notification with invalid data recipients
    Given I signed in as "super_admin" team "admin"
    Then I want fill "title" field with "Test#1"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "eventId" field with "event(VEHICLE_CREATED, user)"
    And I want fill "scope.subtype" field with "any"
    And I want fill "scope.value" field with null
    And I want fill "recipients.0.type" field with 'users_list'
    And I want fill "recipients.0.value.0" field with 'user(client-user-1@ocsico.com)'
    And I want fill "transports.0" field with 'sms'
    And I want create notification
    And response code is 400
    And I see field "errors/0/detail/recipients.0.value/wrong_value" filled with "Wrong value"
    Then I want fill "recipients.0.type" field with 'role'
    And I want fill "recipients.0.value.0" field with 'role(client, Manager)'
    And I want create notification
    And response code is 400
    Then I see field "errors/0/detail/recipients.0.value/wrong_value" filled with "Wrong value"
    And I want fill "recipients.0.value.invalid.invalid" field with 'role(client, Manager)'
    And I want create notification
    And response code is 400
    And I see field "errors/0/detail/recipients.0.value/wrong_value" filled with "Wrong value"

  Scenario: I want create notification
    Given I signed in as "super_admin" team "admin"
    Then I want fill "title" field with "Test#1"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "eventId" field with "event(VEHICLE_CREATED, user)"
    And I want fill "scope.subtype" field with "any"
    And I want fill "scope.value" field with null
    And I want fill "recipients.0.type" field with 'users_list'
    And I want fill "recipients.0.value.0" field with 'user(linxio-dev@ocsico.com)'
    And I want fill "recipients.1.type" field with 'role'
    And I want fill "recipients.1.value.0" field with 'role(client_manager, admin)'
    And I want fill "transports.0" field with 'sms'
    And I want fill "transports.1" field with 'email'
    And I want fill "eventTrackingTimeFrom" field with '10:59'
    And I want fill "eventTrackingTimeUntil" field with '20:59'
    And I want fill "eventTrackingDays.0" field with "monday"
    And I want fill "eventTrackingDays.1" field with "tuesday"
    And I want fill "comment" field with "test comment"
    Then I want create notification
    Then response code is 200
    And I see field "id"
    And I see field "title" filled with "Test#1"
    And I see field "status" filled with "enabled"
    And I see field "importance" filled with "immediately"
    And I see field "eventId"
    And I see field "scope/subtype" filled with "any"
    And I see field "scope/value" filled with null
    And I see field "recipients/0/type" filled with "users_list"
    And I see field "recipients/0/value"
    And I see field "recipients/1/type" filled with "role"
    And I see field "recipients/1/value"
    And I see field "transports/0" filled with 'sms'
    And I see field "transports/1" filled with 'email'
    And I do not see field "transports/2"
    And I see field "ownerTeamId"
    And I see field "listenerTeamId"
    And I see field "createdAt"
    And I see field "createdById"
    And I see field "updatedById"
    And I see field "eventTrackingTimeFrom" filled with '10:59'
    And I see field "eventTrackingTimeUntil" filled with '20:59'
    And I see field "eventTrackingDays/0" filled with "monday"
    And I see field "eventTrackingDays/1" filled with "tuesday"
    And I see field "comment" filled with "test comment"

  Scenario: I want get notification
    Given I signed in as "super_admin" team "admin"
    Then I want fill "title" field with "Test#1"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "eventId" field with "event(VEHICLE_CREATED, user)"
    And I want fill "scope.subtype" field with "any"
    And I want fill "scope.value" field with null
    And I want fill "recipients.0.type" field with 'users_list'
    And I want fill "recipients.0.value.0" field with 'user(linxio-dev@ocsico.com)'
    And I want fill "recipients.1.type" field with 'role'
    And I want fill "recipients.1.value.0" field with 'role(client_manager, admin)'
    And I want fill "transports.0" field with 'sms'
    And I want fill "transports.1" field with 'email'
    And I want fill "eventTrackingTimeFrom" field with '00:00'
    And I want fill "eventTrackingTimeUntil" field with '23:59'
    And I want fill "eventTrackingDays.0" field with "monday"
    Then I want create notification
    Then response code is 200
    Then I want get last notification
    Then response code is 200
    And I see field "id"
    And I see field "title" filled with "Test#1"
    And I see field "status" filled with "enabled"
    And I see field "importance" filled with "immediately"
    And I see field "eventId"
    And I see field "scope/subtype" filled with "any"
    And I see field "scope/value" filled with null
    And I see field "recipients/0/type" filled with "users_list"
    And I see field "recipients/0/value"
    And I see field "recipients/1/type" filled with "role"
    And I see field "recipients/1/value"
    And I see field "transports/0" filled with 'sms'
    And I see field "transports/1" filled with 'email'
    And I see field "eventTrackingTimeFrom" filled with '00:00'
    And I see field "eventTrackingTimeUntil" filled with '23:59'
    And I see field "eventTrackingDays/0" filled with "monday"
    And I do not see field "transports/2"
    And I see field "ownerTeamId"
    And I see field "listenerTeamId"
    And I see field "createdAt"
    And I see field "createdById"
    And I see field "updatedById"

  Scenario: I want update notification
    Given I signed in as "super_admin" team "admin"
    Then I want fill "title" field with "Test#1"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "eventId" field with "event(VEHICLE_CREATED, user)"
    And I want fill "scope.subtype" field with "any"
    And I want fill "scope.value" field with null
    And I want fill "recipients.0.type" field with 'users_list'
    And I want fill "recipients.0.value.0" field with 'user(linxio-dev@ocsico.com)'
    And I want fill "recipients.1.type" field with 'role'
    And I want fill "recipients.1.value.0" field with 'role(client_manager, admin)'
    And I want fill "transports.0" field with 'sms'
    And I want fill "transports.1" field with 'email'
    And I want fill "eventTrackingTimeFrom" field with '00:00'
    And I want fill "eventTrackingTimeUntil" field with '23:59'
    And I want fill "eventTrackingDays.0" field with "monday"
    And I want fill "comment" field with "test comment #1"
    Then I want create notification
    Then response code is 200
    And I see field "title" filled with "Test#1"
    Then I want fill "title" field with "Test#2"
    Then I want update last notification
    Then response code is 200
    And I see field "title" filled with "Test#2"
    Then I want fill "status" field with "disabled"
    Then I want update last notification
    Then response code is 200
    And I see field "status" filled with "disabled"
    Then I want fill "importance" field with "business_hours"
    Then I want update last notification
    Then response code is 200
    And I see field "importance" filled with "business_hours"
    Then I want fill "eventId" field with "event(USER_CREATED, user)"
    Then I want update last notification
    Then response code is 200
    And I see field "eventId"
    And I want fill "scope" field with null
    Then I want fill "scope.subtype" field with "user"
    Then I want fill "scope.value.0" field with "user(linxio-admin@ocsico.com)"
    Then I want update last notification
    Then response code is 200
    And I see field "scope/subtype" filled with "user"
    And I see field "scope/value/0"
    And I do not see field "scope/value/1"
    And I want fill "recipients" field with null
    And I want fill "recipients.0.type" field with 'role'
    And I want fill "recipients.0.value.0" field with 'role(admin, admin)'
    Then I want update last notification
    Then response code is 200
    And I see field "recipients/0/type" filled with "role"
    And I see field "recipients/0/value/0"
    And I do not see field "recipients/1"
    And I want fill "transports" field with null
    And I want fill "transports.0" field with 'inApp'
    Then I want update last notification
    Then response code is 200
    And I see field "transports/0"
    And I do not see field "transports/1"
    And I want fill "eventTrackingTimeFrom" field with '01:30'
    And I want fill "eventTrackingTimeUntil" field with '07:30'
    And I want fill "eventTrackingDays.0" field with "monday"
    And I want fill "eventTrackingDays.1" field with "sunday"
    Then I want update last notification
    Then response code is 200
    And I see field "eventTrackingTimeFrom" filled with '01:30'
    And I see field "eventTrackingTimeUntil" filled with '07:30'
    And I see field "eventTrackingDays/0" filled with "monday"
    And I see field "eventTrackingDays/1" filled with "sunday"
    Then response code is 200
    Then I want fill "comment" field with "test comment #2"
    Then I want update last notification
    Then response code is 200
    And I see field "comment" filled with 'test comment #2'
    Then response code is 200
    And I see field "listenerTeamId" filled with 1
    And I see field "listenerTeam/type" filled with "admin"
    And I see field "listenerTeam/clientId" filled with null
    And I see field "listenerTeam/clientName" filled with null
    Then I want fill "teamId" field with 10
    Then I want update last notification
    Then response code is 200
    And I see field "listenerTeamId" filled with 10
    And I see field "listenerTeam/type" filled with "client"
    And I see field "listenerTeam/clientId" filled with 9
    And I see field "listenerTeam/clientName" filled with "client-name-8"
    Then response code is 200

  Scenario: I want delete notification
    Given I signed in as "super_admin" team "admin"
    Then I want fill "title" field with "Test#1"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "eventId" field with "event(VEHICLE_CREATED, user)"
    And I want fill "scope.subtype" field with "any"
    And I want fill "scope.value" field with null
    And I want fill "recipients.0.type" field with 'users_list'
    And I want fill "recipients.0.value.0" field with 'user(linxio-dev@ocsico.com)'
    And I want fill "recipients.1.type" field with 'role'
    And I want fill "recipients.1.value.0" field with 'role(client_manager, admin)'
    And I want fill "transports.0" field with 'sms'
    And I want fill "transports.1" field with 'email'
    And I want fill "eventTrackingTimeFrom" field with '00:00'
    And I want fill "eventTrackingTimeUntil" field with '23:59'
    And I want fill "eventTrackingDays.0" field with "monday"
    Then I want create notification
    Then response code is 200
    Then I want delete last notification
    Then I want get last notification
    Then response code is 400

  Scenario: I want checked sending notification only active user-recipient
    Given I signed in as "super_admin" team "admin"
    And I want fill "email" field with "client_manager_user@gmail.test"
    And I want fill "name" field with "Alex"
    And I want fill "phone" field with "+6(4)654654"
    And I want fill "roleId" field with "3"
    And I want fill "allTeamsPermissions" field with "false"
    Then I want register client manager user and remember id
    And I see field "email" filled with "client_manager_user@gmail.test"
    And I see field "roleId" filled with 3
    And I want clean filled data
    Then I want fill "title" field with "Test#1"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "eventId" field with "event(USER_DELETED, user)"
    And I want fill "scope.subtype" field with "any"
    And I want fill "scope.value" field with null
    And I want fill "recipients.0.type" field with 'users_list'
    And I want fill "recipients.0.value.0" field with 'user(linxio-dev@ocsico.com)'
    And I want fill "recipients.0.value.1" field with 'user(client_manager_user@gmail.test)'
    And I want fill "recipients.1.type" field with 'role'
    And I want fill "recipients.1.value.0" field with 'role(client_manager, admin)'
    And I want fill "transports.0" field with 'email'
    And I want fill "eventTrackingTimeFrom" field with '00:00'
    And I want fill "eventTrackingTimeUntil" field with '23:59'
    And I want fill "eventTrackingDays.0" field with "monday"
    Then I want create notification
    Then response code is 200
    And I want clean filled data
    Then I want delete admin team user by id with email "client_manager_user@gmail.test"
    Then I want fill "notificationId" field with "event(USER_DELETED, user)"
    Given Elastica populate
    Then I want get event log
    Then response code is 200
    And I see field "data/1/eventName" filled with "USER_DELETED"
    And I see field "data/1/importance" filled with "normal"
    And I see field "data/1/eventTeam" filled with "admin"
    And I see field "data/1/triggeredBy" filled with "user"
    And I see field "data/1/triggeredDetails" filled with "super_admin@user.com"
    And I see field "data/1/eventSourceType" filled with "user"
    And I see field "data/1/eventSource" filled with "client_manager_user@gmail.test"

  Scenario: I want check permissions
    Then I signed in as "driver" team "client"
    Then I want get notifications
    Then response code is 200
    Then I see field "total" filled with 0
    Then I want fill "title" field with "Test#1"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "eventId" field with "event(VEHICLE_CREATED, user)"
    And I want fill "scope.subtype" field with "any"
    And I want fill "scope.value" field with null
    And I want fill "recipients.0.type" field with 'users_list'
    And I want fill "recipients.0.value.0" field with 'user(linxio-dev@ocsico.com)'
    And I want fill "recipients.1.type" field with 'role'
    And I want fill "recipients.1.value.0" field with 'role(client_manager, admin)'
    And I want fill "transports.0" field with 'sms'
    And I want fill "transports.1" field with 'email'
    And I want fill "eventTrackingTimeFrom" field with '00:00'
    And I want fill "eventTrackingTimeUntil" field with '23:59'
    And I want fill "eventTrackingDays.0" field with "monday"
    Then I want create notification
    Then response code is 403
    Then I see field "errors/0/detail" filled with "Access Denied."
    Then I want delete last notification
    Then I want get last notification
    Then response code is 400

  Scenario: I want get list event tracking days
    Given I signed in as "super_admin" team "admin"
    Then I want get list event tracking days
    Then response code is 200
    And I see field "0" filled with "monday"
    And I see field "1" filled with "tuesday"
    And I see field "2" filled with "wednesday"
    And I see field "3" filled with "thursday"
    And I see field "4" filled with "friday"
    And I see field "5" filled with "saturday"
    And I see field "6" filled with "sunday"
