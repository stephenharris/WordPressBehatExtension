<?php
namespace StephenHarris\WordPressBehat\WordPress;

/**
 * An inbox is a collection of e-mails sent to a given e-mail
 *
 * @package StephenHarris\WordPressBehat\WordPress
 */
class Inbox
{
    
    /**
     * The e-mail address associated with this inbox
     * @var string
     */
    private $emailAddress;
    
    /**
     * An array of StephenHarris\WordPressBehat\WordPress\Email objects
     * @var array
     */
    private $emails;

    /**
     * Will set up an inbox with all recorded emails sent to $emailAddress
     * @param string $emailAddress The e-mail address of the recipient.
     */
    public function __construct($emailAddress)
    {
        $this->emailAddress = $emailAddress;
        $this->refresh();
    }
    
    /**
     * Returns an array of e-mails in this inbox
     */
    public function getEmails()
    {
        return $this->emails;
    }
    
    /**
     * Return the latest e-mail recieved with a given subject.
     *
     * If no subject is present then the latest e-mail recieved is returned.
     *
     * @param string|null $subject
     * @return StephenHarris\WordPressBehat\WordPress\Email
     */
    public function getLatestEmail($subject = null)
    {
        
        if (empty($this->emails)) {
            throw new \Exception(sprintf("Inbox for %s is empty", $this->emailAddress));
        }

        foreach ($this->emails as $email) {
            if (is_null($subject) || $subject == $email->getSubject()) {
                return $email;
            }
        }
                
        throw new \Exception(sprintf("No emails for %s found with subject '%s'", $this->emailAddress, $subject));
    }
    
    /**
     * Create an e-mail instance from a file
     *
     * The content of a file encodes details of the file.
     * @param string $file
     */
    protected function parseMail($file)
    {
        $file_contents = file_get_contents($file);
        
        preg_match('/^TO:(.*)$/mi', $file_contents, $to_matches);
        $recipient = trim($to_matches[1]);
        
        preg_match('/^SUBJECT:(.*)$/mi', $file_contents, $subj_matches);
        $subject = trim($subj_matches[1]);
        
        $parts = explode(WORDPRESS_FAKE_MAIL_DIVIDER, $file_contents);
        $body = trim($parts[1]);
        
        return new Email($recipient, $subject, $body);
    }
        
    /**
     * Delete all e-mails in this inbox
     */
    public function clearInbox()
    {
        $filePattern = $this->getInboxDirectory() . '*' . $this->emailAddress . '*';
        foreach (glob($filePattern) as $email) {
            unset($email);
        }
        $this->emails = array();
    }
    
    public function refresh()
    {
        $filePattern = $this->getInboxDirectory() . '*' . $this->emailAddress . '*';
        $this->emails = array();
        foreach (glob($filePattern) as $file) {
            $this->emails[] = $this->parseMail($file);
        }
        $this->emails = array_reverse($this->emails);
        return $this;
    }

    protected function getInboxDirectory()
    {
        return rtrim(WORDPRESS_FAKE_MAIL_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }
}
