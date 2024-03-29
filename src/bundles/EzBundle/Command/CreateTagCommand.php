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
use MCC\Bundle\CultureGouvBundle\Helper\TagsHelper;
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
     * @param TagsService          $tagsService
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
            ->addArgument('limit', InputArgument::OPTIONAL, 'batch limit', 100);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io          = new SymfonyStyle($input, $output);

        $contentTypeIdentifier = $input->getArgument('content_type');
        $contentType = $this->repository->getContentTypeService()->loadContentTypeByIdentifier($contentTypeIdentifier);
        $location    = $this->repository->getLocationService()->loadLocation($input->getArgument('parent_location_id'));

        $syllabsConfig = $this->syllabsConfiguration->getContentTypeConfiguration($contentType->identifier);
        $sourceFields  = $syllabsConfig->getSourceFields();
        $targetFields  = $syllabsConfig->getTargetFields();

        $query     = new Query();
        $criterion = new Query\Criterion\LogicalAnd(
            [
                new Query\Criterion\ContentTypeIdentifier($contentType->identifier),
                new Query\Criterion\Subtree($location->pathString)
            ]
        );

        $query->query = $criterion;
        $query->limit = (int) $input->getArgument('limit');

        $progressBar    = new ProgressBar($output);
        $searchService  = $this->repository->getSearchService();

        $io->text("Search {$contentType->getName()} content to update with Syllabs tags");

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

                $continue = true;
                $fields   = [];
                foreach ($targetFields as $targetField) {
                    if (empty($content->getFieldValue($targetField->getFieldIdentifier())->tags)) {
                        $continue = false;
                        $targetFieldIdentifier = $targetField->getFieldIdentifier();
                        $fields[$targetFieldIdentifier] = $content->getFieldValue($targetFieldIdentifier);
                    }
                }
                if ($continue) {
                    $progressBar->advance();
                    continue;
                }

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
                        $content,
                        $currentLanguage,
                        $syllabsDocs,
                        $targetFields,
                        $fields
                    ) {
                        $doc = $syllabsDocs[0];

                        $syllabsDoc = [
                            'entities' => $doc->getEntities(),
                            'themes'   => $doc->getThemes(),
                            'wikitags' => $doc->getWikitags()
                        ];

                        /** @var TargetFieldConfiguration $targetFieldConfig */
                        foreach ($targetFields as $targetFieldConfig) {
                            $tags = [];
                            $syllabsTags = $syllabsDoc[$targetFieldConfig->getType()];
                            if (isset($fields[$targetFieldConfig->getFieldIdentifier()])) {
                                foreach ($syllabsTags as $syllabsTag) {
                                    if (
                                        $targetFieldConfig->getSubtype() == "" ||
                                        $syllabsTag->type === $targetFieldConfig->getSubtype()
                                    ) {
                                        $tags[] = $this->suggestionService->createTag(
                                            $syllabsTag->text,
                                            $targetFieldConfig->getParentTag()->id,
                                            $currentLanguage
                                        );
                                    }
                                }
                                $field = $content->getField($targetFieldConfig->getFieldIdentifier());
                                $this->suggestionService->addTagsToContent($tags, $field, $content);
                            }
                        }
                    }
                );
                $progressBar->advance();
            }
            $query->offset += $query->limit;
        } while ($query->offset <= $searchResults->totalCount);
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
}
