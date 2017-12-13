<?php

namespace Ekyna\Bundle\GlsUniBoxBundle\Bridge\Commerce\Gateway;

use Ekyna\Component\Commerce\Shipment\Model\ShipmentInterface;

/**
 * Interface GlsGatewayInterface
 * @package Ekyna\Bundle\GlsUniBoxBundle\Bridge\Commerce\Gateway
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface GlsGatewayInterface
{
    /**
     * Builds the label data for the given shipment.
     *
     * @param ShipmentInterface $shipment
     *
     * @return array|null
     */
    public function buildLabelData(ShipmentInterface $shipment);
}
