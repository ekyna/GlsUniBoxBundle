<?php

namespace Ekyna\Bundle\GlsUniBoxBundle\DependencyInjection;

use Ekyna\Bundle\GlsUniBoxBundle\Bridge\Commerce\GatewayFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * Class EkynaGlsUniBoxExtension
 * @package Ekyna\Bundle\GlsUniBoxBundle\DependencyInjection
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class EkynaGlsUniBoxExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        if (class_exists('Ekyna\Bundle\CommerceBundle\EkynaCommerceBundle')) {
            $definition = new Definition(GatewayFactory::class);
            $definition->addTag('ekyna_commerce.shipment_gateway_factory');

            $container->setDefinition('ekyna_gls_uni_box.commerce.shipment.gateway_factory', $definition);
        }
    }
}
