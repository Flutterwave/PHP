<?php
require __DIR__."/../vendor/autoload.php";
session_start();

use Flutterwave\Util\AuthMode;
use Flutterwave\Util\Currency;
\Flutterwave\Flutterwave::bootstrap();

try {

    $data = [
        "amount" => 2000,
        "currency" => Currency::NGN,
        "tx_ref" => uniqid().time(),
        "redirectUrl" => null,
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

    $data['redirectUrl'] = "http://{$_SERVER['HTTP_HOST']}/examples/endpoint/verify.php?tx_ref={$data['tx_ref']}";

    $cardpayment = \Flutterwave\Flutterwave::create("card");
    $customerObj = $cardpayment->customer->create([
        "full_name" => "Olaobaju Abraham",
        "email" => "olaobajua@gmail.com",
        "phone" => "+2349067985861"
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

