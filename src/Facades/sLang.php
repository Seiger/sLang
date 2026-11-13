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
    /** @var bool Resolve through the container so a scope reset discards the previous instance. */
    protected static $cached = false;

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
