<?php

    namespace lahaina\libraries\tablelist;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

    /**
     * Action column extends column class
     *
     * @version 1.0.0
     * 
     * @author Jonathan Nessier FUB <jonathan.nessier@vtg.admin.ch>
     */
    class ActionColumn extends Column {

	private $url;
	private $idName;
	private $onclick;
	private $value;
	protected $htmlTemplateColumnContent = '<td class="action"><a class="{CSS_CLASS}" href="{URL}" onclick="{ONCLICK}">{VALUE}</a></td>';

	/**
	 * Constructor
	 * 
	 * @param string $url URL of action
	 * @param string $idName ID name
	 * @param string $cssClass Additional CSS class(es)
	 * @param string $onclick Javascript function for onclick event
	 * @param string $title Column title
	 * @param string $attribute Name of attribute
	 * @param boolean $sorting Column sorting
	 */
	public function __construct($url, $idName = '', $cssClass = '', $onclick = '', $title = '', $attribute = '', $sorting = false) {
	    $this->url = $url;
	    $this->cssClass = $cssClass;
	    $this->onclick = $onclick;
	    $this->title = $title;
	    $this->attribute = $attribute;
	    $this->sorting = $sorting;
	    $this->idName = $idName;
	}

	/**
	 * Render content of action column
	 * 
	 * @param mixed $row Row data
	 * @return string
	 */
	public function renderContent($row) {

	    if ($row->exists($this->attribute)) {
		$content = $row->get($this->attribute);
	    } else {
		$content = $this->attribute;
	    }

	    if ($this->url) {
		$url = '#';
		if ($row->exists($this->idName)) {
		    $url = str_replace(array('{id}'), array($row->get($this->idName)), $this->url);
		}
		return str_replace(array('{URL}', '{VALUE}', '{CSS_CLASS}', '{ONCLICK}'), array($url, $content, $this->cssClass, $this->onclick), $this->htmlTemplateColumnContent);
	    } else {
		return '';
	    }
	}

    }
    