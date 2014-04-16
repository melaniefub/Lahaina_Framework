<?php

    namespace lahaina\libraries\security;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

    /**
     * Authenticated remote user extends user class
     * 
     * @version 1.0.2
     *
     * @author Jonathan Nessier
     */
    class RemoteUser extends User {

	/**
	 * Constructor
	 * 
	 * @param string $username Username
	 * @param string $roleType Type of role
	 * @param string $domain Domain
	 */
	public function __construct($username = null, $roleType = null, $domain = null) {

	    parent::__construct($username, $roleType);
	    $this->domain = $domain;
	}

	/**
	 * Get domain
	 * 
	 * @return string
	 */
	public function getDomain() {
	    return $this->domain;
	}

	/**
	 * Get username with domain
	 * 
	 * @return string
	 */
	public function getUsernameWithDomain() {
	    return $this->domain . '\\' . $this->username;
	}

    }
    