<?php
use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';

require APPPATH . 'libraries/Format.php';

require APPPATH . 'libraries/Authorization_Token.php';

class Account extends REST_Controller
{

    function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Manila');
        $this->load->model('Account_model', 'modelrepo');
        $this->load->helper('header_helper');
        $this->authorization_token = new Authorization_Token();
    }

    public function index_get()
    {
        $data['status'] = false;
        $data['message'] = 'Forbidden';

        $this->response($data, Rest_Controller::HTTP_FORBIDDEN);
    }

    public function index_post()
    {
        $data['status'] = false;
        $data['message'] = 'Forbidden';
        $this->response($data, Rest_Controller::HTTP_FORBIDDEN);
    }


   

    public function usertype_list_get()
    {
  	    $AVR = true;
		$headers = $this->input->request_headers();
		$today = date('Y-m-d H:i:s');

		$head = checkHeader($this);
		$validateToken = $this->authorization_token->validateToken($headers);
		if ($validateToken['status'] == false) {

			$AVR = false;

			$resp = $validateToken;
		} elseif ($head['status'] == false) {
			$AVR = false;

			$resp = $head;
		} else {

       

                        $resp = $this->modelrepo->get_account_type();
                
            
        }

        if ($AVR) {

            $this->response($resp, Rest_Controller::HTTP_OK);
        } else {

            $this->response($resp, Rest_Controller::HTTP_UNAUTHORIZED);
        }
    }

}
