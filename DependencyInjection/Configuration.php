<?php

declare(strict_types=1);

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
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder('ekyna_gls_uni_box');

        $node = $builder->getRootNode();

        $node
            ->children()
                ->arrayNode('generator')
                    ->children()
                        ->scalarNode('path')
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->defaultValue('%kernel.root_dir%/../var/gls_uni_box_number')
                            ->end()
                    ->end()
                ->end()
                ->arrayNode('client')
                    ->children()
                        ->scalarNode('deposit_number')->isRequired()->cannotBeEmpty()->end() // T8700
                        ->scalarNode('customer_code')->isRequired()->cannotBeEmpty()->end() // T8915
                        ->scalarNode('contact_id')->isRequired()->cannotBeEmpty()->end() // T8914
                    ->end()
                ->end()
            ->end();

        return $builder;
    }
}
