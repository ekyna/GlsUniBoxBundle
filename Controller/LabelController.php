<?php

namespace Ekyna\Bundle\GlsUniBoxBundle\Controller;

use Ekyna\Component\GlsUniBox\Api\Response;
use Ekyna\Component\GlsUniBox\Renderer\LabelRenderer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class LabelController
 * @package Ekyna\Bundle\GlsUniBoxBundle\Controller
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class LabelController extends Controller
{
    /**
     * Label debug action.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function debugAction()
    {
        $exampleBody = <<<EOT
\\\\\\\\\\GLS\\\\\\\\\\T8915:2500011329|T8914:250000007B|T8975:1700000012420000FR|T8904:1|T8905:1|T082:UNIQUENO|T090:NOSAVE|T8700:FR0031|T810:IT - RESERVE TESTINTERNET|T820:14, RUE MICHEL LABROUSSE|T821:FR|T822:31037|T823:TOULOUSE CEDEX1|T200:SHD|T750:SHOP DELIVERY SERVICE|T1229:jacques.dupont@gmail.com|T1230:06 01 020304|T8237:2500833212|T080:V81_8_0001|T520:15072013|T510:ab|T500:FR0031|T103:FR0031|T560:FR01|T8797:IBOXCUS|T540:16.07.2013|T541:14:16|T854:|T210:|ARTNO:Standard|T530:2.00|T206:BP|T207:SHD|T8702:1|T8973:1|T100:FR|CTRA2:FR|T751:M DUPONTJACQUES|T752:|T860:PROXI SUPER XL|T861:|T862:|T863:31-33RUE DE LA TOURAINE|T330:31100|T864:TOULOUSE|T852:2500833212|T859:|T8913:005SXKM3|T8982:005SXKM3|T8981:|ALTZIP:31100|FLOCCODE:FR0031|TOURNO:6397|T320:6397|TOURTYPE:21102|SORT1:0|T310:0|T331:31100|T890:9250|ROUTENO:0|FLOCNO:105|T101:0031|T105:FR|T300:25093109|T805:|NDI:|T8970:A|T8971:A|T8980:AA|T8974:n|T8950:Tour|T8951:ZipCode|T8952:Your GLS TrackID|T8953:Product|T8954:Service Code|T8955:DeliveryAddress|T8956:Contact|T8958:Contact|T8957:CustomerID|T8959:Phone|T8960:Note|T8961:Parcel|T8962:Weight|T8965:ContactID|T8976:1700000012420000FR|T8916:|T8972:005SXKM3|T8902:AFR0031FR003125000113292501369229005SXKM3AAn 0 639731100 002000010011700000012420000FR1700000012420000FR |T8903:A\7CPROXI SUPER\7C31-33 RUE DE LA TOURAINE\7CTOULOUSE\7C\7C\7C\7C|T102:FR0031|PRINTINFO:|PRINT1:|RESULT:E000:|PRINT0:frGLSintermecpf4i.int01|/////GLS/////
EOT;

        $response = Response::create($exampleBody);

        $renderer = new LabelRenderer();
        $raw = $renderer->render($response->getData());

        return $this->render('EkynaGlsUniBoxBundle::debug.html.twig', [
            'labels' => [base64_encode($raw)],
        ]);
    }
}
