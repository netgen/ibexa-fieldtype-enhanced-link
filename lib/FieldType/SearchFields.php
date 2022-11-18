<?php

declare(strict_types=1);

namespace Netgen\IbexaFieldTypeEnhancedLink\FieldType;

use Ibexa\Contracts\Core\FieldType\Indexable;
use Ibexa\Contracts\Core\Persistence\Content\Field;
use Ibexa\Contracts\Core\Persistence\Content\Type\FieldDefinition;
use Ibexa\Contracts\Core\Search;

use function array_key_exists;
use function is_int;
use function reset;

class SearchFields implements Indexable
{
    public function getIndexData(Field $field, FieldDefinition $fieldDefinition): array
    {
        if (isset($field->value->data) && array_key_exists('type', $field->value->data) && $field->value->data['type'] === Type::LINK_TYPE_INTERNAL) {
            if (array_key_exists('id', $field->value->data) && is_int($field->value->data['id'])) {
                $id = [$field->value->data['id']];

                return [
                    new Search\Field(
                        'value',
                        reset($id),
                        new Search\FieldType\StringField(),
                    ),
                ];
            }
        }

        return [];
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
