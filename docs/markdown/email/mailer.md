# Email Sender

Ark defined an interface `ArkMailer` to support the requirement of sending emails.
In the mean time, Ark required another library `phpmailer/phpmailer` (of its 6.x version), 
to provide an implementation of this interface, i.e. `ArkSMTPMailer`.

## Interface `ArkMailer`

The interface `ArkMailer` classified the usage of a standard mail sender,
to send one email, the program should:

1. begin with method `prepare` to setup the email component,
1. inject the parameters, 
1. and finally execute  with method `finallySend`.

Usually the progress of sending an email is like that,
we suppose `$mailer` is an instance of a class implemented the interface.

```php
// you might define $mailer as ArkSMTPMailer
// $mailer = new \sinri\ark\email\ArkSMTPMailer($config);
// or any other instance following ArkMailer

$sent = $mailer->prepare()
    ->addReceiver("ark@example.com", 'Dummy Receiver')
    ->setSubject("Subject of this test Email")
    ->setHTMLBody("<p style='color:red'>" . "Content is also dummy!" . "</p>")
    ->finallySend();
```

The other SMTP attributes such as CC and attachments are also provided,
please refer to the interface code.


