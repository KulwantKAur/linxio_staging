Feature: Depots
  Background:
    Given I signed in as "admin" team "client" and teamId 2

  Scenario: I want CRUD depot
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "model" field with "model1"
    And I want to create vehicle and save id
    And I see field "model" filled with "model1"
    Then I want fill vehicle ids with saved id
    And I want fill "name" field with "test depot"
    And I want fill "color" field with "green"
    And I want to create depot and save id
    Then response code is 200
    And I see field "name" filled with "test depot"
    And I see field "vehiclesCount" filled with 1
    And I see field "vehicles/0/model" filled with "model1"
    And I see field "color" filled with "green"
    Then I want to get depot by saved id
    Then response code is 200
    And I see field "name" filled with "test depot"
    And I see field "vehiclesCount" filled with 1
    And I see field "color" filled with "green"
    And I want fill "model" field with "model2"
    Then I want to create vehicle and save id
    And I see field "model" filled with "model2"
    And I want fill vehicle ids with saved id
    Then I want fill "name" field with "test depot 2"
    Then I want fill "color" field with "red"
    And I want to edit depot by saved id
    Then response code is 200
    And I see field "vehiclesCount" filled with 2
    And I see field "name" filled with "test depot 2"
    And I see field "team/clientName" filled with "client-name-0"
    And I see field "color" filled with "red"
    Then I want fill vehicle ids with empty array
    And I want to edit depot by saved id
    Then response code is 200
    And I see field "vehiclesCount" filled with 0
    Then I want clean filled data
    And I want fill "name" field with "test depot"
    And I want to create depot and save assign depot id
    And I want to delete depot by saved id
    Then response code is 204
    And I want to get vehicle by saved id
    And I want check "depot" is not empty

  Scenario: I want add vehicle with depot
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test depot"
    And I want to create depot and save id
    And I want fill depotId by saved Id
    And I want fill "type" field with "Car"
    And I want fill "model" field with "model"
    Then I want to create vehicle and save id
    And response code is 200
    And I see field "team/type" filled with "client"
    And I see field "depot/name" filled with "test depot"
    And I see field "team/clientName" filled with "client-name-0"

  Scenario: I want get depot list
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "model" field with "model1"
    And I want to create vehicle and save id
    And I see field "model" filled with "model1"
    Then I want fill vehicle ids with saved id
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test depot"
    And I want fill "color" field with "green"
    And I want to create depot and save id
    Then response code is 200
    And I see field "name" filled with "test depot"
    And I see field "color" filled with "green"
    Then I want clean filled data
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test depot"
    And I want to create depot and save id
    Then response code is 200
    And I see field "name" filled with "test depot"
    Then I want clean filled data
    Given Elastica populate
    Then I want get depot list
    Then response code is 200
    And I want fill "vehiclesCountFilter[lt]" field with 1
    Then I want get depot list
    And I see field "total" filled with 1
