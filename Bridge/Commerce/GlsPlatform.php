<?php

namespace Ekyna\Bundle\GlsUniBoxBundle\Bridge\Commerce;

use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Bundle\GlsUniBoxBundle\Bridge\Commerce\Gateway\GlsGatewayInterface;
use Ekyna\Bundle\SettingBundle\Manager\SettingsManagerInterface;
use Ekyna\Component\Commerce\Shipment\Gateway\AbstractPlatform;
use Ekyna\Component\Commerce\Shipment\Gateway\Action\ActionInterface;
use Ekyna\Component\Commerce\Shipment\Gateway\Action\PrintLabel;
use Ekyna\Component\GlsUniBox\Api\Service;
use Ekyna\Component\GlsUniBox\Exception\InvalidArgumentException;
use GuzzleHttp\Psr7\Response;
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
     * @var ConstantsHelper
     */
    protected $constantsHelper;


    /**
     * Constructor.
     *
     * @param SettingsManagerInterface $settingManager
     * @param EngineInterface          $templating
     * @param ConstantsHelper $constantsHelper
     */
    public function __construct(
        SettingsManagerInterface $settingManager,
        EngineInterface $templating,
        ConstantsHelper $constantsHelper
    ) {
        $this->settingManager = $settingManager;
        $this->templating = $templating;
        $this->constantsHelper = $constantsHelper;
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
    public function execute(ActionInterface $action)
    {
        if ($action instanceof PrintLabel) {
            return $this->executePrintLabel($action);
        }

        return null;
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
        $gateway = new $class($name, $this->processGatewayConfig($config));

        $gateway->setSettingManager($this->settingManager);
        $gateway->setTemplating($this->templating);
        $gateway->setConstantsHelper($this->constantsHelper);

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
                ->enumNode('service')
                    ->info('Service')
                    ->values(Service::getChoices())
                    ->isRequired()
                ->end()
            ->end();
    }

    /**
     * @param PrintLabel $action
     *
     * @return Response|null
     */
    protected function executePrintLabel(PrintLabel $action)
    {
        $shipments = $action->getShipments();

        $labels = [];

        foreach ($shipments as $shipment) {
            if ($shipment->getPlatformName() !== static::NAME) {
                continue;
            }

            $gateway = $this->registry->getGateway($shipment->getGatewayName());
            if (!$gateway instanceof GlsGatewayInterface) {
                throw new \LogicException("Something almost impossible just append :-°");
            }

            if (null !== $label = $gateway->buildLabel($shipment)) {
                $labels[] = $label;
            }
        }

        if (empty($labels)) {
            return null;
        }

        /** @noinspection PhpTemplateMissingInspection */
        $rendered = $this->templating->render('@GlsUniBox/labels.html.twig', [
            'labels' => $labels
        ]);

        return new Response(200, ['Content-Type' => 'text/html'], $rendered);
    }

    /**
     * @inheritDoc
     */
    public function supports(ActionInterface $action)
    {
        return $action instanceof PrintLabel;
    }

    /**
     * @inheritDoc
     */
    public function getActions()
    {
        return [
            PrintLabel::class
        ];
    }
}
