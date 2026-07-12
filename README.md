# Enterprise Asset & Resource Management System

A Core PHP 8+, MySQL 8, Bootstrap 5 ARMS scaffold with separated authentication, user panel, and admin panel.

## Run

1. Import `database/schema.sql` in MySQL. The script creates the `arms` database.
2. Update database credentials in `config/config.php` if your Laragon/MySQL user is not `root` with an empty password.
3. Open `http://localhost/oddo/odoo-atom/public/login` in the browser.

If Laragon points directly to this project folder, open `http://localhost/odoo-atom/public/login` or `http://odoo-atom.test/login` instead.

Default admin:

- Email: `admin@arms.local`
- Password: `Admin@12345`

Signup always creates an Employee account. Admin users can promote employees from the Admin Employee Directory.

## Delivery Checklist

- [x] Four-role access control: Admin, Asset Manager, Department Head, and Employee. Management routes are role-gated and employee self-service data is limited to the signed-in user.
- [x] Asset lifecycle, allocation/return history, transfers, overlap-protected bookings, maintenance history with photos and repair cost, audit cycles/discrepancies, notifications, and activity logs.
- [x] Live MySQL dashboard cards/charts, CSV/Excel exports and browser print-to-PDF report output, dark mode, responsive navigation, loading state, and toast UI.
- [x] PDO prepared statements, password hashing, CSRF tokens, escaped output, session regeneration, MIME-checked randomized uploads, validation, and indexed workflow queries.
- [x] In-app booking reminders and overdue-return notifications are generated on dashboard access; booking state is refreshed to Upcoming, Ongoing, or Completed from its times.
- [x] Reports export CSV and Excel, and use the browser print dialog’s Save as PDF capability for PDF output without a third-party dependency.

## Existing Database Upgrade

For an already-imported database, run `database/patch_users_auth_columns.sql`, `database/patch_assets_columns.sql`, and `database/patch_workflows.sql` in phpMyAdmin after backing up the database. A fresh install needs only `database/schema.sql`.
