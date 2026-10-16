CREATE TABLE support_bridge_operations (
 installation_id BIGINT UNSIGNED NOT NULL,
 operation_uuid CHAR(36) CHARACTER SET ascii NOT NULL,
 response_json LONGTEXT NOT NULL,
 created_at DATETIME NOT NULL,
 PRIMARY KEY(installation_id,operation_uuid),
 FOREIGN KEY(installation_id) REFERENCES support_installations(id),
 CHECK(JSON_VALID(response_json))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
