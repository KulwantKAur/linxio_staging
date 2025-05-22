Feature: Devices

  Background:
    Given I signed in as "super_admin" team "admin"

  Scenario: I want add device
    And I want fill device model with name "FM3001"
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
    Then I want to create device and save id
    And response code is 200
    And I see field "model/name" filled with "FM3001"
    And I see field "sn" filled with "test sn"
    And I see field "status" filled with "inStock"
    And I see field "team/type" filled with "admin"
    And I see field "port" filled with 25
    And I see field "hw" filled with "test hw"
    And I see field "sw" filled with "test sw"
    And I see field "imei" filled with "test imei"
    And I see field "phone" filled with "+(375) 245535454"
    And I see field "imsi" filled with "test imsi"
    Then I want to get device by saved id
    And response code is 200
    And I see field "model/name" filled with "FM3001"
    And I see field "sn" filled with "test sn"
    And I see field "status" filled with "inStock"
    And I see field "team/type" filled with "admin"
    And I see field "port" filled with 25
    And I see field "hw" filled with "test hw"
    And I see field "sw" filled with "test sw"
    And I see field "imei" filled with "test imei"
    And I see field "phone" filled with "+(375) 245535454"
    And I see field "imsi" filled with "test imsi"
    Then I want to get device notes by saved id and type "client"
    And I see field "0/note" filled with "test clientNote"
    Then I want to get device notes by saved id and type "admin"
    And I see field "0/note" filled with "test adminNote"
    And I want fill "imei" field with "test imei"
    Then I want to create device and save id
    And response code is 400
    And I see field "errors/0/detail/imei/required" filled with "Device with this imei is already exists"
    And I want fill "imei" field with "test imei2"
    And I want fill "status" field with "unavailable"
    And I want fill "blockingMessage" field with "test msg"
    Then I want to create device and save id
    And I see field "status" filled with "unavailable"
    And I see field "blockingMessage" filled with "test msg"

  Scenario: I want get device list
    And I want fill device model with name "FM3001"
    And I want fill "status" field with "inStock"
    And I want get client by name "client-name-1" and save id
    And I want fill teamId by saved clientId
    And I want fill "phone" field with "+(375) 245535454"
    And I want fill "imei" field with "test imei"
    Then I want to create device and save id
    And I see field "model/name" filled with "FM3001"
    And I want fill "fuelType" field with 1
    And I want fill "fuelTankCapacity" field with "50"
    Then I want to create vehicle and save id
    And I see field "team/type" filled with "client"
    And I see field "status" filled with "inStock"
    And I see field "fuelType" filled with 1
    And I see field "fuelTankCapacity" filled with "string(50)"
    And I want upload file
    Then I want install device
    And I see field "installDate" is not null
    And I see field "deviceInstallation" is not null
    And I want clean filled data
    And I want fill device model with name "FM3001"
    And I want fill "status" field with "unavailable"
    And I want get client by name "client-name-2" and save id
    And I want fill teamId by saved clientId
    And I want fill "phone" field with "+(375) 44444"
    And I want fill "imei" field with "test imei2"
    Then I want to create device and save id
    And I see field "model/name" filled with "FM3001"
    Given Elastica populate
    And I want clean filled data
    And I want fill "fuelType" field with 1
    And I want fill "fuelTankCapacity" field with 50
    When  I want get device list
    And I see field "total" filled with 1
    And I want clean filled data
    When  I want get device list
    And I see field "total" filled with 20
    Then I want fill "status" field with "unavailable"
    And  I want get device list
    And I see field "data/0/status" filled with "unavailable"
    And I see field "total" filled with 1
    Then I want clean filled data
    Then I want fill "phone" field with "+(375) 44444"
    And  I want get device list
    And I see field "data/0/phone" filled with "+(375) 44444"
    And I see field "total" filled with 1
    Then I want clean filled data
    Then I want fill "model" field with "FM3001"
    And  I want get device list
    And I see field "data/0/model/name" filled with "FM3001"
    Then I want clean filled data
    Then I want fill "vendor" field with "Teltonika"
    And  I want get device list
    And I see field "data/0/vendor/name" filled with "Teltonika"
    Then I want clean filled data
    And I want fill device model with name "FM3001"
    And I want fill "status" field with "inStock"
    And I want get client by name "client-name-1" and save id
    And I want fill teamId by saved clientId
    And I want fill "phone" field with "+(375) 245535454"
    And I want fill "imei" field with "test imei3"
    Then I want to create device and save id
    Then response code is 200
    Then I want clean filled data
    Given I signed in as "installer" team "admin"
    And  I want get device list
    And I see field "total" filled with 1

  Scenario: I want install and uninstall device
    And I want fill device model with name "FM3001"
    And I want fill "status" field with "inStock"
    And I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "phone" field with "+(375) 245535454"
    And I want fill "imei" field with "987654321"
    Then I want to create device and save id
    And I see field "model/name" filled with "FM3001"
    And I see field "status" filled with "inStock"
    And I want clean filled data
    And I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    Then I want to create vehicle and save id
    And I see field "team/type" filled with "client"
    And I see field "status" filled with "offline"
    And I want upload file
    And I want fill "odometer" field with "654321"
    Then I want install device
    And I see field "installDate" is not null
    And I see field "deviceInstallation" is not null
    And I see field "status" filled with "offline"
    And I see field "deviceInstallation/odometer" filled with "string(654321)"
    And I want clean filled data
    When I want check vehicle status
    Then I see field "status" filled with "online"
    And I want check "files/0/id" is not empty
    Given I signed in as "admin" team "client" and teamId 3
    Then I want get device installation
    And I do not see field "id"
    Given I signed in as "super_admin" team "admin"
    Then I want get device installation
    And I see right deviceId
    And I want fill "status" field with "unavailable"
    And I want fill "blockingMessage" field with "test message"
    And I see field "device/model/name" filled with "FM3001"
    Then I want uninstall device
    And I see field "deviceInstallation" filled with null
    And I see field "installDate" filled with null
    Then response code is 200
    And I see field "status" filled with "unavailable"
    And I see field "blockingMessage" filled with "test message"
    Then I want install device
    And I want fill "status" field with "inStock"
    Then I want uninstall device
    And I see field "status" filled with "inStock"

  Scenario: I want edit device
    And I want fill device model with name "FM3001"
    And I want fill "sn" field with "test sn"
    And I want fill "status" field with "inStock"
    And I want get client by name "client-name-0" and save id
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
    Then I want to create device and save id
    And response code is 200
    And I see field "model/name" filled with "FM3001"
    And I want clean filled data
    And I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    Then I want to create vehicle and save id
    And I see field "team/type" filled with "client"
    And I see field "status" filled with "offline"
    Then I want install device
    And response code is 200
    And I see field "model/name" filled with "FM3001"
    And I want clean filled data
    And I want fill device model with name "FM36M1"
    And I want fill "sn" field with "test sn2"
    And I want fill "status" field with "unavailable"
    And I want fill "port" field with 22
    And I want fill "hw" field with "test hw2"
    And I want fill "sw" field with "test sw2"
    And I want fill "imei" field with "test imei2"
    And I want fill "phone" field with "+(375) 2222222"
    And I want fill "imsi" field with "test imsi2"
    And I want fill "userName" field with "test username2"
    And I want fill "password" field with "test password2"
    And I want get client team by name "client-name-7" and save id
    And I want fill teamId with saved team id
    And I want to edit device and save id
    And response code is 200
    And I see field "model/name" filled with "FM36M1"
    And I see field "sn" filled with "test sn2"
    And I see field "status" filled with "unavailable"
    And I see field "team/type" filled with "client"
    And I see field "port" filled with 22
    And I see field "hw" filled with "test hw2"
    And I see field "sw" filled with "test sw2"
    And I see field "imei" filled with "test imei2"
    And I see field "phone" filled with "+(375) 2222222"
    And I see field "imsi" filled with "test imsi2"
    And I see field "deviceInstallation" filled with null
    And I see field "team/id" filled with saved "teamId"
    And I do not see field "isFixWithSpeed"
    And I want fill "imei" field with "867060038028151"
    And I want to edit device and save id
    And response code is 400
    And I see field "errors/0/detail/imei/required" filled with "Device with this imei is already exists"

  Scenario: I want delete and restore device
    And I want fill device model with name "FM3001"
    And I want fill "sn" field with "test sn"
    And I want fill "status" field with "inStock"
    And I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
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
    Then I want to create device and save id
    And response code is 200
    And I see field "model/name" filled with "FM3001"
    Then I want to delete saved device
    And response code is 204
    And I want clean filled data
    Then I want restore saved device
    And response code is 200
    And I see field "model/name" filled with "FM3001"
    And I see field "status" filled with "inStock"

  Scenario: I want check users permissions
    And I want fill "email" field with "client_manager_user@gmail.test"
    And I want fill "name" field with "Alex"
    And I want fill "phone" field with "+6(4)654654"
    And I want fill "roleId" field with "3"
    And I want fill "allTeamsPermissions" field with "false"
    Then I want register client manager user and remember id
    And I see field "email" filled with "client_manager_user@gmail.test"
    And I see field "roleId" filled with 3
    Then I want fill "name" field with "client name1"
    And I want fill "taxNr" field with "12345678910"
    And I want fill "planId" field with 1
    And I want fill "timezone" field with 63
    Then I want register client with manager and remember
    And I see field "id"
    And I want clean filled data
    Then I signed with email "client_manager_user@gmail.test"
    And I want fill teamId by saved clientId
    And I want fill device model with name "FM3001"
    And I want fill "imei" field with "test imei"
    Then I want to create device and save id
    And response code is 200
    And I see field "model/name" filled with "FM3001"
    And I want fill device model with name "FM3001"
    And I want fill "imei" field with "test imei2"
    Then I want to create device and save id
    And response code is 200
    Then I signed with email "client-user-1@ocsico.com"
    And I want to edit device and save id
    And response code is 400
    Then I want to create device and save id
    And response code is 403

  Scenario: I want get device vendors list
    Given I signed in as "super_admin" team "admin"
    Then I want get device vendors list
    And response code is 200
    And I see field "0/name" filled with "Teltonika"
    And I see field "0/models/0/name" filled with "FM36M1"
    And I see field "0/models/1/name" filled with "FM3001"

  Scenario: I want export devices list
    And I want fill device model with name "FM3001"
    And I want fill "status" field with "unavailable"
    And I want get client by name "client-name-2" and save id
    And I want fill teamId by saved clientId
    And I want fill "phone" field with "+(375) 44444"
    And I want fill "imei" field with "test imei"
    Then I want to create device and save id
    And I see field "model/name" filled with "FM3001"
    And I want clean filled data
    And I want get client by name "client-name-2" and save id
    And I want fill teamId by saved clientId
    Then I want to create vehicle and save id
    And I see field "team/type" filled with "client"
    Then I want install device
    And I see field "installDate" is not null
    And I see field "deviceInstallation" is not null
    Given Elastica populate
    And I want clean filled data
    Then I want add value to array key "fields" with "id"
    And I want add value to array key "fields" with "model"
    And I want add value to array key "fields" with "client"
    And I want add value to array key "fields" with "vehicle"
    And I want add value to array key "fields" with "status"
    And I want add value to array key "fields" with "imei"
    And I want add value to array key "fields" with "phone"
    And I want add value to array key "fields" with "installDate"
    And I want add value to array key "fields" with "lastActiveTime"
    Then I want export devices list
    And I see csv item number 18 field "Device ID" is not empty
    And I see csv item number 18 field "Model" filled with "FM3001"
    And I see csv item number 18 field "Client" filled with "client-name-2"
    And I see csv item number 18 field "Status" is not empty
    And I see csv item number 18 field "Phone number" filled with "+(375) 44444"
    And I see csv item number 18 field "Installed date" is not empty
    And response code is 200

  Scenario: I want import devices with vehicles
    Then I want upload file "devicesVehicles/importData" "csv" "851" "text/csv"
    And I want import devices and vehicles file
    And response code is 200
    And I see field "data/0/errors/0" filled with "Unknown device vendor"
    And I see field "data/0/errors/1" filled with "Device Phone - Required field"
    And I see field "data/0/errors/2" filled with "Unknown vehicle type"
    And I see field "data/0/deviceImsi" filled with "string(111111111)"
    And I see field "data/1/errors/0" filled with "Unknown device model"
    And I see field "data/1/deviceVendor" filled with "Ulbotech"
    And I see field "data/1/deviceModel" filled with "T30"
    And I see field "data/1/deviceImei" filled with "string(868323028803390)"
    And I see field "data/1/devicePhone" filled with 37254346547
    And I see field "data/1/clientId"
    And I see field "data/1/vehicleTitle" filled with 1705
    And I see field "data/1/vehicleRegNo" filled with "string(1705)"
    And I see field "data/1/vehicleModel" filled with "Mercedes Sprinter"
    And I see field "data/1/vehicleType" filled with "Bus"
    And I see field "data/3/errors/0" filled with "Device already installed into a different vehicle"
    And I see field "data/5/errors/0" filled with "Vehicle already has installed device"
    And I see field "data/6/errors/0" filled with "Vehicle alread belongs to another client"
    And I see field "data/7/errors/0" filled with "Unknown Client ID"
    And I see field "data/8/errors/0" filled with "Unknown vehicle RegNo but no vehicle details provided"