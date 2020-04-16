<?php

namespace Armincms\NovaLogin\Contracts;


interface ShouldVerify
{
	public function verificationFields() : array; 
}
