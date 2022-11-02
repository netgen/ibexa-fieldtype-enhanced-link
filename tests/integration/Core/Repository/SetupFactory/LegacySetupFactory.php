<?php

namespace Netgen\IbexaFieldTypeEnhancedLink\Tests\Integration\Core\Repository\SetupFactory;

use Ibexa\Contracts\Core\Test\Repository\SetupFactory\Legacy as CoreLegacySetupFactory;
use RuntimeException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class LegacySetupFactory extends CoreLegacySetupFactory
{
    /**
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $containerBuilder
     *
     * @throws \Exception
     */
    protected function externalBuildContainer(ContainerBuilder $containerBuilder): void
    {
        parent::externalBuildContainer($containerBuilder);

        $settingsPath = realpath(__DIR__ . '/../../../../../bundle/Resources/config/');

        if ($settingsPath === false) {
            throw new RuntimeException('Unable to find EnhancedLink package settings');
        }

        $loader = new YamlFileLoader($containerBuilder, new FileLocator($settingsPath));

        $loader->load('services.yaml');
    }
}
