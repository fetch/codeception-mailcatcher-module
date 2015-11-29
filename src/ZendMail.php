<?php

namespace Codeception\Module;

use Zend\Mail\Message;

use Codeception\Module;

class ZendMail extends Module
{

    /**
     * @var string
     */
    protected $path;

    /**
     * @var array
     */
    protected $config = array('path');

    /**
     * @var array
     */
    protected $requiredFields = array('path');

    public function _initialize()
    {
        $this->path = realpath($this->config['path']);
    }

    /**
     * Reset emails
     *
     * Clear email directory. You probably want to do this before
     * you do the thing that will send emails
     *
     * @return void
     * @author Koen Punt
     **/
    public function resetEmails()
    {
        $emails = glob($this->path . '/*.mail');
        foreach($emails as $email){
            if(is_file($email)){
                unlink($email);
            }
        }
    }


    /**
     * See In Last Email
     *
     * Look for a string in the most recent email
     *
     * @return void
     * @author Jordan Eldredge <jordaneldredge@gmail.com>
     **/
    public function seeInLastEmail($expected)
    {
        $email = $this->lastMessage();
        $this->seeInEmail($email, $expected);
    }

    /**
     * See In Last Email subject
     *
     * Look for a string in the most recent email subject
     *
     * @return void
     * @author Antoine Augusti <antoine.augusti@gmail.com>
     **/
    public function seeInLastEmailSubject($expected)
    {
        $email = $this->lastMessage();
        $this->seeInEmailSubject($email, $expected);
    }

    /**
     * Don't See In Last Email subject
     *
     * Look for the absence of a string in the most recent email subject
     *
     * @return void
     **/
    public function dontSeeInLastEmailSubject($expected)
    {
        $email = $this->lastMessage();
        $this->dontSeeInEmailSubject($email, $expected);
    }

    /**
     * Don't See In Last Email
     *
     * Look for the absence of a string in the most recent email
     *
     * @return void
     **/
    public function dontSeeInLastEmail($unexpected)
    {
        $email = $this->lastMessage();
        $this->dontSeeInEmail($email, $unexpected);
    }

    /**
     * See In Last Email To
     *
     * Look for a string in the most recent email sent to $address
     *
     * @return void
     * @author Jordan Eldredge <jordaneldredge@gmail.com>
     **/
    public function seeInLastEmailTo($address, $expected)
    {
        $email = $this->lastMessageFrom($address);
        $this->seeInEmail($email, $expected);

    }
    /**
     * Don't See In Last Email To
     *
     * Look for the absence of a string in the most recent email sent to $address
     *
     * @return void
     **/
    public function dontSeeInLastEmailTo($address, $unexpected)
    {
        $email = $this->lastMessageFrom($address);
        $this->dontSeeInEmail($email, $unexpected);
    }

    /**
     * See In Last Email Subject To
     *
     * Look for a string in the most recent email subject sent to $address
     *
     * @return void
     * @author Antoine Augusti <antoine.augusti@gmail.com>
     **/
    public function seeInLastEmailSubjectTo($address, $expected)
    {
        $email = $this->lastMessageFrom($address);
        $this->seeInEmailSubject($email, $expected);

    }
    /**
     * Don't See In Last Email Subject To
     *
     * Look for the absence of a string in the most recent email subject sent to $address
     *
     * @return void
     **/
    public function dontSeeInLastEmailSubjectTo($address, $unexpected)
    {
        $email = $this->lastMessageFrom($address);
        $this->dontSeeInEmailSubject($email, $unexpected);
    }

    /**
     * Grab Matches From Last Email
     *
     * Look for a regex in the email source and return it's matches
     *
     * @return array
     * @author Stephan Hochhaus <stephan@yauh.de>
     **/
    public function grabMatchesFromLastEmail($regex)
    {
        $email = $this->lastMessage();
        $matches = $this->grabMatchesFromEmail($email, $regex);
        return $matches;
    }

    /**
     * Grab From Last Email
     *
     * Look for a regex in the email source and return it
     *
     * @return string
     * @author Stephan Hochhaus <stephan@yauh.de>
     **/
    public function grabFromLastEmail($regex)
    {
        $matches = $this->grabMatchesFromLastEmail($regex);
        return $matches[0];
    }

    /**
     * Grab Matches From Last Email To
     *
     * Look for a regex in most recent email sent to $addres email source and
     * return it's matches
     *
     * @return array
     * @author Stephan Hochhaus <stephan@yauh.de>
     **/
    public function grabMatchesFromLastEmailTo($address, $regex)
    {
        $email = $this->lastMessageFrom($address);
        $matches = $this->grabMatchesFromEmail($email, $regex);
        return $matches;
    }

    /**
     * Grab From Last Email To
     *
     * Look for a regex in most recent email sent to $addres email source and
     * return it
     *
     * @return string
     * @author Stephan Hochhaus <stephan@yauh.de>
     **/
    public function grabFromLastEmailTo($address, $regex)
    {
        $matches = $this->grabMatchesFromLastEmailTo($address, $regex);
        return $matches[0];
    }

    /**
     * Test email count equals expected value
     *
     * @return void
     * @author Mike Crowe <drmikecrowe@gmail.com>
     **/
    public function seeEmailCount($expected)
    {
        $messages = $this->messages();
        $count = count($messages);
        $this->assertEquals($expected, $count);
    }

    // ----------- HELPER METHODS BELOW HERE -----------------------//

    /**
     * Messages
     *
     * Get an array of all the messages
     *
     * @return array
     * @author Koen Punt
     **/
    protected function messages()
    {
        $messages = glob($this->path . '/*.mail');
        return array_map(function ($message) {
            return $this->emailFromFile($message);
        }, $messages);
    }

    /**
     * Last Message
     *
     * Get the most recent email
     *
     * @return Zend\Mail\Message
     * @author Koen Punt
     **/
    protected function lastMessage()
    {
        $messages = $this->messages();
        if (empty($messages)) {
            $this->fail('No messages received');
        }

        return end($messages);
    }

    /**
     * Last Message From
     *
     * Get the most recent email sent to $address
     *
     * @return Zend\Mail\Message
     * @author Koen Punt
     **/
    protected function lastMessageFrom($address)
    {
        $messagesFrom = [];
        $messages = $this->messages();
        if (empty($messages)) {
            $this->fail('No messages received');
        }

        foreach ($messages as $message) {
            if ($message->getTo()->has($address)) {
                $messagesFrom[] = $message;
            }
        }

        if (!empty($messagesFrom))
            return max($messagesFrom);

        $this->fail("No messages sent to {$address}");
    }

    /**
     * Email from file
     *
     * Given a filename, returns the email's object
     *
     * @return Zend\Mail\Message
     * @author Koen Punt
     **/
    protected function emailFromFile($email)
    {
        return Message::fromString(file_get_contents($email));
    }

    /**
     * See In Subject
     *
     * Look for a string in an email subject
     *
     * @return void
     * @author Koen Punt
     **/
    protected function seeInEmailSubject($email, $expected)
    {
        $this->assertContains($expected, $email->getSubject(), "Email Subject Contains");
    }

    /**
     * Don't See In Subject
     *
     * Look for the absence of a string in an email subject
     *
     * @return void
     * @author Koen Punt
     **/
    protected function dontSeeInEmailSubject(Message $email, $unexpected)
    {
        $this->assertNotContains($unexpected, $email->getSubject(), "Email Subject Does Not Contain");
    }

    /**
     * See In Email
     *
     * Look for a string in an email
     *
     * @return void
     * @author Koen Punt
     **/
    protected function seeInEmail(Message $email, $expected)
    {
        $this->assertContains($expected, $email->getBody(), "Email Contains");
    }

    /**
     * Don't See In Email
     *
     * Look for the absence of a string in an email
     *
     * @return void
     * @author Koen Punt
     **/
    protected function dontSeeInEmail(Message $email, $unexpected)
    {
        $this->assertNotContains($unexpected, $email->getBody(), "Email Does Not Contain");
    }

    /**
     * Grab From Email
     *
     * Return the matches of a regex against the raw email
     *
     * @return void
     * @author Koen Punt
     **/
    protected function grabMatchesFromEmail(Message $email, $regex)
    {
        preg_match($regex, $email->getBody(), $matches);
        $this->assertNotEmpty($matches, "No matches found for $regex");
        return $matches;
    }

}
