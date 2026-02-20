<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function send_gmail_notification(string $to, string $subject, string $message): bool
{
    $headers = [
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'From: ' . MAIL_FROM_NAME . ' <' . MAIL_FROM_ADDRESS . '>',
        'Reply-To: ' . MAIL_REPLY_TO,
        'X-Mailer: PHP/' . phpversion(),
    ];

    return mail($to, $subject, $message, implode("\r\n", $headers));
}

function send_sms_reminder(string $toPhone, string $body): bool
{
    if (TWILIO_ACCOUNT_SID === '' || TWILIO_AUTH_TOKEN === '' || TWILIO_FROM_NUMBER === '') {
        return false;
    }

    $url = 'https://api.twilio.com/2010-04-01/Accounts/' . TWILIO_ACCOUNT_SID . '/Messages.json';
    $payload = http_build_query([
        'To' => $toPhone,
        'From' => TWILIO_FROM_NUMBER,
        'Body' => $body,
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERPWD => TWILIO_ACCOUNT_SID . ':' . TWILIO_AUTH_TOKEN,
    ]);

    curl_exec($ch);
    $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $status >= 200 && $status < 300;
}
