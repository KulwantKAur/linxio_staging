Feature: Odometer Events

  Scenario: I want check notification by vehicle odometer
    Given I signed in as "super_admin" team "admin"
    And the queue associated to notification.events events producer is empty
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    Then I want fill "title" field with "Test#-change vehicle odometer"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "eventId" field with "event(ODOMETER_CORRECTED, user)"
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
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
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
    Then I want to create vehicle and save id
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
    And I want install device for vehicle
    And I want set vehicle driver with user of current team with date "2021-01-29 08:19:38"
    Then I want change installed device date for saved vehicle to '2021-01-01T18:55:30+00:00'
    Given There are following tracker payload from topflytech tracker, replace date now true, offset "80", length "6", with socket "test-topflytech-test-socket":
      | payload                                                                                                                                                                                                                                                              |
      | 2626020053022d088888888888888800780e1014012c0006c8c02005010000402000000002fac80020112519205800002042d1041143d04117c200100006147001400000b3b700001e140840176e473d0e58ff   |
      | 2626020053022c088888888888888800780e1014012c0022c7c020050100007fff00000002fab90020112519205400002842cd041143f54117c20010001f120001300000b3b00000181a073e1278463d2b58ff   |
      | 26260200530259088888888888888800780e1014012c0029c9c02005010000402000000003219e0021032519382600008e422d06114370f116c200100008120000200000bc1b000008de039c066676430a58ff   |
    Then I want clean filled data
    Then I want fill "odometer" field with 6900000
    Then I want fill "occurredAt" field with "2019-12-20T06:00:00+00:00"
    Then I want to create odometer record and save id
    And response code is 200
    And I see field "odometer" filled with 6900000
    And I see field "accuracy" filled with 6694786
    And I see field "occurredAt"
    And I see field "lastTrackerRecordOccurredAt"
    And I see field "lastTrackerRecordOdometer" filled with 205214
    When I want to get vehicle odometer history
    And I see field "data/0/odometer" filled with 6900000
    And  I want send messages in queue notification_events
    Then I want clean filled data
    Then I signed with email "linxio-dev@ocsico.com"
    Given Elastica populate index "eventLog"
    And I want fill "eventId" field with "event(ODOMETER_CORRECTED, user)"
    Then I want get event log
    Then response code is 200
    And I see field "data/0/eventName" filled with "ODOMETER_CORRECTED"
    And I see field "data/0/formattedDate"
    And I see field "data/0/importance" filled with "normal"
    And I see field "data/0/eventTeam" filled with "client-name-0"
    And I see field "data/0/triggeredBy" filled with "user"
    And I see field "data/0/triggeredDetails" filled with "super_admin@user.com"
#    And I see field "data/0/eventSourceType" filled with "vehicleodometer"
    And I see field "data/0/eventSource/name" filled with "1234567890Ab"
    And I see field "data/0/eventSource/type" filled with "vehicle"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I see field "data/0/eventSource/vehicleodometerId"
    And I see field "data/0/eventSource/vehicleId"
    And I see field "data/0/notificationsList/0/id"
    And I see field "data/0/notificationsList/0/title" filled with "Test#-change vehicle odometer"
    And I see field "data/0/notificationsList/0/eventId"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I want get event object to save
    And I want clean filled data
    And I want check EntityPlaceholderService
    And I see in saved value field "from_company" filled with "Linxio"
    And I see in saved value field "reg_no" filled with "1234567890Ab"
    And I see in saved value field "user_name" filled with "test user surname"
    And I see in saved value field "new_value" filled with 6900
    And I see in saved value field "old_value"
    And I see in saved value field "event_time"
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/recipient" filled with "string(1)"
    And I see field "data/0/subject" filled with "Alerts - Odometer corrected"
    Then I should get an email with field "data/0/message" containing to template:
    """
    Odometer set to ${new_value} for ${reg_no}. ${comment}
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
    Odometer set to ${new_value} for ${reg_no}. ${comment}
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
    And I see field "data/0/subject" filled with "Alerts - Odometer corrected"
    Then I should get an email with field "data/0/message" containing to template:
    """
    <h3>New notification from the ${from_company} system:</h3>
    <p>Odometer set to ${new_value} for ${reg_no}</p><br/>
    <p><b>Detailed information:</b></p>
    <p>${event_time} - ${user_name} corrected odometer for ${reg_no}; old value - ${old_value}, new value - ${new_value}.</p>
    <br/>
    <p>${comment}</p>
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "vehicle"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 1
    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"
    And I see field "data/0/event/eventId"
    And I see field "data/0/event/eventLogId"