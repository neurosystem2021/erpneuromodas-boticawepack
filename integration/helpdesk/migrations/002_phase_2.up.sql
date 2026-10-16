-- PROPOSED: apply only to a dedicated support database after identity integration.
CREATE TABLE support_ticket_messages (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 tenant_id BIGINT UNSIGNED NOT NULL,
 ticket_id BIGINT UNSIGNED NOT NULL,
 author_id BIGINT UNSIGNED NOT NULL,
 visibility ENUM('public','internal') NOT NULL,
 body TEXT NOT NULL,
 operation_uuid CHAR(36) NOT NULL,
 created_at DATETIME NOT NULL,
 UNIQUE KEY dedup(tenant_id,author_id,operation_uuid),
 UNIQUE KEY message_tenant_ticket(tenant_id,ticket_id,id),
 KEY conversation(tenant_id,ticket_id,id),
 FOREIGN KEY(tenant_id,ticket_id) REFERENCES support_tickets(tenant_id,id),
 FOREIGN KEY(author_id) REFERENCES support_principals(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE support_ticket_attachments (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 tenant_id BIGINT UNSIGNED NOT NULL,
 ticket_id BIGINT UNSIGNED NOT NULL,
 message_id BIGINT UNSIGNED NULL,
 author_id BIGINT UNSIGNED NOT NULL,
 visibility ENUM('public','internal') NOT NULL,
 storage_key CHAR(64) CHARACTER SET ascii NOT NULL UNIQUE,
 original_name VARCHAR(255) NOT NULL,
 detected_mime VARCHAR(100) NOT NULL,
 size_bytes INT UNSIGNED NOT NULL,
 sha256 CHAR(64) CHARACTER SET ascii NOT NULL,
 created_at DATETIME NOT NULL,
 KEY files(tenant_id,ticket_id,id),
 FOREIGN KEY(tenant_id,ticket_id) REFERENCES support_tickets(tenant_id,id),
 FOREIGN KEY(tenant_id,ticket_id,message_id) REFERENCES support_ticket_messages(tenant_id,ticket_id,id),
 FOREIGN KEY(author_id) REFERENCES support_principals(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE support_assignments (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 tenant_id BIGINT UNSIGNED NOT NULL,
 ticket_id BIGINT UNSIGNED NOT NULL,
 technician_id BIGINT UNSIGNED NOT NULL,
 assigned_by BIGINT UNSIGNED NOT NULL,
 assigned_at DATETIME NOT NULL,
 ended_at DATETIME NULL,
 KEY assignments(tenant_id,ticket_id,id),
 FOREIGN KEY(tenant_id,ticket_id) REFERENCES support_tickets(tenant_id,id),
 FOREIGN KEY(technician_id) REFERENCES support_principals(id),
 FOREIGN KEY(assigned_by) REFERENCES support_principals(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE support_notifications (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 tenant_id BIGINT UNSIGNED NOT NULL,
 ticket_id BIGINT UNSIGNED NOT NULL,
 recipient_id BIGINT UNSIGNED NOT NULL,
 kind VARCHAR(50) NOT NULL,
 created_at DATETIME NOT NULL,
 read_at DATETIME NULL,
 KEY unread(tenant_id,recipient_id,read_at,id),
 FOREIGN KEY(tenant_id,ticket_id) REFERENCES support_tickets(tenant_id,id),
 FOREIGN KEY(recipient_id) REFERENCES support_principals(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
