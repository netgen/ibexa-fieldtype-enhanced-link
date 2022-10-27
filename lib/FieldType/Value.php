<?php

declare(strict_types=1);

namespace Netgen\IbexaFieldTypeEnhancedLink\FieldType;

use Ibexa\Core\FieldType\Value as BaseValue;

class Value extends BaseValue
{
    public ?int $destinationContentId;

    /**
     * @noinspection MagicMethodsValidityInspection
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct(?int $destinationContentId = null)
    {
        $this->destinationContentId = $destinationContentId;
    }

    public function __toString()
    {
        return (string)$this->destinationContentId;
    }
}
