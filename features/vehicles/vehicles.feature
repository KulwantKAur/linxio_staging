Feature: Vehicles

  Scenario: I want add vehicle
    Given I signed in as "super_admin" team "admin"
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "type" field with "Car"
    And I want fill "makeModel" field with "model"
    And I want fill "make" field with "make"
    And I want fill "isUnavailable" field with true
    And I want fill "regNo" field with "regNo"
    And I want fill "defaultLabel" field with "defaultLabel"
    And I want fill "vin" field with "vin"
    And I want fill "regCertNo" field with "regCertNo"
    And I want fill "enginePower" field with 2.0
    And I want fill "engineCapacity" field with 1.0
    And I want fill "emissionClass" field with "emissionClass"
    And I want fill "co2Emissions" field with 0.1
    And I want fill "grossWeight" field with 0.2
    And I want fill "status" field with "active"
    And I want fill "fuelType" field with 1
    And I want fill "clientNote" field with "test clientNote"
    And I want fill "adminNote" field with "test adminNote"
    And I want fill "averageFuel" field with "33.4"
    And I want upload picture
    Then I want to create vehicle and save id
    And response code is 200
    And I see field "team/type" filled with "client"
    And I see field "type" filled with "Car"
    And I see field "regNo" filled with "regNo"
    And I see field "defaultLabel" filled with "defaultLabel"
    And I see field "vin" filled with "vin"
    And I see field "regCertNo" filled with "regCertNo"
    And I see field "enginePower" filled with 2.0
    And I see field "engineCapacity" filled with 1.0
    And I see field "fuelType" filled with 1
    And I see field "emissionClass" filled with "emissionClass"
    And I see field "co2Emissions" filled with 0.1
    And I see field "grossWeight" filled with 0.2
    And I see field "status" filled with "unavailable"
    And I see field "averageFuel" filled with 33.4
    And I want check "picture" is not empty
    Then I want to get vehicle by saved id
    And response code is 200
    And I see field "team/type" filled with "client"
    And I see field "type" filled with "Car"
    And I see field "regNo" filled with "regNo"
    And I see field "defaultLabel" filled with "defaultLabel"
    And I see field "vin" filled with "vin"
    And I see field "regCertNo" filled with "regCertNo"
    And I see field "enginePower" filled with 2.0
    And I see field "engineCapacity" filled with 1.0
    And I see field "fuelType" filled with 1
    And I see field "emissionClass" filled with "emissionClass"
    And I see field "co2Emissions" filled with 0.1
    And I see field "grossWeight" filled with 0.2
    And I see field "averageFuel" filled with 33.4
    Then I want to get vehicle notes by saved id and type "client"
    And I see field "0/note" filled with "test clientNote"
    Then I want to get vehicle notes by saved id and type "admin"
    And I see field "0/note" filled with "test adminNote"
    # check installer permission
    Given I signed in as "installer" team "admin"
    And I want fill teamId by saved clientId
    And I want fill "regNo" field with "test reg no"
    And I want fill "type" field with "Car"
    And I want fill "makeModel" field with "model"
    And I want fill "make" field with "make"
    And I want fill "status" field with "active"
    And I want fill "vin" field with "vin2"
    Then I want to create vehicle and save id
    And response code is 200
    Then I want to create vehicle and save id
    And response code is 400

  Scenario: I want get vehicle list
    Given I signed in as "super_admin" team "admin"
    Then I want fill "model" field with 2
    Then I want fill "type" field with "Car"
    Then I want fill "vin" field with 4
    Then I want fill "fuelType" field with 1
    Then I want fill "year" field with "2000"
    Then I want fill "regDate" field with "2002-08-13T11:09:12+00:00"
    And I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    Then I want to create vehicle and save id
    And I see field "team/type" filled with "client"
    And I see field "regDate" filled with "2002-08-13T11:09:12+00:00"
    And I want get client by name "client-name-1" and save id
    And I want fill teamId by saved clientId
    Then I want to create vehicle and save id
    And I see field "team/type" filled with "client"
    Given Elastica populate
    And I want clean filled data
    When  I want get vehicle list
    Then response code is 200
    And I see field "total" filled with 48
    Then I want fill teamId by saved clientId
    And  I want get vehicle list
    And I see field "total" filled with 1
    Then response code is 200
    And I want clean filled data
    And  I want get vehicle list
    And I see field "total" filled with 48
    And I want clean filled data
    Then I want fill model field with 2
    And  I want get vehicle list
    And I see field "total" filled with 1
    And I want clean filled data
    Then I want fill type field with 3
    And  I want get vehicle list
    And I see field "total" filled with 1
    And I want clean filled data
    Then I want fill vin field with 4
    And  I want get vehicle list
    And I see field "total" filled with 1
    And I want clean filled data
    And I want fill "fuelType" field with 2
    And  I want get vehicle list
    And I see field "total" filled with 1
    And I want clean filled data
    Then I want fill year field with 2000
    And  I want get vehicle list
    And I see field "total" filled with 1
    And I want clean filled data
    Then I want fill client field with "client-name-1"
    And  I want get vehicle list
    And I see field "total" filled with 1
    And I want clean filled data
    And I want fill "model" field with "Toyota Hilux"
    And I want fill "fullSearch" field with "Frenchams QLD"
    When  I want get vehicle list
    And I see field "total" filled with 2
    And I want clean filled data
    And I want fill array key "regDate" with "gte" field with "2002-08-13T11:09:12+00:00"
    And I want fill array key "regDate" with "lte" field with "2002-08-13T11:09:12+00:00"
    When  I want get vehicle list
    And I see field "total" filled with 1

  Scenario: I want edit and delete vehicle
    And the queue associated to notification.events events producer is empty
    Given I signed in as "super_admin" team "admin"
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test group"
    And I want to create vehicle group and save id
    And I want fill vehicle group id
    And I want fill "type" field with "Car"
    And I want fill "model" field with "model"
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
    And I want fill "status" field with "active"
    And I want fill "averageFuel" field with 33.4
    Then I want to create vehicle and save id
    And response code is 200
    And I see field "team/type" filled with "client"
    And I want clean filled data
    When I want get client by name "client-name-1" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test group1"
    And I want to create vehicle group and save id
    And I want fill vehicle group id
    And I want fill "type" field with "Car"
    And I want fill "model" field with "model2"
    And I want fill "regNo" field with "regNo2"
    And I want fill "defaultLabel" field with "defaultLabel2"
    And I want fill "vin" field with "vin2"
    And I want fill "regCertNo" field with "regCertNo2"
    And I want fill "enginePower" field with 3.0
    And I want fill "engineCapacity" field with 2.0
    And I want fill "fuelType" field with 1
    And I want fill "emissionClass" field with "emissionClass2"
    And I want fill "co2Emissions" field with 0.2222
    And I want fill "grossWeight" field with 0.3333
    And I want fill "status" field with "inactive"
    And I want fill "averageFuel" field with 22.2
    And I want upload picture
    Then I want to edit vehicle by saved id
    And response code is 200
    And I see field "team/type" filled with "client"
    And I see field "type" filled with "type2"
    And I see field "regNo" filled with "regNo2"
    And I see field "defaultLabel" filled with "defaultLabel2"
    And I see field "vin" filled with "vin2"
    And I see field "regCertNo" filled with "regCertNo2"
    And I see field "enginePower" filled with 3.0
    And I see field "engineCapacity" filled with 2.0
    And I see field "fuelType" filled with 1
    And I see field "emissionClass" filled with "emissionClass2"
    And I see field "co2Emissions" filled with 0.2222
    And I see field "grossWeight" filled with 0.3333
    And I see field "averageFuel" filled with 22.2
    And I see field "status" filled with "inactive"
    And I want check "picture" is not empty
    Then I want clean filled data
    And I want fill teamId by saved clientId
    And I want fill "isUnavailable" field with true
    And I want fill "unavailableMessage" field with "testMessage"
    Then I want to edit vehicle by saved id
    And I see field "status" filled with "unavailable"
    And I see field "unavailableMessage" filled with "testMessage"
    Then I want clean filled data
    And I want fill teamId by saved clientId
    And I want fill "isUnavailable" field with false
    Then I want to edit vehicle by saved id
    And I see field "status" filled with "inactive"
    When I want fill device model for vehicle with name "FM3001"
    And I want fill "sn" field with "test sn"
    And I want fill "status" field with "inStock"
    And I want fill "port" field with 25
    And I want fill "hw" field with "test hw"
    And I want fill "sw" field with "test sw"
    And I want fill "imei" field with "test imei"
    And I want fill "phone" field with "+(375) 245535454"
    And I want fill "imsi" field with "test imsi"
    And I want fill "devEui" field with "test devEui"
    And I want fill "userName" field with "test username"
    And I want fill "password" field with "test password"
    Then I want to create device for vehicle and save id
    Then I want install device for vehicle
    Then I want clean filled data
    Then I want to delete vehicle by saved id
    And response code is 204
    When I want to get vehicle by saved id
    And I see field "status" filled with "deleted"
    And I do not see field "device"
    When I want to get device of vehicle by saved id
    And I see field "status" filled with "inStock"

  Scenario: I want check permissions
    Given I signed in as "super_admin" team "admin"
    And I want fill "email" field with "client_manager_user@gmail.test"
    And I want fill "name" field with "Alex"
    And I want fill "phone" field with "+6(4)654654"
    And I want fill "roleId" field with "3"
    And I want fill "allTeamsPermissions" field with "false"
    Then I want register client manager user and remember id
    And I see field "email" filled with "client_manager_user@gmail.test"
    Then I want clean filled data
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "model" field with "model"
    And I want fill "type" field with "Car"
    Then I want to create vehicle and save id
    And response code is 200
    And I see field "team/type" filled with "client"
    And I want clean filled data
    Then I signed with email "client_manager_user@gmail.test"
    And I want fill "model" field with "model2"
    Then I want to edit vehicle by saved id
    And response code is 400
    And I want fill teamId by saved clientId
    Then I want to create vehicle and save id
    And response code is 400
    Then I signed with email "super_admin@user.com"
    And I want fill "name" field with "Alex"
    Then I want register client with manager and remember
    And I see field "id"
    And I want clean filled data
    Then I signed with email "client_manager_user@gmail.test"
    And I want fill "model" field with "model2"
    And I want fill teamId by saved clientId
    Then I want to create vehicle and save id
    And response code is 200
    And I want fill "model" field with "model3"
    Then I want to edit vehicle by saved id
    And I see field "model" filled with "model3"
    And response code is 200
    Then I signed in as "admin" team "client"
    And I want fill teamId by saved clientId
    Then I want to create vehicle and save id
    And response code is 200

  Scenario: I want export vehicle list
    Given I signed in as "admin" team "admin"
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "type" field with "Car"
    And I want fill "model" field with "model"
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
    And I want fill "status" field with "online"
    Then I want to create vehicle and save id
    And I see field "id"
    And response code is 200
    Given Elastica populate
    And I want clean filled data
    Then I want add value to array key "fields" with "model"
    And I want add value to array key "fields" with "type"
    And I want add value to array key "fields" with "defaultLabel"
    And I want add value to array key "fields" with "year"
    And I want add value to array key "fields" with "vin"
    And I want add value to array key "fields" with "regNo"
    And I want add value to array key "fields" with "status"
    And I want add value to array key "fields" with "fuelType"
    And I want add value to array key "fields" with "fuelTankCapacity"
    And I want add value to array key "fields" with "id"
    And I want fill "limit" field with "100"
    Then I want export vehicles list
    And I see csv item number 46 field "Model" filled with "model"
    And I see csv item number 46 field "Type" filled with "type"
    And I see csv item number 46 field "Vehicle Title" filled with "defaultLabel"
    And I see csv item number 46 field "Status" filled with "online"
    And I see csv item number 46 field "VIN" filled with "vin"
    And I see csv item number 46 field "Vehicle reg. No." filled with "regNo"
    And I see csv item number 46 field "Fuel Type" filled with "Diesel"
    And I see csv item number 46 field "Fuel Tank Capacity" filled with 100
    And response code is 200

  Scenario: I want set driver to vehicle
    Given I signed in as "admin" team "client" and teamId 2
    And I want get client by name "client-name-0" and save id
    And I want to fill teamId by team of current user
    And I want fill "type" field with "Car"
    And I want fill "model" field with "model"
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
    Then I want set vehicle driver with current user
    And I see field "driver/email" filled with "admin@user.com"
    And response code is 200
    Then I want unset vehicle driver with current user
    And I see field "driver" filled with null
    And response code is 200
    Then I want set vehicle driver with current user
    And I want fill "model" field with "model#1"
    And I want fill "regNo" field with "regNo1"
    And I want fill "vin" field with "vin2"
    Then I want to create vehicle and save id
    And response code is 200
    Then I want set vehicle driver with current user and date "2010-12-22T11:00:04+03:00"
    Then I want add value to array key "fields" with "driverHistory"
    Given Elastica populate
    Then I want to get vehicle by model 'model#1'
    And I see field "driver/email" filled with "admin@user.com"
    Then I want to get vehicle by model 'model'
    And I see field "driver" filled with null
    Then I want set vehicle driver with current user and date "2000-12-22T11:00:04+03:00"
    And response code is 200

  Scenario: I want get vehicle list with tracker data
    Given I signed in as "super_admin" team "admin"
    Then I want fill "model" field with 2
    Then I want fill "type" field with "Car"
    Then I want fill "vin" field with 4
    And I want fill "fuelType" field with 1
    Then I want fill "year" field with "2000"
    And I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    Then I want to create vehicle and save id
    And I see field "team/type" filled with "client"
    And I want clean filled data
    And I want get client by name "client-name-1" and save id
    And I want fill teamId by saved clientId
    Then I want to create vehicle and save id
    And I see field "team/type" filled with "client"
    And I want fill device model for vehicle with name "FM3001"
    And I want fill "sn" field with "test sn"
    And I want fill "status" field with "inStock"
    And I want fill "port" field with 25
    And I want fill "hw" field with "test hw"
    And I want fill "sw" field with "test sw"
    And I want fill "imei" field with "test imei"
    And I want fill "phone" field with "+(375) 245535454"
    And I want fill "imsi" field with "test imsi"
    And I want fill "devEui" field with "test devEui"
    And I want fill "userName" field with "test username"
    And I want fill "password" field with "test password"
    And I want fill "clientNote" field with "test clientNote"
    And I want fill "adminNote" field with "test adminNote"
    Then I want to create device for vehicle and save id
    Then I want install device for vehicle
    Given Elastica populate
    Then I want clean filled data
    When I want add value to array key "fields" with "device"
    Then I want get vehicle list
    Then response code is 200
    And I see field "total" filled with 48
    Then I want fill teamId by saved clientId
    And I want get vehicle list
    And I see field "data/0/device/trackerData" filled with "null"
    And I see field "total" filled with 1
    Then response code is 200

  Scenario: I want to get one vehicle with tracker data
    Given I signed in as "super_admin" team "admin"
    Then I want fill "model" field with 2
    Then I want fill "type" field with "Car"
    Then I want fill "vin" field with 4
    Then I want fill "fuelType" field with 1
    Then I want fill "year" field with "2000"
    And I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    Then I want to create vehicle and save id
    And I see field "team/type" filled with "client"
    And I want fill device model for vehicle with name "FM3001"
    And I want fill "sn" field with "test sn"
    And I want fill "status" field with "inStock"
    And I want fill "port" field with 25
    And I want fill "hw" field with "test hw"
    And I want fill "sw" field with "test sw"
    And I want fill "imei" field with "888888888888888"
    And I want fill "phone" field with "+(375) 245535454"
    And I want fill "imsi" field with "test imsi"
    And I want fill "devEui" field with "test devEui"
    And I want fill "userName" field with "test username"
    And I want fill "password" field with "test password"
    And I want fill "clientNote" field with "test clientNote"
    And I want fill "adminNote" field with "test adminNote"
    Then I want to create device for vehicle and save id
    Then I want install device for vehicle
    Given Elastica populate
    When I want fill "payload" field with "000F383838383838383838383838383838"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response"
    And I see field "imei"
    When I want fill "payload" field with "00000000000004d608130000016b0a18fff8001073a68d201e100b00c801610e0000f00c05ef00f0001504c800450105b50006b6000442328f43000044000002f100006465100000274c000000016b0a1b1338001073a68d201e100b00c801610d0000f00c05ef00f0011504c800450105b50005b6000342322643000044000002f100006465100000274c000000016b0a1b3a48001073a68d201e100b00c801610d0000000c05ef00f0011504c800450105b50006b6000342322443000044000002f100006465100000274c000000016b0a1b6158001073a68d201e100b00c801610e0000000c05ef00f0011504c800450105b50006b6000342322643000044000002f100006465100000274c000000016b0a1b8868001073a68d201e100b00c801610e0000000c05ef00f0011504c800450105b50006b6000342322843000044000002f100006465100000274c000000016b0a1baf78001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342317c43000044000002f100006465100000274c000000016b0a1bd688001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b600034230ae43000044000002f100006465100000274c000000016b0a1bfd98001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50005b6000342307443000044000002f100006465100000274c000000016b0a1c0180001073a68d201e100b00c801610f0000f00c05ef00f0001504c800450105b50006b6000342307543000044000002f100006465100000274c000000016b0a1c1508001073a68d201e100b00c801610f0000f00c05ef00f0011504c800450105b50006b6000342304a43000044000002f100006465100000274c000000016b0a1c3c18001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342311443000044000002f100006465100000274c000000016b0a1c6328001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b600034231a343000044000002f100006465100000274c000000016b0a1c8a38001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b600034231f043000044000002f100006465100000274c000000016b0a1cb148001073a68d201e100b00c80161100000000c05ef00f0011504c800450105b50005b600034231fa43000044000002f100006465100000274c000000016b0a1cd858001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342322343000044000002f100006465100000274c000000016b0a1cff68001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342322643000044000002f100006465100000274c000000016b0a1d2678001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342322843000044000002f100006465100000274c000000016b0a1d4d88001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342322843000044000002f100006465100000274c000000016b0a1d7498001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b600034231ad43000044000002f100006465100000274c0013000015d1"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Given Elastica populate
    When I want add value to array key "fields" with "device"
    And I want add value to array key "fields" with "lastStatusDuration"
    Then I want to get vehicle by saved id
    And I see field "team/type" filled with "client"
    And I see field "device/trackerData/lastCoordinates/lat" filled with "custom(53.88410990)"
    And I see field "device/trackerData/lastCoordinates/lng" filled with "custom(27.60147330)"
    And I see field "device/trackerData/temperatureLevel" filled with "null"
    And I see field "device/trackerData/mileage" filled with "10060"
    And I see field "device/trackerData/engineHours" filled with "null"
    And I see field "device/trackerData/batteryVoltage" filled with 0
    And I see field "device/trackerData/externalVoltage" filled with "12717"
    And I see field "device/trackerData/gpsStatus" filled with "true"
    And I see field "device/trackerData/lastDataReceived"
    And I see field "device/trackerData/standsIgnition" filled with "false"
    And I see field "device/trackerData/speed" filled with "0"
    And I see field "device/trackerData/angle" filled with "353"
    And I see field "device/trackerData/iButton" filled with "null"
    And I see field "averageDailyMileage" filled with "null"
    And I see field "lastStatusDuration" filled with "null"
    Then response code is 200
    Then I want clean filled data
    And I want fill "mileage" field with 10060
    And I want add value to array key "fields" with "device"
    And I want get vehicle list
    And I see field "total" filled with 1
    And I see field "data/0/device/trackerData/mileage" filled with 10060

  Scenario: I want get driver history
    Given I signed in as "super_admin" team "admin"
    And I want to fill teamId by team of current user
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
    Then I want set vehicle driver with current user
    And I see field "driver/email" filled with "super_admin@user.com"
    And response code is 200
    Then I want get vehicle history for driver "super_admin@user.com"
    And I see field "0/model" filled with "test model"
    And I see field "0/fullName" filled with "test user surname"
    Then I want unset vehicle driver with current user
    And I want fill "model" field with "test model #1"
    And I want fill "regNo" field with "regNo1"
    And I want fill "vin" field with "vin2"
    Then I want to create vehicle and save id
    And response code is 200
    Then I want set vehicle driver with current user
    Then I want get vehicle history for driver "super_admin@user.com"
    And I see field "0/model" filled with "test model #1"
    And I see field "0/fullName" filled with "test user surname"
    And I see field "1/model" filled with "test model"
    And I see field "1/fullName" filled with "test user surname"
    Then I want unset vehicle driver with current user
    Then I want set vehicle driver with current user
    Then I want get vehicle history for driver "super_admin@user.com"
    And I see field "0/model" filled with "test model #1"
    And I see field "0/fullName" filled with "test user surname"
    And I see field "1/model" filled with "test model"
    And I see field "1/fullName" filled with "test user surname"

  Scenario: I want to get a vehicle summary list
    Given I signed in as "super_admin" team "admin"
    And I want fill device model for vehicle with name "FM3001"
    And I want fill "sn" field with "test sn"
    And I want fill "status" field with "inStock"
    And I want fill "port" field with 25
    And I want fill "hw" field with "test hw"
    And I want fill "sw" field with "test sw"
    And I want fill "imei" field with "888888888888888"
    And I want fill "phone" field with "+(375) 245535454"
    And I want fill "imsi" field with "test imsi"
    And I want fill "devEui" field with "test devEui"
    And I want fill "userName" field with "test username"
    And I want fill "password" field with "test password"
    And I want fill "clientNote" field with "test clientNote"
    And I want fill "adminNote" field with "test adminNote"
    And I want to create device for vehicle and save id
    And I want clean filled data
    Given I signed in as "manager" team "client"
    And I want to fill teamId by team of current user
    And I want fill "model" field with "Mercedes"
    And I want fill "regNo" field with "B1234567890"
    And I want fill "type" field with "Car"
    And I want fill "vin" field with "1G6AR5S38E0134828"
    And I want fill "defaultLabel" field with "Jenny"
    And I want fill "fuelType" field with 1
    And I want fill "year" field with "2000"
    And I want to create vehicle and save id
    Given I signed in as "super_admin" team "admin"
    And I want install device for vehicle
    And Elastica populate
    And I want fill "payload" field with "000F383838383838383838383838383838"
    And I want to send teltonika tcp data to api with socket "test-socket-id"
    And I see field "response"
    And I see field "imei"
    And I want fill "payload" field with "00000000000004d608130000016b0a4c44f800106e9e02201c473600ca01070d0046000c05ef01f0011505c800450105b50006b6000542379e43000044000002f1000064651000003986000000016b0a4c4cc800106e8682201c456300cb01070e0044000c05ef01f0011505c800450105b50006b600054237a143000044000002f10000646510000039af000000016b0a4c549800106e703e201c43e400cc0108100046000c05ef01f0011505c800450105b50007b6000442379743000044000002f10000646510000039d5000000016b0a4c5c6800106e5b36201c402e00d10107110047000c05ef01f0011505c800450105b50006b6000342378043000044000002f10000646510000039fb000000016b0a4c643800106e449f201c3d1e00d50106110048000c05ef01f0011505c800450105b50006b6000342378743000044000002f1000064651000003a20000000016b0a4c6c0800106e2c24201c3bf200d50107100048000c05ef01f0011505c800450105b50006b6000342375643000044000002f1000064651000003a49000000016b0a4c73d800106e1345201c3ae800d401090f0049000c05ef01f0011505c800450105b50006b6000442374e43000044000002f1000064651000003a73000000016b0a4c7ba800106df62c201c3e2900d00109100049000c05ef01f0011505c800450105b50007b6000442376543000044000002f1000064651000003a9d000000016b0a4c837800106ddfc6201c3c2400cd010a0f0048000c05ef01f0011505c800450105b50007b6000442378343000044000002f1000064651000003ace000000016b0a4c8b4800106dc8ec201c3b5c00cd010a0f0046000c05ef01f0011505c800450105b50007b6000442378243000044000002f1000064651000003af4000000016b0a4c931800106db18d201c3a3000cc010a100047000c05ef01f0011505c800450105b50006b6000342377743000044000002f1000064651000003b1a000000016b0a4c9ae800106d9923201c39bc00cd010b0f0046000c05ef01f0011505c800450105b50005b6000342377743000044000002f1000064651000003b42000000016b0a4ca2b800106d8170201c39cc00cf010b0f0047000c05ef01f0011505c800450105b50005b6000342373d43000044000002f1000064651000003b6b000000016b0a4caa8800106d6712201c3a4100d1010b0e0048000c05ef01f0011505c800450105b50005b6000342374543000044000002f1000064651000003b99000000016b0a4cb25800106d4f1d201c39fe00cf010a100049000c05ef01f0011505c800450105b50004b6000342374743000044000002f1000064651000003bc0000000016b0a4cba2800106d379c201c392600ce010a10004b000c05ef01f0011505c800450105b50004b6000342377d43000044000002f1000064651000003be7000000016b0a4cc1f800106d1e28201c382c00cc010a10004c000c05ef01f0011505c800450105b50005b6000342377a43000044000002f1000064651000003c11000000016b0a4cc9c800106d04e5201c376400cd010a10004c000c05ef01f0011505c800450105b50005b6000342378743000044000002f1000064651000003c3c000000016b0a4cd19800106ceb91201c36ce00ce010a11004d000c05ef01f0011505c800450105b50005b6000342378843000044000002f1000064651000003c6600130000a6d2"
    And I want to send teltonika tcp data to api with socket "test-socket-id"
    And I want fill "payload" field with "00000000000004d608130000016b0a4cd96800106cd260201c35f500ce010c0f004d000c05ef01f0011505c800450105b50006b6000342379343000044000002f1000064651000003c91000000016b0a4ce13800106cb8a8201c358000cd010d10004e000c05ef01f0011505c800450105b50005b600034237a043000044000002f1000064651000003cbb000000016b0a4ce90800106c9eae201c33f000ce011010004f000c05ef01f0011505c800450105b50005b600034237ab43000044000002f1000064651000003ce6000000016b0a4cf0d800106c840e201c35c300d0011211004f000c05ef01f0011505c800450105b50005b6000342379843000044000002f1000064651000003d13000000016b0a4cf8a800106c695c201c371000d101130f004f000c05ef01f0011505c800450105b50006b600034237a643000044000002f1000064651000003d40000000016b0a4d007800106c4f73201c392600d2011411004e000c05ef01f0011505c800450105b50005b600034237a543000044000002f1000064651000003d6d000000016b0a4d084800106c3856201c3b9f00d2011311004d000c05ef01f0011505c800450105b50005b600034237b143000044000002f1000064651000003d97000000016b0a4d101800106c20e6201c3d8200d40114110044000c05ef01f0011505c800450105b50005b600034237a543000044000002f1000064651000003dbe000000016b0a4d1bd000106c0bad201c3f2300d4011410003f000c05ef01f0011505c800450105b50005b6000342378d43000044000002f1000064651000003de4000000016b0a4d23a000106bf8aa201c409200d401150f0035000c05ef01f0011505c800450105b50006b600034236fd43000044000002f1000064651000003e07000000016b0a4d2f5800106be213201c41be00d401140f0028000c05ef01f0011505c800450105b50006b6000342376c43000044000002f1000064651000003e30000000016b0a4d3b1000106bd21f201c426400d301140f001a000c05ef01f0011505c800450105b50006b6000342377143000044000002f1000064651000003e4f000000016b0a4d46c800106bc8d0201c435e00d4011710000e000c05ef01f0011505c800450105b50005b6000342378d43000044000002f1000064651000003e62000000016b0a4d69f000106bb516201c45c700d4011410001a000c05ef01f0011505c800450105b50005b600034237d143000044000002f1000064651000003e82000000016b0a4d799000106b9f15201c475700d60114100025000c05ef01f0011505c800450105b50005b600034237ab43000044000002f1000064651000003ea3000000016b0a4d854800106b8a72201c48b500d6011510002d000c05ef01f0011505c800450105b50005b600034237a243000044000002f1000064651000003ec4000000016b0a4d910000106b7088201c496c00d90114100035000c05ef01f0011505c800450105b50005b600034237a443000044000002f1000064651000003eed000000016b0a4d9cb800106b561a201c4c3900da0116100037000c05ef01f0011505c800450105b50005b6000342379e43000044000002f1000064651000003f19000000016b0a4da48800106b42e5201c4dfb00da011411003c000c05ef01f0011505c800450105b50005b6000342379b43000044000002f1000064651000003f39001300006f6a"
    And I want to send teltonika tcp data to api with socket "test-socket-id"
    And I want fill "payload" field with "00000000000004d608130000016b0a4f794800106696cd201d2f4e00e3012d11003d000c05ef01f0011504c800450105b50005b600034237a943000044000002f10000646510000047cf000000016b0a4f8118001066859d201d355c00e4012d11003b000c05ef01f0011504c800450105b50005b6000342377743000044000002f10000646510000047f1000000016b0a4f88e800106674f2201d3bad00e3012c11003b000c05ef01f0011504c800450105b50005b6000342377243000044000002f1000064651000004813000000016b0a4f90b80010666254201d424000e0012c110041000c05ef01f0011505c800450105b50005b6000342377e43000044000002f1000064651000004835000000016b0a4f98880010664e78201d491600df012c110042000c05ef01f0011505c800450105b50005b600034237b743000044000002f100006465100000485a000000016b0a4fa0580010663c70201d4f6700de012c120042000c05ef01f0011505c800450105b50005b600034237b143000044000002f100006465100000487f000000016b0a4fa828001066295c201d564e00dd012d120042000c05ef01f0011505c800450105b50005b600034237b743000044000002f10000646510000048a4000000016b0a4faff800106615f6201d5ce100dc012d120044000c05ef01f0011505c800450105b50005b600034237a543000044000002f10000646510000048c9000000016b0a4fb7c8001066027e201d63fa00da012d120046000c05ef01f0011505c800450105b50005b6000342376d43000044000002f10000646510000048ef000000016b0a4fbf98001065eed5201d6b6600da012d120046000c05ef01f0011505c800450105b50005b6000342378443000044000002f1000064651000004916000000016b0a4fc768001065db5e201d72a000d8012d120044000c05ef01f0011505c800450105b50005b6000342377e43000044000002f100006465100000493d000000016b0a4fcf38001065c7f7201d791200d7012e120044000c05ef01f0011505c800450105b50005b6000342378143000044000002f1000064651000004963000000016b0a4fd708001065b70a201d807e00d60131120045000c05ef01f0011505c800450105b50005b600034237a743000044000002f1000064651000004987000000016b0a4fded8001065a33f201d89de00d5013312004a000c05ef01f0011505c800450105b50005b600034237b743000044000002f10000646510000049af000000016b0a4fe6a80010658fb7201d924400d40134110049000c05ef01f0011505c800450105b50005b600034237a743000044000002f10000646510000049d8000000016b0a4fee780010657dae201d9afd00d40135110048000c05ef01f0011505c800450105b50005b600034237b243000044000002f1000064651000004a00000000016b0a4ff6480010656bc7201da40a00d30138110048000c05ef01f0011505c800450105b50005b600034237aa43000044000002f1000064651000004a26000000016b0a4ffe180010655a43201daea600d30139110049000c05ef01f0011505c800450105b50005b6000342377b43000044000002f1000064651000004a50000000016b0a5005e8001065489f201db8be00d2013a0e004a000c05ef01f0011505c800450105b50007b6000442377543000044000002f1000064651000004a7a001300000ebc"
    And I want to send teltonika tcp data to api with socket "test-socket-id"
    And Calculate routes
    And I want to migrate postgres functions
    Given I signed in as "manager" team "client"
    And I want to get vehicle summary list from "2019-05-01 00:00:00" to "2019-05-31 23:59:59" page "1" sort "-status"
    Then response code is 200
    And I see field "page" filled with "1"
    And I see field "limit" filled with "10"
    And I see field "total" filled with "1"
    And I see field "data/0/model" filled with "Mercedes"
    And I want fill "defaultLabel" field with "Jenny"
    And I see field "data/0/depot" filled with "null"
    And I see field "data/0/regNo" filled with "B1234567890"
    And I see field "data/0/groups" filled with "null"
    And I see field "data/0/privateDistance" filled with "null"
    And I see field "data/0/workDistance" filled with "null"
    And I see field "data/0/distance" filled with "4340"
    And I see field "data/0/startOdometer" filled with "null"
    And I see field "data/0/endOdometer" filled with "null"
    And I see field "data/0/maxSpeed" filled with "79"
    And I see field "data/0/stops" filled with "0"
    And I see field "data/0/parkingTime" filled with "null"
    And I see field "data/0/drivingTime" filled with "246"
    And I see field "data/0/ecoDriveScores" filled with "null"
    And I see field "data/0/ecoSpeedingEvents" filled with "null"
    And I see field "data/0/speedingEvents" filled with "null"

  Scenario: I want to see a vehicle that did not drive yet
    Given I signed in as "super_admin" team "admin"
    And I want fill device model for vehicle with name "FM3001"
    And I want fill "sn" field with "test sn"
    And I want fill "status" field with "inStock"
    And I want fill "port" field with 25
    And I want fill "hw" field with "test hw"
    And I want fill "sw" field with "test sw"
    And I want fill "imei" field with "888888888888888"
    And I want fill "phone" field with "+(375) 245535454"
    And I want fill "imsi" field with "test imsi"
    And I want fill "devEui" field with "test devEui"
    And I want fill "userName" field with "test username"
    And I want fill "password" field with "test password"
    And I want fill "clientNote" field with "test clientNote"
    And I want fill "adminNote" field with "test adminNote"
    And I want to create device for vehicle and save id
    And I want clean filled data
    Given I signed in as "manager" team "client"
    And I want to fill teamId by team of current user
    And I want fill "model" field with "Mercedes"
    And I want fill "type" field with "Car"
    And I want fill "vin" field with "1G6AR5S38E0134828"
    And I want fill "regNo" field with "B1234567890"
    And I want fill "defaultLabel" field with "Jenny"
    And I want fill "fuelType" field with 1
    And I want fill "year" field with "2000"
    And I want to create vehicle and save id
    Given I signed in as "super_admin" team "admin"
    And I want install device for vehicle
    And Elastica populate
    And I want fill "payload" field with "000F383838383838383838383838383838"
    And I want to send teltonika tcp data to api with socket "test-socket-id"
    And I see field "response"
    And I see field "imei"
    And Calculate routes
    And I want to migrate postgres functions
    Given I signed in as "manager" team "client"
    And I want to get vehicle summary list from "2019-05-01 00:00:00" to "2019-05-31 23:59:59" page "1" sort "-status"
    Then response code is 200
    And I see field "page" filled with "1"
    And I see field "limit" filled with "10"
    And I see field "total" filled with "1"
    And I see field "data/0/model" filled with "Mercedes"
    And I see field "data/0/defaultLabel" filled with "Jenny"
    And I see field "data/0/regNo" filled with "B1234567890"
    And I see field "data/0/depot" filled with "null"
    And I see field "data/0/groups" filled with "null"
    And I see field "data/0/privateDistance" filled with "null"
    And I see field "data/0/workDistance" filled with "null"
    And I see field "data/0/distance" filled with "null"
    And I see field "data/0/startOdometer" filled with "null"
    And I see field "data/0/endOdometer" filled with "null"
    And I see field "data/0/maxSpeed" filled with "null"
    And I see field "data/0/stops" filled with "null"
    And I see field "data/0/parkingTime" filled with "null"
    And I see field "data/0/drivingTime" filled with "null"
    And I see field "data/0/ecoDriveScores" filled with "null"
    And I see field "data/0/ecoSpeedingEvents" filled with "null"
    And I see field "data/0/speedingEvents" filled with "null"

  Scenario: I want get one vehicle with tracker data and wrong odometer value
    Given I signed in as "super_admin" team "admin"
    Then I want fill "model" field with 2
    Then I want fill "type" field with "Car"
    Then I want fill "vin" field with 4
    Then I want fill "fuelType" field with 1
    Then I want fill "year" field with "2000"
    And I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    Then I want to create vehicle and save id
    And I see field "team/type" filled with "client"
    And I want fill device model for vehicle with name "FM3001"
    And I want fill "sn" field with "test sn"
    And I want fill "status" field with "inStock"
    And I want fill "port" field with 25
    And I want fill "hw" field with "test hw"
    And I want fill "sw" field with "test sw"
    And I want fill "imei" field with "888888888888888"
    And I want fill "phone" field with "+(375) 245535454"
    And I want fill "imsi" field with "test imsi"
    And I want fill "devEui" field with "test devEui"
    And I want fill "userName" field with "test username"
    And I want fill "password" field with "test password"
    And I want fill "clientNote" field with "test clientNote"
    And I want fill "adminNote" field with "test adminNote"
    Then I want to create device for vehicle and save id
    Then I want install device for vehicle
    Given Elastica populate
    When I want fill "payload" field with "000F383838383838383838383838383838"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response"
    And I see field "imei"
    When I want fill "payload" field with "000000000000003908010000016ee9b3f7a0005680f103e94c90b900000000000000000905ef00f0005000c802450303423277430f8e4400080110ffffffff0001000096f9"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Given Elastica populate
    When I want add value to array key "fields" with "device"
    Then I want to get vehicle by saved id
    And I see field "team/type" filled with "client"
    And I see field "device/trackerData/mileage" filled with "null"
    Then response code is 200

  Scenario: I want to unset driver according to setting vehicleEngineOff
    Given I signed in as "super_admin" team "admin"
    Then I want fill "model" field with 2
    Then I want fill "type" field with "Car"
    Then I want fill "vin" field with 4
    Then I want fill "fuelType" field with 1
    Then I want fill "year" field with "2000"
    And I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    Then I want to create vehicle and save id
    And I see field "team/type" filled with "client"
    And I want fill device model for vehicle with name "FM3001"
    And I want fill "sn" field with "test sn"
    And I want fill "status" field with "inStock"
    And I want fill "port" field with 25
    And I want fill "hw" field with "test hw"
    And I want fill "sw" field with "test sw"
    And I want fill "imei" field with "888888888888888"
    And I want fill "phone" field with "+(375) 245535454"
    And I want fill "imsi" field with "test imsi"
    And I want fill "devEui" field with "test devEui"
    And I want fill "userName" field with "test username"
    And I want fill "password" field with "test password"
    And I want fill "clientNote" field with "test clientNote"
    And I want fill "adminNote" field with "test adminNote"
    Then I want to create device for vehicle and save id
    Then I want install device for vehicle
    Then I want set vehicle driver with user of current team with date "2019-12-01T11:00:04+03:00"
    And I see field "driver/email" filled with "client-contact-0@ocsico.com"
    Given Elastica populate
    And I want set remembered team settings "vehicleEngineOff" with raw value
    """
      {"enable":true,"value":300}
    """
    Given There are following tracker payload from teltonika tracker with socket "test-socket-id":
      | payload                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        |
      | 000F383838383838383838383838383838                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             |
      | 00000000000003e6080b0000016f209b6ed800106678872021eb0600e600ce0c0000000f04ef00f000c8007100064230f81800004300004400000d06b30f005304f100006465c7000000001000693b590c00113ae801ee00000000000000000000016f20d25d5800106678872021eb0600ec00ce0e0000000f04ef00f000c8007100064230f81800004300004400000d06b30f005304f100006465c7000000001000693b590c00113ae801ee00000000000000000000016f21094bd800106678872021eb0600e700ce0e0000000f04ef00f000c8007100064230f81800004300004400000d06b30f005304f100006465c7000000001000693b590c00113ae801ee00000000000000000000016f21403a5800106678872021eb0600e100ce090000000f04ef00f000c8007100064230d91800004300004400000d06b30f005304f100006465c7000000001000693b590c00113ae801ee00000000000000000000016f217728d800106678872021eb0600d800ce0e0000000f04ef00f000c8007100064230cc1800004300004400000d06b30f005304f100006465c7000000001000693b590c00113ae801ee00000000000000000000016f21ae175800106678872021eb0600dd00ce0d0000000f04ef00f000c8007100064230cc1800004300004400000d06b30f005304f100006465c7000000001000693b590c00113ae801ee00000000000000000000016f21d81d280010667abd2021ec0000e500110d0000f00f04ef00f001c80071000642301c1800004300004400000d06b30f005304f100006465c7000000001000693b590c00113ae801ee00000000000000000000016f21d886a00010666e2d2021ea7000ea011a070003f01507ef01f001c800710021002500302f094239181800034300004400000d06b30f00002a00112b0000311f3504f100000000c7000000001000693b590c00113ae801ee00000000000000000000016f21d8fbd000106678232021ed6f00e7013e0e0000001507ef01f001c800710021ff2500302f094239291800004300004400000d06b30f00002a002f2b0000311f3504f100006465c7000000001000693b590c00113af001ee00000000000000000000016f21d9710000106678232021ed6f00e7013e0d0000001507ef01f001c800710021fc2500302f094238b61800004300004400000d06b30f00002a004d2b0000311f3504f100006465c7000000001000693b590c00113af801ee00000000000000000000016f21d9848800106678232021ed6f00e7013e0d0000f01507ef01f000c800710021fb2500302f094237fb1800004300004400000d06b30f00002a00542b0000311f3504f100006465c7000000001000693b590c00113af901ee00000000000000000b00001098                                                                                                                                                                                                                                                                                                                                                                                                                           |
      | 00000000000004b3080c0000016f21d9c30800106678232021ed6f00e7013e0e0000f01507ef01f001c800710021fb2500302f094237d71800004300004400000d06b30f00002a00632b0000311f3504f100006465c7000000001000693b590c00113afe01ee00000000000000000000016f21da383800106678232021ed6f00e7013e0d0000001507ef01f001c800710021fb2500302f094237e51800004300004400000d06b30f00002a00812b0000311f3504f100006465c7000000001000693b590c00113b0601ee00000000000000000000016f21daad6800106678232021ed6f00e7013e0c0000001507ef01f001c800710021fa2500302f0942376b1800004300004400000d06b30f00002a009f2b0000311f3504f100006465c7000000001000693b590c00113b0e01ee00000000000000000000016f21dab53800106678232021ed6f00e7013e0c0000f01507ef01f000c800710021fa2500302f0942372d1800004300004400000d06b30f00002a009f2b0000311f3504f100006465c7000000001000693b590c00113b0f01ee00000000000000000000016f21dad09000106678232021ed6f00e7013e0e0000f01507ef01f001c800710021f92505302f094237691800004300004400000d06b30f00002a00a72b0000311f3504f100006465c7000000001000693b590c00113b1101ee00000000000000000000016f21db1ac800106678872021ef8400e600c30e0008001507ef01f001c800710021fa2500302f094237831800084300004400000d06b30f00002a00b92b0000311f3504f100006465c7000000001000693b590c00113b1601ee00000000000000000000016f21db323800106676932021e7c500e600b60e0010001507ef01f001c800710021fc2511302f094237961800104300004400000d06b30f00002a00c12b0000311f3504f100006465c7000000031000693b5c0c00113b1601ee00000000000000000000016f21db45c000106675aa2021deea00e300a30e0009001507ef01f001c800710021002519302f094237561800094300004400000d06b30f00002a00c52b0000311f3504f100006465c70000001d1000693b760c00113b1701ee00000000000000000000016f21db68e8001066779e2021db8800df006a0e000b001507ef01f001c800710021fd250b302f0942378418000b4300004400000d06b30f00002a00d02b0000311f3504f100006465c7000000201000693b790c00113b1901ee00000000000000000000016f21db8c100010668d1a2021d66300df00820e000d001507ef01f001c800710021f52514302f0942379f18000d4300004400000d06b30f00002a00d72b0000311f3504f100006465c70000004a1000693ba30c00113b1a01ee00000000000000000000016f21db8ff80010668db02021d40b00de00b00e0012001507ef01f001c800710021f52514302f094237991800124300004400000d06b30f00002a00d72b0000311f3504f100006465c70000004f1000693ba80c00113b1b01ee00000000000000000000016f21dbab5000106688272021bfed00dd00bc0e0014001507ef01f001c800710021fb251f302f094237751800144300004400000d06b30f00002a00df2b0000311f3604f100006465c7000000891000693be20c00113b1f01ee00000000000000000c00007cfb |
      | 00000000000004b3080c0000016f21dde1b8001066230c2021b6f100db01100f0000001507ef01f001c800710021ff2500302f094237541800004300004400000d06b30f00002a01712b0000311f3604f100006465c7000001561000693caf0c00113b4b01ee00000000000000000000016f21de56e8001066230c2021b6f100db01100f0000001507ef01f001c800710021002500302f094237611800004300004400000d06b30f00002a018f2b0000311f3604f100006465c7000001561000693caf0c00113b5301ee00000000000000000000016f21decc18001066230c2021b6f100db01100f0000f01507ef01f000c8007100210025003030094237501800004300004400000d06b30f00002a01ad2b0000311f3604f100006465c7000001561000693caf0c00113b5c01ee00000000000000000000016f21dfc618001066230c2021b6f100db01100d0000001507ef01f000c80071002106252c3030094237441800004300004400000d06b30f00002a01ec2b0000311f3604f100006465c7000001561000693caf0c00113b6d01ee00000000000000000000016f21dfca00001066230c2021b6f100db01100e0000f01507ef01f001c8007100210425473030094237541800004300004400000d06b30f00002a01f02b0000311f3604f100006465c7000001561000693caf0c00113b6e01ee00000000000000000000016f21dfd1d00010660b482021771300df00a50e003d001507ef01f001c80071002104254730300942375918003d4300004400000d06b30f00002a01f02b0000311f3604f100006465c7000001561000693caf0c00113b6e01ee00000000000000000000016f21dfdd8800106611142021685c00e000a60e0022001507ef01f001c80071002100254030300942372918002e4300004400000d06b30f00002a01f42b0000311f3604f100006465c7000001561000693caf0c00113b6e01ee00000000000000000000016f21dff4f80010661ad82021573d00df00a00f0036001507ef01f001c80071002100252930300942372d1800264300004400000d06b30f00002a01fb2b0000311f3604f100006465c70000025c1000693db50c00113b7001ee00000000000000000000016f21e008800010662ec42021375600e0009e0f0049001507ef01f001c800710021012545302f0942376a1800494300004400000d06b30f00002a01ff2b0000311f3604f100006465c7000002b71000693e100c00113b7f01ee00000000000000000000016f21e014380010663ae0202122d400df00a00d004a001507ef01f001c800710021fd2549302f0942374c18004a4300004400000d06b30f00002a02032b0000311f3604f100006465c7000002f41000693e4d0c00113b8901ee00000000000000000000016f21e01ff0001066453a20210ed800e0009f0d0044001507ef01f001c800710021fd2549302f0942375b1800444300004400000d06b30f00002a02032b0000311f3604f100006465c7000003321000693e8b0c00113b9401ee00000000000000000000016f21e027c00010664cc7202103f800e100a00d0039001507ef01f001c800710021ec2548302f094237461800394300004400000d06b30f00002a02062b0000311f3604f100006465c7000003561000693eaf0c00113b9a01ee00000000000000000c00006d89 |
      | 000000000000044f080b0000016f21e158700010647357201fe8f600ea00ee0e0056001507ef01f001c800710021fe2559302f0942388b1800564300004400000d06b30f00282a02552b0000311f3804f100006465c7000008b5100069440e0c00113c8801ee00000000000000000000016f21e16bf80010643ae5201fd45300ef00ef0d004f001507ef01f001c800710021002552302f094238a218004f4300004400000d06b30f00282a02592b0000311f3804f100006465c70000092710006944800c00113c9d01ee00000000000000000000016f21e177b00010641afe201fc80500f200ee0e0048001507ef01f001c800710021012551302f094238551800484300004400000d06b30f00282a025c2b0000311f3804f100006465c70000096910006944c20c00113ca901ee00000000000000000000016f21e17f80001064083e201fc0fd00f400ee0f003e001507ef01f001c800710021012551302f0942383518003e4300004400000d06b30f00282a025c2b0000311f3804f100006465c70000099110006944ea0c00113caf01ee00000000000000000000016f21e18750001063f9a9201fbc1b00f300ed0f002c001507ef01f001c800710021002542302f094237ee18002c4300004400000d06b30f00282a02602b0000311f3804f100006465c7000009b2100069450b0c00113cb401ee00000000000000000000016f21e18b38001063f5b0201fba5900f300ec0f0022001507ef01f001c800710021002542302f094236b01800224300004400000d06b30f00282a02602b0000311f3804f100006465c7000009bf10006945180c00113cb601ee00000000000000000000016f21e18f20001063f39b201fb99100f300e70e0012001507ef01f001c800710021002542302f0942378c1800124300004400000d06b30f00282a02602b0000311f3804f100006465c7000009c710006945200c00113cb601ee00000000000000000000016f21e19308001063f442201fb99100f200e70e0005001507ef01f001c80071002100251a302f094237ea1800054300004400000d06b30f00282a02642b0000311f3804f100006465c7000009cb10006945240c00113cb601ee00000000000000000000016f21e20838001063f678201fb9f500f300e70e0000001507ef01f001c800710021022500302f094237f61800004300004400000d06b30f00282a02822b0000311f3804f100006465c7000009cc10006945250c00113cbe01ee00000000000000000000016f21e27d68001063f678201fb9f500f300e70e0000001507ef01f001c800710021002500302f094237f61800004300004400000d06b30f00282a02a02b0000311f3804f100006465c7000009cc10006945250c00113cc701ee00000000000000000000016f21e298c0001063f678201fb9f500f300e70d0000f01507ef01f000c800710021fe2500302f094237df1800004300004400000d06b30f00282a02a72b0000311f3804f100006465c7000009cc10006945250c00113cc901ee00000000000000000b0000bc94                                                                                                                                                                                                         |
      | 000000000000005a08010000016f21e9110001105c78d4201e62cb00fa01550e0000fa1005ef00f000c8007100fa00064232ab1800004300004400000d06b30f003604f100006465c70000186510006953be0c00113edd01ee0000000000000000010000b704                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   |
      | 0000000000000422080b0000016f21e7b94000105c6f31201e4b4a00fd01020e0018001507ef01f001c80071002100251b302d0942387a1800184300004400000d06b30f00362a03f82b0000311f3c04f100006465c700001814100069536d0c00113ed501ee00000000000000000000016f21e7bd2800105c6bf0201e4c2300fd01170e0013001507ef01f001c80071002100251b302d0942387a1800134300004400000d06b30f00362a03f82b0000311f3c04f100006465c700001814100069536d0c00113ed501ee00000000000000000000016f21e7c8e000105c6c43201e525200fa001a0e0013001507ef01f001c80071002100251b302d094238dd1800134300004400000d06b30f00362a03f82b0000311f3c04f100006465c700001826100069537f0c00113ed601ee00000000000000000000016f21e7ccc800105c7167201e558300fa002b0e001f001507ef01f001c800710021f9251b302d0942392918001f4300004400000d06b30f00362a03fc2b0000311f3c04f100006465c70000182f10006953880c00113ed601ee00000000000000000000016f21e7d49800105c7786201e5ada00fa00190e001c001507ef01f001c800710021f9251b302d0942391018001c4300004400000d06b30f00362a03fc2b0000311f3c04f100006465c700001845100069539e0c00113ed801ee00000000000000000000016f21e7d88000105c799c201e5d9500fa000e0e001c001507ef01f001c800710021fd251d302d0942389618001c4300004400000d06b30f00362a04002b0000311f3c04f100006465c70000184e10006953a70c00113ed801ee00000000000000000000016f21e7e05000105c79de201e619f00fa01620e000f001507ef01f001c800710021fd251d302d0942381218000f4300004400000d06b30f00362a04002b0000311f3c04f100006465c70000185d10006953b60c00113ed901ee00000000000000000000016f21e7e43800105c7938201e628800fa01550e0007001507ef01f001c800710021fd251d302d0942384b1800074300004400000d06b30f00362a04002b0000311f3c04f100006465c70000186210006953bb0c00113ed901ee00000000000000000000016f21e82e7000105c78d4201e62cb00fa01550d0000ef0f04ef00f001c8007100064233841800004300004400000d06b30f003604f100006465c70000186510006953be0c00113edd01ee00000000000000000000016f21e8a3a000105c78d4201e62cb00fa01550e0000000f04ef00f001c8007100064233021800004300004400000d06b30f003604f100006465c70000186510006953be0c00113edd01ee00000000000000000000016f21e8edd800105c78d4201e62cb00fa01550d0000f00f04ef00f000c8007100064232d41800004300004400000d06b30f003604f100006465c70000186510006953be0c00113edd01ee00000000000000000b00005d76                                                                                                                                                                                                                                                                                                   |
    When I want fill "dateFrom" field with "2019-12-01T18:55:30+00:00"
    When I want fill "dateTo" field with "2019-12-30T22:00:00+00:00"
    Then I want get vehicle history for driver "client-contact-0@ocsico.com"
    And response code is 200
    And I see field "0/startDate" filled with "2019-12-01 11:00:04"
    And I see field "0/finishDate" filled with "2019-12-20 00:00:55"
