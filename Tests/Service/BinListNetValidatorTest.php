<?php

namespace Test\Service;

use App\Service\BinListNetValidator;
use Guzzle\Http\ClientInterface;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Random\Randomizer;

/**
 * @covers App\Service\BinListNetValidator
 */
class BinListNetValidatorTest extends MockeryTestCase
{
    private $validator;
    private ClientInterface $client;

    private Randomizer $randomizer;


    public function setUp(): void
    {
        $this->client = Mockery::mock(ClientInterface::class);
        $this->validator = new BinListNetValidator($this->client);
        $this->randomizer = new Randomizer();
    }


    public function testShouldIssueAndParseBinListNet(): void
    {
        $bin = $this->randomizer->getInt(100000, 999999); // 6 digit random int
        $countryCode = $this->randomCountry();
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

    private function randomCountry(): string
    {
        return $this->randomizer->getBytes(2);
    }
}