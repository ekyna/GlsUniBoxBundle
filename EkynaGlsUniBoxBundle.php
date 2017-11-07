<?php

namespace Ekyna\Bundle\GlsUniBoxBundle;

use Ekyna\Component\GlsUniBox\Bridge\Symfony\DependencyInjection\TwigCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class EkynaGlsUniBoxBundle
 * @package Ekyna\Bundle\GlsUniBoxBundle
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class EkynaGlsUniBoxBundle extends Bundle
{
    /**
     * @inheritDoc
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new TwigCompilerPass());
    }
}
