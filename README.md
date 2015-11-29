# Zend Mail Codeception Module

[![Build Status](https://travis-ci.org/fetch/zend-mail-codeception-module.svg)](https://travis-ci.org/fetch/zend-mail-codeception-module)

This module will let you test emails that are sent during your Codeception
acceptance tests.

## Installation

Add the package into your composer.json:

    {
        "require-dev": {
            "codeception/codeception": "*",
            "fetch/zend-mail-codeception-module": "1.*"
        }
    }

Tell Composer to download the package:

    php composer.phar update

Update your Zend mail configuration to use a file transport:

```php
function mail_filename(){
  return uniqid() . '.mail';
}

$transportOptions = new Zend\Mail\Transport\FileOptions([
  'path' => 'tests/_output/mail',
  'callback' => 'mail_filename'
]);
$transport = new Zend\Mail\Transport\File($transportOptions);
```

Then enable it in your `acceptance.suite.yml` configuration and set path
 to the transport directory.

```yaml
class_name: WebGuy
modules:
  enabled:
    - ZendMail
  config:
    ZendMail:
      path: 'tests/_output/mail'
```

You will then need to rebuild your actor class:

    php codecept.phar build

## Example Usage

```php
$I = new WebGuy($scenario);
$I->wantTo('Get a password reset email');

// Cleared old emails from path
$I->resetEmails();

// Reset
$I->amOnPage('forgotPassword.php');
$I->fillField("input[name='email']", 'user@example.com');
$I->click("Submit");
$I->see("Please check your email");

$I->seeInLastEmail("Please click this link to reset your password");
```

## Actions

### resetEmails

Clears the emails in the messages directory. This is prevents seeing emails sent
during a previous test. You probably want to do this before you trigger any
emails to be sent

Example:

```php
// Clears all emails
$I->resetEmails();
```

### seeInLastEmail

Checks that an email contains a value. It searches the full raw text of the
email: headers, subject line, and body.

Example:

```php
$I->seeInLastEmail('Thanks for signing up!');
```

* Param $text

### seeInLastEmailTo

Checks that the last email sent to an address contains a value. It searches the
full raw text of the email: headers, subject line, and body.

This is useful if, for example a page triggers both an email to the new user,
and to the administrator.

Example:

```php
$I->seeInLastEmailTo('user@example.com', 'Thanks for signing up!');
$I->seeInLastEmailTo('admin@example.com', 'A new user has signed up!');
```

* Param $email
* Param $text

### dontSeeInLastEmail

Checks that an email does NOT contain a value. It searches the full raw text of the
email: headers, subject line, and body.

Example:

```php
$I->dontSeeInLastEmail('Hit me with those laser beams');
```

* Param $text

### dontSeeInLastEmailTo

Checks that the last email sent to an address does NOT contain a value. It searches the
full raw text of the email: headers, subject line, and body.

Example:

```php
$I->dontSeeInLastEmailTo('admin@example.com', 'But shoot it in the right direction');
```

* Param $email
* Param $text

### grabMatchesFromLastEmail

Extracts an array of matches and sub-matches from the last email based on
a regular expression. It searches the full raw text of the email: headers,
subject line, and body. The return value is an array like that returned by
`preg_match()`.

Example:

```php
$matches = $I->grabMatchesFromLastEmail('@<strong>(.*)</strong>@');
```

* Param $regex

### grabFromLastEmail

Extracts a string from the last email based on a regular expression.
It searches the full raw text of the email: headers, subject line, and body.

Example:

```php
$match = $I->grabFromLastEmail('@<strong>(.*)</strong>@');
```

* Param $regex

### grabMatchesFromLastEmailTo

Extracts an array of matches and sub-matches from the last email to a given
address based on a regular expression. It searches the full raw text of the
email: headers, subject line, and body. The return value is an array like that
returned by `preg_match()`.

Example:

```php
$matchs = $I->grabMatchesFromLastEmailTo('user@example.com', '@<strong>(.*)</strong>@');
```

* Param $email
* Param $regex

### grabFromLastEmailTo

Extracts a string from the last email to a given address based on a regular
expression.  It searches the full raw text of the email: headers, subject
line, and body.

Example:

```php
$match = $I->grabFromLastEmailTo('user@example.com', '@<strong>(.*)</strong>@');
```

* Param $email
* Param $regex

### seeEmailCount

Asserts that a certain number of emails have been sent since the last time
`resetEmails()` was called.

Example:

```php
$match = $I->seeEmailCount(2);
```

* Param $count

# License

Released under the same license as Codeception: MIT
