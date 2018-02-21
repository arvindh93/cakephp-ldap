<?php
namespace LdapUtility\Query;

use LdapUtility\Exception\LdapException;
use LdapUtiltiy\Ldap;

class LdapQuery
{
    protected $ldap = null;
    protected $ldapConnection = null;
    protected $searchType = null;
    protected $baseDn = null;
    protected $filter = '';
    protected $fields = [];

    //search type
    const LDAP_SEARCH = 1;
    const LDAP_READ = 2;

    const LDAP_SEARCH_TYPE = [
        self::LDAP_SEARCH,
        self::LDAP_READ
    ];

    /**
     * Constructor
     *
     * @param LdapUtility\Ldap $ldap Ldap
     * @param int $searchType Search type
     * @return void
     */
    public function __construct($ldap, $searchType)
    {
        $this->ldap = $ldap;
        $this->ldapConnection = $ldap->getConnection();

        if (!in_array($searchType, self::LDAP_SEARCH_TYPE)) {
            throw new LdapException('Error creating query object - Invalid Search type');
        }
        $this->searchType = $searchType;
    }

    /**
     * set baseDn
     *
     * @param string $dn Base distinguished name
     * @return LdapUtility\LdapQuery $query
     */
    public function setBaseDn($dn)
    {
        $this->baseDn = $dn;

        return $this;
    }

    /**
     * get baseDn
     *
     * @return string $baseDn baseDn
     */
    public function getBaseDn()
    {
        return $this->baseDn;
    }

    /**
     * set where filter
     *
     * @param string $filter Filter condition
     * @return LdapUtility\LdapQuery $query
     */
    public function where($filter)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * Select Fields
     *
     * @param array $fields Fields
     * @return LdapUtility\LdapQuery $query
     */
    public function select($fields = [])
    {
        if (!is_array($fields)) {
            throw new LdapException("Invalid type for fields");
        }
        $this->fields = $fields;

        return $this;
    }


    /**
     * First result as array
     *
     * @return array $result result
     */
    public function first()
    {
        $resultIdentifier = $this->getResultIdentifier();
        $entryIdentifier = $this->ldapConnection->getFirstEntry($resultIdentifier);
        if ($entryIdentifier === false) {
            return [];
        }
        $rawResult = $this->ldapConnection->getAllAttributes($entryIdentifier);
        $proceessedResult = $this->processResultForSingleEntry($rawResult);

        return $proceessedResult;
    }

    /**
     * Get all entries
     *
     * @return array $result result
     */
    public function all()
    {
        $resultIdentifier = $this->getResultIdentifier();
        $rawResult = $this->ldapConnection->getAllEntries($resultIdentifier);
        $proceessedResult = $this->processResultForAllEntries($rawResult);

        return $proceessedResult;
    }

    /**
     * Process raw LDAP entries result
     *
     * @param array $rawEntries Raw entries
     * @return array $processedResullt processed result
     */
    public function processResultForAllEntries($rawEntries)
    {
        if ($rawEntries['count'] < 1) {
            return [];
        }

        $result = [];
        foreach ($rawEntries as $key => $entry) {
            if ($key === 'count') {
                continue;
            }
            $result[] = $this->processResultForSingleEntry($entry);
        }

        return $result;
    }

    /**
     * Process result for single entry
     *
     * @param array $entry raw entry data
     * @return array $result Result
     */
    public function processResultForSingleEntry($entry)
    {
        $result = [];
        foreach ($entry as $key => $value) {
            if (is_array($value)) {
                if ($value['count'] > 1) {
                    $nestedValue = [];
                    for ($i = 0; $i < $value['count']; $i++) {
                        $nestedValue[] = $value[$i];
                    }
                    $result[$key] = $nestedValue;
                } else {
                    $result[$key] = $value[0];
                }
            }
        }

        return $result;
    }

    /**
     * Result identifier
     *
     * @return false|resource $resultIdentifier Identifier
     */
    public function getResultIdentifier()
    {
        $baseDn = $this->getBaseDn() ?? $this->ldap->getBaseDn();

        if ($this->searchType == self::LDAP_SEARCH) {
            $result = $this->ldapConnection->search($baseDn, $this->filter, $this->fields);
        } elseif ($this->searchType == self::LDAP_READ) {
            $result = $this->ldapConnection->read($baseDn, $this->filter, $this->fields);
        }
        if ($result === false) {
            $this->ldap->throwExceptionOnErrors();
        }

        return $result;
    }
}
