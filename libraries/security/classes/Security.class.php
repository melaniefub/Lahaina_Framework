<?php

    namespace lahaina\libraries\security;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

use lahaina\framework\common\Lahaina;

    /**
     * Abstract security class
     *
     * @version 1.0.2
     *
     * @author Jonathan Nessier FUB <jonathan.nessier@vtg.admin.ch>
     */
    abstract class Security {

	/**
	 * @var User
	 */
	protected $_user;

	/**
	 * @var Lahaina
	 */
	protected $_lahaina;

	/**
	 * Constructor
	 * 
	 * @param \lahaina\libraries\core\Lahaina $lahaina Lahaina framework base
	 */
	public function __construct(Lahaina $lahaina) {

	    $this->_lahaina = $lahaina;

	    if ($this->_lahaina->session()->get('user')) {
		$user = $this->_lahaina->session()->get('user');
		$this->_user = $user;
	    } else {
		$this->auth();
	    }
	}

	/**
	 * Get authenticated user
	 * 
	 * @return User
	 */
	public function getUser() {
	    return $this->_user;
	}

	/**
	 * Check if user roles exists
	 * 
	 * @param array $roleTypes Types of roles
	 * @return boolean
	 */
	public function checkUserRoles($roleTypes = array()) {
	    if ($this->_user) {
		return $this->_user->hasRoles($roleTypes);
	    }
	    return false;
	}

	/**
	 * Check if user role exists
	 * 
	 * @param string $roleTypes Type of role
	 * @return boolean
	 */
	public function checkUserRole($roleType) {
	    if ($this->_user) {
		return $this->_user->hasRole($roleType);
	    }
	    return false;
	}

    }
    