<?php

declare(strict_types=1);

namespace Netgen\IbexaFieldTypeEnhancedLinkBundle\View;

use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Exceptions\UnauthorizedException;
use Ibexa\Contracts\Core\Repository\FieldTypeService;
use Ibexa\Contracts\Core\Repository\Values\Content\Field;
use Ibexa\Core\MVC\Symfony\FieldType\View\ParameterProviderInterface;
use Netgen\IbexaFieldTypeEnhancedLink\FieldType\Value;

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
        /** @var Value $value */
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
