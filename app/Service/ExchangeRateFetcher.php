<?php


namespace App\Service;
use GuzzleHttp\Client;
class ExchangeRateFetcher implements ExchangeRateInterface
{
    private const API_URL = 'https://api.apilayer.com/exchangerates_data/latest';

    private const BASE_CURRENCY = 'EUR';


    private array $rates;



    public function __construct()
    {
        $this->fetchRates();
    }


    private function fetchRates(): void
    {
        $client = new Client();
        $apiKey = getenv('EXCHANGE_RATES_API_KEY');
        $headers = [
            'apikey' => $apiKey,
        ];
        $response = $client->get(self::getServiceUrl(),  $headers);
        $rates = json_decode($response->getBody(), true);
        $this->rates = $rates['rates']; // discard the metadata
    }

    public function getExchangeRate(string $currencyCode): float
    {
        if (!array_key_exists($currencyCode, $this->rates)) {
            return 0.0; // rate not found
        }

        return $this->rates[$currencyCode];
    }


    private static function getServiceUrl(): string
    {
        return self::API_URL . "?base=" . self::BASE_CURRENCY;
    }
}