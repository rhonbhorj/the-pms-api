<?php
defined( 'BASEPATH' ) or exit( 'No direct script access allowed' );

class ApiService
 {
    protected $CI;
    protected $secret_key;
    protected $endpoint_base_url;

    public function __construct()
 {
        $this->CI = &get_instance();
        $this->CI->load->database();


        $this->endpoint_base_url = $_ENV[ 'ENDPOINT_BASE_URL' ];
    }



  public function generate_token()
  {
    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => $this->endpoint_base_url.'/generate-token',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'GET',
      CURLOPT_HTTPHEADER => array(
                    'X-API-KEY: '.$_ENV['X_API_KEY'],
                    'X-API-USERNAME: '.$_ENV['X_API_USERNAME'],
                    'X-API-PASSWORD: '.$_ENV['X_API_PASSWORD']
      ),
    ));

    $response = curl_exec( $curl );
    $http_status_code = curl_getinfo( $curl, CURLINFO_HTTP_CODE );
    curl_close( $curl );

    $jdata = 	json_decode( $response, true );
    $resp[ 'response' ] =  $jdata;
    $resp[ 'status_code' ] = $http_status_code;

    return $resp;

      }

    public function call_external_api( $v )
 {
        $generated_token = $this->generate_token();

        $endpoint = $this->endpoint_base_url . '/v1/cashin';
        $curl = curl_init();

        curl_setopt_array( $curl, array(
            CURLOPT_URL =>  $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>json_encode( $v ),
            CURLOPT_HTTPHEADER => array(
                'X-API-KEY: '.$_ENV['X_API_KEY'],
                'X-API-USERNAME: '.$_ENV['X_API_USERNAME'],
                'X-API-PASSWORD: '.$_ENV['X_API_PASSWORD'],
                'Content-Type: application/json',
                'Authorization: Bearer '.$generated_token['response'][ 'data' ][ 'token' ]
            ),
        ) );

        $response = curl_exec( $curl );

        curl_close( $curl );

        // expecting to be a json encoded response
        $resp[ 'response' ] =  json_decode( $response, true );
        $resp[ 'status_code' ] = $generated_token;

        return $resp;
    
}

    public function generate_qr_api( $data )
 {
     
    // pgw/api/v1/transactions/qr-codes/generate/
        $endpoint = $this->endpoint_base_url . '/v1/cashin';
        $generated_token = $this->generate_token();
        $dataToSend = $data;
        if($generated_token['status_code']==200 ||$generated_token['status_code']==201){
            $ch = curl_init();
            curl_setopt( $ch, CURLOPT_URL, $endpoint );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $ch, CURLOPT_HTTPHEADER, [
                'X-API-KEY: '.$_ENV['X_API_KEY'],
                'X-API-USERNAME: '.$_ENV['X_API_USERNAME'],
                'X-API-PASSWORD: '.$_ENV['X_API_PASSWORD'],
                'Authorization: Bearer '. $generated_token['response'][ 'data' ][ 'token' ],
                'Content-Type: application/json'
            ] );
            curl_setopt( $ch, CURLOPT_POST, true );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $dataToSend, JSON_PRESERVE_ZERO_FRACTION ) );
    
            $response = curl_exec( $ch );
            $http_status_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
    
            curl_close( $ch );
    
            // expecting to be a json encoded response
            $resp[ 'response' ] =  json_decode( $response, true );
          
            $resp[ 'status_code' ] = $http_status_code;


        }else{

          
            $resp[ 'response' ]=$generated_token;
            $resp[ 'status_code' ] = $generated_token['status_code'];

        }
      

        return $resp;

    }


        public function call_back($data, $endpoint)
    {

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => $endpoint,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>json_encode($data),
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json'
          ),
        ));
        
        $response = curl_exec($curl);
        $http_status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
       
       
        $resp['response'] =$response;
        $resp['status_code'] = $http_status_code;

        return $resp;
    }
}