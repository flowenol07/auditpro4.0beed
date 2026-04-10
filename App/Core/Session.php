<?php

namespace Core;

class Session {

    public static function has($key, $child_key = null) {

        if( $child_key )
			return isset( $_SESSION[ $key ][ $child_key ] ) ? true : false;
		else
		    return ( isset( $_SESSION[ $key ] ) ) ? true : false;
	}

    public static function set($key, $value) {
		return $_SESSION[ $key ] = $value;
	}

    public static function get($key, $default = null) {
		return (self::has($key) && !empty( $_SESSION[ $key ] )) ? $_SESSION[ $key ] : $default;
	}

    public static function delete($key) {

		if ( self::has($key) )
			unset($_SESSION[$key]);
	}

    public static function flash($key, $string = null) {

		if ( self::has($key) && $string == null )
        {
			$session = self::get($key);
			self::delete($key);
			return $session;
		}
        elseif($string != null)
            self::set($key, $string);
	}

    public static function isLoggedIn()
	{
		return self::has( 'emp_id' ) /*&& $this -> emp_id*/;
	}

}

?>