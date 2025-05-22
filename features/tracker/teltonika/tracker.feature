Feature: Tracker Teltonika

  Scenario: I want connect to tcp server
    When I want to connect to teltonika tcp server

  Scenario: I want connect to wrong tcp server
    When I want to connect to wrong tcp server

  Scenario: I want send tcp data to API
    When I want fill "payload" field with "000F383632323539353838383334323930"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response" filled with "string(01)"
    And I see field "imei" filled with "string(862259588834290)"
    Then I want check tracker auth by socket "test-socket-id"
    When I want fill "payload" field with "00000000000000FE080400000113fc208dff000f14f650209cca80006f00d60400040004030101150316030001460000015d0000000113fc17610b000f14ffe0209cc580006e00c00500010004030101150316010001460000015e0000000113fc284945000f150f00209cd200009501080400000004030101150016030001460000015d0000000113fc267c5b000f150a50209cccc0009300680400000004030101150016030001460000015b000400008612"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response" filled with "4"
    And I see field "imei" filled with "string(862259588834290)"
    When I want fill "payload" field with "00000000000000FE080400000113fc208dff00e8035307fdd922c7006f00d60400040004030101150316030001460000015d0000000113fc17610b000f14ffe0209cc580006e00c00500010004030101150316010001460000015e0000000113fc284945000f150f00209cd200009501080400000004030101150016030001460000015d0000000113fc267c5b000f150a50209cccc0009300680400000004030101150016030001460000015b000400008612"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response" filled with "4"
    And I see field "imei" filled with "string(862259588834290)"
    When I want fill "payload" field with "000f383636343235303333353330323233"
    Then I want to send teltonika tcp data to api with socket "test-socket-id2"
    Then I see field "response"
    And I see field "imei" filled with "string(866425033530223)"
    Then I want check tracker auth by socket "test-socket-id2"
    When I want fill "payload" field with "000000000000002108010000016af95d85a80010662846202274e300da00000500000000000000000100004dd4"
    Then I want to send teltonika tcp data to api with socket "test-socket-id2"
    Then I see field "response" filled with "1"
    And I see field "imei" filled with "string(866425033530223)"

  Scenario: I want send wrong tcp data to API
    When I want fill "payload" field with "000asdqweasdsd"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then response code is 400

  Scenario: I want see tracker data logs
    When I want fill "payload" field with "000F383632323539353838383334323930"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response"
    And I see field "imei"
    When I want fill "payload" field with "00000000000000FE080400000113fc208dff000f14f650209cca80006f00d60400040004030101150316030001460000015d0000000113fc17610b000f14ffe0209cc580006e00c00500010004030101150316010001460000015e0000000113fc284945000f150f00209cd200009501080400000004030101150016030001460000015d0000000113fc267c5b000f150a50209cccc0009300680400000004030101150016030001460000015b000400008612"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response" filled with "4"
    When I want fill "imei" field with "862259588834290"
    And I want fill "startDate" field with "2007-07-25T00:00:00+00:00"
    And I want fill "endDate" field with "2007-07-25T23:59:59+00:00"
    Then I want to get tracker data log
    And I see field "total" filled with "4"
    And I see field "data"
    And I see field "data/0/ts" filled with "2007-07-25T06:36:37+00:00"
    And I see field "data/0/createdAt"
    And I see field "data/0/gpsData/speed" filled with "1"
    And I see field "data/0/gpsData/angle" filled with "192"
    And I see field "data/0/gpsData/coordinates/lat" filled with "custom(54.71450880)"
    And I see field "data/0/gpsData/coordinates/lng" filled with "custom(25.30344640)"
    And I see field "data/0/ioData" filled with "[1]: 1, [21]: 3, [22]: 1, [70]: 350"
    And response code is 200

  Scenario: I want check geofence
    Given I want handle check area event
    Given I signed in as "super_admin" team "admin"
    Then I want fill "model" field with 2
    Then I want fill "type" field with "Car"
    Then I want fill "vin" field with 4
    Then I want fill "regNo" field with "testingRegNo"
    Then I want fill "fuelType" field with 1
    Then I want fill "year" field with "2000"
    And I want get client by name "client-name-0" and save id
    And  I want set remembered team settings "mapApiOptions" with raw value
    """
      [2]
    """
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
    Given I signed in as "admin" team "client" and teamId 2
    And I want set vehicle driver with user of current team with date "2019-05-29 08:19:38"
    Given Elastica populate
    Then I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test area"
    And I want fill "status" field with "active"
    And I want fill area test coordinates "54.033901" "27.155388"
    And I want fill area test coordinates "54.060509" "27.793689"
    And I want fill area test coordinates "53.786366" "27.808801"
    And I want fill area test coordinates "53.800157" "27.188345"
    And I want fill area test coordinates "54.033901" "27.155388"
    And I want to create area and save id
    And I see field "name" filled with "test area"
    And I see field "status" filled with "active"
    Then I want clean filled data
    And I want fill teamId by saved clientId
    Then I want fill area ids with saved id
    And I want fill "name" field with "test group"
    And I want fill "color" field with "#fff"
    And I want to create area group and save id
    And I see field "name" filled with "test group"
    When I want fill "payload" field with "000F383838383838383838383838383838"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response"
    And I see field "imei"
    When I want fill "payload" field with "00000000000004d608130000016b0a18fff8001073a68d201e100b00c801610e0000f00c05ef00f0001504c800450105b50006b6000442328f43000044000002f100006465100000274c000000016b0a1b1338001073a68d201e100b00c801610d0000f00c05ef00f0011504c800450105b50005b6000342322643000044000002f100006465100000274c000000016b0a1b3a48001073a68d201e100b00c801610d0000000c05ef00f0011504c800450105b50006b6000342322443000044000002f100006465100000274c000000016b0a1b6158001073a68d201e100b00c801610e0000000c05ef00f0011504c800450105b50006b6000342322643000044000002f100006465100000274c000000016b0a1b8868001073a68d201e100b00c801610e0000000c05ef00f0011504c800450105b50006b6000342322843000044000002f100006465100000274c000000016b0a1baf78001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342317c43000044000002f100006465100000274c000000016b0a1bd688001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b600034230ae43000044000002f100006465100000274c000000016b0a1bfd98001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50005b6000342307443000044000002f100006465100000274c000000016b0a1c0180001073a68d201e100b00c801610f0000f00c05ef00f0001504c800450105b50006b6000342307543000044000002f100006465100000274c000000016b0a1c1508001073a68d201e100b00c801610f0000f00c05ef00f0011504c800450105b50006b6000342304a43000044000002f100006465100000274c000000016b0a1c3c18001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342311443000044000002f100006465100000274c000000016b0a1c6328001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b600034231a343000044000002f100006465100000274c000000016b0a1c8a38001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b600034231f043000044000002f100006465100000274c000000016b0a1cb148001073a68d201e100b00c80161100000000c05ef00f0011504c800450105b50005b600034231fa43000044000002f100006465100000274c000000016b0a1cd858001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342322343000044000002f100006465100000274c000000016b0a1cff68001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342322643000044000002f100006465100000274c000000016b0a1d2678001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342322843000044000002f100006465100000274c000000016b0a1d4d88001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342322843000044000002f100006465100000274c000000016b0a1d7498001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b600034231ad43000044000002f100006465100000274c0013000015d1"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    And I want check area history for current vehicle, area and driver arrived
    And I want to get vehicle by saved id
    Then I see field "areas/0/area/name" filled with "test area"
    And I want unset vehicle driver with current user with date "2019-05-31 08:19:38"
    Then I want clean filled data
    And I want fill bool field "isInArea" with text value "true"
    And I want get vehicle list
    And I see field "total" filled with 1
    And I want fill bool field "isInArea" with text value "false"
    Given I signed in as "super_admin" team "admin"
    And I want get vehicle list
    And I see field "total" filled with 46
    Then I want clean filled data
    And I want fill areas id with saved id
    And I want get vehicle list
    And I see field "total" filled with 1
    Then I want clean filled data
    And I want fill area group ids
    And I want get vehicle list
    And I see field "total" filled with 1
    Then I want clean filled data
    And I want fill "fullSearch" field with "test area"
    And I want get vehicle list
    And I see field "total" filled with 1
    Given I signed in as "admin" team "client" and teamId 2
    And I want set vehicle driver with user of current team with date "2019-07-19 08:19:38"
    When I want fill "payload" field with "00000000000001ca08050000016c0cfefc08005a1949a7ebced28500000000000000f0150c0100020003000400b300b4004502f0011500c800ef004f63060900200a0018b50000b600004238f243108302f10000c545cd00000000014e00000000000000000000016c0cfefff0005a1949a7ebced28500000000000000ef150c0100020003000400b300b4004502f0011500c800ef014f08060900200a0018b50000b600004238e143108302f10000c545cd07ef870c014e00000000000000000000016c0cff03d8005a1949a7ebced28500000000000000b3150c0100020003000400b301b4004502f0001500c800ef014f08060900200a0018b50000b600004238fd43108102f10000c545cd07ef870c014e00000000000000000000016c0cff040a005a1949a7ebced28500000000000000f0150c0100020003000400b301b4004502f0001500c800ef014f08060900200a0018b50000b600004238fd43108102f10000c545cd07ef870c014e00000000000000000000016c0cff07c0005a1949a7ebced28500000000000000f0150c0100020003000400b301b4004502f0011500c800ef014f08060900200a0018b50000b600004238cf43108102f10000c545cd07ef870c014e00000000000000000500007066"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    And I want check area history for current vehicle, area and driver departed

  Scenario: I want test vehicle distance and duration
    Given I signed in as "super_admin" team "admin"
    Then I want fill "model" field with 2
    Then I want fill "type" field with "Car"
    Then I want fill "vin" field with 4
    Then I want fill "regNo" field with "testingRegNo"
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
    And I want fill "imei" field with "888888888888888"
    And I want fill "phone" field with "+(375) 245535454"
    Then I want to create device for vehicle and save id
    Then I want install device for vehicle
    Given I signed in as "admin" team "client" and teamId 2
    And I want set vehicle driver with current user with date "2019-05-29 08:19:38"
    Given Elastica populate
    Then I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test area"
    And I want fill area test coordinates "54.033901" "27.155388"
    And I want fill area test coordinates "54.060509" "27.793689"
    And I want fill area test coordinates "53.786366" "27.808801"
    And I want fill area test coordinates "53.800157" "27.188345"
    And I want fill area test coordinates "54.033901" "27.155388"
    And I want to create area and save id
    And I see field "name" filled with "test area"
    When I want fill "payload" field with "000F383838383838383838383838383838"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response"
    And I see field "imei"
    When I want fill "payload" field with "000000000000020708050000016e3fc5194a0110653182202243f500ed01650f0025fd1709ef01f001c800710021f8252e3014fd02fe10094236ee1800254300004400000d06d10f003b2a02992b000031039004f100006465c7000013ee10005095700c000d40e301ee00000000000000000000016e3fc519540110653182202243f500ed01650f0025f31607ef01f001c800710021f8252e30140a4236ee1800254300004400000d06d10f003b2a02992b0000310390f303e804f100006465c7000013ee10005095700c000d40e301ee00000000000000000000016e3fc51d2801106530fd2022473600ed016510001dfd1709ef01f001c800710021f8252e3014fd02fe16094236ea18001d4300004400000d06d10f003b2a02992b000031039004f100006465c7000013f710005095790c000d40e301ee00000000000000000000016e3fc51d3201106530fd2022473600ed016510001df31607ef01f001c800710021f8252e30140a4236ea18001d4300004400000d06d10f003b2a02992b0000310390f303e804f100006465c7000013f710005095790c000d40e401ee00000000000000000000016e3fc51d3c01106530fd2022473600ed016510001dff1608ef01f001c800710021f8252e3014ff1d094236ea18001d4300004400000d06d10f003b2a02992b000031039004f100006465c7000013f710005095790c000d40e401ee00000000000000000500003ca7"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Given Calculate routes
    And I want clean filled data
    And I want get current vehicle data from "2019-11-05 18:58:05" to "2019-11-07 18:58:05"
    And I see field "distance" filled with 9
    And I see field "duration" filled with 1
    And I want get current vehicle data by group "month" and count 2 and date "2019-11-15 18:58:05"
    And I see field "0/start" filled with "2019-11-01T00:00:00+00:00"
    And I see field "0/end" filled with "2019-11-30T23:59:59+00:00"
    And I see field "0/distance" filled with 9
    And I see field "0/duration" filled with 1
    And I want get current vehicle data by group "week" and count 5 and date "2019-11-15 18:58:05"
    And I see field "1/start" filled with "2019-11-04T00:00:00+00:00"
    And I see field "1/end" filled with "2019-11-10T23:59:59+00:00"
    And I see field "1/distance" filled with 9
    And I see field "1/duration" filled with 1
    And I want get current vehicle data by group "day" and count 10 and date "2019-11-11 18:58:05"
    And I see field "5/start" filled with "2019-11-06T00:00:00+00:00"
    And I see field "5/end" filled with "2019-11-06T23:59:59+00:00"
    And I see field "5/distance" filled with 9
    And I see field "5/duration" filled with 1

  Scenario: I want test driver distance and duration
    Given I signed in as "super_admin" team "admin"
    Then I want fill "model" field with 2
    Then I want fill "type" field with "Car"
    Then I want fill "vin" field with 4
    Then I want fill "regNo" field with "testingRegNo"
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
    And I want fill "imei" field with "888888888888888"
    And I want fill "phone" field with "+(375) 245535454"
    Then I want to create device for vehicle and save id
    Then I want install device for vehicle
    Given I signed in as "admin" team "client" and teamId 2
    And I want set vehicle driver with current user with date "2019-05-29 08:19:38"
    Given Elastica populate
    Then I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test area"
    And I want fill area test coordinates "54.033901" "27.155388"
    And I want fill area test coordinates "54.060509" "27.793689"
    And I want fill area test coordinates "53.786366" "27.808801"
    And I want fill area test coordinates "53.800157" "27.188345"
    And I want fill area test coordinates "54.033901" "27.155388"
    And I want to create area and save id
    And I see field "name" filled with "test area"
    When I want fill "payload" field with "000F383838383838383838383838383838"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response"
    And I see field "imei"
    When I want fill "payload" field with "000000000000020708050000016e3fc5194a0110653182202243f500ed01650f0025fd1709ef01f001c800710021f8252e3014fd02fe10094236ee1800254300004400000d06d10f003b2a02992b000031039004f100006465c7000013ee10005095700c000d40e301ee00000000000000000000016e3fc519540110653182202243f500ed01650f0025f31607ef01f001c800710021f8252e30140a4236ee1800254300004400000d06d10f003b2a02992b0000310390f303e804f100006465c7000013ee10005095700c000d40e301ee00000000000000000000016e3fc51d2801106530fd2022473600ed016510001dfd1709ef01f001c800710021f8252e3014fd02fe16094236ea18001d4300004400000d06d10f003b2a02992b000031039004f100006465c7000013f710005095790c000d40e301ee00000000000000000000016e3fc51d3201106530fd2022473600ed016510001df31607ef01f001c800710021f8252e30140a4236ea18001d4300004400000d06d10f003b2a02992b0000310390f303e804f100006465c7000013f710005095790c000d40e401ee00000000000000000000016e3fc51d3c01106530fd2022473600ed016510001dff1608ef01f001c800710021f8252e3014ff1d094236ea18001d4300004400000d06d10f003b2a02992b000031039004f100006465c7000013f710005095790c000d40e401ee00000000000000000500003ca7"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Given Calculate routes
    And I want clean filled data
    And I want get current driver data from "2019-11-05 18:58:05" to "2019-11-07 18:58:05"
    And I see field "distance" filled with 9
    And I see field "duration" filled with 1
    And I want get current driver data by group "month" and count 2 and date "2019-11-15 18:58:05"
    And I see field "0/start" filled with "2019-11-01T00:00:00+00:00"
    And I see field "0/end" filled with "2019-11-30T23:59:59+00:00"
    And I see field "0/distance" filled with 9
    And I see field "0/duration" filled with 1
    And I want get current driver data by group "week" and count 5 and date "2019-11-15 18:58:05"
    And I see field "1/start" filled with "2019-11-04T00:00:00+00:00"
    And I see field "1/end" filled with "2019-11-10T23:59:59+00:00"
    And I see field "1/distance" filled with 9
    And I see field "1/duration" filled with 1
    And I want get current driver data by group "day" and count 10 and date "2019-11-11 18:58:05"
    And I see field "5/start" filled with "2019-11-06T00:00:00+00:00"
    And I see field "5/end" filled with "2019-11-06T23:59:59+00:00"
    And I see field "5/distance" filled with 9
    And I see field "5/duration" filled with 1

  Scenario: I want check cases
    Given I signed in as "super_admin" team "admin"
    Then I want fill "model" field with 2
    Then I want fill "type" field with "Car"
    Then I want fill "vin" field with 4
    Then I want fill "regNo" field with "testingRegNo"
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
    Then I want to create device for vehicle and save id
    Then I want install device for vehicle
    Given I signed in as "admin" team "client" and teamId 2
    And I want set vehicle driver with current user with date "2019-05-29 08:19:38"
    When I want fill "payload" field with "000F383838383838383838383838383838"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response"
    And I see field "imei"
    When I want fill "payload" field with "00000000000004d608130000016b0a18fff8001073a68d201e100b00c801610e0000f00c05ef00f0001504c800450105b50006b6000442328f43000044000002f100006465100000274c000000016b0a1b1338001073a68d201e100b00c801610d0000f00c05ef00f0011504c800450105b50005b6000342322643000044000002f100006465100000274c000000016b0a1b3a48001073a68d201e100b00c801610d0000000c05ef00f0011504c800450105b50006b6000342322443000044000002f100006465100000274c000000016b0a1b6158001073a68d201e100b00c801610e0000000c05ef00f0011504c800450105b50006b6000342322643000044000002f100006465100000274c000000016b0a1b8868001073a68d201e100b00c801610e0000000c05ef00f0011504c800450105b50006b6000342322843000044000002f100006465100000274c000000016b0a1baf78001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342317c43000044000002f100006465100000274c000000016b0a1bd688001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b600034230ae43000044000002f100006465100000274c000000016b0a1bfd98001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50005b6000342307443000044000002f100006465100000274c000000016b0a1c0180001073a68d201e100b00c801610f0000f00c05ef00f0001504c800450105b50006b6000342307543000044000002f100006465100000274c000000016b0a1c1508001073a68d201e100b00c801610f0000f00c05ef00f0011504c800450105b50006b6000342304a43000044000002f100006465100000274c000000016b0a1c3c18001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342311443000044000002f100006465100000274c000000016b0a1c6328001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b600034231a343000044000002f100006465100000274c000000016b0a1c8a38001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b600034231f043000044000002f100006465100000274c000000016b0a1cb148001073a68d201e100b00c80161100000000c05ef00f0011504c800450105b50005b600034231fa43000044000002f100006465100000274c000000016b0a1cd858001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342322343000044000002f100006465100000274c000000016b0a1cff68001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342322643000044000002f100006465100000274c000000016b0a1d2678001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342322843000044000002f100006465100000274c000000016b0a1d4d88001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b6000342322843000044000002f100006465100000274c000000016b0a1d7498001073a68d201e100b00c801610f0000000c05ef00f0011504c800450105b50006b600034231ad43000044000002f100006465100000274c0013000015d1"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test area"
    And I want fill area test coordinates "54.033901" "27.155388"
    And I want fill area test coordinates "54.060509" "27.793689"
    And I want fill area test coordinates "53.786366" "27.808801"
    And I want fill area test coordinates "53.800157" "27.188345"
    And I want fill area test coordinates "54.033901" "27.155388"
    And I want to create area and save id
    And I see field "name" filled with "test area"
    #create area history for existed vehicles in this area
    Then I want check area history for current vehicle, area and driver arrived
    Then I want to delete area by saved id
    #set departed date for area history when delete area
    Then I want check area history for current vehicle, area and driver departed

  Scenario: I want check unknown devices auth
    When I want fill "payload" field with "000F383632323539353838383334323935"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "error" filled with "Device with imei: 862259588834295 is not found"
    When I want check unknown devices auth
    Then I see field "0/imei" filled with "string(862259588834295)"
    Then I see field "0/vendor" filled with "Teltonika"
