<?php

namespace AlmaviaCX\Syllabs\API\Value;

class Annotation
{
    /** @var float */
    protected $score;

    /** @var string */
    protected $text;

    /**
     * Annotation constructor.
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
