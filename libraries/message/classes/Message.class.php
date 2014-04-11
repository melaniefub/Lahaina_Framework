<?php

    namespace lahaina\libraries\message;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

    /**
     * Message 
     *
     * @version 1.0.2
     * 
     * @author Jonathan Nessier FUB <jonathan.nessier@vtg.admin.ch>
     */
    abstract class Message {

	protected $text = '';
	protected $html_output = '<div class="message {TYPE}">{TEXT}</div>';
	protected $type = '';

	/**
	 * Constructor
	 * 
	 * @param string $text Text for message
	 * @param string $html_output Template for HTML output
	 */
	public function __construct($text, $html_output = null) {
	    $this->text = $text;

	    if ($html_output) {
		$this->html_output = $html_output;
	    }
	}

	/**
	 * Render message
	 * 
	 * @param string $renderType Type or rendering (echo or type)
	 * @return string
	 */
	public function render($renderType = 'echo') {

	    $rendered_output = str_replace(array('{TYPE}', '{TEXT}'), array($this->type, $this->text), $this->html_output);

	    if ($renderType == 'echo') {
		echo $rendered_output;
	    } else {
		return $rendered_output;
	    }
	}

    }
    