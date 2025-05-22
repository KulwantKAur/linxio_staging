Feature: Fuel mapping list

  Background:
    Given I signed in as "super_admin" team "admin"

  Scenario: I want get fuel mapping list
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test fuel mapping #1"
    Then I want fill "fuelType" field with 1
    And I want to create fuel mapping and save id
    And I see field "name" filled with "test fuel mapping #1"
    And I see field "fuelType/name" filled with "Diesel"
    Then I want fill "name" field with "test fuel mapping #2"
    Then I want fill "fuelType" field with 1
    And I want to create fuel mapping and save id
    And I see field "name" filled with "test fuel mapping #2"
    And I see field "fuelType/name" filled with "Diesel"
    And I want clean filled data
    Given Elastica populate
    And I want fill "limit" field with 20
    Then I want get fuel mapping list
    And I see field "data/15/name" filled with "test fuel mapping #1"
    And I see field "data/15/fuelType/id" filled with "1"
    And I see field "data/15/fuelType/name" filled with "Diesel"
    And I see field "data/16/name" filled with "test fuel mapping #2"
    And I see field "data/16/fuelType/id" filled with "1"
    And I see field "data/16/fuelType/name" filled with "Diesel"
    And response code is 200

  Scenario: I want get fuel mapping list by id
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test fuel mapping"
    Then I want fill "fuelType" field with 1
    And I want to create fuel mapping and save id
    Then I want get fuel mapping by saved id
    And I see field "name" filled with "test fuel mapping"
    And I see field "fuelType/name" filled with "Diesel"
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
    And I want fill "name" field with "test fuel mapping #1"
    Then I want fill "fuelType" field with 1
    And I want to create fuel mapping and save id
    And I see field "name" filled with "test fuel mapping #1"
    And I see field "fuelType/name" filled with "Diesel"
    And response code is 200

  Scenario: I want edit fuel mapping by id
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test fuel mapping #1"
    Then I want fill "fuelType" field with 1
    And  I want to create fuel mapping and save id
    And I see field "name" filled with "test fuel mapping #1"
    And I see field "fuelType/name" filled with "Diesel"
    Then I want clean filled data
    And I want fill "name" field with "test fuel mapping #2"
    Then I want fill "fuelType" field with 2
    And I want fill teamId by saved clientId
    And I want to edit fuel mapping by saved id
    And I see field "name" filled with "test fuel mapping #2"
    And I see field "fuelType/name" filled with "Petrol"
    And response code is 200

  Scenario: I want delete fuel mapping by id
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test fuel mapping"
    Then I want fill "fuelType" field with 1
    And  I want to create fuel mapping and save id
    And I see field "name" filled with "test fuel mapping"
    And I see field "fuelType/name" filled with "Diesel"
    And I see field "status" filled with "active"
    Then I want clean filled data
    And I want to delete fuel mapping by saved id
    And response code is 204
    Then I want get fuel mapping by saved id
    And I see field "name" filled with "test fuel mapping"
    And I see field "fuelType/name" filled with "Diesel"
    And I see field "status" filled with "deleted"
    And response code is 200

  Scenario: I want get fuel types list
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    Then I want get fuel types
    And I see field "0/id"
    And I see field "0/name" filled with "Diesel"
    And I see field "1/id"
    And I see field "1/name" filled with "Petrol"
    And I see field "2/id"
    And I see field "2/name" filled with "Gas"
    And I see field "3/id"
    And I see field "3/name" filled with "Electric engine"
    And response code is 200
