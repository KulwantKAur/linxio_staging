Feature: DrivingBehavior

  Background:
    Given I signed in as "super_admin" team "admin"

  Scenario: I want get vehicle total driving behavior stats
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
    Then I want change installed device date for saved vehicle to '2019-11-01T18:55:30+00:00'
    When I want fill "payload" field with "000F383838383838383838383838383838"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response"
    And I see field "imei"
    When I want fill "payload" field with "00000000000004dc080c0000016e65ccec58011066e2312020aaa600e200eb120031fd1709ef01f001c8007100210025353013fd02fe0e094239b51800314300004400000d06c70f005d2a052b2b00003100bb04f100006465c700002b5310005587a90c000e07d701ee00000000000000000000016e65ccec62011066e2312020aaa600e200eb120031f31607ef01f001c80071002100253530130a4239b51800314300004400000d06c70f005d2a052b2b00003100bbf303e804f100006465c700002b5310005587a90c000e07d901ee00000000000000000000016e65cd03c8011066bec620209f0f00e200f7100021fd1709ef01f001c8007100211325303014fd02fe0e094239761800214300004400000d06c70f005d2a052f2b00003100bb04f100006465c700002b9710005587ed0c000e07df01ee00000000000000000000016e65cd03d2011066bec620209f0f00e200f7100021f31607ef01f001c80071002113253030140a4239761800124300004400000d06c70f005d2a052f2b00003100bbf303e804f100006465c700002ba710005587fd0c000e07e101ee00000000000000000000016e65cd07b0011066ba052020a01a00e200f810001cfd1709ef01f001c8007100210025233015fd02fe0e0942397d1800124300004400000d06c70f005d2a05332b00003100bb04f100006465c700002ba710005587fd0c000e07e101ee00000000000000000000016e65cd07ba011066ba052020a01a00e200f810001cf31607ef01f001c80071002100252330150a42397d1800124300004400000d06c70f005d2a05332b00003100bbf303e804f100006465c700002ba710005587fd0c000e07e101ee00000000000000000000016e65cd07c4011066ba052020a01a00e200f810001cff1608ef01f001c8007100210025233015ff1c0942397d1800124300004400000d06c70f005d2a05332b00003100bb04f100006465c700002ba710005587fd0c000e07e101ee00000000000000000000016e65cd0f8a011066b34020209f2000e300f70d0012fd1709ef01f001c8007100210025233015fd02fe10094239991800124300004400000d06c70f005d2a05332b00003100bb04f100006465c700002bac10005588020c000e07e101ee00000000000000000000016e65cd0f94011066b34020209f2000e300f70d0012f31607ef01f001c80071002100252330150a4239991800124300004400000d06c70f005d2a05332b00003100bbf303e804f100006465c700002bac10005588020c000e07e101ee00000000000000000000016e65cd1368011066b18f20209e9a00e200f70d000dfd1709ef01f001c8007100210025233015fd02fe0e094239a218000d4300004400000d06c70f005d2a05332b00003100bb04f100006465c700002baf10005588050c000e07e101ee00000000000000000000016e65cd1372011066b18f20209e9a00e200f70d000df31607ef01f001c80071002100252330150a4239a218000d4300004400000d06c70f005d2a05332b00003100bbf303e804f100006465c700002baf10005588050c000e07e101ee00000000000000000000016e65cd9c20011066ab1d20209b6900e400f3110010fd1709ef01f001c8007100210225003017fd01fe1009423a611800104300004400000d06c70f005d2a05562b00003100bb04f100006465c700002bb110005588070c000e07e901ee00000000000000000c0000e8ad"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response"
    And I see field "imei"
    Given Calculate idling
    Given Calculate speeding
    When I want clean filled data
    When I want fill "startDate" field with "2019-10-30T18:55:30+00:00"
    And I want fill "endDate" field with "2019-12-30T22:00:00+00:00"
    Then I want get vehicle total driving behavior stats for saved vehicle id
    And I see field "vehicleId"
    And I see field "harshAccelerationScore" filled with "89.36"
    And I see field "harshBrakingScore" filled with "100"
    And I see field "harshCorneringScore" filled with "100"
    And I see field "idlingScore" filled with "100"
    And I see field "speedingScore" filled with "100"
    And I see field "totalScore" filled with "97.87"
    And I see field "drivingTotalTime" filled with "45"
    And I see field "totalDistance" filled with "94"
    And I see field "totalAvgSpeed" filled with "7.52"

  Scenario: I want get vehicle scores for driving behavior
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
    Then I want change installed device date for saved vehicle to '2019-11-01T18:55:30+00:00'
    Given Elastica populate
    When I want fill "payload" field with "000F383838383838383838383838383838"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response"
    And I see field "imei"
    When I want fill "payload" field with "00000000000004dc080c0000016e65ccec58011066e2312020aaa600e200eb120031fd1709ef01f001c8007100210025353013fd02fe0e094239b51800314300004400000d06c70f005d2a052b2b00003100bb04f100006465c700002b5310005587a90c000e07d701ee00000000000000000000016e65ccec62011066e2312020aaa600e200eb120031f31607ef01f001c80071002100253530130a4239b51800314300004400000d06c70f005d2a052b2b00003100bbf303e804f100006465c700002b5310005587a90c000e07d901ee00000000000000000000016e65cd03c8011066bec620209f0f00e200f7100021fd1709ef01f001c8007100211325303014fd02fe0e094239761800214300004400000d06c70f005d2a052f2b00003100bb04f100006465c700002b9710005587ed0c000e07df01ee00000000000000000000016e65cd03d2011066bec620209f0f00e200f7100021f31607ef01f001c80071002113253030140a4239761800124300004400000d06c70f005d2a052f2b00003100bbf303e804f100006465c700002ba710005587fd0c000e07e101ee00000000000000000000016e65cd07b0011066ba052020a01a00e200f810001cfd1709ef01f001c8007100210025233015fd02fe0e0942397d1800124300004400000d06c70f005d2a05332b00003100bb04f100006465c700002ba710005587fd0c000e07e101ee00000000000000000000016e65cd07ba011066ba052020a01a00e200f810001cf31607ef01f001c80071002100252330150a42397d1800124300004400000d06c70f005d2a05332b00003100bbf303e804f100006465c700002ba710005587fd0c000e07e101ee00000000000000000000016e65cd07c4011066ba052020a01a00e200f810001cff1608ef01f001c8007100210025233015ff1c0942397d1800124300004400000d06c70f005d2a05332b00003100bb04f100006465c700002ba710005587fd0c000e07e101ee00000000000000000000016e65cd0f8a011066b34020209f2000e300f70d0012fd1709ef01f001c8007100210025233015fd02fe10094239991800124300004400000d06c70f005d2a05332b00003100bb04f100006465c700002bac10005588020c000e07e101ee00000000000000000000016e65cd0f94011066b34020209f2000e300f70d0012f31607ef01f001c80071002100252330150a4239991800124300004400000d06c70f005d2a05332b00003100bbf303e804f100006465c700002bac10005588020c000e07e101ee00000000000000000000016e65cd1368011066b18f20209e9a00e200f70d000dfd1709ef01f001c8007100210025233015fd02fe0e094239a218000d4300004400000d06c70f005d2a05332b00003100bb04f100006465c700002baf10005588050c000e07e101ee00000000000000000000016e65cd1372011066b18f20209e9a00e200f70d000df31607ef01f001c80071002100252330150a4239a218000d4300004400000d06c70f005d2a05332b00003100bbf303e804f100006465c700002baf10005588050c000e07e101ee00000000000000000000016e65cd9c20011066ab1d20209b6900e400f3110010fd1709ef01f001c8007100210225003017fd01fe1009423a611800104300004400000d06c70f005d2a05562b00003100bb04f100006465c700002bb110005588070c000e07e901ee00000000000000000c0000e8ad"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response"
    And I see field "imei"
    Given Calculate idling
    Given Calculate speeding
    When I want clean filled data
    When I want fill "groupDate" field with "2019-11-13T18:55:30+00:00"
    And I want fill "groupType" field with "day"
    And I want fill "groupCount" field with "3"
    And I want fill "startDate" field with "2019-11-10T18:55:30+00:00"
    And I want fill "endDate" field with "2019-11-15T22:00:00+00:00"
    Then I want get vehicle scores for driving behavior for saved vehicle id
    And I see field "0/vehicleId"
    And I see field "0/harshAccelerationCount" filled with "1"
    And I see field "0/harshBrakingCount" filled with "0"
    And I see field "0/harshCorneringCount" filled with "0"
    And I see field "0/idlingCount" filled with "0"
    And I see field "0/ecoSpeedEventCount" filled with "0"
    And I see field "0/totalDistance" filled with "94"
    And I see field "0/drivingTotalTime" filled with "45"
    And I see field "0/overallScore" filled with "97.87"
    And I see field "0/start" filled with "2019-11-13T00:00:00+00:00"
    And I see field "0/end" filled with "2019-11-13T23:59:59+00:00"

  Scenario: I want get vehicle summary
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
    Then I want change installed device date for saved vehicle to '2019-11-01T18:55:30+00:00'
    Given Elastica populate
    When I want fill "payload" field with "000F383838383838383838383838383838"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response"
    And I see field "imei"
    When I want fill "payload" field with "00000000000004dc080c0000016e65ccec58011066e2312020aaa600e200eb120031fd1709ef01f001c8007100210025353013fd02fe0e094239b51800314300004400000d06c70f005d2a052b2b00003100bb04f100006465c700002b5310005587a90c000e07d701ee00000000000000000000016e65ccec62011066e2312020aaa600e200eb120031f31607ef01f001c80071002100253530130a4239b51800314300004400000d06c70f005d2a052b2b00003100bbf303e804f100006465c700002b5310005587a90c000e07d901ee00000000000000000000016e65cd03c8011066bec620209f0f00e200f7100021fd1709ef01f001c8007100211325303014fd02fe0e094239761800214300004400000d06c70f005d2a052f2b00003100bb04f100006465c700002b9710005587ed0c000e07df01ee00000000000000000000016e65cd03d2011066bec620209f0f00e200f7100021f31607ef01f001c80071002113253030140a4239761800124300004400000d06c70f005d2a052f2b00003100bbf303e804f100006465c700002ba710005587fd0c000e07e101ee00000000000000000000016e65cd07b0011066ba052020a01a00e200f810001cfd1709ef01f001c8007100210025233015fd02fe0e0942397d1800124300004400000d06c70f005d2a05332b00003100bb04f100006465c700002ba710005587fd0c000e07e101ee00000000000000000000016e65cd07ba011066ba052020a01a00e200f810001cf31607ef01f001c80071002100252330150a42397d1800124300004400000d06c70f005d2a05332b00003100bbf303e804f100006465c700002ba710005587fd0c000e07e101ee00000000000000000000016e65cd07c4011066ba052020a01a00e200f810001cff1608ef01f001c8007100210025233015ff1c0942397d1800124300004400000d06c70f005d2a05332b00003100bb04f100006465c700002ba710005587fd0c000e07e101ee00000000000000000000016e65cd0f8a011066b34020209f2000e300f70d0012fd1709ef01f001c8007100210025233015fd02fe10094239991800124300004400000d06c70f005d2a05332b00003100bb04f100006465c700002bac10005588020c000e07e101ee00000000000000000000016e65cd0f94011066b34020209f2000e300f70d0012f31607ef01f001c80071002100252330150a4239991800124300004400000d06c70f005d2a05332b00003100bbf303e804f100006465c700002bac10005588020c000e07e101ee00000000000000000000016e65cd1368011066b18f20209e9a00e200f70d000dfd1709ef01f001c8007100210025233015fd02fe0e094239a218000d4300004400000d06c70f005d2a05332b00003100bb04f100006465c700002baf10005588050c000e07e101ee00000000000000000000016e65cd1372011066b18f20209e9a00e200f70d000df31607ef01f001c80071002100252330150a4239a218000d4300004400000d06c70f005d2a05332b00003100bbf303e804f100006465c700002baf10005588050c000e07e101ee00000000000000000000016e65cd9c20011066ab1d20209b6900e400f3110010fd1709ef01f001c8007100210225003017fd01fe1009423a611800104300004400000d06c70f005d2a05562b00003100bb04f100006465c700002bb110005588070c000e07e901ee00000000000000000c0000e8ad"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response"
    And I see field "imei"
    Given Calculate idling
    Given Calculate speeding
    When I want clean filled data
    And I want fill "startDate" field with "2019-11-10T18:55:30+00:00"
    And I want fill "endDate" field with "2019-11-15T22:00:00+00:00"
    And I want fill "page" field with "1"
    And I want fill "limit" field with "5"
    Then I want get vehicle summary
    And I see field "page" filled with "1"
    And I see field "limit" filled with "5"
    And I see field "total"
    And I see field "data/0/harshAccelerationCount" filled with "0"
    And I see field "data/0/harshBrakingCount" filled with "0"
    And I see field "data/0/harshCorneringCount" filled with "0"
    And I see field "data/0/idlingCount" filled with "0"
    And I see field "data/0/ecoSpeedEventCount" filled with "0"
    And I see field "data/0/harshAccelerationScore" filled with "100"
    And I see field "data/0/harshBrakingScore" filled with "100"
    And I see field "data/0/harshCorneringScore" filled with "100"
    And I see field "data/0/speeding" filled with "100"
    And I see field "data/0/totalDistance" filled with "null"
    And I see field "data/0/drivingTotalTime" filled with "null"
    And I see field "data/0/totalScore" filled with "100"
    And I see field "data/0/totalAvgSpeed" filled with "null"

  Scenario: I want get driver total driving behavior stats
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
    Then I want change installed device date for saved vehicle to '2019-11-01T18:55:30+00:00'
    And I want set vehicle driver with user of current team with date "2019-05-29 08:19:38"
    When I want fill "payload" field with "000F383838383838383838383838383838"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response"
    And I see field "imei"
    When I want fill "payload" field with "00000000000004dc080c0000016e65ccec58011066e2312020aaa600e200eb120031fd1709ef01f001c8007100210025353013fd02fe0e094239b51800314300004400000d06c70f005d2a052b2b00003100bb04f100006465c700002b5310005587a90c000e07d701ee00000000000000000000016e65ccec62011066e2312020aaa600e200eb120031f31607ef01f001c80071002100253530130a4239b51800314300004400000d06c70f005d2a052b2b00003100bbf303e804f100006465c700002b5310005587a90c000e07d901ee00000000000000000000016e65cd03c8011066bec620209f0f00e200f7100021fd1709ef01f001c8007100211325303014fd02fe0e094239761800214300004400000d06c70f005d2a052f2b00003100bb04f100006465c700002b9710005587ed0c000e07df01ee00000000000000000000016e65cd03d2011066bec620209f0f00e200f7100021f31607ef01f001c80071002113253030140a4239761800124300004400000d06c70f005d2a052f2b00003100bbf303e804f100006465c700002ba710005587fd0c000e07e101ee00000000000000000000016e65cd07b0011066ba052020a01a00e200f810001cfd1709ef01f001c8007100210025233015fd02fe0e0942397d1800124300004400000d06c70f005d2a05332b00003100bb04f100006465c700002ba710005587fd0c000e07e101ee00000000000000000000016e65cd07ba011066ba052020a01a00e200f810001cf31607ef01f001c80071002100252330150a42397d1800124300004400000d06c70f005d2a05332b00003100bbf303e804f100006465c700002ba710005587fd0c000e07e101ee00000000000000000000016e65cd07c4011066ba052020a01a00e200f810001cff1608ef01f001c8007100210025233015ff1c0942397d1800124300004400000d06c70f005d2a05332b00003100bb04f100006465c700002ba710005587fd0c000e07e101ee00000000000000000000016e65cd0f8a011066b34020209f2000e300f70d0012fd1709ef01f001c8007100210025233015fd02fe10094239991800124300004400000d06c70f005d2a05332b00003100bb04f100006465c700002bac10005588020c000e07e101ee00000000000000000000016e65cd0f94011066b34020209f2000e300f70d0012f31607ef01f001c80071002100252330150a4239991800124300004400000d06c70f005d2a05332b00003100bbf303e804f100006465c700002bac10005588020c000e07e101ee00000000000000000000016e65cd1368011066b18f20209e9a00e200f70d000dfd1709ef01f001c8007100210025233015fd02fe0e094239a218000d4300004400000d06c70f005d2a05332b00003100bb04f100006465c700002baf10005588050c000e07e101ee00000000000000000000016e65cd1372011066b18f20209e9a00e200f70d000df31607ef01f001c80071002100252330150a4239a218000d4300004400000d06c70f005d2a05332b00003100bbf303e804f100006465c700002baf10005588050c000e07e101ee00000000000000000000016e65cd9c20011066ab1d20209b6900e400f3110010fd1709ef01f001c8007100210225003017fd01fe1009423a611800104300004400000d06c70f005d2a05562b00003100bb04f100006465c700002bb110005588070c000e07e901ee00000000000000000c0000e8ad"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response"
    And I see field "imei"
    Given Calculate idling
    Given Calculate speeding
    When I want clean filled data
    When I want fill "startDate" field with "2019-10-30T18:55:30+00:00"
    And I want fill "endDate" field with "2019-12-30T22:00:00+00:00"
    Then I want get driver total driving behavior stats for team user
    And I see field "driverId"
    And I see field "harshAccelerationScore" filled with "89.36"
    And I see field "harshBrakingScore" filled with "100"
    And I see field "harshCorneringScore" filled with "100"
    And I see field "idlingScore" filled with "100"
    And I see field "speedingScore" filled with "100"
    And I see field "totalScore" filled with "97.87"
    And I see field "drivingTotalTime" filled with "45"
    And I see field "totalDistance" filled with "94"
    And I see field "totalAvgSpeed" filled with "7.52"

  Scenario: I want get driver scores for driving behavior
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
    Then I want change installed device date for saved vehicle to '2019-11-01T18:55:30+00:00'
    And I want set vehicle driver with user of current team with date "2019-05-29 08:19:38"
    Given Elastica populate
    When I want fill "payload" field with "000F383838383838383838383838383838"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response"
    And I see field "imei"
    When I want fill "payload" field with "00000000000004dc080c0000016e65ccec58011066e2312020aaa600e200eb120031fd1709ef01f001c8007100210025353013fd02fe0e094239b51800314300004400000d06c70f005d2a052b2b00003100bb04f100006465c700002b5310005587a90c000e07d701ee00000000000000000000016e65ccec62011066e2312020aaa600e200eb120031f31607ef01f001c80071002100253530130a4239b51800314300004400000d06c70f005d2a052b2b00003100bbf303e804f100006465c700002b5310005587a90c000e07d901ee00000000000000000000016e65cd03c8011066bec620209f0f00e200f7100021fd1709ef01f001c8007100211325303014fd02fe0e094239761800214300004400000d06c70f005d2a052f2b00003100bb04f100006465c700002b9710005587ed0c000e07df01ee00000000000000000000016e65cd03d2011066bec620209f0f00e200f7100021f31607ef01f001c80071002113253030140a4239761800124300004400000d06c70f005d2a052f2b00003100bbf303e804f100006465c700002ba710005587fd0c000e07e101ee00000000000000000000016e65cd07b0011066ba052020a01a00e200f810001cfd1709ef01f001c8007100210025233015fd02fe0e0942397d1800124300004400000d06c70f005d2a05332b00003100bb04f100006465c700002ba710005587fd0c000e07e101ee00000000000000000000016e65cd07ba011066ba052020a01a00e200f810001cf31607ef01f001c80071002100252330150a42397d1800124300004400000d06c70f005d2a05332b00003100bbf303e804f100006465c700002ba710005587fd0c000e07e101ee00000000000000000000016e65cd07c4011066ba052020a01a00e200f810001cff1608ef01f001c8007100210025233015ff1c0942397d1800124300004400000d06c70f005d2a05332b00003100bb04f100006465c700002ba710005587fd0c000e07e101ee00000000000000000000016e65cd0f8a011066b34020209f2000e300f70d0012fd1709ef01f001c8007100210025233015fd02fe10094239991800124300004400000d06c70f005d2a05332b00003100bb04f100006465c700002bac10005588020c000e07e101ee00000000000000000000016e65cd0f94011066b34020209f2000e300f70d0012f31607ef01f001c80071002100252330150a4239991800124300004400000d06c70f005d2a05332b00003100bbf303e804f100006465c700002bac10005588020c000e07e101ee00000000000000000000016e65cd1368011066b18f20209e9a00e200f70d000dfd1709ef01f001c8007100210025233015fd02fe0e094239a218000d4300004400000d06c70f005d2a05332b00003100bb04f100006465c700002baf10005588050c000e07e101ee00000000000000000000016e65cd1372011066b18f20209e9a00e200f70d000df31607ef01f001c80071002100252330150a4239a218000d4300004400000d06c70f005d2a05332b00003100bbf303e804f100006465c700002baf10005588050c000e07e101ee00000000000000000000016e65cd9c20011066ab1d20209b6900e400f3110010fd1709ef01f001c8007100210225003017fd01fe1009423a611800104300004400000d06c70f005d2a05562b00003100bb04f100006465c700002bb110005588070c000e07e901ee00000000000000000c0000e8ad"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response"
    And I see field "imei"
    Given Calculate idling
    Given Calculate speeding
    When I want clean filled data
    When I want fill "groupDate" field with "2019-11-13T18:55:30+00:00"
    And I want fill "groupType" field with "day"
    And I want fill "groupCount" field with "3"
    And I want fill "startDate" field with "2019-11-10T18:55:30+00:00"
    And I want fill "endDate" field with "2019-11-15T22:00:00+00:00"
    Then I want get driver scores for driving behavior for saved driver id
    And I see field "0/vehicleId"
    And I see field "0/harshAccelerationCount" filled with "1"
    And I see field "0/harshBrakingCount" filled with "0"
    And I see field "0/harshCorneringCount" filled with "0"
    And I see field "0/idlingCount" filled with "0"
    And I see field "0/ecoSpeedEventCount" filled with "0"
    And I see field "0/totalDistance" filled with "94"
    And I see field "0/drivingTotalTime" filled with "45"
    And I see field "0/overallScore" filled with "97.87"
    And I see field "0/start" filled with "2019-11-13T00:00:00+00:00"
    And I see field "0/end" filled with "2019-11-13T23:59:59+00:00"

  Scenario: I want get driver summary
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
    Then I want change installed device date for saved vehicle to '2019-11-01T18:55:30+00:00'
    And I want set vehicle driver with user of current team with date "2019-05-29 08:19:38"
    Given Elastica populate
    When I want fill "payload" field with "000F383838383838383838383838383838"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response"
    And I see field "imei"
    When I want fill "payload" field with "00000000000004dc080c0000016e65ccec58011066e2312020aaa600e200eb120031fd1709ef01f001c8007100210025353013fd02fe0e094239b51800314300004400000d06c70f005d2a052b2b00003100bb04f100006465c700002b5310005587a90c000e07d701ee00000000000000000000016e65ccec62011066e2312020aaa600e200eb120031f31607ef01f001c80071002100253530130a4239b51800314300004400000d06c70f005d2a052b2b00003100bbf303e804f100006465c700002b5310005587a90c000e07d901ee00000000000000000000016e65cd03c8011066bec620209f0f00e200f7100021fd1709ef01f001c8007100211325303014fd02fe0e094239761800214300004400000d06c70f005d2a052f2b00003100bb04f100006465c700002b9710005587ed0c000e07df01ee00000000000000000000016e65cd03d2011066bec620209f0f00e200f7100021f31607ef01f001c80071002113253030140a4239761800124300004400000d06c70f005d2a052f2b00003100bbf303e804f100006465c700002ba710005587fd0c000e07e101ee00000000000000000000016e65cd07b0011066ba052020a01a00e200f810001cfd1709ef01f001c8007100210025233015fd02fe0e0942397d1800124300004400000d06c70f005d2a05332b00003100bb04f100006465c700002ba710005587fd0c000e07e101ee00000000000000000000016e65cd07ba011066ba052020a01a00e200f810001cf31607ef01f001c80071002100252330150a42397d1800124300004400000d06c70f005d2a05332b00003100bbf303e804f100006465c700002ba710005587fd0c000e07e101ee00000000000000000000016e65cd07c4011066ba052020a01a00e200f810001cff1608ef01f001c8007100210025233015ff1c0942397d1800124300004400000d06c70f005d2a05332b00003100bb04f100006465c700002ba710005587fd0c000e07e101ee00000000000000000000016e65cd0f8a011066b34020209f2000e300f70d0012fd1709ef01f001c8007100210025233015fd02fe10094239991800124300004400000d06c70f005d2a05332b00003100bb04f100006465c700002bac10005588020c000e07e101ee00000000000000000000016e65cd0f94011066b34020209f2000e300f70d0012f31607ef01f001c80071002100252330150a4239991800124300004400000d06c70f005d2a05332b00003100bbf303e804f100006465c700002bac10005588020c000e07e101ee00000000000000000000016e65cd1368011066b18f20209e9a00e200f70d000dfd1709ef01f001c8007100210025233015fd02fe0e094239a218000d4300004400000d06c70f005d2a05332b00003100bb04f100006465c700002baf10005588050c000e07e101ee00000000000000000000016e65cd1372011066b18f20209e9a00e200f70d000df31607ef01f001c80071002100252330150a4239a218000d4300004400000d06c70f005d2a05332b00003100bbf303e804f100006465c700002baf10005588050c000e07e101ee00000000000000000000016e65cd9c20011066ab1d20209b6900e400f3110010fd1709ef01f001c8007100210225003017fd01fe1009423a611800104300004400000d06c70f005d2a05562b00003100bb04f100006465c700002bb110005588070c000e07e901ee00000000000000000c0000e8ad"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response"
    And I see field "imei"
    Given Calculate idling
    Given Calculate speeding
    When I want clean filled data
    And I want fill "startDate" field with "2019-11-10T18:55:30+00:00"
    And I want fill "endDate" field with "2019-11-15T22:00:00+00:00"
    And I want fill "page" field with "1"
    And I want fill "limit" field with "5"
    Then I want get driver summary
    And I see field "page" filled with "1"
    And I see field "limit" filled with "5"
    And I see field "total"
    And I see field "data/0/harshAccelerationCount" filled with "0"
    And I see field "data/0/harshBrakingCount" filled with "0"
    And I see field "data/0/harshCorneringCount" filled with "0"
    And I see field "data/0/idlingCount" filled with "0"
    And I see field "data/0/ecoSpeedEventCount" filled with "0"
    And I see field "data/0/harshAccelerationScore" filled with "100"
    And I see field "data/0/harshBrakingScore" filled with "100"
    And I see field "data/0/harshCorneringScore" filled with "100"
    And I see field "data/0/speeding" filled with "100"
    And I see field "data/0/totalDistance" filled with "null"
    And I see field "data/0/drivingTotalTime" filled with "null"
    And I see field "data/0/totalScore" filled with "100"
    And I see field "data/0/totalAvgSpeed" filled with "null"

  Scenario: I want get driver eco-speed for driving behavior
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
    Then I want change installed device date for saved vehicle to '2019-11-01T18:55:30+00:00'
    And I want set vehicle driver with user of current team with date "2019-05-29 08:19:38"
    Given Elastica populate
    When I want fill "payload" field with "000F383838383838383838383838383838"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response"
    And I see field "imei"
    When I want fill "payload" field with "000000000000016b0805000001701400129000567e539ce95e0fa1001700080f0048001008ef01f00150011505c80045011e02254507b5000db60007423a55180048430f6444000931371901100000008b000000017014001a6000567e5648e95e1f0f001800080f0050001008ef01f00150011505c80045011e02255007b5000db60007423a02180050430f6744000931371a0110000000b4000000017014002a0000567e5af8e95e3f49001900020f0055001008ef01f00150011505c80045011e02255807b5000db600074239f2180055430f6744000931371a0110000001110000000170140039a000567e5720e95e5fa40018015e0f0056001008ef01f00150011505c80045011e02255507b5000db600074239e8180056430f6744000931371a01100000016f000000017014003d8800567e5475e95e67850019015b0f0051001008ef01f00150011505c80045011e02255507b5000db600074239d3180051430f6744000931371a01100000018500050000f163"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response"
    And I see field "imei"
    Given Calculate speeding
    When I want clean filled data
    And I want fill "startDate" field with "2020-02-04T18:55:30+00:00"
    And I want fill "endDate" field with "2020-02-06T22:00:00+00:00"
    Then I want get driver eco-speed
    And I see field "data/0/duration" filled with "1"
    And I see field "data/0/avgSpeed" filled with "86"
    And I see field "data/0/totalDistance" filled with "22"
    And I see field "data/0/coordinates"
    And I see field "score" filled with "96"

  Scenario: I want to get CSV for vehicle summary
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
    Then I want change installed device date for saved vehicle to '2019-11-01T18:55:30+00:00'
    Given Elastica populate
    When I want fill "payload" field with "000F383838383838383838383838383838"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response"
    And I see field "imei"
    When I want fill "payload" field with "00000000000004dc080c0000016e65ccec58011066e2312020aaa600e200eb120031fd1709ef01f001c8007100210025353013fd02fe0e094239b51800314300004400000d06c70f005d2a052b2b00003100bb04f100006465c700002b5310005587a90c000e07d701ee00000000000000000000016e65ccec62011066e2312020aaa600e200eb120031f31607ef01f001c80071002100253530130a4239b51800314300004400000d06c70f005d2a052b2b00003100bbf303e804f100006465c700002b5310005587a90c000e07d901ee00000000000000000000016e65cd03c8011066bec620209f0f00e200f7100021fd1709ef01f001c8007100211325303014fd02fe0e094239761800214300004400000d06c70f005d2a052f2b00003100bb04f100006465c700002b9710005587ed0c000e07df01ee00000000000000000000016e65cd03d2011066bec620209f0f00e200f7100021f31607ef01f001c80071002113253030140a4239761800124300004400000d06c70f005d2a052f2b00003100bbf303e804f100006465c700002ba710005587fd0c000e07e101ee00000000000000000000016e65cd07b0011066ba052020a01a00e200f810001cfd1709ef01f001c8007100210025233015fd02fe0e0942397d1800124300004400000d06c70f005d2a05332b00003100bb04f100006465c700002ba710005587fd0c000e07e101ee00000000000000000000016e65cd07ba011066ba052020a01a00e200f810001cf31607ef01f001c80071002100252330150a42397d1800124300004400000d06c70f005d2a05332b00003100bbf303e804f100006465c700002ba710005587fd0c000e07e101ee00000000000000000000016e65cd07c4011066ba052020a01a00e200f810001cff1608ef01f001c8007100210025233015ff1c0942397d1800124300004400000d06c70f005d2a05332b00003100bb04f100006465c700002ba710005587fd0c000e07e101ee00000000000000000000016e65cd0f8a011066b34020209f2000e300f70d0012fd1709ef01f001c8007100210025233015fd02fe10094239991800124300004400000d06c70f005d2a05332b00003100bb04f100006465c700002bac10005588020c000e07e101ee00000000000000000000016e65cd0f94011066b34020209f2000e300f70d0012f31607ef01f001c80071002100252330150a4239991800124300004400000d06c70f005d2a05332b00003100bbf303e804f100006465c700002bac10005588020c000e07e101ee00000000000000000000016e65cd1368011066b18f20209e9a00e200f70d000dfd1709ef01f001c8007100210025233015fd02fe0e094239a218000d4300004400000d06c70f005d2a05332b00003100bb04f100006465c700002baf10005588050c000e07e101ee00000000000000000000016e65cd1372011066b18f20209e9a00e200f70d000df31607ef01f001c80071002100252330150a4239a218000d4300004400000d06c70f005d2a05332b00003100bbf303e804f100006465c700002baf10005588050c000e07e101ee00000000000000000000016e65cd9c20011066ab1d20209b6900e400f3110010fd1709ef01f001c8007100210225003017fd01fe1009423a611800104300004400000d06c70f005d2a05562b00003100bb04f100006465c700002bb110005588070c000e07e901ee00000000000000000c0000e8ad"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response"
    And I see field "imei"
    Given Calculate idling
    Given Calculate speeding
    When I want clean filled data
    And I want fill "startDate" field with "2019-11-10T18:55:30+00:00"
    And I want fill "endDate" field with "2019-11-15T22:00:00+00:00"
    And I want fill "page" field with "47"
    And I want fill "limit" field with "1"
    Then I want add value to array key "fields" with "harshAcceleration"
    And I want add value to array key "fields" with "drivingTotalTime"
    And I want add value to array key "fields" with "totalDistance"
    Then I want to get vehicle summary csv
    And I see csv item number 2 field "Harsh Acceleration" filled with "89.36 (x1)"
    And I see csv item number 2 field "Driving Total Time" filled with "00:00:45"
    And I see csv item number 2 field "Total Distance" filled with "0.1"
    And response code is 200

  Scenario: I want to get CSV for driver summary
    Then I want fill "model" field with 2
    Then I want fill "type" field with "Car"
    Then I want fill "vin" field with 4
    Then I want fill "fuelType" field with 1
    Then I want fill "year" field with "2000"
    And I want get client by name "ACME1" and save id
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
    Then I want change installed device date for saved vehicle to '2019-11-01T18:55:30+00:00'
    And I want set vehicle driver with user of current team with date "2019-05-29 08:19:38"
    Given Elastica populate
    When I want fill "payload" field with "000F383838383838383838383838383838"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response"
    And I see field "imei"
    When I want fill "payload" field with "00000000000004dc080c0000016e65ccec58011066e2312020aaa600e200eb120031fd1709ef01f001c8007100210025353013fd02fe0e094239b51800314300004400000d06c70f005d2a052b2b00003100bb04f100006465c700002b5310005587a90c000e07d701ee00000000000000000000016e65ccec62011066e2312020aaa600e200eb120031f31607ef01f001c80071002100253530130a4239b51800314300004400000d06c70f005d2a052b2b00003100bbf303e804f100006465c700002b5310005587a90c000e07d901ee00000000000000000000016e65cd03c8011066bec620209f0f00e200f7100021fd1709ef01f001c8007100211325303014fd02fe0e094239761800214300004400000d06c70f005d2a052f2b00003100bb04f100006465c700002b9710005587ed0c000e07df01ee00000000000000000000016e65cd03d2011066bec620209f0f00e200f7100021f31607ef01f001c80071002113253030140a4239761800124300004400000d06c70f005d2a052f2b00003100bbf303e804f100006465c700002ba710005587fd0c000e07e101ee00000000000000000000016e65cd07b0011066ba052020a01a00e200f810001cfd1709ef01f001c8007100210025233015fd02fe0e0942397d1800124300004400000d06c70f005d2a05332b00003100bb04f100006465c700002ba710005587fd0c000e07e101ee00000000000000000000016e65cd07ba011066ba052020a01a00e200f810001cf31607ef01f001c80071002100252330150a42397d1800124300004400000d06c70f005d2a05332b00003100bbf303e804f100006465c700002ba710005587fd0c000e07e101ee00000000000000000000016e65cd07c4011066ba052020a01a00e200f810001cff1608ef01f001c8007100210025233015ff1c0942397d1800124300004400000d06c70f005d2a05332b00003100bb04f100006465c700002ba710005587fd0c000e07e101ee00000000000000000000016e65cd0f8a011066b34020209f2000e300f70d0012fd1709ef01f001c8007100210025233015fd02fe10094239991800124300004400000d06c70f005d2a05332b00003100bb04f100006465c700002bac10005588020c000e07e101ee00000000000000000000016e65cd0f94011066b34020209f2000e300f70d0012f31607ef01f001c80071002100252330150a4239991800124300004400000d06c70f005d2a05332b00003100bbf303e804f100006465c700002bac10005588020c000e07e101ee00000000000000000000016e65cd1368011066b18f20209e9a00e200f70d000dfd1709ef01f001c8007100210025233015fd02fe0e094239a218000d4300004400000d06c70f005d2a05332b00003100bb04f100006465c700002baf10005588050c000e07e101ee00000000000000000000016e65cd1372011066b18f20209e9a00e200f70d000df31607ef01f001c80071002100252330150a4239a218000d4300004400000d06c70f005d2a05332b00003100bbf303e804f100006465c700002baf10005588050c000e07e101ee00000000000000000000016e65cd9c20011066ab1d20209b6900e400f3110010fd1709ef01f001c8007100210225003017fd01fe1009423a611800104300004400000d06c70f005d2a05562b00003100bb04f100006465c700002bb110005588070c000e07e901ee00000000000000000c0000e8ad"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response"
    And I see field "imei"
    Given Calculate idling
    Given Calculate speeding
    When I want clean filled data
    And I want login as client with name "client-name-0"
    And I want fill "startDate" field with "2019-11-10T18:55:30+00:00"
    And I want fill "endDate" field with "2019-11-15T22:00:00+00:00"
    And I want fill "page" field with "1"
    And I want fill "limit" field with "5"
    Then I want add value to array key "fields" with "harshAcceleration"
    And I want add value to array key "fields" with "drivingTotalTime"
    And I want add value to array key "fields" with "totalDistance"
    Then I want to get driver summary csv
    And I see csv item number 0 field "Harsh Acceleration" filled with "89.36 (x1)"
    And I see csv item number 0 field "Driving Total Time" filled with "00:00:45"
    And I see csv item number 0 field "Total Distance" filled with "0.1"
    And response code is 200

  Scenario: I test total distance for team vehicles
    Then I want fill "model" field with 2
    Then I want fill "type" field with "Car"
    Then I want fill "vin" field with 4
    Then I want fill "fuelType" field with 1
    Then I want fill "regNo" field with "test regno"
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
    Then I want change installed device date for saved vehicle to '2019-11-01T18:55:30+00:00'
    Given I signed in as "admin" team "client" and teamId 2
    Then I want set vehicle driver with current user
    When I want fill "payload" field with "000F383838383838383838383838383838"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response"
    And I see field "imei"
    When I want fill "payload" field with "00000000000004dc080c0000016e65ccec58011066e2312020aaa600e200eb120031fd1709ef01f001c8007100210025353013fd02fe0e094239b51800314300004400000d06c70f005d2a052b2b00003100bb04f100006465c700002b5310005587a90c000e07d701ee00000000000000000000016e65ccec62011066e2312020aaa600e200eb120031f31607ef01f001c80071002100253530130a4239b51800314300004400000d06c70f005d2a052b2b00003100bbf303e804f100006465c700002b5310005587a90c000e07d901ee00000000000000000000016e65cd03c8011066bec620209f0f00e200f7100021fd1709ef01f001c8007100211325303014fd02fe0e094239761800214300004400000d06c70f005d2a052f2b00003100bb04f100006465c700002b9710005587ed0c000e07df01ee00000000000000000000016e65cd03d2011066bec620209f0f00e200f7100021f31607ef01f001c80071002113253030140a4239761800124300004400000d06c70f005d2a052f2b00003100bbf303e804f100006465c700002ba710005587fd0c000e07e101ee00000000000000000000016e65cd07b0011066ba052020a01a00e200f810001cfd1709ef01f001c8007100210025233015fd02fe0e0942397d1800124300004400000d06c70f005d2a05332b00003100bb04f100006465c700002ba710005587fd0c000e07e101ee00000000000000000000016e65cd07ba011066ba052020a01a00e200f810001cf31607ef01f001c80071002100252330150a42397d1800124300004400000d06c70f005d2a05332b00003100bbf303e804f100006465c700002ba710005587fd0c000e07e101ee00000000000000000000016e65cd07c4011066ba052020a01a00e200f810001cff1608ef01f001c8007100210025233015ff1c0942397d1800124300004400000d06c70f005d2a05332b00003100bb04f100006465c700002ba710005587fd0c000e07e101ee00000000000000000000016e65cd0f8a011066b34020209f2000e300f70d0012fd1709ef01f001c8007100210025233015fd02fe10094239991800124300004400000d06c70f005d2a05332b00003100bb04f100006465c700002bac10005588020c000e07e101ee00000000000000000000016e65cd0f94011066b34020209f2000e300f70d0012f31607ef01f001c80071002100252330150a4239991800124300004400000d06c70f005d2a05332b00003100bbf303e804f100006465c700002bac10005588020c000e07e101ee00000000000000000000016e65cd1368011066b18f20209e9a00e200f70d000dfd1709ef01f001c8007100210025233015fd02fe0e094239a218000d4300004400000d06c70f005d2a05332b00003100bb04f100006465c700002baf10005588050c000e07e101ee00000000000000000000016e65cd1372011066b18f20209e9a00e200f70d000df31607ef01f001c80071002100252330150a4239a218000d4300004400000d06c70f005d2a05332b00003100bbf303e804f100006465c700002baf10005588050c000e07e101ee00000000000000000000016e65cd9c20011066ab1d20209b6900e400f3110010fd1709ef01f001c8007100210225003017fd01fe1009423a611800104300004400000d06c70f005d2a05562b00003100bb04f100006465c700002bb110005588070c000e07e901ee00000000000000000c0000e8ad"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response"
    And I see field "imei"
    Given Calculate routes
    When I want clean filled data
    When I want fill "days" field with "1000"
    Then I want get team vehicles total distance
    And I see field "distance/current/total_distance" filled with 94
    And I see field "distance/prev/total_distance" filled with 0
    And I see field "shortest/vehicles/0/vehicle_regno" filled with "test regno"
    And I see field "longest/vehicles/0/vehicle_regno" filled with "test regno"
    Then I want get team drivers total distance
    And I see field "shortest/drivers/0/fullname" filled with "test user surname"
    And I see field "longest/drivers/0/total_distance" filled with 94
    And I see field "longest/drivers/0/fullname" filled with "test user surname"
    And I see field "longest/drivers/0/total_distance" filled with 94

  Scenario: I want test driving behavior dashboard
    Given insert Procedures
    Given I signed in as "admin" team "client" and teamId 2
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
    Given I signed in as "super_admin" team "admin"
    Then I want to create device for vehicle and save id
    Given I signed in as "admin" team "client" and teamId 2
    Then I want install device for vehicle
    And response code is 200
    Then I want change installed device date for saved vehicle to '2019-11-01T18:55:30+00:00'
    And response code is 200
    And I want set vehicle driver with user of current team with date "2019-05-29 08:19:38"
    And response code is 200
    Given Elastica populate
    When I want fill "payload" field with "000F383838383838383838383838383838"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response"
    And I see field "imei"
    When I want fill "payload" field with "00000000000004dc080c0000016e65ccec58011066e2312020aaa600e200eb120031fd1709ef01f001c8007100210025353013fd02fe0e094239b51800314300004400000d06c70f005d2a052b2b00003100bb04f100006465c700002b5310005587a90c000e07d701ee00000000000000000000016e65ccec62011066e2312020aaa600e200eb120031f31607ef01f001c80071002100253530130a4239b51800314300004400000d06c70f005d2a052b2b00003100bbf303e804f100006465c700002b5310005587a90c000e07d901ee00000000000000000000016e65cd03c8011066bec620209f0f00e200f7100021fd1709ef01f001c8007100211325303014fd02fe0e094239761800214300004400000d06c70f005d2a052f2b00003100bb04f100006465c700002b9710005587ed0c000e07df01ee00000000000000000000016e65cd03d2011066bec620209f0f00e200f7100021f31607ef01f001c80071002113253030140a4239761800124300004400000d06c70f005d2a052f2b00003100bbf303e804f100006465c700002ba710005587fd0c000e07e101ee00000000000000000000016e65cd07b0011066ba052020a01a00e200f810001cfd1709ef01f001c8007100210025233015fd02fe0e0942397d1800124300004400000d06c70f005d2a05332b00003100bb04f100006465c700002ba710005587fd0c000e07e101ee00000000000000000000016e65cd07ba011066ba052020a01a00e200f810001cf31607ef01f001c80071002100252330150a42397d1800124300004400000d06c70f005d2a05332b00003100bbf303e804f100006465c700002ba710005587fd0c000e07e101ee00000000000000000000016e65cd07c4011066ba052020a01a00e200f810001cff1608ef01f001c8007100210025233015ff1c0942397d1800124300004400000d06c70f005d2a05332b00003100bb04f100006465c700002ba710005587fd0c000e07e101ee00000000000000000000016e65cd0f8a011066b34020209f2000e300f70d0012fd1709ef01f001c8007100210025233015fd02fe10094239991800124300004400000d06c70f005d2a05332b00003100bb04f100006465c700002bac10005588020c000e07e101ee00000000000000000000016e65cd0f94011066b34020209f2000e300f70d0012f31607ef01f001c80071002100252330150a4239991800124300004400000d06c70f005d2a05332b00003100bbf303e804f100006465c700002bac10005588020c000e07e101ee00000000000000000000016e65cd1368011066b18f20209e9a00e200f70d000dfd1709ef01f001c8007100210025233015fd02fe0e094239a218000d4300004400000d06c70f005d2a05332b00003100bb04f100006465c700002baf10005588050c000e07e101ee00000000000000000000016e65cd1372011066b18f20209e9a00e200f70d000df31607ef01f001c80071002100252330150a4239a218000d4300004400000d06c70f005d2a05332b00003100bbf303e804f100006465c700002baf10005588050c000e07e101ee00000000000000000000016e65cd9c20011066ab1d20209b6900e400f3110010fd1709ef01f001c8007100210225003017fd01fe1009423a611800104300004400000d06c70f005d2a05562b00003100bb04f100006465c700002bb110005588070c000e07e901ee00000000000000000c0000e8ad"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response"
    And I see field "imei"
    Given Calculate idling
    Given Calculate speeding
    When I want clean filled data
    And I want fill "days" field with "300"
    Then I want get driving behavior dashboard
    And response code is 200
    And I see field "drivers/best"
    And I see field "drivers/worst"
    And I see field "vehicles/best/0/totalScore" filled with 97.87
    And I see field "vehicles/worst/0/totalScore" filled with 97.87
    When I want clean filled data
    And I want fill "groupType" field with "day"
    And I want fill "groupCount" field with "30"
    And I want fill "groupDate" field with "2019-12-01T18:55:30+00:00"
    Then I want get driving behavior dashboard by date range
    And response code is 200
    And I see field "vehiclesTotalAverageScore" filled with 99.929
    And I see field "data/18/vehiclesAverageScore" filled with 97.87

  Scenario: I want send tcp data to API for Topflytech with driver behavior event
    Given There are following tracker payload from topflytech tracker with socket "test-topflytech-socket-id":
      | payload                                                                                                                                                                                                                                                              |
      | 2525010015000a0866425035404484100441250331                                                                                                                                                                                                                         |
      | 252505003C000108664250354044840020051212100258866B4276D6E342912AB4411115050500000000000058866B4276D6E342912AB44111150505   |
    Given Elastica populate
    When I want fill "startDate" field with "2020-05-11T18:55:30+00:00"
    And I want fill "endDate" field with "2020-05-13T22:00:00+00:00"
    And I want fill "limit" field with "100"
    And I want fill "sort" field with "-harshBrakingCount"
    Then I want get vehicle summary
    And I see field "data/0/harshBrakingCount" filled with "1"
