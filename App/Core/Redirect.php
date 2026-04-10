<?php

namespace Core;

class Redirect {
    
    protected static $url;

	//  Redirect To Specific Location
	public static function to( $url )
	{
		header( 'Location: ' . $url );
		exit;
	}

}

?>