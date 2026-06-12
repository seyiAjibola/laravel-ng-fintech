<?php

namespace SeyiAjibola\NgFintech\Facades;

use Illuminate\Support\Facades\Facade;

class Payment extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'fintech.payment';
    }
}