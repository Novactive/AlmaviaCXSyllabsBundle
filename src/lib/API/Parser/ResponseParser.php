<?php

namespace AlmaviaCX\Syllabs\API\Parser;

use AlmaviaCX\Syllabs\API\Value\Document;
use AlmaviaCX\Syllabs\API\Value\EntityAnnotation;
use AlmaviaCX\Syllabs\API\Value\ThemeAnnotation;
use AlmaviaCX\Syllabs\API\Value\WikitagAnnotation;

class ResponseParser
{
    /**
     * @param array $resultDoc
     * @param Document[] $documents
     *
     * @return array
     */
    public function parseDocuments(array $resultDoc, array $documents): array
    {
        $newDocs = [];
        foreach ($resultDoc as $result) {
            $newDocs[] = $this->parseResults($result['full_text'], $documents[$result['id']]);
        }

        return $newDocs;
    }

    /**
     * @param array $results
     * @param Document $currentDoc
     *
     * @return Document
     */
    public function parseResults(array $results, $currentDoc): Document
    {
        $currentDoc->entities = $this->parseAnnotations(
            $results['entities'],
            EntityAnnotation::class,
            [
                'text'  => 'text',
                'score' => 'score',
                'type'  => 'type'
            ]
        );

        $currentDoc->themes = $this->parseAnnotations(
            $results['themes'],
            ThemeAnnotation::class,
            [
                'name'  => 'text',
                'score' => 'score'
            ]
        );

        $currentDoc->wikitags = $this->parseAnnotations(
            $results['wikitags'],
            WikitagAnnotation::class,
            [
                'name'  => 'text',
                'score' => 'score',
                'url'   => 'url'
            ]
        );

        return $currentDoc;
    }

    /**
     * @param array  $rawAnnotations
     * @param string $className
     * @param array  $fieldsMap
     *
     * @return array
     */
    public function parseAnnotations(
        array $rawAnnotations,
        string $className,
        array $fieldsMap
    ): array {
        $annotations = [];
        foreach ($rawAnnotations as $rawAnnotation) {
            $annotations[] = $this->parseValue(
                $rawAnnotation,
                $className,
                $fieldsMap
            );
        }

        return $annotations;
    }

    /**
     * @param array  $rawValue
     * @param string $className
     * @param array  $fieldsMap
     *
     * @return mixed
     */
    public function parseValue(
        array $rawValue,
        string $className,
        array $fieldsMap
    ) {
        $fields = [];
        foreach ($fieldsMap as $source => $target) {
            $fields[$target] = $rawValue[$source];
        }

        return new $className($fields);
    }

}
