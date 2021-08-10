<?php

namespace AlmaviaCX\Syllabs\API\Service;

use AlmaviaCX\Syllabs\API\Gateway\ProcessGateway;
use AlmaviaCX\Syllabs\API\Value\Document;

class ProcessService
{
    /** @var ProcessGateway */
    protected $gateway;

    /**
     * ProcessService constructor.
     *
     * @param ProcessGateway $gateway
     */
    public function __construct(ProcessGateway $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * @param Document[] $documents
     *
     * @return Document[]
     */
    public function process(array $documents): array
    {
        return $this->gateway->process($documents);
    }
}
