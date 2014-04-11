<?php

    namespace lahaina\libraries\tablelist;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

    /**
     * Pagination
     *
     * @version 1.0.2
     * 
     * @author Jonathan Nessier FUB <jonathan.nessier@vtg.admin.ch>
     * @author Melanie Rufer FUB <melanie.rufer@vtg.admin.ch>
     */
    class Pagination {

	private $entitiesPerPage;
	private $entitiesTotal;
	private $currentPage;
	private $numPages;
	private $order;
	private $sort;
	private $name;

	/**
	 * Constructor
	 * 
	 * @param \lahaina\framework\Config $tablelistConfig Configuration of tablelist
	 */
	public function __construct(Config $tablelistConfig) {

	    $this->currentPage = (int) $tablelistConfig->get('page');
	    $this->entitiesPerPage = (int) $tablelistConfig->get('limit');
	    $this->entitiesTotal = (int) $tablelistConfig->get('entitiesTotal');
	    $this->order = $tablelistConfig->get('order');
	    $this->sort = $tablelistConfig->get('sort');
	    $this->name = $tablelistConfig->get('name');

	    if ($this->entitiesTotal > $this->entitiesPerPage) {
		$this->numPages = ceil($this->entitiesTotal / $this->entitiesPerPage);
	    } else {
		$this->numPages = 1;
	    }
	}

	/**
	 * Render pagination
	 * 
	 * @param string $renderType Type or rendering (echo or type)
	 * @return string
	 */
	public function render($renderType = 'return') {

	    if ($this->numPages > 1) {
		$output = '<ul class="pagination">';
		for ($i = 1; $i <= $this->numPages; $i++) {
		    $cssClass = (string) $i == $this->currentPage ? 'current' : '';
		    $output .= '<li><a class="' . $cssClass . '" href="?page=' . (string) $i . '&name=' . $this->name . '&sort=' . $this->sort . '&order=' . $this->order . '">' . (string) ($i) . '</a></li>';
		}
		$output .= '</ul>';
	    } else {
		$output = '';
	    }

	    if ($renderType == 'echo') {
		echo $output;
	    } else {
		return $output;
	    }
	}

    }
    