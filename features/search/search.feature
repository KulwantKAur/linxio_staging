Feature: Search
  Background:
    Given I signed in as "super_admin" team "admin"

  Scenario: I want make full search
    Given Elastica populate
    When I want make full search with query "acm"
    Then I see field "0/type" filled with "user"
    And I see field "0/items/0/email" filled with "acme-admin@linxio.local"
    Then I see field "1/type" filled with "client"
    And I see field "1/items/0/name" filled with "ACME1"
    When I want make full search with query "86"
    Then I see field "1/type" filled with "device"
    Then I see field "0/type" filled with "vehicle"
    Then I see field "0/items/0/deviceId"
    When I want make full search with query "QLD"
    Then I see field "0/type" filled with "vehiclegroup"
    When I want make full search with query "Frenchams"
    Then I see field "0/type" filled with "depot"
    When I want make full search with query "asia"
    Then I see field "0/type" filled with "areagroup"
    Then I see field "1/type" filled with "area"
    Then I see field "1/items/0/team"
    #search vehicle without device
    When I want make full search with query "221SVP"
    Then I do not see field "0"
    When I want make full search with query "10"
    Then I see field "0/type" filled with "area"
    Then I see field "0/items/0/team/clientId" filled with 10
    When I want make full search with query "9"
    Then I see field "0/type" filled with "vehicle"
    Then I see field "0/items/0/id" filled with 11