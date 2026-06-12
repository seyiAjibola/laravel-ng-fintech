<?php

namespace SeyiAjibola\NgFintech\Facades;

use Illuminate\Support\Facades\Facade;

class Banking extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'fintech.banking';
    }
}