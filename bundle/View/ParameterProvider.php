<?php

declare(strict_types=1);

namespace Netgen\IbexaFieldTypeEnhancedLinkBundle\View;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;
use eZ\Publish\API\Repository\FieldTypeService;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\Core\MVC\Symfony\FieldType\View\ParameterProviderInterface;

final class ParameterProvider implements ParameterProviderInterface
{
    private ContentService $contentService;
    private FieldTypeService $fieldTypeService;

    public function __construct(
        ContentService $contentService,
        FieldTypeService $fieldTypeService
    ) {
        $this->contentService = $contentService;
        $this->fieldTypeService = $fieldTypeService;
    }

    public function getViewParameters(Field $field): array
    {
        return [
            'available' => $this->isLinkAvailable($field),
        ];
    }

    private function isLinkAvailable(Field $field): bool
    {
        /** @var \Netgen\IbexaFieldTypeEnhancedLink\FieldType\Value $value */
        $value = $field->value;

        if ($this->fieldTypeService->getFieldType($field->fieldTypeIdentifier)->isEmptyValue($value)) {
            return false;
        }

        if ($value->isTypeExternal()) {
            return true;
        }

        try {
            $contentInfo = $this->contentService->loadContentInfo($value->reference);
        } catch (NotFoundException|UnauthorizedException $exception) {
            return false;
        }

        return !$contentInfo->isTrashed();
    }
}
