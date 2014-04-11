<?php

    namespace lahaina\libraries\tablelist;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

use lahaina\framework\data\Container;
use lahaina\framework\common\Lahaina;

    /**
     * Tablelist config extends data container class
     *
     * @version 1.0.2
     * 
     * @author Jonathan Nessier FUB <jonathan.nessier@vtg.admin.ch>
     * @author Melanie Rufer FUB <melanie.rufer@vtg.admin.ch>
     */
    class Config extends Container {

	/**
	 * Constructor
	 * 
	 * @param string $name Name of the tablelist
	 * @param string $defaultSort Default attribute who should be sorted
	 * @param string $defaultOrder Ordering of default sorted attribute
	 * @param integer $defaultPage Startpage of pagination
	 * @param integer $entitiesPerPage Entites per page of pagination
	 * @param integer $entitiesTotal Total count entities (need for pagination)
	 * @param string $cssClass Additional CSS classes
	 * @param \lahaina\framework\common\Lahaina $lahaina Lahaina framework base 
	 */
	public function __construct($name, $defaultSort, $defaultOrder, $defaultPage, $entitiesPerPage, $entitiesTotal, $cssClass, Lahaina $lahaina) {

	    $this->set('name', $name);

	    $requestData = $lahaina->request()->getData('get');
	    $session = $lahaina->session();

	    // Set sort and order
	    if ($requestData->get('name') == $name && $requestData->get('sort') && $requestData->get('order')) {
		$this->set('sort', $requestData->get('sort'));
		$this->set('order', $requestData->get('order'));
	    } elseif ($session->exists($name)) {
		$this->set('sort', $session->get($name)->get('sort'));
		$this->set('order', $session->get($name)->get('order'));
	    } else {
		$this->set('sort', $defaultSort);
		$this->set('order', $defaultOrder);
	    }

	    // Set page number
	    if ($requestData->get('page')) {
		$this->set('page', (int) $requestData->get('page'));
	    } elseif ($session->exists($name)) {
		$this->set('page', (int) $session->get($name)->get('page'));
	    } else {
		$this->set('page', $defaultPage);
	    }

	    // Set sort, order and page number as session values
	    $session->set($name, array(
		'sort' => $this->get('sort'),
		'order' => $this->get('order'),
		'page' => $this->get('page')
	    ));

	    // Set pagination config
	    $this->set('offset', $this->get('page') === null ? 1 : abs(($this->get('page') - 1) * $entitiesPerPage));
	    $this->set('limit', $entitiesPerPage);
	    $this->set('entitiesTotal', $entitiesTotal);
	    $this->set('cssClass', $cssClass);
	}

    }
    