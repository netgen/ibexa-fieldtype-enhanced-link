<?php

declare(strict_types=1);

namespace Netgen\IbexaFieldTypeEnhancedLink\FieldType;

use Ibexa\Core\FieldType\Value as BaseValue;

class Value extends BaseValue
{
    public const LinkTypeInternal = 'internal';
    public const LinkTypeExternal = 'external';

    public string $linkType;
    public $destinationContentId;

    /**
     * @noinspection MagicMethodsValidityInspection
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct($destinationContentId = null)
    {
        $this->destinationContentId = $destinationContentId;
    }

    public function isInternal(): bool
    {

    }

    public function isExternal(): bool
    {

    }

    public function __toString()
    {
        return (string)$this->destinationContentId;
    }
}
