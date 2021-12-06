<?php

/**
 * @copyright Novactive
 * Date: 03/12/2021
 */

declare(strict_types=1);

namespace AlmaviaCX\Syllabs\API\Gateway;

use AlmaviaCX\Syllabs\API\HttpClient;
use AlmaviaCX\Syllabs\API\Parser\ResponseParser;
use AlmaviaCX\Syllabs\API\Value\Annotation;

class BlacklistGateway
{
    /** @var HttpClient */
    protected $client;

    /** @var ResponseParser */
    protected $responseParser;

    /** @var string */
    private const URL = "/list/%s/blacklist";

    /**
     * BlacklistGateway constructor.
     *
     * @param HttpClient $client
     */
    public function __construct(HttpClient $client)
    {
        $this->client = $client;
    }


    public function addAnnotations(array $annotations, string $type)
    {
        $response = $this->client->call($annotations, sprintf(self::URL, $type));
    }
}
