<?php

namespace SeyiAjibola\NgFintech\Facades;

use Illuminate\Support\Facades\Facade;

class Identity extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'fintech.identity';
    }
}