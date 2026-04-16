# Gmail SMTP Setup and Media/Our Work DB Architecture Guide

This guide explains:

1. Exactly where to change settings if you want real Gmail SMTP delivery.
2. Why separate `media` and `our_work` tables are not created in your database.
3. How your current architecture works and what to do if you want separate tables in future.

## 1) Where to Change SMTP for Real Gmail

Your project reads SMTP settings from two places:

1. `admin_settings` table (saved from admin panel UI) - highest priority
2. `admin/config/config.php` constants - fallback defaults

The mail sending function is in:

- `admin/core/mailer.php`

It loads settings in this order (important):

- `smtp_host`, `smtp_port`, `smtp_user`, `smtp_pass`, `smtp_encryption`
- from `admin_settings` first via `get_admin_setting(...)`
- then from constants in `admin/config/config.php` (`MAIL_SMTP_*`)

So if you save SMTP in admin panel, those values override `config.php` defaults.

## 2) Recommended Way (No Code Edit): Use Admin SMTP Settings Page

Open:

- `/admin/smtp-settings.php`

Set these values for Gmail:

- From Name: your site/app name
- From Email: your Gmail address (or matching sender)
- SMTP Host: `smtp.gmail.com`
- SMTP Port: `587`
- Encryption: `tls`
- SMTP Username: your full Gmail address
- SMTP Password: Gmail App Password (16-char), NOT normal Gmail password

Then click **Save SMTP Settings**.

### Important Gmail Requirements

Gmail SMTP does not work with normal account password (for most accounts).
You must:

1. Enable 2-Step Verification in your Google account.
2. Generate an App Password.
3. Use that App Password in `smtp_pass`.

If you do not use an App Password, authentication fails.

## 3) Alternative Way (Code Defaults)

If you want default fallback values in code, edit:

- `admin/config/config.php`

Set:

- `MAIL_SMTP_HOST` -> `smtp.gmail.com`
- `MAIL_SMTP_PORT` -> `587`
- `MAIL_SMTP_USER` -> your Gmail
- `MAIL_SMTP_PASS` -> your App Password

Note: if `admin_settings` already has saved SMTP, those UI values will still take precedence.

## 4) Why You Are Not Receiving Reset Email in Gmail Right Now

Your current defaults are Mailtrap sandbox values in `admin/config/config.php`:

- host: `sandbox.smtp.mailtrap.io`

Mailtrap Sandbox captures email inside Mailtrap inbox and does not forward to Gmail inbox.
So application mail can be successful technically, but Gmail never receives it.

To receive in Gmail, switch SMTP host/user/pass to real Gmail SMTP as described above.

## 5) Why `media` and `our_work` Tables Are Not Created

This is by design in your current architecture.

Your app uses one table:

- `gallery`

And separates sections using one column:

- `display_section` with values like:
  - `gallery`
  - `media_coverage`
  - `our_work`

### Where this is defined

In schema:

- `database/schema.sql` creates table `gallery` with `display_section` enum.

Runtime bootstrap also ensures same structure:

- `admin/core/schema_bootstrap.php`

### How modules use this

- Media module queries `gallery` with `display_section = 'media_coverage'`
  - `admin/modules/media/index.php`

- Our Work module queries `gallery` with `display_section IN ('our_work', 'our-work')`
  - `admin/modules/our-work/index.php`

- Frontend pages also fetch by section:
  - `media-coverage.php` uses `frontend_gallery_items($pdo, 'media_coverage')`
  - `our-work.php` uses `frontend_gallery_items($pdo, 'our_work')`

So there are no separate physical tables (`media`, `our_work`).
They are logical sections inside one physical table (`gallery`).

## 6) Why This Single-Table Design Was Chosen

Benefits:

- Less duplicate schema/code
- One upload pipeline for all image sections
- Easier shared features (status, category, uploader, timestamps)

Tradeoff:

- You must always filter by `display_section` correctly
- If filters are wrong, cross-section mixing can happen

(Your current code has section filters in place to prevent mixing.)

## 7) If You Really Want Separate Tables in Future

You would need:

1. New tables (`media`, `our_work`) in SQL.
2. Data migration from `gallery` by `display_section`.
3. Module query rewrites in:
   - `admin/modules/media/*`
   - `admin/modules/our-work/*`
   - `media-coverage.php`
   - `our-work.php`
4. Schema bootstrap updates in `admin/core/schema_bootstrap.php`.
5. Testing for create/edit/delete and frontend rendering.

This is possible, but it is a structural refactor, not a small config change.

## 8) Quick Verification Checklist (After Gmail Setup)

1. Save Gmail SMTP settings in `/admin/smtp-settings.php`.
2. Submit forgot-password for registered admin email.
3. Check `admin/activity-log.php` for reset request activity.
4. Check PHP error log for SMTP auth errors (if any).
5. Confirm Gmail Inbox/Spam/Promotions tabs.

---

If you want, I can also prepare a second file with a copy-paste Gmail SMTP checklist (with screenshots checklist items) for your team handover.
