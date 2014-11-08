<?php
/**
 * ApiClient
 *
 * PHP version 5
 *
 * @package  Abenity
 * @license  http://creativecommons.org/licenses/MIT/ MIT
 */

/**
 * Abenity API interface
 *
 * @package  Abenity
 * @license  http://creativecommons.org/licenses/MIT/ MIT
 */

namespace Abenity;

class ApiClient
{

    const API_VERSION = 'v1';

    const DEFAULT_TIMEOUT = 2;

    public $debug;

    public $timeout;

    private $api_username;

    private $api_password;

    private $api_key;

    private $api_url = 'https://api.abenity.com';

    /**
     * ApiClient constructor
     */
    public function __construct($api_username, $api_password, $api_key, $version = self::DEFAULT_TIMEOUT, $timeout = self::DEFAULT_TIMEOUT)
    {

        // Set API Credentials
        $this->api_username = $api_username;
        $this->api_password = $api_password;
        $this->api_key = $api_key;

        // Set API Timeout
        $this->timeout = $timeout;
    }

    /**
     * Send a HTTP request to the API
     *
     * @param string $api_method The API method to be called
     * @param string $http_method The HTTP method to be used (GET, POST, PUT, DELETE, etc.)
     * @param array $data Any data to be sent to the API
     * @return string An XML-formatted response
     **/
    private function sendRequest($api_method, $http_method = 'GET', $data = null)
    {

        // Set the request type and construct the POST request
        $data = array_merge( (array) $data,
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
        curl_setopt($ch, CURLOPT_URL, $this->api_url.$api_method);

        // Do not ouput the HTTP header
        curl_setopt($ch, CURLOPT_HEADER, false);

        // Save the response to a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Send data as PUT request
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $http_method);

        // This may be necessary, depending on your server's configuration
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

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

        // Parse XML response
        $parsed_response = $this->parseResponse($curl_response, 'xml');

        // Return parsed response
        return $parsed_response;
    }

    /**
     *
     **/
    private function parseResponse($response, $format = 'xml')
    {

        $result = null;

        if($format == 'xml'){
            $result = simplexml_load_string($response);
        }

        return $result;
    }

    /**
     *
     */
    public function registerMember($member_profile)
    {
        return $this->sendRequest('/client/register_member.xml', 'POST', $member_profile);
    }

    /**
     *
     */
    public function authenticateMember($username, $password)
    {
        $data = array(
            'username' => $username,
            'password' => $password
        );
        return $this->sendRequest('/client/register_member.xml', 'POST', $data);
    }
}
