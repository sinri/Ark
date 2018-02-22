# Email Sender through SMTP

The default implementation of interface `ArkMailer` is class `ArkSMTPMailer`,
which uses SMTP protocol to request remote email services.

As sending mail through the OS itself is not welcomed by almost cloud service providers, 
using regular email services through SMTP protocol might be a better choice.

As described, this implementation replies library `sinri/smallphpmailer`, which support SMTP.
So you must provide the configuration for SMTP linking when you construct an class instance,
or call method `setUpSMTP`.

The configuration is commonly organized as an array as 

```php
$config = [
    'host' => 'smtp.example.com',
    'smtp_auth' => true,
    'username' => '',
    'password' => '',
    'smtp_secure' => 'ssl',
    'port' => 465,
    'display_name' => 'Ark Mailer Tester',
];
```

The other usage is as interface `ArkMailer` defined.