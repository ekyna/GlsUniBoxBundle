<?php

namespace Ekyna\Bundle\GlsUniBoxBundle\Bridge\Commerce;

use Ekyna\Bundle\SettingBundle\Manager\SettingsManagerInterface;
use Ekyna\Component\Commerce\Shipment\Gateway\AbstractPlatform;
use Symfony\Component\Config\Definition;
use Symfony\Component\Templating\EngineInterface;

/**
 * Class GlsPlatform
 * @package Ekyna\Bundle\GlsUniBoxBundle\Bridge\Commerce
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class GlsPlatform extends AbstractPlatform
{
    const NAME = 'GLS';

    /**
     * @var SettingsManagerInterface
     */
    private $settingManager;

    /**
     * @var EngineInterface
     */
    private $templating;


    /**
     * Constructor.
     *
     * @param SettingsManagerInterface $settingManager
     * @param EngineInterface          $templating
     */
    public function __construct(SettingsManagerInterface $settingManager, EngineInterface $templating)
    {
        $this->settingManager = $settingManager;
        $this->templating = $templating;
    }

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
        $gateway = new Gateway\BPGateway($name, $this->processGatewayConfig($config));

        $gateway->setSettingManager($this->settingManager);
        $gateway->setTemplating($this->templating);

        return $gateway;
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
