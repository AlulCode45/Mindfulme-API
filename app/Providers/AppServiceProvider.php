<?php

namespace App\Providers;

use App\Contracts\Interfaces\AiDiscussionInterface;
use App\Contracts\Interfaces\BundlePackageInterface;
use App\Contracts\Interfaces\ComplaintInterface;
use App\Contracts\Interfaces\EvidenceInterface;
use App\Contracts\Interfaces\PsychologistAvailabilityInterface;
use App\Contracts\Interfaces\SessionTypeInterface;
use App\Contracts\Interfaces\UserBundlePointInterface;
use App\Contracts\Repositories\AiDiscussionRepository;
use App\Contracts\Repositories\BundlePackageRepository;
use App\Contracts\Repositories\ComplaintRepository;
use App\Contracts\Repositories\EvidenceRepository;
use App\Contracts\Repositories\PsychologistAvailabilityRepository;
use App\Contracts\Repositories\SessionTypeRepository;
use App\Contracts\Repositories\UserBundlePointRepository;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{

    private array $register = [
        BundlePackageInterface::class => BundlePackageRepository::class,
        UserBundlePointInterface::class => UserBundlePointRepository::class,
        ComplaintInterface::class => ComplaintRepository::class,
        AiDiscussionInterface::class => AiDiscussionRepository::class,
        EvidenceInterface::class => EvidenceRepository::class,
        SessionTypeInterface::class => SessionTypeRepository::class,
        PsychologistAvailabilityInterface::class => PsychologistAvailabilityRepository::class,
        // SessionInterface::class => SessionRepository::class,
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
