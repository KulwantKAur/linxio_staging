Feature: ReminderCategory

  Scenario: I want CRUD reminderCategory
    # create vehicle + reminder
    Given I signed in as "super_admin" team "admin"
    When I want get client by name "client-name-0" and save id
    And I want fill teamId by saved clientId
    And I want fill "type" field with "Car"
    And I want fill "model" field with "model"
    And I want fill "status" field with "active"
    Then I want to create vehicle and save id
    And response code is 200
    And I see field "team/type" filled with "client"
    And I see field "type" filled with "type"
    # create reminder category
    Then I want clean filled data
    And I want fill "name" field with "test reminder category"
    And I want fill teamId by saved clientId
    And I want create reminder category and save id
    And I see field "name" filled with "test reminder category"
    # edit reminder category
    Then I want clean filled data
    And I want fill "name" field with "test reminder category2"
    Then I want edit reminder category
    And I see field "name" filled with "test reminder category2"
    # get reminder category list
    Then I want clean filled data
    And I want get reminder category list
    And I see field "total" filled with 5
    And I see field "data/4/name" filled with "test reminder category2"
    Then I want add value to array key "fields" with "name"
    Then I want add value to array key "fields" with "status"
    Then I want export reminder category list
    And I see csv item number 4 field "Name" filled with "test reminder category2"
    And I see csv item number 4 field "Status" filled with "active"
    Given I signed in as "admin" team "client" and teamId 2
    And I want clean filled data
    Then I want fill "title" field with "test reminder"
    And I want fill vehicle id
    And I want fill "status" field with "active"
    And I want fill "date" field with "2020-10-10 12:12:12"
    And I want fill "datePeriod" field with "15"
    And I want fill "category" with saved reminder category id
    Then I want to create reminder and save id
    And response code is 200
    And I see field "title" filled with "test reminder"
    # get reminder list with category filter
    Then I want clean filled data
    And I want fill categoryId with saved reminder category id
    Then I want get reminder list
    And I see field "total" filled with 1
    And I see field "data/0/category/name" filled with "test reminder category2"
    Then I want clean filled data
    And I want fill "categoryId" field with 999
    Then I want get reminder list
    And I see field "total" filled with 0
    Given I signed in as "super_admin" team "admin"
    # delete reminder category
    Then I want clean filled data
    And I want to delete reminder category by saved id
    And response code is 204
    And I want fill categoryId with saved reminder category id
    Then I want get reminder list
    And I see field "total" filled with 0
    Then I want clean filled data
    And I want get reminder category list
    And I see field "total" filled with 4
