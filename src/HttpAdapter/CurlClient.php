<?php

namespace Flutterwave\HttpAdapter;

use Psr\Http\Message\ResponseFactoryInterface;

class CurlClient implements \Psr\Http\Client\ClientInterface
{
    /**
     * @var ResponseFactoryInterface
     */
    protected ResponseFactoryInterface $responseFactory;

    /**
     * @var array
     */
    protected array $curlOptions;

    public function isCompatible(): bool
    {
        return \extension_loaded('curl');
    }

    public function sendRequest(\Psr\Http\Message\RequestInterface $request): \Psr\Http\Message\ResponseInterface
    {
        if (!$this->isCompatible()) {
            throw new \RuntimeException('You do not have the curl extension enabled or installed.');
        }

        $ch = \curl_init();

        \curl_setopt($ch, CURLOPT_URL, $request->getUri()->__toString());

        \curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        \curl_setopt($ch, CURLOPT_HTTPHEADER, $this->getHeaders($request));

        \curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $request->getMethod());

        \curl_setopt($ch, CURLOPT_POSTFIELDS, $request->getBody()->__toString());

        $response = curl_exec($ch);

        curl_close($ch);

        return $this->createResponse($response);
    }

    private function createResponse(bool $response): void
    {
        //TODO: complete createResponse method for curlclient implementation
    }

    private function getHeaders(\Psr\Http\Message\RequestInterface $request)
    {
        $headers = [];

        foreach ($request->getHeaders() as $name => $values) {
            $headers[] = $name . ': ' . implode(', ', $values);
        }

        return $headers;
    }
}
