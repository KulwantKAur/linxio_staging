Feature: Passwords

  Scenario: I want to reset password
    Given I signed in
    When I want to request new password "super_admin@user.com"
    Then response code is 200
    And I see field "mail_sent" filled with true
    When I want to reset password "super_admin@user.com" "123456"
    Then response code is 200
    And I see field "email" filled with "super_admin@user.com"
    When I want login "super_admin@user.com" "123456"
    Then response code is 200
    And I see field "otp_required" filled with false

  Scenario: I want to reset password with non-existing email
    Given I signed in
    When I want to request new password with non-existing email "non-existing@example.com"
    Then I see field "errors"
    And I see field "errors/0/error_code" filled with "User is not found"
    And response code is 404

  Scenario: I want to reset password with already requested token
    Given I signed in
    When I want to request new password "super_admin@user.com"
    Then response code is 200
    And I see field "mail_sent" filled with true
    When I want to reset password "super_admin@user.com" "123456"
    Then response code is 200
    And I see field "email" filled with "super_admin@user.com"
    When I want to reset password "super_admin@user.com" "123456"
    Then I see field "errors"
    And I see field "errors/0/detail" filled with "Request for password for this token is not found"
    And response code is 400

  Scenario: I want to reset password with expired token
    Given I signed in
    When I want to request new password "super_admin@user.com"
    Then response code is 200
    And I see field "mail_sent" filled with true
    Then I want to reset password with token "super_admin@user.com"
    When I want to reset password "super_admin@user.com" "123456"
    Then I see field "errors"
    And I see field "errors/0/detail/token" filled with "Temporary link for reset password has been expired. Please, try to request new one"
    And response code is 400

  Scenario: I want to check expired token
    Given I signed in
    When I want to request new password "super_admin@user.com"
    Then response code is 200
    And I see field "mail_sent" filled with true
    Then I want to reset password with token "super_admin@user.com"
    When I want check reset token "super_admin@user.com"
    Then I see field "tokenValid" filled with false
    And response code is 200

  Scenario: I want to check not found token
    Given I signed in
    When I want to request new password "super_admin@user.com"
    Then response code is 200
    And I see field "mail_sent" filled with true
    When I want to reset password "super_admin@user.com" "123456"
    Then response code is 200
    And I see field "email" filled with "super_admin@user.com"
    When I want check reset token "super_admin@user.com"
    Then I see field "tokenValid" filled with false
    And response code is 200

  Scenario: I want to check token
    Given I signed in
    When I want to request new password "super_admin@user.com"
    Then response code is 200
    And I see field "mail_sent" filled with true
    When I want check reset token "super_admin@user.com"
    Then I see field "tokenValid" filled with true
    And response code is 200


