<?php

declare(strict_types=1);

namespace Flutterwave\Helper;

class EnvVariables
{
    public const VERSION = 'v3';
    public const SDK_VERSION = '1.0.7';
    public const BASE_URL = 'https://api.flutterwave.com/' . self::VERSION;
    public const TIME_OUT = 30;
}
