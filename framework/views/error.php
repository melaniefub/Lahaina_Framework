<?php

    if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

    $error = $this->_lahaina->getData('error');

    echo '<div class="message error">' . $error . '<br /><br />
        <small><i>Wir bitten Sie um Verständnis! Bei allfälligen 
        Fragen wenden Sie sich an den Administrator 
        (' . $this->_lahaina->config('app.admin.email') . ').</i></small></div>';
?>