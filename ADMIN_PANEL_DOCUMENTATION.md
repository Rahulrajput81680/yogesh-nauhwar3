# Admin Panel — Complete Documentation

> **Date written:** March 5, 2026  
> **Stack:** PHP (no framework), MySQL / MariaDB, Bootstrap 5, Vanilla JS

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [Directory & File Map](#2-directory--file-map)
3. [Roles & Permissions](#3-roles--permissions)
4. [How Delete Actions Are Restricted](#4-how-delete-actions-are-restricted)
5. [Admin vs Super Admin — Key Differences](#5-admin-vs-super-admin--key-differences)
6. [Security Rules & Implementation](#6-security-rules--implementation)
7. [Superadmin / Subadmin / Users — How the Hierarchy Works](#7-superadmin--subadmin--users--how-the-hierarchy-works)
8. [Module-by-Module Walkthrough](#8-module-by-module-walkthrough)
9. [Core File Reference](#9-core-file-reference)
10. [Configuration Reference](#10-configuration-reference)
11. [Known Bugs Fixed](#11-known-bugs-fixed)

---

## 1. Project Overview

A **self-contained, multi-role PHP admin panel** designed to be dropped into any website project. It manages:

- Blog posts (rich CMS, soft-delete / trash)
- Gallery images
- Hero/banner section
- Contact form submissions
- Dynamic form builder + submission viewer
- User (admin) management
- Activity log
- Role-based access control (RBAC)
- Password reset via email
- File uploads with MIME verification

The panel is **module-driven**: each feature (blog, gallery, forms, roles, etc.) can be toggled on/off in `config/modules.php` without touching any other code. When the `roles` module is **disabled**, every logged-in user is treated as superadmin — useful for solo / simple sites.

---

## 2. Directory & File Map

```
admin/
├── init.php                ← Bootstrap file — every page starts here
├── index.php               ← Redirects to login or dashboard
├── login.php               ← Login form + brute-force protection
├── logout.php              ← Destroys session, redirects to login
├── dashboard.php           ← Overview stats + recent activity
├── profile.php             ← Self-edit: name, email, password, avatar
├── activity-log.php        ← Read-only audit trail (superadmin only)
├── forgot-password.php     ← Request password-reset link
├── reset-password.php      ← Consume token + set new password
│
├── config/
│   ├── config.php          ← DB, URLs, mail, upload & security constants
│   └── modules.php         ← Toggle each module on/off (true/false)
│
├── core/
│   ├── security.php        ← CSRF, session, sanitise, escape, redirect, hash
│   ├── functions.php       ← General helpers (pagination, logging, badges…)
│   ├── permissions.php     ← RBAC engine — role→permission map + helpers
│   ├── mailer.php          ← SMTP / mail() email sender (no library)
│   ├── uploader.php        ← FileUploader class: validation, MIME, thumb
│   └── password_reset.php  ← Token generation, DB handling, email dispatch
│
├── includes/
│   ├── header.php          ← HTML head, navbar, sidebar (role-aware nav)
│   └── footer.php          ← Closing tags, Bootstrap JS
│
├── assets/
│   ├── css/admin.css       ← Custom styles on top of Bootstrap 5
│   └── js/admin.js         ← Confirm-delete prompts, AJAX field editor, etc.
│
└── modules/
    ├── blog/               ← Blog CRUD + soft-delete + restore
    ├── contact/            ← Read / delete contact messages
    ├── forms/              ← Form builder CRUD + submission viewer
    ├── gallery/            ← Image gallery CRUD
    ├── hero/               ← Single hero/banner record edit
    └── users/              ← Admin user CRUD (restricted by role)

database/
└── schema.sql              ← Full table definitions (run once to set up)

uploads/
├── blog/                   ← Blog featured images
├── forms/                  ← Form file-upload field attachments
├── gallery/                ← Gallery images
└── hero/                   ← Hero banner image
```

---

## 3. Roles & Permissions

Roles are defined in `core/permissions.php`. The `roles` module must be **enabled** in `config/modules.php` for these restrictions to take effect. When disabled, everyone has full access.

### Permission Map

| Permission Key | superadmin | admin | editor | teacher |
| -------------- | :--------: | :---: | :----: | :-----: |
| blog_view      |     ✅     |  ✅   |   ✅   |   ✅    |
| blog_create    |     ✅     |  ✅   |   ✅   |   ✅    |
| blog_edit      |     ✅     |  ✅   |   ✅   |   ✅    |
| blog_delete    |     ✅     |  ✅   |   ❌   |   ❌    |
| blog_restore   |     ✅     |  ✅   |   ❌   |   ❌    |
| gallery_view   |     ✅     |  ✅   |   ✅   |   ✅    |
| gallery_create |     ✅     |  ✅   |   ✅   |   ❌    |
| gallery_edit   |     ✅     |  ✅   |   ✅   |   ❌    |
| gallery_delete |     ✅     |  ✅   |   ❌   |   ❌    |
| hero_view      |     ✅     |  ✅   |   ✅   |   ❌    |
| hero_edit      |     ✅     |  ✅   |   ❌   |   ❌    |
| contact_view   |     ✅     |  ✅   |   ✅   |   ✅    |
| contact_delete |     ✅     |  ✅   |   ❌   |   ❌    |
| forms_view     |     ✅     |  ✅   |   ✅   |   ✅    |
| forms_create   |     ✅     |  ✅   |   ✅   |   ✅    |
| forms_edit     |     ✅     |  ✅   |   ❌   |   ❌    |
| forms_delete   |     ✅     |  ✅   |   ❌   |   ❌    |
| users_view     |     ✅     |  ✅   |   ❌   |   ❌    |
| users_create   |     ✅     |  ✅   |   ❌   |   ❌    |
| users_edit     |     ✅     |  ✅   |   ❌   |   ❌    |
| users_delete   |     ✅     |  ✅   |   ❌   |   ❌    |
| profile_view   |     ✅     |  ✅   |   ✅   |   ✅    |
| profile_edit   |     ✅     |  ✅   |   ✅   |   ✅    |

> **superadmin** uses the wildcard `['*']` — it passes every `has_permission()` call automatically without being listed per-permission.

---

## 4. How Delete Actions Are Restricted

Delete restrictions are enforced at **three layers**:

### Layer 1 — PHP Gate (server-side, hard enforcement)

Every module that performs a delete checks `require_permission()` or `has_permission()` before executing the SQL. Example from `modules/blog/delete.php`:

```php
require_permission('blog_delete');   // redirects to dashboard if not allowed
```

`require_permission()` is defined in `core/permissions.php`:

```php
function require_permission(string $permission): void
{
    if (!has_permission($permission)) {
        set_flash('error', 'You do not have permission to perform this action.');
        redirect(ADMIN_URL . '/dashboard.php');
        exit;
    }
}
```

This means **even if someone crafts a direct URL**, the server blocks the action before any SQL runs.

### Layer 2 — CSRF Token Validation

Every destructive action validates a CSRF token:

```php
if (!validate_csrf_token($_GET['csrf'] ?? '')) {
    // reject
}
```

Tokens expire after `CSRF_TOKEN_EXPIRE` seconds (default 3600). This prevents cross-site request forgery attacks disguised as delete requests.

### Layer 3 — UI Hide (client-side, UX only)

Delete buttons and links are wrapped in `has_permission()` checks in the template so that editor/teacher roles never even see the button:

```php
<?php if (has_permission('blog_delete')): ?>
    <a href="delete.php?id=...&csrf=..." class="btn delete-btn">Delete</a>
<?php endif; ?>
```

This is a UX convenience — the real security is always the server-side gate (Layer 1).

### Where the logic lives

| File                            | Permission checked                       |
| ------------------------------- | ---------------------------------------- |
| `modules/blog/delete.php`       | `blog_delete`                            |
| `modules/gallery/delete.php`    | `gallery_delete`                         |
| `modules/contact/delete.php`    | `contact_delete`                         |
| `modules/forms/delete.php`      | `forms_delete`                           |
| `modules/forms/submissions.php` | `forms_delete` (UI wrap + inline delete) |
| `modules/users/delete.php`      | `users_delete`                           |
| `core/permissions.php`          | Central RBAC engine                      |

---

## 5. Admin vs Super Admin — Key Differences

| Capability                                             | superadmin  |         admin         |
| ------------------------------------------------------ | :---------: | :-------------------: |
| All content CRUD (blog, gallery, hero, contact, forms) |     ✅      |          ✅           |
| Delete any content                                     |     ✅      |          ✅           |
| Restore soft-deleted blog posts from Trash             |     ✅      |          ✅           |
| View / Create / Edit / Delete admin users              |     ✅      |          ✅           |
| View Activity Log (`activity-log.php`)                 |     ✅      |          ❌           |
| Can assign the **superadmin** role to a new user       |     ✅      |          ❌           |
| Create another superadmin account                      |     ✅      |          ❌           |
| Bypasses every `has_permission()` via wildcard `*`     |     ✅      |  ❌ (explicit list)   |
| Can delete their own account or other admins           |     ✅      | ✅ (via users module) |
| Module enable/disable (config file level)              | ✅ (manual) |          ❌           |

**Key point:** An `admin` has a fixed, explicit list of permissions. A `superadmin` carries the wildcard `['*']`, so even permissions added to the system in the future auto-apply without touching the permission map.

---

## 6. Security Rules & Implementation

### 6.1 Session Security

Configured in `init.php` before `session_start()`:

| Setting                    | Value                  | Purpose                                    |
| -------------------------- | ---------------------- | ------------------------------------------ |
| `session.cookie_httponly`  | `1`                    | Hides cookie from JavaScript (anti-XSS)    |
| `session.use_only_cookies` | `1`                    | Prevents session ID in URL                 |
| `session.cookie_samesite`  | `Strict`               | Blocks CSRF via cross-site requests        |
| `session.cookie_secure`    | `0` (set `1` on HTTPS) | Sends cookie only over HTTPS in production |
| Session ID regeneration    | Every 30 min           | Mitigates session fixation attacks         |
| Session timeout            | 3600 s (configurable)  | Auto-logout idle sessions                  |

### 6.2 CSRF Protection

Every form and every destructive GET action requires a valid CSRF token.

- **Token generation:** `generate_csrf_token()` in `core/security.php` — uses `bin2hex(random_bytes(32))` (cryptographically secure).
- **Token lifetime:** `CSRF_TOKEN_EXPIRE` constant (default 1 hour).
- **Validation:** `validate_csrf_token($token)` uses `hash_equals()` — constant-time comparison prevents timing attacks.
- **Forms:** embed with `<?php echo csrf_field(); ?>` which outputs a hidden input.
- **GET deletes:** token appended as `?csrf=<token>` in the link.

### 6.3 Password Security

- Passwords hashed with `PASSWORD_BCRYPT` at cost factor **12** via `hash_password()`.
- Verified with `password_verify()` (timing-safe).
- Minimum length enforced (`MIN_PASSWORD_LENGTH`, default 8).
- Password reset uses a 64-char hex token (`bin2hex(random_bytes(32))`), expires in **30 minutes**, and existing tokens are invalidated before issuing a new one.
- Reset endpoint (`reset-password.php`) uses constant-time DB lookup.

### 6.4 XSS Prevention

Two complementary functions in `core/security.php`:

- **`sanitize_input($data)`** — applied to all user input coming **in** (trim + stripslashes + `htmlspecialchars`).
- **`escape($data)`** — applied to all variables going **out** to HTML (wraps `htmlspecialchars` with `ENT_QUOTES`).

### 6.5 SQL Injection Prevention

All database queries use **PDO prepared statements** with parameterised values (`?` placeholders). Raw string interpolation into SQL is avoided except for pagination `LIMIT/OFFSET` values which are cast to `(int)` first.

### 6.6 File Upload Security

Handled by `core/uploader.php` (`FileUploader` class):

1. `$_FILES` error code checked before any processing.
2. File size validated against `MAX_UPLOAD_SIZE` (default 5 MB).
3. Extension whitelist checked: `jpg, jpeg, png, gif, webp`.
4. **MIME type verified** using `finfo(FILEINFO_MIME_TYPE)` — prevents disguised executables (e.g., an `.php` file renamed to `.jpg`).
5. Uploaded filename is sanitised with `sanitize_filename()` (strips everything except `a-z A-Z 0-9 . _ -`), then a unique timestamp+uniqid suffix is added to prevent collisions and path traversal.
6. Files stored outside the web root structure is recommended; currently stored in `/uploads/` subdirectories.

### 6.7 Direct File Access Prevention

Every core and config file checks for the `ADMIN_INIT` constant defined only by `init.php`:

```php
if (!defined('ADMIN_INIT')) {
    die('Direct access not permitted');
}
```

### 6.8 Rate Limiting / Brute Force (Login)

The login page tracks failed attempts in the session and can be extended with IP-based rate limiting. The password reset flow deliberately returns the same message whether or not an email exists ("If your email is registered…") to prevent user enumeration.

### 6.9 Output Encoding

All dynamic values in HTML are passed through `escape()`. All database values used in SQL are bound as parameters. Content-Type headers default to `text/html; charset=UTF-8`.

---

## 7. Superadmin / Subadmin / Users — How the Hierarchy Works

```
superadmin
    │
    ├── Can create / edit / delete any user including other admins
    ├── Can assign any role (superadmin, admin, editor, teacher)
    ├── Sees activity log of all users
    │
    └── admin  (subadmin)
            │
            ├── Full content management
            ├── Can manage users EXCEPT cannot promote to superadmin
            ├──
            │
            ├── editor
            │       Can create & edit content
            │       Cannot delete anything
            │       Cannot manage users
            │
            └── teacher
                    Can view most content & create blog/form entries
                    Cannot delete anything
                    Cannot manage users
```

### User Lifecycle

1. **Creation** — `modules/users/create.php` (requires `users_create`). Superadmin can set any role; admin can set admin/editor/teacher.
2. **Edit** — `modules/users/edit.php` (requires `users_edit`). Password field is optional on edit (leave blank = keep current).
3. **Delete** — `modules/users/delete.php` (requires `users_delete`). Cannot self-delete (the panel checks `$user_id !== $_SESSION['admin_id']`).
4. **Profile self-edit** — `profile.php` — any logged-in user can update their own name, email, avatar and password. No permission needed beyond being logged in.
5. **Password reset** — any user (even logged out) can trigger a reset via `forgot-password.php` → email link → `reset-password.php`.

### Session Variables Set on Login

```php
$_SESSION['admin_logged_in'] = true;
$_SESSION['admin_id']        = $user['id'];
$_SESSION['admin_username']  = $user['username'];
$_SESSION['admin_role']      = $user['role'];   // 'superadmin'|'admin'|'editor'|'teacher'
$_SESSION['admin_full_name'] = $user['full_name'];
$_SESSION['last_activity']   = time();
```

The `admin_role` session value is what `has_permission()` reads on every page load.

---

## 8. Module-by-Module Walkthrough

### 8.1 Blog (`modules/blog/`)

| File         | Purpose                                                                                                                          |
| ------------ | -------------------------------------------------------------------------------------------------------------------------------- |
| `index.php`  | List all posts with search, status filter, pagination. Supports a Trash view (soft-deleted posts) for roles with `blog_restore`. |
| `create.php` | New post form. Requires `blog_create`.                                                                                           |
| `edit.php`   | Edit existing post. Requires `blog_edit`.                                                                                        |
| `delete.php` | Soft-delete (sets `deleted_at`) or permanent purge from trash. Requires `blog_delete`.                                           |

**Soft Delete:** Posts are never immediately removed. `delete.php` sets `deleted_at = NOW()`. The index query filters `WHERE b.deleted_at IS NULL` (normal view) or `IS NOT NULL` (trash). Restoring clears `deleted_at`.

### 8.2 Gallery (`modules/gallery/`)

| File         | Purpose                                                 |
| ------------ | ------------------------------------------------------- |
| `index.php`  | Grid view of all gallery images.                        |
| `create.php` | Upload image + title/description. Uses `FileUploader`.  |
| `edit.php`   | Update metadata; optionally replace image.              |
| `delete.php` | Delete record + physical file from `/uploads/gallery/`. |

### 8.3 Hero (`modules/hero/`)

| File        | Purpose                                                                            |
| ----------- | ---------------------------------------------------------------------------------- |
| `index.php` | Display current hero config.                                                       |
| `edit.php`  | Edit headline, subtext, CTA button, background image. Only one hero record exists. |

### 8.4 Contact (`modules/contact/`)

| File         | Purpose                                                     |
| ------------ | ----------------------------------------------------------- |
| `index.php`  | Table of all contact form submissions from the public site. |
| `view.php`   | Read full message detail + mark as read.                    |
| `delete.php` | Permanent delete. Requires `contact_delete`.                |

### 8.5 Form Builder (`modules/forms/`)

| File                  | Purpose                                                                                                                                                                                                                        |
| --------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `index.php`           | List all custom forms with submission counts.                                                                                                                                                                                  |
| `create.php`          | New form: name, slug, description, status.                                                                                                                                                                                     |
| `edit.php`            | Edit form metadata + add/edit/delete form fields. Field types: text, email, number, phone, date, textarea, select, radio, checkbox, file.                                                                                      |
| `delete.php`          | Delete form + all its fields + all its submissions. Requires `forms_delete`.                                                                                                                                                   |
| `submissions.php`     | View all submissions for a given form. Delete individual submissions (requires `forms_delete`, hidden for editor/teacher). Back button is role-aware: routes to `edit.php` if user has `forms_edit`, otherwise to `index.php`. |
| `view-submission.php` | Full detail view of one submission with all field values.                                                                                                                                                                      |

### 8.6 Users (`modules/users/`)

| File         | Purpose                                                  |
| ------------ | -------------------------------------------------------- |
| `index.php`  | List all admin users. Requires `users_view`.             |
| `create.php` | Create new admin user with role assignment.              |
| `edit.php`   | Edit user details / role. Cannot demote last superadmin. |
| `delete.php` | Remove user. Cannot self-delete.                         |

### 8.7 Activity Log (`activity-log.php`)

Read-only audit trail. Accessible by **superadmin only** (`require_permission` not used — guarded by role check in header nav). Every create/edit/delete action calls `log_activity($action, $module, $item_id, $details)` which inserts into the `activity_log` table.

---

## 9. Core File Reference

### `init.php`

Bootstraps the entire application:

1. Defines `ADMIN_INIT` constant (gates all core files).
2. Configures and starts PHP session with security flags.
3. Regenerates session ID every 30 minutes.
4. Loads `config/config.php` → `config/modules.php`.
5. Loads core files in order: security → functions → permissions → mailer → password_reset.
6. Calls `check_session_timeout()` for logged-in users.

### `core/security.php`

| Function                       | Description                                                         |
| ------------------------------ | ------------------------------------------------------------------- |
| `generate_csrf_token()`        | Creates/returns 64-hex CSRF token, stores in session with timestamp |
| `validate_csrf_token($token)`  | Validates token via `hash_equals` + expiry check                    |
| `csrf_field()`                 | Returns `<input type="hidden">` HTML with current token             |
| `sanitize_input($data)`        | Trim + stripslashes + htmlspecialchars (also works on arrays)       |
| `escape($data)`                | htmlspecialchars for output (also works on arrays)                  |
| `sanitize_filename($filename)` | Strips dangerous chars for safe upload filenames                    |
| `generate_slug($string)`       | Converts string to URL-safe slug                                    |
| `redirect($url)`               | `header(Location:)` + `exit()`                                      |
| `is_logged_in()`               | Checks `$_SESSION['admin_logged_in']`                               |
| `require_login()`              | Redirects to login if not authenticated                             |
| `check_session_timeout()`      | Logs out after `SESSION_TIMEOUT` seconds of inactivity              |
| `set_flash($type, $message)`   | Stores one-time message in session                                  |
| `get_flash()`                  | Returns + clears flash message                                      |
| `hash_password($pwd)`          | `password_hash` with bcrypt cost 12                                 |
| `verify_password($pwd, $hash)` | `password_verify`                                                   |

### `core/functions.php`

| Function                                 | Description                                   |
| ---------------------------------------- | --------------------------------------------- |
| `format_date($date)`                     | Formats date string with `date()`             |
| `format_datetime($datetime)`             | Formats datetime string                       |
| `truncate($string, $length)`             | Truncates with ellipsis                       |
| `create_pagination(...)`                 | Renders Bootstrap pagination HTML             |
| `log_activity(...)`                      | Inserts row in `activity_log` table           |
| `get_recent_activities($limit)`          | Fetches recent log entries with username JOIN |
| `count_records($table, $where, $params)` | Generic COUNT query                           |
| `get_status_badge($status)`              | Returns Bootstrap badge HTML                  |
| `is_module_enabled($name)`               | Checks modules config array                   |
| `require_module($name)`                  | Redirects to dashboard if module off          |

### `core/permissions.php`

| Function                          | Description                                                                            |
| --------------------------------- | -------------------------------------------------------------------------------------- |
| `has_permission($permission)`     | Returns bool — checks role map. Always true if roles module off or role is superadmin. |
| `require_permission($permission)` | Calls `has_permission`; on failure sets flash error and redirects to dashboard.        |
| `role_label($role)`               | Returns human-readable label ("Super Admin" etc.)                                      |
| `role_badge($role)`               | Returns Bootstrap badge HTML with role colour                                          |
| `get_roles()`                     | Returns associative array of all valid roles                                           |

### `core/mailer.php`

Provides `send_email($to, $subject, $htmlBody, $plainBody)` which:

- Uses `SimpleMailer` (built-in SMTP client with STARTTLS) if `MAIL_SMTP_HOST` is configured.
- Falls back to PHP native `mail()` if not.
- `SimpleMailer` handles EHLO, STARTTLS negotiation, AUTH LOGIN, MAIL FROM, RCPT TO, DATA, QUIT.

### `core/uploader.php` — `FileUploader` class

| Method                          | Description                                                |
| ------------------------------- | ---------------------------------------------------------- |
| `upload($file, $subfolder)`     | Validates + moves uploaded file, returns filename or false |
| `getErrors()`                   | Returns array of error strings                             |
| `delete($filename, $subfolder)` | Removes physical file from disk                            |
| `getUrl($filename, $subfolder)` | Returns public URL to file                                 |

### `core/password_reset.php`

| Function                                            | Description                                                      |
| --------------------------------------------------- | ---------------------------------------------------------------- |
| `generate_reset_token()`                            | 64-char hex from `random_bytes(32)`                              |
| `create_password_reset_request($pdo, $email)`       | Validates user, invalidates old tokens, inserts new, sends email |
| `validate_reset_token($pdo, $token)`                | Checks token exists, not used, not expired                       |
| `complete_password_reset($pdo, $token, $password)`  | Updates password, marks token used                               |
| `send_password_reset_email($user, $link, $expires)` | Builds HTML email + calls `send_email()`                         |

---

## 10. Configuration Reference

### `config/config.php`

| Constant                        | Default                                      | Purpose                                   |
| ------------------------------- | -------------------------------------------- | ----------------------------------------- |
| `DB_HOST/NAME/USER/PASS`        | localhost / shared-admin-panel / root / root | Database connection                       |
| `BASE_URL`                      | `http://localhost:5001`                      | Site root URL                             |
| `ADMIN_URL`                     | `BASE_URL . '/admin'`                        | Admin panel URL prefix                    |
| `PROJECT_NAME`                  | `Admin Panel`                                | Displayed in page titles & emails         |
| `SESSION_NAME`                  | md5 of BASE_URL                              | Unique cookie name per project            |
| `SESSION_TIMEOUT`               | `3600`                                       | Auto-logout after N seconds of inactivity |
| `UPLOAD_DIR`                    | `/uploads` (absolute)                        | Physical upload path                      |
| `UPLOAD_URL`                    | `BASE_URL . '/uploads'`                      | Public URL for uploads                    |
| `MAX_UPLOAD_SIZE`               | `5242880` (5 MB)                             | Per-file upload limit in bytes            |
| `ALLOWED_IMAGE_TYPES`           | jpg,jpeg,png,gif,webp                        | Accepted image extensions                 |
| `THUMB_WIDTH/HEIGHT`            | 300×300                                      | Thumbnail generation size                 |
| `ITEMS_PER_PAGE`                | `10`                                         | Pagination page size                      |
| `CSRF_TOKEN_EXPIRE`             | `3600`                                       | CSRF token lifetime in seconds            |
| `MIN_PASSWORD_LENGTH`           | `8`                                          | Minimum password length                   |
| `MAIL_FROM_ADDRESS`             | `noreply@<host>`                             | From address for all emails               |
| `MAIL_SMTP_HOST/PORT/USER/PASS` | _(empty)_                                    | Optional SMTP credentials                 |

### `config/modules.php`

| Key            | Default | Module                                        |
| -------------- | ------- | --------------------------------------------- |
| `blog`         | `true`  | Blog management                               |
| `gallery`      | `true`  | Gallery management                            |
| `hero`         | `true`  | Hero section editor                           |
| `contact`      | `true`  | Contact form submissions                      |
| `forms`        | `true`  | Dynamic form builder                          |
| `pages`        | `false` | Static page manager (planned)                 |
| `testimonials` | `false` | Testimonials (planned)                        |
| `roles`        | `true`  | RBAC — set false to give everyone full access |

---
