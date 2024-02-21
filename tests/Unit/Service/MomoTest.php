<?php

namespace Unit\Service;

use Flutterwave\Flutterwave;
use Flutterwave\Util\AuthMode;
use PHPUnit\Framework\TestCase;
use Flutterwave\Util\Currency;
use Flutterwave\Test\Resources\Setup\Config;

class MomoTest extends TestCase
{
    protected function setUp(): void
    {
        Flutterwave::bootstrap();
    }

    public function testAuthModeRwandaRedirect(){
        $data = [
            "amount" => 2000,
            "currency" => Currency::RWF,
            "tx_ref" => uniqid().time(),
            "redirectUrl" => null,
            "additionalData" => [
                "network" => "MTN",
            ]
        ];

        $momopayment = \Flutterwave\Flutterwave::create("momo");
        $customerObj = $momopayment->customer->create([
            "full_name" => "Abiodun Abrahams",
            "email" => "developers@flutterwavego.com",
            "phone" => "+2349067982061"
        ]);

        $data['customer'] = $customerObj;

        $payload  = $momopayment->payload->create($data);

        $result = $momopayment->initiate($payload);

        $this->assertSame(AuthMode::REDIRECT,$result['mode']);
    }


    public function testInitiateTanzaniaRedirect(){
        $data = [
            "amount" => 2000,
            "currency" => Currency::TZS,
            "tx_ref" => uniqid().time(),
            "redirectUrl" => null,
            "additionalData" => [
                "network" => "VODAFONE",
            ]
        ];

        $momopayment = \Flutterwave\Flutterwave::create("momo");
        $customerObj = $momopayment->customer->create([
            "full_name" => "Abiodun Abrahams",
            "email" => "developers@flutterwavego.com",
            "phone" => "+2349067982061"
        ]);

        $data['customer'] = $customerObj;

        $payload  = $momopayment->payload->create($data);
        $result = $momopayment->initiate($payload);
        $this->assertSame('pending',$result['data_to_save']['status']);

        return $data['tx_ref'];
    }

    public function testAuthModeGhanaRedirect(){
        $data = [
            "amount" => 2000,
            "currency" => Currency::GHS,
            "tx_ref" => uniqid().time(),
            "redirectUrl" => null,
            "additionalData" => [
                "network" => "MTN",
            ]
        ];

        $momopayment = \Flutterwave\Flutterwave::create("momo");
        $customerObj = $momopayment->customer->create([
            "full_name" => "Akin Temilade",
            "email" => "vicomma@gmail.com",
            "phone" => "+2349067985861"
        ]);

        $data['customer'] = $customerObj;

        $payload  = $momopayment->payload->create($data);

        $result = $momopayment->initiate($payload);

        $this->assertSame(AuthMode::REDIRECT,$result['mode']);
    }

    public function testAuthModeUgandaRedirect(){
        $data = [
            "amount" => 2000,
            "currency" => Currency::UGX,
            "tx_ref" => uniqid().time(),
            "redirectUrl" => null,
            "additionalData" => [
                "network" => "MTN",
            ]
        ];

        $momopayment = \Flutterwave\Flutterwave::create("momo");
        $customerObj = $momopayment->customer->create([
            "full_name" => "Ali Bolaji",
            "email" => "developers@flutterwavego.com",
            "phone" => "+2349067901861"
        ]);

        $data['customer'] = $customerObj;

        $payload  = $momopayment->payload->create($data);

        $result = $momopayment->initiate($payload);

        $this->assertSame(AuthMode::REDIRECT,$result['mode']);
    }

//    public function testAuthModeFrancoCallback(){
//        $data = [
//            "amount" => 2000,
//            "currency" => Currency::XAF,
//            "tx_ref" => uniqid().time(),
//            "redirectUrl" => null,
//            "additionalData" => [
//                "network" => "MTN",
//                "country" => "CM"
//            ]
//        ];
//
//        $momopayment = \Flutterwave\Flutterwave::create("momo");
//        $customerObj = $momopayment->customer->create([
//            "full_name" => "Truce Jake",
//            "email" => "developers@flutterwavego.com",
//            "phone" => "+2349067900861"
//        ]);
//
//        $data['customer'] = $customerObj;
//
//        $payload  = $momopayment->payload->create($data);
//
//        $result = $momopayment->initiate($payload);
//
//        $this->assertSame(AuthMode::CALLBACK,$result['mode']);
//    }

//    public function testAuthModeZambiaRedirect(){
//        $data = [
//            "amount" => 2000,
//            "currency" => Currency::ZMW,
//            "tx_ref" => uniqid().time(),
//            "redirectUrl" => null,
//            "additionalData" => [
//                "network" => "MTN",
//            ]
//        ];
//
//        $momopayment = \Flutterwave\Flutterwave::create("momo");
//        $customerObj = $momopayment->customer->create([
//            "full_name" => "Flutterwave Developers",
//            "email" => "developers@flutterwavego.com",
//            "phone" => "+2349067985001"
//        ]);
//
//        $data['customer'] = $customerObj;
//
//        $payload  = $momopayment->payload->create($data);
//
//        $result = $momopayment->initiate($payload);
//
//        $this->assertSame(AuthMode::REDIRECT,$result['mode']);
//    }

    public function testInvalidCurrency()
    {
        $data = [
            "amount" => 2000,
            "currency" => Currency::XOF,
            "tx_ref" => uniqid().time(),
            "redirectUrl" => null,
            "additionalData" => [
                "network" => "MTN",
            ]
        ];

        $momopayment = \Flutterwave\Flutterwave::create("momo");
        $customerObj = $momopayment->customer->create([
            "full_name" => "Olaobaju Jesulayomi Abraham",
            "email" => "vicomma@gmail.com",
            "phone" => "+2349067985861"
        ]);

        $data['customer'] = $customerObj;

        $payload  = $momopayment->payload->create($data);
        $this->expectException(\InvalidArgumentException::class);
        $result = $momopayment->initiate($payload);
    }
}