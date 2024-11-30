<?php

namespace Test\Service;

use App\Exceptions\InvalidBinException;
use App\Service\BinListNetValidator;
use Faker\Factory;
use Faker\Generator;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Http\Message\ResponseInterface;


#[CoversClass(BinListNetValidator::class)]
class BinListNetValidatorTest extends MockeryTestCase
{

    private const string RESPONSE_OK = BinListNetValidator::HTTP_STATUS_OK;
    private $validator;
    private ClientInterface $client;

    private Generator $faker;


    public function setUp(): void
    {
        parent::setUp();
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

        // create a minimal response containing the country code
        $mockReturn = $this->mockBinListNetResponseNotOk();
        $this->client->shouldReceive('get')
            ->once()->withArgs(function($args) use ($bin) {
                return $this->isValidServiceUrl($args, $bin);
            })->andReturn($mockReturn);

        $this->expectException(InvalidBinException::class);
        $country = $this->validator->getCountryByBinNumber($bin);

    }

    /**
     * If a BIN is invalid, the provider returns empty country info
     */
    public function testShouldReportInvalidResponse(): void
    {
        $bin = $this->getRandomBinNumber();
        $mockReturn = $this->mockEmptyBinListNetResponse();
        $this->client->shouldReceive('get')
            ->once()->withArgs(function($args) use ($bin) {
                 return $this->isValidServiceUrl($args, $bin);
            })->andReturn($mockReturn);

        $this->expectException(InvalidBinException::class);
        $country = $this->validator->getCountryByBinNumber($bin);
    }

    public function testShouldCatchServerResponses(): void
    {
        $bin = $this->getRandomBinNumber();
        $mockReturn = $this->mockBinListNetResponseNotOk($bin);
        $requestUrl = BinListNetValidator::SERVICE_URL . $bin;
        $this->client->shouldReceive('get')
            ->once()->withArgs(function($args) use ($bin) {
                return $this->isValidServiceUrl($args, $bin);
            })->andThrow(new BadResponseException(
                "Test exception",
                new Request('GET', $requestUrl),
                new Response($this->getRandomHttpErrorCode()
            )));

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

    private function mockBinListNetResponseNotOk(): ResponseInterface
    {
        $code = $this->getRandomHttpErrorCode();
        $reasonPhrase = 'Server Error';
        return new Response($code, [], $reasonPhrase);
    }

    private function mockEmptyBinListNetResponse(): ResponseInterface
    {
        $responseData = json_encode([
            'country' => [],
            'bank' => [],
        ]);

        return new Response(self::RESPONSE_OK, ['Content-Type' => 'application/json'], $responseData);
    }

    private function getRandomCountryCode(): string
    {
        return $this->faker->countryCode();
    }

    private function getRandomBinNumber(): string
    {
        return substr($this->faker->creditCardNumber(), 0, 6);
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function isValidServiceUrl(string $url, string $bin): bool
    {
        if(!preg_match('/(^.*\/)(\d+)$/', $url, $matches)) {
            return false;
        }
        return ($matches[2] === $bin) && ($matches[1] === BinListNetValidator::SERVICE_URL);
    }

    private function getRandomHttpErrorCode(): int
    {
        $errors = [400, 404, 403, 429, 500, 502, 504]; // throw one of these

        return  $this->faker->randomElement($errors);
    }
}