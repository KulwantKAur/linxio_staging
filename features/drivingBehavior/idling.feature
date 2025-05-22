Feature: Idling

  Background:
    Given I signed in as "super_admin" team "admin"

  Scenario: I want get vehicle idling
    Given insert Procedures
    And I want fill "model" field with "Test model"
    And I want fill "type" field with "Car"
    And I want fill "vin" field with "1234567890Ab"
    And I want fill "fuelType" field with "1"
    And I want fill "year" field with "2000"
    And I want fill "ecoSpeed" field with "85"
    And I want fill "defaultLabel" field with "Default label"
    And I want fill "regNo" field with "1234567890Ab"
    Then I want fill "excessiveIdling" field with "3"
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
    Then I want change installed device date for saved vehicle to '2019-01-01T18:55:30+00:00'
    Given Elastica populate
    When I want fill "payload" field with "000F383838383838383838383838383838"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response"
    And I see field "imei"
    When I want fill "payload" field with "0000000000000391080a0000016c28e49608005a181c3eebcf0422000800fa0b000d00150c0100020003000400b301b4004503f0011505c800ef014f08060900200a0018b5000db6000a4238eb43107402f10000c545cd07ef870d014e00000000000000000000016c28e499f0005a181b4eebcf0383000800e80b000a00150c0100020003000400b301b4004503f0011505c800ef014f08060900200a0018b5000db6000a42391b43107502f10000c545cd07ef870d014e00000000000000000000016c28e49dd8005a181b0febcf0259000800c80b000a00150c0100020003000400b301b4004503f0011505c800ef014f08060900200a0018b5000db6000a4238e843107402f10000c545cd07ef870d014e00000000000000000000016c28e4a1c0005a181b1bebcf014f000800b90b000a00150c0100020003000400b301b4004503f0011505c800ef014f08060900200a0018b5000db6000a4238e043107402f10000c545cd07ef870d014e00000000000000000000016c28e4a5a8005a181b48ebcf0005000800af0b000c00150c0100020003000400b301b4004503f0011505c800ef014f08060900200a0018b5000db6000a42390543107402f10000c545cd07ef870d014e00000000000000000000016c28e53e00005a182180ebceb51f000b00ac0b0004f0150c0100020003000400b301b4004503f0001505c800ef014f08060900200a0018b5000cb6000a4238cb43107402f10000c545cd07ef870d014e00000000000000000000016c28e54da0005a1820d7ebceb375000b00d30b000900150c0100020003000400b301b4004503f0001505c800ef014f08060900200a0018b5000cb6000a4238f543107402f10000c545cd07ef870d014e00000000000000000000016c28e55188005a181fdbebceb2a0000c00e20b000cf0150c0100020003000400b301b4004503f0011505c800ef014f08060900200a0018b5000cb6000a42392843107402f10000c545cd07ef870d014e00000000000000000000016c28e55570005a181e54ebceb1e7000d00f00b000f00150c0100020003000400b301b4004503f0011505c800ef014f08060900200a0018b5000cb6000a42392143107402f10000c545cd07ef870d014e00000000000000000000016c28e55958005a181b35ebceb234000c010c0b001300150c0100020003000400b301b4004503f0011505c800ef014f08060900200a0018b5000cb6000a42390143107402f10000c545cd07ef870d014e00000000000000000a0000bd5a"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    When I want fill "startDate" field with "2019-07-24T18:55:30+00:00"
    When I want fill "endDate" field with "2019-07-26T22:00:00+00:00"
    Then I want add value to array key "fields" with "driver"
    And I want add value to array key "fields" with "vehicle"
    And I want add value to array key "fields" with "coordinates"
    And I want set remembered team settings "excessiveIdling" with raw value
    """
      {"enable":true,"value":2}
    """
    Given Calculate idling
    Then I want get vehicle idling for saved vehicle id
    And I see field "data/0/duration" filled with "5"
    And I see field "data/0/startDate"
    And I see field "data/0/endDate"
    And I see field "score" filled with "100"
    Then I want clean filled data

  Scenario: I want get driver idling
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
    When I want fill "payload" field with "00000000000003ab080d0000017013fc597000567dff0ae95bb323001101180f0000f01008ef01f00050011504c80045011e02250007b5000ab600074239bf180000430f674400093137180110ffffffff000000017013fcfd8000567dff0ae95bb323001101180e0000f01008ef01f00150001504c80045011e02250007b5000ab60007423a01180000430f674400093137180110ffffffff000000017013fd0d2000567dfc80e95bb42e001501140f000e001008ef01f00150011504c80045011e02250907b5000db60007423a2c18000e430f674400093137180110ffffffff000000017013fd110800567dfa7ce95bb460001501140f0013001008ef01f00150011504c80045011e02251407b5000db60007423a18180013430f674400093137180110ffffffff000000017013fd18d800567df4f3e95bb4e5001501150f001a001008ef01f00150011504c80045011e02251407b5000db60007423a5118001a430f674400093137180110ffffffff000000017013fd1cc000567df16fe95bb59d001501200f001f001008ef01f00150011504c80045011e02251f07b5000db60007423a4c18001f430f674400093137180110ffffffff000000017013fd20a800567dedfbe95bb6fb0015012f0f0020001008ef01f00150011504c80045011e02251f07b5000db60007423a4f180020430f674400093137180110ffffffff000000017013fd249000567deafde95bb931001601400f0022001008ef01f00150011504c80045011e02251f07b5000db60007423a4f180022430f67440009313718011000000028000000017013fd287800567de8e8e95bbbfe0015014f0f0023001008ef01f00150011504c80045011e02252407b5000ab60007423a4d180023430f67440009313718011000000031000000017013fd2c6000567de841e95bbfa30015015e0f0027001008ef01f00150011504c80045011e02252407b5000ab60007423a47180027430f6744000931371801100000003b000000017013fd304800567deaece95bc421001600060f0030001008ef01f00150011504c80045011e02253307b5000ab60007423a29180030430f67440009313718011000000045000000017013fd343000567dec4ae95bc9cb001400060f0038001008ef01f00150011504c80045011e02253307b5000db60007423a64180038430f67440009313718011000000053000000017013fd43d000567df000e95be16d001400090f003e001008ef01f00150011504c80045011e02253f07b5000db60007423a5218003e430f67440009313718011000000096000d000066d1"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response"
    And I see field "imei"
    Given Calculate idling
    And I want set remembered team settings "excessiveIdling" with raw value
    """
      {"enable":true,"value":40}
    """
    When I want clean filled data
    And I want fill "startDate" field with "2020-02-04T18:55:30+00:00"
    And I want fill "endDate" field with "2020-02-06T22:00:00+00:00"
    Then I want get driver idling
    And I see field "data/0/duration" filled with "42"
    And I see field "data/0/address" filled with "null"
    And I see field "data/0/coordinates"
    And I see field "score" filled with "90.91"