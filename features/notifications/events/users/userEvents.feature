Feature: Users Events

  Background:
    Given I signed in as "super_admin" team "admin"
    And the queue associated to notification.events events producer is empty

  Scenario: I want to check notification on create Admin Team User
    Then I want disabled all default notification
    Then I want fill "title" field with "Test# - create Admin Team User"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "eventId" field with "event(ADMIN_USER_CREATED, user)"
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
    And I want clean filled data
    And I want fill "email" field with "admin_team_user@gmail.test"
    And I want fill "name" field with "Alex 2"
    And I want fill "phone" field with "+6(4)654654"
    And I want fill "roleId" field with "2"
    Then I want register client manager user and remember id
    And I want send messages in queue notification_events
    And I see field "email" filled with "admin_team_user@gmail.test"
    And I see field "roleId" filled with 2
    And I see field "id"
    And I want clean filled data
    And response code is 200
    Then I want clean filled data
#    Then I want sleep on 1 seconds
    Then I signed with email "linxio-dev@ocsico.com"
    Given Elastica populate index "eventLog"
    And I want fill "eventId" field with "event(ADMIN_USER_CREATED, user)"
    Then I want get event log
    Then response code is 200
    And I see field "total" filled with 1
    And I see field "data/0/eventName" filled with "ADMIN_USER_CREATED"
    And I see field "data/0/formattedDate"
    And I see field "data/0/importance" filled with "normal"
    And I see field "data/0/eventTeam" filled with "admin"
    And I see field "data/0/triggeredBy" filled with "user"
    And I see field "data/0/triggeredDetails" filled with "super_admin@user.com"
    And I see field "data/0/eventSourceType" filled with "user"
    And I see field "data/0/eventSource/name" filled with "admin_team_user@gmail.test"
    And I see field "data/0/eventSource/type" filled with "user"
    And I see field "data/0/eventSource/entityTeam/type" filled with "admin"
    And I see field "data/0/eventSource/userId"
    And I see field "data/0/notificationsList/0/id"
    And I see field "data/0/notificationsList/0/title" filled with "Test# - create Admin Team User"
    And I see field "data/0/notificationsList/0/eventId"
    And I see field "data/0/eventSource/entityTeam/type" filled with "admin"
    And I want get event object to save
    And I want clean filled data
    And I want check EntityPlaceholderService
    And I see in saved value field "from_company" filled with "Linxio"
    And I see in saved value field "user_email" filled with "admin_team_user@gmail.test"
    And I see in saved value field "user_name" filled with "Alex 2"
    And I see in saved value field "team" filled with "admin"
    And I see in saved value field "event_time"
    And I see in saved value field "data_url" filled with "https://url/admin/team/user/44"
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/recipient" filled with "string(1)"
    And I see field "data/0/subject" filled with "Alerts - New user created"
    Then I should get an email with field "data/0/message" containing to template:
    """
    User created: ${user_email} (${user_name}). ${comment}
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "user"
    And I see field "data/0/event/entityTeam/type" filled with "admin"
    And I see field "data/0/event/entityTeam/clientId" filled with null
    And I see field "data/0/event/entityTeam/clientName" filled with null
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
    User created: ${user_email}. ${comment}
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "user"
    And I see field "data/0/event/entityTeam/type" filled with "admin"
    And I see field "data/0/event/entityTeam/clientId" filled with null
    And I see field "data/0/event/entityTeam/clientName" filled with null
    And I see field "data/0/event/eventId"
    And I see field "data/0/event/eventLogId"
    And I want clean filled data
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "email"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "email"
    And I see field "data/0/recipient" filled with "linxio-dev@ocsico.com"
    And I see field "data/0/subject" filled with "Alerts - New user created"
    Then I should get an email with field "data/0/message" containing to template:
    """
    <h3>New notification from the ${from_company} system:</h3>
    <p>User ${user_name} <b>${user_email}</b> was created.</p><br/>
    <p><b>Detailed information:</b></p>
    <p>${event_time} - user ${user_name} <b>${user_email}</b> (team - ${team}) was created by ${triggered_by}.</p>
    <p>User page: ${data_url}</p><br/>
    <p>${comment}</p>
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "user"
    And I see field "data/0/event/entityTeam/type" filled with "admin"
    And I see field "data/0/event/entityTeam/clientId" filled with null
    And I see field "data/0/event/entityTeam/clientName" filled with null
    And I see field "data/0/event/eventId"
    And I see field "data/0/event/eventLogId"

  Scenario: I want to check notification on create Client Team User from Admin
    Then I want disabled all default notification
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
    And I want clean filled data
    Then I want fill "title" field with "Test# - create Client Team User"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "eventId" field with "event(USER_CREATED, user)"
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
    And I want fill teamId by saved clientId
    Then I want create notification
    Then response code is 200
    Then I want clean filled data
    And I want fill "name" field with "client user name"
    And I want fill "surname" field with "client user surname"
    And I want fill "position" field with "pos"
    And I want fill "phone" field with "+46 (87)464675"
    And I want fill "email" field with "client_team_user@gmail.com"
    And I want fill "roleId" field with 6
    Then I want register user for current client
    And  I want send messages in queue notification_events
    And I see field "name" filled with "client user name"
    And I see field "surname" filled with "client user surname"
    And I see field "position" filled with "pos"
    And I see field "phone" filled with "+46 (87)464675"
    And I see field "email" filled with "client_team_user@gmail.com"
    And I see field "status" filled with "new"
    And I see field "roleId" filled with 6
    And I want clean filled data
    And response code is 200
    Then I want clean filled data
#    Then I want sleep on 1 seconds
    Then I signed with email "linxio-dev@ocsico.com"
    Given Elastica populate index "eventLog"
    And I want fill "eventId" field with "event(USER_CREATED, user)"
    Then I want get event log
    Then response code is 200
    And I see field "total" filled with 1
    And I see field "data/0/eventName" filled with "USER_CREATED"
    And I see field "data/0/formattedDate"
    And I see field "data/0/importance" filled with "normal"
    And I see field "data/0/eventTeam" filled with "client name test"
    And I see field "data/0/triggeredBy" filled with "user"
    And I see field "data/0/triggeredDetails" filled with "super_admin@user.com"
#    And I see field "data/0/eventSourceType" filled with "user"
    And I see field "data/0/eventSource/name" filled with "client_team_user@gmail.com"
    And I see field "data/0/eventSource/type" filled with "user"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I see field "data/0/eventSource/userId"
    And I see field "data/0/notificationsList/0/id"
    And I see field "data/0/notificationsList/0/title" filled with "Test# - create Client Team User"
    And I see field "data/0/notificationsList/0/eventId"
    And I want get event object to save
    And I want clean filled data
    And I want check EntityPlaceholderService
    And I see in saved value field "from_company" filled with "Linxio"
    And I see in saved value field "user_email" filled with "client_team_user@gmail.com"
    And I see in saved value field "user_name" filled with "client user name client user surname"
    And I see in saved value field "team" filled with "client name test"
    And I see in saved value field "event_time"
    And I see in saved value field "data_url" filled with "https://url/admin/clients/12/users/44"
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/recipient" filled with "string(1)"
    And I see field "data/0/subject" filled with "Alerts - New user created"
    Then I should get an email with field "data/0/message" containing to template:
    """
    User created: ${user_email} (${user_name}). ${comment}
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "user"
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
    User created: ${user_email}. ${comment}
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "user"
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
    And I see field "data/0/subject" filled with "Alerts - New user created"
    Then I should get an email with field "data/0/message" containing to template:
    """
    <h3>New notification from the ${from_company} system:</h3>
    <p>User ${user_name} <b>${user_email}</b> was created.</p><br/>
    <p><b>Detailed information:</b></p>
    <p>${event_time} - user ${user_name} <b>${user_email}</b> (team - ${team}) was created by ${triggered_by}.</p>
    <p>User page: ${data_url}</p><br/>
    <p>${comment}</p>
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "user"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId"
    And I see field "data/0/event/entityTeam/clientName" filled with "client name test"
    And I see field "data/0/event/eventId"
    And I see field "data/0/event/eventLogId"

  Scenario: I want to check notification on create Client Team User from Client
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
    And I want clean filled data
    Then I want fill "title" field with "Test# - create Client Team User"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "eventId" field with "event(USER_CREATED, user)"
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
    And I want fill teamId by saved clientId
    Then I want create notification
    Then response code is 200
    Then I want clean filled data
    Then I want login as client with name "client name test"
    And I want fill "name" field with "client user name"
    And I want fill "surname" field with "client user surname"
    And I want fill "position" field with "pos"
    And I want fill "phone" field with "+46 (87)464675"
    And I want fill "email" field with "client_team_user@gmail.com"
    And I want fill "roleId" field with 6
    Then I want register user for current client
    And  I want send messages in queue notification_events
    And I see field "name" filled with "client user name"
    And I see field "surname" filled with "client user surname"
    And I see field "position" filled with "pos"
    And I see field "phone" filled with "+46 (87)464675"
    And I see field "email" filled with "client_team_user@gmail.com"
    And I see field "status" filled with "new"
    And I see field "roleId" filled with 6
    And I want clean filled data
    And response code is 200
    Then I want clean filled data
#    Then I want sleep on 10 seconds
    Then I signed with email "linxio-dev@ocsico.com"
    Given Elastica populate index "eventLog"
    And I want fill "eventId" field with "event(USER_CREATED, user)"
    Then I want get event log
    Then response code is 200
    And I see field "total" filled with 1
    And I see field "data/0/eventName" filled with "USER_CREATED"
    And I see field "data/0/formattedDate"
    And I see field "data/0/importance" filled with "normal"
    And I see field "data/0/eventTeam" filled with "client name test"
    And I see field "data/0/triggeredBy" filled with "user"
    And I see field "data/0/triggeredDetails" filled with "super_admin@user.com"
#    And I see field "data/0/eventSourceType" filled with "user"
    And I see field "data/0/eventSource/name" filled with "client_team_user@gmail.com"
    And I see field "data/0/eventSource/type" filled with "user"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I see field "data/0/eventSource/userId"
    And I see field "data/0/notificationsList/0/id"
    And I see field "data/0/notificationsList/0/title" filled with "Test# - create Client Team User"
    And I see field "data/0/notificationsList/0/eventId"
    And I want get event object to save
    And I want clean filled data
    And I want check EntityPlaceholderService
    And I see in saved value field "from_company" filled with "Linxio"
    And I see in saved value field "user_email" filled with "client_team_user@gmail.com"
    And I see in saved value field "user_name" filled with "client user name client user surname"
    And I see in saved value field "team" filled with "client name test"
    And I see in saved value field "event_time"
    And I see in saved value field "data_url" filled with "https://url/admin/clients/12/users/44"
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/recipient" filled with "string(1)"
    And I see field "data/0/subject" filled with "Alerts - New user created"
    Then I should get an email with field "data/0/message" containing to template:
    """
    User created: ${user_email} (${user_name}). ${comment}
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "user"
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
    User created: ${user_email}. ${comment}
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "user"
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
    And I see field "data/0/subject" filled with "Alerts - New user created"
    Then I should get an email with field "data/0/message" containing to template:
    """
    <h3>New notification from the ${from_company} system:</h3>
    <p>User ${user_name} <b>${user_email}</b> was created.</p><br/>
    <p><b>Detailed information:</b></p>
    <p>${event_time} - user ${user_name} <b>${user_email}</b> (team - ${team}) was created by ${triggered_by}.</p>
    <p>User page: ${data_url}</p><br/>
    <p>${comment}</p>
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "user"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId"
    And I see field "data/0/event/entityTeam/clientName" filled with "client name test"
    And I see field "data/0/event/eventId"
    And I see field "data/0/event/eventLogId"

  Scenario: I want to check notification on edit name Admin Team User
    Then I want disabled all default notification
    Then I want fill "title" field with "Test# - change name for Admin Team User"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "eventId" field with "event(ADMIN_USER_CHANGED_NAME, user)"
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
    And I want clean filled data
    And I want fill "email" field with "client_manager_user@gmail.test"
    And I want fill "name" field with "Alex"
    And I want fill "phone" field with "+6(4)654654"
    And I want fill "roleId" field with "3"
    And I want fill "allTeamsPermissions" field with "false"
    Then I want register client manager user and remember id
    And I see field "email" filled with "client_manager_user@gmail.test"
    And I see field "roleId" filled with 3
    Then I want fill "name" field with "client name1"
    And I want fill "taxNr" field with "12345678910"
    And I want fill "planId" field with 1
    And I want fill "timezone" field with 63
    Then I want register client with manager and remember
    And I see field "id"
    And I want clean filled data
    Then I want fill "name" field with "Alex TS"
    Then I want fill "surname" field with "Alex TS"
    And I want update admin team user data by id with email "client_manager_user@gmail.test"
    And I want send messages in queue notification_events
    And response code is 200
    Then I want clean filled data
#    Then I want sleep on 1 seconds
    Then I signed with email "linxio-dev@ocsico.com"
    Given Elastica populate index "eventLog"
    And I want fill "eventId" field with "event(ADMIN_USER_CHANGED_NAME, user)"
    Then I want get event log
    Then response code is 200
    And I see field "total" filled with 1
    And I see field "data/0/eventName" filled with "ADMIN_USER_CHANGED_NAME"
    And I see field "data/0/formattedDate"
    And I see field "data/0/importance" filled with "normal"
    And I see field "data/0/eventTeam" filled with "admin"
    And I see field "data/0/triggeredBy" filled with "user"
    And I see field "data/0/triggeredDetails" filled with "super_admin@user.com"
#    And I see field "data/0/eventSourceType" filled with "user"
    And I see field "data/0/eventSource/name" filled with "client_manager_user@gmail.test"
    And I see field "data/0/eventSource/type" filled with "user"
    And I see field "data/0/eventSource/entityTeam/type" filled with "admin"
    And I see field "data/0/eventSource/userId"
    And I see field "data/0/notificationsList/0/id"
    And I see field "data/0/notificationsList/0/title" filled with "Test# - change name for Admin Team User"
    And I see field "data/0/notificationsList/0/eventId"
    And I see field "data/0/eventSource/entityTeam/type" filled with "admin"
    And I want get event object to save
    And I want clean filled data
    And I want check EntityPlaceholderService
    And I see in saved value field "from_company" filled with "Linxio"
    And I see in saved value field "user_email" filled with "client_manager_user@gmail.test"
    And I see in saved value field "user_name" filled with "Alex TS Alex TS"
    And I see in saved value field "team" filled with "admin"
    And I see in saved value field "triggered_by" filled with "test user surname"
    And I see in saved value field "event_time"
    And I see in saved value field "data_url" filled with "https://url/admin/team/user/44"
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/recipient" filled with "string(1)"
    And I see field "data/0/subject" filled with "Alerts - User changed name"
    Then I should get an email with field "data/0/message" containing to template:
    """
    User changed name: ${user_name} (${user_email}). ${comment}
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "user"
    And I see field "data/0/event/entityTeam/type" filled with "admin"
    And I see field "data/0/event/entityTeam/clientId" filled with null
    And I see field "data/0/event/entityTeam/clientName" filled with null
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
    User changed name: ${user_name} (${user_email}). ${comment}
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "user"
    And I see field "data/0/event/entityTeam/type" filled with "admin"
    And I see field "data/0/event/entityTeam/clientId" filled with null
    And I see field "data/0/event/entityTeam/clientName" filled with null
    And I see field "data/0/event/eventId"
    And I see field "data/0/event/eventLogId"
    And I want clean filled data
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "email"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "email"
    And I see field "data/0/recipient" filled with "linxio-dev@ocsico.com"
    And I see field "data/0/subject" filled with "Alerts - User changed name"
    Then I should get an email with field "data/0/message" containing to template:
    """
    <h3>New notification from the ${from_company} system:</h3>
    <p>Name changed for user <b>${user_name} (${user_email})</b></p><br/>
    <p><b>Detailed information:</b></p>
    <p>${event_time} - ${triggered_by} have renamed user from \"${old_value}\" to \"${user_name}\".</p>
    <p>User page: ${data_url}</p><br/>
    <p>${comment}</p>
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "user"
    And I see field "data/0/event/entityTeam/type" filled with "admin"
    And I see field "data/0/event/entityTeam/clientId" filled with null
    And I see field "data/0/event/entityTeam/clientName" filled with null
    And I see field "data/0/event/eventId"
    And I see field "data/0/event/eventLogId"

  Scenario: I want check notification by login as a user with type 'client' under an authorized admin
    Then I want disabled all default notification
    Then I want fill "title" field with "Test# - login as user with type client"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "eventId" field with "event(LOGIN_AS_USER, user)"
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
    Then I want login as client with name "ACME1"
    And  I want send messages in queue notification_events
    And I see field "teamType" filled with "client"
    And I see field "email" filled with "acme-admin@linxio.local"
    And I see field "token"
    Then I want logout
    And response code is 204
    Then I signed with email "acme-admin@linxio.local"
    And  I want send messages in queue notification_events
    Then I want logout
    And response code is 204
    Given I signed in as "admin" team "client" and teamId 2
    And  I want send messages in queue notification_events
    Then I want logout
    And response code is 204
#    Then I want sleep on 1 seconds
    Then I signed with email "linxio-dev@ocsico.com"
    Given Elastica populate index "eventLog"
    And I want fill "eventId" field with "event(LOGIN_AS_USER, user)"
    Then I want get event log
    Then response code is 200
    And I see field "total" filled with 1
    And I see field "data/0/eventName" filled with "LOGIN_AS_USER"
    And I see field "data/0/formattedDate"
    And I see field "data/0/importance" filled with "normal"
    And I see field "data/0/eventTeam" filled with "ACME1"
    And I see field "data/0/triggeredBy" filled with "user"
    And I see field "data/0/triggeredDetails" filled with "super_admin@user.com"
#    And I see field "data/0/eventSourceType" filled with "user"
    And I see field "data/0/eventSource/name" filled with "acme-admin@linxio.local"
    And I see field "data/0/eventSource/type" filled with "user"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I see field "data/0/eventSource/userId"
    And I see field "data/0/notificationsList/0/id"
    And I see field "data/0/notificationsList/0/title" filled with "Test# - login as user with type client"
    And I see field "data/0/notificationsList/0/eventId"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I want get event object to save
    And I want clean filled data
    And I want check EntityPlaceholderService
    And I see in saved value field "from_company" filled with "Linxio"
    And I see in saved value field "client_email" filled with "Acme"
    And I see in saved value field "client_name" filled with "ACME Ltd"
    And I see in saved value field "event_time"
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/recipient" filled with "string(1)"
    And I see field "data/0/subject" filled with "Alerts - User activity"
    Then I should get an email with field "data/0/message" containing to template:
    """
    Login as user: ${user_email} ${user_name}. ${comment}
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "user"
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
    Login as user: ${user_email} ${user_name}. ${comment}
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "user"
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
    And I see field "data/0/subject" filled with "Alerts - User activity"
    Then I should get an email with field "data/0/message" containing to template:
    """
    <h3>New notification from the ${from_company} system:</h3>
    <p>Login as user: ${user_email} ${user_name}</p><br/>
    <p><b>Detailed information:</b></p>
    <p>Date: ${event_time}.</p><br/>
    <p>${comment}</p>
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "user"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 10
    And I see field "data/0/event/entityTeam/clientName" filled with "ACME1"
    And I see field "data/0/event/eventId"
    And I see field "data/0/event/eventLogId"

  Scenario: I want check notification by login as a user with type 'user' under an authorized admin
    Then I want fill "title" field with "Test# - login as user with type user"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "eventId" field with "event(LOGIN_AS_USER, user)"
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
    Then I want login as user with email "client-user-0@ocsico.com"
    And  I want send messages in queue notification_events
    And I see field "teamType" filled with "client"
    And I see field "email" filled with "client-user-0@ocsico.com"
    And I see field "token"
    Then I want logout
    And response code is 204
    Then I signed with email "client-user-0@ocsico.com"
    And  I want send messages in queue notification_events
    Then I want logout
    And response code is 204
    Given I signed in as "admin" team "client" and teamId 2
    And  I want send messages in queue notification_events
    Then I want logout
    And response code is 204
#    Then I want sleep on 1 seconds
    Then I signed with email "linxio-dev@ocsico.com"
    Given Elastica populate index "eventLog"
    And I want fill "eventId" field with "event(LOGIN_AS_USER, user)"
    Then I want get event log
    Then response code is 200
    And I see field "total" filled with 1
    And I see field "data/0/eventName" filled with "LOGIN_AS_USER"
    And I see field "data/0/formattedDate"
    And I see field "data/0/importance" filled with "normal"
    And I see field "data/0/eventTeam" filled with "client-name-0"
    And I see field "data/0/triggeredBy" filled with "user"
    And I see field "data/0/triggeredDetails" filled with "super_admin@user.com"
#    And I see field "data/0/eventSourceType" filled with "user"
    And I see field "data/0/eventSource/name" filled with "client-user-0@ocsico.com"
    And I see field "data/0/eventSource/type" filled with "user"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I see field "data/0/eventSource/userId"
    And I see field "data/0/notificationsList/0/id"
    And I see field "data/0/notificationsList/0/title" filled with "Test# - login as user with type user"
    And I see field "data/0/notificationsList/0/eventId"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I want get event object to save
    And I want clean filled data
    And I want check EntityPlaceholderService
    And I see in saved value field "from_company" filled with "Linxio"
    And I see in saved value field "client_email" filled with "Acme"
    And I see in saved value field "client_name" filled with "ACME Ltd"
    And I see in saved value field "event_time"
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/recipient" filled with "string(1)"
    And I see field "data/0/subject" filled with "Alerts - User activity"
    Then I should get an email with field "data/0/message" containing to template:
    """
    Login as user: ${user_email} ${user_name}. ${comment}
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "user"
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
    Login as user: ${user_email} ${user_name}. ${comment}
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "user"
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
    And I see field "data/0/subject" filled with "Alerts - User activity"
    Then I should get an email with field "data/0/message" containing to template:
    """
    <h3>New notification from the ${from_company} system:</h3>
    <p>Login as user: ${user_email} ${user_name}</p><br/>
    <p><b>Detailed information:</b></p>
    <p>Date: ${event_time}.</p><br/>
    <p>${comment}</p>
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "user"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 1
    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"
    And I see field "data/0/event/eventId"
    And I see field "data/0/event/eventLogId"