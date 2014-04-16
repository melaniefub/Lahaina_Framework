<?php

    namespace lahaina\libraries\message;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

use lahaina\framework\common\Lahaina;

    /**
     * Helper for message library
     *
     * @version 1.0.2
     * 
     * @author Jonathan Nessier
     */
    class Helper {

	/**
	 * Render message
	 * 
	 * @param \lahaina\framework\common\Lahaina $lahaina Lahaina framework base
	 */
	public function renderMessage(Lahaina $lahaina) {

	    $session = $lahaina->session();

	    if ($session->getFlash('message') && $session->getFlash('message') instanceof Message) {
		$session->getFlash('message')->render();
	    } elseif ($lahaina->getData('message') && $lahaina->getData('message') instanceof Message) {
		$lahaina->getData('message')->render();
	    }
	}

    }
    