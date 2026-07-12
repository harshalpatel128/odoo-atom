USE arms;

ALTER TABLE assets
    ADD COLUMN assigned_user_id BIGINT UNSIGNED NULL AFTER department_id,
    ADD COLUMN purchase_date DATE NULL AFTER asset_condition,
    ADD COLUMN purchase_cost DECIMAL(12,2) NULL AFTER purchase_date,
    ADD COLUMN warranty_expiry DATE NULL AFTER purchase_cost,
    ADD COLUMN vendor VARCHAR(180) NULL AFTER warranty_expiry,
    ADD COLUMN qr_code VARCHAR(180) NULL AFTER vendor,
    ADD COLUMN barcode VARCHAR(180) NULL AFTER qr_code,
    ADD COLUMN updated_at TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP AFTER created_at,
    ADD INDEX idx_assets_assigned_user (assigned_user_id),
    ADD CONSTRAINT fk_assets_assigned_user FOREIGN KEY (assigned_user_id) REFERENCES users(id) ON DELETE SET NULL;

UPDATE assets
SET purchase_date = acquisition_date,
    purchase_cost = acquisition_cost,
    qr_code = CONCAT('QR-', asset_tag),
    barcode = CONCAT('BC-', asset_tag)
WHERE purchase_date IS NULL
   OR purchase_cost IS NULL
   OR qr_code IS NULL
   OR barcode IS NULL;
