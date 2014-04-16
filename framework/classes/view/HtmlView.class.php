<?php

    namespace lahaina\framework\view;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

use lahaina\framework\exception\FrameworkException;
use lahaina\framework\common\Lahaina;
use lahaina\framework\mvc\View;

    /**
     * View
     *
     * @version 1.0.2
     *
     * @author Jonathan Nessier
     */
    class HtmlView extends View {

	/**
	 * @var Lahaina
	 */
	protected $_lahaina;
	protected $_headerFile = 'header.php';
	protected $_footerFile = 'footer.php';
	protected $_title = '';

	/**
	 * Constructor
	 * 
	 * @param \lahaina\framework\common\Lahaina $lahaina Lahaina framework base
	 */
	public function __construct(Lahaina $lahaina) {
	    $this->_lahaina = $lahaina;
	}

	/**
	 * Set path and title of view
	 * 
	 * @param string $path Absolute or relativ path of view (without .php extension)
	 * @param string $title Title of view
	 */
	public function set($path, $title = '') {
	    if (strpos($path, '.php') === false) {
		$path = $path . '.php';
	    }
	    $this->_path = $path;
	    $this->_title = $title;
	}

	/**
	 * Get title of view
	 * 
	 * @return string Title of view
	 */
	public function getTitle() {
	    return $this->_title;
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

	    if (file_exists(TEMPLATE_PATH . '/' . $this->_headerFile)) {
		include (TEMPLATE_PATH . '/' . $this->_headerFile);
	    } else {
		throw new FrameworkException('Cannot find header template (' . TEMPLATE_PATH . '/' . $this->_headerFile . ')');
	    }

	    if (file_exists(PATH . '/application/views/' . strtolower($this->_path))) {
		include (PATH . '/application/views/' . strtolower($this->_path));
	    } elseif (file_exists(strtolower($this->_path))) {
		include (strtolower($this->_path));
	    } else {
		throw new FrameworkException('Cannot find view (' . $path . ')', 3);
	    }

	    if (file_exists(TEMPLATE_PATH . '/' . $this->_footerFile)) {
		include (TEMPLATE_PATH . '/' . $this->_footerFile);
	    } else {
		throw new ViewException('Cannot find footer template (' . TEMPLATE_PATH . '/' . $this->footerFile . ')');
	    }
	    ob_end_flush();
	}

    }
    