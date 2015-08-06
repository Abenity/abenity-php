<?php

// Include autoloader (from Composer)
require 'vendor/autoload.php';

// Define Abenity API Credentials. Replace these with your values.
define('ABENITY_API_USERNAME', 'Acme');
define('ABENITY_API_PASSWORD', 'a3d2de');
define('ABENITY_API_KEY', 'dlk1o89wc7emcyd7yqphja60i7x5jkx');

// Create new Abenity object
$abenity = new \Abenity\ApiClient(ABENITY_API_USERNAME, ABENITY_API_PASSWORD, ABENITY_API_KEY);

// Define your private key, probably read in from a file.
$privatekey = '-----BEGIN RSA PRIVATE KEY-----
...
-----END RSA PRIVATE KEY-----';

// Set member profile
$member = array(
    'creation_time' => date('c'),
    'salt' => rand(0,100000),
    'send_welcome_email' => 1,
    'client_user_id' => '1',
    'email' => 'john@acme.com',
    'firstname' => 'John',
    'lastname' => 'Smith',
    'address' => '2134 Main Street',
    'city' => 'Irvine',
    'state' => 'CA',
    'zip' => '92620',
    'country' => 'US'
);

// Attempt to register member
$abenity_response = $Abenity->ssoMember($privatekey, $member);

// Test verifiction
if( $abenity_response->status == 'ok' ){

    // Save Member's Abenity username/password for future use
    // ...
    echo "Pass\n";

}else{

    // Handle error
    // ...
    echo "Fail: " . $abenity_response->error . "\n";

}

