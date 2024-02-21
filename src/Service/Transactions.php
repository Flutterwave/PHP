<?php

declare(strict_types=1);

namespace Flutterwave\Service;

use Flutterwave\Contract\ConfigInterface;
use Flutterwave\EventHandlers\EventTracker;
use Flutterwave\Traits\ApiOperations\Post;
use Psr\Http\Client\ClientExceptionInterface;

class Transactions extends Service
{
    use EventTracker;
    use Post;

    public const ENDPOINT = 'transactions';
    public const REFUND_PATH = '/:id' . '/refund';
    public const MULTI_REFUND_ENDPOINT = '/refunds';
    public const REFUND_DETAILS_PATH = 'refunds/:id';
    public const TRANSACTION_FEE_PATH = '/fee';
    public const RESEND_FAILED_HOOKS_PATH = '/:id/resend-hook';
    public const TRANSACTION_TIMELINE_PATH = '/:id/events';
    public const VALIDATE_TRANSACTION = 'validate-charge';
    private static string $name = 'transactions';
    private string $end_point;
    private array $payment_type = [
        'card','debit_ng_account','mobilemoney','bank_transfer', 'ach_payment',
    ];

    public function __construct(?ConfigInterface $config = null)
    {
        parent::__construct($config);
        $this->end_point = Transactions::ENDPOINT;
        
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function verify(string $transactionId): \stdClass
    {
        $this->checkTransactionId($transactionId);
        $this->logger->notice('Transaction Service::Verifying Transaction...' . $transactionId);
        self::startRecording();
        $response = $this->request(
            null,
            'GET',
            self::ENDPOINT . "/{$transactionId}/verify",
        );
        self::setResponseTime();

        return $response;
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function verifyWithTxref(string $tx_ref): \stdClass
    {
        $this->logger->notice('Transaction Service::Verifying Transaction...' . $tx_ref);
        self::startRecording();
        $response = $this->request(
            null,
            'GET',
            self::ENDPOINT . '/verify_by_reference?tx_ref=' . $tx_ref,
        );
        self::setResponseTime();
        return $response;
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function refund(string $trasanctionId): \stdClass
    {
        $this->checkTransactionId($trasanctionId);
        $this->logger->notice("Transaction Service::Refunding Transaction...{$trasanctionId}");
        self::startRecording();
        $response = $this->request(
            null,
            'GET',
            self::ENDPOINT . "/{$trasanctionId}/refund",
        );
        self::setResponseTime();
        return $response;
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function getAllTransactions(): \stdClass
    {
        $this->logger->notice('Transaction Service::Retrieving all Transaction for Merchant');
        self::startRecording();
        $response = $this->request(
            null,
            'GET',
            self::ENDPOINT,
        );
        self::setResponseTime();
        return $response;
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function getRefundInfo(string $trasanctionId): \stdClass
    {
        $this->checkTransactionId($trasanctionId);
        $this->logger->notice("Transaction Service::Retrieving refund:Transactionid => {$trasanctionId}");
        self::startRecording();
        $response = $this->request(
            null,
            'GET',
            "refunds/{$trasanctionId}",
        );
        self::setResponseTime();
        return $response;
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function getTransactionFee(
        string $amount,
        string $currency = 'NGN',
        string $payment_type = 'card'
    ): \stdClass {
        if (! $amount) {
            $msg = 'Please pass a valid amount';
            $this->logger->warning($msg);
            throw new \InvalidArgumentException($msg);
        }
        $data = [
            'amount' => $amount,
            'currency' => $currency,
        ];

        if (! isset($this->payment_type[$payment_type])) {
            $logData = json_encode($this->payment_type);
            $msg = "Please pass a valid Payment Type: options::{$logData}";
            $this->logger->warning($msg);
            throw new \InvalidArgumentException($msg);
        }

        $data['payment_type'] = $payment_type;

        $query = http_build_query($data);

        $logData = json_encode($data);
        $this->logger->notice("Transaction Service::Retrieving Transaction Fee: Util => {$logData}");
        self::startRecording();
        $response = $this->request(
            null,
            'GET',
            self::ENDPOINT . "/fee?{$query}",
        );
        self::setResponseTime();
        return $response;
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function resendFailedHooks(string $transactionId): \stdClass
    {
        $this->checkTransactionId($transactionId);
        $this->logger->notice("Transaction Service::Resending Transaction Webhook: TransactionId => {$transactionId}");
        self::startRecording();
        $response = $this->request(
            null,
            'POST',
            self::ENDPOINT . "/{$transactionId}/resend-hook",
        );
        self::setResponseTime();
        return $response;
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function retrieveTimeline(string $transactionId): \stdClass
    {
        $this->checkTransactionId($transactionId);
        $this->logger->notice(
            "Transaction Service::Retrieving Transaction Timeline: TransactionId => {$transactionId}"
        );
        self::startRecording();
        $response = $this->request(
            null,
            'GET',
            self::ENDPOINT . "/{$transactionId}/events",
        );
        self::setResponseTime();
        return $response;
    }

    /**
     * @throws ClientExceptionInterface
     */
    public function validate(string $otp, string $flw_ref): \stdClass
    {
        $logData = json_encode(
            [
                'flw_ref' => $flw_ref,
                'date' => date('mm-dd-YYYY h:i:s'),
            ]
        );

        $this->logger->notice('Transaction Service::Validating Transaction ...' . $logData);

        $data = [
            'otp' => $otp,
            'flw_ref' => $flw_ref,
        //            "type" => "card" //default would be card
        ];

        return $this->request(
            $data,
            'POST',
            self::VALIDATE_TRANSACTION,
        );
    }

    public function getName(): string
    {
        return self::$name;
    }
}
