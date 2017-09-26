<?php

namespace Ekyna\Bundle\GlsUniBoxBundle\Bridge\Commerce\Gateway;

use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\GlsUniBox\Api\Service;

/**
 * Class FDFGateway
 * @package Ekyna\Bundle\GlsUniBoxBundle\Bridge\Commerce\Gateway
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class FDFGateway extends AbstractGateway
{
    /**
     * @inheritDoc
     */
    protected function createRequest(ShipmentInterface $shipment)
    {
        $request = parent::createRequest($shipment);

        $request->setService(Service::FDF);

        // TODO make sure email is set (T1229)
        // TODO make sure mobile is set (T1230)
    }
}
