<?php

namespace Core;

abstract class Model extends DBConnection {

	protected $_db;

	public function __construct() {
		$this -> _db = self::instance();
	}
}

?>