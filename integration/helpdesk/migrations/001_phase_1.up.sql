-- PROPOSED: apply only to a dedicated support database after identity integration.
CREATE TABLE support_installations (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 installation_uuid CHAR(36) CHARACTER SET ascii COLLATE ascii_bin NOT NULL UNIQUE,
 origin_https VARCHAR(255) NOT NULL UNIQUE,
 active TINYINT NOT NULL DEFAULT 0,
 created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE support_tenants (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 installation_id BIGINT UNSIGNED NOT NULL,
 external_company_id VARCHAR(100) NOT NULL,
 active TINYINT NOT NULL DEFAULT 1,
 created_at DATETIME NOT NULL,
 UNIQUE KEY origin_company(installation_id,external_company_id),
 FOREIGN KEY(installation_id) REFERENCES support_installations(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE support_principals (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 installation_id BIGINT UNSIGNED NOT NULL,
 external_user_id VARCHAR(255) NOT NULL,
 active TINYINT NOT NULL DEFAULT 1,
 UNIQUE KEY origin_user(installation_id,external_user_id),
 FOREIGN KEY(installation_id) REFERENCES support_installations(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE support_memberships (
 tenant_id BIGINT UNSIGNED NOT NULL,
 principal_id BIGINT UNSIGNED NOT NULL,
 role ENUM('superadmin','supervisor','technician','company_admin','requester') NOT NULL,
 active TINYINT NOT NULL DEFAULT 1,
 PRIMARY KEY(tenant_id,principal_id,role),
 FOREIGN KEY(tenant_id) REFERENCES support_tenants(id),
 FOREIGN KEY(principal_id) REFERENCES support_principals(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE support_categories (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(150) NOT NULL UNIQUE,
 active TINYINT NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE support_tickets (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 tenant_id BIGINT UNSIGNED NOT NULL,
 requester_id BIGINT UNSIGNED NOT NULL,
 category_id BIGINT UNSIGNED NOT NULL,
 branch_external_id VARCHAR(100) NOT NULL DEFAULT '',
 code VARCHAR(32) NULL UNIQUE,
 subject VARCHAR(180) NOT NULL,
 description TEXT NOT NULL,
 affected_module VARCHAR(100) NOT NULL,
 priority ENUM('critical','high','medium','low') NOT NULL DEFAULT 'medium',
 status ENUM('new','assigned','in_progress','waiting_customer','escalated','resolved','closed','reopened','cancelled') NOT NULL DEFAULT 'new',
 technician_id BIGINT UNSIGNED NULL,
 version INT UNSIGNED NOT NULL DEFAULT 1,
 created_at DATETIME NOT NULL,
 updated_at DATETIME NOT NULL,
 first_response_at DATETIME NULL,
 resolved_at DATETIME NULL,
 closed_at DATETIME NULL,
 root_cause TEXT NULL,
 resolution TEXT NULL,
 UNIQUE KEY ticket_tenant(tenant_id,id),
 KEY inbox(tenant_id,status,priority,created_at,id),
 KEY technician(technician_id,status,created_at,id),
 FOREIGN KEY(tenant_id) REFERENCES support_tenants(id),
 FOREIGN KEY(requester_id) REFERENCES support_principals(id),
 FOREIGN KEY(technician_id) REFERENCES support_principals(id),
 FOREIGN KEY(category_id) REFERENCES support_categories(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE support_ticket_status_history (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 tenant_id BIGINT UNSIGNED NOT NULL,
 ticket_id BIGINT UNSIGNED NOT NULL,
 actor_id BIGINT UNSIGNED NOT NULL,
 previous_status VARCHAR(30) NULL,
 next_status VARCHAR(30) NOT NULL,
 reason TEXT NOT NULL,
 created_at DATETIME NOT NULL,
 KEY history(tenant_id,ticket_id,id),
 FOREIGN KEY(tenant_id,ticket_id) REFERENCES support_tickets(tenant_id,id),
 FOREIGN KEY(actor_id) REFERENCES support_principals(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE support_audit_logs (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 tenant_id BIGINT UNSIGNED NOT NULL,
 actor_id BIGINT UNSIGNED NOT NULL,
 action VARCHAR(80) NOT NULL,
 entity_type VARCHAR(50) NOT NULL,
 entity_id BIGINT UNSIGNED NULL,
 metadata_json LONGTEXT NOT NULL,
 created_at DATETIME NOT NULL,
 KEY audit(tenant_id,created_at,id),
 FOREIGN KEY(tenant_id) REFERENCES support_tenants(id),
 FOREIGN KEY(actor_id) REFERENCES support_principals(id),
 CHECK(JSON_VALID(metadata_json))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE support_installation_keys (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 installation_id BIGINT UNSIGNED NOT NULL,
 key_id VARCHAR(80) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
 public_key_base64 TEXT CHARACTER SET ascii NOT NULL,
 active TINYINT NOT NULL DEFAULT 1,
 created_at DATETIME NOT NULL,
 revoked_at DATETIME NULL,
 UNIQUE KEY install_key(installation_id,key_id),
 FOREIGN KEY(installation_id) REFERENCES support_installations(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
CREATE TABLE support_request_nonces (
 installation_id BIGINT UNSIGNED NOT NULL,
 nonce CHAR(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
 expires_at DATETIME NOT NULL,
 PRIMARY KEY(installation_id,nonce),
 KEY expired(expires_at),
 FOREIGN KEY(installation_id) REFERENCES support_installations(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
