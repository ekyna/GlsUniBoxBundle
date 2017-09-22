<?php

namespace Ekyna\Bundle\GlsUniBoxBundle\Bridge\Commerce;

use Ekyna\Component\Commerce\Shipment\Gateway\AbstractFactory;
use Symfony\Component\Config\Definition;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * Class GlsGatewayFactory
 * @package Ekyna\Bundle\GlsUniBoxBundle\Bridge\Commerce
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class GatewayFactory extends AbstractFactory
{
    const NAME = 'GLS';


    /**
     * @inheritDoc
     */
    public function getName()
    {
        return static::NAME;
    }

    /**
     * @inheritDoc
     */
    public function createGateway($name, array $config = [])
    {
        return new Gateway\BPGateway($name, $this->processGatewayConfig($config));
    }

    /**
     * @inheritDoc
     */
    protected function createConfigDefinition(Definition\Builder\NodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->scalarNode('deposit_number')
                    ->info('Code dépôt')
                    ->isRequired()
                ->end()
                ->scalarNode('customer_code')
                    ->info('Code client')
                    ->isRequired()
                ->end()
                ->scalarNode('contact_id')
                    ->info('Contact ID')
                    ->isRequired()
                ->end()
            ->end();
    }
}
