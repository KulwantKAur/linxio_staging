Feature: Clients
  Background: data exists
    And I signed in as "super_admin" team "admin"

  Scenario: I want to register client
    And I want fill "name" field with "client name"
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
    And I see field "name" filled with "client name"
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
    Then I want register user for current client
    And I see field "name" filled with "client user name"
    And I see field "surname" filled with "client user surname"
    And I see field "position" filled with "pos"
    And I see field "phone" filled with "+46 (87)464675"
    And I see field "email" filled with "dawdawd1ada@gmail.com"
    And I see field "status" filled with "new"

  Scenario: I want to filter client list
    Given Elastica populate
    And I want fill "name" field with "client name"
    And I want fill "legalName" field with "client legal name"
    And I want fill "taxNr" field with "12345678910"
    And I want fill "legalAddress" field with "client legal address"
    And I want fill "billingAddress" field with "client billing address"
    And I want fill "status" field with "client"
    And I want fill "planId" field with 1
    And I want fill "timezone" field with 63
    And I want fill "clientNote" field with "client note"
    And I want fill "adminNote" field with "admin note"
    And I want register client and remember
    And I see field "planId" filled with 1
    Then I want clean filled data
    And I want fill "name" field with "client user name"
    And I want fill "surname" field with "client user surname"
    And I want fill "position" field with "pos"
    And I want fill "phone" field with "+46 (87)464675"
    And I want fill "email" field with "dawdawd1ada@gmail.com"
    Then I want register user for current client
    Then I want fill "name" field with "unique"
    Then I want fill "planId" field with 4
    Then I want fill "status" field with "client"
    And I want register client
    Given Elastica populate
    Then I want clean filled data
    And I want fill "keyContactName" field with "client user name"
    And I want get clients list
    And I see field "data/0/keyContact/name" filled with "client user name"
    And I see field "data/0/keyContact/surname" filled with "client user surname"
    And I see field "data/0/name" filled with "client name"
    And I see field "data/0/legalName" filled with "client legal name"
    And I see field "data/0/taxNr" filled with "string(12345678910)"
    And I see field "data/0/legalAddress" filled with "client legal address"
    And I see field "data/0/status" filled with "client"
    And I see field "data/0/keyContact/email" filled with "dawdawd1ada@gmail.com"
    And I see field "data/0/usersCount" filled with "1"
    And I see field "data/0/activeUsersCount" filled with 0
    #Given Elastica populate
    Then I want clean filled data
    And I want fill "name" field with "unique"
    And I want get clients list
    And I see field "data/0/name" filled with "unique"
    And I see field "data/0/devicesCount" filled with 0
    And I see field "data/0/activeDevicesCount" filled with 0
    And I see field "data/0/vehiclesCount" filled with 0
    And I see field "data/0/activeVehiclesCount" filled with 0
    Then I want clean filled data
    And I want fill "status" field with "client"
    And I want get clients list
    And I see field "data/0/status" filled with "client"
    Then I want clean filled data
    And I want fill array key usersCount with gte field with 2
    And I want get clients list
    And I see field "data/0/usersCount" filled with 2
    Then I want clean filled data
    And I want fill "activeUsersCount" field with 1
    And I want get clients list
    Then I want clean filled data
    And I want fill array key activeUsersCount with gte field with 1
    And I want fill array key devicesCount with gte field with 0
    And I want fill array key activeDevicesCount with gte field with 0
    Then I want clean filled data
    And I want fill array key createdAt with lte field with "date::now"
    And I want get clients list
    And I see field "total" filled with 12
    Then I want clean filled data
    And I want fill array key createdAt with gte field with "date::now"
    And I want get clients list
    And I see field "total" filled with 0

  Scenario: I want get client plans
    And I want get client plans
    Then I see field "0/name" filled with "starter"
    Then I see field "1/name" filled with "fleet_essentials"
    Then I see field "2/name" filled with "fleet_plus"

  Scenario: I want get client by ID
    And I want fill "name" field with "client name"
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
    And I see field "name" filled with "client name"
    And I see field "legalName" filled with "client legal name"
    Then I want get remembered client
    And I see field "name" filled with "client name"
    And I see field "legalName" filled with "client legal name"
    Then I signed with email "client-user-0@ocsico.com"
    And I want get remembered client
    And I see field "errors/0/detail" filled with "Access Denied."

  Scenario: I want get client users
    Then I want fill "name" field with "client name"
    And I want fill "legalName" field with "client legal name"
    And I want fill "taxNr" field with "12345678910"
    And I want fill "legalAddress" field with "client legal address"
    And I want fill "billingAddress" field with "client billing address"
    And I want fill "status" field with "client"
    And I want fill "planId" field with 1
    And I want fill "timezone" field with 63
    And I want fill "clientNote" field with "client note"
    And I want fill "adminNote" field with "admin note"
    And I want register client and remember
    Then I want fill "name" field with "client user name"
    And I want fill "surname" field with "client user surname"
    And I want fill "position" field with "pos"
    And I want fill "phone" field with "+46 (87)464675"
    And I want fill "email" field with "client_user@gmail.com"
    Then I want register user for current client
    And I see field "name" filled with "client user name"
    And I see field "surname" filled with "client user surname"
    And I see field "position" filled with "pos"
    And I see field "phone" filled with "+46 (87)464675"
    And I see field "email" filled with "client_user@gmail.com"
    And I see field "status" filled with "new"
    Then I want clean filled data
    Then I want fill "name" field with "client user2"
    And I want fill "surname" field with "client user2 surname"
    And I want fill "position" field with "pos"
    And I want fill "phone" field with "+46 (87)464675"
    And I want fill "email" field with "client_user_2@gmail.com"
    Then I want register user for current client
    And I see field "email" filled with "client_user_2@gmail.com"
    Then I want clean filled data
    Given Elastica populate
    And I want fill "fullName" field with "client user2 client user2 surname"
    And I want get current client users
    And I see field "data/0/email" filled with "client_user_2@gmail.com"
    And I see field "total" filled with 1
    Then I want clean filled data
    And I want fill "email" field with "client_user@gmail.com"
    And I want get current client users
    And I see field "data/0/email" filled with "client_user@gmail.com"
    And I see field "total" filled with 1

  Scenario: I want get client user by id
    Then I want fill "name" field with "client name"
    And I want fill "legalName" field with "client legal name"
    And I want fill "taxNr" field with "12345678910"
    And I want fill "legalAddress" field with "client legal address"
    And I want fill "billingAddress" field with "client billing address"
    And I want fill "status" field with "client"
    And I want fill "planId" field with 1
    And I want fill "timezone" field with "3"
    And I want fill "clientNote" field with "client note"
    And I want fill "adminNote" field with "admin note"
    And I want register client and remember
    And I see field "name" filled with "client name"
    Then I want clean filled data
    Then I want fill "name" field with "client user2"
    And I want fill "surname" field with "client user2 surname"
    And I want fill "position" field with "pos"
    And I want fill "phone" field with "+46 (87)464675"
    And I want fill "email" field with "client_user_2@gmail.com"
    Then I want register user for current client
    And I see field "email" filled with "client_user_2@gmail.com"

  Scenario: I want to register client and update client data
    And I want fill "name" field with "client name"
    And I want fill "legalName" field with "client legal name"
    And I want fill "taxNr" field with "12345678910"
    And I want fill "legalAddress" field with "client legal address"
    And I want fill "billingAddress" field with "client billing address"
    And I want fill "taxNr" field with "12345678910"
    And I want fill "status" field with "client"
    And I want fill "planId" field with 1
    And I want fill "timezone" field with "3"
    And I want fill "clientNote" field with "client note"
    And I want fill "adminNote" field with "admin note"
    Then I want register client
    And I see field "name" filled with "client name"
    And I see field "legalName" filled with "client legal name"
    And I see field "taxNr" filled with "string(12345678910)"
    And I see field "legalAddress" filled with "client legal address"
    And I see field "billingAddress" filled with "client billing address"
    And I see field "status" filled with "client"
    And I see field "timezone" filled with "3"
    When I want fill "name" field with "client name new"
    And I want fill "legalName" field with "client legal name new"
    And I want fill "planId" field with 2
    Then I want update client "client name"
    And I see field "name" filled with "client name new"
    And I see field "legalName" filled with "client legal name new"
    And I see field "planId" filled with 2
    Then I want get remembered client
    And I see field "updatedBy/role/name" filled with "super_admin"
    And response code is 200
    And I signed in as "admin" team "client" and client teamId
    Then I want fill "name" field with "client name new1"
    And I want update client "client name new"
    And I see field "name" filled with "client name new1"
    And I signed in as "manager" team "client"
    Then I want fill "name" field with "client name new2"
    And I want update client "client name new1"
    Then I see field "errors/0/detail" filled with "Access Denied."

  Scenario: I want test login with ID
    And I want fill "driverId" field with "test driver id"
    When I want to update client user with client name "ACME1" and user email "nikki.burns@acme.local"
    And I see field "driverId" filled with "test driver id"
    And I want fill "deviceId" field with "test imei"
    Then I want login with driver id
    And I want get client by name "ACME1" and save id
    And I do not see field "token"
    And I see field "errors/0/error_code" filled with "Device is not found"
    And I want set remembered team settings "loginWithId" with value true
    Then I want login with driver id
    And I see field "errors/0/error_code" filled with "Device is not found"
    Then I signed with email "acme-admin@linxio.local"
    And I want fill "loginWithId" field with true
    Then I want set mobile device with id "test imei"
    And I see field "deviceId" filled with "test imei"
    And I see field "loginWithId" filled with true
    Then I want login with driver id
    And I see field "token"
    And I see field "loginWithId" filled with true
    And I want fill "driverId" field with "admin driver id"
    Then I want login with driver id
    And I do not see field "token"
    And I see field "errors/0/error_code" filled with "User is not found"
    Then I signed with email "acme-admin@linxio.local"
    And I want fill "loginWithId" field with false
    Then I want set mobile device with id "test imei"
    And response code is 200
    And I want fill "driverId" field with "test driver id"
    Then I want login with driver id
    And I see field "errors/0/error_code" filled with "Log in with this device is not allowed"
    Then I want get mobile device with id "test imei"
    And I see field "deviceId" filled with "test imei"
    And I see field "loginWithId" filled with false
    And I want fill "driverId" field with "admin driver id"
    And I want check client "ACME1" driver id
    And I see field "isUnique" filled with true
    And I want fill "driverId" field with "test driver id"
    And I want check client "ACME1" driver id
    And I see field "isUnique" filled with false

  Scenario: I want get client users emails
    Then I want fill "name" field with "client name"
    And I want fill "legalName" field with "client legal name"
    And I want fill "taxNr" field with "12345678910"
    And I want fill "legalAddress" field with "client legal address"
    And I want fill "billingAddress" field with "client billing address"
    And I want fill "status" field with "client"
    And I want fill "planId" field with 1
    And I want fill "timezone" field with 63
    And I want fill "clientNote" field with "client note"
    And I want fill "adminNote" field with "admin note"
    And I want register client and remember
    Then I want clean filled data
    Then I want fill "name" field with "client user name 1"
    And I want fill "surname" field with "client user surname 1"
    And I want fill "position" field with "pos"
    And I want fill "phone" field with "+46 (87)464675"
    And I want fill "email" field with "client_user_97@gmail.com"
    Then I want register user for current client
    Then I want clean filled data
    Then I want fill "name" field with "client user name 2"
    And I want fill "surname" field with "client user surname 2"
    And I want fill "position" field with "pos"
    And I want fill "phone" field with "+46 (87)464675"
    And I want fill "email" field with "client_user_98@gmail.com"
    Then I want register user for current client
    Then I want clean filled data
    Then I want fill "name" field with "client user name 3"
    And I want fill "surname" field with "client user surname 3"
    And I want fill "position" field with "pos"
    And I want fill "phone" field with "+46 (87)464675"
    And I want fill "email" field with "client_user_99@gmail.com"
    Then I want register user for current client
    Then I want clean filled data
    Given Elastica populate
    Then I want fill "fields" field with json: '["email"]'
    And I want get current client users
    And I see field "data/0/id"
    And I see field "data/0/email" filled with "client_user_97@gmail.com"
    And I see field "data/1/id"
    And I see field "data/1/email" filled with "client_user_98@gmail.com"
    And I see field "data/2/id"
    And I see field "data/2/email" filled with "client_user_99@gmail.com"

  Scenario: I want check access rule
    Then I signed in as "support" team "admin"
    And I want register client
    Then I see field "errors/0/detail" filled with "Access Denied."

  Scenario: I want get clients with filtered fields
    Given Elastica populate
    And I signed in
    And I want fill "fields" field with json: '["name", "status"]'
    And I want get clients list
    And I see field "data/0/id"
    And I see field "data/0/name"
    And I see field "data/0/status"
    And I do not see field "data/0/managerId"
    And I do not see field "data/0/keyContactId"
    And I do not see field "data/0/timezone"
    And I do not see field "data/0/accountingContact"
    And I do not see field "data/0/planId"
    And I do not see field "data/0/legalName"
    And I do not see field "data/0/legalAddress"
    And I do not see field "data/0/billingAddress"
    And I do not see field "data/0/taxNr"
    And I do not see field "data/0/createdAt"
    And I do not see field "data/0/createdById"
    And I do not see field "data/0/usersCount"
    And I do not see field "data/0/activeUsersCount"
    And I do not see field "data/0/devicesCount"
    And I do not see field "data/0/activeDevicesCount"
    And I do not see field "data/0/vehiclesCount"
    And I do not see field "data/0/activeVehiclesCount"
    And I do not see field "data/0/manager"
    And I do not see field "data/0/keyContact"
    And I do not see field "data/0/plan"
    Then I want clean filled data
    And I want fill "fields" field with json: '["manager", "keyContact", "plan"]'
    And I want get clients list
    And I do not see field "data/0/name"
    And I do not see field "data/0/status"
    And I do not see field "data/0/managerId"
    And I do not see field "data/0/keyContactId"
    And I do not see field "data/0/timezone"
    And I do not see field "data/0/accountingContact"
    And I do not see field "data/0/planId"
    And I do not see field "data/0/legalName"
    And I do not see field "data/0/legalAddress"
    And I do not see field "data/0/billingAddress"
    And I do not see field "data/0/taxNr"
    And I do not see field "data/0/createdAt"
    And I do not see field "data/0/createdById"
    And I do not see field "data/0/usersCount"
    And I do not see field "data/0/activeUsersCount"
    And I do not see field "data/0/devicesCount"
    And I do not see field "data/0/activeDevicesCount"
    And I do not see field "data/0/vehiclesCount"
    And I do not see field "data/0/activeVehiclesCount"
    And I see field "data/0/manager/id"
    And I see field "data/0/manager/id"
    And I see field "data/0/manager/email"
    And I see field "data/0/manager/name"
    And I see field "data/0/manager/surname"
    And I see field "data/0/manager/role"
    And I see field "data/0/manager/phone"
    And I see field "data/0/manager/status"
    And I see field "data/0/keyContact/id"
    And I see field "data/0/keyContact/email"
    And I see field "data/0/keyContact/name"
    And I see field "data/0/keyContact/surname"
    And I see field "data/0/keyContact/role"
    And I see field "data/0/keyContact/phone"
    And I see field "data/0/keyContact/status"
    And I see field "data/0/plan/id"
    And I see field "data/0/plan/name"
    And I see field "data/0/plan/displayName"

  Scenario: I want get admin team users
    And I want fill "email" field with "admin_team_user@gmail.test"
    And I want fill "name" field with "Alex"
    And I want fill "roleId" field with "2"
    And I want fill "phone" field with "+46 (87)464675"
    Then I want register admin team user
    Then I see field "email" filled with "admin_team_user@gmail.test"
    And I want fill "name" field with "client name"
    And I want fill "legalName" field with "client legal name"
    And I want fill "taxNr" field with "12345678910"
    And I want fill "legalAddress" field with "client legal address"
    And I want fill "billingAddress" field with "client billing address"
    And I want fill "status" field with "client"
    And I want fill "planId" field with 1
    And I want fill "timezone" field with 63
    And I want fill "clientNote" field with "client note"
    And I want fill "adminNote" field with "admin note"
    And I want register client and remember
    And I see field "name" filled with "client name"
    Then I want clean filled data
    And I want fill "name" field with "client user name"
    And I want fill "surname" field with "client user surname"
    And I want fill "position" field with "pos"
    And I want fill "phone" field with "+46 (87)464675"
    And I want fill "email" field with "dawdawd1ada@gmail.com"
    Then I want register user for current client
    Given Elastica populate
    And I see field "email" filled with "dawdawd1ada@gmail.com"
    And I want fill "email" field with "admin_team_user@gmail.test"
    Then I want get admin team users
    And I see field "total" filled with 1
    And I see field "data/0/email" filled with "admin_team_user@gmail.test"
    Then I want clean filled data
    And I want fill "email" field with "dawdawd1ada@gmail.com"
    Then I want get admin team users
    And I see field "total" filled with 0

  Scenario: I want update client user data
    And I signed in
    And I want fill "name" field with "changed - client-user-name"
    And I want fill "surname" field with "changed - client-user-name"
    And I want fill "phone" field with "test2"
    And I want fill "position" field with "test3"
    And I want fill "driverId" field with "test4"
    When I want to update client user with client name "client-name-0" and user email "client-user-0@ocsico.com"
    And I see field "name" filled with "changed - client-user-name"
    And I see field "surname" filled with "changed - client-user-name"
    And I see field "phone" filled with "test2"
    And I see field "position" filled with "test3"
    And I see field "driverId" filled with "test4"

  Scenario: I want update user data with not allowed fields
    And I signed in
    And I want fill "email" field with "changed - client-user@ocsico.com"
    When I want to update client user with client name "client-name-0" and user email "client-user-0@ocsico.com"
    Then I see field "email" filled with "client-user-0@ocsico.com"
    Then I want clean filled data
    And I want fill "name" field with "client-user-name-00"
    When I want to update client user with client name "client-name-0" and user email "client-user-0@ocsico.com"
    Then I see field "name" filled with "client-user-name-00"
    Then I want clean filled data
    And I signed with email "client-user-0@ocsico.com"
    And I want fill "name" field with "client-user-name-000"
    When I want to update client user with client name "client-name-0" and user email "client-user-0@ocsico.com"
    Then I see field "name" filled with "client-user-name-00"

  Scenario: I want block a user
    And I signed in
    And I want fill "isBlocked" field with true
    And I want fill "blockingMessage" field with "test"
    When I want to update client user with client name "client-name-0" and user email "client-user-0@ocsico.com"
    And I see field "status" filled with "blocked"
    And I see field "blockingMessage" filled with "test"
    Then I want clean filled data
    And I want fill "isBlocked" field with false
    When I want to update client user with client name "client-name-0" and user email "client-user-0@ocsico.com"
    And I see field "status" filled with "active"

  Scenario: I want delete client user
    And I signed in
    When I want delete client user with client name "client-name-0" and user email "client-user-0@ocsico.com"
    And response code is 204
    And I see in DB field "status" filled with "deleted" for user with "client-user-0@ocsico.com"

  Scenario: I want to delete a user who is a key contact
    And I signed in
    And I want set key contact "client-user-0@ocsico.com" for client "client-name-0"
    When I want delete client user with client name "client-name-0" and user email "client-user-0@ocsico.com"
    And response code is 400
    And I see in DB field "status" filled with "active" for user with "client-user-0@ocsico.com"

  Scenario: I want set invalid client settings
    And I want fill "name" field with "client name"
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
    And I want register client and remember
    And I want set remembered team settings otp for role "admin" "admin" with value 1
    And response code is 400
    And I see field "errors/0/detail/0.roleId/wrong_value" filled with "Wrong value"

  Scenario: I want set and check client settings
    And I want fill "name" field with "client name"
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
    And I want register client and remember
    And I want set remembered team settings "otp" for role "admin" "client" with value "1"
    And I see field "6/team/type" filled with "client"
    And I see field "6/value" filled with "string(1)"
    And I see field "6/name" filled with "otp"
    Then I want clean filled data
    And I want fill "name" field with "client user name"
    And I want fill "surname" field with "client user surname"
    And I want fill "position" field with "pos"
    And I want fill "phone" field with "+46 (87)464675"
    And I want fill "email" field with "dawdawd1ada@gmail.com"
    And I want fill "roleId" field with 6
    Then I want register user for current client
    And I see field "email" filled with "dawdawd1ada@gmail.com"
    Given Elastica populate
    Then I want get remembered team settings
    And I see field "6/team/type" filled with "client"
    And I see field "6/value" filled with "string(1)"
    And I see field "6/role/name" filled with 'admin'
    Then I want clean filled data
    And I want fill "email" field with "dawdawd1ada@gmail.com"
    Then I want get current client users
    And I see field "data/0/email" filled with "dawdawd1ada@gmail.com"
    And I see field "data/0/role/name" filled with 'admin'
    Then I want get remembered team settings
    And I see field "6/team/type" filled with "client"
    And I see field "6/value" filled with "string(1)"
    Then I want clean filled data
    And I want set remembered team settings "custom" for role "admin" "client" with raw value
    """
      {"value":1}
    """
    And I see field "0/team/type" filled with "client"
    And I see field "0/value/value" filled with 1
    And I see field "0/name" filled with "custom"

  Scenario: I want check client default settings
    And I want fill "name" field with "client name"
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
    And I want register client and remember
    Then I want get remembered team settings
    And I see field "1/role" filled with null
    And I see field "1/value" filled with 1
    And I see field "1/name" filled with "email"
    And I see field "3/role" filled with null
    And I see field "3/value" filled with 1
    And I see field "3/name" filled with "inApp"
    And I see field "4/role" filled with null
    And I see field "4/value" filled with 1
    And I see field "4/name" filled with "language"
    And I see field "5/role" filled with null
    And I see field "5/value" filled with "0"
    And I see field "5/name" filled with "loginWithId"
    And I see field "6/role/name" filled with admin
    And I see field "6/value" filled with 0
    And I see field "6/name" filled with "otp"
    And I see field "7/role/name" filled with manager
    And I see field "7/value" filled with 0
    And I see field "7/name" filled with "otp"
    And I see field "8/role/name" filled with driver
    And I see field "8/value" filled with 0
    And I see field "8/name" filled with "otp"
    And I see field "9/role" filled with null
    And I see field "9/value" filled with 3
    And I see field "9/name" filled with "provider"
    And I see field "10/role" filled with null
    And I see field "10/value" filled with 1
    And I see field "10/name" filled with "sms"
    And I see field "11/role" filled with null
    And I see field "11/value/name" filled with "Light Theme"
    And I see field "11/name" filled with "theme"
    And I see field "12/role" filled with null
    And I see field "12/value" filled with 63
    And I see field "12/name" filled with "timezone"

  Scenario: I want set and check client settings
    And I want fill "name" field with "client name"
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
    And I want register client and remember
    And I want set remembered team settings theme with value 'dark_theme'
    And I see field "11/team/type" filled with "client"
    And I see field "11/value/name" filled with "Dark Theme"
    And I see field "11/name" filled with "theme"
    Then I want clean filled data
    And I want fill "name" field with "client user name"
    And I want fill "surname" field with "client user surname"
    And I want fill "position" field with "pos"
    And I want fill "phone" field with "+46 (87)464675"
    And I want fill "email" field with "dawdawd1ada@gmail.com"
    And I want fill "roleId" field with 6
    Then I want register user for current client
    And I see field "email" filled with "dawdawd1ada@gmail.com"
    Given Elastica populate
    Then I want get remembered team settings
    And I see field "11/team/type" filled with "client"
    And I see field "11/value/name" filled with "Dark Theme"
    And I see field "11/role" filled with null
    Then I want clean filled data
    Then I signed with email "dawdawd1ada@gmail.com"
    Then I want get my theme
    And I see field "name" filled with "Dark Theme"

  Scenario: I want export client list
    And I signed in
    Given Elastica populate
    Then I want add value to array key "fields" with "name"
    Then I want add value to array key "fields" with "status"
    Then I want add value to array key "fields" with "plan"
    Then I want add value to array key "fields" with "keyContact"
    Then I want add value to array key "fields" with "manager"
    Then I want add value to array key "fields" with "usersCount"
    Then I want add value to array key "fields" with "activeUsersCount"
    Then I want add value to array key "fields" with "devicesCount"
    Then I want add value to array key "fields" with "activeDevicesCount"
    Then I want add value to array key "fields" with "vehiclesCount"
    Then I want add value to array key "fields" with "activeVehiclesCount"
    Then I want export clients list
    And I see csv item number 9 field "Company Name" filled with "ACME1"
    And I see csv item number 9 field "Status" filled with "Client"
    And I see csv item number 9 field "Plan" filled with "Fleet Plus"
    And I see csv item number 9 field "Key contact" filled with "Acme Admin"
    And I see csv item number 9 field "Manager" filled with "Admin Client Manager #1"
    And I see csv item number 9 field "Users total" filled with "18"
    And I see csv item number 9 field "Users active" filled with "18"
    And I see csv item number 9 field "Devices total" filled with "18"
    And I see csv item number 9 field "Devices active" filled with "18"
    And I see csv item number 9 field "Vehicles total" filled with "46"
    And I see csv item number 9 field "Vehicles active" filled with "46"
    And response code is 200

  Scenario: I want check manager permission for creation client user
    And I want fill "name" field with "client name"
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
    And I see field "name" filled with "client name"
    And I see field "legalName" filled with "client legal name"
    And I see field "taxNr" filled with "string(12345678910)"
    And I see field "legalAddress" filled with "client legal address"
    And I see field "billingAddress" filled with "client billing address"
    And I see field "status" filled with "client"
    And I see field "timezone" filled with 63
    Then I want clean filled data
    And I want fill "name" field with "client user manager"
    And I want fill "surname" field with "client user surname"
    And I want fill "position" field with "pos"
    And I want fill "phone" field with "+46 (87)464675"
    And I want fill "email" field with "dawdawd1ada@gmail.com"
    And I want fill "roleId" field with 7
    Then I want register user for current client
    And I see field "name" filled with "client user manager"
    And I see field "surname" filled with "client user surname"
    And I see field "position" filled with "pos"
    And I see field "phone" filled with "+46 (87)464675"
    And I see field "email" filled with "dawdawd1ada@gmail.com"
    And I see field "status" filled with "new"
    Then I signed with email "dawdawd1ada@gmail.com"
    Then I want clean filled data
    And I want fill "name" field with "client user manager"
    And I want fill "surname" field with "client user surname"
    And I want fill "position" field with "pos"
    And I want fill "phone" field with "+46 (87)464675"
    And I want fill "email" field with "not_driver@gmail.com"
    And I want fill "roleId" field with 6
    Then I want register user for current client
    And response code is 400
