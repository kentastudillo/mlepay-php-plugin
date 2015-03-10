<?php

/*
Plugin Name: mlepay
Plugin URI: http://www.mlepay.com
Description: MLePay payment gateway plugin.
Author: Made with love by Kent of Symph.
Version: 1.1
Author URI: http://www.sym.ph
*/

class MLePay {
    private $epay_email = ""; // Your registered epay email.
    private $secret_key = ''; // Your ML ePay Secret Key
    private $epay_url = "https://www.mlepay.com/api/v2/transaction/create"; // Do not change this.

    /**
      * Create a transaction
      * receiver_email = Your registered account email in ML ePay.
      * sender_email = The email address of your customer.
      * sender_name = The complete name of your customer.
      * sender_phone = The phone number of your customer.
      * sender_address = The address of your customer.
      * amount - Should be in cents.
      * nonce = A random string, used for security measure.
      * timestamp - UNIX timestamp of current date and time.
      * currency - Currently, only PHP is supported.
      * expiry - UNIX timestamp, must be greater than the current date and time. MM-DD-YY HH-MM-SS
      * payload = Custom transaction details.
      * description = The description of the transaction.
    **/
    public function create_transaction($transaction){
        $nonce = $this->randString(16);
        $timestamp = time();
        $expiry = $this->due_time($transaction["expiry"]);

        $request_body = array(
            "receiver_email"=> $this->epay_email,
            "sender_email"=> $transaction["customer_email"],
            "sender_name"=> $transaction["customer_name"],
            "sender_phone"=> $transaction["customer_phone"],
            "sender_address"=> $transaction["customer_address"],
            "amount"=> (int)($transaction["amount"]), // Amount in CENTS
            "currency"=> "PHP",
            "nonce"=> $nonce,
            "timestamp"=> $timestamp,
            "expiry"=> $expiry,
            "payload"=> $transaction["payload"],
            "description"=> $transaction["product_description"]
        );
        $data_string = json_encode($request_body);
        $base_string = "POST";
        $base_string .= "&" . "https%3A//www.mlepay.com/api/v2/transaction/create";
        $base_string .= "&" . rawurlencode($data_string);
        $secret_key = $this->secret_key;

        $signature = base64_encode(hash_hmac("sha256", $base_string, $secret_key, true));
        $ch = curl_init($this->epay_url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'X-Signature: ' . $signature)
        );
        $result = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $result = json_decode($result, true);
        return $result;
    }

    /**
      * Generate random string for nonce.
    **/
    function randString($length) {
        $charset = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $str = '';
        $count = strlen($charset);

        while ($length--) {
            $str .= $charset[mt_rand(0, $count-1)];
        }

        return $str;

    }

    /**
      * Generate expiry time.
    **/
    function due_time($expiry){
        date_default_timezone_set("UTC");
        $expiry = new DateTime($expiry);
        $expiry = strtotime($expiry);

        return $expiry;
    }
}
?>