<?php
$servername = "localhost";
$username = "root";
$password = "root";
$db = "cjclive_db_new";

// Create connection
$conn = new mysqli($servername,$username,$password,$db);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}else{
	
	$pending_payment  = mysqli_query($conn,"SELECT * FROM `user_payments`
 WHERE `payment_mode` LIKE 'COIN'
AND `status` = 0" ); 
	$pending_payment = mysqli_fetch_all($pending_payment,MYSQLI_ASSOC);
if($pending_payment){
 foreach ($pending_payment as $key => $value) {

    $data1 =  coinpayments_api_call('get_tx_info',$value['payment_id']);
  
 if($data1){
       if ($data1['result']['status'] == 100){
        $id = $value['user_id'];
      $request  = mysqli_query($conn,"SELECT * FROM `user_payments`
WHERE `user_id` = ".$id." AND `status` =0 ORDER BY id desc LIMIT 0 , 1" );
$request=mysqli_fetch_assoc($request);
$user_payment =  mysqli_query($conn,"SELECT * FROM `user_payments`
WHERE `id` = ".$request['id']."" );
$user_payment=mysqli_fetch_assoc($request);
 $user =  mysqli_query($conn,"SELECT * FROM `users`
WHERE `id` = ".$id."" );
$user=mysqli_fetch_assoc($user);
$subscription =  mysqli_query($conn,"SELECT * FROM `subscriptions`
WHERE `id` = ".$user_payment['subscription_id']."" );
 echo Setting::get('referral_commission')/100;die;


       }


   }
 










 }











}
















}

 function coinpayments_api_call($cmd,$txid, $req = array()) {
    // Fill these in from your API Keys page

    $public_key = 'da4e739345ad9dd8421a82d1a1c321caef4d796c03e5f4207456d894c9a18329';
    $private_key = 'C15058fB1F4f349B4bcb437776ca05fa846008c222cf9a22e8707C9CD570d85d';
    
    // Set the API command and required fields
    $req['version'] = 1;
    $req['cmd'] = $cmd;
    $req['key'] = $public_key;
    $req['format'] = 'json'; //supported values are json and xml
    // $req['amount'] = '1'; //supported values are json and xml
    // $req['buyer_email'] = 'paypal366@gmail.com'; //supported values are json and xml
    // $req['currency1'] = 'USD'; //supported values are json and xml
    $req['txid'] = $txid; //supported values are json and xml
    
    // Generate the query string
    $post_data = http_build_query($req, '', '&');
    // $post_data = "cmd=create_transaction&amount=1&currency1=USD&currency2=BTC&buyer_email=paypal366@gmail.com&version=1&key=f5bf2fb054da50065a6b14caa15de923b5d775564d654d5def2a81f39150ca98";
$private_key = "C15058fB1F4f349B4bcb437776ca05fa846008c222cf9a22e8707C9CD570d85d";
    // Calculate the HMAC signature on the POST data
    $hmac = hash_hmac('sha512', $post_data, $private_key);
    
    // Create cURL handle and initialize (if needed)
    static $ch = NULL;
    if ($ch === NULL) {
        $ch = curl_init('https://www.coinpayments.net/api.php');
        curl_setopt($ch, CURLOPT_FAILONERROR, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('HMAC: '.$hmac));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    
    // Execute the call and close cURL handle     
    $data = curl_exec($ch);                
    // Parse and return data if successful.
    if ($data !== FALSE) {
        if (PHP_INT_SIZE < 8 && version_compare(PHP_VERSION, '5.4.0') >= 0) {
            // We are on 32-bit PHP, so use the bigint as string option. If you are using any API calls with Satoshis it is highly NOT recommended to use 32-bit PHP
            $dec = json_decode($data, TRUE, 512, JSON_BIGINT_AS_STRING);
        } else {
            $dec = json_decode($data, TRUE);
        }
        if ($dec !== NULL && count($dec)) {
            return $dec;
        } else {
            // If you are using PHP 5.5.0 or higher you can use json_last_error_msg() for a better error message
            return array('error' => 'Unable to parse JSON result ('.json_last_error().')');
        }
    } else {
        return array('error' => 'cURL error: '.curl_error($ch));
    }
}
?>