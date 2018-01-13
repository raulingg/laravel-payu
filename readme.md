LaravelPayU
================

[![Build Status](https://travis-ci.org/raulingg/laravel-payu.svg?branch=master)](https://travis-ci.org/raulingg/laravel-payu)
[![StyleCI](https://styleci.io/repos/115456243/shield?branch=master)](https://styleci.io/repos/115456243)

Introducción
------------

LaravelPayU provee una interfaz sencilla para utilizar el sdk de PayU en proyectos que tienen como base el framework [*Laravel*](https://laravel.com).
Este proyecto hace uso del [sdk de Payu](http://developers.payulatam.com/es/sdk/), pero no es un proyecto oficial de PayU.

Requerimientos
------------
* [php >= 7](http://php.net/)
* [Laravel >= 5.2 <= 5.5](https://laravel.com)


Instalación y configuración
------------

Instalar el paquete mediante composer:

```bash
composer require raulrqm/laravel-payu
```

Luego incluir el ServiceProvider en el arreglo de providers en *config/app.php*

```bash
Raulingg\LaravelPayU\Providers\PayuClientServiceProvider::class,
```

Publicar la configuración para incluir la información de la cuenta de PayU:

```bash
php artisan vendor:publish 
```

Incluir la información de la cuenta y ajustes en el archivo *.env* o directamente en
el archivo de configuración *config/payu.php*

```bash
PAYU_ON_TESTING=true
PAYU_MERCHANT_ID=your-merchant-id
PAYU_API_LOGIN=your-api-login
PAYU_API_KEY=your-api-key
PAYU_ACCOUNT_ID=your-account-id
PAYU_COUNTRY=your-country-ref: AR/BR/CO/CL/MX/PA/PE/US
```

## Uso del API

Esta versión contiene solo una interfaz para pagos únicos y consultas.
Si necesita usar tokenización, pagos en efectivo y pagos recurrentes debe usar el sdk de PayU directamente.

### Ping

Para consultar la disponibilidad de la plataforma se puede usar el método doPing en el controlador
designado:

```php
<?php

namespace App\Http\Controllers;

use Raulingg\LaravelPayU\Contracts\PayuClientInterface;

class PaymentsController extends Controller
{

    public function doPing(PayuClientInterface $payuClient)
    {
        $payuClient->doPing(function($response) {
            $code = $response->code;
            // ... revisar el codigo de respuesta
        }, function($error) {
            // ... Manejo de errores PayUException
        });
    }
    

```

### Pagos Únicos

Permite el pago de ordenes generadas de la siguiente manera:

```php
<?php

namespace App\Http\Controllers;

use Raulingg\LaravelPayU\Contracts\PayuClientInterface;
use PayUParameters;

class PaymentsController extends Controller
{

    public function pay(PayuClientInterface $payuClient)
    {
        // Estos datos son de prueba, estos deben ser asignados según tus requerimientos
        $data = [
            PayUParameters::VALUE => request()->input('amount'),
            PayUParameters::DESCRIPTION => 'Payment cc test',
            PayUParameters::REFERENCE_CODE => uniqid(time()),

            PayUParameters::CURRENCY => 'PEN',

            PayUParameters::PAYMENT_METHOD => request()->input('card_type'), // VISA, MASTERCARD, ...

            PayUParameters::CREDIT_CARD_NUMBER => request()->input('card_number') // '4907840000000005',
            PayUParameters::CREDIT_CARD_EXPIRATION_DATE => request()->input('card_expiration_date'),
            PayUParameters::CREDIT_CARD_SECURITY_CODE => request()->input('card_security_code'),

            PayUParameters::INSTALLMENTS_NUMBER => 1,

            PayUParameters::PAYER_NAME => 'APPROVED',
            PayUParameters::PAYER_DNI => '458784778',

            PayUParameters::IP_ADDRESS => '127.0.0.1',
        ];

        $payuClient->pay($data, function($response) {
            if ($response->code == 'SUCCESS') {        
                // ... El código para el caso de éxito
            } else {
            //... El código de respuesta no fue exitoso
            }
        }, function($error) {
            // ... Manejo de errores PayUException, InvalidArgument
        });
    }

```

El método *pay* recibe tres parámetros:

- Un array con los datos de pago.
- Una función (closure) que recibe la respuesta de la consulta.
- Una función (closure) que recibe las excepciones generadas por validación ó errores en el pago.

También puede usar los métodos *authorize* y *capture* para autorización de
pago y captura de la orden, pero recuerde que sólo están disponibles para **Brasíl**.

Ver documentación del [sdk para pagos](http://developers.payulatam.com/es/sdk/payments.html).

### Consultas

Luego en el controlador designado para consultas podemos hacer consultas usando el id asignado por Payu, la referencia dada por nosotros, o el id de la transacción:

```php
<?php

use Raulingg\LaravelPayU\Contracts\PayuClientInterface as PayuClient;

...
$payuClient = app()->make(PayuClient::class);
$payuOrderId = 123;

$payuClient->searchById($payuOrderId, function($response, $order) {
    // ... Usar la información de respuesta
}, function($error) {
    // ... Manejo de errores PayUException, InvalidArgument
});

$payuReferenceCode = "2014-05-06 06:14:19";

$payuClient->searchByReference($payuReferenceCode, function($response) {
    // ... Usar la información de respuesta
}, function($error) {
    // ... Manejo de errores PayUException, InvalidArgument
});

$payuTransactionId = '960b1a5d-575d-4bd9-927e-0ffbf5dc4296';

$payuClient->searchByTransaction($payuTransactionId, function($response) {
    // ... Usar la información de respuesta
}, function($error) {
    // ... Manejo de errores PayUException, InvalidArgument
});

```

Los métodos *searchById*, *searchByReference* y *searchByTransaction* reciben tres parámetros:

- El valor del campo usado como entrada para la búsqueda (OrderId, ReferenceCode, transactionId)
- Una función (closure) que recibe la respuesta de la consulta.
- Una función (closure) que recibe las Excepciones generadas por validación ó errores en el pago.

Ver documentación del [sdk de consultas](http://developers.payulatam.com/es/sdk/queries.html).

Pruebas
------------

Instalar las dependencias y luego ejecutar las pruebas:

```bash
vendor/bin/phpunit
```

Se usan por defecto valores de prueba provistos por Payu, para más detalles visita
[sdk sandbox](http://developers.payulatam.com/es/sdk/sandbox.html)

Errores y contribuciones
------------

Para un error escribir directamente el problema en github issues o enviarlo
al correo relaxedchild@gmail.com. Si desea contribuir con el proyecto por favor enviar los ajustes siguiendo la guía de contribuciones:

- Usar las recomendaciones de estilos [psr-1](http://www.php-fig.org/psr/psr-1/) y [psr-2](http://www.php-fig.org/psr/psr-2/)

- Preferiblemente escribir código que favorezca el uso de Laravel

- Escribir las pruebas y revisar el código antes de hacer un pull request

