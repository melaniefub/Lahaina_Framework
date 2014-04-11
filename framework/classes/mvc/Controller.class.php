<?php

    namespace lahaina\framework\mvc;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

use lahaina\framework\mvc\View;
use lahaina\framework\common\Lahaina;
use lahaina\framework\view\HtmlView;

    /**
     * Controller
     *
     * @version 1.0.2
     * 
     * @author Jonathan Nessier FUB <jonathan.nessier@vtg.admin.ch>
     */
    class Controller {

	/**
	 * @var Lahaina 
	 */
	protected $_lahaina;

	/**
	 * @var View
	 */
	protected $_view;
	protected $_render = true;

	/**
	 * Constructor
	 * 
	 * @param \lahaina\framework\common\Lahaina $lahaina Lahaina framework base
	 */
	public function __construct(Lahaina $lahaina) {
	    $this->_lahaina = $lahaina;
	    $this->_lahaina->logger()->info('Create controller', $this);

	    $this->_view = new HtmlView($lahaina);
	}

	/**
	 * Error action
	 *
	 * @param string $message Message
	 */
	public function error($message) {
	    $this->_lahaina->setCurrentAction('error');
	    $this->_lahaina->setData('error', $message);
	    $this->_view->set(PATH . '/framework/views/error');
	}

	/**
	 * Page not found action
	 */
	public function pageNotFound() {
	    $this->error('<b>[Die aufgerufene Seite existiert nicht]</b><br />
                Bitte überprüfen Sie den Link oder die eingegebene URL in ihrem Browser.');
	}

	/**
	 * No access action
	 */
	public function noAccess() {
	    $this->error('<b>[Sie haben keine Berechtigung]</b><br />
                Sie haben die falsche oder keine Rolle um auf die entsprechende Seite gelangen zu können.');
	}

	/**
	 * Destructor, rendering of definied view before the class will be destroyed
	 */
	public function __destruct() {
	    if ($this->_render && $this->_view && $this->_view->getPath()) {
		$this->_view->render($this->_lahaina);
	    }
	}

	/**
	 * Redirect to a definied internal or external url
	 *
	 * @param string $url URL
	 */
	protected function _redirect($url) {
	    $this->_render = false;
	    header('Location:' . $url);
	}

	/**
	 * Route to a controller/action
	 *
	 * @param string $controllerName Name of controller
	 * @param string $actionName Name of action
	 * @param mixed $id Identifier
	 */
	protected function _route($controllerName, $actionName = null, $id = null) {
	    $actionUri = '';
	    if ($actionName) {
		$actionUri = '/' . strtolower($actionName);
	    }
	    $idUri = '';
	    if ($id) {
		$idUri = '/' . $id;
	    }

	    $controllerUri = '/' . str_replace('_controller', '', strtolower($controllerName));
	    $this->_redirect(URL . $controllerUri . $actionUri . $idUri);
	}

    }
    