<?php

    namespace lahaina\framework\handler;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

use lahaina\framework\exception\FrameworkException;
use lahaina\framework\common\Lahaina;

    /**
     * Error handler
     * 
     * @version 1.0.2
     * 
     * @see http://us3.php.net/manual/en/function.set-error-handler.php#109149
     */
    class Error {

	/**
	 * @var Lahaina
	 */
	private $_lahaina;

	/**
	 * Constructor
	 * 
	 * @param \lahaina\framework\common\Lahaina $lahaina Lahaina object
	 */
	public function __construct(Lahaina $lahaina) {
	    $this->_lahaina = $lahaina;
	}

	/**
	 * Set error handler
	 */
	public function setError() {
	    set_error_handler(array($this, 'captureNormal'));
	}

	/**
	 * Set exception handler
	 */
	public function setException() {
	    set_exception_handler(array($this, 'captureException'));
	}

	/**
	 * Register shutdown function for critical errors
	 */
	public function registerShutdownFunction() {
	    register_shutdown_function(array($this, 'captureShutdown'));
	}

	/**
	 * Capture function for normal errors
	 * 
	 * @param int $number
	 * @param string $message
	 * @param string $file
	 * @param int $line
	 */
	public function captureNormal($errno, $errstr, $errfile, $errline) {

	    // Cleaning output
	    ob_end_clean();

	    // Define exception ouput
	    $header = '<html><head><title>Lahaina Framework</title><body style="padding: 10px; font-family: arial, sans-serif;">';
	    $body = '<h1 style="background: #005EA7; font-size: 16px; color: #fff; padding: 5px 10px; margin: 0 0 5px 0;">
                Lahaina Framework</h1>
                <div style="background: #DBEAF9; font-size: 14px; padding: 5px 6px; margin: 0 0 5px 0;">
                <b>Error [' . $errno . ']:</b> <i>' . $errstr . '</i></div>
                <div style="margin: 5px 0 0 0; padding: 5px 10px;  color: #fff; background: #005EA7; font-size: 10px;">
                    Error [' . $errno . '] in ' . $errfile . ', line ' . $errline . '</div>';
	    $footer = '</body></html>';

	    // Write log
	    $this->_lahaina->logger()->error('Error [' . $errno . '] in ' . $errfile . ', line ' . $errline . ': ' . $errstr);

	    // Exit output
	    exit($header . $body . $footer);
	}

	/**
	 * Capture function for exceptions 
	 * 
	 * @param \Exception $e Captured exception
	 * @exit string exception output
	 */
	public function captureException($e) {

	    // Cleaning output
	    ob_end_clean();

	    // Check exceptiion type
	    $exceptionType = basename(get_class($e));
	    if ($e instanceof FrameworkException) {
		$exceptionType = $exceptionType . ' (' . $e->getComponentAsString() . ')';
	    }

	    // Define exception ouput
	    $header = '<html><head><title>Lahaina Framework</title><body style="padding: 10px; font-family: arial, sans-serif;">';
	    $body = '<h1 style="background: #005EA7; font-size: 16px; color: #fff; padding: 5px 10px; margin: 0 0 5px 0;">
                Lahaina Framework</h1>
                <div style="background: #005EA7; font-size: 14px; color: #fff; padding: 5px 6px; margin: 0 0 5px 0;">
                <b>' . $exceptionType . ':</b> <i>' . $e->getMessage() . '</i></div>
                <pre style="margin: 5px 0 0 0; padding: 5px 10px; background: #DBEAF9; font-size: 12px;">' . $e->getTraceAsString() . '</pre>
                <div style="margin: 5px 0 0 0; padding: 5px 10px;  color: #fff; background: #005EA7; font-size: 10px;">' .
		    $exceptionType . ' in ' . $e->getFile() . ', line ' . $e->getLine() . '</div>';
	    $footer = '</body></html>';

	    // Write log
	    $this->_lahaina->logger()->error($exceptionType . ' in ' . $e->getFile() . ', line ' . $e->getLine() . ': ' . $e->getMessage() . "\n" . $e->getTraceAsString());

	    // Exit output
	    exit($header . $body . $footer);
	}

	/**
	 * Capture function for critical errors
	 * 
	 * @exit mixed Fehlermeldung
	 */
	public function captureShutdown() {

	    // Get last error
	    $error = error_get_last();

	    // Check if last error is given
	    if ($error) {

		// Clean output
		ob_end_clean();

		// Define ciritcal error ouput
		$header = '<html><head><title>Lahaina Framework</title><body style="padding: 10px; font-family: arial, sans-serif;">';
		$body = '<h1 style="background: #A50000; font-size: 16px; color: #fff; padding: 5px 10px; margin: 0 0 5px 0;">
                    Lahaina Framework</h1>
                    <div style="background: #fff; font-size: 14px; color: #fff; padding: 5px 6px; margin: 0 0 5px 0;">
                    <b>Error [' . $error['type'] . ']:</b> <i>' . $error['message'] . '</i></div>
                    <div style="margin: 5px 0 0 0; padding: 5px 10px;  color: #fff; background: #A50000; font-size: 10px;">
                       Critical Error [' . $error['type'] . '] in ' . $error['file'] . ', line ' . $error['line'] . '</div>';
		$footer = '</body></html>';

		// Write log
		$this->_lahaina->logger()->error('Error [' . $errno . '] in ' . $errfile . ', line ' . $errline . ': ' . $errstr);

		// Exit output
		exit($header . $body . $footer);

		// Exit output
		exit($header . $body . $footer);
	    }
	}

    }

?>