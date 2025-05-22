Feature: Clients Events

  Background:
    Given I signed in as "super_admin" team "admin"
    And the queue associated to notification.events events producer is empty

  Scenario: I want check notification by register new client
    Then I want fill "title" field with "Test# - client created"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "eventId" field with "event(CLIENT_CREATED, user)"
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
    And I see field "listenerTeamId" filled with 1
    And I see field "ownerTeamId" filled with 1
    And I want clean filled data
    And I want fill "name" field with "client name test"
    And I want fill "legalName" field with "client legal name"
    And I want fill "taxNr" field with "12345678910"
    And I want fill "legalAddress" field with "client legal address"
    And I want fill "billingAddress" field with "client billing address"
    And I want fill "taxNr" field with "12345678910"
    And I want fill "status" field with "client"
    And I want fill "planId" field with 1
    And I want fill "timezone" field with 63
    And I want fill "clientNote" field with "client note"
    And I want fill "adminNote" field with "admin note"
    Then I want register client and remember
    And  I want send messages in queue notification_events
    And I see field "name" filled with "client name test"
    And I see field "legalName" filled with "client legal name"
    And I see field "taxNr" filled with "string(12345678910)"
    And I see field "legalAddress" filled with "client legal address"
    And I see field "billingAddress" filled with "client billing address"
    And I see field "status" filled with "client"
    And I see field "timezone" filled with 63
    Then I want clean filled data
    And I want fill "name" field with "client user name"
    And I want fill "surname" field with "client user surname"
    And I want fill "position" field with "pos"
    And I want fill "phone" field with "+46 (87)464675"
    And I want fill "email" field with "dawdawd1ada@gmail.com"
#    Then I want sleep on 1 seconds
    Then I signed with email "linxio-dev@ocsico.com"
    Given Elastica populate index "eventLog"
    And I want fill "eventId" field with "event(CLIENT_CREATED, user)"
    Then I want get event log
    Then response code is 200
    And I see field "total" filled with 1
    And I see field "data/0/eventName" filled with "CLIENT_CREATED"
    And I see field "data/0/formattedDate"
    And I see field "data/0/importance" filled with "normal"
    And I see field "data/0/eventTeam" filled with "client name test"
    And I see field "data/0/triggeredBy" filled with "user"
    And I see field "data/0/triggeredDetails" filled with "super_admin@user.com"
#    And I see field "data/0/eventSourceType" filled with "user"
    And I see field "data/0/eventSource/name" filled with "client name test"
    And I see field "data/0/eventSource/type" filled with "client"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I see field "data/0/eventSource/clientId"
    And I see field "data/0/notificationsList/0/id"
    And I see field "data/0/notificationsList/0/title" filled with "[CLIENT_CREATED] Client Notification for admin by team admin"
    And I see field "data/0/notificationsList/1/title" filled with "Test# - client created"
    And I see field "data/0/notificationsList/0/eventId"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I want get event object to save
    And I want clean filled data
    And I want check EntityPlaceholderService
    And I see in saved value field "from_company" filled with "Linxio"
    And I see in saved value field "client_name" filled with "client name test"
    And I see in saved value field "team" filled with "client name test"
    And I see in saved value field "triggered_by" filled with "test user surname"
    And I see in saved value field "event_time"
    And I see in saved value field "data_url" filled with "Client page: https://url/admin/clients/11"
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/recipient" filled with "string(1)"
    And I see field "data/0/subject" filled with "Alerts - New client created"
    Then I should get an email with field "data/0/message" containing to template:
    """
    Client created: ${client_name}. ${comment}
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "client"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId"
    And I see field "data/0/event/entityTeam/clientName" filled with "client name test"
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
    Client created: ${client_name}. ${comment}
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "client"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId"
    And I see field "data/0/event/entityTeam/clientName" filled with "client name test"
    And I see field "data/0/event/eventId"
    And I see field "data/0/event/eventLogId"
    And I want clean filled data
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "email"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "email"
    And I see field "data/0/recipient" filled with "linxio-dev@ocsico.com"
    And I see field "data/0/subject" filled with "Alerts - New client created"
    Then I should get an email with field "data/0/message" containing to template:
    """
    <h3>New notification from the ${from_company} system:</h3>
    <p>Client <b>${client_name}</b> was created.</p><br/>
    <p><b>Detailed information:</b></p>
    <p>${event_time} - client ${client_name} was created by ${triggered_by}.</p>
    <p>Client page: ${data_url}</p><br/>
    <p>${comment}</p>
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "client"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId"
    And I see field "data/0/event/entityTeam/clientName" filled with "client name test"
    And I see field "data/0/event/eventId"

  Scenario: I want to check notification on client blocked
    Then I want fill "title" field with "Test# - client blocked"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "eventId" field with "event(CLIENT_BLOCKED, user)"
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
    And I see field "listenerTeamId" filled with 1
    And I see field "ownerTeamId" filled with 1
    Then I want clean filled data
    And I want fill "name" field with "client name test"
    And I want fill "legalName" field with "client legal name"
    And I want fill "taxNr" field with "12345678910"
    And I want fill "legalAddress" field with "client legal address"
    And I want fill "billingAddress" field with "client billing address"
    And I want fill "taxNr" field with "12345678910"
    And I want fill "status" field with "client"
    And I want fill "planId" field with 1
    And I want fill "timezone" field with 14
    And I want fill "clientNote" field with "client note"
    And I want fill "adminNote" field with "admin note"
    Then I want register client
    And I see field "name" filled with "client name test"
    And I see field "legalName" filled with "client legal name"
    And I see field "taxNr" filled with "string(12345678910)"
    And I see field "legalAddress" filled with "client legal address"
    And I see field "billingAddress" filled with "client billing address"
    And I see field "status" filled with "client"
    And I see field "timezone" filled with 14
    When I want fill "status" field with "blocked"
    Then I want update client "client name test"
    And  I want send messages in queue notification_events
    And I see field "status" filled with "blocked"
    And response code is 200
    Then I want clean filled data
    And I want fill "isBlocked" field with false
    When I want to update client user with client name "client-name-0" and user email "client-user-0@ocsico.com"
    And I see field "status" filled with "active"
    Then I want clean filled data
#    Then I want sleep on 1 seconds
    Then I signed with email "linxio-dev@ocsico.com"
    Given Elastica populate index "eventLog"
    And I want fill "eventId" field with "event(CLIENT_BLOCKED, user)"
    Then I want get event log
    Then response code is 200
    And I see field "total" filled with 1
    And I see field "data/0/eventName" filled with "CLIENT_BLOCKED"
    And I see field "data/0/formattedDate"
    And I see field "data/0/importance" filled with "normal"
    And I see field "data/0/eventTeam" filled with "client name test"
    And I see field "data/0/triggeredBy" filled with "user"
    And I see field "data/0/triggeredDetails" filled with "super_admin@user.com"
#    And I see field "data/0/eventSourceType" filled with "user"
    And I see field "data/0/eventSource/name" filled with "client name test"
    And I see field "data/0/eventSource/type" filled with "client"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I see field "data/0/eventSource/clientId"
    And I see field "data/0/notificationsList/0/id"
    And I see field "data/0/notificationsList/0/title" filled with "[CLIENT_BLOCKED] Client Notification for admin by team admin"
    And I see field "data/0/notificationsList/1/title" filled with "Test# - client blocked"
    And I see field "data/0/notificationsList/0/eventId"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I want get event object to save
    And I want clean filled data
    And I want check EntityPlaceholderService
    And I see in saved value field "from_company" filled with "Linxio"
    And I see in saved value field "client_name" filled with "client name test"
    And I see in saved value field "team" filled with "client name test"
    And I see in saved value field "triggered_by" filled with "test user surname"
    And I see in saved value field "event_time"
    And I see in saved value field "data_url" filled with "Client page: https://url/admin/clients/11"
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/recipient" filled with "string(1)"
    And I see field "data/0/subject" filled with "Alerts - Client blocked"
    Then I should get an email with field "data/0/message" containing to template:
    """
    Client blocked: ${client_name}. ${comment}
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "client"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId"
    And I see field "data/0/event/entityTeam/clientName" filled with "client name test"
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
    Client blocked: ${client_name}. ${comment}
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "client"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId"
    And I see field "data/0/event/entityTeam/clientName" filled with "client name test"
    And I see field "data/0/event/eventId"
    And I see field "data/0/event/eventLogId"
    And I want clean filled data
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "email"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/1/transportType" filled with "email"
    And I see field "data/1/recipient" filled with "linxio-dev@ocsico.com"
    And I see field "data/1/subject" filled with "Alerts - Client blocked"
    Then I should get an email with field "data/1/message" containing to template:
    """
    <h3>New notification from the ${from_company} system:</h3>
    <p>Client <b>${client_name}</b> was blocked.</p><br/>
    <p><b>Detailed information:</b></p>
    <p>${event_time} - client ${client_name} from ${team} was blocked by ${triggered_by}.</p>
    <p>Client page: ${data_url}</p><br/>
    <p>${comment}</p>
    """
    And I see field "data/1/status" filled with "pending"
    And I see field "data/1/event/eventSourceType" filled with "client"
    And I see field "data/1/event/entityTeam/type" filled with "client"
    And I see field "data/1/event/entityTeam/clientId"
    And I see field "data/1/event/entityTeam/clientName" filled with "client name test"
    And I see field "data/1/event/eventId"

  Scenario: I want to check notification on client with status demo expired
    Then I want fill "title" field with "Test# - client demo expired"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "eventId" field with "event(CLIENT_DEMO_EXPIRED, user)"
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
    And I see field "listenerTeamId" filled with 1
    And I see field "ownerTeamId" filled with 1
    Then I want clean filled data
    And I want fill "name" field with "client name test"
    And I want fill "legalName" field with "client legal name"
    And I want fill "taxNr" field with "12345678910"
    And I want fill "legalAddress" field with "client legal address"
    And I want fill "billingAddress" field with "client billing address"
    And I want fill "taxNr" field with "12345678910"
    And I want fill "status" field with "demo"
    And I want fill "planId" field with 1
    And I want fill "timezone" field with 14
    And I want fill "clientNote" field with "client note"
    And I want fill "adminNote" field with "admin note"
    And I want fill "expirationDate" field with now
    Then I want register client
    And response code is 200
    Then I want clean filled data
    Given Client demo update status
    And  I want send messages in queue notification_events
#    Then I want sleep on 1 seconds
    Then I signed with email "linxio-dev@ocsico.com"
    Given Elastica populate index "eventLog"
    And I want fill "eventId" field with "event(CLIENT_DEMO_EXPIRED, user)"
    Then I want get event log
    Then response code is 200
    And I see field "total" filled with 1
    And I see field "data/0/eventName" filled with "CLIENT_DEMO_EXPIRED"
    And I see field "data/0/formattedDate"
    And I see field "data/0/importance" filled with "normal"
    And I see field "data/0/eventTeam" filled with "client name test"
    And I see field "data/0/triggeredBy" filled with "user"
    And I see field "data/0/triggeredDetails" filled with "system"
#    And I see field "data/0/eventSourceType" filled with "user"
    And I see field "data/0/eventSource/name" filled with "client name test"
    And I see field "data/0/eventSource/type" filled with "client"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I see field "data/0/eventSource/clientId"
    And I see field "data/0/notificationsList/0/id"
    And I see field "data/0/notificationsList/0/title" filled with "Test# - client demo expired"
    And I see field "data/0/notificationsList/0/eventId"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I want get event object to save
    And I want clean filled data
    And I want check EntityPlaceholderService
    And I see in saved value field "from_company" filled with "Linxio"
    And I see in saved value field "client_name" filled with "client name test"
    And I see in saved value field "team" filled with "client name test"
    And I see in saved value field "triggered_by"
    And I see in saved value field "event_time"
    And I see in saved value field "data_url" filled with "Client page: https://url/admin/clients/11"
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/recipient" filled with "string(1)"
    And I see field "data/0/subject" filled with "Alerts - Client demo expired"
    Then I should get an email with field "data/0/message" containing to template:
    """
    Client demo expired: ${client_name}. ${comment}
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "client"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId"
    And I see field "data/0/event/entityTeam/clientName" filled with "client name test"
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
    Client demo expired: ${client_name}. ${comment}
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "client"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId"
    And I see field "data/0/event/entityTeam/clientName" filled with "client name test"
    And I see field "data/0/event/eventId"
    And I see field "data/0/event/eventLogId"
    And I want clean filled data
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "email"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/1/transportType" filled with "email"
    And I see field "data/1/recipient" filled with "linxio-dev@ocsico.com"
    And I see field "data/1/subject" filled with "Alerts - Client demo expired"
    Then I should get an email with field "data/1/message" containing to template:
    """
    <h3>New notification from the ${from_company} system:</h3>
    <p>Demo period expired for the client <b>${client_name}</b>.</p><br/>
    <p><b>Detailed information:</b></p>
    <p>${event_time} - client ${client_name} demo period expired.</p>
    <p>Client page: ${data_url}</p><br/>
    <p>${comment}</p>
    """
    And I see field "data/1/status" filled with "pending"
    And I see field "data/1/event/eventSourceType" filled with "client"
    And I see field "data/1/event/entityTeam/type" filled with "client"
    And I see field "data/1/event/entityTeam/clientId"
    And I see field "data/1/event/entityTeam/clientName" filled with "client name test"
    And I see field "data/1/event/eventId"