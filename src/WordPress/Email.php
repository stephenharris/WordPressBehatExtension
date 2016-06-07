<?php
namespace StephenHarris\WordPressBehat\WordPress;

/**
 * An e-mail instance represents a sent e-mail
 *
 * @package StephenHarris\WordPressBehat\WordPress
 */
class Email
{

    private $recipient;
    
    private $subject;
    
    private $body;
    
    public function __construct($recipient, $subject = '', $body = '')
    {
        $this->recipient = $recipient;
        $this->subject   = $subject;
        $this->body      = $body;
    }
    
    public function getRecipient()
    {
        return $this->recipient;
    }
    
    public function getSubject()
    {
        return $this->subject;
    }

    public function getBody()
    {
        return $this->body;
    }
}
