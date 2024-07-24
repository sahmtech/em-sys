<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class MuqeemApiService
{
    protected $baseUrl;
    protected $apiId;
    protected $appKey;
    protected $username;
    protected $password;
    protected $httpClient;
    protected $accessToken;

    public function __construct()
    {
        $this->baseUrl = config('services.muqeem.base_url');
        $this->apiId = config('services.muqeem.api_id');
        $this->appKey = config('services.muqeem.app_key');
        $this->username = config('services.muqeem.username');
        $this->password = config('services.muqeem.password');
        $this->httpClient = new Client();
    }

    public function authenticate()
    {
        try {
            // Implement authentication logic
            $url = $this->baseUrl . '/api/authenticate';

            $response = $this->httpClient->post($url, [
                'json' => [
                    'username' => $this->username,
                    'password' => $this->password,
                ],
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                    'app-id' => $this->apiId,
                    'app-key' => $this->appKey,
                ],
            ]);

            // Get response body
            $apiResponse = $response->getBody()->getContents();

            $apiData = json_decode($apiResponse, true);

            // Muqeem API returns an access token in response
            $this->accessToken = $apiData['id_token'];
        } catch (RequestException $e) {

            // Handle authentication failure (e.g., log error, throw exception)
            if ($e->getResponse()) {
                // Log or handle specific error from API response
                throw new \Exception("Authentication failed: " . $e->getResponse()->getBody()->getContents());
            } else {
                throw new \Exception("Failed to connect to Muqeem API: " . $e->getMessage());
            }
        }
    }

    public function callApiEndpoint($endpoint, $method = 'GET', $data = [])
    {
        try {
            if (!$this->accessToken) {
                $this->authenticate();
            }
            // dd($this->accessToken);

            $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
            $headers = [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'app-id' => $this->apiId,
                'app-key' => $this->appKey,
                'Authorization' => 'Bearer ' . $this->accessToken,
            ];
            $options = [
                'headers' => $headers,
                'json' => $data,
            ];

            $response = $this->httpClient->request($method, $url, $options);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            return
                'File:' .
                $e->getFile() .
                'Line:' .
                $e->getLine() .
                'Message:' .
                $e->getMessage();
        }
    }
}
