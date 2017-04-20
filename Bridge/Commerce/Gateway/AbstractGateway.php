<?php

declare(strict_types=1);

namespace Ekyna\Bundle\GlsUniBoxBundle\Bridge\Commerce\Gateway;

use DateTime;
use Decimal\Decimal;
use Ekyna\Bundle\CommerceBundle\Service\ConstantsHelper;
use Ekyna\Bundle\SettingBundle\Manager\SettingManagerInterface;
use Ekyna\Component\Commerce\Exception\ShipmentGatewayException;
use Ekyna\Component\Commerce\Shipment\Gateway;
use Ekyna\Component\Commerce\Shipment\Model as Shipment;
use Ekyna\Component\GlsUniBox\Api;
use Ekyna\Component\GlsUniBox\Generator\NumberGeneratorInterface;
use Ekyna\Component\GlsUniBox\Renderer\LabelRenderer;
use Exception;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

/**
 * Class AbstractGateway
 * @package Ekyna\Bundle\GlsUniBoxBundle\Bridge\Commerce\Gateway
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractGateway extends Gateway\AbstractGateway
{
    protected const TRACKING_URL = 'https://gls-group.eu/FR/fr/suivi-colis?match=%s';

    protected NumberGeneratorInterface $numberGenerator;
    protected SettingManagerInterface  $settingManager;
    protected ConstantsHelper          $constantsHelper;

    private ?Api\Client      $client    = null;
    private ?PhoneNumberUtil $phoneUtil = null;

    public function setNumberGenerator(NumberGeneratorInterface $numberGenerator): void
    {
        $this->numberGenerator = $numberGenerator;
    }

    public function setSettingManager(SettingManagerInterface $settingManager): void
    {
        $this->settingManager = $settingManager;
    }

    public function setConstantsHelper(ConstantsHelper $constantsHelper): void
    {
        $this->constantsHelper = $constantsHelper;
    }

    public function getActions(): array
    {
        return [
            Gateway\GatewayActions::SHIP,
            Gateway\GatewayActions::CANCEL,
            Gateway\GatewayActions::PRINT_LABEL,
            //GatewayActions::TRACK,
        ];
    }

    public function getCapabilities(): int
    {
        return static::CAPABILITY_SHIPMENT; // | static::CAPABILITY_PARCEL
    }

    public function ship(Shipment\ShipmentInterface $shipment): bool
    {
        $this->supportShipment($shipment);

        if ($this->hasTrackingNumber($shipment)) {
            return false;
        }

        // TODO deal with parcels

        // TODO Check data validity/obsolescence (with hash ?)
        $request = $this->createRequest($shipment);

        $data = $request->getData();

        try {
            $response = $this->getClient()->send($request);
        } catch (Exception $e) {
            throw new ShipmentGatewayException($e->getMessage(), $e->getCode(), $e);
        }

        $data = array_replace($data, $response->getData());

        if (!$response->isSuccessful()) {
            throw new ShipmentGatewayException('Error code: ' . $response->getErrorCode());
        }

        // TODO Remove unnecessary data keys

        // Set tracking number
        if (isset($data[Api\Config::T8913])) {
            $shipment->setTrackingNumber($data[Api\Config::T8913]);
        }

        $shipment->setGatewayData($data);

        $this->persister->persist($shipment);

        parent::ship($shipment);

        return true;
    }

    public function track(Shipment\ShipmentDataInterface $shipment): ?string
    {
        $this->supportShipment($shipment);

        if (!empty($number = $shipment->getTrackingNumber())) {
            return sprintf(self::TRACKING_URL, $number);
        }

        return null;
    }

    public function printLabel(Shipment\ShipmentDataInterface $shipment, array $types = null): array
    {
        $this->supportShipment($shipment);

        if ($shipment instanceof Shipment\ShipmentParcelInterface) {
            $s = $shipment->getShipment();
        } else {
            $s = $shipment;
        }

        /** @var Shipment\ShipmentInterface $s */
        $this->ship($s);

        if (empty($types)) {
            $types = $this->getDefaultLabelTypes();
        }

        // TODO deal with parcels

        if (!$shipment->hasLabels()) {
            if (!empty($data = $s->getGatewayData())) {
                $renderer = new LabelRenderer();
                $shipment->addLabel(
                    $this->createLabel(
                        $renderer->render($data),
                        Shipment\ShipmentLabelInterface::TYPE_SHIPMENT,
                        Shipment\ShipmentLabelInterface::FORMAT_PNG,
                        Shipment\ShipmentLabelInterface::SIZE_A6
                    )
                );

                $this->persister->persist($s);
            }
        }

        foreach ($shipment->getLabels() as $label) {
            if (in_array($label->getType(), $types, true)) {
                return [$label];
            }
        }

        return [];
    }

    /**
     * Returns the default label types.
     */
    protected function getDefaultLabelTypes(): array
    {
        return [Shipment\ShipmentLabelInterface::TYPE_SHIPMENT];
    }

    /**
     * Creates an api request.
     */
    protected function createRequest(Shipment\ShipmentInterface $shipment): Api\Request
    {
        $sale = $shipment->getSale();

        $senderAddress = $this->addressResolver->resolveSenderAddress($shipment);
        $receiverAddress = $this->addressResolver->resolveReceiverAddress($shipment);

        if (0 >= $weight = $shipment->getWeight()) {
            $weight = $this->weightCalculator->calculateShipment($shipment);
        }

        // Force weight > 100g
        $weight = max($weight, new Decimal('0.1'));

        $request = new Api\Request($this->numberGenerator->generate());
        $request
            ->setDate(new DateTime())
            ->setWeight($weight->toFixed(3))
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

    protected function getClient(): Api\Client
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
     * @param PhoneNumber|string $number
     */
    private function formatPhoneNumber($number): string
    {
        if ($number instanceof PhoneNumber) {
            if (null === $this->phoneUtil) {
                $this->phoneUtil = PhoneNumberUtil::getInstance();
            }

            return $this->phoneUtil->format($number, PhoneNumberFormat::INTERNATIONAL);
        }

        return (string)$number;
    }
}
