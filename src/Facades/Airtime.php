<?php

namespace SeyiAjibola\NgFintech\Facades;

use Illuminate\Support\Facades\Facade;

class Airtime extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'fintech.airtime';
    }
}