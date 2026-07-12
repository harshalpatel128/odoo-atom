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
