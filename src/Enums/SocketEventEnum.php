<?php

namespace App\Enums;

enum SocketEventEnum: string
{
    case PanicButton = 'panicButton';
    case Undefined = 'undefined';
    case Alarm = 'alarm';
    case Obd = 'obd';
    case Online = 'online';
    case Offline = 'offline';

    case SourceApi = 'api';
    case SourceTraccar = 'traccar';
    case SourceStreamax = 'streamax';
}
