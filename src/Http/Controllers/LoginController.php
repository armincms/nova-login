<?php

namespace Armincms\NovaLogin\Http\Controllers;

use Armincms\NovaLogin\Contracts\ReversedTwoFactorAuthentication;
use Armincms\NovaLogin\Contracts\TwoFactorAuthentication;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\InteractsWithTime;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Laravel\Nova\Nova;  

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers, ValidatesRequests, InteractsWithTime;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('nova.guest:'.config('nova.guard'))->except('logout');
    }


    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function login(Request $request)
    {  
        $this->validate($request, $this->auth()->loginRules($request));

        $credentials = $request->only( array_keys($this->auth()->loginFields()) ); 

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        } 

        if ($this->auth()->attemptLogin($credentials)) {
            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request); 
    } 

    public function username()
    {
        return collect($this->auth(request())->loginFields())->filter(function($filed, $name) {
            return $name !== 'password';
        })->keys()->first();
    }


    /**
     * Send the response after the user was authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        $this->clearLoginAttempts($request);

        return $this->auth($request)->authenticated($request)
                ?: redirect()->intended($this->auth()->redirectPath() ?? $this->redirectPath());
    }

    public function auth()
    {
        return app('login')->forDomain(request()->getHost());
    }
 
    /**
     * Show the application's login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm(Request $request)
    { 
        $auth = app('login')->forDomain($request->getHost());

        if($auth instanceof ReversedTwoFactorAuthentication) {
            // if need verification
            return redirect()->to($auth->route('verification'));
        }  

        return view('nova-login::auth.login', [
            'auth' => $auth
        ]);
    }  

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    { 
        $this->guard()->logout();

        $request->session()->invalidate();

        return redirect($this->redirectPath());
    }

    /**
     * Get the post register / login redirect path.
     *
     * @return string
     */
    public function redirectPath()
    {
        return Nova::path();
    }
}
