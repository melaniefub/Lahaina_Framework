<?php

namespace lahaina\libraries\tablelist;

if (!defined('PATH'))
    exit('Kein direkter Skriptzugriff erlaubt!');

/**
 * Redirect column extends column class
 *
 * @version 1.0.0
 * 
 * @author Melanie Rufer
 */
class RedirectColumn extends Column {

    private $url;
    private $onclick;
    private $value;
    protected $htmlTemplateColumnContent = '<td class="action"><a class="{CSS_CLASS}" href="{URL}" onclick="{ONCLICK}">{VALUE}</a></td>';
    protected $htmlTemplateColumnContentNull = '<td class="action">{URL}</td>';

    /**
     * Constructor
     * 
     * @param string $url URL of reference
     * @param string $cssClass Additional CSS class(es)
     * @param string $onclick Javascript function for onclick event
     * @param string $title Column title
     * @param string $attribute Name of attribute
     * @param boolean $sorting Column sorting
     */
    public function __construct($url, $cssClass = '', $onclick = '', $title = '', $attribute = '', $sorting = false) {
        $this->url = $url;
        $this->cssClass = $cssClass;
        $this->onclick = $onclick;
        $this->title = $title;
        $this->attribute = $attribute;
        $this->sorting = $sorting;
    }

    /**
     * Render content of redirect column
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
            if ($row->exists($this->url)) {
                $url = $row->get($this->url);
                if ($url === '') {
                    return str_replace(array('{URL}'), $url, $this->htmlTemplateColumnContentNull);
                }
                return str_replace(array('{URL}', '{VALUE}', '{CSS_CLASS}', '{ONCLICK}'), array($url, $content, $this->cssClass, $this->onclick), $this->htmlTemplateColumnContent);
            }
        }
    }

}
