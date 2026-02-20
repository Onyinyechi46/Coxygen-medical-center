<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/notifier.php';

$gmailMailbox = env_value('GMAIL_IMAP_MAILBOX');
$gmailUser = env_value('GMAIL_IMAP_USER');
$gmailPassword = env_value('GMAIL_IMAP_PASSWORD');

if (!$gmailMailbox || !$gmailUser || !$gmailPassword) {
    fwrite(STDERR, "Missing GMAIL_IMAP_MAILBOX, GMAIL_IMAP_USER or GMAIL_IMAP_PASSWORD environment variables.\n");
    exit(1);
}

$inbox = imap_open($gmailMailbox, $gmailUser, $gmailPassword);
if (!$inbox) {
    fwrite(STDERR, "Cannot connect to Gmail IMAP.\n");
    exit(1);
}

$emails = imap_search($inbox, 'UNSEEN');
if (!$emails) {
    imap_close($inbox);
    echo "No new replies.\n";
    exit;
}

foreach ($emails as $emailNumber) {
    $overview = imap_fetch_overview($inbox, (string)$emailNumber, 0);
    $subject = $overview[0]->subject ?? '(No subject)';
    $from = $overview[0]->from ?? 'Unknown';

    send_gmail_notification(
        ADMIN_NOTIFY_EMAIL,
        'Patient replied to auto-email',
        "A response was received.\nFrom: {$from}\nSubject: {$subject}\nCheck Gmail inbox for full message."
    );
}

imap_close($inbox);
echo 'Processed replies: ' . count($emails) . PHP_EOL;
