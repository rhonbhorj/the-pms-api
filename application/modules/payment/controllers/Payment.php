<?php
use Restserver\Libraries\REST_Controller;

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . 'libraries/REST_Controller.php';

require APPPATH . 'libraries/Format.php';
require APPPATH . 'libraries/Authorization_Token.php';
require_once(APPPATH . 'services/ApiService.php');
class Payment extends REST_Controller
{

    function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Asia/Manila');

        $this->load->model('pAYMENT_model', 'modelrepo');
        $this->load->helper('header_helper');
        $this->load->helper('ngsi_payment');
        $this->apiService = new ApiService();
      

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



	private function _validate_request($request)
	{

		$chk_reference_number =  $this->modelrepo->chk_reference_number($request);

		if ($chk_reference_number != false) {


			$return_data['status'] = false;
			$return_data['message'] = 'Transaction with this client ref (' . $request['reference_number'] . ') already exists.';
			
		} else {

			if ($request['endpoint'] != "p2m-generateQR") {
				$return_data['status'] = false;
				$return_data['message'] = 'invalid endpoint';
			} else {
				if ($request['method'] != 'dynamic' && $request['method'] != 'static') {
					$return_data['status'] = false;
					$return_data['message'] = 'method not available';
				} else {

					$return_data = false;
				}
			}
		}

		return  $return_data;
	}
	public function payin_post()
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
			$contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';

			switch ($contentType) {
				case 'application/json':
					$json = file_get_contents('php://input');
					$_POST = json_decode($json, true);
					$datapost = $_POST;
					// if (isset($_POST['merchant_details']) && isset($_POST['email_confirmation'])) {   //client request
                    if (isset($_POST['merchant_details'])) {
						// Merge merchant_details into main $_POST 
						$_POST = array_merge($_POST, $_POST['merchant_details']);
						// $_POST = array_merge($_POST, $_POST['email_confirmation']); //client request
                        $_POST = array_merge($_POST, $_POST['other_details']);
					} else {

						$resp['status'] = false;
						$resp['message'] = 'Invalid request';
						$this->output
							->set_status_header(401)
							->set_content_type('application/json')
							->set_output(json_encode($resp));
						return;
					}

					break;

				default:
					$resp['status'] = false;
					$resp['message'] = 'Invalid Content type';
					$this->output
						->set_status_header(401)
						->set_content_type('application/json')
						->set_output(json_encode($resp));
					return;
			}

			// Now start validation
			$this->form_validation->set_data($_POST);

			$this->form_validation->set_rules('endpoint', 'Endpoint', 'trim|required');
			$this->form_validation->set_rules('method', 'Method', 'trim|required');
			$this->form_validation->set_rules('return_url', 'return_url', 'trim|required|valid_url');
			$this->form_validation->set_rules('callback_url', 'callback_url', 'trim|required|valid_url');
			$this->form_validation->set_rules('txn_amount', 'txn_amount', 'trim|required|numeric');
			$this->form_validation->set_rules('reference_number', 'reference_number', 'trim|required|min_length[5]|max_length[40]');
			$this->form_validation->set_rules('txn_type', 'txn_type', 'trim|required|numeric');
			// $this->form_validation->set_rules('name', 'Name', 'trim|required');
			// $this->form_validation->set_rules('mobile_number', 'mobile_number', 'trim|required|regex_match[/^[0-9]{10,15}$/]');

            // $this->form_validation->set_rules('email', 'email', 'trim|required|valid_email');
			// $this->form_validation->set_rules('auto', 'auto', 'trim|required');




			$ins_data['params'] = json_encode($datapost);

			$ins_data['request_at'] = $today;

			$ins_data['method'] = $_SERVER['REQUEST_METHOD'];

			$ins_data['uri'] = $this->uri->uri_string();

			$apiLogId = $this->modelrepo->do_apilogs($ins_data);
			if ($apiLogId) {

				if ($this->form_validation->run() == FALSE) {
					$FVE = $this->form_validation->error_array();
					$this->response([
						'status' => false,
						'message' => 'Error validation.',
						'data' => $FVE
					], Rest_Controller::HTTP_UNAUTHORIZED);
				} else {
					if ($datapost['merchant_details']['txn_amount'] < 10) {
						$AVR = false;
						$data['status'] = false;
						$data['message'] = 'The amount must be minimun of 10';
						$resp = $data;
					} else {


						$pdata['endpoint'] = strip_tags(trim($datapost['endpoint']));

						$pdata['method'] = strip_tags(trim($datapost['merchant_details']['method']));

						$pdata['reference_number'] = strip_tags(trim($datapost['reference_number']));

						$pdata['txn_amount'] = strip_tags(trim($datapost['merchant_details']['txn_amount']));

						$pdata['payment_type'] = strip_tags(trim($datapost['merchant_details']['txn_type']));

						// $pdata['name'] = strip_tags(trim($datapost['merchant_details']['name']));
                        	$pdata['name'] = "LTO";

						// $pdata['mobile_number'] = strip_tags(trim($datapost['merchant_details']['mobile_number']));
                        $pdata['mobile_number'] = "09123456789"; //this is of for now 


						$pdata['return_url'] = strip_tags(trim($datapost['return_url']));
						$pdata['callback_url'] = strip_tags(trim($datapost['callback_url']));
		
						$pdata['other_details'] = $datapost['other_details'] ?? null;

						if (!empty($datapost['other_details'])) {
							if (is_array($datapost['other_details'])) {
								$pdata['other_details'] =  $datapost['other_details'];

							} else {


								// Handle the case where it's not an array (optional)
								$this->response([
									'status' => false,
									'message' => 'other_details data must be key and value.',

								], Rest_Controller::HTTP_UNAUTHORIZED);
							}
						} else {
							$pdata['other_details'] = '';
						}

						$vadidate =  $this->_validate_request($pdata);

						if ($vadidate == true) {
							$AVR = false;
							$resp = $vadidate;
						} else {
							// if (isset($datapost['email_confirmation']['email'])) {
							// 	$this->form_validation->set_rules('email', 'email', 'trim|required|valid_email');
							// 	$this->form_validation->set_rules('auto', 'auto', 'trim|required');

							// 	//   $this->form_validation->set_rules('auto', 'auto', 'trim|require');
							// 	if ($this->form_validation->run() == FALSE) {
							// 		$emailValidationError = $this->form_validation->error_array();
							// 		$this->response([
							// 			'status' => false,
							// 			'message' => 'Error validation',
							// 			'data' => $emailValidationError
							// 		], Rest_Controller::HTTP_UNAUTHORIZED);
							// 	} else {
							// 		$pdata['email'] = strip_tags(trim($datapost['email_confirmation']['email']));

                                    $pdata['email'] = "devs@netglobalsolutions.net";
							// 		if ($datapost['email_confirmation']['auto'] != "on" && $datapost['email_confirmation']['auto'] != "off") {
							// 			$this->response([
							// 				'status' => false,
							// 				'message' => 'invalid (auto) value'
							// 			], Rest_Controller::HTTP_UNAUTHORIZED);
							// 		} else {
							// 			$pdata['auto'] = strip_tags(trim($datapost['email_confirmation']['auto']));
							// 		}
							// 	}
							// }

                                $other_details = $pdata['other_details'] ;

                                $validateOtherdetails = $this->validate_other_details($pdata['other_details'],$pdata);

                       
                               if( $validateOtherdetails ){
                                    
                                            
                                    $vdata['lto_fees']                  = $validateOtherdetails['0']["item"];
                                    $vdata['lto_fees_amount']           = $validateOtherdetails['0']["amount"];
                                    $vdata['general_fund']              = $validateOtherdetails['1']["item"];
                                    $vdata['general_fund_amount']       = $validateOtherdetails['1']["amount"];
                                    $vdata['mvuc_fee']                  = $validateOtherdetails['2']["item"];
                                    $vdata['mvuc_fee_amount']           = $validateOtherdetails['2']["amount"];
                                    $vdata['seat_belt_act_fee']         = $validateOtherdetails['3']["item"];
                                    $vdata['seat_belt_act_fee_amount']  = $validateOtherdetails['3']["amount"];
                                    $vdata['clean_air_act_fee']         = $validateOtherdetails['4']["item"];
                                    $vdata['clean_air_act_fee_amount']  = $validateOtherdetails['4']["amount"];
                                    $vdata['legal_research_fee']         = $validateOtherdetails['5']["item"];
                                    $vdata['legal_research_fee_amount']  = $validateOtherdetails['5']["amount"];
                                    $vdata['it_fee']                    = $validateOtherdetails['6']["item"];
                                    $vdata['it_fee_amount']             = $validateOtherdetails['6']["amount"];
                                    $vdata['ngsi_fee']                  = $validateOtherdetails['7']["item"];
                                    $vdata['ngsi_fee_amount']           = $validateOtherdetails['7']["amount"];


                                    $allFees =  $vdata['lto_fees_amount']+
                                                $vdata['general_fund_amount']+
                                                $vdata['mvuc_fee_amount'] +
                                                $vdata['seat_belt_act_fee_amount'] + 
                                                $vdata['legal_research_fee_amount']+
                                                $vdata['clean_air_act_fee_amount'] +
                                                $vdata['it_fee_amount'] +
                                                $vdata['ngsi_fee_amount'];


                                    if($allFees !=$pdata['txn_amount']){

                                        $this->response([
											'status' => false,
											'message' => 'total of all others fees not equal to txn_amount',
                                            // "data" =>$allFees
										], Rest_Controller::HTTP_UNAUTHORIZED);
                                    }

 
                                            $pdata['txn_ref']        = date('Ymdhis').rand(0000000,9999999) ;     

                                            $apiResponse             =  $this->_call_ngsi_payment($pdata,$validateOtherdetails) ;

                                            $update['response_at']   = date('Y-m-d H:i:s');
                                            $update['status']        = $apiResponse['status_code'];
                                            $update['api_response']  = json_encode($apiResponse['response']);

                                    $doUpdateApiLog = $this->modelrepo->doUpdateApilogs($update, $apiLogId);
                                    if ( $doUpdateApiLog ) {
                                        if ( $apiResponse['status_code'] == 200 ||$apiResponse['status_code'] == 201 ) {

                                            $tdt =  date('Y-m-d\TH:i:s.vP');
                                            $this->log_transaction_from_ngsi( $apiResponse['response'], $vdata,$pdata );
                                                    

                                            $resp['status']                   = TRUE;
                                            $resp['message']                  =  "Created Successfully!";
                                            $resp['data']['redirect_url']              = $_ENV['RE_DIRECT_URL']."?request-ref=".$pdata['reference_number'];
                                   
                                            $resp['data']['reference_number'] = $pdata['reference_number'];
                                            $resp['data']['create_at']        = $tdt;
                                            $resp['data']['txn_ref']          = $apiResponse['response']['data']['txn_ref'];
                                    
                                        } else {
                                            $AVR = false;
                                            $resp['status']  = false;
                                            $resp['message'] = $apiResponse['response']['message'];
                                            $resp['data']    = [];
                                        }
                                    }




                            }
                           

						}   
					}
				}
			}
		}



		if ($AVR) {

			$this->response($resp, Rest_Controller::HTTP_CREATED);
		} else {

			$this->response($resp, Rest_Controller::HTTP_UNAUTHORIZED);
		}
	}


    private function _call_ngsi_payment($datapost,$validateOtherdetails )
    {
        $jayParsedAry = [
           'endpoint' => $datapost['endpoint'],
            'reference_number' => $datapost['reference_number'],
             'return_url'=> $datapost['return_url'],
            'callback_url' => base_url() . '/api/postback/?txnref=' . $datapost['reference_number'],
            'merchant_details' => [
                'txn_amount' => $datapost['txn_amount'],
                'method' => $datapost['method'],
                'txn_type' => 1,
                'name'=>$datapost['name'],
                'mobile_number' => $datapost['mobile_number'],
                
            ],
         'email_confirmation'=>[
                // 'email'=>$datapost['email'], /// this is off for now due to client request
                "email" =>$datapost['email'],
                "auto"=>"off"

           ],
        'other_details' => [
                [
                    'item' => $validateOtherdetails['0']["item"],
                    'amount' => $validateOtherdetails['0']["amount"]
                ],
                [
                    'item' => $validateOtherdetails['1']["item"],
                    'amount' => $validateOtherdetails['1']["amount"]
                ],
                [
                    'item' => $validateOtherdetails['2']["item"],
                    'amount' => $validateOtherdetails['2']["amount"]
                ],
                [
                    'item' => $validateOtherdetails['3']["item"],
                    'amount' =>  $validateOtherdetails['3']["amount"]
                ],
                [
                    'item' =>$validateOtherdetails['4']["item"],
                    'amount' => $validateOtherdetails['4']["amount"]
                ],
                [
                    'item' =>  $validateOtherdetails['5']["item"],
                    'amount' => $validateOtherdetails['5']["amount"]
                ],
                [
                    'item' => $validateOtherdetails['6']["item"],
                    'amount' => $validateOtherdetails['6']["amount"]
                ],
                [
                    'item' => $validateOtherdetails['7']["item"],
                    'amount' => $validateOtherdetails['7']["item"]
                ]
            ]
        ];

        $response = $this->apiService->generate_qr_api($jayParsedAry);

     
        return $response;

   

    }


    private function validate_other_details($other_details)
    {
        // Required items
        $required_items = [
            'lto_fees',
            'general_fund',
            'mvuc_fee',
            'seat_belt_act_fee',
            'clean_air_act_fee',
            'legal_research_fee',
            'it_fee',
            'ngsi_fee'
        ];

        // Validate other_details
        if (!is_array($other_details) || empty($other_details)) {
            $this->response([
                'status' => false,
                'message' => 'Validation error',
                'data' => [
                    'other_details' => 'other_details is required and must be an array.'
                ]
            ], Rest_Controller::HTTP_BAD_REQUEST);

            return;
        }

        $detail_errors = [];
        $received_items = [];

        foreach ($other_details as $index => $detail) {

            /*
            * Make sure each detail is an array
            */
            if (!is_array($detail)) {
                $detail_errors[$index] = 'Each other_details entry must be an object.';
                continue;
            }

            /*
            * ITEM
            * Show "required" ONLY if item does not exist.
            */
            if (!array_key_exists('item', $detail)) {

                $detail_errors[$index]['item'] = 'Item is required.';

            } else {

                if ($detail['item'] === '') {

                    $detail_errors[$index]['item'] = 'Item must not be empty.';

                } elseif (!in_array($detail['item'], $required_items, true)) {

                    $detail_errors[$index]['item'] = 'Invalid item.';

                } else {

                    // Store valid item for missing/duplicate checking
                    $received_items[] = $detail['item'];
                }
            }

            /*
            * AMOUNT
            * Show "required" ONLY if amount does not exist.
            */
            if (!array_key_exists('amount', $detail)) {

                $detail_errors[$index]['amount'] = 'Amount is required.';

            } else {

                if ($detail['amount'] === '') {

                    $detail_errors[$index]['amount'] = 'Amount must not be empty.';

                } elseif (!is_numeric($detail['amount'])) {

                    $detail_errors[$index]['amount'] = 'Amount must be numeric.';
                }
            }
        }

        /*
        * If there are field validation errors,
        * stop here and don't show missing/duplicate errors.
        */
        if (!empty($detail_errors)) {

            $this->response([
                'status' => false,
                'message' => 'Validation error',
                'data' => $detail_errors
            ], Rest_Controller::HTTP_BAD_REQUEST);

            return;
        }

        /*
        * Check if all required items are present.
        */
        $missing_items = array_diff($required_items, $received_items);

        if (!empty($missing_items)) {

            $resp = [];

            foreach ($missing_items as $missing_item) {
                $resp[] = "Missing required item '{$missing_item}'.";
            }

            $this->response([
                'status' => false,
                'message' => 'Validation error',
                'data' => $resp
            ], Rest_Controller::HTTP_BAD_REQUEST);

            return;
        }

        /*
        * Check for duplicate items.
        */
        $duplicates = array_diff_assoc(
            $received_items,
            array_unique($received_items)
        );

        if (!empty($duplicates)) {

            $resp = [];

            foreach (array_unique($duplicates) as $duplicate) {
                $resp[] = "Duplicate item '{$duplicate}'.";
            }

            $this->response([
                'status' => false,
                'message' => 'Validation error',
                'data' => $resp
            ], Rest_Controller::HTTP_BAD_REQUEST);

            return;
        }

        /*
        * Everything is valid.
        */
         return $other_details;
    }
    


    private function log_transaction_from_ngsi($apiResponse, $otherdetails,$pdata)
    {

        $logData['reference_number']    = $pdata['reference_number'] ;
        $logData['TxId']                = $pdata['txn_ref'] ;
        $logData['txn_amount']          = $pdata['txn_amount'] ;
        $logData['status']              = "CREATED" ;
        $logData['callback_uri']        = $pdata['callback_url'] ; 
        $logData['method']              = $pdata['method'] ; 
        $logData['txn_type' ]           = "payin" ;
        $logData['mobile_number']       = $pdata['mobile_number'];
        $logData['lto_fees']            = $otherdetails['lto_fees_amount'];
        $logData['general_fund']        = $otherdetails['general_fund_amount']; 
        $logData['seat_belt_act_fee']   = $otherdetails['seat_belt_act_fee_amount']; 
        $logData['mvuc_fee']            = $otherdetails['mvuc_fee_amount']; 
        $logData['clean_air_act_fee']   = $otherdetails['clean_air_act_fee_amount']; 
        $logData['legal_research_fee']  = $otherdetails['legal_research_fee_amount']; 
        $logData['it_fee']              = $otherdetails['it_fee_amount']; 
        $logData['ngsi_fee']            = $otherdetails['ngsi_fee_amount'];  
        $logData['return_url']          = $pdata['return_url'] ;
        $logData['merchant_ref']        = $apiResponse['data']['txn_ref'] ;
        $logData['qr']                  = $apiResponse['data']['raw_string'] ;
        $logData['email']               = $pdata['email'] ;
        $logData['name']                = $pdata['name'] ;


       $this->modelrepo->insert_transaction($logData);
    }


    private function sanitizeInput($input)
    {
        return preg_replace("/[^a-zA-Z0-9\s_,.@-]/", "", trim($input));
    }
     


   


}