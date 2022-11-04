<?php

declare(strict_types=1);

namespace Netgen\IbexaFieldTypeEnhancedLink\FieldType;

use Ibexa\Contracts\Core\FieldType\Value as SPIValue;
use Ibexa\Contracts\Core\Persistence\Content\FieldValue;
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
        /** @var Value $value */
        if (is_string($value->link)) {
            return (string)$value->text;
        }
        if (is_int($value->link)) {
            try {
                $contentInfo = $this->handler->loadContentInfo($value->link);
                $versionInfo = $this->handler->loadVersionInfo($value->link, $contentInfo->currentVersionNo);
            } catch (NotFoundException $e) {
                return '';
            }

            return $versionInfo->names[$languageCode] ?? $versionInfo->names[$contentInfo->mainLanguageCode];
        }
        return '';
    }

    /**
     * @return \Ibexa\Core\FieldType\ValidationError[]
     */
    public function validate(FieldDefinition $fieldDefinition, SPIValue $value): array
    {
        /** @var Value $value */
        $validationErrors = [];
        if ($this->isEmptyValue($value)  || is_string($value->link)) {
            return $validationErrors;
        }

        $allowedContentTypes = $fieldDefinition->getFieldSettings()['selectionContentTypes'] ?? [];

        $validationError = $this->targetContentValidator->validate(
            (int) $value->link,
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
        /** @var Value $value */
        return !isset($value->link);
    }

    /**
     * Inspects given $inputValue and potentially converts it into a dedicated value object.
     *
     * @param int|string|\Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo|Value $inputValue
     *
     * @return Value
     */
    protected function createValueFromInput($inputValue)
    {
        // ContentInfo
        if ($inputValue instanceof ContentInfo) {
            return new Value($inputValue->id);
        } elseif (is_int($inputValue) || is_string($inputValue)) {
            return new Value($inputValue);
        }

        return $this->getEmptyValue();
    }

    /**
     * Throws an exception if value structure is not of expected format.
     *
     * @param Value $value
     *@throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException If the value does not match the expected structure.
     *
     */
    protected function checkValueStructure(BaseValue $value): void
    {
        if (isset($value->link) && !is_int($value->link) && !is_string($value->link)) {
            throw new InvalidArgumentType(
                '$value->link',
                'int|string',
                $value->link
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
            $link = $hash['link'];
            if (isset($link)) {
                return new Value($link, $hash['text']);
            }
        }
        return $this->getEmptyValue();
    }

    public function toHash(SPIValue $value): array
    {
        /** @var Value $value */
        return [
            'link' => $value->link,
            'text' => $value->text,
        ];
    }

    public function isSearchable(): bool
    {
        return true;
    }

    public function getRelations(SPIValue $fieldValue): array
    {
        /** @var Value $fieldValue */
        $relations = [];
        if ($fieldValue->link !== null) {
            $relations[Relation::FIELD] = [$fieldValue->link];
        }

        return $relations;
    }

    public function toPersistenceValue(SPIValue $value)
    {
        /** @var Value $value */
        if (is_string($value->link)) {
            return new FieldValue(
                [
                    'data' => [
                        'id' => null,
                        'text' => $value->text,
                        'type' => 'external',
                    ],
                    'externalData' => $value->link,
                    'sortKey' => $this->getSortInfo($value),
                ]
            );
        }

        if (is_int($value->link)) {
            return new FieldValue(
                [
                    'data' => [
                        'id' => $value->link,
                        'text' => $value->text,
                        'type' => 'internal',
                    ],
                    'externalData' => null,
                    'sortKey' => $this->getSortInfo($value),
                ]
            );
        }

        return new FieldValue(
            [
                'data' => [],
                'externalData' => null,
                'sortKey' => null,
            ]
        );
    }

    /**
     * Converts a persistence $fieldValue to a Value.
     *
     * This method builds a field type value from the $data and $externalData properties.
     *
     * @param FieldValue $fieldValue
     *
     * @return Value
     */
    public function fromPersistenceValue(FieldValue $fieldValue): Value
    {
        if ($fieldValue->data === null || !isset($fieldValue->data['id']) || ($fieldValue->data['type']==='external' && $fieldValue->externalData === null)) {
            return $this->getEmptyValue();
        }
        if ($fieldValue->data['type']==='external') {
            return new Value(
                $fieldValue->externalData,
                $fieldValue->data['text']
            );
        }

        return new Value(
            $fieldValue->data['id'],
            $fieldValue->data['text']
        );
    }
}
