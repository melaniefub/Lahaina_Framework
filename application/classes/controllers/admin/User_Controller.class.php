<?php

    namespace application\controllers\admin;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

use lahaina\libraries\message\Error;
use lahaina\libraries\message\Warning;
use lahaina\libraries\message\Success;
use lahaina\libraries\validation\ValidationMessage;
use lahaina\libraries\validation\ValidationException;
use lahaina\libraries\tablelist\ActionColumn;
use lahaina\libraries\tablelist\Column;
use lahaina\libraries\tablelist\Config as TablelistConfig;
use lahaina\libraries\tablelist\Tablelist;
use application\models\User_Model;
use application\controllers\Admin_Controller;

    /**
     * User controller
     *
     * @author Jonathan Nessier
     */
    class User_Controller extends Admin_Controller {

	/**
	 * Index action
	 */
	public function index() {

	    // Get number of users
	    $usersTotal = $this->_lahaina->orm()->factory('user')->count();

	    // Create configuriation for tablelist
	    $tblConfig = new TablelistConfig('userList', 'email', 'asc', 1, 10, $usersTotal, 'long', $this->_lahaina);

	    // Create columns for tablelist
	    $tablelistColumns = array(
		new Column('Benutzername', 'getFullUsername', true),
		new Column('E-Mailadresse', 'email', true),
		new Column('Rolle', 'role.name', true),
		new ActionColumn(URL . '/admin/user/edit/{id}', 'user_id', 'icon edit'),
		new ActionColumn(URL . '/admin/user/delete/{id}', 'user_id', 'confirm icon delete', 'javascript:$(\'.dialog-confirm.delete-user\').dialog(\'open\');'));

	    // Get selected users for tablelist
	    $user_repo = new User_Model($this->_lahaina);
	    $users = $user_repo->findAllUsers();

	    $users->sort($tblConfig->get('sort'), $tblConfig->get('order'));
	    $users->slice($tblConfig->get('offset'), $tblConfig->get('limit'));

	    // Create tablelist
	    $tablelist = new Tablelist($users, $tablelistColumns, $tblConfig);

	    // Set tablelist to lahaina data
	    $this->_lahaina->setData('tablelist', $tablelist);

	    // Set view 
	    $this->_view->set('admin/user/list', 'Benutzerverwaltung &raquo; Übersicht');
	}

	/**
	 * Create user action
	 */
	public function create() {

	    // Set name of current action (needed for navigation)
	    $this->_lahaina->setCurrentAction('create');

	    // Get roles
	    $roles = $this->_lahaina->orm()->factory('role')->findAll();

	    // Set roles to lahaina data
	    $this->_lahaina->setData('roles', $roles);

	    // Check wether user data is set
	    if (!$this->_lahaina->getData()->exists('user')) {
		$this->_lahaina->setData('user', array());
	    }

	    // Set view
	    $this->_view->set('admin/user/create', 'Benutzerverwaltung &raquo; Benutzer erstellen');
	}

	/**
	 * Edit user action
	 *
	 * @param integer $id ID of user
	 */
	public function edit($id = null) {

	    // Set name of current action (needed for navigation)
	    $this->_lahaina->setCurrentAction('index');

	    // Get roles
	    $roles = $this->_lahaina->orm()->factory('role')->findAll();

	    // Set roles to lahaina data
	    $this->_lahaina->setData('roles', $roles);

	    // Check if ID of user is not null
	    if ($id) {

		// Get user data by ID and set to lahaina data
		$user = $this->_lahaina->orm()->factory('user')->findOne($id);

		if ($user != null) {
		    $this->_lahaina->setData('user', $user);
		}
	    }

	    // Check if lahaina data has user data
	    if ($this->_lahaina->getData()->exists('user')) {

		// Set view
		$this->_view->set('admin/user/edit', 'Benutzerverwaltung &raquo; Benutzer bearbeiten');
	    } else {

		// Load user not found action
		return $this->userNotFound($id);
	    }
	}

	/**
	 * Delete user action
	 *
	 * @param integer $id ID of user
	 */
	public function delete($id) {

	    // Set name of current action (needed for navigation)
	    $this->_lahaina->setCurrentAction('index');

	    // Get session
	    $session = $this->_lahaina->session();

	    try {

		// Delete user
		$user = $this->_lahaina->orm()->factory('user')->findOne($id);
		$result = $user->delete();

		// Check if user is deleted
		if ($result >= 1) {

		    // Create success message and route to user controller
		    $session->setFlash('message', new Success('Benutzer erfolgreich gelöscht'));
		    return $this->_route('admin/User_Controller', 'index');

		    // Check if no user is deleted
		} elseif ($result === 0) {

		    // Load user not found action
		    return $this->userNotFound($id);
		}

		// Exception handling
	    } catch (\Exception $e) {
		if ($e->getCode() == '23000') {
		    $this->_lahaina->setData('message', new Warning('Benutzer mit der ID {' . $id . '} konnte nicht gelöscht werden, weil dieser noch verwendet wird.'));
		} else {
		    $this->_lahaina->setData('message', new Error('Benutzer mit der ID {' . $id . '} konnte nicht gelöscht werden<br /><br /><b>Fehlermeldung</b><br />' . $e->getMessage()));
		}
		return $this->index();
	    }
	}

	/**
	 * User save action
	 */
	public function save() {
	    // Get session and request
	    $request = $this->_lahaina->request();
	    $session = $this->_lahaina->session();

	    // Get user data of POST request
	    $user_data = $request->getData('post', 'user');

	    // Check if user data exist
	    if (isset($user_data)) {

		try {

		    $user = new User_Model($this->_lahaina);

		    if ($user_data->exists('user_id')) {
			$user->update($user_data);
		    } else {
			$user->create($user_data);
		    }
		    $user->validate();
		    $user->save();

		    $session->setFlash('message', new Success('Benutzer erfolgreich gespeichert'));
		    return $this->_route('admin/user', 'index');


		    // Exception handling if validation failed
		} catch (ValidationException $e) {
		    $this->_lahaina->setData('message', new ValidationMessage($e->getErrors()));
		    $this->_lahaina->setData('validationErrors', $e->getErrors());
		    $this->_lahaina->setData('user', $user_data);
		    if ($user_data->exists('user_id')) {
			return $this->edit();
		    } else {
			return $this->create();
		    }

		    // Exception handling
		} catch (\Exception $e) {
		    $this->_lahaina->setData('message', new Error('Benutzer konnte nicht gespeichert werden<br /><br /><b>Fehlermeldung</b><br />' . $e->getMessage()));
		    return $this->index();
		}
	    }
	}

	/**
	 * User not found action
	 *
	 * @param integer $id ID of user
	 */
	private function userNotFound($id) {
	    $this->_lahaina->setData('message', new Warning('Es wurde kein Benutzer mit der ID <b>' . $id . '</b> gefunden'));
	    return $this->index();
	}

    }
    