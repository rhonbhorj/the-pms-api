<?php



    function generate_qr_api( $data )
    {
     
    // pgw/api/v1/transactions/qr-codes/generate/
        $endpoint = $_ENV['ENDPOINT_BASE_URL']. '/v1/cashin';
        $generated_token = generate_token();
        $dataToSend = $data;
        if( $generated_token['status_code'] == 200 || $generated_token['status_code'] == 201 ){

          $resp = external_api( $generated_token,$dataToSend, $endpoint );
        }else{
  
            $resp[ 'response' ]=null;
            $resp[ 'status_code' ] = $generated_token['status_code'];

        }
      
        return $resp;

    }

    function cancel_qr_api( $dataToSend )
    {
     
   
        $endpoint = $_ENV['ENDPOINT_BASE_URL']. '/cashin-cancel';
        $generated_token = generate_token();
     
        if( $generated_token['status_code'] == 200 || $generated_token['status_code'] == 201 ){

          $resp = external_api( $generated_token,$dataToSend, $endpoint );
        }else{
  
            $resp[ 'response' ]=null;
            $resp[ 'status_code' ] = $generated_token['status_code'];

        }
      
        return $resp;

    }


    function external_api( $generated_token,$dataToSend,$endpoint )
    {

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

        $resp[ 'response' ] =  json_decode( $response, true );
    
        $resp[ 'status_code' ] = $http_status_code;

        return  $resp;

    }


 function generate_token()
 {
    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => $_ENV['ENDPOINT_BASE_URL'].'/generate-token',
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

    return  $resp;

    }