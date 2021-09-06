SMailer usage
==========

```php
  require "SMailer.php";

  $params = [
      'server' => [
          'host' => 'smtp.gmail.com',
          'port' => '587',
      ],
      'auth' => [
          'username' => "smtpusername",
          'password' => "smtppassword",
      ]
  ];

  $mailer = new SMailer($params);
  $results = $mailer->send(
      "toemail@example.com",
      "fromemail@example.com",
      "Email subject",
      "Email content"
  );
```
