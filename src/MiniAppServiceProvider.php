<?php

namespace Msx\MiniApp;

use Illuminate\Support\ServiceProvider;

class MiniAppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        // 发布配置文件
        $this->publishes([
            __DIR__.'/config/miniapp.php' => config_path('miniapp.php')
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        // 单列绑定服务
        $this->app->singleton('miniapp', function ($app) {
            return new MiniApp($app['session'], $app['config']);
        });
    }
}
