<?php

    namespace lahaina\framework\data;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

use lahaina\framework\exception\FrameworkException;
use lahaina\framework\data\Container;

    /**
     * Data collection list with optional specified entry type
     *
     * @version 1.0.2
     *
     * @author Jonathan Nessier FUB <jonathan.nessier@vtg.admin.ch>
     */
    class Collection implements \IteratorAggregate, \Countable {

	protected $_collection = array();
	protected $_entryType;

	/**
	 * Constructor
	 * 
	 * @param array $data Collection data as array
	 * @param entry $entryType Entry type as class name
	 * @throws FrameworkException
	 */
	public function __construct($data = array(), $entryType = null) {

	    if ($entryType) {
		$this->setEntryType($entryType);
	    }

	    if (!is_array($data) && !($data instanceof Container)) {
		throw new FrameworkException('Values must be an array or type of Container');
	    }

	    foreach ($data as $entry) {
		$this->add($entry);
	    }
	}

	/**
	 * Set entry type (only class name possible)
	 * 
	 * @param entry $entryType Entry type as class name
	 */
	public function setEntryType($entryType) {
	    $this->_entryType = $entryType;
	}

	/**
	 * Add entry to collection
	 * 
	 * @param mixed $entry Collection entry
	 */
	public function add($entry) {
	    if ($this->_entryType && !($entry instanceof $this->_entryType)) {
		throw new FrameworkException('Collection entry must be type of ' . $this->_entryType);
	    }
	    $this->_collection[] = $entry;
	}

	/**
	 * Fetch one collection entry by key or index
	 * 
	 * @param mixed $entry Collection entry key or index
	 */
	public function fetch($key) {
	    return isset($this->_collection[$key]) ? $this->_collection[$key] : null;
	}

	/**
	 * Output the collection data as a multidimensional array
	 *
	 * @return array
	 */
	public function toArray() {
	    return $this->_collection;
	}

	/**
	 * Get first collection entry
	 * 
	 * @return mixed
	 */
	public function first() {
	    return $this->count() > 0 ? $this->_collection[0] : null;
	}

	/**
	 * Get last collection entry
	 * 
	 * @return mixed
	 */
	public function last() {
	    return $this->count() > 0 ? end($this->_collection) : null;
	}

	/**
	 * Number of collection entries
	 * 
	 * @return integer
	 */
	public function count() {
	    return count($this->_collection);
	}

	/**
	 * Sort collection entries by entry key
	 * 
	 * @param mixed $sort Entry property key
	 * @param string $order Sort order (asc or desc)
	 * @return \lahaina\framework\data\Collection
	 */
	public function sort($sort, $order = 'asc') {

	    $sortColumn = array();
	    foreach ($this->_collection as $index => $entry) {
		$sortColumn[$index] = $entry->get($sort);
	    }

	    if ($order == 'asc') {
		$order = SORT_ASC;
	    } else {
		$order = SORT_DESC;
	    }
	    array_multisort($sortColumn, $order, $this->_collection);
	    return $this;
	}

	/**
	 * Slice collection
	 * 
	 * @param integer $offset Start index of slicing
	 * @param integer $length Number of entries
	 * @return \lahaina\framework\data\Collection
	 */
	public function slice($offset, $length) {
	    $this->_collection = array_slice($this->_collection, $offset, $length);
	    return $this;
	}

	/**
	 * Get limited collection
	 * 
	 * @param integer $offset Start index of limitation
	 * @param integer $limit Number of entries of limited collection
	 * @return \lahaina\framework\data\Collection
	 */
	public function limit($offset, $limit) {
	    return new Collection(array_slice($this->_collection, $offset, $limit), $this->_entryType);
	}

	/**
	 * Get an iterator, implements IteratorAggregate
	 * 
	 * @return ArrayIterator
	 */
	public function getIterator() {
	    return new \ArrayIterator($this->_collection);
	}

    }
    