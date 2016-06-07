<?php
namespace StephenHarris\WordPressBehat\WordPress;

/**
 * An inbox is a collection of e-mails sent to a given e-mail
 *
 * @package StephenHarris\WordPressBehat\WordPress
 */
class InboxFactory
{

    private static $instance = null;
    
    private $inboxes = array();
    
    private function __construct()
    {
    }
    
    public static function getInstance()
    {
        if (null == self::$instance) {
            self::$instance = new InboxFactory();
        }
        return self::$instance;
    }

    /**
     * Will set up an inbox with all recorded emails sent to $emailAddress
     * @param string $emailAddress The e-mail address of the recipient.
     */
    public function getInbox($emailAddress)
    {

        if (!isset($this->inboxes[$emailAddress])) {
            $this->inboxes[$emailAddress] = new Inbox($emailAddress);
        }

        return $this->inboxes[$emailAddress];
    }
}
