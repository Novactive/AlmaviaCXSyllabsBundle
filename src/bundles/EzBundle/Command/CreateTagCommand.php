<?php

namespace AlmaviaCX\Bundle\Syllabs\EzBundle\Command;

use AlmaviaCX\Syllabs\API\Service\ProcessService;
use AlmaviaCX\Syllabs\API\Value\Document;
use AlmaviaCX\Syllabs\Ez\Config\SyllabsConfiguration;
use AlmaviaCX\Syllabs\Ez\Service\SuggestionService;
use AlmaviaCX\Syllabs\Ez\Value\Configuration\TargetFieldConfiguration;
use eZ\Publish\Api\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct;
use eZ\Publish\Core\Base\Exceptions\ContentFieldValidationException;
use eZ\Publish\Core\Repository\SearchService;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\Core\FieldType\Tags\Value as TagsValue;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateTagCommand extends Command
{
    protected static $defaultName = 'almaviacx:syllabs:createtag';

    /** @var Repository */
    protected $repository;

    /** @var ProcessService */
    protected $processService;

    /** @var SuggestionService */
    protected $suggestionService;

    /** @var SyllabsConfiguration */
    protected $syllabsConfiguration;

    /** @var TagsService */
    protected $tagsService;

    /**
     * CreateTagCommand constructor.
     *
     * @param Repository           $repository
     * @param ProcessService       $processService
     * @param SuggestionService    $suggestionService
     * @param SyllabsConfiguration $syllabsConfiguration
     */
    public function __construct(
        Repository $repository,
        ProcessService $processService,
        SuggestionService $suggestionService,
        SyllabsConfiguration $syllabsConfiguration,
        TagsService $tagsService
    ) {
        $this->repository           = $repository;
        $this->processService       = $processService;
        $this->suggestionService    = $suggestionService;
        $this->syllabsConfiguration = $syllabsConfiguration;
        $this->tagsService          = $tagsService;

        parent::__construct(self::$defaultName);
    }

    protected function configure()
    {
        $this
            ->setName('almaviacx:syllabs:createtag')
            ->setAliases(['acx:s:ct', 'acx:syllabs:ct'])
            ->setDescription('Syllabs création de tags')
            ->addArgument('content_type', InputArgument::REQUIRED)
            ->addArgument('parent_location_id', InputArgument::REQUIRED)
            ->addArgument('limit', InputArgument::OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io          = new SymfonyStyle($input, $output);
        $contentType = $input->getArgument('content_type');

        $query     = new Query();
        $criterion = new Query\Criterion\LogicalAnd(
            [
                new Query\Criterion\ContentTypeIdentifier($contentType),
                new Query\Criterion\ParentLocationId($input->getArgument('parent_location_id'))
            ]
        );

        $query->query = $criterion;
        $query->limit = (int) $input->getArgument('limit');

        $progressBar    = new ProgressBar($output);
        $searchService  = $this->repository->getSearchService();
        $contentService = $this->repository->getContentService();

        $io->text("Search {$contentType} content to update with Syllabs tags");

        $syllabsConfig = $this->syllabsConfiguration->getContentTypeConfiguration($contentType);
        $sourceFields  = $syllabsConfig->getSourceFields();
        $targetFields  = $syllabsConfig->getTargetFields();

        $fieldDefinitionIdentifiers = ['tags', 'eztag_region', 'published_date', 'picture'];
        $this->repository->sudo(
            function () use (
                $contentType,
                $fieldDefinitionIdentifiers
            ) {
                $this->updateContentType($contentType, $fieldDefinitionIdentifiers, false);
            }
        );

        do {
            $searchResults = $searchService->findContent($query);
            if (0 === $query->offset) {
                $io->text("Found {$searchResults->totalCount} to export");
                $progressBar->start($searchResults->totalCount);
            }

            foreach ($searchResults->searchHits as $searchHit) {
                /** @var Content $content */
                $content         = $searchHit->valueObject;
                $currentLanguage = $content->versionInfo->initialLanguageCode;

                $doc['id'] = $content->id;
                foreach ($sourceFields as $sourceFieldConfiguration) {
                    $fieldContents = [];
                    foreach ($sourceFieldConfiguration->getFieldsIdentifiers() as $fieldsIdentifier) {
                        $fieldContents[] = $this->getFieldContent($content, $fieldsIdentifier);
                    }
                    $doc[$sourceFieldConfiguration->getType()] = $fieldContents;
                }

                $processDoc  = [
                    $content->id => new Document($doc)
                ];
                $syllabsDocs = $this->processService->process($processDoc);

                $this->repository->sudo(
                    function () use (
                        $contentService,
                        $content,
                        $currentLanguage,
                        $syllabsDocs,
                        $targetFields,
                        $io
                    ) {
                        $doc = $syllabsDocs[0];

                        $syllabsDoc = [
                            'entities' => $doc->getEntities(),
                            'themes'   => $doc->getThemes(),
                            'wikitags' => $doc->getWikitags()
                        ];

                        $contentDraft        = $contentService->createContentDraft($content->contentInfo);
                        $contentUpdateStruct = $contentService->newContentUpdateStruct();

                        $tags = [];
                        /** @var TargetFieldConfiguration $targetFieldConfiguration */
                        foreach ($targetFields as $targetFieldConfiguration) {
                            $syllabsTags = $syllabsDoc[$targetFieldConfiguration->getType()];
                            foreach ($syllabsTags as $annotation) {
                                $tags[] = $this->suggestionService->createTag($annotation->text,
                                                                              $targetFieldConfiguration->getParentTag(
                                                                              )->id,
                                                                              $currentLanguage
                                );
                            }
                            $tagsValue = new TagsValue($tags);
                            $contentUpdateStruct->setField($targetFieldConfiguration->getFieldIdentifier(), $tagsValue);
                        }

                        try {
                            $contentDraft = $contentService->updateContent(
                                $contentDraft->versionInfo,
                                $contentUpdateStruct
                            );
                            $content      = $contentService->publishVersion($contentDraft->versionInfo);
                        } catch (ContentFieldValidationException $e) {
                            $io->text("Error while updating content {$content->id} - {$content->getName()}");
                            echo"<pre>";print_r($e->getFieldErrors());echo"</pre>";
//                            foreach($e->getFieldErrors() as $errors) {
//                                foreach ($errors['fre-FR'] as $error) {
//                                    $io->error($error->getTranslatableMessage());
//                                }
//                            }
                        }
                    }
                );
                $progressBar->advance();
            }
            $query->offset += $query->limit;
        }
        while ($query->offset <= $searchResults->totalCount);

        $this->repository->sudo(
            function () use (
                $contentType,
                $fieldDefinitionIdentifiers
            ) {
                $this->updateContentType($contentType, $fieldDefinitionIdentifiers, true);
            }
        );

    }

    /**
     * @param Content $content
     * @param string  $fieldIdentifier
     *
     * @return string
     */
    protected function getFieldContent(Content $content, string $fieldIdentifier)
    {
        $fieldContent = "";
        $type         = get_class($content->getFieldValue($fieldIdentifier));

        if (strstr($type, 'TextLine')) {
            $fieldContent = $content->getFieldValue($fieldIdentifier)->text;
        } elseif (strstr($type, 'TextBlock')) {
            $fieldContent = $content->getFieldValue($fieldIdentifier)->text;
        } elseif (strstr($type, 'RichText')) {
            $fieldContent = strip_tags($content->getFieldValue($fieldIdentifier)->xml->saveXML());
        }

        return $fieldContent;
    }

    /**
     * @param string $contentTypeIdentifier
     * @param array  $fieldDefinitionIdentifiers
     * @param bool   $required
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    protected function updateContentType(
        string $contentTypeIdentifier,
        array $fieldDefinitionIdentifiers,
        bool $required
    ) {
        $contentTypeService = $this->repository->getContentTypeService();
        $contentType        = $contentTypeService->loadContentTypeByIdentifier($contentTypeIdentifier);
        $contentTypeDraft   = $contentTypeService->createContentTypeDraft($contentType);

        foreach ($fieldDefinitionIdentifiers as $fieldDefinitionIdentifier) {
            $fieldDefinition                         = $contentType->getFieldDefinition($fieldDefinitionIdentifier);
            $fieldDefinitionUpdateStruct             = $contentTypeService->newFieldDefinitionUpdateStruct();
            $fieldDefinitionUpdateStruct->isRequired = $required;
            $contentTypeService->updateFieldDefinition(
                $contentTypeDraft,
                $fieldDefinition,
                $fieldDefinitionUpdateStruct
            );
        }

        $contentTypeService->publishContentTypeDraft($contentTypeDraft);
    }

}