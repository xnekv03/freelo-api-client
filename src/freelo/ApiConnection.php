<?php declare(strict_types=1);


namespace Freelo;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;

trait ApiConnection
{

    protected $endpointUrl = 'https://api.freelo.cz/v1/';

    /**
     * @throws Exception
     */
    public function apiGetCall($url)
    {
        $client = new Client();

        try {
            $response = $client->request('GET', $this->endpointUrl . $url, [
                'auth' => [$this->loginEmail, $this->apiKey]
            ]);
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }

        $response = $response->getBody()->getContents();
        return json_decode($response);
    }


    /**
     * @throws Exception
     */
    public function apiPostCall(string $url, array $body)
    {
        $client = new Client();

        try {
            $response = $client->request('POST', $this->endpointUrl . $url, [
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
