<?php

namespace Ekyna\Bundle\GlsUniBoxBundle\Bridge\Commerce\Gateway;

use Doctrine\ORM\EntityManagerInterface;
use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Bundle\SettingBundle\Manager\SettingsManagerInterface;
use Ekyna\Component\Commerce\Shipment\Gateway\AbstractGateway as BaseGateway;
use Ekyna\Component\Commerce\Shipment\Gateway\Action\ActionInterface;
use Ekyna\Component\Commerce\Shipment\Gateway\Action\PrintLabel;
use Ekyna\Component\Commerce\Shipment\Model\AddressResolverAwareInterface;
use Ekyna\Component\Commerce\Shipment\Model\AddressResolverAwareTrait;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\GlsUniBox\Api;
use Ekyna\Component\GlsUniBox\Generator\NumberGeneratorInterface;
use Ekyna\Component\GlsUniBox\Renderer\LabelRenderer;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

/**
 * Class AbstractGateway
 * @package Ekyna\Bundle\GlsUniBoxBundle\Bridge\Commerce\Gateway
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractGateway extends BaseGateway implements AddressResolverAwareInterface, GlsGatewayInterface
{
    use AddressResolverAwareTrait;

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
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var Api\Client
     */
    private $client;

    /**
     * @var PhoneNumberUtil
     */
    private $phoneUtil;


    /**
     * Sets the number generator.
     *
     * @param NumberGeneratorInterface $numberGenerator
     */
    public function setNumberGenerator(NumberGeneratorInterface $numberGenerator)
    {
        $this->numberGenerator = $numberGenerator;
    }

    /**
     * Sets the setting manager.
     *
     * @param SettingsManagerInterface $settingManager
     */
    public function setSettingManager(SettingsManagerInterface $settingManager)
    {
        $this->settingManager = $settingManager;
    }

    /**
     * Sets the constants helper.
     *
     * @param ConstantsHelper $constantsHelper
     */
    public function setConstantsHelper(ConstantsHelper $constantsHelper)
    {
        $this->constantsHelper = $constantsHelper;
    }

    /**
     * Sets the entity manager.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function setEntityManager(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @inheritDoc
     */
    public function execute(ActionInterface $action)
    {
        // TODO Simplify response data before storing it in Shipment::gatewayData ...

        if ($action instanceof PrintLabel) {
            return $this->executePrintLabel($action);
        }

        // TODO throw new UnsupportedShipmentAction();

        return null;
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
     * Builds the label data for the given shipment.
     *
     * @param ShipmentInterface $shipment
     *
     * @return array|null
     */
    public function buildLabelData(ShipmentInterface $shipment)
    {
        if ($shipment->getGatewayName() !== $this->getName()) {
            return null;
        }

        $this->syncData($shipment);

        return $shipment->getGatewayData();
    }

    /**
     * @param PrintLabel $action
     */
    protected function executePrintLabel(PrintLabel $action)
    {
        $renderer = new LabelRenderer();

        $shipments = $action->getShipments();

        foreach ($shipments as $shipment) {
            if (null !== $data = $this->buildLabelData($shipment)) {
                $action->addLabel($renderer->render($data));
            }
        }
    }

    /**
     * Synchronise the gateway data if needed.
     *
     * @param ShipmentInterface $shipment
     */
    protected function syncData(ShipmentInterface $shipment)
    {
        $data = $shipment->getGatewayData();

        // TODO Check data validity/obsolescence (with hash ?)
        if (empty($data)) {
            $request = $this->createRequest($shipment);
            $data = $request->getData();

            $response = $this->getClient()->send($request);

            $data = array_replace($data, $response->getData());

            // TODO Remove unnecessary data keys

            // Set tracking number
            if (isset($data[Api\Config::T8913])) {
                $shipment->setTrackingNumber($data[Api\Config::T8913]);
            }

            $shipment->setGatewayData($data);

            // Persist shipment
            $this->entityManager->persist($shipment);
            /** @noinspection PhpMethodParametersCountMismatchInspection */
            $this->entityManager->flush($shipment); // TODO Find a way to flush once all shipments
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

        $senderAddress = $this->addressResolver->resolveSenderAddress($shipment);
        $receiverAddress = $this->addressResolver->resolveReceiverAddress($shipment);

        if (0 >= $weight = $shipment->getWeight()) {
            $weight = $this->weightCalculator->calculateShipment($shipment);
        }

        $request = new Api\Request($this->numberGenerator->generate());
        $request
            ->setDate(new \DateTime())
            ->setWeight($weight)
            ->setReceiverReference($sale->getNumber())
            ->setReceiverReference2($shipment->getNumber());

        if (null !== $customer = $sale->getCustomer()) {
            $request->setReceiverReference3($customer->getNumber());
        }

        // Receiver
        if ($shipment->isReturn()) {
            $receiverCompany = $this->settingManager->getParameter('general.site_name');
            $receiverEmail = $this->settingManager->getParameter('general.admin_email');
        } else {
            $receiverEmail = $sale->getEmail();
            if (empty($receiverCompany = $receiverAddress->getCompany())) {
                $receiverCompany = $this->constantsHelper->renderIdentity($receiverAddress);
            }
        }

        $request
            ->setReceiverCompany($receiverCompany)
            ->setReceiverStreet($receiverAddress->getStreet())
            ->setReceiverPostalCode($receiverAddress->getPostalCode())
            ->setReceiverCity($receiverAddress->getCity())
            ->setReceiverCountry(strtoupper($receiverAddress->getCountry()->getCode()))
            //->setReceiverComment()
            ->setReceiverEmail($receiverEmail);

        if (!empty($supplement = $receiverAddress->getSupplement())) {
            $request->setReceiverSupplement1($supplement);
        }
        if (!empty($phone = $receiverAddress->getPhone())) {
            $request->setReceiverPhone($this->formatPhoneNumber($phone));
        }
        if (!empty($mobile = $receiverAddress->getMobile())) {
            $request->setReceiverMobile($this->formatPhoneNumber($mobile));
        }

        // Sender
        $request
            ->setSenderCompany($senderAddress->getCompany())
            ->setSenderStreet($senderAddress->getStreet())
            ->setSenderCountry($senderAddress->getCountry()->getCode())
            ->setSenderPostalCode($senderAddress->getPostalCode())
            ->setSenderCity($senderAddress->getCity());

        return $request;
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

    /**
     * Formats the phone number.
     *
     * @param mixed $number
     *
     * @return string
     */
    private function formatPhoneNumber($number)
    {
        if ($number instanceof PhoneNumber) {
            if (null === $this->phoneUtil) {
                $this->phoneUtil = PhoneNumberUtil::getInstance();
            }

            return $this->phoneUtil->format($number, PhoneNumberFormat::INTERNATIONAL);
        }

        return (string) $number;
    }
}
