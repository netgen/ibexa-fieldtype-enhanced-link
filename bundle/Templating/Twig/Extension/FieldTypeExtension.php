<?php

declare(strict_types=1);

namespace Netgen\IbexaFieldTypeEnhancedLinkBundle\Templating\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class FieldTypeExtension extends AbstractExtension
{
    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'ng_enhancedlink_has_location',
                [FieldTypeRuntime::class, 'hasLocation'],
                ['is_safe' => ['html']],
            ),
        ];
    }
}
