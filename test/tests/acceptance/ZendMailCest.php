<?php

use Zend\Mail\Message;

class ZendMailCest
{

    /**
     * @var Zend\Mail\Transport\File
     */
    protected $transport;

    public static function _mail_filename(){
        return uniqid() . '.mail';
    }

    public function _before(\NoGuy $I) {
        // Clear old emails
        $I->resetEmails();

        $transportOptions = new Zend\Mail\Transport\FileOptions([
            'path' => __DIR__ . '/../_output',
            'callback' => 'ZendMailCest::_mail_filename'
        ]);
        $this->transport = new Zend\Mail\Transport\File($transportOptions);
    }

    public function test_reset_emails(\NoGuy $I)
    {
        $body = 'Hello World!';
        $this->mail('user@example.com', 'Subject Line', $body);
        $I->seeEmailCount(1);
        $I->resetEmails();
        $I->seeEmailCount(0);
    }

    public function test_see_in_last_email(\NoGuy $I)
    {
        $body = 'Hello World!';
        $this->mail('user@example.com', 'Subject Line', $body, 'no-reply@example.com');
        $I->seeInLastEmail($body);
    }

    public function test_see_in_last_email_subject(\NoGuy $I)
    {
        $subject = 'Subject Line';
        $this->mail('user@example.com', $subject, 'Hello World!', 'no-reply@example.com');
        $I->seeInLastEmailSubject($subject);
    }

    public function test_dont_see_in_last_email_subject(\NoGuy $I)
    {
        $subject = 'Subject Line';
        $this->mail('user@example.com', $subject, 'Hello World!', 'no-reply@example.com');
        $this->mail('user@example.com', 'Another Subject', 'Hello World!', 'no-reply@example.com');
        $I->dontSeeInLastEmailSubject($subject);
    }

    public function test_dont_see_in_last_email(\NoGuy $I)
    {
        $body = 'Hello World!';
        $this->mail('user@example.com', 'Subject Line', $body, 'no-reply@example.com');
        $this->mail('user@example.com', 'Subject Line', 'Goodbye World!', 'no-reply@example.com');
        $I->dontSeeInLastEmail($body);
    }

    public function test_see_in_last_email_to(\NoGuy $I)
    {
        $body = 'Hello World!';
        $user = 'userA@example.com';
        $this->mail($user, 'Subject Line', $body, 'no-reply@example.com');
        $this->mail('userB@example.com', 'Subject Line', 'Goodbye World!', 'no-reply@example.com');
        $I->seeInLastEmailTo($user, $body);
    }

    public function test_dont_see_in_last_email_to(\NoGuy $I)
    {
        $body = 'Goodbye Word!';
        $user = 'userA@example.com';
        $this->mail($user, 'Subject Line', 'Hello World!', 'no-reply@example.com');
        $this->mail('userB@example.com', 'Subject Line', $body, 'no-reply@example.com');
        $I->dontSeeInLastEmailTo($user, $body);
    }

    public function test_see_in_last_email_subject_to(\NoGuy $I)
    {
        $subject = 'Subject Line';
        $user = 'userA@example.com';
        $this->mail($user, $subject, 'Hello World!', 'no-reply@example.com');
        $this->mail('userB@example.com', 'Subject Line', 'Goodbye World!', 'no-reply@example.com');
        $I->seeInLastEmailSubjectTo($user, $subject);
    }

    public function test_dont_see_in_last_email_subject_to(\NoGuy $I)
    {
        $subject = 'Subject Line';
        $user = 'userA@example.com';
        $this->mail($user, 'Nothing to see here', 'Hello World!', 'no-reply@example.com');
        $this->mail('userB@example.com', $subject, 'Hello World!', 'no-reply@example.com');
        $I->dontSeeInLastEmailSubjectTo($user, $subject);
    }

    public function test_grab_matches_from_last_email(\NoGuy $I)
    {
        $this->mail('user@example.com', 'Subject Line', 'Hello World!', 'no-reply@example.com');
        $matches = $I->grabMatchesFromLastEmail('/Hello (World)/');
        $I->assertEquals($matches, array('Hello World', 'World'));
    }

    public function test_grab_from_last_email(\NoGuy $I)
    {
        $this->mail('user@example.com', 'Subject Line', 'Hello World!', 'no-reply@example.com');
        $match = $I->grabFromLastEmail('/Hello (World)/');
        $I->assertEquals($match, 'Hello World');
    }

    public function test_grab_matches_from_last_email_to(\NoGuy $I)
    {
        $user = 'user@example.com';
        $this->mail($user, 'Subject Line', 'Hello World!', 'no-reply@example.com');
        $this->mail('userB@example.com', 'Subject Line', 'Nothing to see here', 'no-reply@example.com');
        $matches = $I->grabMatchesFromLastEmailTo($user, '/Hello (World)/');
        $I->assertEquals($matches, array('Hello World', 'World'));
    }

    public function test_grab_from_last_email_to(\NoGuy $I)
    {
        $user = 'user@example.com';
        $this->mail($user, 'Subject Line', 'Hello World!', 'no-reply@example.com');
        $this->mail('userB@example.com', 'Subject Line', 'Nothing to see here', 'no-reply@example.com');
        $match = $I->grabFromLastEmailTo($user, '/Hello (World)/');
        $I->assertEquals($match, 'Hello World');
    }

    protected function mail($to, $subject, $body, $from = null){
        $message = new Message();
        $message->setTo($to);
        $message->setSubject($subject);
        $message->setBody($body);
        if ($from) {
            $message->setFrom($from);
        }
        $this->transport->send($message);
    }
}
