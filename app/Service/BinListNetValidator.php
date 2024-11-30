<?php

namespace App\Service;

use App\Exceptions\InvalidBinException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;

class BinListNetValidator implements BinValidationService
{

    public const string SERVICE_URL = 'https://lookup.binlist.net/';

    public const string HTTP_STATUS_OK = '200';

    public function __construct(private readonly ClientInterface $client)
    {

    }
    public function getCountryByBinNumber(string $binNumber): string
    {
        try {
            $response = $this->client->get($this->getRequestUri($binNumber));
            $code = $response->getStatusCode();
            if ($code != self::HTTP_STATUS_OK) {
                throw new InvalidBinException("Invalid BIN or server error: $code; response=" . $response->getReasonPhrase());
            }

            $body = $response->getBody()?->getContents();

            if (null === $body || empty($body)) {
                throw new InvalidBinException("BIN number '{$binNumber}' not found");
            }

            $binData = json_decode($body, true);

            if (null === $binData) {
                throw new InvalidBinException("BIN data is not valid");
            }

            if (!array_key_exists('country', $binData)) {
                throw new InvalidBinException("Retrieved data for $binNumber has no country information");
            }

            if (!array_key_exists('alpha2', $binData['country'])) {
                throw new InvalidBinException("Retrieved data for $binNumber has no country code, may be invalid BIN");
            }

            return strtoupper($binData['country']['alpha2']);
        } catch (ClientException | GuzzleException $e) { // TODO: some server errors are not fatal (eg. 429)
            throw new InvalidBinException("Could not get data or BIN number '{$binNumber}', server returns {$e->getMessage()}");
        }
    }

    private function getRequestUri(string $binNumber): string
    {
        return self::SERVICE_URL . $binNumber;
    }
}