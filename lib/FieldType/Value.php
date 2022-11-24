<?php

declare(strict_types=1);

namespace Netgen\IbexaFieldTypeEnhancedLink\FieldType;

use Ibexa\Core\FieldType\Value as BaseValue;

use function is_int;
use function is_string;

class Value extends BaseValue
{
    public $reference;
    public ?string $label;
    public string $target;
    public ?string $suffix;

    /**
     * @noinspection MagicMethodsValidityInspection
     * @noinspection PhpMissingParentConstructorInspection
     *
     * @param ?int|?string $reference
     */
    public function __construct(
        $reference = null,
        ?string $label = null,
        string $target = Type::TARGET_LINK,
        ?string $suffix = null
    ) {
        $this->reference = $reference;
        $this->label = $label;
        $this->target = $target;
        $this->suffix = $suffix;
    }

    public function __toString()
    {
        if (is_string($this->reference)) {
            return $this->reference;
        }

        if (is_int($this->reference)) {
            return (string) $this->reference;
        }

        return '';
    }

    public function isTypeExternal(): bool
    {
        return is_string($this->reference);
    }

    public function isTypeInternal(): bool
    {
        return is_int($this->reference);
    }

    public function isTargetModal(): bool
    {
        return $this->target === Type::TARGET_MODAL;
    }

    public function isTargetEmbed(): bool
    {
        return $this->target === Type::TARGET_EMBED;
    }

    public function isTargetLink(): bool
    {
        return $this->target === Type::TARGET_LINK;
    }

    public function isTargetLinkInNewTab(): bool
    {
        return $this->target === Type::TARGET_LINK_IN_NEW_TAB;
    }
}
