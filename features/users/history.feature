Feature: Users history
  Background:
    Given I signed in as "super_admin" team "admin"

  Scenario: I want to register user and check history of user creation
    When I want fill "email" field with "alextest@gmail.test"
    And I want fill "name" field with "Alex"
    And I want fill "surname" field with "AL"
    And I want fill "teamType" field with "admin"
    And I want fill "roleId" field with 1
    And I want fill "position" field with "pos"
    And I want fill "phone" field with "+11(23) 123-123-21"
    When I want register
    Then I see field "email" filled with "alextest@gmail.test"
    And I see field "name" filled with "Alex"
    And I see field "surname" filled with "AL"
    And I see field "teamType" filled with "admin"
    And I see field "position" filled with "pos"
    And I see field "phone" filled with "+11(23) 123-123-21"
    And I see field "status" filled with "new"
    And response code is 200
    Then I want to know user "alextest@gmail.test" exists in entity history

  Scenario: I want to register user and check history of user updates
    And I want fill "name" field with "Bob"
    When I want to update client user with client name "client-name-0" and user email "client-user-0@ocsico.com"
    And I see field "name" filled with "Bob"
    When I want get user update history "client-user-0@ocsico.com"
    And I see field "0/entity" filled with "App\Entity\User"
    And I see field "0/type" filled with "user.updated"
    Then I want sleep on 1 seconds
    When I want fill "name" field with "Jimmy"
    When I want to update client user with client name "client-name-0" and user email "client-user-0@ocsico.com"
    And I see field "name" filled with "Jimmy"
    And response code is 200
    When I want get user update history "client-user-0@ocsico.com"
    And I see field "0/entity" filled with "App\Entity\User"
    And I see field "0/type" filled with "user.updated"
    And I see field "1/entity" filled with "App\Entity\User"
    And I see field "1/type" filled with "user.updated"

  Scenario: I want to register user, login and check history of user last login
    When I want fill "email" field with "alextest@gmail.test"
    And I want fill "name" field with "Alex"
    And I want fill "surname" field with "AL"
    And I want fill "teamType" field with "admin"
    And I want fill "roleId" field with 1
    And I want fill "position" field with "pos"
    And I want fill "phone" field with "+11(23) 123-123-21"
    And I want fill "status" field with "active"
    When I want register
    Then I want set user password "alextest@gmail.test" "1aAw!awaw" in DB
    And I want verify user phone by email "alextest@gmail.test"
    When I want login "alextest@gmail.test" "1aAw!awaw"
    When I want verify otp "1234"
    When I want get user last login history "alextest@gmail.test"
    And I see field "0/entity" filled with "App\Entity\User"
    And I see field "0/type" filled with "user.last_login"
    And response code is 200