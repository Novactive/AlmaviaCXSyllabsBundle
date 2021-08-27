<?php

namespace AlmaviaCX\Syllabs\Ez\Service;

use AlmaviaCX\Syllabs\API\Service\ProcessService;
use Netgen\TagsBundle\API\Repository\TagsService;
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

    public function createTag(string $keyword, string $language, int $parentTagId)
    {
        $newTag = null;
        $tags   = $this->tagsService->loadTagsByKeyword($keyword, $language);
        foreach ($tags as $tag) {
            if ($tag->parentTagId == $parentTagId) {
                $newTag = $tag;
            }
        }

        if (is_null($newTag)) {
            $tagCreateStruct = new TagCreateStruct();
            $tagCreateStruct->setKeyword($keyword, 'fre-FR');
            $tagCreateStruct->parentTagId      = $parentTagId;
            $tagCreateStruct->mainLanguageCode = 'fre-FR';
            $newTag                            = $this->tagsService->createTag($tagCreateStruct);
        }

        return [
            'id'          => $newTag->id,
            'parentTagId' => $newTag->parentTagId,
            'keywords'    => $newTag->keywords
        ];
    }

}