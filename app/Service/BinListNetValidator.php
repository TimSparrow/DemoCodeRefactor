<?php

namespace App\Service;

use App\Exceptions\InvalidBinException;
use App\Service\BinValidationService;
use Guzzle\Http\ClientInterface;

class BinListNetValidator implements BinValidationService
{

    public const string SERVICE_URL = 'https://lookup.binlist.net/';


    public function __construct(private readonly ClientInterface $client)
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