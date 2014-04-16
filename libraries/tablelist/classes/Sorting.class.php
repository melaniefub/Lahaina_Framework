<?php

    namespace lahaina\libraries\tablelist;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

    /**
     * Sorting
     *
     * @version 1.0.2
     * 
     * @author Jonathan Nessier
     * @author Melanie Rufer
     */
    class Sorting {

	private $name;
	private $page;
	private $sort;
	private $order;

	/**
	 * Constructor
	 * 
	 * @param string $name Name of tablelist
	 * @param integer $page Number of page
	 * @param string $sort Column to sort
	 * @param string $order Ordering of column
	 */
	public function __construct($name, $page, $sort, $order) {
	    $this->name = $name;
	    if (is_array($sort) && count($sort) == 2) {
		$this->sort = $sort[0] . '.' . $sort[1];
	    } else {
		$this->sort = $sort;
	    }

	    $this->order = $order;
	    $this->page = $page;
	}

	/**
	 * Get column to sort
	 * 
	 * @return string
	 */
	public function getSort() {
	    return $this->sort;
	}

	/**
	 * Get ordering of column
	 * 
	 * @return string
	 */
	public function getOrder() {
	    return $this->order;
	}

	/**
	 * Get generated relative URL for column title
	 * 
	 * @return string
	 */
	public function getUrl() {
	    $order = $this->order == 'asc' ? 'desc' : 'asc';
	    return '?page=' . (string) $this->page . '&name=' . $this->name . '&sort=' . $this->sort . '&order=' . $order;
	}

    }
    