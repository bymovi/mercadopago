<?php namespace Bymovi\Mercadopago;

use Illuminate\Support\ServiceProvider;
use Config;

class MercadopagoServiceProvider extends ServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->package('bymovi/mercadopago');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $loader = $this->app['config']->getLoader();
        // Get environment name
        $env = $this->app['config']->getEnvironment();
        // Add package namespace with path set, override package if app config exists in the main app directory
        if (file_exists(app_path() . '/config/packages/bymovi/mercadopago')) {
            $loader->addNamespace('mercadopago', app_path() . '/config/packages/bymovi/mercadopago');
        } else {
            $loader->addNamespace('mercadopago', __DIR__ . '/../../config');
        }
        $config = $loader->load($env, 'mercadopago', 'mercadopago');
        $this->app['config']->set('mercadopago::mercadopago', $config);

        $this->app['mercadopago'] = $this->app->share(function ($app){
            $client_id = Config::get('mercadopago::mercadopago.MERCADOPAGO_CLIENT_ID');
            $secret_id = Config::get('mercadopago::mercadopago.MERCADOPAGO_CLIENT_SECRET');

            return new Mercadopago($client_id, $secret_id);
        });

//        $this->app->booting(function()
//        {
//            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
//            $loader->alias('Mercadopago', 'Bymovi\Mercadopago\Facades\Mercadopago');
//        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array();
    }

}
