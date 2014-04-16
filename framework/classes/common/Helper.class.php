<?php

    namespace lahaina\framework\common;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

    /**
     * Helper
     *
     * @version 1.0.2
     * 
     * @author Jonathan Nessier
     */
    class Helper {

	/**
	 * Check if value is serialized
	 * 
	 * @param string $value Value
	 * @return boolean Value is serialized
	 */
	public function isSerialized($value) {
	    if (!is_string($value)) {
		return false;
	    }
	    if (trim($value) == "") {
		return false;
	    }
	    if (preg_match("/^(i|s|a|o|d):(.*);/si", $value)) {
		return true;
	    }
	    return false;
	}

	/**
	 * Modifies a string to remove all non ASCII characters and spaces.
	 * 
	 * @param string $text Text to slugify
	 * @return string Slugified text
	 */
	public function slugify($text) {
	    // replace non letter or digits by -
	    $text = preg_replace('~[^\\pL\d]+~u', '-', $text);

	    // trim
	    $text = trim($text, '-');

	    // lowercase
	    $text = strtolower($text);

	    // Replace umlauts
	    $text = str_replace(array('ä', 'ü', 'ö', 'ß'), array('ae', 'ue', 'oe', 'ss'), $text);

	    // remove unwanted characters
	    $text = preg_replace('~[^-\w]+~', '', $text);

	    // transliterate
	    if (function_exists('iconv')) {
		$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
	    }

	    if (empty($text)) {
		return 'n-a';
	    }

	    return $text;
	}

    }
    