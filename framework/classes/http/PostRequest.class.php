<?php

    namespace lahaina\framework\http;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

use lahaina\framework\data\Container;

    /**
     * Http post request
     *
     * @version 1.0.2
     *
     * @author Jonathan Nessier
     */
    class PostRequest extends Request {

	/**
	 * @var Container
	 */
	protected $_post;

	/**
	 * @var Container
	 */
	protected $_files;

	/**
	 * Constructor
	 *
	 * @param array $get GET data
	 * @param array $post POST data
	 * @param array $files FILES data
	 */
	public function __construct($get = array(), $post = array(), $files = array(), $uri = array()) {

	    parent::__construct($get, $uri);

	    $this->_files = new Container($files);
	    $this->_post = new Container($post);
	}

    }
    