<?php

namespace Kobalt\LaravelMoneybird\Facades;

use Illuminate\Support\Facades\Facade;

class Moneybird extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'moneybird';
    }
}