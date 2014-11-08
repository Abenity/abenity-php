<?php

// Include autoloader (from Composer)
require 'vendor/autoload.php';

// Define Abenity API Credentials. Replace these with your values.
define('ABENITY_API_USERNAME', 'Acme');
define('ABENITY_API_PASSWORD', 'a3d2de');
define('ABENITY_API_KEY', 'dlk1o89wc7emcyd7yqphja60i7x5jkx');

// Create new Abenity object
$abenity = new \Abenity\ApiClient(ABENITY_API_USERNAME, ABENITY_API_PASSWORD, ABENITY_API_KEY);

// Attempt to register member
$abenity_response = $abenity->authenticateMember('username', 'password');

// Test verifiction
if( $abenity_response->status == 'ok' ){

    // Compose an HTML link
    $HTML_link = '<a href="'.$abenity_response->login_URL.'?encrypted_username='.$abenity_response->encrypted_username.'&encrypted_password='.$abenity_response->encrypted_password.'">Visit your Savings Program</a>';

    // Redirect browser
    // header('Location: '.$abenity_response->login_URL.'?encrypted_username='.$abenity_response->encrypted_username.'&encrypted_password='.$abenity_response->encrypted_password);
    // exit;

}else{

    // Handle error
    if (isset($abenity_response->error)) {
        foreach($abenity_response->error as $key => $val){
            echo $key . ': ' . $val . "\n";
        }
    }

}
