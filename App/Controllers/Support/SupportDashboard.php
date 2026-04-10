<?php

namespace Controllers\Support;

use Core\Controller;
use Core\Request;
use Core\Session;

class SupportDashboard extends Controller
{
    public $me = null, $data = [], $request;

    public function __construct($me)
    {
        $this->me = $me;
        $this->request = new Request();
    }

    public function index()
    {
        // Only support users
        if (Session::get('emp_type') != 10) {
            echo "Access Restricted! You don't have permission to enter this page...";
            return;
        }

        return return2View($this, $this->me->viewDir . 'index');
    }
}
