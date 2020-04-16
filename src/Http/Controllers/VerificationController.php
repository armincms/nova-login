<?php

namespace Armincms\NovaLogin\Http\Controllers;

use Armincms\NovaLogin\Contracts\ReversedTwoFactorAuthentication;
use Armincms\NovaLogin\Contracts\TwoFactorAuthentication;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\AuthenticatesUsers; 
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Laravel\Nova\Nova;  

class VerificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Verification Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use ValidatesRequests;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('nova.guest:'.$this->auth()->guard());
        $this->middleware('throttle:60,30');
    } 

    /**
     * Show the application's verification form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showVerificationForm(Request $request)
    {  
        return view('nova-login::auth.verification', [
            'auth' => $this->auth()
        ]);
    }  

    /**
     * Handle a verification request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function verification(Request $request)
    {  
        $this->validate($request, [
            'mobile' => 'required|numeric'
        ]);  

        $this->auth()->verifyCredentials($credentials = $request->mobile); 
 
        return redirect()->to( $this->auth()->route('verify', $credentials) ); 
    }   

    public function showVerifyForm(Request $request, $credentials)
    {   
        return view('nova-login::auth.verify', [
            'auth' => $this->auth(),
            'credentials'=> $credentials,  
        ]);   
    }

    public function verify(Request $request, $credentials)
    {
        $this->validate($request, [
            'code' => function($attribute, $value, $fail) use ($credentials) { 
                if(! $this->auth()->validateForCredentials($credentials, $value)) {
                    $fail("Invalid verification code.");
                }
            }
        ]); 

        $this->auth()->attemptLogin($credentials, $request->filled('remember'));

        return $this->sendLoginResponse($request);
    }


    public function resend(Request $request, $credentials)
    {
        $this->auth()->verifyCredentials($credentials); 

        return [];
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

        return $this->auth($request)->authenticated($request)
                ?: redirect()->intended($this->auth()->redirectPath() ?? $this->redirectPath());
    }

    public function auth()
    {
        return app('login')->forDomain(request()->getHost());
    }

    public function rules(Request $request)
    {
        return $this->auth()->verificationFields()[$request->posting_method]['rules'] ?? [];
    }   
}
