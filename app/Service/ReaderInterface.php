<?php

namespace App\Service;

use Generator;

interface ReaderInterface
{
    public function eachLine(): Generator;
}