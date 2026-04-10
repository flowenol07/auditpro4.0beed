<?php

namespace Core;

class Request {

	// Request Input Vars Post, Get vars @var array
	protected $inputs = [];

	public function __construct()
	{
		$this -> setInputs();
	}
	
	public static function method($type, $callback) {

		// If request is what we want, run callback
		if ($_SERVER['REQUEST_METHOD'] === $type) {
			$callback();
		}
	}

	// Set Request Inputs array
	protected function setInputs()
	{
		foreach( $_GET as $input => $value )
			$this -> inputs[ $input ] = self::sanitize( (!is_array($value) ? trim_str( $value ) : $value ) );

		foreach( $_POST as $input => $value )
			$this -> inputs[ $input ] = self::sanitize( (!is_array($value) ? trim_str( $value ) : $value ) );

		// if( !empty( $_FILES ) )
		// $this -> files = UploadedFile::resolve();
	}

	public function setInputCustom($key, $value)
	{
		$this -> inputs[ $key ] = self::sanitize( trim_str( $value ) );
	}

	// Get Input value
	public function input( $key, $default = null )
	{
		// Get Input value
		if( $this -> has( $key ) )
			return $this -> inputs[ $key ];
		else
			return $default;
	}

	// Get All Request Inputs
	public function all()
	{
		return $this -> inputs;
	}

	// Check If Request has Specific Input
	public function has( $key )
	{
		if(array_key_exists($key, $this -> inputs) && $this -> inputs[ $key ] == '0')
			return true;
		
		return isset( $this -> inputs[ $key ] );
	}

	// remoe Input
	public function remove( $key )
	{
		unset($this -> inputs[ $key ]);
		return $this -> inputs;
	}

	// santize input data
	private static function sanitize( $string ) {
		
		if(!is_array($string))
			return htmlentities($string, ENT_QUOTES, 'UTF-8');

		return $string;
	}
}