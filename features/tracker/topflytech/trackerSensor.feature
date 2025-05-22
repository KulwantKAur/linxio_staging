Feature: Tracker Sensor Topflytech

  Scenario: I want to handle command generation for driver sensor id update
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
    And I want fill "imei" field with "865284042786776"
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
    Then I want to set vehicle driver by user email "driver@example.com" and date "2020-10-20 08:19:38"
    Given Elastica populate
    When I want fill "driverSensorId" field with "01B3EC0011C0"
    Then I want to update current client's user by email "driver@example.com"
    Given Elastica populate
    Then I want clean filled data
    When I want add value to array key "fields" with "trackerCommands"
    And I want fill "imei" field with "865284042786776"
    When I want get device list
    # BLEIDA,01:B3:EC:00:11:C0,7#
    And I see field "data/0/trackerCommands/0/command" filled with "27278100100001086528404278677601424c454944412c30313a42333a45433a30303a31313a43302c3723"

  Scenario: I want to reassign driver during sensor BLE id receiving
    Given I signed in as "super_admin" team "admin"
    And I want get client by name "ACME1" and save id
    And I want fill teamId by saved clientId
    And I want fill "type" field with "Car"
    And I want fill "model" field with "test model"
    And I want fill "regNo" field with "regNoForSensorId"
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
    And I want fill "name" field with "client driver name"
    And I want fill "surname" field with "client driver surname"
    And I want fill "phone" field with "+46 (87)4646753"
    And I want fill "email" field with "driver@example.com"
    Then I want to register driver for current client
    And I see field "email" filled with "driver@example.com"
    When I want fill "driverSensorId" field with "FFFF9602E6C1"
    Then I want to update current client's user by email "driver@example.com"
    Given Elastica populate
    Given There are following tracker payload from topflytech tracker with socket "test-topflytech-test-socket":
      | payload                                                                                                                                                                                                                                                              |
      | 262603000f02ea0865284040731352                                                                                                                                                                                                                         |
      | 262610003100010865284040731352210201042835010003ffff9602e6c129000081f9000107f7c40c24c2002924c20029                                                                                                                                                     |
    Given Elastica populate
    Then I want clean filled data
    And I want fill "regNo" field with "regNoForSensorId"
    When I want get vehicle list
    And I see field "data/0/regNo" filled with "regNoForSensorId"
    And I see field "data/0/driver/email" filled with "driver@example.com"

  Scenario: I want to handle temp and humidity sensors
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
    Given There are following tracker payload from topflytech tracker with socket "test-topflytech-test-socket":
      | payload                                                                                                                                                                                                                                                              |
      | 262603000f02ea0865284040731352                                                                                                                                                                                                                         |
      | 262610003600130865284040731352201217050123010004d497ae41f7dd00640d080ce4000148e3fa78d738b600640ccd17d400004a   |
    Given Elastica populate
    Then I want clean filled data
    When I want to get device sensor list by saved device
    And I see field "total" filled with "2"
    And I see field "data/0/sensor/sensorId" filled with "d497ae41f7dd"
    And I see field "data/0/sensor/label" filled with "null"
    And I see field "data/0/sensor/type/name" filled with "TOPFLYTECH_TEMP_AND_HUMIDITY_TYPE"
    And I see field "data/0/lastTrackerHistorySensor/occurredAt" filled with "2020-12-17T05:01:23+00:00"
    And I see field "data/0/isAutoCreated" filled with "true"
    And I see field "data/0/sensor/isAutoCreated" filled with "true"
    And I see field "data/1/sensor/sensorId" filled with "e3fa78d738b6"
    And I see field "data/1/isAutoCreated" filled with "true"
    Then I want clean filled data
    And I want fill "startDate" field with "2020-12-17T05:01:23+00:00"
    And I want fill "endDate" field with "2020-12-18T05:01:23+00:00"
    When I want to get device sensor history by saved sensor
    And I see field "total" filled with "1"
    And I see field "data/0/batteryPercentage" filled with "100"
    And I see field "data/0/value" filled with "33.36°C, 33%, 0"
    And I see field "data/0/occurredAt" filled with "2020-12-17T05:01:23+00:00"
    And I see field "data/0/lastPosition" filled with "null"
    Then I want clean filled data
    And I want fill "startDate" field with "2020-12-17T05:01:23+00:00"
    And I want fill "endDate" field with "2020-12-18T05:01:23+00:00"
    When I want to get device sensor history by saved device
    And I see field "total" filled with "2"
    And I see field "data/0/trackerHistoriesSensor/0/batteryPercentage" filled with "100"
    And I see field "data/0/trackerHistoriesSensor/0/value" filled with "33.36°C, 33%, 0"
    And I see field "data/0/trackerHistoriesSensor/0/occurredAt" filled with "2020-12-17T05:01:23+00:00"
    And I see field "data/0/trackerHistoriesSensor/0/lastPosition" filled with "null"
    Then I want to export to csv saved device temp and humidity sensor history
    And I see csv item number 0 field "Sensor BLE ID" filled with "d497ae41f7dd"
    And I see csv item number 0 field "Sensor Label" filled with ""
    And I see csv item number 0 field "Occurred Date" filled with "17/12/2020 16:01"
    And I see csv item number 0 field "Temperature" filled with "33.36"
    And I see csv item number 0 field "Humidity" filled with "33"
    And I see csv item number 0 field "Value" filled with "33.36°C, 33%, 0"
    And I see csv item number 0 field "Battery Percentage" filled with "100"
    And I see csv item number 0 field "Position" filled with ""

  Scenario: I want to handle command generation for temp and humidity sensor update
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
    Then I want to create device temp and humidity sensor by saved device and save id
    Given Elastica populate
    When I want fill "sensorId" field with "123456789102"
    Then I want to edit device sensor by saved sensor
    Then I want clean filled data
    When I want add value to array key "fields" with "trackerCommands"
    And I want fill "imei" field with "865284040731352"
    When I want get device list
    # BTIDA,12:34:56:78:91:01,1#
    And I see field "data/0/trackerCommands/0/command" filled with "2626810010000108652840407313520142544944412c31323a33343a35363a37383a39313a30312c3123"
    # BTIDD,12:34:56:78:91:01#
    And I see field "data/0/trackerCommands/1/command" filled with "2626810010000108652840407313520142544944442c31323a33343a35363a37383a39313a303123"
    # BTIDA,12:34:56:78:91:02,1#
    And I see field "data/0/trackerCommands/2/command" filled with "2626810010000108652840407313520142544944412c31323a33343a35363a37383a39313a30322c3123"

  # @todo add mock or replace `emSlave` -> `em` everywhere in code
  Scenario: I want to handle reports endpoints
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
    Given There are following tracker payload from topflytech tracker with socket "test-topflytech-test-socket":
      | payload                                                                                                                                                                                                                                                              |
      | 262603000f02ea0865284040731352                                                                                                                                                                                                                         |
      | 262610003600130865284040731352201217050123010004d497ae41f7dd00640d080ce4000148e3fa78d738b600640ccd17d400004a   |
      | 262610003600130865284040731352201217050124010004d497ae41f7dd00640d080ce4000148e3fa78d738b600640ccd17d400004a   |
    Given Elastica populate
    Then I want clean filled data
    And I want fill "startDate" field with "2020-12-17T05:01:23+00:00"
    And I want fill "endDate" field with "2020-12-18T05:01:23+00:00"
    Then I want to get vehicle list for report temp and humidity sensor
    And I see field "data/0/regNo" filled with "regNo"
    And I see field "total" filled with "1"
    # @todo can't test this endpoint because of test-db function for `vehicle.model`
#    Then I want clean filled data
#    And I want to fill params with json: '{"startDate": "2020-12-17T05:01:23+00:00", "endDate": "2020-12-18T05:01:23+00:00"}'
#    Then I want to export report temp and humidity sensor to csv by vehicle
#    And I see csv item number 0 field "Sensor BLE ID" filled with "d497ae41f7dd"
#    And I see csv item number 0 field "Sensor Label" filled with ""
#    And I see csv item number 0 field "Occurred Date" filled with "17/12/2020 16:01"
#    And I see csv item number 0 field "Temperature" filled with "33.36"
#    And I see csv item number 0 field "Humidity" filled with "33"
#    And I see csv item number 0 field "Battery Voltage" filled with "2000"
#    And I see csv item number 0 field "Battery Percentage" filled with "100"
#    And I see csv item number 0 field "Position" filled with ""
    Then I want clean filled data
    And I want fill "startDate" field with "2020-12-17T05:01:23+00:00"
    And I want fill "endDate" field with "2020-12-18T05:01:23+00:00"
    Then I want to get sensor list for report temp and humidity sensor
    And I see field "data/0/sensorId" filled with "d497ae41f7dd"
    And I see field "data/0/label" filled with "null"
    And I see field "data/0/type/name" filled with "TOPFLYTECH_TEMP_AND_HUMIDITY_TYPE"
    Then I want clean filled data
    And I want to fill params with json: '{"startDate": "2020-12-17T05:01:23+00:00", "endDate": "2020-12-18T05:01:23+00:00"}'
    Then I want to export report temp and humidity sensor to csv by sensor
    And I see csv item number 0 field "Sensor BLE ID" filled with "d497ae41f7dd"
    And I see csv item number 0 field "Sensor Label" filled with ""
    And I see csv item number 0 field "Occurred Date" filled with "17/12/2020 16:01"
    And I see csv item number 0 field "Temperature" filled with "33.36"
    And I see csv item number 0 field "Humidity" filled with "33"
    And I see csv item number 0 field "Battery Percentage" filled with "100"
    And I see csv item number 0 field "Position" filled with ""