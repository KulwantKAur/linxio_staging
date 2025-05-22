Feature: Service Records Events

  Background:
    Given I signed in as "super_admin" team "admin"
    And the queue associated to notification.events events producer is empty

  Scenario: I want check notification by service record added
    Then I want fill "title" field with "Test# service record added"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "eventId" field with "event(SERVICE_RECORD_ADDED, user)"
    And I want fill "scope.subtype" field with "any"
    And I want fill "scope.value.0" field with null
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
    And I want fill "teamId" field with 2
    Then I want create notification
    Then response code is 200
    Then I want get last notification
    And I see field "listenerTeamId" filled with 2
    And I see field "ownerTeamId" filled with 1
    And I want clean filled data
    Given I signed in as "admin" team "client" and teamId 2
    When I want get client by name "client-name-0" and save id
    And I want fill "model" field with "Test model"
    And I want fill "type" field with "Car"
    And I want fill "vin" field with "1234567890Ab"
    And I want fill "fuelType" field with "1"
    And I want fill "year" field with "2000"
    And I want fill "ecoSpeed" field with "85"
    And I want fill "defaultLabel" field with "Default label"
    And I want fill "regNo" field with "TEST REGNO"
    And I want fill "makeModel" field with "testModel"
    And I want fill "make" field with "testMake"
    And I want fill "status" field with "active"
    And I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want to create vehicle and save id
    And response code is 200
    And I want clean filled data
    Then I want fill "title" field with "test reminder"
    And I want fill vehicle id
    And I want fill "status" field with "active"
    And I want fill "date" field with "2020-10-10 12:12:12"
    And I want fill "datePeriod" field with "15"
    And I want fill "dateNotification" field with "5"
    And I want fill "mileage" field with "70000"
    And I want fill "mileagePeriod" field with "10000"
    And I want fill "mileageNotification" field with "1000"
    And I want fill "hours" field with "9000"
    And I want fill "hoursPeriod" field with "250"
    And I want fill "hoursNotification" field with "50"
    And I want fill "note" field with "text note"
    Then I want to create reminder and save id
    And response code is 200
    And I see field "title" filled with "test reminder"
    And I want clean filled data
    Then I want fill "date" field with "2020-10-10 10:10:10"
    And I want fill "note" field with "test note"
    And I want fill "cost" field with "2000"
    And I want fill "status" field with "active"
    And I want upload file
    And I want to create service record for saved reminder and save id
    And I want send messages in queue notification_events
    And response code is 200
    And I see field "note" filled with "test note"
    And I see field "formattedDate" filled with "10/10/2020 10:10"
    And I see field "cost" filled with "string(2000)"
    And I see field "status" filled with "active"
    And I see field "files/0/id"
    And I want clean filled data
#    Then I want sleep on 1 seconds
    Then I signed with email "linxio-dev@ocsico.com"
    Given Elastica populate index "eventLog"
    And I want fill "eventId" field with "event(SERVICE_RECORD_ADDED, user)"
    Then I want get event log
    Then response code is 200
    And I see field "data/0/eventName" filled with "SERVICE_RECORD_ADDED"
    And I see field "data/0/formattedDate"
    And I see field "data/0/importance" filled with "normal"
    And I see field "data/0/eventTeam" filled with "client-name-0"
    And I see field "data/0/triggeredBy" filled with "user"
    And I see field "data/0/triggeredDetails" filled with "admin@user.com"
#    And I see field "data/0/eventSourceType" filled with "servicerecord"
    And I see field "data/0/eventSource/name" filled with "test reminder"
    And I see field "data/0/eventSource/type" filled with "reminder"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I see field "data/0/eventSource/vehicleId"
    And I see field "data/0/eventSource/servicerecordId"
    And I see field "data/0/shortDetails/vehicleRegNo"
    And I see field "data/0/notificationsList/0/id"
    And I see field "data/0/notificationsList/0/title" filled with "Test# service record added"
    And I see field "data/0/notificationsList/0/eventId"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I want get event object to save
    And I want clean filled data
    And I want check EntityPlaceholderService
    And I see in saved value field "from_company" filled with "Linxio"
    And I see in saved value field "reg_no" filled with "TEST REGNO"
    And I see in saved value field "model" filled with "testMake testModel"
    And I see in saved value field "team" filled with "client-name-0"
    And I see in saved value field "status" filled with "active"
    And I see in saved value field "title" filled with "test reminder"
    And I see in saved value field "entity" filled with "TEST REGNO"
    And I see in saved value field "triggered_by" filled with "test user surname"
    And I see in saved value field "event_time"
    And I see in saved value field "data_url" filled with "Service reminder page: https://url/client/fleet/47/service-reminders/1"
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/recipient" filled with "string(1)"
    And I see field "data/0/subject" filled with "Alerts - Service record added"
    Then I should get an email with field "data/0/message" containing to template:
    """
    Service record added: ${title}. ${comment}
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "reminder"
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
    Service record added: ${title}. ${comment}
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "reminder"
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
    And I see field "data/0/subject" filled with "Alerts - Service record added"
    Then I should get an email with field "data/0/message" containing to template:
    """
    <h3>New notification from the ${from_company} system:</h3>
    <p>Service record added (${entity}).</p><br/>
    <p><b>Detailed information:</b></p>
    <p>${event_time} - ${triggered_by} have added new service record \"${title}\" (${entity}).</p>
    <p>${data_url}</p><br/>
    <p>${comment}</p>
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "reminder"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 1
    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"
    And I see field "data/0/event/eventId"
    And I see field "data/0/event/eventLogId"

  Scenario: I want check notification by service repair added
    Then I want fill "title" field with "Test# service repair added"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "eventId" field with "event(SERVICE_REPAIR_ADDED, user)"
    And I want fill "scope.subtype" field with "any"
    And I want fill "scope.value.0" field with null
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
    And I want fill "teamId" field with 2
    Then I want create notification
    Then response code is 200
    Then I want get last notification
    And I see field "listenerTeamId" filled with 2
    And I see field "ownerTeamId" filled with 1
    And I want clean filled data
    Given I signed in as "admin" team "client" and teamId 2
    When I want get client by name "client-name-0" and save id
    And I want fill "model" field with "Test model"
    And I want fill "type" field with "Car"
    And I want fill "vin" field with "1234567890Ab"
    And I want fill "fuelType" field with "1"
    And I want fill "year" field with "2000"
    And I want fill "ecoSpeed" field with "85"
    And I want fill "defaultLabel" field with "Default label"
    And I want fill "regNo" field with "TEST REGNO"
    And I want fill "makeModel" field with "testModel"
    And I want fill "make" field with "testMake"
    And I want fill "status" field with "active"
    And I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want to create vehicle and save id
    And response code is 200
    And I want clean filled data
    And I want fill vehicle id
    Then I want fill "date" field with "2020-10-10 10:10:10"
    And I want fill "note" field with "test note"
    And I want fill "cost" field with "2000"
    And I want fill "status" field with "active"
    And I want fill "title" field with "test repair"
    And I want upload file
    And I want to create repair and save id
    And I want send messages in queue notification_events
    And response code is 200
    And I see field "note" filled with "test note"
    And I see field "formattedDate" filled with "10/10/2020 10:10"
    And I see field "cost" filled with "string(2000)"
    And I see field "status" filled with "active"
    And I see field "repairTitle" filled with "test repair"
    And I see field "files/0/id"
    And I want clean filled data
    Then I signed with email "linxio-dev@ocsico.com"
    Given Elastica populate index "eventLog"
    And I want fill "eventId" field with "event(SERVICE_REPAIR_ADDED, user)"
    Then I want get event log
    Then response code is 200
    And I see field "data/0/eventName" filled with "SERVICE_REPAIR_ADDED"
    And I see field "data/0/formattedDate"
    And I see field "data/0/importance" filled with "normal"
    And I see field "data/0/eventTeam" filled with "client-name-0"
    And I see field "data/0/triggeredBy" filled with "user"
    And I see field "data/0/triggeredDetails" filled with "admin@user.com"
#    And I see field "data/0/eventSourceType" filled with "servicerecord"
    And I see field "data/0/eventSource/name" filled with "test repair"
    And I see field "data/0/eventSource/type" filled with "reminder"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I see field "data/0/eventSource/vehicleId"
    And I see field "data/0/eventSource/servicerecordId"
    And I see field "data/0/shortDetails/vehicleRegNo" filled with "TEST REGNO"
    And I see field "data/0/notificationsList/0/id"
    And I see field "data/0/notificationsList/0/title" filled with "Test# service repair added"
    And I see field "data/0/notificationsList/0/eventId"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I want get event object to save
    And I want clean filled data
    And I want check EntityPlaceholderService
    And I see in saved value field "from_company" filled with "Linxio"
    And I see in saved value field "reg_no" filled with "TEST REGNO"
    And I see in saved value field "model" filled with "testMake testModel"
    And I see in saved value field "status" filled with "active"
    And I see in saved value field "title" filled with "test repair"
    And I see in saved value field "entity" filled with "TEST REGNO"
    And I see in saved value field "triggered_by" filled with "test user surname"
    And I see in saved value field "event_time"
    And I see in saved value field "data_url" filled with "Service repair page: https://url/client/fleet/47/repair-costs/1"
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/recipient" filled with "string(1)"
    And I see field "data/0/subject" filled with "Alerts - Service repair added"
    Then I should get an email with field "data/0/message" containing to template:
    """
    Service repair added: ${title}. ${comment}
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "reminder"
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
    Service repair added: ${title}. ${comment}
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "reminder"
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
    And I see field "data/0/subject" filled with "Alerts - Service repair added"
    Then I should get an email with field "data/0/message" containing to template:
    """
    <h3>New notification from the ${from_company} system:</h3>
    <p>Service repair added (${entity}).</p><br/>
    <p><b>Detailed information:</b></p>
    <p>${event_time} - ${triggered_by} have added new service repair \"${title}\" (${entity}).</p>
    <p>${data_url}</p><br/>
    <p>${comment}</p>
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "reminder"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 1
    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"
    And I see field "data/0/event/eventId"
    And I see field "data/0/event/eventLogId"
