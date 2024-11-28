<?php

namespace Test\Service;

use App\Exceptions\InvalidBinException;
use App\Service\BinListNetValidator;
use Faker\Factory;
use Faker\Generator;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversFunction;
use Psr\Http\Message\ResponseInterface;
use Random\Randomizer;

#[CoversClass(\App\Service\BinListNetValidator::class)]
class BinListNetValidatorTest extends MockeryTestCase
{

    private const string RESPONSE_OK = BinListNetValidator::HTTP_STATUS_OK;
    private $validator;
    private ClientInterface $client;

    private Generator $faker;


    public function setUp(): void
    {
        $this->client = Mockery::mock(ClientInterface::class);
        $this->validator = new BinListNetValidator($this->client);
        $this->faker = Factory::create();
    }



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

    public function testShouldRequestServerAndFailIfServerResponseNotOK(): void
    {
        $bin = $this->getRandomBinNumber();
        $countryCode = $this->getRandomCountryCode();

        // create a minimal response containing the country code
        $mockReturn = $this->mockBinListNetResponseNotOk($countryCode);
        $this->client->shouldReceive('get')
            ->once()->withArgs(function($args) use ($bin) {

                if(!preg_match('/(^.*\/)(\d{6})$/', $args, $matches)) {
                    return false;
                }
                return ($matches[2] === $bin) && ($matches[1] === BinListNetValidator::SERVICE_URL);
            })->andReturn($mockReturn);

        $this->expectException(InvalidBinException::class);
        $country = $this->validator->getCountryByBinNumber($bin);

    }

    private function mockBinListNetResponse(string $countryCode): ResponseInterface
    {
        $body = json_encode([
            'country' => ['alpha2' => $countryCode],
        ]);

        return new Response(self::RESPONSE_OK, ['Content-Type' => 'application/json'], $body);
    }

    private function mockBinListNetResponseNotOk(string $countryCode): ResponseInterface
    {
        $errors = [400, 404, 403, 500]; // throw one of these
        $code = $this->faker->randomElement($errors);
        $reasonPhrase = 'Server Error';
        $response = new Response($code, [], $reasonPhrase);

        return $response;
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