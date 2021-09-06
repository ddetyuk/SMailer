<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
          integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

    <title>SMailer</title>
</head>
<body>
<div class="container">
    <h3>Test form</h3>
    <p>Test form for checking send mail functionality</p>
    <?php
    $results = '';
    if (!empty($_POST)) {

        require "SMailer.php";

        $params = [
            'server' => [
                'host' => getenv('smtphost'),
                'port' => getenv('smtpport'),
            ],
            'auth' => [
                'username' => getenv('smtpuser'),
                'password' => getenv('smtppassword'),
            ]
        ];

        $mailer = new SMailer($params);
        $results = $mailer->send(
            $_POST['to'],
            $_POST['from'],
            $_POST['subject'],
            $_POST['content']
        );
    }
    ?>
    <form  action="index.php" method="post">
        <?php if ($results) { ?>
            <?php if ($results>=200 && $results<300) { ?>
                <div class="alert alert-success" role="alert">
                    Success: <?php echo $results; ?>
                </div>
            <?php }?>
            <?php if ($results>=300 && $results<400) { ?>
                <div class="alert alert-warning" role="alert">
                    Temporary error: <?php echo $results; ?>
                </div>
            <?php }?>
            <?php if ($results>=400) { ?>
                <div class="alert alert-danger" role="alert">
                    Permanent Error: <?php echo $results; ?>
                </div>
            <?php }?>
        <?php } ?>
        <div class="form-group">
            <label for="to">From</label>
            <input type="email" class="form-control" name="from" id="from" aria-describedby="emailHelp" placeholder="Email from adress">
            <small id="toHelp" class="form-text text-muted"></small>
        </div>
        <div class="form-group">
            <label for="to">To</label>
            <input type="email" class="form-control" name="to" id="to" aria-describedby="emailHelp" placeholder="Email to adress">
            <small id="toHelp" class="form-text text-muted"></small>
        </div>
        <div class="form-group">
            <label for="subject">Subject</label>
            <input class="form-control" id="subject" name="subject" aria-describedby="emailHelp"
                   placeholder="Enter email subject">
            <small id="subjectHelp" class="form-text text-muted"></small>
        </div>
        <div class="form-group">
            <label for="content">Content</label>
            <textarea class="form-control" id="content" name="content" aria-describedby="emailHelp"
                      placeholder="Enter email content"></textarea>
            <small id="contentHelp" class="form-text text-muted"></small>
        </div>
        <button type="submit" class="btn btn-primary">Send Email</button>
    </form>
</div>
<!-- Optional JavaScript -->
<!-- jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
        crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"
        integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1"
        crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"
        integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM"
        crossorigin="anonymous"></script>
</body>
</html>
