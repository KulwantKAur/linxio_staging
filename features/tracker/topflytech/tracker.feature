Feature: Tracker Topflytech

  Scenario: I want connect to tcp server
    When I want to connect to topflytech tcp server

  Scenario: I want send tcp data to API
    When I want fill "payload" field with "2525010015000a0866425035404484100441250331"
    Then I want to send topflytech tcp data to api with socket "test-topflytech-socket-id"
    Then I see field "response" filled with "252501000F000a0866425035404484"
    And I see field "imei" filled with "string(866425035404484)"
    When I want fill "payload" field with "2525020044b6a70866425035404484003c0e101e07d0001f49c000050100000021174518600000008d80db0020051308402400008e425d1c1743c42e07c2000000121234"
    Then I want to send topflytech tcp data to api with socket "test-topflytech-socket-id"
    Then I see field "response" filled with "252502000Fb6a70866425035404484"
    When I want fill "imei" field with "866425035404484"
    And I want fill "startDate" field with "2020-05-12T00:00:00+00:00"
    And I want fill "endDate" field with "2020-05-14T23:59:59+00:00"
    Then I want to get tracker data log
    And I see field "total" filled with "1"
    And I see field "data"
    And I see field "data/0/ts" filled with "2020-05-13T08:40:24+00:00"
    And I see field "data/0/createdAt"
    And I see field "data/0/gpsData/speed" filled with "0"
    And I see field "data/0/gpsData/angle" filled with "18"
    And I see field "data/0/gpsData/coordinates/lat" filled with "custom(-33.79566956)"
    And I see field "data/0/gpsData/coordinates/lng" filled with "custom(151.11079407)"
    And response code is 200

  Scenario: I want see TLW1AndTLD1AE tracker data logs
    Given There are following tracker payload from topflytech tracker with socket "test-topflytech-test-socket":
      | payload                                                                                                                                                                                                                                                              |
      | 2525010015000a0866425035404484100441250331                                                                                                                                                                                                                         |
      | 252502004400010866425035404484000A00FF2001000020C89600989910101010055501550000101005050005051010050558866B4276D6E342912AB441111505051010   |
    When I want fill "imei" field with "866425035404484"
    And I want fill "startDate" field with "2005-05-09T10:05:05+00:00"
    And I want fill "endDate" field with "2005-05-11T10:05:05+00:00"
    Then I want to get tracker data log
    And I see field "total" filled with "1"
    And I see field "data"
    And I see field "data/0/ts" filled with "2005-05-10T10:05:05+00:00"
    And I see field "data/0/createdAt"
    And I see field "data/0/gpsData/speed" filled with "111.5"
    And I see field "data/0/gpsData/angle" filled with "205"
    And I see field "data/0/gpsData/coordinates/lat" filled with "custom(22.52078438)"
    And I see field "data/0/gpsData/coordinates/lng" filled with "custom(113.91886902)"
    And I see field "data/0/payload" filled with "252502004400010866425035404484000A00FF2001000020C89600989910101010055501550000101005050005051010050558866B4276D6E342912AB441111505051010"
    And response code is 200

  Scenario: I want see TLD1DADE tracker data logs
    Given There are following tracker payload from topflytech tracker with socket "test-topflytech-test-socket":
      | payload                                                                                                                                                                                                                                                              |
      | 26260100190001088061689888888811101162501301010611                                                                                                                                                                                                                         |
      | 262602005300010880616898888888000A00FF2001000020C89600989910104FEE0000101005050005051010050558866B4276D6E342912AB44100000505101011151010101010101010101010101010101010   |
    When I want fill "imei" field with "880616898888888"
    And I want fill "startDate" field with "2005-05-09T10:05:05+00:00"
    And I want fill "endDate" field with "2005-05-11T10:05:05+00:00"
    Then I want to get tracker data log
    And I see field "total" filled with "1"
    And I see field "data"
    And I see field "data/0/ts" filled with "2005-05-10T10:05:05+00:00"
    And I see field "data/0/createdAt"
    And I see field "data/0/gpsData/speed" filled with "111.5"
    And I see field "data/0/gpsData/angle" filled with "205"
    And I see field "data/0/gpsData/coordinates/lat" filled with "custom(22.52078438)"
    And I see field "data/0/gpsData/coordinates/lng" filled with "custom(113.91886902)"
    And I see field "data/0/payload" filled with "262602005300010880616898888888000A00FF2001000020C89600989910104FEE0000101005050005051010050558866B4276D6E342912AB44100000505101011151010101010101010101010101010101010"
    And response code is 200

  Scenario: I want see TLP1 tracker data logs
    Given There are following tracker payload from topflytech tracker with socket "test-topflytech-test-socket":
      | payload                                                                                                                                                                                                                                                              |
      | 2727010017000108806168988888891016010207110111                                                                                                                                                                                                                         |
      | 272702004900010880616898888889490005051010050558866B4276D6E342912AB4411115050589989989910050334545101005050004000A00FF00FF200100980000FFFFFFFFFFFF   |
    When I want fill "imei" field with "880616898888889"
    And I want fill "startDate" field with "2005-05-09T10:05:05+00:00"
    And I want fill "endDate" field with "2005-05-11T10:05:05+00:00"
    Then I want to get tracker data log
    And I see field "total" filled with "1"
    And I see field "data"
    And I see field "data/0/ts" filled with "2005-05-10T10:05:05+00:00"
    And I see field "data/0/createdAt"
    And I see field "data/0/gpsData/speed" filled with "111.5"
    And I see field "data/0/gpsData/angle" filled with "205"
    And I see field "data/0/gpsData/coordinates/lat" filled with "custom(22.52078438)"
    And I see field "data/0/gpsData/coordinates/lng" filled with "custom(113.91886902)"
    And I see field "data/0/payload" filled with "272702004900010880616898888889490005051010050558866B4276D6E342912AB4411115050589989989910050334545101005050004000A00FF00FF200100980000FFFFFFFFFFFF"
    And response code is 200

  Scenario: I want check changed model according to payload protocol
    Given There are following tracker payload from topflytech tracker with socket "test-topflytech-test-socket":
      | payload                                                                                                                                                                                                                                                              |
      | 2525010015000a0866425035404484100441250331                                                                                                                                                                                                                         |
      | 252502004400010866425035404484000A00FF2001000020C89600989910101010055501550000101005050005051010050558866B4276D6E342912AB441111505051010   |
    Given I signed in as "super_admin" team "admin"
    Then I want fill "model" field with "TLD1-A-E"
    And I want get device list
    And I see field "total" filled with "1"
    And response code is 200
    Given There are following tracker payload from topflytech tracker with socket "test-topflytech-test-socket":
      | payload                                                                                                                                                                                                                                                            |
      | 262602005300010866425035404484000A00FF2001000020C89600989910104FEE0000101005050005051010050558866B4276D6E342912AB44100000505101011151010101010101010101010101010101010   |
    Then I want fill "model" field with "TLD1-A-E"
    And I want get device list
    And I see field "total" filled with "0"

  Scenario: I want get one vehicle with tracker data
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
    And I want fill device model for vehicle with name "TLP1-LF"
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
    Given There are following tracker payload from topflytech tracker with socket "test-topflytech-test-socket":
      | payload                                                                                                                                                                                                                                                              |
      | 2727010015000a0888888888888888100441250331                                                                                                                                                                                                                         |
      | 272704004900010888888888888888000005051010050558866B4276D6E342912AB44111150505899899899190A0334545101005058000000A00FF00FF200100980000FFFFFFFFFFFF   |
    When I want add value to array key "fields" with "device"
    Then I want to get vehicle by saved id
    And I see field "team/type" filled with "client"
    And I see field "device/trackerData/temperatureLevel" filled with "-32000"
    And I see field "device/trackerData/batteryVoltage" filled with "4500"
    And I see field "device/trackerData/externalVoltage" filled with "null"
    And I see field "device/trackerData/batteryVoltagePercentage" filled with "90"
    And I see field "device/trackerData/solarChargingStatus" filled with "false"
    Then response code is 200
    Then I want to get device of vehicle by saved id
    Given There are following tracker payload from topflytech tracker with socket "test-topflytech-test-socket":
      | payload                                                                                                                                                                                                                                                              |
      | 2727010015000a0888888888888888100441250331                                                                                                                                                                                                                         |
      | 272704004900010888888888888888000005051010060558866B4276D6E342912AB4411115050589989989913050334545101005058008000A00FF00FF200100980000FFFFFFFFFFFF   |
    # Need to update device dependency with last tracker history
    Given Elastica populate
    Then I want to get vehicle by saved id
    And I see field "team/type" filled with "client"
    And I see field "device/trackerData/temperatureLevel" filled with "80000"
    And I see field "device/trackerData/batteryVoltage" filled with "4500"
    And I see field "device/trackerData/externalVoltage" filled with "null"
    And I see field "device/trackerData/batteryVoltagePercentage" filled with "30"
    And I see field "device/trackerData/solarChargingStatus" filled with "true"
    Then response code is 200

  Scenario: I want to connect to udp server
    When I want to connect to topflytech udp server

  Scenario: I want to handle engineOnTime event
    Given I want to handle engineOnTime event
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
    Given There are following tracker payload from topflytech tracker with socket "test-topflytech-test-socket":
      | payload                                                                                                                                                                                                                                                              |
      | 252503000f02ea0865284042786776                                                                                                                                                                                                                         |
      | 2525020044b2a7086528404278677600780e1014012c00504ac000050100004020000000000000006b115800201103100006000022432732174315da05c2113300d31243   |
      | 2525020044b2a8086528404278677600780e1014012c005148c000050100004020000000000000006b12930020110310001600002c43d4311743c1dc05c2111300c11242   |
    Then I want to get vehicle by saved id
    And I see field "engineOnTime" filled with "10"

  Scenario: I want to handle OBDData for TLD1-DA-DE model
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
    And I want fill "imei" field with "880886898888888"
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
      | 262603000f02ea0880886898888888                                                                                                                                                                                                                         |
      | 262602020300010880886898888888000A00FF2001000020C89600989910104FEE0000101005050020112010050558866B4276D6E342912AB44100000505101011151010101010101010101010101010101010   |
    Given Elastica populate index "device"
    Then I want clean filled data
    Then I want fill "imei" field with "880886898888888"
    And I want get device list
    And I see field "data/0/trackerData/OBDData"
    And I see field "data/0/trackerData/OBDData/accumulatingFuel" filled with "269488144"
    And I see field "data/0/trackerData/OBDData/remainFuelRate" filled with "16"