<?php

declare(strict_types=1);

namespace Netgen\IbexaFieldTypeEnhancedLink\FieldType;

use Ibexa\Core\FieldType\Value as BaseValue;

use function is_int;
use function is_string;

class Value extends BaseValue
{
    public $link;
    public ?string $text;

    /**
     * @noinspection MagicMethodsValidityInspection
     * @noinspection PhpMissingParentConstructorInspection
     *
     * @param mixed|null $link
     */
    public function __construct($link = null, ?string $text = null)
    {
        // promjeni text u labelu, dodaj konstante za opciju modala
        $this->link = $link;
        $this->text = $text;
    }

    public function __toString()
    {
        if (is_string($this->link)) {
            return $this->link;
        }
        if (is_int($this->link)) {
            return (string) ($this->link);
        }

        return '';
    }
}
