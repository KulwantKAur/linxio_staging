Feature: Devices

  Background:
    Given I signed in as "super_admin" team "admin"
    And Elastica populate

  Scenario: I want get coordinates list
    When I want fill "dateFrom" field with "2007-07-25"
    And I want fill "dateTo" field with "2007-07-26"
    Then I want get coordinates list by imei "862259588834290"
    And response code is 200

  Scenario: I want get coordinates list hourly
    When I want fill "dateFrom" field with "2007-07-25T06:00:00+00:00"
    And I want fill "dateTo" field with "2007-07-26T06:00:00+00:00"
    And I want fill "filter" field with "hourly"
    Then I want get coordinates list by imei "862259588834290"
    And I see field "0/date"
    And I see field "0/date" filled with "2007-07-25T06:00:00+00:00"
    And I see field "1/date" filled with "2007-07-25T07:00:00+00:00"
    And I see field "0/coordinates"
    And I see field "1/coordinates"

  Scenario: I want get coordinates list daily
    When I want fill "dateFrom" field with "2007-07-25T00:00:00+10:00"
    And I want fill "dateTo" field with "2007-07-25T23:59:59+10:00"
    And I want fill "filter" field with "daily"
    Then I want get coordinates list by imei "862259588834290"
    And I see field "0/date"
    And I see field "0/date" filled with "2007-07-25T00:00:00+10:00"
    And I see field "0/coordinates"

  Scenario: I want get paginated coordinates list
    Given I want create teltonika device coordinates history
    When I want fill "dateFrom" field with "2007-07-25T06:00:00+00:00"
    And I want fill "dateTo" field with "2007-07-26T06:00:00+00:00"
    Then I want get paginated coordinates list by imei "862259588834290"
    And I see field "page" filled with "1"
    And I see field "limit" filled with "10"
    And I see field "total" filled with "4"
    When I want fill "dateFrom" field with "2007-07-25T06:00:00+00:00"
    And I want fill "dateTo" field with "2007-07-26T06:00:00+00:00"
    And I want fill "page" field with "2"
    And I want fill "limit" field with "2"
    Then I want get paginated coordinates list by imei "862259588834290"
    And I see field "page" filled with "2"
    And I see field "limit" filled with "2"
    And I see field "total" filled with "4"