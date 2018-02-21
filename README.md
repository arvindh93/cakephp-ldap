# Cakephp-ldap-auth plugin for CakePHP

## Requirements

* CakePHP 3.1+

## Installation

You can install this plugin into your CakePHP application using [composer](http://getcomposer.org).

The recommended way to install composer packages is:

```
composer require aravind-zrx/Cakephp-ldap
```

## Usage

In your app's `config/bootstrap.php` add:

```php
// In config/bootstrap.php
Plugin::load('LdapUtility');
```

or using cake's console:

```sh
./bin/cake plugin load LdapUtility
```

## Configuration:

Basic configuration for creating ldap handler instance

```php
	$config = [
		'host' => 'ldap.example.com',
        'port' => 389,
        'baseDn' => 'dc=example,dc=com',
        'startTLS' => true,
        'hideErrors' => true,
        'commonBindDn' => 'cn=readonly.user,ou=people,dc=example,dc=com',
        'commonBindPassword' => 'secret'
	]
	$ldapHandler = new LdapUtility\Ldap($config);
```

Setup Ldap authentication config in Controller

```php
    // In your controller, for e.g. src/Api/AppController.php
    public function initialize()
    {
        parent::initialize();

        $this->loadComponent('Auth', [
            'storage' => 'Memory',
            'authenticate', [
                'LdapUtility.Ldap' => [
					'host' => 'ldap.example.com',
			        'port' => 389,
			        'baseDn' => 'dc=example,dc=com',
			        'startTLS' => true,
			        'hideErrors' => true,
			        'commonBindDn' => 'cn=readonly.user,ou=people,dc=example,dc=com',
			        'commonBindPassword' => 'secret',
			        'fields' => [
			            'username' => 'cn',
			            'suffix' => 'ou=people,dc=test,dc=com'
			        ]
				]
            ],

            'unauthorizedRedirect' => false,
            'checkAuthIn' => 'Controller.initialize',
        ]);
    }
```

## Usage:

#Creating Query object for Search/Read operation:
Search - $ldapHandler->search()
Read - $ldapHandler->read()

#Operations on query object:
	select() - accepts an array of attributes to fetch from ldap entry
	setBaseDn() - accepts baseDn string defaults to config - baseDn
	where() - accepts filter string
	first() - execute the query and get the first entry details as array
	all() - executes the query and get all the possible entries as array

## Example:

Search for entry with cn starting with test
```php
	$ldapHandler->search()
		->setBaseDn('ou=people,dc=example,dc=com')
		->select(['cn', 'sn', 'mail'])
		->where('cn=test*')
		->all()
```

Search for entry with cn starting with test and get first entry
```php
	$ldapHandler->search()
		->setBaseDn('ou=people,dc=example,dc=com')
		->select(['cn', 'sn', 'mail'])
		->where('cn=test*')
		->first()
```

Read a particular entry with cn=test.user
```php
	$ldapHandler->read()
		->setBaseDn('cn=test.user,ou=people,dc=example,dc=com')
		->select(['cn', 'sn', 'mail'])
		->where('cn=test.user')
		->first()
```

