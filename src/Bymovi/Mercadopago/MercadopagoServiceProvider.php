<?php namespace Bymovi\Mercadopago;

use Config;
use Empresa;
use Illuminate\Support\ServiceProvider;
use Log;
use Session;

class MercadopagoServiceProvider extends ServiceProvider
{

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
        if (file_exists(app_path() . '/config/packages/bymovi/mercadopago'))
        {
            $loader->addNamespace('mercadopago', app_path() . '/config/packages/bymovi/mercadopago');
        }
        else
        {
            $loader->addNamespace('mercadopago', __DIR__ . '/../../config');
        }

        $config = $loader->load($env, 'mercadopago', 'mercadopago');
        $this->app['config']->set('mercadopago::mercadopago', $config);

        $this->app->bind('mercadopago', function($app, $subdominio) {
            if (!is_string($subdominio))
            {
                throw new MercadopagoException('Subdominio debe ser string');
            }

            $client_id     = Config::get("mercadopago::mercadopago.$subdominio.client_id");
            $client_secret = Config::get("mercadopago::mercadopago.$subdominio.client_secret");

            if (is_null($client_id) || is_null($client_secret))
            {
                throw new MercadopagoException('Mercadopago client_id o client_secret incorrectos o mal configurados');
            }

            return new Mercadopago($client_id, $client_secret);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }

}
