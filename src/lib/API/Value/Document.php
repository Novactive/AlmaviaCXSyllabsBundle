<?php

namespace AlmaviaCX\Syllabs\API\Value;

class Document
{
    /** @var int */
    protected $id;

    /** @var string */
    protected $title;

    /** @var string */
    protected $text;

    /** @var EntityAnnotation[] */
    public $entities;

    /** @var ThemeAnnotation[] */
    public $themes;

    /** @var WikitagAnnotation[] */
    public $wikitags;

    /**
     * Document constructor.
     *
     * @param array $properties
     */
    public function __construct(array $properties = [])
    {
        foreach ($properties as $property => $value) {
            $this->$property = $value;
        }
    }

    public function __get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
    }

}
