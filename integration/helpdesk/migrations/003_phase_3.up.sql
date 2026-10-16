-- PROPOSED: apply only to a dedicated support database after identity integration.
CREATE TABLE support_sla_policies (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 tenant_id BIGINT UNSIGNED NULL,
 priority ENUM('critical','high','medium','low') NOT NULL,
 first_response_minutes INT UNSIGNED NOT NULL,
 resolution_minutes INT UNSIGNED NOT NULL,
 timezone VARCHAR(60) NOT NULL DEFAULT 'America/Lima',
 calendar_json LONGTEXT NOT NULL,
 pause_waiting_customer TINYINT NOT NULL DEFAULT 1,
 active TINYINT NOT NULL DEFAULT 1,
 FOREIGN KEY(tenant_id) REFERENCES support_tenants(id),
 CHECK(JSON_VALID(calendar_json))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE support_sla_events (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 tenant_id BIGINT UNSIGNED NOT NULL,
 ticket_id BIGINT UNSIGNED NOT NULL,
 policy_id BIGINT UNSIGNED NOT NULL,
 kind ENUM('started','paused','resumed','response_breach','resolution_breach','resolved','reopened') NOT NULL,
 reason TEXT NOT NULL,
 policy_snapshot_json LONGTEXT NOT NULL,
 cycle INT UNSIGNED NOT NULL DEFAULT 1,
 event_key VARCHAR(100) NOT NULL,
 created_at DATETIME NOT NULL,
 UNIQUE KEY dedup_event(tenant_id,ticket_id,event_key),
 FOREIGN KEY(tenant_id,ticket_id) REFERENCES support_tickets(tenant_id,id),
 FOREIGN KEY(policy_id) REFERENCES support_sla_policies(id),
 CHECK(JSON_VALID(policy_snapshot_json))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE support_worklogs (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 tenant_id BIGINT UNSIGNED NOT NULL,
 ticket_id BIGINT UNSIGNED NOT NULL,
 technician_id BIGINT UNSIGNED NOT NULL,
 started_at DATETIME NOT NULL,
 ended_at DATETIME NOT NULL,
 description TEXT NOT NULL,
 FOREIGN KEY(tenant_id,ticket_id) REFERENCES support_tickets(tenant_id,id),
 FOREIGN KEY(technician_id) REFERENCES support_principals(id),
 CHECK(ended_at>=started_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE support_satisfaction_surveys (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 tenant_id BIGINT UNSIGNED NOT NULL,
 ticket_id BIGINT UNSIGNED NOT NULL,
 respondent_id BIGINT UNSIGNED NOT NULL,
 cycle INT UNSIGNED NOT NULL DEFAULT 1,
 solved TINYINT NOT NULL,
 speed TINYINT UNSIGNED NOT NULL,
 treatment TINYINT UNSIGNED NOT NULL,
 solution TINYINT UNSIGNED NOT NULL,
 recommend TINYINT NOT NULL,
 comment TEXT NOT NULL,
 created_at DATETIME NOT NULL,
 UNIQUE KEY survey_cycle(tenant_id,ticket_id,cycle),
 FOREIGN KEY(tenant_id,ticket_id) REFERENCES support_tickets(tenant_id,id),
 FOREIGN KEY(respondent_id) REFERENCES support_principals(id),
 CHECK(speed BETWEEN 1 AND 5 AND treatment BETWEEN 1 AND 5 AND solution BETWEEN 1 AND 5),
 CHECK(solved IN (0,1) AND recommend IN (0,1))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
