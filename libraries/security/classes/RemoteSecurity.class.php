<?php

    namespace lahaina\libraries\security;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

    /**
     * Remote security for single-sign on extends security class
     *
     * @version 1.0.2
     *
     * @author Jonathan Nessier
     */
    class RemoteSecurity extends Security {

	/**
	 * Authenticate user with remote username (Single-Sign On)
	 */
	protected function auth() {

	    $this->_lahaina->database()->prepare('SELECT u.username, r.role_type FROM user as u 
	                                        LEFT JOIN role as r ON u.role_id = r.role_id
	                                        WHERE u.username = :username
	                                        LIMIT 1', true);
	    $this->_lahaina->database()->bindValue(':username', $this->_getRemoteUsername());

	    $result = $this->_lahaina->database()->execute()->fetchAll();

	    if ($result->count() > 0) {
		$user = $result->first();
		$this->_user = new RemoteUser($user->get('username'), $user->get('role_type'), $this->_getRemoteDomain());
		$this->_lahaina->session()->set('user', $this->_user);

		$this->_lahaina->logger()->info('User (' . $this->_user->getUsernameWithDomain() . ') is authenticated', $this);
	    }
	}

	/**
	 * Get username of remote user
	 * 
	 * @return string
	 */
	private function _getRemoteUsername() {
	    $remoteUser = $this->_getRemoteUser();
	    if (isset($remoteUser[1])) {
		return $remoteUser[1];
	    }
	}

	/**
	 * Get domain of remote user
	 * 
	 * @return string
	 */
	private function _getRemoteDomain() {
	    $remoteUser = $this->_getRemoteUser();
	    if (isset($remoteUser[0])) {
		return $remoteUser[0];
	    }
	}

	/**
	 * Get remote user
	 * 
	 * @return array
	 */
	private function _getRemoteUser() {
	    if (isset($_SERVER['REMOTE_USER'])) {
		if (strpos($_SERVER['REMOTE_USER'], '@') !== false) {
		    $remoteUser = explode('@', $_SERVER['REMOTE_USER']);
		} else {
		    $remoteUser = explode('\\', $_SERVER['REMOTE_USER']);
		}
		return list($domain, $remoteUsername) = $remoteUser;
	    }
	}

    }
    