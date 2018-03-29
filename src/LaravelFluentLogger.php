<?php
/**
 * Created by PhpStorm.
 * User: Maksim
 * Date: 3/29/2018
 * Time: 14:30
 */

namespace Ytake\LaravelFluent;

use Monolog\Logger;
use Fluent\Logger\FluentLogger;

class LaravelFluentLogger
{

    private $app;
    private $channel_config;

    public function __construct($app, $config)
    {
        $this->app = $app;
        $this->channel_config = $config;
    }

    /**
     * Create a custom Monolog instance.
     *
     * @return Logger
     */
    public function getLogger()
    {
        return $this->createFluentLogger();
    }


    /**
     * @return Logger
     */
    public function createFluentLogger()
    {
        $config = $this->app['config']['fluent'];
        $host = $config['host'] ? $config['host'] : FluentLogger::DEFAULT_ADDRESS;
        $port = $config['port'] ? $config['port'] : FluentLogger::DEFAULT_LISTEN_PORT;
        $options = $config['options'] ? $config['options'] : [];
        $tagFormat = isset($config['tagFormat']) ? $config['tagFormat'] : null;
        $log = new Logger('fluent', [new FluentHandler(
            new FluentLogger($host, $port, $options),
            $tagFormat
        )]);
        return $log;
    }

}