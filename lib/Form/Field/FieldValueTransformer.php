<?php

declare(strict_types=1);

namespace Netgen\IbexaFieldTypeEnhancedLink\Form\Field;

use Netgen\IbexaFieldTypeEnhancedLink\FieldType\Value;
use Symfony\Component\Form\DataTransformerInterface;

use function is_numeric;

class FieldValueTransformer implements DataTransformerInterface
{
    public function transform($value)
    {
        if (!$value instanceof Value) {
            return null;
        }

        return $value->destinationContentId ?? null;
    }

    public function reverseTransform($value): ?Value
    {
        if (!is_numeric($value)) {
            return null;
        }

        return new Value($value);
    }
}
