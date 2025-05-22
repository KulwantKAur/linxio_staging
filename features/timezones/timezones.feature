Feature: TimeZones
  Scenario:  I want get timezones
    When I want get timezones
    Then I see field "0/id" filled with 409
    Then I see field "0/displayName" filled with "(UTC-11:00) Pacific/Midway"
    Then I see field "0/name" filled with "Pacific/Midway"
    Then I see field "424/id" filled with 404
    Then I see field "424/displayName" filled with "(UTC+14:00) Pacific/Kiritimati"
    Then I see field "424/name" filled with "Pacific/Kiritimati"
