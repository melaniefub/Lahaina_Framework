<?php

    namespace lahaina\libraries\navigation;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

    /**
     * Action item extends item class
     *
     * @version 1.0.2
     * 
     * @author Jonathan Nessier
     */
    class ActionItem extends Item {

	/**
	 * Constructor
	 * 
	 * @param string $title Title of navigation item
	 * @param string $controllerName Name of controller
	 * @param string $actionName Name of action
	 * @param string $id ID of entity
	 * @param string $cssClass Additional CSS class(es) 
	 * @param array $sub = Sub controllers of current item
	 */
	public function __construct($title, $controllerName, $actionName = null, $id = null, $cssClass = null, $sub = array()) {
	    $this->title = $title;
	    $this->url = URL . '/' . strtolower($controllerName) . ($actionName ? '/' . $actionName . ($id ? '/' . $id : '') : '');
	    $this->actionName = $actionName;

	    $this->controllerName = strtolower($controllerName);

	    $this->cssClass = $cssClass;
	    $this->sub = $sub;
	}

    }
    