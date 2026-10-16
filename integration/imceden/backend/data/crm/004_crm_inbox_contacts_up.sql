-- Datos visibles del contacto y estado operativo de la bandeja.
SET NAMES utf8mb4;

ALTER TABLE crm_conversation
  ADD COLUMN IF NOT EXISTS contact_name VARCHAR(160) NULL AFTER external_chat_id,
  ADD COLUMN IF NOT EXISTS contact_phone VARCHAR(40) NULL AFTER contact_name;

