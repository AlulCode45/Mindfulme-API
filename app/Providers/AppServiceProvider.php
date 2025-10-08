<?php

namespace App\Providers;

use App\Contracts\Interfaces\BundlePackageInterface;
use App\Contracts\Interfaces\UserBundlePointInterface;
use App\Contracts\Repositories\BundlePackageRepository;
use App\Contracts\Repositories\UserBundlePointRepository;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    private array $register = [
        BundlePackageInterface::class => BundlePackageRepository::class,
        UserBundlePointInterface::class => UserBundlePointRepository::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        foreach ($this->register as $key => $value) {
            $this->app->bind($key, $value);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('google', \SocialiteProviders\Google\Provider::class);
        });
    }
}
