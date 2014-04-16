<?php

    namespace lahaina\libraries\navigation;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

use lahaina\framework\common\Lahaina;

    /**
     * Navigation
     *
     * @version 1.0.2
     * 
     * @author Jonathan Nessier
     */
    class Navigation {

	private $items = array();
	private $htmlTemplate = '<ul>{NAV_ITEMS}</ul>';

	/**
	 * Constructor
	 * 
	 * @param array $items
	 */
	public function __construct($items = array()) {
	    $this->items = $items;
	}

	/**
	 * Add item
	 * 
	 * @param \lahaina\libraries\navigation\Item $item Item of navigation
	 */
	public function add(Item $item) {
	    $this->items[] = $item;
	}

	/**
	 * Render navigation
	 * 
	 * @param \lahaina\framework\common\Lahaina $lahaina Lahaina framework base
	 * @param string $renderType Type or rendering (echo or type)
	 * @return string
	 */
	public function render(Lahaina $lahaina, $renderType = 'echo') {

	    if (count($this->items) > 0) {
		$items = '';
		foreach ($this->items as $item) {
		    $items .= $item->render($lahaina, null, 'return');
		}

		$rendered_output = str_replace(array('{NAV_ITEMS}'), array($items), $this->htmlTemplate);

		if ($renderType == 'echo') {
		    echo $rendered_output;
		} else {
		    return $rendered_output;
		}
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
    