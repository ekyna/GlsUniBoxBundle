<?php

declare(strict_types=1);

namespace Ekyna\Bundle\GlsUniBoxBundle\Bridge\Commerce\Gateway;

use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Ekyna\Component\GlsUniBox\Api\Request;
use Ekyna\Component\GlsUniBox\Api\Service;

/**
 * Class SHDGateway
 * @package Ekyna\Bundle\GlsUniBoxBundle\Bridge\Commerce\Gateway
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SHDGateway extends AbstractGateway
{
    protected function createRequest(ShipmentInterface $shipment): Request
    {
        $request = parent::createRequest($shipment);

        $request->setService(Service::SHD);

        // TODO make sure email is set (T1229)
        // TODO make sure mobile is set (T1230)
        // TODO make sure withdrawal point is set (T8237)

        return $request;
    }
}
