<?php

namespace App\Api\Support;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\ClientException;

abstract class AbstractCallApi
{
    protected string $route;
    protected string $method;
    protected array $requestParameters;
    public mixed $response;
    public string $errorMessage;


    /**
     * Preforming the api call
     * @return ResponseInterface|null
     * @throws GuzzleException
     */
    public function execute(): ?ResponseInterface
    {
        /*Guzzle Client*/
        $client = new Client();

        /*Making the request*/
        try {
            return $this->response = $client->request(
                $this->method,      /*Http request methods*/
                $this->route,       /*Full url being requested on*/
                $this->requestParameters   /*Request parameters like headers, auth etc. */
            );
        } catch (ClientException $exception) {
            $this->errorMessage = $exception->getMessage();
            Log::error($this->errorMessage);
            return null;
        }

    }

    /**
     * @param ?ResponseInterface $response
     * @return mixed
     * @throws JsonException Process the api response and return accordingly.
     */
    protected function handleResponse(?ResponseInterface $response): mixed
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

    private function validateApiResponse(): bool
    {
        if ($this->validateStatusCode($this->response->getStatusCode()))
            return true;

        return false;
    }

    private function validateStatusCode(?int $statusCode): bool
    {
        switch ($statusCode) {
            case 200:
                return true;
            case 400:
                $this->errorMessage = 'Bad request';
                break;
            case 401:
                $this->errorMessage = 'Unauthorized';
                break;
            case 403:
                $this->errorMessage = 'Forbidden';
                break;
            case 404:
                $this->errorMessage = 'Page not found';
                break;
            case 429:
                $this->errorMessage = 'Too many requests';
                break;
            case 500:
                $this->errorMessage = 'Internal server error';
                break;
            case 501:
                $this->errorMessage = 'Not implemented';
                break;
            case 502:
                $this->errorMessage = 'Bad gateway';
                break;
            case 503:
                $this->errorMessage = 'Service unavailable';
                break;
            case 504:
                $this->errorMessage = 'Gateway timed out';
                break;
        }

        return false;
    }

}
