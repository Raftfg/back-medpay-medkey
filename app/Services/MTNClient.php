<?php

namespace App\Services;

use GuzzleHttp\Client;

class MTNClient
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://sandbox.momodeveloper.mtn.com',
            'headers' => [
                'Content-Type' => 'application/json',
                'Ocp-Apim-Subscription-Key' => env('MTN_CLIENT_ID'),
            ],
        ]);
    }
    public function sendRequest($method, $url, $params = [])
    {
        try {
            $response = $this->client->request($method, $url, [
                'json' => $params,
            ]);
            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            // Gérer les erreurs de requête
            throw $e;
        }
    }
}
