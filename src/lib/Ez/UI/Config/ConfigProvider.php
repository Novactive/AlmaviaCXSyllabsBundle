<?php

/**
 * @copyright Novactive
 * Date: 27/07/2021
 */

declare(strict_types=1);

namespace AlmaviaCX\Syllabs\Ez\UI\Config;

use AlmaviaCX\Syllabs\Ez\Config\SyllabsConfiguration;
use EzSystems\EzPlatformAdminUi\UI\Config\ProviderInterface;

class ConfigProvider implements ProviderInterface
{
    /** @var SyllabsConfiguration */
    protected $configuration;

    /**
     * ConfigProvider constructor.
     *
     * @param SyllabsConfiguration $configuration
     */
    public function __construct(SyllabsConfiguration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @inheritDoc
     */
    public function getConfig()
    {
        return $this->configuration->getUiConfiguration();
    }
}
