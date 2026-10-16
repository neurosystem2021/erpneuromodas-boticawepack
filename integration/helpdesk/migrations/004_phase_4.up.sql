-- PROPOSED: apply only to a dedicated support database after identity integration.
CREATE TABLE support_knowledge_articles (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 tenant_id BIGINT UNSIGNED NULL,
 category_id BIGINT UNSIGNED NOT NULL,
 author_id BIGINT UNSIGNED NOT NULL,
 title VARCHAR(180) NOT NULL,
 body MEDIUMTEXT NOT NULL,
 keywords VARCHAR(500) NOT NULL,
 published TINYINT NOT NULL DEFAULT 0,
 created_at DATETIME NOT NULL,
 updated_at DATETIME NOT NULL,
 KEY articles(tenant_id,published,category_id,id),
 FULLTEXT KEY search_articles(title,body,keywords),
 FOREIGN KEY(tenant_id) REFERENCES support_tenants(id),
 FOREIGN KEY(category_id) REFERENCES support_categories(id),
 FOREIGN KEY(author_id) REFERENCES support_principals(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE support_integration_outbox (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 tenant_id BIGINT UNSIGNED NOT NULL,
 ticket_id BIGINT UNSIGNED NOT NULL,
 channel ENUM('email','whatsapp') NOT NULL,
 event_uuid CHAR(36) NOT NULL UNIQUE,
 payload_json LONGTEXT NOT NULL,
 attempts INT UNSIGNED NOT NULL DEFAULT 0,
 next_attempt_at DATETIME NOT NULL,
 delivered_at DATETIME NULL,
 KEY pending(delivered_at,next_attempt_at,id),
 FOREIGN KEY(tenant_id,ticket_id) REFERENCES support_tickets(tenant_id,id),
 CHECK(JSON_VALID(payload_json))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
