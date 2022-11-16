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

use function array_intersect;
use function array_key_exists;
use function count;
use function in_array;
use function is_array;
use function is_bool;
use function is_int;
use function is_string;

class Type extends FieldType
{
    public const SELECTION_BROWSE = 0;
    public const SELECTION_DROPDOWN = 1;
    public const LINK_TYPE_EXTERNAL = 'external';
    public const LINK_TYPE_INTERNAL = 'internal';
    public const LINK_TYPE_ALL = 'all';
    public const TARGET_LINK = 'link';
    public const TARGET_IN_PLACE = 'in_place';
    public const TARGET_MODAL = 'modal';
    public const TARGET_LINK_IN_NEW_TAB = 'link_new_tab';

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
        'allowedLinkType' => [
            'type' => 'choice',
            'default' => self::LINK_TYPE_ALL,
        ],
        'allowedTargets' => [
            'type' => 'array',
            'default' => [
                self::TARGET_LINK,
                self::TARGET_LINK_IN_NEW_TAB,
                self::TARGET_IN_PLACE,
                self::TARGET_MODAL,
            ],
        ],
        'enableQueryParameter' => [
            'type' => 'bool',
            'default' => false,
        ],
    ];

    private SPIContentHandler $handler;
    private TargetContentValidator $targetContentValidator;

    public function __construct(
        SPIContentHandler $handler,
        TargetContentValidator $targetContentValidator
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
                    "[{$name}]",
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
                            "[{$name}]",
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
                            "[{$name}]",
                        );
                    }

                    break;

                case 'rootDefaultLocation':
                case 'enableQueryParameter':
                    if (!is_bool($value)) {
                        $validationErrors[] = new ValidationError(
                            "Setting '%setting%' value must be of boolean type",
                            null,
                            [
                                '%setting%' => $name,
                            ],
                            "[{$name}]",
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
                            "[{$name}]",
                        );
                    }

                    break;

                case 'allowedLinkType':
                    if (!in_array($value, [self::LINK_TYPE_INTERNAL, self::LINK_TYPE_EXTERNAL, self::LINK_TYPE_ALL], true)) {
                        $validationErrors[] = new ValidationError(
                            "Setting '%setting%' value must be %external%, %internal% or %all%",
                            null,
                            [
                                '%setting%' => $name,
                                '%external%' => self::LINK_TYPE_EXTERNAL,
                                '%internal%' => self::LINK_TYPE_INTERNAL,
                                '%all%' => self::LINK_TYPE_ALL,
                            ],
                            "[{$name}]",
                        );
                    }

                    break;

                case 'allowedTargets':
                    if (!is_array($value) || count(array_intersect($value, [self::TARGET_LINK, self::TARGET_LINK_IN_NEW_TAB, self::TARGET_IN_PLACE, self::TARGET_MODAL])) === 0) {
                        $validationErrors[] = new ValidationError(
                            "Setting '%setting%' value must be one or either %link%, %link_in_new_tab%, %in_place% and/or %modal%",
                            null,
                            [
                                '%setting%' => $name,
                                '%link%' => self::TARGET_LINK,
                                '%link_in_new_tab%' => self::TARGET_LINK_IN_NEW_TAB,
                                '%in_place%' => self::TARGET_IN_PLACE,
                                '%modal%' => self::TARGET_MODAL,
                            ],
                            "[{$name}]",
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
        if ($value->isExternal()) {
            return (string) $value->label;
        }
        if ($value->isInternal()) {
            try {
                $contentInfo = $this->handler->loadContentInfo($value->reference);
                $versionInfo = $this->handler->loadVersionInfo($value->reference, $contentInfo->currentVersionNo);
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
        if ($this->isEmptyValue($value)) {
            return $validationErrors;
        }

        $allowedTargets = $fieldDefinition->getFieldSettings()['allowedTargets'] ?? [];
        if (!empty($allowedTargets) && !in_array($value->target, $allowedTargets, true)) {
            $validationErrors[] = new ValidationError(
                'Target %target% is not a valid target',
                null,
                [
                    '%target%' => $value->target,
                ],
                'allowedTargets',
            );
        }

        $allowedLinkType = $fieldDefinition->getFieldSettings()['allowedLinkType'] ?? '';
        if (($allowedLinkType === self::LINK_TYPE_EXTERNAL && !$value->isExternal()) || ($allowedLinkType === self::LINK_TYPE_INTERNAL && !$value->isInternal())) {
            $validationErrors[] = new ValidationError(
                'Link type is not allowed. Must be of type %type%',
                null,
                [
                    '%type%' => $allowedLinkType,
                ],
                'allowedLinkType',
            );
        }

        $allowedContentTypes = $fieldDefinition->getFieldSettings()['selectionContentTypes'] ?? [];
        $validationError = $this->targetContentValidator->validate(
            $value,
            $allowedContentTypes,
        );
        if (isset($validationError)) {
            $validationErrors[] = $validationError;
        }

        return $validationErrors;
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
        /* @var Value $value */
        return !isset($value->reference);
    }

    public function fromHash($hash): Value
    {
        if ($hash !== null) {
            $reference = $hash['reference'];
            if (isset($reference)) {
                return new Value($reference, $hash['label'], $hash['target'], $hash['suffix']);
            }
        }

        return $this->getEmptyValue();
    }

    public function toHash(SPIValue $value): array
    {
        /* @var Value $value */
        return [
            'reference' => $value->reference,
            'label' => $value->label,
            'target' => $value->target,
            'suffix' => $value->suffix,
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
        if ($fieldValue->reference !== null) {
            $relations[Relation::FIELD] = [$fieldValue->reference];
        }

        return $relations;
    }

    public function toPersistenceValue(SPIValue $value): FieldValue
    {
        /** @var Value $value */
        if ($value->isExternal()) {
            return new FieldValue(
                [
                    'data' => [
                        'id' => null,
                        'label' => $value->label,
                        'type' => 'external',
                        'target' => $value->target,
                        'suffix' => $value->suffix,
                    ],
                    'externalData' => $value->reference,
                    'sortKey' => $this->getSortInfo($value),
                ],
            );
        }

        if ($value->isInternal()) {
            return new FieldValue(
                [
                    'data' => [
                        'id' => $value->reference,
                        'label' => $value->label,
                        'type' => 'internal',
                        'target' => $value->target,
                        'suffix' => $value->suffix,
                    ],
                    'externalData' => null,
                    'sortKey' => $this->getSortInfo($value),
                ],
            );
        }

        return new FieldValue(
            [
                'data' => [],
                'externalData' => null,
                'sortKey' => null,
            ],
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
        if (is_array($fieldValue->data) && array_key_exists('type', $fieldValue->data)) {
            $labelData = $fieldValue->data['label'] ?? null;
            $suffixData = $fieldValue->data['suffix'] ?? null;
            $targetData = $fieldValue->data['target'] ?? self::TARGET_LINK;

            if ($fieldValue->data['type'] === 'internal' && array_key_exists('id', $fieldValue->data) && is_int($fieldValue->data['id'])) {
                return new Value(
                    $fieldValue->data['id'],
                    $labelData,
                    $targetData,
                    $suffixData,
                );
            }

            if ($fieldValue->data['type'] === 'external' && is_string($fieldValue->externalData)) {
                return new Value(
                    $fieldValue->externalData,
                    $labelData,
                    $targetData,
                    $suffixData,
                );
            }
        }

        return $this->getEmptyValue();
    }

    /**
     * @param int|string|\Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo|Value $inputValue
     */
    protected function createValueFromInput($inputValue)
    {
        if ($inputValue instanceof ContentInfo) {
            return new Value($inputValue->id);
        }
        if (is_int($inputValue) || is_string($inputValue)) {
            return new Value($inputValue);
        }

        return $inputValue;
    }

    /**
     * Throws an exception if value structure is not of expected format.
     *
     * @param Value $value
     *
     *@throws \Ibexa\Contracts\Core\Repository\Exceptions\InvalidArgumentException if the value does not match the expected structure
     */
    protected function checkValueStructure(BaseValue $value): void
    {
        if (!$value->isInternal() && !$value->isExternal()) {
            throw new InvalidArgumentType(
                '$value->reference',
                'int|string',
                $value->reference,
            );
        }
    }

    protected function getSortInfo(BaseValue $value): string
    {
        return (string) $value;
    }
}
