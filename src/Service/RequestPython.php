<?php

declare(strict_types=1);

namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;

final class RequestPython
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'http://django:8000',
        ]);
    }

    /**
     * @throws GuzzleException
     * @throws \Exception
     */
    public function execute($url, $data)
    {
        try {
            $response = $this->client->post($url, [
                'json' => $data
            ]);

            $statusCode = $response->getStatusCode();
            $body = $response->getBody();
            $output = json_decode($body->getContents(), true);

            if ($statusCode !== 200 || $output['status'] !== 'success' || empty($output['result'])) {
                throw new \Exception('[ code '.$statusCode.' ] : '.json_encode($output));
            }

            return $output;
        } catch (RequestException $e) {
            throw new \Exception('Ошибка обращения к скрипту python: '.$e->getMessage());
        }
    }
}
