<?php  
namespace Armincms\NovaLogin;

use Illuminate\Database\Eloquent\Model; 
 
class AuthCredential extends Model
{  
	protected $guarded = [];

	// public static function boot()
	// {
	// 	parent::boot();

	// 	static::saving(function($model) { 
	// 		$model->fill([
	// 			'token' => md5($model->credentials .  time())
	// 		]); 
	// 	});
	// }
}