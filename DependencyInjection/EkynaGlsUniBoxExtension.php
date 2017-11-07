<?php

namespace Ekyna\Bundle\GlsUniBoxBundle\DependencyInjection;

use Ekyna\Bundle\GlsUniBoxBundle\Bridge\Commerce\GlsPlatform;
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

        $definition = new Definition(GlsPlatform::class);
        $definition->addTag('ekyna_commerce.shipment.gateway_platform');
        $definition->addArgument(new Reference('ekyna_setting.manager'));
        $definition->addArgument(new Reference('templating'));
        $definition->addArgument(new Reference('ekyna_commerce.constants_helper'));
        $definition->addArgument($config['client']);

        $container->setDefinition('ekyna_gls_uni_box.gateway_platform', $definition);
    }
}
