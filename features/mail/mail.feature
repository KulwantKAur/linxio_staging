Feature: Mail

  Scenario: I want test support email request
    When I want create sendEmail mock
    Then I signed in as "super_admin" team "admin"
    And I want upload file to field files
    Then I want send support message with body "test support request"
    And I want check email data
    And I see field "emailData/to" filled with "support@linxio.com"
    And I see field "emailData/subject" filled with "Support request"
    And I see field "emailData/body" filled with "test support request"
    And I want check email file with index "0"