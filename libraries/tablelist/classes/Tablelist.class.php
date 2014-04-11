<?php

    namespace lahaina\libraries\tablelist;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

    /**
     * Tablelist
     *
     * @version 1.0.2
     * 
     * @author Jonathan Nessier FUB <jonathan.nessier@vtg.admin.ch>
     * @author Melanie Rufer FUB <melanie.rufer@vtg.admin.ch>
     */
    class Tablelist {

	private $name;
	private $entities = array();
	private $columns;
	private $sort;
	private $order;
	private $page;
	private $cssClass;
	private $htmlTemplateTableHeader = '<table id="{NAME}" class="list {CSSCLASS}">';
	private $htmlTemplateTableHead = '{TITLE}';
	private $htmlTemplateTableData = '{CONTENT}';
	private $htmlTemplateTableFooter = '</table>{PAGINATION}';
	private $pagination;

	/**
	 * Constructor
	 * 
	 * @param array $entities Entities data
	 * @param array $columns Columns for tablelist
	 * @param \lahaina\libraries\tablelist\Config $tablelistConfig
	 */
	public function __construct($entities, $columns, Config $tablelistConfig) {
	    $this->entities = $entities;
	    $this->columns = $columns;

	    $this->cssClass = $tablelistConfig->get('cssClass');
	    $this->sort = $tablelistConfig->get('sort');
	    $this->order = $tablelistConfig->get('order');
	    $this->page = $tablelistConfig->get('page');
	    if ($tablelistConfig->exists('page') && $tablelistConfig->exists('limit') && $tablelistConfig->exists('entitiesTotal')) {
		$this->pagination = new Pagination($tablelistConfig);
	    }
	    $this->name = $tablelistConfig->get('name');
	}

	/**
	 * Render tablelist
	 * 
	 * @param string $renderType Type or rendering (echo or type)
	 * @return string
	 */
	public function render($renderType = 'echo') {

	    // Create table header        
	    $output = str_replace(array('{NAME}', '{CSSCLASS}'), array($this->name, $this->cssClass), $this->htmlTemplateTableHeader);

	    // Create table heads
	    $output .= '<tr>';
	    foreach ($this->columns as $column) {
		$output .= str_replace(array('{TITLE}'), array($column->renderTitle($this->name, $this->page, $this->sort, $this->order)), $this->htmlTemplateTableHead);
	    }
	    $output .= '</tr>';

	    // Create table data
	    $cssClass = 'even';
	    if ($this->entities->count() > 0) {
		foreach ($this->entities as $entity) {
		    $output .= '<tr class="' . $cssClass . '">';
		    foreach ($this->columns as $column) {
			$output .= str_replace('{CONTENT}', $column->renderContent($entity), $this->htmlTemplateTableData);
		    }
		    $output .= '</tr>';
		    $cssClass = ($cssClass == 'even' ? 'odd' : 'even');
		}
	    } else {
		$columnNumber = count($this->columns);
		$output .= '<tr class="' . $cssClass . '"><td colspan="' . $columnNumber . '">Keine Einträge vorhanden</td></tr>';
	    }

	    // Create table footer
	    if ($this->pagination) {
		$pagination = $this->pagination->render();
		$output .= str_replace('{PAGINATION}', $pagination, $this->htmlTemplateTableFooter);
	    }

	    if ($renderType == 'echo') {
		echo $output;
	    } else {
		return $output;
	    }
	}

	/**
	 * Set HTML template
	 *
	 * @param array $htmlTemplates HTML templates
	 */
	public function setHtmlTemplates(array $htmlTemplates) {
	    if (is_array($htmlTemplates)) {
		if (isset($htmlTemplates['header'])) {
		    $this->htmlTemplateTableHeader = $htmlTemplates['header'];
		}
		if (isset($htmlTemplates['tableHead'])) {
		    $this->htmlTemplateTableHead = $htmlTemplates['tableHead'];
		}
		if (isset($htmlTemplates['tableData'])) {
		    $this->htmlTemplateTableData = $htmlTemplates['tableData'];
		}
		if (isset($htmlTemplates['footer'])) {
		    $this->htmlTemplateTableFooter = $htmlTemplates['footer'];
		}
	    } else {
		throw new LibraryException('HTML templates for tablelist mus be in an associative array');
	    }
	}

    }
    