<?php

namespace Momenoor\FormBuilder\Facades;

use Illuminate\Support\Facades\Facade;


/**
 * @method static \Momenoor\FormBuilder\FormBuilder make(string $class, array $array)
 */
class FormBuilder extends Facade {

    public static function getFacadeAccessor(): string
    {
        return 'FormBuilder';
    }
}
