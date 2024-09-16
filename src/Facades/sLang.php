<?php namespace Seiger\sLang\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class sLang
 *
 * The sLang class is a facade for accessing the 'sLang' component.
 * @mixin \Seiger\sLang\sLang
 */
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