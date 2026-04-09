<?php

function send_app_mail(string $to, string $subject, string $message, string &$error = null): bool
{
    global $mail_config;

    $fromEmail = $mail_config['from_email'] ?? 'no-reply@jobhub.local';
    $fromName = $mail_config['from_name'] ?? 'JobHub';
    $replyTo = $mail_config['reply_to'] ?? $fromEmail;

    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/plain; charset=UTF-8',
        "From: {$fromName} <{$fromEmail}>",
        "Reply-To: {$replyTo}",
        'X-Mailer: PHP/' . phpversion(),
    ];

    $sent = mail($to, $subject, $message, implode("\r\n", $headers));

    if (!$sent) {
        $error = 'PHP mail() could not send the message. Configure SMTP/sendmail in XAMPP before testing email delivery.';
    }

    return $sent;
}
