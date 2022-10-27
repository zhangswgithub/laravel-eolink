<?php

namespace Redam\Eolink;

use Illuminate\Support\Facades\Facade;

class EolinkFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'eolink';
    }
}