<?php

declare(strict_types=1);

namespace Netgen\IbexaFieldTypeEnhancedLink\Tests\Unit\FieldType;

use Ibexa\Contracts\Core\FieldType\Value as SPIValue;
use Ibexa\Contracts\Core\Persistence\Content\Handler as SPIContentHandler;
use Ibexa\Contracts\Core\Persistence\Content\VersionInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Relation;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Core\Base\Exceptions\InvalidArgumentException;
use Ibexa\Core\FieldType\ValidationError;
use Ibexa\Core\Repository\Validator\TargetContentValidatorInterface;
use Ibexa\Tests\Core\FieldType\FieldTypeTest;
use Netgen\IbexaFieldTypeEnhancedLink\FieldType\Type;
use Netgen\IbexaFieldTypeEnhancedLink\FieldType\Value;

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

        $this->targetContentValidator = $this->createMock(TargetContentValidatorInterface::class);
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
                new Value(23),
                ['destinationContentId' => 23],
            ],
            [
                new Value(),
                ['destinationContentId' => null],
            ],
        ];
    }

    public function provideInputForFromHash(): array
    {
        return [
            [
                ['destinationContentId' => 23],
                new Value(23),
            ],
            [
                ['destinationContentId' => null],
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
                    'selectionRoot' => 42,
                ],
            ],
            [
                [
                    'selectionMethod' => Type::SELECTION_DROPDOWN,
                    'selectionRoot' => 'some-key',
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
                    'unknownKey' => 23,
                    'selectionMethod' => Type::SELECTION_BROWSE,
                    'selectionRoot' => 42,
                ],
            ],
            [
                // Invalid selectionMethod
                [
                    'selectionMethod' => 2342,
                    'selectionRoot' => 42,
                ],
            ],
            [
                // Invalid selectionRoot
                [
                    'selectionMethod' => Type::SELECTION_DROPDOWN,
                    'selectionRoot' => [],
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

    public function testValidateNotExistingContentRelation(): void
    {
        $destinationContentId = 'invalid';

        $this->targetContentValidator
            ->expects(self::once())
            ->method('validate')
            ->with((int) $destinationContentId)
            ->willReturn($this->generateValidationError($destinationContentId));

        $validationErrors = $this->doValidate([], new Value($destinationContentId));

        self::assertIsArray($validationErrors);
        self::assertEquals([$this->generateValidationError($destinationContentId)], $validationErrors);
    }

    public function testValidateInvalidContentType(): void
    {
        $destinationContentId = 12;
        $allowedContentTypes = ['article', 'folder'];

        $this->targetContentValidator
            ->expects(self::once())
            ->method('validate')
            ->with($destinationContentId, $allowedContentTypes)
            ->willReturn($this->generateContentTypeValidationError('test'));

        $validationErrors = $this->doValidate(
            ['fieldSettings' => ['selectionContentTypes' => $allowedContentTypes]],
            new Value($destinationContentId),
        );

        self::assertIsArray($validationErrors);
        self::assertEquals([$this->generateContentTypeValidationError('test')], $validationErrors);
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
            [[], new Value('invalid'), []],
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
                'default' => false,
            ],
            'selectionContentTypes' => [
                'type' => 'array',
                'default' => [],
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

    private function generateValidationError(string $contentId): ValidationError
    {
        return new ValidationError(
            'Content with identifier %contentId% is not a valid relation target',
            null,
            [
                '%contentId%' => $contentId,
            ],
            'targetContentId',
        );
    }

    private function generateContentTypeValidationError(string $contentTypeIdentifier): ValidationError
    {
        return new ValidationError(
            'Content Type %contentTypeIdentifier% is not a valid relation target',
            null,
            [
                '%contentTypeIdentifier%' => $contentTypeIdentifier,
            ],
            'targetContentId',
        );
    }
}
