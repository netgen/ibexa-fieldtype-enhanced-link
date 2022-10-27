<?php

declare(strict_types=1);

namespace Netgen\IbexaFieldTypeEnhancedLink\FieldType;

use Ibexa\Contracts\Core\FieldType\Value as SPIValue;
use Ibexa\Contracts\Core\Persistence\Content\Handler as SPIContentHandler;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Contracts\Core\Repository\Values\Content\Relation;
use Ibexa\Contracts\Core\Repository\Values\ContentType\FieldDefinition;
use Ibexa\Core\Base\Exceptions\InvalidArgumentType;
use Ibexa\Core\FieldType\FieldType;
use Ibexa\Core\FieldType\ValidationError;
use Ibexa\Core\FieldType\Value as BaseValue;
use Ibexa\Core\Repository\Validator\TargetContentValidatorInterface;

class Type extends FieldType
{
    public const SELECTION_BROWSE = 0;
    public const SELECTION_DROPDOWN = 1;

    protected $settingsSchema = [
        'selectionMethod' => [
            'type' => 'int',
            'default' => self::SELECTION_BROWSE,
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

    private SPIContentHandler $handler;
    private TargetContentValidatorInterface $targetContentValidator;

    public function __construct(
        SPIContentHandler $handler,
        TargetContentValidatorInterface $targetContentValidator
    ) {
        $this->handler = $handler;
        $this->targetContentValidator = $targetContentValidator;
    }

    public function validateFieldSettings($fieldSettings): array
    {
        $validationErrors = [];

        foreach ($fieldSettings as $name => $value) {
            if (!isset($this->settingsSchema[$name])) {
                $validationErrors[] = new ValidationError(
                    "Setting '%setting%' is unknown",
                    null,
                    [
                        '%setting%' => $name,
                    ],
                    "[$name]"
                );
                continue;
            }

            switch ($name) {
                case 'selectionMethod':
                    if ($value !== self::SELECTION_BROWSE && $value !== self::SELECTION_DROPDOWN) {
                        $validationErrors[] = new ValidationError(
                            "Setting '%setting%' must be either %selection_browse% or %selection_dropdown%",
                            null,
                            [
                                '%setting%' => $name,
                                '%selection_browse%' => self::SELECTION_BROWSE,
                                '%selection_dropdown%' => self::SELECTION_DROPDOWN,
                            ],
                            "[$name]"
                        );
                    }
                    break;
                case 'selectionRoot':
                    if (!is_int($value) && !is_string($value) && $value !== null) {
                        $validationErrors[] = new ValidationError(
                            "Setting '%setting%' value must be of either null, string or integer",
                            null,
                            [
                                '%setting%' => $name,
                            ],
                            "[$name]"
                        );
                    }
                    break;
                case 'rootDefaultLocation':
                    if (!is_bool($value) && $value !== null) {
                        $validationErrors[] = new ValidationError(
                            "Setting '%setting%' value must be of either null or bool",
                            null,
                            [
                                '%setting%' => $name,
                            ],
                            "[$name]"
                        );
                    }
                    break;
                case 'selectionContentTypes':
                    if (!is_array($value)) {
                        $validationErrors[] = new ValidationError(
                            "Setting '%setting%' value must be of array type",
                            null,
                            [
                                '%setting%' => $name,
                            ],
                            "[$name]"
                        );
                    }
                    break;
            }
        }

        return $validationErrors;
    }

    public function getFieldTypeIdentifier(): string
    {
        return 'ngenhancedlink';
    }

    public function getName(SPIValue $value, FieldDefinition $fieldDefinition, string $languageCode): string
    {
        if (empty($value->destinationContentId)) {
            return '';
        }

        try {
            $contentInfo = $this->handler->loadContentInfo($value->destinationContentId);
            $versionInfo = $this->handler->loadVersionInfo($value->destinationContentId, $contentInfo->currentVersionNo);
        } catch (NotFoundException $e) {
            return '';
        }

        return $versionInfo->names[$languageCode] ?? $versionInfo->names[$contentInfo->mainLanguageCode];
    }

    /**
     * @return \Ibexa\Core\FieldType\ValidationError[]
     */
    public function validate(FieldDefinition $fieldDefinition, SPIValue $value): array
    {
        /** @var \Netgen\IbexaFieldTypeEnhancedLink\FieldType\Value $value */
        $validationErrors = [];

        if ($this->isEmptyValue($value)) {
            return $validationErrors;
        }

        $allowedContentTypes = $fieldDefinition->getFieldSettings()['selectionContentTypes'] ?? [];

        $validationError = $this->targetContentValidator->validate(
            (int) $value->destinationContentId,
            $allowedContentTypes
        );

        return $validationError === null ? $validationErrors : [$validationError];
    }

    public function getEmptyValue(): Value
    {
        return new Value();
    }

    /**
     * Returns if the given $value is considered empty by the field type.
     *
     * @param mixed $value
     *
     * @return bool
     */
    public function isEmptyValue(SPIValue $value): bool
    {
        /** @var \Netgen\IbexaFieldTypeEnhancedLink\FieldType\Value $value */
        return $value->destinationContentId === null;
    }

    /**
     * Inspects given $inputValue and potentially converts it into a dedicated value object.
     *
     * @param int|string|\Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo|\Netgen\IbexaFieldTypeEnhancedLink\FieldType\Value $inputValue
     *
     * @return \Netgen\IbexaFieldTypeEnhancedLink\FieldType\Value
     */
    protected function createValueFromInput($inputValue)
    {
        // ContentInfo
        if ($inputValue instanceof ContentInfo) {
            $inputValue = new Value($inputValue->id);
        } elseif (is_int($inputValue) || is_string($inputValue)) { // content id
            $inputValue = new Value($inputValue);
        }

        return $inputValue;
    }

    /**
     * Throws an exception if value structure is not of expected format.
     *
     * @param \Netgen\IbexaFieldTypeEnhancedLink\FieldType\Value $value
     *@throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException If the value does not match the expected structure.
     *
     */
    protected function checkValueStructure(BaseValue $value): void
    {
        if (!is_int($value->destinationContentId) && !is_string($value->destinationContentId)) {
            throw new InvalidArgumentType(
                '$value->destinationContentId',
                'string|int',
                $value->destinationContentId
            );
        }
    }

    protected function getSortInfo(BaseValue $value): string
    {
        return (string)$value;
    }

    public function fromHash($hash): Value
    {
        if ($hash !== null) {
            $destinationContentId = $hash['destinationContentId'];
            if ($destinationContentId !== null) {
                return new Value((int)$destinationContentId);
            }
        }

        return $this->getEmptyValue();
    }

    public function toHash(SPIValue $value): array
    {
        /** @var \Netgen\IbexaFieldTypeEnhancedLink\FieldType\Value $value */
        $destinationContentId = null;
        if ($value->destinationContentId !== null) {
            $destinationContentId = (int)$value->destinationContentId;
        }

        return [
            'destinationContentId' => $destinationContentId,
        ];
    }

    public function isSearchable(): bool
    {
        return true;
    }

    public function getRelations(SPIValue $fieldValue): array
    {
        /** @var \Netgen\IbexaFieldTypeEnhancedLink\FieldType\Value $fieldValue */
        $relations = [];
        if ($fieldValue->destinationContentId !== null) {
            $relations[Relation::FIELD] = [$fieldValue->destinationContentId];
        }

        return $relations;
    }
}
