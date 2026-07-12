USE arms;

DELIMITER $$
CREATE PROCEDURE patch_assetflow_workflows()
BEGIN
    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'asset_allocations' AND COLUMN_NAME = 'condition_before') THEN
        ALTER TABLE asset_allocations ADD COLUMN condition_before ENUM('New','Good','Fair','Poor','Damaged') NULL AFTER expected_return_date;
    END IF;
    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'asset_allocations' AND COLUMN_NAME = 'condition_after') THEN
        ALTER TABLE asset_allocations ADD COLUMN condition_after ENUM('New','Good','Fair','Poor','Damaged') NULL AFTER condition_before;
    END IF;
    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'asset_allocations' AND COLUMN_NAME = 'status') THEN
        ALTER TABLE asset_allocations ADD COLUMN status ENUM('Allocated','Returned','Cancelled') NOT NULL DEFAULT 'Allocated' AFTER condition_after;
    END IF;
    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'resource_bookings' AND COLUMN_NAME = 'purpose') THEN
        ALTER TABLE resource_bookings ADD COLUMN purpose VARCHAR(255) NULL AFTER user_id;
    END IF;
    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'maintenance_requests' AND COLUMN_NAME = 'repair_cost') THEN
        ALTER TABLE maintenance_requests ADD COLUMN repair_cost DECIMAL(12,2) NULL AFTER technician_user_id;
    END IF;
    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'maintenance_requests' AND COLUMN_NAME = 'repair_notes') THEN
        ALTER TABLE maintenance_requests ADD COLUMN repair_notes TEXT NULL AFTER repair_cost;
    END IF;
    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'activity_logs' AND COLUMN_NAME = 'role_name') THEN
        ALTER TABLE activity_logs ADD COLUMN role_name VARCHAR(80) NULL AFTER user_id;
    END IF;
    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'activity_logs' AND COLUMN_NAME = 'user_agent') THEN
        ALTER TABLE activity_logs ADD COLUMN user_agent VARCHAR(255) NULL AFTER ip_address;
    END IF;
    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'activity_logs' AND COLUMN_NAME = 'status') THEN
        ALTER TABLE activity_logs ADD COLUMN status VARCHAR(40) NOT NULL DEFAULT 'Success' AFTER user_agent;
    END IF;
    IF NOT EXISTS (SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'maintenance_history' AND COLUMN_NAME = 'repair_cost') THEN
        ALTER TABLE maintenance_history ADD COLUMN repair_cost DECIMAL(12,2) NULL AFTER notes;
    END IF;
END$$
DELIMITER ;

CALL patch_assetflow_workflows();
DROP PROCEDURE patch_assetflow_workflows;

ALTER TABLE resource_bookings
    MODIFY status ENUM('Pending','Approved','Rejected','Cancelled','Completed','Upcoming','Ongoing') NOT NULL DEFAULT 'Pending';

ALTER TABLE maintenance_requests
    MODIFY status ENUM('Pending','Approved','Rejected','Technician Assigned','In Progress','Completed','Resolved') NOT NULL DEFAULT 'Pending';

CREATE TABLE IF NOT EXISTS maintenance_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    maintenance_request_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    status VARCHAR(80) NOT NULL,
    notes TEXT NULL,
    repair_cost DECIMAL(12,2) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_maintenance_history_request (maintenance_request_id),
    CONSTRAINT fk_maintenance_history_request FOREIGN KEY (maintenance_request_id) REFERENCES maintenance_requests(id) ON DELETE CASCADE,
    CONSTRAINT fk_maintenance_history_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

UPDATE asset_allocations
SET status = IF(returned_at IS NULL, 'Allocated', 'Returned')
WHERE status IS NULL OR status = '';

UPDATE resource_bookings SET status = 'Approved' WHERE status IN ('Upcoming','Ongoing');
