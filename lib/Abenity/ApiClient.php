<?php

namespace Abenity;

/**
 * Abenity API interface
 *
 * @package  Abenity
 * @license  http://creativecommons.org/licenses/MIT/ MIT
 */
class ApiClient
{

    private $version = 2;

    public $debug;

    public $timeout;

    private $api_username;

    private $api_password;

    private $api_key;

    private $api_url = 'https://api.abenity.com';

    private $public_key;

    private $triple_des_key;

    /**
     * ApiClient constructor
     *
     * @param String $api_username Your API Username
     * @param String $api_password Your API Password
     * @param String $api_key Your API Key
     * @param Integer $version The API version
     * @param String $environment Default is "live". Set to "Sandbox" to send requests to "sandbox.abenity.com"
     * @param Integer $timeout Set how long to wait for the API to respond
     *
     * @return null
     */
    public function __construct($api_username, $api_password, $api_key, $version = 2, $environment = 'live', $timeout = 10)
    {

        // Set API Credentials
        $this->api_username = $api_username;
        $this->api_password = $api_password;
        $this->api_key = $api_key;

        $this->public_key = 'ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAAAgQC8NVUZUtr2IHiFoY8s/qFGmZOIewAvg' .
            'S4FMXWZ81Qc8lkAlZr9e171xn4PgKr+S7YsfCt+1XKyo5XmrJyaNUe/aRptB93NFn6RoFzExgfpkooxcHpWcP' .
            'y+Hb5e0rwPDBA6zfyrYRj8uK/1HleFEr4v8u/HbnJmiFoNJ2hfZXn6Qw== phpseclib-generated-key';

        // Set API Version
        if (is_numeric($version)) {
            $this->version = $version;
        }

        // Set Environment
        if ($environment == 'sandbox') {
            $this->api_url = 'https://sandbox.abenity.com';
        }

        // Set API Timeout
        $this->timeout = 10;
        if (is_numeric($timeout)) {
            $this->timeout = $timeout;
        }

        // Define a code alphabet for generating strings
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

        // Create Triple DES Key
        $triple_des_size = mcrypt_get_key_size('tripledes', 'cbc');
        for ($i = 0; $i < $triple_des_size; $i++) {
            $this->triple_des_key .= $codeAlphabet[$this->cryptoRandSecure(0, strlen($codeAlphabet))];
        }

    }

    /**
     * Send a HTTP request to the API
     *
     * @param string $api_method The API method to be called
     * @param string $http_method The HTTP method to be used (GET, POST, PUT, DELETE, etc.)
     * @param array $data Any data to be sent to the API
     *
     * @return string A data-object of the response
     */
    private function sendRequest($api_method, $http_method = 'GET', $data = null)
    {

        // Set the request type and construct the POST request
        if(is_array($data)){
            $data = array_merge(
                (array) $data,
                array(
                    'api_username' => $this->api_username,
                    'api_password' => $this->api_password,
                    'api_key' => $this->api_key,
                )
            );
            $postdata = http_build_query($data);
        }else{
            $postdata = sprintf(
                "api_username=%s&api_password=%s&api_key=%s&%s",
                urlencode($this->api_username),
                urlencode($this->api_password),
                urlencode($this->api_key),
                $data
            );
        }

        // Debugging output
        $this->debug = array();
        $this->debug['HTTP Method'] = $http_method;
        $this->debug['Request URL'] = $this->api_url.$api_method;

        // Create a cURL handle
        $ch = curl_init();

        // Set the request
        curl_setopt($ch, CURLOPT_URL, $this->api_url . '/v' . $this->version . '/client' . $api_method);

        // Do not ouput the HTTP header
        curl_setopt($ch, CURLOPT_HEADER, false);

        // Save the response to a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Send data as PUT request
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $http_method);

        // This may be necessary, depending on your server's configuration
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        // Set the maximum number of seconds to allow cURL functions to execute
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);

        // Set the user agent
        curl_setopt($ch, CURLOPT_USERAGENT, 'abenity/abenity-php v2');

        // Send data
        if (!empty($postdata)) {

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Length: '.strlen($postdata)));
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);

            // Debugging output
            $this->debug['Post Data'] = $postdata;

        }

        // Execute cURL request
        $curl_response = curl_exec($ch);

        // Save CURL debugging info
        $this->debug['Curl Info'] = curl_getinfo($ch);

        // Close cURL handle
        curl_close($ch);

        // Parse JSON response
        $parsed_response = $this->parseResponse($curl_response, 'json');

        // Return parsed response
        return $parsed_response;
    }

    /**
     * Parse the API response
     *
     * @param string $response The raw API response
     * @param string $format The format to parse. ['json'|'xml']
     *
     * @return string The parsed response
     */
    private function parseResponse($response, $format = 'json')
    {

        $result = null;

        if ($format == 'json') {
            $result = json_decode($response);
        }

        elseif ($format == 'xml') {
            $result = simplexml_load_string($response);
        }

        return $result;
    }

    /**
     * Create a random number. This is a more secure "rand" function
     * Taken from: http://us1.php.net/manual/en/function.openssl-random-pseudo-bytes.php#104322
     *
     * @param integer $min The random number lower bound
     * @param integer $max The random number upper bound
     *
     * @return integer A random integer between the input bounds
     */
    private function cryptoRandSecure($min, $max)
    {

        $range = $max - $min;

        if ($range < 0) {
            return $min;
        }

        $log = log($range, 2);

        // length in bytes
        $bytes = (int) ($log / 8) + 1;

        // length in bites
        $bits = (int) $log + 1;

        // set all lower bits to 1
        $filter = (int) (1 << $bits) - 1;

        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            // discard irrelevant bits
            $rnd = $rnd & $filter;
        } while ($rnd >= $range);

        return $min + $rnd;
    }

    /**
     * Symmetrically encrypt a string of information
     *
     * @param string $payload_string An input string
     * @param string $iv An initialization vector for Triple-DES in CBC mode
     *
     * @return string A base64-encoded and url-encoded representation of the $payload_string
     */
    private function encryptPayload($payload_string, $iv)
    {

        $payload_urlencoded = '';

        $payload_binary = mcrypt_encrypt(MCRYPT_3DES, $this->triple_des_key, $payload_string, MCRYPT_MODE_CBC, $iv);
        $payload_base64 = base64_encode($payload_binary);
        $payload_urlencoded =  urlencode($payload_base64) . "decode";

        return $payload_urlencoded;
    }

    /**
     * Asymmetrically encrypt a symmetrical encryption key
     *
     * @param string $triple_des_key A Triple DES (3DES) encryption key
     *
     * @return string A base64-encoded and url-encoded representation of the $triple_des_key
     */
    private function encryptCipher($triple_des_key)
    {

        $triple_des_key_urlencoded = '';

        $rsa = new \phpseclib\Crypt\RSA;
        $rsa->setEncryptionMode(CRYPT_RSA_ENCRYPTION_PKCS1);
        $rsa->loadKey($this->public_key);
        $triple_des_key_binary = $rsa->encrypt($triple_des_key);
        $triple_des_key_base64 = base64_encode($triple_des_key_binary);
        $triple_des_key_urlencoded = urlencode($triple_des_key_base64) . "decode";

        return $triple_des_key_urlencoded;
    }

    /**
     * Sign a message using a private RSA key
     *
     * @param string $payload The message to be signed
     * @param string $private_key An RSA private key
     *
     * @return string A base64-encoded and url-encoded hash of the $payload_string
     */
    private function signMessage($payload, $private_key)
    {

        $signature_urlencoded = '';

        $rsa_signature = new \phpseclib\Crypt\RSA;
        $rsa_signature->loadKey($private_key);
        $rsa_signature->setSignatureMode(CRYPT_RSA_SIGNATURE_PKCS1);
        $rsa_signature->setHash('md5');

        $payload_base64 = urldecode( substr($payload, 0, -6) );

        $signature_binary = $rsa_signature->sign($payload_base64);
        $signature_base64 = base64_encode($signature_binary);
        $signature_urlencoded = urlencode($signature_base64) . "decode";

        return $signature_urlencoded;
    }

    /**
     * Single Sign-On a member
     *
     * @param array $member_profile An array of key/value pairs that describes the member
     * @param array $private_key Your RSA private key, used to sign your message
     *
     * @return string The raw API response
     */
    public function ssoMember($member_profile, $private_key)
    {

        // Convert member profile array to a HTTP query string

        $payload_string = http_build_query($member_profile);

        // Create Initialization Vector for symmetric encryption
        $iv_size = mcrypt_get_iv_size(MCRYPT_3DES, MCRYPT_MODE_CBC);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $iv_urlencoded = urlencode(base64_encode($iv)) . "decode";

        $Payload = $this->encryptPayload($payload_string, $iv);
        $Cipher = $this->encryptCipher($this->triple_des_key);
        $Signature = $this->signMessage($Payload, $private_key);

        $data = sprintf(
            "Payload=%s&Cipher=%s&Signature=%s&Iv=%s",
            $Payload,
            $Cipher,
            $Signature,
            $iv_urlencoded
        );

        return $this->sendRequest('/sso_member.json', 'POST', $data);
    }

    /**
     * Deactivate a Member
     *
     * @param string $client_user_id The unique Client User ID for the member
     * @param boolean $send_notification Set to true to send a notification email
     *
     * @return string The raw API response
     */
    public function deactivateMember($client_user_id, $send_notification = false)
    {
        $data = array(
            'client_user_id' => $client_user_id,
            'send_notification' => $send_notification
        );
        return $this->sendRequest('/deactivate_member.json', 'POST', $data);
    }

    /**
     * Reactivate a Member
     *
     * @param string $client_user_id The unique Client User ID for the member
     * @param boolean $send_notification Set to true to send a notification email
     *
     * @return string The raw API response
     */
    public function reactivateMember($client_user_id, $send_notification = false)
    {
        $data = array(
            'client_user_id' => $client_user_id,
            'send_notification' => $send_notification
        );
        return $this->sendRequest('/reactivate_member.json', 'POST', $data);
    }
}
