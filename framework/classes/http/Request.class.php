<?php

    namespace lahaina\framework\http;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

use lahaina\framework\exception\FrameworkException;
use lahaina\framework\data\Container;

    /**
     * Http request
     *
     * @version 1.0.2
     * 
     * @author Jonathan Nessier
     */
    class Request {

	/**
	 * @var Uri
	 */
	protected $_uri;

	/**
	 * @var Data
	 */
	protected $_get;

	/**
	 * Constructor
	 *
	 * @param array $data GET data
	 * @param array $uri URI of called page (Controller/View/ID)
	 */
	public function __construct($get = array(), $uri = array()) {
	    $this->_uri = new Uri($uri);
	    $this->_get = new Container($get);
	}

	/**
	 * Get part of the URI with given index
	 *
	 * @param integer $index Index
	 *
	 * @return string
	 */
	public function getUri($index = null) {
	    return ($index ? $this->_uri->get($index) : $this->_uri);
	}

	/**
	 * Get request data of given method
	 *
	 * @param string $dataType GET, POST or FILES method
	 * @param string $name Name of value
	 * @param boolean $convert Convert all applicable characters to HTML entities
	 * @return \lahaina\framework\data\Container
	 * @throws FrameworkException
	 */
	public function getData($dataType, $name = null, $convert = false) {

	    $dataType = '_' . strtolower($dataType);
	    if (isset($this->$dataType)) {
		$methodRequest = $this->$dataType;
	    } else {
		throw new FrameworkException(strtoupper($dataType) . ' data of request not found');
	    }

	    if ($methodRequest->exists($name)) {
		$data = $methodRequest->get($name);
	    } elseif ($name != null) {
		return null;
	    } else {
		$data = $methodRequest;
	    }

	    if ($convert) {
		return $this->convertToHtmlEntities($data);
	    }
	    return $data;
	}

	/**
	 * Convert all applicable characters of data to HTML entities
	 * 
	 * @param mixed $data
	 * @return mixed
	 */
	private function convertToHtmlEntities($data) {
	    if ($data instanceof Container) {
		$data = $data->toArray();
		array_walk_recursive($data, function (&$value) {
		    $value = htmlentities($value);
		});
		return new Container($data);
	    } else {
		return htmlentities($data);
	    }
	}

    }
    