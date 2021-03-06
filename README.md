# laravel-fluent-logger
fluent logger for laravel with support of google stackdriver logger format
(with Monolog handler for Fluentd )

[fluentd](http://www.fluentd.org/)

## Versions

Laravel 5.3  ~1.0

Laravel 5.4  ~2.0

Laravel 5.5  ~3.0

Laravel 5.6  ~4.0

### Installation For Laravel and Lumen
Require this package with Composer

```bash
$ composer require maksimru/laravel-fluent-logger
```

or composer.json

```json
"require": {
  "maksimru/laravel-fluent-logger": "~2.0"
},
```

## for laravel
your config/app.php
```php
'providers' => [
    \Ytake\LaravelFluent\LogServiceProvider::class,
]
```

### publish configure

* basic

```bash
$ php artisan vendor:publish
```

* use tag option

```bash
$ php artisan vendor:publish --tag=log
```

* use provider

```bash
$ php artisan vendor:publish --provider="Ytake\LaravelFluent\LogServiceProvider"
```

### All logs to fluentd

add fluent driver to config/logging.php

```php
'fluent' => [
    'driver' => 'fluent',
],
```

Edit .env to update LOG_CHANNEL.

LOG_CHANNEL=fluent

## fluentd config sample

```
## match tag=local.** (for laravel log develop)
<match local.**>
  type stdout
</match>
```

example (production)

 ```
<match production.**>
  type stdout
</match>
 ```
 and more

## Package Optimize (Optional for production)

required config/compile.php

```php
'providers' => [
    //
    \Ytake\LaravelFluent\LogServiceProvider::class,
],
```

## for lumen
Extend \Laravel\Lumen\Application and override the  getMonologHandler() method to set up your own logging config.

example
```php
<?php

namespace App\Foundation;

use Monolog\Logger;
use Fluent\Logger\FluentLogger;
use Ytake\LaravelFluent\FluentHandler;

class Application extends \Laravel\Lumen\Application
{
    /**
     * @return FluentHandler
     */
    protected function getMonologHandler()
    {
        return new FluentHandler(
            new FluentLogger(env('FLUENTD_HOST', '127.0.0.1'), env('FLUENTD_PORT', 24224), []),
            Logger::DEBUG
        );
    }
}

```

## fluentd config sample(lumen)

```
<match lumen.**>
  type stdout
</match>
```

## Original Author ##

- [Yuuki Takezawa](mailto:yuuki.takezawa@comnect.jp.net) ([twitter](http://twitter.com/ex_takezawa))

## License ##

The code for laravel-fluent-logger is distributed under the terms of the MIT license.
