<?php

declare(strict_types=1);

namespace Netgen\IbexaFieldTypeEnhancedLink\Tests\Unit\FieldType;

use eZ\Publish\SPI\FieldType\Value as SPIValue;
use eZ\Publish\SPI\Persistence\Content\FieldValue;
use eZ\Publish\SPI\Persistence\Content\Handler as SPIContentHandler;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Relation;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentException;
use eZ\Publish\Core\Base\Exceptions\InvalidArgumentType;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use eZ\Publish\Core\FieldType\ValidationError;
use eZ\Publish\Core\FieldType\Tests\FieldTypeTest;
use Netgen\IbexaFieldTypeEnhancedLink\FieldType\InternalLinkValidator;
use Netgen\IbexaFieldTypeEnhancedLink\FieldType\Type;
use Netgen\IbexaFieldTypeEnhancedLink\FieldType\Value;

/**
 * @group type
 */
class EnhancedLinkTypeTest extends FieldTypeTest
{
    private const DESTINATION_CONTENT_ID = 14;
    private const NON_EXISTENT_CONTENT_ID = 123;

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
            ->with(
                self::logicalOr(
                    self::equalTo(self::NON_EXISTENT_CONTENT_ID),
                    self::equalTo(self::DESTINATION_CONTENT_ID),
                ),
            )
            ->willReturnCallback(static function ($contentId) use ($destinationContentInfo) {
                if ($contentId === self::DESTINATION_CONTENT_ID) {
                    return $destinationContentInfo;
                }

                throw new NotFoundException('Content', self::NON_EXISTENT_CONTENT_ID);
            });

        $this->contentHandler
            ->method('loadVersionInfo')
            ->with(self::DESTINATION_CONTENT_ID, $currentVersionNo)
            ->willReturn($versionInfo);

        $this->targetContentValidator = $this->createMock(InternalLinkValidator::class);
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
                new Value(23, 'test', Type::TARGET_LINK, null),
                [
                    'reference' => 23,
                    'label' => 'test',
                    'target' => Type::TARGET_LINK,
                    'suffix' => null,
                ],
            ],
            [
                new Value(),
                [
                    'reference' => null,
                    'label' => null,
                    'target' => Type::TARGET_LINK,
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
                    'target' => Type::TARGET_LINK,
                    'suffix' => null,
                ],
                new Value(23, 'test', Type::TARGET_LINK, null),
            ],
            [
                [
                    'reference' => null,
                    'label' => null,
                    'target' => Type::TARGET_LINK,
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
                    'rootDefaultLocation' => false,
                    'selectionContentTypes' => [],
                    'allowedLinkType' => Type::LINK_TYPE_ALL,
                    'allowedTargetsInternal' => [
                        Type::TARGET_LINK,
                        Type::TARGET_LINK_IN_NEW_TAB,
                        Type::TARGET_EMBED,
                        Type::TARGET_MODAL,
                    ],
                    'allowedTargetsExternal' => [
                        Type::TARGET_LINK,
                        Type::TARGET_LINK_IN_NEW_TAB,
                    ],
                    'enableSuffix' => false,
                ],
            ],
            [
                [
                    'selectionMethod' => Type::SELECTION_BROWSE,
                    'selectionRoot' => null,
                    'rootDefaultLocation' => false,
                    'selectionContentTypes' => [],
                    'allowedLinkType' => Type::LINK_TYPE_EXTERNAL,
                    'allowedTargetsInternal' => [
                        Type::TARGET_LINK,
                        Type::TARGET_LINK_IN_NEW_TAB,
                        Type::TARGET_EMBED,
                        Type::TARGET_MODAL,
                    ],
                    'allowedTargetsExternal' => [
                        Type::TARGET_LINK_IN_NEW_TAB,
                    ],
                    'enableSuffix' => true,
                ],
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
                    'rootDefaultLocation' => false,
                    'selectionContentTypes' => [],
                    'allowedLinkType' => Type::LINK_TYPE_ALL,
                    'allowedTargetsInternal' => [
                        Type::TARGET_LINK,
                        Type::TARGET_LINK_IN_NEW_TAB,
                        Type::TARGET_EMBED,
                        Type::TARGET_MODAL,
                    ],
                    'allowedTargetsExternal' => [
                        Type::TARGET_LINK,
                        Type::TARGET_LINK_IN_NEW_TAB,
                    ],
                    'enableSuffix' => false,
                ],
            ],
            [
                // Invalid selectionMethod
                [
                    'selectionMethod' => 'invalid',
                    'selectionRoot' => null,
                    'rootDefaultLocation' => false,
                    'selectionContentTypes' => [],
                    'allowedLinkType' => Type::LINK_TYPE_ALL,
                    'allowedTargetsInternal' => [
                        Type::TARGET_LINK,
                        Type::TARGET_LINK_IN_NEW_TAB,
                        Type::TARGET_EMBED,
                        Type::TARGET_MODAL,
                    ],
                    'allowedTargetsExternal' => [
                        Type::TARGET_LINK,
                        Type::TARGET_LINK_IN_NEW_TAB,
                    ],
                    'enableSuffix' => false,
                ],
            ],
            [
                // Invalid selectionRoot
                [
                    'selectionMethod' => Type::SELECTION_BROWSE,
                    'selectionRoot' => [],
                    'rootDefaultLocation' => false,
                    'selectionContentTypes' => [],
                    'allowedLinkType' => Type::LINK_TYPE_ALL,
                    'allowedTargetsInternal' => [
                        Type::TARGET_LINK,
                        Type::TARGET_LINK_IN_NEW_TAB,
                        Type::TARGET_EMBED,
                        Type::TARGET_MODAL,
                    ],
                    'allowedTargetsExternal' => [
                        Type::TARGET_LINK,
                        Type::TARGET_LINK_IN_NEW_TAB,
                    ],
                    'enableSuffix' => false,
                ],
            ],
            [
                // Invalid rootDefaultLocation
                [
                    'selectionMethod' => Type::SELECTION_BROWSE,
                    'selectionRoot' => null,
                    'rootDefaultLocation' => 'invalid',
                    'selectionContentTypes' => [],
                    'allowedLinkType' => Type::LINK_TYPE_ALL,
                    'allowedTargetsInternal' => [
                        Type::TARGET_LINK,
                        Type::TARGET_LINK_IN_NEW_TAB,
                        Type::TARGET_EMBED,
                        Type::TARGET_MODAL,
                    ],
                    'allowedTargetsExternal' => [
                        Type::TARGET_LINK,
                        Type::TARGET_LINK_IN_NEW_TAB,
                    ],
                    'enableSuffix' => false,
                ],
            ],
            [
                // Invalid enableSuffix
                [
                    'selectionMethod' => Type::SELECTION_BROWSE,
                    'selectionRoot' => null,
                    'rootDefaultLocation' => false,
                    'selectionContentTypes' => [],
                    'allowedLinkType' => Type::LINK_TYPE_ALL,
                    'allowedTargetsInternal' => [
                        Type::TARGET_LINK,
                        Type::TARGET_LINK_IN_NEW_TAB,
                        Type::TARGET_EMBED,
                        Type::TARGET_MODAL,
                    ],
                    'allowedTargetsExternal' => [
                        Type::TARGET_LINK,
                        Type::TARGET_LINK_IN_NEW_TAB,
                    ],
                    'enableSuffix' => 'invalid',
                ],
            ],
            [
                // Invalid selectionContentTypes
                [
                    'selectionMethod' => Type::SELECTION_BROWSE,
                    'selectionRoot' => null,
                    'rootDefaultLocation' => false,
                    'selectionContentTypes' => 'string',
                    'allowedLinkType' => Type::LINK_TYPE_ALL,
                    'allowedTargetsInternal' => [
                        Type::TARGET_LINK,
                        Type::TARGET_LINK_IN_NEW_TAB,
                        Type::TARGET_EMBED,
                        Type::TARGET_MODAL,
                    ],
                    'allowedTargetsExternal' => [
                        Type::TARGET_LINK,
                        Type::TARGET_LINK_IN_NEW_TAB,
                    ],
                    'enableSuffix' => false,
                ],
            ],
            [
                // Invalid allowedLinkType
                [
                    'selectionMethod' => Type::SELECTION_BROWSE,
                    'selectionRoot' => null,
                    'rootDefaultLocation' => false,
                    'selectionContentTypes' => [],
                    'allowedLinkType' => 'invalid',
                    'allowedTargetsInternal' => [
                        Type::TARGET_LINK,
                        Type::TARGET_LINK_IN_NEW_TAB,
                        Type::TARGET_EMBED,
                        Type::TARGET_MODAL,
                    ],
                    'allowedTargetsExternal' => [
                        Type::TARGET_LINK,
                        Type::TARGET_LINK_IN_NEW_TAB,
                    ],
                    'enableSuffix' => false,
                ],
            ],
            [
                // Invalid allowedTargetsInternal
                [
                    'selectionMethod' => Type::SELECTION_BROWSE,
                    'selectionRoot' => null,
                    'rootDefaultLocation' => false,
                    'selectionContentTypes' => [],
                    'allowedLinkType' => Type::LINK_TYPE_ALL,
                    'allowedTargetsInternal' => [
                        'invalid',
                    ],
                    'allowedTargetsExternal' => [
                        Type::TARGET_LINK,
                        Type::TARGET_LINK_IN_NEW_TAB,
                    ],
                    'enableSuffix' => false,
                ],
            ],
            [
                // Invalid allowedTargetsExternal
                [
                    'selectionMethod' => Type::SELECTION_BROWSE,
                    'selectionRoot' => null,
                    'rootDefaultLocation' => false,
                    'selectionContentTypes' => [],
                    'allowedLinkType' => Type::LINK_TYPE_ALL,
                    'allowedTargetsInternal' => [
                        Type::TARGET_LINK,
                        Type::TARGET_LINK_IN_NEW_TAB,
                        Type::TARGET_EMBED,
                        Type::TARGET_MODAL,
                    ],
                    'allowedTargetsExternal' => [
                        'invalid',
                    ],
                    'enableSuffix' => false,
                ],
            ],
        ];
    }

    /**
     * @throws \eZ\Publish\API\Repository\Exceptions\InvalidArgumentException
     */
    public function testGetRelations(): void
    {
        $type = $this->createFieldTypeUnderTest();
        self::assertEquals(
            [
                Relation::FIELD => [70],
            ],
            $type->getRelations($type->acceptValue(70)),
        );
    }

    /**
     * @dataProvider provideDataForToPersistenceValue
     */
    public function testToPersistenceValue(
        Value $value,
        FieldValue $expected
    ): void {
        $type = $this->createFieldTypeUnderTest();
        $fieldValue = $type->toPersistenceValue($value);

        self::assertEquals($fieldValue, $expected);
    }

    /**
     * @dataProvider provideDataForFromPersistenceValue
     */
    public function testFromPersistenceValue(
        FieldValue $persistenceValue,
        Value $expected
    ): void {
        $type = $this->createFieldTypeUnderTest();
        $fieldType = $type->fromPersistenceValue($persistenceValue);

        self::assertEquals($fieldType, $expected);
    }

    public function provideDataForGetName(): array
    {
        return [
            'empty_destination_content_id' => [
                $this->getEmptyValueExpectation(), '',
            ],
            'destination_content_id' => [
                new Value(self::DESTINATION_CONTENT_ID), 'name_en_GB',
            ],
            'string_name' => [
                new Value('test', 'label'), 'label',
            ],
            'destination_content_id_non_existent' => [
                new Value(self::NON_EXISTENT_CONTENT_ID), '',
            ],
        ];
    }

    public function testIsSearchable(): void
    {
        $type = $this->createFieldTypeUnderTest();

        self::assertTrue($type->isSearchable());
    }

    /**
     * @group targetValidation
     */
    public function testInvalidExternalTargetValidationError(): void
    {
        $fieldDefinition = $this->getFieldDefinitionMock(['allowedTargetsExternal' => [Type::TARGET_LINK, Type::TARGET_LINK_IN_NEW_TAB]]);
        $fieldType = $this->createFieldTypeUnderTest();
        $validationErrors = $fieldType->validate($fieldDefinition, new Value('test', '', Type::TARGET_MODAL));

        self::assertIsArray($validationErrors);
        self::assertEquals([$this->generateInvalidExternalTargetValidationError(Type::TARGET_MODAL)], $validationErrors);
    }

    /**
     * @group targetValidation
     */
    public function testInvalidInternalTargetValidationError(): void
    {
        $fieldDefinition = $this->getFieldDefinitionMock(['allowedTargetsInternal' => [Type::TARGET_LINK, Type::TARGET_LINK_IN_NEW_TAB]]);
        $fieldType = $this->createFieldTypeUnderTest();
        $validationErrors = $fieldType->validate($fieldDefinition, new Value(1, '', Type::TARGET_MODAL));

        self::assertIsArray($validationErrors);
        self::assertEquals([$this->generateInvalidInternalTargetValidationError(Type::TARGET_MODAL)], $validationErrors);
    }

    public function testDisallowedLinkTypeValidationError(): void
    {
        $fieldDefinition = $this->getFieldDefinitionMock(['allowedLinkType' => Type::LINK_TYPE_EXTERNAL]);
        $fieldType = $this->createFieldTypeUnderTest();
        $validationErrors = $fieldType->validate($fieldDefinition, new Value(1));
        self::assertIsArray($validationErrors);
        self::assertEquals([$this->generateDisallowedLinkTypeValidationError(Type::LINK_TYPE_EXTERNAL)], $validationErrors);
    }

    public function testInvalidValueStructureValidationError(): void
    {
        $fieldType = $this->createFieldTypeUnderTest();

        try {
            $fieldType->acceptValue(new Value(true));
        } catch (InvalidArgumentType $e) {
            $result = true;
        }

        self::assertTrue($result);
    }

    public function provideDataForFromPersistenceValue(): array
    {
        return [
            'null_data' => [
                new FieldValue(['data' => null]),
                $this->getEmptyValueExpectation(),
            ],
            'external_type' => [
                new FieldValue([
                    'data' => [
                        'type' => Type::LINK_TYPE_EXTERNAL,
                        'id' => 'test',
                        'label' => 'label',
                        'target' => Type::TARGET_LINK,
                        'suffix' => null,
                    ],
                    'externalData' => 'test',
                ]),
                new Value('test', 'label', Type::TARGET_LINK),
            ],
            'internal_type' => [
                new FieldValue([
                    'data' => [
                        'type' => Type::LINK_TYPE_INTERNAL,
                        'id' => 12,
                        'label' => 'label',
                        'target' => Type::TARGET_MODAL,
                        'suffix' => null,
                    ],
                    'externalData' => null,
                ]),
                new Value(12, 'label', Type::TARGET_MODAL),
            ],
            'no_type_key' => [
                new FieldValue([
                    'data' => [
                        'id' => 12,
                        'label' => 'label',
                        'target' => Type::TARGET_MODAL,
                        'suffix' => null,
                    ],
                    'externalData' => null,
                ]),
                $this->getEmptyValueExpectation(),
            ],
            'data_not_array' => [
                new FieldValue([
                    'data' => 'not an array',
                ]),
                $this->getEmptyValueExpectation(),
            ],
            'external_type_without_string_external_data' => [
                new FieldValue([
                    'data' => [
                        'type' => Type::LINK_TYPE_EXTERNAL,
                        'id' => 'test',
                        'label' => 'label',
                        'target' => Type::TARGET_LINK,
                        'suffix' => null,
                    ],
                    'externalData' => null,
                ]),
                $this->getEmptyValueExpectation(),
            ],
            'internal_type_without_data_id' => [
                new FieldValue([
                    'data' => [
                        'type' => Type::LINK_TYPE_INTERNAL,
                        'label' => 'label',
                        'target' => Type::TARGET_MODAL,
                        'suffix' => null,
                    ],
                    'externalData' => null,
                ]),
                $this->getEmptyValueExpectation(),
            ],
            'internal_type_without_int_data_id' => [
                new FieldValue([
                    'data' => [
                        'type' => null,
                        'id' => 12,
                        'label' => 'label',
                        'target' => Type::TARGET_MODAL,
                        'suffix' => null,
                    ],
                    'externalData' => null,
                ]),
                $this->getEmptyValueExpectation(),
            ],
            'missing_fields' => [
                new FieldValue([
                    'data' => [
                        'id' => 12,
                        'type' => Type::LINK_TYPE_INTERNAL,
                    ],
                    'externalData' => null,
                ]),
                new Value(12, null, Type::TARGET_LINK, null),
            ],
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
                            'type' => Type::LINK_TYPE_EXTERNAL,
                            'target' => Type::TARGET_LINK,
                            'suffix' => null,
                        ],
                        'externalData' => 'test',
                        'sortKey' => 'test',
                    ],
                ),
            ],
            'int_reference_value' => [
                new Value(15, 'label', Type::TARGET_LINK),
                new FieldValue(
                    [
                        'data' => [
                            'id' => 15,
                            'label' => 'label',
                            'type' => Type::LINK_TYPE_INTERNAL,
                            'target' => Type::TARGET_LINK,
                            'suffix' => null,
                        ],
                        'externalData' => null,
                        'sortKey' => '15',
                    ],
                ),
            ],
            'boolean_reference_value' => [
                new Value(false),
                new FieldValue(
                    [
                        'data' => [],
                        'externalData' => null,
                        'sortKey' => null,
                    ],
                ),
            ],
        ];
    }

    public function provideValidDataForValidate(): array
    {
        return [
            [[], new Value(self::DESTINATION_CONTENT_ID)],
        ];
    }

    public function provideInvalidDataForValidate(): array
    {
        return [
            [[], new Value(true), []],
            [[], new Value(), []],
        ];
    }

    protected function createFieldTypeUnderTest(): Type
    {
        $fieldType = new Type(
            $this->contentHandler,
            $this->targetContentValidator,
        );
        $fieldType->setTransformationProcessor($this->getTransformationProcessorMock());

        return $fieldType;
    }

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
                'default' => false,
            ],
            'selectionContentTypes' => [
                'type' => 'array',
                'default' => [],
            ],
            'allowedLinkType' => [
                'type' => 'choice',
                'default' => Type::LINK_TYPE_ALL,
            ],
            'allowedTargetsInternal' => [
                'type' => 'array',
                'default' => [
                    Type::TARGET_LINK,
                    Type::TARGET_LINK_IN_NEW_TAB,
                    Type::TARGET_EMBED,
                    Type::TARGET_MODAL,
                ],
            ],
            'allowedTargetsExternal' => [
                'type' => 'array',
                'default' => [
                    Type::TARGET_LINK,
                    Type::TARGET_LINK_IN_NEW_TAB,
                ],
            ],
            'enableSuffix' => [
                'type' => 'bool',
                'default' => true,
            ],
        ];
    }

    protected function getEmptyValueExpectation(): Value
    {
        return new Value();
    }

    protected function provideFieldTypeIdentifier(): string
    {
        return 'ngenhancedlink';
    }

    private function generateInvalidExternalTargetValidationError(string $target): ValidationError
    {
        return new ValidationError(
            'Target %target% is not a valid target',
            null,
            [
                '%target%' => $target,
            ],
            'allowedTargetsExternal',
        );
    }

    /**
     * @return \eZ\Publish\API\Repository\Values\ContentType\FieldDefinition|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getFieldDefinitionMock(array $fieldSettings)
    {
        /** @var |\PHPUnit\Framework\MockObject\MockObject $fieldDefinitionMock */
        $fieldDefinitionMock = $this->createMock(FieldDefinition::class);
        $fieldDefinitionMock
            ->method('getFieldSettings')
            ->willReturn($fieldSettings);

        return $fieldDefinitionMock;
    }

    private function generateInvalidInternalTargetValidationError(string $target): ValidationError
    {
        return new ValidationError(
            'Target %target% is not a valid target',
            null,
            [
                '%target%' => $target,
            ],
            'allowedTargetsInternal',
        );
    }

    private function generateDisallowedLinkTypeValidationError(string $allowedLinkType): ValidationError
    {
        return new ValidationError(
            'Link type is not allowed. Must be of type %type%',
            null,
            [
                '%type%' => $allowedLinkType,
            ],
            'allowedLinkType',
        );
    }
}
