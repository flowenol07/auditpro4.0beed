<?php

namespace Core;

use Core\Notifications;
use Core\SiteUrls;

class Controller {

	protected $_model;

	// Load the model
	public function model($model) {

		// Require the model file
		require_once APP_ROOT . DS . 'Models' . DS . $model . '.php';

		// Instantiate new model object
		return new $model();
	}

	// Load the view
	public function view($view, $data = array(), $optional = null) {

		$data['noti'] = new Notifications();
		$data['siteUrls'] = new SiteUrls();
		$data['userDetails'] = Session::get('emp_details');

		// Require the header file
		if($optional == 'login')
			require_once APP_ROOT . DS . 'Views/partials/head-login.php';
		else
			require_once APP_ROOT . DS .  'Views/partials/head.php';
			
		if($optional == null)
		{
			require_once APP_ROOT . DS .  'Views/partials/header.php';
			require_once APP_ROOT . DS .  'Views/partials/page-heading.php';
			echo $data['noti']::getSessionAlertNoti();
		}

		// Require the view file
		require_once APP_ROOT . DS . 'Views/' . $view . '.php';
        
		// Require the footer file
		if($optional == 'login')
			require_once APP_ROOT . DS . 'Views/partials/footer-login.php';
		else
			require_once APP_ROOT . DS . 'Views/partials/footer.php';
		
		if(isset($data['data']['js']))
		{
			foreach($data['data']['js'] as $cJsFile)
			{
				if(strpos($cJsFile, URL) !== false)
					echo "\n" . '<script src="'. $cJsFile .'"></script>' . "\n";
				else
					echo "\n" . '<script src="'. PUBLIC_JS . $cJsFile .'"></script>' . "\n";
			}
		}

		if(isset($data['data']['inline_js']))
		{
			//for inline js
			echo $data['data']['inline_js'];
		}

		require_once APP_ROOT . DS . 'Views/partials/close.php';
	}
}

?>