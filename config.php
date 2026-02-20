<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function env_value(string $key, ?string $default = null): ?string
{
    $value = getenv($key);
    if ($value === false || $value === '') {
        return $default;
    }
    return $value;
}

define('APP_NAME', env_value('APP_NAME', 'Coxygen Medical Center'));
define('APP_URL', env_value('APP_URL', 'http://127.0.0.1:8000'));
define('DB_HOST', env_value('DB_HOST', '127.0.0.1'));
define('DB_PORT', env_value('DB_PORT', '3306'));
define('DB_NAME', env_value('DB_NAME', 'coxygen'));
define('DB_USER', env_value('DB_USER', 'root'));
define('DB_PASS', env_value('DB_PASS', ''));

define('MAIL_FROM_NAME', env_value('MAIL_FROM_NAME', APP_NAME));
define('MAIL_FROM_ADDRESS', env_value('MAIL_FROM_ADDRESS', 'your-clinic@gmail.com'));
define('MAIL_REPLY_TO', env_value('MAIL_REPLY_TO', MAIL_FROM_ADDRESS));
define('ADMIN_NOTIFY_EMAIL', env_value('ADMIN_NOTIFY_EMAIL', MAIL_FROM_ADDRESS));

define('TWILIO_ACCOUNT_SID', env_value('TWILIO_ACCOUNT_SID', ''));
define('TWILIO_AUTH_TOKEN', env_value('TWILIO_AUTH_TOKEN', ''));
define('TWILIO_FROM_NUMBER', env_value('TWILIO_FROM_NUMBER', ''));
