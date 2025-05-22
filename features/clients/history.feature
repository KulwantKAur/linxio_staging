Feature: Clients history
  Background:
    Given I signed in as "super_admin" team "admin"

  Scenario: I want to register client and check history of client creation
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
    Then I want register client
    And I see field "name" filled with "client name"
    And I see field "legalName" filled with "client legal name"
    And I see field "taxNr" filled with "string(12345678910)"
    And I see field "legalAddress" filled with "client legal address"
    And I see field "billingAddress" filled with "client billing address"
    And I see field "status" filled with "client"
    And I see field "timezone" filled with 63
    Then I want to know client "client name" exists in entity history

  Scenario: I want to register client and check history of client updates
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
    Then I want register client
    And I see field "name" filled with "client name"
    And I see field "legalName" filled with "client legal name"
    And I see field "taxNr" filled with "string(12345678910)"
    And I see field "legalAddress" filled with "client legal address"
    And I see field "billingAddress" filled with "client billing address"
    And I see field "status" filled with "client"
    And I see field "timezone" filled with 63
    When I want fill "name" field with "client name new"
    Then I want update client "client name"
    And I see field "name" filled with "client name new"
    And response code is 200
    When I want get client update history "client name new"
    And I see field "0/entity" filled with "App\Entity\Client"
    And I see field "0/type" filled with "client.updated"
    Then I want sleep on 1 seconds
    When I want fill "name" field with "client name new 2"
    Then I want update client "client name new"
    And I see field "name" filled with "client name new 2"
    And response code is 200
    When I want get client update history "client name new 2"
    And I see field "0/entity" filled with "App\Entity\Client"
    And I see field "0/type" filled with "client.updated"
    And I see field "1/entity" filled with "App\Entity\Client"
    And I see field "1/type" filled with "client.updated"