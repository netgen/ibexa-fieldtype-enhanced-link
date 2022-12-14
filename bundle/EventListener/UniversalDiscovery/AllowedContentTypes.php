<?php

declare(strict_types=1);

namespace Netgen\IbexaFieldTypeEnhancedLinkBundle\EventListener\UniversalDiscovery;

use Ibexa\AdminUi\UniversalDiscovery\Event\ConfigResolveEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use function array_intersect;
use function array_values;
use function in_array;

class AllowedContentTypes implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ConfigResolveEvent::NAME => ['onUdwConfigResolve'],
        ];
    }

    public function onUdwConfigResolve(ConfigResolveEvent $event): void
    {
        $context = $event->getContext();
        $config = $event->getConfig();

        if (!in_array($event->getConfigName(), ['single', 'multiple'], true)) {
            return;
        }

        if (
            !isset($context['type'], $context['allowed_content_types'])
            || 'object_relation' !== $context['type']
        ) {
            return;
        }

        if (!empty($config['allowed_content_types'])) {
            $intersection = array_values(
                array_intersect(
                    $config['allowed_content_types'],
                    $context['allowed_content_types'],
                ),
            );

            $config['allowed_content_types'] = empty($intersection)
                ? null
                : $intersection;
        } else {
            $config['allowed_content_types'] = empty($context['allowed_content_types'])
                ? null
                : $context['allowed_content_types'];
        }

        $event->setConfig($config);
    }
}
