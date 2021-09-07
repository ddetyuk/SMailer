Mail-tester Score
==========

Text message:

Score : 10/10
https://www.mail-tester.com/test-yj7dsx1qo

Html message

Score : 10/10
https://www.mail-tester.com/test-1s5j0u9tj


SMailer usage
==========

```php
    require "SMailer.php";
    
    $to      = "toemail@example.com",
    $from    = "fromemail@example.com",
    $subject = "Email subject",
    $content = "Email content"
    
    $mailer = new SMailer('smtp.gmail.com', '587', "smtpusername", "smtppassword");
    $message = $mailer->createTextMessage($to, $from, $subject, $content);
    $code = $mailer->sendMail( $to, $from, $message);
    if($code>=200 && $code<300){
        echo "OK";
    }
```
