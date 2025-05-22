Feature: Fuel Card Report

  Background:
    Given I signed with email "client-user-9@ocsico.com"

  Scenario: I want check upload file BP_Invoice.csv
    And I want upload file "fuelCard/BP_Invoice_valid" "csv" "505" "text/csv"
    And I want load file and save response
    And response code is 200
    And I see field "file/displayName" filled with "BP_Invoice_valid_test.csv"
    And I see field "data/0/id"
    And I see field "data/0/vehicle/regNo" filled with "221SVP"
    And I see field "data/0/transactionDate" filled with "2022-09-03T07:30:21+00:00"
    And I see field "data/0/refueledFuelType" filled with null
    And I see field "data/0/refueled" filled with "54.46"
    And I see field "data/0/comments/error/0/" filled with "Date in future!"
    And I see field "data/0/comments/warning/0/" filled with "Unknown fuel type!"
    And I see field "data/1/id"
    And I see field "data/1/vehicle" filled with "221SVP1"
    And I see field "data/1/transactionDate" filled with "2018-09-06T16:55:00+00:00"
    And I see field "data/1/refueledFuelType" filled with null
    And I see field "data/1/refueled" filled with "43.85"
    And I see field "data/1/comments/error/0" filled with "Unknown vehicle!"
    And I see field "data/1/comments/warning/0" filled with "Unknown fuel type!"
    And I see field "data/2/id"
    And I see field "data/2/vehicle/regNo" filled with "221SVP"
    And I see field "data/2/transactionDate" filled with "2018-09-07T16:49:02+00:00"
    And I see field "data/2/refueledFuelType" filled with null
    And I see field "data/2/refueled" filled with "46.67"
    And I see field "data/2/comments/warning/0" filled with "Unknown fuel type!"
    And I see field "data/3/id"
    And I see field "data/3/vehicle/regNo" filled with "221SVP"
    And I see field "data/3/transactionDate" filled with "2018-09-05T17:25:49+00:00"
    And I see field "data/3/refueledFuelType" filled with null
    And I see field "data/3/refueled" filled with "61.89"
    And I see field "data/3/comments/warning/0/" filled with "Unknown fuel type!"
    When I want save upload file "BP_Invoice_valid_test.csv"
    And response code is 201
    Given Elastica populate
    And I want fill "status" field with "saved"
    And I want fill "sort" field with "-transactionDate"
    Then I want get fuel card report
    Then response code is 200
    And I see field "total" filled with "2"
    And I see field "data/0/id"
    And I see field "data/0/fuelCardNumber" filled with null
    And I see field "data/0/refueled" filled with "46.67"
    And I see field "data/0/total" filled with "61.21"
    And I see field "data/0/fuelPrice" filled with "1.31"
    And I see field "data/0/petrolStation" filled with "BP SILKSTONE"
    And I see field "data/0/refueledFuelType" filled with null
    And I see field "data/0/transactionDate" filled with "2018-09-07T16:49:02+00:00"
    And I see field "data/0/isShowTime" filled with true
    And I see field "data/0/driver" filled with null
    And I see field "data/0/status" filled with "saved"
    And I see field "data/1/id"
    And I see field "data/1/fuelCardNumber" filled with null
    And I see field "data/1/refueled" filled with "61.89"
    And I see field "data/1/total" filled with "81.16"
    And I see field "data/1/fuelPrice" filled with "1.31"
    And I see field "data/1/petrolStation" filled with "BP YAMANTO"
    And I see field "data/1/refueledFuelType" filled with null
    And I see field "data/1/transactionDate" filled with "2018-09-05T17:25:49+00:00"
    When I want delete upload file "BP_Invoice_valid_test.csv"
    And response code is 204
    When I want upload file "fuelCard/BP_Invoice_invalid" "csv" "315" "text/csv"
    And I want load file and save response
    And response code is 400

  Scenario: I want check upload file Transaction.csv
    And I want upload file "fuelCard/Transaction_valid" "csv" "1140" "text/csv"
    And I want load file and save response
    And response code is 200
    And I see field "file/displayName" filled with "Transaction_valid_test.csv"
    And I see field "data/0/id"
    And I see field "data/0/vehicle/regNo" filled with "221SVP"
    And I see field "data/0/transactionDate" filled with "2122-09-20T15:30:00+00:00"
    And I see field "data/0/refueledFuelType/name" filled with "Diesel"
    And I see field "data/0/refueled" filled with "28.2488"
    And I see field "data/0/comments/error/0/" filled with "Date in future!"
    And I see field "data/1/id"
    And I see field "data/1/vehicle"
    And I see field "data/1/transactionDate" filled with "2018-09-01T15:27:00+00:00"
    And I see field "data/1/refueledFuelType" filled with "Diesel_UPL"
    And I see field "data/1/refueled" filled with "58.23"
    And I see field "data/1/comments/error/0" filled with "Unknown vehicle!"
    And I see field "data/1/comments/warning/0" filled with "Unknown fuel type!"
    And I see field "data/2/id"
    And I see field "data/2/vehicle"
    And I see field "data/2/transactionDate" filled with "2017-02-19T14:38:00+00:00"
    And I see field "data/2/refueledFuelType" filled with "Diesel_UPL"
    And I see field "data/2/refueled" filled with "62.75"
    And I see field "data/2/comments/warning/0" filled with "Unknown fuel type!"
    And I see field "data/3/id"
    And I see field "data/2/vehicle"
    And I see field "data/3/transactionDate" filled with "2019-02-07T14:06:00+00:00"
    And I see field "data/3/refueledFuelType/name" filled with "Diesel"
    And I see field "data/3/refueled" filled with "55.39"
    And I see field "data/3/comments/error/0/" filled with "Unknown vehicle!"
    When I want save upload file "Transaction_valid_test.csv"
    And response code is 201
    Given Elastica populate
    And I want fill "status" field with "saved"
    And I want fill "sort" field with "-transactionDate"
    Then I want get fuel card report
    Then response code is 200
    And I see field "total" filled with "1"
    And I see field "data/0/id"
    And I see field "data/0/fuelCardNumber" filled with "string(7034305095057913)"
    And I see field "data/0/refueled" filled with "62.75"
    And I see field "data/0/total" filled with "91.55"
    And I see field "data/0/fuelPrice" filled with "1.46"
    And I see field "data/0/petrolStation" filled with "NORMANVILLE SERVICE STATION"
    And I see field "data/0/refueledFuelType" filled with "Diesel_UPL"
    And I see field "data/0/transactionDate" filled with "2017-02-19T14:38:00+00:00"
    And I see field "data/0/vehicle/regNo" filled with "221SVP"
    And I see field "data/0/driver" filled with null
    And I see field "data/0/status" filled with "saved"
    When I want delete upload file "Transaction_valid_test.csv"
    And response code is 204

  Scenario: I want check upload file Fleet Card (ACS)_valid.csv
    And I want upload file "fuelCard/Fleet Card (ACS)_valid" "csv" "2165" "text/csv"
    And I want load file and save response
    And response code is 200
    And I see field "file/displayName" filled with "Fleet Card (ACS)_valid_test.csv"
    And I see field "data/0/id"
    And I see field "data/0/vehicle/regNo" filled with "221SVP"
    And I see field "data/0/transactionDate" filled with "2122-08-01T00:00:00+00:00"
    And I see field "data/0/refueledFuelType/name" filled with "Diesel"
    And I see field "data/0/refueled" filled with "95.38"
    And I see field "data/0/comments/error/0" filled with "Capacity mismatch!"
    And I see field "data/0/comments/error/1" filled with "Date in future!"
    And I see field "data/1/id"
    And I see field "data/1/vehicle/regNo" filled with "221SVP"
    And I see field "data/1/transactionDate" filled with "2018-08-03T00:00:00+00:00"
    And I see field "data/1/refueledFuelType/name" filled with "Diesel"
    And I see field "data/1/refueled" filled with "8.64"
    And I see field "data/1/comments"
    And I see field "data/2/id"
    And I see field "data/2/vehicle/regNo" filled with "221SVP"
    And I see field "data/2/transactionDate" filled with "2018-08-03T00:00:00+00:00"
    And I see field "data/2/refueledFuelType/name" filled with "Diesel"
    And I see field "data/2/refueled" filled with "8.64"
    And I see field "data/2/comments"
    And I see field "data/3/id"
    And I see field "data/3/vehicle/regNo" filled with "221SVP"
    And I see field "data/3/transactionDate" filled with "2018-08-25T00:00:00+00:00"
    And I see field "data/3/refueledFuelType/name" filled with "Petrol"
    And I see field "data/3/refueled" filled with "10.43"
    And I see field "data/3/comments/error/0" filled with "Fuel type mismatch!"
    And I see field "data/4/id"
    And I see field "data/4/vehicle" filled with "Z04428"
    And I see field "data/4/transactionDate" filled with "2018-08-21T00:00:00+00:00"
    And I see field "data/4/refueledFuelType/name" filled with "Diesel"
    And I see field "data/4/refueled" filled with "96.41"
    And I see field "data/4/comments/error/0" filled with "Unknown vehicle!"
    When I want save upload file "Fleet Card (ACS)_valid_test.csv"
    And response code is 201
    Given Elastica populate
    And I want fill "sort" field with "-transactionDate"
    And I want fill "status" field with "saved"
    Then I want get fuel card report
    Then response code is 200
    And I see field "total" filled with "2"
    And I see field "data/0/id"
    And I see field "data/0/fuelCardNumber" filled with "string(7034305091964237)"
    And I see field "data/0/refueled" filled with "8.64"
    And I see field "data/0/total" filled with "9.5"
    And I see field "data/0/fuelPrice" filled with "1.1"
    And I see field "data/0/petrolStation" filled with "7 ELEVEN 1260 CHIRNSIDE PARK"
    And I see field "data/0/refueledFuelType/name" filled with "Diesel"
    And I see field "data/0/transactionDate" filled with "2018-08-03T00:00:00+00:00"
    And I see field "data/0/isShowTime" filled with false
    And I see field "data/0/driver" filled with null
    And I see field "data/0/status" filled with "saved"
    And I see field "data/1/id"
    And I see field "data/1/fuelCardNumber" filled with "string(7034305091964237)"
    And I see field "data/1/refueled" filled with "8.64"
    And I see field "data/1/total" filled with "9.5"
    And I see field "data/1/fuelPrice" filled with "1.1"
    And I see field "data/1/petrolStation" filled with "7 ELEVEN 1260 CHIRNSIDE PARK"
    And I see field "data/1/refueledFuelType/name" filled with "Diesel"
    And I see field "data/1/transactionDate" filled with "2018-08-03T00:00:00+00:00"
    And I see field "data/1/isShowTime" filled with false
    And I see field "data/1/driver" filled with null
    And I see field "data/1/status" filled with "saved"
    When I want delete upload file "Fleet Card (ACS)_valid_test.csv"
    And response code is 204

  Scenario: I want check upload file Caltex_valid.xlsx
    And I want upload file "fuelCard/Caltex_valid" "xlsx" "10048" "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    And I want load file and save response
    And response code is 200
    And I see field "file/displayName" filled with "Caltex_valid_test.xlsx"
    And I see field "data/0/id"
    And I see field "data/0/vehicle/regNo" filled with "221SVP"
    And I see field "data/0/vehicle/fuelTypeArray/name" filled with "Diesel"
    And I see field "data/0/transactionDate" filled with "2022-11-05T00:00:00+00:00"
    And I see field "data/0/refueledFuelType/name" filled with "Petrol"
    And I see field "data/0/refueled" filled with "19.04"
    And I see field "data/0/comments/error/0" filled with "Fuel type mismatch!"
    And I see field "data/0/comments/error/1" filled with "Date in future!"
    And I see field "data/1/id"
    And I see field "data/1/vehicle/regNo" filled with "221SVP"
    And I see field "data/1/vehicle/fuelTypeArray/name" filled with "Diesel"
    And I see field "data/1/transactionDate" filled with "2018-11-12T00:00:00+00:00"
    And I see field "data/1/refueledFuelType" filled with "ULP_01"
    And I see field "data/1/refueled" filled with "26.21"
    And I see field "data/1/comments/warning/0" filled with "Unknown fuel type!"
    And I see field "data/2/id"
    And I see field "data/2/vehicle/regNo" filled with "221SVP"
    And I see field "data/2/vehicle/fuelTypeArray/name" filled with "Diesel"
    And I see field "data/2/transactionDate" filled with "2018-11-28T00:00:00+00:00"
    And I see field "data/2/refueledFuelType/name" filled with "Petrol"
    And I see field "data/2/refueled" filled with "25.18"
    And I see field "data/2/comments/error/0" filled with "Fuel type mismatch!"
    And I see field "data/3/id"
    And I see field "data/3/vehicle"
    And I see field "data/3/transactionDate" filled with "2018-11-02T00:00:00+00:00"
    And I see field "data/3/refueledFuelType/name" filled with "Petrol"
    And I see field "data/3/refueled" filled with "37.22"
    And I see field "data/3/comments/error/0" filled with "Unknown vehicle!"
    When I want save upload file "Caltex_valid_test.xlsx"
    And response code is 201
    Given Elastica populate
    And I want fill "status" field with "saved"
    And I want fill "sort" field with "-transactionDate"
    Then I want get fuel card report
    Then response code is 200
    And I see field "total" filled with "1"
    And I see field "data/0/id"
    And I see field "data/0/fuelCardNumber" filled with "string(7071340087663612)"
    And I see field "data/0/refueled" filled with "26.21"
    And I see field "data/0/total" filled with "33.72"
    And I see field "data/0/fuelPrice" filled with "1.29"
    And I see field "data/0/petrolStation" filled with "Emu Heights Woolworths S"
    And I see field "data/0/refueledFuelType" filled with "ULP_01"
    And I see field "data/0/transactionDate" filled with "2018-11-12T00:00:00+00:00"
    And I see field "data/0/isShowTime" filled with false
    And I see field "data/0/driver" filled with null
    And I see field "data/0/status" filled with "saved"
    When I want delete upload file "Caltex_valid_test.xlsx"
    And response code is 204

  Scenario: I want check upload file Motorpass_valid.txt
    And I want upload file "fuelCard/Motorpass_valid" "txt" "3444" "text/plain"
    And I want load file and save response
    And response code is 200
    And I see field "file/displayName" filled with "Motorpass_valid_test.txt"
    And I see field "data/0/id"
    And I see field "data/0/vehicle/regNo" filled with "221SVP"
    And I see field "data/0/vehicle/fuelTypeArray/name" filled with "Diesel"
    And I see field "data/0/transactionDate" filled with "2122-10-16T00:00:00+00:00"
    And I see field "data/0/refueledFuelType/name" filled with "Petrol"
    And I see field "data/0/refueled" filled with "27.91"
    And I see field "data/0/comments/error/0" filled with "Fuel type mismatch!"
    And I see field "data/0/comments/error/1" filled with "Date in future!"
    And I see field "data/1/id"
    And I see field "data/1/vehicle/regNo" filled with "221SVP"
    And I see field "data/1/transactionDate" filled with "2018-10-30T00:00:00+00:00"
    And I see field "data/1/refueledFuelType" filled with "E10 ULP_01"
    And I see field "data/1/refueled" filled with "33.06"
    And I see field "data/1/comments/warning/0" filled with "Unknown fuel type!"
    And I see field "data/2/id"
    And I see field "data/2/vehicle"
    And I see field "data/2/transactionDate" filled with "2018-10-12T00:00:00+00:00"
    And I see field "data/2/refueledFuelType/name" filled with "Petrol"
    And I see field "data/2/refueled" filled with "18.72"
    And I see field "data/2/comments/error/0" filled with "Unknown vehicle!"
    When I want save upload file "Motorpass_valid_test.txt"
    And response code is 201
    Given Elastica populate
    And I want fill "status" field with "saved"
    And I want fill "sort" field with "-transactionDate"
    Then I want get fuel card report
    Then response code is 200
    And I see field "total" filled with "1"
    And I see field "data/0/id"
    And I see field "data/0/fuelCardNumber" filled with "1630 2415"
    And I see field "data/0/refueled" filled with "33.06"
    And I see field "data/0/total" filled with "49.56"
    And I see field "data/0/fuelPrice" filled with "1.5"
    And I see field "data/0/petrolStation" filled with "COLES EXPRESS COLYTON"
    And I see field "data/0/refueledFuelType" filled with "E10 ULP_01"
    And I see field "data/0/transactionDate" filled with "2018-10-30T00:00:00+00:00"
    And I see field "data/0/isShowTime" filled with false
    And I see field "data/0/driver" filled with null
    And I see field "data/0/status" filled with "saved"
    When I want delete upload file "Motorpass_valid_test.txt"
    And response code is 204

  Scenario: I want check upload file MPDATA010718_valid.txt
    And I want upload file "fuelCard/MPDATA010718_valid" "txt" "3444" "text/plain"
    And I want load file and save response
    And response code is 200
    And I see field "file/displayName" filled with "MPDATA010718_valid_test.txt"
    And I see field "data/0/id"
    And I see field "data/0/vehicle/regNo" filled with "221SVP"
    And I see field "data/0/vehicle/fuelTypeArray/name" filled with "Diesel"
    And I see field "data/0/transactionDate" filled with "2018-06-06T00:00:00+00:00"
    And I see field "data/0/refueledFuelType" filled with "PREMIUM"
    And I see field "data/0/refueled" filled with "26.89"
    And I see field "data/0/comments/warning/0" filled with "Unknown fuel type!"
    And I see field "data/1/id"
    And I see field "data/1/vehicle/regNo" filled with "221SVP"
    And I see field "data/1/vehicle/fuelTypeArray/name" filled with "Diesel"
    And I see field "data/1/transactionDate" filled with "2122-06-20T00:00:00+00:00"
    And I see field "data/1/refueledFuelType/name" filled with "Petrol"
    And I see field "data/1/refueled" filled with "28.02"
    And I see field "data/1/comments/error/0" filled with "Fuel type mismatch!"
    And I see field "data/1/comments/error/1" filled with "Date in future!"
    And I see field "data/2/id"
    And I see field "data/2/vehicle"
    And I see field "data/2/transactionDate" filled with "2018-06-01T00:00:00+00:00"
    And I see field "data/2/refueledFuelType/name" filled with "Petrol"
    And I see field "data/2/refueled" filled with "41.2"
    And I see field "data/2/comments/error/0" filled with "Unknown vehicle!"
    And I see field "data/3/id"
    And I see field "data/3/vehicle"
    And I see field "data/3/transactionDate" filled with "2018-06-15T00:00:00+00:00"
    And I see field "data/3/refueledFuelType/name" filled with "Petrol"
    And I see field "data/3/refueled" filled with "39.25"
    And I see field "data/3/comments/error/0" filled with "Unknown vehicle!"
    When I want save upload file "MPDATA010718_valid_test.txt"
    And response code is 201
    Given Elastica populate
    And I want fill "status" field with "saved"
    And I want fill "sort" field with "-transactionDate"
    Then I want get fuel card report
    Then response code is 200
    And I see field "total" filled with "1"
    And I see field "data/0/id"
    And I see field "data/0/fuelCardNumber" filled with "1630 2415"
    And I see field "data/0/refueled" filled with "26.89"
    And I see field "data/0/total" filled with "43"
    And I see field "data/0/fuelPrice" filled with "1.6"
    And I see field "data/0/petrolStation" filled with "BP CONNECT ST MARYS"
    And I see field "data/0/refueledFuelType" filled with "PREMIUM"
    And I see field "data/0/transactionDate" filled with "2018-06-06T00:00:00+00:00"
    And I see field "data/0/isShowTime" filled with false
    And I see field "data/0/driver" filled with null
    And I see field "data/0/status" filled with "saved"
    When I want delete upload file "MPDATA010718_valid_test.txt"
    And response code is 204

  Scenario: I want check upload file Shell_valid.xls
    And I want upload file "fuelCard/Shell_valid" "xls" "13824" "application/vnd.ms-excel"
    And I want load file and save response
    And response code is 200
    And I see field "file/displayName" filled with "Shell_valid_test.xls"
    And I see field "data/0/id"
    And I see field "data/0/vehicle/regNo" filled with "221SVP"
    And I see field "data/0/transactionDate" filled with "2122-03-10T09:51:00+00:00"
    And I see field "data/0/refueledFuelType/name" filled with "Diesel"
    And I see field "data/0/refueled" filled with "custom(40.40)"
    And I see field "data/0/comments/error/0" filled with "Date in future!"
    And I see field "data/1/id"
    And I see field "data/1/vehicle/regNo" filled with "132XRZ"
    And I see field "data/1/vehicle/fuelTypeArray" filled with null
    And I see field "data/1/transactionDate" filled with "2019-03-17T09:33:00+00:00"
    And I see field "data/1/refueledFuelType/name" filled with "Diesel"
    And I see field "data/1/refueled" filled with "custom(24.88)"
    And I see field "data/1/comments"
    And I see field "data/2/id"
    And I see field "data/2/vehicle"
    And I see field "data/2/transactionDate" filled with "2019-03-31T10:36:00+00:00"
    And I see field "data/2/refueledFuelType/name" filled with "Petrol"
    And I see field "data/2/refueled" filled with "custom(37.60)"
    And I see field "data/2/comments/error/0" filled with "Fuel type mismatch!"
    And I see field "data/3/id"
    And I see field "data/3/vehicle"
    And I see field "data/3/transactionDate" filled with "2019-03-31T09:14:00+00:00"
    And I see field "data/3/refueledFuelType/name" filled with "Petrol"
    And I see field "data/3/refueled" filled with "custom(23.43)"
    And I see field "data/3/comments/error/0" filled with "Fuel type mismatch!"
    When I want save upload file "Shell_valid_test.xls"
    And response code is 201
    Given Elastica populate
    And I want fill "status" field with "saved"
    And I want fill "sort" field with "-transactionDate"
    Then I want get fuel card report
    And I see field "total" filled with "1"
    And I see field "data/0/id"
    And I see field "data/0/fuelCardNumber" filled with "703430 1074037547"
    And I see field "data/0/refueled" filled with "24.88"
    And I see field "data/0/total" filled with "38.29"
    And I see field "data/0/fuelPrice" filled with "1.54"
    And I see field "data/0/petrolStation" filled with "NOWRA              NSW"
    And I see field "data/0/refueledFuelType/name" filled with "Diesel"
    And I see field "data/0/transactionDate" filled with "2019-03-17T09:33:00+00:00"
    And I see field "data/0/isShowTime" filled with true
    And I see field "data/0/driver" filled with null
    And I see field "data/0/status" filled with "saved"
    Then response code is 200
    When I want delete upload file "Shell_valid_test.xls"
    And response code is 204

  Scenario: I want check upload file Caltex_transaction_detail_valid.xlsx
    And I want upload file "fuelCard/Caltex_transaction_detail_valid" "xlsx" "12322" "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    And I want load file and save response
    And response code is 200
    And I see field "file/displayName" filled with "Caltex_transaction_detail_valid_test.xlsx"
    And I see field "data/0/id"
    And I see field "data/0/vehicle/regNo" filled with "221SVP"
    And I see field "data/0/transactionDate" filled with "2122-07-02T14:40:00+00:00"
    And I see field "data/0/refueledFuelType/name" filled with "Diesel"
    And I see field "data/0/refueled" filled with "34.18"
    And I see field "data/0/comments/error/0" filled with "Date in future!"
    And I see field "data/1/id"
    And I see field "data/1/vehicle/regNo" filled with "221SVP"
    And I see field "data/1/transactionDate" filled with "2020-07-21T15:09:00+00:00"
    And I see field "data/1/refueledFuelType" filled with "VORTEX 95 TEST"
    And I see field "data/1/refueled" filled with "35.06"
    And I see field "data/1/comments/warning/0" filled with "Unknown fuel type!"
    And I see field "data/2/id"
    And I see field "data/2/vehicle/regNo" filled with "221SVP"
    And I see field "data/2/transactionDate" filled with "2020-07-24T12:27:00+00:00"
    And I see field "data/2/refueledFuelType/name" filled with "Diesel"
    And I see field "data/2/refueled" filled with "31.79"
    And I see field "data/3/id"
    And I see field "data/3/transactionDate" filled with "2020-06-30T07:30:00+00:00"
    And I see field "data/3/refueledFuelType/name" filled with "Petrol"
    And I see field "data/3/refueled" filled with "37.64"
    And I see field "data/3/comments/error/0" filled with "Unknown vehicle!"
    When I want save upload file "Caltex_transaction_detail_valid_test.xlsx"
    And response code is 201
    Given Elastica populate
    And I want fill "status" field with "saved"
    And I want fill "sort" field with "-transactionDate"
    Then I want get fuel card report
    And I see field "total" filled with "2"
    And I see field "data/0/fuelCardNumber" filled with "string(7071340087663612)"
    And I see field "data/0/refueled" filled with "31.79"
    And I see field "data/0/total" filled with "1.439"
    And I see field "data/0/fuelPrice" filled with 0.05
    And I see field "data/0/petrolStation" filled with "EG FUELCO 1512 PLUMPTON"
    And I see field "data/0/refueledFuelType/name" filled with "Diesel"
    And I see field "data/0/transactionDate" filled with "2020-07-24T12:27:00+00:00"
    And I see field "data/0/isShowTime" filled with true
    And I see field "data/0/driver" filled with null
    And I see field "data/0/status" filled with "saved"
    And I see field "data/1/fuelCardNumber" filled with "string(7071340087663610)"
    And I see field "data/1/refueled" filled with "35.06"
    And I see field "data/1/total" filled with "1.549"
    And I see field "data/1/fuelPrice" filled with 0.04
    And I see field "data/1/petrolStation" filled with "EG FUELCO 1021 GRANVILLE"
    And I see field "data/1/refueledFuelType" filled with "VORTEX 95 TEST"
    And I see field "data/1/transactionDate" filled with "2020-07-21T15:09:00+00:00"
    And I see field "data/1/isShowTime" filled with true
    And I see field "data/1/driver" filled with null
    And I see field "data/1/status" filled with "saved"
    Then response code is 200
    When I want delete upload file "Caltex_transaction_detail_valid_test.xlsx"
    And response code is 204

  Scenario: I want check upload file FuelTransaction.csv
    And I want upload file "fuelCard/FuelTransaction_valid" "csv" "1140" "text/csv"
    And I want load file and save response
    And response code is 200
    And I see field "file/displayName" filled with "FuelTransaction_valid_test.csv"
    And I see field "data/0/id"
    And I see field "data/0/vehicle/regNo" filled with "221SVP"
    And I see field "data/0/transactionDate" filled with "2022-03-09T12:38:00+00:00"
    And I see field "data/0/refueledFuelType/name" filled with "Petrol (Ethanol Mix)"
    And I see field "data/0/refueled" filled with "41.88"
    And I see field "data/0/comments/error/0/" filled with "Unknown fuel type!"
    And I see field "data/1/id"
    And I see field "data/1/vehicle"
    And I see field "data/1/transactionDate" filled with "2022-02-28T14:46:00+00:00"
    And I see field "data/1/refueledFuelType" filled with "Petrol (Unleaded)"
    And I see field "data/1/refueled" filled with "31.78"
    And I see field "data/1/comments/error/0" filled with "Unknown vehicle!"
    And I see field "data/1/comments/warning/0" filled with "Unknown fuel type!"
    And I see field "data/2/id"
    And I see field "data/2/vehicle"
    And I see field "data/2/transactionDate" filled with "2022-03-03T16:44:00+00:00"
    And I see field "data/2/refueledFuelType" filled with "Diesel (Premium Diesel)"
    And I see field "data/2/refueled" filled with "74.65"
    And I see field "data/2/comments/error/0" filled with "Unknown vehicle!"
    And I see field "data/2/comments/warning/0" filled with "Unknown fuel type!"
    And I see field "data/3/id"
    And I see field "data/2/vehicle"
    And I see field "data/3/transactionDate" filled with "2019-02-07T14:06:00+00:00"
    And I see field "data/3/refueledFuelType/name" filled with "Diesel"
    And I see field "data/3/refueled" filled with "55.39"
    And I see field "data/3/comments/error/0/" filled with "Unknown vehicle!"
    When I want save upload file "FuelTransaction_valid_test.csv"
    And response code is 201
    Given Elastica populate
    And I want fill "status" field with "saved"
    And I want fill "sort" field with "-transactionDate"
    Then I want get fuel card report
    Then response code is 200
    And I see field "total" filled with "1"
    And I see field "data/0/id"
    And I see field "data/0/fuelCardNumber" filled with "string(7034305095057913)"
    And I see field "data/0/refueled" filled with "62.75"
    And I see field "data/0/total" filled with "91.55"
    And I see field "data/0/fuelPrice" filled with "1.46"
    And I see field "data/0/petrolStation" filled with "NORMANVILLE SERVICE STATION"
    And I see field "data/0/refueledFuelType" filled with "Diesel_UPL"
    And I see field "data/0/transactionDate" filled with "2017-02-19T14:38:00+00:00"
    And I see field "data/0/vehicle/regNo" filled with "221SVP"
    And I see field "data/0/driver" filled with null
    And I see field "data/0/status" filled with "saved"
    When I want delete upload file "FuelTransaction_valid_test.csv"
    And response code is 204

  Scenario: I want check upload file ShellV2_valid.csv
    And I want upload file "fuelCard/ShellV2_valid" "csv" "432" "text/csv"
    And I want load file and save response
    And response code is 200
    And I see field "file/displayName" filled with "ShellV2_valid_test.csv"
    And I see field "data/0/id"
    And I see field "data/0/vehicle/regNo" filled with "221SVP"
    And I see field "data/0/transactionDate" filled with "2022-09-21T16:25:00+00:00"
    And I see field "data/0/refueledFuelType/name" filled with "Petrol"
    And I see field "data/0/refueled" filled with "39.92"
#    And I see field "data/0/comments/error/0/" filled with "Unknown fuel type!"
    When I want save upload file "ShellV2_valid_test.csv"
    And response code is 201
    Given Elastica populate
    And I want fill "status" field with "saved"
    And I want fill "sort" field with "-transactionDate"
    Then I want get fuel card report
    Then response code is 200
    And I see field "total" filled with "1"
    And I see field "data/0/id"
    And I see field "data/0/fuelCardNumber" filled with "string(7034301095494362)"
    And I see field "data/0/refueled" filled with "39.92"
    And I see field "data/0/total" filled with "66.23"
    And I see field "data/0/fuelPrice" filled with "1.66"
    And I see field "data/0/petrolStation" filled with "COLES EXP 2720 ALBANY ORANA"
    And I see field "data/0/refueledFuelType" filled with "Petrol"
    And I see field "data/0/transactionDate" filled with "2022-09-21T16:25:00+00:00"
    And I see field "data/0/vehicle/regNo" filled with "221SVP"
    And I see field "data/0/driver" filled with null
    And I see field "data/0/status" filled with "saved"
    When I want delete upload file "ShellV2_valid_test.csv"
    And response code is 204

  Scenario: I want check upload file transactionsV4_valid.csv
    And I want upload file "fuelCard/transactionsV4_valid" "csv" "717" "text/csv"
    And I want load file and save response
    And response code is 200
    And I see field "file/displayName" filled with "transactionsV4_valid_test.csv"
    And I see field "data/0/id"
    And I see field "data/0/vehicle/regNo" filled with "221SVP"
    And I see field "data/0/transactionDate" filled with "2022-10-15T10:48:00+00:00"
    And I see field "data/0/refueledFuelType/name" filled with "Diesel"
    And I see field "data/0/refueled" filled with "63.81"
#    And I see field "data/0/comments/error/0/" filled with "Unknown fuel type!"
    When I want save upload file "transactionsV4_valid_test.csv"
    And response code is 201
    Given Elastica populate
    And I want fill "status" field with "saved"
    And I want fill "sort" field with "-transactionDate"
    Then I want get fuel card report
    Then response code is 200
    And I see field "total" filled with "1"
    And I see field "data/0/id"
    And I see field "data/0/fuelCardNumber" filled with "string(7034301095494362)"
    And I see field "data/0/refueled" filled with "63.81"
    And I see field "data/0/total" filled with "149.25"
#    And I see field "data/0/fuelPrice" filled with "1.66"
    And I see field "data/0/petrolStation" filled with "NORTH HOBART"
    And I see field "data/0/refueledFuelType" filled with "Diesel"
    And I see field "data/0/transactionDate" filled with "2022-10-15T10:48:00+00:00"
    And I see field "data/0/vehicle/regNo" filled with "221SVP"
    And I see field "data/0/driver" filled with null
    And I see field "data/0/status" filled with "saved"
    When I want delete upload file "transactionsV4_valid_test.csv"
    And response code is 204

  Scenario: I want get report Fuel Summary for elastica
    And I want upload file "fuelCard/Shell_summary" "xls" "13824" "application/vnd.ms-excel"
    And I want load file and save response
    And response code is 200
    When I want save upload file "Shell_summary_test.xls"
    And response code is 201
    Given Elastica populate
    And I want fill "status" field with "saved"
    Then I want get fuel summary report for elastica
    Then response code is 200
    And I see field "data/0/key" filled with "5"
    And I see field "data/0/refueled/value" filled with "102.88"
    And I see field "data/0/total/value" filled with "159.09"
    And I see field "data/0/top_hits/vehicle/regno" filled with "202HBP"
    And I see field "data/1/key" filled with "6"
    And I see field "data/1/refueled/value" filled with "46.86"
    And I see field "data/1/total/value" filled with "65.56"
    And I see field "data/1/top_hits/vehicle/regno" filled with "221SVP"
    And I see field "data/1/top_hits/vehicle/depot/name" filled with "Frenchams TP"
    And I see field "data/1/top_hits/vehicle/groups/0/name" filled with "TransPlant"
    When I want delete upload file "Shell_summary_test.xls"
    And response code is 204

  Scenario: I want check data access depending on team
    And I want upload file "fuelCard/Shell_valid" "xls" "13824" "application/vnd.ms-excel"
    And I want load file and save response
    And response code is 200
    When I want save upload file "Shell_valid_test.xls"
    And response code is 201
    Given Elastica populate
    And I want fill "status" field with "saved"
    And I want fill "sort" field with "-transactionDate"
    Then I want get fuel card report
    And I see field "data/0/id"
    And I see field "data/0/fuelCardNumber" filled with "703430 1074037547"
    And I see field "data/0/refueled" filled with "24.88"
    And I see field "data/0/total" filled with "38.29"
    And I see field "data/0/fuelPrice" filled with "1.54"
    And I see field "data/0/petrolStation" filled with "NOWRA              NSW"
    And I see field "data/0/refueledFuelType/name" filled with "Diesel"
    And I see field "data/0/transactionDate" filled with "2019-03-17T09:33:00+00:00"
    And I see field "data/0/isShowTime" filled with true
    And I see field "data/0/driver" filled with null
    And I see field "data/0/status" filled with "saved"
    And I see field "data/0/teamId" filled with 11
    Then response code is 200
    When I want delete upload file "Shell_valid_test.xls"
    And response code is 204
    Then I want clean files data
    Given I signed in as "admin" team "client"
    And I want fill "name" field with "test group"
    And I want to create vehicle group and save id
    And I want fill vehicle group id
    And I want fill "type" field with "Car"
    And I want fill "model" field with "model"
    And I want fill "isUnavailable" field with true
    And I want fill "regNo" field with "221SVP"
    And I want fill "defaultLabel" field with "defaultLabel"
    And I want fill "vin" field with "vin"
    And I want fill "regCertNo" field with "regCertNo"
    And I want fill "enginePower" field with 2.0
    And I want fill "engineCapacity" field with 1.0
    And I want fill "emissionClass" field with "emissionClass"
    And I want fill "co2Emissions" field with 0.1
    And I want fill "status" field with "active"
    And I want fill "teamId" field with 12
    Then I want to create vehicle and save id
    And response code is 200
    And I see field "team/id" filled with 12
    And I see field "team/type" filled with "client"
    And I see field "regNo" filled with "221SVP"
    Then I want clean filled data
    And I want upload file "fuelCard/MPDATA010718_valid" "txt" "3444" "text/plain"
    And I want load file and save response
    And response code is 200
    When I want save upload file "MPDATA010718_valid_test.txt"
    And response code is 201
    Given Elastica populate
    And I want fill "status" field with "saved"
    And I want fill "sort" field with "-transactionDate"
    Then I want get fuel card report
    Then response code is 200
    And I see field "data/0/id"
    And I see field "data/0/fuelCardNumber" filled with "1630 2415"
    And I see field "data/0/refueled" filled with "26.89"
    And I see field "data/0/total" filled with "43"
    And I see field "data/0/fuelPrice" filled with "1.6"
    And I see field "data/0/petrolStation" filled with "BP CONNECT ST MARYS"
    And I see field "data/0/refueledFuelType" filled with "PREMIUM"
    And I see field "data/0/transactionDate" filled with "2018-06-06T00:00:00+00:00"
    And I see field "data/0/isShowTime" filled with false
    And I see field "data/0/driver" filled with null
    And I see field "data/0/status" filled with "saved"
    And I see field "data/0/teamId" filled with 12
    When I want delete upload file "MPDATA010718_valid_test.txt"
    And response code is 204

  Scenario: I want check duplicate records validations
    And I want upload file "fuelCard/Motorpass_valid" "txt" "3444" "text/plain"
    And I want load file and save response
    And response code is 200
    And I see field "file/displayName" filled with "Motorpass_valid_test.txt"
    And I see field "data/0/id"
    And I see field "data/0/vehicle/regNo" filled with "221SVP"
    And I see field "data/0/vehicle/fuelTypeArray/name" filled with "Diesel"
    And I see field "data/0/transactionDate" filled with "2122-10-16T00:00:00+00:00"
    And I see field "data/0/refueledFuelType/name" filled with "Petrol"
    And I see field "data/0/refueled" filled with "27.91"
    And I see field "data/0/comments/error/0" filled with "Fuel type mismatch!"
    And I see field "data/0/comments/error/1" filled with "Date in future!"
    And I see field "data/1/id"
    And I see field "data/1/vehicle/regNo" filled with "221SVP"
    And I see field "data/1/transactionDate" filled with "2018-10-30T00:00:00+00:00"
    And I see field "data/1/refueledFuelType" filled with "E10 ULP_01"
    And I see field "data/1/refueled" filled with "33.06"
    And I see field "data/1/comments/warning/0" filled with "Unknown fuel type!"
    And I see field "data/2/id"
    And I see field "data/2/vehicle"
    And I see field "data/2/transactionDate" filled with "2018-10-12T00:00:00+00:00"
    And I see field "data/2/refueledFuelType/name" filled with "Petrol"
    And I see field "data/2/refueled" filled with "18.72"
    And I see field "data/2/comments/error/0" filled with "Unknown vehicle!"
    When I want save upload file "Motorpass_valid_test.txt"
    And response code is 201
    Then I want clean files data
    And I want upload file "fuelCard/Motorpass_valid" "txt" "3444" "text/plain"
    And I want load file and save response
    And response code is 200
    And I see field "file/displayName" filled with "Motorpass_valid_test.txt"
    And I see field "data/0/id"
    And I see field "data/0/vehicle/regNo" filled with "221SVP"
    And I see field "data/0/vehicle/fuelTypeArray/name" filled with "Diesel"
    And I see field "data/0/transactionDate" filled with "2122-10-16T00:00:00+00:00"
    And I see field "data/0/refueledFuelType/name" filled with "Petrol"
    And I see field "data/0/refueled" filled with "27.91"
    And I see field "data/0/comments/error/0" filled with "Fuel type mismatch!"
    And I see field "data/0/comments/error/1" filled with "Date in future!"
    And I see field "data/1/id"
    And I see field "data/1/vehicle/regNo" filled with "221SVP"
    And I see field "data/1/transactionDate" filled with "2018-10-30T00:00:00+00:00"
    And I see field "data/1/refueledFuelType" filled with "E10 ULP_01"
    And I see field "data/1/refueled" filled with "33.06"
    And I see field "data/1/comments/warning/0" filled with "Unknown fuel type!"
    And I see field "data/1/comments/warning/1" filled with "Duplicate?"
    And I see field "data/2/id"
    And I see field "data/2/vehicle"
    And I see field "data/2/transactionDate" filled with "2018-10-12T00:00:00+00:00"
    And I see field "data/2/refueledFuelType/name" filled with "Petrol"
    And I see field "data/2/refueled" filled with "18.72"
    And I see field "data/2/comments/error/0" filled with "Unknown vehicle!"
    When I want save upload file "Motorpass_valid_test.txt"
    And response code is 201
    When I want delete upload file "Motorpass_valid_test.txt"
    And response code is 204

  Scenario: I want get report Fuel Summary for query DB and export report to csv
    And I want upload file "fuelCard/Shell_summary" "xls" "13824" "application/vnd.ms-excel"
    And I want load file and save response
    And response code is 200
    When I want save upload file "Shell_summary_test.xls"
    And response code is 201
    And I want fill "startDate" field with "2019-03-01T00:00:01+00:00"
    And I want fill "endDate" field with "2019-11-30T00:00:01+00:00"
    And I want fill "sort" field with "-refueled"
    Then I want get fuel summary report for query-db
    Then response code is 200
    And I see field "data/0/vehicle_id" filled with "5"
    And I see field "data/0/reg_no" filled with "202HBP"
    And I see field "data/0/depot" filled with null
    And I see field "data/0/groups" filled with null
    And I see field "data/0/total" filled with "custom(159.09)"
    And I see field "data/0/refueled" filled with "custom(102.88)"
    And I see field "data/1/vehicle_id" filled with "6"
    And I see field "data/1/reg_no" filled with "221SVP"
    And I see field "data/1/depot" filled with "Frenchams TP"
    And I see field "data/1/groups" filled with "TransPlant"
    And I see field "data/1/total" filled with "custom(65.56)"
    And I see field "data/1/refueled" filled with "custom(46.86)"
    And response code is 200
    And I want fill "startDate" field with "2019-03-01T00:00:01+00:00"
    And I want fill "endDate" field with "2019-11-30T00:00:01+00:00"
    And I want fill "sort" field with "-refueled"
    Then I want export fuel summary report for query-db
    And I see csv item number 0 field "VEHICLE REGNO" filled with "202HBP"
    And I see csv item number 0 field "DEPOT" filled with ''
    And I see csv item number 0 field "GROUPS" filled with ''
    And I see csv item number 0 field "Total (AUD)" filled with "159.09"
    And I see csv item number 0 field "Refueled (l)" filled with "102.88"
    And I see csv item number 1 field "VEHICLE REGNO" filled with "221SVP"
    And I see csv item number 1 field "DEPOT" filled with "Frenchams TP"
    And I see csv item number 1 field "GROUPS" filled with "TransPlant"
    And I see csv item number 1 field "Total (AUD)" filled with "65.56"
    And I see csv item number 1 field "Refueled (l)" filled with "46.86"
    When I want delete upload file "Shell_summary_test.xls"
    And response code is 204

  Scenario: I want check upload file BP_Invoice.csv with vehicle coordinates
    And I want upload file "fuelCard/BP_Invoice_valid" "csv" "505" "text/csv"
    And I want load file and save response
    And response code is 200
    When I want save upload file "BP_Invoice_valid_test.csv"
    And response code is 201
    Given Elastica populate
    And I want fill "status" field with "saved"
    And I want fill "sort" field with "-transactionDate"
    Then I want add value to array key "fields" with "carCoordinates"
    And I want get fuel card report
    Then response code is 200
    And I see field "data/0/id"
    And I see field "data/0/fuelCardNumber" filled with null
    And I see field "data/0/refueled" filled with "46.67"
    And I see field "data/0/total" filled with "61.21"
    And I see field "data/0/fuelPrice" filled with "1.31"
    And I see field "data/0/petrolStation" filled with "BP SILKSTONE"
    And I see field "data/0/refueledFuelType" filled with "null"
    And I see field "data/0/transactionDate" filled with "2018-09-07T16:49:02+00:00"
    And I see field "data/0/isShowTime" filled with true
    And I see field "data/0/driver" filled with null
    And I see field "data/0/status" filled with "saved"
    And I see field "data/0/carCoordinates" filled with "null"
    And I see field "data/1/id"
    And I see field "data/1/fuelCardNumber" filled with null
    And I see field "data/1/refueled" filled with "61.89"
    And I see field "data/1/total" filled with "81.16"
    And I see field "data/1/fuelPrice" filled with "1.31"
    And I see field "data/1/petrolStation" filled with "BP YAMANTO"
    And I see field "data/1/refueledFuelType" filled with null
    And I see field "data/1/transactionDate" filled with "2018-09-05T17:25:49+00:00"
    And I see field "data/1/carCoordinates" filled with "null"

  Scenario: I want get full report "Fuel Card Records" for query Elastica, check access for team and export data to csv
    And I want upload file "fuelCard/Transaction_valid" "csv" "1140" "text/csv"
    And I want load file and save response
    And response code is 200
    And I see field "file/displayName" filled with "Transaction_valid_test.csv"
    And I see field "data/0/id"
    And I see field "data/0/vehicle/regNo" filled with "221SVP"
    And I see field "data/0/transactionDate" filled with "2122-09-20T15:30:00+00:00"
    And I see field "data/0/refueledFuelType/name" filled with "Diesel"
    And I see field "data/0/refueled" filled with "28.2488"
    And I see field "data/0/comments/error/0/" filled with "Date in future!"
    And I see field "data/1/id"
    And I see field "data/1/vehicle"
    And I see field "data/1/transactionDate" filled with "2018-09-01T15:27:00+00:00"
    And I see field "data/1/refueledFuelType" filled with "Diesel_UPL"
    And I see field "data/1/refueled" filled with "58.23"
    And I see field "data/1/comments/error/0" filled with "Unknown vehicle!"
    And I see field "data/1/comments/warning/0" filled with "Unknown fuel type!"
    And I see field "data/2/id"
    And I see field "data/2/vehicle"
    And I see field "data/2/transactionDate" filled with "2017-02-19T14:38:00+00:00"
    And I see field "data/2/refueledFuelType" filled with "Diesel_UPL"
    And I see field "data/2/refueled" filled with "62.75"
    And I see field "data/2/comments/warning/0" filled with "Unknown fuel type!"
    And I see field "data/3/id"
    And I see field "data/2/vehicle"
    And I see field "data/3/transactionDate" filled with "2019-02-07T14:06:00+00:00"
    And I see field "data/3/refueledFuelType/name" filled with "Diesel"
    And I see field "data/3/refueled" filled with "55.39"
    And I see field "data/3/comments/error/0/" filled with "Unknown vehicle!"
    When I want save upload file "Transaction_valid_test.csv"
    And response code is 201
    Given Elastica populate
    And I want fill "status" field with "saved"
    And I want fill "sort" field with "-transactionDate"
    And I want fill "vehicleRegNo" field with "221SVP"
    Then I want get fuel card report
    Then response code is 200
    And I see field "data/0/id"
    And I see field "data/0/fuelCardNumber" filled with "string(7034305095057913)"
    And I see field "data/0/refueled" filled with "62.75"
    And I see field "data/0/total" filled with "91.55"
    And I see field "data/0/fuelPrice" filled with "1.46"
    And I see field "data/0/petrolStation" filled with "NORMANVILLE SERVICE STATION"
    And I see field "data/0/refueledFuelType" filled with "Diesel_UPL"
    And I see field "data/0/transactionDate" filled with "2017-02-19T14:38:00+00:00"
    And I see field "data/0/vehicle/regNo" filled with "221SVP"
    And I see field "data/0/driver" filled with null
    And I see field "data/0/status" filled with "saved"
    And I see field "additionalFields/total/refueled/value" filled with "62.75"
    And I see field "additionalFields/total/total/value" filled with "91.55"
    And I see field "additionalFields/total/fuelPrice/value" filled with "1.46"
    Then I want add value to array key "fields" with "id"
    Then I want add value to array key "fields" with "regNo"
    Then I want add value to array key "fields" with "model"
    Then I want add value to array key "fields" with "defaultLabel"
    Then I want add value to array key "fields" with "groupsList"
    Then I want add value to array key "fields" with "depotName"
    Then I want add value to array key "fields" with "fuelType"
    Then I want add value to array key "fields" with "fuelCardNumber"
    Then I want add value to array key "fields" with "refueled"
    Then I want add value to array key "fields" with "total"
    Then I want add value to array key "fields" with "fuelPrice"
    Then I want add value to array key "fields" with "petrolStation"
    Then I want add value to array key "fields" with "refueledFuelType"
    Then I want add value to array key "fields" with "transactionDate"
    Then I want add value to array key "fields" with "driver"
    Then I want export fuel card report
    And I see csv item number 0 field "ID" is not empty
    And I see csv item number 0 field "Regno" filled with "221SVP"
    And I see csv item number 0 field "Model" filled with "Toyota Hiace"
    And I see csv item number 0 field "Title" filled with "Matt"
    And I see csv item number 0 field "Depot" filled with "Frenchams TP"
    And I see csv item number 0 field "Groups" filled with "TransPlant"
    And I see csv item number 0 field "Fuel Type" filled with "Diesel"
    And I see csv item number 0 field "Fuel Card Number" filled with "7034305095057913"
    And I see csv item number 0 field "Refueled (l)" filled with "62.75"
    And I see csv item number 0 field "Total (AUD)" filled with "91.55"
    And I see csv item number 0 field "Petrol Station" filled with "NORMANVILLE SERVICE STATION"
    And I see csv item number 0 field "Refueled Fuel Type" filled with "Diesel_UPL"
    And I see csv item number 0 field "Transaction Date" filled with "2017-02-19T14:38:00+00:00"
    And I see csv item number 0 field "Driver" filled with ""
    And I want fill "status" field with "saved"
    And I want fill "sort" field with "-transactionDate"
    Then I want get fuel card report
    Then response code is 200
    And I see field "total" filled with 1
    And I see field "data/0/id"
    And I see field "data/0/fuelCardNumber" filled with "string(7034305095057913)"
    And I see field "data/0/refueled" filled with "62.75"
    And I see field "data/0/total" filled with "91.55"
    And I see field "data/0/fuelPrice" filled with "1.46"
    And I see field "data/0/petrolStation" filled with "NORMANVILLE SERVICE STATION"
    And I see field "data/0/refueledFuelType" filled with "Diesel_UPL"
    And I see field "data/0/transactionDate" filled with "2017-02-19T14:38:00+00:00"
    And I see field "data/0/vehicle/regNo" filled with "221SVP"
    And I see field "data/0/driver" filled with null
    And I see field "data/0/status" filled with "saved"
    Then I want clean files data
    Given I signed in as "admin" team "client"
    And I want fill "status" field with "saved"
    And I want fill "sort" field with "-transactionDate"
    And I want fill "vehicleRegNo" field with "221SVP"
    Then I want get fuel card report
    Then response code is 200
    And I see field "total" filled with 0
    When I want delete upload file "Transaction_valid_test.csv"
    And response code is 204

  Scenario: I want check permissions
    Then I signed in as "support" team "admin"
    And I want upload file "fuelCard/BP_Invoice_valid" "csv" "505" "text/csv"
    And I want load file and save response
    Then I see field "errors/0/detail" filled with "Access Denied."
    Then I signed in as "installer" team "admin"
    And I want upload file "fuelCard/BP_Invoice_valid" "csv" "505" "text/csv"
    And I want load file and save response
    Then I see field "errors/0/detail" filled with "Access Denied."
    Then I signed in as "client_manager" team "admin"
    And I want upload file "fuelCard/BP_Invoice_valid" "csv" "505" "text/csv"
    And I want load file and save response
    Then I see field "errors/0/detail" filled with "Access Denied."
    Then I signed in as "admin" team "admin"
    And I want upload file "fuelCard/BP_Invoice_valid" "csv" "505" "text/csv"
    And I want load file and save response
    Then I see field "errors/0/detail" filled with "Access Denied."
    Then I signed in as "super_admin" team "admin"
    And I want upload file "fuelCard/BP_Invoice_valid" "csv" "505" "text/csv"
    And I want load file and save response
    Then I see field "errors/0/detail" filled with "Access Denied."
