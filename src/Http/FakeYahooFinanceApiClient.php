<?php

namespace App\Http;

use Symfony\Component\HttpFoundation\JsonResponse;

class FakeYahooFinanceApiClient implements FinanceApiClientInterface
{
    public static $statusCode = 200;
    public static $content = '';

    public function fetchStockProfile(string $symbol, string $region): JsonResponse
    {
        return new JsonResponse(self::$content, self::$statusCode, [], $json = true); // Already json, don't encode
    }
}