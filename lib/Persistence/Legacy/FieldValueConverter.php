<?php

declare(strict_types=1);

namespace Netgen\IbexaFieldTypeEnhancedLink\Persistence\Legacy;

use Ibexa\Contracts\Core\Persistence\Content\FieldValue;
use Ibexa\Contracts\Core\Persistence\Content\Type\FieldDefinition;
use Ibexa\Core\Persistence\Legacy\Content\FieldValue\Converter;
use Ibexa\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use Ibexa\Core\Persistence\Legacy\Content\StorageFieldValue;

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
            $storageFieldValue->dataText = json_encode($value->data, JSON_THROW_ON_ERROR);
        }
        $storageFieldValue->sortKeyInt = (int) $value->sortKey;
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
        $fieldValue->sortKey = (int) $value->sortKeyInt;
    }

    /**
     * @throws \JsonException
     */
    public function toStorageFieldDefinition(FieldDefinition $fieldDef, StorageFieldDefinition $storageDef): void
    {
        $jsonToSave = [];
        $fieldSettings = $fieldDef->fieldTypeConstraints->fieldSettings;
        $selectionMethod = isset($fieldSettings['selectionMethod']) ? (int) $fieldSettings['selectionMethod'] : 0;
        $jsonToSave['selection_method'] = $selectionMethod;
        $jsonToSave['root_default_location'] = (bool) $fieldSettings['rootDefaultLocation'];
        if (!empty($fieldSettings['selectionRoot'])) {
            $jsonToSave['current_object_placement'] = (string) $fieldSettings['selectionRoot'];
        } else {
            $jsonToSave['current_object_placement'] = 0;
        }
        $jsonToSave['allowed_content_types'] = $fieldSettings['selectionContentTypes'];
        $jsonToSave['allowed_link_types'] = $fieldSettings['allowedLinkType'];
        $jsonToSave['allowed_targets'] = $fieldSettings['allowedTargets'];

        $storageDef->dataText5 = json_encode($jsonToSave, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);

        // BC: For Backwards Compatibility for legacy and in case of downgrades or data sharing
        // Selection method, 0 = browse, 1 = dropdown
        $storageDef->dataInt1 = $selectionMethod;
        // Selection root, location ID, or 0 if empty
        $storageDef->dataInt2 = (int) $fieldSettings['selectionRoot'];
    }

    /**
     * Converts field definition data in $storageDef into $fieldDef.
     *
     * @throws \JsonException
     */
    public function toFieldDefinition(StorageFieldDefinition $storageDef, FieldDefinition $fieldDef): void
    {
        // default settings
        // use dataInt1 and dataInt2 fields as default for backward compatibility
        $fieldDef->fieldTypeConstraints->fieldSettings = [
            'selectionMethod' => $storageDef->dataInt1,
            'selectionRoot' => $storageDef->dataInt2 === 0 ? '' : $storageDef->dataInt2,
            'rootDefaultLocation' => true,
            'selectionContentTypes' => [],
            'allowedTargets' => [],
            'allowedLinkType' => [],
        ];

        if (empty($storageDef->dataText5)) {
            return;
        }

        try {
            $decodedJson = json_decode($storageDef->dataText5, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return;
        }
        $fieldSettings = &$fieldDef->fieldTypeConstraints->fieldSettings;
        $fieldSettings['selectionMethod'] = $decodedJson['selection_method'];
        $fieldSettings['selectionRoot'] = $decodedJson['current_object_placement'];
        $fieldSettings['rootDefaultLocation'] = $decodedJson['root_default_location'];
        $fieldSettings['selectionContentTypes'] = $decodedJson['allowed_content_types'];
        $fieldSettings['allowedTargets'] = $decodedJson['allowed_targets'];
        $fieldSettings['allowedLinkType'] = $decodedJson['allowed_link_types'];
    }

    public function getIndexColumn(): string
    {
        return 'sort_key_int';
    }
}
