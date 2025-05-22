Feature: InspectionForm

  Scenario: I want fill inspection form
    Given I signed in as "admin" team "client" and teamId 2
    And I want to fill teamId by team of current user
    And I want fill "model" field with "Mercedes"
    And I want fill "regNo" field with "B1234567890"
    And I want fill "type" field with "Car"
    And I want fill "vin" field with "1G6AR5S38E0134828"
    And I want fill "defaultLabel" field with "Jenny"
    And I want fill "fuelType" field with 1
    And I want fill "year" field with "2000"
    And I want to create vehicle and save id
    Then response code is 200
    And I see field "regNo" filled with "B1234567890"
    Then I want fill vehicle id
    And I want get inspection form template for saved vehicleId
    And response code is 200
    And I see field "id"
    Then I want clean filled data
    And I want fill inspection form fields key 1 with "value" field with "true"
    And I want fill inspection form fields key 1 with "time" field with "30"
    And I want fill inspection form fields key 1 with "note" field with "test note"
    And I want upload field key 1 file
    And I want upload sign file
    And I want fill inspection form
    And response code is 200
    And I see field "form/title" filled with "test inspection form"
    And I see field "values/0/value" filled with true
    And I see field "values/0/time" filled with "string(30)"
    And I see field "values/0/note" filled with "test note"
    And I see field "values/0/file/id"
    And I see field "values/0/template/type" filled with "checkbox"
    And I see field "values/0/template/title" filled with "test checkbox"
    And I see field "vehicle/regNo" filled with "B1234567890"
    And I see field "status" filled with "pass"
    And I see field "statusRatio" filled with "1/1"
    And I see field "duration" filled with 30
    And I see field "files/0/type" filled with "sign"
    And I see field "date"
    Then I want clean filled data
    Then I want fill vehicle id
    And I want get inspection form list
    And I see field "data/0/form/title" filled with "test inspection form"
    And response code is 200
    Then I want clean filled data
    Then I want get inspection form by saved id
    And I see field "form/title" filled with "test inspection form"
    And response code is 200
    And I want get client by name "client-name-1" and save id
    And I want set saved team to inspection form
    And I want get inspection form template for saved vehicleId
    And response code is 200
    And I see field "id"
    And I want get client by name "client-name-0" and save id
    And I want set saved team to inspection form
    And I want set remembered team settings "inspectionFormPeriod" with value "inspectionFormPeriodOncePerDay"
    Then I want fill vehicle id
    And I want get inspection form template for saved vehicleId
    And response code is 200
    And I do not see field "id"
    And I want set remembered team settings "inspectionFormPeriod" with value "inspectionFormPeriodNever"
    And I want get inspection form template for saved vehicleId
    And response code is 200
    And I do not see field "id"
