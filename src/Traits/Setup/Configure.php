<?php

declare(strict_types=1);

namespace Flutterwave\Traits\Setup;

use Flutterwave\Contract\ConfigInterface;
use Flutterwave\Helper\Config;
use Flutterwave\Config\ForkConfig;

trait Configure
{
    public static function bootstrap(?ConfigInterface $config = null): void
    {
        if (\is_null($config) && \is_null(self::$config)) {
            include __DIR__ . '/../../../setup.php';

            if ('composer' === $flutterwave_installation) {
                $config = Config::setUp(
                    $keys[Config::SECRET_KEY],
                    $keys[Config::PUBLIC_KEY],
                    $keys[Config::ENCRYPTION_KEY],
                    $keys[Config::ENV]
                );
            }

            if ('manual' === $flutterwave_installation) {
                $config = ForkConfig::setUp(
                    $keys[ForkConfig::SECRET_KEY],
                    $keys[ForkConfig::PUBLIC_KEY],
                    $keys[ForkConfig::ENCRYPTION_KEY],
                    $keys[ForkConfig::ENV]
                );
            }
        }

        if (\is_null(self::$config)) {
            self::$config = $config;
        }
            
        self::$methods = include __DIR__ . '/../../Util/methods.php';
    }
}
