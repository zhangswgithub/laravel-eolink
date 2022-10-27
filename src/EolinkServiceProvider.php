<?php

namespace Redam\Eolink;

use Illuminate\Support\ServiceProvider;

class EolinkServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // 发布配置文件
        $this->publishes([
            __DIR__.'/config/eolink.php' => config_path('eolink.php'),
        ]);
    }

    public function register()
    {
        $this->app->singleton('eolink', function () {
            return new Eolink();
        });
    }

    protected function configPath()
    {
        return __DIR__ . '/config/eolink.php';
    }

    protected function eolinkOptions()
    {
        return $this->app['config']->get('eolink');
    }

}