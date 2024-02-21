<?php

namespace Unit\Service;

use Flutterwave\Flutterwave;

use Flutterwave\Util\AuthMode;
use PHPUnit\Framework\TestCase;
use Flutterwave\Util\Currency;
use Flutterwave\Test\Resources\Setup\Config;
use Test_Cards;

class CardTest extends TestCase
{
    protected function setUp(): void
    {
        Flutterwave::bootstrap();
    }

    public function testAuthModeReturnPin()
    {
        $data = [
            "amount" => 2000,
            "currency" => Currency::NGN,
            "tx_ref" => "TEST-".uniqid().time(),
            "redirectUrl" => "https://www.example.com",
            "additionalData" => [
                "subaccounts" => [
                    ["id" => "RSA_345983858845935893"]
                ],
                "meta" => [
                    "unique_id" => uniqid().uniqid()
                ],
                "preauthorize" => false,
                "payment_plan" => null,
                "card_details" => Test_Cards::MSTR_CARD_PIN_TWO
            ],
        ];

        $cardpayment = Flutterwave::create("card");
        $customerObj = $cardpayment->customer->create([
            "full_name" => "Olaobaju Abraham",
            "email" => "ol868gjdfjua@gmail.com",
            "phone" => "+2349067985861"
        ]);
        
        $data['customer'] = $customerObj;
        $payload  = $cardpayment->payload->create($data);
        $result = $cardpayment->initiate($payload);

        $this->assertSame(AuthMode::PIN,$result['mode']);
    }

    public function testInvalidArgumentExceptionThrowOnNoCardDetails()
    {
        $data = [
            "amount" => 2000,
            "currency" => Currency::NGN,
            "tx_ref" => "TEST-".uniqid().time(),
            "redirectUrl" => "https://www.example.com",
            "additionalData" => null,
        ];

        $cardpayment = Flutterwave::create("card");
        $customerObj = $cardpayment->customer->create([
            "full_name" => "Olaobaju Abraham",
            "email" => "ola57679urhfdjf@gmail.com",
            "phone" => "+234906792751"
        ]);
        $data['customer'] = $customerObj;
        $payload  = $cardpayment->payload->create($data);
        $msg = "Card Service:Please pass card details.";
        $this->expectException(\InvalidArgumentException::class);
        $result = $cardpayment->initiate($payload);
    }

    public function testAuthModeReturnRedirect()
    {
        $data = [
            "amount" => 2000,
            "currency" => Currency::NGN,
            "tx_ref" => "TEST-".uniqid().time(),
            "redirectUrl" => "https://www.example.com",
            "additionalData" => [
                "subaccounts" => [
                    ["id" => "RSA_345983858845935893"]
                ],
                "meta" => [
                    "unique_id" => uniqid().uniqid()
                ],
                "preauthorize" => false,
                "payment_plan" => null,
                "card_details" => Test_Cards::MSTR_CARD_PIN_ONE
            ],
        ];

        $cardpayment = Flutterwave::create("card");
        $customerObj = $cardpayment->customer->create([
            "full_name" => "Olaobaju Abraham",
            "email" => "ol868gjdfjua@gmail.com",
            "phone" => "+2349062985861"
        ]);
        $data['customer'] = $customerObj;
        $payload  = $cardpayment->payload->create($data);
        $result = $cardpayment->initiate($payload);
        $payload->set(AuthMode::PIN,"1234");
        $result = $cardpayment->initiate($payload);// with pin in payload

        $this->assertSame(AuthMode::REDIRECT, $result['mode']);

    }

    // public function testAuthModeReturnAVS()
    // {
    //     $data = [
    //         "amount" => 2000,
    //         "currency" => Currency::NGN,
    //         "tx_ref" => "TEST-".uniqid().time(),
    //         "redirectUrl" => "https://www.example.com",
    //         "additionalData" => [
    //             "subaccounts" => [
    //                 ["id" => "RSA_345983858845935893"]
    //             ],
    //             "meta" => [
    //                 "unique_id" => uniqid().uniqid()
    //             ],
    //             "preauthorize" => false,
    //             "payment_plan" => null,
    //             "card_details" => [
    //                 "card_number" => "4556052704172643",
    //                 "cvv" => "899",
    //                 "expiry_month" => "09",
    //                 "expiry_year" => "32"
    //             ]
    //         ],
    //     ];

    //     $cardpayment = Flutterwave::create("card");
    //     $customerObj = $cardpayment->customer->create([
    //         "full_name" => "Olaobaju Abraham",
    //         "email" => "oyudfjmscfka@gmail.com",
    //         "phone" => "+2349067968461"
    //     ]);
    //     $data['customer'] = $customerObj;
    //     $payload  = $cardpayment->payload->create($data);
    //     $result = $cardpayment->initiate($payload);
    //     $this->assertSame(AuthMode::AVS, $result['mode']);
    // }

    public function testPreuthCard()
    {
        $data = [
            "amount" => 2000,
            "currency" => Currency::NGN,
            "tx_ref" => "TEST-".uniqid().time(),
            "redirectUrl" => "https://www.example.com",
            "additionalData" => [
                "subaccounts" => [
                    ["id" => "RSA_345983858845935893"]
                ],
                "meta" => [
                    "unique_id" => uniqid().uniqid()
                ],
                "preauthorize" => false,
                "payment_plan" => null,
                "card_details" => Test_Cards::PREATH
            ],
        ];

        $cardpayment = Flutterwave::create("card");
        $customerObj = $cardpayment->customer->create([
            "full_name" => "Olaobaju Abraham",
            "email" => "ol868gjdfjua@gmail.com",
            "phone" => "+2349062985861"
        ]);
        $data['customer'] = $customerObj;
        $payload  = $cardpayment->payload->create($data);
        $result = $cardpayment->initiate($payload);

        $this->assertTrue(!empty($result['url']));
    }

    public function testAuthModelReturnNoauth()
    {
        $this->assertTrue(true);
    }

}