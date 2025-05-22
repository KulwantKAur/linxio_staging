Feature: Route

  Background:
    Given insert Procedures

  Scenario: I want get vehicle routes
    Given Map service mapbox is ready to use
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
    When I want fill "dateFrom" field with "2019-05-30T18:55:30+00:00"
    When I want fill "dateTo" field with "2019-05-30T22:00:00+00:00"
    Then I want add value to array key "fields" with "driver"
    And I want add value to array key "fields" with "vehicle"
    And I want add value to array key "fields" with "coordinates"
    Given Calculate routes
    Then I want get vehicle routes
    And I see field "0/routes/0/type" filled with "stopped"
    And I see field "0/routes/0/coordinates/0/lat" filled with "custom(53.88410990)"
    And I see field "0/routes/0/coordinates/0/lng" filled with "custom(27.60147330)"
    And I see field "0/routes/0/duration" filled with "292"
    And I see field "0/routes/0/distance" filled with "null"
    And I see field "0/routes/0/driver" filled with "null"
    And I see field "0/routes/0/vehicle/id"
    And I see field "0/routes/0/avgSpeed" filled with "0"
    And I see field "0/routes/0/maxSpeed" filled with "0"
    And I see field "0/routes/0/address" filled with "Partizanskiy rayon, Minsk, City of Minsk, Belarus"
    When I want fill "dateFrom" field with "2019-05-30T18:55:30+00:00"
    And I want fill "dateTo" field with "2019-05-30T22:00:00+00:00"
    And I want add value to array key "fields" with "coordinates"
    And I want add value to array key "fields" with "coordinates.speed"
    And I want add value to array key "fields" with "coordinates.externalVoltage"
    And I want add value to array key "fields" with "coordinates.temperatureLevel"
    Then I want get vehicle routes
    And I see field "0/routes/0/coordinates/0/lat" filled with "custom(53.88410990)"
    And I see field "0/routes/0/coordinates/0/lng" filled with "custom(27.60147330)"
    And I see field "0/routes/0/coordinates/0/speed" filled with "0"
    And I see field "0/routes/0/coordinates/0/externalVoltage" filled with "12943"
    And I see field "0/routes/0/coordinates/0/temperatureLevel" filled with "null"
    Then I want get vehicle routes paginated
    And I see field "data/0/coordinates/0/lat" filled with "custom(53.88410990)"
    And I see field "data/0/coordinates/0/lng" filled with "custom(27.60147330)"
    And I see field "data/0/coordinates/0/speed" filled with "0"
    And I see field "data/0/coordinates/0/externalVoltage" filled with "12943"
    And I see field "data/0/coordinates/0/temperatureLevel" filled with "null"
    And I want add value to array key "fields" with "summary"
    Then I want get vehicle routes
    And I see field "routes/0/coordinates/0/lat" filled with "custom(53.88410990)"
    And I see field "routes/0/coordinates/0/lng" filled with "custom(27.60147330)"
    And I see field "routes/0/coordinates/0/speed" filled with "0"
    And I see field "routes/0/coordinates/0/externalVoltage" filled with "12943"
    And I see field "routes/0/coordinates/0/temperatureLevel" filled with "null"
    And I see field "summary/distance" filled with 0
    And I see field "summary/stopped" filled with 292
    And I see field "summary/startOdometer" filled with 10060
    And I see field "summary/endOdometer" filled with 10060
    And I see field "summary/idling"
    And I see field "summary/driving"

  Scenario: I want get vehicle routes with nullable coordinates
    Given Map service mapbox is ready to use
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
    When I want fill "payload" field with "00000000000004db080d0000016e03c8a35800107b1bed20256cae00000000000000f01507ef00f000c800710021002500303e094232281800004300004400000d06df0f00452a09b72b000031010704f100006465c700002e3e100046fa6a0c000bab7e01ee00000000000000000000016e03c8fd3000107b1bed20256cae00000000000000f01507ef00f001c8007100219c25003040094231fd1800004300004400000d06df0f00452a00002b000031010704f100006465c700002e3e100046fa6a0c000bab7e01ee00000000000000000000016e03c9a91000107b1bed20256cae00000000000000001507ef00f001c8007100219c250030400942325f1800004300004400000d06df0f00452a00002b000031010704f100006465c700000000100046fa6a0c000bab7e01ee00000000000000000000016e03ca02e800107b1bed20256cae00000000000000f00f04ef00f000c8007100064232a81800004300004400000d06df0f004504f100006465c700000000100046fa6a0c000bab7e01ee00000000000000000000016e0400f16800107b1bed20256cae00000000000000000f04ef00f000c8007100064232ff1800004300004400000d06df0f004504f100006465c700000000100046fa6a0c000bab7e01ee00000000000000000000016e0425cee800107b1bed20256cae00000000000000000f04ef00f000c800710006422ff21800004300004400000d06df0f004504f100006465c700000000100046fa6a0c000bab7e01ee00000000000000000000016e0425d2d000107b1bed20256cae00000000000000f00f04ef00f001c800710006422ff11800004300004400000d06df0f004504f100006465c700000000100046fa6a0c000bab7e01ee00000000000000000000016e0426829800000000000000000000000000000000f01507ef01f001c800710021fb2509304309423a471800004300004400000d06df0f00002a001f2b000031010704f100006465c700000000100046fa6a0c000bab8401ee00000000000000000000016e0426f7c800000000000000000000000000000000001507ef01f001c80071002106250030430942378f1800004300004400000d06df0f00002a003e2b000031010704f100006465c700000000100046fa6a0c000bab8401ee00000000000000000000016e04276cf800000000000000000000000000000000001507ef01f001c800710021f72509304109423ac71800004300004400000d06df0f00002a005a2b000031010704f100006465c700000000100046fa6a0c000bab8401ee00000000000000000000016e0427846800107ba84a202597c800d60062050003001507ef01f001c800710021ff2508304209423aac1800034300004400000d06df0f00002a00622b000031010704f100006465c700000000100046fa6a0c000bab8401ee00000000000000000000016e0427b73000107b88d8202594da010c0113040004001507ef01f001c800710021002520303f094239ec1800044300004400000d06df0f00002a006e2b000031010704f100006465c700000000100046fa810c000bab8401ee00000000000000000000016e0427bf0000107b7c26202589a700f20104050008001507ef01f001c800710021002520303f094239dd1800084300004400000d06df0f00002a006e2b000031010704f100006465c700000000100046faa60c000bab8401ee00000000000000000d00002fe4"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    When I want fill "dateFrom" field with "2019-10-24T18:55:30+00:00"
    When I want fill "dateTo" field with "2019-10-26T22:00:00+00:00"
    And I want add value to array key "fields" with "coordinates"
    And I want add value to array key "fields" with "coordinates.speed"
    Given Calculate routes
    Then I want get vehicle routes
    And I see field "0/routes/1/type" filled with "driving"
    And I see field "0/routes/1/coordinates/0/speed" filled with "0"
    And I see field "0/routes/1/coordinates/0/nullable" filled with "true"
    And I see field "0/routes/1/duration" filled with "81"
    And I see field "0/routes/1/distance" filled with "60"
    And I see field "0/routes/1/avgSpeed" filled with "3"
    And I see field "0/routes/1/maxSpeed" filled with "8"
    And I see field "0/routes/1/address" filled with "null"
    And I see field "0/routes/1/coordinates/4/nullable" filled with "false"

  Scenario: I want get vehicle routes with enabled location
    Given Map service mapbox is ready to use
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
    When I want set remembered team settings "mapApiOptions" with raw value
    """
      [2]
    """
    Then I want fill "dateFrom" field with "2019-05-30T18:55:30+00:00"
    And I want fill "dateTo" field with "2019-05-30T22:00:00+00:00"
    And I want add value to array key "fields" with "driver"
    And I want add value to array key "fields" with "vehicle"
    And I want add value to array key "fields" with "coordinates"
    Given Calculate routes
    Then I want get vehicle routes
    And I see field "0/routes/0/type" filled with "stopped"
    And I see field "0/routes/0/coordinates/0/lat" filled with "custom(53.88410990)"
    And I see field "0/routes/0/coordinates/0/lng" filled with "custom(27.60147330)"
    And I see field "0/routes/0/duration" filled with "292"
    And I see field "0/routes/0/distance" filled with "null"
    And I see field "0/routes/0/driver" filled with "null"
    And I see field "0/routes/0/vehicle/id"
    And I see field "0/routes/0/avgSpeed" filled with "0"
    And I see field "0/routes/0/maxSpeed" filled with "0"
    And I see field "0/routes/0/address" filled with "Partizanskiy rayon, Minsk, City of Minsk, Belarus"

  Scenario: I want get vehicle routes with switching of drivers
    Given Map service mapbox is ready to use
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
    Then I want set vehicle driver with current user
    Given Elastica populate
    When I want fill "payload" field with "000F383838383838383838383838383838"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response"
    And I see field "imei"
    When I want fill "payload" field with "00000000000004d608130000016b0a18fff8001073a68d201e100b00c801610e0000f00c05ef00f0001504c800450105b50006b6000442328f43000044000002f100006465100000274c000000016b0a1b1338001073a68d201e100b00c801610d0000f00c05ef00f0011504c800450105b50005b6000342322643000044000002f100006465100000274c000000016b0a1b3a48001073a68d201e100b00c801610d0000000c05ef00f0011504c800450105b50006b6000342322443000044000002f100006465100000274c000000016b0a1b6158001073a68d201e100b00c801610e0000000c05ef00f0011504c800450105b50006b6000342322643000044000002f100006465100000274c000000016b0a1b8868001073a68d201e100b00c801610e0000000c05ef00f0011504c800450105b50006b6000342322843000044000002f100006465100000274c000000016b0a1baf78001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342317c43000044000002f100006465100000274c000000016b0a1bd688001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b600034230ae43000044000002f100006465100000274c000000016b0a1bfd98001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50005b6000342307443000044000002f100006465100000274c000000016b0a1c0180001073a68d201e100b00c801610f0000f00c05ef00f0001504c800450105b50006b6000342307543000044000002f100006465100000274c000000016b0a1c1508001073a68d201e100b00c801610f0000f00c05ef00f0011504c800450105b50006b6000342304a43000044000002f100006465100000274c000000016b0a1c3c18001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342311443000044000002f100006465100000274c000000016b0a1c6328001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b600034231a343000044000002f100006465100000274c000000016b0a1c8a38001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b600034231f043000044000002f100006465100000274c000000016b0a1cb148001073a68d201e100b00c80161100000000c05ef00f0011504c800450105b50005b600034231fa43000044000002f100006465100000274c000000016b0a1cd858001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342322343000044000002f100006465100000274c000000016b0a1cff68001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342322643000044000002f100006465100000274c000000016b0a1d2678001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342322843000044000002f100006465100000274c000000016b0a1d4d88001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342322843000044000002f100006465100000274c000000016b0a1d7498001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b600034231ad43000044000002f100006465100000274c0013000015d1"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I want set vehicle driver with not current user
    When I want fill "payload" field with "000000000000004a08010000016c141b82b8005a1945ccebced3320000000000000000110c0100020003000400b300b4004504f0001505c801ef004f080409000c0a001442326b43107201f10000c545000100008c7d"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    When I want fill "dateFrom" field with "2019-05-30T18:55:30+00:00"
    When I want fill "dateTo" field with "2019-05-30T22:00:00+00:00"
    Then I want add value to array key "fields" with "driver"
    And I want add value to array key "fields" with "vehicle"
    And I want add value to array key "fields" with "coordinates"
    Given Calculate routes
    Then I want get vehicle routes
    And I see field "0/routes/0/type" filled with "stopped"
    And I see field "0/routes/0/coordinates/0/lat" filled with "custom(53.88410990)"
    And I see field "0/routes/0/coordinates/0/lng" filled with "custom(27.60147330)"
    And I see field "0/routes/0/duration" filled with "4462904"
    And I see field "0/routes/0/distance" filled with "null"
    And I see field "0/routes/0/driver"
    And I see field "0/routes/0/vehicle/id"
    And I see field "0/routes/0/avgSpeed" filled with "0"
    And I see field "0/routes/0/maxSpeed" filled with "0"
    And I see field "0/routes/0/address" filled with "null"
    When I want fill "dateFrom" field with "2019-05-30T18:55:30+00:00"
    And I want fill "dateTo" field with "2019-05-30T22:00:00+00:00"
    And I want add value to array key "fields" with "coordinates"
    And I want add value to array key "fields" with "coordinates.speed"
    And I want add value to array key "fields" with "coordinates.externalVoltage"
    And I want add value to array key "fields" with "coordinates.temperatureLevel"
    Given Calculate routes
    Then I want get vehicle routes
    And I see field "0/routes/0/coordinates/0/lat" filled with "custom(53.88410990)"
    And I see field "0/routes/0/coordinates/0/lng" filled with "custom(27.60147330)"
    And I see field "0/routes/0/coordinates/0/speed" filled with "0"
    And I see field "0/routes/0/coordinates/0/externalVoltage" filled with "12943"
    And I see field "0/routes/0/coordinates/0/temperatureLevel" filled with "null"

  Scenario: I want get driver routes with switching of vehicles
    Given Map service mapbox is ready to use
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
    Then I want set vehicle driver with current user
    Given Elastica populate
    When I want fill "payload" field with "000F383838383838383838383838383838"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response"
    And I see field "imei"
    When I want fill "payload" field with "00000000000004d608130000016b0a18fff8001073a68d201e100b00c801610e0000f00c05ef00f0001504c800450105b50006b6000442328f43000044000002f100006465100000274c000000016b0a1b1338001073a68d201e100b00c801610d0000f00c05ef00f0011504c800450105b50005b6000342322643000044000002f100006465100000274c000000016b0a1b3a48001073a68d201e100b00c801610d0000000c05ef00f0011504c800450105b50006b6000342322443000044000002f100006465100000274c000000016b0a1b6158001073a68d201e100b00c801610e0000000c05ef00f0011504c800450105b50006b6000342322643000044000002f100006465100000274c000000016b0a1b8868001073a68d201e100b00c801610e0000000c05ef00f0011504c800450105b50006b6000342322843000044000002f100006465100000274c000000016b0a1baf78001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342317c43000044000002f100006465100000274c000000016b0a1bd688001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b600034230ae43000044000002f100006465100000274c000000016b0a1bfd98001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50005b6000342307443000044000002f100006465100000274c000000016b0a1c0180001073a68d201e100b00c801610f0000f00c05ef00f0001504c800450105b50006b6000342307543000044000002f100006465100000274c000000016b0a1c1508001073a68d201e100b00c801610f0000f00c05ef00f0011504c800450105b50006b6000342304a43000044000002f100006465100000274c000000016b0a1c3c18001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342311443000044000002f100006465100000274c000000016b0a1c6328001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b600034231a343000044000002f100006465100000274c000000016b0a1c8a38001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b600034231f043000044000002f100006465100000274c000000016b0a1cb148001073a68d201e100b00c80161100000000c05ef00f0011504c800450105b50005b600034231fa43000044000002f100006465100000274c000000016b0a1cd858001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342322343000044000002f100006465100000274c000000016b0a1cff68001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342322643000044000002f100006465100000274c000000016b0a1d2678001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342322843000044000002f100006465100000274c000000016b0a1d4d88001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342322843000044000002f100006465100000274c000000016b0a1d7498001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b600034231ad43000044000002f100006465100000274c0013000015d1"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I want uninstall device
    Then I want to create vehicle and save id
    Then I want install device for vehicle
    Then I want set vehicle driver with current user
    When I want fill "payload" field with "000000000000004a08010000016c141b82b8005a1945ccebced3320000000000000000110c0100020003000400b300b4004504f0001505c801ef004f080409000c0a001442326b43107201f10000c545000100008c7d"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    When I want fill "dateFrom" field with "2019-05-30T18:55:30+00:00"
    When I want fill "dateTo" field with "2019-05-30T22:00:00+00:00"
    Then I want add value to array key "fields" with "driver"
    And I want add value to array key "fields" with "vehicle"
    And I want add value to array key "fields" with "coordinates"
    Given Calculate routes
    Then I want get vehicle routes
    And I see field "0/routes/0/type" filled with "stopped"
    And I see field "0/routes/0/coordinates/0/lat" filled with "custom(53.88410990)"
    And I see field "0/routes/0/coordinates/0/lng" filled with "custom(27.60147330)"
    And I see field "0/routes/0/duration" filled with "4462904"
    And I see field "0/routes/0/distance" filled with "null"
    And I see field "0/routes/0/driver"
    And I see field "0/routes/0/vehicle/id"
    And I see field "0/routes/0/avgSpeed" filled with "0"
    And I see field "0/routes/0/maxSpeed" filled with "0"
    And I see field "0/routes/0/address" filled with "null"

  Scenario: I want get vehicle routes according to client route settings
    Given I signed in as "super_admin" team "admin"
    And I want set admin team with role "admin" "admin" setting otp value 1
    Then I want fill "model" field with 2
    Then I want fill "type" field with "Car"
    Then I want fill "vin" field with 4
    Then I want fill "fuelType" field with 1
    Then I want fill "year" field with "2000"
    And I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    Then I want to create vehicle and save id
    And I see field "team/type" filled with "client"
    And I want set remembered team settings "ignoreStops" with raw value
    """
      {"enable":true,"value":300}
    """
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
    Given Calculate routes
    When I want fill "dateFrom" field with "2019-05-30T18:55:30+00:00"
    And I want fill "dateTo" field with "2019-05-30T22:00:00+00:00"
    And I want add value to array key "fields" with "coordinates"
    Then I want get vehicle routes
    And I see field "0/routes/0/type" filled with "driving"
    And I see field "0/routes/0/coordinates/0/lat" filled with "custom(53.88410990)"
    And I see field "0/routes/0/coordinates/0/lng" filled with "custom(27.60147330)"
    And I see field "0/routes/0/duration" filled with "292"
    And I see field "0/routes/0/distance" filled with "null"

  Scenario: I want update vehicle route
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
    Given Calculate routes
    When I want fill "dateFrom" field with "2019-05-30T18:55:30+00:00"
    When I want fill "dateTo" field with "2019-05-30T22:00:00+00:00"
    Then I want get vehicle routes and save first
    And response code is 200
    When I want fill "comment" field with "test comment"
    And I want fill "scope" field with "work"
    Then I want update saved route
    And response code is 200
    And I see field "comment" filled with "test comment"
    And I see field "scope" filled with "work"

  Scenario: I want get driver routes
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
    Then I want set vehicle driver with user of current team with date "2019-04-30 19:00:31"
    Given Elastica populate
    When I want fill "payload" field with "000F383838383838383838383838383838"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response"
    And I see field "imei"
    When I want fill "payload" field with "00000000000004d608130000016b0a18fff8001073a68d201e100b00c801610e0000f00c05ef00f0001504c800450105b50006b6000442328f43000044000002f100006465100000274c000000016b0a1b1338001073a68d201e100b00c801610d0000f00c05ef00f0011504c800450105b50005b6000342322643000044000002f100006465100000274c000000016b0a1b3a48001073a68d201e100b00c801610d0000000c05ef00f0011504c800450105b50006b6000342322443000044000002f100006465100000274c000000016b0a1b6158001073a68d201e100b00c801610e0000000c05ef00f0011504c800450105b50006b6000342322643000044000002f100006465100000274c000000016b0a1b8868001073a68d201e100b00c801610e0000000c05ef00f0011504c800450105b50006b6000342322843000044000002f100006465100000274c000000016b0a1baf78001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342317c43000044000002f100006465100000274c000000016b0a1bd688001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b600034230ae43000044000002f100006465100000274c000000016b0a1bfd98001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50005b6000342307443000044000002f100006465100000274c000000016b0a1c0180001073a68d201e100b00c801610f0000f00c05ef00f0001504c800450105b50006b6000342307543000044000002f100006465100000274c000000016b0a1c1508001073a68d201e100b00c801610f0000f00c05ef00f0011504c800450105b50006b6000342304a43000044000002f100006465100000274c000000016b0a1c3c18001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342311443000044000002f100006465100000274c000000016b0a1c6328001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b600034231a343000044000002f100006465100000274c000000016b0a1c8a38001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b600034231f043000044000002f100006465100000274c000000016b0a1cb148001073a68d201e100b00c80161100000000c05ef00f0011504c800450105b50005b600034231fa43000044000002f100006465100000274c000000016b0a1cd858001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342322343000044000002f100006465100000274c000000016b0a1cff68001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342322643000044000002f100006465100000274c000000016b0a1d2678001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342322843000044000002f100006465100000274c000000016b0a1d4d88001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342322843000044000002f100006465100000274c000000016b0a1d7498001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b600034231ad43000044000002f100006465100000274c0013000015d1"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Given Calculate routes
    When I want fill "dateFrom" field with "2019-05-30T18:55:30+00:00"
    When I want fill "dateTo" field with "2019-05-30T22:00:00+00:00"
    Then I want add value to array key "fields" with "driver"
    Then I want get driver routes
    And I see field "0/routes/0/type" filled with "stopped"
    And I see field "0/routes/0/duration" filled with "292"
    And I see field "0/routes/0/distance" filled with "null"
    And I see field "0/routes/0/driver/name" filled with "client-contact-name-0"
    And I see field "0/routes/0/avgSpeed" filled with "0"
    And I see field "0/routes/0/maxSpeed" filled with "0"
    And I see field "0/routes/0/pointStart/address"
    And I see field "0/routes/0/pointFinish/address"
    Then I want get driver routes paginated
    And I see field "data/0/type" filled with "stopped"
    And I see field "data/0/duration" filled with "292"
    And I see field "data/0/distance" filled with "null"
    And I see field "data/0/driver/name" filled with "client-contact-name-0"
    And I see field "data/0/avgSpeed" filled with "0"
    And I see field "data/0/maxSpeed" filled with "0"
    And I see field "data/0/pointStart/address"
    And I see field "data/0/pointFinish/address"

  Scenario: I want get vehicle routes with oldest tracker data
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
    When I want fill "payload" field with "00000000000004da08110000016cbe137938001067a81c2021f50d00c800b4050006000f06ef01f001c8002101250e300a061800064300004400000d06832a00dd31000f03c7000001f81000089a810c0000b153000000016cbe13cb40001067a8d32021f5d500c800b4060000ef0f06ef00f001c80021fd25003009061800004300004400000d06832a00f031000f03c7000001fa1000089a830c0000b158000000016cbe144070001067a8d32021f5d500c800b4090000000f06ef00f001c80021002500300d061800004300004400000d06832a00f131000f03c7000001fa1000089a830c0000b158000000016cbe14b5a0001067a8d32021f5d500c800b40b0000000a03ef00f001c800041800004300004400000d068303c7000000001000089a830c0000b158000000016cbe1503c0001067a8d32021f5d500c800b40c0000f00a03ef00f000c800041800004300004400000d068303c7000000001000089a830c0000b158000000016cbe1d6060001067a8d32021f5d500c800b40d0000f00a03ef00f001c800041800004300004400000d068303c7000000001000089a830c0000b158000000016cbe1d8b58001067a8d32021f5d500c800b40d0000ef0f06ef01f001c80021002500300e061800004300004400000d06832a000031000f03c7000000001000089a830c0000b158000000016cbe1dc208001067a9262021f7fb00ca014e0d0006000f06ef01f001c80021002502300d061800064300004400000d06832a000c31000f03c7000000001000089a830c0000b15b000000016cbe1dc5f0001067a85e2021f8d400ca01580c000a000f06ef01f001c80021002502300d0618000a4300004400000d06832a000c31000f03c7000000001000089a830c0000b15b000000016cbe1de148001067a1a9202202ca00cb01540d0014000f06ef01f001c800210f250f300c061800144300004400000d06832a001431000f03c7000000241000089aa70c0000b15c000000016cbe1df0e80010679c42202209a000cc01490d000e000f06ef01f001c80021002517300c0618000e4300004400000d06832a001831000f03c70000003a1000089abd0c0000b15d000000016cbe1e0470001067939a2022120600ce01480d001b000f06ef01f001c8002103251a300b0618001b4300004400000d06822a002031000f03c7000000521000089ad50c0000b15e000000016cbe1e141000106785cc20221d3800cf01400d0025000f06ef01f001c80021032525300c061800254300004400000d06822a002431000f03c7000000771000089afa0c0000b161000000016cbe1e27980010676f56202229ea00d201370d001a000f06ef01f001c80021ff2527300b0618001a4300004400000d06822a002831000f03c7000000ae1000089b310c0000b166000000016cbe1e33500010676a5320222cc800d301330d0010000f06ef01f001c80021fe2512300b061800104300004400000d06822a002c31000f03c7000000c01000089b430c0000b167000000016cbe1e3f0800106761dc202230c000d401310d001c000f06ef01f001c80021fe2512300b0618001c4300004400000d06822a002c31000f03c7000000cc1000089b4f0c0000b167000000016cbe1e4ac000106754f82022357000d401260d0020000f06ef01f001c8002103251f300b061800204300004400000d06822a003031000f03c7000000e51000089b680c0000b169001100006d87"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Given Calculate routes
    When I want fill "dateFrom" field with "2019-07-10T08:00:00+00:00"
    When I want fill "dateTo" field with "2019-09-11T08:00:00+00:00"
    Then I want add value to array key "fields" with "driver"
    And I want add value to array key "fields" with "vehicle"
    Then I want get vehicle routes
    And I see field "0/routes/0/type" filled with "driving"
    And I see field "0/routes/0/duration" filled with "21"
    And I see field "0/routes/0/distance" filled with "2"
    And I see field "0/routes/0/driver" filled with "null"
    And I see field "0/routes/0/vehicle/id"
    And I see field "0/routes/0/avgSpeed" filled with "3"
    And I see field "0/routes/0/maxSpeed" filled with "6"
    When I want fill "payload" field with "000000000000010e08050000016d1a6090180056886a9ee9674c04006f012a0e0000ef0c06ef00f00050001504c800450106b5000db60007423157180000430f5444000800000000016d1a6084600056886a9ee9674c04006f012a0e0000f00c06ef01f00050011504c800450106b5000db6000742314f180000430f5444000800000000016d1a5f8290005688674ce9674b3c00000000000000ef0805ef01f0015000c8024503034231ae430f5444000800000000016d1a5f7ac0005688674ce9674b3c00000000000000f00805ef00f0015000c8024503034231d1430f5444000800000000016d1a5ea3e8005688674ce9674b3c00000000000000000805ef00f0005000c8024503034231d1430f5444000800000500005763"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Given Calculate routes
    When I want fill "dateFrom" field with "2019-07-10T08:00:00+00:00"
    When I want fill "dateTo" field with "2019-09-11T08:00:00+00:00"
    Then I want get vehicle routes
    And I see field "0/routes/0/type" filled with "driving"
    And I see field "0/routes/0/duration" filled with "21"
    And I see field "0/routes/0/distance" filled with "2"
    And I see field "0/routes/0/driver" filled with "null"
    And I see field "0/routes/0/vehicle/id"
    And I see field "0/routes/0/avgSpeed" filled with "3"
    And I see field "0/routes/0/maxSpeed" filled with "6"

  Scenario: I want get vehicle routes with newest tracker data
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
    When I want fill "payload" field with "000000000000010e08050000016d1a6090180056886a9ee9674c04006f012a0e0000ef0c06ef00f00050001504c800450106b5000db60007423157180000430f5444000800000000016d1a6084600056886a9ee9674c04006f012a0e0000f00c06ef01f00050011504c800450106b5000db6000742314f180000430f5444000800000000016d1a5f8290005688674ce9674b3c00000000000000ef0805ef01f0015000c8024503034231ae430f5444000800000000016d1a5f7ac0005688674ce9674b3c00000000000000f00805ef00f0015000c8024503034231d1430f5444000800000000016d1a5ea3e8005688674ce9674b3c00000000000000000805ef00f0005000c8024503034231d1430f5444000800000500005763"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Given Calculate routes
    When I want fill "dateFrom" field with "2019-07-10T08:00:00+00:00"
    When I want fill "dateTo" field with "2019-09-11T08:00:00+00:00"
    Then I want add value to array key "fields" with "driver"
    And I want add value to array key "fields" with "vehicle"
    Then I want get vehicle routes
    And I see field "0/routes/0/type" filled with "stopped"
    And I see field "0/routes/0/duration" filled with "57"
    And I see field "0/routes/0/distance" filled with "null"
    And I see field "0/routes/0/driver" filled with "null"
    And I see field "0/routes/0/vehicle/id"
    And I see field "0/routes/0/avgSpeed" filled with "0"
    And I see field "0/routes/0/maxSpeed" filled with "0"
    When I want fill "payload" field with "00000000000004da08110000016cbe137938001067a81c2021f50d00c800b4050006000f06ef01f001c8002101250e300a061800064300004400000d06832a00dd31000f03c7000001f81000089a810c0000b153000000016cbe13cb40001067a8d32021f5d500c800b4060000ef0f06ef00f001c80021fd25003009061800004300004400000d06832a00f031000f03c7000001fa1000089a830c0000b158000000016cbe144070001067a8d32021f5d500c800b4090000000f06ef00f001c80021002500300d061800004300004400000d06832a00f131000f03c7000001fa1000089a830c0000b158000000016cbe14b5a0001067a8d32021f5d500c800b40b0000000a03ef00f001c800041800004300004400000d068303c7000000001000089a830c0000b158000000016cbe1503c0001067a8d32021f5d500c800b40c0000f00a03ef00f000c800041800004300004400000d068303c7000000001000089a830c0000b158000000016cbe1d6060001067a8d32021f5d500c800b40d0000f00a03ef00f001c800041800004300004400000d068303c7000000001000089a830c0000b158000000016cbe1d8b58001067a8d32021f5d500c800b40d0000ef0f06ef01f001c80021002500300e061800004300004400000d06832a000031000f03c7000000001000089a830c0000b158000000016cbe1dc208001067a9262021f7fb00ca014e0d0006000f06ef01f001c80021002502300d061800064300004400000d06832a000c31000f03c7000000001000089a830c0000b15b000000016cbe1dc5f0001067a85e2021f8d400ca01580c000a000f06ef01f001c80021002502300d0618000a4300004400000d06832a000c31000f03c7000000001000089a830c0000b15b000000016cbe1de148001067a1a9202202ca00cb01540d0014000f06ef01f001c800210f250f300c061800144300004400000d06832a001431000f03c7000000241000089aa70c0000b15c000000016cbe1df0e80010679c42202209a000cc01490d000e000f06ef01f001c80021002517300c0618000e4300004400000d06832a001831000f03c70000003a1000089abd0c0000b15d000000016cbe1e0470001067939a2022120600ce01480d001b000f06ef01f001c8002103251a300b0618001b4300004400000d06822a002031000f03c7000000521000089ad50c0000b15e000000016cbe1e141000106785cc20221d3800cf01400d0025000f06ef01f001c80021032525300c061800254300004400000d06822a002431000f03c7000000771000089afa0c0000b161000000016cbe1e27980010676f56202229ea00d201370d001a000f06ef01f001c80021ff2527300b0618001a4300004400000d06822a002831000f03c7000000ae1000089b310c0000b166000000016cbe1e33500010676a5320222cc800d301330d0010000f06ef01f001c80021fe2512300b061800104300004400000d06822a002c31000f03c7000000c01000089b430c0000b167000000016cbe1e3f0800106761dc202230c000d401310d001c000f06ef01f001c80021fe2512300b0618001c4300004400000d06822a002c31000f03c7000000cc1000089b4f0c0000b167000000016cbe1e4ac000106754f82022357000d401260d0020000f06ef01f001c8002103251f300b061800204300004400000d06822a003031000f03c7000000e51000089b680c0000b169001100006d87"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Given Calculate routes
    When I want fill "dateFrom" field with "2019-07-10T08:00:00+00:00"
    When I want fill "dateTo" field with "2019-09-11T08:00:00+00:00"
    Then I want get vehicle routes
    And I see field "0/routes/0/type" filled with "driving"
    And I see field "0/routes/0/duration" filled with "21"
    And I see field "0/routes/0/driver" filled with "null"
    And I see field "0/routes/0/vehicle/id"
    And I see field "0/routes/0/avgSpeed" filled with "6"
    And I see field "0/routes/0/maxSpeed" filled with "6"

  Scenario: I want get route by id
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
    Given Calculate routes
    When I want fill "dateFrom" field with "2019-05-30T18:55:30+00:00"
    When I want fill "dateTo" field with "2019-05-30T22:00:00+00:00"
    Then I want get vehicle routes and save first
    And response code is 200
    Then I want get route by saved id
    And response code is 200
    And I see field "id"
    And I see field "pointStart"
    Then I want clean filled data
    Then I want fill "fields.0" field with "coordinates"
    And I want get route by saved id with query params
    And response code is 200
    And I see field "id"
    And I see field "pointStart"
    And I see field "coordinates"

  Scenario: I want get vehicle/driver routes history
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
    Then I want set vehicle driver with current user
    Given Elastica populate
    When I want fill "payload" field with "000F383838383838383838383838383838"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response"
    And I see field "imei"
    When I want fill "payload" field with "00000000000004d608130000016b0a18fff8001073a68d201e100b00c801610e0000f00c05ef00f0001504c800450105b50006b6000442328f43000044000002f100006465100000274c000000016b0a1b1338001073a68d201e100b00c801610d0000f00c05ef00f0011504c800450105b50005b6000342322643000044000002f100006465100000274c000000016b0a1b3a48001073a68d201e100b00c801610d0000000c05ef00f0011504c800450105b50006b6000342322443000044000002f100006465100000274c000000016b0a1b6158001073a68d201e100b00c801610e0000000c05ef00f0011504c800450105b50006b6000342322643000044000002f100006465100000274c000000016b0a1b8868001073a68d201e100b00c801610e0000000c05ef00f0011504c800450105b50006b6000342322843000044000002f100006465100000274c000000016b0a1baf78001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342317c43000044000002f100006465100000274c000000016b0a1bd688001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b600034230ae43000044000002f100006465100000274c000000016b0a1bfd98001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50005b6000342307443000044000002f100006465100000274c000000016b0a1c0180001073a68d201e100b00c801610f0000f00c05ef00f0001504c800450105b50006b6000342307543000044000002f100006465100000274c000000016b0a1c1508001073a68d201e100b00c801610f0000f00c05ef00f0011504c800450105b50006b6000342304a43000044000002f100006465100000274c000000016b0a1c3c18001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342311443000044000002f100006465100000274c000000016b0a1c6328001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b600034231a343000044000002f100006465100000274c000000016b0a1c8a38001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b600034231f043000044000002f100006465100000274c000000016b0a1cb148001073a68d201e100b00c80161100000000c05ef00f0011504c800450105b50005b600034231fa43000044000002f100006465100000274c000000016b0a1cd858001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342322343000044000002f100006465100000274c000000016b0a1cff68001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342322643000044000002f100006465100000274c000000016b0a1d2678001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342322843000044000002f100006465100000274c000000016b0a1d4d88001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342322843000044000002f100006465100000274c000000016b0a1d7498001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b600034231ad43000044000002f100006465100000274c0013000015d1"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Given Calculate routes
    When I signed with email "client-user-0@ocsico.com"
    Then I want fill "dateFrom" field with "2017-05-30T18:55:30+00:00"
    And I want fill "dateTo" field with "2019-11-30T22:00:00+00:00"
    And I want add value to array key "fields" with "driverId"
    And I want add value to array key "fields" with "vehicleId"
    Then I want get vehicles and drivers routes history
    And I see field "0/vehicleId"
    And I see field "0/routes"
    And I see field "0/routes/0/type" filled with "stopped"

  Scenario: I want test route report
    Given I signed in as "admin" team "client" and teamId 2
    And I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test group"
    And I want to create vehicle group and save id
    And I want fill "name" field with "test depot"
    And I want to create depot and save id
    Then I want fill "model" field with 2
    Then I want fill "type" field with "Car"
    Then I want fill "vin" field with 4
    Then I want fill "regNo" field with "testingRegNo"
    Then I want fill "fuelType" field with 1
    Then I want fill "year" field with "2000"
    And I want fill depotId by saved Id
    And I want fill vehicle group id
    Then I want to create vehicle and save id
    And I see field "team/type" filled with "client"
    Given I signed in as "super_admin" team "admin"
    And I want fill device model for vehicle with name "FM3001"
    And I want fill "sn" field with "test sn"
    And I want fill "status" field with "inStock"
    And I want fill "port" field with 25
    And I want fill "imei" field with "888888888888888"
    And I want fill "phone" field with "+(375) 245535454"
    Then I want to create device for vehicle and save id
    And Response code is 200
    Given I signed in as "admin" team "client" and teamId 2
    Then I want install device for vehicle
    And I want set vehicle driver with user of current team with date "2019-05-29 08:19:38"
    And Response code is 200
    Given Elastica populate
    And I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test area"
    And I want fill "status" field with "active"
    And I want fill area test coordinates "54.072912" "27.096977"
    And I want fill area test coordinates "54.145370" "28.123750"
    And I want fill area test coordinates "53.721726" "28.140802"
    And I want fill area test coordinates "53.747721" "26.932842"
    And I want fill area test coordinates "54.072912" "27.096977"
    And I want to create area and save id
    And I see field "name" filled with "test area"
    When I want fill "payload" field with "000F383838383838383838383838383838"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response"
    And I see field "imei"
    When I want fill "payload" field with "000000000000020708050000016e3fc5194a0110653182202243f500ed01650f0025fd1709ef01f001c800710021f8252e3014fd02fe10094236ee1800254300004400000d06d10f003b2a02992b000031039004f100006465c7000013ee10005095700c000d40e301ee00000000000000000000016e3fc519540110653182202243f500ed01650f0025f31607ef01f001c800710021f8252e30140a4236ee1800254300004400000d06d10f003b2a02992b0000310390f303e804f100006465c7000013ee10005095700c000d40e301ee00000000000000000000016e3fc51d2801106530fd2022473600ed016510001dfd1709ef01f001c800710021f8252e3014fd02fe16094236ea18001d4300004400000d06d10f003b2a02992b000031039004f100006465c7000013f710005095790c000d40e301ee00000000000000000000016e3fc51d3201106530fd2022473600ed016510001df31607ef01f001c800710021f8252e30140a4236ea18001d4300004400000d06d10f003b2a02992b0000310390f303e804f100006465c7000013f710005095790c000d40e401ee00000000000000000000016e3fc51d3c01106530fd2022473600ed016510001dff1608ef01f001c800710021f8252e3014ff1d094236ea18001d4300004400000d06d10f003b2a02992b000031039004f100006465c7000013f710005095790c000d40e401ee00000000000000000500003ca7"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Given Calculate routes
    Then I want clean filled data
    And I want fill "startDate" field with "2018-05-30T18:55:30+00:00"
    And I want fill "endDate" field with "2020-05-30T22:00:00+00:00"
    And I want get route report with type json
    And Response code is 200
    And I see field "total" filled with 1
    And I see field "data/0/driver_name" filled with "client-contact-name-0 client-surname-name-0"
    And I see field "data/0/groups" filled with "test group"
    And I see field "data/0/depot_name" filled with "test depot"
    And I see field "data/0/started_at" filled with "2019-11-06 08:11:52"
    And I see field "data/0/finished_at" filled with "2019-11-06 08:11:53"
    And I see field "data/0/start_areas_name" filled with "test area"
    And I see field "data/0/finish_areas_name" filled with "test area"
    And I see field "data/0/distance" filled with 9
    And I see field "data/0/start_odometer" filled with "string(5281136)"
    And I see field "data/0/finish_odometer" filled with "string(5281145)"
    And I see field "data/0/driving_time" filled with "string(1)"
    And I see field "data/0/avg_speed" filled with 33
    And I see field "data/0/max_speed" filled with 37
    Then I want fill vehicle id
    And I want get route report with type json
    And Response code is 200
    And I see field "total" filled with 1
    And I see field "data/0/driver_name" filled with "client-contact-name-0 client-surname-name-0"
    And I see field "data/0/groups" filled with "test group"
    Then I want get route report vehicle list
    And Response code is 200
    And I see field "total" filled with 1
    And I see field "data/0/regNo" filled with "testingRegNo"
    Then I want fill "vehicleId" field with 999
    And I want get route report with type json
    And Response code is 200
    And I see field "total" filled with 0

  Scenario: I want test route stops
    Given I signed in as "admin" team "client" and teamId 2
    And I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test group"
    And I want to create vehicle group and save id
    And I want fill "name" field with "test depot"
    And I want to create depot and save id
    Then I want fill "model" field with 2
    Then I want fill "type" field with "Car"
    Then I want fill "vin" field with 4
    Then I want fill "regNo" field with "testingRegNo"
    Then I want fill "fuelType" field with 1
    Then I want fill "year" field with "2000"
    And I want fill depotId by saved Id
    And I want fill vehicle group id
    Then I want to create vehicle and save id
    And I see field "team/type" filled with "client"
    Given I signed in as "super_admin" team "admin"
    And I want fill device model for vehicle with name "FM3001"
    And I want fill "sn" field with "test sn"
    And I want fill "status" field with "inStock"
    And I want fill "port" field with 25
    And I want fill "imei" field with "888888888888888"
    And I want fill "phone" field with "+(375) 245535454"
    Then I want to create device for vehicle and save id
    And Response code is 200
    Given I signed in as "admin" team "client" and teamId 2
    Then I want install device for vehicle
    And Response code is 200
    And I want set vehicle driver with user of current team with date "2019-05-29 08:19:38"
    Given Elastica populate
    And I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test area"
    And I want fill "status" field with "active"
    And I want fill area test coordinates "54.072912" "27.096977"
    And I want fill area test coordinates "54.145370" "28.123750"
    And I want fill area test coordinates "53.721726" "28.140802"
    And I want fill area test coordinates "53.747721" "26.932842"
    And I want fill area test coordinates "54.072912" "27.096977"
    And I want to create area and save id
    And I see field "name" filled with "test area"
    When I want fill "payload" field with "000F383838383838383838383838383838"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response"
    And I see field "imei"
    When I want fill "payload" field with "00000000000004d608130000016b0a18fff8001073a68d201e100b00c801610e0000f00c05ef00f0001504c800450105b50006b6000442328f43000044000002f100006465100000274c000000016b0a1b1338001073a68d201e100b00c801610d0000f00c05ef00f0011504c800450105b50005b6000342322643000044000002f100006465100000274c000000016b0a1b3a48001073a68d201e100b00c801610d0000000c05ef00f0011504c800450105b50006b6000342322443000044000002f100006465100000274c000000016b0a1b6158001073a68d201e100b00c801610e0000000c05ef00f0011504c800450105b50006b6000342322643000044000002f100006465100000274c000000016b0a1b8868001073a68d201e100b00c801610e0000000c05ef00f0011504c800450105b50006b6000342322843000044000002f100006465100000274c000000016b0a1baf78001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342317c43000044000002f100006465100000274c000000016b0a1bd688001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b600034230ae43000044000002f100006465100000274c000000016b0a1bfd98001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50005b6000342307443000044000002f100006465100000274c000000016b0a1c0180001073a68d201e100b00c801610f0000f00c05ef00f0001504c800450105b50006b6000342307543000044000002f100006465100000274c000000016b0a1c1508001073a68d201e100b00c801610f0000f00c05ef00f0011504c800450105b50006b6000342304a43000044000002f100006465100000274c000000016b0a1c3c18001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342311443000044000002f100006465100000274c000000016b0a1c6328001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b600034231a343000044000002f100006465100000274c000000016b0a1c8a38001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b600034231f043000044000002f100006465100000274c000000016b0a1cb148001073a68d201e100b00c80161100000000c05ef00f0011504c800450105b50005b600034231fa43000044000002f100006465100000274c000000016b0a1cd858001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342322343000044000002f100006465100000274c000000016b0a1cff68001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342322643000044000002f100006465100000274c000000016b0a1d2678001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342322843000044000002f100006465100000274c000000016b0a1d4d88001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342322843000044000002f100006465100000274c000000016b0a1d7498001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b600034231ad43000044000002f100006465100000274c0013000015d1"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Given Calculate routes
    Then I want clean filled data
    And I want fill "startDate" field with "2018-05-30T18:55:30+00:00"
    And I want fill "endDate" field with "2020-05-30T22:00:00+00:00"
    And I want get route stops with type json
    And Response code is 200
    And I see field "total" filled with 1
    And I see field "data/0/driver_name" filled with "client-contact-name-0 client-surname-name-0"
    And I see field "data/0/groups" filled with "test group"
    And I see field "data/0/depot_name" filled with "test depot"
    And I see field "data/0/started_at" filled with "2019-05-30 18:55:39"
    And I see field "data/0/finished_at" filled with "2019-05-30 19:00:31"
    And I see field "data/0/areas_name" filled with "test area"
    And I see field "data/0/finish_odometer" filled with "string(10060)"
    And I see field "data/0/parking_time" filled with "string(292)"
    Then I want fill vehicle id
    And I want get route stops with type json
    And Response code is 200
    And I see field "total" filled with 1
    And I see field "data/0/driver_name" filled with "client-contact-name-0 client-surname-name-0"
    And I see field "data/0/groups" filled with "test group"
    Then I want get route stops vehicle list
    And Response code is 200
    And I see field "total" filled with 1
    And I see field "data/0/regNo" filled with "testingRegNo"
    Then I want fill "vehicleId" field with 999
    And I want get route stops with type json
    And Response code is 200
    And I see field "total" filled with 0

  Scenario: I want route coordinates recalculate
    Given I signed in as "super_admin" team "admin"
    And I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test group"
    And I want to create vehicle group and save id
    And I want fill "name" field with "test depot"
    And I want to create depot and save id
    Then I want fill "model" field with 2
    Then I want fill "type" field with "Car"
    Then I want fill "vin" field with 4
    Then I want fill "regNo" field with "testingRegNo"
    Then I want fill "fuelType" field with 1
    Then I want fill "year" field with "2000"
    And I want fill depotId by saved Id
    And I want fill vehicle group id
    Then I want to create vehicle and save id
    And I see field "team/type" filled with "client"
    And I want fill device model for vehicle with name "FM3001"
    And I want fill "sn" field with "test sn"
    And I want fill "status" field with "inStock"
    And I want fill "port" field with 25
    And I want fill "imei" field with "888888888888888"
    And I want fill "phone" field with "+(375) 245535454"
    Then I want to create device for vehicle and save id
    Then I want install device for vehicle
    And I want set vehicle driver with user of current team with date "2019-05-29 08:19:38"
    Given Elastica populate
    When I want fill "payload" field with "000F383838383838383838383838383838"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response"
    And I see field "imei"
    When I want fill "payload" field with "0000000000000495080c0000016ef0fec9ea00107b1952202573e800000000000000f00f04ef00f001c8007100064230751800004300004400000d06c10f004c04f100006465c7000000001000659c2e0c0010c09101ee00000000000000000000016ef0ff3f1000107b1952202573e800000000000000000f04ef00f001c800710006422eac1800004300004400000d06c10f004c04f100006465c7000000001000659c2e0c0010c09101ee00000000000000000000016ef101a45800000000000000000000000000000000f01507ef01f001c800710021012500302209423b7a1800004300004400000d06c10f00002a00952b00003104c004f100006465c7000000001000659c2e0c0010c0b801ee00000000000000000000016ef102198800000000000000000000000000000000001507ef01f001c80071002103250d302209423b351800004300004400000d06c10f00002a00b52b00003104c004f100006465c7000000001000659c2e0c0010c0b801ee00000000000000000000016ef10263c000107baa7020259e4a00c70067050005001507ef01f001c800710021fd250a301f09423af21800054300004400000d06c10f00002a00c92b00003104c004f100006465c7000000001000659c2e0c0010c0b801ee00000000000000000000016ef1028ad000107b91a2202594fb00ea00f205000b001507ef01f001c800710021012515301e09423b5918000b4300004400000d06c10f00002a00d12b00003104c004f100006465c70000001e1000659c4c0c0010c0b801ee00000000000000000000016ef1029a7000107b7d5220258eed00ea00f5050020001507ef01f001c800710021002521301e09423b471800204300004400000d06c10f00002a00d52b00003104c004f100006465c7000000471000659c750c0010c0ba01ee00000000000000000000016ef102adf800107b5f70202585ae00eb00f4050027001507ef01f001c800710021002527301e09423b3f1800274300004400000d06c10f00002a00d92b00003104c004f100006465c7000000801000659cae0c0010c0bf01ee00000000000000000000016ef102c18000107b3f8a20257dce00ec00f5060027001507ef01f001c80071002100252a302009423b3f1800274300004400000d06c10f00002a00e12b00003104c004f100006465c7000000ba1000659ce80c0010c0c401ee00000000000000000000016ef102d50800107b238b202574c100f000f4060025001507ef01f001c800710021002528302009423b271800254300004400000d06c10f00002a00e52b00003104c004f100006465c7000000f11000659d1f0c0010c0ca01ee00000000000000000000016ef102f44800107b039420256f2800e200f5080000001507ef01f001c80071002101251a302009423a271800004300004400000d06c10f00002a00ed2b00003104c004f100006465c7000001371000659d650c0010c0cf01ee00000000000000000000016ef10307d000107affff20256dfc00db0100090007001507ef01f001c80071002103250130200942398a1800074300004400000d06c10f00002a00f12b00003104c004f100006465c7000001371000659d650c0010c0d001ee00000000000000000c00003074"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Given Calculate routes
    Then I want clean filled data
    When I want fill "dateFrom" field with "2019-05-30T18:55:30+00:00"
    When I want fill "dateTo" field with "2019-12-30T22:00:00+00:00"
    Then I want add value to array key "fields" with "driver"
    Then I want get vehicle routes
    And Response code is 200
    And I see field "0/routes/0/type" filled with "stopped"
    And I see field "0/routes/0/pointStart/lastCoordinates/lat" filled with 53.9325416
    And I see field "0/routes/0/pointStart/lastCoordinates/lng" filled with 27.6502866
    And I see field "0/routes/0/pointStart/lastCoordinates/ts" filled with "2019-12-10T18:07:40+00:00"
    And I see field "0/routes/0/pointFinish/lastCoordinates/lat" filled with 53.9336266
    And I see field "0/routes/0/pointFinish/lastCoordinates/lng" filled with 27.6540016
    And I see field "0/routes/0/pointFinish/lastCoordinates/ts" filled with "2019-12-10T18:10:47+00:00"
    And I see field "0/routes/0/pointStart/address"
    And I see field "0/routes/0/pointFinish/address"
    And I see field "0/routes/1/type" filled with "driving"
    And I see field "0/routes/1/pointStart/lastCoordinates/lat" filled with 53.9325416
    And I see field "0/routes/1/pointStart/lastCoordinates/lng" filled with 27.6502866
    And I see field "0/routes/1/pointStart/lastCoordinates/ts" filled with "2019-12-10T18:10:47+00:00"
    And I see field "0/routes/1/pointFinish/lastCoordinates/lat" filled with 53.93239
    And I see field "0/routes/1/pointFinish/lastCoordinates/lng" filled with 27.6496383
    And I see field "0/routes/1/pointFinish/lastCoordinates/ts" filled with "2019-12-10T18:12:18+00:00"

  Scenario: I want test fbt report
    Given I signed in as "admin" team "client" and teamId 2
    And I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test group"
    And I want to create vehicle group and save id
    And I want fill "name" field with "test depot"
    And I want to create depot and save id
    Then I want fill "model" field with 2
    Then I want fill "type" field with "Car"
    Then I want fill "vin" field with 4
    Then I want fill "regNo" field with "testingRegNo"
    Then I want fill "fuelType" field with 1
    Then I want fill "year" field with "2000"
    And I want fill depotId by saved Id
    And I want fill vehicle group id
    Then I want to create vehicle and save id
    And I see field "team/type" filled with "client"
    Given I signed in as "super_admin" team "admin"
    And I want fill device model for vehicle with name "FM3001"
    And I want fill "sn" field with "test sn"
    And I want fill "status" field with "inStock"
    And I want fill "port" field with 25
    And I want fill "imei" field with "888888888888888"
    And I want fill "phone" field with "+(375) 245535454"
    Then I want to create device for vehicle and save id
    And Response code is 200
    Given I signed in as "admin" team "client" and teamId 2
    Then I want install device for vehicle
    And Response code is 200
    And I want set vehicle driver with user of current team with date "2019-05-29 08:19:38"
    Given Elastica populate
    And I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test area"
    And I want fill "status" field with "active"
    And I want fill area test coordinates "54.072912" "27.096977"
    And I want fill area test coordinates "54.145370" "28.123750"
    And I want fill area test coordinates "53.721726" "28.140802"
    And I want fill area test coordinates "53.747721" "26.932842"
    And I want fill area test coordinates "54.072912" "27.096977"
    And I want to create area and save id
    And I see field "name" filled with "test area"
    When I want fill "payload" field with "000F383838383838383838383838383838"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response"
    And I see field "imei"
    When I want fill "payload" field with "000000000000020708050000016e3fc5194a0110653182202243f500ed01650f0025fd1709ef01f001c800710021f8252e3014fd02fe10094236ee1800254300004400000d06d10f003b2a02992b000031039004f100006465c7000013ee10005095700c000d40e301ee00000000000000000000016e3fc519540110653182202243f500ed01650f0025f31607ef01f001c800710021f8252e30140a4236ee1800254300004400000d06d10f003b2a02992b0000310390f303e804f100006465c7000013ee10005095700c000d40e301ee00000000000000000000016e3fc51d2801106530fd2022473600ed016510001dfd1709ef01f001c800710021f8252e3014fd02fe16094236ea18001d4300004400000d06d10f003b2a02992b000031039004f100006465c7000013f710005095790c000d40e301ee00000000000000000000016e3fc51d3201106530fd2022473600ed016510001df31607ef01f001c800710021f8252e30140a4236ea18001d4300004400000d06d10f003b2a02992b0000310390f303e804f100006465c7000013f710005095790c000d40e401ee00000000000000000000016e3fc51d3c01106530fd2022473600ed016510001dff1608ef01f001c800710021f8252e3014ff1d094236ea18001d4300004400000d06d10f003b2a02992b000031039004f100006465c7000013f710005095790c000d40e401ee00000000000000000500003ca7"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Given Calculate routes
    Then I want clean filled data
    And I want fill "startDate" field with "2018-05-30T18:55:30+00:00"
    And I want fill "endDate" field with "2020-05-30T22:00:00+00:00"
    And I want get fbt report with type json
    And Response code is 200
    And I see field "total" filled with 1
    And I see field "data/0/driver_name" filled with "client-contact-name-0 client-surname-name-0"
    And I see field "data/0/groups" filled with "test group"
    And I see field "data/0/depot_name" filled with "test depot"
    And I see field "data/0/started_at" filled with "2019-11-06 08:11:52"
    And I see field "data/0/finished_at" filled with "2019-11-06 08:11:53"
    And I see field "data/0/start_areas_name" filled with "test area"
    And I see field "data/0/finish_areas_name" filled with "test area"
    And I see field "data/0/distance" filled with 9
    And I see field "data/0/start_odometer" filled with "string(5281136)"
    And I see field "data/0/finish_odometer" filled with "string(5281145)"
    And I see field "data/0/driving_time" filled with "string(1)"
    And I see field "data/0/scope"
    And I see field "data/0/comment"
    Then I want fill vehicle id
    And I want get fbt report with type json
    And Response code is 200
    And I see field "total" filled with 1
    And I see field "data/0/driver_name" filled with "client-contact-name-0 client-surname-name-0"
    And I see field "data/0/groups" filled with "test group"
    Then I want get fbt report with type json
    And Response code is 200
    And I see field "total" filled with 1
    And I see field "data/0/regno" filled with "testingRegNo"
    Then I want fill "vehicleId" field with 999
    And I want get fbt report with type json
    And Response code is 200
    And I see field "total" filled with 0

  Scenario: I want test driver summary report
    Given I signed in as "super_admin" team "admin"
    And I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test group"
    And I want to create vehicle group and save id
    And I want fill "name" field with "test depot"
    And I want to create depot and save id
    Then I want fill "model" field with 2
    Then I want fill "type" field with "Car"
    Then I want fill "vin" field with 4
    Then I want fill "regNo" field with "testingRegNo"
    Then I want fill "fuelType" field with 1
    Then I want fill "year" field with "2000"
    And I want fill depotId by saved Id
    And I want fill vehicle group id
    Then I want to create vehicle and save id
    And I see field "team/type" filled with "client"
    And I want fill device model for vehicle with name "FM3001"
    And I want fill "sn" field with "test sn"
    And I want fill "status" field with "inStock"
    And I want fill "port" field with 25
    And I want fill "imei" field with "888888888888888"
    And I want fill "phone" field with "+(375) 245535454"
    Then I want to create device for vehicle and save id
    And Response code is 200
    Given I signed in as "admin" team "client" and teamId 2
    Then I want install device for vehicle
    And Response code is 200
    And I want set vehicle driver with user of current team with date "2019-05-29 08:19:38"
    Given Elastica populate
    And I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test area"
    And I want fill "status" field with "active"
    And I want fill area test coordinates "54.072912" "27.096977"
    And I want fill area test coordinates "54.145370" "28.123750"
    And I want fill area test coordinates "53.721726" "28.140802"
    And I want fill area test coordinates "53.747721" "26.932842"
    And I want fill area test coordinates "54.072912" "27.096977"
    And I want to create area and save id
    And I see field "name" filled with "test area"
    When I want fill "payload" field with "000F383838383838383838383838383838"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response"
    And I see field "imei"
    When I want fill "payload" field with "000000000000020708050000016e3fc5194a0110653182202243f500ed01650f0025fd1709ef01f001c800710021f8252e3014fd02fe10094236ee1800254300004400000d06d10f003b2a02992b000031039004f100006465c7000013ee10005095700c000d40e301ee00000000000000000000016e3fc519540110653182202243f500ed01650f0025f31607ef01f001c800710021f8252e30140a4236ee1800254300004400000d06d10f003b2a02992b0000310390f303e804f100006465c7000013ee10005095700c000d40e301ee00000000000000000000016e3fc51d2801106530fd2022473600ed016510001dfd1709ef01f001c800710021f8252e3014fd02fe16094236ea18001d4300004400000d06d10f003b2a02992b000031039004f100006465c7000013f710005095790c000d40e301ee00000000000000000000016e3fc51d3201106530fd2022473600ed016510001df31607ef01f001c800710021f8252e30140a4236ea18001d4300004400000d06d10f003b2a02992b0000310390f303e804f100006465c7000013f710005095790c000d40e401ee00000000000000000000016e3fc51d3c01106530fd2022473600ed016510001dff1608ef01f001c800710021f8252e3014ff1d094236ea18001d4300004400000d06d10f003b2a02992b000031039004f100006465c7000013f710005095790c000d40e401ee00000000000000000500003ca7"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    When I want fill "payload" field with "0000000000000495080c0000016ef0fec9ea00107b1952202573e800000000000000f00f04ef00f001c8007100064230751800004300004400000d06c10f004c04f100006465c7000000001000659c2e0c0010c09101ee00000000000000000000016ef0ff3f1000107b1952202573e800000000000000000f04ef00f001c800710006422eac1800004300004400000d06c10f004c04f100006465c7000000001000659c2e0c0010c09101ee00000000000000000000016ef101a45800000000000000000000000000000000f01507ef01f001c800710021012500302209423b7a1800004300004400000d06c10f00002a00952b00003104c004f100006465c7000000001000659c2e0c0010c0b801ee00000000000000000000016ef102198800000000000000000000000000000000001507ef01f001c80071002103250d302209423b351800004300004400000d06c10f00002a00b52b00003104c004f100006465c7000000001000659c2e0c0010c0b801ee00000000000000000000016ef10263c000107baa7020259e4a00c70067050005001507ef01f001c800710021fd250a301f09423af21800054300004400000d06c10f00002a00c92b00003104c004f100006465c7000000001000659c2e0c0010c0b801ee00000000000000000000016ef1028ad000107b91a2202594fb00ea00f205000b001507ef01f001c800710021012515301e09423b5918000b4300004400000d06c10f00002a00d12b00003104c004f100006465c70000001e1000659c4c0c0010c0b801ee00000000000000000000016ef1029a7000107b7d5220258eed00ea00f5050020001507ef01f001c800710021002521301e09423b471800204300004400000d06c10f00002a00d52b00003104c004f100006465c7000000471000659c750c0010c0ba01ee00000000000000000000016ef102adf800107b5f70202585ae00eb00f4050027001507ef01f001c800710021002527301e09423b3f1800274300004400000d06c10f00002a00d92b00003104c004f100006465c7000000801000659cae0c0010c0bf01ee00000000000000000000016ef102c18000107b3f8a20257dce00ec00f5060027001507ef01f001c80071002100252a302009423b3f1800274300004400000d06c10f00002a00e12b00003104c004f100006465c7000000ba1000659ce80c0010c0c401ee00000000000000000000016ef102d50800107b238b202574c100f000f4060025001507ef01f001c800710021002528302009423b271800254300004400000d06c10f00002a00e52b00003104c004f100006465c7000000f11000659d1f0c0010c0ca01ee00000000000000000000016ef102f44800107b039420256f2800e200f5080000001507ef01f001c80071002101251a302009423a271800004300004400000d06c10f00002a00ed2b00003104c004f100006465c7000001371000659d650c0010c0cf01ee00000000000000000000016ef10307d000107affff20256dfc00db0100090007001507ef01f001c80071002103250130200942398a1800074300004400000d06c10f00002a00f12b00003104c004f100006465c7000001371000659d650c0010c0d001ee00000000000000000c00003074"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Given Calculate routes
    Then I want clean filled data
    When I want fill "dateFrom" field with "2018-05-30T18:55:30+00:00"
    When I want fill "dateTo" field with "2020-05-30T22:00:00+00:00"
    Then I want get vehicle routes and save first
    And response code is 200
    When I want fill "comment" field with "test comment"
    And I want fill "scope" field with "work"
    Then I want update saved route
    And response code is 200
    And I see field "comment" filled with "test comment"
    And I see field "scope" filled with "work"
    Then I want clean filled data
    And I want fill "startDate" field with "2018-05-30T18:55:30+00:00"
    And I want fill "endDate" field with "2020-05-30T22:00:00+00:00"
    And I want get driver summary report with type json
    And Response code is 200
    And I see field "total" filled with 1
    And I see field "data/0/regno" filled with "testingRegNo"
    And I see field "data/0/max_speed" filled with 39
    And I see field "data/0/stops_count" filled with 1
    And I see field "data/0/work_distance" filled with 1377982
    And I see field "data/0/work_duration" filled with 2973348
    And I see field "data/0/total_distance" filled with 1378293
    And I see field "data/0/total_duration" filled with 2973439
    And I see field "data/0/parking_time" filled with 187
    And I see field "data/0/max_speed_total" filled with 39
    And I see field "data/0/stops_count_total" filled with 1
    And I see field "data/0/work_distance_total" filled with 1377982
    And I see field "data/0/work_duration_total" filled with 2973348
    And I see field "data/0/total_distance_total" filled with 1378293
    And I see field "data/0/total_duration_total" filled with 2973439
    And I see field "data/0/parking_time_total" filled with 187
    Then I want fill driver id
    And I want get driver summary report with type json
    And Response code is 200
    And I see field "total" filled with 1
    Then I want get driver summary report driver list
    And Response code is 200
    And I see field "total" filled with 1
    And I see field "data/0/driver_id" filled with 7
    Then I want fill "driver_id" field with 999
    And I want get driver summary report with type json
    And Response code is 200
    And I see field "total" filled with 0

  Scenario: I want to update driver in related entities
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
    Then I want change installed device date for saved vehicle to '2019-11-01T18:55:30+00:00'
    Then I want set vehicle driver with user of current team with date "2019-10-01T11:00:04+03:00"
    And I see field "driver/id" filled with "string(27)"
    And response code is 200
    Given There are following tracker payload from teltonika tracker with socket "test-socket-id":
      | payload                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        |
      | 000F383838383838383838383838383838                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             |
      | 000000000000016f08050000017077346780005697ba16e9487a1c0047011710001c001008ef01f00150011504c80045011e02251b07b50008b600064234f918001c430fdc440009313f1d01100000007d000000017077346b68005697b706e9487a5e00460116100015001008ef01f00150011504c80045011e02251b07b50009b60006423529180015430fdc440009313f1d011000000085000000017077346f50005697b4bfe9487a900046010e100010001008ef01f00150011504c80045011e02250b07b5000bb6000642350e180010430fdc440009313f1d01100000008c000000017077346f5a005697b340e9487a4e0045010210000afd120aef01f00150011504c80045011e02250bfd03fe2407b50009b6000642352d18000a430fdc440009313f1d011000000091000000017077347720005697b256e94879c8004500ee100007001008ef01f00150011504c80045011e02250b07b5000bb600074234b1180007430fdc440009313f1d011000000095000500001765                                                                                                                                                                                                                                                                                                                                                                                                                           |
    Given Calculate routes
    When I want fill "dateFrom" field with "2020-02-20T18:55:30+00:00"
    And I want fill "dateTo" field with "2020-02-25T22:00:00+00:00"
    Then I want get vehicle routes
    And I see field "0/driverId" filled with "string(27)"
    And I want set another vehicle driver with current team with date "2020-02-21T11:00:04+03:00"
    And I want handle check event when driver update on vehicle
    When I want fill "dateFrom" field with "2020-02-20T18:55:30+00:00"
    And I want fill "dateTo" field with "2020-02-25T22:00:00+00:00"
    Then I want get vehicle routes
    And I see field "0/driverId" filled with "string(42)"