<?php

    namespace lahaina\libraries\tablelist;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

    /**
     * Column
     *
     * @version 1.0.2
     * 
     * @author Jonathan Nessier
     * @author Melanie Rufer
     */
    class Column {

	protected $title;
	protected $attribute;
	protected $sortingAttribute;
	protected $htmlTemplateColumnTitle = '<th>{TITLE}</th>';
	protected $htmlTemplateColumnTitleSorting = '<th class="sort"><a class="{CSS_CLASS}" href="{SORTING_URL}">{TITLE}</a></th>';
	protected $htmlTemplateColumnContent = '<td>{VALUE}</td>';

	/**
	 * Constructor
	 * 
	 * @param string $title Column title
	 * @param string $attribute Name of attribute
	 * @param string $sortingAttribute Name of attribute for sorting
	 */
	public function __construct($title, $attribute, $sortingAttribute = null) {
	    $this->title = $title;
	    $this->attribute = $attribute;
	    if ($sortingAttribute === true) {
		$this->sortingAttribute = $this->attribute;
	    } else {
		$this->sortingAttribute = $sortingAttribute;
	    }
	}

	/**
	 * Render title of column
	 * 
	 * @param integer $page Number of page
	 * @param string $sort Attribute which should be sorted
	 * @param string $order Ordering of attribute
	 * @return string
	 */
	public function renderTitle($name = null, $page = null, $sort = null, $order = null) {
	    if ($this->sortingAttribute) {
		if ($sort === $this->sortingAttribute) {
		    $cssClass = strtolower($order);
		} else {
		    $cssClass = '';
		    $sort = $this->sortingAttribute;
		}

		$sorting = new Sorting($name, $page, $sort, $order);
		return str_replace(array('{TITLE}', '{SORTING_URL}', '{CSS_CLASS}'), array($this->title, $sorting->getUrl(), $cssClass), $this->htmlTemplateColumnTitleSorting);
	    }
	    return str_replace(array('{TITLE}'), array($this->title), $this->htmlTemplateColumnTitle);
	}

	/**
	 * Render content of column
	 * 
	 * @param mixed $row Row data
	 * @return string
	 */
	public function renderContent($row) {
	    if (is_array($this->attribute) && count($this->attribute) == 2) {
		if (method_exists($row, $this->attribute[0])) {
		    $content = $row->{$this->attribute[0]}()->{$this->attribute[1]};
		}
	    } elseif (is_string($this->attribute) && method_exists($row, $this->attribute)) {
		$content = $row->{$this->attribute}();
	    } elseif (is_string($this->attribute)) {
		$content = $row->get($this->attribute);
	    } else {
		throw new LibraryException('Unkown attribute ' . var_dump($this->attribute));
	    }
	    return str_replace(array('{VALUE}'), array($content), $this->htmlTemplateColumnContent);
	}

	/**
	 * Set HTML template
	 *
	 * @param array $htmlTemplates HTML templates
	 */
	public function setHtmlTemplates(array $htmlTemplates) {
	    if ($htmlTemplates) {
		if (isset($htmlTemplates['columnTitle'])) {
		    $this->htmlTemplateColumnTitle = $htmlTemplates['columnTitle'];
		}
		if (isset($htmlTemplates['columnTitleSorting'])) {
		    $this->htmlTemplateColumnTitleSorting = $htmlTemplates['columnTitleSorting'];
		}
		if (isset($htmlTemplates['columnContent'])) {
		    $this->htmlTemplateColumnContent = $htmlTemplates['columnContent'];
		}
	    }
	}

    }
    