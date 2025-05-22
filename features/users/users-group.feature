Feature: Users

  Scenario: I want crud user group
    Given I signed in as "admin" team "client" and teamId 2
    And I want fill "name" field with "test user group"
    And I want add current user to userIds
    And I want create user group
    And response code is 200
    And I see field "name" filled with "test user group"
    And I see field "usersCount" filled with 1
    Then I want get user group list
    And I see field "data/0/name" filled with "test user group"
    And I see field "data/0/usersCount" filled with 1
    Then I want get saved user group by id
    And I see field "name" filled with "test user group"
    And I see field "usersCount" filled with 1
    Then I want fill "name" field with "test user group2"
    And I want fill key "userIds" with empty array
    And I want edit saved user group
    And I see field "name" filled with "test user group2"
    And I see field "usersCount" filled with 0
    Then I want delete saved user group
    And response code is 204
    Then I want get user group list
    And I see field "total" filled with 0


