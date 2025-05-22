Feature: Vehicles

  Background:
    Given I signed in as "admin" team "client" and teamId 2

  Scenario: I want get vehicle group list
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "model" field with "model1"
    And I want to create vehicle and save id
    And I see field "model" filled with "model1"
    Then I want fill vehicle ids with saved id
    And I want fill "name" field with "test group"
    And I want fill "color" field with "green"
    And I want to create vehicle group and save id
    And Elastica populate
    Then I want to get vehicle group list
    And I see field "data/0/name" filled with "test group"
    And I see field "data/0/color" filled with "green"
    And I see field "data/0/vehiclesCount" filled with 1
    Given I signed in as "admin" team "admin"
    Then I want to get vehicle group list
    And response code is 200
    Given I signed in as "support" team "admin"
    Then I want to get vehicle group list
    And response code is 403

  Scenario: I want get vehicle by id
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test group"
    And I want fill "color" field with "green"
    And I want to create vehicle group and save id
    Then I want get vehicle group by saved id
    And I see field "name" filled with "test group"
    And I see field "color" filled with "green"

  Scenario: I want check permissions
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test group1"
    And I want to create vehicle group and save id
    And I see field "name" filled with "test group1"
    Then I signed in as "driver" team "client"
    And I want fill "name" field with "test group"
    And I want to create vehicle group and save id
    And response code is 403

  Scenario: I want edit vehicle group by id
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test group"
    And I want fill "color" field with "green"
    And I want to create vehicle group and save id
    And I see field "name" filled with "test group"
    And I see field "color" filled with "green"
    Then I want clean filled data
    And I want fill "name" field with "test group2"
    And I want fill "color" field with "red"
    And I want fill teamId by saved clientId
    And I want to edit vehicle group by saved id
    And I see field "name" filled with "test group2"
    And I see field "color" filled with "red"
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "model" field with "model1"
    And I want to create vehicle and save id
    And I see field "model" filled with "model1"
    Then I want fill vehicle ids with saved id
    And I want to edit vehicle group by saved id
    And I see field "vehiclesCount" filled with 1
    Then I want fill vehicle ids with empty array
    And I want to edit vehicle group by saved id
    And I see field "vehiclesCount" filled with 0

  Scenario: I want delete vehicle by id
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test group"
    And I want to create vehicle group and save id
    And I see field "name" filled with "test group"
    Then I want clean filled data
    And I want fill "name" field with "test group2"
    And I want fill teamId by saved clientId
    And I want delete vehicle group by saved id
    Then response code is 204
