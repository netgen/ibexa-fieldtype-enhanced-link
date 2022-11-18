<?php

declare(strict_types=1);

namespace Netgen\IbexaFieldTypeEnhancedLink\Form\Field;

use Netgen\IbexaFieldTypeEnhancedLink\FieldType\Value;
use Symfony\Component\Form\DataTransformerInterface;

use function array_key_exists;
use function is_array;

class FieldValueTransformer implements DataTransformerInterface
{
    /**
     * @var \Ibexa\Contracts\Core\Repository\FieldType
     */
    private $fieldType;

    /**
     * @param \Ibexa\Contracts\Core\Repository\FieldType $fieldType
     */
    public function __construct(\Ibexa\Contracts\Core\Repository\FieldType $fieldType)
    {
        $this->fieldType = $fieldType;
    }

    public function transform($value)
    {
        if (!$value instanceof Value) {
            return null;
        }
        if ($value->isExternal()) {
            return [
                'url' => $value->reference,
                'label_external' => $value->label,
                'target_external' => $value->target,
                'link_type' => 'external',
            ];
        }
        if ($value->isInternal()) {
            return [
                'suffix' => $value->suffix,
                'label_internal' => $value->label,
                'target_internal' => $value->target,
                'id' => $value->reference,
                'link_type' => 'internal',
            ];
        }

        return null;
    }

    public function reverseTransform($value): ?Value
    {
        if (is_array($value) && array_key_exists('link_type', $value)) {
            if ($value['link_type'] === 'internal') {
                if (isset($value['id'], $value['target_internal'])) {
                    return new Value($value['id'], $value['label_internal'], $value['target_internal'], $value['suffix']);
                }
            }
            if ($value['link_type'] === 'external') {
                if (isset($value['url'], $value['target_internal'])) {
                    return new Value($value['url'], $value['label_external'], $value['target_external']);
                }
            }
        }

        return $this->fieldType->getEmptyValue();
    }
}
