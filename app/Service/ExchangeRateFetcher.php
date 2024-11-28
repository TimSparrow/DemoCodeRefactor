<?php


namespace App\Service;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;

class ExchangeRateFetcher implements ExchangeRateInterface
{
    public const string API_URL = 'https://api.apilayer.com/exchangerates_data/latest';

    public const string BASE_CURRENCY = 'EUR'; // should be a parameter


    private array $rates;



    public function __construct(private readonly ClientInterface $client, string $apiKey)
    {
        $this->fetchRates($apiKey);
    }


    private function fetchRates(string $apiKey): void
    {
        $headers = [
            'apikey' => $apiKey,
        ];
        $response = $this->client->get(self::getServiceUrl(),  $headers);
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


    public static function getServiceUrl(): string
    {
        return self::API_URL . "?base=" . self::BASE_CURRENCY;
    }
}