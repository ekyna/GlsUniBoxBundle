<?php

declare(strict_types=1);

namespace Ekyna\Bundle\GlsUniBoxBundle\Bridge\Commerce\Gateway;

use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\GlsUniBox\Api\Request;
use Ekyna\Component\GlsUniBox\Api\Service;

/**
 * Class GBPGateway
 * @package Ekyna\Bundle\GlsUniBoxBundle\Bridge\Commerce\Gateway
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class GBPGateway extends AbstractGateway
{
    protected function createRequest(ShipmentInterface $shipment): Request
    {
        $request = parent::createRequest($shipment);

        $request->setService(Service::GBP);

        return $request;
    }
}
