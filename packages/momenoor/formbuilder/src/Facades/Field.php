<?php

namespace Momenoor\FormBuilder\Facades;

use Illuminate\Support\Facades\Facade;


class Field extends Facade
{

    //public function make(array $attributes = [])
    public static function getFacadeAccessor(): string
    {
        return 'Field';
    }
}
