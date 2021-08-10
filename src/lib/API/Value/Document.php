<?php

namespace AlmaviaCX\Syllabs\API\Value;

class Document implements \JsonSerializable
{
    /** @var int */
    protected $id;

    /** @var string[] */
    protected $title;

    /** @var string[] */
    protected $text;

    /** @var EntityAnnotation[] */
    protected $entities;

    /** @var ThemeAnnotation[] */
    protected $themes;

    /** @var WikitagAnnotation[] */
    protected $wikitags;

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

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string[]
     */
    public function getTitle(): array
    {
        return $this->title;
    }

    /**
     * @return string[]
     */
    public function getText(): array
    {
        return $this->text;
    }

    /**
     * @return EntityAnnotation[]
     */
    public function getEntities(): array
    {
        return $this->entities;
    }

    /**
     * @param EntityAnnotation[] $entities
     */
    public function setEntities(array $entities): void
    {
        $this->entities = $entities;
    }

    /**
     * @return ThemeAnnotation[]
     */
    public function getThemes(): array
    {
        return $this->themes;
    }

    /**
     * @param ThemeAnnotation[] $themes
     */
    public function setThemes(array $themes): void
    {
        $this->themes = $themes;
    }

    /**
     * @return WikitagAnnotation[]
     */
    public function getWikitags(): array
    {
        return $this->wikitags;
    }

    /**
     * @param WikitagAnnotation[] $wikitags
     */
    public function setWikitags(array $wikitags): void
    {
        $this->wikitags = $wikitags;
    }


    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return [
            'id'       => $this->id,
            'title'    => $this->title,
            'text'     => $this->text,
            'entities' => $this->entities,
            'themes'   => $this->themes,
            'wikitags' => $this->wikitags,
        ];
    }
}
