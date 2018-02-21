<?php
namespace LdapUtility\Test\TestCase;

use Cake\Log\Log;
use Cake\TestSuite\IntegrationTestCase;
use LdapUtility\Auth\LdapAuthenticate;

class LdapAuthenticateTest extends IntegrationTestCase
{
    protected $ldapAuth = null;
    protected $registry = null;
    protected $testConfig = [
        'host' => 'localhost',
        'baseDN' => 'dc=test,dc=com',
        'startTLS' => false,
        'hideErrors' => true,
        'fields' => [
            'username' => 'cn',
            'suffix' => 'ou=people,dc=test,dc=com'
        ]
    ];
    public function setUp()
    {
        parent::setUp();

        //mock registry
        $this->registry = $this->getMockBuilder('Cake\Controller\ComponentRegistry')->getMock();
        $this->ldapAuth = new LdapAuthenticate($this->registry, $this->testConfig);
    }

    //testIdentify
    public function testAuthenticate()
    {
        $expected = [
            'sn' => 'test',
            'cn' => 'test.user',
            'mail' => 'testuser@nodomain.com'
        ];
        $mockRequest = $this->getMockBuilder('Cake\Network\Request')->getMock();
        $mockResponse = $this->getMockBuilder('Cake\Network\Response')->getMock();

        //empty username and password
        try {
            $actual = $this->ldapAuth->authenticate($mockRequest, $mockResponse);
            $this->fail("Expected exception stating empty username or password");
        } catch (\Exception $e) {
            $this->assertEquals('Empty username or password', $e->getMessage());
        }

        //valid username and passowrd
        $mockRequest->data['username'] = 'test.user';
        $mockRequest->data['password'] = 'test';
        $actual = $this->ldapAuth->authenticate($mockRequest, $mockResponse);
        $this->assertArraySubset($expected, $actual);

        //invalid username and passowrd
        $mockRequest->data['username'] = 'test.user234324';
        $mockRequest->data['password'] = 'test';
        $actual = $this->ldapAuth->authenticate($mockRequest, $mockResponse);
        $this->assertFalse($actual);
    }
}
