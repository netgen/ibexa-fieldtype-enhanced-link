<?php

namespace Netgen\IbexaFieldTypeEnhancedLink\Tests\Unit\Persistence\Legacy;

use Ibexa\Contracts\Core\Persistence\Content\FieldTypeConstraints;
use Ibexa\Contracts\Core\Persistence\Content\Type\FieldDefinition as PersistenceFieldDefinition;
use Ibexa\Core\FieldType\RelationList\Type;
use Ibexa\Core\Persistence\Legacy\Content\FieldValue\Converter\RelationConverter;
use Ibexa\Core\Persistence\Legacy\Content\StorageFieldDefinition;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Ibexa\Core\Persistence\Legacy\Content\FieldValue\Converter\RelationConverter
 */
class FieldValueConverterTest extends TestCase
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|\Ibexa\Core\Persistence\Legacy\Content\FieldValue\Converter\RelationConverter */
    protected $converter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->converter = new RelationConverter();
    }

    /**
     * @group fieldType
     * @group relationlist
     */
    public function testToStorageFieldDefinition()
    {
        $fieldDefinition = new PersistenceFieldDefinition(
            [
                'fieldTypeConstraints' => new FieldTypeConstraints(
                    [
                        'fieldSettings' => [
                            'selectionMethod' => Type::SELECTION_BROWSE,
                            'selectionRoot' => 12345,
                            'selectionContentTypes' => ['article', 'blog_post'],
                            'rootDefaultLocation' => true,
                        ],
                    ]
                ),
            ]
        );

        $expectedStorageFieldDefinition = new StorageFieldDefinition();
        $expectedStorageFieldDefinition->dataText5 = <<<EOT
<?xml version="1.0" encoding="utf-8"?>
<related-objects><constraints><allowed-class contentclass-identifier="article"/><allowed-class contentclass-identifier="blog_post"/></constraints><selection_type value="0"/><root_default_location value="1"/><contentobject-placement node-id="12345"/></related-objects>

EOT;
        // For BC these are still set
        $expectedStorageFieldDefinition->dataInt1 = 0;
        $expectedStorageFieldDefinition->dataInt2 = 12345;

        $actualStorageFieldDefinition = new StorageFieldDefinition();

        $this->converter->toStorageFieldDefinition($fieldDefinition, $actualStorageFieldDefinition);

        $this->assertEquals(
            $expectedStorageFieldDefinition,
            $actualStorageFieldDefinition
        );
    }
}
