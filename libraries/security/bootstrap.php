<?php

    if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

use lahaina\libraries\security\LoginSecurity;
use lahaina\libraries\security\RemoteSecurity;

if ($securityConfig->get('authenticationType') === 'remote') {
	$security = new RemoteSecurity($this->_lahaina);
    } else {
	$security = new LoginSecurity($this->_lahaina);
    }

    $this->_lahaina->setData('security', $security);
    