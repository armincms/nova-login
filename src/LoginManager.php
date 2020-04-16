<?php 

namespace Armincms\NovaLogin;
  

class LoginManager  
{  
    /**
     * The application instance.
     *
     * @var \Illuminate\Contracts\Foundation\Application
     */
    protected $app;

    /**
     * The registered Authenticators.
     *
     * @var array
     */
    protected $authenticators = [];
  
    /**
     * Register a custom driver creator Closure.
     *
     * @param  string  $driver
     * @param  \Closure  $callback
     * @return $this
     */
    public function extend(Authenticator $authenticator)
    {
        $this->authenticators[] = $authenticator;

        return $this;
    }

    public function guard(string $name = null) : ?Authenticator
    {
        if(is_null($name)) { 
            return $this->forDomain($this->app['request']->getHost());
        } 

        return Collection::make($this->authenticators)->last(function($auth) use ($name) {
            return $auth->name() === $name;
        });
    } 

    public function forDomain(string $domain) : ?Authenticator
    { 
        return collect($this->authenticators)->sortByDesc(function($auth) {
            return empty($auth->domain());
        })->last(function($auth) use ($domain) {
            return $auth->checkDomain($domain);
        });  
    }

    public function authenticators()
    {
    	return $this->authenticators;
    }  
}