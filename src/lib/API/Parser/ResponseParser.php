<?php

namespace AlmaviaCX\Syllabs\API\Parser;

use AlmaviaCX\Syllabs\API\Value\EntityAnnotation;
use AlmaviaCX\Syllabs\API\Value\ThemeAnnotation;
use AlmaviaCX\Syllabs\API\Value\WikitagAnnotation;

class ResponseParser
{
    /**
     * @param array $documents
     *
     * @return array
     */
    public function parseDocuments(array $documents): array
    {
        $results = [];
        foreach ($documents as $document) {
            $result['id']          = $document['id'];
            $result['annotations'] = $this->parseResults($document['full_text']);
            $results[]             = $result;
        }

        return $results;
    }

    /**
     * @param array $results
     *
     * @return array
     */
    public function parseResults(array $results): array
    {
        $entities = $this->parseAnnotations(
            $results['entities'],
            EntityAnnotation::class,
            [
                'text'  => 'text',
                'score' => 'score',
                'type'  => 'type'
            ]
        );

        $themes = $this->parseAnnotations(
            $results['themes'],
            ThemeAnnotation::class,
            [
                'name'  => 'text',
                'score' => 'score'
            ]
        );

        $wikitags = $this->parseAnnotations(
            $results['wikitags'],
            WikitagAnnotation::class,
            [
                'name'  => 'text',
                'score' => 'score',
                'url'   => 'url'
            ]
        );

        return [
            'entities' => $entities,
            'themes'   => $themes,
            'wikitags' => $wikitags
        ];
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
