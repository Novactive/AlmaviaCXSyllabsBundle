<?php

namespace AlmaviaCX\Syllabs\API\Value;

class ThemeAnnotation extends Annotation
{
    /**
     * ThemeAnnotation constructor.
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
