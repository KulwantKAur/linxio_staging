Feature: Vehicles Events

  Background:
    Given I signed in as "super_admin" team "admin"
    And the queue associated to notification.events events producer is empty

  Scenario: I want check notification by vehicle created
    Then I want fill "title" field with "Test# - vehicle created"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "teamId" field with 2
    And I want fill "eventId" field with "event(VEHICLE_CREATED, user)"
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
    Given I signed in as "admin" team "client" and teamId 2
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
#    And I want fill "name" field with "test group"
#    And I want to create vehicle group and save id
#    And I want fill vehicle group id
    And I want fill "type" field with "Car"
    And I want fill "model" field with "model"
    And I want fill "available" field with true
    And I want fill "regNo" field with "testRegNo"
    And I want fill "defaultLabel" field with "defaultLabel"
    And I want fill "vin" field with "vin"
    And I want fill "regCertNo" field with "regCertNo"
    And I want fill "enginePower" field with 2.0
    And I want fill "engineCapacity" field with 1.0
    And I want fill "fuelType" field with 1
    And I want fill "emissionClass" field with "emissionClass"
    And I want fill "co2Emissions" field with 0.1
    And I want fill "grossWeight" field with 0.2
    And I want fill "status" field with "online"
    And I want fill "makeModel" field with "testModel"
    And I want fill "make" field with "testMake"
    Then I want to create vehicle and save id
    And I want send messages in queue notification_events
    And response code is 200
    Then I want clean filled data
#    Then I want sleep on 1 seconds
    Then I signed with email "linxio-dev@ocsico.com"
    Given Elastica populate index "eventLog"
    And I want fill "eventId" field with "event(VEHICLE_CREATED, user)"
    Then I want get event log
    Then response code is 200
    And I see field "data/0/eventName" filled with "VEHICLE_CREATED"
    And I see field "data/0/formattedDate"
    And I see field "data/0/importance" filled with "normal"
    And I see field "data/0/eventTeam" filled with "client-name-0"
    And I see field "data/0/triggeredBy" filled with "user"
    And I see field "data/0/triggeredDetails" filled with "admin@user.com"
#    And I see field "data/0/eventSourceType" filled with "vehicle"
    And I see field "data/0/eventSource/name" filled with "testRegNo"
    And I see field "data/0/eventSource/type" filled with "vehicle"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I see field "data/0/eventSource/vehicleId"
    And I see field "data/0/notificationsList/0/id"
    And I see field "data/0/notificationsList/0/title" filled with "Test# - vehicle created"
    And I see field "data/0/notificationsList/0/eventId"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I want get event object to save
    And I want clean filled data
    And I want check EntityPlaceholderService
    And I see in saved value field "from_company" filled with "Linxio"
    And I see in saved value field "reg_no" filled with "testRegNo"
    And I see in saved value field "model" filled with "testMake testModel"
    And I see in saved value field "team" filled with "client-name-0"
    And I see in saved value field "triggered_by" filled with "test user surname"
    And I see in saved value field "event_time"
    And I see in saved value field "reg_no_with_model" filled with "testRegNo (testMake testModel)"
    And I see in saved value field "vehicle_url" filled with "Vehicle page: https://url/client/fleet/47/specification"
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/recipient" filled with "string(1)"
    And I see field "data/0/subject" filled with "Alerts - Vehicle created"
    Then I should get an email with field "data/0/message" containing to template:
    """
    Vehicle created: ${reg_no}. ${comment}
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
    Vehicle created: ${reg_no}. ${comment}
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
    And I see field "data/0/subject" filled with "Alerts - Vehicle created"
    Then I should get an email with field "data/0/message" containing to template:
    """
    <h3>New notification from the ${from_company} system:</h3>
    <p>New vehicle created - ${reg_no_with_model}.</p><br/>
    <p><b>Detailed information:</b></p>
    <p>${event_time} - ${triggered_by} have created vehicle vehicle ${reg_no_with_model} [team: ${team}]</p>
    <p>${vehicle_url}</p><br/>
    <p>${comment}</p>
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "vehicle"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 1
    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"
    And I see field "data/0/event/eventId"
    And I see field "data/0/event/eventLogId"

  Scenario: I want check notification by vehicle change regNo
    Then I want fill "title" field with "Test# - vehicle change regNo"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "teamId" field with 2
    And I want fill "eventId" field with "event(VEHICLE_CHANGED_REGNO, user)"
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
    Given I signed in as "admin" team "client" and teamId 2
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
#    And I want fill "name" field with "test group"
#    And I want to create vehicle group and save id
#    And I want fill vehicle group id
    And I want fill "type" field with "Car"
    And I want fill "makeModel" field with "testModel"
    And I want fill "make" field with "testMake"
    And I want fill "available" field with true
    And I want fill "regNo" field with "testRegNo"
    And I want fill "defaultLabel" field with "defaultLabel"
    And I want fill "vin" field with "vin"
    And I want fill "regCertNo" field with "regCertNo"
    And I want fill "enginePower" field with 2.0
    And I want fill "engineCapacity" field with 1.0
    And I want fill "fuelType" field with 1
    And I want fill "emissionClass" field with "emissionClass"
    And I want fill "co2Emissions" field with 0.1
    And I want fill "grossWeight" field with 0.2
    And I want fill "status" field with "inactive"
    Then I want to create vehicle and save id
    And response code is 200
    Then I want clean filled data
    And I want fill "name" field with "test group1"
    And I want to create vehicle group and save id
    And I want fill vehicle group id
    And I want fill "type" field with "Car"
    And I want fill "regNo" field with "changeRegNo2"
    And I want fill "defaultLabel" field with "defaultLabel2"
    And I want fill "vin" field with "vin2"
    And I want fill "regCertNo" field with "regCertNo2"
    And I want fill "enginePower" field with 3.0
    And I want fill "engineCapacity" field with 2.0
    And I want fill "fuelType" field with 1
    And I want fill "emissionClass" field with "emissionClass2"
    And I want fill "co2Emissions" field with 0.2222
    And I want fill "grossWeight" field with 0.3333
    And I want fill "status" field with "inactive"
    And I want fill "averageFuel" field with 22.2
    When I want upload file "test_image" "png" "975" "image/png"
    Then I want to edit vehicle by saved id
    And I want send messages in queue notification_events
    And response code is 200
    Then I want clean files data
    And I see field "team/type" filled with "client"
#    And I see field "type" filled with "type2"
    And I see field "regNo" filled with "changeRegNo2"
    And I see field "defaultLabel" filled with "defaultLabel2"
    And I see field "vin" filled with "vin2"
    And I see field "regCertNo" filled with "regCertNo2"
    And I see field "enginePower" filled with 3.0
    And I see field "engineCapacity" filled with 2.0
    And I see field "fuelType" filled with 1
    And I see field "emissionClass" filled with "emissionClass2"
    And I see field "co2Emissions" filled with 0.2222
    And I see field "grossWeight" filled with 0.3333
    And I see field "averageFuel" filled with 22.2
    And I see field "status" filled with "inactive"
    And I want check "picture" is not empty
    Then I want clean filled data
#    Then I want sleep on 1 seconds
    Then I signed with email "linxio-dev@ocsico.com"
    Given Elastica populate index "eventLog"
    And I want fill "eventId" field with "event(VEHICLE_CHANGED_REGNO, user)"
    Then I want get event log
    Then response code is 200
    And I see field "data/0/eventName" filled with "VEHICLE_CHANGED_REGNO"
    And I see field "data/0/formattedDate"
    And I see field "data/0/importance" filled with "normal"
    And I see field "data/0/eventTeam" filled with "client-name-0"
    And I see field "data/0/triggeredBy" filled with "user"
    And I see field "data/0/triggeredDetails" filled with "admin@user.com"
  #    And I see field "data/0/eventSourceType" filled with "vehicle"
    And I see field "data/0/eventSource/name" filled with "changeRegNo2"
    And I see field "data/0/eventSource/type" filled with "vehicle"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I see field "data/0/eventSource/vehicleId"
    And I see field "data/0/shortDetails/oldValue" filled with "testRegNo"
    And I see field "data/0/notificationsList/0/id"
    And I see field "data/0/notificationsList/0/title" filled with "Test# - vehicle change regNo"
    And I see field "data/0/notificationsList/0/eventId"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I want get event object to save
    And I want clean filled data
    And I want check EntityPlaceholderService
    And I see in saved value field "from_company" filled with "Linxio"
    And I see in saved value field "reg_no" filled with "changeRegNo2"
    And I see in saved value field "model" filled with "testMake testModel"
    And I see in saved value field "team" filled with "client-name-0"
    And I see in saved value field "triggered_by" filled with "test user surname"
    And I see in saved value field "old_value" filled with "--"
    And I see in saved value field "event_time"
    And I see in saved value field "reg_no_with_model" filled with "changeRegNo2 (testMake testModel)"
    And I see in saved value field "vehicle_url" filled with "Vehicle page: https://url/client/fleet/47/specification"
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/recipient" filled with "string(1)"
    And I see field "data/0/subject" filled with "Alerts - Vehicle regNo was changed"
    Then I should get an email with field "data/0/message" containing to template:
    """
    Vehicle regNo was changed: ${reg_no}. ${comment}
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
    Vehicle regNo was changed: ${reg_no}. ${comment}
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
    And I see field "data/0/subject" filled with "Alerts - Vehicle regNo was changed"
    Then I should get an email with field "data/0/message" containing to template:
    """
    <h3>New notification from the ${from_company} system:</h3>
    <p>RegNo changed for vehicle ${reg_no_with_model}.</p><br/>
    <p><b>Detailed information:</b></p>
    <p>${event_time} - ${triggered_by} have changed vehicle RegNo from \"${old_value}\" to \"${reg_no}\".</p>
    <p>${vehicle_url}</p><br/>
    <p>${comment}</p>
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "vehicle"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 1
    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"
    And I see field "data/0/event/eventId"
    And I see field "data/0/event/eventLogId"

  Scenario: I want check notification by vehicle change model
    Then I want fill "title" field with "Test# - vehicle change model"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "teamId" field with 2
    And I want fill "eventId" field with "event(VEHICLE_CHANGED_MODEL, user)"
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
    Given I signed in as "admin" team "client" and teamId 2
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
#    And I want fill "name" field with "test group"
#    And I want to create vehicle group and save id
#    And I want fill vehicle group id
    And I want fill "type" field with "Car"
    And I want fill "makeModel" field with "testModel"
    And I want fill "make" field with "testMake"
    And I want fill "available" field with true
    And I want fill "regNo" field with "testRegNo"
    And I want fill "defaultLabel" field with "defaultLabel"
    And I want fill "vin" field with "vin"
    And I want fill "regCertNo" field with "regCertNo"
    And I want fill "enginePower" field with 2.0
    And I want fill "engineCapacity" field with 1.0
    And I want fill "fuelType" field with 1
    And I want fill "emissionClass" field with "emissionClass"
    And I want fill "co2Emissions" field with 0.1
    And I want fill "grossWeight" field with 0.2
    And I want fill "status" field with "inactive"
    Then I want to create vehicle and save id
    And response code is 200
    Then I want clean filled data
    And I want fill "name" field with "test group1"
    And I want to create vehicle group and save id
    And I want fill vehicle group id
    And I want fill "type" field with "Car"
    And I want fill "makeModel" field with "changeMakeModel2"
    And I want fill "make" field with "changeMake2"
    And I want fill "regNo" field with "RegNo2"
    And I want fill "defaultLabel" field with "defaultLabel2"
    And I want fill "vin" field with "vin2"
    And I want fill "regCertNo" field with "regCertNo2"
    And I want fill "enginePower" field with 3.0
    And I want fill "engineCapacity" field with 2.0
    And I want fill "fuelType" field with 1
    And I want fill "emissionClass" field with "emissionClass2"
    And I want fill "co2Emissions" field with 0.2222
    And I want fill "grossWeight" field with 0.3333
    And I want fill "status" field with "inactive"
    And I want fill "averageFuel" field with 22.2
    When I want upload file "test_image" "png" "975" "image/png"
    Then I want to edit vehicle by saved id
    And I want send messages in queue notification_events
    And response code is 200
    Then I want clean files data
    And I see field "team/type" filled with "client"
#    And I see field "type" filled with "type2"
    And I see field "model" filled with "changeMake2 changeMakeModel2"
    And I see field "makeModel" filled with "changeMakeModel2"
    And I see field "make" filled with "changeMake2"
    And I see field "regNo" filled with "RegNo2"
    And I see field "defaultLabel" filled with "defaultLabel2"
    And I see field "vin" filled with "vin2"
    And I see field "regCertNo" filled with "regCertNo2"
    And I see field "enginePower" filled with 3.0
    And I see field "engineCapacity" filled with 2.0
    And I see field "fuelType" filled with 1
    And I see field "emissionClass" filled with "emissionClass2"
    And I see field "co2Emissions" filled with 0.2222
    And I see field "grossWeight" filled with 0.3333
    And I see field "averageFuel" filled with 22.2
    And I see field "status" filled with "inactive"
    And I want check "picture" is not empty
    Then I want clean filled data
#    Then I want sleep on 1 seconds
    Then I signed with email "linxio-dev@ocsico.com"
    Given Elastica populate index "eventLog"
    And I want fill "eventId" field with "event(VEHICLE_CHANGED_MODEL, user)"
    Then I want get event log
    Then response code is 200
    And I see field "data/0/eventName" filled with "VEHICLE_CHANGED_MODEL"
    And I see field "data/0/formattedDate"
    And I see field "data/0/importance" filled with "normal"
    And I see field "data/0/eventTeam" filled with "client-name-0"
    And I see field "data/0/triggeredBy" filled with "user"
    And I see field "data/0/triggeredDetails" filled with "admin@user.com"
  #    And I see field "data/0/eventSourceType" filled with "vehicle"
    And I see field "data/0/eventSource/name" filled with "changeMake2 changeMakeModel2"
    And I see field "data/0/eventSource/type" filled with "vehicle"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I see field "data/0/eventSource/vehicleId"
    And I see field "data/0/shortDetails/oldValue" filled with "testMake testModel"
    And I see field "data/0/notificationsList/0/id"
    And I see field "data/0/notificationsList/0/title" filled with "Test# - vehicle change model"
    And I see field "data/0/notificationsList/0/eventId"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I want get event object to save
    And I want clean filled data
    And I want check EntityPlaceholderService
    And I see in saved value field "from_company" filled with "Linxio"
    And I see in saved value field "reg_no" filled with "RegNo2"
    And I see in saved value field "model" filled with "changeMake2 changeMakeModel2"
    And I see in saved value field "team" filled with "client-name-0"
    And I see in saved value field "triggered_by" filled with "test user surname"
    # Fix context
    And I see in saved value field "old_value" filled with "--"
    And I see in saved value field "event_time"
    And I see in saved value field "reg_no_with_model" filled with "RegNo2 (changeMake2 changeMakeModel2)"
    And I see in saved value field "vehicle_url" filled with "Vehicle page: https://url/client/fleet/47/specification"
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/recipient" filled with "string(1)"
    And I see field "data/0/subject" filled with "Alerts - Vehicle model was changed"
    Then I should get an email with field "data/0/message" containing to template:
    """
    Vehicle model was changed: ${model}. ${comment}
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
    Vehicle model was changed: ${model}. ${comment}
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
    And I see field "data/0/subject" filled with "Alerts - Vehicle model was changed"
    Then I should get an email with field "data/0/message" containing to template:
    """
    <h3>New notification from the ${from_company} system:</h3>
    <p>Vehicle model changed for ${reg_no}.</p><br/>
    <p><b>Detailed information:</b></p>
    <p>${event_time} - ${triggered_by} have changed vehicle model from \"${old_value}\" to \"${model}\".</p>
    <p>${vehicle_url}</p><br/>
    <p>${comment}</p>
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "vehicle"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 1
    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"
    And I see field "data/0/event/eventId"
    And I see field "data/0/event/eventLogId"

  Scenario: I want check notification by vehicle online
    Then I want fill "title" field with "Test# - vehicle online"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "teamId" field with 2
    And I want fill "eventId" field with "event(VEHICLE_ONLINE, user)"
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
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
#    And I want fill "name" field with "test group"
#    And I want to create vehicle group and save id
#    And I want fill vehicle group id
    And I want fill "type" field with "Car"
    And I want fill "makeModel" field with "testModel"
    And I want fill "make" field with "testMake"
    And I want fill "regNo" field with "regNo"
    And I want fill "defaultLabel" field with "defaultLabel"
    And I want fill "vin" field with "vin"
    And I want fill "regCertNo" field with "regCertNo"
    And I want fill "enginePower" field with 2.0
    And I want fill "engineCapacity" field with 1.0
    And I want fill "fuelType" field with 1
    And I want fill "emissionClass" field with "emissionClass"
    And I want fill "co2Emissions" field with 0.1
    And I want fill "grossWeight" field with 0.2
    And I want fill "status" field with "active"
    And I want fill "averageFuel" field with 33.4
    Then I want to create vehicle and save id
    And response code is 200
#    And I want send messages in queue notification_events
    And I see field "team/type" filled with "client"
    And I want clean filled data
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "name" field with "test group1"
    And I want to create vehicle group and save id
    And I want fill vehicle group id
    And I want fill "type" field with "Car"
    And I want fill "regNo" field with "regNo2"
    And I want fill "defaultLabel" field with "defaultLabel2"
    And I want fill "vin" field with "vin2"
    And I want fill "regCertNo" field with "regCertNo2"
    And I want fill "enginePower" field with 3.0
    And I want fill "engineCapacity" field with 2.0
    And I want fill "fuelType" field with 1
    And I want fill "emissionClass" field with "emissionClass2"
    And I want fill "co2Emissions" field with 0.2222
    And I want fill "grossWeight" field with 0.3333
    And I want fill "status" field with "inactive"
    And I want fill "averageFuel" field with 22.2
    When I want upload file "test_image" "png" "975" "image/png"
    Then I want to edit vehicle by saved id
    And response code is 200
    And I want check "picture" is not empty
    Then I want clean files data
    Then I want clean filled data
    And I want fill teamId by saved clientId
    And I want fill "isUnavailable" field with true
    And I want fill "unavailableMessage" field with "testMessage"
    Then I want to edit vehicle by saved id
    And I see field "status" filled with "unavailable"
    And I see field "unavailableMessage" filled with "testMessage"
    Then I want clean filled data
    And I want fill teamId by saved clientId
    And I want fill "isUnavailable" field with false
    Then I want to edit vehicle by saved id
    And I see field "status" filled with "inactive"
    When I want fill device model for vehicle with name "FM3001"
    And I want fill "sn" field with "test sn"
    And I want fill "status" field with "inStock"
    And I want fill "port" field with 25
    And I want fill "hw" field with "test hw"
    And I want fill "sw" field with "test sw"
    And I want fill "imei" field with "test imei"
    And I want fill "phone" field with "+(375) 245535454"
    And I want fill "imsi" field with "test imsi"
    And I want fill "devEui" field with "test devEui"
    And I want fill "userName" field with "test username"
    And I want fill "password" field with "test password"
    Then I want to create device for vehicle and save id
    Then I want install device for vehicle
    And I want send messages in queue notification_events
    Then I want clean filled data
    Then I signed with email "linxio-dev@ocsico.com"
    Given Elastica populate index "eventLog"
    And I want fill "eventId" field with "event(VEHICLE_ONLINE, user)"
    Then I want get event log
    Then response code is 200
    And I see field "data/0/eventName" filled with "VEHICLE_ONLINE"
    And I see field "data/0/formattedDate"
    And I see field "data/0/importance" filled with "normal"
    And I see field "data/0/eventTeam" filled with "client-name-0"
    And I see field "data/0/triggeredBy" filled with null
#    And I see field "data/0/triggeredBy" filled with "user"
    And I see field "data/0/triggeredDetails" filled with "super_admin@user.com"
  #    And I see field "data/0/eventSourceType" filled with "vehicle"
    And I see field "data/0/eventSource/name" filled with "regNo2"
    And I see field "data/0/eventSource/type" filled with "vehicle"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I see field "data/0/eventSource/vehicleId"
    And I see field "data/0/notificationsList/0/id"
    And I see field "data/0/notificationsList/0/title" filled with "Test# - vehicle online"
    And I see field "data/0/notificationsList/0/eventId"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I want get event object to save
    And I want clean filled data
    And I want check EntityPlaceholderService
    And I see in saved value field "from_company" filled with "Linxio"
    And I see in saved value field "reg_no" filled with "regNo2"
    And I see in saved value field "model" filled with "testMake testModel"
    And I see in saved value field "team" filled with "client-name-0"
    And I see in saved value field "triggered_by" filled with "test user surname"
    # Fix context
    And I see in saved value field "driver" filled with "--"
    And I see in saved value field "status" filled with "online"
    And I see in saved value field "event_time"
    And I see in saved value field "reg_no_with_model" filled with "regNo2 (testMake testModel)"
    And I see in saved value field "vehicle_url" filled with "Vehicle page: https://url/client/fleet/47/specification"
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/recipient" filled with "string(1)"
    And I see field "data/0/subject" filled with "Alerts - Vehicle changed status: online"
    Then I should get an email with field "data/0/message" containing to template:
    """
    Vehicle ${reg_no} has changed status: ${status}. ${comment}
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
    Vehicle ${reg_no} has changed status: ${status}. ${comment}
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
    And I see field "data/0/subject" filled with "Alerts - Vehicle changed status: online"
    Then I should get an email with field "data/0/message" containing to template:
    """
    <h3>New notification from the ${from_company} system:</h3>
    <p>Vehicle ${reg_no_with_model} status changed to \"${status}\".</p><br/>
    <p><b>Detailed information:</b></p>
    <p>${event_time} - Vehicle ${reg_no_with_model} with driver ${driver} changed status to \"${status}\"</p>
    <p>${vehicle_url}</p><br/>
    <p>${comment}</p>
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "vehicle"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 1
    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"
    And I see field "data/0/event/eventId"
    And I see field "data/0/event/eventLogId"

  Scenario: I want check notification by vehicle offline
    Then I want fill "title" field with "Test# - vehicle offline"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "teamId" field with 2
    And I want fill "eventId" field with "event(VEHICLE_OFFLINE, user)"
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
    Given I signed in as "admin" team "client" and teamId 2
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
#    And I want fill "name" field with "test group"
#    And I want to create vehicle group and save id
#    And I want fill vehicle group id
    And I want fill "type" field with "Car"
    And I want fill "makeModel" field with "testModel"
    And I want fill "make" field with "testMake"
    And I want fill "available" field with true
    And I want fill "regNo" field with "testRegNo"
    And I want fill "defaultLabel" field with "defaultLabel"
    And I want fill "vin" field with "vin"
    And I want fill "regCertNo" field with "regCertNo"
    And I want fill "enginePower" field with 2.0
    And I want fill "engineCapacity" field with 1.0
    And I want fill "fuelType" field with 1
    And I want fill "emissionClass" field with "emissionClass"
    And I want fill "co2Emissions" field with 0.1
    And I want fill "grossWeight" field with 0.2
    And I want fill "status" field with "offline"
    Then I want to create vehicle and save id
    Then I want clean filled data
    And I want fill "name" field with "driverName"
    And I want fill "surname" field with "driverSurname"
    And I want fill "phone" field with "+46 (87)4646753"
    And I want fill "email" field with "driver@example.com"
    Then I want to register driver for current client
    And I see field "email" filled with "driver@example.com"
    Then I want to set vehicle driver by user email "driver@example.com" and date "2020-10-20 08:19:38"
    And I want clean filled data
    And I want fill teamId by saved clientId
    And I want fill "isUnavailable" field with true
    And I want fill "unavailableMessage" field with "testMessage"
    Then I want to edit vehicle by saved id
    And I see field "status" filled with "unavailable"
    And I see field "unavailableMessage" filled with "testMessage"
    Then I want clean filled data
    And I want fill teamId by saved clientId
    And I want fill "isUnavailable" field with false
    Then I want to edit vehicle by saved id
    And I want send messages in queue notification_events
    And I see field "status" filled with "offline"
    Then I signed with email "linxio-dev@ocsico.com"
    Given Elastica populate index "eventLog"
    And I want fill "eventId" field with "event(VEHICLE_OFFLINE, user)"
    Then I want get event log
    Then response code is 200
    And I see field "data/0/eventName" filled with "VEHICLE_OFFLINE"
    And I see field "data/0/formattedDate"
    And I see field "data/0/importance" filled with "normal"
    And I see field "data/0/eventTeam" filled with "client-name-0"
    And I see field "data/0/triggeredBy" filled with null
#    And I see field "data/0/triggeredBy" filled with "user"
    And I see field "data/0/triggeredDetails" filled with "admin@user.com"
  #    And I see field "data/0/eventSourceType" filled with "vehicle"
    And I see field "data/0/eventSource/name" filled with "testRegNo"
    And I see field "data/0/eventSource/type" filled with "vehicle"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I see field "data/0/eventSource/vehicleId"
    And I see field "data/0/notificationsList/0/id"
    And I see field "data/0/notificationsList/0/title" filled with "Test# - vehicle offline"
    And I see field "data/0/notificationsList/0/eventId"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I want get event object to save
    And I want clean filled data
    And I want check EntityPlaceholderService
    And I see in saved value field "from_company" filled with "Linxio"
    And I see in saved value field "reg_no" filled with "testRegNo"
    And I see in saved value field "model" filled with "testMake testModel"
    And I see in saved value field "team" filled with "client-name-0"
    And I see in saved value field "triggered_by" filled with "test user surname"
    # Fix context
    And I see in saved value field "driver" filled with "driverName driverSurname"
    And I see in saved value field "status" filled with "offline"
    And I see in saved value field "event_time"
    And I see in saved value field "reg_no_with_model" filled with "testRegNo (testMake testModel)"
    And I see in saved value field "vehicle_url" filled with "Vehicle page: https://url/client/fleet/47/specification"
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/recipient" filled with "string(1)"
    And I see field "data/0/subject" filled with "Alerts - Vehicle changed status: offline"
    Then I should get an email with field "data/0/message" containing to template:
    """
    Vehicle ${reg_no} has changed status: ${status}. ${comment}
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
    Vehicle ${reg_no} has changed status: ${status}. ${comment}
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
    And I see field "data/0/subject" filled with "Alerts - Vehicle changed status: offline"
    Then I should get an email with field "data/0/message" containing to template:
    """
    <h3>New notification from the ${from_company} system:</h3>
    <p>Vehicle ${reg_no_with_model} status changed to \"${status}\".</p><br/>
    <p><b>Detailed information:</b></p>
    <p>${event_time} - Vehicle ${reg_no_with_model} with driver ${driver} changed status to \"${status}\"</p>
    <p>${vehicle_url}</p><br/>
    <p>${comment}</p>
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "vehicle"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 1
    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"
    And I see field "data/0/event/eventId"
    And I see field "data/0/event/eventLogId"

  Scenario: I want check notification by vehicle unavailable
    Then I want fill "title" field with "Test# - vehicle unavailable"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "teamId" field with 2
    And I want fill "eventId" field with "event(VEHICLE_UNAVAILABLE, user)"
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
    Given I signed in as "admin" team "client" and teamId 2
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
#    And I want fill "name" field with "test group"
#    And I want to create vehicle group and save id
#    And I want fill vehicle group id
    And I want fill "type" field with "Car"
    And I want fill "makeModel" field with "testModel"
    And I want fill "make" field with "testMake"
    And I want fill "available" field with true
    And I want fill "regNo" field with "testRegNo"
    And I want fill "defaultLabel" field with "defaultLabel"
    And I want fill "vin" field with "vin"
    And I want fill "regCertNo" field with "regCertNo"
    And I want fill "enginePower" field with 2.0
    And I want fill "engineCapacity" field with 1.0
    And I want fill "fuelType" field with 1
    And I want fill "emissionClass" field with "emissionClass"
    And I want fill "co2Emissions" field with 0.1
    And I want fill "grossWeight" field with 0.2
    And I want fill "status" field with "inactive"
    Then I want to create vehicle and save id
    And response code is 200
    Then I want clean filled data
    And I want fill "name" field with "driverName"
    And I want fill "surname" field with "driverSurname"
    And I want fill "phone" field with "+46 (87)4646753"
    And I want fill "email" field with "driver@example.com"
    Then I want to register driver for current client
    And I see field "email" filled with "driver@example.com"
    Then I want to set vehicle driver by user email "driver@example.com" and date "2020-10-20 08:19:38"
    And I want clean filled data
    And I want fill "name" field with "test group1"
    And I want to create vehicle group and save id
    And I want fill vehicle group id
    And I want fill "type" field with "Car"
    And I want fill "regNo" field with "regNo2"
    And I want fill "defaultLabel" field with "defaultLabel2"
    And I want fill "vin" field with "vin2"
    And I want fill "regCertNo" field with "regCertNo2"
    And I want fill "enginePower" field with 3.0
    And I want fill "engineCapacity" field with 2.0
    And I want fill "fuelType" field with 1
    And I want fill "emissionClass" field with "emissionClass2"
    And I want fill "co2Emissions" field with 0.2222
    And I want fill "grossWeight" field with 0.3333
    And I want fill "status" field with "inactive"
    And I want fill "averageFuel" field with 22.2
    When I want upload file "test_image" "png" "975" "image/png"
    Then I want to edit vehicle by saved id
    And response code is 200
    Then I want clean files data
    And I see field "team/type" filled with "client"
#    And I see field "type" filled with "type2"
    And I see field "regNo" filled with "regNo2"
    And I see field "defaultLabel" filled with "defaultLabel2"
    And I see field "vin" filled with "vin2"
    And I see field "regCertNo" filled with "regCertNo2"
    And I see field "enginePower" filled with 3.0
    And I see field "engineCapacity" filled with 2.0
    And I see field "fuelType" filled with 1
    And I see field "emissionClass" filled with "emissionClass2"
    And I see field "co2Emissions" filled with 0.2222
    And I see field "grossWeight" filled with 0.3333
    And I see field "averageFuel" filled with 22.2
    And I see field "status" filled with "inactive"
    And I want check "picture" is not empty
    Then I want clean filled data
    And I want fill teamId by saved clientId
    And I want fill "isUnavailable" field with true
    And I want fill "unavailableMessage" field with "testMessage"
    Then I want to edit vehicle by saved id
    And I want send messages in queue notification_events
    And I see field "status" filled with "unavailable"
    And I see field "unavailableMessage" filled with "testMessage"
    Then I want clean filled data
    And response code is 200
    Then I want clean filled data
#    Then I want sleep on 1 seconds
    Then I signed with email "linxio-dev@ocsico.com"
    Given Elastica populate index "eventLog"
    And I want fill "eventId" field with "event(VEHICLE_UNAVAILABLE, user)"
    Then I want get event log
    Then response code is 200
    And I see field "data/0/eventName" filled with "VEHICLE_UNAVAILABLE"
    And I see field "data/0/formattedDate"
    And I see field "data/0/importance" filled with "normal"
    And I see field "data/0/eventTeam" filled with "client-name-0"
    And I see field "data/0/triggeredBy" filled with "user"
    And I see field "data/0/triggeredDetails" filled with "admin@user.com"
  #    And I see field "data/0/eventSourceType" filled with "vehicle"
    And I see field "data/0/eventSource/name" filled with "regNo2"
    And I see field "data/0/eventSource/type" filled with "vehicle"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I see field "data/0/eventSource/vehicleId"
    And I see field "data/0/notificationsList/0/id"
    And I see field "data/0/notificationsList/0/title" filled with "Test# - vehicle unavailable"
    And I see field "data/0/notificationsList/0/eventId"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I want get event object to save
    And I want clean filled data
    And I want check EntityPlaceholderService
    And I see in saved value field "from_company" filled with "Linxio"
    And I see in saved value field "reg_no" filled with "regNo2"
    And I see in saved value field "model" filled with "testMake testModel"
    And I see in saved value field "team" filled with "client-name-0"
    And I see in saved value field "triggered_by" filled with "test user surname"
    # Fix context
    And I see in saved value field "driver" filled with "driverName driverSurname"
    And I see in saved value field "status" filled with "unavailable"
    And I see in saved value field "event_time"
    And I see in saved value field "reg_no_with_model" filled with "regNo2 (testMake testModel)"
    And I see in saved value field "vehicle_url" filled with "Vehicle page: https://url/client/fleet/47/specification"
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/recipient" filled with "string(1)"
    And I see field "data/0/subject" filled with "Alerts - Vehicle changed status: unavailable"
    Then I should get an email with field "data/0/message" containing to template:
    """
    Vehicle ${reg_no} has changed status: ${status}. ${comment}
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
    Vehicle ${reg_no} has changed status: ${status}. ${comment}
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
    And I see field "data/0/subject" filled with "Alerts - Vehicle changed status: unavailable"
    Then I should get an email with field "data/0/message" containing to template:
    """
    <h3>New notification from the ${from_company} system:</h3>
    <p>Vehicle ${reg_no_with_model} status changed to \"${status}\".</p><br/>
    <p><b>Detailed information:</b></p>
    <p>${event_time} - ${triggered_by} changed status for vehicle ${reg_no_with_model} with driver ${driver} to \"${status}\"</p>
    <p>${vehicle_url}</p><br/>
    <p>${comment}</p>
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "vehicle"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 1
    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"
    And I see field "data/0/event/eventId"
    And I see field "data/0/event/eventLogId"

  Scenario: I want check notification by vehicle deleted
    Then I want fill "title" field with "Test# - vehicle deleted"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "teamId" field with 2
    And I want fill "eventId" field with "event(VEHICLE_DELETED, user)"
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
    Given I signed in as "admin" team "client" and teamId 2
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
#    And I want fill "name" field with "test group"
#    And I want to create vehicle group and save id
#    And I want fill vehicle group id
    And I want fill "type" field with "Car"
    And I want fill "makeModel" field with "testModel"
    And I want fill "make" field with "testMake"
    And I want fill "available" field with true
    And I want fill "regNo" field with "testRegNo"
    And I want fill "defaultLabel" field with "defaultLabel"
    And I want fill "vin" field with "vin"
    And I want fill "regCertNo" field with "regCertNo"
    And I want fill "enginePower" field with 2.0
    And I want fill "engineCapacity" field with 1.0
    And I want fill "fuelType" field with 1
    And I want fill "emissionClass" field with "emissionClass"
    And I want fill "co2Emissions" field with 0.1
    And I want fill "grossWeight" field with 0.2
    And I want fill "status" field with "online"
    Then I want to create vehicle and save id
    And response code is 200
    Then I want to delete vehicle by saved id
    And I want send messages in queue notification_events
    And response code is 204
    When I want to get vehicle by saved id
    And I see field "status" filled with "deleted"
    And response code is 200
    Then I want clean filled data
#    Then I want sleep on 1 seconds
    Then I signed with email "linxio-dev@ocsico.com"
    Given Elastica populate index "eventLog"
#    And I want fill "eventId" field with "event(VEHICLE_DELETED, user)"
    Then I want get event log
    Then response code is 200
    And I see field "data/0/eventName" filled with "VEHICLE_DELETED"
    And I see field "data/0/formattedDate"
    And I see field "data/0/importance" filled with "normal"
    And I see field "data/0/eventTeam" filled with "client-name-0"
    And I see field "data/0/triggeredBy" filled with "user"
    And I see field "data/0/triggeredDetails" filled with "admin@user.com"
#    And I see field "data/0/eventSourceType" filled with "vehicle"
    And I see field "data/0/eventSource/name" filled with "testRegNo"
    And I see field "data/0/eventSource/type" filled with "vehicle"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I see field "data/0/eventSource/vehicleId"
    And I see field "data/0/notificationsList/0/id"
    And I see field "data/0/notificationsList/0/title" filled with "Test# - vehicle deleted"
    And I see field "data/0/notificationsList/0/eventId"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I want get event object to save
    And I want clean filled data
    And I want check EntityPlaceholderService
    And I see in saved value field "from_company" filled with "Linxio"
    And I see in saved value field "reg_no" filled with "testRegNo"
    And I see in saved value field "model" filled with "testMake testModel"
    And I see in saved value field "team" filled with "client-name-0"
    And I see in saved value field "triggered_by" filled with "test user surname"
    And I see in saved value field "event_time"
    And I see in saved value field "reg_no_with_model" filled with "testRegNo (testMake testModel)"
    And I see in saved value field "vehicle_url" filled with "Vehicle page: https://url/client/fleet/47/specification"
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/recipient" filled with "string(1)"
    And I see field "data/0/subject" filled with "Alerts - Vehicle deleted"
    Then I should get an email with field "data/0/message" containing to template:
    """
    Vehicle ${reg_no} has changed status: ${status}. ${comment}
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
    Vehicle ${reg_no} has changed status: ${status}. ${comment}
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
    And I see field "data/0/subject" filled with "Alerts - Vehicle deleted"
    Then I should get an email with field "data/0/message" containing to template:
    """
    <h3>New notification from the ${from_company} system:</h3>
    <p>Vehicle ${reg_no_with_model} status changed to \"${status}\".</p><br/>
    <p><b>Detailed information:</b></p>
    <p>${event_time} - ${triggered_by} changed status for vehicle ${reg_no_with_model} with driver ${driver} to \"${status}\"</p>
    <p>${vehicle_url}</p><br/>
    <p>${comment}</p>
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "vehicle"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 1
    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"
    And I see field "data/0/event/eventId"
    And I see field "data/0/event/eventLogId"

  Scenario: I want check notification by vehicle overspeeding
    Given I want handle overSpeeding event
    Then I want fill "title" field with "Test# - vehicle overspeeding"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "teamId" field with 2
    And I want fill "eventId" field with "event(VEHICLE_OVERSPEEDING, user)"
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
    And I want fill "additionalParams.overSpeed" field with 80
    Then I want create notification
    Then response code is 200
    Then I want get last notification
    And I see field "listenerTeamId" filled with 2
    And I see field "ownerTeamId" filled with 1
    And I see field "additionalParams/overSpeed" filled with "string(80)"
    And I want fill "makeModel" field with "testModel"
    And I want fill "make" field with "testMake"
    And I want fill "type" field with "Car"
    And I want fill "vin" field with "1234567890Ab"
    And I want fill "fuelType" field with "1"
    And I want fill "year" field with "2000"
    And I want fill "ecoSpeed" field with "85"
    And I want fill "defaultLabel" field with "Default label"
    And I want fill "regNo" field with "RG1234567890Ab"
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
    And I want to create device for vehicle and save id
    And I want install device for vehicle
    And I want install device for vehicle
    And response code is 200
    And I want set vehicle driver with user of current team with date "2021-01-29 08:19:38"
    Then I want change installed device date for saved vehicle to '2021-01-01T18:55:30+00:00'
    Given There are following tracker payload from topflytech tracker, replace date now true, offset "80", length "6", with socket "test-topflytech-test-socket":
      | payload                                                                                                                                                                                                                                                              |
      | 26260200530259088888888888888800780e1014012c0029c9c02005010000402000000003219e0021092210382600008e422d06114370f116c200100008120000200000bc1b000008de039c066676430a58ff   |
      | 26260200530259088888888888888800780e1014012c0029c9c02005010000402000000003219e0021092211382600008e422d06114370f116c200100008120000200000bc1b000008de039c066676430a58ff   |
      | 26260200530259088888888888888800780e1014012c0029c9c02005010000402000000003219e0021092212382600008e422d06114370f116c200100008120000200000bc1b000008de039c066676430a58ff   |
    Given Calculate routes
    And I want send messages in queue notification_post_handle
    And I want send messages in queue notification_events
#    Then I want change installed device date for saved vehicle to '2019-01-01T18:55:30+00:00'
#    And I want fill "payload" field with "000F383838383838383838383838383838"
#    And I want to send teltonika tcp data to api with socket "test-socket-id"
#    And I want fill "payload" field with "000000000000016b08050000017105c12bf00056a7d94ce9608d7400a00029100057001008ef01f00150011503c80045011e00255907b50009b600064235ce180057430faf44000831ffff011002fefb9b000000017105c13b900056a7f0eee960a931009e001c0f0057001008ef01f00150011503c80045011e00255907b5000bb600074235ed180057430fb044000831ffff011002fefbfb000000017105c14b300056a80135e960ca43009d00100f0058001008ef01f00150011503c80045011e00255907b5000fb600074235fa180058430faf44000831ffff011002fefc60000000017105c156e80056a80700e960e4d3009b00060f005a001008ef01f00150011503c80045011e00255b07b5000bb600064235ee18005a430faf44000831ffff011002fefcad000000017105c166880056a80732e9610916009c016210005d001008ef01f00150011502c80045011e00255d07b5000ab600064235f918005d430faf44000831ffff011002fefd1300050000ef8c"
#    And I want to send teltonika tcp data to api with socket "test-socket-id"
#    And I want fill "payload" field with "000000000000016b08050000017105c32f900056aa6090e962dad800cb004411005f001008ef01f00150011503c80045011e00256007b5000ab600064235cf18005f430fb244000831ffff011002ff0987000000017105c33f300056aa8bece962eaec00cc003b11005e001008ef01f00150011502c80045011e00255f07b5000ab600064235da18005e430faf44000831ffff011002ff09f3000000017105c34ed00056aab0d6e963003600d2003210005b001008ef01f00150011502c80045011e00255d07b5000ab600064235f218005b430faf44000831ffff011002ff0a59000000017105c35e700056aad131e9631aa500d500290f005a001008ef01f00150011502c80045011e00255c07b5000ab6000742360318005a430faf44000831ffff011002ff0ac2000000017105c366400056aade58e96327fe00d90026100055001008ef01f00150011502c80045011e00255807b5000ab600064235f5180055430faf44000831ffff011002ff0af500050000c625"
#    And I want to send teltonika tcp data to api with socket "test-socket-id"
#    And I want send messages in queue notification_events
#    Then I want clean filled data
#    Then I want sleep on 1 seconds
    Then I signed with email "linxio-dev@ocsico.com"
    Given Elastica populate index "eventLog"
    And I want fill "eventId" field with "event(VEHICLE_OVERSPEEDING, user)"
    Then I want get event log
    Then response code is 200
    And I see field "data/0/eventName" filled with "VEHICLE_OVERSPEEDING"
    And I see field "data/0/formattedDate"
    And I see field "data/0/importance" filled with "normal"
    And I see field "data/0/eventTeam" filled with "client-name-0"
    And I see field "data/0/triggeredBy" filled with "driver"
    And I see field "data/0/triggeredDetails" filled with "driver@example.com"
#    And I see field "data/0/eventSourceType" filled with "device"
    And I see field "data/0/eventSource/name" filled with "RG1234567890Ab"
    And I see field "data/0/eventSource/type" filled with "device"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I see field "data/0/eventSource/trackerhistoryId"
    And I see field "data/0/eventSource/deviceId"
    And I see field "data/0/shortDetails/lastCoordinates"
    And I see field "data/0/shortDetails/address"
    And I see field "data/0/shortDetails/areas"
    And I see field "data/0/shortDetails/deviceImei" filled with "string(888888888888888)"
    And I see field "data/0/shortDetails/vehicleRegNo" filled with "RG1234567890Ab"
    And I see field "data/0/shortDetails/maxSpeed" filled with "87"
    And I see field "data/0/shortDetails/limit/0/overSpeed" filled with "string(80)"
    And I see field "data/0/notificationsList/0/id"
    And I see field "data/0/notificationsList/0/title" filled with "Test# - vehicle overspeeding"
    And I see field "data/0/notificationsList/0/eventId"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I want get event object to save
    And I want clean filled data
    And I want check EntityPlaceholderService
    And I see in saved value field "from_company" filled with "Linxio"
    And I see in saved value field "reg_no" filled with "testRegNo"
    And I see in saved value field "model" filled with "testMake testModel"
    And I see in saved value field "team" filled with "client-name-0"
    And I see in saved value field "triggered_by" filled with "test user surname"
    And I see in saved value field "event_time"
    And I see in saved value field "reg_no_with_model" filled with "testRegNo (testMake testModel)"
    And I see in saved value field "vehicle_url" filled with "Vehicle page: https://url/client/fleet/47/specification"
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/recipient" filled with "string(1)"
    And I see field "data/0/subject" filled with "Alerts - Overspeeding"
    Then I should get an email with field "data/0/message" containing to template:
    """
    Vehicle ${reg_no} has changed status: ${status}. ${comment}
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
    Vehicle ${reg_no} has changed status: ${status}. ${comment}
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
    And I want fill "transport" field with "email"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "email"
    And I see field "data/0/recipient" filled with "linxio-dev@ocsico.com"
    And I see field "data/0/subject" filled with "Alerts - Overspeeding"
    Then I should get an email with field "data/0/message" containing to template:
    """
    <h3>New notification from the ${from_company} system:</h3>
    <p>Vehicle ${reg_no_with_model} status changed to \"${status}\".</p><br/>
    <p><b>Detailed information:</b></p>
    <p>${event_time} - ${triggered_by} changed status for vehicle ${reg_no_with_model} with driver ${driver} to \"${status}\"</p>
    <p>${vehicle_url}</p><br/>
    <p>${comment}</p>
    """
#    Then I should get an email with field "data/0/message" containing:
#    """
#    <h3>New notification from the Linxio system:</h3>
#    <p>Vehicle RG1234567890Ab driving >87km/h.</p><br/>
#    <p><b>Detailed information:</b></p>
#    <p>2020-01-01 01:00 - Vehicle RG1234567890Ab (testMake testModel) with driver client driver name client driver surname driving more than 87km/h, speed limit is set to 80km/h</p>
#    <p>Vehicle page: https://url/client/fleet/47/specification</p>
#    <p>Driver page: https://url/client/drivers/44/profile-info</p><br/>
#    <p></p>
#    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "device"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 1
    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"
    And I see field "data/0/event/eventId"
    And I see field "data/0/event/eventLogId"