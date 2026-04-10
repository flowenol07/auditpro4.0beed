<?php 

/*
	App core class
 	Gets URL and loads controller
	URL FORMAT - /controller/method/params 
*/

namespace Core;

use Core\SiteUrls;

class App {

	private $_controller 	= 'Home';
	private $_method 		= 'index';
	private $_params 		= array();
	
	public function __construct() {

		$url = $this -> getURL();

		// Check Ajax Request
		/*if( isset( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] ) && 
			$_SERVER[ 'HTTP_X_REQUESTED_WITH' ] == 'XMLHttpRequest' )
			$this -> is_ajax = true;*/
		
		if( empty($url['controller']) || (!empty($url['controller']) && $url['controller'] != 'auth' && !Session::has("emp_id")) )
			Redirect::to( SiteUrls::getUrl('auth') );

		if( Session::has("emp_id") && Session::has("need_password_policy") &&
			($url['controller'] != 'password-policy' && $url['method'] != 'logout') )
			Redirect::to( SiteUrls::getUrl('passwordPolicy') );

		//find matching controller
		$me = SiteUrls::findMathchingController($url['controller']);	
		
		if( $url['controller'] == 'auditpro-evidences' && check_evidence_upload_strict() )
		{
			require_once EVIDENCE_UPLOAD['controller'];
			exit;
		}
				
		// echo Session::get('emp_type');
		// echo in_array(Session::get('emp_type'), $me -> accessControl);

		//check access control
		accessControlCheck($me);

		/* if(!empty(Session::get('emp_type')) && Session::get('emp_type') != '1' && !Session::has('assessment_id') && ( $me -> controller != 'Reports' && $me -> controller != 'AuditAssessment' ))
			Redirect::to( SiteUrls::getUrl('auditAssessment') ); */

		// Look for controller file if exists
		if(empty($url['controller']) || !is_object($me) || (is_object($me) && !file_exists(CONTROLLER . DS . (!empty($me -> controllerDir) ? ($me -> controllerDir . DS) : '') . $me -> controller . '.php' ) ) )
		{
			Except::exc_404( 'Controller Missing' );
			exit;
		}
		else
		{
			//me found insert dynamic id key
			$me -> menuKey = $me -> id;
			
			// Create new controller object
			$controller = strval(CONTROLLER_NAMESPACE . '\\' . (!empty($me -> controllerDir) ? ($me -> controllerDir . DS) : '') . ucwords($me -> controller));

			// replace / slash to \ update 24.08.2024
			$controller = preg_replace('/\//', '\\\\', $controller);
			$this -> _controller = new $controller($me);

			// Check, if the method is given in the URL //for custom method

			if( !empty($url['method']) && $url['method'] == 'data-table-ajx' && !method_exists($this -> _controller, 'dataTableAjax') )
			{
				Except::exc_404( 'Controller Method Missing' );
				exit;
			}
			else if( ( !empty($url['method']) && !method_exists($this -> _controller, $url['method']) ) && 
				( !empty($url['method']) && isset($me -> extraMethods) && !isset($me -> extraMethods -> { $url['method'] })) && 
				( empty($url['method']) && isset($me -> extraMethods) && !isset($me -> extraMethods -> { $url['controller'] }) )
			) {
				Except::exc_404( 'Controller Method Missing' );
				exit;
			}

			if( $url['method'] == 'data-table-ajx' )
				$this -> _method = 'dataTableAjax';
			else
			{
				//check using method
				if( !empty($url['method']) && method_exists($this -> _controller, $url['method']) )
					$this -> _method = $url['method'];
				
				// check extra methods // using method
				if( !empty($url['method']) && 
					isset($me -> extraMethods) && 
					isset($me -> extraMethods -> { $url['method'] }))
					$this -> _method = $me -> extraMethods -> { $url['method'] };

				// check extra methods // using controller
				elseif( empty($url['method']) && 
					isset($me -> extraMethods) && 
					isset($me -> extraMethods -> { $url['controller'] }))
					$this -> _method = $me -> extraMethods -> { $url['controller'] };

				//if no method default method will be index
				$this -> _method = ( !empty($this -> _method) && $this -> _method != 'index' ) ? $this -> _method : ( (isset($me -> method) && !empty($me -> method)) ? $me -> method : 'index' );
			}				

			// Check, if the method is given in the URL
			if( !empty($this -> _method) && !method_exists($this -> _controller, $this -> _method) ) {
				Except::exc_404( 'Controller Method Missings' );
				exit;
			}

			$url['parameters'] = !empty($url['parameters']) ? $url['parameters'] : [];

			// Call method from the controller class, pass the params
			call_user_func_array([ $this -> _controller, $this -> _method ], [ $url['parameters'] ]);
		}
	}

	private function getURL() {

		$parse_url = array( 'controller' => null, 'method' => null, 'parameters' => null );

		if (isset($_GET['url'])) {

			// Trim right slash
			$url = rtrim($_GET['url'], '/');

			// Sanitize URL string
			$url = filter_var($url, FILTER_SANITIZE_URL);

			// Convert into array
			$url = explode('/', $url);

			if(isset($url[0])) //set controller
				$parse_url['controller'] = trim_str($url[0]);

			if(isset($url[1])) //set method
				$parse_url['method'] = trim_str($url[1]);

			//set parameters //method call
			$parse_url = $this -> setGETParameters($parse_url, $url);

			//set GET parameters //method call
			$parse_url = $this -> setGETParameters($parse_url, null, 'get');

			unset($url);
		}

		return $parse_url;
	}

	private function setGETParameters($parse_url, $url = null, $type = 'url')
	{
		$temp_get = array();
		
		//check get has parameters
		if($type == 'get' && sizeof($_GET) > 1)
		{
			$temp_get = $_GET;
			unset( $temp_get['url'] );
		}
		else
		{
			//url parameters
			unset($url[0], $url[1]);

			if(is_array($url) && sizeof($url) > 0)
				$temp_get = array_values($url);
		}

		if(is_array($temp_get) && sizeof($temp_get) > 0)
		{
			foreach($temp_get as $c_get_key => $c_get_val)
			{
				$temp_gen_key = ($type == 'url') ? ('val_' . ($c_get_key + 1)) : $c_get_key;
				$parse_url['parameters'][ trim_str($temp_gen_key) ] = trim_str($c_get_val);
			}
		}

		unset($type, $temp_get, $temp_gen_key);

		return $parse_url;
	}
}