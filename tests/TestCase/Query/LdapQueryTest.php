<?php
namespace LdapUtility\Test\TestCase;

use Cake\Log\Log;
use Cake\TestSuite\IntegrationTestCase;
use LdapUtility\Ldap;
use LdapUtility\Connection\LdapConnection;
use LdapUtility\Exception\LdapException;
use LdapUtility\Query\LdapQuery;

class LdapQueryTest extends IntegrationTestCase
{
    protected $testConfig = [
        'host' => 'localhost',
        'baseDn' => 'dc=test,dc=com',
        'startTLS' => false,
        'hideErrors' => false,
        'fields' => [
            'username' => 'cn',
            'suffix' => 'ou=people,dc=test,dc=com'
        ],
        'commonBindDn' => 'cn=test.user,ou=people,dc=test,dc=com',
        'commonBindPassword' => 'test'
    ];
    protected $ldapQuery = null;

    public function setUp()
    {
        parent::setUp();
        $this->ldapQuery = null;
    }

    public function testSearch()
    {
        #### testing all #####
        //valid entry with valid base dn
        $this->ldapQuery = new LdapQuery(new Ldap($this->testConfig), LdapQuery::LDAP_SEARCH);
        $expected = [
            'cn' => 'test.user',
            'sn' => 'test',
            'mail' => 'testuser@nodomain.com'
        ];
        $actual = $this->ldapQuery->setBaseDn('ou=people,dc=test,dc=com')
            ->where('cn=test.user')
            ->all();
        $this->assertArraySubset($expected, $actual[0]);

        //valid entry without setting base dn
        $this->ldapQuery = new LdapQuery(new Ldap($this->testConfig), LdapQuery::LDAP_SEARCH);
        $expected = [
            'cn' => 'test.user',
            'sn' => 'test',
            'mail' => 'testuser@nodomain.com'
        ];
        $actual = $this->ldapQuery
            ->where('cn=test.user')
            ->all();
        $this->assertArraySubset($expected, $actual[0]);

        //valid wildcard entry
        $this->ldapQuery = new LdapQuery(new Ldap($this->testConfig), LdapQuery::LDAP_SEARCH);
        $expected = [
            [
                'cn' => 'test.user',
                'sn' => 'test',
                'mail' => 'testuser@nodomain.com'
            ],
            [
                'cn' => 'test.user2',
                'sn' => 'test'
            ],
            [
                'cn' => 'test.user3',
                'sn' => 'test'
            ]
        ];
        $actual = $this->ldapQuery->setBaseDn('ou=people,dc=test,dc=com')
            ->where('cn=test.user*')
            ->all();
        $this->assertEquals(count($expected), count($actual));
        $count = count($expected);
        for ($i = 0; $i < $count; $i++) {
            $this->assertArraySubset($expected[$i], $actual[$i]);
        }

        //valid entry with fields
        $this->ldapQuery = new LdapQuery(new Ldap($this->testConfig), LdapQuery::LDAP_SEARCH);
        $expected = [
            'cn' => 'test.user',
        ];
        $actual = $this->ldapQuery->setBaseDn('ou=people,dc=test,dc=com')
            ->select(['cn'])
            ->where('cn=test.user')
            ->all();
        $this->assertEquals($expected, $actual[0]);

        //valid wildcard entry with fields
        $this->ldapQuery = new LdapQuery(new Ldap($this->testConfig), LdapQuery::LDAP_SEARCH);
        $expected = [
            [
                'cn' => 'test.user'
            ],
            [
                'cn' => 'test.user2'
            ],
            [
                'cn' => 'test.user3'
            ]
        ];
        $actual = $this->ldapQuery->setBaseDn('ou=people,dc=test,dc=com')
            ->select(['cn'])
            ->where('cn=*user*')
            ->all();
        $count = count($expected);
        for ($i = 0; $i < $count; $i++) {
            $this->assertEquals($expected[$i], $actual[$i]);
        }

        //invalid entry
        $this->ldapQuery = new LdapQuery(new Ldap($this->testConfig), LdapQuery::LDAP_SEARCH);
        $actual = $this->ldapQuery->setBaseDn('ou=people,dc=test,dc=com')
            ->where('cn=no.user')
            ->all();
        $this->assertEmpty($actual);

        //invalid baseDn
        try {
            $config = array_merge($this->testConfig, ['hideErrors' => true]);
            $this->ldapQuery = new LdapQuery(new Ldap($config), LdapQuery::LDAP_SEARCH);
            $actual = $this->ldapQuery->setBaseDn('dsdfasdfla')
                ->where('cn=no.user')
                ->all();
            $this->fail("Expected Ldap exception on invalid DN syntax");
        } catch (LdapException $e) {
            $this->assertEquals($e->getCode(), 34);
            $this->assertEquals($e->getMessage(), ldap_err2str(34));
        }

        //invalid host
        try {
            $config = array_merge($this->testConfig, ['host' => 'invalidhost', 'hideErrors' => true]);
            $this->ldapQuery = new LdapQuery(new Ldap($config), LdapQuery::LDAP_SEARCH);
            $actual = $this->ldapQuery->setBaseDn('dsdfasdfla')
                ->where('cn=no.user')
                ->all();
            $this->fail("Expected Ldap exception on invalid DN syntax");
        } catch (LdapException $e) {
            $this->assertEquals($e->getCode(), -1);
            $this->assertEquals($e->getMessage(), ldap_err2str(-1));
        }

        #### testing first #####
        //valid entry with valid base dn
        $this->ldapQuery = new LdapQuery(new Ldap($this->testConfig), LdapQuery::LDAP_SEARCH);
        $expected = [
            'cn' => 'test.user',
            'sn' => 'test',
            'mail' => 'testuser@nodomain.com'
        ];
        $actual = $this->ldapQuery->setBaseDn('ou=people,dc=test,dc=com')
            ->where('cn=test.user')
            ->first();
        $this->assertArraySubset($expected, $actual);

        //valid entry without setting base dn
        $this->ldapQuery = new LdapQuery(new Ldap($this->testConfig), LdapQuery::LDAP_SEARCH);
        $expected = [
            'cn' => 'test.user',
            'sn' => 'test',
            'mail' => 'testuser@nodomain.com'
        ];
        $actual = $this->ldapQuery
            ->where('cn=test.user')
            ->first();
        $this->assertArraySubset($expected, $actual);

        //valid wildcard entry
        $this->ldapQuery = new LdapQuery(new Ldap($this->testConfig), LdapQuery::LDAP_SEARCH);
        $expected = [
                'cn' => 'test.user',
                'sn' => 'test',
                'mail' => 'testuser@nodomain.com'
        ];
        $actual = $this->ldapQuery->setBaseDn('ou=people,dc=test,dc=com')
            ->where('cn=test.user*')
            ->first();
        $this->assertArraySubset($expected, $actual);

        //valid entry with fields
        $this->ldapQuery = new LdapQuery(new Ldap($this->testConfig), LdapQuery::LDAP_SEARCH);
        $expected = [
            'cn' => 'test.user',
        ];
        $actual = $this->ldapQuery->setBaseDn('ou=people,dc=test,dc=com')
            ->select(['cn'])
            ->where('cn=test.user')
            ->first();
        $this->assertEquals($expected, $actual);

        //valid wildcard entry with fields
        $this->ldapQuery = new LdapQuery(new Ldap($this->testConfig), LdapQuery::LDAP_SEARCH);
        $expected = [
            'cn' => 'test.user'
        ];
        $actual = $this->ldapQuery->setBaseDn('ou=people,dc=test,dc=com')
            ->select(['cn'])
            ->where('cn=*user*')
            ->first();
        $this->assertEquals($expected, $actual);

        //invalid entry
        $this->ldapQuery = new LdapQuery(new Ldap($this->testConfig), LdapQuery::LDAP_SEARCH);
        $actual = $this->ldapQuery->setBaseDn('ou=people,dc=test,dc=com')
            ->where('cn=no.user')
            ->all();
        $this->assertEmpty($actual);

        //invalid baseDn
        try {
            $config = array_merge($this->testConfig, ['hideErrors' => true]);
            $this->ldapQuery = new LdapQuery(new Ldap($config), LdapQuery::LDAP_SEARCH);
            $actual = $this->ldapQuery->setBaseDn('dsdfasdfla')
                ->where('cn=no.user')
                ->first();
            $this->fail("Expected Ldap exception on invalid DN syntax");
        } catch (LdapException $e) {
            $this->assertEquals($e->getCode(), 34);
            $this->assertEquals($e->getMessage(), ldap_err2str(34));
        }

        //invalid host
        try {
            $config = array_merge($this->testConfig, ['host' => 'invalidhost', 'hideErrors' => true]);
            $this->ldapQuery = new LdapQuery(new Ldap($config), LdapQuery::LDAP_SEARCH);
            $actual = $this->ldapQuery->setBaseDn('dsdfasdfla')
                ->where('cn=no.user')
                ->first();
            $this->fail("Expected Ldap exception on invalid DN syntax");
        } catch (LdapException $e) {
            $this->assertEquals($e->getCode(), -1);
            $this->assertEquals($e->getMessage(), ldap_err2str(-1));
        }
    }

    public function testRead()
    {
        #### testing all #####
        //valid entry with valid base dn
        $this->ldapQuery = new LdapQuery(new Ldap($this->testConfig), LdapQuery::LDAP_READ);
        $expected = [
            'cn' => 'test.user',
            'sn' => 'test',
            'mail' => 'testuser@nodomain.com'
        ];
        $actual = $this->ldapQuery->setBaseDn('cn=test.user,ou=people,dc=test,dc=com')
            ->where('cn=test.user')
            ->all();
        Log::debug($actual);
        $this->assertArraySubset($expected, $actual[0]);

        //valid entry without setting base dn
        $this->ldapQuery = new LdapQuery(new Ldap($this->testConfig), LdapQuery::LDAP_READ);
        $actual = $this->ldapQuery
            ->where('cn=test.user')
            ->all();
        $this->assertEmpty($actual);

        //valid wildcard entry
        $this->ldapQuery = new LdapQuery(new Ldap($this->testConfig), LdapQuery::LDAP_READ);
        $expected = [
            [
                'cn' => 'test.user',
                'sn' => 'test',
                'mail' => 'testuser@nodomain.com'
            ]
        ];
        $actual = $this->ldapQuery->setBaseDn('cn=test.user,ou=people,dc=test,dc=com')
            ->where('cn=test.user*')
            ->all();
        $this->assertEquals(count($expected), count($actual));
        $count = count($expected);
        for ($i = 0; $i < $count; $i++) {
            $this->assertArraySubset($expected[$i], $actual[$i]);
        }

        //valid entry with fields
        $this->ldapQuery = new LdapQuery(new Ldap($this->testConfig), LdapQuery::LDAP_READ);
        $expected = [
            'cn' => 'test.user',
        ];
        $actual = $this->ldapQuery->setBaseDn('cn=test.user,ou=people,dc=test,dc=com')
            ->select(['cn'])
            ->where('cn=test.user')
            ->all();
        $this->assertEquals($expected, $actual[0]);

        //valid wildcard entry with fields
        $this->ldapQuery = new LdapQuery(new Ldap($this->testConfig), LdapQuery::LDAP_READ);
        $expected = [
            [
                'cn' => 'test.user'
            ]
        ];
        $actual = $this->ldapQuery->setBaseDn('cn=test.user,ou=people,dc=test,dc=com')
            ->select(['cn'])
            ->where('cn=*user*')
            ->all();
        $count = count($expected);
        for ($i = 0; $i < $count; $i++) {
            $this->assertEquals($expected[$i], $actual[$i]);
        }

        //invalid entry
        $this->ldapQuery = new LdapQuery(new Ldap($this->testConfig), LdapQuery::LDAP_READ);
        $actual = $this->ldapQuery->setBaseDn('cn=test.user,ou=people,dc=test,dc=com')
            ->where('cn=no.user')
            ->all();
        $this->assertEmpty($actual);

        //invalid baseDn
        try {
            $config = array_merge($this->testConfig, ['hideErrors' => true]);
            $this->ldapQuery = new LdapQuery(new Ldap($config), LdapQuery::LDAP_READ);
            $actual = $this->ldapQuery->setBaseDn('dsdfasdfla')
                ->where('cn=no.user')
                ->all();
            $this->fail("Expected Ldap exception on invalid DN syntax");
        } catch (LdapException $e) {
            $this->assertEquals($e->getCode(), 34);
            $this->assertEquals($e->getMessage(), ldap_err2str(34));
        }

        //invalid host
        try {
            $config = array_merge($this->testConfig, ['host' => 'invalidhost', 'hideErrors' => true]);
            $this->ldapQuery = new LdapQuery(new Ldap($config), LdapQuery::LDAP_READ);
            $actual = $this->ldapQuery->setBaseDn('dsdfasdfla')
                ->where('cn=no.user')
                ->all();
            $this->fail("Expected Ldap exception on invalid DN syntax");
        } catch (LdapException $e) {
            $this->assertEquals($e->getCode(), -1);
            $this->assertEquals($e->getMessage(), ldap_err2str(-1));
        }

        #### testing first #####
        //valid entry with valid base dn
        $this->ldapQuery = new LdapQuery(new Ldap($this->testConfig), LdapQuery::LDAP_READ);
        $expected = [
            'cn' => 'test.user',
            'sn' => 'test',
            'mail' => 'testuser@nodomain.com'
        ];
        $actual = $this->ldapQuery->setBaseDn('cn=test.user,ou=people,dc=test,dc=com')
            ->where('cn=test.user')
            ->first();
        $this->assertArraySubset($expected, $actual);

        //valid entry without setting base dn
        $this->ldapQuery = new LdapQuery(new Ldap($this->testConfig), LdapQuery::LDAP_READ);
        $actual = $this->ldapQuery
            ->where('cn=test.user')
            ->first();
        $this->assertEmpty($actual);

        //valid wildcard entry
        $this->ldapQuery = new LdapQuery(new Ldap($this->testConfig), LdapQuery::LDAP_READ);
        $expected = [
                'cn' => 'test.user',
                'sn' => 'test',
                'mail' => 'testuser@nodomain.com'
        ];
        $actual = $this->ldapQuery->setBaseDn('cn=test.user,ou=people,dc=test,dc=com')
            ->where('cn=test.user*')
            ->first();
        $this->assertArraySubset($expected, $actual);

        //valid entry with fields
        $this->ldapQuery = new LdapQuery(new Ldap($this->testConfig), LdapQuery::LDAP_READ);
        $expected = [
            'cn' => 'test.user',
        ];
        $actual = $this->ldapQuery->setBaseDn('cn=test.user,ou=people,dc=test,dc=com')
            ->select(['cn'])
            ->where('cn=test.user')
            ->first();
        $this->assertEquals($expected, $actual);

        //valid wildcard entry with fields
        $this->ldapQuery = new LdapQuery(new Ldap($this->testConfig), LdapQuery::LDAP_READ);
        $expected = [
            'cn' => 'test.user'
        ];
        $actual = $this->ldapQuery->setBaseDn('cn=test.user,ou=people,dc=test,dc=com')
            ->select(['cn'])
            ->where('cn=*user*')
            ->first();
        $this->assertEquals($expected, $actual);

        //invalid entry
        $this->ldapQuery = new LdapQuery(new Ldap($this->testConfig), LdapQuery::LDAP_READ);
        $actual = $this->ldapQuery->setBaseDn('ou=people,dc=test,dc=com')
            ->where('cn=no.user')
            ->all();
        $this->assertEmpty($actual);

        //invalid baseDn
        try {
            $config = array_merge($this->testConfig, ['hideErrors' => true]);
            $this->ldapQuery = new LdapQuery(new Ldap($config), LdapQuery::LDAP_READ);
            $actual = $this->ldapQuery->setBaseDn('dsdfasdfla')
                ->where('cn=no.user')
                ->first();
            $this->fail("Expected Ldap exception on invalid DN syntax");
        } catch (LdapException $e) {
            $this->assertEquals($e->getCode(), 34);
            $this->assertEquals($e->getMessage(), ldap_err2str(34));
        }

        //invalid host
        try {
            $config = array_merge($this->testConfig, ['host' => 'invalidhost', 'hideErrors' => true]);
            $this->ldapQuery = new LdapQuery(new Ldap($config), LdapQuery::LDAP_READ);
            $actual = $this->ldapQuery->setBaseDn('dsdfasdfla')
                ->where('cn=no.user')
                ->first();
            $this->fail("Expected Ldap exception on invalid DN syntax");
        } catch (LdapException $e) {
            $this->assertEquals($e->getCode(), -1);
            $this->assertEquals($e->getMessage(), ldap_err2str(-1));
        }
    }
}