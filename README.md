# Coxygen-medical-center

## Local PHP setup

1. Create the database and schema:
   ```bash
   mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS coxygen CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   mysql -u root -p coxygen < schema.sql
   ```
2. Export environment variables (example):
   ```bash
   export DB_HOST=127.0.0.1
   export DB_PORT=3306
   export DB_NAME=coxygen
   export DB_USER=root
   export DB_PASS=''
   export MAIL_FROM_ADDRESS='your-clinic@gmail.com'
   export MAIL_REPLY_TO='your-clinic@gmail.com'
   export ADMIN_NOTIFY_EMAIL='admin@gmail.com'
   ```
3. Serve locally:
   ```bash
   php -S 127.0.0.1:8000
   ```

## Added PHP endpoints

- `register.php` — registers patients and sends immediate email acknowledgments.
- `login.php` / `logout.php` — basic session authentication.
- `registered_users.php` — authenticated list of registered users.
- `auth_stat.php` — current auth status + KYC status.
- `reminder_worker.php` — scheduled reminder sender for appointment email/SMS notifications.
- `gmail_reply_poll.php` — polls Gmail IMAP for new replies and notifies admin email.

## Real-time-ish notification flow

- **Registration emails:** sent immediately in `register.php`.
- **Reply alerts:** run `gmail_reply_poll.php` every minute using cron to get near real-time reply notifications.
- **Appointment reminders:** run `reminder_worker.php` every 5 minutes (or every minute) via cron.

Example cron (Linux):

```cron
* * * * * cd /workspace/Coxygen-medical-center && /usr/bin/php gmail_reply_poll.php >> /tmp/gmail_reply_poll.log 2>&1
*/5 * * * * cd /workspace/Coxygen-medical-center && /usr/bin/php reminder_worker.php >> /tmp/reminder_worker.log 2>&1
```

## Phone reminders

SMS reminders use Twilio when these variables are set:

```bash
export TWILIO_ACCOUNT_SID='AC...'
export TWILIO_AUTH_TOKEN='...'
export TWILIO_FROM_NUMBER='+1...'
```

Without Twilio credentials, SMS reminders are skipped while email reminders still run.

## Gmail IMAP variables for reply polling

```bash
export GMAIL_IMAP_MAILBOX='{imap.gmail.com:993/imap/ssl}INBOX'
export GMAIL_IMAP_USER='your-clinic@gmail.com'
export GMAIL_IMAP_PASSWORD='app-password'
```

Use a Gmail App Password (2FA enabled account) for IMAP access.
