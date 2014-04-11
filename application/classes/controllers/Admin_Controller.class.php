<?php

    namespace application\controllers;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

use lahaina\framework\mvc\Controller;
use lahaina\framework\common\Lahaina;

    /**
     * Admin controller
     *
     * @version 1.0.2
     *
     * @author Jonathan Nessier FUB <jonathan.nessier@vtg.admin.ch>
     */
    class Admin_Controller extends Controller {

	/**
	 * Constructor
	 * 
	 * @param lahaina\framework\common\Lahaina $lahaina Lahaina framework base
	 */
	public function __construct(Lahaina $lahaina) {
	    parent::__construct($lahaina);

	    $security = $this->_lahaina->getData('security');
	    if (!$security->checkUserRole('ADMIN')) {
		$this->noAccess();
		exit();
	    }
	}

	public function index() {
	    $this->_route('admin/user');
	}

    }
    