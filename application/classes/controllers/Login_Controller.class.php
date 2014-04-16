<?php

    namespace application\controllers;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

use lahaina\framework\mvc\Controller;
use lahaina\framework\common\Lahaina;
use lahaina\libraries\message\Success;
use lahaina\libraries\message\Warning;

    /**
     * Login controller for user authentication
     *
     * @version 1.0.2
     * 
     * @author Jonathan Nessier
     */
    class Login_Controller extends Controller {

	/**
	 * @var Security
	 */
	private $_security;

	/**
	 * Constructor
	 * 
	 * @param lahaina\framework\common\Lahaina $lahaina Lahaina framework base
	 * @param lahaina\framework\common\Loader $loader Loader
	 */
	public function __construct(Lahaina $lahaina) {
	    parent::__construct($lahaina);

	    $this->_security = $this->_lahaina->getData('security');
	}

	/**
	 * Index action
	 */
	public function index() {
	    if ($this->_security->getUser()) {
		return $this->_redirect(URL);
	    } else {
		$this->_view->set('login', 'Login');
	    }
	}

	/**
	 * Authentication action
	 */
	public function auth() {
	    if ($this->_security->getUser()) {
		$this->_lahaina->session()->setFlash('message', new Success('Erfolgreich eingeloggt'));
		return $this->_redirect(URL);
	    } else {
		$this->_lahaina->setData('message', new Warning('Benutzername/Passwort ungültig'));
		return $this->index();
	    }
	}

	/**
	 * Logout action
	 */
	public function logout() {
	    $this->_lahaina->session()->remove('user');
	    $this->_lahaina->session()->setFlash('message', new Success('Erfolgreich ausgeloggt'));
	    return $this->_redirect(URL . '/login');
	}

    }
    