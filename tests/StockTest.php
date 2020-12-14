<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class StockTest extends KernelTestCase
{
    private $entityManager;

    protected function setUp()
    {
        $kernel = self::bootKernel();

        DatabasePrimer::prime($kernel);

        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager();
    }

    public function testItWorks()
    {
        $this->assertTrue(true);
    }
}