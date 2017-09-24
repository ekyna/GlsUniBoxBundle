<?php

namespace Ekyna\Bundle\GlsUniBoxBundle\Bridge\Commerce\Gateway;

use Ekyna\Bundle\SettingBundle\Manager\SettingsManagerInterface;
use Ekyna\Component\Commerce\Shipment\Gateway\AbstractGateway as BaseGateway;
use Ekyna\Component\Commerce\Shipment\Gateway\Action\ActionInterface;
use Ekyna\Component\Commerce\Shipment\Gateway\Action\PrintLabel;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\GlsUniBox\Api;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Templating\EngineInterface;

/**
 * Class AbstractGateway
 * @package Ekyna\Bundle\GlsUniBoxBundle\Bridge\Commerce\Gateway
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractGateway extends BaseGateway
{
    /**
     * @var SettingsManagerInterface
     */
    protected $settingManager;

    /**
     * @var EngineInterface
     */
    protected $templating;

    /**
     * @var Api\Client
     */
    private $client;


    /**
     * Sets the settingManager.
     *
     * @param SettingsManagerInterface $settingManager
     */
    public function setSettingManager(SettingsManagerInterface $settingManager)
    {
        $this->settingManager = $settingManager;
    }

    /**
     * Sets the templating engine.
     *
     * @param EngineInterface $templating
     */
    public function setTemplating(EngineInterface $templating)
    {
        $this->templating = $templating;
    }

    /**
     * @inheritDoc
     */
    public function process(ShipmentInterface $shipment, ServerRequestInterface $request)
    {
        // TODO: Implement process() method.

        // TODO Simplify response data before storing it in Shipment::gatewayData ...
    }

    /**
     * @inheritDoc
     */
    public function execute(ActionInterface $action)
    {
        // TODO Simplify response data before storing it in Shipment::gatewayData ...

        if ($action instanceof PrintLabel) {
            return $this->printLabel($action);
        }

        // TODO throw new UnsupportedShipmentAction();

        return null;
    }

    protected function printLabel(PrintLabel $action)
    {
        $shipments = $action->getShipments();

        foreach ($shipments as $shipment) {
            if ($shipment->getGatewayName() !== $this->getName()) {
                continue; // TODO warn user about skipped shipment
            }


        }
    }

    protected function syncData(ShipmentInterface $shipment)
    {
        $data = $shipment->getGatewayData();

        if (empty($data)/* TODO || compose hash */) {
            $request = $this->createRequest($shipment);

            $response = $this->getClient()->send($request);
        }
    }

    /**
     * Creates an api request.
     *
     * @param ShipmentInterface $shipment
     *
     * @return Api\Request
     */
    protected function createRequest(ShipmentInterface $shipment)
    {
        $sale = $shipment->getSale();
        $customer = $sale->getCustomer();

        // TODO isReturn() ?

        $request = new Api\Request();
        $request
            ->setDate(new \DateTime())
            ->setReceiverReference($customer->getNumber())
            ->setReceiverCompany('Company')
            ->setOriginReference('0200000000050000FR')
            ->setWeight(12.32)
            ->setReceiverStreet('ALLEE DE GASCOGNE')
            ->setReceiverSupplement2('LOT. FEYDEAU OUEST')
            ->setReceiverCountry('FR')
            ->setReceiverPostalCode('33370')
            ->setReceiverCity('ARTIGUES PRES BORDEAUX')
            ->setSenderCompany('IT - RESERVE TEST INTERNET')
            ->setSenderStreet('14, RUE MICHEL LABROUSSE')
            ->setSenderCountry('FR')
            ->setSenderPostalCode('31037')
            ->setSenderCity('TOULOUSE CEDEX 1');

        return $request;
    }


    /**
     * @inheritDoc
     */
    public function getActions(ShipmentInterface $shipment = null)
    {
        $actions = [
            PrintLabel::class,
        ];

        /*if (null !== $shipment) {

        }*/

        return $actions;
    }

    /**
     * Returns the api client.
     *
     * @return Api\Client
     */
    protected function getClient()
    {
        if (null !== $this->client) {
            return $this->client;
        }

        return $this->client = new Api\Client([
            Api\Config::T8700 => $this->config['deposit_number'],
            Api\Config::T8915 => $this->config['customer_code'],
            Api\Config::T8914 => $this->config['contact_id'],
        ]);
    }
}
