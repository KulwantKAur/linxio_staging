Feature: Users

  Scenario: I want test server
    When I want to get access to server
    Then I see field "status" filled with "Ok"
    And response code is 200

  Scenario: I want to register and login without 2FA
    Given I signed in as "super_admin" team "admin"
    When I want fill "email" field with "alextest@gmail.test"
    And I want fill "name" field with "Alex"
    And I want fill "surname" field with "AL"
    And I want fill "teamType" field with "admin"
    And I want fill "roleId" field with "2"
    And I want fill "position" field with "pos"
    And I want upload avatar
    And I want fill "phone" field with "+11(23) 123-123-21"
    When I want handle notification event 'ADMIN_USER_CREATED' 'client_manager_user@gmail.test'
    When I want register
    Then I want set user password "alextest@gmail.test" "1aAw!awaw" in DB
    Then I see field "email" filled with "alextest@gmail.test"
    And I see field "name" filled with "Alex"
    And I see field "surname" filled with "AL"
    And I see field "teamType" filled with "admin"
    And I see field "position" filled with "pos"
    And I want check "picture" is not empty
    And I see field "phone" filled with "+11(23) 123-123-21"
    And I see field "status" filled with "new"
    And response code is 200
    And I want verify user phone by email "alextest@gmail.test"
    When I want login without 2FA "alextest@gmail.test" "1aAw!awaw"
    Then I see field "token"
    Then I see field "refreshToken"
    And I see field "otp_required" filled with false
    And response code is 200
    When I want check token
    Then  I see field "email" filled with "alextest@gmail.test"
    And response code is 200
    Then I want refresh token
    Then I see field "token"
    Then I see field "refreshToken"
    When I want check token
    Then  I see field "email" filled with "alextest@gmail.test"

  Scenario: I want to register and login
    Given I signed in as "super_admin" team "admin"
    When I want fill "email" field with "alextest@gmail.test"
    And I want fill "name" field with "Alex"
    And I want fill "surname" field with "AL"
    And I want fill "teamType" field with "admin"
    And I want fill "roleId" field with 1
    And I want fill "position" field with "pos"
    And I want upload avatar
    And I want fill "phone" field with "+11(23) 123-123-21"
    When I want handle notification event 'ADMIN_USER_CREATED' 'client_manager_user@gmail.test'
    When I want register
    Then I want set user password "alextest@gmail.test" "1aAw!awaw" in DB
    Then I see field "email" filled with "alextest@gmail.test"
    And I see field "name" filled with "Alex"
    And I see field "surname" filled with "AL"
    And I see field "teamType" filled with "admin"
    And I see field "position" filled with "pos"
    And I want check "picture" is not empty
    And I see field "phone" filled with "+11(23) 123-123-21"
    And I see field "status" filled with "new"
    And response code is 200
    And I want verify user phone by email "alextest@gmail.test"
    When I want login "alextest@gmail.test" "1aAw!awaw"
    Then I see field "otp_required" filled with true
    And I see field "phone" filled with "+**(**) ***-***-21"
    And response code is 200
    And I want fill "deviceId" field with "device-id-1234"
    When I want verify otp "1234"
    Then I see field "token"
    Then I see field "refreshToken"
    And response code is 200
    When I want check token
    Then  I see field "email" filled with "alextest@gmail.test"
    And response code is 200
    # Login with deviceID
    When I want login "alextest@gmail.test" "1aAw!awaw"
    Then I see field "otp_required" filled with false

  Scenario: I want login with wrong token
    When I want login with wrong token
    Then response code is 401

  Scenario: I want to register, login and verify OTP with expired code
    Given I signed in as "super_admin" team "admin"
    When I want fill "email" field with "alextest@gmail.test"
    And I want fill "name" field with "Alex"
    And I want fill "surname" field with "AL"
    And I want fill "teamType" field with "admin"
    And I want fill "roleId" field with "2"
    And I want fill "position" field with "pos"
    And I want fill "phone" field with "+11(23) 123-123-21"
    When I want register
    Then I want set user password "alextest@gmail.test" "1aAw!awaw" in DB
    Then I see field "email" filled with "alextest@gmail.test"
    And I see field "name" filled with "Alex"
    And I see field "surname" filled with "AL"
    And I see field "teamType" filled with "admin"
    And I see field "position" filled with "pos"
    And I see field "phone" filled with "+11(23) 123-123-21"
    And I see field "status" filled with "new"
    And response code is 200
    And I want verify user phone by email "alextest@gmail.test"
    When I want login "alextest@gmail.test" "1aAw!awaw"
    Then I see field "otp_required" filled with true
    And response code is 200
    When I want verify otp with expired code "1234"
    Then I see field "errors"
    And response code is 401

  Scenario: I want get current user info
    When I signed in as "super_admin" team "admin"
    Then I want get current user info
    And I see field "email" filled with "super_admin@user.com"
    And I see field "name" filled with "test user"
    And I see field "permissions"
    And I want check "timezone/offset" is not empty

  Scenario: I want register admin team user
    When I signed in as "super_admin" team "admin"
    And I want fill "email" field with "admin_team_user@gmail.test"
    And I want fill "name" field with "Alex"
    And I want fill "phone" field with "+6(4)654654"
    And I want fill "roleId" field with "2"
    Then I want register admin team user
    And I see field "email" filled with "admin_team_user@gmail.test"
    And I see field "name" filled with "Alex"
    And I see field "teamType" filled with "admin"
    And I see field "roleId" filled with 2
    And I see field "phone" filled with "+6(4)654654"

  Scenario: I want login first time
    Given I signed in as "super_admin" team "admin"
    When I want fill "email" field with "new-user@gmail.test"
    And I want fill "name" field with "new-user"
    And I want fill "surname" field with "new-user"
    And I want fill "teamType" field with "admin"
    And I want fill "position" field with "pos"
    And I want fill "phone" field with "+11(23) 123-123-21"
    And I want fill "roleId" field with "2"
    When I want register
    Then I want set user password "new-user@gmail.test" "1aAw!awaw" in DB
    And response code is 200
    And I want verify user phone by email "new-user@gmail.test"
    And I see in DB field "status" filled with "new" for user with "new-user@gmail.test"
    When I want login without 2FA "new-user@gmail.test" "1aAw!awaw"
    Then I see field "token"
    And I see field "otp_required" filled with false
    And response code is 200
    When I want check token
    Then I want get current user info
    And response code is 200
    And I see in DB field "status" filled with "active" for user with "new-user@gmail.test"

  Scenario: I want login with blocked user
    Given I signed in as "super_admin" team "admin"
    When I want fill "email" field with "new-user@gmail.test"
    And I want fill "name" field with "new-user"
    And I want fill "surname" field with "new-user"
    And I want fill "teamType" field with "admin"
    And I want fill "position" field with "pos"
    And I want fill "phone" field with "+11(23) 123-123-21"
    And I want fill "roleId" field with "2"
    When I want register
    Then I want set user password "new-user@gmail.test" "1aAw!awaw" in DB
    And response code is 200
    And I want verify user phone by email "new-user@gmail.test"
    When I want set "status" field with "blocked" for user with email "new-user@gmail.test"
    When I want set "blockingMessage" field with "blocked text message" for user with email "new-user@gmail.test"
    And I see in DB field "status" filled with "blocked" for user with "new-user@gmail.test"
    When I want login without 2FA "new-user@gmail.test" "1aAw!awaw"
    And I see field "blocked" filled with true
    And I see field "message" filled with "blocked text message"

  Scenario: I want to get the data for the user who was blocked
    Given I signed in as "super_admin" team "admin"
    When I want fill "email" field with "new-user@gmail.test"
    And I want fill "name" field with "new-user"
    And I want fill "surname" field with "new-user"
    And I want fill "teamType" field with "admin"
    And I want fill "position" field with "pos"
    And I want fill "phone" field with "+11(23) 123-123-21"
    And I want fill "roleId" field with "2"
    When I want register
    Then I want set user password "new-user@gmail.test" "1aAw!awaw" in DB
    And response code is 200
    And I want verify user phone by email "new-user@gmail.test"
    And I see in DB field "status" filled with "new" for user with "new-user@gmail.test"
    When I want login without 2FA "new-user@gmail.test" "1aAw!awaw"
    Then I see field "token"
    And I see field "otp_required" filled with false
    And response code is 200
    When I want check token
    Then I want get current user info
    And response code is 200
    And I see in DB field "status" filled with "active" for user with "new-user@gmail.test"
    When I want set "status" field with "blocked" for user with email "new-user@gmail.test"
    When I want set "blockingMessage" field with "blocked text message" for user with email "new-user@gmail.test"
    And I see in DB field "status" filled with "blocked" for user with "new-user@gmail.test"
    Then I want get current user info
    And I see field "blocked" filled with true
    And I see field "message" filled with "blocked text message"

  Scenario: I want login with deleted user
    Given I signed in as "super_admin" team "admin"
    When I want fill "email" field with "new-user@gmail.test"
    And I want fill "name" field with "new-user"
    And I want fill "surname" field with "new-user"
    And I want fill "teamType" field with "admin"
    And I want fill "position" field with "pos"
    And I want fill "phone" field with "+11(23) 123-123-21"
    And I want fill "roleId" field with "2"
    When I want register
    Then I want set user password "new-user@gmail.test" "1aAw!awaw" in DB
    And response code is 200
    And I want verify user phone by email "new-user@gmail.test"
    When I want set "status" field with "deleted" for user with email "new-user@gmail.test"
    And I see in DB field "status" filled with "deleted" for user with "new-user@gmail.test"
    When I want login without 2FA "new-user@gmail.test" "1aAw!awaw"
    And response code is 401

  Scenario: I want login with unverified phone user
    Given I signed in as "super_admin" team "admin"
    When I want fill "email" field with "new-user@gmail.test"
    And I want fill "name" field with "new-user"
    And I want fill "surname" field with "new-user"
    And I want fill "teamType" field with "admin"
    And I want fill "position" field with "pos"
    And I want fill "phone" field with "+11(23) 123-123-21"
    And I want fill "roleId" field with "2"
    When I want register
    Then I want set user password "new-user@gmail.test" "1aAw!awaw" in DB
    And response code is 200
    When I want login without 2FA "new-user@gmail.test" "1aAw!awaw"
    Then I see field "isPhoneVerified" filled with false
    And I see field "verifyToken"
    And I see field "phone"

  Scenario: I want edit Admin Team User
    When I signed in as "super_admin" team "admin"
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

  Scenario: I want edit Admin Team User with wrong data
    When I signed in as "super_admin" team "admin"
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
    Then I want fill "isBlocked" field with true
    And I want update admin team user data by id with email "client_manager_user@gmail.test"
    And response code is 400
    And I want clean filled data
    Then I want fill key "teamPermissions" with empty array
    And I want update admin team user data by id with email "client_manager_user@gmail.test"
    And I see field "errors"
    And response code is 400
    And I want clean filled data
    Then I want fill "roleId" field with 4
    And I see field "errors"
    And response code is 400

  Scenario: I want block Admin Team User
    When I signed in as "super_admin" team "admin"
    And I want fill "email" field with "client_manager_user@gmail.test"
    And I want fill "name" field with "Alex"
    And I want fill "phone" field with "+6(4)654654"
    And I want fill "roleId" field with "3"
    And I want fill "allTeamsPermissions" field with "false"
    Then I want register client manager user and remember id
    And I see field "email" filled with "client_manager_user@gmail.test"
    Then I want clean filled data
    Then I want fill "isBlocked" field with true
    When I want handle notification event 'USER_BLOCKED' 'client_manager_user@gmail.test'
    And I want update admin team user data by id with email "client_manager_user@gmail.test"

  Scenario: I want add Manager to Clients
    When I signed in as "super_admin" team "admin"
    And I want fill "email" field with "client_manager_user@gmail.test"
    And I want fill "name" field with "Alex"
    And I want fill "phone" field with "+6(4)654654"
    And I want fill "roleId" field with "3"
    And I want fill "allTeamsPermissions" field with "false"
    Then I want register client manager user and remember id
    And I see field "email" filled with "client_manager_user@gmail.test"
    And I see field "roleId" filled with 3
    Then I want clean filled data
    And I want fill "name" field with "client name1"
    And I want fill "taxNr" field with "12345678910"
    And I want fill "planId" field with 1
    And I want fill "timezone" field with 63
    Then I want register client with manager and remember
    And I want clean filled data
    And I want add manager "client_manager_user@gmail.test" saved clients
    And I see field "teamPermission/0/type" filled with "client"

  Scenario: I want delete and restore Admin Team User
    When I signed in as "super_admin" team "admin"
    And I want fill "email" field with "client_manager_user@gmail.test"
    And I want fill "name" field with "Alex"
    And I want fill "phone" field with "+6(4)654654"
    And I want fill "roleId" field with "3"
    And I want fill "allTeamsPermissions" field with "false"
    Then I want register client manager user and remember id
    And I see field "email" filled with "client_manager_user@gmail.test"
    And I see field "roleId" filled with 3
    And I want clean filled data
    When I want handle notification event 'USER_DELETED' 'client_manager_user@gmail.test'
    Then I want delete admin team user by id with email "client_manager_user@gmail.test"
    And response code is 204
    Then I want restore user by id with email "client_manager_user@gmail.test"
    And I see field "status" filled with "new"

  Scenario: I want set invalid team settings
    When I signed in as "super_admin" team "admin"
    And I want set admin team with role "admin" "client" setting otp value 1
    And response code is 400
    And I see field "errors/0/detail/0.roleId/wrong_value" filled with "Wrong value"

  Scenario: I want set and check admin team settings
    When I signed in as "super_admin" team "admin"
    And I want set admin team with role "admin" "admin" setting otp value 1
    And I see field "10/team/type" filled with "admin"
    And I see field "10/value" filled with 1
    And I see field "10/name" filled with "otp"
    Then I want fill "email" field with "admin_team_user@gmail.test"
    And I want fill "name" field with "Alex"
    And I want fill "phone" field with "+6(4)654654"
    And I want fill "roleId" field with "2"
    Then I want register admin team user
    Given Elastica populate
    Then I want get admin team users
    And I see field "data/0/email" filled with "admin_team_user@gmail.test"
    Then I want get admin settings
    And I see field "10/team/type" filled with "admin"
    And I see field "10/value" filled with 1
    And I want set admin team with role "admin" "admin" setting "timezone" value 1
    And I see field "18/team/type" filled with "admin"
    And I see field "18/value" filled with "string(1)"
    And I see field "18/name" filled with "timezone"

  Scenario: I want get setting by key
    When I signed in as "super_admin" team "admin"
    Then I want get setting by key "theme"
    And I see field "name" filled with "theme"

  Scenario: I want get setting by key array
    When I signed in as "super_admin" team "admin"
    Then I want add value to array key "keys" with "otp"
    Then I want add value to array key "keys" with "language"
    Then I want add value to array key "keys" with "ecoSpeed"
    Then I want add value to array key "keys" with "excessiveIdling"
    Then I want get setting by keys of array
    And I see field "3/name" filled with "otp"
    And I see field "2/name" filled with "language"
    And I see field "1/name" filled with "ecoSpeed"
    And I see field "1/value/value" filled with 85
    And I see field "0/name" filled with "excessiveIdling"
    And I see field "0/value/value" filled with 120

  Scenario: I want set and check admin team theme settings
    When I signed in as "super_admin" team "admin"
    And I want set admin team with role "admin" "admin" setting theme value 'dark_theme'
    And I signed in as "admin" team "admin"
    Then I want get my theme
    And I see field "name" filled with "Dark Theme"

  Scenario: I check admin team default theme
    And I signed in as "admin" team "admin"
    Then I want get my theme
    And I see field "name" filled with "Light Theme"

  Scenario: I want to login and logout
    When I signed in as "super_admin" team "admin"
    Then I want get current user info
    And I see field "id"
    And I see field "email"
    Then I want logout
    And response code is 204
    Then I want get current user info
    And response code is 401

  Scenario: I want export admin team list
    And I signed in
    Given Elastica populate
    Then I want add value to array key "fields" with "fullName"
    Then I want add value to array key "fields" with "email"
    Then I want add value to array key "fields" with "role"
    Then I want add value to array key "fields" with "last_logged_at"
    Then I want add value to array key "fields" with "status"
    Then I want export admin team list
    And I see csv item number 0 field "User Name" filled with "Super Admin"
    And I see csv item number 0 field "E-mail" filled with "linxio-dev@ocsico.com"
    And I see csv item number 0 field "Role" filled with "SuperAdmin"
    And I see csv item number 0 field "Last Login" filled with ""
    And I see csv item number 0 field "Status" filled with "Active"
    And response code is 200

  Scenario: I want export client users list by teamId
    When I signed in as "super_admin" team "admin"
    And I want get client team by name "ACME1" and save id
    Then I want add value to array key "fields" with "fullName"
    Then I want add value to array key "fields" with "email"
    Then I want add value to array key "fields" with "role"
    Then I want add value to array key "fields" with "last_logged_at"
    Then I want add value to array key "fields" with "status"
    And I want fill teamId with saved team id
    Then I want export users list by teamId
    And I see csv item number 0 field "User Name" filled with "Acme Admin"
    And I see csv item number 0 field "E-mail" filled with "acme-admin@linxio.local"
    And I see csv item number 0 field "Role" filled with "Admin"
    And I see csv item number 0 field "Last Login" filled with ""
    And I see csv item number 0 field "Status" filled with "Active"
    And response code is 200

  Scenario: I want login as client
    When I signed in as "super_admin" team "admin"
    Then I want get current user info
    And I see field "email" filled with "super_admin@user.com"
    Then I want login as client with name "ACME1"
    And I see field "teamType" filled with "client"
    And I see field "email" filled with "acme-admin@linxio.local"
    And I see field "token"

  Scenario: I want login as user
    When I signed in as "super_admin" team "admin"
    Then I want get current user info
    And I see field "email" filled with "super_admin@user.com"
    Then I want login as user with email "acme-admin@linxio.local"
    And I see field "teamType" filled with "client"
    And I see field "email" filled with "acme-admin@linxio.local"
    And I see field "token"

  Scenario: I want get driver list
    When I signed in as "super_admin" team "admin"
    Then I want get driver list
    And I see field "data/0/role/name" filled with "driver"
    And I see field "data/0/vehicle/device"
    And I see field "data/0/lastRoute"

  Scenario: I want test set mobile device token
    Then I signed with email "acme-admin@linxio.local"
    And I want fill "deviceToken" field with "test token 01"
    When I want set mobile device token
    Then response code is 200
    And I see field "deviceToken" filled with "test token 01"
    And I want clean filled data
    Then I want logout
    And response code is 204
    Then I signed with email "acme-admin@linxio.local"
    And I want fill "deviceToken" field with "test token 02"
    When I want set mobile device token
    And I see field "deviceToken" filled with "test token 02"