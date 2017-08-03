<?php

/**
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 *
 * Copyright (c) 2015-2016 Yuuki Takezawa
 *
 */
namespace Ytake\LaravelFluent;

use Fluent\Logger\FluentLogger;
use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Foundation\Application;
use Monolog\Logger;

/**
 * Class LogServiceProvider
 */
class LogServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function register()
    {
        /**
         * for package configure
         */
        $configPath = __DIR__ . '/config/fluent.php';
        $this->mergeConfigFrom($configPath, 'fluent');
        $this->publishes([$configPath => config_path('fluent.php')], 'log');

        $this->app->bind('fluent.handler', function ($app) {
            return new RegisterPushHandler(
                $app['Illuminate\Contracts\Logging\Log'],
                $app['config']->get('fluent')
            );
        });
    }

    /**
     * {@inheritdoc}
     */
    protected function createLogger(Application $app)
    {
        if ($app['config']->get('app.log') == 'fluent') {
            $app->instance('log', $log = new Writer(
                new Logger($app->environment()), $app['events'])
            );
            $this->configureFluentHandler($app, $log);
            return $log;
        }
    }

    /**
     * pushHandler Fluentd
     * @param Application $app
     * @param Writer $log
     */
    protected function configureFluentHandler(Application $app, Writer $log)
    {
        $configure = $app['config']->get('fluent');
        $host = $configure['host'] ? $configure['host'] : FluentLogger::DEFAULT_ADDRESS;
        $port = $configure['port'] ? $configure['port'] : FluentLogger::DEFAULT_LISTEN_PORT;
        $options = $configure['options'] ? $configure['options'] : [];
        $tagFormat = isset($configure['tagFormat']) ? $configure['tagFormat'] : null;
        $log->useFluentLogger($host, $port, $options, $tagFormat);
    }

    /**
     * {@inheritdoc}
     */
    public static function compiles()
    {
        return [
            base_path() . '/vendor/ytake/laravel-fluent-logger/src/LogServiceProvider.php',
            base_path() . '/vendor/ytake/laravel-fluent-logger/src/ConfigureLogging.php',
            base_path() . '/vendor/ytake/laravel-fluent-logger/src/FluentHandler.php',
            base_path() . '/vendor/ytake/laravel-fluent-logger/src/RegisterPushHandler.php',
            base_path() . '/vendor/ytake/laravel-fluent-logger/src/Writer.php',
            base_path() . '/vendor/fluent/logger/src/Entity.php',
            base_path() . '/vendor/fluent/logger/src/Exception.php',
            base_path() . '/vendor/fluent/logger/src/FluentLogger.php',
            base_path() . '/vendor/fluent/logger/src/JsonPacker.php',
            base_path() . '/vendor/fluent/logger/src/LoggerInterface.php',
            base_path() . '/vendor/fluent/logger/src/PackerInterface.php',
        ];
    }
}