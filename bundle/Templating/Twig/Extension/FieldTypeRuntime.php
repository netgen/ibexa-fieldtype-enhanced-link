<?php

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
        return $this->repository->sudo(
            fn (): bool => $this->repository->getContentService()->loadContentInfo($reference)->mainLocationId !== null
        );
    }
}
