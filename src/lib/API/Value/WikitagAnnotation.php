<?php

namespace AlmaviaCX\Syllabs\API\Value;

class WikitagAnnotation extends Annotation
{
    /** @var string */
    protected $url;

    /**
     * WikitagAnnotation constructor.
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
