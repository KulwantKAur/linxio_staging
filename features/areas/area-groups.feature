Feature: Areas

  Background:
    Given I signed in as "admin" team "client" and teamId 2

  Scenario: I want get area group list
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test area"
    And I want fill area test coordinates "50.6373" "3.0750"
    And I want fill area test coordinates "50.6374" "3.0750"
    And I want fill area test coordinates "50.6374" "3.0749"
    And I want fill area test coordinates "50.63" "3.07491"
    And I want fill area test coordinates "50.6373" "3.0750"
    And I want to create area and save id
    And I see field "name" filled with "test area"
    Then I want fill area ids with saved id
    And I want fill "name" field with "test group"
    And I want fill "color" field with "#fff"
    And I want to create area group and save id
    Given Elastica populate
    Then I want to get area group list
    And I see field "data/0/name" filled with "test group"
    And I see field "data/0/color" filled with "#fff"
    And I see field "data/0/areasCount" filled with 1
    Given I signed in as "manager" team "client"
    Then I want to get area group list
    And response code is 200
    And I do not see field "0"

  Scenario: I want get area group by id
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test group"
    And I want to create area group and save id
    Then I want get area group by saved id
    And I see field "name" filled with "test group"

  Scenario: I want check permissions
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test group1"
    And I want to create area group and save id
    And I see field "name" filled with "test group1"
    Then I signed in as "driver" team "client"
    And I want fill "name" field with "test group"
    And I want to create area group and save id
    And response code is 403

  Scenario: I want edit area group by id
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test group"
    And I want to create area group and save id
    And I see field "name" filled with "test group"
    Then I want clean filled data
    And I want fill "name" field with "test group2"
    And I want fill teamId by saved clientId
    And I want to edit area group by saved id
    And I see field "name" filled with "test group2"
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test area"
    And I want fill area test coordinates "50.6373" "3.0750"
    And I want fill area test coordinates "50.6374" "3.0750"
    And I want fill area test coordinates "50.6374" "3.0749"
    And I want fill area test coordinates "50.63" "3.07491"
    And I want fill area test coordinates "50.6373" "3.0750"
    And I want to create area and save id
    And I see field "name" filled with "test area"
    Then I want fill area ids with saved id
    And I want to edit area group by saved id
    And I see field "areas/0/name" filled with "test area"
    Then I want fill areas id with empty array
    And I want to edit area group by saved id
    And I do not see field "areas/0"

  Scenario: I want delete area group by id
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test group"
    And I want to create area group and save id
    And I see field "name" filled with "test group"
    Then I want clean filled data
    And I want fill "name" field with "test group2"
    And I want fill teamId by saved clientId
    And I want delete area group by saved id
    Then response code is 204
