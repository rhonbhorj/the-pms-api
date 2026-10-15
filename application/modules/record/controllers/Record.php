<?php
use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';

require APPPATH . 'libraries/Format.php';

require APPPATH . 'libraries/Authorization_Token.php';

class Record extends REST_Controller
{

    function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Manila');
        $this->load->model('Record_model', 'modelrepo');
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
    function vallidate_access()
    {
        $headers = $this->input->request_headers();
        $today = date('Y-m-d H:i:s');

        $head = checkHeader($this);
        $validateToken = $this->authorization_token->validateToken($headers);
        if ($validateToken['status'] == false) {

            $result['status'] = false;
            $result['data'] = $validateToken;
        } elseif ($head['status'] == false) {

            $result['status'] = false;
            $result['data'] = $head;
        } else {

            $result['status'] = true;
            $result['data'] = $head;
        }

        return $result;
    }
    public function index_post()
    {
        $data['status'] = false;
        $data['message'] = 'Forbidden';
        $this->response($data, Rest_Controller::HTTP_FORBIDDEN);
    }

    private function sanitizeInput($input)
    {
        return preg_replace("/[^a-zA-Z0-9\s,@.-]/", "", trim($input));
    }

    public function transaction_status_post()
    {
        $AVR = true;

        $chkAcess = $this->vallidate_access();

        if ($chkAcess['status'] == false) {

            $AVR = $chkAcess['status'];
            $resp = $chkAcess['data'];
        } else {

            $this->form_validation->set_rules('reference_number', 'reference_number', 'trim|required');

       

            $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';

            switch ($contentType) {
                case 'application/json':
                    $json = file_get_contents('php://input');
                    $_POST = json_decode($json, true);
                    $datapost = $_POST;
                    break;
                default:
                    $datapost = array(
                        'reference_number' => $this->input->post('reference_number', true),
                   
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
                $pdata = array(
                  
                    'reference_number' => $this->sanitizeInput($datapost['reference_number']), //user is login
                );

                
                   

                 $transactioDetail =   $this->modelrepo->get_transaction_detail($pdata);
                if( !$transactioDetail ){
                    $AVR = false;
                    $resp['status'] = false;
                    $resp['message'] = "No data found";
                }else{
                    $resp['status'] = true;
                    $resp['message'] = "success";
                    $resp['data'] = array( "status"                   => $transactioDetail['status'],
                                            "transaction_type"        => "cashin",
                                            "amount"                  => $transactioDetail['txn_amount'],
                                            "reference_number"        => $transactioDetail['reference_number'],
                                            "transaction_Reference"   => $transactioDetail['TxId'],
                                            "date_quested"            => $transactioDetail["requested_at"],
                                            "date_modified"           => $transactioDetail["modified_at"]??""
                                    );
                }

  
                }

             
             
            
        }
        if ($AVR) {

            $this->response($resp, Rest_Controller::HTTP_OK);
        } else {

            $this->response($resp, Rest_Controller::HTTP_UNAUTHORIZED);
        }

    }

     public function qr_raw_post()
    {
        $AVR = true;

        $chkAcess = $this->vallidate_access();

        if ($chkAcess['status'] == false) {

            $AVR = $chkAcess['status'];
            $resp = $chkAcess['data'];
        } else {

            $this->form_validation->set_rules('reference_number', 'reference_number', 'trim|required');

       

            $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';

            switch ($contentType) {
                case 'application/json':
                    $json = file_get_contents('php://input');
                    $_POST = json_decode($json, true);
                    $datapost = $_POST;
                    break;
                default:
                    $datapost = array(
                        'reference_number' => $this->input->post('reference_number', true),
                   
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
                $pdata = array(
                    'reference_number' => $this->sanitizeInput($datapost['reference_number']), 
                );

                 $transactioDetail =   $this->modelrepo->get_transaction_detail($pdata);

                if( !$transactioDetail ){
                    $AVR = false;
                    $resp['status'] = false;
                    $resp['message'] = "No data found";
                }else{
                    if($transactioDetail['status'] !='CREATED'){
                        $AVR = false;
                        $resp['status']  = false;
                        $resp['message'] =  "your transaction is already ".$transactioDetail['status'];
                    } 

                        $resp['status']  = true;
                        $resp['message'] = "success";
                        $resp['data']    = array( "qr_raw" => $transactioDetail['qr']);
                }

  
            }
        }
        if ($AVR) {

            $this->response($resp, Rest_Controller::HTTP_OK);
        } else {

            $this->response($resp, Rest_Controller::HTTP_UNAUTHORIZED);
        }

    }

      public function cashin_cancel_post()
    {
        $AVR = true;

        $chkAcess = $this->vallidate_access();

        if ($chkAcess['status'] == false) {

            $AVR = $chkAcess['status'];
            $resp = $chkAcess['data'];
        } else {

            $this->form_validation->set_rules('reference_number', 'reference_number', 'trim|required');

       

            $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';

            switch ($contentType) {
                case 'application/json':
                    $json = file_get_contents('php://input');
                    $_POST = json_decode($json, true);
                    $datapost = $_POST;
                    break;
                default:
                    $datapost = array(
                        'reference_number' => $this->input->post('reference_number', true),
                   
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
                $pdata = array(
                    'reference_number' => $this->sanitizeInput($datapost['reference_number']), 
                );

                 $transactioDetail =   $this->modelrepo->get_transaction_detail($pdata);

                if( !$transactioDetail ){
                    $AVR = false;
                    $resp['status'] = false;
                    $resp['message'] = "No data found";
                }else{
                    if($transactioDetail['status'] !='CREATED'){
                        $AVR = false;
                        $resp['status']  = false;
                        $resp['message'] =  "your transaction is already ".$transactioDetail['status'];
                    }else{ 
                        $callNgsiCancel =cancel_qr_api( $pdata );


                        if( $callNgsiCancel['status_code'] != '200'){
                            $AVR = false;
                            $resp['status']  = false;
                            $resp['message'] = $callNgsiCancel['response']['message']."." ;
                           

                        }else{

                            $updaTocancel = $this->modelrepo->update_to_cancel($pdata);

                            $resp['status']  = true;
                            $resp['message'] = "success";
                            $resp['data']    =  $callNgsiCancel['response']['message'];
                        }
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
  
   

       ////this is for  redirect url fetch data
        public function txn_status_post()
    {
        $AVR = true;

        $chkAcess = $this->vallidate_access();

        if ($chkAcess['status'] == false) {

            $AVR = $chkAcess['status'];
            $resp = $chkAcess['data'];
        } else {

            $this->form_validation->set_rules('reference_number', 'reference_number', 'trim|required');

       

            $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';

            switch ($contentType) {
                case 'application/json':
                    $json = file_get_contents('php://input');
                    $_POST = json_decode($json, true);
                    $datapost = $_POST;
                    break;
                default:
                    $datapost = array(
                        'reference_number' => $this->input->post('reference_number', true),
                   
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
                $pdata = array(
                  
                    'reference_number' => $this->sanitizeInput($datapost['reference_number']), //user is login
                );

                
                   

                 $transactioDetail =   $this->modelrepo->get_transaction_detail($pdata);
                if( !$transactioDetail ){
                    $AVR = false;
                    $resp['status'] = false;
                    $resp['message'] = "No data found";
                }else{
                    $resp['status'] = true;
                    $resp['message'] = "success";
                    $resp['data'] = array( "status"                   => $transactioDetail['status'],
                                            "transaction_type"        => "cashin",
                                            "amount"                  => $transactioDetail['txn_amount'],
                                            "reference_number"        => $transactioDetail['reference_number'],
                                            "transaction_Reference"   => $transactioDetail['TxId'],
                                            "date_quested"            => $transactioDetail["requested_at"],
                                            "date_modified"           => $transactioDetail["modified_at"]??"",

                                            "mobile_number"           => $transactioDetail["mobile_number"],
                                            "lto_fees"                => $transactioDetail['lto_fees'],
                                            "general_fund"            => $transactioDetail['general_fund'],
                                            "seat_belt_act_fee"       => $transactioDetail['seat_belt_act_fee'],
                                            "mvuc_fee"                => $transactioDetail["mvuc_fee"],
                                            "clean_air_act_fee"       => $transactioDetail["clean_air_act_fee"],

                                            "legal_research_fee"      => $transactioDetail['legal_research_fee'],
                                            "it_fee"                  => $transactioDetail['it_fee'],
                                            "ngsi_fee"                => $transactioDetail["ngsi_fee"],
                                            "qr"                      => $transactioDetail["qr"],
                                            "return_url"              => $transactioDetail["return_url"],


                                    );
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