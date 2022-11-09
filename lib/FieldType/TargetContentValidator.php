<?php

declare(strict_types=1);

namespace Netgen\IbexaFieldTypeEnhancedLink\FieldType;

use Ibexa\Contracts\Core\Persistence\Content;
use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Core\FieldType\ValidationError;

use function in_array;

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

    public function validate(Value $value, array $allowedContentTypes = [], array $allowedTargets = []): ?ValidationError
    {
        try {
            if ($value->isInternal()) {
                $content = $this->contentHandler->load((int) $value->reference);
                $contentType = $this->contentTypeHandler->load($content->versionInfo->contentInfo->contentTypeId);
                if (!empty($allowedContentTypes) && !in_array($contentType->identifier, $allowedContentTypes, true)) {
                    return new ValidationError(
                        'Content Type %contentTypeIdentifier% is not a valid relation target',
                        null,
                        [
                            '%contentTypeIdentifier%' => $contentType->identifier,
                        ],
                        'targetContentId',
                    );
                }
            }
            if (!empty($allowedTargets) && !in_array($value->target, $allowedTargets, true)) {
                return new ValidationError(
                    'Target %target% is not a valid target',
                    null,
                    [
                        '%target%' => $value->target,
                    ],
                    'targetContentId',
                );
            }
        } catch (NotFoundException $e) {
            return new ValidationError(
                'Content with identifier %contentId% is not a valid enhanced link target',
                null,
                [
                    '%contentId%' => (int) $value->reference,
                ],
                'targetContentId',
            );
        }

        return null;
    }
}
