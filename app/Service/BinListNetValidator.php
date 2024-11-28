<?php

namespace App\Service;

use App\Exceptions\InvalidBinException;
use App\Service\BinValidationService;
use GuzzleHttp\ClientInterface;

class BinListNetValidator implements BinValidationService
{

    public const string SERVICE_URL = 'https://lookup.binlist.net/';

    public const string HTTP_STATUS_OK = '200';

    public function __construct(private readonly ClientInterface $client)
    {

    }
    public function getCountryByBinNumber(string $binNumber): string
    {
        $response = $this->client->get($this->getRequestUri($binNumber));
        $code = $response->getStatusCode();
        if ($code != self::HTTP_STATUS_OK) {
            throw new InvalidBinException("Invalid BIN or server error: $code; response=". $response->getReasonPhrase());
        }

        $body = $response->getBody() ?->getContents();

        if(null === $body || empty($body)) {
            throw new InvalidBinException("BIN number '{$binNumber}' not found");
        }

        $json = json_decode($body, true);
        return $json['country']['alpha2'];
    }

    private function getRequestUri(string $binNumber): string
    {
        return self::SERVICE_URL . $binNumber;
    }
}