<?php

namespace Noking50\User;

use Illuminate\Support\Facades\Facade;

/**
 * @see Noking50\User\User
 */
class UserFacade extends Facade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() {
        return 'user';
    }

}
