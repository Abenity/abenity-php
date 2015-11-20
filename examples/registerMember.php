<?php

/*
    Implementation Notes:

    1) Change the API credentials
    2) Define your $member
    3) Run script from Composer directory for autoloading to work
        [composer]# php vendor/abenity/abenity-php/examples/ssoMember.php
*/

// Include autoloader (from Composer)
require __DIR__ . '/../../../../vendor/autoload.php';

// Define Abenity API Credentials. Replace these with your values.
define('ABENITY_API_USERNAME', 'Acme');
define('ABENITY_API_PASSWORD', 'a3d2de');
define('ABENITY_API_KEY', 'dlk1o89wc7emcyd7yqphja60i7x5jkx');

// Create new Abenity object
$abenity = new \Abenity\ApiClient(ABENITY_API_USERNAME, ABENITY_API_PASSWORD, ABENITY_API_KEY);

// Set member profile
$member = array(
    'firstname' => 'John',
    'lastname' => 'Smith',
    'address' => '2134 Main Street',
    'city' => 'Irvine',
    'state' => 'CA',
    'zip' => '92620',
    'country' => 'US',
    'phone' => '(949) 234-0987',
    'position' => 'secretary',
    'email' => 'john@acme.com',
    'username' => 'jsmith',
    'password' => 'abc123',
    'spotlight' => 1,
    'offer_radius' => 20,
    'send_welcome_email' => 1
);


// Attempt to register member
$abenity_response = $abenity->registerMember($member);

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
