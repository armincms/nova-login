<?php

namespace Armincms\NovaLogin\Http\Middleware;
 
use Illuminate\Support\Str; 


class GuardDetector
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\Response
     */
    public function handle($request, $next)
    { 
        if($auth = $this->detectGuard($request)) { 
            app('config')->set("nova.guard", $auth->guard()); 
            app('config')->set("nova.passwords", $this->passwords($auth->guard())); 
        }      

        return $next($request); 
    }

    /**
     * Determine whether this tool belongs to the package.
     *
     * @param  \Laravel\Nova\Tool  $tool
     * @return bool
     */
    public function detectGuard($request)
    {
        return app('login')->forDomain($request->getHost());    
    } 

    public function passwords(string $guard)
    {   
        return config("auth.guards.{$guard}.provider"); 
    }
}
