<?php

declare(strict_types=1);

namespace Netgen\IbexaFieldTypeEnhancedLink\FieldType;

use Ibexa\Contracts\Core\Persistence\Content;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Core\FieldType\ValidationError;

/**
 * Validator for checking existence of content and its content type.
 */
class TargetContentValidator
{
    /** @var \Ibexa\Contracts\Core\Persistence\Content\Handler */
    private $contentHandler;

    /** @var \Ibexa\Contracts\Core\Persistence\Content\Type\Handler */
    private $contentTypeHandler;

    public function __construct(
        Content\Handler $contentHandler,
        Content\Type\Handler $contentTypeHandler
    ) {
        $this->contentHandler = $contentHandler;
        $this->contentTypeHandler = $contentTypeHandler;
    }

    public function validate(int $value, array $allowedContentTypes = []): ?ValidationError
    {
        try {
            $content = $this->contentHandler->load($value);
            $contentType = $this->contentTypeHandler->load($content->versionInfo->contentInfo->contentTypeId);

            if (!empty($allowedContentTypes) && !in_array($contentType->identifier, $allowedContentTypes, true)) {
                return new ValidationError(
                    'Content Type %contentTypeIdentifier% is not a valid relation target',
                    null,
                    [
                        '%contentTypeIdentifier%' => $contentType->identifier,
                    ],
                    'targetContentId'
                );
            }
        } catch (NotFoundException $e) {
            return new ValidationError(
                'Content with identifier %contentId% is not a valid relation target',
                null,
                [
                    '%contentId%' => $value,
                ],
                'targetContentId'
            );
        }

        return null;
    }
}