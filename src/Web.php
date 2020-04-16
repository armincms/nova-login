<?php 

namespace Armincms\NovaLogin;
 

class Web extends Authenticator
{  
	public function domain() : ?string
	{ 
		return null;
	}

	public function loginFields() : array
	{
		return [
		];
	}

}