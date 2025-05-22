<?xml version='1.0' encoding='UTF-8'?>

<!DOCTYPE properties SYSTEM 'http://java.sun.com/dtd/properties.dtd'>

<properties>

    <entry key='config.default'>./conf/default.xml</entry>

    <!--

    This is the main configuration file. All your configuration parameters should be placed in this file.

    Default configuration parameters are located in the "default.xml" file. You should not modify it to avoid issues
    with upgrading to a new version. Parameters in the main config file override values in the default file. Do not
    remove "config.default" parameter from this file unless you know what you are doing.

    For list of available parameters see following page: https://www.traccar.org/configuration-file/
    https://github.com/traccar/traccar/blob/master/src/main/java/org/traccar/config/Keys.java

    -->

    <entry key='database.driver'>org.postgresql.Driver</entry>
    <entry key='database.url'>jdbc:postgresql://{{ default .Env.DB_HOST "database" }}:{{ default .Env.DB_PORT "5432" }}/{{ default .Env.DB_NAME "traccar" }}</entry>
    <entry key='database.user'>{{ default .Env.DB_USER "example" }}</entry>
    <entry key='database.password'>{{ default .Env.DB_PASS "example" }}</entry>

    <entry key='logger.level'>{{ default .Env.LOG_LEVEL "all" }}</entry>
    <entry key='logger.file'>/opt/traccar/logs/tracker-server.log</entry>

    <entry key='forward.enable'>true</entry>
    <entry key='forward.type'>json</entry>
    <entry key='forward.url'>http://{{ default .Env.API_HOST "api" }}:{{ default .Env.API_PORT "80" }}/api/traccar/hook/positions</entry>
    <entry key='event.forward.enable'>true</entry>
    <entry key='event.forward.url'>http://{{ default .Env.API_HOST "api" }}:{{ default .Env.API_PORT "80" }}/api/traccar/hook/events</entry>
    <entry key='filter.enable'>true</entry>
    <entry key='filter.duplicate'>true</entry>
    <!--<entry key='filter.invalid'>true</entry>-->
    <entry key='filter.future'>86400</entry>
    <entry key='filter.past'>31536000</entry>
    <!--<entry key='filter.zero'>true</entry>-->
    <entry key='gt06.timezone'>-28800</entry>
    <!--<entry key='event.enable'>false</entry>-->
    <entry key='web.persistSession'>true</entry>
    <entry key='processing.copyAttributes.enable'>true</entry>
    <entry key='processing.copyAttributes'>battery,batteryLevel,iccid</entry>

    <entry key='server.timeout'>300</entry>
    <entry key='gt06.timeout'>180</entry>
    <entry key='meitrack.timeout'>60</entry>
    <entry key='teltonika.timeout'>60</entry>
    <entry key='ulbotech.timeout'>60</entry>
    <entry key='gl200.timeout'>60</entry>
    <entry key='dmt.timeout'>60</entry>
    <entry key='dmthttp.timeout'>60</entry>
    <entry key='eelink.timeout'>60</entry>

</properties>
