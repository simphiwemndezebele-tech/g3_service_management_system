<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


/*
|--------------------------------------------------------------------------
| Load PHPMailer
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../vendor/autoload.php';


/*
|--------------------------------------------------------------------------
| Send Email Function
|--------------------------------------------------------------------------
*/

function sendEmail(
    $recipientEmail,
    $recipientName,
    $subject,
    $message
) {

    $mail = new PHPMailer(true);

    try {

        /*
        |--------------------------------------------------------------------------
        | SMTP SETTINGS - Gmail
        |--------------------------------------------------------------------------
        */

        $mail->isSMTP();

        $mail->Host = 'smtp.gmail.com';

        $mail->SMTPAuth = true;

        $mail->Username = 'simphiwemndzebele18@gmail.com';

        /*
        IMPORTANT:
        Replace the value below with the Gmail
        App Password you created.

        Do NOT send the password to me.
        */

        $mail->Password = 'zljf tcjf zwah ywbr';

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        $mail->Port = 587;


        /*
        |--------------------------------------------------------------------------
        | Sender
        |--------------------------------------------------------------------------
        */

        $mail->setFrom(
            'simphiwemndzebele18@gmail.com',
            'G3 Systems'
        );


        /*
        |--------------------------------------------------------------------------
        | Recipient
        |--------------------------------------------------------------------------
        */

        $mail->addAddress(
            $recipientEmail,
            $recipientName
        );


        /*
        |--------------------------------------------------------------------------
        | Email Content
        |--------------------------------------------------------------------------
        */

        $mail->isHTML(true);

        $mail->Subject = $subject;


        /*
        |--------------------------------------------------------------------------
        | Email Body
        |--------------------------------------------------------------------------
        */

        $mail->Body = "

        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: auto;'>

            <h2 style='margin-bottom: 5px;'>
                G3 Systems
            </h2>

            <p>
                Service Management System
            </p>

            <hr>

            <p>
                " . nl2br(htmlspecialchars($message)) . "
            </p>

            <br>

            <p>
                Regards,<br>
                <strong>G3 Systems</strong><br>
                Service Management System
            </p>

        </div>

        ";


        /*
        |--------------------------------------------------------------------------
        | Plain Text Alternative
        |--------------------------------------------------------------------------
        */

        $mail->AltBody = $message;


        /*
        |--------------------------------------------------------------------------
        | Send Email
        |--------------------------------------------------------------------------
        */

        $mail->send();

        return true;


    } catch (Exception $e) {

        /*
        |--------------------------------------------------------------------------
        | Email Error
        |--------------------------------------------------------------------------
        */

        error_log(
            "G3 Systems Email Error: " . $mail->ErrorInfo
        );

        return false;
    }
}

?>