<?php
namespace LdapUtility\Test\TestCase;

use Cake\Log\Log;
use Cake\TestSuite\IntegrationTestCase;
use LdapUtility\Exception\LdapException;
use LdapUtility\Ldap;
use Cake\Core\App;

class LdapTest extends IntegrationTestCase
{
    protected $testConfig = [
        'host' => 'localhost',
        'baseDn' => 'dc=test,dc=com',
        'startTLS' => false,
        'hideErrors' => true,
        'fields' => [
            'username' => 'cn',
            'suffix' => 'ou=people,dc=test,dc=com'
        ],
        'commonBindDn' => 'cn=test.user,ou=people,dc=test,dc=com',
        'commonBindPassword' => 'test'
    ];

    protected $ldap = null;

    public function setUp()
    {
        parent::setUp();
        $this->ldap = new Ldap($this->testConfig);
        $this->ldap->bindUsingCommonCredentials();
    }

    public function tearDown()
    {
        parent::tearDown();
        if (!empty($this->ldap)) {
            $this->ldap->close();
        }
    }

    public function testConnect()
    {
        $this->assertTrue(is_resource($this->ldap->getConnection()->getConnection()));
    }

    public function testBindUsingCredentials()
    {
        //valid user in ldap
        $this->ldap->bindUsingCredentials('cn=test.user,ou=people,dc=test,dc=com', 'test');
        $actualErrorNo = $this->ldap->getConnection()->getErrorNo();
        $expectedErrorNo = 0;
        $this->assertEquals($expectedErrorNo, $actualErrorNo);

        //invalid user in ldap
        try {
            $this->ldap->bindUsingCredentials('cn=invalid.user', 'testasdfs');
            $this->fail('Failed asserting exception while binding using credentials. Expected Ldapexception with invalid credentials message');
        } catch (LdapException $e) {
            $this->assertEquals($e->getMessage(), 'Invalid credentials');
            $this->assertEquals(49, $e->getCode());
        }

        //invalid domin controller
        try {
            $this->ldap->bindUsingCredentials('invaliduser@nodomain.com', 'testasdfs');
            $this->fail('Failed asserting exception while binding using credentials. Expected Ldapexception with invalid DN syntax message');
        } catch (LdapException $e) {
            $this->assertEquals($e->getMessage(), 'Invalid DN syntax');
            $this->assertEquals(34, $e->getCode());
        }

        //invalid user in ldap
        try {
            $this->ldap->bindUsingCredentials('cn=invalid.user', 'testasdfs');
            $this->fail('Failed asserting exception while binding using credentials. Expected Ldapexception with invalid user message');
        } catch (LdapException $e) {
            $this->assertEquals($e->getMessage(), 'Invalid credentials');
            $this->assertEquals(49, $e->getCode());
        }

        //invalid host
        $this->ldap = new Ldap(array_merge($this->testConfig, ['host' => 'invalid.ldap.host']));
        try {
            $this->ldap->bindUsingCredentials('cn=test.user,dc=test,dc=com', 'test');
            $this->fail('Failed asserting exception while binding using credentials. Expected Ldapexception with invalid credentials message');
        } catch (LdapException $e) {
            $this->assertEquals($e->getMessage(), "Can't contact LDAP server");
            $this->assertEquals(-1, $e->getCode());
        }
    }

    public function getLdapError()
    {
        return ldap_error($this->ldap->getConnection()->getConnection());
    }
}