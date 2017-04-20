<?php

declare(strict_types=1);

namespace Ekyna\Bundle\GlsUniBoxBundle\DependencyInjection;

use Ekyna\Bundle\GlsUniBoxBundle\Bridge\Commerce\GlsPlatform;
use Ekyna\Bundle\GlsUniBoxBundle\Controller\LabelController;
use Ekyna\Component\Commerce\Bridge\Symfony\DependencyInjection\ShipmentGatewayRegistryPass;
use Ekyna\Component\GlsUniBox\Generator\NumberGenerator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class EkynaGlsUniBoxExtension
 * @package Ekyna\Bundle\GlsUniBoxBundle\DependencyInjection
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class EkynaGlsUniBoxExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container
            ->register('ekyna_gls_uni_box.generator.number', NumberGenerator::class)
            ->addArgument($config['generator']['path']);

        $container
            ->register('ekyna_gls_uni_box.gateway_platform', GlsPlatform::class)
            ->addArgument(new Reference('ekyna_gls_uni_box.generator.number'))
            ->addArgument(new Reference('ekyna_setting.manager'))
            ->addArgument(new Reference('ekyna_commerce.helper.constants'))
            ->addArgument($config['client'])
            ->addTag(ShipmentGatewayRegistryPass::PLATFORM_TAG);

        if ('dev' !== $container->getParameter('kernel.debug')) {
            return;
        }

        $container
            ->register('ekyna_gls_uni_box.controller.label', LabelController::class)
            ->addArgument(new Reference('twig'));

        $container
            ->setAlias(LabelController::class, 'ekyna_gls_uni_box.controller.label')
            ->setPublic(true);
    }
}
