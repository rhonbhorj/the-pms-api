<?php
use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';

require APPPATH . 'libraries/Format.php';
require APPPATH . 'libraries/Authorization_Token.php';
require_once(APPPATH . 'services/ApiService.php');
class Api extends REST_Controller
{

    function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Manila');
        $this->load->model('api_model', 'modelrepo');
        $this->apiService = new ApiService();
        $this->authorization_token = new Authorization_Token();
    }

    public function error()
    {
                $this->response([
                    'status' => false,
                    'status_code' => 404,
                    'message' => '404 Page Not Found',
                    
                ], Rest_Controller::HTTP_NOT_FOUND);

    }
    
    public function index_get()
    {
        $data['status'] = false;
        $data['status_code'] = 403;
        $data['message'] = 'Forbidden';

        $this->response($data, Rest_Controller::HTTP_FORBIDDEN);
    }
        public function index_post()
    {
        $data['status'] = false;
        $data['status_code'] = 403;
        $data['message'] = 'Forbidden';

        $this->response($data, Rest_Controller::HTTP_FORBIDDEN);
    }
    public function postback_post( $ref_number = 0 )
 {
        $ref_number = $_GET[ 'txnref' ];

        $this->output->set_content_type( 'application/json' );

        $data = json_decode( file_get_contents( 'php://input' ), true );

        $data_exist = $this->modelrepo->finddata( $ref_number );

        $call_back_data[ 'reference_number' ] = $ref_number;

        $call_back_data[ 'callback_data' ] = json_encode( $data );

        $call_back_data[ 'tbl_date' ] = date( 'Y-m-d H:i:s' );

        $call_back_data[ 'TxId' ] = $data[ 'TxId' ];

        $call_back_data[ 'referenceNumber' ] = $data[ 'referenceNumber' ];

        $call_back_data[ 'callback_status' ] = $data[ 'status' ];

        if ( $data_exist != false ) {

            $this->modelrepo->doInsertCallback( $call_back_data );
            $this->response( [
                'messege' => 'Failed',
                'error' => 'already transact'
            ], Rest_Controller::HTTP_UNAUTHORIZED );
        } else {

            // / insert data to tbl_callback
            $do_cback_data = $this->modelrepo->doInsertCallback( $call_back_data );

            if ( $do_cback_data ) {
                $TransData = $this->modelrepo->select_refNumber( $ref_number );
                // this client response
                $response = $this->apiService->call_back( $data, $TransData[ 'callback_uri' ] );
                $transData[ 'status' ] = $this->status_get( $data[ 'status' ] );
                $transData[ 'trace_reference' ] = $call_back_data[ 'TxId' ];
                // $transData[ 'referenceNumber' ] = $call_back_data[ 'referenceNumber' ];

                $transData[ 'modified_at' ] = date( 'Y-m-d H:i:s' );
                $trans_updated = $this->modelrepo->updateTransaction( $transData, $ref_number );

                if ( $trans_updated ) {

                    $cback_update[ 'client_data_resp' ] = json_encode( $response );
                    $doUpdateApiLog = $this->modelrepo->updateCallBack( $cback_update, $do_cback_data );





                    if ( $response[ 'status_code' ] == 200 || $response[ 'status_code' ] == 201 ) {

                        $this->response([
                             json_decode($response['response'],true)
                        ],$response['status_code']);
                    } else {
                        $this->response([
                            json_decode($response['response'],true)
                       ],$response['status_code']);
                    }
                }
            }
        }
    }

    function status_get( $type )
 {
        $caffeine = '';
        $map = [
            '1' => 'STARTED ',
            '2' => 'PENDING',
            '3' => 'FAILED',
            '4' => 'SUCCESS'
        ];

        $caffeine = $map[ $type ];
        return $caffeine;
    }

    // public function index_post()
    // {   
    //         $AVR = true;

    //         $today = date('Y-m-d H:i:s');
    //         $headers = $this->input->request_headers();
    //         $head = checkHeader($this);
    //         		$validateToken = $this->authorization_token->validateToken($headers);
	// 	    if ($validateToken['status'] == false) {

	// 		$AVR = false;

	// 		$resp = $validateToken;
	// 	    } elseif ($head['status'] == false) {

    //             $AVR = false;

    //             $err = $head;
                
    //             $this->response( $err, Rest_Controller::HTTP_BAD_REQUEST);
    //         } else {

    //             $this->form_validation->set_rules('page', 'page', 'trim|required');

    //             $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';

    //             switch ($contentType) {
    //                 case 'application/json':
    //                     $json = file_get_contents('php://input');
    //                     $_POST = json_decode($json, true);
    //                     $datapost = $_POST;
    //                     break;
    //                 default:
    //                     $datapost = array(
    //                         'page' => $this->input->post('page', true)
    //                     );
    //             }

    //             if ($this->form_validation->run() == FALSE) {
    //                 $FVE = $this->form_validation->error_array();
    //                 $this->response([
    //                     'status' => false,
    //                     'status_code' => 401,
    //                     'message' => 'Error validation',
    //                     'data'    => $FVE
    //                 ], Rest_Controller::HTTP_UNAUTHORIZED);
    //             } else {
    //                 $pdata['page'] = strip_tags(trim($datapost['page']));

    //                   $pageData = $this->modelrepo->chk_get_page($pdata); // validate 

    //                 if($pageData==false){
    //                     $this->response([
    //                     'status'      => false,
    //                     'status_code' => 400,
    //                     'message'     => 'invalid page',
    //                 ], Rest_Controller::HTTP_UNAUTHORIZED);

    //                 }  
    //                 $result = $this->modelrepo->get_page_details($pageData['id']); //page data

        
    //                     if (!empty($result)) {
                            
    //                     $data = [];

    //                     foreach ($result as $row) {
    //                        $getImage=  $this->modelrepo->get_page_image($row['image_id']);

    //                         $data[] = [
                                
    //                             'background_image' => $row['background_image'],
    //                             'title' => $row['title'],
    //                             'header' => $row['content_header'],
    //                             'body' => $row['content_body'],
    //                             'footer' => $row['footer'],
    //                             'image' => $row['image_id'],
    //                             'url' => $row['url'],
    //                             'carousell' => json_decode($row['carousell'],true),

    //                             'others' => json_decode($row['others'],true),
    //                             'img'=> $getImage
    //                         ];

    //                     }
    //                     $resp['status'] = true;

    //                     $resp['status_code'] = 200;

    //                     $resp['message'] = "Success";
                        
    //                     $resp['data'] = $data;
                        
    //                 }



    //                                         }
    //         }

    //         if ($AVR) {

    //             $this->response($resp, Rest_Controller::HTTP_OK);
    //         } else {

    //             $this->response($resp, Rest_Controller::HTTP_UNAUTHORIZED);
    //         }
    // }




 








	
}