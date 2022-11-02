<?php

namespace Netgen\IbexaFieldTypeEnhancedLink\Tests\Integration\Core\Repository\FieldType;

use Ibexa\Contracts\Core\Repository\Values\Content\Content;
use Ibexa\Contracts\Core\Repository\Values\Content\Field;
use Ibexa\Contracts\Core\Repository\Values\Content\Relation as APIRelation;
use Ibexa\Core\Base\Exceptions\InvalidArgumentType;
use Ibexa\Core\Repository\Values\Content\Relation;
use Ibexa\Tests\Integration\Core\Repository\FieldType\RelationSearchBaseIntegrationTestTrait;
use Ibexa\Tests\Integration\Core\Repository\FieldType\BaseIntegrationTest;
use Netgen\IbexaFieldTypeEnhancedLink\FieldType\Value;

/**
 * Integration test for use field type.
 *
 * @group integration
 * @group field-type
 */
class EnhancedLinkIntegrationTest extends BaseIntegrationTest
{
    use RelationSearchBaseIntegrationTestTrait;

    public function getTypeName(): string
    {
        return 'ngenhancedlink';
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function getCreateExpectedRelations(Content $content): array
    {
        $contentService = $this->getRepository()->getContentService();

        return [
            new Relation(
                [
                    'sourceFieldDefinitionIdentifier' => 'data',
                    'type' => APIRelation::FIELD,
                    'sourceContentInfo' => $content->contentInfo,
                    'destinationContentInfo' => $contentService->loadContentInfo(4),
                ]
            ),
        ];
    }

    /**
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException
     * @throws \Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException
     */
    public function getUpdateExpectedRelations(Content $content): array
    {
        $contentService = $this->getRepository()->getContentService();

        return [
            new Relation(
                [
                    'sourceFieldDefinitionIdentifier' => 'data',
                    'type' => APIRelation::FIELD,
                    'sourceContentInfo' => $content->contentInfo,
                    'destinationContentInfo' => $contentService->loadContentInfo(49),
                ]
            ),
        ];
    }

    public function getSettingsSchema(): array
    {
        return [
            'selectionMethod' => [
                'type' => 'int',
                'default' => 0,
            ],
            'selectionRoot' => [
                'type' => 'string',
                'default' => null,
            ],
            'selectionContentTypes' => [
                'type' => 'array',
                'default' => [],
            ],
            'rootDefaultLocation' => [
                'type' => 'bool',
                'default' => false,
            ],
        ];
    }

    public function getValidatorSchema(): array
    {
        return [];
    }

    /**
     * Get a valid $fieldSettings value.
     *
     * @todo Implement correctly
     */
    public function getValidFieldSettings(): array
    {
        return [
            'selectionMethod' => 0,
            'selectionRoot' => 1,
            'selectionContentTypes' => [],
            'rootDefaultLocation' => false,
        ];
    }

    /**
     * Get a valid $validatorConfiguration.
     *
     * @todo Implement correctly
     */
    public function getValidValidatorConfiguration(): array
    {
        return [];
    }

    /**
     * Get $fieldSettings value not accepted by the field type.
     *
     * @todo Implement correctly
     */
    public function getInvalidFieldSettings(): array
    {
        return [
            'selectionMethod' => 'a',
            'selectionRoot' => true,
            'unknownSetting' => false,
            'selectionContentTypes' => true,
        ];
    }

    /**
     * Get $validatorConfiguration not accepted by the field type.
     *
     * @todo Implement correctly
     */
    public function getInvalidValidatorConfiguration(): array
    {
        return ['noValidator' => true];
    }

    /**
     * Get initial field data for valid object creation.
     */
    public function getValidCreationFieldData(): Value
    {
        return new Value(4);
    }

    public function getFieldName(): string
    {
        return 'Users';
    }

    public function assertFieldDataLoadedCorrect(Field $field): void
    {
        $this->assertInstanceOf(
            Value::class,
            $field->value
        );

        $expectedData = [
            'destinationContentId' => 4,
        ];
        $this->assertPropertiesCorrect(
            $expectedData,
            $field->value
        );
    }

    public function provideInvalidCreationFieldData(): array
    {
        return [
            [
                new Value([]),
                InvalidArgumentType::class,
            ],
        ];
    }

    public function getValidUpdateFieldData(): Value
    {
        return new Value(49);
    }

    public function assertUpdatedFieldDataLoadedCorrect(Field $field): void
    {
        self::assertInstanceOf(Value::class, $field->value);

        $expectedData = [
            'destinationContentId' => 49,
        ];
        $this->assertPropertiesCorrect(
            $expectedData,
            $field->value
        );
    }

    public function provideInvalidUpdateFieldData(): array
    {
        return $this->provideInvalidCreationFieldData();
    }

    public function assertCopiedFieldDataLoadedCorrectly(Field $field): void
    {
        $this->assertInstanceOf(
            Value::class,
            $field->value
        );

        $expectedData = [
            'destinationContentId' => 4,
        ];

        $this->assertPropertiesCorrect(
            $expectedData,
            $field->value
        );
    }

    public function provideToHashData(): array
    {
        return [
            [
                new Value(4),
                [
                    'destinationContentId' => 4,
                ],
            ],
        ];
    }

    public function provideFromHashData(): array
    {
        return [
            [
                ['destinationContentId' => 4],
                new Value(4),
            ],
        ];
    }

    public function providerForTestIsEmptyValue(): array
    {
        return [
            [new Value()],
        ];
    }

    public function providerForTestIsNotEmptyValue(): array
    {
        return [
            [
                $this->getValidCreationFieldData(),
            ],
        ];
    }
}
