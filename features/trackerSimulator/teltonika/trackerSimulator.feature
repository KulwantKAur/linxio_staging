Feature: Tracker

  Scenario: I want send tcp data from simulator to API
    Given Create devices for simulator
    Then I want to send tcp data from simulator to api with socket "simulator-socket-id"
    Then I see field "response"
    And I see field "imei"
    Then I want check tracker auth by socket "simulator-socket-id"
    When I want fill "payload" field with "00000000000000FE080400000113fc208dff000f14f650209cca80006f00d60400040004030101150316030001460000015d0000000113fc17610b000f14ffe0209cc580006e00c00500010004030101150316010001460000015e0000000113fc284945000f150f00209cd200009501080400000004030101150016030001460000015d0000000113fc267c5b000f150a50209cccc0009300680400000004030101150016030001460000015b000400008612"
    Then I want to send teltonika tcp data to api with socket "simulator-socket-id"
    Then I see field "response" filled with "4"
    And I see field "imei"
    Then I want check tracker auth by socket "simulator-socket-id"
    When I want fill "payload" field with "00000000000000FE080400000113fc208dff00e8035307fdd922c7006f00d60400040004030101150316030001460000015d0000000113fc17610b000f14ffe0209cc580006e00c00500010004030101150316010001460000015e0000000113fc284945000f150f00209cd200009501080400000004030101150016030001460000015d0000000113fc267c5b000f150a50209cccc0009300680400000004030101150016030001460000015b000400008612"
    Then I want to send teltonika tcp data to api with socket "simulator-socket-id"
    Then I see field "response" filled with "4"
    And I see field "imei"

  Scenario: I want generate track for simulator
    Given Create devices for simulator
    Then I want to send tcp data from simulator to api with socket "simulator-socket-id"
    Then I see field "response"
    And I see field "imei"
    Then I want check tracker auth by socket "simulator-socket-id"
    When I want fill "payload" field with "00000000000000FE080400000113fc208dff000f14f650209cca80006f00d60400040004030101150316030001460000015d0000000113fc17610b000f14ffe0209cc580006e00c00500010004030101150316010001460000015e0000000113fc284945000f150f00209cd200009501080400000004030101150016030001460000015d0000000113fc267c5b000f150a50209cccc0009300680400000004030101150016030001460000015b000400008612"
    Then I want to send teltonika tcp data to api with socket "simulator-socket-id"
    Then I see field "response" filled with "4"
    And I see field "imei"
    Then I want check tracker auth by socket "simulator-socket-id"
    When I want fill "payload" field with "00000000000000FE080400000113fc208dff00e8035307fdd922c7006f00d60400040004030101150316030001460000015d0000000113fc17610b000f14ffe0209cc580006e00c00500010004030101150316010001460000015e0000000113fc284945000f150f00209cd200009501080400000004030101150016030001460000015d0000000113fc267c5b000f150a50209cccc0009300680400000004030101150016030001460000015b000400008612"
    Then I want to send teltonika tcp data to api with socket "simulator-socket-id"
    Then I see field "response" filled with "4"
    When I want fill "imei" field with "862259588834290"
    Then I want to generate track for simulator
    And response code is 302
    And I want to check if 13 tracks have been generated