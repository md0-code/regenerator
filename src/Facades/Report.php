<?php

namespace MD0\ReGenerator\Facades;

use Illuminate\Support\Facades\Facade;

class Report extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'regenerator';
    }
}
