<?php

namespace App\Tests\feature;

use App\Entity\Stock;
use App\Http\FakeYahooFinanceApiClient;
use App\Tests\DatabaseDependantTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Console\Application;

class RefreshStockProfileCommandTest extends DatabaseDependantTestCase
{

    /** @test */
    public function the_refresh_stock_profile_command_creates_new_records_correctly()
    {
        // SETUP //
        $application = new Application(self::$kernel);

        // Command
        $command = $application->find('app:refresh-stock-profile');

        $commandTester = new CommandTester($command);

        // Set faked return content
        FakeYahooFinanceApiClient::$content = '{"symbol":"AMZN","shortName":"Amazon.com, Inc.","region":"US","exchangeName":"NasdaqGS","currency":"USD","price":3258.7083,"previousClose":3172.69,"priceChange":86.02}';

        // DO SOMETHING //
        $commandTester->execute([
            'symbol' => 'AMZN',
            'region' => 'US'
        ]);

        // MAKE ASSERTIONS //
        // DB assertions
        $repo = $this->entityManager->getRepository(Stock::class);

        /** @var Stock $stock */
        $stock = $repo->findOneBy(['symbol' => 'AMZN']);

        $this->assertSame('USD', $stock->getCurrency());
        $this->assertSame('NasdaqGS', $stock->getExchangeName());
        $this->assertSame('AMZN', $stock->getSymbol());
        $this->assertSame('Amazon.com, Inc.', $stock->getShortName());
        $this->assertSame('US', $stock->getRegion());
        $this->assertGreaterThan(50, $stock->getPreviousClose());
        $this->assertGreaterThan(50, $stock->getPrice());
        $this->assertStringContainsString('Amazon.com, Inc. has been saved / updated', $commandTester->getDisplay());
    }


    /** @test */
    public function the_refresh_stock_profile_command_updates_existing_records_correctly()
    {
        // SETUP
        // An existing Stock record
        $stock = new Stock();
        $stock->setSymbol('AMZN');
        $stock->setRegion('US');
        $stock->setExchangeName('NasdaqGS');
        $stock->setCurrency('USD');
        $stock->setShortName('Amazon.com, Inc.');
        $stock->setPreviousClose(3000);
        $stock->setPrice(3100);
        $stock->setPriceChange(100);

        $this->entityManager->persist($stock);
        $this->entityManager->flush();

        $stockId = $stock->getId();

        $application = new Application(self::$kernel);

        // Command
        $command = $application->find('app:refresh-stock-profile');

        $commandTester = new CommandTester($command);

        // Non 200 response
        FakeYahooFinanceApiClient::$statusCode = 200;

        // Error content
        FakeYahooFinanceApiClient::setContent([
            'previous_close' => 3172.69,
            'price'          => 3258.7083,
            'price_change'   => 86.02
        ]);

        // DO SOMETHING
        // Execute the command
        $commandStatus = $commandTester->execute([
            'symbol' => 'AMZN',
            'region' => 'US'
        ]);

        // MAKE ASSERTIONS
        $repo = $this->entityManager->getRepository(Stock::class);

        $stockRecord = $repo->find($stockId);

        $this->assertEquals(3172.69, $stockRecord->getPreviousClose());
        $this->assertEquals(3258.7083, $stockRecord->getPrice());
        $this->assertEquals(86.02, $stockRecord->getPriceChange());

        $stockRecordCount = $repo->createQueryBuilder('stock')
            ->select('count(stock.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $this->assertEquals(0, $commandStatus);

        // Check no duplicates i.e. 1 record instead of 2
        $this->assertEquals(1, $stockRecordCount);
    }


    /** @test */
    public function non_200_status_code_responses_are_handled_correctly()
    {
        // SETUP
        // SETUP //
        $application = new Application(self::$kernel);

        // Command
        $command = $application->find('app:refresh-stock-profile');

        $commandTester = new CommandTester($command);

        // Non 200 response
        FakeYahooFinanceApiClient::$statusCode = 500;

        // Error content
        FakeYahooFinanceApiClient::$content = 'Finance API Client Error ';

        // DO SOMETHING
        $commandStatus = $commandTester->execute([
            'symbol' => 'AMZN',
            'region' => 'US'
        ]);

        $repo = $this->entityManager->getRepository(Stock::class);

        $stockRecordCount = $repo->createQueryBuilder('stock')
            ->select('count(stock.id)')
            ->getQuery()
            ->getSingleScalarResult();

        // MAKE ASSERTIONS
        $this->assertEquals(1, $commandStatus);

        $this->assertEquals(0, $stockRecordCount);

        $this->assertStringContainsString('Finance API Client Error', $commandTester->getDisplay());
    }


}