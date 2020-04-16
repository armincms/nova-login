<?php

namespace Armincms\NovaLogin;

use Laravel\Nova\Nova;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Armincms\NovaLogin\Contracts\Registrable;
use Armincms\NovaLogin\Contracts\ShouldVerify;
use Laravel\Nova\Events\NovaServiceProviderRegistered; 
use Armincms\NovaLogin\Contracts\TwoFactorAuthentication; 

class PendingRouteRegistration
{
    /**
     * Indicates if the routes have been registered.
     *
     * @var bool
     */
    protected $registered = false;

    protected $authenticator;


    public function __construct(Authenticator $authenticator)
    { 
        $this->authenticator = $authenticator;
    } 

    public function group(array $middleware = ['web'], $routes)
    {  
        $name = $this->guard === config('nova-auth.defaults.guard', 'user') ? null : "{$this->name}."; 

        Route::namespace(__NAMESPACE__.'\Http\Controllers')
            ->domain($this->domain)
            ->middleware($middleware)
            ->as("nova.{$name}")
            ->prefix(Nova::path())
            ->group($routes);

        return $this;
    }

    /**
     * Register the Nova authentication routes.
     *
     * @param  array  $middleware
     * @return $this
     */
    public function withAuthenticationRoutes($middleware = ['web'])
    {
        $this
            ->group($middleware, function ($router) {
                Route::get('/login', 'LoginController@showLoginForm');
                Route::post('/login', 'LoginController@login')->name('login'); 
            })
            ->group(config('nova.middleware', []), function ($router) {
                Route::get('/logout', 'LoginController@logout')->name('logout');
            }); 

        return $this;
    }



    /**
     * Register the Nova authentication routes.
     *
     * @param  array  $middleware
     * @return $this
     */
    public function withRegisterationRoutes($middleware = ['web'])
    { 
        return $this->group(function ($router) {
            Route::get('/login', 'LoginController@showLoginForm');
            Route::post('/login', 'LoginController@login')->name('login');
        });
    }

    /**
     * Register the Nova password reset routes.
     *
     * @param  array  $middleware
     * @return $this
     */
    public function withPasswordResetRoutes($middleware = ['web'])
    {
        Nova::$resetsPasswords = true;

        $this->group(function ($router) {
            Route::get('/password/reset', 'ForgotPasswordController@showLinkRequestForm')->name('password.request');
            Route::post('/password/email', 'ForgotPasswordController@sendResetLinkEmail')->name('password.email');
            Route::get('/password/reset/{token}', 'ResetPasswordController@showResetForm')->name('password.reset');
            Route::post('/password/reset', 'ResetPasswordController@reset');
        });

        return $this;
    }

    /**
     * Register the Nova password reset routes.
     *
     * @param  array  $middleware
     * @return $this
     */
    public function withVerificationRoutes($middleware = ['web'])
    { 
        $this
            ->group($middleware, function ($router) {
                Route::get('/verification', 'VerificationController@showVerificationForm');
                Route::post('/verification', 'VerificationController@verification')
                    ->name('verification'); 
                Route::get(
                    '/verification/{credentials}', 'VerificationController@showVerifyForm'
                );
                Route::post('/verification/{credentials}', 'VerificationController@verify')
                    ->name('verify');
                Route::post(
                    '/verification/{credentials}/resend', 'VerificationController@resend'
                )->name('resend'); 
            }); 

        return $this; 
    }

    /**
     * Register the Nova routes.
     *
     * @return void
     */
    public function register()
    {
        $this->registered = true;

        $this->withAuthenticationRoutes();

        if($this->authenticator instanceof Registrable) {
            $this->withRegisterationRoutes();
        }

        if($this->authenticator instanceof TwoFactorAuthentication) {
            $this->withVerificationRoutes();
        }

        Event::listen(NovaServiceProviderRegistered::class, function () {
            Route::middleware(config('nova.middleware', []))
                ->domain($this->domain)
                ->group(function () {
                    Route::get(Nova::path(), 'Laravel\Nova\Http\Controllers\RouterController@show')->name('nova.index');
                });

            Route::middleware(config('nova.middleware', []))
                ->domain($this->domain)
                ->as("nova.{$this->name}.")
                ->prefix(Nova::path())
                ->get('/{view}', 'Laravel\Nova\Http\Controllers\RouterController@show')
                ->where('view', '.*');
        });
    }

    /**
     * Handle the object's destruction and register the router route.
     *
     * @return void
     */
    public function __destruct()
    {
        if (! $this->registered) {
            $this->register();
        }
    }

    public function __get($key)
    {
        return $this->authenticator->{$key}();
    }
}
