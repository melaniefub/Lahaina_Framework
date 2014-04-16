<?php

    namespace lahaina\framework\persistence;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

    /**
     * Types of statement values
     *
     * @version 1.0.2
     *
     * @author Jonathan Nessier
     */
    class Type {

	const INTEGER = 'integer';
	const STRING = 'string';
	const BLOB = 'blob';
	const BOOLEAN = 'boolean';
	const NULL = 'null';

	}

	