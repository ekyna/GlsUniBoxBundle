<?php

namespace Ekyna\Bundle\GlsUniBoxBundle\Bridge\Commerce\Gateway;

use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\GlsUniBox\Api\Service;

/**
 * Class EBPGateway
 * @package Ekyna\Bundle\GlsUniBoxBundle\Bridge\Commerce\Gateway
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class EBPGateway extends AbstractGateway
{
    /**
     * @inheritDoc
     */
    protected function createRequest(ShipmentInterface $shipment)
    {
        $request = parent::createRequest($shipment);

        $request->setService(Service::EBP);
    }
}
