<?php
namespace LdapUtility;

use Cake\Core\InstanceConfigTrait;
use LdapUtility\Connection\LdapConnection;
use LdapUtility\Exception\LdapException;
use LdapUtility\Query\LdapQuery;

/**
 * class LDAP
 */
class Ldap
{
    use InstanceConfigTrait;

    /**
     * Default config for LDAP
     *
     * -`host` - LDAP host name
     * -`port` - port to connect - defaults to 389
     * -`protocol_version` - LDAP protocol version, defaults to 3
     * -`baseDN` - base DN
     * -`startTLS` - bool value whether to est connection with TLS
     * -`commonBindDn` - common bind credential to use search feature
     * -`commonBindPassword` - common bind password
     * -`hideErrors` - bool value whether to suppress errors and warnings - defaults to false
     */
    protected $_defaultConfig = [
        'host' => '',
        'port' => 389,
        'protocol_version' => 3,
        'baseDn' => '',
        'startTLS' => false,
        'commonBindDn' => '',
        'commonBindPassword' => '',
        'hideErrors' => false,
    ];

    protected $ldapConnection = null;
    protected $bound = false;

    /**
     * Constructor
     * @param array $config - Config
     * @return void
     */
    public function __construct($config)
    {
        $this->config($config);

        $this->ldapConnection = new LdapConnection();
        $this->connect();
        $this->setErrorStatus();
        $this->setProtocolVersion();
        $this->setTLS();
    }

    /**
     * Connect ldap server
     * @return void
     */
    protected function connect()
    {
        $this->ldapConnection->connect($this->_configRead('host'), $this->_configRead('port'));
    }

    /**
     * Set LDAP protocol version - defaults to version 3
     * @return void
     */
    protected function setProtocolVersion()
    {
        $this->ldapConnection->setOption(LDAP_OPT_PROTOCOL_VERSION, $this->_configRead('protocol_version'));
    }

    /**
     * Set Error status
     * @return void
     */
    protected function setErrorStatus()
    {
        if ($this->_configRead('hideErrors')) {
            $this->ldapConnection->hideErrors();
        } else {
            $this->ldapConnection->showErrors();
        }
    }

    /**
     * Set startTLS config
     * @return void
     */
    protected function setTLS()
    {
        if ($this->_configRead('startTLS')) {
            $this->ldapConnection->startTLS();
            $this->throwExceptionOnErrors();
        }
    }

    /**
     * LDAP bind using credentials
     *
     * @param string $bindDn - the distinguished name
     * @param string $password - password
     * @return void
     */
    public function bindUsingCredentials($bindDn, $password)
    {
        $this->bound = $this->ldapConnection->bind($bindDn, $password);

        if (!$this->bound) {
            $this->throwExceptionOnErrors();
        }
    }

    /**
     * LDAP bind using common bind credentials
     *
     * @return void
     */
    public function bindUsingCommonCredentials()
    {
        $bindDn = $this->_configRead('commonBindDn');
        $password = $this->_configRead('commonBindPassword');
        $this->bound = $this->bindUsingCredentials($bindDn, $password);
    }

    /**
     * Read and LDAP entry
     *
     * @return LdapUtility\LdapQuery $query
     */
    public function read()
    {
        return new LdapQuery($this, LdapQuery::LDAP_READ);
    }

    /**
     * Search using LDAP
     *
     * @return LdapUtility\LdapQuery $query
     */
    public function search()
    {
        return new LdapQuery($this, LdapQuery::LDAP_SEARCH);
    }

    /**
     * LDAP connection - resource link identifier
     * @return resource $ldapConnection
     */
    public function getConnection()
    {
        return $this->ldapConnection;
    }

    /**
     * Convert LDAP error into exceptions
     *
     * @return void
     * @throws LdapUtility\Exception\LdapException on LDAP error
     */
    public function throwExceptionOnErrors()
    {
        $errorNo = $this->ldapConnection->getErrorNo();
        if ($errorNo !== 0) {
            throw new LdapException($this->ldapConnection->getError(), $errorNo);
        }
    }

    /**
     * Get base dn from config
     *
     * @return string $baseDn base dn
     */
    public function getBaseDn()
    {
        return $this->_configRead('baseDn');
    }

    /**
     * bound status
     * @return bool success
     */
    public function getBound()
    {
        return $this->bound;
    }

    /**
     * Close existing LDAP connection
     * @return bool success
     */
    public function close()
    {
        return $this->ldapConnection->close();
    }
}
