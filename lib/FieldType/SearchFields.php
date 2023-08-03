<?php

declare(strict_types=1);

namespace Netgen\IbexaFieldTypeEnhancedLink\FieldType;

use eZ\Publish\SPI\FieldType\Indexable;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\SPI\Search;
use eZ\Publish\SPI\Search\FieldType\StringField;

use function is_int;

class SearchFields implements Indexable
{
    public function getIndexData(Field $field, FieldDefinition $fieldDefinition): array
    {
        $type = $field->value->data['type'] ?? null;
        $id = $field->value->data['id'] ?? null;

        if ($type === Type::LINK_TYPE_INTERNAL && is_int($id)) {
            return [
                new Search\Field(
                    'value',
                    $id,
                    new StringField(),
                ),
            ];
        }

        return [];
    }

    public function getIndexDefinition(): array
    {
        return [
            'value' => new StringField(),
        ];
    }

    public function getDefaultMatchField(): string
    {
        return 'value';
    }

    public function getDefaultSortField(): string
    {
        return $this->getDefaultMatchField();
    }
}
