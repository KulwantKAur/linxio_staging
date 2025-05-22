Feature: Placeholders
  Scenario: I want check method getUserFrontendLink
    Then I want fill "admin" user mock with id 1
    And Method EntityPlaceholderService->getUserFrontendLink "test_domain" must return "test_domain/admin/team/user/1"
    Then I want clean filled data
    Then I want fill "client" user mock with id 3
    And Method EntityPlaceholderService->getUserFrontendLink "test_domain" must return "test_domain/admin/clients/2/users/3"

  Scenario: I want check EntityPlaceholderService
    Then I want fill "admin" user mock with id 1
    And I want fill event mock "USER_BLOCKED" "user"
    And I want check EntityPlaceholderService
    And I see in saved value field "user_email" filled with "test@ocsico.com"
    And I see in saved value field "user_name" filled with "test user"
    And I see in saved value field "data_message"
    And I see in saved value field "data_url" filled with "/admin/team/user/1"
    And I want fill event mock "Invalid" "user"
    And I want check EntityPlaceholderService
    And I see in saved value field "user_email" filled with "user_email"
    And I see in saved value field "user_name" filled with "user_name"
    And I see in saved value field "data_message" filled with "data_message"
    And I see in saved value field "data_url" filled with "data_url"
