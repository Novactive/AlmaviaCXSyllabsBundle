<?php

namespace AlmaviaCX\Syllabs\API\Value;

class EntityAnnotation extends Annotation
{
    /** @var string */
    protected $type;

    /**
     * EntityAnnotation constructor.
     *
     * @param array $properties
     */
    public function __construct(array $properties = [])
    {
        foreach ($properties as $property => $value) {
            $this->$property = $value;
        }
    }
}
