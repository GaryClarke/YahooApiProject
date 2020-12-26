<?php

namespace App\Command;

use App\Entity\Stock;
use App\Http\YahooFinanceApiClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RefreshStockProfileCommand extends Command
{
    protected static $defaultName = 'app:refresh-stock-profile';

    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * @var YahooFinanceApiClient
     */
    private YahooFinanceApiClient $yahooFinanceApiClient;


    public function __construct(EntityManagerInterface $entityManager, YahooFinanceApiClient $yahooFinanceApiClient)
    {
        $this->entityManager = $entityManager;

        $this->yahooFinanceApiClient = $yahooFinanceApiClient;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Retrieve a stock profile from the Yahoo Finance API. Update the record in the DB')
            ->addArgument('symbol', InputArgument::REQUIRED, 'Stock symbol e.g. AMZN for Amazon')
            ->addArgument('region', InputArgument::REQUIRED, 'The region of the company e.g. US for United States');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // 1. Ping Yahoo API and grab the response (a stock profile) ['statusCode' => $statusCode, 'content' => $someJsonContent]
        $stockProfile = $this->yahooFinanceApiClient->fetchStockProfile($input->getArgument('symbol'), $input->getArgument('region'));

        if ($stockProfile['statusCode'] !== 200) {

            // Handle non 200 status code responses
        }

        // 2b. Use the stock profile to create a record if it doesn't exist
        $stock = $this->serializer->deserialize($stockProfile['content'], Stock::class, 'json');

//        $stock = new Stock();
//        $stock->setCurrency($stockProfile->currency);
//        $stock->setExchangeName($stockProfile->exchangeName);
//        $stock->setSymbol($stockProfile->symbol);
//        $stock->setShortName($stockProfile->shortName);
//        $stock->setRegion($stockProfile->region);
//        $stock->setPreviousClose($stockProfile->previousClose);
//        $stock->setPrice($stockProfile->price);
//        $priceChange = $stockProfile->price - $stockProfile->previousClose;
//        $stock->setPriceChange($priceChange);

        $this->entityManager->persist($stock);

        $this->entityManager->flush();

        return Command::SUCCESS;
    }
}
