<?php

namespace AlmaviaCX\Syllabs\Ez\Service;

use AlmaviaCX\Syllabs\API\Service\ProcessService;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagCreateStruct;

class SuggestionService
{
    /** @var TagsService $tagsService */
    protected $tagsService;

    /**
     * SuggestionService constructor.
     *
     * @param TagsService $tagsService
     */
    public function __construct(TagsService $tagsService)
    {
        $this->tagsService = $tagsService;
    }

    public function createTag(string $keyword, int $parentTagId, string $language): Tag
    {
        $tags   = $this->tagsService->loadTagsByKeyword($keyword, $language);
        foreach ($tags as $tag) {
            if ($tag->parentTagId == $parentTagId) {
                return $tag;
            }
        }

        $tagCreateStruct = new TagCreateStruct();
        $tagCreateStruct->setKeyword($keyword, $language);
        $tagCreateStruct->parentTagId      = $parentTagId;
        $tagCreateStruct->mainLanguageCode = $language;
        return $this->tagsService->createTag($tagCreateStruct);
    }
}
