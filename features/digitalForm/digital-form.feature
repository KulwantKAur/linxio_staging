Feature: DigitalForm

  Scenario: I want fill digital form
    Given I signed in as "admin" team "client" and teamId 11
    And I want get digital all forms
    Then response code is 200
    And I want view first form from list
    Then response code is 200
    And I want fill "inspectionPeriod" field with "inspectionFormPeriodShowAlways"
    And I want fill digital form step fields: key "0", "order" field with "1"
    And I want fill digital form step fields: key "0", "title" field with "Form title step 1"
    And I want fill digital form step fields: key "0", "description" field with "Form description step 1"
    And I want fill digital form step fields: key "0", "options" field with '{"type":"file"}'
    And I want fill digital form step fields: key "1", "order" field with "2"
    And I want fill digital form step fields: key "1", "title" field with "Form title step 2"
    And I want fill digital form step fields: key "1", "description" field with "Form description step 2"
    And I want fill digital form step fields: key "1", "options" field with '{"type":"datetime"}'
    And I want fill digital form step fields: key "1", "condition" field with '{"questionId":1,"operator":">","value":2}'
    And I want fill digital form step fields: key "2", "order" field with "3"
    And I want fill digital form step fields: key "2", "title" field with "Form title step 3"
    And I want fill digital form step fields: key "2", "description" field with "Form description step 3"
    And I want fill digital form step fields: key "2", "options" field with '{"type":"text.multi","min":10,"max":30000,"default":"Default text for multi line text"}'
    And I want fill digital form step fields: key "3", "order" field with "4"
    And I want fill digital form step fields: key "3", "title" field with "Form title step 4"
    And I want fill digital form step fields: key "3", "description" field with "Form description step 4"
    And I want fill digital form step fields: key "3", "options" field with '{"type":"text.multi","min":10,"max":30000,"default":"Default text for multi line text"}'
    And I want fill digital form step fields: key "3", "condition" field with '{"questionId":3,"operator":">","value":5}'
    And I want fill digital form schedule fields: scopeKey "0" type "depot" and value "1"
    And I want fill digital form schedule fields: scopeKey "1" type "group" and value "2"
    And I want fill digital form schedule fields: scopeKey "2" type "vehicle" and value "3"
    And I want create digital form
    Then response code is 200
    And I want clear fill step data
    And I want fill "inspectionPeriod" field with "inspectionFormPeriodShowAlways"
    And I want fill digital form step fields: key "0", "order" field with "1"
    And I want fill digital form step fields: key "0", "title" field with "Form title step 1"
    And I want fill digital form step fields: key "0", "description" field with "Form description step 1"
    And I want fill digital form step fields: key "0", "options" field with '{"type":"file"}'
    And I want edit digital form
    And I want fill digital form answer fields: stepId "1" value "1" and duration "4"
    And I want fill digital form answer fields: stepId "2" value "2" and duration "4"
    And I want fill digital form answer fields: stepId "3" value "2" and duration "4"
    And I want fill digital form answer fields: stepId "4" value "1" and duration "4"
    And I want fill digital form answer fields: stepId "26" value "130000" and duration "4"
    And I want fill digital form answer fields: stepId "27" value "Lorem Ipsum is simply dummy" and duration "4"
    And I want to create answer to first digital form
    And I want get single digital form answer
    Then response code is 200
    And I want get all digital form schedulers
    Then response code is 200
    And I want get first digital form scheduler from list
    Then response code is 200
