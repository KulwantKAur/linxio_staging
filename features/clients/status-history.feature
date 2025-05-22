Feature: Clients status history
  Scenario: I want to register client and update status for history
    When I signed in
    And I want fill "name" field with "client name"
    And I want fill "legalName" field with "client legal name"
    And I want fill "taxNr" field with "12345678910"
    And I want fill "legalAddress" field with "client legal address"
    And I want fill "billingAddress" field with "client billing address"
    And I want fill "taxNr" field with "12345678910"
    And I want fill "status" field with "client"
    And I want fill "planId" field with 1
    And I want fill "timezone" field with 14
    And I want fill "clientNote" field with "client note"
    And I want fill "adminNote" field with "admin note"
    Then I want register client
    And I see field "name" filled with "client name"
    And I see field "legalName" filled with "client legal name"
    And I see field "taxNr" filled with "string(12345678910)"
    And I see field "legalAddress" filled with "client legal address"
    And I see field "billingAddress" filled with "client billing address"
    And I see field "status" filled with "client"
    And I see field "timezone" filled with 14
    When I want fill "status" field with "blocked"
    Then I want update client "client name"
    And I see field "status" filled with "blocked"
    And response code is 200
    Then I want get client status history "client name"
    And I see field "0/payload" filled with "blocked"
    And I see field "0/type" filled with "client.status"
    And response code is 200
    When I want fill "status" field with "client"
    Then I want update client "client name"
    And I see field "status" filled with "client"
    And response code is 200
    Then I want get client status history "client name"
    And I see field "0/payload" filled with "client"
    And I see field "0/type" filled with "client.status"
    And I see field "1/payload" filled with "blocked"
    And I see field "1/type" filled with "client.status"
    And response code is 200
    #check permission
    When I want get client by name "client-name-1" and save id
    And I signed in as "admin" team "client" and client teamId
    Then I want get client status history "client-name-0"
    And I see field "errors/0/detail" filled with "Access Denied."