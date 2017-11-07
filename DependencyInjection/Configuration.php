<?php

namespace Ekyna\Bundle\GlsUniBoxBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package Ekyna\Bundle\GlsUniBoxBundle\DependencyInjection
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ekyna_gls_uni_box');

        $rootNode
            ->children()
                ->arrayNode('client')
                    ->children()
                        ->scalarNode('deposit_number')->isRequired()->cannotBeEmpty()->end() // T8700
                        ->scalarNode('customer_code')->isRequired()->cannotBeEmpty()->end() // T8915
                        ->scalarNode('contact_id')->isRequired()->cannotBeEmpty()->end() // T8914
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
