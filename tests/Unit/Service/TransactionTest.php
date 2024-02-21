<?php

namespace Unit\Service;

use Flutterwave\Service\Transactions;
use PHPUnit\Framework\TestCase;

class TransactionTest extends TestCase
{
    public Transactions $service;

    protected function setUp(): void
    {
        $this->service = new Transactions();
    }

    /**
     * @depends Unit\Service\MomoTest::testInitiateTanzaniaRedirect
     */
    public function testVerifyingTransaction(string $tx_ref)
    {
        $result = $this->service->verifyWithTxref($tx_ref);
        $data = $result->data;
        $this->assertSame($data->customer->email, "developers@flutterwavego.com");
        return [ "id" => $data->id, "amount" => $data->amount, "currency" => $data->currency ];
    }

    /**
     * @depends testVerifyingTransaction
     */
    public function testVerifyingTransactionWithId(array $data)
    {
        $tx_id = $data['id'];

        $result = $this->service->verify($tx_id);
        $data = $result->data;
        $this->assertSame($data->customer->email, "developers@flutterwavego.com");
    }

    /**
     * @depends testVerifyingTransaction
     */
    public function testResendingFailedHooks( array $data )
    {
        sleep(6);
        $tx_id = $data['id'];
        $result = $this->service->resendFailedHooks($tx_id);
        $this->assertTrue( $result->status === "success" && $result->data === "hook sent");
    }

    /**
     * @depends testVerifyingTransaction
     */
    public function testRetrievingTimeline( array $data )
    {
        $tx_id = $data['id'];
        $result = $this->service->retrieveTimeline($tx_id);
        $this->assertTrue( $result->status === "success" && $result->message === "Transaction events fetched");
    }

    // public function testValidateCharge( string $flw_ref )
    // {
    //     $result = $this->service->validate("3310", $flw_ref);
    //     dd($result);
    // }
}