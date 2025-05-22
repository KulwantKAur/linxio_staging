Feature: Fuel ignore list

  Background:
    Given I signed in as "super_admin" team "admin"

  Scenario: I want get fuel ignore list
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test fuel ignore #1"
    And I want to create fuel ignore and save id
    And I see field "name" filled with "test fuel ignore #1"
    Then I want fill "name" field with "test fuel ignore #2"
    And I want to create fuel ignore and save id
    And I see field "name" filled with "test fuel ignore #2"
    And I want clean filled data
    Given Elastica populate
    And I want fill "limit" field with 20
    Then I want get fuel ignore list
    And I see field "data/11/name" filled with "test fuel ignore #1"
    And I see field "data/12/name" filled with "test fuel ignore #2"
    And response code is 200

  Scenario: I want get fuel ignore list by id
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test fuel ignore"
    And I want to create fuel ignore and save id
    Then I want get fuel ignore by saved id
    And I see field "name" filled with "test fuel ignore"
    And response code is 200

  Scenario: I want check permissions
    And I want fill "email" field with "client_manager_user@gmail.test"
    And I want fill "name" field with "Alex"
    And I want fill "phone" field with "+6(4)654654"
    And I want fill "roleId" field with "3"
    And I want fill "allTeamsPermissions" field with "false"
    Then I want register client manager user and remember id
    And I see field "email" filled with "client_manager_user@gmail.test"
    Then I want clean filled data
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test fuel ignore #1"
    And I want to create fuel ignore and save id
    And I see field "name" filled with "test fuel ignore #1"
    And response code is 200

  Scenario: I want edit fuel ignore by id
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test fuel ignore #1"
    And  I want to create fuel ignore and save id
    And I see field "name" filled with "test fuel ignore #1"
    Then I want clean filled data
    And I want fill "name" field with "test fuel ignore #2"
    And I want fill teamId by saved clientId
    And I want to edit fuel ignore by saved id
    And I see field "name" filled with "test fuel ignore #2"
    And response code is 200

  Scenario: I want delete fuel ignore by id
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test fuel ignore"
    And  I want to create fuel ignore and save id
    And I see field "name" filled with "test fuel ignore"
    Then I want clean filled data
    And I want to delete fuel ignore by saved id
    Then response code is 204