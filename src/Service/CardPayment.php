<?php

declare(strict_types=1);

namespace Flutterwave\Service;

use Exception;
use Flutterwave\Contract\ConfigInterface;
use Flutterwave\Contract\Payment;
use Flutterwave\EventHandlers\CardEventHandler;
use Flutterwave\Entities\Payload;
use Flutterwave\Traits\Group\Charge;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use Psr\Http\Client\ClientExceptionInterface;

class CardPayment extends Service implements Payment
{
    use Charge;

    public const ENDPOINT = 'charges';
    public const TYPE = 'card';
    protected static int $count = 0;
    protected bool $preauthorize = false;
    protected ?object $payment = null;
    protected string $url;
    private static string $name = 'card';
    private string $end_point;
    private CardEventHandler $eventHandler;

    public function __construct(?ConfigInterface $config = null)
    {
        parent::__construct($config);

        $endpoint = $this->getEndpoint();

        $this->url = $this->baseUrl . '/' . $endpoint . '?type=' . self::TYPE;
        $this->end_point = self::ENDPOINT . '?type=' . self::TYPE;
        $this->eventHandler = new CardEventHandler($config);
    }

    /**
     * @param  Payload $payload
     * @return array
     * @throws GuzzleException
     */
    public function initiate(Payload $payload): array
    {
        if (self::$count >= 2) {
            //TODO: if payload does not have pin on 2nd request, trigger a warning.
        }
        $this->logger->notice('Card Service::Initiating Card Payment...');

        if ($this->checkPayloadIsValid($payload, 'card_details')) {
            self::$count++;
            $this->logger->notice('Card Service::Payload Confirmed...');
            return $this->charge($payload);
        }
        $msg = 'Card Service:Please pass card details.';
        $this->logger->info($msg);
        throw new InvalidArgumentException($msg);
    }

    public function save(callable $callback): void
    {
        // TODO: Implement save() method.
    }

    /**
     * @param  Payload $payload
     * @return array
     *
     * @throws ClientExceptionInterface
     */
    public function charge(Payload $payload): array
    {
        $tx_ref = $payload->get('tx_ref');
        $this->logger->notice("Card Service::Started Charging Card tx_ref:({$tx_ref})...");

        //required data
        $url = $this->url;
        $secret = $this->config->getSecretKey();
        $payload = $payload->toArray(self::TYPE);
        //request payload
        $client = $this->encryption(
            json_encode($payload)
        );

        $body = [
            'client' => $client,
        ];

        $this->eventHandler::startRecording();
        $request = $this->request($body, 'POST');
        $this->eventHandler::setResponseTime();
        return $this->handleAuthState($request, $payload);
    }

    /**
     * this is the encrypt3Des function that generates an encryption Key for you
     * by passing your transaction Util and Secret Key as a parameter.
     *
     * @param  string $data
     * @param  $key
     * @return string
     */

    public function encrypt3Des(string $data, $key): string
    {
        $encData = openssl_encrypt($data, 'DES-EDE3', $key, OPENSSL_RAW_DATA);
        return base64_encode($encData);
    }

    /**
     * this is the encryption function that combines the getkey() and encryptDes().
     */

    public function encryption(string $params): string
    {
        //retrieve secret key
        $key = $this->config->getEncryptkey();
        //encode the data and the
        return $this->encrypt3Des($params, $key);
    }

    /**
     * @param  \stdClass $response
     * @param  $payload
     * @return array
     * @throws Exception
     */
    public function handleAuthState(\stdClass $response, $payload): array
    {
        // dd($response);
        $data['request_data'] = null;

        $mode = (\property_exists($response, 'meta') ) ? 
        $response->meta->authorization->mode: $response->data->auth_model;
        if ($mode === 'pin') {
            $data['request_data'] = $payload;
        }
        $extra_data = $this->eventHandler->onAuthorization(
            $response, 
            ['logger' => $this->logger, 'mode' => $mode]
        );
        
        return array_merge([ 'request_data' => $data['request_data'] ], $extra_data);
    }

    public function getName(): string
    {
        return self::$name;
    }
}
