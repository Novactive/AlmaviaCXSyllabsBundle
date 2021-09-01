<?php

/**
 * @copyright Novactive
 * Date: 30/07/2021
 */

declare(strict_types=1);

namespace AlmaviaCX\Syllabs\Ez\Config;

use AlmaviaCX\Syllabs\Ez\Value\Configuration\ContentTypeConfiguration;
use AlmaviaCX\Syllabs\Ez\Value\Configuration\SourceFieldConfiguration;
use AlmaviaCX\Syllabs\Ez\Value\Configuration\TargetFieldConfiguration;
use AlmaviaCX\Syllabs\Ez\Value\Configuration\TargetFieldTypeConfiguration;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\Core\MVC\ConfigResolverInterface;
use Netgen\TagsBundle\API\Repository\TagsService;

class SyllabsConfiguration
{
    /** @var ContentTypeService */
    protected $contentTypeService;

    /** @var TagsService */
    protected $tagsService;

    /** @var ConfigResolverInterface */
    protected $configResolver;

    /**
     * SyllabsConfiguration constructor.
     *
     * @param ContentTypeService $contentTypeService
     * @param TagsService $tagsService
     * @param ConfigResolverInterface $configResolver
     */
    public function __construct(
        ContentTypeService $contentTypeService,
        TagsService $tagsService,
        ConfigResolverInterface $configResolver
    ) {
        $this->contentTypeService = $contentTypeService;
        $this->tagsService = $tagsService;
        $this->configResolver = $configResolver;
    }

    protected function getRawConfigurations(): array
    {
        return $this->configResolver->getParameter('syllabs.config', 'almaviacx');
    }

    /**
     * @return ContentTypeConfiguration[]
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function getConfigurations(): array
    {
        $rawConfigurations = $this->getRawConfigurations();
        $configuration = [];
        foreach ($rawConfigurations as $rawConfiguration) {
            foreach ($rawConfiguration['content_type_identifiers'] as $contentTypeIdentifier) {
                $targetFields = [];
                foreach ($rawConfiguration['target_fields'] as $targetFieldType => $targetField) {
                    $parentTag = is_int($targetField['parent_tag']) ?
                        $this->tagsService->loadTag($targetField['parent_tag']) :
                        $this->tagsService->loadTagByRemoteId($targetField['parent_tag']);
                    $targetFields[] = new TargetFieldConfiguration(
                        $targetFieldType,
                        $targetField['field_identifier'],
                        $parentTag
                    );
                }

                $sourceFields = [];
                foreach ($rawConfiguration['source_fields'] as $sourceFieldType => $fieldsIdentifiers) {
                    $sourceFields[] = new SourceFieldConfiguration($sourceFieldType, $fieldsIdentifiers);
                }

                $configuration[$contentTypeIdentifier] = new ContentTypeConfiguration(
                    $contentTypeIdentifier,
                    $sourceFields,
                    $targetFields
                );
            }
        }
        return $configuration;
    }

    public function getUiConfiguration(): array
    {
        $configurations = $this->getConfigurations();
        $uiConfiguration = [];
        foreach ($configurations as $configuration) {
            $targetFields = [];
            foreach ($configuration->getTargetFields() as $targetField) {
                $targetFields[$targetField->getType()] = [
                    'fieldIdentfier' => $targetField->getFieldIdentifier(),
                    'parentTagId' => $targetField->getParentTag()->id
                ];
            }
            $sourceFields = [];
            foreach ($configuration->getSourceFields() as $sourceField) {
                $sourceFields[$sourceField->getType()] = $sourceField->getFieldsIdentifiers();
            }

            $uiConfiguration['contentTypes'][$configuration->getContentTypeIdentifier()] = [
                'sourceFields' => $sourceFields,
                'targetFields' => $targetFields
            ];
        }
        return $uiConfiguration;
    }

    public function getContentTypeConfiguration(string $contentTypeIdentifier): ?ContentTypeConfiguration
    {
        $configuration = $this->getConfigurations();
        return $configuration[$contentTypeIdentifier] ?? null;
    }
}
