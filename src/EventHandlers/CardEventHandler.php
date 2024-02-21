<?php

declare(strict_types=1);

namespace Flutterwave\EventHandlers;

use Flutterwave\Contract\ConfigInterface;
use Flutterwave\Util\AuthMode;

class CardEventHandler implements EventHandlerInterface
{
    use EventTracker;

    private static ConfigInterface $config;
    public function __construct($config)
    {
        self::$config = $config;
    }

    /**
     * This is called only when a transaction is successful
     *
     * @param array
     * */
    public function onSuccessful($transactionData): void
    {
        // Get the transaction from your DB using the transaction reference (txref)
        // Check if you have previously given value for the transaction. If you have, redirect to your successpage else, continue
        // Comfirm that the transaction is successful
        // Confirm that the chargecode is 00 or 0
        // Confirm that the currency on your db transaction is equal to the returned currency
        // Confirm that the db transaction amount is equal to the returned amount
        // Update the db transaction record (includeing parameters that didn't exist before the transaction is completed. for audit purpose)
        // Give value for the transaction
        // Update the transaction to note that you have given value for the transaction
        // You can also redirect to your success page from here
        if ($transactionData['data']['chargecode'] === '00' || $transactionData['data']['chargecode'] === '0') {
            self::sendAnalytics('Initiate-Card-Payments');
            echo 'Transaction Completed';
        } else {
            $this->onFailure($transactionData);
        }
    }

    /**
     * This is called only when a transaction failed
     * */
    public function onFailure($transactionData): void
    {
        self::sendAnalytics('Initiate-Card-Payments-error');
        // Get the transaction from your DB using the transaction reference (txref)
        // Update the db transaction record (includeing parameters that didn't exist before the transaction is completed. for audit purpose)
        // You can also redirect to your failure page from here
    }

    /**
     * This is called when a transaction is requeryed from the payment gateway
     * */
    public function onRequery($transactionReference): void
    {
        // Do something, anything!
    }

    /**
     * This is called a transaction requery returns with an error
     * */
    public function onRequeryError($requeryResponse): void
    {
        // Do something, anything!
    }

    /**
     * This is called when a transaction is canceled by the user
     * */
    public function onCancel($transactionReference): void
    {
        // Do something, anything!
        // Note: Somethings a payment can be successful, before a user clicks the cancel button so proceed with caution
    }

    /**
     * This is called when a transaction doesn't return with a success or a failure response. This can be a timedout transaction on the Rave server or an abandoned transaction by the customer.
     * */
    public function onTimeout($transactionReference, $data): void
    {
        // Get the transaction from your DB using the transaction reference (txref)
        // Queue it for requery. Preferably using a queue system. The requery should be about 15 minutes after.
        // Ask the customer to contact your support and you should escalate this issue to the flutterwave support team. Send this as an email and as a notification on the page. just incase the page timesout or disconnects
    }

    /**
     * @throws \Exception
     */
    public function onAuthorization(\stdClass $response, ?array $resource = null): array
    {
        $logger = null;
        $data = [];

        $mode = $resource['mode'] ?? $response->meta->authorization->mode;

        if (property_exists($response, 'data')) {
            $transactionId = $response->data->id;
            $tx_ref = $response->data->tx_ref;
            $data['data_to_save'] = [
                'transactionId' => $transactionId,
                'tx_ref' => $tx_ref,
            ];
        }

        switch ($mode) {
        case AuthMode::PIN:
            $data['dev_instruction'] = "Redirect user to a form to enter his pin and re-initiate the charge adding the params ['pin' => 'USERS_PIN'] to the payload.";
            $data['instruction'] = 'Enter the pin of your card';
            break;
        case AuthMode::REDIRECT:
            $data['dev_instruction'] = 'Redirect the user to the auth link for validation';
            $data['url'] = $response->meta->authorization->redirect;
            break;
        case AuthMode::AVS:
            $data['dev_instruction'] = "Redirect user to a form to enter certain details and re-initiate the charge adding the params ['mode' => 'avs_noauth', 'city' => 'USER_CITY', 'state' => 'USER_STATE', 'country' => 'USER_COUNTRY', 'zipcode' => 'USER_ZIP'] to the payload.";
            $data['instruction'] = 'please complete the form for Address Verification.';
            break;
        case AuthMode::OTP:
            $data['dev_instruction'] = 'Redirect user to a form to validate with OTP code sent to their Phone.';
            $data['instruction'] = $response->data->processor_response;
            $data['validate'] = true;
            break;
        default:
            $data['data_to_save']['status'] = $response->data->status; 
            $data['data_to_save']['amount'] = $response->data->amount; 
            $data['data_to_save']['currency'] = $response->data->currency; 
            $data['data_to_save']['customer_name'] = $response->data->customer->name; 
            $data['data_to_save']['customer_email'] = $response->data->customer->email; 
        }

        $data['mode'] = $mode;

        if (is_array($resource) && ! empty($resource)) {
            $logger = $resource['logger'];
            $logger->notice('Card Event::Authorization Mode: ' . $mode);
        }

        return $data;
    }
}
