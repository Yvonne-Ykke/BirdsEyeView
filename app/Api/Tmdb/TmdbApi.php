<?php

namespace App\Api\Tmdb;

use App\Api\Support\AbstractCallApi;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;

/*https://developer.themoviedb.org/docs/getting-started*/
class TmdbApi extends  AbstractCallApi
{
    private string $baseUrl = 'https://iets/api/';

    /**
     * @throws GuzzleException
     * @throws JsonException
     */
    public function __invoke($endpoint, $parameters = [])
    {
        $this->route = $this->createRoute($endpoint, $parameters);
        $this->method = 'GET';

        $this->requestParameters = [
            'auth' => [
                env('TMDB_API_USERNAME'),
                env('TMDB_API_PASSWORD'),
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
