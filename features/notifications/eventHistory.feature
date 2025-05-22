Feature: Event history

  Scenario: I want check EventLogService
    Given I signed in as "super_admin" team "admin"
    And I want handle event log event 'USER_BLOCKED' 'client-user-0@ocsico.com'
    Given Elastica populate
    Then I want get event log
    Then response code is 200
    And I see field "data/0/id"
    And I see field "data/0/eventName" filled with "USER_BLOCKED"
    And I see field "data/0/importance" filled with "normal"
    And I see field "data/0/eventTeam" filled with "client-name-0"
    And I see field "data/0/triggeredBy" filled with "user"
    And I see field "data/0/triggeredDetails" filled with "system"
    And I see field "data/0/eventSourceType" filled with "user"
    And I see field "data/0/eventSource" filled with "client-user-0@ocsico.com"
    And I see field "data/0/teamId" filled with null
    And I see field "data/0/notificationsList"
    And I see field "data/0/formattedDate"
    Then I want add value to array key "fields" with "id"
    Then I want add value to array key "fields" with "eventName"
    Then I want add value to array key "fields" with "importance"
    Then I want add value to array key "fields" with "eventTeam"
    Then I want add value to array key "fields" with "triggeredBy"
    Then I want add value to array key "fields" with "triggeredDetails"
    Then I want add value to array key "fields" with "eventSourceType"
    Then I want add value to array key "fields" with "eventSource"
    Then I want export event log list
    And I see csv item number 0 field "ID" is not empty
    And I see csv item number 0 field "EVENT TYPE" filled with "USER_BLOCKED"
    And I see csv item number 0 field "IMPORTANCE" filled with "normal"
    And I see csv item number 0 field "TEAM" filled with "client-name-0"
    And I see csv item number 0 field "TRIGGERED BY (TYPE)" filled with "user"
    And I see csv item number 0 field "TRIGGERED BY (INSTANCE)" filled with "system"
    And I see csv item number 0 field "EVENT SOURCE TYPE" filled with "user"
    And I see csv item number 0 field "EVENT SOURCE" filled with "client-user-0@ocsico.com"
    And I see csv item number 0 field "NOTIFICATION GENERATED?" filled with "no"
    And response code is 200
    Then I want clean filled data