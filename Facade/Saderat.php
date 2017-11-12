<?php namespace Larabookir\Saderat\Facade;
/**
 * @Author     Hamed Pakdaman - pakdaman.it@gmail.com
 * @website    larabook.ir
 * @link       http://pear.php.net/package/PackageName
 * @version    1.00
 */


use Illuminate\Support\Facades\Facade;

/**
 * @see \Larabookir\Saderat\SaderatResolver
 */
class Saderat extends Facade
{
	/**
	 * The name of the binding in the IoC container.
	 *
	 * @return string
	 */
	protected static function getFacadeAccessor()
	{
		return 'saderat';
	}
}
