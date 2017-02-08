<?php

/**
 * Abenity API Test
 *
 * @category  Abenity
 * @package   Abenity_Api_Test
 * @author    Abenity <support@abenity.com>
 * @copyright 2017 Abenity Inc.
 * @license   MIT
 * @link      https://github.com/Abenity/abenity-php
 **/

namespace Abenity\ApiClient\Tests;

/**
 * Abenity API Class
 *
 * @category  Abenity
 * @package   Abenity_Api_Test
 * @author    Abenity <support@abenity.com>
 * @copyright 2017 Abenity Inc.
 * @license   MIT
 * @link      https://github.com/Abenity/abenity-php
 **/
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

    public function testDeactivateMember()
    {
        $response = $this->client->deactivateMember(array());
        $this->assertEquals('fail', $response->status);
    }

    public function testReactivateMember()
    {
        $response = $this->client->reactivateMember(array());
        $this->assertEquals('fail', $response->status);
    }
}
