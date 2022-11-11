<?php

declare(strict_types=1);

namespace Netgen\IbexaFieldTypeEnhancedLink\Persistence\Legacy;

use Ibexa\Contracts\Core\Persistence\Content\FieldValue;
use Ibexa\Contracts\Core\Persistence\Content\Type\FieldDefinition;
use Ibexa\Core\Persistence\Legacy\Content\FieldValue\Converter;
use Ibexa\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use Ibexa\Core\Persistence\Legacy\Content\StorageFieldValue;
use Netgen\IbexaFieldTypeEnhancedLink\FieldType\Type;

use function json_decode;
use function json_encode;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;

class FieldValueConverter implements Converter
{
    /**
     * @throws \JsonException
     */
    public function toStorageValue(FieldValue $value, StorageFieldValue $storageFieldValue): void
    {
        if (empty($value->data['id'])) {
            $storageFieldValue->dataText = null;
        } else {
            $storageFieldValue->dataText = json_encode($value->data, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
        }
        $storageFieldValue->sortKeyString = (string) $value->sortKey;
    }

    /**
     * @throws \JsonException
     */
    public function toFieldValue(StorageFieldValue $value, FieldValue $fieldValue): void
    {
        if ($value->dataText) {
            $fieldValue->data = json_decode($value->dataText, true, 512, JSON_THROW_ON_ERROR);
        } else {
            $fieldValue->data = null;
        }
        $fieldValue->sortKey = (string) $value->sortKeyString;
    }

    /**
     * @throws \JsonException
     */
    public function toStorageFieldDefinition(FieldDefinition $fieldDef, StorageFieldDefinition $storageDef): void
    {
        $storageDef->dataText5 = json_encode($fieldDef->fieldTypeConstraints->fieldSettings, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
    }

    /**
     * Converts field definition data in $storageDef into $fieldDef.
     *
     * @throws \JsonException
     */
    public function toFieldDefinition(StorageFieldDefinition $storageDef, FieldDefinition $fieldDef): void
    {
        $fieldDef->fieldTypeConstraints->fieldSettings = [
            'selectionMethod' => Type::SELECTION_BROWSE,
            'selectionRoot' => null,
            'rootDefaultLocation' => true,
            'selectionContentTypes' => [],
            'allowedTargets' => [
                Type::ALLOWED_TARGET_LINK,
                Type::ALLOWED_TARGET_LINK_IN_NEW_TAB,
                Type::ALLOWED_TARGET_IN_PLACE,
                Type::ALLOWED_TARGET_MODAL,
            ],
            'allowedLinkType' => [
                Type::ALLOWED_LINK_TYPE_EXTERNAL,
                Type::ALLOWED_LINK_TYPE_INTERNAL,
            ],
            'enableQueryParameter' => false,
        ];

        if (empty($storageDef->dataText5)) {
            return;
        }
        $decodedJson = json_decode($storageDef->dataText5, true, 512, JSON_THROW_ON_ERROR);

        $fieldSettings = &$fieldDef->fieldTypeConstraints->fieldSettings;
        $fieldSettings['selectionMethod'] = $decodedJson['selectionMethod'];
        $fieldSettings['selectionRoot'] = $decodedJson['selectionRoot'];
        $fieldSettings['rootDefaultLocation'] = $decodedJson['rootDefaultLocation'];
        $fieldSettings['selectionContentTypes'] = $decodedJson['selectionContentTypes'];
        $fieldSettings['allowedTargets'] = $decodedJson['allowedTargets'];
        $fieldSettings['allowedLinkType'] = $decodedJson['allowedLinkType'];
        $fieldSettings['enableQueryParameter'] = $decodedJson['enableQueryParameter'];
    }

    public function getIndexColumn(): string
    {
        return 'sort_key_string';
    }
}
