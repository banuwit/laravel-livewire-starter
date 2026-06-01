<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Facades\Event;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();

         // Implicitly grant "Super Admin" role all permissions
        // This works in the app by using gate-related functions like auth()->user->can() and @can()
        Gate::before(function ($user, $ability) {
            return $user->hasRole('superadmin') ? true : null;
        });

        // Log auth events
        Event::listen(Login::class, function (Login $event) {
            activity('auth')
                ->causedBy($event->user)
                ->withProperties(['ip' => request()->ip(), 'user_agent' => request()->userAgent()])
                ->log('login');

            $event->user->notify(new \App\Notifications\UserLoginNotification(
                ipAddress: request()->ip(),
                userAgent: request()->userAgent(),
            ));
        });

        Event::listen(Logout::class, function (Logout $event) {
            activity('auth')
                ->causedBy($event->user)
                ->withProperties(['ip' => request()->ip()])
                ->log('logout');
        });

        Event::listen(Failed::class, function (Failed $event) {
            $builder = activity('auth')
                ->withProperties([
                    'ip' => request()->ip(),
                    'email' => $event->credentials['email'] ?? null,
                    'user_agent' => request()->userAgent(),
                ]);

            if ($event->user) {
                $builder->causedBy($event->user);
            }

            $builder->log('failed_login');
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
