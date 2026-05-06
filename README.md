# BarcodePapel

Laravel 13 application with Alpine.js, Tailwind CSS, and Blade. Designed to be deployed on Coolify (self-hosted PaaS).

## Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 13, PHP 8.3+ |
| Frontend | Alpine.js 3, Tailwind CSS 3, Blade |
| Database | MySQL 8+ or PostgreSQL 16+ |
| Build | Vite 5 |
| Container | Docker (PHP-FPM + Nginx + Supervisor) |
| Deploy | Coolify (self-hosted PaaS) |

---

## Prerequisites

- PHP 8.3+
- Composer 2.x
- Node.js 20+ and npm
- MySQL 8+ or PostgreSQL 16+
- Docker and Docker Compose (for containerized development)

---

## Development Setup

### 1. Clone and enter the project

```bash
git clone https://github.com/your-org/barcodepapel.git
cd barcodepapel
```

### 2. Install PHP dependencies

```bash
composer install
```

### 3. Install Node dependencies

```bash
npm install
```

### 4. Configure environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and set your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=barcodepapel
DB_USERNAME=your_user
DB_PASSWORD=your_password
```

### 5. Run database migrations

```bash
php artisan migrate
```

Optionally seed the database with test data:

```bash
php artisan db:seed
```

### 6. Create storage symlink

```bash
php artisan storage:link
```

### 7. Start the development servers

In one terminal, start Vite (asset bundler):

```bash
npm run dev
```

In another terminal, start Laravel:

```bash
php artisan serve
```

Visit `http://localhost:8000`.

---

## Docker Compose (local development)

```bash
# Copy and configure environment
cp .env.example .env
# Set APP_KEY before starting
php artisan key:generate --show   # Copy the output

# Edit docker-compose.yml and add the APP_KEY value, then:
docker compose up -d

# The app will be available at http://localhost:8080
# Mailpit web UI at http://localhost:8025
```

---

## Available Artisan Commands

```bash
# Run database migrations
php artisan migrate

# Rollback last migration batch
php artisan migrate:rollback

# Fresh migration with seeds
php artisan migrate:fresh --seed

# Process queue jobs
php artisan queue:work

# Run task scheduler (every minute via cron)
php artisan schedule:run

# Cache configuration for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Clear all caches
php artisan optimize:clear
```

---

## Running Tests

```bash
# Run all tests with Pest
php artisan test

# Run with coverage report
php artisan test --coverage

# Run a specific test file
php artisan test tests/Feature/Auth/AuthenticationTest.php

# Run tests in parallel
php artisan test --parallel
```

---

## Code Style

```bash
# Check code style
./vendor/bin/pint --test

# Fix code style
./vendor/bin/pint
```

---

## Deploying to Coolify

### Prerequisites

- A Coolify instance (v4+) running and accessible.
- A GitHub/GitLab repository connected to Coolify.
- A MySQL or PostgreSQL service created in Coolify.

### Step-by-step Coolify deployment

**1. Create a new application in Coolify**

- Source: your Git repository
- Build pack: **Dockerfile**
- Branch: `main` (or your production branch)
- Port: `80`

**2. Configure environment variables in Coolify**

Go to your application > Environment Variables and add:

```env
APP_NAME=BarcodePapel
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:YOUR_GENERATED_KEY_HERE
APP_URL=https://your-domain.com
APP_TIMEZONE=America/Mexico_City
APP_LOCALE=es

# Database — use the internal Coolify service hostname
DB_CONNECTION=mysql
DB_HOST=your-coolify-mysql-hostname
DB_PORT=3306
DB_DATABASE=barcodepapel
DB_USERNAME=barcodepapel
DB_PASSWORD=your_secure_password

# Session & Cache (using database — no Redis needed)
SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=database
QUEUE_CONNECTION=database

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.your-provider.com
MAIL_PORT=587
MAIL_SCHEME=tls
MAIL_USERNAME=your@email.com
MAIL_PASSWORD=your_mail_password
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME=BarcodePapel

# Health check
HEALTH_CHECK_TOKEN=your-random-secret-token
```

**3. Generate APP_KEY**

Run this locally and copy the output to Coolify:

```bash
php artisan key:generate --show
```

**4. Health check**

Configure Coolify health check:
- Path: `/up`
- Interval: 30s
- Timeout: 10s

**5. Deploy**

Click "Deploy" in Coolify. The entrypoint script will automatically:

1. Wait for the database to be ready.
2. Run `php artisan migrate --force`.
3. Cache config, routes, views, and events.
4. Start PHP-FPM, Nginx, the queue worker (2 processes), and the scheduler via Supervisor.

---

## Architecture

```
barcodepapel/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/          # Authentication controllers
│   │   │   ├── DashboardController.php
│   │   │   └── HealthController.php
│   │   └── Requests/Auth/
│   │       └── LoginRequest.php
│   ├── Models/
│   │   └── User.php
│   └── Providers/
│       └── AppServiceProvider.php
├── bootstrap/
│   ├── app.php                # Laravel 13 bootstrap
│   └── providers.php
├── config/
│   ├── app.php
│   ├── database.php
│   └── health.php
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
├── docker/
│   ├── nginx/
│   │   ├── nginx.conf
│   │   └── default.conf
│   ├── php/
│   │   ├── php.ini
│   │   └── php-fpm.conf
│   ├── entrypoint.sh
│   └── supervisord.conf
├── public/
├── resources/
│   ├── css/app.css            # Tailwind CSS
│   ├── js/app.js              # Alpine.js
│   └── views/
│       ├── auth/              # Login, register, forgot-password, etc.
│       ├── components/        # Blade components
│       ├── layouts/
│       │   ├── app.blade.php  # Authenticated layout
│       │   └── guest.blade.php # Guest layout
│       └── dashboard.blade.php
├── routes/
│   ├── web.php
│   ├── api.php
│   └── console.php
├── tests/
│   ├── Feature/
│   │   ├── Auth/
│   │   ├── DashboardTest.php
│   │   └── HealthCheckTest.php
│   └── Unit/
│       └── UserTest.php
├── Dockerfile
├── docker-compose.yml
├── vite.config.js
├── tailwind.config.js
└── .env.example
```

---

## Authentication Features

| Feature | Route | Description |
|---------|-------|-------------|
| Login | `GET/POST /login` | Email + password with rate limiting |
| Register | `GET/POST /register` | Name, email, password with confirmation |
| Logout | `POST /logout` | Session invalidation |
| Forgot Password | `GET/POST /forgot-password` | Email reset link |
| Reset Password | `GET/POST /reset-password/{token}` | Token-based reset |
| Email Verification | `GET /verify-email` | Required before dashboard access |
| Confirm Password | `GET/POST /confirm-password` | For sensitive operations |

---

## Frontend Components

Alpine.js global stores:

- `$store.notifications` — toast notification system with `success()`, `error()`, `warning()`
- `$store.ui` — sidebar toggle, dark mode with `localStorage` persistence

Alpine.js reusable data:

- `dropdown(defaultOpen)` — dropdown menus with click-outside close
- `modal(defaultOpen)` — modal dialogs with body scroll lock
- `asyncForm()` — forms with loading state and validation error handling

Blade components:

- `<x-input-label>` — form labels with optional required indicator
- `<x-text-input>` — text inputs with error state styling
- `<x-input-error>` — validation error display
- `<x-primary-button>` — submit button with loading state
- `<x-checkbox>` — styled checkbox input
- `<x-auth-session-status>` — session status alert

---

## Production Checklist

Before going live:

- [ ] Set `APP_ENV=production` and `APP_DEBUG=false`
- [ ] Generate a fresh `APP_KEY` and keep it secret
- [ ] Configure a real mail provider (not `log`)
- [ ] Set `SESSION_SECURE_COOKIE=true` (HTTPS enforced)
- [ ] Review and set `BCRYPT_ROUNDS=12` (already the default)
- [ ] Configure proper database backups
- [ ] Set up monitoring/alerting
- [ ] Review Nginx headers in `docker/nginx/nginx.conf`
- [ ] Configure a CDN for static assets (optional)

---

## License

MIT
