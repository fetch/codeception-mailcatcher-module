<?php

use Zend\Mail\Message;

function mail_filename(){
	return uniqid() . '.mail';
}

$transportOptions = new Zend\Mail\Transport\FileOptions([
	'path' => __DIR__ . '/../_output',
	'callback' => 'mail_filename'
]);
$transport = new Zend\Mail\Transport\File($transportOptions);

$I = new NoGuy($scenario);
$I->wantTo('Run a Zend Mail cept test');

// Clear old emails
$I->resetEmails();

// Compose an email
$message = new Message();

$body = 'Testing';
$message->setBody($body);

$message->setSubject('Subject Line');
$message->setTo('user@example.com');

// Send an email
$transport->send($message);

$I->seeInLastEmail($body);
