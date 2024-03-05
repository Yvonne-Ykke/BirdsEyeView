<?php

namespace App\Api\Tmdb;

use App\Api\Support\AbstractCallApi;
use GuzzleHttp\Exception\GuzzleException;
use JsonException;

/*https://developer.themoviedb.org/docs/getting-started*/
/*To test connection: start tinker and execute
* > app(App\Api\Tmdb\TmdbApi::class)('3/authentication')
*/
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

        if ($this->response) {
            return $this->handleResponse($this->response);
        }

        return $this->errorMessage;
    }

    /**
     * @param string $endpoint
     * @param array $parameters
     * @return string
     */
    private function createRoute(string $endpoint, array $parameters): string
    {
        return $this->baseUrl . $endpoint;
    }

}
