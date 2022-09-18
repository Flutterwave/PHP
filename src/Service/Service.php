<?php

namespace Flutterwave\Service;

use Flutterwave\Contract\ConfigInterface;
use Flutterwave\Contract\ServiceInterface;
use Flutterwave\EventHandlers\EventHandlerInterface;
use Flutterwave\Helper\Config;
use Unirest\Exception;
use Unirest\Request\Body;
use Unirest\Response;

class Service implements ServiceInterface
{
    const ENDPOINT = "";
    private static string $name = "service";
    /**
     * @var ConfigInterface|Config|null
     */
    private static $spareConfig;
    private EventHandlerInterface $eventHandler;
    protected string $baseUrl;
    protected \Psr\Log\LoggerInterface $logger;
    private \Unirest\Request $http;
    protected ConfigInterface $config;
    public ?Payload $payload;
    public ?Customer $customer;
    protected string $url;
    protected string $secret;

    public function __construct(?ConfigInterface $config = null)
    {
        self::bootstrap($config);
        $this->customer = new Customer;
        $this->payload = new Payload;
        $this->config = (is_null($config))?self::$spareConfig:$config;
        $this->http = $this->config->getHttp();
        $this->logger = $this->config->getLoggerInstance();
        $this->secret = $this->config->getSecretKey();
        $this->url  = $this->config::BASE_URL."/";
        $this->baseUrl = $this->config::BASE_URL;
    }

    /**
     * @throws Exception
     * @throws \Exception
     */
    protected function request(?array $data = null, string $verb = 'GET', $additionalurl = ""): \stdClass
    {
        $response = null;
        $secret = $this->config->getSecretKey();

        switch ($verb){
            case 'POST':
                $json = Body::Json($data);
                $response = $this->http::post($this->url.$additionalurl,[
                    "Authorization" => "Bearer $secret",
                    "Content-Type" => "application/json"
                ],$json);
                break;
            case 'PUT':
                $json = Body::Json($data??[]);
                $response = $this->http::put($this->url.$additionalurl,[
                    "Authorization" => "Bearer $secret",
                    "Content-Type" => "application/json"
                ],$json);
                break;
            case 'DELETE':
                $response = $this->http::delete($this->url.$additionalurl,[
                    "Authorization" => "Bearer $secret",
                    "Content-Type" => "application/json"
                ]);
            default:
                $response = $this->http::get($this->url.$additionalurl,[
                    "Authorization" => "Bearer $secret",
                    "Content-Type" => "application/json"
                ]);
                break;
        }

        if($response instanceof Response)
        {
            if($response->code > 200){
                $this->logger->error("Service::". $response->body->message);
                throw new \Exception($response->body->message);
                exit;
            }

            if(is_string($response->body)){
                $this->logger->error("Service::". $response->body);
                throw new \Exception($response->body);
            }
        }

        return $response->body;
    }

    public function getName(): string
    {
        return self::$name;
    }

    protected function checkTransactionId($transactionId):void
    {
        $pattern = '/([0-9]){7}/';
        $is_valid = preg_match_all($pattern,$transactionId);

        if(!$is_valid){
            $this->logger->warning("Transaction Service::cannot verify invalid transaction id. ");
            throw new \InvalidArgumentException("cannot verify invalid transaction id.");
        }
    }

    private  static function bootstrap(?ConfigInterface $config = null)
    {
        if(\is_null($config))
        {
            require __DIR__."/../../setup.php";
            $config = Config::setUp(
                $_SERVER[Config::SECRET_KEY],
                $_SERVER[Config::PUBLIC_KEY],
                $_SERVER[Config::ENCRYPTION_KEY],
                $_SERVER['ENV']
            );
        }
        self::$spareConfig = $config;
    }
}