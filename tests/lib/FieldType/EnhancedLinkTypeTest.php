<?php

declare(strict_types=1);

namespace Netgen\IbexaFieldTypeEnhancedLink\Tests\Unit\FieldType;

use Http\Discovery\Exception\NotFoundException;
use Ibexa\Contracts\Core\Exception\InvalidArgumentType;
use Ibexa\Contracts\Core\FieldType\Value as SPIValue;
use Ibexa\Contracts\Core\Persistence\Content\Handler as SPIContentHandler;
use Ibexa\Contracts\Core\Persistence\Content\VersionInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Relation;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Contracts\Core\Repository\Values\ValueObject;
use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Core\FieldType\ValidationError;
use Ibexa\Core\Repository\Validator\TargetContentValidatorInterface;
use Ibexa\Tests\Core\FieldType\FieldTypeTest;
use Netgen\IbexaFieldTypeEnhancedLink\FieldType\TargetContentValidator;
use Netgen\IbexaFieldTypeEnhancedLink\FieldType\Type;
use Netgen\IbexaFieldTypeEnhancedLink\FieldType\Value;
use function PHPUnit\Framework\stringContains;
use Ibexa\Contracts\Core\Persistence\Content\FieldValue;

/**
 * @group type
 */
class EnhancedLinkTypeTest extends FieldTypeTest
{
    private const DESTINATION_CONTENT_ID = 14;

    private $contentHandler;

    /** @var \Ibexa\Core\Repository\Validator\TargetContentValidatorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $targetContentValidator;

    protected function setUp(): void
    {
        parent::setUp();

        $versionInfo = new VersionInfo([
            'versionNo' => 24,
            'names' => [
                'en_GB' => 'name_en_GB',
                'de_DE' => 'Name_de_DE',
            ],
        ]);
        $currentVersionNo = 28;
        $destinationContentInfo = $this->createMock(ContentInfo::class);
        $destinationContentInfo
            ->method('__get')
            ->willReturnMap([
                ['currentVersionNo', $currentVersionNo],
                ['mainLanguageCode', 'en_GB'],
            ]);

        $this->contentHandler = $this->createMock(SPIContentHandler::class);
        $this->contentHandler
            ->method('loadContentInfo')
            ->with(self::DESTINATION_CONTENT_ID)
            ->willReturn($destinationContentInfo);

        $this->contentHandler
            ->method('loadVersionInfo')
            ->with(self::DESTINATION_CONTENT_ID, $currentVersionNo)
            ->willReturn($versionInfo);

        $this->targetContentValidator = $this->createMock(TargetContentValidator::class);
    }

    protected function createFieldTypeUnderTest(): Type
    {
        $fieldType = new Type(
            $this->contentHandler,
            $this->targetContentValidator
        );
        $fieldType->setTransformationProcessor($this->getTransformationProcessorMock());

        return $fieldType;
    }

    /**
     * Returns the validator configuration schema expected from the field type.
     *
     * @return array
     */
    protected function getValidatorConfigurationSchemaExpectation(): array
    {
        return [];
    }

    protected function getSettingsSchemaExpectation(): array
    {
        return [
            'selectionMethod' => [
                'type' => 'int',
                'default' => Type::SELECTION_BROWSE,
            ],
            'selectionRoot' => [
                'type' => 'string',
                'default' => null,
            ],
            'rootDefaultLocation' => [
                'type' => 'bool',
                'default' => true,
            ],
            'selectionContentTypes' => [
                'type' => 'array',
                'default' => [],
            ],
            'allowedLinkType' => [
                'type' => 'array',
                'default' => [
                    Type::ALLOWED_LINK_TYPE_EXTERNAL,
                    Type::ALLOWED_LINK_TYPE_INTERNAL,
                ],
            ],
            'allowedTargets' => [
                'type' => 'array',
                'default' => [
                    Type::ALLOWED_TARGET_LINK,
                    Type::ALLOWED_TARGET_LINK_IN_NEW_TAB,
                    Type::ALLOWED_TARGET_IN_PLACE,
                    Type::ALLOWED_TARGET_MODAL,
                ],
            ],
            'enableQueryParameter' => [
                'type' => 'bool',
                'default' => false,
            ],
        ];
    }

    protected function getEmptyValueExpectation(): Value
    {
        return new Value();
    }

    public function provideInvalidInputForAcceptValue(): array
    {
        return [
            [
                true,
                InvalidArgumentException::class,
            ],
        ];
    }

    public function provideValidInputForAcceptValue(): array
    {
        return [
            [
                new Value(),
                new Value(),
            ],
            [
                23,
                new Value(23),
            ],
            [
                new ContentInfo(['id' => 23]),
                new Value(23),
            ],
        ];
    }

    public function provideInputForToHash(): array
    {
        return [
            [
                new Value(23, 'test', 'link', null),
                [
                    'reference' => 23,
                    'label' => 'test',
                    'target' => 'link',
                    'suffix' => null
                ],
            ],
            [
                new Value(),
                [
                    'reference' => null,
                    'label' => null,
                    'target' => Value::DEFAULT_TARGET,
                    'suffix' => null,
                ],
            ],
        ];
    }

    public function provideInputForFromHash(): array
    {
        return [
            [
                [
                    'reference' => 23,
                    'label' => 'test',
                    'target' => 'link',
                    'suffix' => null
                ],
                new Value(23, 'test', 'link', null),
            ],
            [
                [
                    'reference' => null,
                    'label' => null,
                    'target' => Value::DEFAULT_TARGET,
                    'suffix' => null,
                ],
                new Value(),
            ],
        ];
    }

    public function provideValidFieldSettings(): array
    {
        return [
            [
                [
                    'selectionMethod' => Type::SELECTION_BROWSE,
                    'selectionRoot' => null,
                    'rootDefaultLocation' => true,
                    'selectionContentTypes' => [],
                    'allowedLinkType' => [
                        Type::ALLOWED_LINK_TYPE_EXTERNAL,
                        Type::ALLOWED_LINK_TYPE_INTERNAL,
                    ],
                    'allowedTargets' => [
                        Type::ALLOWED_TARGET_LINK,
                        Type::ALLOWED_TARGET_LINK_IN_NEW_TAB,
                        Type::ALLOWED_TARGET_IN_PLACE,
                        Type::ALLOWED_TARGET_MODAL,
                    ],
                    'enableQueryParameter' => false,
                ]
            ],
            [
                [
                    'selectionMethod' => Type::SELECTION_BROWSE,
                    'selectionRoot' => null,
                    'rootDefaultLocation' => true,
                    'selectionContentTypes' => [],
                    'allowedLinkType' => [
                        Type::ALLOWED_LINK_TYPE_EXTERNAL,
                    ],
                    'allowedTargets' => [
                        Type::ALLOWED_TARGET_IN_PLACE,
                        Type::ALLOWED_TARGET_MODAL,
                    ],
                    'enableQueryParameter' => true,
                ]
            ],
        ];
    }

    public function provideInValidFieldSettings(): array
    {
        return [
            [
                // Unknown key
                [
                    'unknownKey' => Type::SELECTION_BROWSE,
                    'selectionRoot' => null,
                    'rootDefaultLocation' => true,
                    'selectionContentTypes' => [],
                    'allowedLinkType' => [
                        Type::ALLOWED_LINK_TYPE_EXTERNAL,
                        Type::ALLOWED_LINK_TYPE_INTERNAL,
                    ],
                    'allowedTargets' => [
                        Type::ALLOWED_TARGET_LINK,
                        Type::ALLOWED_TARGET_LINK_IN_NEW_TAB,
                        Type::ALLOWED_TARGET_IN_PLACE,
                        Type::ALLOWED_TARGET_MODAL,
                    ],
                    'enableQueryParameter' => false,
                ],
            ],
            [
                // Invalid selectionMethod
                [
                    'selectionMethod' => 'invalid',
                    'selectionRoot' => null,
                    'rootDefaultLocation' => true,
                    'selectionContentTypes' => [],
                    'allowedLinkType' => [
                        Type::ALLOWED_LINK_TYPE_EXTERNAL,
                        Type::ALLOWED_LINK_TYPE_INTERNAL,
                    ],
                    'allowedTargets' => [
                        Type::ALLOWED_TARGET_LINK,
                        Type::ALLOWED_TARGET_LINK_IN_NEW_TAB,
                        Type::ALLOWED_TARGET_IN_PLACE,
                        Type::ALLOWED_TARGET_MODAL,
                    ],
                    'enableQueryParameter' => false,
                ],
            ],
            [
                // Invalid selectionRoot
                [
                    'selectionMethod' => Type::SELECTION_BROWSE,
                    'selectionRoot' => [],
                    'rootDefaultLocation' => true,
                    'selectionContentTypes' => [],
                    'allowedLinkType' => [
                        Type::ALLOWED_LINK_TYPE_EXTERNAL,
                        Type::ALLOWED_LINK_TYPE_INTERNAL,
                    ],
                    'allowedTargets' => [
                        Type::ALLOWED_TARGET_LINK,
                        Type::ALLOWED_TARGET_LINK_IN_NEW_TAB,
                        Type::ALLOWED_TARGET_IN_PLACE,
                        Type::ALLOWED_TARGET_MODAL,
                    ],
                    'enableQueryParameter' => false,
                ],
            ],
            [
                // Invalid rootDefaultLocation
                [
                    'selectionMethod' => Type::SELECTION_BROWSE,
                    'selectionRoot' => null,
                    'rootDefaultLocation' => 'string',
                    'selectionContentTypes' => [],
                    'allowedLinkType' => [
                        Type::ALLOWED_LINK_TYPE_EXTERNAL,
                        Type::ALLOWED_LINK_TYPE_INTERNAL,
                    ],
                    'allowedTargets' => [
                        Type::ALLOWED_TARGET_LINK,
                        Type::ALLOWED_TARGET_LINK_IN_NEW_TAB,
                        Type::ALLOWED_TARGET_IN_PLACE,
                        Type::ALLOWED_TARGET_MODAL,
                    ],
                    'enableQueryParameter' => false,
                ],
            ],
            [
                // Invalid selectionContentTypes
                [
                    'selectionMethod' => Type::SELECTION_BROWSE,
                    'selectionRoot' => null,
                    'rootDefaultLocation' => true,
                    'selectionContentTypes' => 'string',
                    'allowedLinkType' => [
                        Type::ALLOWED_LINK_TYPE_EXTERNAL,
                        Type::ALLOWED_LINK_TYPE_INTERNAL,
                    ],
                    'allowedTargets' => [
                        Type::ALLOWED_TARGET_LINK,
                        Type::ALLOWED_TARGET_LINK_IN_NEW_TAB,
                        Type::ALLOWED_TARGET_IN_PLACE,
                        Type::ALLOWED_TARGET_MODAL,
                    ],
                    'enableQueryParameter' => false,
                ],
            ],
            [
                // Invalid allowedLinkType
                [
                    'selectionMethod' => Type::SELECTION_BROWSE,
                    'selectionRoot' => null,
                    'rootDefaultLocation' => true,
                    'selectionContentTypes' => [],
                    'allowedLinkType' => [
                        'invalid'
                    ],
                    'allowedTargets' => [
                        Type::ALLOWED_TARGET_LINK,
                        Type::ALLOWED_TARGET_LINK_IN_NEW_TAB,
                        Type::ALLOWED_TARGET_IN_PLACE,
                        Type::ALLOWED_TARGET_MODAL,
                    ],
                    'enableQueryParameter' => false,
                ],
            ],
            [
                // Invalid allowedTargets
                [
                    'selectionMethod' => Type::SELECTION_BROWSE,
                    'selectionRoot' => null,
                    'rootDefaultLocation' => true,
                    'selectionContentTypes' => [],
                    'allowedLinkType' => [
                        Type::ALLOWED_LINK_TYPE_EXTERNAL,
                        Type::ALLOWED_LINK_TYPE_INTERNAL,
                    ],
                    'allowedTargets' => [
                        'invalid'
                    ],
                    'enableQueryParameter' => false,
                ],
            ],
        ];
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException
     */
    public function testGetRelations(): void
    {
        $ft = $this->createFieldTypeUnderTest();
        self::assertEquals(
            [
                Relation::FIELD => [70],
            ],
            $ft->getRelations($ft->acceptValue(70)),
        );
    }

    /**
     * @dataProvider provideDataForToPersistenceValue
     */
    public function testToPersistenceValue(
        Value $value,
        FieldValue $expected
    ): void {
        $ft = $this->createFieldTypeUnderTest();
        $fieldValue = $ft->toPersistenceValue($value);

        self::assertEquals($fieldValue, $expected);
    }

    /**
     * @dataProvider provideDataForFromPersistenceValue
     */
    public function testFromPersistenceValue(
        FieldValue $value,
        Value $expected
    ): void {
        $ft = $this->createFieldTypeUnderTest();
        $fieldType = $ft->fromPersistenceValue($value);

        self::assertEquals($fieldType, $expected);
    }

    /**
     * @dataProvider provideDataForGetName
     */
    public function testGetName(
        SPIValue $value,
        string $expected,
        array $fieldSettings = [],
        string $languageCode = 'en_GB'
    ): void {
        /** @var \Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition|\PHPUnit\Framework\MockObject\MockObject $fieldDefinitionMock */
        $fieldDefinitionMock = $this->createMock(FieldDefinition::class);
        $fieldDefinitionMock->method('getFieldSettings')->willReturn($fieldSettings);

        $name = $this->getFieldTypeUnderTest()->getName($value, $fieldDefinitionMock, $languageCode);

        self::assertSame($expected, $name);
    }

    public function provideDataForGetName(): array
    {
        return [
            'empty_destination_content_id' => [
                $this->getEmptyValueExpectation(), '', [], 'en_GB',
            ],
            'destination_content_id' => [
                new Value(self::DESTINATION_CONTENT_ID), 'name_en_GB', [], 'en_GB',
            ],
            'destination_content_id_de_DE' => [
                new Value(self::DESTINATION_CONTENT_ID), 'Name_de_DE', [], 'de_DE',
            ],
            'string_name' => [
                new Value('test', 'label'), 'label', [], 'de_DE',
            ]
        ];
    }

    public function testIsSearchable(): void
    {
        $ft = $this->createFieldTypeUnderTest();

        self::assertTrue($ft->isSearchable());
    }

    public function provideDataForFromPersistenceValue(): array
    {
        return [
            'null_data' => [
              new FieldValue(['data' => null]),
              $this->getEmptyValueExpectation()
            ],
            'external_type' => [
                new FieldValue([
                    'data' =>
                        [
                            'type' => 'external',
                            'id' => 'test',
                            'label' => 'label',
                            'target' => Type::ALLOWED_TARGET_LINK,
                            'suffix' => null
                        ],
                    'externalData' => 'test'
                ]),
                new Value('test', 'label', Type::ALLOWED_TARGET_LINK)
            ],
            'internal_type' => [
                new FieldValue([
                    'data' =>
                        [
                            'type' => 'internal',
                            'id' => 12,
                            'label' => 'label',
                            'target' => Type::ALLOWED_TARGET_MODAL,
                            'suffix' => null
                        ],
                    'externalData' => null
                ]),
                new Value(12, 'label', Type::ALLOWED_TARGET_MODAL)
            ]
        ];
    }

    public function provideDataForToPersistenceValue(): array
    {
        return [
            'string_reference_value' => [
                new Value('test'),
                new FieldValue(
                    [
                        'data' => [
                            'id' => null,
                            'label' => null,
                            'type' => 'external',
                            'target' => Type::ALLOWED_TARGET_LINK,
                            'suffix' => null,
                        ],
                        'externalData' => 'test',
                        'sortKey' => 'test',
                    ]
                )
            ],
            'int_reference_value' => [
                new Value(15, 'label', Type::ALLOWED_TARGET_LINK),
                new FieldValue(
                    [
                        'data' => [
                            'id' => 15,
                            'label' => 'label',
                            'type' => 'internal',
                            'target' => Type::ALLOWED_TARGET_LINK,
                            'suffix' => null,
                        ],
                        'externalData' => null,
                        'sortKey' => '15',
                    ]
                )
            ],
            'boolean_reference_value' => [
                new Value(false),
                new FieldValue(
                    [
                        'data' => [],
                        'externalData' => null,
                        'sortKey' => null,
                    ]
                )
            ],
        ];
    }

    public function provideValidDataForValidate(): array
    {
        return [
            [[], new Value(5)],
        ];
    }

    public function provideInvalidDataForValidate(): array
    {
        return [
            [[], new Value(true), []],
            [[], new Value(), []],
        ];
    }


    protected function provideFieldTypeIdentifier(): string
    {
        return 'ngenhancedlink';
    }
    
}
