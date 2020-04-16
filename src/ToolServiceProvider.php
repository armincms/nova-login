<?php

namespace Armincms\NovaLogin;

use Laravel\Nova\Http\Middleware\RedirectIfAuthenticated;
use Armincms\NovaLogin\Http\Middleware\Authorize;
use Illuminate\Database\Eloquent\Builder;  
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;

class ToolServiceProvider extends ServiceProvider
{  
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    { 
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'nova-login');
        $this->loadJsonTranslationsFrom(__DIR__.'/../resources/lang'); 
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        
        $this->routes(); 
    }

    /**
     * Register the Nova routes.
     *
     * @return \Laravel\Nova\PendingRouteRegistration
     */
    public function routes()
    {
        Route::aliasMiddleware('nova.guest', RedirectIfAuthenticated::class);

        collect(app('login')->authenticators())
            ->mapInto(PendingRouteRegistration::class)
            ->map->register();   
    } 

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('login', function($app) {
            return tap(new LoginManager($app), function($manager) {
                $manager->extend(new Web);
            });
        });

        app(\Illuminate\Contracts\Http\Kernel::class)->prependMiddleware(
            Http\Middleware\GuardDetector::class
        ); 


        Builder::macro('verification', function() {
            $model = $this->getModel(); 
            
            if($model instanceof \Illuminate\Contracts\Auth\Authenticatable) {
                 return $model->morphOne(AuthCredential::class, 'auth');
            }

            unset(static::$macros['verification']);

            return $model->verification();
        });
    }
}
