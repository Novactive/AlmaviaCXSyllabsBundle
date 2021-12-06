<?php

/**
 * @copyright Novactive
 * Date: 03/12/2021
 */

declare(strict_types=1);

namespace AlmaviaCX\Syllabs\Ez\Value\Configuration;

use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;

class BlacklistConfiguration
{
    /** @var Tag */
    protected $parentTag;

    /** @var string */
    protected $subtype;

    /** @var string */
    protected $type;

    /**
     * BlacklistConfiguration constructor.
     *
     * @param Tag    $parentTag
     * @param string $subtype
     * @param string $type
     */
    public function __construct(Tag $parentTag, string $type, string $subtype = '')
    {
        $this->parentTag = $parentTag;
        $this->subtype = $subtype;
        $this->type = $type;
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

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
}
