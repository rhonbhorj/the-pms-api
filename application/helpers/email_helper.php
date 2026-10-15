<?php if ( ! defined( 'BASEPATH' ) ) exit( 'No direct script access allowed' );




 function generate_token()
{
       $curl = curl_init();
      
       curl_setopt_array( $curl, array(
           CURLOPT_URL =>   $_ENV[ 'EMAIL_BASE_URL' ] . '/generate-token',
           CURLOPT_RETURNTRANSFER => true,
           CURLOPT_ENCODING => '',
           CURLOPT_MAXREDIRS => 10,
           CURLOPT_TIMEOUT => 0,
           CURLOPT_FOLLOWLOCATION => true,
           CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
           CURLOPT_CUSTOMREQUEST => 'POST',
           CURLOPT_POSTFIELDS =>'{
         "username": "'.  $_ENV[ 'EMAIL_API_USER_NAME' ].'",
         "secret": "'.  $_ENV[ 'EMAIL_API_SECRET' ].'",
         "app_uuid": "'.  $_ENV[ 'EMAIL_API_UUID' ].'"
       }',
           CURLOPT_HTTPHEADER => array(
               'Content-Type: application/json'

           ),
       ) );
     

  


       $response = curl_exec( $curl );
       $http_status_code = curl_getinfo( $curl, CURLINFO_HTTP_CODE );
       curl_close( $curl );

       $jdata = 	json_decode( $response, true );
       // // $jdata['httpstatus']=$http_status_code;
       // return $jdata[ 'data' ][ 'token' ];

       $resp[ 'response' ] =  $jdata;
       $resp[ 'status_code' ] = $http_status_code;

return $resp;

   }


if ( ! function_exists( 'sendemail' ) ) 
 {

    function sendemail( $request )
 {


    $generated_token = generate_token();
  

      $email=$request['email'];
      
     

    
        // $email_messege = [
        //     'app_uuid' => $_ENV[ 'API_UUID' ],
        //     'reference_number' => $request["subject"] ,
        //     'receivers' => [
        //       $email
        //     ],
        //     'subject' => 'Payment Confirmation - Ref: ' . $request["subject"] ,
        //     'body' => $request[ 'message' ]
        // ];
        

    
       
       

  $email_message = [
            'app_uuid' => $_ENV[ 'EMAIL_API_UUID' ],
            'reference_number' => $request["subject"] ."-".rand(0000,9999) ,
            'receivers' => 
              $request['email'],
            'subject' =>   $request["subject"] ,
            'body' => $request[ 'message' ]
          ];
        
     $jdata=   json_encode($email_message);
        

        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL =>  $_ENV[ 'EMAIL_BASE_URL' ].'/api/mailer',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>$jdata,
          CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Bearer '.$generated_token['response'][ 'data' ][ 'token' ]
          ),
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);
        // echo $response;

// echo json_encode($request[ 'subject' ]);

    }



     function send_to_email($request,$company)
     {

      $data['email'] = $request['email'];

      $data['subject'] = "WBS OTP";
 

      $data['name'] = 'WBS';


              // $data['message'] = 'Your OTP : '.$request['otp'];

     $data['message']="<body style='margin:0; padding:10px; background-color:#eef3f7; font-family: 'Segoe UI', 'Inter', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing:antialiased; font-size:16px; line-height:1.5;'>
    <center style='width:100%; table-layout:fixed; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%;'>
        <div style='max-width:520px; margin:24px auto; background-color:#ffffff; border-radius:16px; box-shadow:0 8px 20px rgba(0,20,50,0.08); overflow:hidden;border:1px solid #8585cc;'>

            <!-- header with drop water logo image -->
            <div style='padding:32px 30px 12px 30px; text-align:center; background-color:#ffffff;'>
                <table width='100%' cellpadding='0' cellspacing='0' border='0' style='margin:0 auto;'>
                    <tr>
                        <td align='center' style='padding:0;'>
                            <img src='https://www.clipartmax.com/png/full/426-4264078_free-icon-download-liquid-search-engine-%E2%93%92-purple-water-drop-icon.png'alt='Water drop logo'style='display:block; width:80px; height:80px; margin:0 auto 8px auto; filter: drop-shadow(0 6px 10px rgba(0,120,255,0.4)); border-radius: 0; background: transparent;'><div style='font-weight:800; font-size:1.5rem; color:#003779; letter-spacing:-0.5px; line-height:1.2;'>WATER DEMO</div>
                        </td>
                    </tr>
                </table>
                <table width='100%' cellpadding='0' cellspacing='0' border='0' style='margin:1rem 0 0.2rem 0;'>
                    <tr>
                        <td height='2' bgcolor='#004b8f' style='background: linear-gradient(90deg, transparent, #004b8f, #7cb3d9, #004b8f, transparent); height:2px; font-size:0; line-height:0;'>&nbsp;</td>
                    </tr>
                </table>
            </div>

          
            <div style='padding:10px 30px 20px 30px; background-color:#ffffff;'>
                <p style='margin:0 0 12px 0; color:#1a344d; font-weight:500; font-size:18px;'>OTP To Verify Your Identity</p>

                <p style='margin:0 0 12px 0; color:#2a405c; font-size:16px;'>
                    You are required to enter the following code to sign 
                </p>
                <p style='margin:0 0 10px 0; color:#4a6b8a; font-size:15px;'>
                    ⏱️ <strong style='color:#b85c00;'>Please enter the code in 10 minutes.</strong>
                </p>

                <div style='background:#f4f9ff; border-radius:28px; padding:8px 20px; margin:28px 0 24px 0; text-align:center; border:1px solid #cde1f5;'>
                    <p style='margin:8px 0; color:#0c3b6b; font-size:14px; font-weight:500; letter-spacing:1px; text-transform:uppercase;'>Your verification code</p>
                    <p style='margin:0 0 10px 0; font-size:34px; font-weight:800; color:#0a3a66; letter-spacing:8px; word-break:break-all;'>".$request['otp']."</p>
                </div>

                <p style='margin:20px 0 16px 0; color:#5e7b9c; font-size:14px; border-left:3px solid #b3d0f0; padding-left:16px; background:#fafdff; padding:12px 16px; border-radius:12px;'>
                    <span style='font-weight:600;'>If this wasn't you,</span> please ignore this email<br>
                </p>
            </div>

            <!-- footer -->
            <div style='background-color:#f2f7fd; padding:24px 30px 28px 30px; text-align:center; border-top:1px solid #d1e2f2;'>
                <p style='margin:20px 0 0 0; font-size:11px; color:#8ba3c2;'>This is a transactional email, verification code required.</p>
            </div>
        </div>

        <p style='max-width:520px; margin:8px auto 24px auto; color:#7b98b5; font-size:12px;'>If you're having trouble, copy the code ".$request['otp']." into the signing page.</p>
    </center>
</body>";     

sendemail( $data );
     }




}