<?php

namespace Raulingg\LaravelPayU\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Config\Repository;
use Raulingg\LaravelPayU\Client\PayuClient;
use Raulingg\LaravelPayU\Contracts\PayuClientInterface;

class PayuClientServiceProvider extends ServiceProvider
{
    const CONFIG_FILE_NAME_PAYU = 'payu';

    /**
     * @var bool|array
     */
    private $settings = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPublishConfig();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigs();
        $this->app->singleton(PayuClientInterface::class, $this->getCreatePayuClientClosure());
    }

    /**
     * @return void
     */
    protected function registerPublishConfig()
    {
        $publishPath = $this->app['path.config'].DIRECTORY_SEPARATOR.static::CONFIG_FILE_NAME_PAYU.'.php';
        $this->publishes([$this->getConfigPath() => $publishPath]);
    }

    /**
     * Merge default config and config from application `config` folder.
     */
    protected function mergeConfigs()
    {
        $repo = $this->getConfigRepository();
        $config = $repo->get(static::CONFIG_FILE_NAME_PAYU, []);
        $base = $this->getBaseConfig();
        $result = $config + $base;
        $repo->set(static::CONFIG_FILE_NAME_PAYU, $result);
    }

    /**
     * @return \Closure
     */
    protected function getCreatePayuClientClosure()
    {
        return function () {
            $settings = $this->getSettings();

            return new PayuClient($settings);
        };
    }

    /**
     * @return string
     */
    protected function getConfigPath()
    {
        $root = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR;
        $path = $root.'config'.DIRECTORY_SEPARATOR.static::CONFIG_FILE_NAME_PAYU.'.php';

        return $path;
    }

    /**
     * @return array
     */
    protected function getSettings()
    {
        if ($this->settings === false) {
            $this->settings = $this->getConfigRepository()->get('payu', []);
        }

        return $this->settings;
    }

    /**
     * @return \Illuminate\Contracts\Config\Repository
     */
    protected function getConfigRepository()
    {
        /** @var Repository $config */
        $config = $this->app->make('config');

        return $config;
    }

    /**
     * @return array
     */
    protected function getBaseConfig()
    {
        $path = $this->getConfigPath();
        /** @noinspection PhpIncludeInspection */
        $base = require $path;

        return $base;
    }
}
