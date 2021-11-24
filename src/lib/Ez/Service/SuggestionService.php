<?php

namespace AlmaviaCX\Syllabs\Ez\Service;

use AlmaviaCX\Syllabs\API\Service\ProcessService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Field;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Netgen\TagsBundle\API\Repository\Values\Tags\TagCreateStruct;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

class SuggestionService
{
    /** @var TagsService $tagsService */
    protected $tagsService;

    /** @var \Doctrine\DBAL\Connection */
    protected $dbHandler;

    /** @var TagAwareAdapterInterface $cache */
    protected $cache;

    /** @var \eZ\Publish\SPI\Persistence\Handler $persistenceHandler */
    protected $persistenceHandler;

    /** @var \eZ\Publish\SPI\Search\Handler $searchHandler */
    protected $searchHandler;

    /**
     * SuggestionService constructor.
     *
     * @param TagsService                         $tagsService
     * @param \Doctrine\DBAL\Connection           $dbHandler
     * @param TagAwareAdapterInterface            $cache
     * @param \eZ\Publish\SPI\Persistence\Handler $persistenceHandler
     * @param \eZ\Publish\SPI\Search\Handler      $searchHandler
     */
    public function __construct(
        TagsService $tagsService,
        \Doctrine\DBAL\Connection $dbHandler,
        TagAwareAdapterInterface $cache,
        \eZ\Publish\SPI\Persistence\Handler $persistenceHandler,
        \eZ\Publish\SPI\Search\Handler $searchHandler
    ) {
        $this->tagsService = $tagsService;
        $this->dbHandler = $dbHandler;
        $this->cache = $cache;
        $this->persistenceHandler = $persistenceHandler;
        $this->searchHandler = $searchHandler;
    }

    public function createTag(string $keyword, int $parentTagId, string $language, bool $visible = false): Tag
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
        $tagCreateStruct->visible = $visible;
        return $this->tagsService->createTag($tagCreateStruct);
    }

    public function addTagsToContent(array $tags, Field $field, Content $content)
    {
        $qb    = $this->dbHandler->createQueryBuilder();
        /** @var Tag $tag */
        foreach ($tags as $tag) {
            $query = $qb
                ->insert('eztags_attribute_link')
                ->setValue('keyword_id', '?')
                ->setValue('objectattribute_id', '?')
                ->setValue('objectattribute_version', '?')
                ->setValue('object_id', '?')
                ->setValue('priority', '?')
                ->setParameter(0, $tag->id)
                ->setParameter(1, $field->id)
                ->setParameter(2, $content->versionInfo->versionNo)
                ->setParameter(3, $content->id)
                ->setParameter(4, 0);
            $query->execute();
        }

        $this->cache->invalidateTags(['content-'.$content->id]);
        $this->searchHandler->indexContent(
            $this->persistenceHandler->contentHandler()->load($content->id, $content->getVersionInfo()->versionNo)
        );
    }
}
