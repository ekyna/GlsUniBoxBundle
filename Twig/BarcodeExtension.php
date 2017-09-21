<?php

namespace Ekyna\Bundle\GlsUniBoxBundle\Twig;

use Com\Tecnick\Barcode\Barcode;

/**
 * Class BarcodeExtension
 * @package Ekyna\Bundle\GlsUniBoxBundle\Twig
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BarcodeExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('barcode_datamatrix', [$this, 'getBarcodeDatamatrix'], ['is_safe' => ['html']]),
        ];
    }

    public function getBarcodeDatamatrix($data)
    {
        $barcode = new Barcode();

        $bobj = $barcode->getBarcodeObj(
            'DATAMATRIX,H',             // barcode type and additional comma-separated parameters
            $data,                      // data string to encode
            256,                        // bar height (use absolute or negative value as multiplication factor)
            256,                        // bar width (use absolute or negative value as multiplication factor)
            'black',                    // foreground color
            array(0, 0, 0, 0)           // padding (use absolute or negative values as multiplication factors)
        )->setBackgroundColor('white'); // background color

        return base64_encode($bobj->getPngData());
    }
}
