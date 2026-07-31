<p align="center">
  <img src="public/logo.svg" width="150" alt="MMS Group logo">
</p>

<h1 align="center">MMS Group Property Management Platform</h1>

<p align="center">
  One platform for property projects, bookings, installments, payments, plot inventory, allotments, commissions, and customer records.
</p>

## About the project

The MMS Group Property Management Platform is a Laravel application built to manage the complete property lifecycle—from project setup and plot inventory to customer booking, payment verification, installment tracking, and final plot allotment.

The application includes dedicated experiences for management, staff, agents, and customers. Public project information, images, descriptions, and blueprints are loaded from the database and displayed on the responsive welcome page.

## Main features

- Project management with descriptions, locations, images, and blueprints
- Block and plot inventory management
- Plot plan import and inventory review
- Package and payment-plan configuration
- Customer booking and approval workflow
- Installment schedules and overdue-balance tracking
- Payment proof submission and verification
- Printable payment receipts
- Project-based plot allotment
- Agent referrals and three-level commission tracking
- Commission withdrawal and payout management
- Customer, agent, staff, admin, and Super Admin roles
- Permission-based navigation and route protection
- Customer portal with bookings, payments, allotments, notifications, and referrals
- Agent portal with sales, commissions, and payout history
- Management dashboard with project-level business reporting
- Email campaigns, WhatsApp notifications, and payment gateway settings
- Activity/audit log
- Light and dark themes
- Passkey support
- Responsive public and authenticated interfaces

## Technology stack

- PHP 8.3+
- Laravel 13
- Laravel Breeze
- Blade and Alpine.js
- Tailwind CSS
- Vite
- MySQL/MariaDB or SQLite
- Spatie Laravel Permission
- Spatie Laravel Activitylog
- PHPUnit

## Requirements

Install the following before setting up the project:

- PHP 8.3 or newer
- Composer
- Node.js 20 or newer
- npm
- MySQL/MariaDB for production, or SQLite for local development

Required PHP extensions typically include:

```text
bcmath, ctype, curl, dom, fileinfo, filter, mbstring, openssl,
pdo, pdo_mysql or pdo_sqlite, session, tokenizer, xml
```

## Local installation

Clone the repository and enter the project directory:

```bash
git clone <repository-url> mmsg
cd mmsg
```

Install the application:

```bash
composer install
cp .env.example .env
php artisan key:generate
npm install
```

### Database configuration

For SQLite:

```bash
touch database/database.sqlite
```

Then keep the following value in `.env`:

```env
DB_CONNECTION=sqlite
```

For MySQL or MariaDB, update `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mmsg
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password
```

Run migrations and seed the required roles, permissions, payment methods, and Super Admin:

```bash
php artisan migrate --seed
php artisan storage:link
```

Build frontend assets:

```bash
npm run build
```

Start the local application:

```bash
composer run dev
```

Alternatively, run the services separately:

```bash
php artisan serve
php artisan queue:work
npm run dev
```

## Super Admin account

The database seeder creates the initial Super Admin from these `.env` values:

```env
SUPER_ADMIN_NAME="Super Admin"
SUPER_ADMIN_EMAIL=sadmin@example.com
SUPER_ADMIN_PASSWORD=P@ssWord
```

Default login:

```text
Email:    sadmin@example.com
Password: P@ssWord
```

> Change the default password immediately after the first login. For production, set a strong password in `.env` before running the seeder.

To run only the Super Admin seeder:

```bash
php artisan db:seed --class=SuperAdminSeeder
```

## Development commands

Run all tests:

```bash
composer test
```

Run a specific test:

```bash
php artisan test tests/Feature/DashboardTest.php
```

Format PHP files:

```bash
./vendor/bin/pint
```

Rebuild production assets:

```bash
npm run build
```

Clear application caches:

```bash
php artisan optimize:clear
```

## Uploaded files

Project images, project blueprints, payment proofs, and other uploaded assets use Laravel storage. Ensure the public storage link exists:

```bash
php artisan storage:link
```

The web server must be able to write to:

```text
storage/
bootstrap/cache/
```

## Queue and scheduler

The default environment uses the database queue:

```env
QUEUE_CONNECTION=database
```

Run the queue worker:

```bash
php artisan queue:work --tries=3
```

For production, configure a process manager so the queue worker remains active.

Add Laravel's scheduler to cron:

```cron
* * * * * cd /path/to/mmsg && php artisan schedule:run >> /dev/null 2>&1
```

## Email configuration

Local development defaults to logging emails:

```env
MAIL_MAILER=log
```

For production, configure a real SMTP provider:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_SCHEME=tls
MAIL_FROM_ADDRESS=no-reply@example.com
MAIL_FROM_NAME="${APP_NAME}"
```

Do not leave local mail services such as `127.0.0.1:1025` configured on production unless that service is actually running.

## WhatsApp notifications

WhatsApp Cloud API support can be configured with:

```env
WHATSAPP_NOTIFICATIONS_ENABLED=true
WHATSAPP_API_URL=https://graph.facebook.com/v23.0
WHATSAPP_PHONE_NUMBER_ID=
WHATSAPP_ACCESS_TOKEN=
WHATSAPP_DEFAULT_COUNTRY_CODE=92
WHATSAPP_NOTIFICATION_TEMPLATE=account_activity_update
WHATSAPP_NOTIFICATION_TEMPLATE_LANGUAGE=en
```

## Plot plan processing

AI-assisted plot plan reading can be enabled with:

```env
GEMINI_API_KEY=
GEMINI_VISION_MODEL=gemini-3.1-flash-lite
```

Never commit API keys or production credentials to the repository.

## Production deployment

Recommended production setup:

1. Point the domain document root to the project's `public` directory.
2. Create the production `.env` file.
3. Set `APP_ENV=production`, `APP_DEBUG=false`, and the correct `APP_URL`.
4. Configure the production database, mail provider, queue, and notification services.
5. Install optimized Composer dependencies.
6. Run database migrations.
7. Build frontend assets.
8. Create the storage link.
9. Cache configuration, routes, events, and views.
10. Start the queue worker and scheduler.

Typical deployment commands:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

The following directories must be writable by the web-server user:

```bash
chmod -R 775 storage bootstrap/cache
```

If the hosting account serves files from `public_html`, keep the Laravel application outside `public_html` where possible and point the domain's document root to the repository's `public` directory.

## Security checklist

- Never commit `.env`
- Disable debug mode in production
- Replace the default Super Admin password
- Use HTTPS
- Restrict database credentials to the production database
- Configure secure mail and notification credentials
- Run queue workers under a process manager
- Back up the database and uploaded files
- Review role permissions before creating staff accounts
- Keep PHP, Composer packages, and npm packages updated

## Project structure

```text
app/                 Application models, controllers, services, and policies
database/migrations  Database schema
database/seeders     Roles, permissions, payment methods, and admin seeders
public/              Public entry point, built assets, and branding
resources/views/     Blade templates
resources/js/        Frontend JavaScript
resources/css/       Tailwind application styles
routes/web.php       Web routes
tests/               Feature and unit tests
```

## License

This project is proprietary software developed for MMS Group. Unauthorized copying, redistribution, or commercial use is prohibited unless written permission is granted by MMS Group.
