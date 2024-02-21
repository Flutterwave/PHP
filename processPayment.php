<?php

declare(strict_types=1);

# if vendor file is not present, notify developer to run composer install.
require __DIR__.'/vendor/autoload.php';

use Flutterwave\Controller\PaymentController;
use Flutterwave\EventHandlers\ModalEventHandler as PaymentHandler;
use Flutterwave\Flutterwave;
use Flutterwave\Library\Modal;
use \Flutterwave\Config\ForkConfig;

// start a session.
session_start();

// Define custom config.
// $myConfig = ForkConfig::setUp(
//     'FLWSECK_TEST-XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX-X', //Secret key
//     'FLWPUBK_TEST-XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX-X', // Public key
//     'FLWSECK_TESTXXXXXXXXXXX', //Encryption key
//     'staging' //Environment Variable
// );

// uncomment the block if you just want to pass the keys with a specific configuration.
// $_ENV['SECRET_KEY'] = "FLWSECK_TEST-XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX-X";
// $_ENV['PUBLIC_KEY'] = "FLWPUBK_TEST-XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX-X";
// $_ENV['ENCRYPTION_KEY'] = "FLWSECK_TESTXXXXXXXXXXXX";
// $_ENV['ENV'] = "staging";

// controller default
$controller = null;

try {
    Flutterwave::bootstrap(); // create a .env or Flutterwave::bootstrap($myConfig)
    $customHandler = new PaymentHandler();
    $client = new Flutterwave();
    $modalType = Modal::STANDARD; // Modal::POPUP or Modal::STANDARD
    $controller = new PaymentController( $client, $customHandler, $modalType );
} catch(\Exception $e ) {
    echo $e->getMessage();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $request = $_REQUEST;
    $request['redirect_url'] = $_SERVER['HTTP_ORIGIN'] . $_SERVER['REQUEST_URI'];
    try {
        $controller->process( $request );
    } catch(\Exception $e) {
        echo $e->getMessage();
    }
}

$request = $_GET;
# Confirming Payment.
if(isset($request['tx_ref'])) {
    $controller->callback( $request );
} else {
    
}
exit();
