ALTER TABLE support_tickets
 ADD COLUMN estimated_minutes INT UNSIGNED NULL,
 ADD COLUMN expected_resolution_at DATETIME NULL;
