<?php

namespace Noking50\User\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see Noking50\User\User
 */
class User extends Facade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() {
        return 'user';
    }

}
