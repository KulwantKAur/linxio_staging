Feature: Alert settings

  Scenario: I want check get alert setting access depending on type team
    Given I signed in as "super_admin" team "admin"
    Given Elastica populate
    Then I want get alert setting
    Then response code is 200
    And I see field "0/type" filled with 'admin'
    And I see field "0/alertSubTypes/0/id" filled with 1
    And I see field "0/alertSubTypes/0/name" filled with "Admin user accounts"
    And I see field "0/alertSubTypes/0/permissions" filled with "admin_team_user_list"
    And I see field "0/alertSubTypes/0/events/0/alias" filled with "User created"
    And I see field "0/alertSubTypes/0/events/0/eventSource" filled with "user"
    And I see field "0/alertSubTypes/0/events/0/notificationCount" filled with 0
    And I see field "0/alertSubTypes/0/events/0/teamType" filled with "admin"
    And I see field "0/alertSubTypes/0/events/1/alias" filled with "User blocked"
    And I see field "0/alertSubTypes/0/events/1/eventSource" filled with "user"
    And I see field "0/alertSubTypes/0/events/1/notificationCount" filled with 0
    And I see field "0/alertSubTypes/0/events/1/teamType" filled with "admin"
    And I see field "0/alertSubTypes/0/events/2/alias" filled with "User deleted"
    And I see field "0/alertSubTypes/0/events/2/eventSource" filled with "user"
    And I see field "0/alertSubTypes/0/events/2/notificationCount" filled with 0
    And I see field "0/alertSubTypes/0/events/2/teamType" filled with "admin"
    And I see field "0/alertSubTypes/0/events/3/alias" filled with "User password reset"
    And I see field "0/alertSubTypes/0/events/3/eventSource" filled with "user"
    And I see field "0/alertSubTypes/0/events/3/notificationCount" filled with 0
    And I see field "0/alertSubTypes/0/events/3/teamType" filled with "admin"
    And I see field "0/alertSubTypes/0/events/4/alias" filled with "User changed name"
    And I see field "0/alertSubTypes/0/events/4/eventSource" filled with "user"
    And I see field "0/alertSubTypes/0/events/4/notificationCount" filled with 0
    And I see field "0/alertSubTypes/0/events/4/teamType" filled with "admin"
    And I see field "0/alertSubTypes/1/name" filled with "Admin activities"
    And I see field "0/alertSubTypes/1/permissions" filled with "admin_team_user_list"
    And I see field "0/alertSubTypes/1/events/0/alias" filled with "Login as user"
    And I see field "0/alertSubTypes/1/events/0/eventSource" filled with "user"
    And I see field "0/alertSubTypes/1/events/0/notificationCount" filled with 0
    And I see field "0/alertSubTypes/1/events/0/teamType" filled with "admin"
    And I see field "0/alertSubTypes/2/name" filled with "Client accounts"
    And I see field "0/alertSubTypes/2/permissions" filled with "client_list"
    And I see field "0/alertSubTypes/2/events/0/alias" filled with "Client created"
    And I see field "0/alertSubTypes/2/events/0/eventSource" filled with "client"
    And I see field "0/alertSubTypes/2/events/0/notificationCount" filled with 1
    And I see field "0/alertSubTypes/2/events/0/teamType" filled with "admin"
    And I see field "0/alertSubTypes/2/events/1/alias" filled with "Client demo expired"
    And I see field "0/alertSubTypes/2/events/1/eventSource" filled with "client"
    And I see field "0/alertSubTypes/2/events/1/notificationCount" filled with 1
    And I see field "0/alertSubTypes/2/events/1/teamType" filled with "admin"
    And I see field "0/alertSubTypes/2/events/2/alias" filled with "Client blocked"
    And I see field "0/alertSubTypes/2/events/2/eventSource" filled with "client"
    And I see field "0/alertSubTypes/2/events/2/notificationCount" filled with 1
    And I see field "0/alertSubTypes/2/events/2/teamType" filled with "admin"
    And I see field "0/alertSubTypes/3/name" filled with "Device status"
    And I see field "0/alertSubTypes/3/permissions" filled with "device_list"
    And I see field "0/alertSubTypes/3/events/0/alias" filled with "Device unknown detected"
    And I see field "0/alertSubTypes/3/events/0/eventSource" filled with "device"
    And I see field "0/alertSubTypes/3/events/0/notificationCount" filled with 0
    And I see field "0/alertSubTypes/3/events/0/teamType" filled with "admin"
    And I see field "0/alertSubTypes/3/events/1/alias" filled with "Device in stock"
    And I see field "0/alertSubTypes/3/events/1/eventSource" filled with "device"
    And I see field "0/alertSubTypes/3/events/1/notificationCount" filled with 0
    And I see field "0/alertSubTypes/3/events/1/teamType" filled with "admin"
    And I see field "0/alertSubTypes/3/events/2/alias" filled with "Device offline"
    And I see field "0/alertSubTypes/3/events/2/eventSource" filled with "device"
    And I see field "0/alertSubTypes/3/events/2/notificationCount" filled with 0
    And I see field "0/alertSubTypes/3/events/2/teamType" filled with "admin"
    And I see field "0/alertSubTypes/3/events/3/alias" filled with "Device unavailable"
    And I see field "0/alertSubTypes/3/events/3/eventSource" filled with "device"
    And I see field "0/alertSubTypes/3/events/3/notificationCount" filled with 0
    And I see field "0/alertSubTypes/3/events/3/teamType" filled with "admin"
    And I see field "0/alertSubTypes/3/events/4/alias" filled with "Device deleted"
    And I see field "0/alertSubTypes/3/events/4/eventSource" filled with "device"
    And I see field "0/alertSubTypes/3/events/4/notificationCount" filled with 0
    And I see field "0/alertSubTypes/3/events/4/teamType" filled with "admin"
    And I see field "1/type" filled with 'client'
    And I see field "1/alertSubTypes/0/name" filled with "Vehicle Activities"
    And I see field "1/alertSubTypes/0/permissions" filled with "vehicle_list"
    And I see field "1/alertSubTypes/0/events/0/alias" filled with "Driving without assigned driver"
    And I see field "1/alertSubTypes/0/events/0/eventSource" filled with "vehicle"
    And I see field "1/alertSubTypes/0/events/0/notificationCount" filled with 0
    And I see field "1/alertSubTypes/0/events/0/teamType" filled with "client"
    And I see field "1/alertSubTypes/0/events/1/alias" filled with "Overspeeding"
    And I see field "1/alertSubTypes/0/events/1/eventSource" filled with "device"
    And I see field "1/alertSubTypes/0/events/1/notificationCount" filled with 0
    And I see field "1/alertSubTypes/0/events/1/teamType" filled with "client"
    And I see field "1/alertSubTypes/0/events/2/alias" filled with "Driving longer than defined"
    And I see field "1/alertSubTypes/0/events/2/eventSource" filled with "vehicle"
    And I see field "1/alertSubTypes/0/events/2/notificationCount" filled with 0
    And I see field "1/alertSubTypes/0/events/2/teamType" filled with "client"
    And I see field "1/alertSubTypes/0/events/3/alias" filled with "Standing longer than defined"
    And I see field "1/alertSubTypes/0/events/3/eventSource" filled with "vehicle"
    And I see field "1/alertSubTypes/0/events/3/notificationCount" filled with 0
    And I see field "1/alertSubTypes/0/events/3/teamType" filled with "client"
    And I see field "1/alertSubTypes/0/events/4/alias" filled with "Vehicle is moving"
    And I see field "1/alertSubTypes/0/events/4/eventSource" filled with "vehicle"
    And I see field "1/alertSubTypes/0/events/4/notificationCount" filled with 0
    And I see field "1/alertSubTypes/0/events/4/teamType" filled with "client"
    And I see field "1/alertSubTypes/0/events/5/alias" filled with "Excess Idling"
    And I see field "1/alertSubTypes/0/events/5/eventSource" filled with "vehicle"
    And I see field "1/alertSubTypes/0/events/5/notificationCount" filled with 0
    And I see field "1/alertSubTypes/0/events/5/teamType" filled with "client"
    And I see field "1/alertSubTypes/0/events/6/alias" filled with "Vehicle being towed"
    And I see field "1/alertSubTypes/0/events/6/eventSource" filled with "device"
    And I see field "1/alertSubTypes/0/events/6/notificationCount" filled with 0
    And I see field "1/alertSubTypes/0/events/6/teamType" filled with "client"
    And I see field "1/alertSubTypes/0/events/7/alias" filled with "Supply Voltage"
    And I see field "1/alertSubTypes/0/events/7/eventSource" filled with "device"
    And I see field "1/alertSubTypes/0/events/7/notificationCount" filled with 0
    And I see field "1/alertSubTypes/0/events/7/teamType" filled with "client"
    And I see field "1/alertSubTypes/1/name" filled with "Vehicle Documents"
    And I see field "1/alertSubTypes/1/permissions" filled with "document_list"
    And I see field "1/alertSubTypes/1/events/0/alias" filled with "Document expiring soon"
    And I see field "1/alertSubTypes/1/events/0/eventSource" filled with "document"
    And I see field "1/alertSubTypes/1/events/0/notificationCount" filled with 1
    And I see field "1/alertSubTypes/1/events/0/teamType" filled with "client"
    And I see field "1/alertSubTypes/1/events/1/alias" filled with "Document expired"
    And I see field "1/alertSubTypes/1/events/1/eventSource" filled with "document"
    And I see field "1/alertSubTypes/1/events/1/notificationCount" filled with 1
    And I see field "1/alertSubTypes/1/events/1/teamType" filled with "client"
    And I see field "1/alertSubTypes/1/events/2/alias" filled with "Document deleted"
    And I see field "1/alertSubTypes/1/events/2/eventSource" filled with "document"
    And I see field "1/alertSubTypes/1/events/2/notificationCount" filled with 0
    And I see field "1/alertSubTypes/1/events/2/teamType" filled with "client"
    And I see field "1/alertSubTypes/1/events/3/alias" filled with "Document added"
    And I see field "1/alertSubTypes/1/events/3/eventSource" filled with "document"
    And I see field "1/alertSubTypes/1/events/3/notificationCount" filled with 0
    And I see field "1/alertSubTypes/1/events/3/teamType" filled with "client"
    And I see field "1/alertSubTypes/2/name" filled with "Digital Forms"
    And I see field "1/alertSubTypes/2/permissions" filled with "digital_form_list"
    And I see field "1/alertSubTypes/2/events/0/alias" filled with "Failed inspection"
    And I see field "1/alertSubTypes/2/events/0/eventSource" filled with "digitalform"
    And I see field "1/alertSubTypes/2/events/0/notificationCount" filled with 0
    And I see field "1/alertSubTypes/2/events/0/teamType" filled with "client"
    And I see field "1/alertSubTypes/2/events/1/alias" filled with "Digital form is not completed"
    And I see field "1/alertSubTypes/2/events/1/eventSource" filled with "vehicle"
    And I see field "1/alertSubTypes/2/events/1/notificationCount" filled with 0
    And I see field "1/alertSubTypes/2/events/1/teamType" filled with "client"
    And I see field "1/alertSubTypes/3/name" filled with "Driver activities"
    And I see field "1/alertSubTypes/3/permissions" filled with "driver_list"
    And I see field "1/alertSubTypes/3/events/0/alias" filled with "New driver assigned to vehicle"
    And I see field "1/alertSubTypes/3/events/0/eventSource" filled with "vehicle"
    And I see field "1/alertSubTypes/3/events/0/notificationCount" filled with 0
    And I see field "1/alertSubTypes/3/events/0/teamType" filled with "client"
    And I see field "1/alertSubTypes/3/events/1/alias" filled with "Driver route undefined"
    And I see field "1/alertSubTypes/3/events/1/eventSource" filled with "vehicle"
    And I see field "1/alertSubTypes/3/events/1/notificationCount" filled with 0
    And I see field "1/alertSubTypes/3/events/1/teamType" filled with "client"
    And I see field "1/alertSubTypes/4/name" filled with "Sensor Data"
    And I see field "1/alertSubTypes/4/permissions" filled with "vehicle_list"
    And I see field "1/alertSubTypes/4/events/0/alias" filled with "Sensor temperature"
    And I see field "1/alertSubTypes/4/events/0/eventSource" filled with "trackerhistorysensor"
    And I see field "1/alertSubTypes/4/events/0/notificationCount" filled with 0
    And I see field "1/alertSubTypes/4/events/0/teamType" filled with "client"
    And I see field "1/alertSubTypes/4/events/1/alias" filled with "Sensor humidity"
    And I see field "1/alertSubTypes/4/events/1/eventSource" filled with "trackerhistorysensor"
    And I see field "1/alertSubTypes/4/events/1/notificationCount" filled with 0
    And I see field "1/alertSubTypes/4/events/1/teamType" filled with "client"
    And I see field "1/alertSubTypes/4/events/2/alias" filled with "Sensor light"
    And I see field "1/alertSubTypes/4/events/2/eventSource" filled with "trackerhistorysensor"
    And I see field "1/alertSubTypes/4/events/2/notificationCount" filled with 0
    And I see field "1/alertSubTypes/4/events/2/teamType" filled with "client"
    And I see field "1/alertSubTypes/4/events/3/alias" filled with "Sensor battery level"
    And I see field "1/alertSubTypes/4/events/3/eventSource" filled with "trackerhistorysensor"
    And I see field "1/alertSubTypes/4/events/3/notificationCount" filled with 0
    And I see field "1/alertSubTypes/4/events/3/teamType" filled with "client"
    And I see field "1/alertSubTypes/4/events/4/alias" filled with "Sensor status"
    And I see field "1/alertSubTypes/4/events/4/eventSource" filled with "trackerhistorysensor"
    And I see field "1/alertSubTypes/4/events/4/notificationCount" filled with 0
    And I see field "1/alertSubTypes/4/events/4/teamType" filled with "client"
    And I see field "1/alertSubTypes/4/events/5/alias" filled with "SOS button"
    And I see field "1/alertSubTypes/4/events/5/eventSource" filled with "device"
    And I see field "1/alertSubTypes/4/events/5/notificationCount" filled with 0
    And I see field "1/alertSubTypes/4/events/5/teamType" filled with "client"
    And I see field "1/alertSubTypes/5/name" filled with "Driver Documents"
    And I see field "1/alertSubTypes/5/permissions" filled with "document_list"
    And I see field "1/alertSubTypes/5/events/0/alias" filled with "Document expiring soon"
    And I see field "1/alertSubTypes/5/events/0/eventSource" filled with "document"
    And I see field "1/alertSubTypes/5/events/0/notificationCount" filled with 1
    And I see field "1/alertSubTypes/5/events/0/teamType" filled with "client"
    And I see field "1/alertSubTypes/5/events/1/alias" filled with "Document expired"
    And I see field "1/alertSubTypes/5/events/1/eventSource" filled with "document"
    And I see field "1/alertSubTypes/5/events/1/notificationCount" filled with 1
    And I see field "1/alertSubTypes/5/events/1/teamType" filled with "client"
    And I see field "1/alertSubTypes/5/events/2/alias" filled with "Document deleted"
    And I see field "1/alertSubTypes/5/events/2/eventSource" filled with "document"
    And I see field "1/alertSubTypes/5/events/2/notificationCount" filled with 0
    And I see field "1/alertSubTypes/5/events/2/teamType" filled with "client"
    And I see field "1/alertSubTypes/5/events/3/alias" filled with "Document added"
    And I see field "1/alertSubTypes/5/events/3/eventSource" filled with "document"
    And I see field "1/alertSubTypes/5/events/3/notificationCount" filled with 0
    And I see field "1/alertSubTypes/5/events/3/teamType" filled with "client"
    And I see field "1/alertSubTypes/6/name" filled with "Vehicle status"
    And I see field "1/alertSubTypes/6/permissions" filled with "vehicle_list"
    And I see field "1/alertSubTypes/6/events/0/alias" filled with "Vehicle added"
    And I see field "1/alertSubTypes/6/events/0/eventSource" filled with "vehicle"
    And I see field "1/alertSubTypes/6/events/0/notificationCount" filled with 1
    And I see field "1/alertSubTypes/6/events/0/teamType" filled with "client"
    And I see field "1/alertSubTypes/6/events/1/alias" filled with "Vehicle deleted"
    And I see field "1/alertSubTypes/6/events/1/eventSource" filled with "vehicle"
    And I see field "1/alertSubTypes/6/events/1/notificationCount" filled with 1
    And I see field "1/alertSubTypes/6/events/1/teamType" filled with "client"
    And I see field "1/alertSubTypes/6/events/2/alias" filled with "Vehicle unavailable"
    And I see field "1/alertSubTypes/6/events/2/eventSource" filled with "vehicle"
    And I see field "1/alertSubTypes/6/events/2/notificationCount" filled with 1
    And I see field "1/alertSubTypes/6/events/2/teamType" filled with "client"
    And I see field "1/alertSubTypes/6/events/3/alias" filled with "Vehicle offline"
    And I see field "1/alertSubTypes/6/events/3/eventSource" filled with "vehicle"
    And I see field "1/alertSubTypes/6/events/3/notificationCount" filled with 1
    And I see field "1/alertSubTypes/6/events/3/teamType" filled with "client"
    And I see field "1/alertSubTypes/6/events/4/alias" filled with "Vehicle online"
    And I see field "1/alertSubTypes/6/events/4/eventSource" filled with "vehicle"
    And I see field "1/alertSubTypes/6/events/4/notificationCount" filled with 1
    And I see field "1/alertSubTypes/6/events/4/teamType" filled with "client"
    And I see field "1/alertSubTypes/7/name" filled with "Areas"
    And I see field "1/alertSubTypes/7/permissions" filled with "area_list"
    And I see field "1/alertSubTypes/7/events/0/alias" filled with "Vehicle entered area"
    And I see field "1/alertSubTypes/7/events/0/eventSource" filled with "vehicle"
    And I see field "1/alertSubTypes/7/events/0/notificationCount" filled with 0
    And I see field "1/alertSubTypes/7/events/0/teamType" filled with "client"
    And I see field "1/alertSubTypes/7/events/1/alias" filled with "Vehicle left area"
    And I see field "1/alertSubTypes/7/events/1/eventSource" filled with "vehicle"
    And I see field "1/alertSubTypes/7/events/1/notificationCount" filled with 0
    And I see field "1/alertSubTypes/7/events/1/teamType" filled with "client"
    And I see field "1/alertSubTypes/7/events/2/alias" filled with "Vehicle overspeeding inside area"
    And I see field "1/alertSubTypes/7/events/2/eventSource" filled with "vehicle"
    And I see field "1/alertSubTypes/7/events/2/notificationCount" filled with 0
    And I see field "1/alertSubTypes/7/events/2/teamType" filled with "client"
    And I see field "1/alertSubTypes/8/name" filled with "Service reminders"
    And I see field "1/alertSubTypes/8/permissions" filled with "reminder_list"
    And I see field "1/alertSubTypes/8/events/0/alias" filled with "Service due soon"
    And I see field "1/alertSubTypes/8/events/0/eventSource" filled with "reminder"
    And I see field "1/alertSubTypes/8/events/0/notificationCount" filled with 0
    And I see field "1/alertSubTypes/8/events/0/teamType" filled with "client"
    And I see field "1/alertSubTypes/8/events/1/alias" filled with "Service overdue"
    And I see field "1/alertSubTypes/8/events/1/eventSource" filled with "reminder"
    And I see field "1/alertSubTypes/8/events/1/notificationCount" filled with 2
    And I see field "1/alertSubTypes/8/events/1/teamType" filled with "client"
    And I see field "1/alertSubTypes/8/events/2/alias" filled with "Service reminder deleted"
    And I see field "1/alertSubTypes/8/events/2/eventSource" filled with "reminder"
    And I see field "1/alertSubTypes/8/events/2/notificationCount" filled with 0
    And I see field "1/alertSubTypes/8/events/2/teamType" filled with "client"
    And I see field "1/alertSubTypes/8/events/3/alias" filled with "Service reminder added"
    And I see field "1/alertSubTypes/8/events/3/eventSource" filled with "reminder"
    And I see field "1/alertSubTypes/8/events/3/notificationCount" filled with 0
    And I see field "1/alertSubTypes/8/events/3/teamType" filled with "client"
    And I see field "1/alertSubTypes/8/events/4/alias" filled with "Service completed"
    And I see field "1/alertSubTypes/8/events/4/eventSource" filled with "reminder"
    And I see field "1/alertSubTypes/8/events/4/notificationCount" filled with 0
    And I see field "1/alertSubTypes/8/events/4/teamType" filled with "client"
    And I see field "1/alertSubTypes/8/events/5/alias" filled with "Repair Added"
    And I see field "1/alertSubTypes/8/events/5/eventSource" filled with "reminder"
    And I see field "1/alertSubTypes/8/events/5/notificationCount" filled with 0
    And I see field "1/alertSubTypes/8/events/5/teamType" filled with "client"
    And I see field "1/alertSubTypes/9/name" filled with "Vehicle data"
    And I see field "1/alertSubTypes/9/permissions" filled with "vehicle_list"
    And I see field "1/alertSubTypes/9/events/0/alias" filled with "Vehicle rego number changed"
    And I see field "1/alertSubTypes/9/events/0/eventSource" filled with "vehicle"
    And I see field "1/alertSubTypes/9/events/0/notificationCount" filled with 0
    And I see field "1/alertSubTypes/9/events/0/teamType" filled with "client"
    And I see field "1/alertSubTypes/9/events/1/alias" filled with "Vehicle make/model change"
    And I see field "1/alertSubTypes/9/events/1/eventSource" filled with "vehicle"
    And I see field "1/alertSubTypes/9/events/1/notificationCount" filled with 0
    And I see field "1/alertSubTypes/9/events/1/teamType" filled with "client"
    And I see field "1/alertSubTypes/9/events/2/alias" filled with "Vehicle odometer corrected"
    And I see field "1/alertSubTypes/9/events/2/eventSource" filled with "vehicle"
    And I see field "1/alertSubTypes/9/events/2/notificationCount" filled with 0
    And I see field "1/alertSubTypes/9/events/2/teamType" filled with "client"
    And I see field "1/alertSubTypes/10/name" filled with "User accounts"
    And I see field "1/alertSubTypes/10/permissions" filled with "client_users_list"
    And I see field "1/alertSubTypes/10/events/0/alias" filled with "User created"
    And I see field "1/alertSubTypes/10/events/0/eventSource" filled with "user"
    And I see field "1/alertSubTypes/10/events/0/notificationCount" filled with 1
    And I see field "1/alertSubTypes/10/events/0/teamType" filled with "client"
    And I see field "1/alertSubTypes/10/events/1/alias" filled with "User blocked"
    And I see field "1/alertSubTypes/10/events/1/eventSource" filled with "user"
    And I see field "1/alertSubTypes/10/events/1/notificationCount" filled with 1
    And I see field "1/alertSubTypes/10/events/1/teamType" filled with "client"
    And I see field "1/alertSubTypes/10/events/2/alias" filled with "User deleted"
    And I see field "1/alertSubTypes/10/events/2/eventSource" filled with "user"
    And I see field "1/alertSubTypes/10/events/2/notificationCount" filled with 1
    And I see field "1/alertSubTypes/10/events/2/teamType" filled with "client"
    And I see field "1/alertSubTypes/10/events/3/alias" filled with "User password reset"
    And I see field "1/alertSubTypes/10/events/3/eventSource" filled with "user"
    And I see field "1/alertSubTypes/10/events/3/notificationCount" filled with 1
    And I see field "1/alertSubTypes/10/events/3/teamType" filled with "client"
    And I see field "1/alertSubTypes/10/events/4/alias" filled with "User changed name"
    And I see field "1/alertSubTypes/10/events/4/eventSource" filled with "user"
    And I see field "1/alertSubTypes/10/events/4/notificationCount" filled with 0
    And I see field "1/alertSubTypes/10/events/4/teamType" filled with "client"
    Given I signed in as "admin" team "client"
    Then I want get alert setting
    Then response code is 200
    And I see field "0/type" filled with 'client'
    And I see field "0/alertSubTypes/0/name" filled with "Vehicle Activities"
    And I see field "0/alertSubTypes/0/permissions" filled with "vehicle_list"
    And I see field "0/alertSubTypes/0/events/0/alias" filled with "Driving without assigned driver"
    And I see field "0/alertSubTypes/0/events/0/eventSource" filled with "vehicle"
    And I see field "0/alertSubTypes/0/events/0/notificationCount" filled with 0
    And I see field "0/alertSubTypes/0/events/0/teamType" filled with "client"
    And I see field "0/alertSubTypes/0/events/1/alias" filled with "Overspeeding"
    And I see field "0/alertSubTypes/0/events/1/eventSource" filled with "device"
    And I see field "0/alertSubTypes/0/events/1/notificationCount" filled with 0
    And I see field "0/alertSubTypes/0/events/1/teamType" filled with "client"
    And I see field "0/alertSubTypes/0/events/2/alias" filled with "Driving longer than defined"
    And I see field "0/alertSubTypes/0/events/2/eventSource" filled with "vehicle"
    And I see field "0/alertSubTypes/0/events/2/notificationCount" filled with 0
    And I see field "0/alertSubTypes/0/events/2/teamType" filled with "client"
    And I see field "0/alertSubTypes/0/events/3/alias" filled with "Standing longer than defined"
    And I see field "0/alertSubTypes/0/events/3/eventSource" filled with "vehicle"
    And I see field "0/alertSubTypes/0/events/3/notificationCount" filled with 0
    And I see field "0/alertSubTypes/0/events/3/teamType" filled with "client"
    And I see field "0/alertSubTypes/0/events/4/alias" filled with "Vehicle is moving"
    And I see field "0/alertSubTypes/0/events/4/eventSource" filled with "vehicle"
    And I see field "0/alertSubTypes/0/events/4/notificationCount" filled with 0
    And I see field "0/alertSubTypes/0/events/4/teamType" filled with "client"
    And I see field "0/alertSubTypes/0/events/5/alias" filled with "Excess Idling"
    And I see field "0/alertSubTypes/0/events/5/eventSource" filled with "vehicle"
    And I see field "0/alertSubTypes/0/events/5/notificationCount" filled with 0
    And I see field "0/alertSubTypes/0/events/5/teamType" filled with "client"
    And I see field "0/alertSubTypes/0/events/6/alias" filled with "Vehicle being towed"
    And I see field "0/alertSubTypes/0/events/6/eventSource" filled with "device"
    And I see field "0/alertSubTypes/0/events/6/notificationCount" filled with 0
    And I see field "0/alertSubTypes/0/events/6/teamType" filled with "client"
    And I see field "0/alertSubTypes/0/events/7/alias" filled with "Supply Voltage"
    And I see field "0/alertSubTypes/0/events/7/eventSource" filled with "device"
    And I see field "0/alertSubTypes/0/events/7/notificationCount" filled with 0
    And I see field "0/alertSubTypes/0/events/7/teamType" filled with "client"
    And I see field "0/alertSubTypes/1/name" filled with "Vehicle Documents"
    And I see field "0/alertSubTypes/1/permissions" filled with "document_list"
    And I see field "0/alertSubTypes/1/events/0/alias" filled with "Document expiring soon"
    And I see field "0/alertSubTypes/1/events/0/eventSource" filled with "document"
    And I see field "0/alertSubTypes/1/events/0/notificationCount" filled with 0
    And I see field "0/alertSubTypes/1/events/0/teamType" filled with "client"
    And I see field "0/alertSubTypes/1/events/1/alias" filled with "Document expired"
    And I see field "0/alertSubTypes/1/events/1/eventSource" filled with "document"
    And I see field "0/alertSubTypes/1/events/1/notificationCount" filled with 0
    And I see field "0/alertSubTypes/1/events/1/teamType" filled with "client"
    And I see field "0/alertSubTypes/1/events/2/alias" filled with "Document deleted"
    And I see field "0/alertSubTypes/1/events/2/eventSource" filled with "document"
    And I see field "0/alertSubTypes/1/events/2/notificationCount" filled with 0
    And I see field "0/alertSubTypes/1/events/2/teamType" filled with "client"
    And I see field "0/alertSubTypes/1/events/3/alias" filled with "Document added"
    And I see field "0/alertSubTypes/1/events/3/eventSource" filled with "document"
    And I see field "0/alertSubTypes/1/events/3/notificationCount" filled with 0
    And I see field "0/alertSubTypes/1/events/3/teamType" filled with "client"
    And I see field "0/alertSubTypes/2/name" filled with "Digital Forms"
    And I see field "0/alertSubTypes/2/permissions" filled with "digital_form_list"
    And I see field "0/alertSubTypes/2/events/0/alias" filled with "Failed inspection"
    And I see field "0/alertSubTypes/2/events/0/eventSource" filled with "digitalform"
    And I see field "0/alertSubTypes/2/events/0/notificationCount" filled with 0
    And I see field "0/alertSubTypes/2/events/0/teamType" filled with "client"
    And I see field "0/alertSubTypes/2/events/1/alias" filled with "Digital form is not completed"
    And I see field "0/alertSubTypes/2/events/1/eventSource" filled with "vehicle"
    And I see field "0/alertSubTypes/2/events/1/notificationCount" filled with 0
    And I see field "0/alertSubTypes/2/events/1/teamType" filled with "client"
    And I see field "0/alertSubTypes/3/name" filled with "Driver activities"
    And I see field "0/alertSubTypes/3/permissions" filled with "driver_list"
    And I see field "0/alertSubTypes/3/events/0/alias" filled with "New driver assigned to vehicle"
    And I see field "0/alertSubTypes/3/events/0/eventSource" filled with "vehicle"
    And I see field "0/alertSubTypes/3/events/0/notificationCount" filled with 0
    And I see field "0/alertSubTypes/3/events/0/teamType" filled with "client"
    And I see field "0/alertSubTypes/3/events/1/alias" filled with "Driver route undefined"
    And I see field "0/alertSubTypes/3/events/1/eventSource" filled with "vehicle"
    And I see field "0/alertSubTypes/3/events/1/notificationCount" filled with 0
    And I see field "0/alertSubTypes/3/events/1/teamType" filled with "client"
    And I see field "0/alertSubTypes/4/name" filled with "Sensor Data"
    And I see field "0/alertSubTypes/4/permissions" filled with "vehicle_list"
    And I see field "0/alertSubTypes/4/events/0/alias" filled with "Sensor temperature"
    And I see field "0/alertSubTypes/4/events/0/eventSource" filled with "trackerhistorysensor"
    And I see field "0/alertSubTypes/4/events/0/notificationCount" filled with 0
    And I see field "0/alertSubTypes/4/events/0/teamType" filled with "client"
    And I see field "0/alertSubTypes/4/events/1/alias" filled with "Sensor humidity"
    And I see field "0/alertSubTypes/4/events/1/eventSource" filled with "trackerhistorysensor"
    And I see field "0/alertSubTypes/4/events/1/notificationCount" filled with 0
    And I see field "0/alertSubTypes/4/events/1/teamType" filled with "client"
    And I see field "0/alertSubTypes/4/events/2/alias" filled with "Sensor light"
    And I see field "0/alertSubTypes/4/events/2/eventSource" filled with "trackerhistorysensor"
    And I see field "0/alertSubTypes/4/events/2/notificationCount" filled with 0
    And I see field "0/alertSubTypes/4/events/2/teamType" filled with "client"
    And I see field "0/alertSubTypes/4/events/3/alias" filled with "Sensor battery level"
    And I see field "0/alertSubTypes/4/events/3/eventSource" filled with "trackerhistorysensor"
    And I see field "0/alertSubTypes/4/events/3/notificationCount" filled with 0
    And I see field "0/alertSubTypes/4/events/3/teamType" filled with "client"
    And I see field "0/alertSubTypes/4/events/4/alias" filled with "Sensor status"
    And I see field "0/alertSubTypes/4/events/4/eventSource" filled with "trackerhistorysensor"
    And I see field "0/alertSubTypes/4/events/4/notificationCount" filled with 0
    And I see field "0/alertSubTypes/4/events/4/teamType" filled with "client"
    And I see field "0/alertSubTypes/4/events/5/alias" filled with "SOS button"
    And I see field "0/alertSubTypes/4/events/5/eventSource" filled with "device"
    And I see field "0/alertSubTypes/4/events/5/notificationCount" filled with 0
    And I see field "0/alertSubTypes/4/events/5/teamType" filled with "client"
    And I see field "0/alertSubTypes/5/name" filled with "Driver Documents"
    And I see field "0/alertSubTypes/5/permissions" filled with "document_list"
    And I see field "0/alertSubTypes/5/events/0/alias" filled with "Document expiring soon"
    And I see field "0/alertSubTypes/5/events/0/eventSource" filled with "document"
    And I see field "0/alertSubTypes/5/events/0/notificationCount" filled with 0
    And I see field "0/alertSubTypes/5/events/0/teamType" filled with "client"
    And I see field "0/alertSubTypes/5/events/1/alias" filled with "Document expired"
    And I see field "0/alertSubTypes/5/events/1/eventSource" filled with "document"
    And I see field "0/alertSubTypes/5/events/1/notificationCount" filled with 0
    And I see field "0/alertSubTypes/5/events/1/teamType" filled with "client"
    And I see field "0/alertSubTypes/5/events/2/alias" filled with "Document deleted"
    And I see field "0/alertSubTypes/5/events/2/eventSource" filled with "document"
    And I see field "0/alertSubTypes/5/events/2/notificationCount" filled with 0
    And I see field "0/alertSubTypes/5/events/2/teamType" filled with "client"
    And I see field "0/alertSubTypes/5/events/3/alias" filled with "Document added"
    And I see field "0/alertSubTypes/5/events/3/eventSource" filled with "document"
    And I see field "0/alertSubTypes/5/events/3/notificationCount" filled with 0
    And I see field "0/alertSubTypes/5/events/3/teamType" filled with "client"
    And I see field "0/alertSubTypes/6/name" filled with "Vehicle status"
    And I see field "0/alertSubTypes/6/permissions" filled with "vehicle_list"
    And I see field "0/alertSubTypes/6/events/0/alias" filled with "Vehicle added"
    And I see field "0/alertSubTypes/6/events/0/eventSource" filled with "vehicle"
    And I see field "0/alertSubTypes/6/events/0/notificationCount" filled with 0
    And I see field "0/alertSubTypes/6/events/0/teamType" filled with "client"
    And I see field "0/alertSubTypes/6/events/1/alias" filled with "Vehicle deleted"
    And I see field "0/alertSubTypes/6/events/1/eventSource" filled with "vehicle"
    And I see field "0/alertSubTypes/6/events/1/notificationCount" filled with 0
    And I see field "0/alertSubTypes/6/events/1/teamType" filled with "client"
    And I see field "0/alertSubTypes/6/events/2/alias" filled with "Vehicle unavailable"
    And I see field "0/alertSubTypes/6/events/2/eventSource" filled with "vehicle"
    And I see field "0/alertSubTypes/6/events/2/notificationCount" filled with 0
    And I see field "0/alertSubTypes/6/events/2/teamType" filled with "client"
    And I see field "0/alertSubTypes/6/events/3/alias" filled with "Vehicle offline"
    And I see field "0/alertSubTypes/6/events/3/eventSource" filled with "vehicle"
    And I see field "0/alertSubTypes/6/events/3/notificationCount" filled with 0
    And I see field "0/alertSubTypes/6/events/3/teamType" filled with "client"
    And I see field "0/alertSubTypes/6/events/4/alias" filled with "Vehicle online"
    And I see field "0/alertSubTypes/6/events/4/eventSource" filled with "vehicle"
    And I see field "0/alertSubTypes/6/events/4/notificationCount" filled with 0
    And I see field "0/alertSubTypes/6/events/4/teamType" filled with "client"
    And I see field "0/alertSubTypes/7/name" filled with "Areas"
    And I see field "0/alertSubTypes/7/permissions" filled with "area_list"
    And I see field "0/alertSubTypes/7/events/0/alias" filled with "Vehicle entered area"
    And I see field "0/alertSubTypes/7/events/0/eventSource" filled with "vehicle"
    And I see field "0/alertSubTypes/7/events/0/notificationCount" filled with 0
    And I see field "0/alertSubTypes/7/events/0/teamType" filled with "client"
    And I see field "0/alertSubTypes/7/events/1/alias" filled with "Vehicle left area"
    And I see field "0/alertSubTypes/7/events/1/eventSource" filled with "vehicle"
    And I see field "0/alertSubTypes/7/events/1/notificationCount" filled with 0
    And I see field "0/alertSubTypes/7/events/1/teamType" filled with "client"
    And I see field "0/alertSubTypes/7/events/2/alias" filled with "Vehicle overspeeding inside area"
    And I see field "0/alertSubTypes/7/events/2/eventSource" filled with "vehicle"
    And I see field "0/alertSubTypes/7/events/2/notificationCount" filled with 0
    And I see field "0/alertSubTypes/7/events/2/teamType" filled with "client"
    And I see field "0/alertSubTypes/8/name" filled with "Service reminders"
    And I see field "0/alertSubTypes/8/permissions" filled with "reminder_list"
    And I see field "0/alertSubTypes/8/events/0/alias" filled with "Service due soon"
    And I see field "0/alertSubTypes/8/events/0/eventSource" filled with "reminder"
    And I see field "0/alertSubTypes/8/events/0/notificationCount" filled with 0
    And I see field "0/alertSubTypes/8/events/0/teamType" filled with "client"
    And I see field "0/alertSubTypes/8/events/1/alias" filled with "Service overdue"
    And I see field "0/alertSubTypes/8/events/1/eventSource" filled with "reminder"
    And I see field "0/alertSubTypes/8/events/1/notificationCount" filled with 0
    And I see field "0/alertSubTypes/8/events/1/teamType" filled with "client"
    And I see field "0/alertSubTypes/8/events/2/alias" filled with "Service reminder deleted"
    And I see field "0/alertSubTypes/8/events/2/eventSource" filled with "reminder"
    And I see field "0/alertSubTypes/8/events/2/notificationCount" filled with 0
    And I see field "0/alertSubTypes/8/events/2/teamType" filled with "client"
    And I see field "0/alertSubTypes/8/events/3/alias" filled with "Service reminder added"
    And I see field "0/alertSubTypes/8/events/3/eventSource" filled with "reminder"
    And I see field "0/alertSubTypes/8/events/3/notificationCount" filled with 0
    And I see field "0/alertSubTypes/8/events/3/teamType" filled with "client"
    And I see field "0/alertSubTypes/8/events/4/alias" filled with "Service completed"
    And I see field "0/alertSubTypes/8/events/4/eventSource" filled with "reminder"
    And I see field "0/alertSubTypes/8/events/4/notificationCount" filled with 0
    And I see field "0/alertSubTypes/8/events/4/teamType" filled with "client"
    And I see field "0/alertSubTypes/8/events/5/alias" filled with "Repair Added"
    And I see field "0/alertSubTypes/8/events/5/eventSource" filled with "reminder"
    And I see field "0/alertSubTypes/8/events/5/notificationCount" filled with 0
    And I see field "0/alertSubTypes/8/events/5/teamType" filled with "client"
    And I see field "0/alertSubTypes/9/name" filled with "Vehicle data"
    And I see field "0/alertSubTypes/9/permissions" filled with "vehicle_list"
    And I see field "0/alertSubTypes/9/events/0/alias" filled with "Vehicle rego number changed"
    And I see field "0/alertSubTypes/9/events/0/eventSource" filled with "vehicle"
    And I see field "0/alertSubTypes/9/events/0/notificationCount" filled with 0
    And I see field "0/alertSubTypes/9/events/0/teamType" filled with "client"
    And I see field "0/alertSubTypes/9/events/1/alias" filled with "Vehicle make/model change"
    And I see field "0/alertSubTypes/9/events/1/eventSource" filled with "vehicle"
    And I see field "0/alertSubTypes/9/events/1/notificationCount" filled with 0
    And I see field "0/alertSubTypes/9/events/1/teamType" filled with "client"
    And I see field "0/alertSubTypes/9/events/2/alias" filled with "Vehicle odometer corrected"
    And I see field "0/alertSubTypes/9/events/2/eventSource" filled with "vehicle"
    And I see field "0/alertSubTypes/9/events/2/notificationCount" filled with 0
    And I see field "0/alertSubTypes/9/events/2/teamType" filled with "client"
    And I see field "0/alertSubTypes/10/name" filled with "User accounts"
    And I see field "0/alertSubTypes/10/permissions" filled with "client_users_list"
    And I see field "0/alertSubTypes/10/events/0/alias" filled with "User created"
    And I see field "0/alertSubTypes/10/events/0/eventSource" filled with "user"
    And I see field "0/alertSubTypes/10/events/0/notificationCount" filled with 0
    And I see field "0/alertSubTypes/10/events/0/teamType" filled with "client"
    And I see field "0/alertSubTypes/10/events/1/alias" filled with "User blocked"
    And I see field "0/alertSubTypes/10/events/1/eventSource" filled with "user"
    And I see field "0/alertSubTypes/10/events/1/notificationCount" filled with 0
    And I see field "0/alertSubTypes/10/events/1/teamType" filled with "client"
    And I see field "0/alertSubTypes/10/events/2/alias" filled with "User deleted"
    And I see field "0/alertSubTypes/10/events/2/eventSource" filled with "user"
    And I see field "0/alertSubTypes/10/events/2/notificationCount" filled with 0
    And I see field "0/alertSubTypes/10/events/2/teamType" filled with "client"
    And I see field "0/alertSubTypes/10/events/3/alias" filled with "User password reset"
    And I see field "0/alertSubTypes/10/events/3/eventSource" filled with "user"
    And I see field "0/alertSubTypes/10/events/3/notificationCount" filled with 0
    And I see field "0/alertSubTypes/10/events/3/teamType" filled with "client"
    And I see field "0/alertSubTypes/10/events/4/alias" filled with "User changed name"
    And I see field "0/alertSubTypes/10/events/4/eventSource" filled with "user"
    And I see field "0/alertSubTypes/10/events/4/notificationCount" filled with 0
    And I see field "0/alertSubTypes/10/events/4/teamType" filled with "client"

  Scenario: I want check permissions for get alert setting
    Then I signed in as "driver" team "client"
    And I want get alert setting
    Then response code is 403
    And I see field "errors/0/detail" filled with "Access Denied."