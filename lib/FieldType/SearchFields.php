<?php

declare(strict_types=1);

namespace Netgen\IbexaFieldTypeEnhancedLink\FieldType;

use Ibexa\Contracts\Core\FieldType\Indexable;
use Ibexa\Contracts\Core\Persistence\Content\Field;
use Ibexa\Contracts\Core\Persistence\Content\Type\FieldDefinition;
use Ibexa\Contracts\Core\Search;

class SearchFields implements Indexable
{
    public function getIndexData(Field $field, FieldDefinition $fieldDefinition): array
    {
        return [
            new Search\Field(
                'value',
                reset($field->value->data),
                new Search\FieldType\StringField()
            ),
        ];
    }

    public function getIndexDefinition(): array
    {
        return [
            'value' => new Search\FieldType\StringField(),
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
