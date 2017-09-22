<?php

namespace Ekyna\Bundle\GlsUniBoxBundle\Bridge\Commerce\Gateway;

use Ekyna\Component\Commerce\Shipment\Gateway\AbstractGateway as BaseGateway;
use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class AbstractGateway
 * @package Ekyna\Bundle\GlsUniBoxBundle\Bridge\Commerce\Gateway
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AbstractGateway extends BaseGateway
{
    /**
     * @inheritDoc
     */
    public function process(ShipmentInterface $shipment, ServerRequestInterface $request)
    {
        // TODO: Implement process() method.
    }
}
