<?php

namespace Test\Service;

use App\Service\BinListNetValidator;
use Faker\Factory;
use Faker\Generator;
use GuzzleHttp\ClientInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Random\Randomizer;

/**
 * @covers \App\Service\BinListNetValidator
 */
class BinListNetValidatorTest extends MockeryTestCase
{
    private $validator;
    private ClientInterface $client;

    private Generator $faker;


    public function setUp(): void
    {
        $this->client = Mockery::mock(ClientInterface::class);
        $this->validator = new BinListNetValidator($this->client);
        $this->faker = Factory::create();
    }


    /**
     * @covers \App\Service\BinListNetValidator::getCountryByBinNumber
     */
    public function testShouldIssueAndParseBinListNet(): void
    {
        $bin = $this->getRandomBinNumber();
        $countryCode = $this->getRandomCountryCode();

        // create a minimal response containing the country code
        $mockReturn = $this->mockBinListNetResponse($countryCode);
        $this->client->shouldReceive('get')
            ->once()->withArgs(function($args) use ($bin) {

                if(!preg_match('/(^.*\/)(\d{6})$/', $args, $matches)) {
                    return false;
                }
                return ($matches[2] === $bin) && ($matches[1] === BinListNetValidator::SERVICE_URL);
            })->andReturn($mockReturn);
        $country = $this->validator->getCountryByBinNumber($bin);
        $this->assertEquals($countryCode, $country);
    }

    private function mockBinListNetResponse(string $countryCode): string
    {
        return json_encode([
            'country' => ['alpha2' => $countryCode],
        ]);
    }

    private function getRandomCountryCode(): string
    {
        return $this->faker->countryCode();
    }

    private function getRandomBinNumber(): string
    {
        return substr($this->faker->creditCardNumber(), 0, 6);
    }
}