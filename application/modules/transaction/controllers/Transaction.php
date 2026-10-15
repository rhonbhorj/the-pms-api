<?php
use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';

require APPPATH . 'libraries/Format.php';
require APPPATH . 'libraries/Authorization_Token.php';

class Transaction extends REST_Controller
{

    function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Manila');
        $this->load->model('transaction_model', 'modelrepo');
        $this->load->helper('header_helper');
         $this->load->helper('ngsi_payment');

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

   public function filter_date_post()
    {
     $AVR = true;
        $headers = $this->input->request_headers();
        $today = date('Y-m-d H:i:s');

        $head = checkHeader($this);
        $validateToken = $this->authorization_token->validateToken($headers);
        if ($validateToken['status'] == false) {

            $AVR = false;

            $this->response($validateToken, Rest_Controller::HTTP_FORBIDDEN);
        } elseif ($head['status'] == false) {
            $AVR = false;

            $this->response($head, Rest_Controller::HTTP_FORBIDDEN);
        } else {

           
            $this->form_validation->set_rules('session_id', 'session_id', 'trim|required');
            $this->form_validation->set_rules('status', 'status', 'trim|required');
            $this->form_validation->set_rules('date_from', 'date_from', 'trim|required|callback_validate_date');
            $this->form_validation->set_rules('date_to', 'date_to', 'trim|required|callback_validate_date');
      

            $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';

            switch ($contentType) {
                case 'application/json':
                    $json = file_get_contents('php://input');
                    $_POST = json_decode($json, true);
                    $datapost = $_POST;
                    break;
                default:
                    $datapost = array(
                        
                        'session_id' => $this->input->post('session_id', true),
                        'status' => $this->input->post('status', true),
                        'date_from' => $this->input->post('date_from', true),
                        'date_to' => $this->input->post('date_to', true)
                    );
            }

            if ($this->form_validation->run() == FALSE) {
                $FVE = $this->form_validation->error_array();
                $this->response([
                    'status' => false,
                    'message' => 'Error validation',
                    'data' => $FVE
                ], Rest_Controller::HTTP_UNAUTHORIZED);
            } else {
               
                $pdata['session_id'] = $this->sanitizeInput( strip_tags(trim($datapost['session_id'])) );
                $pdata['date_to']    = $this->sanitizeInput( strip_tags(trim($datapost['date_to'])) );
                $pdata['date_from']  = $this->sanitizeInput( strip_tags(trim($datapost['date_from'])) );
                $pdata['status']     = $this->sanitizeInput( strip_tags(trim($datapost['status'])) );
                

         
                $validateSession = $this->modelrepo->validate_session($pdata);

                if ( !$validateSession ) {
                    $AVR = false;
                    $resp['status']      = false;
                    $resp['status_code'] = 1001;
                    $resp['message']     = "session denied";
                } else {

                      
                     $getTransaction = $this->modelrepo->get_transaction_by_date_range($pdata);

                        if(!$getTransaction){
                        $AVR = false;
                        $resp['status']      = false;
                        $resp['status_code'] = 401;
                        $resp['message']     = "No data";
                        $resp['data']        = $getTransaction;

                        }else{
                        
                        $resp['status']      = true;
                        $resp['status_code'] = 200;
                        $resp['message']     = "Success";
                        $resp['data']        = $getTransaction;



                        }
        
                }
            }
        }

        if ($AVR) {

            $this->response($resp, Rest_Controller::HTTP_OK);
        } else {

            $this->response($resp, Rest_Controller::HTTP_UNAUTHORIZED);
        }

    }

        private function sanitizeInput($input)
    {
        return preg_replace("/[^a-zA-Z0-9\s_,.@-]/", "", trim($input));
    }

      public function validate_date($date)
    {
        // Check if date format is valid (e.g., YYYY-MM-DD)
        if (DateTime::createFromFormat('Y-m-d', $date) !== FALSE) {
            return TRUE;
        } else {
            $this->form_validation->set_message('validate_date', 'The {field} field must be a valid date in YYYY-MM-DD format.');
            return FALSE;
        }
    }
    public function search_data_post()
    {
        $AVR = true;
        $headers = $this->input->request_headers();
        $today = date('Y-m-d H:i:s');

        $head = checkHeader($this);
        $validateToken = $this->authorization_token->validateToken($headers);
        if ($validateToken['status'] == false) {

            $AVR = false;

            $this->response($validateToken, Rest_Controller::HTTP_FORBIDDEN);
        } elseif ($head['status'] == false) {
            $AVR = false;

            $this->response($head, Rest_Controller::HTTP_FORBIDDEN);
        } else {

           
            $this->form_validation->set_rules('session_id', 'session_id', 'trim|required');
            $this->form_validation->set_rules('search', 'search', 'trim|required');
      
      

            $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';

            switch ($contentType) {
                case 'application/json':
                    $json = file_get_contents('php://input');
                    $_POST = json_decode($json, true);
                    $datapost = $_POST;
                    break;
                default:
                    $datapost = array(
                        
                        'search' => $this->input->post('search', true),
                        
                    );
            }

            if ($this->form_validation->run() == FALSE) {
                $FVE = $this->form_validation->error_array();
                $this->response([
                    'status' => false,
                    'message' => 'Error validation',
                    'data' => $FVE
                ], Rest_Controller::HTTP_UNAUTHORIZED);
            } else {
               
                $pdata['session_id'] = $this->sanitizeInput( strip_tags(trim($datapost['session_id'])) );
                $pdata['search']    = $this->sanitizeInput( strip_tags(trim($datapost['search'])) );


         
                $validateSession = $this->modelrepo->validate_session($pdata);

                if ( !$validateSession ) {
                    $AVR = false;
                    $resp['status']      = false;
                    $resp['status_code'] = 1001;
                    $resp['message']     = "session denied";
                } else {

                      
                     $searchTransaction = $this->modelrepo->search_transaction_details($pdata);

                        if(!$searchTransaction){
                        $AVR = false;
                        $resp['status']      = false;
                        $resp['status_code'] = 401;
                        $resp['message']     = "No data";
                        $resp['data']        = [];

                        }else{
                        
                        $resp['status']      = true;
                        $resp['status_code'] = 200;
                        $resp['message']     = "Success";
                        $resp['data']        = $searchTransaction;



                        }
        
                }
            }
        }

        if ($AVR) {

            $this->response($resp, Rest_Controller::HTTP_OK);
        } else {

            $this->response($resp, Rest_Controller::HTTP_UNAUTHORIZED);
        }
    
    }
}