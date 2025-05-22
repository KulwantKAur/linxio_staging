Feature: Idling Events

  Background:
    Given I signed in as "super_admin" team "admin"

  Scenario: I want check notification by vehicle excessing idling
    And the queue associated to notification.events events producer is empty
    And I want clean filled data
    Then I want fill "title" field with "Test# - vehicle excessing idling"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "listenerTeamId" field with 2
    And I want fill "eventId" field with "event(VEHICLE_EXCESSING_IDLING, user)"
    And I want fill "scope.subtype" field with "any"
    And I want fill "scope.value" field with null
    And I want fill "recipients.0.type" field with 'users_list'
    And I want fill "recipients.0.value.1" field with 'user(linxio-dev@ocsico.com)'
    And I want fill "transports.0" field with 'inApp'
    And I want fill "transports.1" field with 'sms'
    And I want fill "transports.2" field with 'email'
    And I want fill "eventTrackingTimeFrom" field with '00:00'
    And I want fill "eventTrackingTimeUntil" field with '23:59'
    And I want fill "eventTrackingDays.0" field with "monday"
    And I want fill "eventTrackingDays.1" field with "tuesday"
    And I want fill "eventTrackingDays.2" field with "wednesday"
    And I want fill "eventTrackingDays.3" field with "thursday"
    And I want fill "eventTrackingDays.4" field with "friday"
    And I want fill "eventTrackingDays.5" field with "saturday"
    And I want fill "eventTrackingDays.6" field with "sunday"
    And I want fill "additionalParams.timeDuration" field with 4
    And I want fill "teamId" field with 2
    Then I want create notification
    Then response code is 200
    Then response code is 200
    Then I want get last notification
    And I see field "listenerTeamId" filled with 2
    And I see field "ownerTeamId" filled with 1
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
    And  I want send messages in queue notification_events
    Then I want get vehicle idling for saved vehicle id
    And I see field "data/0/duration" filled with "5"
    And I see field "data/0/startDate"
    And I see field "data/0/endDate"
    And I see field "score" filled with "100"
    Then I want clean filled data
    Then I signed with email "linxio-dev@ocsico.com"
    Given Elastica populate index "eventLog"
    And I want fill "eventId" field with "event(VEHICLE_EXCESSING_IDLING, user)"
    Then I want get event log
    Then response code is 200
    And I see field "data/0/eventName" filled with "VEHICLE_EXCESSING_IDLING"
    And I see field "data/0/formattedDate"
    And I see field "data/0/importance" filled with "normal"
    And I see field "data/0/eventTeam" filled with "client-name-0"
    And I see field "data/0/triggeredBy" filled with "driver"
    And I see field "data/0/triggeredDetails" filled with "system"
    And I see field "data/0/eventSource/name" filled with "1234567890Ab"
    And I see field "data/0/eventSource/type" filled with "vehicle"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
#    And I see field "data/0/eventSource/vehicleId"
    And I see field "data/0/eventSource/idlingId"
    And I see field "data/0/shortDetails/address"
    And I see field "data/0/shortDetails/areas"
    And I see field "data/0/shortDetails/lastCoordinates"
    And I see field "data/0/shortDetails/deviceImei" filled with "string(888888888888888)"
    And I see field "data/0/shortDetails/vehicleRegNo" filled with "1234567890Ab"
    And I see field "data/0/shortDetails/limit/0/timeDuration" filled with "string(4)"
    And I see field "data/0/notificationsList/0/id"
    And I see field "data/0/notificationsList/0/title" filled with "Test# - vehicle excessing idling"
    And I see field "data/0/notificationsList/0/eventId"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I want clean filled data
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/recipient" filled with "string(1)"
    And I see field "data/0/subject" filled with "Alerts - Vehicle excessive idling"
    And I see field "data/0/message" filled with "Vehicle: 1234567890Ab is idling more than 5s. "
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "vehicle"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 1
    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"
    And I want clean filled data
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "sms"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "sms"
    And I see field "data/0/recipient" filled with "custom(+0452096181)"
    And I see field "data/0/subject" filled with "[Linxio] Notification Message"
    And I see field "data/0/message" filled with "Vehicle: 1234567890Ab is idling more than 5s. "
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "vehicle"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 1
    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"