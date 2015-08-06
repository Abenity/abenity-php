<?php

class ApiClientTest extends \PHPUnit_Framework_TestCase
{
    private $client;
    private $invalid_token;
    private $valid_token;

    public function setUp()
    {
        // Constants defined in phpunit.xml.dist
        $this->client = new \Abenity\ApiClient(ABENITY_API_USERNAME, ABENITY_API_PASSWORD, ABENITY_API_KEY);
    }

    protected function tearDown()
    {
        unset($this->client);
    }

    public function testSsoMember()
    {
        $response = $this->client->ssoMember(array(), '');
        $this->assertEquals('fail', $response->status);
    }

    public function testRegisterMember()
    {
        $response = $this->client->registerMember(array());
        $this->assertEquals('fail', $response->status);
    }

    public function testAuthenticateMember()
    {
        $response = $this->client->authenticateMember('username', 'password');
        $this->assertEquals('fail', $response->status);
    }
}
