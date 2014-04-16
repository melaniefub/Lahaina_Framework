<?php

    namespace lahaina\libraries\mail;

if (!defined('PATH'))
	exit('Kein direkter Skriptzugriff erlaubt!');

use lahaina\libraries\mail\MailException;

    /**
     * Mail class
     *
     * @version 0.2
     * 
     * @author Melanie Rufer
     */
    class Mail {

	protected $recipients;
	protected $sender;
	protected $subject;
	protected $text;

	/**
	 * Constructor
	 * 
	 * @param string $recipients
	 * @param string $sender
	 * @param string $subject
	 * @param string $text
	 */
	public function __construct($recipients = null, $sender = null, $subject = null, $text = null) {
	    $this->setRecipients($recipients);
	    $this->sender = $sender;
	    $this->subject = $subject;
	    $this->setText($text);
	}

	/**
	 * Set recipient(s)
	 * 
	 * @param string|array $recipient Email adddresses of recipient(s)
	 * @throws MailException
	 */
	public function setRecipients($recipients) {
	    if (is_array($recipients)) {
		foreach ($recipients as $recipient) {
		    $this->recipients .= $recipient . ', ';
		}
	    } elseif (is_string($recipients)) {
		$this->recipients = $recipients;
	    } else {
		throw new MailException('Recipient musst be an email as string or multiple emails in an array');
	    }
	}

	/**
	 * Set sender
	 * 
	 * @param string $sender
	 */
	public function setSender($sender) {
	    $this->sender = $sender;
	}

	/**
	 * Set subject
	 * 
	 * @param string $subject
	 */
	public function setSubject($subject) {
	    $this->subject = $subject;
	}

	/**
	 * Set text
	 * 
	 * @param string $text
	 * @throws MailException
	 */
	public function setText($text) {
	    if (strlen($text) >= 10) {
		$this->text = $text;
	    } else {
		throw new MailException('Text must be 10 or more chars.');
	    }
	}

	/**
	 * Send Email
	 * 
	 * @return boolean
	 * @throws MailException
	 */
	public function send() {
	    if ($this->recipients && $this->sender && $this->subject && $this->text) {
		$header = "From: " . $this->sender . "\r\n";
		$header .= "Content-type: text/html\r\n";

		if (mail($this->recipients, $this->subject, $this->text, $header) === true) {
		    return true;
		} else {
		    throw new MailException('Email could not be sent');
		}
	    } else {
		throw new MailException('Not all email attributes set.');
	    }
	}

    }
    