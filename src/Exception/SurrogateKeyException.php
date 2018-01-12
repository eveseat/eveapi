<?php
/**
 * User: Warlof Tutsimo <loic.leuilliot@gmail.com>
 * Date: 12/01/2018
 * Time: 22:48
 */

namespace Seat\Eveapi\Exception;


use Exception;

class SurrogateKeyException extends Exception
{

	/**
	 * SurrogateKeyException constructor.
	 *
	 * @param string $string
	 */
	public function __construct( $string ) {}

}
