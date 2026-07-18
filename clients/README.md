# Unnat Technology Services Client Leads

A modern PHP and MongoDB web app for managing projects, client leads, meeting schedules, and reminder notifications. The app opens on an admin login page, then shows a projects dashboard and per-project client section.

## Features

- Admin session login with password hash verification
- MongoDB-backed projects and clients collections
- Project search, filter, create, edit, and delete
- Client search, filter, add, edit, and delete
- Add Client form opens only from the Add Client button
- Meeting schedule date and time per client
- Email reminder 30 minutes before scheduled meetings
- PWA manifest, service worker, installability, and push notification subscription
- Web Push reminders 30 minutes before scheduled meetings
- Responsive dashboard theme using the supplied UTS logo and favicon

## Requirements

- PHP 8.1+
- Composer
- MongoDB Atlas or MongoDB server
- PHP MongoDB extension enabled

Install the PHP MongoDB extension if needed:

```bash
pecl install mongodb
```

Then enable it in `php.ini`:

```ini
extension=mongodb
```

## Setup

1. Install dependencies:

```bash
composer install
```

2. Create `.env` from the example:

```bash
copy .env.example .env
```

3. Set your MongoDB connection:

```env
MONGODB_URI=<paste MongoDB URI here>
MONGODB_DATABASE=client_leads
```

4. Generate an admin password hash:

```bash
php scripts/hash_admin_password.php "your-secure-password"
```

Paste the generated hash into:

```env
ADMIN_EMAIL=admin@example.com
ADMIN_PASSWORD_HASH=<generated hash>
```

5. Generate VAPID keys for browser push:

```bash
vendor/bin/web-push generate:vapid
```

Paste the values into:

```env
VAPID_SUBJECT=mailto:admin@example.com
VAPID_PUBLIC_KEY=
VAPID_PRIVATE_KEY=
```

6. Run the app:

```bash
composer serve
```

Open `http://localhost:8000`.

## Notification Scheduling

The scheduler script finds clients with meetings due in the next 30 minutes and with no previous notification marker, sends email and PWA push notifications, then marks them as notified.

Run manually:

```bash
php scripts/send_meeting_notifications.php
```

For production, run it every minute or every five minutes with Task Scheduler, cron, or your hosting provider scheduler.

Example cron:

```cron
* * * * * cd /path/to/client-leads && php scripts/send_meeting_notifications.php >> storage/logs/notifications.log 2>&1
```

## MongoDB Collections

The app creates and uses these collections automatically:

- `projects`
- `clients`
- `push_subscriptions`

Indexes are created from the model constructors for common search and scheduling queries.

## Deployment Notes

- Point your web server document root to `public/`.
- Keep `.env` outside public access and never commit it.
- Configure a real mail transport for PHP `mail()` or replace `NotificationService::sendEmail()` with SMTP from your hosting provider.
- PWA push requires HTTPS in production. `localhost` works for development.

## Hostinger Subdomain Deployment

If your subdomain document root is `public_html/clients`, upload the whole project into that folder. The root `.htaccess` forwards requests into `public/` and blocks direct browser access to `app/`, `vendor/`, `storage/`, `.env`, and Composer files.

Recommended steps:

1. Run Composer locally or by Hostinger SSH:

```bash
composer2 install --no-dev --optimize-autoloader
```

2. Upload all project files to:

```text
public_html/clients
```

3. Create this file on the server:

```text
public_html/clients/.env
```

4. Add your production values:

```env
APP_URL=https://clients.unnattechnologyservices.com
MONGODB_URI=<paste MongoDB URI here>
MONGODB_DATABASE=client_leads
ADMIN_EMAIL=admin@example.com
ADMIN_PASSWORD_HASH=<generated hash>
```

5. Make sure the hosting PHP version is 8.1 or newer and the `mongodb` PHP extension is available.

6. Open:

```text
https://clients.unnattechnologyservices.com/login
```

If your plan does not support the MongoDB PHP extension, deploy this app on a VPS or switch the database layer to MySQL.
