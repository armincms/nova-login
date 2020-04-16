<?php

namespace Armincms\NovaLogin\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

interface TwoFactorAuthentication
{
	public function verified(Authenticatable $user) : bool; 
}
