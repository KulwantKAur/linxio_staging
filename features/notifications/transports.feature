Feature: Transports

  Scenario: I want test email transport
    When I want fill "transportType" field with "email"
    And I want fill "recipient" field with "test.linxio@ocsico.com"
    And I want fill "body.subject" field with "test subject"
    And I want fill "body.body" field with "test text"
    And I want fill "status" field with "delivery"
    And I want fill "sendingTime" field with "2019-01-01 01:01:01"
    And I want fill "processingTime" field with "2019-01-01 01:01:01"
    And I want fill "occurrenceTime" field with "2019-01-01 01:01:01"
    Then I want create notification message
    Then I want set mock Email Consumer "test.linxio@ocsico.com" "test subject" "test text"
    Then I want call email consumer

  Scenario: I want test mobileapp transport
    And the queue associated to notification.events events producer is empty
    And the queue associated to notification.mobileapp mobileapp producer is empty
    Given I signed with email "linxio-dev@ocsico.com"
    And I want fill "deviceId" field with "test device id"
    And I want fill "deviceToken" field with "test token 01"
    When I want set mobile device token
    Then response code is 200
    And I see field "deviceToken" filled with "test token 01"
    And I want clean filled data
    Given I signed in as "super_admin" team "admin"
    And I want fill "email" field with "client_manager_user@gmail.test"
    And I want fill "name" field with "Alex"
    And I want fill "phone" field with "+6(4)654654"
    And I want fill "roleId" field with "3"
    And I want fill "status" field with "active"
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
    And I see field "data/1/eventName" filled with "USER_DELETED"
    And I want clean filled data
    Then Notifications send
    And I want send messages in queue notification mobile_app
    Then I signed with email "linxio-dev@ocsico.com"
    And I want fill "status" field with "delivery"
    And I want fill "transport" field with "mobile_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/1/transportType" filled with "mobile_app"
    And I see field "data/1/recipient" filled with "string(1)"
    And I see field "data/1/subject" filled with "User deleted"
    And I see field "data/1/message" filled with "User deleted: client_manager_user@gmail.test. Here text comment"
    And I see field "data/1/status" filled with "delivery"
    And I see field "data/1/event/userId"
    And I see field "data/1/event/eventSourceType" filled with "user"
    And I see field "data/1/event/entityTeam/type" filled with "admin"
    And I see field "data/1/event/entityTeam/clientId" filled with null
    And I see field "data/1/event/entityTeam/clientName" filled with null
