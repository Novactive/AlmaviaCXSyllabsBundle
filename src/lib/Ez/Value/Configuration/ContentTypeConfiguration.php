<?php

/**
 * @copyright Novactive
 * Date: 30/07/2021
 */

declare(strict_types=1);

namespace AlmaviaCX\Syllabs\Ez\Value\Configuration;

use eZ\Publish\API\Repository\Values\ContentType\ContentType;

class ContentTypeConfiguration
{
    /** @var string */
    protected $contentTypeIdentifier;

    /** @var SourceFieldConfiguration[] */
    protected $sourceFields;

    /** @var TargetFieldConfiguration[] */
    protected $targetFields;

    /**
     * ContentTypeConfiguration constructor.
     *
     * @param string                     $contentTypeIdentifier
     * @param SourceFieldConfiguration[] $sourceFields
     * @param TargetFieldConfiguration[] $targetFields
     */
    public function __construct(string $contentTypeIdentifier, array $sourceFields, array $targetFields)
    {
        $this->contentTypeIdentifier = $contentTypeIdentifier;
        $this->sourceFields = $sourceFields;
        $this->targetFields = $targetFields;
    }

    /**
     * @return string
     */
    public function getContentTypeIdentifier(): string
    {
        return $this->contentTypeIdentifier;
    }

    /**
     * @return SourceFieldConfiguration[]
     */
    public function getSourceFields(): array
    {
        return $this->sourceFields;
    }

    /**
     * @return TargetFieldConfiguration[]
     */
    public function getTargetFields(): array
    {
        return $this->targetFields;
    }
}
