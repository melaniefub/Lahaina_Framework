<?php

    namespace application\models;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

use lahaina\framework\mvc\Model;

    /**
     * Role model
     * 
     * @version 1.0.2
     *
     * @author Jonathan Nessier
     */
    class Role_Model extends Model {

	const ID_COLUMN = 'role_id';
	const TABLE_NAME = 'role';
	const CONNECTION_NAME = null;

	public function users($find = true) {
	    return $this->_hasMany('User', $find);
	}

    }
    