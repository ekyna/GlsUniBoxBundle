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
                ->scalarNode('deposit_number')->isRequired()->end() // T8700
                ->scalarNode('customer_code')->isRequired()->end() // T8915
                ->scalarNode('contact_id')->isRequired()->end() // T8914
            ->end();

        return $treeBuilder;
    }
}
