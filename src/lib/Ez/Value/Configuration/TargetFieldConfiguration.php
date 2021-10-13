<?php

/**
 * @copyright Novactive
 * Date: 30/07/2021
 */

declare(strict_types=1);

namespace AlmaviaCX\Syllabs\Ez\Value\Configuration;

use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

class TargetFieldConfiguration
{
    /** @var string */
    protected $type;

    /** @var string */
    protected $fieldIdentifier;

    /** @var Tag */
    protected $parentTag;

    /** @var string */
    protected $subtype;

    /**
     * TargetFieldConfiguration constructor.
     *
     * @param string $type
     * @param string $fieldIdentifier
     * @param Tag    $parentTag
     * @param string $subtype
     */
    public function __construct(string $type, string $fieldIdentifier, Tag $parentTag, string $subtype)
    {
        $this->type            = $type;
        $this->fieldIdentifier = $fieldIdentifier;
        $this->parentTag       = $parentTag;
        $this->subtype         = $subtype;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getFieldIdentifier(): string
    {
        return $this->fieldIdentifier;
    }

    /**
     * @return Tag
     */
    public function getParentTag(): Tag
    {
        return $this->parentTag;
    }

    /**
     * @return string
     */
    public function getSubtype(): string
    {
        return $this->subtype;
    }
}
