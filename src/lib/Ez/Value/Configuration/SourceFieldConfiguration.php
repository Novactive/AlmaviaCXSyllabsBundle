<?php
/**
 * @copyright Novactive
 * Date: 30/07/2021
 */

declare(strict_types=1);

namespace AlmaviaCX\Syllabs\Ez\Value\Configuration;


class SourceFieldConfiguration
{
    /** @var string */
    protected $type;

    /** @var string[] */
    protected $fieldsIdentifiers;

    /**
     * SourceFieldConfiguration constructor.
     *
     * @param string   $type
     * @param string[] $fieldsIdentifiers
     */
    public function __construct(string $type, array $fieldsIdentifiers)
    {
        $this->type = $type;
        $this->fieldsIdentifiers = $fieldsIdentifiers;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string[]
     */
    public function getFieldsIdentifiers(): array
    {
        return $this->fieldsIdentifiers;
    }
}
