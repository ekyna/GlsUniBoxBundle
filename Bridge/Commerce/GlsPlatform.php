<?php

declare(strict_types=1);

namespace Ekyna\Bundle\GlsUniBoxBundle\Bridge\Commerce;

use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Bundle\SettingBundle\Manager\SettingManagerInterface;
use Ekyna\Component\Commerce\Shipment\Gateway\AbstractPlatform;
use Ekyna\Component\Commerce\Shipment\Gateway\GatewayInterface;
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
    private const NAME = 'GLS';

    protected NumberGeneratorInterface $numberGenerator;
    protected SettingManagerInterface $settingManager;
    protected ConstantsHelper $constantsHelper;
    protected array $defaultConfig;


    public function __construct(
        NumberGeneratorInterface $numberGenerator,
        SettingManagerInterface $settingManager,
        ConstantsHelper $constantsHelper,
        array $defaultConfig = []
    ) {
        $this->numberGenerator = $numberGenerator;
        $this->settingManager = $settingManager;
        $this->constantsHelper = $constantsHelper;
        $this->defaultConfig = $defaultConfig;
    }

    public function getName(): string
    {
        return static::NAME;
    }

    public function getActions(): array
    {
        return [
            PlatformActions::PRINT_LABELS,
        ];
    }

    public function createGateway(string $name, array $config = []): GatewayInterface
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

    public function getConfigDefaults(): array
    {
        return $this->defaultConfig;
    }

    protected function createConfigDefinition(Definition\Builder\NodeDefinition $rootNode): void
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
                ->enumNode('service')
                    ->info('Service')
                    ->values(Service::getChoices())
                    ->isRequired()
                ->end()
            ->end();
    }
}
