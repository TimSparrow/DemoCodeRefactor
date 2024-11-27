<?php

namespace App\Service;

use App\Exceptions\InvalidBinException;
use Guzzle\Http\Client;
use App\Service\BinValidationService;

class BinListNetValidator implements BinValidationService
{

    private const SERVICE_URL = 'https://lookup.binlist.net/';


    public function __construct(private readonly Client $client)
    {

    }
    public function getCountryByBinNumber(string $binNumber): string
    {
        $response = $this->client->get($this->getRequestUri($binNumber));

        $body = $response->getBody()->getContents();

        if(empty($body)) {
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