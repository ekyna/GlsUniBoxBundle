<?php

namespace Ekyna\Bundle\GlsUniBoxBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Class DefaultControllerTest
 * @package Ekyna\Bundle\GlsUniBoxBundle\Tests\Controller
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DefaultControllerTest extends WebTestCase
{
    public function testIndex()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $this->assertContains('Hello World', $client->getResponse()->getContent());
    }
}
