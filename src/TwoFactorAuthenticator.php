<?php 

namespace Armincms\NovaLogin;
 
use Armincms\NovaLogin\Contracts\TwoFactorAuthentication;
use Illuminate\Contracts\Auth\Authenticatable;

abstract class TwoFactorAuthenticator extends Authenticator implements TwoFactorAuthentication
{  
	public function verified(Authenticatable $user) : bool
	{

	}  

	public function verifyCredentials(string $credentials)
	{ 
		if($user = $this->retrieveByCredentials($credentials)) {
	 		return tap($credentials, function($credentials) {
				$verification = AuthCredential::updateOrCreate(['credentials' => $credentials], [
					'token' => bcrypt($code = rand(99999,999999)),
					'verified' => false,
				]); 

				return send_sms(
					$credentials, __("Your verification code is :code", [
						'code' => $code 
					])
				);   
	 		});  
		}  

 		throw new \Exception('Invalid User'); 	
	} 

	public function retrieveByCredentials(string $mobile)
	{
		$user = $this->createModel()->whereMeta('mobile', $mobile)->first();

		if(is_null($user)) {
			$user = $this->createModel()->firstOrCreate([
				'username' => request('username', $mobile),
				'firstname' => request('firstname'),
				'lastname' => request('lastname'),
				'displayname' => request('displayname'), 
				'email' => request('email', $mobile.'@example.com'), 
			], ['password' => bcrypt($mobile)]);

			$user->setMeta('mobile', $mobile);
			$user->save();
		}

		return $user;
	} 

	public function attemptLogin($credentials, bool $remember = fasle)
	{ 
		$user = $this->createModel()->where('username', $credentials)->firstOrFail();

		return \Auth::guard('user')->login($user, $remember); 
	}

	public function resend(string $credentials)
	{
		return $this->prepareTokenForCredentials($verification->credentials);
	}

	public function validateForCredentials(string $credentials, string $code = null)
	{  
		$verification = AuthCredential::where('credentials', $credentials)->first();  

		return app('hash')->driver('bcrypt')->check($code, $verification->token); 
	} 

	public function createModel()
	{
		return \Auth::guard('user')->getProvider()->createModel();
	}

	public function loginRules()
	{
		return [
			'username' => 'required',
			'password' => 'required',
		];
	}
}