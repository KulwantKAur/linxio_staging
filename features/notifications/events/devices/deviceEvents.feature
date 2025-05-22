Feature: Devices Events

  Background:
    Given I signed in as "super_admin" team "admin"
    And the queue associated to notification.events events producer is empty

  Scenario: I want check notification by device offline
    Then I want fill "title" field with "Test# device offline"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "teamId" field with 2
    And I want fill "eventId" field with "event(DEVICE_OFFLINE, user)"
    And I want fill "scope.subtype" field with "any"
    Then I want fill "scope.value.0" field with null
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
    And I want clean filled data
    Given I signed in as "admin" team "client" and teamId 2
    And I want fill "model" field with "Test model"
    And I want fill "type" field with "Car"
    And I want fill "vin" field with "1234567890Ab"
    And I want fill "fuelType" field with "1"
    And I want fill "year" field with "2000"
    And I want fill "ecoSpeed" field with "85"
    And I want fill "defaultLabel" field with "Default label"
    And I want fill "regNo" field with "1234567890Ab"
    And I want fill "makeModel" field with "testModel"
    And I want fill "make" field with "testMake"
    And I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want to create vehicle and save id
    And response code is 200
    And I want fill device model for vehicle with name "TLP1-SF"
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
    And I want to create device for vehicle and save id
    And response code is 200
    And I want upload file
    And I want fill "odometer" field with "654321"
    Then I want install device
    And I want send messages in queue notification_events
    And I see field "installDate" is not null
    And I see field "deviceInstallation" is not null
    And I see field "status" filled with "offline"
    And I see field "deviceInstallation/odometer" filled with "string(654321)"
    And I want clean filled data
    When I want check vehicle status
    Then I see field "status" filled with "online"
    And I want check "files/0/id" is not empty
    Given I signed in as "admin" team "client" and teamId 3
    Then I want get device installation
    And I see right deviceId
    And I want fill "status" field with "unavailable"
    And I want fill "blockingMessage" field with "test message"
    Then I want uninstall device
    And I want send messages in queue notification_events
    And I see field "deviceInstallation" filled with null
    And I see field "installDate" filled with null
    Then response code is 200
    And I see field "status" filled with "unavailable"
    And I see field "blockingMessage" filled with "test message"
    Then I want install device
    And I want fill "status" field with "inStock"
    Then I want uninstall device
    And I want send messages in queue notification_events
    And I see field "status" filled with "inStock"
    And I want clean filled data
    Then I signed with email "linxio-dev@ocsico.com"
    Given Elastica populate index "eventLog"
    And I want fill "eventId" field with "event(DEVICE_OFFLINE, user)"
    Then I want get event log
    Then response code is 200
    And I see field "data/0/eventName" filled with "DEVICE_OFFLINE"
    And I see field "data/0/formattedDate"
    And I see field "data/0/importance" filled with "normal"
    And I see field "data/0/eventTeam" filled with "client-name-0"
    And I see field "data/0/eventSource/name" filled with "string(888888888888888)"
    And I see field "data/0/eventSource/type" filled with "device"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I see field "data/0/eventSource/deviceId"
    And I see field "data/0/shortDetails"
    And I see field "data/0/notificationsList/0/id"
    And I see field "data/0/notificationsList/0/title" filled with "Test# device offline"
    And I see field "data/0/notificationsList/0/eventId"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I want get event object to save
    And I want clean filled data
    And I want check EntityPlaceholderService
    And I see in saved value field "from_company" filled with "Linxio"
    And I see in saved value field "device_imei" filled with "string(888888888888888)"
    And I see in saved value field "event_time"
    And I see in saved value field "status" filled with "inStock"
  And I see in saved value field "data_url"
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/recipient" filled with "string(1)"
    And I see field "data/0/subject" filled with "Alerts - Device changed status: offline"
    Then I should get an email with field "data/0/message" containing to template:
    """
    Device ${device_imei} has changed status: offline. ${comment}
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "device"
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
    Then I should get an email with field "data/0/message" containing to template:
    """
    Device ${device_imei} has changed status: offline. ${comment}
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "device"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 1
    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"
    And I want clean filled data
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "email"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "email"
    And I see field "data/0/recipient" filled with "linxio-dev@ocsico.com"
    And I see field "data/0/subject" filled with "Alerts - Device changed status: offline"
    Then I should get an email with field "data/0/message" containing to template:
    """
    <h3>New notification from the ${from_company} system:</h3>
    <p>Device ${device_imei} status changed to \"offline\"</p><br/>
    <p><b>Detailed information:</b></p>
    <p>${event_time} - Device ${device_imei} changed status to \"offline\"</p>
    <p>${data_url}</p><br/>
    <p>${comment}</p>
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "device"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 1
    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"
    And I see field "data/0/event/eventId"

  Scenario: I want check notification by device in stock
    Then I want fill "title" field with "Test# device in stock"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "teamId" field with 2
    And I want fill "eventId" field with "event(DEVICE_IN_STOCK, user)"
    And I want fill "scope.subtype" field with "any"
    Then I want fill "scope.value.0" field with null
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
    Given I signed in as "admin" team "client" and teamId 2
    And I want fill "model" field with "Test model"
    And I want fill "type" field with "Car"
    And I want fill "vin" field with "1234567890Ab"
    And I want fill "fuelType" field with "1"
    And I want fill "year" field with "2000"
    And I want fill "ecoSpeed" field with "85"
    And I want fill "defaultLabel" field with "Default label"
    And I want fill "regNo" field with "1234567890Ab"
    And I want fill "makeModel" field with "testModel"
    And I want fill "make" field with "testMake"
    And I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want to create vehicle and save id
    And response code is 200
    And I want fill device model for vehicle with name "TLP1-SF"
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
    And I want to create device for vehicle and save id
    And response code is 200
    And I want upload file
    And I want fill "odometer" field with "654321"
    Then I want install device
    And I see field "installDate" is not null
    And I see field "deviceInstallation" is not null
    And I see field "status" filled with "offline"
    And I see field "deviceInstallation/odometer" filled with "string(654321)"
    And I want clean filled data
    When I want check vehicle status
    Then I see field "status" filled with "online"
    And I want check "files/0/id" is not empty
    Given I signed in as "admin" team "client" and teamId 3
    Then I want get device installation
    And I see right deviceId
    And I want fill "status" field with "unavailable"
    And I want fill "blockingMessage" field with "test message"
    Then I want uninstall device
    And I see field "deviceInstallation" filled with null
    And I see field "installDate" filled with null
    Then response code is 200
    And I see field "status" filled with "unavailable"
    And I see field "blockingMessage" filled with "test message"
    Then I want install device
    And I want fill "status" field with "inStock"
    Then I want uninstall device
    And I want send messages in queue notification_events
    And I see field "status" filled with "inStock"
    Then I signed with email "linxio-dev@ocsico.com"
    Given Elastica populate index "eventLog"
    And I want clean filled data
    And I want fill "eventId" field with "event(DEVICE_IN_STOCK, user)"
    Then I want get event log
    Then response code is 200
    And I see field "data/0/eventName" filled with "DEVICE_IN_STOCK"
    And I see field "data/0/importance" filled with "normal"
    And I see field "data/0/eventTeam" filled with "client-name-0"
    And I see field "data/0/triggeredBy" filled with "user"
    And I see field "data/0/triggeredDetails" filled with "admin@user.com"
#    And I see field "data/0/eventSourceType" filled with "device"
    And I see field "data/0/eventSource/name" filled with "string(888888888888888)"
    And I see field "data/0/eventSource/type" filled with "device"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I see field "data/0/eventSource/deviceId"
    And I see field "data/0/shortDetails"
    And I see field "data/0/notificationsList/0/id"
    And I see field "data/0/notificationsList/0/title" filled with "Test# device in stock"
    And I see field "data/0/notificationsList/0/eventId"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I want get event object to save
    And I want clean filled data
    And I want check EntityPlaceholderService
    And I see in saved value field "from_company" filled with "Linxio"
    And I see in saved value field "device_imei" filled with "string(888888888888888)"
    And I see in saved value field "event_time"
    And I see in saved value field "status" filled with "inStock"
    And I see in saved value field "data_url"
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/recipient" filled with "string(1)"
    And I see field "data/0/subject" filled with "Alerts - Device in stock"
    Then I should get an email with field "data/0/message" containing to template:
    """
    Device in stock: ${device_imei}. ${comment}
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "device"
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
    Then I should get an email with field "data/0/message" containing to template:
    """
    Device in stock: ${device_imei}. ${comment}
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "device"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 1
    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"
    And I want clean filled data
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "email"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "email"
    And I see field "data/0/recipient" filled with "linxio-dev@ocsico.com"
    And I see field "data/0/subject" filled with "Alerts - Device in stock"
    Then I should get an email with field "data/0/message" containing to template:
    """
    <h3>New notification from the ${from_company} system:</h3>
    <p>Device in stock: ${device_imei}.</p><br/>
    <p><b>Detailed information:</b></p>
    <p>${event_time} - Device ${device_imei} changed status to \"in stock\"</p>
    <p>${data_url}</p><br/>
    <p>${comment}</p>
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "device"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 1
    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"
    And I see field "data/0/event/eventId"

  Scenario: I want check notification by device unavailable
    Then I want fill "title" field with "Test# device unavailable"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "teamId" field with 2
    And I want fill "eventId" field with "event(DEVICE_UNAVAILABLE, user)"
    And I want fill "scope.subtype" field with "any"
    Then I want fill "scope.value.0" field with null
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
    And I want clean filled data
    Given I signed in as "admin" team "client" and teamId 2
    And I want fill "model" field with "Test model"
    And I want fill "type" field with "Car"
    And I want fill "vin" field with "1234567890Ab"
    And I want fill "fuelType" field with "1"
    And I want fill "year" field with "2000"
    And I want fill "ecoSpeed" field with "85"
    And I want fill "defaultLabel" field with "Default label"
    And I want fill "regNo" field with "1234567890Ab"
    And I want fill "makeModel" field with "testModel"
    And I want fill "make" field with "testMake"
    And I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want to create vehicle and save id
    And response code is 200
    And I want fill device model for vehicle with name "TLP1-SF"
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
    And I want to create device for vehicle and save id
    And response code is 200
    And I want upload file
    And I want fill "odometer" field with "654321"
    Then I want install device
#    And I want send messages in queue notification_events
    And I see field "installDate" is not null
    And I see field "deviceInstallation" is not null
    And I see field "status" filled with "offline"
    And I see field "deviceInstallation/odometer" filled with "string(654321)"
    And I want clean filled data
    When I want check vehicle status
    Then I see field "status" filled with "online"
    And I want check "files/0/id" is not empty
    Given I signed in as "admin" team "client" and teamId 3
    Then I want get device installation
    And I see right deviceId
    And I want fill "status" field with "unavailable"
    And I want fill "blockingMessage" field with "test message"
    Then I want uninstall device
    And I want send messages in queue notification_events
    And I see field "deviceInstallation" filled with null
    And I see field "installDate" filled with null
    Then response code is 200
    And I see field "status" filled with "unavailable"
    And I see field "blockingMessage" filled with "test message"
    And I want clean filled data
    Then I signed with email "linxio-dev@ocsico.com"
    Given Elastica populate index "eventLog"
    And I want fill "eventId" field with "event(DEVICE_UNAVAILABLE, user)"
    Then I want get event log
    Then response code is 200
    And I see field "data/0/eventName" filled with "DEVICE_UNAVAILABLE"
    And I see field "data/0/importance" filled with "normal"
    And I see field "data/0/eventTeam" filled with "client-name-0"
    And I see field "data/0/triggeredBy" filled with "user"
    And I see field "data/0/triggeredDetails" filled with "admin@user.com"
#    And I see field "data/0/eventSourceType" filled with "device"
    And I see field "data/0/eventSource/name" filled with "string(888888888888888)"
    And I see field "data/0/eventSource/type" filled with "device"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I see field "data/0/eventSource/deviceId"
    And I see field "data/0/shortDetails"
    And I see field "data/0/notificationsList/0/id"
    And I see field "data/0/notificationsList/0/title" filled with "Test# device unavailable"
    And I see field "data/0/notificationsList/0/eventId"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I want get event object to save
    And I want clean filled data
    And I want check EntityPlaceholderService
    And I see in saved value field "from_company" filled with "Linxio"
    And I see in saved value field "device_imei" filled with "string(888888888888888)"
    And I see in saved value field "event_time"
    And I see in saved value field "status" filled with "unavailable"
    And I see in saved value field "data_url"
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/recipient" filled with "string(1)"
    And I see field "data/0/subject" filled with "Alerts - Device changed status: unavailable"
    Then I should get an email with field "data/0/message" containing to template:
    """
    Device ${device_imei} has changed status: unavailable. ${comment}
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "device"
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
    Then I should get an email with field "data/0/message" containing to template:
    """
    Device ${device_imei} has changed status: unavailable. ${comment}
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "device"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 1
    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"
    And I want clean filled data
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "email"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "email"
    And I see field "data/0/recipient" filled with "linxio-dev@ocsico.com"
    And I see field "data/0/subject" filled with "Alerts - Device changed status: unavailable"
    Then I should get an email with field "data/0/message" containing to template:
    """
    <h3>New notification from the ${from_company} system:</h3>
    <p>Device ${device_imei} status changed to \"unavailable\"</p><br/>
    <p><b>Detailed information:</b></p>
    <p>${event_time} - Device ${device_imei} changed status to \"unavailable\"</p>
    <p>${data_url}</p><br/>
    <p>${comment}</p>
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "device"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 1
    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"
    And I see field "data/0/event/eventId"

  Scenario: I want check notification by device deleted
    Then I want fill "title" field with "Test# device deleted"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "teamId" field with 2
    And I want fill "eventId" field with "event(DEVICE_DELETED, user)"
    And I want fill "scope.subtype" field with "any"
    Then I want fill "scope.value.0" field with null
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
    And I want clean filled data
    And I want fill device model with name "FM3001"
    And I want fill "sn" field with "test sn"
    And I want fill "status" field with "inStock"
    And I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
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
    Then I want to create device and save id
    And response code is 200
    Then I want to delete saved device
    And I want send messages in queue notification_events
    And response code is 204
    And I want clean filled data
    And I want clean filled data
    Then I signed with email "linxio-dev@ocsico.com"
    Given Elastica populate index "eventLog"
    And I want fill "eventId" field with "event(DEVICE_DELETED, user)"
    Then I want get event log
    Then response code is 200
    And I see field "data/0/eventName" filled with "DEVICE_DELETED"
    And I see field "data/0/formattedDate"
    And I see field "data/0/importance" filled with "normal"
    And I see field "data/0/eventTeam" filled with "client-name-0"
    And I see field "data/0/triggeredBy" filled with "user"
    And I see field "data/0/triggeredDetails" filled with "super_admin@user.com"
#    And I see field "data/0/eventSourceType" filled with "device"
    And I see field "data/0/eventSource/name" filled with "string(888888888888888)"
    And I see field "data/0/eventSource/type" filled with "device"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I see field "data/0/eventSource/deviceId"
    And I see field "data/0/shortDetails/deviceImei" filled with "string(888888888888888)"
    And I see field "data/0/notificationsList/0/id"
    And I see field "data/0/notificationsList/0/title" filled with "Test# device deleted"
    And I see field "data/0/notificationsList/0/eventId"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I want get event object to save
    And I want clean filled data
    And I want check EntityPlaceholderService
    And I see in saved value field "from_company" filled with "Linxio"
    And I see in saved value field "device_imei" filled with "string(888888888888888)"
    And I see in saved value field "event_time"
    And I see in saved value field "status" filled with "deleted"
    And I see in saved value field "data_url"
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/recipient" filled with "string(1)"
    And I see field "data/0/subject" filled with "Alerts - Device deleted"
    Then I should get an email with field "data/0/message" containing to template:
    """
    Device deleted:  ${device_imei}. ${comment}
    """
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
    Then I should get an email with field "data/0/message" containing to template:
    """
    Device deleted:  ${device_imei}. ${comment}
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "device"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 1
    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"
    And I want clean filled data
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "email"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "email"
    And I see field "data/0/recipient" filled with "linxio-dev@ocsico.com"
    And I see field "data/0/subject" filled with "Alerts - Device deleted"
    Then I should get an email with field "data/0/message" containing to template:
    """
    <h3>New notification from the ${from_company} system:</h3>
    <p>Device ${device_imei} was deleted.</p><br/>
    <p><b>Detailed information:</b></p>
    <p>${event_time} - ${triggered_by} have deleted device ${device_imei}.</p>
    <p>${data_url}</p><br/>
    <p>${comment}</p>
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "device"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 1
    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"
    And I see field "data/0/event/eventId"