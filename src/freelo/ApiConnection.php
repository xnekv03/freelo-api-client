<?php /** @noinspection ALL */
declare(strict_types=1);


namespace Freelo;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use stdClass;

/**
 * Trait ApiConnection
 * @package Freelo
 */
trait ApiConnection
{
    /**
     * @var string
     */
    protected $endpointUrl = 'https://api.freelo.cz/v1/';

    /**
     * @throws Exception
     */
    public function apiGetCall($url)
    {
        return $this->callApi($url, 'get');
    }

    /**
     * @throws Exception
     */
    public function apiPostCall(string $url, array $body)
    {
        return $this->callApi($url, 'post', $body);
    }

    /**
     * @throws Exception
     */
    public function apiDeleteCall(string $url)
    {
        return $this->callApi($url, 'delete');
    }


    /**
     * @param string $url
     * @param string $method
     * @param array|null $body
     * @return stdClass
     * @throws Exception
     */
    private function callApi(string $url, string $method, array $body = null): stdClass
    {
        try {
            $response = (new Client())->request($method, $this->endpointUrl . $url, [
                'auth' => [$this->loginEmail, $this->apiKey],
                'body' => json_encode($body),
                RequestOptions::JSON
            ]);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }

        $response = $response->getBody()->getContents();

        return json_decode($response);

    }
}
