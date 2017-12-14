<?php

namespace Ekyna\Bundle\GlsUniBoxBundle\DependencyInjection;

use Ekyna\Bundle\GlsUniBoxBundle\Bridge\Commerce\GlsPlatform;
use Ekyna\Component\GlsUniBox\Generator\NumberGenerator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

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

        $generatorDef = new Definition(NumberGenerator::class);
        $generatorDef->addArgument($config['generator']['path']);
        $container->setDefinition('ekyna_gls_uni_box.number_generator', $generatorDef);

        $platformDef = new Definition(GlsPlatform::class);
        $platformDef->addTag('ekyna_commerce.shipment.gateway_platform');
        $platformDef->addArgument(new Reference('ekyna_gls_uni_box.number_generator'));
        $platformDef->addArgument(new Reference('ekyna_setting.manager'));
        $platformDef->addArgument(new Reference('ekyna_commerce.constants_helper'));
        $platformDef->addArgument(new Reference('ekyna_commerce.order_shipment.manager'));
        $platformDef->addArgument($config['client']);
        $container->setDefinition('ekyna_gls_uni_box.gateway_platform', $platformDef);
    }
}
