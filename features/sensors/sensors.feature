Feature: Sensor

  Scenario: I want to handle CRUD for sensors
    Given I signed in as "super_admin" team "admin"
    And I want get client by name "ACME1" and save id
    And I want fill teamId by saved clientId
    And I want fill "type" field with "Car"
    And I want fill "model" field with "test model"
    And I want fill "regNo" field with "regNo"
    And I want fill "defaultLabel" field with "defaultLabel"
    And I want fill "vin" field with "vin"
    And I want fill "regCertNo" field with "regCertNo"
    And I want fill "enginePower" field with 2.0
    And I want fill "engineCapacity" field with 1.0
    And I want fill "fuelType" field with 1
    And I want fill "fuelTankCapacity" field with 100
    And I want fill "emissionClass" field with "emissionClass"
    And I want fill "co2Emissions" field with 0.1
    And I want fill "grossWeight" field with 0.2
    And I want fill "status" field with "active"
    Then I want to create vehicle and save id
    And response code is 200
    And I want fill device model for vehicle with name "TLD1-DA-DE"
    And I want fill "sn" field with "test sn"
    And I want fill "status" field with "inStock"
    And I want fill "port" field with 25
    And I want fill "hw" field with "test hw"
    And I want fill "sw" field with "test sw"
    And I want fill "imei" field with "865284040731352"
    And I want fill "phone" field with "+(375) 245535454"
    And I want fill "imsi" field with "test imsi"
    And I want fill "devEui" field with "test devEui"
    And I want fill "userName" field with "test username"
    And I want fill "password" field with "test password"
    And I want fill "clientNote" field with "test clientNote"
    And I want fill "adminNote" field with "test adminNote"
    Then I want to create device for vehicle and save id
    Then I want install device for vehicle
    Then I want clean filled data
    When I want fill "sensorId" field with "123456789101"
    When I want fill "label" field with "sensor-label"
    Then I want to create temp and humidity sensor and save id
    And I see field "sensorId" filled with "string(123456789101)"
    And I see field "label" filled with "sensor-label"
    Given Elastica populate
    Then I want to get sensor by saved id
    And I see field "sensorId" filled with "string(123456789101)"
    And I see field "label" filled with "sensor-label"
    Then I want clean filled data
    When I want fill "label" field with "new-sensor-label"
    Then I want to edit sensor by saved id
    And I see field "sensorId" filled with "string(123456789101)"
    And I see field "label" filled with "new-sensor-label"
    Then I want clean filled data
    When I want to export to csv temp and humidity sensor
    And I see csv item number 0 field "Label" filled with "new-sensor-label"
    And I see csv item number 0 field "BLE ID" filled with "123456789101"
    Then I want to delete saved sensor
    And I see field "sensorId" filled with "string(123456789101)"
    And response code is 200

  Scenario: I want to handle driver sensor id permissions for Topflytech devices
    Given I signed in as "super_admin" team "admin"
    And I want get client by name "ACME1" and save id
    And I want fill teamId by saved clientId
    And I want fill "type" field with "Car"
    And I want fill "model" field with "test model"
    And I want fill "regNo" field with "regNo"
    And I want fill "defaultLabel" field with "defaultLabel"
    And I want fill "vin" field with "vin"
    And I want fill "regCertNo" field with "regCertNo"
    And I want fill "enginePower" field with 2.0
    And I want fill "engineCapacity" field with 1.0
    And I want fill "fuelType" field with 1
    And I want fill "fuelTankCapacity" field with 100
    And I want fill "emissionClass" field with "emissionClass"
    And I want fill "co2Emissions" field with 0.1
    And I want fill "grossWeight" field with 0.2
    And I want fill "status" field with "active"
    Then I want to create vehicle and save id
    And response code is 200
    And I want fill device model for vehicle with name "TLP1-SF"
    And I want fill "sn" field with "test sn"
    And I want fill "status" field with "inStock"
    And I want fill "port" field with 25
    And I want fill "hw" field with "test hw"
    And I want fill "sw" field with "test sw"
    And I want fill "imei" field with "865284040728168"
    And I want fill "phone" field with "+(375) 245535454"
    And I want fill "imsi" field with "test imsi"
    And I want fill "devEui" field with "test devEui"
    And I want fill "userName" field with "test username"
    And I want fill "password" field with "test password"
    And I want fill "clientNote" field with "test clientNote"
    And I want fill "adminNote" field with "test adminNote"
    Then I want to create device for vehicle and save id
    Then I want install device for vehicle
    And I want fill "name" field with "client driver name"
    And I want fill "surname" field with "client driver surname"
    And I want fill "phone" field with "+46 (87)4646753"
    And I want fill "email" field with "driver@example.com"
    Then I want to register driver for current client
    And I see field "email" filled with "driver@example.com"
    When I want fill "driverSensorId" field with "01B3EC0011B2"
    Then I want to update current client's user by email "driver@example.com"
    Given Elastica populate
    Then I want clean filled data
    When I want to get sensor list
    And I see field "total" filled with "1"
    And I see field "data/0/sensorId" filled with "01B3EC0011B2"
    When I want add value to array key "fields" with "trackerCommands"
    And I want fill "imei" field with "865284040728168"
    Then I want fill "regNo" field with "regNo"
    And I want get vehicle list
    And I see field "data/0/driver" filled with "null"
    Given There are following tracker payload from topflytech tracker with socket "test-topflytech-test-socket":
      | payload                                                                                                                                                                                                                                                              |
      | 272703000f02ea0865284040728168                                                                                                                                                                                                                         |
      | 27271000270001086528404072816820111200006001000301B3EC0011B23100C858866B4276D6E342912AB44111150505   |
    Then I want fill "regNo" field with "regNo"
    And I want get vehicle list
    And I see field "data/0/driver/email" filled with "driver@example.com"