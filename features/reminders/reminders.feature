Feature: Reminders

  Scenario: I want CRUD reminder
    Given I signed in as "admin" team "client" and teamId 2
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test group"
    And I want to create vehicle group and save id
    And I want fill vehicle group id
    And I want fill "type" field with "Car"
    And I want fill "model" field with "model"
    And I want fill "status" field with "active"
    Then I want to create vehicle and save id
    And response code is 200
    And I see field "team/type" filled with "client"
    And I see field "type" filled with "type"
    And I want clean filled data
    Then I want fill "title" field with "test reminder"
    And I want fill vehicle id
    And I want fill "status" field with "active"
    And I want fill "date" field with "2030-10-10 12:12:12"
    And I want fill "dateCheckbox" field with "true"
    And I want fill "mileageCheckbox" field with "true"
    And I want fill "hoursCheckbox" field with "true"
    And I want fill "datePeriod" field with "15"
    And I want fill "dateNotification" field with "5"
    And I want fill "mileage" field with "70000"
    And I want fill "mileagePeriod" field with "10000"
    And I want fill "mileageNotification" field with "1000"
    And I want fill "hours" field with "9000"
    And I want fill "hoursPeriod" field with "250"
    And I want fill "hoursNotification" field with "50"
    And I want fill "note" field with "text note"
    And I want fill array key "draft" with "date" field with "2030-10-10 12:12:12"
    And I want fill array key "draft" with "cost" field with "33"
    And I want fill array key "draft" with "note" field with "test note"
    Then I want to create reminder and save id
    And response code is 200
    And I see field "title" filled with "test reminder"
    And I see field "status" filled with "active"
    And I see field "controlDate" filled with "2030-10-10T12:12:12+00:00"
    And I see field "datePeriod" filled with "15"
    And I see field "dateNotification" filled with "5"
    And I see field "mileage" filled with "70000"
    And I see field "mileagePeriod" filled with "10000"
    And I see field "mileageNotification" filled with "1000"
    And I see field "hours" filled with "9000"
    And I see field "hoursPeriod" filled with "250"
    And I see field "hoursNotification" filled with "50"
    And I see field "note" filled with "text note"
    And I see field "draftRecord/date" filled with "2030-10-10T12:12:12+00:00"
    And I see field "draftRecord/cost" filled with "string(33)"
    And I see field "draftRecord/note" filled with "test note"
    Then I want to get reminder by saved id
    And response code is 200
    And I see field "title" filled with "test reminder"
    And I see field "status" filled with "active"
    And I see field "controlDate" filled with "2030-10-10T12:12:12+00:00"
    And I see field "datePeriod" filled with "15"
    And I see field "dateNotification" filled with "5"
    And I see field "mileage" filled with "70000"
    And I see field "mileagePeriod" filled with "10000"
    And I see field "mileageNotification" filled with "1000"
    And I see field "hours" filled with "9000"
    And I see field "hoursPeriod" filled with "250"
    And I see field "hoursNotification" filled with "50"
    And I see field "note" filled with "text note"
    And I want clean filled data
    Then I want fill "title" field with "test reminder2"
    And I want fill vehicle id
    And I want fill "status" field with "active"
    And I want fill "date" field with "2221-11-11 10:10:10"
    And I want fill "datePeriod" field with "20"
    And I want fill "dateNotification" field with "10"
    And I want fill "mileage" field with "80000"
    And I want fill "mileagePeriod" field with "20000"
    And I want fill "mileageNotification" field with "2000"
    And I want fill "hours" field with "4000"
    And I want fill "hoursPeriod" field with "350"
    And I want fill "hoursNotification" field with "60"
    And I want fill "note" field with "text note2"
    Then I want to edit reminder by saved id
    And response code is 200
    And I see field "title" filled with "test reminder2"
    And I see field "status" filled with "active"
    And I see field "controlDate" filled with "2221-11-11T10:10:10+00:00"
    And I see field "datePeriod" filled with "20"
    And I see field "dateNotification" filled with "10"
    And I see field "mileage" filled with "80000"
    And I see field "mileagePeriod" filled with "20000"
    And I see field "mileageNotification" filled with "2000"
    And I see field "hours" filled with "4000"
    And I see field "hoursPeriod" filled with "350"
    And I see field "hoursNotification" filled with "60"
    And I see field "note" filled with "text note2"
    And I see field "draftRecord" filled with null
    Then I want to delete reminder by saved id
    And response code is 204

  Scenario: I want get reminders list
    Given I signed in as "admin" team "client" and teamId 2
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test group"
    And I want to create vehicle group and save id
    And I want fill vehicle group id
    And I want fill "type" field with "Car"
    And I want fill "model" field with "model"
    And I want fill "status" field with "active"
    And I want fill "regNo" field with "testRegNo"
    Then I want to create vehicle and save id
    And response code is 200
    And I see field "team/type" filled with "client"
    And I see field "type" filled with "type"
    And I see field "model" filled with "model"
    And I want clean filled data
    Then I want fill "title" field with "test reminder"
    And I want fill vehicle id
    And I want fill "status" field with "active"
    And I want fill "dateCheckbox" field with "true"
    And I want fill "mileageCheckbox" field with "true"
    And I want fill "hoursCheckbox" field with "true"
    And I want fill "date" field with "2220-10-10 12:12:12"
    And I want fill "datePeriod" field with "15"
    And I want fill "dateNotification" field with "5"
    And I want fill "mileage" field with "70000"
    And I want fill "mileagePeriod" field with "10000"
    And I want fill "mileageNotification" field with "1000"
    And I want fill "hours" field with "9000"
    And I want fill "hoursPeriod" field with "250"
    And I want fill "hoursNotification" field with "50"
    And I want fill "note" field with "text note"
    Then I want to create reminder and save id
    And response code is 200
    And I see field "title" filled with "test reminder"
    And I want clean filled data
    Then I want fill "title" field with "test reminder2"
    And I want fill vehicle id
    And I want fill "status" field with "active"
    And I want fill "dateCheckbox" field with "true"
    And I want fill "mileageCheckbox" field with "true"
    And I want fill "hoursCheckbox" field with "true"
    And I want fill "date" field with "2221-11-11 10:10:10"
    And I want fill "datePeriod" field with "20"
    And I want fill "dateNotification" field with "10"
    And I want fill "mileage" field with "80000"
    And I want fill "mileagePeriod" field with "20000"
    And I want fill "mileageNotification" field with "2000"
    And I want fill "hours" field with "4000"
    And I want fill "hoursPeriod" field with "350"
    And I want fill "hoursNotification" field with "60"
    And I want fill "note" field with "text note2"
    Then I want to create reminder and save id
    And response code is 200
    And I see field "title" filled with "test reminder2"
    And I want clean filled data
    Then I want fill "title" field with "test reminder3"
    And I want fill vehicle id
    And I want fill "status" field with "active"
    And I want fill "dateCheckbox" field with "true"
    And I want fill "mileageCheckbox" field with "true"
    And I want fill "hoursCheckbox" field with "true"
    And I want fill "date" field with now
    And I want fill "datePeriod" field with "20"
    And I want fill "dateNotification" field with "10"
    And I want fill "mileage" field with "80000"
    And I want fill "mileagePeriod" field with "20000"
    And I want fill "mileageNotification" field with "2000"
    And I want fill "hours" field with "4000"
    And I want fill "hoursPeriod" field with "350"
    And I want fill "hoursNotification" field with "60"
    And I want fill "note" field with "text note2"
    Then I want to create reminder and save id
    And response code is 200
    Given Elastica populate
    And I want clean filled data
    Then I want get reminder list
    And I see field "total" filled with "3"
    And I see field "data/0/title" filled with "test reminder"
    And I see field "data/1/title" filled with "test reminder2"
    And I want fill "title" field with "test reminder2"
    Then I want get reminder list
    And I see field "total" filled with "1"
    And I see field "data/0/title" filled with "test reminder2"
    And I want clean filled data
    And I want fill array key controlDate with gte field with "2221-11-11T00:00:01"
    And I want fill array key controlDate with lte field with "2221-11-11T23:59:01"
    Then I want get reminder list
    And I see field "total" filled with "1"
    And I see field "data/0/title" filled with "test reminder2"
    And I want clean filled data
    And I want fill "status" field with "active"
    Then I want get reminder list
    And I see field "total" filled with "2"
    And I see field "data/1/title" filled with "test reminder2"
    And I want clean filled data
    And I want fill "controlMileage" field with "70000"
    Then I want get reminder list
    And I see field "total" filled with "1"
    And I see field "data/0/title" filled with "test reminder"
    And I want clean filled data
    And I want fill "controlHours" field with "9000"
    Then I want get reminder list
    And I see field "total" filled with "1"
    And I see field "data/0/title" filled with "test reminder"
    And I want clean filled data
    And I want fill "vehicleRegNo" field with "testReg"
    Then I want get reminder list
    And I see field "total" filled with "3"
    And I see field "data/0/vehicle/regNo" filled with "testRegNo"
    And I want clean filled data
    And I want fill "vehicleRegNo" field with "reg"
    Then I want get reminder list
    And I see field "total" filled with "0"
    And I want clean filled data
    And I want fill array key date with gte field with "2221-11-11T00:00:01"
    And I want fill array key date with lte field with "2221-11-11T23:59:01"
    Then I want get reminder list
    And I see field "total" filled with 1
    And I want clean filled data
    And I want fill "remainingDays" field with 0
    Then I want get reminder list
    And I see field "total" filled with 1
    And I see field "data/0/title" filled with "test reminder3"

  Scenario: I want check reminder status
    Given I signed in as "admin" team "client" and teamId 2
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test group"
    And I want to create vehicle group and save id
    And I want fill vehicle group id
    And I want fill "type" field with "Car"
    And I want fill "model" field with "model"
    And I want fill "status" field with "active"
    Then I want to create vehicle and save id
    And response code is 200
    And I want clean filled data
    Then I want fill "title" field with "test reminder"
    And I want fill vehicle id
    And I want fill "status" field with "active"
    And I want fill "dateCheckbox" field with "true"
    And I want fill date field "date" with now
    And I want fill "dateNotification" field with "5"
    Then I want to create reminder and save id
    And response code is 200
    And I see field "title" filled with "test reminder"
    And I see field "status" filled with "expired"
    And I want clean filled data
    Then I want fill "title" field with "test reminder"
    And I want fill date field "date" with tomorrow
    Then I want to edit reminder by saved id
    And response code is 200
    And I see field "title" filled with "test reminder"
    And I see field "status" filled with "due_soon"
    And I want clean filled data
    Then I want fill "title" field with "test reminder"
    And I want fill "date" field with "2221-11-11 10:10:10"
    Then I want to edit reminder by saved id
    And response code is 200
    And I see field "title" filled with "test reminder"
    And I see field "status" filled with "active"
    And I see field "dateNotification" filled with "5"

  Scenario: I want test export csv
    Given I signed in as "admin" team "client" and teamId 2
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test group"
    And I want to create vehicle group and save id
    And I want fill vehicle group id
    And I want fill "type" field with "Car"
    And I want fill "model" field with "model"
    And I want fill "status" field with "active"
    And I want fill "regNo" field with "testRegNo"
    Then I want to create vehicle and save id
    And response code is 200
    And I see field "team/type" filled with "client"
    And I see field "type" filled with "type"
    And I see field "model" filled with "model"
    And I want clean filled data
    Then I want fill "title" field with "test reminder"
    And I want fill vehicle id
    And I want fill "status" field with "active"
    And I want fill "dateCheckbox" field with "true"
    And I want fill "mileageCheckbox" field with "true"
    And I want fill "hoursCheckbox" field with "true"
    And I want fill "date" field with "2220-10-10 12:12:12"
    And I want fill "datePeriod" field with "15"
    And I want fill "dateNotification" field with "5"
    And I want fill "mileage" field with "70000"
    And I want fill "mileagePeriod" field with "10000"
    And I want fill "mileageNotification" field with "1000"
    And I want fill "hours" field with "9000"
    And I want fill "hoursPeriod" field with "250"
    And I want fill "hoursNotification" field with "50"
    And I want fill "note" field with "text note"
    Then I want to create reminder and save id
    And response code is 200
    And I see field "title" filled with "test reminder"
    And I want clean filled data
    Then I want fill "title" field with "test reminder2"
    And I want fill vehicle id
    And I want fill "status" field with "active"
    And I want fill "dateCheckbox" field with "true"
    And I want fill "mileageCheckbox" field with "true"
    And I want fill "hoursCheckbox" field with "true"
    And I want fill "date" field with "2221-11-11 10:10:10"
    And I want fill "datePeriod" field with "20"
    And I want fill "dateNotification" field with "10"
    And I want fill "mileage" field with "80000"
    And I want fill "mileagePeriod" field with "20000"
    And I want fill "mileageNotification" field with "2000"
    And I want fill "hours" field with "4000"
    And I want fill "hoursPeriod" field with "350"
    And I want fill "hoursNotification" field with "60"
    And I want fill "note" field with "text note2"
    Then I want to create reminder and save id
    And response code is 200
    And I see field "title" filled with "test reminder2"
    And I want clean filled data
    Then I want fill "title" field with "test reminder3"
    And I want fill vehicle id
    And I want fill "status" field with "active"
    And I want fill "dateCheckbox" field with "true"
    And I want fill "mileageCheckbox" field with "true"
    And I want fill "hoursCheckbox" field with "true"
    And I want fill "date" field with now
    And I want fill "datePeriod" field with "20"
    And I want fill "dateNotification" field with "10"
    And I want fill "mileage" field with "80000"
    And I want fill "mileagePeriod" field with "20000"
    And I want fill "mileageNotification" field with "2000"
    And I want fill "hours" field with "4000"
    And I want fill "hoursPeriod" field with "350"
    And I want fill "hoursNotification" field with "60"
    And I want fill "note" field with "text note2"
    Then I want to create reminder and save id
    And response code is 200
    Given Elastica populate
    And I want clean filled data
    Then I want add value to array key "fields" with "vehicleRegNo"
    Then I want add value to array key "fields" with "title"
    Then I want add value to array key "fields" with "status"
    Then I want add value to array key "fields" with "remainingDays"
    Then I want add value to array key "fields" with "remainingMileage"
    Then I want add value to array key "fields" with "remainingHours"
    Then I want add value to array key "fields" with "date"
    Then I want add value to array key "fields" with "mileage"
    Then I want add value to array key "fields" with "hours"
    Then I want export reminders csv
    And I see csv item number 0 field "Title" filled with "test reminder"
    And I see csv item number 1 field "Title" filled with "test reminder2"
    And I see csv item number 2 field "Title" filled with "test reminder3"

  Scenario: I want test report
    Given I signed in as "admin" team "client" and teamId 2
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test group"
    And I want to create vehicle group and save id
    And I want fill vehicle group id
    And I want fill "type" field with "Car"
    And I want fill "model" field with "model"
    And I want fill "status" field with "active"
    And I want fill "regNo" field with "testRegNo"
    Then I want to create vehicle and save id
    And response code is 200
    Then I want set vehicle driver with user of current team with date "2019-05-29 08:19:38"
    And I see field "team/type" filled with "client"
    And I see field "type" filled with "type"
    And I see field "model" filled with "model"
    And I want clean filled data
    Then I want fill "title" field with "test reminder"
    And I want fill vehicle id
    And I want fill "status" field with "active"
    And I want fill "dateCheckbox" field with "true"
    And I want fill "mileageCheckbox" field with "true"
    And I want fill "hoursCheckbox" field with "true"
    And I want fill "date" field with "2220-10-10 12:12:12"
    And I want fill "datePeriod" field with "15"
    And I want fill "dateNotification" field with "5"
    And I want fill "mileage" field with "70000"
    And I want fill "mileagePeriod" field with "10000"
    And I want fill "mileageNotification" field with "1000"
    And I want fill "hours" field with "9000"
    And I want fill "hoursPeriod" field with "250"
    And I want fill "hoursNotification" field with "50"
    And I want fill "note" field with "text note"
    Then I want to create reminder and save id
    And response code is 200
    And I see field "title" filled with "test reminder"
    And I want clean filled data
    Then I want fill "title" field with "test reminder2"
    And I want fill vehicle id
    And I want fill "status" field with "active"
    And I want fill "dateCheckbox" field with "true"
    And I want fill "mileageCheckbox" field with "true"
    And I want fill "hoursCheckbox" field with "true"
    And I want fill "date" field with "2221-11-11 10:10:10"
    And I want fill "datePeriod" field with "20"
    And I want fill "dateNotification" field with "10"
    And I want fill "mileage" field with "80000"
    And I want fill "mileagePeriod" field with "20000"
    And I want fill "mileageNotification" field with "2000"
    And I want fill "hours" field with "4000"
    And I want fill "hoursPeriod" field with "350"
    And I want fill "hoursNotification" field with "60"
    And I want fill "note" field with "text note2"
    Then I want to create reminder and save id
    And response code is 200
    And I see field "title" filled with "test reminder2"
    And I want clean filled data
    Then I want fill "title" field with "test reminder3"
    And I want fill vehicle id
    And I want fill "status" field with "active"
    And I want fill "dateCheckbox" field with "true"
    And I want fill "mileageCheckbox" field with "true"
    And I want fill "hoursCheckbox" field with "true"
    And I want fill "date" field with now
    And I want fill "datePeriod" field with "20"
    And I want fill "dateNotification" field with "10"
    And I want fill "mileage" field with "80000"
    And I want fill "mileagePeriod" field with "20000"
    And I want fill "mileageNotification" field with "2000"
    And I want fill "hours" field with "4000"
    And I want fill "hoursPeriod" field with "350"
    And I want fill "hoursNotification" field with "60"
    And I want fill "note" field with "text note2"
    Then I want to create reminder and save id
    And response code is 200
    And I want clean filled data
    And Elastica populate
    Then I want get reminders report with type json
    And I see field "total" filled with 3
    And I see field "data/0/vehicle/driver/email" filled with "client-contact-0@ocsico.com"
    Then I want add value to array key "fields" with "driver"
    Then I want get reminders report with type csv
    And I see csv item number 0 field "Driver" filled with "client-contact-name-0 client-surname-name-0"

  Scenario: I want test duplicate reminder
    Given I signed in as "admin" team "client" and teamId 2
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test group"
    And I want to create vehicle group and save id
    And I want fill vehicle group id
    And I want fill "type" field with "Car"
    And I want fill "model" field with "model"
    And I want fill "status" field with "online"
    And I want fill "regNo" field with "REG1"
    Then I want to create vehicle and save id
    And response code is 200
    And I see field "model" filled with "model"
    And I want clean filled data
    Then I want fill "title" field with "test reminder"
    And I want fill vehicle id
    And I want fill "status" field with "active"
    And I want fill "dateCheckbox" field with "true"
    And I want fill date field "date" with now
    And I want fill "dateNotification" field with "5"
    Then I want to create reminder and save id
    And response code is 200
    And I see field "title" filled with "test reminder"
    And I want clean filled data
    And I want fill teamId by saved clientId
    And I want fill vehicle group id
    And I want fill "type" field with "Car"
    And I want fill "model" field with "model"
    And I want fill "status" field with "online"
    And I want fill "regNo" field with "REG2"
    Then I want to create vehicle and save id
    And response code is 200
    And I see field "model" filled with "model"
    And I want clean filled data
    Then I want fill vehicles with saved id
    Then I want to duplicate reminder
    And I want clean filled data
    And I want fill "vehicleRegNo" field with "REG2"
    Given Elastica populate
    Then I want get reminder list
    And response code is 200
    And I see field "total" filled with 1
    And I see field "data/0/vehicle/regNo" filled with "REG2"
    And I want clean filled data
    Then I want fill vehicle groups with saved id
    Then I want to duplicate reminder
    And I want clean filled data
    And I want fill "vehicleRegNo" field with "REG2"
    Then I want get reminder list
    And response code is 200
    And I see field "total" filled with 2
    And I see field "data/0/vehicle/regNo" filled with "REG2"

  Scenario: I want dashboard statistic
    Given I signed in as "admin" team "client" and teamId 2
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test group"
    And I want to create vehicle group and save id
    And I want fill vehicle group id
    And I want fill "type" field with "Car"
    And I want fill "model" field with "model"
    And I want fill "status" field with "active"
    Then I want to create vehicle and save id
    And response code is 200
    And I see field "model" filled with "model"
    And I want clean filled data
    Then I want fill "title" field with "test reminder"
    And I want fill vehicle id
    And I want fill "status" field with "active"
    And I want fill "dateCheckbox" field with "true"
    And I want fill date field "date" with now
    And I want fill "dateNotification" field with "5"
    Then I want to create reminder and save id
    And response code is 200
    And I see field "title" filled with "test reminder"
    And I see field "status" filled with "expired"
    Then I want get reminder dashboard statistic
    And I see field "expired/count" filled with 1
    And I see field "reminders/0/title" filled with "test reminder"
    And I see field "reminders/0/status" filled with "expired"
    And I want clean filled data
    Then I want fill "title" field with "test reminder2"
    And I want fill date field "date" with tomorrow
    Then I want to edit reminder by saved id
    And response code is 200
    And I see field "title" filled with "test reminder2"
    And I see field "status" filled with "due_soon"
    Then I want get reminder dashboard statistic
    And I see field "due_soon/count" filled with 1
    And I see field "reminders/0/title" filled with "test reminder2"
    And I see field "reminders/0/status" filled with "due_soon"