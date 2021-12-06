<?php

/**
 * @copyright Novactive
 * Date: 24/11/2021
 */

declare(strict_types=1);

namespace AlmaviaCX\Syllabs\Ez\Config;

use AlmaviaCX\Syllabs\Ez\Value\Configuration\BlacklistConfiguration;
use AlmaviaCX\Syllabs\Ez\Value\Configuration\ContentTypeConfiguration;
use AlmaviaCX\Syllabs\Ez\Value\Configuration\SourceFieldConfiguration;
use AlmaviaCX\Syllabs\Ez\Value\Configuration\TargetFieldConfiguration;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

class SyllabsBlacklistConfiguration
{
    /** @var TagsService */
    protected $tagsService;

    /** @var ConfigResolverInterface */
    protected $configResolver;

    protected $configuration = [];

    /**
     * SyllabsBlacklistConfiguration constructor.
     *
     * @param TagsService             $tagsService
     * @param ConfigResolverInterface $configResolver
     */
    public function __construct(TagsService $tagsService, ConfigResolverInterface $configResolver)
    {
        $this->tagsService = $tagsService;
        $this->configResolver = $configResolver;
    }

    protected function getRawConfigurations(): array
    {
        return $this->configResolver->getParameter('syllabs.blacklist.config', 'almaviacx');
    }

    /**
     * @return BlacklistConfiguration[]
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function getConfigurations(): array
    {
        if (!empty($this->configuration)) {
            return $this->configuration;
        }

        $rawConfigurations = $this->getRawConfigurations();
        foreach ($rawConfigurations as $rawConfiguration) {
            foreach ($rawConfiguration['parent_tags'] as $parentTagIdentifier) {
                $parentTag = is_int($parentTagIdentifier) ?
                    $this->tagsService->loadTag($parentTagIdentifier) :
                    $this->tagsService->loadTagByRemoteId($parentTagIdentifier);

                $this->configuration[] = new BlacklistConfiguration(
                    $parentTag,
                    $rawConfiguration['type'],
                    isset($rawConfiguration['subtype']) ? $rawConfiguration['subtype'] : ''
                );
            }
        }
        return $this->configuration;
    }

    public function getConfigurationForTag(Tag $tag): ?BlacklistConfiguration
    {
        $configurations = $this->getConfigurations();
        foreach ($configurations as $configuration) {
            if ($configuration->getParentTag()->id === $tag->parentTagId) {
                return $configuration;
            }
        }
        return null;
    }
}
