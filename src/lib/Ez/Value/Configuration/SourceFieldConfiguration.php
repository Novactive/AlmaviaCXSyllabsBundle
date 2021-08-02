<?php
/**
 * @copyright Novactive
 * Date: 30/07/2021
 */

declare(strict_types=1);

namespace AlmaviaCX\Syllabs\Ez\Value\Configuration;


class SourceFieldConfiguration
{
    /** @var string[] */
    protected $title;

    /** @var string[] */
    protected $text;

    /**
     * SourceFieldConfiguration constructor.
     *
     * @param string[] $title
     * @param string[] $text
     */
    public function __construct(array $title, array $text)
    {
        $this->title = $title;
        $this->text = $text;
    }

    /**
     * @return array
     */
    public function getTitle(): array
    {
        return $this->title;
    }

    /**
     * @return array
     */
    public function getText(): array
    {
        return $this->text;
    }
}
