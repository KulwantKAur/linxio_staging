Feature: Tracker Ulbotech

  Scenario: I want connect to tcp server
    When I want to connect to ulbotech tcp server

  Scenario: I want send tcp data to API
    When I want fill "payload" field with "2a545330312c38363131303730333431313336363323"
    Then I want to send ulbotech tcp data to api with socket "test-ulbotech-socket-id"
    Then I see field "response" filled with "2a545330312c41434b3a36393923"
    And I see field "imei" filled with "string(861107034113663)"
    When I want fill "payload" field with "2a545330312c3836313130373033343131333636332c3039323730313131303332302c4750533a333b5333332e3837343230363b453135302e3836383735303b303b303b312e31312c5354543a3234323b302c4d47523a3837313534302c4144433a303b31342e35313b313b33322e38393b323b342e33312c56494e3a5741555a5a5a344d384a443031383432382c4556543a46303b323030232a545330312c3836313130373033343131333636332c3039323732353131303332302c4c42533a3530353b313b323037343b374641413730313b3132302c5354543a433234323b302c4d47523a3837313534302c4144433a303b31342e34303b313b33332e30383b323b342e33302c4556543a46303b43303030232a545330312c3836313130373033343131333636332c3039323733323131303332302c4c42533a3530353b313b323037343b374641413730313b3132302c5354543a3234323b302c4d47523a3837313534302c4144433a303b31342e34333b313b33322e38393b323b342e33302c4556543a46303b43303030232a545330312c3836313130373033343131333636332c3039323833323131303332302c4c42533a3530353b313b464646463b46464646464646463b3132302c5354543a3234323b302c4d47523a3837313534382c4144433a303b31342e36393b313b33332e30383b323b342e33312c4556543a31232a545330312c3836313130373033343131333636332c3039323834303131303332302c4c42533a3530353b313b303135303b4444353237423b3132302c5354543a433234323b302c4d47523a3837313535312c4144433a303b31342e35353b313b33332e30383b323b342e33312c4556543a46303b4330303023"
    Then I want to send ulbotech tcp data to api with socket "test-ulbotech-socket-id"
    Then I see field "response" filled with "2a545330312c41434b3a3231353323"
    When I want fill "imei" field with "861107034113663"
    And I want fill "startDate" field with "2020-03-10T00:00:00+00:00"
    And I want fill "endDate" field with "2020-03-12T23:59:59+00:00"
    Then I want to get tracker data log
    And I see field "total" filled with "1"
    And I see field "data"
    And I see field "data/0/ts" filled with "2020-03-11T09:27:01+00:00"
    And I see field "data/0/createdAt"
    And I see field "data/0/gpsData/speed" filled with "0"
    And I see field "data/0/gpsData/angle" filled with "0"
    And I see field "data/0/gpsData/coordinates/lat" filled with "custom(-33.87420600)"
    And I see field "data/0/gpsData/coordinates/lng" filled with "custom(150.86875000)"
    And response code is 200

  Scenario: I want to test device with fix-speed option
    Given I signed in as "super_admin" team "admin"
    And I want fill device model with name "T301"
    And I want fill "sn" field with "test sn"
    And I want fill "status" field with "inStock"
    And I want fill "port" field with 25
    And I want fill "hw" field with "test hw"
    And I want fill "sw" field with "test sw"
    And I want fill "imei" field with "861107034113664"
    And I want fill "phone" field with "+(375) 245535454"
    And I want fill "imsi" field with "test imsi"
    And I want fill "devEui" field with "test devEui"
    And I want fill "userName" field with "test username"
    And I want fill "password" field with "test password"
    And I want fill "clientNote" field with "test clientNote"
    And I want fill "adminNote" field with "test adminNote"
    And I want fill "isFixWithSpeed" field with "true"
    Then I want to create device and save id
    Given There are following tracker payload from ulbotech tracker with socket "ulbotech-test-socket":
      | payload                                                                                                                                                                                                                                                              |
      | 2a545330312c38363131303730333431313336363423                                                                                                                                                                                                                         |
      | 2a545330312c3836313130373033343131333636342c3037353335363134303432302c4750533a333b5333332e3838303432353b453135312e3136323036373b303b303b302e39362c5354543a433230323b302c4d47523a313437333232352c4144433a303b31322e37303b313b33342e37363b323b342e32302c4556543a3123   |
    Given Elastica populate
    Then I want to get device by saved id
    And response code is 200
    And I see field "isFixWithSpeed" filled with "true"
    And I see field "trackerData/movement" filled with "0"
    And I see field "trackerData/ignition" filled with "0"
    And I see field "trackerData/speed" filled with "0"

  Scenario: I want see tracker data logs
    Given There are following tracker payload from ulbotech tracker with socket "ulbotech-test-socket":
      | payload                                                                                                                                                                                                                                                              |
      | 2a545330312c38363131303730333431313336363323                                                                                                                                                                                                                         |
      | 2a545330312c3836313130373033343131333636332c3130303632323131303332302c4750533a333b5333332e3837353739333b453135312e3136303934323b34333b3230363b302e38392c5354543a433234323b302c4d47523a3931373334312c4144433a303b31322e38383b313b33392e32363b323b342e33322c4556543a3223   |
    When I want fill "imei" field with "861107034113663"
    And I want fill "startDate" field with "2020-03-10T00:00:00+00:00"
    And I want fill "endDate" field with "2020-03-12T23:59:59+00:00"
    Then I want to get tracker data log
    And I see field "total" filled with "1"
    And I see field "data"
    And I see field "data/0/ts" filled with "2020-03-11T10:06:22+00:00"
    And I see field "data/0/createdAt"
    And I see field "data/0/gpsData/speed" filled with "43"
    And I see field "data/0/gpsData/angle" filled with "206"
    And I see field "data/0/gpsData/coordinates/lat" filled with "custom(-33.87579300)"
    And I see field "data/0/gpsData/coordinates/lng" filled with "custom(151.16094200)"
    And I see field "data/0/payload" filled with "*TS01,861107034113663,100622110320,GPS:3;S33.875793;E151.160942;43;206;0.89,STT:C242;0,MGR:917341,ADC:0;12.88;1;39.26;2;4.32,EVT:2#"
    And response code is 200

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
    And I want fill device model for vehicle with name "FM3001"
    And I want fill "sn" field with "test sn"
    And I want fill "status" field with "inStock"
    And I want fill "port" field with 25
    And I want fill "hw" field with "test hw"
    And I want fill "sw" field with "test sw"
    And I want fill "imei" field with "865328026266188"
    And I want fill "phone" field with "+(375) 245535454"
    And I want fill "imsi" field with "test imsi"
    And I want fill "devEui" field with "test devEui"
    And I want fill "userName" field with "test username"
    And I want fill "password" field with "test password"
    And I want fill "clientNote" field with "test clientNote"
    And I want fill "adminNote" field with "test adminNote"
    Then I want to create device for vehicle and save id
    Then I want install device for vehicle
    Given There are following tracker payload from ulbotech tracker with socket "ulbotech-test-socket":
      | payload                                                                                                                                                                                                                                                              |
      | 2a545330312c38363533323830323632363631383823                                                                                                                                                                                                                         |
      | 2a545330312c3836353332383032363236363138382c3139343930373034303331352c5354543a3234323b302c4d47523a343035363633372c4144433a303b31352e31363b313b32382e37373b323b332e35352c4746533a303b302c4f42443a333130353346343130433132414533313044303033313246393934313331333332382c46554c3a343036392c4547543a3937303435362c4556543a46303b32303023   |
    Then I want to get vehicle by saved id
    And I see field "engineOnTime" filled with "970456"