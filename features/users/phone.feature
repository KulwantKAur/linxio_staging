Feature: Users verification

  Scenario: I want verify phone and password
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
    Then I want fill "phone" field with "+6(4)654654"
    And I want send verification code to user with email "admin_team_user@gmail.test"
    And I see field "email" filled with "admin_team_user@gmail.test"
    Then I want verify phone "+6(4)654654" with code "1234"
    And I see field "email" filled with "admin_team_user@gmail.test"
    And I see field "isPhoneVerified" filled with true
    Then I want clean filled data
    And I want get phone user with email "admin_team_user@gmail.test" by token
    And I see field "phone" filled with "+6(4)654654"
    Then I want clean filled data
    Then I want set user with email "admin_team_user@gmail.test" password "1aAw!awaw" by token
    And I see field "email" filled with "admin_team_user@gmail.test"
    When I want login "admin_team_user@gmail.test" "1aAw!awaw"
    Then I see field "otp_required" filled with true

  Scenario: I want check verify code for admin team user
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
    Then I want fill "phone" field with "+6(4)654654"
    And I want send verification code to user with email "admin_team_user@gmail.test"
    And I see field "email" filled with "admin_team_user@gmail.test"
    Then I want check verify token "invalid_token"
    And response code is 200
    And I see field "tokenValid" filled with false
    Then I want check verify token for user "admin_team_user@gmail.test"
    And response code is 200
    And I see field "tokenValid" filled with true
    Then I want change createAt for user "admin_team_user@gmail.test" "-15 days"
    Then I want check verify token for user "admin_team_user@gmail.test"
    And response code is 200
    And I see field "tokenValid" filled with false

  Scenario: I want check verify code for client team user
    And I signed in as "super_admin" team "admin"
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
    And I want send verification code to user with email "dawdawd1ada@gmail.com"
    And I see field "email" filled with "dawdawd1ada@gmail.com"
    Then I want check verify token 'invalid_token'
    And response code is 200
    And I see field "tokenValid" filled with false
    Then I want check verify token for user "dawdawd1ada@gmail.com"
    And response code is 200
    And I see field "tokenValid" filled with true
    Then I want change createAt for user "dawdawd1ada@gmail.com" "-15 days"
    Then I want check verify token for user "dawdawd1ada@gmail.com"
    And response code is 200
    And I see field "tokenValid" filled with false
    Then I want change createAt for user "dawdawd1ada@gmail.com" "+15 days"
    Then I want check verify token for user "dawdawd1ada@gmail.com"
    And response code is 200
    And I see field "tokenValid" filled with true
    Then I want set user password "dawdawd1ada@gmail.com" "1aAw!awaw" in DB
    Then I want check verify token for user "dawdawd1ada@gmail.com"
    And response code is 200
    And I see field "tokenValid" filled with false