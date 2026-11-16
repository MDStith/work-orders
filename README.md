# Work Orders

A full-stack work-order application built with Laravel, React, TypeScript, and MySQL. Authenticated users can view and filter orders, while Dispatchers can create orders and update their statuses.

Built as a portfolio project demonstrating server-side authorization, request validation, database persistence, automated testing, and continuous integration.

## Features

- Registration and authentication.
- Viewer and Dispatcher roles.
- Work-order listing, newest first.
- Filtering by Open, In progress, or Completed.
- Dispatcher-only work-order creation and status updates.
- New orders always start as Open.
- Active status filters preserved after updates.
- Form validation with inline error messages.
- Responsive layout, navigation, breadcrumbs, and empty-state messaging.
- Dashboard totals with links to status-filtered listings.
- Pagination with 10 orders per page and status filters preserved in navigation.
- Title and description search combined with status filtering and pagination.

## Technology

| Area                   | Technology                        |
| ---------------------- | --------------------------------- |
| Backend                | PHP 8.5, Laravel 13               |
| Frontend               | React 19, TypeScript, Inertia 3   |
| Styling                | Tailwind CSS 4                    |
| Database               | MySQL 8.4                         |
| Local environment      | Docker, Laravel Sail              |
| Frontend tooling       | Vite+                             |
| Testing                | PHPUnit, SQLite in memory         |
| Static analysis        | PHPStan with Larastan, TypeScript |
| Formatting and linting | Laravel Pint, Vite+ checks        |
| Continuous integration | GitHub Actions                    |

Inertia connects Laravel controllers to React pages. Laravel handles routing, authentication, authorization, validation, and database access; React renders the interface.

## Permissions

| Action                   | Guest | Viewer | Dispatcher |
| ------------------------ | ----- | ------ | ---------- |
| View and filter orders   | No    | Yes    | Yes        |
| Open the creation form   | No    | No     | Yes        |
| Create an order          | No    | No     | Yes        |
| Update an order's status | No    | No     | Yes        |

New registrations always receive the Viewer role. Submitted role values cannot grant Dispatcher access.

Laravel policies enforce creation and update permissions on the server. The interface also hides controls from Viewers.

Supported statuses are `open`, `in_progress`, and `completed`. Dispatchers can select any supported status; sequential transitions are not enforced.

## Local installation

### Prerequisites

- Git.
- Docker with Docker Compose; on Windows, Docker Desktop with Ubuntu through Windows Subsystem for Linux.
- PHP 8.5 and Composer 2 with the extensions required by the locked dependencies.
- Node.js and npm. GitHub Actions uses Node.js 22.
- Ports 80 and 3306 available, or alternative host ports configured.

Run the following commands in a Linux or Ubuntu terminal.

### Clone and install dependencies

```bash
git clone https://github.com/MDStith/work-orders.git
cd work-orders
composer install
cp .env.example .env
```

### Configure the local environment

Set the following values in `.env` for a new local installation:

```dotenv
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=work_orders
DB_USERNAME=sail
DB_PASSWORD=password
```

These are example local development credentials. Keep `.env` out of version control.

The database host is `mysql` because Laravel connects to the MySQL service inside Docker.

### Start the application

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate
npm install
npm run build
```

If MySQL is still initializing when migrations run, wait until it is ready and retry the migration command.

Open http://localhost/register to create a Viewer account. After signing in, use the Work Orders navigation link.

The application can serve built frontend assets without a running frontend development server. Re-run `npm run build` after frontend changes.

### Add sample work orders

```bash
./vendor/bin/sail artisan db:seed --class=WorkOrderSeeder
```

The work-order seeder uses `updateOrCreate()` by title, allowing it to update its sample records on subsequent runs.

### Grant local Dispatcher access

There is no role-management interface. To promote a registered local account, open Laravel Tinker:

```bash
./vendor/bin/sail artisan tinker
```

Replace the example email below with the email used during registration:

```php
$user = App\Models\User::where('email', 'your-email@example.com')->firstOrFail();
$user->role = 'dispatcher';
$user->save();
exit
```

Refresh the application to see the creation and status-update controls.

The role is assigned directly because it is excluded from mass assignment.

### Stop and restart

Stop the containers:

```bash
./vendor/bin/sail down
```

Start them again:

```bash
./vendor/bin/sail up -d
```

The MySQL database persists in a Docker volume between normal stops and starts.

## Quality checks

Build frontend assets before running PHP tests, particularly after adding a React page:

```bash
npm run build
```

Run the configured checks:

```bash
npm run check
npm run types:check
./vendor/bin/sail composer ci:check
```

The Composer check also runs the frontend checks, verifies PHP formatting, performs PHP static analysis, and runs the PHP tests. The first two commands can be used independently for faster frontend feedback.

To apply formatting fixes:

```bash
npm run check:fix
./vendor/bin/sail pint
```

To run only the PHP test suite:

```bash
./vendor/bin/sail artisan test
```

Tests use an in-memory SQLite database, separate from the local MySQL application database.

### Application test coverage

- Viewer role assignment during registration.
- Rejection of Dispatcher role injection during registration.
- Role helper and policy behavior.
- Authentication requirements.
- Listing permissions, filtering, empty results, and ordering.
- Dispatcher-only creation and status updates.
- Required fields, length limits, invalid types, and invalid statuses.
- Forced Open status on creation.
- Filter preservation after updates.
- Database outcomes for permitted and rejected operations.

Three existing authentication tests are skipped because two-factor authentication is disabled.

GitHub Actions runs the configured quality checks on the repository.

## Key application files

| Path                                           | Responsibility                                   |
| ---------------------------------------------- | ------------------------------------------------ |
| `routes/web.php`                               | Authenticated application routes                 |
| `app/Http/Controllers/WorkOrderController.php` | Listing, filtering, creation, and status updates |
| `app/Policies/WorkOrderPolicy.php`             | Dispatcher authorization                         |
| `app/Models/WorkOrder.php`                     | Work-order model                                 |
| `resources/js/pages/work-orders/index.tsx`     | Listing, filtering, and update controls          |
| `resources/js/pages/work-orders/create.tsx`    | Creation form and validation messages            |
| `tests/Feature/`                               | Feature and authorization tests                  |
| `.github/workflows/tests.yml`                  | Continuous integration workflow                  |
| `app/Http/Controllers/DashboardController.php` | Dashboard status counts                          |

## Current scope and planned work

The current application supports viewing, filtering, creating, and updating work-order statuses.

Role administration, work-order deletion, and title/description editing are outside the current scope.

Planned work includes deployment to Amazon Web Services, using EC2 for application hosting, RDS for the database, and CloudWatch for monitoring. Deployment is not yet implemented.
