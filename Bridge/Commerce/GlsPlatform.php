<?php

namespace Ekyna\Bundle\GlsUniBoxBundle\Bridge\Commerce;

use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Bundle\SettingBundle\Manager\SettingsManagerInterface;
use Ekyna\Component\Commerce\Shipment\Gateway\AbstractPlatform;
use Ekyna\Component\Commerce\Shipment\Gateway\PlatformActions;
use Ekyna\Component\GlsUniBox\Api\Service;
use Ekyna\Component\GlsUniBox\Exception\InvalidArgumentException;
use Ekyna\Component\GlsUniBox\Generator\NumberGeneratorInterface;
use Symfony\Component\Config\Definition;

/**
 * Class GlsPlatform
 * @package Ekyna\Bundle\GlsUniBoxBundle\Bridge\Commerce
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class GlsPlatform extends AbstractPlatform
{
    const NAME = 'GLS';

    /**
     * @var NumberGeneratorInterface
     */
    protected $numberGenerator;

    /**
     * @var SettingsManagerInterface
     */
    protected $settingManager;

    /**
     * @var ConstantsHelper
     */
    protected $constantsHelper;

    /**
     * @var array
     */
    protected $defaultConfig;


    /**
     * Constructor.
     *
     * @param NumberGeneratorInterface $numberGenerator
     * @param SettingsManagerInterface $settingManager
     * @param ConstantsHelper          $constantsHelper
     * @param array                    $defaultConfig
     */
    public function __construct(
        NumberGeneratorInterface $numberGenerator,
        SettingsManagerInterface $settingManager,
        ConstantsHelper $constantsHelper,
        array $defaultConfig = []
    ) {
        $this->numberGenerator = $numberGenerator;
        $this->settingManager = $settingManager;
        $this->constantsHelper = $constantsHelper;
        $this->defaultConfig = $defaultConfig;
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
    public function getActions()
    {
        return [
            PlatformActions::PRINT_LABELS,
        ];
    }

    /**
     * @inheritDoc
     */
    public function createGateway($name, array $config = [])
    {
        $class = sprintf('Ekyna\Bundle\GlsUniBoxBundle\Bridge\Commerce\Gateway\%sGateway', $config['service']);
        if (!class_exists($class)) {
            throw new InvalidArgumentException(sprintf("Unexpected service '%s'", $config['service']));
        }

        /** @var Gateway\AbstractGateway $gateway */
        $gateway = new $class($this, $name, $this->processGatewayConfig($config));

        $gateway->setNumberGenerator($this->numberGenerator);
        $gateway->setSettingManager($this->settingManager);
        $gateway->setConstantsHelper($this->constantsHelper);

        return $gateway;
    }

    /**
     * @inheritDoc
     */
    public function getConfigDefaults()
    {
        return $this->defaultConfig;
    }

    /**
     * @inheritDoc
     */
    protected function createConfigDefinition(Definition\Builder\NodeDefinition $rootNode)
    {
        /** @noinspection PhpUndefinedMethodInspection */
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
                ->enumNode('service')
                    ->info('Service')
                    ->values(Service::getChoices())
                    ->isRequired()
                ->end()
            ->end();
    }
}
