Feature: Sms

  Scenario: I want to receive callback with sms status
    When I want fill "From" field with "+375291111111"
    And I want fill "To" field with "+375292222222"
    And I want fill "MessageUUID" field with "ad92a9de-5079-11e9-88b9-0242ac110005"
    When I want to generate sms
    Then I see field "phoneFrom"
    Then I see field "phoneTo"
    Then I see field "status"
    Then I see field "createdAt"
    And I want fill "Status" field with "delivered"
    When I want to receive callback with sms status
    Then I see field "status" filled with "delivered"

  # set `env: prod` in file `behat.yml` and `SMS_ENABLED=true` in file `.env`
  Scenario: I want to check sms sending
    Given I signed in as "super_admin" team "admin"
    When I want fill "email" field with "alextest@gmail.test"
    And I want fill "name" field with "Alex"
    And I want fill "surname" field with "AL"
    And I want fill "teamType" field with "admin"
    And I want fill "roleId" field with "2"
    And I want fill "position" field with "pos"
    And I want fill "phone" field with "+11(23) 123-123-21"
    When I want register
    Then I want set user password "alextest@gmail.test" "passw" in DB
    Then I see field "email" filled with "alextest@gmail.test"
    And I see field "name" filled with "Alex"
    And I see field "surname" filled with "AL"
    And I see field "teamType" filled with "admin"
    And I see field "position" filled with "pos"
    And I see field "phone" filled with "+11(23) 123-123-21"
    And I see field "status" filled with "new"
    And response code is 200
    And I want verify user phone by email "alextest@gmail.test"
    Then Sms service is ready to send sms
    When I want login "alextest@gmail.test" "passw"
    Then I see field "otp_required" filled with true
    And response code is 200
    When I want to find otp by email "alextest@gmail.test"
    And response code is 200
