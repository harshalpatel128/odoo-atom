# ARMS System Design

## Architecture

ARMS uses a lightweight MVC structure: `public/index.php` receives requests, `app/Core/Router.php` maps routes to controllers, controllers enforce middleware and render views, models use PDO for MySQL access, and Bootstrap views are split into `auth`, `admin`, and `user` panels.

## Panels

- Auth panel: `/login`, `/signup`, `/forgot-password`
- User panel: `/user/dashboard`, `/user/assets`, `/user/bookings`, `/user/maintenance`, `/user/transfers`
- Admin panel: `/admin/dashboard`, `/admin/organization`, `/admin/assets`, `/admin/allocations`, `/admin/maintenance`, `/admin/audits`, `/admin/reports`, `/admin/logs`

## Authentication Flow

Signup creates Employee users only. Login validates email, password hash, status, regenerates the session, logs the activity, then redirects by role. Admin users enter the Admin Panel. All other roles enter the User Panel unless more role-specific dashboards are added.

## Role Matrix

| Module | Admin | Department Head | Asset Manager | Employee |
| --- | --- | --- | --- | --- |
| Departments | Full | View department | No | No |
| Categories | Full | No | Manage assets | No |
| Employees and role promotion | Full | No | No | No |
| Assets | Full | View department | Full | View allocated |
| Allocation and transfers | Full | Approve | Approve | Request |
| Resource booking | Full | Book | Book | Book |
| Maintenance | Full | View | Approve/manage | Request |
| Audits | Full | View | Manage | Assigned tasks |
| Reports | Full | Department | Asset reports | Own records |
| Logs | Full | No | No | No |

## Business Rules

- Signup never exposes role selection.
- Only Admin can promote Employee accounts to Department Head or Asset Manager.
- Allocated assets cannot be allocated again; users should request transfer instead.
- Approved maintenance sets the asset to Under Maintenance.
- Resolved maintenance sets the asset to Available.
- Booking overlap rule rejects requests where `requested_start < existing_end` and `requested_end > existing_start`.
- Confirmed missing audit discrepancies update the asset lifecycle to Lost.

## Database

The full MySQL schema is in `database/schema.sql`. It includes users, roles, sessions, password resets, departments, categories, assets, documents, history, allocations, transfers, resources, bookings, maintenance, audits, notifications, and activity logs with foreign keys.

## Security

The scaffold includes password hashing, session regeneration on login, CSRF tokens for state-changing forms, role middleware, prepared statements, status checks, and activity logging. Production hardening should add HTTPS-only cookies, email delivery, stricter upload validation, and per-permission middleware for Department Head and Asset Manager workflows.

## Deployment

Import `database/schema.sql`, update `config/config.php`, point the web server to `public`, and verify login with `admin@arms.local` and `Admin@12345`.
