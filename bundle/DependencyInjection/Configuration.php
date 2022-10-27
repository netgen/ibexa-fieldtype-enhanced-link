<?php

namespace Netgen\IbexaFieldTypeEnhancedLinkBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getAlias(): string
    {
        return 'netgen_ibexa_fieldtype_enhanced_link';
    }

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('netgen_ibexa_fieldtype_enhanced_link');

        return $treeBuilder;
    }
}
