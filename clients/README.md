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
- Client preview: open any client access user exactly as they see the portal after login
- Billing per project with printable invoices and a printable receipt for every part payment

## Billing and bills

Each project carries the agreed value, an optional tax percentage and a list of part
payments. Every part payment records its amount, date and time, method, reference and
statement, and keeps a stable id so its receipt always has the same number and URL.

| Screen | URL | Who can open it |
| --- | --- | --- |
| Billing overview | `/projects/{id}/billing` | Admin, assigned subadmin, assigned client |
| Printable invoice | `/projects/{id}/invoice` | Same |
| Printable part-payment receipt | `/projects/{id}/receipt/{paymentId}` | Same |

The invoice shows the project value, tax, grand total, everything received so far and the
balance due, followed by the full payment ledger. Each receipt shows a single installment
with the amount in words, what had been paid before it, and the balance that remains
afterwards — so a client gets a proper bill after every part payment. Both documents print
straight from the browser (Print / Save as PDF) with the interface chrome removed.

Add a part payment from **Update payments** on the billing screen, save, and the new
receipt is immediately printable.

Company details on the documents come from the `BILLING_*` values in `.env`; see
`.env.example`. Bills are addressed to the first client access user assigned to the
project, falling back to the project name when none is assigned.

## Sharing login details with a client

Every row on **Client Access** has a **Share login** button (also on the preview page). It
opens a composer that builds a ready-to-send message containing the portal address, the
client's login ID, the password and anything you type into the note box, with a live
preview. Send it straight to **WhatsApp**, **email** or **SMS**, **copy** it, or hand it to
the phone's own share sheet with **Other apps…** (Android and iOS; hidden where the browser
has no share API). WhatsApp opens on the client's saved mobile number — a bare 10-digit
number gets `91` prefixed automatically.

Passwords are stored as bcrypt hashes and can never be read back, so the composer fills the
password in exactly two situations:

1. Immediately after you create the client, or change their password while editing — the
   share box opens by itself with the password you just typed.
2. After you use **Set new password** inside the composer, which issues a new password
   (type one or leave the box empty to have a readable one generated) and shows it once.

At any other time the password box is empty and you can still send just the link and login
ID. The plain password lives in the session for a single page render and is never written
to the database or a log.

Set `CLIENT_PORTAL_URL` in `.env` to the address clients actually use; the composer appends
`/login`. The URL stays editable in the composer for one-off cases.

## Client preview

From **Client Access**, the **Preview** button on any row opens
`/client-users/{id}/preview`. It shows that client's profile, their combined billing
totals and the exact project dashboard their login produces. The preview is read-only —
it never signs you in as the client and never changes the session.

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
