ALTER TABLE support_tickets
 ADD COLUMN archived_at DATETIME NULL,
 ADD COLUMN archived_by BIGINT UNSIGNED NULL;

ALTER TABLE support_satisfaction_surveys
 ADD COLUMN technician_id BIGINT UNSIGNED NULL;
