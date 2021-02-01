<?php 

namespace Armincms\NovaLogin;


use Illuminate\Support\Str;
use Illuminate\Http\Request;

abstract class Authenticator 
{ 
	public function title()
	{
		return __('Welcome Back!');
	} 

	public function name() : string
	{
		return Str::lower(class_basename($this));
	}

	public function guard() : string
	{
		return $this->name();
	}

	public function domain() : ?string
	{ 
		return $this->loginPath();
	}

	public function loginFields() : array
	{
		return [
			'username' => [
				'type' => 'text', 
				'label'=> __("Username"),
				'rules'=> [
					'requried'
				]
			],
			'password' => [
				'type' => 'password', 
				'label'=> __("Password"),
				'rules'=> [
					'requried'
				]
			],
		];
	} 

	public function attemptLogin($credentials, bool $remember = false)
	{ 
        return \Auth::guard('admin')->attempt($credentials, $remember);
	}

	public function credentials($request)
	{
		return [
			'username' => $request->username,
			'password' => $request->password,
		];
	} 

	public function loginRules()
	{
		return [
			'username' => 'required',
			'password' => 'required',
		];
	}

	protected function loginPath() : ?string
	{ 
		$host = $this->hostName(request()->getHost());
		$sub = Str::lower($this->name() . '.');
		$domain = Str::lower(Str::contains($host, $sub) ? $host : $sub.$host);
		
		return str_replace($host, $domain, request()->getHost());
	}

	public function checkDomain(string $host)
	{ 
		if(is_null($domain = $this->domain())) {
			return true;
		}

		return $this->hostName($domain) === $this->hostName($host);
	} 

    protected function hostName(string $host)
    {
        $parsed = parse_url($this->ensureProtocol($host)); 

        return Str::lower(Str::after($parsed['host'], 'www.'));
    }  

    public function ensureProtocol(string $domain)
    { 
        if(! preg_match('/^https?:\/\//', $domain)) {
            $domain = "http://{$domain}";
        }

        return $domain; 
    } 

    public function route($route, $params = [])
    { 
    	if($this->guard() !== config('nova-login.defaults.guard', 'user')) {
    		$route = $this->name().'.'.trim($route, '.');
    	} 

    	return route("nova.{$route}", $params);
    }

    /**
     * Get the post register / login redirect path.
     *
     * @return string
     */
    public function redirectPath()
    {
        return \Laravel\Nova\Nova::path();
    }

    public function authenticated()
    {
    	
    }
}
