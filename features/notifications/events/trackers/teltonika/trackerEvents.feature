Feature: Tracker Teltonika Events

  Scenario: I want check notification by unknown devices auth
    Given I signed in as "super_admin" team "admin"
    And the queue associated to notification.events events producer is empty
    Then I want fill "title" field with "Test# - unknown devices auth"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "eventId" field with "event(DEVICE_UNKNOWN_DETECTED, user)"
    And I want fill "scope.subtype" field with "any"
    And I want fill "scope.value" field with null
    And I want fill "recipients.0.type" field with 'users_list'
    And I want fill "recipients.0.value.0" field with 'user(linxio-dev@ocsico.com)'
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
    Then I want create notification
    Then response code is 200
    Then I want logout
    And response code is 204
    When I want fill "payload" field with "000F383632323539353838383334323935"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "error" filled with "Device with imei: 862259588834295 is not found"
    When I want check unknown devices auth
    Then I see field "0/imei" filled with "string(862259588834295)"
    Then I see field "0/vendor" filled with "Teltonika"
    Given I signed in as "super_admin" team "admin"
    Then I signed with email "linxio-dev@ocsico.com"
    Given Elastica populate index "eventLog"
    And I want fill "eventId" field with "event(DEVICE_UNKNOWN_DETECTED, user)"
    Then I want get event log
    Then response code is 200
    And I see field "data/0/eventName" filled with "DEVICE_UNKNOWN_DETECTED"
    And I see field "data/0/formattedDate"
    And I see field "data/0/importance" filled with "normal"
    And I see field "data/0/eventTeam" filled with "admin"
#    And I see field "data/0/eventTeam" filled with "client-name-0"
    And I see field "data/0/triggeredBy" filled with null
    And I see field "data/0/triggeredDetails" filled with "system"
#    And I see field "data/0/eventSourceType" filled with "device"
    And I see field "data/0/eventSource/name" filled with "862259588834295"
    And I see field "data/0/eventSource/type" filled with "device"
#    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
#    And I see field "data/0/eventSource/vehicleId"
#    And I see field "data/0/notificationsList/0/id"
#    And I see field "data/0/notificationsList/0/title" filled with "Test# - unknown devices auth"
#    And I see field "data/0/notificationsList/0/eventId"
#    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
#    And I want clean filled data
#    And I want fill "status" field with "pending"
#    And I want fill "transport" field with "web_app"
#    Then I want get generated notification messages
#    And response code is 200
#    And I see field "data/0/transportType" filled with "web_app"
#    And I see field "data/0/recipient" filled with "string(1)"
#    And I see field "data/0/subject" filled with "Alerts - Vehicle created"
#    And I see field "data/0/message" filled with "Vehicle created: testRegNo. "
#    And I see field "data/0/status" filled with "pending"
#    And I see field "data/0/event/eventSourceType" filled with "vehicle"
#    And I see field "data/0/event/entityTeam/type" filled with "client"
#    And I see field "data/0/event/entityTeam/clientId" filled with 1
#    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"
#    And I see field "data/0/event/eventId"
#    And I see field "data/0/event/eventLogId"
#    And I want clean filled data
#    And I want fill "status" field with "pending"
#    And I want fill "transport" field with "sms"
#    Then I want get generated notification messages
#    And response code is 200
#    And I see field "data/0/transportType" filled with "sms"
#    And I see field "data/0/recipient" filled with "custom(+0452096181)"
#    And I see field "data/0/subject" filled with "[Linxio] Notification Message"
#    And I see field "data/0/message" filled with "Vehicle created: testRegNo. "
#    And I see field "data/0/status" filled with "pending"
#    And I see field "data/0/event/eventSourceType" filled with "vehicle"
#    And I see field "data/0/event/entityTeam/type" filled with "client"
#    And I see field "data/0/event/entityTeam/clientId" filled with 1
#    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"
#    And I see field "data/0/event/eventId"
#    And I see field "data/0/event/eventLogId"

  Scenario: I want check notification by tracker
    Given I want handle check voltage event
    Given I signed in as "super_admin" team "admin"
    And the queue associated to notification.events events producer is empty
    Then I want fill "title" field with "Test# - voltage alerts"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "teamId" field with 2
    And I want fill "eventId" field with "event(TRACKER_VOLTAGE, user)"
    And I want fill "scope.subtype" field with "any"
    And I want fill "scope.value" field with null
    And I want fill "recipients.0.type" field with 'users_list'
    And I want fill "recipients.0.value.0" field with 'user(linxio-dev@ocsico.com)'
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
    And I want fill "additionalParams.deviceVoltage" field with 4
    Then I want create notification
    Then response code is 200
    Then I want get last notification
    And I see field "listenerTeamId" filled with 2
    And I see field "ownerTeamId" filled with 1
    And I see field "additionalParams/deviceVoltage" filled with "string(4)"
    And I want clean filled data
    Given I signed in as "admin" team "client" and teamId 2
    And I want fill "makeModel" field with "testModel"
    And I want fill "make" field with "testMake"
    Then I want fill "type" field with "Car"
    Then I want fill "vin" field with 4
    Then I want fill "regNo" field with "testingRegNo"
    Then I want fill "fuelType" field with 1
    Then I want fill "year" field with "2000"
    And I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    Then I want to create vehicle and save id
    And I see field "team/type" filled with "client"
    And I want clean filled data
    Given I signed in as "super_admin" team "admin"
    And I want fill device model for vehicle with name "FM3001"
    And I want fill "sn" field with "test sn"
    And I want fill "status" field with "inStock"
    And I want fill "port" field with 25
    And I want fill "hw" field with "test hw"
    And I want fill "sw" field with "test sw"
    And I want fill "imei" field with "888888888888888"
    And I want fill "phone" field with "+(375) 245535454"
    Then I want to create device for vehicle and save id
    And response code is 200
    Then I want install device for vehicle
    And response code is 200
    When I want fill "payload" field with "000F383838383838383838383838383838"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response"
    And I see field "imei"
    When I want fill "payload" field with "00000000000003d3080e0000016fa71c08f00056639a02e9791f1a002800460b0000ef0d06ef01f00150011505c800450106b5000bb60008420000180000430e64440000011002a922df000000016fa71ec0400056639abae9791ff30027003b0c0000f00d06ef01f00050011504c800450106b5000ab60007420000180000430e43440000011002a922df000000016fa71ec8100056639abae9791ff30027003b0d0000ef0d06ef00f00050001504c800450106b5000ab60007420000180000430e40440000011002a922df000000016fa71f9ee80056639abae9791ff30027003b0b0000f00d06ef00f00150001504c800450106b5000ab60007420000180000430e5e440000011002a922df000000016fa71fa6b80056639abae9791ff30027003b0c0000ef0d06ef01f00150011504c800450106b5000ab60007420000180000430e5d440000011002a922df000000016fa72023b8025663992ae9791c6f002700be090000fc0e07ef01f00150011505c8004501fc0006b5000bb600084234ad180000430e6e440007011002a922df000000016fa721d5500056639e91e9791c5e001b01070a0000f01008ef01f00050011505c80045011e00250007b5000bb600084237db180000430ee844007a31ffff011002a922df000000016fa721f4900056639e91e9791c5e001a0107090000f01008ef01f00150001505c80045011e00250007b5000cb600084237ee180000430eed44007a31ffff011002a922df000000016fa722f2780056639d44e9791c9100180005090000f01008ef01f00050011505c80045011e00250007b5000cb600084237ec180000430f0044007a31ffff011002a922df000000016fa7275f480056639d44e9791c91fffc0005070000f01008ef01f00150001504c80045011e00250007b5000fb6000b4235c6180000430f4744007931ffff011002a922df000000016fa72870b8005663a45ce9791f8f000201350a0000f01008ef01f00050011504c80045011e00250007b5000bb600074235bf180000430f5544007731ffff011002a922df000000016fa72a8bc8005663a45ce9791f8f000101350a0000f01008ef01f00150001505c80045011e00250007b5000cb600084233b5180000430f7644007331ffff011002a922df000000016fa72d7db000566398d6e979191d000c0085080000fd120aef01f00150011505c80045011e002500fd02fe1b07b5000db6000a42360d180000430fa044006c31ffff011002a922df000000016fa72d990800566398d6e979191d000c00850b0000fd120aef01f00150011505c80045011e002500fd02fe3107b5000bb600074235f0180000430fa044006c31ffff011002a922df000e00002aa0"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    And  I want send messages in queue notification_events
    Then I want sleep on 1 seconds
    Then I signed with email "linxio-dev@ocsico.com"
    Given Elastica populate index "eventLog"
    And I want fill "eventId" field with "event(TRACKER_VOLTAGE, user)"
    Then I want get event log
    Then response code is 200
    And I see field "data/0/eventName" filled with "TRACKER_VOLTAGE"
    And I see field "data/0/formattedDate"
    And I see field "data/0/importance" filled with "normal"
    And I see field "data/0/eventTeam" filled with "client-name-0"
    And I see field "data/0/triggeredBy" filled with "driver"
    And I see field "data/0/triggeredDetails" filled with "-"
#    And I see field "data/0/eventSourceType" filled with "device"
    And I see field "data/0/eventSource/name" filled with "testingRegNo"
    And I see field "data/0/eventSource/type" filled with "device"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I see field "data/0/eventSource/trackerhistoryId"
    And I see field "data/0/eventSource/deviceId"
    And I see field "data/0/shortDetails/address"
    And I see field "data/0/shortDetails/lastCoordinates"
    And I see field "data/0/shortDetails/deviceImei" filled with "string(888888888888888)"
    And I see field "data/0/shortDetails/vehicleRegNo" filled with "testingRegNo"
    And I see field "data/0/shortDetails/deviceVoltage" filled with null
    And I see field "data/0/shortDetails/limit/0/deviceVoltage" filled with "string(4)"
    And I see field "data/0/notificationsList/0/id"
    And I see field "data/0/notificationsList/0/title" filled with "Test# - voltage alerts"
    And I see field "data/0/notificationsList/0/eventId"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I want clean filled data
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/recipient" filled with "string(1)"
    And I see field "data/0/subject" filled with "Alerts - Voltage"
    And I see field "data/0/message" filled with "Supply voltage to tracker is 0V on the vehicle: testingRegNo. "
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "device"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 1
    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"
    And I see field "data/0/event/eventId"
    And I see field "data/0/event/eventLogId"
    And I want clean filled data
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "sms"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "sms"
    And I see field "data/0/recipient" filled with "custom(+0452096181)"
    And I see field "data/0/subject" filled with "[Linxio] Notification Message"
    And I see field "data/0/message" filled with "Supply voltage to tracker is 0V on the vehicle: testingRegNo. "
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "device"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 1
    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"
    And I see field "data/0/event/eventId"
    And I see field "data/0/event/eventLogId"
    And I want clean filled data
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "email"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "email"
    And I see field "data/0/recipient" filled with "linxio-dev@ocsico.com"
    And I see field "data/0/subject" filled with "Alerts - Voltage"
    Then I should get an email with field "data/0/message" containing:
    """
    <h3>New notification from the Linxio system:</h3>
    <p>Tracker Supply voltage is 0V in the vehicle testingRegNo.</p><br/>
    <p><b>Detailed information:</b></p>
    <p>2020-01-01 01:00 - Tracker supply voltage in the vehicle testingRegNo(testMake testModel) with driver -- is 0V, voltage limit is set to 4</p>
    <p>Vehicle page: https://url/client/fleet/47/specification</p><br/>
    <p></p>
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "device"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 1
    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"
    And I see field "data/0/event/eventId"
    And I see field "data/0/event/eventLogId"

  Scenario: I want check notification by vehicle towing
    Given I want handle check towing event
    Given I signed in as "super_admin" team "admin"
    And the queue associated to notification.events events producer is empty
    Then I want fill "title" field with "Test# - towing alerts"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "teamId" field with 2
    And I want fill "eventId" field with "event(VEHICLE_TOWING_EVENT, user)"
    And I want fill "scope.subtype" field with "any"
    And I want fill "scope.value" field with null
    And I want fill "recipients.0.type" field with 'users_list'
    And I want fill "recipients.0.value.0" field with 'user(linxio-dev@ocsico.com)'
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
    Then I want create notification
    Then response code is 200
    Then I want get last notification
    And I see field "listenerTeamId" filled with 2
    And I see field "ownerTeamId" filled with 1
    And I want clean filled data
    Given I signed in as "admin" team "client" and teamId 2
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
    Given I signed in as "super_admin" team "admin"
    And I want fill device model for vehicle with name "FM3001"
    And I want fill "sn" field with "test sn"
    And I want fill "status" field with "inStock"
    And I want fill "port" field with 25
    And I want fill "hw" field with "test hw"
    And I want fill "sw" field with "test sw"
    And I want fill "imei" field with "888888888888888"
    And I want fill "phone" field with "+(375) 245535454"
    Then I want to create device for vehicle and save id
    And response code is 200
    Then I want install device for vehicle
    And response code is 200
    When I want fill "payload" field with "000F383838383838383838383838383838"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    And response code is 200
    Then I see field "response"
    And I see field "imei"
    When I want fill "payload" field with "00000000000001070804000001714e57b8c000568b58e1e94154bf004d00cf110000f00d06ef00f00150001504c800450106b50004b6000342315d180000430f8e4400000110002b6d2f00000001714e57c47800568b58e1e94154bf004d00cf0f0000ef0d06ef01f00150011504c800450106b50006b600034230ab180000430f8e4400000110002b6d2f00000001714e58b2c000568b58e1e94154bf004d00cf110000f00d06ef01f00050011504c800450106b50004b6000342323e180000430f8d4400000110002b6d2f00000001714e58ba9000568b58e1e94154bf004d00cf110000ef0d06ef00f00050001504c800450106b50004b6000342325f180000430f8e4400000110002b6d2f000400001d6a"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    And response code is 200
    And  I want send messages in queue notification_events
    Then I signed with email "linxio-dev@ocsico.com"
    Given Elastica populate index "eventLog"
    And I want fill "eventId" field with "event(VEHICLE_TOWING_EVENT, user)"
    Then I want get event log
    Then response code is 200
    And I see field "data/0/eventName" filled with "VEHICLE_TOWING_EVENT"
    And I see field "data/0/formattedDate"
    And I see field "data/0/importance" filled with "normal"
    And I see field "data/0/eventTeam" filled with "client-name-0"
    And I see field "data/0/triggeredBy" filled with "driver"
    And I see field "data/0/triggeredDetails" filled with "-"
#    And I see field "data/0/eventSourceType" filled with "device"
    And I see field "data/0/eventSource/name" filled with "testingRegNo"
    And I see field "data/0/eventSource/type" filled with "device"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I see field "data/0/eventSource/trackerhistoryId"
    And I see field "data/0/eventSource/deviceId"
    And I see field "data/0/shortDetails/address"
    And I see field "data/0/shortDetails/lastCoordinates"
    And I see field "data/0/shortDetails/deviceImei" filled with "string(888888888888888)"
    And I see field "data/0/shortDetails/vehicleRegNo" filled with "testingRegNo"
    And I see field "data/0/notificationsList/0/id"
    And I see field "data/0/notificationsList/0/title" filled with "Test# - towing alerts"
    And I see field "data/0/notificationsList/0/eventId"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I want clean filled data
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/recipient" filled with "string(1)"
    And I see field "data/0/subject" filled with "Alerts - Vehicle towing event"
    And I see field "data/0/message" filled with "Vehicle has its engine off, but is actually moving: testingRegNo. "
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "device"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 1
    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"
    And I see field "data/0/event/eventId"
    And I see field "data/0/event/eventLogId"
    And I want clean filled data
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "sms"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "sms"
    And I see field "data/0/recipient" filled with "custom(+0452096181)"
    And I see field "data/0/subject" filled with "[Linxio] Notification Message"
    And I see field "data/0/message" filled with "Vehicle has its engine off, but is actually moving: testingRegNo. "
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "device"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 1
    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"
    And I see field "data/0/event/eventId"
    And I see field "data/0/event/eventLogId"

  Scenario: I want check notification about panic button from device
    Given I want handle check panic button event from 'device'
    Given I signed in as "super_admin" team "admin"
    And the queue associated to notification.events events producer is empty
    Then I want fill "title" field with "Test# - mobile panic button"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "teamId" field with 2
    And I want fill "eventId" field with "event(PANIC_BUTTON, user)"
    And I want fill "scope.subtype" field with "any"
    And I want fill "scope.value" field with null
    And I want fill "recipients.0.type" field with 'users_list'
    And I want fill "recipients.0.value.0" field with 'user(linxio-dev@ocsico.com)'
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
    Then I want create notification
    Then response code is 200
    Then I want get last notification
    And I see field "listenerTeamId" filled with 2
    And I see field "ownerTeamId" filled with 1
    And I want clean filled data
    Given I signed in as "admin" team "client" and teamId 2
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
    And I want clean filled data
    Given I signed in as "super_admin" team "admin"
    And I want fill device model for vehicle with name "FM3001"
    And I want fill "sn" field with "test sn"
    And I want fill "status" field with "inStock"
    And I want fill "port" field with 25
    And I want fill "hw" field with "test hw"
    And I want fill "sw" field with "test sw"
    And I want fill "imei" field with "888888888888888"
    And I want fill "phone" field with "+(375) 245535454"
    Then I want to create device for vehicle and save id
    And response code is 200
    Then I want install device for vehicle
    And response code is 200
    When I want fill "payload" field with "000F383838383838383838383838383838"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response"
    And I see field "imei"
    When I want fill "payload" field with "00000000000003d3080e0000016fa71c08f00056639a02e9791f1a002800460b0000ef0d06ef01f00150011505c800450106b5000bb60008420000180000430e64440000011002a922df000000016fa71ec0400056639abae9791ff30027003b0c0000f00d06ef01f00050011504c800450106b5000ab60007420000180000430e43440000011002a922df000000016fa71ec8100056639abae9791ff30027003b0d0000ef0d06ef00f00050001504c800450106b5000ab60007420000180000430e40440000011002a922df000000016fa71f9ee80056639abae9791ff30027003b0b0000f00d06ef00f00150001504c800450106b5000ab60007420000180000430e5e440000011002a922df000000016fa71fa6b80056639abae9791ff30027003b0c0000ef0d06ef01f00150011504c800450106b5000ab60007420000180000430e5d440000011002a922df000000016fa72023b8025663992ae9791c6f002700be090000fc0e07ef01f00150011505c8004501fc0006b5000bb600084234ad180000430e6e440007011002a922df000000016fa721d5500056639e91e9791c5e001b01070a0000f01008ef01f00050011505c80045011e00250007b5000bb600084237db180000430ee844007a31ffff011002a922df000000016fa721f4900056639e91e9791c5e001a0107090000f01008ef01f00150001505c80045011e00250007b5000cb600084237ee180000430eed44007a31ffff011002a922df000000016fa722f2780056639d44e9791c9100180005090000f01008ef01f00050011505c80045011e00250007b5000cb600084237ec180000430f0044007a31ffff011002a922df000000016fa7275f480056639d44e9791c91fffc0005070000f01008ef01f00150001504c80045011e00250007b5000fb6000b4235c6180000430f4744007931ffff011002a922df000000016fa72870b8005663a45ce9791f8f000201350a0000f01008ef01f00050011504c80045011e00250007b5000bb600074235bf180000430f5544007731ffff011002a922df000000016fa72a8bc8005663a45ce9791f8f000101350a0000f01008ef01f00150001505c80045011e00250007b5000cb600084233b5180000430f7644007331ffff011002a922df000000016fa72d7db000566398d6e979191d000c0085080000fd120aef01f00150011505c80045011e002500fd02fe1b07b5000db6000a42360d180000430fa044006c31ffff011002a922df000000016fa72d990800566398d6e979191d000c00850b0000fd120aef01f00150011505c80045011e002500fd02fe3107b5000bb600074235f0180000430fa044006c31ffff011002a922df000e00002aa0"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    And  I want send messages in queue notification_events
    Then I signed with email "linxio-dev@ocsico.com"
    Given Elastica populate index "eventLog"
    And I want fill "eventId" field with "event(PANIC_BUTTON, user)"
    Then I want get event log
    Then response code is 200
    And I see field "data/0/eventName" filled with "PANIC_BUTTON"
    And I see field "data/0/formattedDate"
    And I see field "data/0/importance" filled with "normal"
    And I see field "data/0/eventTeam" filled with "client-name-0"
    And I see field "data/0/triggeredBy" filled with "driver"
    And I see field "data/0/triggeredDetails" filled with "-"
#    And I see field "data/0/eventSourceType" filled with "device"
    And I see field "data/0/eventSource/name" filled with "testingRegNo"
    And I see field "data/0/eventSource/type" filled with "device"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I see field "data/0/eventSource/trackerhistoryId"
    And I see field "data/0/eventSource/deviceId"
    And I see field "data/0/shortDetails/address"
    And I see field "data/0/shortDetails/lastCoordinates"
    And I see field "data/0/shortDetails/deviceImei" filled with "string(888888888888888)"
    And I see field "data/0/shortDetails/vehicleRegNo" filled with "testingRegNo"
    And I see field "data/0/notificationsList/0/id"
    And I see field "data/0/notificationsList/0/title" filled with "Test# - mobile panic button"
    And I see field "data/0/notificationsList/0/eventId"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I want clean filled data
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/recipient" filled with "string(1)"
    And I see field "data/0/subject" filled with "Alerts - SOS button - 888888888888888"
    And I see field "data/0/message" filled with "SOS button pressed in device: 888888888888888 by vehicle: testingRegNo. "
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "device"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 1
    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"
    And I see field "data/0/event/eventId"
    And I see field "data/0/event/eventLogId"
    And I want clean filled data
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "sms"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "sms"
    And I see field "data/0/recipient" filled with "custom(+0452096181)"
    And I see field "data/0/subject" filled with "[Linxio] Notification Message"
    And I see field "data/0/message" filled with "SOS button pressed in 888888888888888, vehicle: testingRegNo. "
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "device"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 1
    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"
    And I see field "data/0/event/eventId"
    And I see field "data/0/event/eventLogId"

  Scenario: I want check notification about panic button from mobile
    Given I want handle check panic button event from 'mobile'
    Given I signed in as "super_admin" team "admin"
    And the queue associated to notification.events events producer is empty
    Then I want fill "title" field with "Test# - mobile panic button"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "teamId" field with 2
    And I want fill "eventId" field with "event(PANIC_BUTTON, user)"
    And I want fill "scope.subtype" field with "any"
    And I want fill "scope.value" field with null
    And I want fill "recipients.0.type" field with 'users_list'
    And I want fill "recipients.0.value.0" field with 'user(linxio-dev@ocsico.com)'
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
    Then I want create notification
    Then response code is 200
    Then I want get last notification
    And I see field "listenerTeamId" filled with 2
    And I see field "ownerTeamId" filled with 1
    And I want clean filled data
    Given I signed in as "admin" team "client" and teamId 2
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
    And I want clean filled data
    Given I signed in as "super_admin" team "admin"
    And I want fill device model for vehicle with name "FM3001"
    And I want fill "sn" field with "test sn"
    And I want fill "status" field with "inStock"
    And I want fill "port" field with 25
    And I want fill "hw" field with "test hw"
    And I want fill "sw" field with "test sw"
    And I want fill "imei" field with "888888888888888"
    And I want fill "phone" field with "+(375) 245535454"
    Then I want to create device for vehicle and save id
    And response code is 200
    Then I want install device for vehicle
    And response code is 200
    When I want fill "payload" field with "000F383838383838383838383838383838"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    Then I see field "response"
    And I see field "imei"
    When I want fill "payload" field with "00000000000003d3080e0000016fa71c08f00056639a02e9791f1a002800460b0000ef0d06ef01f00150011505c800450106b5000bb60008420000180000430e64440000011002a922df000000016fa71ec0400056639abae9791ff30027003b0c0000f00d06ef01f00050011504c800450106b5000ab60007420000180000430e43440000011002a922df000000016fa71ec8100056639abae9791ff30027003b0d0000ef0d06ef00f00050001504c800450106b5000ab60007420000180000430e40440000011002a922df000000016fa71f9ee80056639abae9791ff30027003b0b0000f00d06ef00f00150001504c800450106b5000ab60007420000180000430e5e440000011002a922df000000016fa71fa6b80056639abae9791ff30027003b0c0000ef0d06ef01f00150011504c800450106b5000ab60007420000180000430e5d440000011002a922df000000016fa72023b8025663992ae9791c6f002700be090000fc0e07ef01f00150011505c8004501fc0006b5000bb600084234ad180000430e6e440007011002a922df000000016fa721d5500056639e91e9791c5e001b01070a0000f01008ef01f00050011505c80045011e00250007b5000bb600084237db180000430ee844007a31ffff011002a922df000000016fa721f4900056639e91e9791c5e001a0107090000f01008ef01f00150001505c80045011e00250007b5000cb600084237ee180000430eed44007a31ffff011002a922df000000016fa722f2780056639d44e9791c9100180005090000f01008ef01f00050011505c80045011e00250007b5000cb600084237ec180000430f0044007a31ffff011002a922df000000016fa7275f480056639d44e9791c91fffc0005070000f01008ef01f00150001504c80045011e00250007b5000fb6000b4235c6180000430f4744007931ffff011002a922df000000016fa72870b8005663a45ce9791f8f000201350a0000f01008ef01f00050011504c80045011e00250007b5000bb600074235bf180000430f5544007731ffff011002a922df000000016fa72a8bc8005663a45ce9791f8f000101350a0000f01008ef01f00150001505c80045011e00250007b5000cb600084233b5180000430f7644007331ffff011002a922df000000016fa72d7db000566398d6e979191d000c0085080000fd120aef01f00150011505c80045011e002500fd02fe1b07b5000db6000a42360d180000430fa044006c31ffff011002a922df000000016fa72d990800566398d6e979191d000c00850b0000fd120aef01f00150011505c80045011e002500fd02fe3107b5000bb600074235f0180000430fa044006c31ffff011002a922df000e00002aa0"
    Then I want to send teltonika tcp data to api with socket "test-socket-id"
    And  I want send messages in queue notification_events
    Then I signed with email "linxio-dev@ocsico.com"
    Given Elastica populate index "eventLog"
    And I want fill "eventId" field with "event(PANIC_BUTTON, user)"
    Then I want get event log
    Then response code is 200
    And I see field "data/0/eventName" filled with "PANIC_BUTTON"
    And I see field "data/0/formattedDate"
    And I see field "data/0/importance" filled with "normal"
    And I see field "data/0/eventTeam" filled with "client-name-0"
    And I see field "data/0/triggeredBy" filled with "driver"
    And I see field "data/0/triggeredDetails" filled with "-"
#    And I see field "data/0/eventSourceType" filled with "device"
    And I see field "data/0/eventSource/name" filled with "testingRegNo"
    And I see field "data/0/eventSource/type" filled with "device"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I see field "data/0/eventSource/trackerhistoryId"
    And I see field "data/0/eventSource/deviceId"
    And I see field "data/0/shortDetails/address"
    And I see field "data/0/shortDetails/lastCoordinates"
    And I see field "data/0/shortDetails/deviceImei" filled with "string(888888888888888)"
    And I see field "data/0/shortDetails/vehicleRegNo" filled with "testingRegNo"
    And I see field "data/0/notificationsList/0/id"
    And I see field "data/0/notificationsList/0/title" filled with "Test# - mobile panic button"
    And I see field "data/0/notificationsList/0/eventId"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I want clean filled data
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/recipient" filled with "string(1)"
    And I see field "data/0/subject" filled with "Alerts - SOS button - 888888888888888"
    And I see field "data/0/message" filled with "SOS button pressed in device: 888888888888888 by vehicle: testingRegNo. "
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "device"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 1
    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"
    And I see field "data/0/event/eventId"
    And I see field "data/0/event/eventLogId"
    And I want clean filled data
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "sms"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "sms"
    And I see field "data/0/recipient" filled with "custom(+0452096181)"
    And I see field "data/0/subject" filled with "[Linxio] Notification Message"
    And I see field "data/0/message" filled with "SOS button pressed in 888888888888888, vehicle: testingRegNo. "
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "device"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 1
    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"
    And I see field "data/0/event/eventId"
    And I see field "data/0/event/eventLogId"