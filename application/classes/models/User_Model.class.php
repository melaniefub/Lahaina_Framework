<?php

    namespace application\models;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

use lahaina\framework\mvc\Model;
use lahaina\libraries\validation\Validator;

    /**
     * User model
     * 
     * @version 1.0.2
     *
     * @author Jonathan Nessier
     */
    class User_Model extends Model {

	const ID_COLUMN = 'user_id';
	const TABLE_NAME = 'user';
	const CONNECTION_NAME = null;

	public function getFullUsername() {
	    return $this->username . ' (' . $this->email . ')';
	}

	public $fullUsername = '';

	public function update($data = array()) {
	    parent::update($data);

	    if ($this->exists('username') && $this->exists('email')) {
		$this->fullUsername = $this->username . ' (' . $this->email . ')';
	    }
	}

	public function save() {
	    if ($this->password != '') {
		$this->password = sha1($this->password);
	    } else {
		$this->remove('password');
	    }
	    $this->remove('password2');

	    parent::save();
	}

	public function role($find = true) {
	    return $this->_belongsTo('Role', 'role_id', $find);
	}

	public function findAllUsers() {
	    return $this->_orm->factory('user')->findAll();
	}

	public function validate() {
	    $validator = new Validator($this->getData());
	    $validator
		    ->required('Es wird ein Benutzername benötigt')
		    ->startsWith('U', 'Der Benutzername muss mit dem Buchstaben U beginnen')
		    ->length(9, 'Der Benutzername muss 9 Zeichen lang sein')
		    ->set('username', 'Benutzername');
	    $validator
		    ->required('Es wird eine Rolle benötigt')
		    ->set('role_id', 'Rolle');
	    $validator
		    ->required('Es wird eine E-Mailadresse benötigt')
		    ->email('Die E-Mailadresse hat ein ungültiges Format')
		    ->set('email', 'Email');

	    if ($this->isNew()) {
		$validator
			->required('Es wird ein Passwort benötigt')
			->set('password', 'Passwort');
		$validator
			->required('Es wird ein Passwort (wiederholen) benötigt')
			->set('password2', 'Passwort (wiederholen)');
	    }

	    if ($this->password != '') {
		$validator
			->minlength(6, 'Das Passwort muss mindestens 6 Zeichen lang sein')
			->preg_match('/^\S*(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[\W])\S*$/', 'Das Passwort muss aus Zahlen, Buchstaben und Sonderzeichen bestehen')
			->set('password', 'Passwort');
		$validator
			->minlength(6, 'Das Passwort (wiederholen) muss mindestens 6 Zeichen lang sein')
			->preg_match('/^\S*(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[\W])\S*$/', 'Das Passwort (wiederholen) muss aus Zahlen, Buchstaben und Sonderzeichen bestehen')
			->set('password2', 'Passwort (wiederholen)');
		$validator
			->matches('password', 'Passwort (wiederholen)', 'Das Passwort (wiederholen) stimmt nicht überein')
			->set('password2', 'Passwort');
	    }

	    return $validator->validate();
	}

    }
    