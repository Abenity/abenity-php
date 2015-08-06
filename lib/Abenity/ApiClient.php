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

    private $version;

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
     * @param String $api_username Your API Username
     * @param String $api_password Your API Password
     * @param String $api_key Your API Key
     * @param Integer $version The API version
     * @param Integer $timeout Set how long to wait for the API to respond
     * @return null
     */
    public function __construct($api_username, $api_password, $api_key, $version = 1, $timeout = 10)
    {

        // Set API Credentials
        $this->api_username = $api_username;
        $this->api_password = $api_password;
        $this->api_key = $api_key;

        $this->public_key = 'ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAAAgQC8NVUZUtr2IHiFoY8s/qFGmZOIewAvg' .
            'S4FMXWZ81Qc8lkAlZr9e171xn4PgKr+S7YsfCt+1XKyo5XmrJyaNUe/aRptB93NFn6RoFzExgfpkooxcHpWcP' .
            'y+Hb5e0rwPDBA6zfyrYRj8uK/1HleFEr4v8u/HbnJmiFoNJ2hfZXn6Qw== phpseclib-generated-key';

        // Set API Version
        $this->version = 1;
        if (is_numeric($version) && $version > 0 && $version < 2) {
            $this->version = $version;
        }

        // Set API Timeout
        $this->timeout = 10;
        if (is_numeric($version)) {
            $this->timeout = $timeout;
        }

        // Create new Crypt_RSA object
        $this->rsa = new \Crypt_RSA;

        // Define a code alphabet for generating strings
        $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

        // Create Triple DES Key
        for ($i = 0; $i < 24; $i++) {
            $this->triple_des_key .= $codeAlphabet[$this->cryptoRandSecure(0, strlen($codeAlphabet))];
        }

    }

    /**
     * Send a HTTP request to the API
     *
     * @param string $api_method The API method to be called
     * @param string $http_method The HTTP method to be used (GET, POST, PUT, DELETE, etc.)
     * @param array $data Any data to be sent to the API
     * @return string A data-object of the response
     */
    private function sendRequest($api_method, $http_method = 'GET', $data = null)
    {

        // Set the request type and construct the POST request
        $data = array_merge(
            (array) $data,
            array(
                'api_username' => $this->api_username,
                'api_password' => $this->api_password,
                'api_key' => $this->api_key,
            )
        );
        $postdata = http_build_query($data);

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
     * @param string $response The raw API response
     * @param string $format The format to parse. ['json'|'xml']
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
     * @return string A base64-encoded and url-encoded representation of the $payload_string
     */
    private function encryptPayload($payload_string, $iv)
    {

        $response = '';

        $nameValuePairBinary = mcrypt_encrypt(MCRYPT_3DES, $this->triple_des_key, $payload_string, MCRYPT_MODE_CBC, $iv);
        $nameValuePairText = base64_encode($nameValuePairBinary);
        $response =  urlencode($nameValuePairText) . "decode";

        return $response;
    }

    /**
     * Asymmetrically encrypt a symmetrical encryption key
     *
     * @param string $triple_des_key A Triple DES (3DES) encryption key
     * @return string A base64-encoded and url-encoded representation of the $triple_des_key
     */
    private function encryptCipher($triple_des_key)
    {

        $response = '';

        $this->rsa->loadKey($this->public_key);
        $encryptedSymmetricKeyBinary = $this->rsa->encrypt($triple_des_key);
        $encryptedSymmetricKeyText = base64_encode($encryptedSymmetricKeyBinary);
        $response = urlencode($encryptedSymmetricKeyText) . "decode";

        return $response;
    }

    /**
     * Sign a message using a private RSA key
     *
     * @param string $payload_string The message to be signed
     * @param string $private_key An RSA private key
     * @return string A base64-encoded and url-encoded hash of the $payload_string
     */
    private function signMessage($payload_string, $private_key)
    {

        $response = '';

        $nameValuePairText = urldecode(substr($payload_string, 0, -6));
        $messageDigest = md5($nameValuePairText);
        $this->rsa->loadKey($private_key);
        $signedMessageBinary = $this->rsa->encrypt($messageDigest);
        $signedMessageText = base64_encode($signedMessageBinary);
        $response = urlencode($signedMessageText) . "decode";

        return $response;
    }

    /**
     * Single Sign-On a member
     *
     * @return string The raw API response
     * @param array $member_profile An array of key/value pairs that describes the member
     * @param array $private_key Your RSA private key, used to sign your message
     * @return string The raw API response
     */
    public function ssoMember($member_profile, $private_key)
    {

        // Convert member profile array to a HTTP query string
        $payload_string = http_build_query($member_profile);

        // Create Initialization Vector for symmetric encryption
        $iv_size = mcrypt_get_iv_size(MCRYPT_3DES, MCRYPT_MODE_CBC);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

        $Payload = $this->encryptPayload($payload_string, $iv);
        $Cipher = $this->encryptCipher($this->triple_des_key);
        $Signature = $this->signMessage($Payload, $private_key);

        $data = array(
            'Payload' => $Payload,
            'Cipher' => $Cipher,
            'Signature' => $Signature,
            'Iv' => urlencode(base64_encode($iv)) . "decode"
        );

        return $this->sendRequest('/sso_member.json', 'POST', $data);
    }

    /**
     * Register a Member
     *
     * @param array $member_profile An array of key/value pairs that describes the member
     * @return string The raw API response
     */
    public function registerMember($member_profile)
    {
        return $this->sendRequest('/register_member.json', 'POST', $member_profile);
    }

    /**
     * Authenticate a Username/Password as an active member. If valid,
     * the response will contain a link to log the member into the Abenity program
     *
     * @param string $username The user's unique Abenity Username
     * @param string $password The user's Abenity password
     * @return string The raw API response
     */
    public function authenticateMember($username, $password)
    {
        $data = array(
            'username' => $username,
            'password' => $password
        );
        return $this->sendRequest('/encrypt_login.json', 'POST', $data);
    }
}
