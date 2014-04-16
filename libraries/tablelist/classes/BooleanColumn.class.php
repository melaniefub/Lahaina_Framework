<?php

    namespace lahaina\libraries\tablelist;

    /**
     * Boolean column extends column class
     *
     * @version 1.0.2
     * 
     * @author Melanie Rufer
     */
    class BooleanColumn extends Column {

	/**
	 * Render content of column
	 * 
	 * @param mixed $row Row data
	 * @return string
	 */
	public function renderContent($row) {
	    if ($row->get($this->attribute)) {
		$value = 'Ja';
	    } else {
		$value = 'Nein';
	    }
	    return str_replace(array('{VALUE}'), array($value), $this->htmlTemplateColumnContent);
	}

    }
    