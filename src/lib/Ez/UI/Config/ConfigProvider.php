<?php

/**
 * @copyright Novactive
 * Date: 27/07/2021
 */

declare(strict_types=1);

namespace AlmaviaCX\Syllabs\Ez\UI\Config;

use EzSystems\EzPlatformAdminUi\UI\Config\ProviderInterface;

class ConfigProvider implements ProviderInterface
{

    /**
     * @inheritDoc
     */
    public function getConfig()
    {
        return [
            'contentTypes' => [
                'news' => [
                    'sourceFields' => [
                        'title' => ['title'],
                        'text'  => ['body'],
                    ],
                    'targetFields' => [
                        'entities' => [
                            'fieldIdentfier' => 'syllabs_tags',
                            'parentTagId'    => 1,
                        ],
                        'themes'   => [
                            'fieldIdentfier' => 'syllabs_tags',
                            'parentTagId'    => 1,
                        ],
                        'wikitags' => [
                            'fieldIdentfier' => 'syllabs_tags',
                            'parentTagId'    => 1,
                        ],
                    ],
                ],
            ],
        ];
    }
}
