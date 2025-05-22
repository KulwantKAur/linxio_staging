Feature: Client status changing
  Scenario: I want to register client and update status to allowed value
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

  Scenario: I want to register client with status demo without field expirationDate
    When I signed in
    And I want fill "name" field with "client name"
    And I want fill "legalName" field with "client legal name"
    And I want fill "taxNr" field with "12345678910"
    And I want fill "legalAddress" field with "client legal address"
    And I want fill "billingAddress" field with "client billing address"
    And I want fill "taxNr" field with "12345678910"
    And I want fill "status" field with "demo"
    And I want fill "planId" field with 1
    And I want fill "timezone" field with 14
    And I want fill "clientNote" field with "client note"
    And I want fill "adminNote" field with "admin note"
    Then I want register client
    And response code is 400
    And I see field "errors/0/detail/expirationDate/required"

  Scenario: I want to register client with status demo without invalid expirationDate
    When I signed in
    And I want fill "name" field with "client name"
    And I want fill "legalName" field with "client legal name"
    And I want fill "taxNr" field with "12345678910"
    And I want fill "legalAddress" field with "client legal address"
    And I want fill "billingAddress" field with "client billing address"
    And I want fill "taxNr" field with "12345678910"
    And I want fill "status" field with "demo"
    And I want fill "planId" field with 1
    And I want fill "timezone" field with 14
    And I want fill "clientNote" field with "client note"
    And I want fill "adminNote" field with "admin note"
    And I want fill "expirationDate" field with "2018-01-01 00:00:00"
    Then I want register client
    And response code is 400
    And I see field "errors/0/detail/expirationDate/wrong_value"

  Scenario: I want to register client and update status to not allowed value
    When I signed in
    And I want fill "name" field with "client name"
    And I want fill "legalName" field with "client legal name"
    And I want fill "taxNr" field with "12345678910"
    And I want fill "legalAddress" field with "client legal address"
    And I want fill "billingAddress" field with "client billing address"
    And I want fill "taxNr" field with "12345678910"
    And I want fill "status" field with "demo"
    And I want fill "planId" field with 1
    And I want fill "timezone" field with 14
    And I want fill "clientNote" field with "client note"
    And I want fill "adminNote" field with "admin note"
    And I want fill "expirationDate" field with "2022-08-13T11:09:12+00:00"
    Then I want register client
    And I see field "name" filled with "client name"
    And I see field "legalName" filled with "client legal name"
    And I see field "taxNr" filled with "string(12345678910)"
    And I see field "legalAddress" filled with "client legal address"
    And I see field "billingAddress" filled with "client billing address"
    And I see field "status" filled with "demo"
    And I see field "timezone" filled with 14
    When I want fill "status" field with "test"
    Then I want update client "client name"
    And response code is 400
    And I see field "errors/0/detail/status/wrong_value"