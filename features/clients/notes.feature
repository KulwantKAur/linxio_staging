Feature: Client notes list
  Scenario: I want get clint notes
    When I signed in
    And I want fill "note" field with "first_admin_note"
    And I want fill "noteType" field with "admin"
    When I want add client note to DB for client "client-name-1" created by "linxio-dev@ocsico.com"
    And I want fill "note" field with "last_admin_note"
    And I want fill "noteType" field with "admin"
    When I want add client note to DB for client "client-name-1" created by "linxio-dev@ocsico.com"
    And I want fill "note" field with "first_client_note"
    And I want fill "noteType" field with "client"
    When I want add client note to DB for client "client-name-1" created by "linxio-dev@ocsico.com"
    And I want fill "note" field with "last_client_note"
    And I want fill "noteType" field with "client"
    When I want add client note to DB for client "client-name-1" created by "linxio-dev@ocsico.com"
    Then I want get client notes for client "client-name-1" by type "client"
    And I see field "0/note" filled with "last_client_note"
    And I see field "0/noteType" filled with "client"
    And I see field "0/createdBy/email" filled with "linxio-dev@ocsico.com"
    And I see field "1/note" filled with "first_client_note"
    And I see field "1/createdBy/email" filled with "linxio-dev@ocsico.com"
    Then I want get client notes for client "client-name-1" by type "admin"
    And I see field "0/note" filled with "last_admin_note"
    And I see field "0/createdBy/email" filled with "linxio-dev@ocsico.com"
    And I see field "0/noteType" filled with "admin"
    And I see field "1/note" filled with "first_admin_note"
    And I see field "1/noteType" filled with "admin"
    And I see field "0/createdBy/email" filled with "linxio-dev@ocsico.com"
    #check permission for getting notes
    Then I want get client notes for client "client-name-1" by type "client"
    And I see field "0/note" filled with "last_client_note"
    And I see field "0/noteType" filled with "client"
    And I see field "1/note" filled with "first_client_note"
    And I see field "1/noteType" filled with "client"
    Then I want get client notes for client "client-name-1" by type "admin"
    And I see field "0/note" filled with "last_admin_note"
    And I see field "0/noteType" filled with "admin"
    And I see field "1/note" filled with "first_admin_note"
    And I see field "1/noteType" filled with "admin"
    When I want get client by name "client-name-1" and save id
    And I signed in as "admin" team "client" and client teamId
    Then I want get client notes for client "client-name-1" by type "client"
    And I see field "0/note" filled with "last_client_note"
    And I see field "0/noteType" filled with "client"
    And I see field "1/note" filled with "first_client_note"
    And I see field "1/noteType" filled with "client"
    Then I want get client notes for client "client-name-1" by type "admin"
    And I see field "errors/0/detail" filled with "Access Denied"


