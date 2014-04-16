<?php

    namespace lahaina\libraries\security;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

    /**
     * Abstract authenticated user class
     * 
     * @version 1.0.2
     *
     * @author Jonathan Nessier
     */
    abstract class User {

	protected $username;
	protected $roleType;

	/**
	 * Constructor
	 * 
	 * @param string $username Username
	 * @param string $roleType Type of role
	 */
	public function __construct($username = null, $roleType = null) {
	    $this->username = $username;
	    $this->roleType = $roleType;
	}

	/**
	 * Get type of role
	 * 
	 * @return string
	 */
	public function getRoleType() {
	    return $this->roleType;
	}

	/**
	 * Get username
	 * 
	 * @return string
	 */
	public function getUsername() {
	    return $this->username;
	}

	/**
	 * Check if role exists
	 * 
	 * @param string $roleTypes Type of role
	 * @return boolean
	 */
	public function hasRole($roleType) {
	    if ($this->roleType == $roleType) {
		return true;
	    }
	    return false;
	}

	/**
	 * Check if roles exists
	 * 
	 * @param array $roleTypes Types of roles
	 * @return boolean
	 */
	public function hasRoles($roleTypes) {
	    foreach ($roleTypes as $roleType) {
		if ($this->hasRole($roleType)) {
		    return true;
		}
		return false;
	    }
	}

    }
    