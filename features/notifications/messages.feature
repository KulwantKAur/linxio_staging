Feature: Notification messages

  Background:
    Given I signed in as "super_admin" team "admin"

  Scenario:  I want check mark as read and count unread messages
    And the queue associated to notification.events events producer is empty
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
    And I want fill "teamId" field with 1
    And I want fill "eventId" field with "event(USER_DELETED, user)"
    And I want fill "scope.subtype" field with "any"
    And I want fill "scope.value" field with null
    And I want fill "recipients.0.type" field with 'users_list'
    And I want fill "recipients.0.value.0" field with 'user(linxio-dev@ocsico.com)'
    And I want fill "transports.0" field with 'inApp'
    And I want fill "eventTrackingTimeFrom" field with '00:00'
    And I want fill "eventTrackingTimeUntil" field with '23:59'
    And I want fill "eventTrackingDays.0" field with "monday"
    Then I want create notification
    Then response code is 200
    Then I want get last notification
    And I see field "listenerTeamId" filled with 1
    And I see field "ownerTeamId" filled with 1
    And I want clean filled data
    Then I want delete admin team user by id with email "client_manager_user@gmail.test"
    And I want fill "transportType" field with "web_app"
    And I want fill "recipient" field with 'user(linxio-dev@ocsico.com)'
    And I want fill "body.subject" field with "test subject 1"
    And I want fill "body.body" field with "test text 1"
    And I want fill "status" field with "delivery"
    And I want fill "sendingTime" field with "2019-01-01 01:01:01"
    And I want fill "processingTime" field with "2019-01-01 01:01:01"
    And I want fill "occurrenceTime" field with "2019-01-01 01:01:01"
    And  I want fill last notification
    And  I want fill event log for notification 'USER_DELETED' 'user'
    Then I want create notification message
    And I want fill "transportType" field with "web_app"
    And I want fill "recipient" field with 'user(linxio-dev@ocsico.com)'
    And I want fill "body.subject" field with "test subject 2"
    And I want fill "body.body" field with "test text 2"
    And I want fill "status" field with "delivery"
    And I want fill "sendingTime" field with "2019-01-02 01:01:01"
    And I want fill "processingTime" field with "2019-01-02 01:01:01"
    And I want fill "occurrenceTime" field with "2019-01-02 01:01:01"
    And  I want fill last notification
    And  I want fill event log for notification 'USER_DELETED' 'user'
    Then I want create notification message
    And I want get count unread notification messages
    And I see field "count" filled with 0
    And I want clean filled data
    Then I signed with email "linxio-dev@ocsico.com"
    And I want get count unread notification messages
    And I see field "count" filled with 2
    Then I want mark read notification message with id 1
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/recipient" filled with "string(1)"
    And I see field "data/0/subject" filled with "test subject 2"
    And I see field "data/0/message" filled with "test text 2"
    And I see field "data/0/sendingTime" filled with "2019-01-02T01:01:01+00:00"
    And I see field "data/0/status" filled with "delivery"
    And I see field "data/0/event/eventSourceType" filled with "user"
    And I see field "data/0/event/entityTeam/type" filled with "admin"
    And I see field "data/0/event/entityTeam/clientId" filled with null
    And I see field "data/0/event/entityTeam/clientName" filled with null
    And I see field "data/0/isRead" filled with false
    And I see field "data/1/transportType" filled with "web_app"
    And I see field "data/1/recipient" filled with "string(1)"
    And I see field "data/1/subject" filled with "test subject 1"
    And I see field "data/1/message" filled with "test text 1"
    And I see field "data/1/sendingTime" filled with "2019-01-01T01:01:01+00:00"
    And I see field "data/1/status" filled with "delivery"
    And I see field "data/1/event/eventSourceType" filled with "user"
    And I see field "data/1/event/entityTeam/type" filled with "admin"
    And I see field "data/1/event/entityTeam/clientId" filled with null
    And I see field "data/1/event/entityTeam/clientName" filled with null
    And I see field "data/1/isRead" filled with true

  Scenario: I want to check generate notification message with templates
    And the queue associated to notification.events events producer is empty
    Then I want fill "title" field with "Test#1"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "teamId" field with 1
    And I want fill "eventId" field with "event(ADMIN_USER_CREATED, user)"
    And I want fill "scope.subtype" field with "any"
    Then I want fill "scope.value.0" field with null
    And I want fill "recipients.0.type" field with 'users_list'
    And I want fill "recipients.0.value.0" field with 'user(linxio-dev@ocsico.com)'
    And I want fill "transports.1" field with 'inApp'
    And I want fill "eventTrackingTimeFrom" field with '00:00'
    And I want fill "eventTrackingTimeUntil" field with '23:59'
    And I want fill "eventTrackingDays.0" field with "monday"
    And I want fill "eventTrackingDays.1" field with "tuesday"
    And I want fill "eventTrackingDays.2" field with "wednesday"
    And I want fill "eventTrackingDays.3" field with "thursday"
    And I want fill "eventTrackingDays.4" field with "friday"
    And I want fill "eventTrackingDays.5" field with "saturday"
    And I want fill "eventTrackingDays.6" field with "sunday"
    And I want fill "teamId" field with 1
    Then I want create notification
    Then response code is 200
    Then I want get last notification
    And I see field "listenerTeamId" filled with 1
    And I see field "ownerTeamId" filled with 1
    And I want clean filled data
    And I want fill "email" field with "client_manager_user@gmail.test"
    And I want fill "name" field with "Alex"
    And I want fill "phone" field with "+6(4)654654"
    And I want fill "roleId" field with "3"
    And I want fill "allTeamsPermissions" field with "false"
    Then I want register client manager user and remember id
    And I see field "email" filled with "client_manager_user@gmail.test"
    And I see field "roleId" filled with 3
    And I want clean filled data
    And I want fill "email" field with "admin_team_user@gmail.test"
    And I want fill "name" field with "Alex"
    And I want fill "phone" field with "+6(4)654654"
    And I want fill "roleId" field with "2"
    Then I want register client manager user and remember id
    And I see field "email" filled with "admin_team_user@gmail.test"
    And I see field "roleId" filled with 2
    And I want clean filled data
    Then I want fill "title" field with "Test#1"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "teamId" field with 1
    And I want fill "eventId" field with "event(USER_DELETED, user)"
    And I want fill "scope.subtype" field with "any"
    Then I want fill "scope.value.0" field with null
    And I want fill "recipients.0.type" field with 'users_list'
    And I want fill "recipients.0.value.0" field with 'user(linxio-dev@ocsico.com)'
    And I want fill "transports.1" field with 'inApp'
    And I want fill "transports.2" field with 'sms'
    And I want fill "eventTrackingTimeFrom" field with '00:00'
    And I want fill "eventTrackingTimeUntil" field with '23:59'
    And I want fill "eventTrackingDays.0" field with "monday"
    And I want fill "eventTrackingDays.1" field with "tuesday"
    And I want fill "eventTrackingDays.2" field with "wednesday"
    And I want fill "eventTrackingDays.3" field with "thursday"
    And I want fill "eventTrackingDays.4" field with "friday"
    And I want fill "eventTrackingDays.5" field with "saturday"
    And I want fill "eventTrackingDays.6" field with "sunday"
    And I want fill "comment" field with "Here text comment"
    And I want fill "teamId" field with 1
    Then I want create notification
    Then response code is 200
    Then I want get last notification
    And I see field "listenerTeamId" filled with 1
    And I see field "ownerTeamId" filled with 1
    And I want clean filled data
    Then I want delete admin team user by id with email "client_manager_user@gmail.test"
    And I want send messages in queue notification_events
    Given Elastica populate
    Then I want get event log
    Then response code is 200
    And I see field "data/1/eventName" filled with "ADMIN_USER_CREATED"
    And I see field "data/2/eventName" filled with "USER_DELETED"
    And I want clean filled data
    Then I signed with email "linxio-dev@ocsico.com"
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/2/transportType" filled with "web_app"
    And I see field "data/2/recipient" filled with "string(1)"
    And I see field "data/2/subject" filled with "Alerts - New user created"
    And I see field "data/2/message" filled with "User created: client_manager_user@gmail.test (Alex). "
    And I see field "data/2/status" filled with "pending"
    And I see field "data/2/event/eventSourceType" filled with "user"
    And I see field "data/2/event/entityTeam/type" filled with "admin"
    And I see field "data/2/event/entityTeam/clientId" filled with null
    And I see field "data/2/event/entityTeam/clientName" filled with null
    And I see field "data/1/transportType" filled with "web_app"
    And I see field "data/1/recipient" filled with "string(1)"
    And I see field "data/1/subject" filled with "Alerts - New user created"
    And I see field "data/1/message" filled with "User created: admin_team_user@gmail.test (Alex). "
    And I see field "data/1/status" filled with "pending"
    And I see field "data/1/event/eventSourceType" filled with "user"
    And I see field "data/1/event/entityTeam/type" filled with "admin"
    And I see field "data/1/event/entityTeam/clientId" filled with null
    And I see field "data/1/event/entityTeam/clientName" filled with null
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/recipient" filled with "string(1)"
    And I see field "data/0/subject" filled with "Alerts - User deleted"
    And I see field "data/0/message" filled with "User deleted: client_manager_user@gmail.test (Alex). Here text comment"
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "user"
    And I see field "data/0/event/entityTeam/type" filled with "admin"
    And I see field "data/0/event/entityTeam/clientId" filled with null
    And I see field "data/0/event/entityTeam/clientName" filled with null
    And I want clean filled data
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "sms"
    Then I want get generated notification messages
    And I see field "data/0/transportType" filled with "sms"
    And I see field "data/0/recipient" filled with "custom(+0452096181)"
    And I see field "data/0/subject" filled with "[Linxio] Notification Message"
    And I see field "data/0/message" filled with "User deleted: client_manager_user@gmail.test. Here text comment"
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "user"
    And I see field "data/0/event/entityTeam/type" filled with "admin"
    And I see field "data/0/event/entityTeam/clientId" filled with null
    And I see field "data/0/event/entityTeam/clientName" filled with null
    And I see field "data/1/transportType" filled with "sms"
    And I see field "data/1/recipient" filled with "custom(+0452096181)"
    And I see field "data/1/subject" filled with "[Linxio] Notification Message"
    And I see field "data/1/message" filled with "User created: client_manager_user@gmail.test. "
    And I see field "data/1/status" filled with "pending"
    And I see field "data/1/event/eventSourceType" filled with "user"
    And I see field "data/1/event/entityTeam/type" filled with "admin"
    And I see field "data/1/event/entityTeam/clientId" filled with null
    And I see field "data/1/event/entityTeam/clientName" filled with null
    And I want clean filled data
    Then Notifications send
    And I want send messages in queue notification_webapp
    And I want fill "status" field with "delivery"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/2/transportType" filled with "web_app"
    And I see field "data/2/recipient" filled with "string(1)"
    And I see field "data/2/subject" filled with "Alerts - User deleted"
    And I see field "data/2/message" filled with "User deleted: client_manager_user@gmail.test (Alex)"
    And I see field "data/2/status" filled with "delivery"
    And I see field "data/2/event/eventSourceType" filled with "user"
    And I see field "data/2/event/entityTeam/type" filled with "admin"
    And I see field "data/2/event/entityTeam/clientId" filled with null
    And I see field "data/2/event/entityTeam/clientName" filled with null
    Then I signed in as "admin" team "client"
    And I want get generated notification messages
    And response code is 200
    And I see field "total" filled with 0

  Scenario: I want to check user group message
    And the queue associated to notification.events events producer is empty
    Given I signed in as "admin" team "client" and teamId 2
    And I want fill "name" field with "test user group"
    And I want add current user to userIds
    And I want create user group
    And response code is 200
    And I see field "name" filled with "test user group"
    And I want clean filled data
    Then I want fill "title" field with "Test#1"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "eventId" field with "event(USER_DELETED, user)"
    And I want fill "scope.subtype" field with "any"
    Then I want fill "scope.value.0" field with null
    And I want fill "recipients.0.type" field with 'user_groups_list'
    And I want fill "recipients.0.value.0" field with 'user_group(test user group)'
    And I want fill "transports.0" field with 'inApp'
    And I want fill "eventTrackingTimeFrom" field with '00:00'
    And I want fill "eventTrackingTimeUntil" field with '23:59'
    And I want fill "eventTrackingDays.0" field with "monday"
    And I want fill "eventTrackingDays.1" field with "tuesday"
    And I want fill "eventTrackingDays.2" field with "wednesday"
    And I want fill "eventTrackingDays.3" field with "thursday"
    And I want fill "eventTrackingDays.4" field with "friday"
    And I want fill "eventTrackingDays.5" field with "saturday"
    And I want fill "eventTrackingDays.6" field with "sunday"
    And I want fill "comment" field with "Here text comment"
    And I want fill "teamId" field with 2
    Then I want create notification
    Then response code is 200
    And I see field "transports/0" filled with "inApp"
    Then I want get last notification
    And I see field "listenerTeamId" filled with 2
    And I see field "ownerTeamId" filled with 2
    And I want clean filled data
    Then I want delete client user with client name "client-name-0" and user email "client-user-0@ocsico.com"
    And I want send messages in queue notification_events
    And response code is 204
    Given Elastica populate
    Then I want get event log
    Then response code is 200
    And I see field "data/0/eventName" filled with "USER_DELETED"
    And I want clean filled data
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/subject" filled with "Alerts - User deleted"
    And I see field "data/0/message" filled with "User deleted: client-user-0@ocsico.com (client-user-name-0 client-user-name-0). Here text comment"
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "user"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"

  Scenario: I want to test acknowledge
    Given I signed in as "admin" team "client" and teamId 2
    And I want fill "name" field with "test user group"
    And I want add current user to userIds
    And I want create user group
    And response code is 200
    And I see field "name" filled with "test user group"
    And I want clean filled data
    Then I want fill "title" field with "Test#1"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "eventId" field with "event(USER_DELETED, user)"
    And I want fill "scope.subtype" field with "any"
    Then I want fill "scope.value.0" field with null
    And I want fill "recipients.0.type" field with 'user_groups_list'
    And I want fill "recipients.0.value.0" field with 'user_group(test user group)'
    And I want fill "transports.0" field with 'inApp'
    And I want fill "eventTrackingTimeFrom" field with '00:00'
    And I want fill "eventTrackingTimeUntil" field with '23:59'
    And I want fill "eventTrackingDays.0" field with "monday"
    And I want fill "eventTrackingDays.1" field with "tuesday"
    And I want fill "eventTrackingDays.2" field with "wednesday"
    And I want fill "eventTrackingDays.3" field with "thursday"
    And I want fill "eventTrackingDays.4" field with "friday"
    And I want fill "eventTrackingDays.5" field with "saturday"
    And I want fill "eventTrackingDays.6" field with "sunday"
    And I want fill "comment" field with "Here text comment"
    And I want fill "teamId" field with 2
    And I want fill "hasAcknowledge" field with true
    Then I want create notification
    Then response code is 200
    And I see field "transports/0" filled with "inApp"
    And I see field "hasAcknowledge" filled with true
    Then I want get last notification
    And I see field "listenerTeamId" filled with 2
    And I see field "ownerTeamId" filled with 2
    And I want clean filled data
    Then I want delete client user with client name "client-name-0" and user email "client-user-0@ocsico.com"
    And I want send messages in queue notification_events
    And response code is 204
    Given Elastica populate
    Then I want get event log
    Then response code is 200
    And I see field "data/0/eventName" filled with "USER_DELETED"
    And I see field "data/0/notificationGenerated" filled with "yes"
    And I want clean filled data
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
#    And I see field "data/0/id" filled with 2
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/subject" filled with "Alerts - User deleted"
    And I see field "data/0/acknowledge/status" filled with "open"
    And I want fill "status" field with "actioned"
    And I want fill "comment" field with "testing comment"
    Then I want update ntf message acknowledge with id 2
    And response code is 200
    And I see field "acknowledge/status" filled with "actioned"
    And I see field "acknowledge/comment" filled with "testing comment"