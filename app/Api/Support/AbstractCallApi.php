<?php

namespace App\Api\Support;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractCallApi
{
    protected string $route;
    protected string $method;
    protected array $requestParameters;
    public mixed $response;
    private string $errorMessage;


    /**
     * Preforming the api call
     * @return ResponseInterface
     * @throws GuzzleException
     */
    public function execute(): ResponseInterface
    {
        /*Guzzle Client*/
        $client = new Client();

        /*Making the request*/
        return $this->response = $client->request(
            $this->method,      /*Http request methods*/
            $this->route,       /*Full url being requested on*/
            $this->requestParameters   /*Request parameters like headers, auth etc. */
        );
    }

    /**
     * @param ResponseInterface $response
     * @return mixed
     * @throws JsonException Process the api response and return accordingly.
     */
    protected function handleResponse(ResponseInterface $response): mixed
    {
        /*Retrieve and return the response*/
        $response = $response->getBody()->getContents();
        if ($this->validateApiResponse()) {
            if ($decodedResponse = json_decode($response, true, 512, JSON_THROW_ON_ERROR)) {
                return $decodedResponse;
            }
            return $response;
        }
        return $this->errorMessage ?? 'Something went wrong, try again';
    }

    /*TODO: Implement validation*/
    private function validateApiResponse(): bool
    {
        return true;
    }

}
