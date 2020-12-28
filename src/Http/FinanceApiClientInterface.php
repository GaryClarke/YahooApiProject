<?php

namespace App\Http;

interface FinanceApiClientInterface
{
    public function fetchStockProfile(string $symbol, string $region);
}