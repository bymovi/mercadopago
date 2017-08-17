# Proyecto: bymovi/mercadopago

## Instalación

Correr el siguiente comando

```sh
composer require bymovi/mercadopago=dev-master
```
Buscar la clave `providers` en `app/config/app.php` y registrar el Service Provider

```php
    'providers' => array(
        // ...
        'Bymovi\Mercadopago\MercadopagoServiceProvider',
    )
```

Encuentra la clave `aliases` en `app/config/app.php` y agrega el siguiente código.

```php
    'aliases' => array(
        // ...
        'Mercadopago' => 'Bymovi\Mercadopago\Facades\Mercadopago',
    )
```

Luego ejecutar el siguiente comando para migrar el archivo de configuración

```sh
php artisan config:publish aws/aws-sdk-php-laravel
```

Completar el `array` con las credenciales de MercadoPago

```php
    return array(
        'MERCADOPAGO_CLIENT_ID'     => '',
        'MERCADOPAGO_CLIENT_SECRET' => '',
    );
```