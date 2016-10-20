<?php
namespace StephenHarris\WordPressBehatExtension\WordPress;

/**
 * An inbox is a collection of e-mails sent to a given e-mail
 *
 * @package StephenHarris\WordPressBehatExtension\WordPress
 */
class InboxFactory
{

    private $inboxes = array();
    
    public function __construct($dir)
    {
        $this->dir = $dir;
    }

    /**
     * Will set up an inbox with all recorded emails sent to $emailAddress
     * @param string $emailAddress The e-mail address of the recipient.
     */
    public function getInbox($emailAddress)
    {
        if (!isset($this->inboxes[$emailAddress])) {
            $this->inboxes[$emailAddress] = new Inbox($emailAddress, $this->dir);
        }
        return $this->inboxes[$emailAddress]->refresh();
    }
}
