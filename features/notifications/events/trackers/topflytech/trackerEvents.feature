Feature: Tracker Topflytech Events

  # check new test
  Scenario: I want check notification - sos-button from device with vehicle
    Given I want handle check panic button event from 'device'
    Given I signed in as "admin" team "client" and teamId 2
    And the queue associated to notification.events events producer is empty
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
    And I want fill "name" field with "client driver name"
    And I want fill "surname" field with "client driver surname"
    And I want fill "phone" field with "+46 (87)4646753"
    And I want fill "email" field with "driver@example.com"
    Then I want to register driver for current client
    And I see field "email" filled with "driver@example.com"
    Then I want to set vehicle driver by user email "driver@example.com" and date "2019-01-01 00:00:00"
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
    And I want clean filled data
    Then I want fill "title" field with "Test# - device sos button"
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
    Given I signed in as "admin" team "client" and teamId 2
    And I want install device for vehicle
    And response code is 200
    And I want set vehicle driver with user of current team with date "2021-01-29 08:19:38"
    Then I want change installed device date for saved vehicle to '2021-01-01T18:55:30+00:00'
    Given There are following tracker payload from topflytech tracker, replace date now true, offset "80", length "6", with socket "test-topflytech-test-socket":
      | payload                                                                                                                                                                                                                                                              |
      | 26260200530259088888888888888800780e1014012c0029c9c02005010000402000000003219e0021092210382600008e422d06114370f116c200100008120000200000bc1b000008de039c066676430a58ff   |
      | 26260200530259088888888888888800780e1014012c0029c9c02005010000402000000003219e0021092211382600008e422d06114370f116c200100008120000200000bc1b000008de039c066676430a58ff   |
      | 26260200530259088888888888888800780e1014012c0029c9c02005010000402000000003219e0021092212382600008e422d06114370f116c200100008120000200000bc1b000008de039c066676430a58ff   |
    And I want send messages in queue notification_events
    Then I want clean filled data
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
    And I see field "data/0/triggeredDetails" filled with "driver@example.com"
    And I see field "data/0/eventSource/name" filled with "1234567890Ab"
    And I see field "data/0/eventSource/type" filled with "vehicle"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I see field "data/0/eventSource/vehicleId"
    And I see field "data/0/eventSource/routeId"
    And I see field "data/0/shortDetails/address"
    And I see field "data/0/shortDetails/lastCoordinates"
    And I see field "data/0/shortDetails/duration" filled with 7200
    And I see field "data/0/shortDetails/deviceImei" filled with "string(888888888888888)"
    And I see field "data/0/shortDetails/vehicleRegNo" filled with "1234567890Ab"
    And I see field "data/0/notificationsList/0/id"
    And I see field "data/0/notificationsList/0/title" filled with "Test# - device sos button"
    And I see field "data/0/notificationsList/0/eventId"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I want get event object to save
    And I want clean filled data
    And I want check EntityPlaceholderService
    And I see in saved value field "from_company" filled with "Linxio"
    And I see in saved value field "reg_no_or_device" filled with "1234567890Ab"
    And I see in saved value field "event_time"
    And I see in saved value field "reg_no_with_model_or_device" filled with "1234567890Ab (testMake testModel)"
    And I see in saved value field "driver" filled with "client driver name client driver surname"
    And I see in saved value field "vehicle_url" filled with "Vehicle page: https://url/client/fleet/47/specification"
    And I see in saved value field "driver_url" filled with "Driver page: https://url/client/drivers/44/profile-info"
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/recipient" filled with "string(1)"
    And I see field "data/0/subject" filled with "Alerts - SOS button - 1234567890Ab"
    Then I should get an email with field "data/0/message" containing to template:
    """
    SOS button pressed by driver: ${driver} on ${reg_no_or_device}. ${comment}
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "vehicle"
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
    SOS button pressed by driver: ${driver} on ${reg_no_or_device}. ${event_time} ${comment}
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "vehicle"
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
    And I see field "data/0/subject" filled with "Alerts - [Linxio] SOS button - 1234567890Ab"
    Then I should get an email with field "data/0/message" containing to template:
    """
    <h3>New notification from the ${from_company} system:</h3>
    <p>SOS button pressed on ${reg_no_or_device}.</p><br/>
    <p><b>Detailed information:</b></p>
    <p>${event_time} - SOS button pressed by driver: ${driver} on ${reg_no_with_model_or_device}.</p>
    <p>${vehicle_url}</p>
    <p>${driver_url}</p><br/>
    <p>${comment}</p>
    """
    And I want check "data/0/message" is not empty
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "vehicle"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 1
    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"
    And I see field "data/0/event/eventId"
    And I see field "data/0/event/eventLogId"