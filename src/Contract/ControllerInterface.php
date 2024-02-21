<?php

declare(strict_types=1);

namespace Flutterwave\Contract;

use Flutterwave\EventHandlers\EventHandlerInterface;
use Flutterwave\Flutterwave;

interface ControllerInterface {

    public function __construct(
        Flutterwave $client,
        EventHandlerInterface $handler,
        string $modalType
    );
    
}