Feature: Documents Events

  Background:
    Given I signed in as "super_admin" team "admin"
    And the queue associated to notification.events events producer is empty

  Scenario: I want check notification by vehicle document expire soon
    Then I want fill "title" field with "Test#-document expire soon"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "teamId" field with 2
    And I want fill "eventId" field with "event(DOCUMENT_EXPIRE_SOON, user)"
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
    And I see field "listenerTeamId" filled with 2
    And I see field "ownerTeamId" filled with 1
    And I want clean filled data
    Given I signed in as "admin" team "client" and teamId 2
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
#    And I want fill "name" field with "test group"
#    And I want to create vehicle group and save id
#    And I want fill vehicle group id
    And I want fill "type" field with "Car"
    And I want fill "model" field with "model"
    And I want fill "available" field with true
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
    And I want fill "status" field with "online"
    And I want fill "makeModel" field with "testModel"
    And I want fill "make" field with "testMake"
    Then I want to create vehicle and save id
    And response code is 200
    And I want clean filled data
    Then I want fill vehicle id
    And I want fill "title" field with "Test Document #1"
    Then I want create document
    And response code is 200
    Then I want upgrade document
    And response code is 400
    And I see field "errors/0/detail/issueDate/required" filled with "Required field"
    And I see field "errors/0/detail/files/required" filled with "Required field"
    Then I want clean filled data
    And I want fill "issueDate" field with "2021-10-10T10:10:10+00:00"
    When I want upload file "test_image" "png" "975" "image/png"
    Then I want update document
    And response code is 200
    Then I want clean files data
    Then I want upgrade document
    And response code is 200
    And I see field "title" filled with "Test Document #1"
    And I see field "status" filled with "active"
    And I see field "draft/issueDate" filled with null
    And I see field "records/0/issueDate" filled with "2021-10-10T10:10:10+00:00"
    And I see field "records/0/files/0/displayName" filled with "test_image_test.png"
    Then I want clean filled data
    And I want fill "title" field with "Update#1 Test Document #1"
    And I want fill "issueDate" field with "2020-10-10T10:10:10+00:00"
    And I want set modified date "expDate" "-10 days"
    And I want fill "notifyBefore" field with "1"
    And I want fill "cost" field with "10.12"
    When I want upload file "test_image" "png" "975" "image/png"
    Then I want update document
    Then I want clean files data
    And response code is 200
    Then I want upgrade document
    And response code is 200
    And I see field "title" filled with "Update#1 Test Document #1"
    And I see field "status" filled with "expired"
    And I see field "draft/issueDate" filled with null
    And I see field "notifyBefore" filled with 1
    And I see field "draft/cost" filled with null
    And I see field "records/0/issueDate" filled with "2020-10-10T10:10:10+00:00"
    And I see field "records/0/cost" filled with "10.12"
    And I see field "records/0/files/0/displayName" filled with "test_image_test.png"
    And I see field "records/1/issueDate" filled with "2021-10-10T10:10:10+00:00"
    And I see field "records/1/files/0/displayName" filled with "test_image_test.png"
    Then I want get documents list
    And response code is 200
    And I see field "total" filled with 1
    And I see field "data/0/title" filled with "Update#1 Test Document #1"
    And I see field "data/0/status" filled with "expired"
    And I see field "data/0/issueDate" filled with "2020-10-10T10:10:10+00:00"
    And I see field "data/0/expDate"
    And I see field "data/0/remainingDays"
    Then I want clean filled data
    And I want fill "title" field with "Update#2 Test Document #1"
    And I want fill issueDate field with "2019-10-10T10:10:10+00:00"
    And I want set modified date "expDate" "+5 days"
    And I want fill "notifyBefore" field with "6"
    And I want fill "cost" field with "5"
    When I want upload file "test_image" "png" "975" "image/png"
    Then I want update document
    Then I want clean files data
    And response code is 200
    Then I want upgrade document
    And I want send messages in queue notification_events
    And response code is 200
    And I see field "title" filled with "Update#2 Test Document #1"
    And I see field "status" filled with "expire_soon"
    And I see field "draft/issueDate" filled with null
    And I see field "notifyBefore" filled with 6
    And I see field "draft/cost" filled with null
    And I see field "records/0/issueDate" filled with "2019-10-10T10:10:10+00:00"
    And I see field "records/0/cost" filled with "5"
    And I see field "records/0/files/0/displayName" filled with "test_image_test.png"
    And I see field "records/1/issueDate" filled with "2020-10-10T10:10:10+00:00"
    And I see field "records/1/cost" filled with "10.12"
    And I see field "records/1/files/0/displayName" filled with "test_image_test.png"
    And I see field "records/2/issueDate" filled with "2021-10-10T10:10:10+00:00"
    And I see field "records/2/files/0/displayName" filled with "test_image_test.png"
    Then I want get documents list
    And response code is 200
    And I see field "total" filled with 1
    And I see field "data/0/title" filled with "Update#2 Test Document #1"
    And I see field "data/0/status" filled with "expire_soon"
    And I see field "data/0/issueDate" filled with "2019-10-10T10:10:10+00:00"
    And I see field "data/0/expDate"
    And I see field "data/0/remainingDays"
#    Then I want sleep on 1 seconds
    Then I signed with email "linxio-dev@ocsico.com"
    Given Elastica populate index "eventLog"
    And I want fill "eventId" field with "event(DOCUMENT_EXPIRE_SOON, user)"
    Then I want get event log
    Then response code is 200
    And I see field "data/0/eventName" filled with "DOCUMENT_EXPIRE_SOON"
    And I see field "data/0/formattedDate"
    And I see field "data/0/importance" filled with "normal"
    And I see field "data/0/eventTeam" filled with "client-name-0"
    And I see field "data/0/triggeredBy" filled with null
    And I see field "data/0/triggeredDetails" filled with "admin@user.com"
#    And I see field "data/0/eventSourceType" filled with "document"
    And I see field "data/0/eventSource/name" filled with "regNo"
#    And I see field "data/0/eventSource/name" filled with "Update#2 Test Document #1"
    And I see field "data/0/eventSource/type" filled with "document"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I see field "data/0/eventSource/documentrecordId"
    And I see field "data/0/eventSource/documentId"
    And I see field "data/0/eventSource/vehicleId"
    And I see field "data/0/shortDetails/expiredDate"
    And I see field "data/0/shortDetails/title" filled with "Update#2 Test Document #1"
    And I see field "data/0/notificationsList/0/id"
    And I see field "data/0/notificationsList/0/title" filled with "Test#-document expire soon"
    And I see field "data/0/notificationsList/0/eventId"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I want get event object to save
    And I want clean filled data
    And I want check EntityPlaceholderService
    And I see in saved value field "from_company" filled with "Linxio"
    And I see in saved value field "reg_no" filled with "regNo"
    And I see in saved value field "model" filled with "testMake testModel"
    And I see in saved value field "team" filled with "client-name-0"
    And I see in saved value field "status" filled with "active"
    And I see in saved value field "title" filled with "Update#2 Test Document #1"
    And I see in saved value field "triggered_by" filled with "--"
    And I see in saved value field "triggered_by"
    And I see in saved value field "event_time" filled with "--"
    And I see in saved value field "data_url" filled with "Document page: https://url/client/fleet/47/documents/3"
    And I see in saved value field "data_by_type" filled with "(vehicle - regNo)"
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/recipient" filled with "string(1)"
    And I see field "data/0/subject" filled with "Alerts - Document expire soon"
    Then I should get an email with field "data/0/message" containing to template:
    """
    Document soon to expire: ${title}. ${comment}
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "document"
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
    Document soon to expire: ${title}. ${comment}
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "document"
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
    And I see field "data/0/subject" filled with "Alerts - Document expire soon"
    Then I should get an email with field "data/0/message" containing to template:
    """
    <h3>New notification from the ${from_company} system:</h3>
    <p>Document soon to expire ${data_by_type}.</p><br/>
    <p><b>Detailed information:</b></p>
    <p>${event_time} - document \"${title}\" ${data_by_type} will expire on ${expiration_date}.</p>
    <p>${data_url}</p><br/>
    <p>${comment}</p>
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "document"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 1
    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"
    And I see field "data/0/event/eventId"

  Scenario: I want check notification by vehicle document expired
    Then I want fill "title" field with "Test#-document expired"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "teamId" field with 2
    And I want fill "eventId" field with "event(DRIVER_DOCUMENT_EXPIRED, user)"
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
    And I see field "listenerTeamId" filled with 2
    And I see field "ownerTeamId" filled with 1
    And I want clean filled data
    Given I signed in as "admin" team "client" and teamId 2
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
#    And I want fill "name" field with "test group"
#    And I want to create vehicle group and save id
#    And I want fill vehicle group id
    And I want fill "type" field with "Car"
    And I want fill "model" field with "model"
    And I want fill "available" field with true
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
    And I want fill "status" field with "online"
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
    And I want clean filled data
    Then I want fill vehicle id
    And I want fill "title" field with "Test Document #1"
    Then I want create document
    And response code is 200
    Then I want upgrade document
    And response code is 400
    And I see field "errors/0/detail/issueDate/required" filled with "Required field"
    And I see field "errors/0/detail/files/required" filled with "Required field"
    Then I want clean filled data
    And I want fill "issueDate" field with "2021-10-10T10:10:10+00:00"
    When I want upload file "test_image" "png" "975" "image/png"
    Then I want update document
    And response code is 200
    Then I want clean files data
    Then I want upgrade document
    And response code is 200
    And I see field "title" filled with "Test Document #1"
    And I see field "status" filled with "active"
    And I see field "draft/issueDate" filled with null
    And I see field "records/0/issueDate" filled with "2021-10-10T10:10:10+00:00"
    And I see field "records/0/files/0/displayName" filled with "test_image_test.png"
    Given Elastica populate
    Then I want get documents list
    And response code is 200
    And I see field "total" filled with 1
    And I see field "data/0/title" filled with "Test Document #1"
    And I see field "data/0/status" filled with "active"
    And I see field "data/0/issueDate" filled with "2021-10-10T10:10:10+00:00"
    And I see field "data/0/expDate" filled with null
    And I see field "data/0/remainingDays" filled with null
    Then I want clean filled data
    And I want fill "title" field with "Update#1 Test Document #1"
    And I want fill "issueDate" field with "2020-10-10T10:10:10+00:00"
    And I want set modified date "expDate" "-10 days"
    And I want fill "notifyBefore" field with "1"
    And I want fill "cost" field with "10.12"
    When I want upload file "test_image" "png" "975" "image/png"
    Then I want update document
    And response code is 200
    Then I want clean files data
    Then I want upgrade document
    And I want send messages in queue notification_events
    And response code is 200
#    Then I want sleep on 1 seconds
    Then I signed with email "linxio-dev@ocsico.com"
    Given Elastica populate index "eventLog"
    And I want fill "eventId" field with "event(DRIVER_DOCUMENT_EXPIRED, user)"
    Then I want get event log
    Then response code is 200
    And I see field "data/0/eventName" filled with "DRIVER_DOCUMENT_EXPIRED"
    And I see field "data/0/formattedDate"
    And I see field "data/0/importance" filled with "normal"
    And I see field "data/0/eventTeam" filled with "client-name-0"
    And I see field "data/0/triggeredBy" filled with null
    And I see field "data/0/triggeredDetails" filled with "admin@user.com"
#    And I see field "data/0/eventSourceType" filled with "document"
    And I see field "data/0/eventSource/name" filled with "regNo"
#    And I see field "data/0/eventSource/name" filled with "Update#2 Test Document #1"
    And I see field "data/0/eventSource/type" filled with "document"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I see field "data/0/eventSource/documentrecordId"
    And I see field "data/0/eventSource/documentId"
    And I see field "data/0/eventSource/vehicleId"
    And I see field "data/0/shortDetails/expiredDate"
    And I see field "data/0/shortDetails/title" filled with "Update#1 Test Document #1"
    And I see field "data/0/notificationsList/0/id"
    And I see field "data/0/notificationsList/0/title" filled with "Test#-document expired"
    And I see field "data/0/notificationsList/0/eventId"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I want get event object to save
    And I want clean filled data
    And I want check EntityPlaceholderService
    And I see in saved value field "from_company" filled with "Linxio"
    And I see in saved value field "reg_no" filled with "regNo"
    And I see in saved value field "model" filled with "testMake testModel"
    And I see in saved value field "team" filled with "client-name-0"
    And I see in saved value field "status" filled with "active"
    And I see in saved value field "title" filled with "Update#1 Test Document #1"
    And I see in saved value field "triggered_by" filled with "--"
    And I see in saved value field "driver" filled with "--"
    And I see in saved value field "event_time" filled with "--"
    And I see in saved value field "data_url" filled with "Document page: https://url/client/fleet/47/documents/2"
    And I see in saved value field "data_by_type" filled with "(vehicle - regNo)"
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/recipient" filled with "string(1)"
    And I see field "data/0/subject" filled with "Alerts - Document expired"
    Then I should get an email with field "data/0/message" containing to template:
    """
    Document expired: ${title}. ${comment}
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "document"
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
    Document expired: ${title}. ${comment}
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "document"
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
    And I see field "data/0/subject" filled with "Alerts - Document expired"
    Then I should get an email with field "data/0/message" containing to template:
    """
    <h3>New notification from the ${from_company} system:</h3>
    <p>Document expired ${data_by_type}.</p><br/>
    <p><b>Detailed information:</b></p>
    <p>${event_time} - document expired \"${title}\" expired ${data_by_type}.</p>
    <p>${data_url}</p><br/>
    <p>${comment}</p>
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "document"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 1
    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"
    And I see field "data/0/event/eventId"
    And I see field "data/0/event/eventLogId"

  Scenario: I want check notification by delete vehicle document
    Then I want fill "title" field with "Test#-document deleted"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "teamId" field with 2
    And I want fill "eventId" field with "event(DOCUMENT_DELETED, user)"
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
    And I see field "listenerTeamId" filled with 2
    And I see field "ownerTeamId" filled with 1
    And I want clean filled data
    Given I signed in as "admin" team "client" and teamId 2
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
#    And I want fill "name" field with "test group"
#    And I want to create vehicle group and save id
#    And I want fill vehicle group id
    And I want fill "type" field with "Car"
    And I want fill "model" field with "model"
    And I want fill "available" field with true
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
    And I want fill "status" field with "online"
    And I want fill "makeModel" field with "testModel"
    And I want fill "make" field with "testMake"
    Then I want to create vehicle and save id
    And response code is 200
    Then I want clean filled data
    Then I want fill vehicle id
    And I want fill "title" field with "Test Document #1"
    Then I want create document
    And response code is 200
    And I want clean filled data
    Then I want delete document
    And I want send messages in queue notification_events
    And response code is 201
    Then I want get document
    And I see field "status" filled with "deleted"
#    Then I want sleep on 1 seconds
    Then I signed with email "linxio-dev@ocsico.com"
    Given Elastica populate index "eventLog"
    And I want fill "eventId" field with "event(DOCUMENT_DELETED, user)"
    Then I want get event log
    Then response code is 200
    And I see field "data/0/eventName" filled with "DOCUMENT_DELETED"
    And I see field "data/0/formattedDate"
    And I see field "data/0/importance" filled with "normal"
    And I see field "data/0/eventTeam" filled with "client-name-0"
    And I see field "data/0/triggeredBy" filled with "user"
    And I see field "data/0/triggeredDetails" filled with "admin@user.com"
#    And I see field "data/0/eventSourceType" filled with "document"
    And I see field "data/0/eventSource/name" filled with "Test Document #1"
    And I see field "data/0/eventSource/type" filled with "document"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I see field "data/0/eventSource/documentId"
    And I see field "data/0/eventSource/vehicleId"
    And I see field "data/0/notificationsList/0/id"
    And I see field "data/0/notificationsList/0/title" filled with "Test#-document deleted"
    And I see field "data/0/notificationsList/0/eventId"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I want get event object to save
    And I want clean filled data
    And I want check EntityPlaceholderService
    And I see in saved value field "from_company" filled with "Linxio"
    And I see in saved value field "reg_no" filled with "regNo"
    And I see in saved value field "model" filled with "testMake testModel"
    And I see in saved value field "team" filled with "client-name-0"
    And I see in saved value field "status" filled with "deleted"
    And I see in saved value field "title" filled with "Test Document #1"
    And I see in saved value field "triggered_by" filled with "test user"
    And I see in saved value field "driver" filled with "--"
    And I see in saved value field "event_time"
    And I see in saved value field "data_url" filled with "Document page: https://url/client/fleet/47/documents/1"
    And I see in saved value field "data_by_type" filled with "(vehicle - regNo)"
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/recipient" filled with "string(1)"
    And I see field "data/0/subject" filled with "Alerts - Document deleted"
    Then I should get an email with field "data/0/message" containing to template:
    """
    Document deleted: ${title}. ${comment}
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "document"
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
    Document deleted: ${title}. ${comment}
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "document"
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
    And I see field "data/0/subject" filled with "Alerts - Document deleted"
    Then I should get an email with field "data/0/message" containing to template:
    """
    <h3>New notification from the ${from_company} system:</h3>
    <p>Document deleted ${data_by_type}.</p><br/>
    <p><b>Detailed information:</b></p>
    <p>${event_time} - ${triggered_by} have deleted document \"${title}\" ${data_by_type}</p>
    <p>${data_url}</p><br/>
    <p>${comment}</p>
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "document"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 1
    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"
    And I see field "data/0/event/eventId"
    And I see field "data/0/event/eventLogId"

  Scenario: I want check notification by create Driver Document
    Then I want fill "title" field with "Test#-document record added"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "teamId" field with 11
    And I want fill "eventId" field with "event(DRIVER_DOCUMENT_RECORD_ADDED, user)"
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
    And I see field "listenerTeamId" filled with 11
    And I see field "ownerTeamId" filled with 1
    And I want clean filled data
    Given I signed in as "admin" team "client" and teamId 11
    When I want get client by name "ACME1" and save id
    And I want fill teamId by saved clientId
#    And I want fill "name" field with "test group"
#    And I want to create vehicle group and save id
#    And I want fill vehicle group id
    And I want fill "type" field with "Car"
    And I want fill "model" field with "model"
    And I want fill "available" field with true
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
    And I want fill "status" field with "online"
    And I want fill "makeModel" field with "testModel"
    And I want fill "make" field with "testMake"
    Then I want to create vehicle and save id
    And response code is 200
    Then I want clean filled data
    Then I want fill driver id of current team
    And I want fill "title" field with "Test Document #1"
    Then I want fill "issueDate" field with "2020-10-10T10:10:10+00:00"
    Then I want fill "expDate" field with "2020-10-10T10:10:10+00:00"
    Then I want fill "notifyBefore" field with "10"
    Then I want fill "cost" field with "10.12"
    When I want upload file "test_image" "png" "975" "image/png"
    Then I want create document
    Then I want clean files data
    And I want send messages in queue notification_events
    And response code is 200
    And I see field "draft/issueDate" filled with "2020-10-10T10:10:10+00:00"
    And I see field "draft/expDate" filled with "2020-10-10T10:10:10+00:00"
    And I see field "notifyBefore" filled with 10
    And I see field "draft/cost" filled with 10.12
    And I see field "draft/note" filled with null
    And I do not see field "records/0"
    And I see field "draft/files/0/id"
    And I see field "draft/files/0/displayName" filled with "test_image_test.png"
    Then I want clean filled data
#    Then I want sleep on 1 seconds
    Then I signed with email "linxio-dev@ocsico.com"
    Given Elastica populate index "eventLog"
    And I want fill "eventId" field with "event(DRIVER_DOCUMENT_RECORD_ADDED, user)"
    Then I want get event log
    Then response code is 200
    And I see field "data/0/eventName" filled with "DRIVER_DOCUMENT_RECORD_ADDED"
    And I see field "data/0/formattedDate"
    And I see field "data/0/importance" filled with "normal"
    And I see field "data/0/eventTeam" filled with "ACME1"
    And I see field "data/0/triggeredBy" filled with "user"
    And I see field "data/0/triggeredDetails" filled with "admin@user.com"
#    And I see field "data/0/eventSourceType" filled with "document"
    And I see field "data/0/eventSource/name"
#    And I see field "data/0/eventSource/name" filled with "alan.harrison@acme.local"
    And I see field "data/0/eventSource/type" filled with "driverDocument"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I see field "data/0/eventSource/documentrecordId"
    And I see field "data/0/eventSource/documentId"
    And I see field "data/0/eventSource/driverId"
    And I see field "data/0/shortDetails/expiredDate"
    And I see field "data/0/shortDetails/title" filled with "Test Document #1"
    And I see field "data/0/notificationsList/0/id"
    And I see field "data/0/notificationsList/0/title" filled with "Test#-document record added"
    And I see field "data/0/notificationsList/0/eventId"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I want get event object to save
    And I want clean filled data
    And I want check EntityPlaceholderService
    And I see in saved value field "from_company" filled with "Linxio"
    And I see in saved value field "reg_no" filled with "--"
    And I see in saved value field "model" filled with "--"
#    And I see in saved value field "team" filled with "client-name-0"
    And I see in saved value field "status" filled with "active"
#    ?? "--"
    And I see in saved value field "title" filled with "--"
    And I see in saved value field "triggered_by" filled with "test user surname"
    And I see in saved value field "event_time"
    And I see in saved value field "driver" filled with "Nikki Burns"
    And I see in saved value field "data_url" filled with "Document page: https://url/client/drivers/27/documents/1"
    And I see in saved value field "data_by_type" filled with "(driver - Nikki Burns)"
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/recipient" filled with "string(1)"
    And I see field "data/0/subject" filled with "Alerts - Document record added"
    Then I should get an email with field "data/0/message" containing to template:
    """
    Document record added: ${title}. ${comment}
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "document"
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
    Then I should get an email with field "data/0/message" containing to template:
    """
    Document record added: ${title}. ${comment}
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "document"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 10
    And I see field "data/0/event/entityTeam/clientName" filled with "ACME1"
    And I see field "data/0/event/eventId"
    And I see field "data/0/event/eventLogId"
    And I want clean filled data
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "email"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "email"
    And I see field "data/0/recipient" filled with "linxio-dev@ocsico.com"
    And I see field "data/0/subject" filled with "Alerts - Document record added"
    Then I should get an email with field "data/0/message" containing to template:
    """
    <h3>New notification from the ${from_company} system:</h3>
    <p>Document record added ${data_by_type}.</p><br/>
    <p><b>Detailed information:</b></p>
    <p>${event_time} - ${triggered_by} have added new record to the document \"${title}\" ${data_by_type}</p>
    <p>${data_url}</p><br/>
    <p>${comment}</p>
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "document"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 10
    And I see field "data/0/event/entityTeam/clientName" filled with "ACME1"
    And I see field "data/0/event/eventId"
    And I see field "data/0/event/eventLogId"

  Scenario: I want check notification by driver document expire soon
    Then I want fill "title" field with "Test#-document expire soon"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "teamId" field with 2
    And I want fill "eventId" field with "event(DRIVER_DOCUMENT_EXPIRE_SOON, user)"
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
    And I see field "listenerTeamId" filled with 2
    And I see field "ownerTeamId" filled with 1
    And I want clean filled data
    Given I signed in as "admin" team "client" and teamId 2
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
#    And I want fill "name" field with "test group"
#    And I want to create vehicle group and save id
#    And I want fill vehicle group id
    And I want fill "type" field with "Car"
    And I want fill "model" field with "model"
    And I want fill "available" field with true
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
    And I want fill "status" field with "online"
    And I want fill "makeModel" field with "testModel"
    And I want fill "make" field with "testMake"
    Then I want to create vehicle and save id
    And response code is 200
    And I want fill "name" field with "driverName"
    And I want fill "surname" field with "driverSurname"
    And I want fill "phone" field with "+46 (87)4646753"
    And I want fill "email" field with "driver@example.com"
    Then I want to register driver for current client
    And I see field "email" filled with "driver@example.com"
    Then I want to set vehicle driver by user email "driver@example.com" and date "2020-10-20 08:19:38"
    And I want clean filled data
    Then I want fill driver id of current team
    And I want fill "title" field with "Test Document #1"
    Then I want fill "issueDate" field with "2020-10-10T10:10:10+00:00"
    Then I want fill "expDate" field with "2020-10-10T10:10:10+00:00"
    Then I want fill "notifyBefore" field with "10"
    Then I want fill "cost" field with "10.12"
    When I want upload file "test_image" "png" "975" "image/png"
    Then I want create document
    Then I want clean files data
    And response code is 200
    Then I want upgrade document
    And response code is 200
    Then I want clean filled data
    And I want fill "issueDate" field with "2021-10-10T10:10:10+00:00"
    When I want upload file "test_image" "png" "975" "image/png"
    Then I want update document
    And response code is 200
    Then I want clean files data
    Then I want upgrade document
    And response code is 200
    And I see field "title" filled with "Test Document #1"
    And I see field "status" filled with "active"
    And I see field "draft/issueDate" filled with null
    And I see field "records/0/issueDate" filled with "2021-10-10T10:10:10+00:00"
    And I see field "records/0/files/0/displayName" filled with "test_image_test.png"
    Then I want clean filled data
    And I want fill "title" field with "Update#1 Test Document #1"
    And I want fill "issueDate" field with "2020-10-10T10:10:10+00:00"
    And I want set modified date "expDate" "-10 days"
    And I want fill "notifyBefore" field with "1"
    And I want fill "cost" field with "10.12"
    When I want upload file "test_image" "png" "975" "image/png"
    Then I want update document
    And response code is 200
    Then I want clean files data
    Then I want upgrade document
    And response code is 200
    And I see field "title" filled with "Update#1 Test Document #1"
    And I see field "status" filled with "expired"
    And I see field "draft/issueDate" filled with null
    And I see field "notifyBefore" filled with 1
    And I see field "draft/cost" filled with null
    And I see field "records/0/issueDate" filled with "2020-10-10T10:10:10+00:00"
    And I see field "records/0/cost" filled with "10.12"
    And I see field "records/0/files/0/displayName" filled with "test_image_test.png"
    And I see field "records/1/issueDate" filled with "2021-10-10T10:10:10+00:00"
    And I see field "records/1/files/0/displayName" filled with "test_image_test.png"
    Then I want clean filled data
    And I want fill "title" field with "Update#2 Test Document #1"
    And I want fill issueDate field with "2019-10-10T10:10:10+00:00"
    And I want set modified date "expDate" "+5 days"
    And I want fill "notifyBefore" field with "6"
    And I want fill "cost" field with "5"
    When I want upload file "test_image" "png" "975" "image/png"
    Then I want update document
    And response code is 200
    Then I want clean files data
    Then I want upgrade document
    And I want send messages in queue notification_events
    And response code is 200
    And I see field "title" filled with "Update#2 Test Document #1"
    And I see field "status" filled with "expire_soon"
    And I see field "draft/issueDate" filled with null
    And I see field "notifyBefore" filled with 6
    And I see field "draft/cost" filled with null
    And I see field "records/0/issueDate" filled with "2019-10-10T10:10:10+00:00"
    And I see field "records/0/cost" filled with "5"
    And I see field "records/0/files/0/displayName" filled with "test_image_test.png"
    And I see field "records/1/issueDate" filled with "2020-10-10T10:10:10+00:00"
    And I see field "records/1/cost" filled with "10.12"
    And I see field "records/1/files/0/displayName" filled with "test_image_test.png"
    And I see field "records/2/issueDate" filled with "2021-10-10T10:10:10+00:00"
    And I see field "records/2/files/0/displayName" filled with "test_image_test.png"
#    Then I want sleep on 1 seconds
    Then I signed with email "linxio-dev@ocsico.com"
    Given Elastica populate index "eventLog"
    And I want fill "eventId" field with "event(DRIVER_DOCUMENT_EXPIRE_SOON, user)"
    Then I want get event log
    Then response code is 200
    And I see field "data/0/eventName" filled with "DRIVER_DOCUMENT_EXPIRE_SOON"
    And I see field "data/0/formattedDate"
    And I see field "data/0/importance" filled with "normal"
    And I see field "data/0/eventTeam" filled with "client-name-0"
    And I see field "data/0/triggeredBy" filled with null
    And I see field "data/0/triggeredDetails" filled with "admin@user.com"
#    And I see field "data/0/eventSourceType" filled with "document"
    And I see field "data/0/eventSource/name" filled with "driver@example.com"
#    And I see field "data/0/eventSource/name" filled with "Update#2 Test Document #1"
    And I see field "data/0/eventSource/type" filled with "driverDocument"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I see field "data/0/eventSource/documentrecordId"
    And I see field "data/0/eventSource/documentId"
    And I see field "data/0/eventSource/driverId"
    And I see field "data/0/shortDetails/expiredDate"
    And I see field "data/0/shortDetails/title" filled with "Update#2 Test Document #1"
    And I see field "data/0/notificationsList/0/id"
    And I see field "data/0/notificationsList/0/title" filled with "Test#-document expire soon"
    And I see field "data/0/notificationsList/0/eventId"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I want get event object to save
    And I want clean filled data
    And I want check EntityPlaceholderService
    And I see in saved value field "from_company" filled with "Linxio"
    And I see in saved value field "reg_no" filled with "--"
    And I see in saved value field "model" filled with "--"
    And I see in saved value field "team" filled with "client-name-0"
    And I see in saved value field "status" filled with "active"
    And I see in saved value field "title" filled with "Update#2 Test Document #1"
    And I see in saved value field "triggered_by" filled with "--"
    And I see in saved value field "event_time" filled with "--"
    And I see in saved value field "driver" filled with "driverName driverSurname"
    And I see in saved value field "data_url" filled with "Document page: https://url/client/drivers/45/documents/4"
    And I see in saved value field "data_by_type" filled with "(driver - driverName driverSurname)"
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/recipient" filled with "string(1)"
    And I see field "data/0/subject" filled with "Alerts - Document expire soon"
    Then I should get an email with field "data/0/message" containing to template:
    """
    Document soon to expire: ${title}. ${comment}
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "document"
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
    Document soon to expire: ${title}. ${comment}
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "document"
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
    And I see field "data/0/subject" filled with "Alerts - Document expire soon"
    Then I should get an email with field "data/0/message" containing to template:
    """
    <h3>New notification from the ${from_company} system:</h3>
    <p>Document soon to expire ${data_by_type}.</p><br/>
    <p><b>Detailed information:</b></p>
    <p>${event_time} - document \"${title}\" ${data_by_type} will expire on ${expiration_date}.</p>
    <p>${data_url}</p><br/>
    <p>${comment}</p>
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "document"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 1
    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"
    And I see field "data/0/event/eventId"

  Scenario: I want check notification by driver document expired
    Then I want disabled all default notification
    Then I want fill "title" field with "Test#-document expired"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "teamId" field with 2
    And I want fill "eventId" field with "event(DRIVER_DOCUMENT_EXPIRED, user)"
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
    And I see field "listenerTeamId" filled with 2
    And I see field "ownerTeamId" filled with 1
    And I want clean filled data
    Given I signed in as "admin" team "client" and teamId 2
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
#    And I want fill "name" field with "test group"
#    And I want to create vehicle group and save id
#    And I want fill vehicle group id
    And I want fill "type" field with "Car"
    And I want fill "model" field with "model"
    And I want fill "available" field with true
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
    And I want fill "status" field with "online"
    And I want fill "makeModel" field with "testModel"
    And I want fill "make" field with "testMake"
    Then I want to create vehicle and save id
    And response code is 200
    And I want fill "name" field with "driverName"
    And I want fill "surname" field with "driverSurname"
    And I want fill "phone" field with "+46 (87)4646753"
    And I want fill "email" field with "driver@example.com"
    Then I want to register driver for current client
    And I see field "email" filled with "driver@example.com"
    Then I want to set vehicle driver by user email "driver@example.com" and date "2020-10-20 08:19:38"
    And I want clean filled data
    Then I want fill driver id of current team
    And I want fill "title" field with "Test Document #1"
    Then I want fill "issueDate" field with "2020-10-10T10:10:10+00:00"
    Then I want fill "expDate" field with "2020-10-10T10:10:10+00:00"
    Then I want fill "notifyBefore" field with "10"
    Then I want fill "cost" field with "10.12"
    When I want upload file "test_image" "png" "975" "image/png"
    Then I want create document
    And response code is 200
#    And I want send messages in queue notification_events
    Then I want clean files data
    Then I want upgrade document
    And response code is 200
    Then I want clean filled data
    And I want fill "issueDate" field with "2021-10-10T10:10:10+00:00"
    When I want upload file "test_image" "png" "975" "image/png"
    Then I want update document
    And response code is 200
    Then I want clean files data
    Then I want upgrade document
    And response code is 200
    And I see field "title" filled with "Test Document #1"
    And I see field "status" filled with "active"
    And I see field "draft/issueDate" filled with null
    And I see field "records/0/issueDate" filled with "2021-10-10T10:10:10+00:00"
    And I see field "records/0/files/0/displayName" filled with "test_image_test.png"
    Then I want clean filled data
    And I want fill "title" field with "Update#1 Test Document #1"
    And I want fill "issueDate" field with "2020-10-10T10:10:10+00:00"
    And I want set modified date "expDate" "-10 days"
    And I want fill "notifyBefore" field with "1"
    And I want fill "cost" field with "10.12"
    When I want upload file "test_image" "png" "975" "image/png"
    Then I want update document
    And response code is 200
    Then I want clean files data
    Then I want upgrade document
    And I want send messages in queue notification_events
    And response code is 200
#    Then I want sleep on 1 seconds
    Then I signed with email "linxio-dev@ocsico.com"
    Given Elastica populate index "eventLog"
    And I want fill "eventId" field with "event(DRIVER_DOCUMENT_EXPIRED, user)"
    Then I want get event log
    Then response code is 200
    And I see field "data/0/eventName" filled with "DRIVER_DOCUMENT_EXPIRED"
    And I see field "data/0/formattedDate"
    And I see field "data/0/importance" filled with "normal"
    And I see field "data/0/eventTeam" filled with "client-name-0"
    And I see field "data/0/triggeredBy" filled with null
    And I see field "data/0/triggeredDetails" filled with "admin@user.com"
#    And I see field "data/0/eventSourceType" filled with "document"
    And I see field "data/0/eventSource/name" filled with "driver@example.com"
#    And I see field "data/0/eventSource/name" filled with "Update#2 Test Document #1"
    And I see field "data/0/eventSource/type" filled with "driverDocument"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I see field "data/0/eventSource/documentrecordId"
    And I see field "data/0/eventSource/documentId"
    And I see field "data/0/eventSource/driverId"
    And I see field "data/0/shortDetails/expiredDate"
    And I see field "data/0/shortDetails/title"
    And I see field "data/0/notificationsList/0/id"
    And I see field "data/0/notificationsList/0/title" filled with "Test#-document expired"
    And I see field "data/0/notificationsList/0/eventId"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I want get event object to save
    And I want clean filled data
    And I want check EntityPlaceholderService
    And I see in saved value field "from_company" filled with "Linxio"
    And I see in saved value field "reg_no" filled with "--"
    And I see in saved value field "model" filled with "--"
    And I see in saved value field "team" filled with "client-name-0"
    And I see in saved value field "status" filled with "active"
    And I see in saved value field "title" filled with "Update#1 Test Document #1"
    And I see in saved value field "triggered_by" filled with "--"
    And I see in saved value field "event_time" filled with "--"
    And I see in saved value field "driver" filled with "driverName driverSurname"
    And I see in saved value field "data_url" filled with "Document page: https://url/client/drivers/45/documents/1"
    And I see in saved value field "data_by_type" filled with "(driver - driverName driverSurname)"
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/recipient" filled with "string(1)"
    And I see field "data/0/subject" filled with "Alerts - Document expired"
    Then I should get an email with field "data/0/message" containing to template:
    """
    Document expired: ${title}. ${comment}
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "document"
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
    Document expired: ${title}. ${comment}
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "document"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 1
    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"
    And I see field "data/0/event/eventId"
    And I see field "data/0/event/eventLogId"
    And I want clean filled data
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "email"
    And I want fill "limit" field with "3"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "email"
    And I see field "data/0/recipient" filled with "linxio-dev@ocsico.com"
    And I see field "data/0/subject" filled with "Alerts - Document expired"
    Then I should get an email with field "data/0/message" containing to template:
    """
    <h3>New notification from the ${from_company} system:</h3>
    <p>Document expired ${data_by_type}.</p><br/>
    <p><b>Detailed information:</b></p>
    <p>${event_time} - document expired \"${title}\" expired ${data_by_type}.</p>
    <p>${data_url}</p><br/>
    <p>${comment}</p>
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "document"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 1
    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"
    And I see field "data/0/event/eventId"
    And I see field "data/0/event/eventLogId"