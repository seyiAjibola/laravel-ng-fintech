<?php

namespace SeyiAjibola\NgFintech\Facades;

use Illuminate\Support\Facades\Facade;

class Bills extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'fintech.bills';
    }
}