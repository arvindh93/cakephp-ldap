<?php
namespace LdapUtility\Auth;

use Cake\Auth\BaseAuthenticate;
use Cake\Controller\ComponentRegistry;
use Cake\Network\Request;
use Cake\Network\Response;
use LdapUtility\Exception\LdapException;
use LdapUtility\Ldap;

/**
 * LDAP authentication adapter for AuthComponent
 *
 * Provides LDAP authentication for given username and password
 *
 * ## usage
 * Add LDAP auth to controllers component
 */
class LdapAuthenticate extends BaseAuthenticate
{
    protected $ldap = null;

    /**
     * Constructor
     *
     * {@inheritDoc}
     */
    public function __construct(ComponentRegistry $registry, array $config = [])
    {
        parent::__construct($registry, $config);

        $this->ldap = new ldap($config);
    }

    /**
     * Authenticate user
     *
     * {@inheritDoc}
     */
    public function authenticate(Request $request, Response $response)
    {
        if (empty($request->data['username']) || empty($request->data['password'])) {
            throw new LdapException('Empty username or password');
        }

        return $this->_findUser($request->data['username'], $request->data['password']);
    }

    /**
     * Find user method
     *
     * @param string $username Username
     * @param string $password Password
     * @return bool|array
     */
    protected function _findUser($username, $password = null)
    {
        $bindDn = $this->getBindDn($username);
        try {
            $this->ldap->bindUsingCredentials($bindDn, $password);
            $rDn = $this->getRelativeDn($username);

            $user = $this->ldap->read()
                ->setBaseDn($bindDn)
                ->where($rDn)
                ->first();

            return $user;
        } catch (LdapException $e) {
            return false;
        }
    }

    /**
     * Bind dn from config fields and username
     *
     * @param string $username username
     * @return string $bindDn Bind dn
     */
    public function getBindDn($username)
    {
        $fields = $this->_configRead('fields');
        $prefix = $fields['username'];
        $suffix = $fields['suffix'];

        if (!empty($prefix)) {
            $username = $prefix . "=" . $username;
        }

        if (!empty($suffix)) {
            $username .= ',' . $suffix;
        }

        return $username;
    }

    /**
     * relative dn from config fields and username
     *
     * @param string $username username
     * @return string $rDn relative dn
     */
    public function getRelativeDn($username)
    {
        $fields = $this->_configRead('fields');
        $prefix = $fields['username'];

        if (!empty($prefix)) {
            $username = $prefix . "=" . $username;
        }

        return $username;
    }
}
