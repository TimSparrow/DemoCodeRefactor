## Refactoring test task

### Invocation

<code> php Parser.php transactions.txt </code>

Where transactions.txt should conform to the test task format 
An example transactions.txt is included in this repository

### Notes

1. For proper functioning of exchange rate fetcher, a valid API key for the service https://api.apilayer.com/exchangerates_data/latest' must be obtained and passed as an environment value to the script:

<code> export EXCHANGE_RATES_API_KEY=your_api_key </code>

2. BinList.net may return error 429 Too many requests. Running with a VPN may enable successful testing.
3. Both services (bin country data fetcher and exchange rate fetcher) are implementations of respective interfaces, which are mocked in test cases. Any valid implementation of the said interfaces should work with tests - proof is through the unit tests.
4. I have created test cases for all public methods. Some edge cases are added, some are probably not covered.
5. File reader is not covered by tests - in this case, the file should be mocked - skipped for triviality.
6. This implementation makes use of 3rd party tools:
- mockery
- GuzzleHTTP
- phpunit
- faker
  (which, in turn, may have their own dependencies - all managed by composer)
7. This implementation has been tested to produce valid results with PHP 8.3. Not guaranteed to work with earlier versions
8. Some of the provided BINs are not valid. For the proof of concept, they are ignored (reported to the console)
9. The script produces no output to STDOUT, except the required data. All errors are pushed to STDERR.
10. The script return code 0 on success, anything else - on error (with detailed message in STDERR).
11. The tests should run with phpunit.xml configuration provided.