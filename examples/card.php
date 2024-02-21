<?php
require __DIR__."/../vendor/autoload.php";
session_start();

use Flutterwave\Util\AuthMode;
use Flutterwave\Util\Currency;
use Flutterwave\Config\ForkConfig;
use Dotenv\Dotenv;
// custom config.

if(!file_exists( '.env' )) {
    $dotenv = Dotenv::createImmutable(__DIR__."/../"); 
} else {
    $dotenv = Dotenv::createImmutable(__DIR__."/");
}

$dotenv->safeLoad();

$config = ForkConfig::setUp(
    $_ENV['SECRET_KEY'],
    $_ENV['PUBLIC_KEY'],
    $_ENV['ENV'],
    $_ENV['ENCRYPTION_KEY']
);

\Flutterwave\Flutterwave::bootstrap($config);

try {

    $tx_ref = uniqid().time();

    $data = [
        "amount" => 2000,
        "currency" => Currency::NGN,
        "tx_ref" => $tx_ref,
        "redirectUrl" => "http://{$_SERVER['HTTP_HOST']}/examples/endpoint/verify.php?tx_ref={$tx_ref}",
        "additionalData" => [
            "subaccounts" => [
                ["id" => "RSA_345983858845935893"]
            ],
            "meta" => [
                "unique_id" => uniqid().uniqid()
            ],
            "preauthorize" => false,
            "payment_plan" => null,
            "card_details" => [
//                "card_number" => "5531886652142950",
//                "cvv" => "564",
//                "expiry_month" => "09",
//                "expiry_year" => "32",
                "card_number" => "4556052704172643",
                "cvv" => "899",
                "expiry_month" => "01",
                "expiry_year" => "23"
            ]
        ],
    ];

    $cardpayment = \Flutterwave\Flutterwave::create("card");

    $customerObj = $cardpayment->customer->create([
        "full_name" => "Olaobaju Abraham",
        "email" => "ol3746ydgsbc@gmail.com",
        "phone" => "+2349035462461"
    ]);

    $data['customer'] = $customerObj;
    $payload  = $cardpayment->payload->create($data);

    

    if(!empty($_REQUEST))
    {
        $request = $_REQUEST;

        if(isset($request['view'])){
            $json = json_encode($payload->toArray('card'));
            $request_display = $json;
        }

        if(isset($request['make'])){
            $result = $cardpayment->initiate($payload);
            dd($cardpayment);
            if($result['mode'] === AuthMode::PIN){
                $instruction = $result['instruction'];
                require __DIR__."/view/form/pin.php";
            }

            if($result['mode'] === AuthMode::AVS){
                $instruction = $result['instruction'];
                require __DIR__."/view/form/avs.php";
            }
        }

        if(isset($request['pin'])){
            $payload->set("authorization", [
                    "mode" => AuthMode::PIN,
                    AuthMode::PIN => $request['pin']
            ]);

            $result = $cardpayment->initiate($payload);

            switch ($result['mode']){
                case 'redirect':
                    header("Location:".$result['url']);
                    break;
                case 'otp':
                    require __DIR__."/view/form/otp.php";
                    break;
            }

        }

        if(isset($request['address'])){
            $avs_data = [
                "mode" => AuthMode::AVS,
                "city" => $request['city'],
                "address" => $request['address'],
                "state" => $request['state'],
                "country" => $request['country'],
                "zipcode" => $request['zipcode']
            ];

            $payload->set("authorization", $avs_data);
            $result = $cardpayment->initiate($payload);

            switch ($result['mode']){
                case 'redirect':
                    header("Location:".$result['url']);
                    break;
                case 'otp':
                    require __DIR__."/view/form/otp.php";
                    break;
            }
        }

    }


} catch (Exception $e) {
    $error = $e->getMessage();
}

?>
<link rel="stylesheet" href="./assets/css/index.css">
<?php if(!isset($_GET['make'])):?>
    <div class="buttons">
        <form method="get">
            <h3> Card Payment Sample</h3>
            <span class="error"><?= $error ?? ""  ?></span>
            <div class="request">
                <?= $request_display ?? "" ?>
            </div>
            <div class="cta">
                <button name="view" value="1">View Request</button>
                <button class="make-payment" name="make" value="1">Make Card Payment</button>
            </div>
        </form>
    </div>
    <script src="./assets/js/index.js"></script>
<?php endif ?>
