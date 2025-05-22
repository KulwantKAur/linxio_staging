# set `APP_FRONT_URL=https://url` in file `.env`
Feature: Areas Events

  Background:
    Given I signed in as "super_admin" team "admin"
    And the queue associated to notification.events events producer is empty
    Given I want handle check area event

  Scenario: I want check notification by visited (enter) geofences
    And I want to fill teamId by team of current user
    And I want fill device model for vehicle with name "TLP1-SF"
    And I want fill "sn" field with "test sn"
    And I want fill "status" field with "inStock"
    And I want fill "port" field with 25
    And I want fill "hw" field with "test hw"
    And I want fill "sw" field with "test sw"
    And I want fill "imei" field with "888888888888888"
    And I want fill "phone" field with "+(375) 245535454"
    And I want fill "imsi" field with "test imsi"
    And I want fill "devEui" field with "test devEui"
    And I want fill "userName" field with "test username"
    And I want fill "password" field with "test password"
    And I want fill "clientNote" field with "test clientNote"
    And I want fill "adminNote" field with "test adminNote"
    And I want to create device for vehicle and save id
    And I want clean filled data
    Then I want fill "title" field with "Test# - vehicle geofence alerts - enter"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "teamId" field with 2
    And I want fill "eventId" field with "event(VEHICLE_GEOFENCE_ENTER, user)"
    And I want fill "scope.subtype" field with "vehicle"
    And I want fill "scope.value.0" field with 47
    And I want fill "additionalScope.subtype" field with "area"
    And I want fill "additionalScope.value.0" field with 3
    And I want fill "recipients.0.type" field with 'users_list'
    And I want fill "recipients.0.value." field with 'user(linxio-dev@ocsico.com)'
    And I want fill "transports.0" field with 'inApp'
    And I want fill "transports.1" field with 'sms'
    And I want fill "transports.2" field with 'email'
    And I want fill "eventTrackingTimeFrom" field with '00:00'
    And I want fill "eventTrackingTimeUntil" field with '23:59'
    And I want fill "eventTrackingDays.0" field with "monday"
    And I want fill "eventTrackingDays.1" field with "tuesday"
    And I want fill "eventTrackingDays.2" field with "wednesday"
    And I want fill "eventTrackingDays.3" field with "thursday"
    And I want fill "eventTrackingDays.4" field with "friday"
    And I want fill "eventTrackingDays.5" field with "saturday"
    And I want fill "eventTrackingDays.6" field with "sunday"
    Then I want create notification
    Then response code is 200
    Then I want get last notification
    And I see field "listenerTeamId" filled with 2
    And I see field "ownerTeamId" filled with 1
    Given I signed in as "admin" team "client" and teamId 2
    When I want get client by name "client-name-0" and save id
    And Elastica populate
    And I want to fill teamId by team of current user
#    And I want fill teamId by saved clientId
    And I want fill "name" field with "Test group"
    And I want to create vehicle group and save id
    And I want fill vehicle group id
    And I want fill "model" field with "Mercedes"
    And I want fill "regNo" field with "B1234567890"
    And I want fill "type" field with "Car"
    And I want fill "vin" field with "1G6AR5S38E0134828"
    And I want fill "defaultLabel" field with "Jenny"
    And I want fill "fuelType" field with 1
    And I want fill "year" field with "2000"
    And I want fill "makeModel" field with "testModel"
    And I want fill "make" field with "testMake"
    And I want to create vehicle and save id
    And I want fill "name" field with "client driver name"
    And I want fill "surname" field with "client driver surname"
    And I want fill "phone" field with "+46 (87)4646753"
    And I want fill "email" field with "driver@example.com"
    Then I want to register driver for current client
#    And I see field "email" filled with "driver@example.com"
    Then I want to set vehicle driver by user email "driver@example.com" and date "2020-10-20 08:19:38"
    Given I signed in as "super_admin" team "admin"
    And I want install device for vehicle
    And I want clean filled data
    Given  I signed in as "admin" team "client" and teamId 2
    And I want to fill teamId by team of current user
    And I want fill "name" field with "My another area"
    And I want fill area test coordinates "54.089241" "27.017186"
    And I want fill area test coordinates "54.154393" "28.086158"
    And I want fill area test coordinates "53.719466" "28.113527"
    And I want fill area test coordinates "53.706462" "26.964676"
    And I want fill area test coordinates "54.089241" "27.017186"
    And I want to create area and save id
    And I want clean filled data
    Given Driver "nikki.burns@acme.local" starts driving in the current vehicle at "2019-12-18"
    Given Current vehicle has been to "My another area" from "2019-12-20 00:00:00" to "2019-12-20 05:30:00"
    Given There are following tracker payload from teltonika tracker with socket "that socket":
      | payload                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        |
      | 000F383838383838383838383838383838                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             |
      | 00000000000003e6080b0000016f209b6ed800106678872021eb0600e600ce0c0000000f04ef00f000c8007100064230f81800004300004400000d06b30f005304f100006465c7000000001000693b590c00113ae801ee00000000000000000000016f20d25d5800106678872021eb0600ec00ce0e0000000f04ef00f000c8007100064230f81800004300004400000d06b30f005304f100006465c7000000001000693b590c00113ae801ee00000000000000000000016f21094bd800106678872021eb0600e700ce0e0000000f04ef00f000c8007100064230f81800004300004400000d06b30f005304f100006465c7000000001000693b590c00113ae801ee00000000000000000000016f21403a5800106678872021eb0600e100ce090000000f04ef00f000c8007100064230d91800004300004400000d06b30f005304f100006465c7000000001000693b590c00113ae801ee00000000000000000000016f217728d800106678872021eb0600d800ce0e0000000f04ef00f000c8007100064230cc1800004300004400000d06b30f005304f100006465c7000000001000693b590c00113ae801ee00000000000000000000016f21ae175800106678872021eb0600dd00ce0d0000000f04ef00f000c8007100064230cc1800004300004400000d06b30f005304f100006465c7000000001000693b590c00113ae801ee00000000000000000000016f21d81d280010667abd2021ec0000e500110d0000f00f04ef00f001c80071000642301c1800004300004400000d06b30f005304f100006465c7000000001000693b590c00113ae801ee00000000000000000000016f21d886a00010666e2d2021ea7000ea011a070003f01507ef01f001c800710021002500302f094239181800034300004400000d06b30f00002a00112b0000311f3504f100000000c7000000001000693b590c00113ae801ee00000000000000000000016f21d8fbd000106678232021ed6f00e7013e0e0000001507ef01f001c800710021ff2500302f094239291800004300004400000d06b30f00002a002f2b0000311f3504f100006465c7000000001000693b590c00113af001ee00000000000000000000016f21d9710000106678232021ed6f00e7013e0d0000001507ef01f001c800710021fc2500302f094238b61800004300004400000d06b30f00002a004d2b0000311f3504f100006465c7000000001000693b590c00113af801ee00000000000000000000016f21d9848800106678232021ed6f00e7013e0d0000f01507ef01f000c800710021fb2500302f094237fb1800004300004400000d06b30f00002a00542b0000311f3504f100006465c7000000001000693b590c00113af901ee00000000000000000b00001098                                                                                                                                                                                                                                                                                                                                                                                                                           |
      | 00000000000004b3080c0000016f21d9c30800106678232021ed6f00e7013e0e0000f01507ef01f001c800710021fb2500302f094237d71800004300004400000d06b30f00002a00632b0000311f3504f100006465c7000000001000693b590c00113afe01ee00000000000000000000016f21da383800106678232021ed6f00e7013e0d0000001507ef01f001c800710021fb2500302f094237e51800004300004400000d06b30f00002a00812b0000311f3504f100006465c7000000001000693b590c00113b0601ee00000000000000000000016f21daad6800106678232021ed6f00e7013e0c0000001507ef01f001c800710021fa2500302f0942376b1800004300004400000d06b30f00002a009f2b0000311f3504f100006465c7000000001000693b590c00113b0e01ee00000000000000000000016f21dab53800106678232021ed6f00e7013e0c0000f01507ef01f000c800710021fa2500302f0942372d1800004300004400000d06b30f00002a009f2b0000311f3504f100006465c7000000001000693b590c00113b0f01ee00000000000000000000016f21dad09000106678232021ed6f00e7013e0e0000f01507ef01f001c800710021f92505302f094237691800004300004400000d06b30f00002a00a72b0000311f3504f100006465c7000000001000693b590c00113b1101ee00000000000000000000016f21db1ac800106678872021ef8400e600c30e0008001507ef01f001c800710021fa2500302f094237831800084300004400000d06b30f00002a00b92b0000311f3504f100006465c7000000001000693b590c00113b1601ee00000000000000000000016f21db323800106676932021e7c500e600b60e0010001507ef01f001c800710021fc2511302f094237961800104300004400000d06b30f00002a00c12b0000311f3504f100006465c7000000031000693b5c0c00113b1601ee00000000000000000000016f21db45c000106675aa2021deea00e300a30e0009001507ef01f001c800710021002519302f094237561800094300004400000d06b30f00002a00c52b0000311f3504f100006465c70000001d1000693b760c00113b1701ee00000000000000000000016f21db68e8001066779e2021db8800df006a0e000b001507ef01f001c800710021fd250b302f0942378418000b4300004400000d06b30f00002a00d02b0000311f3504f100006465c7000000201000693b790c00113b1901ee00000000000000000000016f21db8c100010668d1a2021d66300df00820e000d001507ef01f001c800710021f52514302f0942379f18000d4300004400000d06b30f00002a00d72b0000311f3504f100006465c70000004a1000693ba30c00113b1a01ee00000000000000000000016f21db8ff80010668db02021d40b00de00b00e0012001507ef01f001c800710021f52514302f094237991800124300004400000d06b30f00002a00d72b0000311f3504f100006465c70000004f1000693ba80c00113b1b01ee00000000000000000000016f21dbab5000106688272021bfed00dd00bc0e0014001507ef01f001c800710021fb251f302f094237751800144300004400000d06b30f00002a00df2b0000311f3604f100006465c7000000891000693be20c00113b1f01ee00000000000000000c00007cfb |
      | 00000000000004b3080c0000016f21dde1b8001066230c2021b6f100db01100f0000001507ef01f001c800710021ff2500302f094237541800004300004400000d06b30f00002a01712b0000311f3604f100006465c7000001561000693caf0c00113b4b01ee00000000000000000000016f21de56e8001066230c2021b6f100db01100f0000001507ef01f001c800710021002500302f094237611800004300004400000d06b30f00002a018f2b0000311f3604f100006465c7000001561000693caf0c00113b5301ee00000000000000000000016f21decc18001066230c2021b6f100db01100f0000f01507ef01f000c8007100210025003030094237501800004300004400000d06b30f00002a01ad2b0000311f3604f100006465c7000001561000693caf0c00113b5c01ee00000000000000000000016f21dfc618001066230c2021b6f100db01100d0000001507ef01f000c80071002106252c3030094237441800004300004400000d06b30f00002a01ec2b0000311f3604f100006465c7000001561000693caf0c00113b6d01ee00000000000000000000016f21dfca00001066230c2021b6f100db01100e0000f01507ef01f001c8007100210425473030094237541800004300004400000d06b30f00002a01f02b0000311f3604f100006465c7000001561000693caf0c00113b6e01ee00000000000000000000016f21dfd1d00010660b482021771300df00a50e003d001507ef01f001c80071002104254730300942375918003d4300004400000d06b30f00002a01f02b0000311f3604f100006465c7000001561000693caf0c00113b6e01ee00000000000000000000016f21dfdd8800106611142021685c00e000a60e0022001507ef01f001c80071002100254030300942372918002e4300004400000d06b30f00002a01f42b0000311f3604f100006465c7000001561000693caf0c00113b6e01ee00000000000000000000016f21dff4f80010661ad82021573d00df00a00f0036001507ef01f001c80071002100252930300942372d1800264300004400000d06b30f00002a01fb2b0000311f3604f100006465c70000025c1000693db50c00113b7001ee00000000000000000000016f21e008800010662ec42021375600e0009e0f0049001507ef01f001c800710021012545302f0942376a1800494300004400000d06b30f00002a01ff2b0000311f3604f100006465c7000002b71000693e100c00113b7f01ee00000000000000000000016f21e014380010663ae0202122d400df00a00d004a001507ef01f001c800710021fd2549302f0942374c18004a4300004400000d06b30f00002a02032b0000311f3604f100006465c7000002f41000693e4d0c00113b8901ee00000000000000000000016f21e01ff0001066453a20210ed800e0009f0d0044001507ef01f001c800710021fd2549302f0942375b1800444300004400000d06b30f00002a02032b0000311f3604f100006465c7000003321000693e8b0c00113b9401ee00000000000000000000016f21e027c00010664cc7202103f800e100a00d0039001507ef01f001c800710021ec2548302f094237461800394300004400000d06b30f00002a02062b0000311f3604f100006465c7000003561000693eaf0c00113b9a01ee00000000000000000c00006d89 |
      | 000000000000044f080b0000016f21e158700010647357201fe8f600ea00ee0e0056001507ef01f001c800710021fe2559302f0942388b1800564300004400000d06b30f00282a02552b0000311f3804f100006465c7000008b5100069440e0c00113c8801ee00000000000000000000016f21e16bf80010643ae5201fd45300ef00ef0d004f001507ef01f001c800710021002552302f094238a218004f4300004400000d06b30f00282a02592b0000311f3804f100006465c70000092710006944800c00113c9d01ee00000000000000000000016f21e177b00010641afe201fc80500f200ee0e0048001507ef01f001c800710021012551302f094238551800484300004400000d06b30f00282a025c2b0000311f3804f100006465c70000096910006944c20c00113ca901ee00000000000000000000016f21e17f80001064083e201fc0fd00f400ee0f003e001507ef01f001c800710021012551302f0942383518003e4300004400000d06b30f00282a025c2b0000311f3804f100006465c70000099110006944ea0c00113caf01ee00000000000000000000016f21e18750001063f9a9201fbc1b00f300ed0f002c001507ef01f001c800710021002542302f094237ee18002c4300004400000d06b30f00282a02602b0000311f3804f100006465c7000009b2100069450b0c00113cb401ee00000000000000000000016f21e18b38001063f5b0201fba5900f300ec0f0022001507ef01f001c800710021002542302f094236b01800224300004400000d06b30f00282a02602b0000311f3804f100006465c7000009bf10006945180c00113cb601ee00000000000000000000016f21e18f20001063f39b201fb99100f300e70e0012001507ef01f001c800710021002542302f0942378c1800124300004400000d06b30f00282a02602b0000311f3804f100006465c7000009c710006945200c00113cb601ee00000000000000000000016f21e19308001063f442201fb99100f200e70e0005001507ef01f001c80071002100251a302f094237ea1800054300004400000d06b30f00282a02642b0000311f3804f100006465c7000009cb10006945240c00113cb601ee00000000000000000000016f21e20838001063f678201fb9f500f300e70e0000001507ef01f001c800710021022500302f094237f61800004300004400000d06b30f00282a02822b0000311f3804f100006465c7000009cc10006945250c00113cbe01ee00000000000000000000016f21e27d68001063f678201fb9f500f300e70e0000001507ef01f001c800710021002500302f094237f61800004300004400000d06b30f00282a02a02b0000311f3804f100006465c7000009cc10006945250c00113cc701ee00000000000000000000016f21e298c0001063f678201fb9f500f300e70d0000f01507ef01f000c800710021fe2500302f094237df1800004300004400000d06b30f00282a02a72b0000311f3804f100006465c7000009cc10006945250c00113cc901ee00000000000000000b0000bc94                                                                                                                                                                                                         |
      | 000000000000005a08010000016f21e9110001105c78d4201e62cb00fa01550e0000fa1005ef00f000c8007100fa00064232ab1800004300004400000d06b30f003604f100006465c70000186510006953be0c00113edd01ee0000000000000000010000b704                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   |
      | 0000000000000422080b0000016f21e7b94000105c6f31201e4b4a00fd01020e0018001507ef01f001c80071002100251b302d0942387a1800184300004400000d06b30f00362a03f82b0000311f3c04f100006465c700001814100069536d0c00113ed501ee00000000000000000000016f21e7bd2800105c6bf0201e4c2300fd01170e0013001507ef01f001c80071002100251b302d0942387a1800134300004400000d06b30f00362a03f82b0000311f3c04f100006465c700001814100069536d0c00113ed501ee00000000000000000000016f21e7c8e000105c6c43201e525200fa001a0e0013001507ef01f001c80071002100251b302d094238dd1800134300004400000d06b30f00362a03f82b0000311f3c04f100006465c700001826100069537f0c00113ed601ee00000000000000000000016f21e7ccc800105c7167201e558300fa002b0e001f001507ef01f001c800710021f9251b302d0942392918001f4300004400000d06b30f00362a03fc2b0000311f3c04f100006465c70000182f10006953880c00113ed601ee00000000000000000000016f21e7d49800105c7786201e5ada00fa00190e001c001507ef01f001c800710021f9251b302d0942391018001c4300004400000d06b30f00362a03fc2b0000311f3c04f100006465c700001845100069539e0c00113ed801ee00000000000000000000016f21e7d88000105c799c201e5d9500fa000e0e001c001507ef01f001c800710021fd251d302d0942389618001c4300004400000d06b30f00362a04002b0000311f3c04f100006465c70000184e10006953a70c00113ed801ee00000000000000000000016f21e7e05000105c79de201e619f00fa01620e000f001507ef01f001c800710021fd251d302d0942381218000f4300004400000d06b30f00362a04002b0000311f3c04f100006465c70000185d10006953b60c00113ed901ee00000000000000000000016f21e7e43800105c7938201e628800fa01550e0007001507ef01f001c800710021fd251d302d0942384b1800074300004400000d06b30f00362a04002b0000311f3c04f100006465c70000186210006953bb0c00113ed901ee00000000000000000000016f21e82e7000105c78d4201e62cb00fa01550d0000ef0f04ef00f001c8007100064233841800004300004400000d06b30f003604f100006465c70000186510006953be0c00113edd01ee00000000000000000000016f21e8a3a000105c78d4201e62cb00fa01550e0000000f04ef00f001c8007100064233021800004300004400000d06b30f003604f100006465c70000186510006953be0c00113edd01ee00000000000000000000016f21e8edd800105c78d4201e62cb00fa01550d0000f00f04ef00f000c8007100064232d41800004300004400000d06b30f003604f100006465c70000186510006953be0c00113edd01ee00000000000000000b00005d76                                                                                                                                                                                                                                                                                                   |
      | 00000000000003a0080a0000016f21fe0c1800105c7424201e60b6010200000c0000f01507ef01f001c800710021fb2500302f094237c31800004300004400000d06b30f00002a00112b0000311f3c04f100000000c70000000010006953be0c00113edc01ee00000000000000000000016f21fe4e8000105c6e69201e5ee300f40144110009001507ef01f001c800710021ff2507302f094238821800054300004400000d06b30f00002a00242b0000311f3c04f100006465c70000000010006953c00c00113ede01ee00000000000000000000016f21fe565000105c6d90201e5fcc00f3015611000a001507ef01f001c800710021ff2507302f0942388618000a4300004400000d06b30f00002a00242b0000311f3c04f100006465c70000000610006953c60c00113ede01ee00000000000000000000016f21fe759000105c6a2e201e648d00f1013c0d0000001507ef01f001c800710021fd2506302f0942379e1800004300004400000d06b30f00002a002b2b0000311f3c04f100006465c70000001410006953d40c00113ede01ee00000000000000000000016f21fee2f000105c67a4201e6ade00f10000080000ef1507ef00f001c800710021002503302f0942333d1800004300004400000d06b30f00002a00452b0000311f3c04f100006465c70000001410006953d40c00113ee601ee00000000000000000000016f21ff582000105c6793201e6f8e00ef006e0e0000000f04ef00f001c8007100064231a01800004300004400000d06b30f000004f100006465c70000001410006953d40c00113ee601ee00000000000000000000016f2200040000105c6793201e6f8e00ef006e0e0000f00f04ef00f000c8007100064231761800004300004400000d06b30f000004f100006465c70000000010006953d40c00113ee601ee00000000000000000000016f2200cb3800105c6e79201e6f1900ef0030090000f00f04ef00f001c8007100064230b41800004300004400000d06b30f000004f100006465c70000000010006953d40c00113ee601ee00000000000000000000016f2201406800105c651a201e6fe100ef00fc0d0000000f04ef00f001c80071000642314a1800004300004400000d06b30f000004f100006465c70000000010006953d40c00113ee601ee00000000000000000000016f2201b59800105c651a201e6fe100ef00fc090000000f04ef00f001c80071000642314c1800004300004400000d06b30f000004f100006465c70000000010006953d40c00113ee601ee00000000000000000a000052a9                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                       |
      | 00000000000003f5080b0000016f21fe0c1800105c7424201e60b6010200000c0000f01507ef01f001c800710021fb2500302f094237c31800004300004400000d06b30f00002a00112b0000311f3c04f100000000c70000000010006953be0c00113edc01ee00000000000000000000016f21fe4e8000105c6e69201e5ee300f40144110009001507ef01f001c800710021ff2507302f094238821800054300004400000d06b30f00002a00242b0000311f3c04f100006465c70000000010006953c00c00113ede01ee00000000000000000000016f21fe565000105c6d90201e5fcc00f3015611000a001507ef01f001c800710021ff2507302f0942388618000a4300004400000d06b30f00002a00242b0000311f3c04f100006465c70000000610006953c60c00113ede01ee00000000000000000000016f21fe759000105c6a2e201e648d00f1013c0d0000001507ef01f001c800710021fd2506302f0942379e1800004300004400000d06b30f00002a002b2b0000311f3c04f100006465c70000001410006953d40c00113ede01ee00000000000000000000016f21fee2f000105c67a4201e6ade00f10000080000ef1507ef00f001c800710021002503302f0942333d1800004300004400000d06b30f00002a00452b0000311f3c04f100006465c70000001410006953d40c00113ee601ee00000000000000000000016f21ff582000105c6793201e6f8e00ef006e0e0000000f04ef00f001c8007100064231a01800004300004400000d06b30f000004f100006465c70000001410006953d40c00113ee601ee00000000000000000000016f2200040000105c6793201e6f8e00ef006e0e0000f00f04ef00f000c8007100064231761800004300004400000d06b30f000004f100006465c70000000010006953d40c00113ee601ee00000000000000000000016f2200cb3800105c6e79201e6f1900ef0030090000f00f04ef00f001c8007100064230b41800004300004400000d06b30f000004f100006465c70000000010006953d40c00113ee601ee00000000000000000000016f2201406800105c651a201e6fe100ef00fc0d0000000f04ef00f001c80071000642314a1800004300004400000d06b30f000004f100006465c70000000010006953d40c00113ee601ee00000000000000000000016f2201b59800105c651a201e6fe100ef00fc090000000f04ef00f001c80071000642314c1800004300004400000d06b30f000004f100006465c70000000010006953d40c00113ee601ee00000000000000000000016f2201c92000105c5ea8201e6f1900ef00ec0a0000f00f04ef00f000c80071000642314c1800004300004400000d06b30f000004f100006465c70000000010006953d40c00113ee601ee00000000000000000b0000e849                                                                                                                                                                                                                                                                                                                                                                                             |
      | 000000000000005a08010000016f220cbdd001105c5ea8201e6f1900de00ec080000f61005ef00f000c8007100f601064230f81800004300004400000d06b30f000004f100006465c70000000010006953d40c00113ee601ee0000000000000000010000226e                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   |
      | 000000000000040e080c0000016f220cd15800105c6fa6201e6dfe00dc006e0a0000f00f04ef00f001c8007100064230cc1800004300004400000d06b30f000004f100006465c70000000010006953d40c00113ee601ee00000000000000000000016f220d468800105c6829201e6e0e00db006e0a0000000f04ef00f001c8007100064230f61800004300004400000d06b30f000004f100006465c70000000010006953d40c00113ee601ee00000000000000000000016f220dbbb800105c5f70201e6d9a00dd00d5090000000f04ef00f001c8007100064230f81800004300004400000d06b30f000004f100006465c70000000010006953d40c00113ee601ee00000000000000000000016f220dd32800105c5a19201e704500e00102080000f00f04ef00f000c8007100064230f51800004300004400000d06b30f000004f100006465c70000000010006953d40c00113ee601ee00000000000000000000016f22109a1800000000000000000000000000000000ef1507ef00f000c800710021ff2500302d094232c71800004300004400000d06b30f00002a000e2b0000311f3c04f100000000c70000000010006953d40c00113ee501ee00000000000000000000016f2210a1e800000000000000000000000000000000f00f04ef00f001c80071000642326c1800004300004400000d06b30f000004f100000000c70000000010006953d40c00113ee501ee00000000000000000000016f2210b57000105c8adc201e2c2c00d50000060000000f04ef00f001c8007100064231a51800004300004400000d06b30f000004f100006465c70000000010006953d40c00113ee501ee00000000000000000000016f2210ec2000105c48e9201e68a700c70094080000000f04ef00f001c8007100064230d21800004300004400000d06b30f000004f100006465c70000000010006953d40c00113ee501ee00000000000000000000016f2211615000105c5c50201e6aff010d01040b0000000f04ef00f001c80071000642309c1800004300004400000d06b30f000004f100006465c70000000010006953d40c00113ee501ee00000000000000000000016f2211d68000105c5d6b201e6faf00ff00440a0000000f04ef00f001c8007100064230961800004300004400000d06b30f000004f100006465c70000000010006953d40c00113ee501ee00000000000000000000016f22124bb000105c5d5a201e76210111011b0a0000000f04ef00f001c8007100064230731800004300004400000d06b30f000004f100006465c70000000010006953d40c00113ee501ee00000000000000000000016f22125f3800105c5b34201e75de0112011b090000f00f04ef00f000c8007100064230751800004300004400000d06b30f000004f100006465c70000000010006953d40c00113ee501ee00000000000000000c0000a3e7                                                                                                                                                                                                                                                                                                                                           |
      | 000000000000034b08090000016f2214dbf000105c6bce201e636100fb0096070007f01507ef01f001c800710021002508302d094237bb1800074300004400000d06b30f00002a00162b0000311f3c04f100000000c70000000010006953d40c00113ee701ee00000000000000000000016f22151a7000105c7498201e5e3c00f800420b0007001507ef01f001c800710021012505302d0942385f1800074300004400000d06b30f00002a00252b0000311f3c04f100006465c70000000e10006953e20c00113ee701ee00000000000000000000016f2215224000105c75c4201e5f0400f900120b0006001507ef01f001c800710021022508302d094238631800064300004400000d06b30f00002a00282b0000311f3c04f100006465c70000001110006953e50c00113ee701ee00000000000000000000016f2215262800105c75d5201e5fcc00fa01640b0008001507ef01f001c800710021022508302d094238681800084300004400000d06b30f00002a00282b0000311f3c04f100006465c70000001510006953e90c00113ee701ee00000000000000000000016f22152a1000105c7581201e609400fb01590b0008001507ef01f001c800710021022508302d0942387e1800084300004400000d06b30f00002a00282b0000311f3c04f100006465c70000001510006953e90c00113ee701ee00000000000000000000016f22159f4000105c7293201e62aa010000d80a0000000f04ef01f001c80071000642340e1800004300004400000d06b30f000004f100006465c70000002510006953f90c00113eec01ee00000000000000000000016f2215be8000105c7293201e62aa010000d80e0000ef0f04ef00f001c80071000642332d1800004300004400000d06b30f000004f100006465c70000002510006953f90c00113eee01ee00000000000000000000016f221633b000105c7293201e62aa010000d8110000000f04ef00f001c80071000642327c1800004300004400000d06b30f000004f100006465c70000002510006953f90c00113eee01ee00000000000000000000016f2216b49800105c7293201e62aa010000d8110000f00f04ef00f000c8007100064231f21800004300004400000d06b30f000004f100006465c70000000010006953f90c00113eee01ee0000000000000000090000f61b |
#    And  I want send messages in queue notification_events
    And Calculate routes
    And I want send messages in queue notification_post_handle
    And I want send messages in queue notification_events
    And Calculate idlings
#    Given insert Procedures
#    Then I want sleep on 1 seconds
#    Given Elastica populate index "eventLog"
#    Then I signed with email "linxio-dev@ocsico.com"
#    And I want fill "eventId" field with "event(VEHICLE_GEOFENCE_ENTER, user)"
#    Then I want get event log
#    And I see field "data/0/eventName" filled with "VEHICLE_GEOFENCE_ENTER"
#    And I see field "data/0/formattedDate"
#    And I see field "data/0/importance" filled with "normal"
#    And I see field "data/0/eventTeam" filled with "client-name-0"
#    And I see field "data/0/triggeredBy" filled with "geo"
#    And I see field "data/0/triggeredDetails" filled with "driver@example.com"
##    And I see field "data/0/eventSourceType" filled with "vehicle"
#    And I see field "data/0/eventSource/name" filled with "B1234567890"
#    And I see field "data/0/eventSource/type" filled with "vehicle"
#    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
#    And I see field "data/0/eventSource/areahistoryId"
#    And I see field "data/0/eventSource/vehicleId"
#    And I see field "data/0/shortDetails/areas/0/area/name" filled with "My another area"
#    And I see field "data/0/shortDetails/lastCoordinates"
#    And I see field "data/0/shortDetails/address"
#    And I see field "data/0/shortDetails/vehicleRegNo" filled with "B1234567890"
#    And I see field "data/0/shortDetails/deviceImei" filled with "string(888888888888888)"
#    And I see field "data/0/notificationsList/0/id"
#    And I see field "data/0/notificationsList/0/title" filled with "Test# - vehicle geofence alerts - enter"
#    And I see field "data/0/notificationsList/0/eventId"
#    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
#    And I want clean filled data
#    And I want fill "status" field with "pending"
#    And I want fill "transport" field with "web_app"
#    Then I want get generated notification messages
#    And response code is 200
#    And I see field "data/0/transportType" filled with "web_app"
#    And I see field "data/0/recipient" filled with "string(1)"
#    And I see field "data/0/subject" filled with "Alerts - Vehicle area alerts"
#    And I see field "data/0/message" filled with "Vehicle B1234567890 entered to area: My another area. "
#    And I see field "data/0/status" filled with "pending"
#    And I see field "data/0/event/eventSourceType" filled with "vehicle"
#    And I see field "data/0/event/entityTeam/type" filled with "client"
#    And I see field "data/0/event/entityTeam/clientId" filled with 1
#    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"
#    And I see field "data/0/event/eventId"
#    And I see field "data/0/event/eventLogId"
#    And I want clean filled data
#    And I want fill "status" field with "pending"
#    And I want fill "transport" field with "sms"
#    Then I want get generated notification messages
#    And response code is 200
#    And I see field "data/0/transportType" filled with "sms"
#    And I see field "data/0/recipient" filled with "custom(+0452096181)"
#    And I see field "data/0/subject" filled with "[Linxio] Notification Message"
#    And I see field "data/0/message" filled with "Vehicle B1234567890 entered to area: My another area. "
#    And I see field "data/0/status" filled with "pending"
#    And I see field "data/0/event/eventSourceType" filled with "vehicle"
#    And I see field "data/0/event/entityTeam/type" filled with "client"
#    And I see field "data/0/event/entityTeam/clientId" filled with 1
#    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"
#    And I see field "data/0/event/eventId"
#    And I see field "data/0/event/eventLogId"
#    And I want clean filled data
#    And I want fill "status" field with "pending"
#    And I want fill "transport" field with "email"
#    Then I want get generated notification messages
#    And response code is 200
#    And I see field "data/0/transportType" filled with "email"
#    And I see field "data/0/recipient" filled with "linxio-dev@ocsico.com"
#    And I see field "data/0/subject" filled with "Alerts - Vehicle area alerts"
#    Then I should get an email with field "data/0/message" containing:
#    """
#    <h3>New notification from the Linxio system:</h3>
#    <p>Vehicle B1234567890 entered to area: My another area.</p><br/>
#    <p><b>Detailed information:</b></p>
#    <p>2020-01-01 01:00 - Vehicle B1234567890 (testMake testModel) with driver client driver name client driver surname entered the area \"My another area\"</p>
#    <p>Vehicle page: https://url/client/fleet/47/specification</p>
#    <p>Driver page: https://url/client/drivers/45/profile-info</p><br/>
#    <p></p>
#    """
#    And I see field "data/0/status" filled with "pending"
#    And I see field "data/0/event/eventSourceType" filled with "vehicle"
#    And I see field "data/0/event/entityTeam/type" filled with "client"
#    And I see field "data/0/event/entityTeam/clientId" filled with 1
#    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"
#    And I see field "data/0/event/eventId"
#    And I see field "data/0/event/eventLogId"

  Scenario: I want check notification by overspeed visited geofences
    And I want to fill teamId by team of current user
    And I want fill device model for vehicle with name "FM3001"
    And I want fill "sn" field with "test sn"
    And I want fill "status" field with "inStock"
    And I want fill "port" field with 25
    And I want fill "hw" field with "test hw"
    And I want fill "sw" field with "test sw"
    And I want fill "imei" field with "888888888888888"
    And I want fill "phone" field with "+(375) 245535454"
    And I want fill "imsi" field with "test imsi"
    And I want fill "devEui" field with "test devEui"
    And I want fill "userName" field with "test username"
    And I want fill "password" field with "test password"
    And I want fill "clientNote" field with "test clientNote"
    And I want fill "adminNote" field with "test adminNote"
    And I want to create device for vehicle and save id
    And I want clean filled data
    Then I want fill "title" field with "Test# - vehicle overspide inside geofence"
    And I want fill "status" field with "enabled"
    And I want fill "importance" field with "immediately"
    And I want fill "teamId" field with 2
    And I want fill "eventId" field with "event(VEHICLE_OVERSPEEDING_INSIDE_GEOFENCE, user)"
    And I want fill "scope.subtype" field with "any"
    And I want fill "additionalScope.subtype" field with "area"
    And I want fill "additionalScope.value.0" field with 3
    And I want fill "recipients.0.type" field with 'users_list'
    And I want fill "recipients.0.value." field with 'user(linxio-dev@ocsico.com)'
    And I want fill "transports.0" field with 'inApp'
    And I want fill "transports.1" field with 'sms'
    And I want fill "transports.2" field with 'email'
    And I want fill "eventTrackingTimeFrom" field with '00:00'
    And I want fill "eventTrackingTimeUntil" field with '23:59'
    And I want fill "eventTrackingDays.0" field with "monday"
    And I want fill "eventTrackingDays.1" field with "tuesday"
    And I want fill "eventTrackingDays.2" field with "wednesday"
    And I want fill "eventTrackingDays.3" field with "thursday"
    And I want fill "eventTrackingDays.4" field with "friday"
    And I want fill "eventTrackingDays.5" field with "saturday"
    And I want fill "eventTrackingDays.6" field with "sunday"
    And I want fill "additionalParams.overSpeed" field with 5
    Then I want create notification
    Then response code is 200
    Then I want get last notification
    And I see field "listenerTeamId" filled with 2
    And I see field "ownerTeamId" filled with 1
    Given  I signed in as "admin" team "client" and teamId 2
    When I want get client by name "client-name-0" and save id
    And I want to fill teamId by team of current user
    And I want fill "model" field with "Mercedes"
    And I want fill "regNo" field with "B1234567890"
    And I want fill "type" field with "Car"
    And I want fill "vin" field with "1G6AR5S38E0134828"
    And I want fill "defaultLabel" field with "Jenny"
    And I want fill "fuelType" field with 1
    And I want fill "year" field with "2000"
    And I want fill "makeModel" field with "testModel"
    And I want fill "make" field with "testMake"
    And I want to create vehicle and save id
    And I want fill "name" field with "client driver name"
    And I want fill "surname" field with "client driver surname"
    And I want fill "phone" field with "+46 (87)4646753"
    And I want fill "email" field with "driver@example.com"
    Then I want to register driver for current client
    And I see field "email" filled with "driver@example.com"
    Then I want to set vehicle driver by user email "driver@example.com" and date "2020-10-20 08:19:38"
    Given I signed in as "super_admin" team "admin"
    And I want install device for vehicle
    And I want clean filled data
    Given  I signed in as "admin" team "client" and teamId 2
    And Elastica populate
    And I want to fill teamId by team of current user
    And I want fill "name" field with "My another area"
    And I want fill area test coordinates "54.089241" "27.017186"
    And I want fill area test coordinates "54.154393" "28.086158"
    And I want fill area test coordinates "53.719466" "28.113527"
    And I want fill area test coordinates "53.706462" "26.964676"
    And I want fill area test coordinates "54.089241" "27.017186"
    And I want to create area and save id
    And I want clean filled data
    Given Driver "nikki.burns@acme.local" starts driving in the current vehicle at "2019-12-18"
    Given Current vehicle has been to "My another area" from "2019-12-20 00:00:00" to "2019-12-20 05:30:00"
    Given There are following tracker payload from teltonika tracker with socket "that socket":
      | payload                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        |
      | 000F383838383838383838383838383838                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             |
      | 000000000000034b08090000016f2214dbf000105c6bce201e636100fb0096070007f01507ef01f001c800710021002508302d094237bb1800074300004400000d06b30f00002a00162b0000311f3c04f100000000c70000000010006953d40c00113ee701ee00000000000000000000016f22151a7000105c7498201e5e3c00f800420b0007001507ef01f001c800710021012505302d0942385f1800074300004400000d06b30f00002a00252b0000311f3c04f100006465c70000000e10006953e20c00113ee701ee00000000000000000000016f2215224000105c75c4201e5f0400f900120b0006001507ef01f001c800710021022508302d094238631800064300004400000d06b30f00002a00282b0000311f3c04f100006465c70000001110006953e50c00113ee701ee00000000000000000000016f2215262800105c75d5201e5fcc00fa01640b0008001507ef01f001c800710021022508302d094238681800084300004400000d06b30f00002a00282b0000311f3c04f100006465c70000001510006953e90c00113ee701ee00000000000000000000016f22152a1000105c7581201e609400fb01590b0008001507ef01f001c800710021022508302d0942387e1800084300004400000d06b30f00002a00282b0000311f3c04f100006465c70000001510006953e90c00113ee701ee00000000000000000000016f22159f4000105c7293201e62aa010000d80a0000000f04ef01f001c80071000642340e1800004300004400000d06b30f000004f100006465c70000002510006953f90c00113eec01ee00000000000000000000016f2215be8000105c7293201e62aa010000d80e0000ef0f04ef00f001c80071000642332d1800004300004400000d06b30f000004f100006465c70000002510006953f90c00113eee01ee00000000000000000000016f221633b000105c7293201e62aa010000d8110000000f04ef00f001c80071000642327c1800004300004400000d06b30f000004f100006465c70000002510006953f90c00113eee01ee00000000000000000000016f2216b49800105c7293201e62aa010000d8110000f00f04ef00f000c8007100064231f21800004300004400000d06b30f000004f100006465c70000000010006953f90c00113eee01ee0000000000000000090000f61b |
    And  I want send messages in queue notification_events
    And Calculate routes
    And Calculate idlings
    Given insert Procedures
    Then I want sleep on 1 seconds
    Then I signed with email "linxio-dev@ocsico.com"
    Given Elastica populate index "eventLog"
    And I want fill "eventId" field with "event(VEHICLE_OVERSPEEDING_INSIDE_GEOFENCE, user)"
    Then I want get event log
    And I see field "data/0/eventName" filled with "VEHICLE_OVERSPEEDING_INSIDE_GEOFENCE"
    And I see field "data/0/importance" filled with "normal"
    And I see field "data/0/eventTeam" filled with "client-name-0"
    And I see field "data/0/triggeredBy" filled with "geo"
    And I see field "data/0/triggeredDetails" filled with "driver@example.com"
#    And I see field "data/0/eventSourceType" filled with "vehicle"
    And I see field "data/0/eventSource/name" filled with "B1234567890"
    And I see field "data/0/eventSource/type" filled with "vehicle"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I see field "data/0/eventSource/areahistoryId"
    And I see field "data/0/eventSource/vehicleId"
    And I see field "data/0/shortDetails/areas/0/area/name" filled with "My another area"
    And I see field "data/0/shortDetails/lastCoordinates"
    And I see field "data/0/shortDetails/address"
    And I see field "data/0/shortDetails/vehicleRegNo" filled with "B1234567890"
    And I see field "data/0/shortDetails/deviceImei" filled with "string(888888888888888)"
    And I see field "data/0/notificationsList/0/id"
    And I see field "data/0/notificationsList/0/title" filled with "Test# - vehicle overspide inside geofence"
    And I see field "data/0/notificationsList/0/eventId"
    And I see field "data/0/eventSource/entityTeam/type" filled with "client"
    And I want clean filled data
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "web_app"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "web_app"
    And I see field "data/0/recipient" filled with "string(1)"
    And I see field "data/0/subject" filled with "Alerts - Overspeeding inside area"
    And I see field "data/0/message" filled with "Vehicle B1234567890 is driving more than 7km/h to area: My another area. "
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "vehicle"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 1
    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"
    And I want clean filled data
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "sms"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "sms"
    And I see field "data/0/recipient" filled with "custom(+0452096181)"
    And I see field "data/0/subject" filled with "[Linxio] Notification Message"
    And I see field "data/0/message" filled with "Vehicle B1234567890 is driving more than 7km/h to area: My another area. "
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "vehicle"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 1
    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"
    And I want clean filled data
    And I want fill "status" field with "pending"
    And I want fill "transport" field with "email"
    Then I want get generated notification messages
    And response code is 200
    And I see field "data/0/transportType" filled with "email"
    And I see field "data/0/recipient" filled with "linxio-dev@ocsico.com"
    And I see field "data/0/subject" filled with "Alerts - Overspeeding inside area"
    Then I should get an email with field "data/0/message" containing:
    """
    <h3>New notification from the Linxio system:</h3>
    <p>Vehicle B1234567890 driving >7km/h in the area \"My another area\"</p><br/>
    <p><b>Detailed information:</b></p>
    <p>2020-01-01 01:00 - Vehicle B1234567890 (testMake testModel) with driver client driver name client driver surname driving more than 7km/h in the area \"My another area\", speed limit is set to 5km/h</p>
    <p>Vehicle page: https://url/client/fleet/47/specification</p>
    <p>Driver page: https://url/client/drivers/45/profile-info</p><br/>
    <p></p>
    """
    And I see field "data/0/status" filled with "pending"
    And I see field "data/0/event/eventSourceType" filled with "vehicle"
    And I see field "data/0/event/entityTeam/type" filled with "client"
    And I see field "data/0/event/entityTeam/clientId" filled with 1
    And I see field "data/0/event/entityTeam/clientName" filled with "client-name-0"