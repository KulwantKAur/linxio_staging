Feature: Speeding

  Background:
    Given I signed in as "super_admin" team "admin"

  Scenario: I want to get speeding grouped by vehicle
    And I want fill "model" field with "Test model"
    And I want fill "type" field with "Car"
    And I want fill "vin" field with "1234567890Ab"
    And I want fill "fuelType" field with "1"
    And I want fill "year" field with "2000"
    And I want fill "ecoSpeed" field with "85"
    And I want fill "defaultLabel" field with "Default label"
    And I want fill "regNo" field with "1234567890Ab"
    And I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want to create vehicle and save id
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
    And I want install device for vehicle
    Given Elastica populate
    And I want fill "payload" field with "000F383838383838383838383838383838"
    And I want to send teltonika tcp data to api with socket "test-socket-id"
    And I want fill "payload" field with "00000000000004d3080e0000016d53aeddc00010bb4b5f202c9cda00d50038130087001307ef01f001c80071002102258a3029084239be1800874300004400000d071c2a07cf2b00003108c004f100006465c70000acfa100028f28f0c0006a061000000016d53aee5900010bb7111202cab9000d50038120088001307ef01f001c80071002101258d3029084239cb1800884300004400000d071c2a07d32b00003108c004f100006465c70000ad45100028f2da0c0006a081000000016d53aeed600010bbaa19202cc18100d50038120089001307ef01f001c80071002101258d3029084239c31800894300004400000d071c2a07d32b00003108c004f100006465c70000ad91100028f3260c0006a0a2000000016d53aef9180010bbcf99202cd01600d40038140089001307ef01f001c80071002100258f3029084239da1800894300004400000d071c2a07d72b00003108c104f100006465c70000ae03100028f3980c0006a0b2000000016d53af00e80010bbf4e7202cdeee00d40038140086001307ef01f001c800710021ff258d3029084239de1800864300004400000d071c2a07db2b00003108c104f100006465c70000ae4e100028f3e30c0006a0b2000000016d53af08b80010bc19f2202cedd700d40038140086001307ef01f001c800710021ff258d3029084239db1800864300004400000d071c2a07db2b00003108c104f100006465c70000ae9a100028f42f0c0006a0d2000000016d53af10880010bc403a202cfbd600d30038140088001307ef01f001c80071002100258e3029084239de1800884300004400000d071c2a07df2b00003108c104f100006465c70000aee4100028f4790c0006a0f2000000016d53af18580010bc79a6202d117400d1003913008a001307ef01f001c80071002100258e3029084239c91800894300004400000d071c2a07df2b00003108c104f100006465c70000af31100028f4c60c0006a113000000016d53af24100010bca0c7202d1f8400d0003a14008b001307ef01f001c8007100210025903029084239e518008b4300004400000d071c2a07e32b00003108c104f100006465c70000afa2100028f5370c0006a123000000016d53af2be00010bcc7a5202d2db500cf003a140088001307ef01f001c8007100210025903029084239ee1800884300004400000d071c2a07e32b00003108c104f100006465c70000aff0100028f5850c0006a146000000016d53af2fc80010bcee40202d3bd600ce003a140087001307ef01f001c800710021fc25903029084239ec1800884300004400000d071c2a07e72b00003108c104f100006465c70000b03b100028f5d00c0006a146000000016d53af3b800010bd2727202d4f5e00cb003b140084001307ef01f001c800710021fc25903029084239f91800854300004400000d071c2a07e72b00003108c104f100006465c70000b087100028f61c0c0006a166000000016d53af43500010bd4cb8202d5c6300c9003c140082001307ef01f001c80071002100258c3029084239b31800834300004400000d071c2a07eb2b00003108c104f100006465c70000b0f4100028f6890c0006a175000000016d53af4b200010bd726a202d691500c7003c140081001307ef01f001c80071002100258c3029084239af1800814300004400000d071c2a07eb2b00003108c104f100006465c70000b13e100028f6d30c0006a192000e00005bc9"
    And I want to send teltonika tcp data to api with socket "test-socket-id"
    Given Calculate speeding
    When I want to get speeding grouped by vehicle using query "?startDate=2019-09-21&endDate=2019-09-22"
    And I see field "page" filled with "1"
    And I see field "limit" filled with "10"
    And I see field "total" filled with "1"
    And I see field "data/0/defaultLabel" filled with "Default label"
    And I see field "data/0/model" filled with "Test model"
    And I see field "data/0/regNo" filled with "1234567890Ab"
    And I see field "data/0/depot" filled with "null"
    And I see field "data/0/groups" filled with "null"

  Scenario: I want to get speeding for given vehicle
    And I want fill "model" field with "Test model"
    And I want fill "type" field with "Car"
    And I want fill "vin" field with "1234567890Ab"
    And I want fill "fuelType" field with "1"
    And I want fill "year" field with "2000"
    And I want fill "ecoSpeed" field with "85"
    And I want fill "defaultLabel" field with "Default label"
    And I want fill "regNo" field with "1234567890Ab"
    And I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want to create vehicle and save id
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
    And I want install device for vehicle
    Given Elastica populate
    And I want fill "payload" field with "000F383838383838383838383838383838"
    And I want to send teltonika tcp data to api with socket "test-socket-id"
    And I want fill "payload" field with "00000000000004d3080e0000016d53aeddc00010bb4b5f202c9cda00d50038130087001307ef01f001c80071002102258a3029084239be1800874300004400000d071c2a07cf2b00003108c004f100006465c70000acfa100028f28f0c0006a061000000016d53aee5900010bb7111202cab9000d50038120088001307ef01f001c80071002101258d3029084239cb1800884300004400000d071c2a07d32b00003108c004f100006465c70000ad45100028f2da0c0006a081000000016d53aeed600010bbaa19202cc18100d50038120089001307ef01f001c80071002101258d3029084239c31800894300004400000d071c2a07d32b00003108c004f100006465c70000ad91100028f3260c0006a0a2000000016d53aef9180010bbcf99202cd01600d40038140089001307ef01f001c80071002100258f3029084239da1800894300004400000d071c2a07d72b00003108c104f100006465c70000ae03100028f3980c0006a0b2000000016d53af00e80010bbf4e7202cdeee00d40038140086001307ef01f001c800710021ff258d3029084239de1800864300004400000d071c2a07db2b00003108c104f100006465c70000ae4e100028f3e30c0006a0b2000000016d53af08b80010bc19f2202cedd700d40038140086001307ef01f001c800710021ff258d3029084239db1800864300004400000d071c2a07db2b00003108c104f100006465c70000ae9a100028f42f0c0006a0d2000000016d53af10880010bc403a202cfbd600d30038140088001307ef01f001c80071002100258e3029084239de1800884300004400000d071c2a07df2b00003108c104f100006465c70000aee4100028f4790c0006a0f2000000016d53af18580010bc79a6202d117400d1003913008a001307ef01f001c80071002100258e3029084239c91800894300004400000d071c2a07df2b00003108c104f100006465c70000af31100028f4c60c0006a113000000016d53af24100010bca0c7202d1f8400d0003a14008b001307ef01f001c8007100210025903029084239e518008b4300004400000d071c2a07e32b00003108c104f100006465c70000afa2100028f5370c0006a123000000016d53af2be00010bcc7a5202d2db500cf003a140088001307ef01f001c8007100210025903029084239ee1800884300004400000d071c2a07e32b00003108c104f100006465c70000aff0100028f5850c0006a146000000016d53af2fc80010bcee40202d3bd600ce003a140087001307ef01f001c800710021fc25903029084239ec1800884300004400000d071c2a07e72b00003108c104f100006465c70000b03b100028f5d00c0006a146000000016d53af3b800010bd2727202d4f5e00cb003b140084001307ef01f001c800710021fc25903029084239f91800854300004400000d071c2a07e72b00003108c104f100006465c70000b087100028f61c0c0006a166000000016d53af43500010bd4cb8202d5c6300c9003c140082001307ef01f001c80071002100258c3029084239b31800834300004400000d071c2a07eb2b00003108c104f100006465c70000b0f4100028f6890c0006a175000000016d53af4b200010bd726a202d691500c7003c140081001307ef01f001c80071002100258c3029084239af1800814300004400000d071c2a07eb2b00003108c104f100006465c70000b13e100028f6d30c0006a192000e00005bc9"
    And I want to send teltonika tcp data to api with socket "test-socket-id"
    Given Calculate speeding
    When I want to get speeding for given vehicle using query "?startDate=2019-09-21&endDate=2019-09-22"
    And I see field "page" filled with "1"
    And I see field "limit" filled with "10"
    And I see field "total" filled with "1"
    And I see field "data/0/avgSpeed" filled with "135"
    And I see field "data/0/maxSpeed" filled with "139"
    And I see field "data/0/startedAt" filled with "2019-09-21T11:57:12+00:00"
    And I see field "data/0/finishedAt" filled with "2019-09-21T11:57:40+00:00"
    And I see field "data/0/distance" filled with "1092"
    And I see field "data/0/driver" filled with "string('')"
    And I see field "data/0/postedLimit" filled with "85"

  Scenario: I want to get speeding grouped by driver
    And I want fill "model" field with "Test model"
    And I want fill "type" field with "Car"
    And I want fill "vin" field with "1234567890Ab"
    And I want fill "fuelType" field with "1"
    And I want fill "year" field with "2000"
    And I want fill "ecoSpeed" field with "85"
    And I want fill "defaultLabel" field with "Default label"
    And I want fill "regNo" field with "1234567890Ab"
    And I want to set team ID by team ID of following user "nikki.burns@acme.local"
    And I want to create vehicle and save id
    And I want to set following user "nikki.burns@acme.local" as vehicle driver
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
    And I want install device for vehicle
    Given Elastica populate
    And I want fill "payload" field with "000F383838383838383838383838383838"
    And I want to send teltonika tcp data to api with socket "test-socket-id"
    And I want fill "payload" field with "00000000000004d3080e0000016d53aeddc00010bb4b5f202c9cda00d50038130087001307ef01f001c80071002102258a3029084239be1800874300004400000d071c2a07cf2b00003108c004f100006465c70000acfa100028f28f0c0006a061000000016d53aee5900010bb7111202cab9000d50038120088001307ef01f001c80071002101258d3029084239cb1800884300004400000d071c2a07d32b00003108c004f100006465c70000ad45100028f2da0c0006a081000000016d53aeed600010bbaa19202cc18100d50038120089001307ef01f001c80071002101258d3029084239c31800894300004400000d071c2a07d32b00003108c004f100006465c70000ad91100028f3260c0006a0a2000000016d53aef9180010bbcf99202cd01600d40038140089001307ef01f001c80071002100258f3029084239da1800894300004400000d071c2a07d72b00003108c104f100006465c70000ae03100028f3980c0006a0b2000000016d53af00e80010bbf4e7202cdeee00d40038140086001307ef01f001c800710021ff258d3029084239de1800864300004400000d071c2a07db2b00003108c104f100006465c70000ae4e100028f3e30c0006a0b2000000016d53af08b80010bc19f2202cedd700d40038140086001307ef01f001c800710021ff258d3029084239db1800864300004400000d071c2a07db2b00003108c104f100006465c70000ae9a100028f42f0c0006a0d2000000016d53af10880010bc403a202cfbd600d30038140088001307ef01f001c80071002100258e3029084239de1800884300004400000d071c2a07df2b00003108c104f100006465c70000aee4100028f4790c0006a0f2000000016d53af18580010bc79a6202d117400d1003913008a001307ef01f001c80071002100258e3029084239c91800894300004400000d071c2a07df2b00003108c104f100006465c70000af31100028f4c60c0006a113000000016d53af24100010bca0c7202d1f8400d0003a14008b001307ef01f001c8007100210025903029084239e518008b4300004400000d071c2a07e32b00003108c104f100006465c70000afa2100028f5370c0006a123000000016d53af2be00010bcc7a5202d2db500cf003a140088001307ef01f001c8007100210025903029084239ee1800884300004400000d071c2a07e32b00003108c104f100006465c70000aff0100028f5850c0006a146000000016d53af2fc80010bcee40202d3bd600ce003a140087001307ef01f001c800710021fc25903029084239ec1800884300004400000d071c2a07e72b00003108c104f100006465c70000b03b100028f5d00c0006a146000000016d53af3b800010bd2727202d4f5e00cb003b140084001307ef01f001c800710021fc25903029084239f91800854300004400000d071c2a07e72b00003108c104f100006465c70000b087100028f61c0c0006a166000000016d53af43500010bd4cb8202d5c6300c9003c140082001307ef01f001c80071002100258c3029084239b31800834300004400000d071c2a07eb2b00003108c104f100006465c70000b0f4100028f6890c0006a175000000016d53af4b200010bd726a202d691500c7003c140081001307ef01f001c80071002100258c3029084239af1800814300004400000d071c2a07eb2b00003108c104f100006465c70000b13e100028f6d30c0006a192000e00005bc9"
    And I want to send teltonika tcp data to api with socket "test-socket-id"
    Given Calculate speeding
    When I want to get speeding grouped by driver using query "?startDate=2019-09-21&endDate=2019-09-22"
    And I see field "page" filled with "1"
    And I see field "limit" filled with "10"
    And I see field "total" filled with "1"
    And I see field "data/0/driver" filled with "Nikki Burns"

  Scenario: I want to get speeding for given driver
    And I want fill "model" field with "Test model"
    And I want fill "type" field with "Car"
    And I want fill "vin" field with "1234567890Ab"
    And I want fill "fuelType" field with "1"
    And I want fill "year" field with "2000"
    And I want fill "ecoSpeed" field with "85"
    And I want fill "defaultLabel" field with "Default label"
    And I want fill "regNo" field with "1234567890Ab"
    And I want to set team ID by team ID of following user "nikki.burns@acme.local"
    And I want to create vehicle and save id
    And I want to set following user "nikki.burns@acme.local" as vehicle driver
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
    And I want install device for vehicle
    Given Elastica populate
    And I want fill "payload" field with "000F383838383838383838383838383838"
    And I want to send teltonika tcp data to api with socket "test-socket-id"
    And I want fill "payload" field with "00000000000004d3080e0000016d53aeddc00010bb4b5f202c9cda00d50038130087001307ef01f001c80071002102258a3029084239be1800874300004400000d071c2a07cf2b00003108c004f100006465c70000acfa100028f28f0c0006a061000000016d53aee5900010bb7111202cab9000d50038120088001307ef01f001c80071002101258d3029084239cb1800884300004400000d071c2a07d32b00003108c004f100006465c70000ad45100028f2da0c0006a081000000016d53aeed600010bbaa19202cc18100d50038120089001307ef01f001c80071002101258d3029084239c31800894300004400000d071c2a07d32b00003108c004f100006465c70000ad91100028f3260c0006a0a2000000016d53aef9180010bbcf99202cd01600d40038140089001307ef01f001c80071002100258f3029084239da1800894300004400000d071c2a07d72b00003108c104f100006465c70000ae03100028f3980c0006a0b2000000016d53af00e80010bbf4e7202cdeee00d40038140086001307ef01f001c800710021ff258d3029084239de1800864300004400000d071c2a07db2b00003108c104f100006465c70000ae4e100028f3e30c0006a0b2000000016d53af08b80010bc19f2202cedd700d40038140086001307ef01f001c800710021ff258d3029084239db1800864300004400000d071c2a07db2b00003108c104f100006465c70000ae9a100028f42f0c0006a0d2000000016d53af10880010bc403a202cfbd600d30038140088001307ef01f001c80071002100258e3029084239de1800884300004400000d071c2a07df2b00003108c104f100006465c70000aee4100028f4790c0006a0f2000000016d53af18580010bc79a6202d117400d1003913008a001307ef01f001c80071002100258e3029084239c91800894300004400000d071c2a07df2b00003108c104f100006465c70000af31100028f4c60c0006a113000000016d53af24100010bca0c7202d1f8400d0003a14008b001307ef01f001c8007100210025903029084239e518008b4300004400000d071c2a07e32b00003108c104f100006465c70000afa2100028f5370c0006a123000000016d53af2be00010bcc7a5202d2db500cf003a140088001307ef01f001c8007100210025903029084239ee1800884300004400000d071c2a07e32b00003108c104f100006465c70000aff0100028f5850c0006a146000000016d53af2fc80010bcee40202d3bd600ce003a140087001307ef01f001c800710021fc25903029084239ec1800884300004400000d071c2a07e72b00003108c104f100006465c70000b03b100028f5d00c0006a146000000016d53af3b800010bd2727202d4f5e00cb003b140084001307ef01f001c800710021fc25903029084239f91800854300004400000d071c2a07e72b00003108c104f100006465c70000b087100028f61c0c0006a166000000016d53af43500010bd4cb8202d5c6300c9003c140082001307ef01f001c80071002100258c3029084239b31800834300004400000d071c2a07eb2b00003108c104f100006465c70000b0f4100028f6890c0006a175000000016d53af4b200010bd726a202d691500c7003c140081001307ef01f001c80071002100258c3029084239af1800814300004400000d071c2a07eb2b00003108c104f100006465c70000b13e100028f6d30c0006a192000e00005bc9"
    And I want to send teltonika tcp data to api with socket "test-socket-id"
    Given Calculate speeding
    When I want to get speeding for driver "nikki.burns@acme.local" using query "?startDate=2019-09-21&endDate=2019-09-22"
    And I see field "page" filled with "1"
    And I see field "limit" filled with "10"
    And I see field "total" filled with "1"
    And I see field "data/0/avgSpeed" filled with "135"
    And I see field "data/0/maxSpeed" filled with "139"
    And I see field "data/0/startedAt" filled with "2019-09-21 11:57:12"
    And I see field "data/0/finishedAt" filled with "2019-09-21 11:57:40"
    And I see field "data/0/distance" filled with "1092"
    And I see field "data/0/model" filled with "Test model"
    And I see field "data/0/defaultLabel" filled with "Default label"
    And I see field "data/0/regNo" filled with "1234567890Ab"
    And I see field "data/0/groups" filled with "null"
    And I see field "data/0/depot" filled with "null"
    And I see field "data/0/postedLimit" filled with "85"

   Scenario: I want to get get visited geofences grouped by geofence
    And I want to fill teamId by team of current user
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
    Given I signed with email "acme-admin@linxio.local"
    And I want to fill teamId by team of current user
    And I want fill "name" field with "Test group"
    And I want to create vehicle group and save id
    And I want fill vehicle group id
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
    And I want clean filled data
    And Elastica populate
    Given Driver "nikki.burns@acme.local" starts driving in the current vehicle at "2019-12-18"
    Given Current vehicle has been to "Asia Geozone" from "2019-12-20 02:00:00" to "2019-12-20 05:30:00"
    Given I signed with email "acme-admin@linxio.local"
    And I want to get visited geofences grouped by geofence using query "?startDate=2019-12-15&endDate=2019-12-25"
    And I see field "page" filled with "1"
    And I see field "limit" filled with "10"
    And I see field "total" filled with "1"
    And I see field "data/0/geofence" filled with "Asia Geozone"

  Scenario: I want to get visits for given geofence
    And I want to fill teamId by team of current user
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
    Given I signed with email "acme-admin@linxio.local"
    And I want to fill teamId by team of current user
    And I want fill "name" field with "Test group"
    And I want to create vehicle group and save id
    And I want fill vehicle group id
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
    And I want clean filled data
    And Elastica populate
    Given Driver "nikki.burns@acme.local" starts driving in the current vehicle at "2019-12-18"
    Given Current vehicle has been to "Asia Geozone" from "2019-12-20 02:00:00" to "2019-12-20 05:30:00"
    Given I signed with email "acme-admin@linxio.local"
    When I want to get visits for given geofence "Asia Geozone" using query "?startDate=2019-12-20&endDate=2019-12-21"
    And I see field "page" filled with "1"
    And I see field "limit" filled with "10"
    And I see field "total" filled with "1"
    And I see field "data/0/model" filled with "Mercedes"
    And I see field "data/0/defaultLabel" filled with "Jenny"
    And I see field "data/0/regNo" filled with "B1234567890"
    And I see field "data/0/depot" filled with "null"
    And I see field "data/0/arrivedAt" filled with "2019-12-20 02:00:00"
    And I see field "data/0/departedAt" filled with "2019-12-20 05:30:00"
    And I see field "data/0/groups" filled with "Test group"
    And I see field "data/0/driver" filled with "Nikki Burns"
    And I see field "data/0/parkingTime" filled with "null"
    And I see field "data/0/idlingTime" filled with "null"

  Scenario: I want to get eco-speed for given vehicle
    And I want fill "model" field with "Test model"
    And I want fill "type" field with "Car"
    And I want fill "vin" field with "1234567890Ab"
    And I want fill "fuelType" field with "1"
    And I want fill "year" field with "2000"
    And I want fill "ecoSpeed" field with "85"
    And I want fill "defaultLabel" field with "Default label"
    And I want fill "regNo" field with "1234567890Ab"
    And I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want to create vehicle and save id
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
    And I want install device for vehicle
    Then I want change installed device date for saved vehicle to '2019-01-01T18:55:30+00:00'
    Given Elastica populate
    And I want fill "payload" field with "000F383838383838383838383838383838"
    And I want to send teltonika tcp data to api with socket "test-socket-id"
    And I want fill "payload" field with "00000000000004d3080e0000016d53aeddc00010bb4b5f202c9cda00d50038130087001307ef01f001c80071002102258a3029084239be1800874300004400000d071c2a07cf2b00003108c004f100006465c70000acfa100028f28f0c0006a061000000016d53aee5900010bb7111202cab9000d50038120088001307ef01f001c80071002101258d3029084239cb1800884300004400000d071c2a07d32b00003108c004f100006465c70000ad45100028f2da0c0006a081000000016d53aeed600010bbaa19202cc18100d50038120089001307ef01f001c80071002101258d3029084239c31800894300004400000d071c2a07d32b00003108c004f100006465c70000ad91100028f3260c0006a0a2000000016d53aef9180010bbcf99202cd01600d40038140089001307ef01f001c80071002100258f3029084239da1800894300004400000d071c2a07d72b00003108c104f100006465c70000ae03100028f3980c0006a0b2000000016d53af00e80010bbf4e7202cdeee00d40038140086001307ef01f001c800710021ff258d3029084239de1800864300004400000d071c2a07db2b00003108c104f100006465c70000ae4e100028f3e30c0006a0b2000000016d53af08b80010bc19f2202cedd700d40038140086001307ef01f001c800710021ff258d3029084239db1800864300004400000d071c2a07db2b00003108c104f100006465c70000ae9a100028f42f0c0006a0d2000000016d53af10880010bc403a202cfbd600d30038140088001307ef01f001c80071002100258e3029084239de1800884300004400000d071c2a07df2b00003108c104f100006465c70000aee4100028f4790c0006a0f2000000016d53af18580010bc79a6202d117400d1003913008a001307ef01f001c80071002100258e3029084239c91800894300004400000d071c2a07df2b00003108c104f100006465c70000af31100028f4c60c0006a113000000016d53af24100010bca0c7202d1f8400d0003a14008b001307ef01f001c8007100210025903029084239e518008b4300004400000d071c2a07e32b00003108c104f100006465c70000afa2100028f5370c0006a123000000016d53af2be00010bcc7a5202d2db500cf003a140088001307ef01f001c8007100210025903029084239ee1800884300004400000d071c2a07e32b00003108c104f100006465c70000aff0100028f5850c0006a146000000016d53af2fc80010bcee40202d3bd600ce003a140087001307ef01f001c800710021fc25903029084239ec1800884300004400000d071c2a07e72b00003108c104f100006465c70000b03b100028f5d00c0006a146000000016d53af3b800010bd2727202d4f5e00cb003b140084001307ef01f001c800710021fc25903029084239f91800854300004400000d071c2a07e72b00003108c104f100006465c70000b087100028f61c0c0006a166000000016d53af43500010bd4cb8202d5c6300c9003c140082001307ef01f001c80071002100258c3029084239b31800834300004400000d071c2a07eb2b00003108c104f100006465c70000b0f4100028f6890c0006a175000000016d53af4b200010bd726a202d691500c7003c140081001307ef01f001c80071002100258c3029084239af1800814300004400000d071c2a07eb2b00003108c104f100006465c70000b13e100028f6d30c0006a192000e00005bc9"
    And I want to send teltonika tcp data to api with socket "test-socket-id"
    Given Calculate speeding
    When I want fill "startDate" field with "2019-09-20T18:55:30+00:00"
    And I want fill "endDate" field with "2019-09-22T22:00:00+00:00"
    When I want get vehicle eco-speed
    And I see field "data/0/duration" filled with "string(28)"
    And I see field "data/0/avgSpeed" filled with "135"
    And I see field "data/0/totalDistance" filled with "1092"
    And I see field "score" filled with "99.08"
