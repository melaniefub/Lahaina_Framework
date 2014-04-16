<?php

    namespace lahaina\libraries\security;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

use lahaina\framework\common\Lahaina;
use lahaina\framework\http\PostRequest;

    /**
     * Login security for single-sign on extends security class
     *
     * @version 1.0.2
     *
     * @author Jonathan Nessier
     */
    class LoginSecurity extends Security {

	/**
	 * Constructor
	 * 
	 * @param \lahaina\libraries\core\Lahaina $lahaina Lahaina framework base
	 */
	public function __construct(Lahaina $lahaina) {

	    $this->_lahaina = $lahaina;

	    if ($this->_lahaina->session()->exists('user')) {
		$this->_user = $this->_lahaina->session()->get('user');
	    } else {
		$this->_auth();
	    }
	}

	/**
	 * Authenticate user with given username and password
	 */
	protected function _auth() {

	    if ($this->_lahaina->request() instanceof PostRequest) {

		$this->_lahaina->database()->prepare('SELECT u.username, r.role_type FROM user as u 
                                                    LEFT JOIN role as r ON u.role_id = r.role_id
                                                    WHERE u.username = :username AND password = :password
                                                    LIMIT 1', true);
		$this->_lahaina->database()->bindValue(':username', $this->_lahaina->request()->getData('post', 'username'));
		$this->_lahaina->database()->bindValue(':password', sha1($this->_lahaina->request()->getData('post', 'password')));

		$result = $this->_lahaina->database()->execute()->fetchAll();

		if ($result->count() > 0) {
		    $user = $result->first();
		    $this->_user = new LoginUser($user->get('username'), $user->get('role_type'));
		    $this->_lahaina->session()->set('user', $this->_user);

		    $this->_lahaina->logger()->info('User (' . $this->_user->getUsername() . ') is authenticated', $this);

		    return true;
		}
	    }
	    $this->_lahaina->logger()->info('User authentication failed', $this);
	    return false;
	}

    }
    