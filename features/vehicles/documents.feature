Feature: Documents
  Scenario: I want create Vehicle Document
    Given I signed in as "admin" team "client" and teamId 2
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test group"
    And I want to create vehicle group and save id
    And I want fill vehicle group id
    And I want fill "type" field with "Car"
    And I want fill "model" field with "model"
    And I want fill "available" field with true
    And I want fill "regNo" field with "regNo"
    And I want fill "defaultLabel" field with "defaultLabel"
    And I want fill "vin" field with "vin"
    And I want fill "regCertNo" field with "regCertNo"
    And I want fill "enginePower" field with 2.0
    And I want fill "engineCapacity" field with 1.0
    And I want fill "fuelType" field with 1
    And I want fill "emissionClass" field with "emissionClass"
    And I want fill "co2Emissions" field with 0.1
    And I want fill "grossWeight" field with 0.2
    And I want fill "status" field with "online"
    Then I want to create vehicle and save id
    And response code is 200
    Then I want clean filled data
    Then I want fill vehicle id
    And I want fill "title" field with "Test Document #1"
    Then I want fill "issueDate" field with "2020-10-10T10:10:10+00:00"
    Then I want fill "expDate" field with "2020-10-10T10:10:10+00:00"
    Then I want fill "notifyBefore" field with "10"
    Then I want fill "cost" field with "10.12"
    And I want upload file
    Then I want create document
    And response code is 200
    And I see field "draft/issueDate" filled with "2020-10-10T10:10:10+00:00"
    And I see field "draft/expDate" filled with "2020-10-10T10:10:10+00:00"
    And I see field "notifyBefore" filled with 10
    And I see field "draft/cost" filled with 10.12
    And I see field "draft/note" filled with null
    And I do not see field "records/0"
    And I see field "draft/files/0/id"
    And I see field "draft/files/0/displayName" filled with "test_file.png"

  Scenario: I want create Vehicle Document with wrong data
    Given I signed in as "admin" team "client" and teamId 2
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test group"
    And I want to create vehicle group and save id
    And I want fill vehicle group id
    And I want fill "type" field with "Car"
    And I want fill "model" field with "model"
    And I want fill "available" field with true
    And I want fill "regNo" field with "regNo"
    And I want fill "defaultLabel" field with "defaultLabel"
    And I want fill "vin" field with "vin"
    And I want fill "regCertNo" field with "regCertNo"
    And I want fill "enginePower" field with 2.0
    And I want fill "engineCapacity" field with 1.0
    And I want fill "fuelType" field with 1
    And I want fill "emissionClass" field with "emissionClass"
    And I want fill "co2Emissions" field with 0.1
    And I want fill "grossWeight" field with 0.2
    And I want fill "status" field with "online"
    Then I want to create vehicle and save id
    And response code is 200
    Then I want clean filled data
    Then I want create document
    And response code is 400
    And I see field "errors/0/detail/vehicleId/required" filled with "Required field"
    And I see field "errors/0/detail/title/required" filled with "Required field"
    Then I want fill vehicle id
    And I want fill "title" field with "Test Document #1"
    Then I want create document
    And response code is 200
    And I see field "title" filled with "Test Document #1"
    And I see field "status" filled with "draft"
    And I see field "issueDate" filled with null
    And I see field "expDate" filled with null
    And I see field "notifyBefore" filled with null
    And I see field "cost" filled with null
    And I see field "note" filled with null
    And I do not see field "records/0"
    Then I want fill "issueDate" field with "invalid_date"
    Then I want fill "expDate" field with "invalid_date"
    Then I want fill "notifyBefore" field with "-1"
    Then I want fill "cost" field with "-1"
    Then I want create document
    And response code is 400
    And I see field "errors/0/detail/notifyBefore/wrong_value" filled with "Wrong value"
    Then I want fill "notifyBefore" field with "1"
    Then I want create document
    And response code is 400
    And I see field "errors/0/detail/issueDate/wrong_format" filled with "Wrong format"
    And I see field "errors/0/detail/expDate/wrong_format" filled with "Wrong format"
    And I see field "errors/0/detail/cost/wrong_value" filled with "Wrong value"

  Scenario: I want get Vehicle Document
    Given I signed in as "admin" team "client" and teamId 2
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test group"
    And I want to create vehicle group and save id
    And I want fill vehicle group id
    And I want fill "type" field with "Car"
    And I want fill "model" field with "model"
    And I want fill "available" field with true
    And I want fill "regNo" field with "regNo"
    And I want fill "defaultLabel" field with "defaultLabel"
    And I want fill "vin" field with "vin"
    And I want fill "regCertNo" field with "regCertNo"
    And I want fill "enginePower" field with 2.0
    And I want fill "engineCapacity" field with 1.0
    And I want fill "fuelType" field with 1
    And I want fill "emissionClass" field with "emissionClass"
    And I want fill "co2Emissions" field with 0.1
    And I want fill "grossWeight" field with 0.2
    And I want fill "status" field with "online"
    Then I want to create vehicle and save id
    And response code is 200
    Then I want clean filled data
    Then I want fill vehicle id
    And I want fill "title" field with "Test Document #1"
    And I want fill "issueDate" field with "2020-10-10T10:10:10+00:00"
    And I want fill "expDate" field with "2020-10-10T10:10:10+00:00"
    And I want fill "notifyBefore" field with "10"
    And I want fill "cost" field with "10.12"
    And I want upload file
    Then I want create document
    And response code is 200
    Then I want get document
    And I see field "title" filled with "Test Document #1"
    And I see field "status" filled with "draft"
    And I see field "draft/issueDate" filled with "2020-10-10T10:10:10+00:00"
    And I see field "draft/expDate" filled with "2020-10-10T10:10:10+00:00"
    And I see field "notifyBefore" filled with 10
    And I see field "draft/cost" filled with 10.12
    And I see field "draft/note" filled with null
    And I do not see field "records/0"
    And I see field "draft/files/0/id"
    And I see field "draft/files/0/displayName" filled with "test_file.png"

  Scenario: I want get Vehicle Documents list
    Given I signed in as "admin" team "client" and teamId 2
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test group"
    And I want to create vehicle group and save id
    And I want fill vehicle group id
    And I want fill "type" field with "Car"
    And I want fill "model" field with "model"
    And I want fill "available" field with true
    And I want fill "regNo" field with "regNo"
    And I want fill "defaultLabel" field with "defaultLabel"
    And I want fill "vin" field with "vin"
    And I want fill "regCertNo" field with "regCertNo"
    And I want fill "enginePower" field with 2.0
    And I want fill "engineCapacity" field with 1.0
    And I want fill "fuelType" field with 1
    And I want fill "emissionClass" field with "emissionClass"
    And I want fill "co2Emissions" field with 0.1
    And I want fill "grossWeight" field with 0.2
    And I want fill "status" field with "online"
    Then I want to create vehicle and save id
    And response code is 200
    Then I want clean filled data
    Then I want fill vehicle id
    And I want fill "title" field with "Test Document #3"
    And I want fill "issueDate" field with "2220-10-10T10:10:10+00:00"
    And I want fill "expDate" field with now
    And I want fill "notifyBefore" field with "10"
    And I want fill "cost" field with "10.12"
    And I want fill "notifyBefore" field with "999920"
    And I want upload file
    Then I want create document
    And response code is 200
    And I want upgrade document
    And response code is 200
    Then I want clean filled data
    Then I want fill vehicle id
    And I want fill "title" field with "Test Document #1"
    And I want fill "issueDate" field with "2220-10-10T10:10:10+00:00"
    And I want fill "expDate" field with "2220-10-10T10:10:10+00:00"
    And I want fill "notifyBefore" field with "10"
    And I want fill "cost" field with "10.12"
    And I want fill "notifyBefore" field with "999920"
    And I want upload file
    Then I want create document
    And I see field "draft/issueDate" filled with "2220-10-10T10:10:10+00:00"
    And I see field "draft/expDate" filled with "2220-10-10T10:10:10+00:00"
    And response code is 200
    And I want upgrade document
    Then I want fill vehicle id
    And I want fill "title" field with "Test Document #2"
    And I want fill "issueDate" field with "2220-10-11T10:10:10+00:00"
    And I want fill "expDate" field with "2220-10-11T10:10:10+00:00"
    And I want fill "notifyBefore" field with "1"
    And I want fill "cost" field with "5"
    And I want fill "note" field with "test notes"
    And I want upload file
    Then I want create document
    And I see field "draft/issueDate" filled with "2220-10-11T10:10:10+00:00"
    And I see field "draft/expDate" filled with "2220-10-11T10:10:10+00:00"
    And response code is 200
    And I want upgrade document
    Given Elastica populate
    Then I want get documents list
    And response code is 200
    And I see field "total" filled with 3
    And I see field "data/1/title" filled with "Test Document #1"
    And I see field "data/1/status" filled with "expire_soon"
    And I see field "data/1/issueDate" filled with "2220-10-10T10:10:10+00:00"
    And I see field "data/1/expDate" filled with "2220-10-10T10:10:10+00:00"
    And I see field "data/2/title" filled with "Test Document #2"
    And I see field "data/2/status" filled with "active"
    And I see field "data/2/issueDate" filled with "2220-10-11T10:10:10+00:00"
    And I see field "data/2/expDate" filled with "2220-10-11T10:10:10+00:00"
    Then I want clean filled data
    And I want add value to array key "fields" with "regNo"
    And I want add value to array key "fields" with "title"
    And I want add value to array key "fields" with "status"
    And I want add value to array key "fields" with "remainingDays"
    And I want add value to array key "fields" with "issueDate"
    Then I want export documents list
    And I see csv item number 0 field "Vehicle RegNo" filled with "regNo"
    And I see csv item number 0 field "Title" filled with "Test Document #3"
    And I see csv item number 0 field "Status" filled with "expired"
    And I see csv item number 0 field "Remaining Days" filled with "0"
    And I see csv item number 0 field "Issue Date" filled with "10/10/2220"
    And I want get full documents list
    And I see field "total" filled with 3
    Then I want clean filled data
    And I want fill array key issueDate with gte field with "2220-10-11T10:10:10+00:00"
    And I want fill array key issueDate with lte field with "2220-10-11T10:10:10+00:00"
    And I want fill array key expDate with gte field with "2220-10-11T10:10:10+00:00"
    And I want fill array key expDate with lte field with "2220-10-11T10:10:10+00:00"
    Then I want get full documents list
    And I see field "total" filled with 1
    Then I want clean filled data
    Then I want fill "remainingDays" field with "0"
    And I want get full documents list
    And I see field "total" filled with 1
    And I see field "data/0/title" filled with "Test Document #3"
    And I want add value to array key "fields" with "regNo"
    And I want add value to array key "fields" with "title"
    And I want add value to array key "fields" with "status"
    And I want add value to array key "fields" with "remainingDays"
    And I want add value to array key "fields" with "issueDate"
    Then I want export full documents list
    And I see csv item number 0 field "Vehicle RegNo" filled with "regNo"
    And I see csv item number 0 field "Title" filled with "Test Document #3"
    And I see csv item number 0 field "Status" filled with "expired"
    And I see csv item number 0 field "Remaining Days" filled with "0"
    And I see csv item number 0 field "Issue Date" filled with "10/10/2220"
    Given I signed in as "manager" team "client"
    And I want get full documents list
    And I see field "total" filled with 0

  Scenario: I want edit Document
    Given I signed in as "admin" team "client" and teamId 2
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test group"
    And I want to create vehicle group and save id
    And I want fill vehicle group id
    And I want fill "type" field with "Car"
    And I want fill "model" field with "model"
    And I want fill "available" field with true
    And I want fill "regNo" field with "regNo"
    And I want fill "defaultLabel" field with "defaultLabel"
    And I want fill "vin" field with "vin"
    And I want fill "regCertNo" field with "regCertNo"
    And I want fill "enginePower" field with 2.0
    And I want fill "engineCapacity" field with 1.0
    And I want fill "fuelType" field with 1
    And I want fill "emissionClass" field with "emissionClass"
    And I want fill "co2Emissions" field with 0.1
    And I want fill "grossWeight" field with 0.2
    And I want fill "status" field with "online"
    Then I want to create vehicle and save id
    And response code is 200
    Then I want clean filled data
    Then I want fill vehicle id
    And I want fill "title" field with "Test Document #1"
    And I want fill "issueDate" field with "2020-10-10T10:10:10+00:00"
    And I want fill "expDate" field with "2020-10-10T10:10:10+00:00"
    And I want fill "notifyBefore" field with "10"
    And I want fill "cost" field with "10.12"
    And I want upload file
    Then I want create document
    And response code is 200
    Then I want clean filled data
    And I want fill "title" field with "Updated Test Document #1"
    And I want fill "issueDate" field with "2021-10-10T10:10:10+00:00"
    And I want fill "expDate" field with "2021-10-10T10:10:10+00:00"
    And I want fill "notifyBefore" field with "20"
    And I want fill "cost" field with "10.50"
    And I want upload file
    Then I want update document
    And response code is 200
    And I see field "title" filled with "Updated Test Document #1"
    And I see field "status" filled with "draft"
    And I see field "draft/issueDate" filled with "2021-10-10T10:10:10+00:00"
    And I see field "draft/expDate" filled with "2021-10-10T10:10:10+00:00"
    And I see field "notifyBefore" filled with 20
    And I see field "draft/cost" filled with "10.50"
    And I see field "draft/note" filled with null
    And I do not see field "records/0"
    And I see field "draft/files/0/id"
    And I see field "draft/files/0/displayName" filled with "test_file.png"
    And I see field "draft/files/1/id"
    And I see field "draft/files/1/displayName" filled with "test_file.png"

  Scenario: I want upgrade Document
    And the queue associated to notification.events events producer is empty
    Given I signed in as "admin" team "client" and teamId 2
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test group"
    And I want to create vehicle group and save id
    And I want fill vehicle group id
    And I want fill "type" field with "Car"
    And I want fill "model" field with "model"
    And I want fill "available" field with true
    And I want fill "regNo" field with "regNo"
    And I want fill "defaultLabel" field with "defaultLabel"
    And I want fill "vin" field with "vin"
    And I want fill "regCertNo" field with "regCertNo"
    And I want fill "enginePower" field with 2.0
    And I want fill "engineCapacity" field with 1.0
    And I want fill "fuelType" field with 1
    And I want fill "emissionClass" field with "emissionClass"
    And I want fill "co2Emissions" field with 0.1
    And I want fill "grossWeight" field with 0.2
    And I want fill "status" field with "online"
    Then I want to create vehicle and save id
    And response code is 200
    And I want clean filled data
    Then I want fill vehicle id
    And I want fill "title" field with "Test Document #1"
    Then I want create document
    And response code is 200
    Then I want upgrade document
    And response code is 400
    And I see field "errors/0/detail/issueDate/required" filled with "Required field"
    And I see field "errors/0/detail/files/required" filled with "Required field"
    Then I want clean filled data
    And I want fill "issueDate" field with "2021-10-10T10:10:10+00:00"
    And I want upload file
    Then I want update document
    And response code is 200
    Then I want upgrade document
    And response code is 200
    And I see field "title" filled with "Test Document #1"
    And I see field "status" filled with "active"
    And I see field "draft/issueDate" filled with null
    And I see field "records/0/issueDate" filled with "2021-10-10T10:10:10+00:00"
    And I see field "records/0/files/0/displayName" filled with "test_file.png"
    Then I want get documents list
    And response code is 200
    And I see field "total" filled with 1
    And I see field "data/0/title" filled with "Test Document #1"
    And I see field "data/0/status" filled with "active"
    And I see field "data/0/issueDate" filled with "2021-10-10T10:10:10+00:00"
    And I see field "data/0/expDate" filled with null
    And I see field "data/0/remainingDays" filled with null
    Then I want clean filled data
    And I want fill "title" field with "Update#1 Test Document #1"
    And I want fill "issueDate" field with "2020-10-10T10:10:10+00:00"
    And I want set modified date "expDate" "-10 days"
    And I want fill "notifyBefore" field with "1"
    And I want fill "cost" field with "10.12"
    And I want upload file
    Then I want update document
    And response code is 200
    Then I want upgrade document
    And response code is 200
    And I see field "title" filled with "Update#1 Test Document #1"
    And I see field "status" filled with "expired"
    And I see field "draft/issueDate" filled with null
    And I see field "notifyBefore" filled with 1
    And I see field "draft/cost" filled with null
    And I see field "records/0/issueDate" filled with "2020-10-10T10:10:10+00:00"
    And I see field "records/0/cost" filled with "10.12"
    And I see field "records/0/files/0/displayName" filled with "test_file.png"
    And I see field "records/1/issueDate" filled with "2021-10-10T10:10:10+00:00"
    And I see field "records/1/files/0/displayName" filled with "test_file.png"
    Then I want get documents list
    And response code is 200
    And I see field "total" filled with 1
    And I see field "data/0/title" filled with "Update#1 Test Document #1"
    And I see field "data/0/status" filled with "expired"
    And I see field "data/0/issueDate" filled with "2020-10-10T10:10:10+00:00"
    And I see field "data/0/expDate"
    And I see field "data/0/remainingDays"
    Then I want clean filled data
    And I want fill "title" field with "Update#2 Test Document #1"
    And I want fill issueDate field with "2019-10-10T10:10:10+00:00"
    And I want set modified date "expDate" "+5 days"
    And I want fill "notifyBefore" field with "6"
    And I want fill "cost" field with "5"
    And I want upload file
    Then I want update document
    And response code is 200
    Then I want upgrade document
    And response code is 200
    And I see field "title" filled with "Update#2 Test Document #1"
    And I see field "status" filled with "expire_soon"
    And I see field "draft/issueDate" filled with null
    And I see field "notifyBefore" filled with 6
    And I see field "draft/cost" filled with null
    And I see field "records/0/issueDate" filled with "2019-10-10T10:10:10+00:00"
    And I see field "records/0/cost" filled with "5"
    And I see field "records/0/files/0/displayName" filled with "test_file.png"
    And I see field "records/1/issueDate" filled with "2020-10-10T10:10:10+00:00"
    And I see field "records/1/cost" filled with "10.12"
    And I see field "records/1/files/0/displayName" filled with "test_file.png"
    And I see field "records/2/issueDate" filled with "2021-10-10T10:10:10+00:00"
    And I see field "records/2/files/0/displayName" filled with "test_file.png"
    Then I want get documents list
    And response code is 200
    And I see field "total" filled with 1
    And I see field "data/0/title" filled with "Update#2 Test Document #1"
    And I see field "data/0/status" filled with "expire_soon"
    And I see field "data/0/issueDate" filled with "2019-10-10T10:10:10+00:00"
    And I see field "data/0/expDate"
    And I see field "data/0/remainingDays"

  Scenario: I want delete document
    And the queue associated to notification.events events producer is empty
    Given I signed in as "admin" team "client" and teamId 2
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test group"
    And I want to create vehicle group and save id
    And I want fill vehicle group id
    And I want fill "type" field with "Car"
    And I want fill "model" field with "model"
    And I want fill "available" field with true
    And I want fill "regNo" field with "regNo"
    And I want fill "defaultLabel" field with "defaultLabel"
    And I want fill "vin" field with "vin"
    And I want fill "regCertNo" field with "regCertNo"
    And I want fill "enginePower" field with 2.0
    And I want fill "engineCapacity" field with 1.0
    And I want fill "fuelType" field with 1
    And I want fill "emissionClass" field with "emissionClass"
    And I want fill "co2Emissions" field with 0.1
    And I want fill "grossWeight" field with 0.2
    And I want fill "status" field with "online"
    Then I want to create vehicle and save id
    And response code is 200
    Then I want clean filled data
    Then I want fill vehicle id
    And I want fill "title" field with "Test Document #1"
    Then I want create document
    And response code is 200
    And I want clean filled data
    Then I want delete document
    And response code is 201
    And  I want handle notification event 'DOCUMENT_DELETED' 'document' 'Test Document #1' and send messages
    Then I want get document
    And I see field "status" filled with "deleted"
    Given Elastica populate
    Then I want get documents list
    And response code is 200
    And I see field "total" filled with 0
    Then I want fill "title" field with "Update Test Document #1"
    And I want update document
    And response code is 400
    And I see field "errors/0/detail" filled with "Document deleted"
    Then I want delete document
    And response code is 400
    And I see field "errors/0/detail" filled with "Document deleted"
    Then I want upgrade document
    And response code is 400
    And I see field "errors/0/detail" filled with "Document deleted"

  Scenario: I want get documents dashboard statistic
    Given I signed in as "admin" team "client" and teamId 2
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test group"
    And I want to create vehicle group and save id
    And I want fill vehicle group id
    And I want fill "type" field with "Car"
    And I want fill "model" field with "model"
    And I want fill "available" field with true
    And I want fill "regNo" field with "regNo"
    And I want fill "defaultLabel" field with "defaultLabel"
    And I want fill "vin" field with "vin"
    And I want fill "regCertNo" field with "regCertNo"
    And I want fill "enginePower" field with 2.0
    And I want fill "engineCapacity" field with 1.0
    And I want fill "fuelType" field with 1
    And I want fill "emissionClass" field with "emissionClass"
    And I want fill "co2Emissions" field with 0.1
    And I want fill "grossWeight" field with 0.2
    And I want fill "status" field with "online"
    Then I want to create vehicle and save id
    And response code is 200
    Then I want clean filled data
    Then I want fill vehicle id
    And I want fill "title" field with "Test Document #3"
    And I want fill "issueDate" field with "2220-10-10T10:10:10+00:00"
    And I want fill "expDate" field with now
    And I want fill "notifyBefore" field with "10"
    And I want fill "cost" field with "10.12"
    And I want fill "notifyBefore" field with "999920"
    And I want upload file
    Then I want create document
    And response code is 200
    And I want upgrade document
    And response code is 200
    Then I want clean filled data
    Then I want fill vehicle id
    And I want fill "title" field with "Test Document #1"
    And I want fill "issueDate" field with "2220-10-10T10:10:10+00:00"
    And I want fill "expDate" field with "2220-10-10T10:10:10+00:00"
    And I want fill "notifyBefore" field with "10"
    And I want fill "cost" field with "10.12"
    And I want fill "notifyBefore" field with "999920"
    And I want upload file
    Then I want create document
    And I see field "draft/issueDate" filled with "2220-10-10T10:10:10+00:00"
    And I see field "draft/expDate" filled with "2220-10-10T10:10:10+00:00"
    And response code is 200
    And I want upgrade document
    Then I want fill vehicle id
    And I want fill "title" field with "Test Document #2"
    And I want fill "issueDate" field with "2220-10-11T10:10:10+00:00"
    And I want fill "expDate" field with "2220-10-11T10:10:10+00:00"
    And I want fill "notifyBefore" field with "1"
    And I want fill "cost" field with "5"
    And I want fill "note" field with "test notes"
    And I want upload file
    Then I want create document
    And I see field "draft/issueDate" filled with "2220-10-11T10:10:10+00:00"
    And I see field "draft/expDate" filled with "2220-10-11T10:10:10+00:00"
    And response code is 200
    Then I want get documents dashboard statistic
    And I see field "documents/1/status" filled with "expire_soon"
    And I see field "documents/0/status" filled with "expired"
    And I see field "expired/count" filled with 1
    And I see field "expire_soon/count" filled with 1

  Scenario: I want create Driver Document
    Given I signed in as "admin" team "client" and teamId 11
    When I want get client by name "ACME1" and save id
    And I want fill teamId by saved clientId
    And I want fill "type" field with "Car"
    And I want fill "model" field with "model"
    And I want fill "available" field with true
    And I want fill "regNo" field with "regNo"
    And I want fill "defaultLabel" field with "defaultLabel"
    And I want fill "vin" field with "vin"
    And I want fill "regCertNo" field with "regCertNo"
    And I want fill "enginePower" field with 2.0
    And I want fill "engineCapacity" field with 1.0
    And I want fill "fuelType" field with 1
    And I want fill "emissionClass" field with "emissionClass"
    And I want fill "co2Emissions" field with 0.1
    And I want fill "grossWeight" field with 0.2
    And I want fill "status" field with "online"
    Then I want to create vehicle and save id
    And response code is 200
    Then I want clean filled data
    Then I want fill driver id of current team
    And I want fill "title" field with "Test Document #1"
    Then I want fill "issueDate" field with "2020-10-10T10:10:10+00:00"
    Then I want fill "expDate" field with "2020-10-10T10:10:10+00:00"
    Then I want fill "notifyBefore" field with "10"
    Then I want fill "cost" field with "10.12"
    And I want upload file
    Then I want create document
    And response code is 200
    And I see field "draft/issueDate" filled with "2020-10-10T10:10:10+00:00"
    And I see field "draft/expDate" filled with "2020-10-10T10:10:10+00:00"
    And I see field "notifyBefore" filled with 10
    And I see field "draft/cost" filled with 10.12
    And I see field "draft/note" filled with null
    And I do not see field "records/0"
    And I see field "draft/files/0/id"
    And I see field "draft/files/0/displayName" filled with "test_file.png"

  Scenario: I want create Driver Document with wrong data
    Given I signed in as "admin" team "client" and teamId 11
    When I want get client by name "ACME1" and save id
    And I want fill teamId by saved clientId
    And I want fill "type" field with "Car"
    And I want fill "model" field with "model"
    And I want fill "available" field with true
    And I want fill "regNo" field with "regNo"
    And I want fill "defaultLabel" field with "defaultLabel"
    And I want fill "vin" field with "vin"
    And I want fill "regCertNo" field with "regCertNo"
    And I want fill "enginePower" field with 2.0
    And I want fill "engineCapacity" field with 1.0
    And I want fill "fuelType" field with 1
    And I want fill "emissionClass" field with "emissionClass"
    And I want fill "co2Emissions" field with 0.1
    And I want fill "grossWeight" field with 0.2
    And I want fill "status" field with "online"
    Then I want to create vehicle and save id
    And response code is 200
    Then I want clean filled data
    Then I want create document
    And response code is 400
    And I see field "errors/0/detail/vehicleId/required" filled with "Required field"
    And I see field "errors/0/detail/driverId/required" filled with "Required field"
    And I see field "errors/0/detail/title/required" filled with "Required field"
    Then I want fill driver id of current team
    And I want fill "title" field with "Test Document #1"
    Then I want create document
    And response code is 200
    And I see field "title" filled with "Test Document #1"
    And I see field "status" filled with "draft"
    And I see field "issueDate" filled with null
    And I see field "expDate" filled with null
    And I see field "notifyBefore" filled with null
    And I see field "cost" filled with null
    And I see field "note" filled with null
    And I do not see field "records/0"
    Then I want fill "issueDate" field with "invalid_date"
    Then I want fill "expDate" field with "invalid_date"
    Then I want fill "notifyBefore" field with "-1"
    Then I want fill "cost" field with "-1"
    Then I want create document
    And response code is 400
    And I see field "errors/0/detail/notifyBefore/wrong_value" filled with "Wrong value"
    Then I want fill "notifyBefore" field with "1"
    Then I want create document
    And response code is 400
    And I see field "errors/0/detail/issueDate/wrong_format" filled with "Wrong format"
    And I see field "errors/0/detail/expDate/wrong_format" filled with "Wrong format"
    And I see field "errors/0/detail/cost/wrong_value" filled with "Wrong value"

  Scenario: I want get Driver Document
    Given I signed in as "admin" team "client" and teamId 11
    When I want get client by name "ACME1" and save id
    And I want fill teamId by saved clientId
    And I want fill "type" field with "Car"
    And I want fill "model" field with "model"
    And I want fill "available" field with true
    And I want fill "regNo" field with "regNo"
    And I want fill "defaultLabel" field with "defaultLabel"
    And I want fill "vin" field with "vin"
    And I want fill "regCertNo" field with "regCertNo"
    And I want fill "enginePower" field with 2.0
    And I want fill "engineCapacity" field with 1.0
    And I want fill "fuelType" field with 1
    And I want fill "emissionClass" field with "emissionClass"
    And I want fill "co2Emissions" field with 0.1
    And I want fill "grossWeight" field with 0.2
    And I want fill "status" field with "online"
    Then I want to create vehicle and save id
    And response code is 200
    Then I want clean filled data
    Then I want fill driver id of current team
    And I want fill "title" field with "Test Document #1"
    And I want fill "issueDate" field with "2020-10-10T10:10:10+00:00"
    And I want fill "expDate" field with "2020-10-10T10:10:10+00:00"
    And I want fill "notifyBefore" field with "10"
    And I want fill "cost" field with "10.12"
    And I want upload file
    Then I want create document
    And response code is 200
    Then I want get document
    And I see field "title" filled with "Test Document #1"
    And I see field "status" filled with "draft"
    And I see field "draft/issueDate" filled with "2020-10-10T10:10:10+00:00"
    And I see field "draft/expDate" filled with "2020-10-10T10:10:10+00:00"
    And I see field "notifyBefore" filled with 10
    And I see field "draft/cost" filled with 10.12
    And I see field "draft/note" filled with null
    And I do not see field "records/0"
    And I see field "draft/files/0/id"
    And I see field "draft/files/0/displayName" filled with "test_file.png"

  Scenario: I want get Driver Documents list
    Given I signed in as "admin" team "client" and teamId 11
    When I want get client by name "ACME1" and save id
    And I want fill teamId by saved clientId
    And I want fill "type" field with "Car"
    And I want fill "model" field with "model"
    And I want fill "available" field with true
    And I want fill "regNo" field with "regNo"
    And I want fill "defaultLabel" field with "defaultLabel"
    And I want fill "vin" field with "vin"
    And I want fill "regCertNo" field with "regCertNo"
    And I want fill "enginePower" field with 2.0
    And I want fill "engineCapacity" field with 1.0
    And I want fill "fuelType" field with 1
    And I want fill "emissionClass" field with "emissionClass"
    And I want fill "co2Emissions" field with 0.1
    And I want fill "grossWeight" field with 0.2
    And I want fill "status" field with "online"
    Then I want to create vehicle and save id
    And response code is 200
    Then I want clean filled data
    Then I want fill driver id of current team
    And I want fill "title" field with "Test Document #3"
    And I want fill "issueDate" field with "2220-10-10T10:10:10+00:00"
    And I want fill "expDate" field with now
    And I want fill "notifyBefore" field with "10"
    And I want fill "cost" field with "10.12"
    And I want fill "notifyBefore" field with "999920"
    And I want upload file
    Then I want create document
    And response code is 200
    And I want upgrade document
    And response code is 200
    Then I want clean filled data
    Then I want fill driver id of current team
    And I want fill "title" field with "Test Document #1"
    And I want fill "issueDate" field with "2220-10-10T10:10:10+00:00"
    And I want fill "expDate" field with "2220-10-10T10:10:10+00:00"
    And I want fill "notifyBefore" field with "10"
    And I want fill "cost" field with "10.12"
    And I want fill "notifyBefore" field with "999920"
    And I want upload file
    Then I want create document
    And I see field "draft/issueDate" filled with "2220-10-10T10:10:10+00:00"
    And I see field "draft/expDate" filled with "2220-10-10T10:10:10+00:00"
    And response code is 200
    And I want upgrade document
    Then I want fill driver id of current team
    And I want fill "title" field with "Test Document #2"
    And I want fill "issueDate" field with "2220-10-11T10:10:10+00:00"
    And I want fill "expDate" field with "2220-10-11T10:10:10+00:00"
    And I want fill "notifyBefore" field with "1"
    And I want fill "cost" field with "5"
    And I want fill "note" field with "test notes"
    And I want upload file
    Then I want create document
    And I see field "draft/issueDate" filled with "2220-10-11T10:10:10+00:00"
    And I see field "draft/expDate" filled with "2220-10-11T10:10:10+00:00"
    And response code is 200
    And I want upgrade document
    Given Elastica populate
    Then I want get driver documents list
    And response code is 200
    And I see field "total" filled with 3
    And I see field "data/1/title" filled with "Test Document #1"
    And I see field "data/1/status" filled with "expire_soon"
    And I see field "data/1/issueDate" filled with "2220-10-10T10:10:10+00:00"
    And I see field "data/1/expDate" filled with "2220-10-10T10:10:10+00:00"
    And I see field "data/2/title" filled with "Test Document #2"
    And I see field "data/2/status" filled with "active"
    And I see field "data/2/issueDate" filled with "2220-10-11T10:10:10+00:00"
    And I see field "data/2/expDate" filled with "2220-10-11T10:10:10+00:00"
    Then I want clean filled data
    And I want add value to array key "fields" with "driver"
    And I want add value to array key "fields" with "title"
    And I want add value to array key "fields" with "status"
    And I want add value to array key "fields" with "remainingDays"
    And I want add value to array key "fields" with "issueDate"
    Then I want export driver documents list
    And I see csv item number 0 field "Driver" filled with "Nikki Burns"
    And I see csv item number 0 field "Title" filled with "Test Document #3"
    And I see csv item number 0 field "Status" filled with "expired"
    And I see csv item number 0 field "Remaining Days" filled with "0"
    And I see csv item number 0 field "Issue Date" filled with "10/10/2220"
    And I want get driver full documents list
    And I see field "total" filled with 3
    Then I want clean filled data
    And I want fill array key issueDate with gte field with "2220-10-11T10:10:10+00:00"
    And I want fill array key issueDate with lte field with "2220-10-11T10:10:10+00:00"
    And I want fill array key expDate with gte field with "2220-10-11T10:10:10+00:00"
    And I want fill array key expDate with lte field with "2220-10-11T10:10:10+00:00"
    Then I want get driver full documents list
    And I see field "total" filled with 1
    Then I want clean filled data
    Then I want fill "remainingDays" field with "0"
    And I want get driver full documents list
    And I see field "total" filled with 1
    And I see field "data/0/title" filled with "Test Document #3"
    And I want add value to array key "fields" with "driver"
    And I want add value to array key "fields" with "title"
    And I want add value to array key "fields" with "status"
    And I want add value to array key "fields" with "remainingDays"
    And I want add value to array key "fields" with "issueDate"
    Then I want export driver full documents list
    And I see csv item number 0 field "Driver" filled with "Nikki Burns"
    And I see csv item number 0 field "Title" filled with "Test Document #3"
    And I see csv item number 0 field "Status" filled with "expired"
    And I see csv item number 0 field "Remaining Days" filled with "0"
    And I see csv item number 0 field "Issue Date" filled with "10/10/2220"
