<?php

    namespace lahaina\framework\mvc;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

use lahaina\framework\exception\FrameworkException;
use lahaina\framework\common\Lahaina;

    /**
     * View
     *
     * @version 1.0.2
     *
     * @author Jonathan Nessier
     */
    class View {

	/**
	 * @var Lahaina
	 */
	protected $_lahaina;
	protected $_path = '';

	/**
	 * Constructor
	 * 
	 * @param \lahaina\framework\common\Lahaina $lahaina Lahaina framework base
	 */
	public function __construct(Lahaina $lahaina) {
	    $this->_lahaina = $lahaina;
	}

	/**
	 * Set path of view
	 * 
	 * @param string $path Absolute or relativ path of view (without .php extension)
	 */
	public function set($path) {
	    if (strpos($path, '.php') === false) {
		$path = $path . '.php';
	    }
	    $this->_path = $path;
	}

	/**
	 * Get path of view
	 * 
	 * @return string Path of view
	 */
	public function getPath() {
	    return $this->_path;
	}

	/**
	 * Render view 
	 * 
	 * @param \lahaina\framework\common\Lahaina $lahaina Lahaina framework base
	 * @throws FrameworkException
	 */
	public function render() {
	    $this->_lahaina->logger()->info('Render view (' . $this->_path . ')', $this);

	    ob_start();

	    if (file_exists(PATH . '/application/views/' . strtolower($this->_path))) {
		include (PATH . '/application/views/' . strtolower($this->_path));
	    } elseif (file_exists(strtolower($this->_path))) {
		include (strtolower($this->_path));
	    } else {
		throw new FrameworkException('Cannot find view (' . $this->_path . ')', 3);
	    }

	    ob_end_flush();
	}

    }
    