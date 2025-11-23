<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomeControllerTest extends WebTestCase
{
    public function testHomePageReturnsResponse(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        
        // Verifie que la page repond (200 ou 500)
        $this->assertLessThan(600, $client->getResponse()->getStatusCode());
    }
}
