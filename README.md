# Work Orders

A full-stack work-order application built with Laravel, React, TypeScript, and MySQL. Authenticated users can view, search, filter, and paginate orders. Dispatchers can also create orders, edit their details, update statuses, and delete orders after confirmation.

Built as a portfolio project demonstrating server-side authorization, request validation, database persistence, automated testing, and continuous integration.

## Features

- Registration and authentication.
- Viewer and Dispatcher roles.
- Work-order listing, newest first, with consistent ordering for matching timestamps.
- Filtering by Open, In Progress, or Completed.
- Title and description search combined with status filtering.
- Pagination with 10 orders per page and search and status filters preserved in navigation.
- Dispatcher-only work-order creation.
- New orders always start as Open, regardless of submitted status.
- Dispatcher-only editing of work-order titles and descriptions.
- Separate status updates that preserve the active search and status filters.
- Dispatcher-only deletion with a confirmation prompt.
- Deletion preserves search and status filters and returns to page one.
- Form validation with inline error messages.
- Responsive layout, navigation, breadcrumbs, and empty-state messaging.
- Dashboard totals with links to status-filtered listings.

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

| Action                                    | Guest | Viewer | Dispatcher |
| ----------------------------------------- | ----- | ------ | ---------- |
| View, search, filter, and paginate orders | No    | Yes    | Yes        |
| Open the creation form                    | No    | No     | Yes        |
| Create an order                           | No    | No     | Yes        |
| Update an order's status                  | No    | No     | Yes        |
| Edit title and description                | No    | No     | Yes        |
| Delete an order                           | No    | No     | Yes        |

New registrations always receive the Viewer role. Submitted role values cannot grant Dispatcher access.

Laravel policies enforce creation, editing, status-update, and deletion permissions on the server. The interface also hides restricted controls from Viewers.

Supported statuses are `open`, `in_progress`, and `completed`. Dispatchers can select any supported status; sequential transitions are not enforced.

Editing an order's title and description does not change its status. Deletion permanently removes the order; there is no restore feature.

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

The example environment defaults to SQLite. For the MySQL database provided by Laravel Sail, set these values in `.env`, replacing any existing or commented versions:

```dotenv
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=work_orders
DB_USERNAME=sail
DB_PASSWORD=password
```

These credentials are for local development.

`DB_HOST=mysql` refers to the MySQL service inside Docker. Sail serves the application at http://localhost on port 80.

If running a separate clone alongside another installation, give it a unique Docker Compose project name in `.env`:

```dotenv
COMPOSE_PROJECT_NAME=work-orders-install-check
```

Stop the other installation's containers before starting this clone because they use the same host ports.

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

Refresh the application to see creation, editing, status-update, and deletion controls.

The role is assigned directly because it is excluded from mass assignment.

To return the account to Viewer access, repeat the lookup and save with `$user->role = 'viewer';`.

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

Run the complete configured check suite:

```bash
./vendor/bin/sail composer ci:check
```

The Composer check runs frontend formatting and lint checks, TypeScript checks, PHP formatting verification, PHP static analysis, and PHP tests.

For faster frontend feedback, run these checks independently:

```bash
npm run check
npm run types:check
```

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
- Pagination, matching timestamps, and filter preservation.
- Title and description search, combined filters, literal wildcard characters, and searches for `"0"`.
- Dispatcher-only creation and forced Open status.
- Status-update authorization, validation, and search and filter preservation.
- Edit-form values, editing authorization, and title and description validation.
- Protection against status changes through the details-editing endpoint.
- Deletion authorization and removal of only the selected order.
- Search and status-filter preservation after deletion.
- Rejection of invalid deletion filters without removing the order.
- Missing-order responses.
- Dashboard counts for both roles and empty databases.
- Database outcomes for permitted and rejected operations.

Three existing authentication tests are skipped because two-factor authentication is disabled.

GitHub Actions runs the configured quality checks on the repository.

## Key application files

| Path                                           | Responsibility                                                                          |
| ---------------------------------------------- | --------------------------------------------------------------------------------------- |
| `routes/web.php`                               | Authenticated application routes                                                        |
| `app/Http/Controllers/WorkOrderController.php` | Listing, search, filtering, pagination, creation, editing, status updates, and deletion |
| `app/Http/Controllers/DashboardController.php` | Dashboard status counts                                                                 |
| `app/Policies/WorkOrderPolicy.php`             | Dispatcher authorization                                                                |
| `app/Models/WorkOrder.php`                     | Work-order model                                                                        |
| `resources/js/pages/work-orders/index.tsx`     | Listing, search, filters, pagination, status updates, and deletion controls             |
| `resources/js/pages/work-orders/create.tsx`    | Creation form and validation messages                                                   |
| `resources/js/pages/work-orders/edit.tsx`      | Details-editing form and validation messages                                            |
| `tests/Feature/`                               | Feature and authorization tests                                                         |
| `.github/workflows/tests.yml`                  | Continuous integration workflow                                                         |

## Current scope and planned work

The application supports viewing, searching, filtering, paginating, creating, editing, updating statuses, and deleting work orders, with server-enforced Viewer and Dispatcher permissions.

Role administration and restoration of deleted orders are outside the current scope.

The next phase focuses on clean-clone installation verification, remaining starter-kit branding and action feedback as needed, README screenshots, and deployment preparation.

Planned deployment uses Amazon Web Services: EC2 for application hosting, RDS for the database, HTTPS for secure access, and CloudWatch for monitoring. Deployment is not yet implemented.
