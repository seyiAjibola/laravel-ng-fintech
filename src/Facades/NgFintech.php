<?php

namespace SeyiAjibola\NgFintech\Facades;

use Illuminate\Support\Facades\Facade;

class NgFintech extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'ngfintech';
    }
}