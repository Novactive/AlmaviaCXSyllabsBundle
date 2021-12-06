<?php

/**
 * @copyright Novactive
 * Date: 03/12/2021
 */

declare(strict_types=1);

namespace AlmaviaCX\Syllabs\API\Service;

use AlmaviaCX\Syllabs\API\Gateway\BlacklistGateway;
use AlmaviaCX\Syllabs\Ez\Config\SyllabsBlacklistConfiguration;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

class BlacklistService
{
    /** @var BlacklistGateway */
    protected $gateway;

    /**
     * BlacklistService constructor.
     *
     * @param BlacklistGateway $gateway
     */
    public function __construct(BlacklistGateway $gateway)
    {
        $this->gateway = $gateway;
    }


    public function addAnnotations(array $annotations, string $type)
    {
        $this->gateway->addAnnotations($annotations, $type);
    }
}
