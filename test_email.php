<?php

require_once("includes/email_functions.php");

$recipientEmail = "simphiwemndzebele18@gmail.com";
$recipientName = "Simphiwe";

$subject = "G3 Systems - Email Test";

$message = "
Hello Simphiwe,

This is a test email from the G3 Service Management System.

If you received this email, then:

✓ PHPMailer is working
✓ Gmail SMTP is working
✓ G3 email notifications are ready

We can now connect email notifications to service requests.

Regards,
G3 Systems
Service Management System
";


if (sendEmail(
    $recipientEmail,
    $recipientName,
    $subject,
    $message
)) {

    echo "
    <div style='
        font-family: Arial;
        max-width: 600px;
        margin: 50px auto;
        padding: 30px;
        text-align: center;
        border: 1px solid #ddd;
        border-radius: 10px;
    '>

        <h1 style='color: green;'>
            ✅ Email Sent Successfully!
        </h1>

        <p>
            The G3 Service Management System successfully
            sent an email through Gmail.
        </p>

        <p>
            Check:
            <strong>$recipientEmail</strong>
        </p>

    </div>
    ";

} else {

    echo "
    <div style='
        font-family: Arial;
        max-width: 600px;
        margin: 50px auto;
        padding: 30px;
        text-align: center;
        border: 1px solid #ddd;
        border-radius: 10px;
    '>

        <h1 style='color: red;'>
            ❌ Email Failed
        </h1>

        <p>
            PHPMailer could not send the email.
        </p>

        <p>
            Check the PHP error log for details.
        </p>

    </div>
    ";
}

?>