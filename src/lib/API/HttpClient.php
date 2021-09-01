<?php

namespace AlmaviaCX\Syllabs\API;

use GuzzleHttp\Client;

class HttpClient extends Client
{
    /** @var string */
    protected $APIUrl;

    public function __construct(string $APIUrl, array $config = [])
    {
        $this->APIUrl = $APIUrl;
        parent::__construct($config);
    }

    public function call($datas, $url)
    {
        $options = [
            'json' => $datas
        ];
        $uri     = $this->APIUrl.$url;

        return $this->request('POST', $uri, $options);
    }
}
