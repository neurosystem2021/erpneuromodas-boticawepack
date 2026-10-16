-- Centro de comunicaciones CRM. Migración aditiva e idempotente.
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS crm_channel_connection (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NOT NULL,
  channel VARCHAR(30) NOT NULL,
  provider VARCHAR(40) NOT NULL,
  display_name VARCHAR(120) NOT NULL,
  external_instance_id VARCHAR(120) NULL,
  status ENUM('disabled','disconnected','connecting','connected','error') NOT NULL DEFAULT 'disabled',
  is_default TINYINT(1) NOT NULL DEFAULT 0,
  last_seen_at DATETIME NULL,
  last_error VARCHAR(500) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_crm_channel_connection (tenant_id, channel, display_name),
  KEY ix_crm_channel_status (tenant_id, channel, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS crm_conversation (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NOT NULL,
  connection_id BIGINT UNSIGNED NULL,
  customer_id BIGINT UNSIGNED NULL,
  channel VARCHAR(30) NOT NULL,
  external_chat_id VARCHAR(190) NULL,
  status ENUM('open','waiting','resolved','blocked') NOT NULL DEFAULT 'open',
  assigned_user VARCHAR(100) NULL,
  unread_count INT UNSIGNED NOT NULL DEFAULT 0,
  last_message_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_crm_conversation_external (tenant_id, channel, external_chat_id),
  KEY ix_crm_conversation_queue (tenant_id, status, last_message_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS crm_message (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NOT NULL,
  conversation_id BIGINT UNSIGNED NOT NULL,
  customer_id BIGINT UNSIGNED NULL,
  direction ENUM('inbound','outbound') NOT NULL,
  sender_type ENUM('customer','agent','bot','system') NOT NULL,
  body TEXT NOT NULL,
  status ENUM('received','draft','queued','sent','delivered','read','failed') NOT NULL,
  external_message_id VARCHAR(190) NULL,
  metadata_json JSON NULL,
  sent_at DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_crm_message_external (tenant_id, external_message_id),
  KEY ix_crm_message_conversation (tenant_id, conversation_id, id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS crm_message_outbox (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NOT NULL,
  campaign_id BIGINT UNSIGNED NULL,
  customer_id BIGINT UNSIGNED NOT NULL,
  channel VARCHAR(30) NOT NULL,
  destination VARCHAR(80) NOT NULL,
  body TEXT NOT NULL,
  status ENUM('simulated','pending','processing','sent','failed','cancelled') NOT NULL DEFAULT 'simulated',
  attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
  idempotency_key CHAR(64) NOT NULL,
  available_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  sent_at DATETIME NULL,
  last_error VARCHAR(500) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_crm_outbox_idempotency (tenant_id, idempotency_key),
  KEY ix_crm_outbox_queue (tenant_id, status, available_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS crm_bot_rule (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(120) NOT NULL,
  trigger_text VARCHAR(250) NOT NULL,
  response_text TEXT NOT NULL,
  priority INT NOT NULL DEFAULT 100,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  handoff TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_crm_bot_rule_name (tenant_id, name),
  KEY ix_crm_bot_rule_active (tenant_id, is_active, priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
