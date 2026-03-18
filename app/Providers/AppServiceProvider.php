<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Repositories\UserRepository;
use App\Repositories\Interfaces\MasterRepositoryInterface;
use App\Repositories\MasterRepository;
use App\Repositories\Interfaces\CustomerRepositoryInterface;
use App\Repositories\CustomerRepository;
use App\Repositories\Interfaces\VehicleRepositoryInterface;
use App\Repositories\VehicleRepository;
use App\Events\EmailEvent;
use App\Listeners\EmailListeners;
use App\Repositories\Interfaces\VehicleOwnerRepositoryInterface;
use App\Repositories\VehicleOwnerRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(MasterRepositoryInterface::class, MasterRepository::class);
        $this->app->bind(CustomerRepositoryInterface::class, CustomerRepository::class);
        $this->app->bind(VehicleRepositoryInterface::class, VehicleRepository::class);
        $this->app->bind(VehicleOwnerRepositoryInterface::class, VehicleOwnerRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(
            EmailEvent::class,
            EmailListeners::class
        );
    }
}
