<?php

namespace App\Api\Tmdb;

use App\Api\Support\AbstractCallApi;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;

/*https://developer.themoviedb.org/docs/getting-started*/
class TmdbApi extends  AbstractCallApi
{
    private string $baseUrl = 'https://api.themoviedb.org/';

    /**
     * @throws GuzzleException
     * @throws JsonException
     */
    public function __invoke($endpoint, $parameters = [])
    {
        $this->route = $this->createRoute($endpoint, $parameters);
        $this->method = 'GET';

        $this->requestParameters = [
            'headers' => [
                'Authorization' => 'Bearer ' . env('TMDB_API_ACCESS_CODE'),
                'accept' => 'application/json',
            ],
        ];

        $this->response = $this->execute();
        return $this->handleResponse($this->response);
    }

    /**
     * @param string $endpoint
     * @param array $parameters
     * @return string
     */
    private function createRoute(string $endpoint, array $parameters): string
    {
        return $this->baseUrl . $endpoint . '/?format=json&' . http_build_query($parameters);
    }

}
