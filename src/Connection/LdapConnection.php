<?php
namespace LdapUtility\Connection;

/**
 * LDAP connection class
 */
class LdapConnection
{
    protected $connection = null;
    protected $hideErrors = false;
    public $digMsg = '';
    public $errMsg = '';

    /**
     * Connect ldap host
     *
     * @param string $host host
     * @param int $port port
     * @return false|resouce identifier
     */
    public function connect($host, $port = 389)
    {
        return $this->connection = ldap_connect($host, $port);
    }

    /**
     * Set option for LDAP connection
     *
     * @param int $key LDAP predefined constant option
     * @param mixed $value value to set
     * @return bool success
     */
    public function setOption($key, $value)
    {
        if ($this->hideErrors) {
            return @ldap_set_option($this->connection, $key, $value);
        }

        return ldap_set_option($this->connection, $key, $value);
    }

    /**
     * start TLS connection
     *
     * @return bool success
     */
    public function startTLS()
    {
        return ldap_start_tls($this->connection);
    }

    /**
     * hide errors - to avoid error display
     * @return void
     */
    public function hideErrors()
    {
        $this->hideErrors = true;
    }

    /**
     * make errors/warnings visible
     * @return void
     */
    public function showErrors()
    {
        $this->hideErrors = false;
    }

    /**
     * Bind using given bindDn and password
     *
     * @param string $bindDn bind dn
     * @param string $password password
     * @return bool success
     */
    public function bind($bindDn, $password)
    {
        if ($this->hideErrors) {
            return @ldap_bind($this->connection, $bindDn, $password);
        }

        return ldap_bind($this->connection, $bindDn, $password);
    }

    /**
     * Search for filter in ldap directory
     *
     * @param string $baseDn base dn
     * @param string $filter filter string to search
     * @param array $attributes attributes to return
     * @return false|resource identifier
     */
    public function search($baseDn, $filter, $attributes)
    {
        if ($this->hideErrors) {
            return @ldap_search($this->connection, $baseDn, $filter, $attributes);
        }

        return ldap_search($this->connection, $baseDn, $filter, $attributes);
    }

    /**
     * Read an entry in ldap directory
     *
     * @param string $baseDn base dn
     * @param string $filter filter string to search
     * @param array $attributes attributes to return
     * @return false|resource identifier
     */
    public function read($baseDn, $filter, $attributes)
    {
        if ($this->hideErrors) {
            return @ldap_read($this->connection, $baseDn, $filter, $attributes);
        }

        return ldap_read($this->connection, $baseDn, $filter, $attributes);
    }

    /**
     * Get all entries based on entry identifier
     *
     * @param entry_identifier $entryId Entry identifier
     * @return array $result entries
     */
    public function getAllEntries($entryId)
    {
        if ($this->hideErrors) {
            return @ldap_get_entries($this->connection, $entryId);
        }

        return ldap_get_entries($this->connection, $entryId);
    }

    /**
     * Get first entry
     *
     * @param entry_identifier $entryId Entry identifier
     * @return array $result entries
     */
    public function getFirstEntry($entryId)
    {
        if ($this->hideErrors) {
            return @ldap_first_entry($this->connection, $entryId);
        }

        return ldap_first_entry($this->connection, $entryId);
    }

    /**
     * Get All attributes
     *
     * @param entry_identifier $entryId Entry identifier
     * @return array $result entries
     */
    public function getAllAttributes($entryId)
    {
        if ($this->hideErrors) {
            return @ldap_get_attributes($this->connection, $entryId);
        }

        return ldap_get_attributes($this->connection, $entryId);
    }

    /**
     * Get connection identifier
     * @return link_identifier ldap connection resource
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Get LDAP error code
     * @return int $errorCode LDAP error code
     */
    public function getErrorNo()
    {
        return ldap_errno($this->connection);
    }

    /**
     * Get LDAP error message
     * @return string $error LDAP error message
     */
    public function getError()
    {
        return ldap_error($this->connection);
    }

    /**
     * Close existing ldap connection
     * @return bool success
     */
    public function close()
    {
        return ldap_close($this->connection);
    }
}