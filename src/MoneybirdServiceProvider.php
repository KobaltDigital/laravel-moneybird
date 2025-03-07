<?php

namespace Kobalt\LaravelMoneybird;

use Illuminate\Support\ServiceProvider;
use Kobalt\LaravelMoneybird\OAuth2\MoneybirdOAuth;

class MoneybirdServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/moneybird.php' => config_path('moneybird.php'),
        ]);

        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');

        $this->publishesMigrations([
            __DIR__ . '/database/migrations' => database_path('migrations'),
        ]);
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/config/moneybird.php', 'moneybird');

        $this->app->singleton('moneybird', function ($app) {
            return new MoneybirdOAuth(
                config('moneybird.client_id'),
                config('moneybird.client_secret'),
                config('moneybird.redirect_uri')
            );
        });
    }
}