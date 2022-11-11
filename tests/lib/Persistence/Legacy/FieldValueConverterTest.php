<?php

declare(strict_types=1);

namespace Netgen\IbexaFieldTypeEnhancedLink\Tests\Unit\Persistence\Legacy;

use Ibexa\Contracts\Core\Persistence\Content\FieldTypeConstraints;
use Ibexa\Contracts\Core\Persistence\Content\FieldValue;
use Ibexa\Contracts\Core\Persistence\Content\Type\FieldDefinition as PersistenceFieldDefinition;
use Ibexa\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use Ibexa\Core\Persistence\Legacy\Content\StorageFieldValue;
use Netgen\IbexaFieldTypeEnhancedLink\FieldType\Type;
use Netgen\IbexaFieldTypeEnhancedLink\Persistence\Legacy\FieldValueConverter;
use PHPUnit\Framework\TestCase;

/**
 * @group converter
 */
class FieldValueConverterTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|\Ibexa\Core\Persistence\Legacy\Content\FieldValue\Converter\RelationConverter */
    protected $converter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->converter = new FieldValueConverter();
    }

    public function testToStorageFieldDefinition()
    {
        $fieldDefinition = new PersistenceFieldDefinition(
            [
                'fieldTypeConstraints' => new FieldTypeConstraints(
                    [
                        'fieldSettings' => [
                            'selectionMethod' => Type::SELECTION_DROPDOWN,
                            'selectionRoot' => 12345,
                            'rootDefaultLocation' => true,
                            'selectionContentTypes' => ['article', 'blog_post'],
                            'allowedLinkType' => [Type::ALLOWED_LINK_TYPE_EXTERNAL, Type::ALLOWED_LINK_TYPE_INTERNAL],
                            'allowedTargets' => [Type::ALLOWED_TARGET_LINK, Type::ALLOWED_TARGET_LINK_IN_NEW_TAB, Type::ALLOWED_TARGET_IN_PLACE, Type::ALLOWED_TARGET_MODAL],
                            'enableQueryParameter' => false,
                        ],
                    ],
                ),
            ],
        );

        $expectedStorageFieldDefinition = new StorageFieldDefinition();
        $expectedStorageFieldDefinition->dataText5 =
            <<<'DATATEXT'
            {
                "selectionMethod": 1,
                "selectionRoot": 12345,
                "rootDefaultLocation": true,
                "selectionContentTypes": [
                    "article",
                    "blog_post"
                ],
                "allowedLinkType": [
                    "external",
                    "internal"
                ],
                "allowedTargets": [
                    "link",
                    "link_new_tab",
                    "in_place",
                    "modal"
                ],
                "enableQueryParameter": false
            }
            DATATEXT;

        $actualStorageFieldDefinition = new StorageFieldDefinition();
        $this->converter->toStorageFieldDefinition($fieldDefinition, $actualStorageFieldDefinition);

        self::assertEquals(
            $expectedStorageFieldDefinition,
            $actualStorageFieldDefinition,
        );
    }

    public function testToFieldDefinition()
    {
        $storageFieldDefinition = new StorageFieldDefinition();
        $storageFieldDefinition->dataText5 =
            <<< 'DATATEXT'
            {
                "selectionMethod": 1,
                "selectionRoot": 12345,
                "rootDefaultLocation": true,
                "selectionContentTypes": [
                    "article",
                    "blog_post"
                ],
                "allowedLinkType": [
                    "external",
                    "internal"
                ],
                "allowedTargets": [
                    "link",
                    "link_new_tab",
                    "in_place",
                    "modal"
                ],
                "enableQueryParameter": false
            }
            DATATEXT;

        $expectedFieldDefinition = new PersistenceFieldDefinition();
        $expectedFieldDefinition->fieldTypeConstraints = new FieldTypeConstraints(
            [
                'fieldSettings' => [
                    'selectionMethod' => Type::SELECTION_DROPDOWN,
                    'selectionRoot' => 12345,
                    'selectionContentTypes' => ['article', 'blog_post'],
                    'rootDefaultLocation' => true,
                    'allowedLinkType' => [Type::ALLOWED_LINK_TYPE_EXTERNAL, Type::ALLOWED_LINK_TYPE_INTERNAL],
                    'allowedTargets' => [Type::ALLOWED_TARGET_LINK, Type::ALLOWED_TARGET_LINK_IN_NEW_TAB, Type::ALLOWED_TARGET_IN_PLACE, Type::ALLOWED_TARGET_MODAL],
                    'enableQueryParameter' => false,
                ],
            ],
        );

        $actualFieldDefinition = new PersistenceFieldDefinition();
        $this->converter->toFieldDefinition($storageFieldDefinition, $actualFieldDefinition);

        self::assertEquals($expectedFieldDefinition, $actualFieldDefinition);
    }

    public function testToFieldDefinitionWithDataText5Null()
    {
        $storageFieldDefinition = new StorageFieldDefinition();
        $storageFieldDefinition->dataText5 = null;

        $expectedFieldDefinition = new PersistenceFieldDefinition();
        $expectedFieldDefinition->fieldTypeConstraints = new FieldTypeConstraints(
            [
                'fieldSettings' => [
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
                ],
            ],
        );

        $actualFieldDefinition = new PersistenceFieldDefinition();
        $this->converter->toFieldDefinition($storageFieldDefinition, $actualFieldDefinition);

        self::assertEquals($expectedFieldDefinition, $actualFieldDefinition);
    }

    public function testToFieldDefinitionWithIncompleteFieldSettings()
    {
        $storageFieldDefinition = new StorageFieldDefinition();
        $storageFieldDefinition->dataText5 =
            <<< 'DATATEXT'
            {
                "selectionMethod": 1,
                "rootDefaultLocation": true,
                "selectionContentTypes": [
                    "article",
                    "blog_post"
                ],
                "allowedLinkType": [
                    "external"
                ],
                "enableQueryParameter": true
            }
            DATATEXT;

        $expectedFieldDefinition = new PersistenceFieldDefinition();
        $expectedFieldDefinition->fieldTypeConstraints = new FieldTypeConstraints(
            [
                'fieldSettings' => [
                    'selectionMethod' => Type::SELECTION_DROPDOWN,
                    'selectionRoot' => null,
                    'selectionContentTypes' => ['article', 'blog_post'],
                    'rootDefaultLocation' => true,
                    'allowedLinkType' => [Type::ALLOWED_LINK_TYPE_EXTERNAL],
                    'allowedTargets' => [Type::ALLOWED_TARGET_LINK, Type::ALLOWED_TARGET_LINK_IN_NEW_TAB, Type::ALLOWED_TARGET_IN_PLACE, Type::ALLOWED_TARGET_MODAL],
                    'enableQueryParameter' => true,
                ],
            ],
        );

        $actualFieldDefinition = new PersistenceFieldDefinition();
        $this->converter->toFieldDefinition($storageFieldDefinition, $actualFieldDefinition);

        self::assertEquals($expectedFieldDefinition, $actualFieldDefinition);
    }

    public function testToFieldDefinitionWithInvalidDataText5Format()
    {
        $this->expectException(\JsonException::class);

        $storageFieldDefinition = new StorageFieldDefinition();
        $storageFieldDefinition->dataText5 = 'String that is not in a valid json format';

        $fieldDefinition = new PersistenceFieldDefinition();
        $this->converter->toFieldDefinition($storageFieldDefinition, $fieldDefinition);
    }

    public function testToFieldValue()
    {
        $storageFieldValue = new StorageFieldValue();
        $storageFieldValue->dataText =
            <<< 'DATATEXT'
            {
                "id": 1,
                "label": "Enhanced link",
                "type": "internal",
                "target": "link",
                "suffix": "start"
            }
            DATATEXT;
        $storageFieldValue->sortKeyString = 'reference';

        $expectedFieldValue = new FieldValue();
        $expectedFieldValue->data = [
            'id' => 1,
            'label' => 'Enhanced link',
            'type' => 'internal',
            'target' => 'link',
            'suffix' => 'start',
        ];
        $expectedFieldValue->sortKey = 'reference';

        $actualFieldValue = new FieldValue();
        $this->converter->toFieldValue($storageFieldValue, $actualFieldValue);

        self::assertEquals($expectedFieldValue, $actualFieldValue);
    }

    public function testToFieldValueWithDataTextNull()
    {
        $storageFieldValue = new StorageFieldValue();
        $storageFieldValue->dataText = null;
        $storageFieldValue->sortKeyString = 'reference';

        $expectedFieldValue = new FieldValue();
        $expectedFieldValue->data = null;
        $expectedFieldValue->sortKey = 'reference';

        $actualFieldValue = new FieldValue();
        $this->converter->toFieldValue($storageFieldValue, $actualFieldValue);

        self::assertEquals($expectedFieldValue, $actualFieldValue);
    }

    public function testToFieldValueWithInvalidDataTextFormat()
    {
        $this->expectException(\JsonException::class);

        $storageFieldValue = new StorageFieldValue();
        $storageFieldValue->dataText = 'String that is not in a valid json format';

        $fieldValue = new FieldValue();
        $this->converter->toFieldValue($storageFieldValue, $fieldValue);
    }

    public function testToStorageValue()
    {
        $fieldValue = new FieldValue();
        $fieldValue->data = [
            'id' => 1,
            'label' => 'Enhanced link',
            'type' => 'internal',
            'target' => 'link',
            'suffix' => 'start',
        ];
        $fieldValue->sortKey = 'reference';

        $expectedStorageFieldValue = new StorageFieldValue();
        $expectedStorageFieldValue->dataText =
            <<< 'DATATEXT'
            {
                "id": 1,
                "label": "Enhanced link",
                "type": "internal",
                "target": "link",
                "suffix": "start"
            }
            DATATEXT;
        $expectedStorageFieldValue->sortKeyString = 'reference';

        $actualStorageFieldValue = new StorageFieldValue();
        $this->converter->toStorageValue($fieldValue, $actualStorageFieldValue);

        self::assertEquals($expectedStorageFieldValue, $actualStorageFieldValue);
    }

    public function testToStorageValueWithIdNull()
    {
        $fieldValue = new FieldValue();
        $fieldValue->data = [
            'id' => null,
            'label' => 'Enhanced link',
            'type' => 'external',
            'target' => 'link',
            'suffix' => 'start',
        ];
        $fieldValue->sortKey = 'reference';

        $expectedStorageFieldValue = new StorageFieldValue();
        $expectedStorageFieldValue->dataText = null;
        $expectedStorageFieldValue->sortKeyString = 'reference';

        $actualStorageFieldValue = new StorageFieldValue();
        $this->converter->toStorageValue($fieldValue, $actualStorageFieldValue);

        self::assertEquals($expectedStorageFieldValue, $actualStorageFieldValue);
    }

    public function testGetIndexColumn()
    {
        $expectedIndexColumn = 'sort_key_string';

        $actualIndexColumn = $this->converter->getIndexColumn();

        self::assertEquals($expectedIndexColumn, $actualIndexColumn);
    }
}
