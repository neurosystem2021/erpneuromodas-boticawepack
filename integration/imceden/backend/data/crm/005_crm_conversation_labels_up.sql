-- Etiqueta comercial visible en la bandeja de WhatsApp.
SET NAMES utf8mb4;

ALTER TABLE crm_conversation
  ADD COLUMN IF NOT EXISTS sales_label VARCHAR(30) NOT NULL DEFAULT 'new' AFTER status;

UPDATE crm_conversation
SET sales_label = 'new'
WHERE sales_label IS NULL OR sales_label = '';

CREATE INDEX IF NOT EXISTS ix_crm_conversation_sales_label
  ON crm_conversation (tenant_id, sales_label, last_message_at);
