<?php

declare(strict_types=1);

namespace AlmaviaCX\Syllabs\API\Gateway;

use AlmaviaCX\Syllabs\API\HttpClient;
use AlmaviaCX\Syllabs\API\Parser\ResponseParser;
use AlmaviaCX\Syllabs\API\Value\Document;
use AlmaviaCX\Syllabs\API\Value\EntitieAnnotation;
use AlmaviaCX\Syllabs\API\Value\ThemeAnnotation;
use AlmaviaCX\Syllabs\API\Value\WikitagAnnotation;
use spec\EzSystems\EzPlatformGraphQL\Tools\FieldArgArgument;

class ProcessGateway
{
    /** @var HttpClient */
    protected $client;

    /** @var ResponseParser */
    protected $responseParser;

    /** @var string */
    const URL = "/process";

    public function __construct(HttpClient $client, ResponseParser $responseParser)
    {
        $this->client         = $client;
        $this->responseParser = $responseParser;
    }

    /**
     * @param array $documents
     */
    public function process(array $documents): array
    {
        $datas['processes'] = ['all'];

        foreach ($documents as $document) {
            $dataDoc              = [
                'id'    => $document->id,
                'title' => $document->title,
                'text'  => $document->text
            ];
            $datas['documents'][] = $dataDoc;
        }

        $response = $this->client->call($datas, self::URL);

        $documents = json_decode($response->getBody(true)->getContents(), true);

        return $this->responseParser->parseDocuments($documents['documents']);
    }

}
