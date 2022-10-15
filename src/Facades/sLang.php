<?php namespace Seiger\sLang\Facades;

use Illuminate\Support\Facades\Facade;

class sLang extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'sLang';
    }
}