Feature: Tracker Sensor Topflytech

  Background:
    Given I signed in as "super_admin" team "admin"
    And the queue associated to notification.events events producer is empty

  Scenario: I want to reassign driver during sensor BLE id receiving
    And I want clean filled data
    Then I want fill "title" field with "Test# - vehicle reassign driver"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "teamId" field with 11
    And I want fill "eventId" field with "event(VEHICLE_REASSIGNED, user)"
    And I want fill "scope.subtype" field with "any"
    And I want fill "scope.value.0" field with null
    And I want fill "recipients.0.type" field with 'users_list'
    And I want fill "recipients.0.value." field with 'user(linxio-dev@ocsico.com)'
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
    And I see field "listenerTeamId" filled with 11
    And I see field "ownerTeamId" filled with 1
    And I want clean filled data
    And I want get client by name "ACME1" and save id
    And I want fill teamId by saved clientId
    And I want fill "type" field with "Car"
    And I want fill "model" field with "test model"
    And I want fill "regNo" field with "regNoForSensorId"
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
    And I want clean filled data
    And I want fill device model for vehicle with name "TLP1-SF"
    And I want fill "sn" field with "test sn"
    And I want fill "status" field with "inStock"
    And I want fill "port" field with 25
    And I want fill "hw" field with "test hw"
    And I want fill "sw" field with "test sw"
    And I want fill "imei" field with "865284040731352"
    And I want fill "phone" field with "+(375) 245535454"
    And I want fill "imsi" field with "test imsi"
    And I want fill "devEui" field with "test devEui"
    And I want fill "userName" field with "test username"
    And I want fill "password" field with "test password"
    And I want fill "clientNote" field with "test clientNote"
    And I want fill "adminNote" field with "test adminNote"
    Then I want to create device for vehicle and save id
    Then I want install device for vehicle
    And I want fill "name" field with "client driver name"
    And I want fill "surname" field with "client driver surname"
    And I want fill "phone" field with "+46 (87)4646753"
    And I want fill "email" field with "driver@example.com"
    Then I want to register driver for current client
    And I see field "email" filled with "driver@example.com"
    And I see field "team/id" filled with 11
    When I want fill "driverSensorId" field with "FFFF9602E6C1"
    Then I want to update current client's user by email "driver@example.com"
    Given There are following tracker payload from topflytech tracker with socket "test-topflytech-test-socket":
      | payload                                                                                                                                                                                                                                                              |
      | 262603000f02ea0865284040731352                                                                                                                                                                                                                         |
      | 262610003100010865284040731352210201042835010003ffff9602e6c129000081f9000107f7c40c24c2002924c20029                                                                                                                                                     |

    And  I want send messages in queue notification_events
    Then I want sleep on 1 seconds
    Then I signed with email "linxio-dev@ocsico.com"
    Given Elastica populate index "eventLog"
    And I want fill "eventId" field with "event(VEHICLE_REASSIGNED, user)"
    Then I want get event log
    Then response code is 200
    And I see field "data/0/eventName" filled with "VEHICLE_REASSIGNED"
    And I see field "data/0/formattedDate"
    And I see field "data/0/importance" filled with "normal"
    And I see field "data/0/eventTeam" filled with "ACME1"
    And I see field "data/0/teamId" filled with 11
    And I see field "data/0/triggeredBy" filled with null
    And I see field "data/0/triggeredDetails" filled with "driver@example.com"
    And I see field "data/0/eventSource/name" filled with "regNoForSensorId"
    And I see field "data/0/eventSource/type" filled with "vehicle"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I see field "data/0/eventSource/vehicleId"
    And I see field "data/0/eventSource/deviceId"
    And I see field "data/0/shortDetails/oldValue" filled with null
    And I see field "data/0/shortDetails/newValue" filled with "client driver name client driver surname"
    And I see field "data/0/notificationsList/0/id"
    And I see field "data/0/notificationsList/0/title" filled with "Test# - vehicle reassign driver"
    And I see field "data/0/notificationsList/0/eventId"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I want clean filled data
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/recipient" filled with "string(1)"
    And I see field "data/0/subject" filled with "Alerts - Vehicle reassigned"
    And I see field "data/0/message" filled with "Vehicle regNoForSensorId has been re-assigned to driver client driver name client driver surname. "
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "vehicle"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 10
    And I see field "data/0/event/entityTeam/clientName" filled with "ACME1"
    And I see field "data/0/event/eventId"
    And I see field "data/0/event/eventLogId"
    And I want clean filled data
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "sms"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "sms"
    And I see field "data/0/recipient" filled with "custom(+0452096181)"
#    And I see field "data/0/subject" filled with "[Linxio] Notification Message"
    And I see field "data/0/subject" filled with "Alerts - Vehicle reassigned"
    And I see field "data/0/message" filled with "Vehicle regNoForSensorId has been re-assigned to driver client driver name client driver surname. "
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "vehicle"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 10
    And I see field "data/0/event/entityTeam/clientName" filled with "ACME1"
    And I see field "data/0/event/eventId"
    And I see field "data/0/event/eventLogId"

  Scenario: I want to test sensor temperature event
    And I want clean filled data
    Then I want fill "title" field with "Test# - sensor temperature"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "teamId" field with 11
    And I want fill "eventId" field with "event(SENSOR_TEMPERATURE, user)"
    And I want fill "scope.subtype" field with "any"
    And I want fill "scope.value.0" field with null
    And I want fill "recipients.0.type" field with 'users_list'
    And I want fill "recipients.0.value." field with 'user(linxio-dev@ocsico.com)'
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
    And I want fill "additionalParams.isSensorTemperature" field with true
    And I want fill "additionalParams.type" field with greater
    And I want fill "additionalParams.temperature" field with 10
    Then I want create notification
    Then response code is 200
    Then I want get last notification
    And I see field "listenerTeamId" filled with 11
    And I see field "ownerTeamId" filled with 1
    And I want clean filled data
    And I want get client by name "ACME1" and save id
    And I want fill teamId by saved clientId
    And I want fill "type" field with "Car"
    And I want fill "model" field with "test model"
    And I want fill "regNo" field with "regNoForSensorId"
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
    And I want clean filled data
    And I want fill device model for vehicle with name "TLP1-SF"
    And I want fill "sn" field with "test sn"
    And I want fill "status" field with "inStock"
    And I want fill "port" field with 25
    And I want fill "hw" field with "test hw"
    And I want fill "sw" field with "test sw"
    And I want fill "imei" field with "865284040731352"
    And I want fill "phone" field with "+(375) 245535454"
    And I want fill "imsi" field with "test imsi"
    And I want fill "devEui" field with "test devEui"
    And I want fill "userName" field with "test username"
    And I want fill "password" field with "test password"
    And I want fill "clientNote" field with "test clientNote"
    And I want fill "adminNote" field with "test adminNote"
    Then I want to create device for vehicle and save id
    Then I want install device for vehicle
    And I want fill "name" field with "client driver name"
    And I want fill "surname" field with "client driver surname"
    And I want fill "phone" field with "+46 (87)4646753"
    And I want fill "email" field with "driver@example.com"
    Then I want to register driver for current client
    And I see field "email" filled with "driver@example.com"
    And I see field "team/id" filled with 11
    When I want fill "driverSensorId" field with "FFFF9602E6C1"
    Then I want to update current client's user by email "driver@example.com"
    Given There are following tracker payload from topflytech tracker with socket "test-topflytech-test-socket":
      | payload                                                                                                                                                                                                                                                              |
      | 262603000f02ea0865284040731352                                                                                                                                                                                                                         |
      | 262610003600130865284040731352201217050123010004d497ae41f7dd00640d080ce4000148e3fa78d738b600640ccd17d400004a   |
      | 262610003600130865284040731352201217050124010004d497ae41f7dd00640d080ce4000148e3fa78d738b600640ccd17d400004a   |
    Given I want handle sensor event
    And  I want send messages in queue notification_events
    Then I want sleep on 1 seconds
    Then I signed with email "linxio-dev@ocsico.com"
    Given Elastica populate index "eventLog"
    And I want fill "eventId" field with "event(SENSOR_TEMPERATURE, user)"
    Then I want get event log
    Then response code is 200
    And I see field "data/0/eventName" filled with "SENSOR_TEMPERATURE"
    And I see field "data/0/formattedDate"
    And I see field "data/0/importance" filled with "normal"
    And I see field "data/0/eventTeam" filled with "ACME1"
    And I see field "data/0/teamId" filled with 11
    And I see field "data/0/triggeredBy" filled with driver
    And I see field "data/0/eventSource/name" filled with "regNoForSensorId"
    And I see field "data/0/eventSource/type" filled with "trackerhistorysensor"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I see field "data/0/notificationsList/0/id"
    And I see field "data/0/notificationsList/0/title" filled with "Test# - sensor temperature"
    And I see field "data/0/notificationsList/0/eventId"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I see field "data/0/notificationGenerated" filled with "yes"
    And I want clean filled data
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/recipient" filled with "string(1)"
    And I see field "data/0/subject" filled with "Alerts - Sensor Temperature"
#    And I see field "data/0/message" filled with "Sensor d497ae41f7dd detected temp value 10."
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "trackerhistorysensor"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 10
    And I see field "data/0/event/entityTeam/clientName" filled with "ACME1"
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
#    And I see field "data/0/message" filled with "Vehicle regNoForSensorId has been re-assigned to driver client driver name client driver surname. "
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "trackerhistorysensor"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 10
    And I see field "data/0/event/entityTeam/clientName" filled with "ACME1"
    And I see field "data/0/event/eventId"
    And I see field "data/0/event/eventLogId"

  Scenario: I want to test sensor humidity event
    And I want clean filled data
    Then I want fill "title" field with "Test# - sensor humidity"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "teamId" field with 11
    And I want fill "eventId" field with "event(SENSOR_HUMIDITY, user)"
    And I want fill "scope.subtype" field with "any"
    And I want fill "scope.value.0" field with null
    And I want fill "recipients.0.type" field with 'users_list'
    And I want fill "recipients.0.value." field with 'user(linxio-dev@ocsico.com)'
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
    And I want fill "additionalParams.isSensorHumidity" field with true
    And I want fill "additionalParams.type" field with greater
    And I want fill "additionalParams.humidity" field with 10
    Then I want create notification
    Then response code is 200
    Then I want get last notification
    And I see field "listenerTeamId" filled with 11
    And I see field "ownerTeamId" filled with 1
    And I want clean filled data
    And I want get client by name "ACME1" and save id
    And I want fill teamId by saved clientId
    And I want fill "type" field with "Car"
    And I want fill "model" field with "test model"
    And I want fill "regNo" field with "regNoForSensorId"
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
    And I want clean filled data
    And I want fill device model for vehicle with name "TLP1-SF"
    And I want fill "sn" field with "test sn"
    And I want fill "status" field with "inStock"
    And I want fill "port" field with 25
    And I want fill "hw" field with "test hw"
    And I want fill "sw" field with "test sw"
    And I want fill "imei" field with "865284040731352"
    And I want fill "phone" field with "+(375) 245535454"
    And I want fill "imsi" field with "test imsi"
    And I want fill "devEui" field with "test devEui"
    And I want fill "userName" field with "test username"
    And I want fill "password" field with "test password"
    And I want fill "clientNote" field with "test clientNote"
    And I want fill "adminNote" field with "test adminNote"
    Then I want to create device for vehicle and save id
    Then I want install device for vehicle
    And I want fill "name" field with "client driver name"
    And I want fill "surname" field with "client driver surname"
    And I want fill "phone" field with "+46 (87)4646753"
    And I want fill "email" field with "driver@example.com"
    Then I want to register driver for current client
    And I see field "email" filled with "driver@example.com"
    And I see field "team/id" filled with 11
    When I want fill "driverSensorId" field with "FFFF9602E6C1"
    Then I want to update current client's user by email "driver@example.com"
    Given There are following tracker payload from topflytech tracker with socket "test-topflytech-test-socket":
      | payload                                                                                                                                                                                                                                                              |
      | 262603000f02ea0865284040731352                                                                                                                                                                                                                         |
      | 262610003600130865284040731352201217050123010004d497ae41f7dd00640d080ce4000148e3fa78d738b600640ccd17d400004a   |
      | 262610003600130865284040731352201217050124010004d497ae41f7dd00640d080ce4000148e3fa78d738b600640ccd17d400004a   |
    Given I want handle sensor event
    And  I want send messages in queue notification_events
    Then I want sleep on 1 seconds
    Then I signed with email "linxio-dev@ocsico.com"
    Given Elastica populate index "eventLog"
    And I want fill "eventId" field with "event(SENSOR_HUMIDITY, user)"
    Then I want get event log
    Then response code is 200
    And I see field "data/0/eventName" filled with "SENSOR_HUMIDITY"
    And I see field "data/0/formattedDate"
    And I see field "data/0/importance" filled with "normal"
    And I see field "data/0/eventTeam" filled with "ACME1"
    And I see field "data/0/teamId" filled with 11
    And I see field "data/0/triggeredBy" filled with driver
    And I see field "data/0/eventSource/name" filled with "regNoForSensorId"
    And I see field "data/0/eventSource/type" filled with "trackerhistorysensor"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I see field "data/0/notificationsList/0/id"
    And I see field "data/0/notificationsList/0/title" filled with "Test# - sensor humidity"
    And I see field "data/0/notificationsList/0/eventId"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I see field "data/0/notificationGenerated" filled with "yes"
    And I want clean filled data
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/recipient" filled with "string(1)"
    And I see field "data/0/subject" filled with "Alerts - Sensor Humidity"
#    And I see field "data/0/message" filled with "Sensor d497ae41f7dd detected hum value 10."
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "trackerhistorysensor"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 10
    And I see field "data/0/event/entityTeam/clientName" filled with "ACME1"
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
#    And I see field "data/0/message" filled with "Vehicle regNoForSensorId has been re-assigned to driver client driver name client driver surname. "
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "trackerhistorysensor"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 10
    And I see field "data/0/event/entityTeam/clientName" filled with "ACME1"
    And I see field "data/0/event/eventId"
    And I see field "data/0/event/eventLogId"

  Scenario: I want to test sensor light event
    And I want clean filled data
    Then I want fill "title" field with "Test# - sensor light"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "teamId" field with 11
    And I want fill "eventId" field with "event(SENSOR_LIGHT, user)"
    And I want fill "scope.subtype" field with "any"
    And I want fill "scope.value.0" field with null
    And I want fill "recipients.0.type" field with 'users_list'
    And I want fill "recipients.0.value." field with 'user(linxio-dev@ocsico.com)'
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
    And I want fill "additionalParams.isSensorLight" field with true
    And I want fill "additionalParams.light" field with 0
    Then I want create notification
    Then response code is 200
    Then I want get last notification
    And I see field "listenerTeamId" filled with 11
    And I see field "ownerTeamId" filled with 1
    And I want clean filled data
    And I want get client by name "ACME1" and save id
    And I want fill teamId by saved clientId
    And I want fill "type" field with "Car"
    And I want fill "model" field with "test model"
    And I want fill "regNo" field with "regNoForSensorId"
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
    And I want clean filled data
    And I want fill device model for vehicle with name "TLP1-SF"
    And I want fill "sn" field with "test sn"
    And I want fill "status" field with "inStock"
    And I want fill "port" field with 25
    And I want fill "hw" field with "test hw"
    And I want fill "sw" field with "test sw"
    And I want fill "imei" field with "865284040731352"
    And I want fill "phone" field with "+(375) 245535454"
    And I want fill "imsi" field with "test imsi"
    And I want fill "devEui" field with "test devEui"
    And I want fill "userName" field with "test username"
    And I want fill "password" field with "test password"
    And I want fill "clientNote" field with "test clientNote"
    And I want fill "adminNote" field with "test adminNote"
    Then I want to create device for vehicle and save id
    Then I want install device for vehicle
    And I want fill "name" field with "client driver name"
    And I want fill "surname" field with "client driver surname"
    And I want fill "phone" field with "+46 (87)4646753"
    And I want fill "email" field with "driver@example.com"
    Then I want to register driver for current client
    And I see field "email" filled with "driver@example.com"
    And I see field "team/id" filled with 11
    When I want fill "driverSensorId" field with "FFFF9602E6C1"
    Then I want to update current client's user by email "driver@example.com"
    Given There are following tracker payload from topflytech tracker with socket "test-topflytech-test-socket":
      | payload                                                                                                                                                                                                                                                              |
      | 262603000f02ea0865284040731352                                                                                                                                                                                                                         |
      | 262610003600130865284040731352201217050123010004d497ae41f7dd00640d080ce4000148e3fa78d738b600640ccd17d400004a   |
      | 262610003600130865284040731352201217050124010004d497ae41f7dd00640d080ce4000148e3fa78d738b600640ccd17d400004a   |
    Given I want handle sensor event
    And  I want send messages in queue notification_events
    Then I want sleep on 1 seconds
    Then I signed with email "linxio-dev@ocsico.com"
    Given Elastica populate index "eventLog"
    And I want fill "eventId" field with "event(SENSOR_LIGHT, user)"
    Then I want get event log
    Then response code is 200
    And I see field "data/0/eventName" filled with "SENSOR_LIGHT"
    And I see field "data/0/formattedDate"
    And I see field "data/0/importance" filled with "normal"
    And I see field "data/0/eventTeam" filled with "ACME1"
    And I see field "data/0/teamId" filled with 11
    And I see field "data/0/triggeredBy" filled with driver
    And I see field "data/0/eventSource/name" filled with "regNoForSensorId"
    And I see field "data/0/eventSource/type" filled with "trackerhistorysensor"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I see field "data/0/notificationsList/0/id"
    And I see field "data/0/notificationsList/0/title" filled with "Test# - sensor light"
    And I see field "data/0/notificationsList/0/eventId"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I see field "data/0/notificationGenerated" filled with "yes"
    And I want clean filled data
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/recipient" filled with "string(1)"
    And I see field "data/0/subject" filled with "Alerts - Sensor light"
#    And I see field "data/0/message" filled with "Sensor e3fa78d738b6 detected light status changed to off."
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "trackerhistorysensor"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 10
    And I see field "data/0/event/entityTeam/clientName" filled with "ACME1"
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
#    And I see field "data/0/message" filled with "Vehicle regNoForSensorId has been re-assigned to driver client driver name client driver surname. "
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "trackerhistorysensor"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 10
    And I see field "data/0/event/entityTeam/clientName" filled with "ACME1"
    And I see field "data/0/event/eventId"
    And I see field "data/0/event/eventLogId"

  Scenario: I want to test sensor battery level event
    And I want clean filled data
    Then I want fill "title" field with "Test# - sensor battery level"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "teamId" field with 11
    And I want fill "eventId" field with "event(SENSOR_BATTERY_LEVEL, user)"
    And I want fill "scope.subtype" field with "any"
    And I want fill "scope.value.0" field with null
    And I want fill "recipients.0.type" field with 'users_list'
    And I want fill "recipients.0.value." field with 'user(linxio-dev@ocsico.com)'
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
    And I want fill "additionalParams.isBatteryLevel" field with true
    And I want fill "additionalParams.batteryLevel" field with 120
    Then I want create notification
    Then response code is 200
    Then I want get last notification
    And I see field "listenerTeamId" filled with 11
    And I see field "ownerTeamId" filled with 1
    And I want clean filled data
    And I want get client by name "ACME1" and save id
    And I want fill teamId by saved clientId
    And I want fill "type" field with "Car"
    And I want fill "model" field with "test model"
    And I want fill "regNo" field with "regNoForSensorId"
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
    And I want clean filled data
    And I want fill device model for vehicle with name "TLP1-SF"
    And I want fill "sn" field with "test sn"
    And I want fill "status" field with "inStock"
    And I want fill "port" field with 25
    And I want fill "hw" field with "test hw"
    And I want fill "sw" field with "test sw"
    And I want fill "imei" field with "865284040731352"
    And I want fill "phone" field with "+(375) 245535454"
    And I want fill "imsi" field with "test imsi"
    And I want fill "devEui" field with "test devEui"
    And I want fill "userName" field with "test username"
    And I want fill "password" field with "test password"
    And I want fill "clientNote" field with "test clientNote"
    And I want fill "adminNote" field with "test adminNote"
    Then I want to create device for vehicle and save id
    Then I want install device for vehicle
    And I want fill "name" field with "client driver name"
    And I want fill "surname" field with "client driver surname"
    And I want fill "phone" field with "+46 (87)4646753"
    And I want fill "email" field with "driver@example.com"
    Then I want to register driver for current client
    And I see field "email" filled with "driver@example.com"
    And I see field "team/id" filled with 11
    When I want fill "driverSensorId" field with "FFFF9602E6C1"
    Then I want to update current client's user by email "driver@example.com"
    Given There are following tracker payload from topflytech tracker with socket "test-topflytech-test-socket":
      | payload                                                                                                                                                                                                                                                              |
      | 262603000f02ea0865284040731352                                                                                                                                                                                                                         |
      | 262610003600130865284040731352201217050123010004d497ae41f7dd00640d080ce4000148e3fa78d738b600640ccd17d400004a   |
      | 262610003600130865284040731352201217050124010004d497ae41f7dd00640d080ce4000148e3fa78d738b600640ccd17d400004a   |
    Given I want handle sensor event
    And  I want send messages in queue notification_events
    Then I want sleep on 1 seconds
    Then I signed with email "linxio-dev@ocsico.com"
    Given Elastica populate index "eventLog"
    And I want fill "eventId" field with "event(SENSOR_BATTERY_LEVEL, user)"
    Then I want get event log
    Then response code is 200
    And I see field "data/0/eventName" filled with "SENSOR_BATTERY_LEVEL"
    And I see field "data/0/formattedDate"
    And I see field "data/0/importance" filled with "normal"
    And I see field "data/0/eventTeam" filled with "ACME1"
    And I see field "data/0/teamId" filled with 11
    And I see field "data/0/triggeredBy" filled with driver
    And I see field "data/0/eventSource/name" filled with "regNoForSensorId"
    And I see field "data/0/eventSource/type" filled with "trackerhistorysensor"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I see field "data/0/notificationsList/0/id"
    And I see field "data/0/notificationsList/0/title" filled with "Test# - sensor battery level"
    And I see field "data/0/notificationsList/0/eventId"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I see field "data/0/notificationGenerated" filled with "yes"
    And I want clean filled data
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/recipient" filled with "string(1)"
    And I see field "data/0/subject" filled with "Alerts - Sensor battery level"
#    And I see field "data/0/message" filled with "Sensor e3fa78d738b6 detected battery level 100."
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "trackerhistorysensor"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 10
    And I see field "data/0/event/entityTeam/clientName" filled with "ACME1"
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
#    And I see field "data/0/message" filled with "Vehicle regNoForSensorId has been re-assigned to driver client driver name client driver surname. "
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "trackerhistorysensor"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 10
    And I see field "data/0/event/entityTeam/clientName" filled with "ACME1"
    And I see field "data/0/event/eventId"
    And I see field "data/0/event/eventLogId"

  Scenario: I want to test sensor status event
    And I want clean filled data
    Then I want fill "title" field with "Test# - sensor status"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "teamId" field with 11
    And I want fill "eventId" field with "event(SENSOR_STATUS, user)"
    And I want fill "scope.subtype" field with "any"
    And I want fill "scope.value.0" field with null
    And I want fill "recipients.0.type" field with 'users_list'
    And I want fill "recipients.0.value." field with 'user(linxio-dev@ocsico.com)'
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
    And I want fill "additionalParams.isStatus" field with true
    And I want fill "additionalParams.status" field with 1
    Then I want create notification
    Then response code is 200
    Then I want get last notification
    And I see field "listenerTeamId" filled with 11
    And I see field "ownerTeamId" filled with 1
    And I want clean filled data
    And I want get client by name "ACME1" and save id
    And I want fill teamId by saved clientId
    And I want fill "type" field with "Car"
    And I want fill "model" field with "test model"
    And I want fill "regNo" field with "regNoForSensorId"
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
    And I want clean filled data
    And I want fill device model for vehicle with name "TLP1-SF"
    And I want fill "sn" field with "test sn"
    And I want fill "status" field with "inStock"
    And I want fill "port" field with 25
    And I want fill "hw" field with "test hw"
    And I want fill "sw" field with "test sw"
    And I want fill "imei" field with "865284040731352"
    And I want fill "phone" field with "+(375) 245535454"
    And I want fill "imsi" field with "test imsi"
    And I want fill "devEui" field with "test devEui"
    And I want fill "userName" field with "test username"
    And I want fill "password" field with "test password"
    And I want fill "clientNote" field with "test clientNote"
    And I want fill "adminNote" field with "test adminNote"
    Then I want to create device for vehicle and save id
    Then I want install device for vehicle
    And I want fill "name" field with "client driver name"
    And I want fill "surname" field with "client driver surname"
    And I want fill "phone" field with "+46 (87)4646753"
    And I want fill "email" field with "driver@example.com"
    Then I want to register driver for current client
    And I see field "email" filled with "driver@example.com"
    And I see field "team/id" filled with 11
    When I want fill "driverSensorId" field with "FFFF9602E6C1"
    Then I want to update current client's user by email "driver@example.com"
    Given There are following tracker payload from topflytech tracker with socket "test-topflytech-test-socket":
      | payload                                                                                                                                                                                                                                                              |
      | 262603000f02ea0865284040731352                                                                                                                                                                                                                         |
      | 262610003600130865284040731352201217050123010004d497ae41f7dd00640d080ce4000148e3fa78d738b600640ccd17d400004a   |
      | 262610003600130865284040731352201217050124010004d497ae41f7dd00640d080ce4000148e3fa78d738b600640ccd17d400004a   |
    Given I want handle sensor event
    And  I want send messages in queue notification_events
    Then I want sleep on 1 seconds
    Then I signed with email "linxio-dev@ocsico.com"
    Given Elastica populate index "eventLog"
    And I want fill "eventId" field with "event(SENSOR_STATUS, user)"
    Then I want get event log
    Then response code is 200
    And I see field "data/0/eventName" filled with "SENSOR_STATUS"
    And I see field "data/0/formattedDate"
    And I see field "data/0/importance" filled with "normal"
    And I see field "data/0/eventTeam" filled with "ACME1"
    And I see field "data/0/teamId" filled with 11
    And I see field "data/0/triggeredBy" filled with driver
    And I see field "data/0/eventSource/name" filled with "regNoForSensorId"
    And I see field "data/0/eventSource/type" filled with "trackerhistorysensor"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I see field "data/0/notificationsList/0/id"
    And I see field "data/0/notificationsList/0/title" filled with "Test# - sensor status"
    And I see field "data/0/notificationsList/0/eventId"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I see field "data/0/notificationGenerated" filled with "yes"
    And I want clean filled data
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/recipient" filled with "string(1)"
    And I see field "data/0/subject" filled with "Alerts - Sensor status"
#    And I see field "data/0/message" filled with "Sensor e3fa78d738b6 changed status to online."
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "trackerhistorysensor"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 10
    And I see field "data/0/event/entityTeam/clientName" filled with "ACME1"
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
#    And I see field "data/0/message" filled with "Vehicle regNoForSensorId has been re-assigned to driver client driver name client driver surname. "
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "trackerhistorysensor"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 10
    And I see field "data/0/event/entityTeam/clientName" filled with "ACME1"
    And I see field "data/0/event/eventId"
    And I see field "data/0/event/eventLogId"