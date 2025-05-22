Feature: Tracker Ulbotech Events

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
    Then I want to create device for vehicle and save id
    And response code is 200
    Given There are following tracker payload from ulbotech tracker with socket "ulbotech-test-socket":
      | payload                                                                                                                                                                                                                                                              |
      | 2a545330312c38363131303730333431313336363423                                                                                                                                                                                                                         |
      | 2a545330312c3836313130373033343131333636342c3037353335363134303432302c4750533a333b5333332e3838303432353b453135312e3136323036373b303b303b302e39362c5354543a433230323b302c4d47523a313437333232352c4144433a303b31322e37303b313b33342e37363b323b342e32302c4556543a3123   |
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
    And I see field "data/0/shortDetails/deviceImei" filled with "string(861107034113664)"
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
    And I see field "data/0/subject" filled with "Alerts - SOS button - 861107034113664"
    And I see field "data/0/message" filled with "SOS button pressed in device: 861107034113664 by vehicle: testingRegNo. "
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
    And I see field "data/0/message" filled with "SOS button pressed in 861107034113664, vehicle: testingRegNo. "
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
    And I want get client by name "client-name-0" and save id
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
    Then I want set vehicle driver with current user
#    And I want set vehicle driver with user of current team with date "2019-05-29 08:19:38"
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
      | 2a545330312c3836353332383032363236363138382c3139343930373034303331352c5354543a3234323b3430302c4d47523a343035363633372c4144433a303b31352e31363b313b32382e37373b323b332e35352c4746533a303b302c4f42443a333130353346343130433132414533313044303033313246393934313331333332382c46554c3a343036392c4547543a3937303435362c4556543a46303b32303023   |
    Given I signed in as "admin" team "client" and teamId 2
    And I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    Then I want click mobile panic button
    And  I want send messages in queue notification_events
    Then response code is 204
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
    And I see field "data/0/shortDetails/deviceImei" filled with "string(861107034113664)"
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
    And I see field "data/0/subject" filled with "Alerts - SOS button - 861107034113664"
    And I see field "data/0/message" filled with "SOS button pressed in device: 861107034113664 by vehicle: testingRegNo. "
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
    And I see field "data/0/message" filled with "SOS button pressed in 861107034113664, vehicle: testingRegNo. "
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "device"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 1
    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"
    And I see field "data/0/event/eventId"
    And I see field "data/0/event/eventLogId"
