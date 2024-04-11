<?php

namespace App\Providers;

use BotMan\BotMan\BotMan;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Cache\LaravelCache;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\Drivers\Telegram\TelegramDriver;
use Geocoder\Geocoder;
use Geocoder\Provider\Yandex\Yandex;
use Geocoder\StatefulGeocoder;
use GuzzleHttp\Client;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }

        DriverManager::loadDriver(TelegramDriver::class);
        $this->app->bind(BotMan::class, fn() => BotManFactory::create(config('botman'), new LaravelCache()));

        $this->app->bind(Geocoder::class, function () {
            $httpClient = new Client();
            $provider = new Yandex($httpClient, null, config('geocoder.yandex.token'));
            return new StatefulGeocoder($provider, 'ru');
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
