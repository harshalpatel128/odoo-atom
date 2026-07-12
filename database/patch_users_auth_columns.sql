USE arms;

DELIMITER $$
CREATE PROCEDURE patch_assetflow_users_auth_columns()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'users'
          AND COLUMN_NAME = 'phone'
    ) THEN
        ALTER TABLE users ADD COLUMN phone VARCHAR(40) NULL AFTER department_id;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'users'
          AND COLUMN_NAME = 'remember_token_hash'
    ) THEN
        ALTER TABLE users ADD COLUMN remember_token_hash VARCHAR(255) NULL AFTER phone;
    END IF;
END$$
DELIMITER ;

CALL patch_assetflow_users_auth_columns();
DROP PROCEDURE patch_assetflow_users_auth_columns;
