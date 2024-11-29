<?php

namespace App\Service;

use Generator;

class FileReaderIterator implements ReaderInterface
{
    private const int BUFFER_SIZE = 8192;

    public function __construct(private string $path)
    {
        if(!is_file($this->path)) {
            throw new \UnexpectedValueException("File '{$this->path}' does not exist");
        }
    }


    public function eachLine(): Generator
    {
        $fp = fopen($this->path, "r");
        while($line = fgets($fp, self::BUFFER_SIZE)) {
            yield $line;
        }
        fclose($fp);
    }
}