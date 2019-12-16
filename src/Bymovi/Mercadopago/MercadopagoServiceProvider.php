<?php namespace Bymovi\Mercadopago;

use Empresa;
use Illuminate\Support\ServiceProvider;
use Config;
use Log;
use Session;

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

        $this->app->bind('mercadopago', function($app)
        {
            $id_empresa = Session::get('id_empresa');
            $subdominio = Empresa::getSubdominioById($id_empresa);

            switch ($subdominio)
            {
                case 'amoemra':
                    $client_id = Config::get('mercadopago::mercadopago.MERCADOPAGO_CLIENT_ID_AMOEMRA');
                    $secret_id = Config::get('mercadopago::mercadopago.MERCADOPAGO_CLIENT_SECRET_AMOEMRA');
                    break;
                case 'boreal':
                    $client_id = Config::get('mercadopago::mercadopago.MERCADOPAGO_CLIENT_ID_BOREAL');
                    $secret_id = Config::get('mercadopago::mercadopago.MERCADOPAGO_CLIENT_SECRET_BOREAL');
                    break;
                case 'bayres':
                    $client_id = Config::get('mercadopago::mercadopago.MERCADOPAGO_CLIENT_ID_BAYRES');
                    $secret_id = Config::get('mercadopago::mercadopago.MERCADOPAGO_CLIENT_SECRET_BAYRES');
                    break;
                default:
                    $client_id = Config::get('mercadopago::mercadopago.MERCADOPAGO_CLIENT_ID');
                    $secret_id = Config::get('mercadopago::mercadopago.MERCADOPAGO_CLIENT_SECRET');
                    break;
            }

            return new Mercadopago($client_id, $secret_id);
        });
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
