Feature: Service Records

  Scenario: I want CRUD service records
    Given I signed in as "admin" team "client" and teamId 2
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
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
    And I want fill "date" field with "2020-10-10 12:12:12"
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
    Then I want fill "date" field with "2020-10-10 10:10:10"
    And I want fill "note" field with "test note"
    And I want fill "cost" field with "2000"
    And I want fill "status" field with "active"
    And I want upload file
    And I want to create service record for saved reminder and save id
    And response code is 200
    And I see field "note" filled with "test note"
    And I see field "formattedDate" filled with "10/10/2020 10:10"
    And I see field "cost" filled with "string(2000)"
    And I see field "status" filled with "active"
    And I see field "files/0/id"
    Then I want to get service record from reminder by id
    And response code is 200
    And I see field "note" filled with "test note"
    And I see field "cost" filled with 2000
    And I see field "formattedDate" filled with "10/10/2020 10:10"
    And I see field "files/0/id"
    Then I want clean filled data
    And I want fill "date" field with "2022-10-10 10:10:10"
    And I want fill "note" field with "test note2"
    And I want fill "cost" field with "3000"
    And I want to edit service record for saved reminder by saved id
    And I see field "formattedDate" filled with "10/10/2022 10:10"
    And I see field "note" filled with "test note2"
    And I see field "cost" filled with "string(3000)"
    Then I want to delete service record for saved reminder by saved id
    And response code is 204

  Scenario: I want get service record list
    Given I signed in as "admin" team "client" and teamId 2
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "type" field with "Car"
    And I want fill "model" field with "model"
    And I want fill "status" field with "online"
    Then I want to create vehicle and save id
    And response code is 200
    And I see field "model" filled with "model"
    And I want clean filled data
    Then I want fill "title" field with "test reminder"
    And I want fill vehicle id
    And I want fill "status" field with "active"
    And I want fill "date" field with "2020-10-10 12:12:12"
    And I want fill "datePeriod" field with "15"
    And I want fill "dateNotification" field with "5"
    Then I want to create reminder and save id
    And response code is 200
    And I see field "title" filled with "test reminder"
    And I want clean filled data
    Then I want fill "date" field with "2020-10-10 10:10:10"
    And I want fill "note" field with "test note"
    And I want fill "cost" field with "2000"
    And I want fill "status" field with "active"
    And I want to create service record for saved reminder and save id
    And response code is 200
    And I see field "note" filled with "test note"
    And I want clean filled data
    Then I want fill "date" field with "2022-10-10 10:10:10"
    And I want fill "note" field with "test note2"
    And I want fill "cost" field with "3000"
    And I want fill "status" field with "active"
    And I want to create service record for saved reminder and save id
    And response code is 200
    And I see field "note" filled with "test note2"
    And I want clean filled data
    Then I want get service record list for saved reminder
    And I see field "total" filled with "2"
    And I want clean filled data
    And I want fill teamId by saved clientId
    And I want fill "type" field with "Car"
    And I want fill "model" field with "model"
    And I want fill "status" field with "online"
    Then I want to create vehicle and save id
    And response code is 200
    And I see field "model" filled with "model"
    And I want clean filled data
    Then I want fill "title" field with "test reminder"
    And I want fill vehicle id
    And I want fill "status" field with "active"
    And I want fill "date" field with "2020-10-10 12:12:12"
    And I want fill "datePeriod" field with "15"
    And I want fill "dateNotification" field with "5"
    Then I want to create reminder and save id
    And response code is 200
    And I see field "title" filled with "test reminder"
    And I want clean filled data
    Then I want fill "date" field with "2020-10-10 10:10:10"
    And I want fill "note" field with "test note3"
    And I want fill "cost" field with "2000"
    And I want fill "status" field with "active"
    And I want to create service record for saved reminder and save id
    And response code is 200
    And I want clean filled data
    Then I want get service record list for saved reminder
    And I see field "total" filled with "1"
    Then I want clean filled data
    And I want get full service record list
    And I see field "total" filled with 3
    Given I signed in as "manager" team "client" and teamId 3
    And I want get full service record list
    And I see field "total" filled with 0

  Scenario: I want get service record report summary
    Given I signed in as "admin" team "client" and teamId 2
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "type" field with "Car"
    And I want fill "model" field with "model"
    And I want fill "status" field with "active"
    And I want fill "regNo" field with "REGNO111"
    Then I want to create vehicle and save id
    And response code is 200
    And I see field "model" filled with "model"
    And I want clean filled data
    Then I want fill "title" field with "test reminder"
    And I want fill vehicle id
    And I want fill "status" field with "active"
    And I want fill "date" field with "2020-10-10 12:12:12"
    And I want fill "datePeriod" field with "15"
    And I want fill "dateNotification" field with "5"
    Then I want to create reminder and save id
    And response code is 200
    And I see field "title" filled with "test reminder"
    And I want clean filled data
    Then I want fill "date" field with "2020-10-10 10:10:10"
    And I want fill "note" field with "test note"
    And I want fill "cost" field with "2000"
    And I want fill "status" field with "active"
    And I want to create service record for saved reminder and save id
    And response code is 200
    And I see field "note" filled with "test note"
    And I want clean filled data
    Then I want fill "date" field with "2022-10-10 10:10:10"
    And I want fill "note" field with "test note2"
    And I want fill "cost" field with "3000"
    And I want fill "status" field with "active"
    And I want to create service record for saved reminder and save id
    And response code is 200
    And I see field "note" filled with "test note2"
    And Elastica populate
    And I want clean filled data
    Then I want get service record list for saved reminder
    And I see field "total" filled with "2"
    And I want clean filled data
    And I want fill teamId by saved clientId
    And I want fill "type" field with "Car"
    And I want fill "model" field with "model"
    And I want fill "status" field with "active"
    And I want fill "regNo" field with "REGNO222"
    Then I want to create vehicle and save id
    And response code is 200
    And I see field "model" filled with "model"
    And I want clean filled data
    Then I want fill "title" field with "test reminder"
    And I want fill vehicle id
    And I want fill "status" field with "active"
    And I want fill "date" field with "2020-10-10 12:12:12"
    And I want fill "datePeriod" field with "15"
    And I want fill "dateNotification" field with "5"
    Then I want to create reminder and save id
    And response code is 200
    And I see field "title" filled with "test reminder"
    And I want clean filled data
    Then I want fill "date" field with "2020-10-10 10:10:10"
    And I want fill "note" field with "test note"
    And I want fill "cost" field with "2000"
    And I want fill "status" field with "active"
    And I want to create service record for saved reminder and save id
    And response code is 200
    And I want clean filled data
    Then I want fill "startDate" field with "2018-10-10 10:10:10"
    Then I want fill "endDate" field with "2023-10-10 10:10:10"
    Then I want fill "regno" field with "REGNO111"
    Then I want get service record summary report
    And response code is 200
    And I see field "total" filled with 1
    And I see field "data/0/regno" filled with "REGNO111"
    And I see field "data/0/sr_count" filled with "string(2)"
    And I see field "data/0/sr_cost" filled with "string(5000)"
    And I see field "data/0/sr_last_date" filled with "2022-10-10T10:10:10+00:00"
    Then I want clean filled data
    Then I want fill "startDate" field with "2018-10-10 10:10:10"
    Then I want fill "endDate" field with "2023-10-10 10:10:10"
    Then I want fill "regno" field with "REGNO222"
    Then I want get service record summary report
    And response code is 200
    And I see field "total" filled with 1
    And I see field "data/0/regno" filled with "REGNO222"
    And I see field "data/0/sr_count" filled with "string(1)"
    And I see field "data/0/sr_cost" filled with "string(2000)"
    And I see field "data/0/sr_last_date" filled with "2020-10-10T10:10:10+00:00"

  Scenario: I want get service record report detailed
    Given I signed in as "admin" team "client" and teamId 2
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "type" field with "Car"
    And I want fill "model" field with "model"
    And I want fill "status" field with "online"
    And I want fill "regNo" field with "REGNO111"
    Then I want to create vehicle and save id
    And response code is 200
    And I see field "model" filled with "model"
    And I want clean filled data
    Then I want fill "title" field with "test reminder"
    And I want fill vehicle id
    And I want fill "status" field with "active"
    And I want fill "date" field with "2020-10-10 12:12:12"
    And I want fill "datePeriod" field with "15"
    And I want fill "dateNotification" field with "5"
    Then I want to create reminder and save id
    And response code is 200
    And I see field "title" filled with "test reminder"
    And I want clean filled data
    Then I want fill "date" field with "2020-10-10 10:10:10"
    And I want fill "note" field with "test note"
    And I want fill "cost" field with "2000"
    And I want fill "status" field with "active"
    And I want to create service record for saved reminder and save id
    And response code is 200
    And I see field "note" filled with "test note"
    And I want clean filled data
    Then I want fill "date" field with "2022-10-10 10:10:10"
    And I want fill "note" field with "test note2"
    And I want fill "cost" field with "3000"
    And I want fill "status" field with "active"
    And I want to create service record for saved reminder and save id
    And response code is 200
    And I see field "note" filled with "test note2"
    And I want clean filled data
    Then I want get service record list for saved reminder
    And I see field "total" filled with "2"
    And I want clean filled data
    And I want fill teamId by saved clientId
    And I want fill "type" field with "Car"
    And I want fill "model" field with "model"
    And I want fill "status" field with "online"
    And I want fill "regNo" field with "REGNO222"
    Then I want to create vehicle and save id
    And response code is 200
    And I see field "model" filled with "model"
    And Elastica populate
    And I want clean filled data
    Then I want fill "title" field with "test reminder"
    And I want fill vehicle id
    And I want fill "status" field with "active"
    And I want fill "date" field with "2020-10-10 12:12:12"
    And I want fill "datePeriod" field with "15"
    And I want fill "dateNotification" field with "5"
    Then I want to create reminder and save id
    And response code is 200
    And I see field "title" filled with "test reminder"
    And I want clean filled data
    Then I want fill "date" field with "2020-10-10 10:10:10"
    And I want fill "note" field with "test note"
    And I want fill "cost" field with "2000"
    And I want fill "status" field with "active"
    And I want to create service record for saved reminder and save id
    And response code is 200
    And I want clean filled data
    Then I want fill "startDate" field with "2018-10-10 10:10:10"
    Then I want fill "endDate" field with "2023-10-10 10:10:10"
    Then I want fill "regno" field with "REGNO111"
    Then I want get service record detailed report
    And response code is 200
    And I see field "total" filled with 2
    And I see field "data/0/regno" filled with "REGNO111"
    And I see field "data/0/sr_note" filled with "test note2"
    And I see field "data/0/sr_amount" filled with "string(3000)"
    And I see field "data/0/sr_date" filled with "2022-10-10T10:10:10+00:00"
    And I see field "data/1/regno" filled with "REGNO111"
    And I see field "data/1/sr_note" filled with "test note"
    And I see field "data/1/sr_amount" filled with "string(2000)"
    And I see field "data/1/sr_date" filled with "2020-10-10T10:10:10+00:00"
    Then I want clean filled data
    Then I want fill "startDate" field with "2018-10-10 10:10:10"
    Then I want fill "endDate" field with "2023-10-10 10:10:10"
    Then I want fill "regno" field with "REGNO222"
    Then I want get service record detailed report
    And response code is 200
    And I see field "total" filled with 1
    And I see field "data/0/regno" filled with "REGNO222"
    And I see field "data/0/sr_note" filled with "test note"
    And I see field "data/0/sr_amount" filled with "string(2000)"
    And I see field "data/0/sr_date" filled with "2020-10-10T10:10:10+00:00"
    Then I want clean filled data
    Then I want fill "startDate" field with "2018-10-10 10:10:10"
    Then I want fill "endDate" field with "2023-10-10 10:10:10"
    Then I want get service records vehicles list
    And I see field "total" filled with 2
    And I see field "data/0/regNo" filled with "REGNO111"
    And I see field "data/1/regNo" filled with "REGNO222"

  Scenario: I want CRUD repairs
    Given I signed in as "admin" team "client" and teamId 2
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "type" field with "Car"
    And I want fill "model" field with "model"
    And I want fill "status" field with "online"
    And I want fill "regNo" field with "TEST REGNO"
    Then I want to create vehicle and save id
    And response code is 200
    And I see field "team/type" filled with "client"
    And I see field "type" filled with "type"
    And I want clean filled data
    And I want fill vehicle id
    Then I want fill "date" field with "2020-10-10 10:10:10"
    And I want fill "note" field with "test note"
    And I want fill "cost" field with "2000"
    And I want fill "status" field with "active"
    And I want fill "title" field with "test repair"
    And I want upload file
    And I want to create repair and save id
    And response code is 200
    And I see field "note" filled with "test note"
    And I see field "formattedDate" filled with "10/10/2020 10:10"
    And I see field "cost" filled with "string(2000)"
    And I see field "status" filled with "active"
    And I see field "repairTitle" filled with "test repair"
    And I see field "files/0/id"
    Then I want clean filled data
    And I want fill "date" field with "2022-10-10 10:10:10"
    And I want fill "note" field with "test note2"
    And I want fill "cost" field with "3000"
    And I want fill "title" field with "test repair 2"
    And I want fill vehicle id
    And I want to edit repair by saved id
    And I see field "formattedDate" filled with "10/10/2022 10:10"
    And I see field "note" filled with "test note2"
    And I see field "cost" filled with "string(3000)"
    And I see field "repairTitle" filled with "test repair 2"
    Then I want clean filled data
    Then I want to get repair by saved id
    And I see field "formattedDate" filled with "10/10/2022 10:10"
    And I see field "note" filled with "test note2"
    And I see field "cost" filled with "string(3000)"
    And I see field "repairTitle" filled with "test repair 2"
    Then I want get repairs list
    And I see field "data/0/formattedDate" filled with "10/10/2022 10:10"
    And I see field "data/0/note" filled with "test note2"
    And I see field "data/0/cost" filled with "string(3000)"
    And I see field "data/0/repairTitle" filled with "test repair 2"
    Then I want add value to array key "fields" with "date"
    Then I want add value to array key "fields" with "repairTitle"
    Then I want add value to array key "fields" with "user"
    Then I want add value to array key "fields" with "cost"
    Then I want add value to array key "fields" with "note"
    Then I want export repairs list
    And I see csv item number 0 field "Date" filled with "10/10/2022 10:10"
    And I see csv item number 0 field "Title" filled with "test repair 2"
    And I see csv item number 0 field "User" filled with "test user surname"
    And I see csv item number 0 field "Cost" filled with "3000"
    And I see csv item number 0 field "Note" filled with "test note2"
    Then I want clean filled data
    Then I want fill "startDate" field with "2018-10-10 10:10:10"
    Then I want fill "endDate" field with "2023-10-10 10:10:10"
    Then I want get repairs vehicles list
    And I see field "data/0/regNo" filled with "TEST REGNO"
    Then I want to delete repair by saved id
    And response code is 204

  Scenario: I test common report
    Given I signed in as "admin" team "client"
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "type" field with "Car"
    And I want fill "model" field with "model"
    And I want fill "status" field with "online"
    And I want fill "regNo" field with "TEST REGNO"
    Then I want to create vehicle and save id
    And response code is 200
    And I see field "team/type" filled with "client"
    And I see field "type" filled with "type"
    And I see field "model" filled with "model"
    And I want clean filled data
    And I want fill vehicle id
    Then I want fill "date" field with "2020-10-10 10:10:10"
    And I want fill "note" field with "test note"
    And I want fill "cost" field with "2000"
    And I want fill "status" field with "active"
    And I want fill "title" field with "test repair"
    And I want upload file
    And I want to create repair and save id
    And response code is 200
    And I see field "note" filled with "test note"
    And I want clean filled data
    And I want fill teamId by saved clientId
    And I want fill "type" field with "Car"
    And I want fill "model" field with "model"
    And I want fill "status" field with "online"
    And I want fill "regNo" field with "TEST REGNO1"
    Then I want to create vehicle and save id
    And response code is 200
    And I see field "team/type" filled with "client"
    And I see field "type" filled with "type"
    And I see field "model" filled with "model"
    Then I want fill "title" field with "test reminder"
    And I want fill vehicle id
    And I want fill "status" field with "active"
    And I want fill "date" field with "2020-10-10 12:12:12"
    And I want fill "datePeriod" field with "15"
    And I want fill "dateNotification" field with "5"
    Then I want to create reminder and save id
    And response code is 200
    And I see field "title" filled with "test reminder"
    And I want clean filled data
    Then I want fill "date" field with "2020-10-10 10:10:10"
    And I want fill "note" field with "test note"
    And I want fill "cost" field with "2000"
    And I want fill "status" field with "active"
    And I want to create service record for saved reminder and save id
    And response code is 200
    And I see field "note" filled with "test note"
    Then I want clean filled data
    Then I want fill "startDate" field with "2018-10-10 10:10:10"
    Then I want fill "endDate" field with "2023-10-10 10:10:10"
    Then I want get common report
    And response code is 200
    And I see field "total" filled with 2
    Then I want get common vehicle list
    And response code is 200
    And I see field "total" filled with 2
    And I see field "data/0/regNo" filled with "TEST REGNO"
    And I see field "data/1/regNo" filled with "TEST REGNO1"

  Scenario: I want test repairs dashboard
    Given I signed in as "admin" team "client"
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "type" field with "Car"
    And I want fill "model" field with "model"
    And I want fill "status" field with "online"
    And I want fill "regNo" field with "TEST REGNO"
    Then I want to create vehicle and save id
    And response code is 200
    And I see field "team/type" filled with "client"
    And I see field "type" filled with "type"
    And I see field "model" filled with "model"
    And I want clean filled data
    And I want fill vehicle id
    Then I want fill "date" field with now
    And I want fill "note" field with "test note"
    And I want fill "cost" field with "2000"
    And I want fill "status" field with "active"
    And I want fill "title" field with "test repair"
    And I want upload file
    And I want to create repair and save id
    And response code is 200
    And I see field "note" filled with "test note"
    Then I want fill date field "date" with 3 days ago
    And I want fill "note" field with "test note2"
    And I want fill "cost" field with "3000"
    And I want fill "status" field with "active"
    And I want fill "title" field with "test repair2"
    And I want upload file
    And I want to create repair and save id
    And response code is 200
    And I see field "note" filled with "test note2"
    And I want fill "days" field with 2
    Then I want to get repairs dashboard
    And response code is 200
    And I see field "totalCost/current/cost" filled with 2000
    And I see field "totalCost/prev/cost" filled with 3000
    And I see field "vehicles/0/sum_cost" filled with "string(2000)"