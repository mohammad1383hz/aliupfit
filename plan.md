# AliUPFit Laravel Migration Plan

## Requirements
- Preserve the supplied Persian RTL public website's HTML structure, CSS declarations, class names, colors, typography, responsive rules, animations, assets and interactions. Do not redesign the public UI.
- Move the supplied one-page site into a Laravel Blade view and use Laravel routes/controllers for public rendering, database-backed site settings and form submissions.
- Provide persistent storage and a protected admin panel. Create administrator accounts only with an explicit Artisan command; do not ship a default password.
- Keep the existing page's PHP-free/browser-only appearance and behavior where possible; introduce backend requests only where data persistence is required.

## Implementation
- Laravel 13, PHP 8.3+, SQLite by default, configurable through standard Laravel `.env` for production database choice.
- Preserve the source HTML/CSS in `resources/views/site.blade.php`; keep public static assets embedded as supplied. Inject only server-provided site settings into the existing JS constants, add a CSRF meta token, and post valid registration/contact requests to Laravel.
- Persist settings as key/value JSON records and submitted forms as JSON payloads with type, status and timestamps. Validate requests server-side, rate limit public submission routes, and show records in admin.
- Protect admin routes with Laravel session auth and an `is_admin` user flag. Provide settings editing, form inbox/status updates, and safe administrator creation via a command.
- Keep admin UI and styling isolated from the public site's markup and styles.

## Project Structure
- `resources/views/site.blade.php`: original user-facing HTML and CSS, retained with minimal Blade injection points.
- `resources/views/admin/*`: separate admin login, dashboard, settings and submissions screens.
- `app/Http/Controllers/*`: public page/form endpoints and admin auth/management.
- `app/Models/*`: settings and form submission persistence.
- `database/migrations/*`, `database/seeders/*`: SQLite schema and original site defaults.
- `routes/web.php`: public routes and protected admin routes.
- `README.md`: setup, database, admin account creation, deployment notes.

## Design
The public-site design is the supplied `AliUPFit-site-background.html`; no alternate style or redesign is introduced. The admin area uses a minimal, clearly separated utility layout and does not change public-site CSS.
