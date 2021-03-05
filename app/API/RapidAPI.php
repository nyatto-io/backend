<?php

namespace App\API;

use GuzzleHttp\Client;

class RapidAPI
{
    /**
     * The HTTP client
     *
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * RapidAPI account key
     *
     * @var string
     */
    protected $key;

    /**
     * The MAL host
     *
     * @var string
     */
    protected $host;

    /**
     * Create a new RapidAPI client instance
     *
     * @param \GuzzleHttp\Client $client
     * @param array $config
     */
    public function __construct(Client $client, $config)
    {
        $this->client = $client;
        $this->key = $config['key'];
        $this->host = $config['host'];
    }

    /**
     * Search for a list of animes or mangas.
     *
     * @param string $query The title of the query
     * @param string $type Can be `anime` or `manga`
     * @return array
     */
    public function search($query, $type)
    {
        $query = urlencode($query);
        $url = "/search/{$type}?q={$query}";

        $response = $this->client->get($url, [
            'headers' => [
                'x-rapidapi-key' => $this->key,
                'x-rapidapi-host' => $this->host,
            ]
        ]);

        $data = (array)json_decode($response->getBody());

        return $data['results'];
    }
}
