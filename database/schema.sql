CREATE DATABASE IF NOT EXISTS arms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE arms;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS activity_logs, notifications, audit_discrepancies, audit_items, audit_assets, audit_cycles;
DROP TABLE IF EXISTS maintenance_updates, maintenance_history, maintenance_requests, resource_bookings, resources;
DROP TABLE IF EXISTS asset_return_history, asset_transfer_requests, asset_transfers, asset_allocations;
DROP TABLE IF EXISTS asset_documents, asset_images, asset_history, assets, employees, asset_categories;
DROP TABLE IF EXISTS user_sessions, password_resets, settings, permissions, departments, users, roles;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL,
    slug VARCHAR(80) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id BIGINT UNSIGNED NOT NULL,
    module VARCHAR(80) NOT NULL,
    can_view TINYINT(1) NOT NULL DEFAULT 0,
    can_create TINYINT(1) NOT NULL DEFAULT 0,
    can_update TINYINT(1) NOT NULL DEFAULT 0,
    can_delete TINYINT(1) NOT NULL DEFAULT 0,
    UNIQUE KEY uq_role_module (role_id, module),
    CONSTRAINT fk_permissions_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE departments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL UNIQUE,
    head_user_id BIGINT UNSIGNED NULL,
    parent_id BIGINT UNSIGNED NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_departments_status (status),
    CONSTRAINT fk_departments_parent FOREIGN KEY (parent_id) REFERENCES departments(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    department_id BIGINT UNSIGNED NULL,
    phone VARCHAR(40) NULL,
    remember_token_hash VARCHAR(255) NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_users_role (role_id),
    INDEX idx_users_department (department_id),
    INDEX idx_users_status (status),
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id),
    CONSTRAINT fk_users_department FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
) ENGINE=InnoDB;

ALTER TABLE departments
    ADD CONSTRAINT fk_departments_head FOREIGN KEY (head_user_id) REFERENCES users(id) ON DELETE SET NULL;

CREATE TABLE employees (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    employee_code VARCHAR(40) NOT NULL UNIQUE,
    designation VARCHAR(120) NULL,
    joining_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_employees_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE password_resets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_password_resets_token (token_hash),
    CONSTRAINT fk_password_resets_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE user_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    session_id VARCHAR(190) NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    last_seen_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_sessions_session (session_id),
    CONSTRAINT fk_user_sessions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(120) NOT NULL UNIQUE,
    setting_value TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE asset_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL UNIQUE,
    description TEXT NULL,
    custom_fields_json JSON NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_asset_categories_status (status)
) ENGINE=InnoDB;

CREATE TABLE assets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    asset_tag VARCHAR(40) NOT NULL UNIQUE,
    name VARCHAR(180) NOT NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    serial_number VARCHAR(150) NULL UNIQUE,
    department_id BIGINT UNSIGNED NULL,
    assigned_user_id BIGINT UNSIGNED NULL,
    location VARCHAR(180) NULL,
    asset_condition ENUM('New','Good','Fair','Poor','Damaged') NOT NULL DEFAULT 'Good',
    purchase_date DATE NULL,
    purchase_cost DECIMAL(12,2) NULL,
    warranty_expiry DATE NULL,
    vendor VARCHAR(180) NULL,
    qr_code VARCHAR(180) NULL,
    barcode VARCHAR(180) NULL,
    photo_path VARCHAR(255) NULL,
    is_shared_bookable TINYINT(1) NOT NULL DEFAULT 0,
    lifecycle_status ENUM('Available','Allocated','Reserved','Maintenance','Under Maintenance','Lost','Disposed','Retired') NOT NULL DEFAULT 'Available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_assets_category (category_id),
    INDEX idx_assets_department (department_id),
    INDEX idx_assets_assigned_user (assigned_user_id),
    INDEX idx_assets_status (lifecycle_status),
    CONSTRAINT fk_assets_category FOREIGN KEY (category_id) REFERENCES asset_categories(id),
    CONSTRAINT fk_assets_department FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    CONSTRAINT fk_assets_assigned_user FOREIGN KEY (assigned_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE asset_images (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    asset_id BIGINT UNSIGNED NOT NULL,
    file_name VARCHAR(180) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    uploaded_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_asset_images_asset FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE,
    CONSTRAINT fk_asset_images_user FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE asset_documents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    asset_id BIGINT UNSIGNED NOT NULL,
    file_name VARCHAR(180) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    uploaded_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_asset_documents_asset FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE,
    CONSTRAINT fk_asset_documents_user FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE asset_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    asset_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    action VARCHAR(180) NOT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_asset_history_asset (asset_id),
    CONSTRAINT fk_asset_history_asset FOREIGN KEY (asset_id) REFERENCES assets(id) ON DELETE CASCADE,
    CONSTRAINT fk_asset_history_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE asset_allocations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    asset_id BIGINT UNSIGNED NOT NULL,
    employee_id BIGINT UNSIGNED NOT NULL,
    department_id BIGINT UNSIGNED NULL,
    allocated_by BIGINT UNSIGNED NULL,
    expected_return_date DATE NULL,
    condition_before ENUM('New','Good','Fair','Poor','Damaged') NULL,
    condition_after ENUM('New','Good','Fair','Poor','Damaged') NULL,
    status ENUM('Allocated','Returned','Cancelled') NOT NULL DEFAULT 'Allocated',
    returned_at DATETIME NULL,
    return_condition_notes TEXT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_allocations_asset (asset_id),
    INDEX idx_allocations_employee (employee_id),
    CONSTRAINT fk_allocations_asset FOREIGN KEY (asset_id) REFERENCES assets(id),
    CONSTRAINT fk_allocations_employee FOREIGN KEY (employee_id) REFERENCES users(id),
    CONSTRAINT fk_allocations_department FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
    CONSTRAINT fk_allocations_by FOREIGN KEY (allocated_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE asset_transfers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    asset_id BIGINT UNSIGNED NOT NULL,
    from_user_id BIGINT UNSIGNED NULL,
    to_user_id BIGINT UNSIGNED NOT NULL,
    requested_by BIGINT UNSIGNED NOT NULL,
    approved_by BIGINT UNSIGNED NULL,
    status ENUM('Requested','Approved','Rejected','Reallocated') NOT NULL DEFAULT 'Requested',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    decided_at DATETIME NULL,
    INDEX idx_transfer_status (status),
    INDEX idx_transfer_asset_status (asset_id, status),
    CONSTRAINT fk_transfers_asset FOREIGN KEY (asset_id) REFERENCES assets(id),
    CONSTRAINT fk_transfers_from FOREIGN KEY (from_user_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_transfers_to FOREIGN KEY (to_user_id) REFERENCES users(id),
    CONSTRAINT fk_transfers_requested FOREIGN KEY (requested_by) REFERENCES users(id),
    CONSTRAINT fk_transfers_approved FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE asset_return_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    allocation_id BIGINT UNSIGNED NOT NULL,
    asset_id BIGINT UNSIGNED NOT NULL,
    returned_by BIGINT UNSIGNED NOT NULL,
    received_by BIGINT UNSIGNED NULL,
    condition_notes TEXT NULL,
    returned_at DATETIME NOT NULL,
    CONSTRAINT fk_returns_allocation FOREIGN KEY (allocation_id) REFERENCES asset_allocations(id),
    CONSTRAINT fk_returns_asset FOREIGN KEY (asset_id) REFERENCES assets(id),
    CONSTRAINT fk_returns_by FOREIGN KEY (returned_by) REFERENCES users(id),
    CONSTRAINT fk_returns_received FOREIGN KEY (received_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE resources (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    resource_type ENUM('Meeting Room','Projector','Vehicle','Shared Equipment') NOT NULL,
    location VARCHAR(180) NULL,
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    INDEX idx_resources_status (status)
) ENGINE=InnoDB;

CREATE TABLE resource_bookings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    resource_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    purpose VARCHAR(255) NULL,
    starts_at DATETIME NOT NULL,
    ends_at DATETIME NOT NULL,
    status ENUM('Pending','Approved','Rejected','Cancelled','Completed','Upcoming','Ongoing') NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_booking_overlap (resource_id, starts_at, ends_at),
    INDEX idx_booking_user (user_id),
    CONSTRAINT fk_bookings_resource FOREIGN KEY (resource_id) REFERENCES resources(id),
    CONSTRAINT fk_bookings_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE maintenance_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    asset_id BIGINT UNSIGNED NOT NULL,
    requested_by BIGINT UNSIGNED NOT NULL,
    description TEXT NOT NULL,
    priority ENUM('Low','Medium','High','Critical') NOT NULL DEFAULT 'Medium',
    photo_path VARCHAR(255) NULL,
    status ENUM('Pending','Approved','Rejected','Technician Assigned','In Progress','Completed','Resolved') NOT NULL DEFAULT 'Pending',
    technician_user_id BIGINT UNSIGNED NULL,
    repair_cost DECIMAL(12,2) NULL,
    repair_notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at DATETIME NULL,
    INDEX idx_maintenance_status (status),
    CONSTRAINT fk_maintenance_asset FOREIGN KEY (asset_id) REFERENCES assets(id),
    CONSTRAINT fk_maintenance_requested FOREIGN KEY (requested_by) REFERENCES users(id),
    CONSTRAINT fk_maintenance_technician FOREIGN KEY (technician_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE maintenance_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    maintenance_request_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    status VARCHAR(80) NOT NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    repair_cost DECIMAL(12,2) NULL,
    CONSTRAINT fk_maintenance_history_request FOREIGN KEY (maintenance_request_id) REFERENCES maintenance_requests(id) ON DELETE CASCADE,
    CONSTRAINT fk_maintenance_history_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE audit_cycles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(180) NOT NULL,
    scope VARCHAR(180) NOT NULL,
    department_id BIGINT UNSIGNED NULL,
    location VARCHAR(180) NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('Open','Locked','Closed') NOT NULL DEFAULT 'Open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_audit_cycles_department FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE audit_assets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    audit_cycle_id BIGINT UNSIGNED NOT NULL,
    asset_id BIGINT UNSIGNED NOT NULL,
    auditor_user_id BIGINT UNSIGNED NULL,
    result ENUM('Verified','Missing','Damaged') NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_audit_assets_cycle FOREIGN KEY (audit_cycle_id) REFERENCES audit_cycles(id) ON DELETE CASCADE,
    CONSTRAINT fk_audit_assets_asset FOREIGN KEY (asset_id) REFERENCES assets(id),
    CONSTRAINT fk_audit_assets_auditor FOREIGN KEY (auditor_user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE audit_discrepancies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    audit_asset_id BIGINT UNSIGNED NOT NULL,
    discrepancy_type ENUM('Missing','Damaged','Location Mismatch','Ownership Mismatch') NOT NULL,
    status ENUM('Open','Confirmed','Resolved') NOT NULL DEFAULT 'Open',
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_audit_discrepancies_asset FOREIGN KEY (audit_asset_id) REFERENCES audit_assets(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    type VARCHAR(80) NOT NULL,
    title VARCHAR(180) NOT NULL,
    body TEXT NULL,
    read_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_notifications_user_read (user_id, read_at),
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE activity_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    action VARCHAR(180) NOT NULL,
    module VARCHAR(100) NOT NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_activity_logs_module (module),
    INDEX idx_activity_logs_user (user_id),
    CONSTRAINT fk_activity_logs_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

INSERT INTO roles (id, name, slug) VALUES
(1, 'Admin', 'admin'),
(2, 'Department Head', 'department_head'),
(3, 'Asset Manager', 'asset_manager'),
(4, 'Employee', 'employee');

INSERT INTO permissions (role_id, module, can_view, can_create, can_update, can_delete) VALUES
(1,'all',1,1,1,1),(2,'department',1,0,1,0),(2,'assets',1,0,0,0),(2,'approvals',1,0,1,0),
(3,'assets',1,1,1,0),(3,'maintenance',1,1,1,0),(3,'audits',1,1,1,0),(4,'self_service',1,1,1,0);

INSERT INTO departments (name, status) VALUES
('Corporate','active'),('IT','active'),('Facilities','active'),('Finance','active'),('Human Resources','active'),
('Operations','active'),('Sales','active'),('Marketing','active'),('Legal','active'),('Procurement','active'),
('Research','active'),('Security','active'),('Customer Success','active'),('Logistics','active'),('Administration','active');

INSERT INTO users (name, email, password_hash, role_id, department_id, phone, status)
VALUES ('System Admin', 'admin@arms.local', '$2y$10$XdWTVZhzbCLs86vIMGv5zOlFdIS8TQQk3LEtdOPqKdEFpV06dWmhW', 1, 1, '+910000000001', 'active');

INSERT INTO asset_categories (name, description, custom_fields_json) VALUES
('Laptops','Portable computers', JSON_OBJECT('warranty_period','text')),('Desktops','Desktop workstations', JSON_OBJECT('processor','text')),
('Monitors','Display equipment', JSON_OBJECT('screen_size','number')),('Printers','Print devices', JSON_OBJECT('toner_type','text')),
('Network Devices','Routers and switches', JSON_OBJECT('ports','number')),('Mobile Phones','Company phones', JSON_OBJECT('imei','text')),
('Tablets','Tablet devices', JSON_OBJECT('os','text')),('Furniture','Office furniture', JSON_OBJECT('material','text')),
('Vehicles','Company vehicles', JSON_OBJECT('registration_number','text')),('Projectors','Projection equipment', JSON_OBJECT('lumens','number')),
('Meeting Rooms','Bookable rooms', JSON_OBJECT('capacity','number')),('Audio Equipment','Speakers and microphones', JSON_OBJECT('channels','number')),
('Cameras','Photo and video cameras', JSON_OBJECT('resolution','text')),('Storage Devices','External storage', JSON_OBJECT('capacity','text')),
('Servers','Server hardware', JSON_OBJECT('rack_unit','number')),('Software Licenses','Licensed software', JSON_OBJECT('license_key','text')),
('Access Cards','Employee access cards', JSON_OBJECT('card_number','text')),('Tools','Facilities tools', JSON_OBJECT('tool_type','text')),
('Lab Equipment','Research equipment', JSON_OBJECT('calibration_date','date')),('Safety Equipment','Safety gear', JSON_OBJECT('expiry','date')),
('Kitchen Equipment','Pantry assets', JSON_OBJECT('power_rating','text')),('Whiteboards','Office boards', JSON_OBJECT('size','text')),
('UPS Units','Power backup', JSON_OBJECT('backup_minutes','number')),('Scanners','Scanning devices', JSON_OBJECT('dpi','number')),
('Biometric Devices','Attendance devices', JSON_OBJECT('model','text')),('Conference Phones','Meeting phones', JSON_OBJECT('extension','text')),
('Shared Equipment','Common bookable equipment', JSON_OBJECT('capacity','number')),('Cables','Connectivity items', JSON_OBJECT('length','text')),
('Storage Cabinets','Cabinets and lockers', JSON_OBJECT('lock_type','text')),('Miscellaneous','Other assets', JSON_OBJECT('notes','text'));

INSERT INTO resources (name, resource_type, location) VALUES
('Meeting Room B2','Meeting Room','Second Floor'),('Projector A','Projector','Facilities Desk'),('Vehicle 1','Vehicle','Parking Bay 4'),
('Training Room','Meeting Room','First Floor'),('Shared Camera Kit','Shared Equipment','IT Store');

INSERT INTO settings (setting_key, setting_value) VALUES
('asset_tag_prefix','AF'),('upload_max_mb','5'),('timezone','Asia/Calcutta'),('app_name','AssetFlow');

DELIMITER $$
CREATE PROCEDURE seed_assetflow()
BEGIN
    DECLARE i INT DEFAULT 1;
    DECLARE uid BIGINT UNSIGNED;
    DECLARE aid BIGINT UNSIGNED;
    DECLARE alloc_id BIGINT UNSIGNED;
    WHILE i <= 100 DO
        INSERT INTO users (name, email, password_hash, role_id, department_id, phone, status)
        VALUES (
            CONCAT('Employee ', LPAD(i,3,'0')),
            CONCAT('employee', LPAD(i,3,'0'), '@assetflow.local'),
            '$2y$10$XdWTVZhzbCLs86vIMGv5zOlFdIS8TQQk3LEtdOPqKdEFpV06dWmhW',
            CASE WHEN i <= 5 THEN 2 WHEN i <= 15 THEN 3 ELSE 4 END,
            ((i - 1) MOD 15) + 1,
            CONCAT('+91', LPAD(i,10,'0')),
            'active'
        );
        SET uid = LAST_INSERT_ID();
        INSERT INTO employees (user_id, employee_code, designation, joining_date)
        VALUES (uid, CONCAT('EMP', LPAD(i,4,'0')), CASE WHEN i <= 5 THEN 'Department Head' WHEN i <= 15 THEN 'Asset Manager' ELSE 'Employee' END, DATE_SUB(CURDATE(), INTERVAL i DAY));
        IF i <= 15 THEN
            UPDATE departments SET head_user_id = uid WHERE id = i;
        END IF;
        SET i = i + 1;
    END WHILE;

    SET i = 1;
    WHILE i <= 500 DO
        INSERT INTO assets (asset_tag, name, category_id, serial_number, department_id, assigned_user_id, location, asset_condition, purchase_date, purchase_cost, warranty_expiry, vendor, qr_code, barcode, is_shared_bookable, lifecycle_status)
        VALUES (
            CONCAT('AF-', LPAD(i,5,'0')),
            CONCAT('Asset ', LPAD(i,4,'0')),
            ((i - 1) MOD 30) + 1,
            CONCAT('SN-', LPAD(i,7,'0')),
            ((i - 1) MOD 15) + 1,
            CASE WHEN i <= 300 THEN ((i - 1) MOD 100) + 2 ELSE NULL END,
            CONCAT('Location ', ((i - 1) MOD 20) + 1),
            ELT((i MOD 5) + 1, 'New','Good','Fair','Poor','Damaged'),
            DATE_SUB(CURDATE(), INTERVAL (i MOD 900) DAY),
            1000 + (i * 17.35),
            DATE_ADD(CURDATE(), INTERVAL (i MOD 730) DAY),
            CONCAT('Vendor ', ((i - 1) MOD 25) + 1),
            CONCAT('QR-AF-', LPAD(i,5,'0')),
            CONCAT('BC-AF-', LPAD(i,5,'0')),
            IF(i MOD 12 = 0, 1, 0),
            CASE WHEN i <= 300 THEN 'Allocated' WHEN i MOD 17 = 0 THEN 'Maintenance' WHEN i MOD 19 = 0 THEN 'Reserved' ELSE 'Available' END
        );
        SET i = i + 1;
    END WHILE;

    SET i = 1;
    WHILE i <= 300 DO
        INSERT INTO asset_allocations (asset_id, employee_id, department_id, allocated_by, expected_return_date, condition_before, status, notes)
        VALUES (i, ((i - 1) MOD 100) + 2, ((i - 1) MOD 15) + 1, ((i - 1) MOD 10) + 7, DATE_ADD(CURDATE(), INTERVAL (i MOD 120) DAY), 'Good', 'Allocated', 'Seed allocation');
        SET i = i + 1;
    END WHILE;

    SET i = 1;
    WHILE i <= 150 DO
        INSERT INTO resource_bookings (resource_id, user_id, purpose, starts_at, ends_at, status)
        VALUES (((i - 1) MOD 5) + 1, ((i - 1) MOD 100) + 2, CONCAT('Booking purpose ', i), DATE_ADD(NOW(), INTERVAL i HOUR), DATE_ADD(NOW(), INTERVAL (i + 1) HOUR), ELT((i MOD 5) + 1, 'Pending','Approved','Rejected','Completed','Cancelled'));
        SET i = i + 1;
    END WHILE;

    SET i = 1;
    WHILE i <= 100 DO
        INSERT INTO maintenance_requests (asset_id, requested_by, description, priority, status, technician_user_id)
        VALUES (((i - 1) MOD 500) + 1, ((i - 1) MOD 100) + 2, CONCAT('Maintenance request ', i), ELT((i MOD 4) + 1, 'Low','Medium','High','Critical'), ELT((i MOD 6) + 1, 'Pending','Approved','Rejected','Technician Assigned','In Progress','Resolved'), ((i - 1) MOD 10) + 7);
        INSERT INTO maintenance_history (maintenance_request_id, user_id, status, notes) VALUES (LAST_INSERT_ID(), ((i - 1) MOD 100) + 2, 'Created', 'Seed update');
        SET i = i + 1;
    END WHILE;

    SET i = 1;
    WHILE i <= 50 DO
        INSERT INTO audit_cycles (name, scope, department_id, location, start_date, end_date, status)
        VALUES (CONCAT('Audit Cycle ', i), 'Department', ((i - 1) MOD 15) + 1, CONCAT('Location ', ((i - 1) MOD 20) + 1), DATE_ADD(CURDATE(), INTERVAL i DAY), DATE_ADD(CURDATE(), INTERVAL (i + 7) DAY), ELT((i MOD 3) + 1, 'Open','Locked','Closed'));
        SET aid = LAST_INSERT_ID();
        INSERT INTO audit_assets (audit_cycle_id, asset_id, auditor_user_id, result, notes)
        VALUES (aid, ((i - 1) MOD 500) + 1, ((i - 1) MOD 15) + 2, ELT((i MOD 3) + 1, 'Verified','Missing','Damaged'), 'Seed audit item');
        IF i <= 25 THEN
            INSERT INTO audit_discrepancies (audit_asset_id, discrepancy_type, status, notes)
            VALUES (LAST_INSERT_ID(), ELT((i MOD 4) + 1, 'Missing','Damaged','Location Mismatch','Ownership Mismatch'), 'Open', 'Seed discrepancy');
        END IF;
        SET i = i + 1;
    END WHILE;

    SET i = 1;
    WHILE i <= 500 DO
        INSERT INTO notifications (user_id, type, title, body, read_at)
        VALUES (((i - 1) MOD 100) + 2, 'system', CONCAT('Notification ', i), 'Seed notification body', IF(i MOD 3 = 0, NOW(), NULL));
        SET i = i + 1;
    END WHILE;

    SET i = 1;
    WHILE i <= 1000 DO
        INSERT INTO activity_logs (user_id, action, module, ip_address)
        VALUES (IF(i MOD 8 = 0, NULL, ((i - 1) MOD 100) + 2), CONCAT('Seed action ', i), ELT((i MOD 8) + 1, 'Authentication','Users','Departments','Assets','Allocations','Bookings','Maintenance','Audits'), '127.0.0.1');
        SET i = i + 1;
    END WHILE;
END$$
DELIMITER ;

CALL seed_assetflow();
DROP PROCEDURE seed_assetflow;
