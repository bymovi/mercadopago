<?php namespace Bymovi\Mercadopago\Facades;

use Illuminate\Support\Facades\Facade;

class Mercadopago extends Facade
{
    protected static function getFacadeAccessor(){
        return 'mercadopago';
    }

    /**
     * @param $subdominio
     * @return \Bymovi\Mercadopago\Mercadopago
     */
    public static function init($subdominio)
    {
        return static::$app->make('mercadopago', $subdominio);
    }
}
