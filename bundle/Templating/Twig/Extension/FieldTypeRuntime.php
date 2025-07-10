<?php

declare(strict_types=1);

namespace Netgen\IbexaFieldTypeEnhancedLinkBundle\Templating\Twig\Extension;

use Ibexa\Contracts\Core\Repository\Repository;

class FieldTypeRuntime
{
    private Repository $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    public function hasLocation(int $reference): bool
    {
        /** @var bool $callback */
        $callback = fn (): bool => $this->repository->getContentService()->loadContentInfo($reference)->mainLocationId !== null;

        return $this->repository->sudo($callback);
    }
}
