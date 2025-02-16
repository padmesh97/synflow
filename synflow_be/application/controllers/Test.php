<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once APPPATH . 'libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Test extends REST_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index_get()
    {
        $data['status'] = 'success';

        $this->response($data, 200);
        // phpinfo();
    }

}
