<?php

namespace Test\Service;

use App\Service\ExchangeRateFetcher;
use Faker\Factory;
use Faker\Generator;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Http\Message\ResponseInterface;

class ExchangeRateFetcherTest extends MockeryTestCase
{
    private ClientInterface | Mockery\MockInterface $client;
    private ExchangeRateFetcher $fetcher;

    private Generator $faker;

    public function setUp(): void
    {
        $this->client = Mockery::mock(ClientInterface::class);
        $this->faker = Factory::create();
    }

    public function testShouldLoadRatesOnCreation(): void
    {
        $apiKey = $this->faker->word;
        $this->client->shouldReceive('get')->once()->withArgs(
            function($url, $headers) use ($apiKey) {
                if (!preg_match('#^(https:.*)\?base=(.*)$#', $url, $matches)) {
                    return false;
                }

                print_r($matches);

                print_r($headers);

                return (
                    $matches[0] == ExchangeRateFetcher::getServiceUrl() &&
                    $matches[1] == ExchangeRateFetcher::API_URL &&
                    $matches[2] == ExchangeRateFetcher::BASE_CURRENCY &&
                    is_array($headers) && $headers['apikey'] == $apiKey

                );
            }
        )->andReturn($this->mockServiceResponse());
        $this->fetcher = new ExchangeRateFetcher($this->client, $apiKey);

        // do some tests with the loaded result
    }

    public function testGetExchangeRate()
    {
        $apiKey = $this->faker->word();
        $currency = $this->faker->currencyCode();
        $rate = $this->faker->randomFloat();
        $exchangeRates = $this->mockServiceResponse([$currency => $rate]);
        $this->client->shouldReceive('get')->once()->andReturn($exchangeRates);
        $this->fetcher = new ExchangeRateFetcher($this->client, $apiKey);
        $actualRate = $this->fetcher->getExchangeRate($currency);
        $this->assertEquals($rate, $actualRate);
    }

    private function mockServiceResponse(array $defaults = []): ResponseInterface {
        $rates = [];
        foreach($defaults as $currency => $rate) {
            $rates[$currency] = $rate;
        }

        $additionalRecords = $this->faker->numberBetween(2, 10);

        for($i = 0; $i < $additionalRecords; $i++) {
            $code = $this->faker->currencyCode();
            if (!array_key_exists($code, $rates)) {
                $rates[$code] = $this->faker->randomFloat();
            }
        }

        return new Response(200, [], json_encode(['rates' => $rates]));
    }

}
