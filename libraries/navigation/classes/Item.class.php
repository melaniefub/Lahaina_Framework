<?php

    namespace lahaina\libraries\navigation;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

use lahaina\framework\common\Lahaina;

    /**
     * Navigation item
     *
     * @version 1.0.2
     * 
     * @author Jonathan Nessier
     */
    abstract class Item {

	protected $title;
	protected $url;
	protected $htmlTemplate = '<li class="{CLASS}"><a href="{HREF}">{TITLE}</a></li>';
	protected $sub = array();
	protected $controllerName = '';
	protected $actionName = '';

	/**
	 * Check navigation item whether it currently is active
	 *
	 * @param \lahaina\framework\common\Lahaina $lahaina Lahaina framework base
	 * @return boolean
	 */
	private function _isCurrent(Lahaina $lahaina) {

	    if ($lahaina->hasCurrentController($this->controllerName)) {
		if (!$this->actionName || $lahaina->hasCurrentAction($this->actionName)) {
		    return true;
		}
	    }
	    if (is_array($this->sub)) {
		foreach ($this->sub as $controller) {
		    if ($lahaina->hasCurrentController($controller)) {
			return true;
		    }
		}
	    }
	    return false;
	}

	/**
	 * Render navigation item
	 * 
	 * @param lahaina\framework\common\Lahaina $lahaina Lahaina framework base
	 * @param string $renderType Type or rendering (echo or type)
	 * @return string
	 */
	public function render($lahaina, $renderType = 'echo') {

	    if ($this->_isCurrent($lahaina)) {
		$this->cssClass .= ' current';
	    }

	    $rendered_output = str_replace(array('{HREF}', '{TITLE}', '{CLASS}'), array($this->url, $this->title, $this->cssClass), $this->htmlTemplate);

	    if ($renderType == 'echo') {
		echo $rendered_output;
	    } else {
		return $rendered_output;
	    }
	}

	/**
	 * Set HTML template
	 *
	 * @param string $htmlTemplate HTML template
	 */
	public function setHtmlTemplate($htmlTemplate) {
	    if ($htmlTemplate) {
		$this->htmlTemplate = $htmlTemplate;
	    }
	}

    }
    