Feature: Storage

  Scenario: I want create and get storage record
    Given I signed in as "super_admin" team "admin"
    And I want fill "key" field with "test.key"
    And I want fill "value" field with "value of storage record"
    And I want to create storage record
    Then response code is 200
    And I see field "key" filled with "test.key"
    And I see field "value" filled with "value of storage record"
    Then I want to get storage record by key "test.key"
    Then response code is 200
    And I see field "key" filled with "test.key"
    And I see field "value" filled with "value of storage record"