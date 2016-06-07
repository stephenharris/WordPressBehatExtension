<?php 
use \StephenHarris\WordPressBehat\WordPress\InboxFactory;

class inboxTest extends PHPUnit_Framework_TestCase
{
	
	public function setUp(){
		$this->inboxFactory = InboxFactory::getInstance();
	}
	
	public function tearDown(){
		/*$files = glob(WORDPRESS_FAKE_MAIL_DIR.'/*'); // get all file names
		foreach($files as $file){ // iterate files
			if(is_file($file))
				unlink($file); // delete file
		}*/
	}
	
	protected function expectException( $class, $message, $code = null ) {
		self::setExpectedException( $class, $message, $code );
	}
	
	
	public function testEmailRecieved(){
		
		$this->sendEmail( 'test@example.com', 'Subject', 'Message' );
		
		$inbox        = $this->inboxFactory->getInbox( 'test@example.com' );
		$email        = $inbox->getLatestEmail();

		$this->assertEquals( 'Subject', $email->getSubject() );
		$this->assertEquals( 'Message', $email->getBody() );
		$this->assertEquals( 'test@example.com', $email->getRecipient() );
		
	}
	
	public function testInboxCleared(){		
		$this->sendEmail( 'test@example.com', 'Subject', 'Message' );
	
		$inbox        = $this->inboxFactory->getInbox( 'test@example.com' );
		$email        = $inbox->getLatestEmail();
		
		$this->expectException( '\Exception', 'Inbox for test@example.com is empty' );
		$inbox->clearInbox();
		
		$email        = $inbox->getLatestEmail();
	}
	
	public function testSelectEmailBySubject(){
		$this->sendEmail( 'test@example.com', 'Foo', 'First' );
		$this->sendEmail( 'test@example.com', 'Bar', 'Second' );
		$this->sendEmail( 'test@example.com', 'Foo', 'Third' );
		$this->sendEmail( 'test@example.com', 'Bar', 'Fourth' );
	
		$inbox        = $this->inboxFactory->getInbox( 'test@example.com' )->refresh();
		$email        = $inbox->getLatestEmail('Foo');
	
		$this->assertEquals( 'Foo', $email->getSubject() );
		$this->assertEquals( 'Third', $email->getBody() );
		$this->assertEquals( 'test@example.com', $email->getRecipient() );
	}
	
	private function sendEmail( $to, $subject = '', $body = '' ) {
		
		$dir      = rtrim(WORDPRESS_FAKE_MAIL_DIR, DIRECTORY_SEPARATOR);
		$fileName = time() . "-$to-" . $subject;

		$filePath = $dir . DIRECTORY_SEPARATOR . $fileName;
		$content  = "TO: $to" . PHP_EOL;
		$content .= "SUBJECT: $subject" . PHP_EOL;
		$content .= WORDPRESS_FAKE_MAIL_DIVIDER . PHP_EOL . $body;
		if (!is_dir(WORDPRESS_FAKE_MAIL_DIR)) {
			mkdir(WORDPRESS_FAKE_MAIL_DIR, 0777, true);
		}
		$result = (bool) file_put_contents($filePath, $content);
		return $result;
	}
	
	
}