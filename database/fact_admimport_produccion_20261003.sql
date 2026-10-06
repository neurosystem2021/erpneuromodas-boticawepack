-- IMPORT COMPANY EDEN E.I.R.L.
-- RESPALDO COMPLETO LISTO PARA PRODUCCION
-- Generado: 2026-10-03 16:14:16 (America/Lima)
-- Origen: base local fact_imceden
-- Destino sugerido: fact_admimport
--
-- IMPORTANTE: este archivo conserva el uso exacto de mayusculas y
-- minusculas requerido por MySQL/MariaDB en Linux (lower_case_table_names=0).
-- No incluye CREATE DATABASE ni USE: seleccione fact_admimport en phpMyAdmin
-- antes de ejecutar la importacion.
-- ADVERTENCIA: es un respaldo completo y contiene DROP TABLE. Si se importa
-- sobre una base existente reemplazara sus tablas y datos. Haga primero un
-- respaldo de produccion.
-- Los DEFINER locales fueron eliminados para que las rutinas se creen con
-- el usuario autorizado de produccion.

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `Cb_CajaBanco`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Cb_CajaBanco` (
  `IdCajaBanco` int(11) NOT NULL AUTO_INCREMENT,
  `IdTipoCajaBanco` int(11) DEFAULT NULL,
  `IdCuenta` int(11) DEFAULT NULL,
  `FechaDoc` datetime DEFAULT NULL,
  `Concepto` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `Importe` double DEFAULT NULL,
  `Anulado` bit(1) DEFAULT NULL,
  `UsuarioReg` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `FechaReg` datetime DEFAULT NULL,
  `UsuarioMod` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `FechaMod` date DEFAULT NULL,
  `IdProveedor` int(11) NOT NULL DEFAULT 0,
  `IdCliente` int(11) NOT NULL DEFAULT 0,
  `IdCierre` int(11) DEFAULT NULL,
  `EsDelVendedor` tinyint(1) DEFAULT 0,
  `IdAlmacen` tinyint(4) DEFAULT NULL,
  PRIMARY KEY (`IdCajaBanco`),
  KEY `fk_2` (`IdCuenta`),
  KEY `fk_TipoBanco` (`IdTipoCajaBanco`),
  CONSTRAINT `fk_2` FOREIGN KEY (`IdCuenta`) REFERENCES `Cb_Cuenta` (`IdCuenta`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_TipoBanco` FOREIGN KEY (`IdTipoCajaBanco`) REFERENCES `Cb_TipoCajaBanco` (`IdTipoCajaBanco`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Cb_CajaBanco` WRITE;
/*!40000 ALTER TABLE `Cb_CajaBanco` DISABLE KEYS */;
/*!40000 ALTER TABLE `Cb_CajaBanco` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Cb_CajaBancoDet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Cb_CajaBancoDet` (
  `IdCajaBanco` int(11) NOT NULL AUTO_INCREMENT,
  `IdDocDet` int(11) NOT NULL,
  `Importe` float DEFAULT NULL,
  `Hash` varchar(255) DEFAULT NULL,
  `Tipo` varchar(45) NOT NULL,
  PRIMARY KEY (`IdDocDet`,`IdCajaBanco`,`Tipo`),
  KEY `fk1` (`IdCajaBanco`),
  CONSTRAINT `fk1` FOREIGN KEY (`IdCajaBanco`) REFERENCES `Cb_CajaBanco` (`IdCajaBanco`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Cb_CajaBancoDet` WRITE;
/*!40000 ALTER TABLE `Cb_CajaBancoDet` DISABLE KEYS */;
/*!40000 ALTER TABLE `Cb_CajaBancoDet` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Cb_CierreCaja`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Cb_CierreCaja` (
  `IdCierreCaja` int(11) NOT NULL AUTO_INCREMENT,
  `FechaCierre` datetime DEFAULT NULL,
  `IdTurno` int(11) DEFAULT NULL,
  `UsuarioReg` varchar(255) DEFAULT NULL,
  `IdAlmacen` tinyint(4) DEFAULT NULL,
  `Ingresos` float DEFAULT NULL,
  `Salidas` float DEFAULT NULL,
  `Ventas` float DEFAULT NULL,
  `Total` float DEFAULT NULL,
  PRIMARY KEY (`IdCierreCaja`)
) ENGINE=MyISAM AUTO_INCREMENT=289 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Cb_CierreCaja` WRITE;
/*!40000 ALTER TABLE `Cb_CierreCaja` DISABLE KEYS */;
/*!40000 ALTER TABLE `Cb_CierreCaja` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Cb_Cuenta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Cb_Cuenta` (
  `IdCuenta` int(11) NOT NULL AUTO_INCREMENT,
  `Cuenta` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `Anulado` bit(1) DEFAULT NULL,
  PRIMARY KEY (`IdCuenta`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Cb_Cuenta` WRITE;
/*!40000 ALTER TABLE `Cb_Cuenta` DISABLE KEYS */;
INSERT INTO `Cb_Cuenta` VALUES (1,'Caja Principal',0x00),(2,'Banco BCP',0x00),(3,'Banco Continental',0x00);
/*!40000 ALTER TABLE `Cb_Cuenta` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Cb_LetraCaja`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Cb_LetraCaja` (
  `IdLetraCaja` int(11) NOT NULL,
  `IdDocDet` int(11) NOT NULL,
  `Hash` varchar(255) DEFAULT NULL,
  `IdCajaBanco` int(11) NOT NULL,
  `NumeroLetra` int(11) NOT NULL,
  `Importe` float(11,2) DEFAULT NULL,
  `FechaReg` datetime DEFAULT NULL,
  `FechaPago` datetime DEFAULT NULL,
  `Tipo` varchar(45) NOT NULL,
  `Cancelado` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Cb_LetraCaja` WRITE;
/*!40000 ALTER TABLE `Cb_LetraCaja` DISABLE KEYS */;
/*!40000 ALTER TABLE `Cb_LetraCaja` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Cb_TipoCajaBanco`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Cb_TipoCajaBanco` (
  `IdTipoCajaBanco` int(11) NOT NULL AUTO_INCREMENT,
  `TipoCajaBanco` varchar(255) CHARACTER SET latin1 COLLATE latin1_swedish_ci DEFAULT NULL,
  `Tipo` int(11) DEFAULT NULL,
  `AplicaDocAPagar` bit(1) DEFAULT NULL,
  `AplicaDocACobrar` bit(1) DEFAULT NULL,
  `Anulado` bit(1) DEFAULT NULL,
  PRIMARY KEY (`IdTipoCajaBanco`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_spanish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Cb_TipoCajaBanco` WRITE;
/*!40000 ALTER TABLE `Cb_TipoCajaBanco` DISABLE KEYS */;
INSERT INTO `Cb_TipoCajaBanco` VALUES (1,'PAGO A PROVEEDOR',1,0x01,0x00,0x00),(2,'PAGO DE CLIENTE',0,0x00,0x01,0x00),(3,'VALE DE EGRESO',1,0x00,0x00,0x00),(4,'VALE DE INGRESO',0,0x00,0x00,0x00);
/*!40000 ALTER TABLE `Cb_TipoCajaBanco` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `crm_audit_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `crm_audit_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `actor` varchar(100) NOT NULL,
  `action` varchar(80) NOT NULL,
  `entity_type` varchar(60) NOT NULL,
  `entity_id` varchar(64) DEFAULT NULL,
  `before_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`before_json`)),
  `after_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`after_json`)),
  `ip_hash` char(64) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ix_crm_audit_entity` (`tenant_id`,`entity_type`,`entity_id`),
  KEY `ix_crm_audit_time` (`tenant_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `crm_audit_log` WRITE;
/*!40000 ALTER TABLE `crm_audit_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `crm_audit_log` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `crm_bot_rule`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `crm_bot_rule` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `name` varchar(120) NOT NULL,
  `trigger_text` varchar(250) NOT NULL,
  `response_text` text NOT NULL,
  `priority` int(11) NOT NULL DEFAULT 100,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `handoff` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_crm_bot_rule_name` (`tenant_id`,`name`),
  KEY `ix_crm_bot_rule_active` (`tenant_id`,`is_active`,`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `crm_bot_rule` WRITE;
/*!40000 ALTER TABLE `crm_bot_rule` DISABLE KEYS */;
/*!40000 ALTER TABLE `crm_bot_rule` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `crm_campaign`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `crm_campaign` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `segment_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(160) NOT NULL,
  `channel` enum('manual','email','whatsapp','sms') NOT NULL DEFAULT 'manual',
  `status` enum('draft','scheduled','active','completed','cancelled') NOT NULL DEFAULT 'draft',
  `content_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`content_json`)),
  `starts_at` datetime DEFAULT NULL,
  `ends_at` datetime DEFAULT NULL,
  `sent_count` int(10) unsigned NOT NULL DEFAULT 0,
  `success_count` int(10) unsigned NOT NULL DEFAULT 0,
  `created_by` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ix_crm_campaign_status` (`tenant_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `crm_campaign` WRITE;
/*!40000 ALTER TABLE `crm_campaign` DISABLE KEYS */;
/*!40000 ALTER TABLE `crm_campaign` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `crm_campaign_recipient`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `crm_campaign_recipient` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `campaign_id` bigint(20) unsigned NOT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `status` enum('pending','excluded','simulated','sent','failed') NOT NULL DEFAULT 'pending',
  `idempotency_key` char(64) NOT NULL,
  `sent_at` datetime DEFAULT NULL,
  `conversion_amount` decimal(14,2) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_crm_campaign_recipient` (`tenant_id`,`campaign_id`,`customer_id`),
  UNIQUE KEY `uq_crm_campaign_idempotency` (`tenant_id`,`idempotency_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `crm_campaign_recipient` WRITE;
/*!40000 ALTER TABLE `crm_campaign_recipient` DISABLE KEYS */;
/*!40000 ALTER TABLE `crm_campaign_recipient` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `crm_channel_connection`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `crm_channel_connection` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `channel` varchar(30) NOT NULL,
  `provider` varchar(40) NOT NULL,
  `display_name` varchar(120) NOT NULL,
  `external_instance_id` varchar(120) DEFAULT NULL,
  `status` enum('disabled','disconnected','connecting','connected','error') NOT NULL DEFAULT 'disabled',
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `last_seen_at` datetime DEFAULT NULL,
  `last_error` varchar(500) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_crm_channel_connection` (`tenant_id`,`channel`,`display_name`),
  KEY `ix_crm_channel_status` (`tenant_id`,`channel`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `crm_channel_connection` WRITE;
/*!40000 ALTER TABLE `crm_channel_connection` DISABLE KEYS */;
/*!40000 ALTER TABLE `crm_channel_connection` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `crm_consent`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `crm_consent` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `channel` varchar(30) NOT NULL,
  `status` enum('granted','denied','unknown') NOT NULL DEFAULT 'unknown',
  `source` varchar(60) NOT NULL,
  `recorded_at` datetime NOT NULL,
  `created_by` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_crm_consent_channel` (`tenant_id`,`customer_id`,`channel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `crm_consent` WRITE;
/*!40000 ALTER TABLE `crm_consent` DISABLE KEYS */;
/*!40000 ALTER TABLE `crm_consent` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `crm_conversation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `crm_conversation` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `connection_id` bigint(20) unsigned DEFAULT NULL,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `channel` varchar(30) NOT NULL,
  `external_chat_id` varchar(190) DEFAULT NULL,
  `contact_name` varchar(160) DEFAULT NULL,
  `contact_phone` varchar(40) DEFAULT NULL,
  `status` enum('open','waiting','resolved','blocked') NOT NULL DEFAULT 'open',
  `sales_label` varchar(30) NOT NULL DEFAULT 'new',
  `assigned_user` varchar(100) DEFAULT NULL,
  `unread_count` int(10) unsigned NOT NULL DEFAULT 0,
  `last_message_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_crm_conversation_external` (`tenant_id`,`channel`,`external_chat_id`),
  KEY `ix_crm_conversation_queue` (`tenant_id`,`status`,`last_message_at`),
  KEY `ix_crm_conversation_sales_label` (`tenant_id`,`sales_label`,`last_message_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `crm_conversation` WRITE;
/*!40000 ALTER TABLE `crm_conversation` DISABLE KEYS */;
/*!40000 ALTER TABLE `crm_conversation` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `crm_customer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `crm_customer` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `external_customer_id` varchar(64) DEFAULT NULL,
  `display_name` varchar(180) NOT NULL,
  `document_number` varchar(32) DEFAULT NULL,
  `phone` varchar(32) DEFAULT NULL,
  `email` varchar(190) DEFAULT NULL,
  `consent_marketing` tinyint(1) NOT NULL DEFAULT 0,
  `last_purchase_at` datetime DEFAULT NULL,
  `purchase_count` int(10) unsigned NOT NULL DEFAULT 0,
  `total_spent` decimal(14,2) NOT NULL DEFAULT 0.00,
  `recency_days` int(10) unsigned DEFAULT NULL,
  `r_score` tinyint(3) unsigned DEFAULT NULL,
  `f_score` tinyint(3) unsigned DEFAULT NULL,
  `m_score` tinyint(3) unsigned DEFAULT NULL,
  `rfm_score` char(3) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `source_updated_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_crm_customer_external` (`tenant_id`,`external_customer_id`),
  KEY `ix_crm_customer_document` (`tenant_id`,`document_number`),
  KEY `ix_crm_customer_phone` (`tenant_id`,`phone`),
  KEY `ix_crm_customer_last_purchase` (`tenant_id`,`last_purchase_at`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `crm_customer` WRITE;
/*!40000 ALTER TABLE `crm_customer` DISABLE KEYS */;
INSERT INTO `crm_customer` VALUES (1,1,'10330','CONSULTORA CONSTRUCTORA NEUROSYSTEM PERU S.A.C.','20573027125',NULL,NULL,0,'2026-10-01 19:02:54',1,16.80,0,5,1,1,'511',1,'2026-10-02 12:54:07','2026-10-02 12:54:07','2026-10-02 12:54:07'),(2,1,'10331','CLIENTE VARIOS','00000000',NULL,NULL,0,'2026-10-01 21:42:45',6,385.30,0,5,3,2,'532',1,'2026-10-02 12:54:07','2026-10-02 12:54:07','2026-10-02 12:54:07');
/*!40000 ALTER TABLE `crm_customer` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `crm_customer_event`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `crm_customer_event` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `event_type` varchar(40) NOT NULL,
  `source_type` varchar(40) NOT NULL,
  `source_id` varchar(64) DEFAULT NULL,
  `amount` decimal(14,2) DEFAULT NULL,
  `occurred_at` datetime NOT NULL,
  `metadata_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata_json`)),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_crm_event_source` (`tenant_id`,`event_type`,`source_type`,`source_id`),
  KEY `ix_crm_event_customer` (`tenant_id`,`customer_id`,`occurred_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `crm_customer_event` WRITE;
/*!40000 ALTER TABLE `crm_customer_event` DISABLE KEYS */;
/*!40000 ALTER TABLE `crm_customer_event` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `crm_customer_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `crm_customer_tag` (
  `tenant_id` bigint(20) unsigned NOT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `tag_id` bigint(20) unsigned NOT NULL,
  `created_by` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`tenant_id`,`customer_id`,`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `crm_customer_tag` WRITE;
/*!40000 ALTER TABLE `crm_customer_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `crm_customer_tag` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `crm_interaction`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `crm_interaction` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `interaction_type` varchar(40) NOT NULL,
  `channel` varchar(30) DEFAULT NULL,
  `subject` varchar(180) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `occurred_at` datetime NOT NULL,
  `created_by` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ix_crm_interaction_customer` (`tenant_id`,`customer_id`,`occurred_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `crm_interaction` WRITE;
/*!40000 ALTER TABLE `crm_interaction` DISABLE KEYS */;
/*!40000 ALTER TABLE `crm_interaction` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `crm_loyalty_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `crm_loyalty_account` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `balance` int(11) NOT NULL DEFAULT 0,
  `lifetime_earned` int(10) unsigned NOT NULL DEFAULT 0,
  `lifetime_redeemed` int(10) unsigned NOT NULL DEFAULT 0,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_crm_loyalty_customer` (`tenant_id`,`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `crm_loyalty_account` WRITE;
/*!40000 ALTER TABLE `crm_loyalty_account` DISABLE KEYS */;
/*!40000 ALTER TABLE `crm_loyalty_account` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `crm_loyalty_ledger`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `crm_loyalty_ledger` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `account_id` bigint(20) unsigned NOT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `movement_type` enum('earn','redeem','expire','adjust') NOT NULL,
  `points` int(11) NOT NULL,
  `source_type` varchar(40) NOT NULL,
  `source_id` varchar(64) NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_by` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_crm_loyalty_source` (`tenant_id`,`movement_type`,`source_type`,`source_id`),
  KEY `ix_crm_loyalty_customer` (`tenant_id`,`customer_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `crm_loyalty_ledger` WRITE;
/*!40000 ALTER TABLE `crm_loyalty_ledger` DISABLE KEYS */;
/*!40000 ALTER TABLE `crm_loyalty_ledger` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `crm_message`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `crm_message` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `conversation_id` bigint(20) unsigned NOT NULL,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `direction` enum('inbound','outbound') NOT NULL,
  `sender_type` enum('customer','agent','bot','system') NOT NULL,
  `body` text NOT NULL,
  `status` enum('received','draft','queued','sent','delivered','read','failed') NOT NULL,
  `external_message_id` varchar(190) DEFAULT NULL,
  `metadata_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata_json`)),
  `sent_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_crm_message_external` (`tenant_id`,`external_message_id`),
  KEY `ix_crm_message_conversation` (`tenant_id`,`conversation_id`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `crm_message` WRITE;
/*!40000 ALTER TABLE `crm_message` DISABLE KEYS */;
/*!40000 ALTER TABLE `crm_message` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `crm_message_outbox`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `crm_message_outbox` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `campaign_id` bigint(20) unsigned DEFAULT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `channel` varchar(30) NOT NULL,
  `destination` varchar(80) NOT NULL,
  `body` text NOT NULL,
  `status` enum('simulated','pending','processing','sent','failed','cancelled') NOT NULL DEFAULT 'simulated',
  `attempts` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `idempotency_key` char(64) NOT NULL,
  `available_at` datetime NOT NULL DEFAULT current_timestamp(),
  `sent_at` datetime DEFAULT NULL,
  `last_error` varchar(500) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_crm_outbox_idempotency` (`tenant_id`,`idempotency_key`),
  KEY `ix_crm_outbox_queue` (`tenant_id`,`status`,`available_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `crm_message_outbox` WRITE;
/*!40000 ALTER TABLE `crm_message_outbox` DISABLE KEYS */;
/*!40000 ALTER TABLE `crm_message_outbox` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `crm_message_template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `crm_message_template` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `name` varchar(120) NOT NULL,
  `channel` varchar(30) NOT NULL,
  `subject` varchar(180) DEFAULT NULL,
  `body` text NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_crm_template_name` (`tenant_id`,`name`,`channel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `crm_message_template` WRITE;
/*!40000 ALTER TABLE `crm_message_template` DISABLE KEYS */;
/*!40000 ALTER TABLE `crm_message_template` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `crm_segment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `crm_segment` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `name` varchar(120) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `rules_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`rules_json`)),
  `member_count` int(10) unsigned NOT NULL DEFAULT 0,
  `refreshed_at` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_crm_segment_name` (`tenant_id`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `crm_segment` WRITE;
/*!40000 ALTER TABLE `crm_segment` DISABLE KEYS */;
/*!40000 ALTER TABLE `crm_segment` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `crm_segment_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `crm_segment_member` (
  `tenant_id` bigint(20) unsigned NOT NULL,
  `segment_id` bigint(20) unsigned NOT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `added_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`tenant_id`,`segment_id`,`customer_id`),
  KEY `ix_crm_segment_customer` (`tenant_id`,`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `crm_segment_member` WRITE;
/*!40000 ALTER TABLE `crm_segment_member` DISABLE KEYS */;
/*!40000 ALTER TABLE `crm_segment_member` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `crm_setting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `crm_setting` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_by` varchar(100) NOT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_crm_setting` (`tenant_id`,`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `crm_setting` WRITE;
/*!40000 ALTER TABLE `crm_setting` DISABLE KEYS */;
/*!40000 ALTER TABLE `crm_setting` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `crm_sync_run`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `crm_sync_run` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `source_name` varchar(80) NOT NULL,
  `status` enum('running','completed','failed') NOT NULL,
  `cursor_value` varchar(100) DEFAULT NULL,
  `rows_processed` int(10) unsigned NOT NULL DEFAULT 0,
  `error_message` varchar(1000) DEFAULT NULL,
  `started_at` datetime NOT NULL DEFAULT current_timestamp(),
  `finished_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ix_crm_sync_source` (`tenant_id`,`source_name`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `crm_sync_run` WRITE;
/*!40000 ALTER TABLE `crm_sync_run` DISABLE KEYS */;
/*!40000 ALTER TABLE `crm_sync_run` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `crm_tag`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `crm_tag` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `name` varchar(80) NOT NULL,
  `color` char(7) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_crm_tag_name` (`tenant_id`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `crm_tag` WRITE;
/*!40000 ALTER TABLE `crm_tag` DISABLE KEYS */;
/*!40000 ALTER TABLE `crm_tag` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `crm_task`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `crm_task` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `title` varchar(180) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','in_progress','done','cancelled') NOT NULL DEFAULT 'pending',
  `priority` enum('low','normal','high') NOT NULL DEFAULT 'normal',
  `assigned_user` varchar(100) DEFAULT NULL,
  `due_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `created_by` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `ix_crm_task_queue` (`tenant_id`,`status`,`due_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `crm_task` WRITE;
/*!40000 ALTER TABLE `crm_task` DISABLE KEYS */;
/*!40000 ALTER TABLE `crm_task` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `GEN_EMPRESA`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `GEN_EMPRESA` (
  `IDEMPRESA` int(11) NOT NULL AUTO_INCREMENT,
  `RAZONSOCIAL` varchar(255) DEFAULT NULL,
  `RUC` varchar(255) DEFAULT NULL,
  `DIRECCION` text DEFAULT NULL,
  `IGV` float DEFAULT NULL,
  `CONSULTARUCURL` varchar(255) NOT NULL,
  `CONSULTARUCTOKEN` text NOT NULL,
  `DESCRIPCION` varchar(255) DEFAULT NULL,
  `TELEFONO` varchar(255) DEFAULT NULL,
  `EMAIL` varchar(255) DEFAULT NULL,
  `CELULAR` varchar(255) DEFAULT NULL,
  `CONFIG` text NOT NULL,
  `UBIGEO` varchar(10) DEFAULT NULL,
  `DEPARTAMENTO` varchar(100) DEFAULT NULL,
  `PROVINCIA` varchar(100) DEFAULT NULL,
  `DISTRITO` varchar(100) DEFAULT NULL,
  `VENTASINSTOCK` tinyint(4) DEFAULT 0,
  `APPTOKEN` text DEFAULT NULL,
  PRIMARY KEY (`IDEMPRESA`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `GEN_EMPRESA` WRITE;
/*!40000 ALTER TABLE `GEN_EMPRESA` DISABLE KEYS */;
INSERT INTO `GEN_EMPRESA` VALUES (1,'IMPORT COMPANY EDEN E.I.R.L.','20612697923','PRO.ABTAO NRO. 584 OTR. CENT. CU HUANUCO (JR. ABTAO 584)',0,'https://dniruc.apisperu.com/api/v1/','eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJlbWFpbCI6Impob253akBob3RtYWlsLmNvbSJ9.zP_n6q63-AlEAeBpTj_V1J9nqUfEMYpcOU5s1Hsk2gg','VENTA DE BATERIAS, AROS Y LLANTAS AL POR MAYOR Y MENOR','','','','a:1:{s:15:\"SUMA_VALORIZADO\";d:9273.5;}','100601','HUANUCO','HUÁNUCO','HUÁNUCO',1,'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9hcHAuZmFjdHVyYS52aXAiLCJzdWIiOiJhZG1pbiIsImlhdCI6MTczNTU5ODkzMywiZGF0YSI6eyJVc3VhcmlvIjoiYWRtaW4iLCJJZFVzdWFyaW9QZXJmaWwiOjEsIklkQWxtYWNlbiI6MSwiSWREb2NWZW50YVB1bnRvVmVudGEiOjEsInJ1YyI6IjIwMzkzNjA2MDg5In19.-Rb4G5Ts49pHEP4AyjZfXJFq6ErP8ijEgYeYcXeyrWk');
/*!40000 ALTER TABLE `GEN_EMPRESA` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Gen_EmpresaConfig`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Gen_EmpresaConfig` (
  `IdEmpresaConfig` int(11) NOT NULL AUTO_INCREMENT,
  `IdEmpresa` int(11) NOT NULL,
  `MetaKey` varchar(255) DEFAULT NULL,
  `MetaValue` longtext DEFAULT NULL,
  PRIMARY KEY (`IdEmpresaConfig`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Gen_EmpresaConfig` WRITE;
/*!40000 ALTER TABLE `Gen_EmpresaConfig` DISABLE KEYS */;
INSERT INTO `Gen_EmpresaConfig` VALUES (1,1,'logo_gris','logo-gris.png'),(2,1,'dominio','daywa2.factura.vip'),(4,1,'clave_certificado','K5i573m452023TA'),(5,1,'usuario_sol','KOQETAS5'),(6,1,'clave_sol','KoQn3TaS3'),(7,1,'sunat_entorno','FE_PRODUCCION'),(8,1,'exonerado_igv','1'),(10,1,'certificado','MIACAQMwgAYJKoZIhvcNAQcBoIAkgASCA+gwgDCABgkqhkiG9w0BBwGggCSABIID6DCCBbwwggW4BgsqhkiG9w0BDAoBAqCCBPswggT3MCkGCiqGSIb3DQEMAQMwGwQUYZRWGD8oe+0umISvTKnSI4Fc27ECAwDIAASCBMiUfMQfYud6dwU1Jk9oFOTIll0Yx11AaxEEjv2zuX8NOZfis+FsXKV7zY4zwArCBbjZ9bFSqsgzUPW1MA7JlI6cQQd2CzDQF0UkRADMSaBAc3GrSYa2FK34traXSzT2etLhg2JikvGSkxSfJgEwSBblBeOZP7FpyUIMmMna3Do894qHoJpbimtbh7x7dHEiRJnSg4af2H3fzw53g9wV/Pdd4AF5mzUw8ExObB1rezVvgIgnIH7UTcxqwShVNa/DPtUJiS+fe0XdrrwU9+aOylKr9n9iyVgeorzy+Sp+Ri2OsTxLuI5MoJBRQUsp9yeFgTLdG6ukfim+19eGFuNBbIhXEHF/4Woi9bZSdpTvlpk3iz4RCG2/2uqHTYtB2MaO/iOts705bq3EVYQqqlkOZ8IhKX7zLkEICXePb6/QDmFH5PDqXAw3UrEpIptVchDUtHEFL/fxjZ3YAuAzpemD9r0n49qQwAkWUjOvExyg/Ocq+vyO0F7ayn/qA3ja03iCufc0gIdR7PoAa5OVUqg04pMGgQ6+6zVKLNnunLwQSfWPsGdjkrsF/DyffJ5QMlJdU9cRVPtiBsoExYf83kqOmMaQiqED6z6M7yRm7KlgOdvAkFCE4ULsrqRq4lj2u4RBM/bkwHDVQnMG0GvLB4plLJcj5BppysUQvgKHQKDi2nGla/EKPaMBS3a7bWYRICGWrshLptmQ3oyUyBaq9gMouqXYMYkQyE+Yi0WGAl+Atx0tIX2ATTmaH5jFCgMIb56elAFLzoB857q2JWPfb57GptzYBy6VgGWTcm6lzHDVFCG/EalVt5pnb9+OjO0/e4sX13rtO5HsdCq7R398GEIMSOWwcl6KX9XuOqZIpve7uuO0te1A4MFpEeYebjkd6+wGNxVLKrqlXWEHGFda2LvgDCeMofs3MyZxi7jMmmNF7mM8KLqDu98p4cTsj/0I0MHL+nmIp2OMcCtU7WvgIa/7Er0D6VxFQwX4RGPtGJcgeaEEKXAmShAZqOprb5YZ5h04rh4sUnl+h/ON+cwyN2nP/lFPh9J98CfG9Jv0VV5NySDm7UI8xxHukwHLNLtxFjeAW0msMSNW+Y+Bs+kKzDKqLz5hNBiVkBxc+Xo2vb/MjeJXSIwm6wNwg1fUNVfhYkws/jtAyk0t6KCIvZSu9BK2ty2AMsehetKDb60Rrk7bJSbh5AgDCLQCBIID6IGptlQtjzS41LWtqcZv2wmO5AWhTo+kBIIB2LRpqfUyr0taicnuwhHMhazcFpsy88vJV10/ts6msieyOwOEGqUQWHIY+WihlxLHomcME7xi33pQbGfIbbEDdGhfONR4Bea10ugRu6fpheefqqrjUwT4p3oDyCTZibISrzK94mS+ynIvh7sdhLQya5fFMAKBwFlKYdgSnQYNc/EIsBk2AADcbkw1Gy3/dhnLD6rC0lztVP9OsFjlkdfe4D8qfOJfehxfzSaxStE8Y+c8jaPbix4l7kdGhyoMENfzSTg45QEQod60cq+PNVulQDgllHLwAF5ZS+RtspDsRzdx1JQnLNBlQrFPKNK8zM26uY6oxKLcOnJEa54hHdeNkZ20OMZ17uU/3ORfZ6PsNqXygNX1+sl10PG7u4NKYeq0Z1/K0R/QVCzC+bzNljGBqTAjBgkqhkiG9w0BCRUxFgQUN4XnLwrfWB+JFJz45L6858Yg2SUwgYEGCSqGSIb3DQEJFDF0HnIAfAB8AFUAUwBPACAAVABSAEkAQgBVAFQAQQBSAEkATwB8AHwAIABLAE8AUQBFAFQAQQAgAEwARQBOAEMARQBSAEkAQQAgAFMALgBSAC4ATAAuACAAQwBEAFQAIAAyADAAMwA5ADMAMgA3ADMANAAxADEAAAAAAAAwgAYJKoZIhvcNAQcGoIAwgAIBADCABgkqhkiG9w0BBwEwKQYKKoZIhvcNAQwBBjAbBBS+qH9NuyNlOOpOvXUOMka9AO099AIDAMgAoIAEggPoVi01ZMxX4W3UZnNa3uuEqdUR39um3Gf7mIxZCBOOIiYaV/Z1ZwQ9qyX2gdug1Y/tVbz/qloGivn3TpIh1mwV8hMwT9fnyEf93g9Wi8IBkbBqn41CySECVqbkpQvTy8DhNLYDRw60oc87VnkXMGu409dKT3wFXp7IC+W8m/f2oJrYL48Vl1GRFu3X5HJAv7I4/MP0EGLpzLsXBQZ/AVWXj1IQwJxRQWtryKo+RXDpyFVExD2dT62nySR5oDl9eMfHhmH3aIzeZV8pJVvSjcdt7JctTKNO/1cziXJpHbYJ3aCh39258ePGo7asHuKceS1EdvVjvvoiYGP2jGAmB15vyZWzigln9ite9EBpsbAbAr59pRzfnLhfLcrCvGyOViUKJNsQv7CgSMXGBXkWftbWpfp/9Rgy4YUzwime3Puz6Or8mt3pTzdxZeCnNQgH/vvd///ExNaLrJwp6MmBIguwt7MCgaStQ2AI8xbWUdSufQMMMiP52cnkKVgYnODWh1LZccvSla7xR+OM7Hp20BZDbAJ6UtjQGRIEtuM2JJEEggPoWBIVPw590XQkA1nXcLcDkHsVfg6vIearkeZOPGG42bWlbIMHfKOI2Uo2/7bEbq18FzNOaqv/MZjLmuxTVTbILzUZfMwI7tt5jz5WH11iuLz3jHaRznRvXMlFwNzhCDzs2L1T1164wxD7FkMBTX/d0nKEyetxspEgVIg0beed2sPAqMZ8En+fH4f5EzKXadoCgSaJfxvlihCRurlyF2XVIgKf2yiHA1yZW5stp+Z4V80fVpisioY2ap33uw92OH1ueR6CWBGdwy18xQ4ZJW5PyTn/9TqY+9nK38uxgAww9oOsOkcyszd3H9Xn/pa79+DqTlyLaOu2Sdqa2nAaHSBpsQatZ4u/6+oiKM0wO+ujm+KoAm5Pe6GN+1f1zt8e6bDFWvBC2X6KuY38rH2lzIc83y0mmCRGRUCNk4kglMBt7I4H+Mt9l285o6k+5A69YNQlDyDjtjsejV3xKd9N5Z2XQ0EjyhVJ3YqJ8R8dcP85JNscP18lXcYlzdJ8qYpGxGdJk6gt63DxtlX2TLlz9/81mRpUc2EZPTJHBeO0nzTJ8mddEqyaB7jRtw57+MYl/2PedbPVP78+4c9t+BxaTjzy+5M/0w7Zx8psIyl246X5v9kO3Dd0WZ57AJ2vcDDNcSxIb7xBM4SURvByD7uMsVtMwcWIzYhAeSixbfIsDnpxblyhoxjRi3uE5zGvvZfs5G9pnr238XIgewmMA0txq7msUFwK+L2iCQBRVHxng/vU65aE+l2ZhmdszBA1ySMz1Sz2xGUkQc++HulJ0xMEggPo83Wwhofv4Gyj8SKxMXpxBqEweiF0NdDsdtgf7dghdSiPAHcYiuWiiCpz2WlDtTjb/RrlK02Fgl/MOHjWTzyEfjnBS5OflYp5OqhfBhXaqT3/hI2lY4mGG6dpYEd7LA+bDr15rGkcNAb7RkDom8Vsj71xzfNKqlELGudUB8jNyWe7u51ebX5fkyIDxqAjAnoZPMMJCzsOlVarl4fsjYoqD7mdAMEq6UyIoW0VFZ1uHj3mbY6mGS3Zm1agW4eHb4nrAzh5EF6Jm0+a93xrVwrYzoyrodcCFcG3UTGQA72GIMgERrzWtiYsviCjL53HpNQrreJgQFOGtWHieOEbitSob41ZMhzYQ7nIkwx+J68c1VpWhPh0qFIDtMsbmKsEEWo8rmF+sjBIXtV11HPJf0HlPr5HuOQezJ5m/PzZLAo62zMKc3pfyzsdABx/WtbsiP/tEgvZED7aQNUgn0zZO215HJ3qELCl1GZEVKfYSF9gSiHPvEIpVOjreT0kZm0vZ11KCQBCM7a9eRVk119uyODJn0sYPpjcUNLMXASCA+hrsQMGKrmXI/uaTMYGIvbP3MO1OMlFhDRqg0CVtqVy1OXLaVawjcblA+YQGwsnREbABRIV3OcQohIXC5KXNQMpsQbdIMpN7zDKx5xZfF5MuInLkFD1K+o0xxFYGwXd1tHH+S3oYuHUYrkrRNBe708KS5yXBVj9pUM67I/EwBdGiLsatSEeQzQisJP2lol1tb5al+cMU7H6W0jlrcqsYNln68ycu1qge3B8JElKKNFIGHhgnnSCuF5sAL82/tE/3xfTd+QcXf8TLLnqDD58jzt22iyAVWGXJdzenygIG3COqU0IvfJWcOHeCD1gzpiDIqaoW0QXVHzsaQcs4FNSu7t418tTC6IzHi0E7q11OjlFBg3N9ans1/LP5vKbehJdD9ZKH7zWbb85eFR8PXGLk4OwRgLKezLJfsqr77l69f68fS4BH+SB/qJRJ1mr/aX5XM7z0o9ck1FRJ3xNajVtN8vGJdihAMieXdaGhYOz2V3qxjGcrKG6RmRyXH8fEm3L2XFcKV7A7YyqBizjQPy1XoIPKYIagNP/y8NFH/SMRzKw/pJGJLQi6NJPwnf6J/FxMga88n9Cn0KAbPkdO5rzQSOreMeCkaAjJVedVCPhsbJ/Mqq02Ba3k+sul1sQv0B472FinUD7ZTbT1yngfUIJDzE2/jel+w7vhe15nUgBXGiTE+0VSm4eTqV2HEv64d+qpv0jUc7RhvSj7fZ6sz5vGgI6XVzlDyog/sRB9WLZfO2/WXocwbUq5hCBv+GvmIrqyBKd2Pa9uhk8EpjT/A/8igoEggPoHsShftZ7BM91bVXfa4elTxuGeZa/8ht6YhAganTzTkUxELdOfkb33Vdb3K7q7yWEzGJzOrKac2RJo1Kr+CAqRu88U4eZfcIhtOhQzSVlVZ1a6iPsCXaDXkcaASlCRQA3Y9fzaOoAS5Z5P3LzaIU4+Tk6dMvInS57OdI2Pnk4+glyLefulFXJZ6c6eddHuV4Q4Zu5BkAz9E5KNdIdBVKD9OicDYmM+/YY868cJ++YtQXRqBd0U5PJlGIyKWh2YpkLU0UergTxK66Eb4MX6mZPC5OIW8sq4ToleIPByf/IJsZNJnOUsuhB1+MkJqwBwMZCcgJFmN4qTfdSZY0tKYTdYh0dDePhJqjoZPKEHKHgl9KrvUUbNYgSUHxvjukL5ZVJSGzZVZPqAy5aVUpanTv8oaLmhx5+YLB3Tmdq95bN5JekBrA0eCf9Tp6IcvAgoAMwCpbxaLJo03FHHedXMH104ACqhOavw6qhBCZvn+5gfl1WRE+9sngZV0dYVQkCqVWQohKV87xJw/d1Q9bjgRMUaFzux8DrBIID6I3Y9Wyf439wIYbirbMYkhhmlrU53ZUUflLs2KGhgXjOj6ksgSpytMAPv6DcIFju2UTVM31QrcG1QB/UIEcUynZWvVZ2JjZbj+FtkysVYC5WrbcwkR3yZkbxim+gorn2Wsd0Xj4XR6VznzSJR3xMAxCzq/JjN8dtUsuqjFARTdIJwPzXssfJKPrMJQIRv2lmD2BE9f6u0ytUBPbptpBaLhE0mQPOWTRtoXNpXnTAzw2n8PJ2eftWOpr5BdanvLHdVsFmoXtV3pAdeuzJ+sxpNxwEGVR4gJ+cqo7me46JvCX49nG+7jqIZlIUd/8zFLG+ZfJigySK2pUkl/x+N9DLsHm0kYW8DQHyoKuifAz+T8cn6gYk57YZxt6ru2q/XdUKH7AIlvEm0yu6E+9yk9w8LC1K/Qo4l54oZ5mzCrIYwiARk7bYjVkAZNXCTdRAAGGM2Gw8hR+S0bJFTYsxfr3CvxTI16Iu7vSN0cxQ1+876EzK+OJaFN8gmOjUQxh0vVUl44KkNOLZEpqPjhitpz4i8rsynp/rtc2qwvf6vG1lKMJr7zxVGPe72tvTKsopdsRpvPd41OxT1TdCwbrAUK80tkBWA3X/uSBwzF5lkwjFI9hrqTJ+OPbZNdObMG/5m+OVdjaJ2XtEZ1+hnBMeMwGBeKoU4J1nLdFjhyzW7uGJ1+dzCprevVDd8HEfzWQ1ZlFyAngP+RVZUTbz6qO5hI2jTRBAd474cAv9otPNmNOS13nJm5mqCOtFLCoZ4IGHP1KQEq8A9wEr5S2IiJPyUmOmqVy8/DgEggPoN4gJ5odw0MYcP6DUcceUu7tKFJpI2rYvC4/5BVynS8IiqoYWa/gES2tdy3VNPJOoXFDeA+BO2rfi85dKLu7UzCYBaSzotEifQrxc4YhTw71XSon8MuDwhESTbxzaDji38MIs9BUCNSafpomb327qwctc5oJQgh70/5YXy6xRMvya0ax7115IiUQM7wR+3VPu0nNH6QIHPONAWF3T15xoBOnJf83alV4reihM6yhzU/cmHPBy1A1B72XDUPtI4RX7Tnt1LUqsLqfKhUwmm8YA5RZw2MXfLF5dRsQem9nUW0Bg+/SaDiLzU8ZCk4hDxI6tGICpGgNrBhGP9835OTPCd8xESypUsvDZzLqtJSfNLUkuUHuJUmUZ5FIVEHrZBE3FmhqBfiE9NfRu3ll8pItu3Qripf1GkoXtze7pCrSbxiVhm/Tr5gT+pFFUPXoCn6CS3pvblKsZb5Im9nMSZQNovPWR3xPGXGgY+00TWUBQy/1kqjyqXzLmGHVLx1OHFum/63mNycTqLbk5u93rwaLdaBEEggPoQRIpImwQeuwKU/OVX6khR2+yA/NGcT/jByTxZCw86W+Ix1th2R7KJVDVFpa9sB7cxzV6IXfCysx2ysSPsPQdW9FXxm+lKmkN4D7bMeXdiGnqqYJAIl+X7/aJhhXbs7tcofzIYDQE60HWkBceQQFfrAGyKfqAO48USWmvRpRK+FvrqojJ5atE9yuS5lq/CoWDkjHPyW+vuO8SD1NupTk4v67C/4LLKcmeIjUerWLkVg6Qoaohv5EhtDv5wlVhyF/mhrbV0wQJHKFzBXMIWmPF5hoFw2MjcwVS2lEALvZ47u0dNvSBA+E9IeJdFFFE9EtHey6htdsutbgGZqztXj3s5TpG16tNyPZXX05ga50//IKFdbVWG1X3Ceq+i52Zr80euiIH9R8rveKb+TPTXiyJJywM+NqCh09ux+iDbe2P9jVOUeJa8LATi3anCYG2gx4/SiQ8l+pqsFN3U/N4XhQUBwWfPjDI9H9BnIhgK3dGvHhAEseRPk4PTgOcnzdFo1tJwuS7SwBZQT1bDwfEXgBX38eJKDPG4FKYfyu7ek+GGZqdqPnfHQb1LfypHIg9DrncGI3OGW7Vr9ol5Vt0lrF9jvSQZrUGPD03woapOlql8atoAiRy6XHDZxGZsqwba7ta0yAxFlCnXECP6wMoV/0jytLBKO4PPfv7746zJJW/wlSkNb4aRXs4r8S4PdYvRX0Q6gUQF356dMqfoorEnw2RrkXSMlYEGk8luU97WWzRW7ZnMvPVT2SxSu3n0Kkoi9Wr+mCrg8y1AFHPpMK49WGYI6sxAlGpKpEEggPoDwemrDwysCjNyOg6Nd1kFKbJ/rCvSGXHjnFRecXaqyJttYuOZV6h1hmPOkJ7jyilS1kkScASE2O/Sbo1fUlIjTHCaXOHfw66V+U5JHuR2kc8iy4yqNbNiwlwcpCK6I61GyP4j0tOpgf16/8r7mppqmoyGAVng7d+3r+YSmuax49v5TwUhbkXwB3y41X7Yiv7fdnzlB7CAn1/DvihBhVrXpmq8ol/+mUpyd2jFdzlI2yTeC0Q9ALegfzdG1Y6T3hEzKNArQBYZZu3wsstQvRP/+R0RnSpmh4jVXPmMH4pM7ervOJpzaFbkiu8ysDzgLUWHP0mzWhxE1HHUGs+NnAiIwAX9XuLq9fKD5wVIhxOQ8l5NpTojmRkCnjuX2kqx6LJQathvoww4XABaqnT/NIe0JVS6WsNqOkI6jOSOFkxxNNxtodo8zfqpS0uWcKD44nY38P7FitbY8r1bKgw6HDcdh2yU6OxVA5xOLe85EyGR4UoCl7fUWIlymxxUknWu8tbgsNPgMHPERspVzpknASCA+i0Xc+DWIF/Z4OnHzLgZ4H2mTbA8AXnIjJXsPhOPgRtYTK4AsBCfV+CiAdA1ipwoaCG1aYv5TIA4U7VlbRyOgs41RtzPDyH3t7MPLzKsofc/DCkUq9WJ81g9gLqXrI10LoWpe6lBo3+7VGV8ZAVl5wNaloVj1P30tP3/E+EGnM5oIij2WwXZCVIyYbzk+O/G8aV/90TiXz5kIxlgk/bxKU8qAzZs5wvbn1C431v95NV8u1ERr2M9/4C/5G7njT5+tO80lrC96OUkdA0vRFau+MN7y3zNX032ShWDfLXLq93I6uBjtl6ncxSzLs7+tPMw4o6hbzMCoRCKawK+0yWhMhjszBl7OltwIDuw8lUb/YxZkiURXpJvuBenl+GOy8+haIb4cYZevyZfEsCeRAeVpb4KOhlGYXl8gp9VmCegIeJUOjdPqbLP4wxG0nAXi13yjaDbpXJusRRPLw/lHpx2pOEvKxpr+U7jlNrZuONPJ1V2aSuwUbQOrFuMVI+RPlu+xi55SOwBkKdcdP0yadBw/pMqmy5Dk+DWiuLPOtMDWkRRI+qM3+d52Vmv7wx5ZsXxPYhHSPIyOwUg1Xvk2OVBBDJjCoocRKczRZGyVIS4z9dciozqIG4H19OzWfcGO046yLa11pzjC82vH6NfkW7meGdAQ+noZup85iZZ90onH9SdOgKBMKH5yk6vmh/0KDQz03Xok4EofOChZxbcuI1D1DufXelTPxZiHRI+00MYJ7abolBXn58CLLhNhYUDiRfFJ/V2fehBrGomL9qwscN946CTglZtYEAaAHGyWkEggPo/O4PyTDadXV4XR/HyquFmWf/px40Kk6pPaSkLLor9P2yHO1EtTmN/HtJIzgDuHzEVl9DJl8Cp0Cyq/OfevokGXu3oMePFCpbtLZK5jVqaMmWXkDngO2/YJboLGhdT0HcXApDOeQUW7zuUnnyVLSuRcWAObvX+qKfqfCR0HgnnXayB3Q0RZ+Hpzx7urmLpQmCX2A86rgTNPA+ukVBcC9LeKadZSBKmWJtL9hOMyOOs3PyptWy/XKZKkQBuuiM++wfCsWH4iQ6FyAf9wsfbXB63MrvdfiysNsqdejm2vUWckeIjoyAOACxs8RPV3KdywdzEyp+lSo/PEDiuODKic0zphcHiRvFX1+PiSyfvI5K7c4YTlmsNybIQTOqUt5kqBUy/29Zaa9vEE1iJKSD/3d46f+VaOd7/s0Vk3jk5ylos/Med2KIsqUf17WDF0jtX90vmxb8HSB1a/Vt9uJ9oV0zVf+aBcrOwCEQUTBD6KTFelL6mxw51+R6SDzhIM9k21SCD6hWIH+4SeOfBIID6IZO4hWYZJhKQ+7tIdETKRs1fFd7m9/1QU2pBMAyga42I2HI7lBCoYfCbMyr/je50dTwFaYyKXb8R5B64XZqkzbyEdxgzXwApvJKSrDgQayP9E0BmaL2SIBA5Tjds1TF5UEyJ+hiu0aZdrhsa7jyS4yN7yfUsmik9EcCo5Z+DCSfv1G1H+LYrF+GZjLmU/XqKS32y8G4I58fOIwLrkhzne85R6otTzDaCRbXbQZKNpHycevy27SjY9OH3l+WG1Ele7OXID21Y0jYWZfOfwxXJNlCM3QV7bLPtGrwBBJQL4MmHe2B0baQJvEGRq/NRbXBhH6lPjLKbP87MsDSzehixDirr4nzQS85ETnasqe0r2dh4H+GZJx8OUsHWw3GkdZqwQUZWd1cW20k2oRfiD7eQcvDJ6LFKG1H2Gq1gmLle/yskySxLUvsARIDSSBHV58AO2w08E/+uMEeHjjr64qWF9sle1XE6VKITsb9BKqzZ0BrdMXpOORVaBPASC06r2mcRYYyOAt+TIo/oFiK0cxgpCvTPxpbU4JzgmHPy3fEED+D1s5soSkHU/rMyTkYSwBECe5dhPd/zw+nnVrwsP8KQJWgpB1q3iMWYwhVlnW2sEgYiUf0CPpVw4Z/rxK/LiiMa2PXPtW7am0+saCCnpPEr8sBusChZrfTHqHxpyIuGuXns5bSPLwNKBJnONSM1YGydYuoOC+qTI3PS2odpBxhtZreNBIGNgyaYxqNXCCUPZO0Or/ycKQU5zOJLaFA8zBuGOXgd2veWOKFru3qkrB9+4ElLpI7gB18u5UYKJJ2v+sEggPofUPnO9RL8fKB/aS8AOYU5fKUet0wDaZdiwZkogWyPsE5sa9RHczD/zEGDHlBKI3h8kGA4VgBqUNrU1gcwDajrkV5lMVlXyXOZHFGoQovgObQxogeqPRwBgcpHwt10D04CKs7kZhnWk7/JMVEAQQPFamhNWbNaembL30SDye6IRzJNCJaHAir1cRcNjwb7A9b6kid+OH40Vk79iQtAuiBEorkOpOJySXo5Ui50RiPd6csHdzdI8K7pYADrt10MYo83gi6atpkMDcvcsyiPTQ1XIaOCfrfK+Mk1odf0jYPTDkH4ijzb3aOCqtDIXfl1ILHb96EOucvwXiJLtSwuoPUGsyNaaJNQ03FeRlp1bp/l0O2wlZb7uhCR54ywuNtalKGSPxDoFmBZNJi8TVBNPearyrt3/wkapL/Gm3kk81y1zfgUejrkP5PXJEozuvMJp4MUvziqiRRaHSnz2lNAkm7AN3yvVZo+3FTiuxljf9v6/GgqCeV9tIOBHyxBx52PlV7yFXbATAEggPovr9kmAK37osOyAtMCJi7DJALoJgP96xyCxugU1PQNR5TgCSayZqKJa46aXjYi6nxhnf2HB8yphwmooaj1rna9rKFb9uc0IPZw1ayL2LEIptVXv542FAbX4dhkwWtKscTmiPtIuGylPiCbS8bKwx3NoqwmXtQptUJQ5j4kjiFu5+IOvvgaQoDNbFzrhMpi2ubPI4ounhmrHjX7GXbeAr7lBYxaLYVEefI8rhJfq/8v11p+tTNmBElrfDvB1TEcIvi6nDSsPTyVXgBhtvjbVcAVh6rwHd4ls7LvDqK2gtcNjGHv4R9Xm9TvM5lwRRjSovnSFvOyN52gY62iV1BgPSoNysj1HmC2CWLv2hO32kVg2F6b2hPMk5a2b60hfexikawDtPClM72wQWeNRJbc7VtAdx1e54w5yb3Aa3Zyi3EwxWc77E2N3w0vaPA/YesCX8vgHHXvVZLYLFtY9r8TPainFEE8+KJ12bEwhuf11LBJ2i1KMHc7i1JfctpNnuxv7iW9CpmXQdwlvt2Ubg/NVf/SKsgjZyWjCMS2oIv2ojcEW8T4J7i3WXYfv+pNSrqnanRVzAIQj1f2/uj7u2QpXdZ9z2Tfc4pNEEhS1kb4ILZBuNcZiVF1UbY7m6OaVJMYOmIWU+BZNkNHPEqlSonHhr8k5n6sGNRmPFuMo01tByKe1OZFWgkULqXjfGIj/gSTaThA9P/1ugWRS+WhzxOgYnfTjMc3cCNZ8/BenvIQNChyFGOvS13lwPkOE+X0dRGDgYbBtWrUCWtDCIYcIkRM7sAFW96uP6A3RwDODBs+AHTgmjI5WQEggKoILkTczeueiEBK5dWmFvpVyuQ8C18KcrhcG1tbJzP2s3CGFvbdYJcmXY4Elywq0Ym7Nea5YO18UlkiqzEHq6AsriUEsv25SN6mX383YmOjQ1xrpRM7mpOSg4wxzxQL8paqXScMh8EZUFsbXU+nYFCmNx0BoOioSy9S3Bpe/f3Rh/U/N0BxbvcHGNdpx/FbiLmzM3Yjs7wfp/drFqbmUn7rIPeZIPf6FsbJFBitvdslsSWyE35tEFBePXVSERWp1k+XNUNh/Vr7yogX7PpB2rc0MUQLPe7QIYDt5IvPZq5rEBsZyCI5VD3ulHewWApnZfcdCdLsZn+fwCyfrutqJiQr5p4wtjfcGvrxT199NM05/5vebEmTC1jbuKKatCOd22C4X7YqUUAL6W7SZnUnXZ6UgNeTxOjPfeefAIckKqGR1G4JDf87l2WT4hyALv3y6qgKRGvKEtcghR+eXf3cNjcP9f/fxcceb8WKKmawzlDbmxH46ROBCENVixiCkm7PsIsBwSCATOwWf2hr8U/waPTLyQJTFlO2iBMw1CXf6D7EuA7TlDcnIAK8/wlya9xnYkmVHjoymI4EhZFgatdYrowkQjpRrZF+BvLTUfqmfRKmzAxh4w6YsQDSPtHChmEO8VjiO0gWKSK84UMsWjhLM09pPkBND/phZtmpVeLVM4KvnHIZ/V+l5vDKMI+kQz2ojMByT5liu6x2hh1VfiX6EOE3qo/WZTIxEMChFiSfTOJAaCIb1snXZAxdyw7GkQE8JI96miBIA4efSfyDvLM1u5M4hN8ARATNV9WzF/v8vRH80a705j6sZkjtVTaZuvvsRUQB8D5ab1NEZLOhsFQ+sGs6XJDZ2dILAZzQDcL1rMFVILDdoKLCYOh7OOstOCNVJMcbbExXsKfKbFBrpDbAAAAAAAAAAAAAAAAAAAAAAAAMD4wITAJBgUrDgMCGgUABBRn6rGHR/vggxk461gpG3HL3b8TMgQUQ0q8FCDmB4KUZVpnGNSdT3K2kf4CAwGQAAAA'),(11,1,'nombre_comercial','Comercial Genesis'),(12,1,'telefono_soporte','997 578 199'),(13,1,'logo_principal','resources/uploads/empresa/logo_principal-20261002043422-1c191a.jpg'),(14,1,'logo_ticket','resources/uploads/empresa/logo_ticket-20261002043422-0337c6.jpg'),(15,1,'imagen_proforma','resources/uploads/empresa/imagen_proforma-20261002043422-fdf74b.jpg'),(16,1,'actividad_comercial','VENTA DE BATERIAS, AROS Y LLANTAS AL POR MAYOR Y MENOR'),(17,1,'etiqueta_ancho_mm','50'),(18,1,'etiqueta_alto_mm','30'),(19,1,'etiqueta_columnas','2'),(20,1,'etiqueta_mostrar_color','0'),(21,1,'etiqueta_mostrar_genero','0'),(22,1,'etiqueta_mostrar_botapie','0'),(23,1,'etiqueta_mostrar_precio','0'),(24,1,'etiqueta_mostrar_talla','0'),(25,1,'etiqueta_logo','resources/uploads/etiquetas/logo-etiqueta-20261002184740-1b0460.jpg');
/*!40000 ALTER TABLE `Gen_EmpresaConfig` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Gen_Impresora`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Gen_Impresora` (
  `IdImpresora` int(11) NOT NULL AUTO_INCREMENT,
  `NombreImpresora` varchar(255) DEFAULT NULL,
  `Ip` varchar(50) DEFAULT NULL,
  `Puerto` varchar(10) DEFAULT NULL,
  `EsCocina` tinyint(1) DEFAULT 0,
  `LongitudLinea` int(11) DEFAULT NULL,
  PRIMARY KEY (`IdImpresora`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Gen_Impresora` WRITE;
/*!40000 ALTER TABLE `Gen_Impresora` DISABLE KEYS */;
INSERT INTO `Gen_Impresora` VALUES (1,'PUNTO DE VENTA','192.168.1.68','9100',0,48);
/*!40000 ALTER TABLE `Gen_Impresora` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Gen_Moneda`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Gen_Moneda` (
  `Moneda` varchar(255) DEFAULT NULL,
  `TipoCambio` float DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Gen_Moneda` WRITE;
/*!40000 ALTER TABLE `Gen_Moneda` DISABLE KEYS */;
INSERT INTO `Gen_Moneda` VALUES ('PEN',1),('USD',3.3);
/*!40000 ALTER TABLE `Gen_Moneda` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Gen_Producto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Gen_Producto` (
  `IdProducto` int(11) NOT NULL AUTO_INCREMENT,
  `IdProductoMarca` int(11) NOT NULL,
  `IdProductoFormaFarmaceutica` int(11) NOT NULL,
  `IdProductoMedicion` int(11) NOT NULL,
  `IdProductoCategoria` int(11) NOT NULL,
  `IdProductoTalla` int(11) NOT NULL,
  `Producto` varchar(255) DEFAULT NULL,
  `ProductoDesc` text DEFAULT NULL,
  `ProductoDescCorto` varchar(255) DEFAULT NULL,
  `CodigoBarra` varchar(255) DEFAULT NULL,
  `CodeBarModelo` text DEFAULT NULL,
  `Codigo` varchar(255) DEFAULT NULL,
  `Dosis` varchar(255) DEFAULT NULL,
  `PrecioContado` float DEFAULT NULL,
  `PrecioPorMayor` float DEFAULT NULL,
  `StockPorMayor` float DEFAULT NULL,
  `StockMinimo` float DEFAULT NULL,
  `Anulado` tinyint(1) DEFAULT NULL,
  `FechaReg` datetime DEFAULT NULL,
  `UsuarioReg` varchar(255) DEFAULT NULL,
  `FechaMod` datetime DEFAULT NULL,
  `UsuarioMod` varchar(255) DEFAULT NULL,
  `Hash` varchar(255) DEFAULT NULL,
  `ControlaStock` bit(1) DEFAULT NULL,
  `EsChatarra` tinyint(1) NOT NULL DEFAULT 0,
  `PrecioCosto` float DEFAULT NULL,
  `VentaEstrategica` bit(1) DEFAULT NULL,
  `PorcentajeUtilidad` float DEFAULT NULL,
  `IdBloque` int(11) DEFAULT NULL,
  `Genero` varchar(45) DEFAULT NULL,
  `Color` varchar(255) DEFAULT NULL,
  `Botapie` varchar(255) DEFAULT NULL,
  `ProductoModelo` varchar(255) NOT NULL,
  `EsPadre` tinyint(1) NOT NULL DEFAULT 0,
  `Imagen` varchar(255) DEFAULT NULL,
  `Imagen2` varchar(255) DEFAULT NULL,
  `Imagen3` varchar(255) DEFAULT NULL,
  `PorcentajeLiquidacion` float DEFAULT 0,
  `TotalCompras` decimal(19,4) DEFAULT 0.0000,
  `TotalVentas` decimal(19,4) DEFAULT 0.0000,
  `PreciosPorProducto` text DEFAULT NULL,
  `TienePresentacion` tinyint(1) DEFAULT 0,
  `Anaquel` text DEFAULT NULL,
  PRIMARY KEY (`IdProducto`,`IdProductoMarca`,`IdProductoFormaFarmaceutica`,`IdProductoMedicion`,`IdProductoCategoria`,`IdProductoTalla`),
  KEY `fk_Producto_ProductoMarca1_idx` (`IdProductoMarca`),
  KEY `fk_Producto_ProductoFormaFarmaceutica1_idx` (`IdProductoFormaFarmaceutica`),
  KEY `fk_Producto_ProductoMedicion1_idx` (`IdProductoMedicion`),
  KEY `fk_Producto_ProductoCategoria1_idx` (`IdProductoCategoria`),
  CONSTRAINT `fk_Producto_ProductoCategoria1` FOREIGN KEY (`IdProductoCategoria`) REFERENCES `Gen_ProductoCategoria` (`IdProductoCategoria`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_Producto_ProductoFormaFarmaceutica1` FOREIGN KEY (`IdProductoFormaFarmaceutica`) REFERENCES `Gen_ProductoFormaFarmaceutica` (`IdProductoFormaFarmaceutica`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_Producto_ProductoMarca1` FOREIGN KEY (`IdProductoMarca`) REFERENCES `Gen_ProductoMarca` (`IdProductoMarca`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_Producto_ProductoMedicion1` FOREIGN KEY (`IdProductoMedicion`) REFERENCES `Gen_ProductoMedicion` (`IdProductoMedicion`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=880 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Gen_Producto` WRITE;
/*!40000 ALTER TABLE `Gen_Producto` DISABLE KEYS */;
INSERT INTO `Gen_Producto` VALUES (1,615,1,6,309,8,'BATERIA ENERJET MT36 12V 360AMP G36 L','','','ICE1','',NULL,NULL,194.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-1-8503',0x01,0,128.34,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(2,615,1,6,309,8,'BATERIA ENERJET 15MB90 12V 1051AMP G48 L','','','ICE2','',NULL,NULL,386.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-2-7295',0x01,0,297.4,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(3,615,1,6,309,8,'BATERIA ENERJET TC54 12V 710AMP G60 L','','','ICE3','',NULL,NULL,302.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-3-5259',0x01,0,232.824,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(4,615,1,6,309,8,'BATERIA ENERJET DS50 12V 550AMP G40 L','','','ICE4','',NULL,NULL,267.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-4-1366',0x01,0,205.48,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(5,615,1,6,309,8,'BATERIA ENERJET WH60 12V 720AMP G42 L','','','ICE5','',NULL,NULL,297.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-5-9678',0x01,0,228.5,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(6,615,1,6,309,8,'BATERIA ENERJET ST69 12V 810AMP G24 L','','','ICE6','',NULL,NULL,322.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-6-1260',0x01,0,248.1,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(7,615,1,6,309,8,'BATERIA ENERJET 15M99 12V 1162AMP G27 L','','','ICE7','',NULL,NULL,450,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-7-8691',0x01,0,346.12,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(8,615,1,6,309,8,'BATERIA ENERJET 17T114 (NO) 12V 1218AMP G31 R','','','ICE8','',NULL,NULL,554.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-8-6421',0x01,0,426.42,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(9,615,1,6,309,8,'BATERIA ENERJET 17T114 (TE) 12V 1218AMP G31 R','','','ICE9','',NULL,NULL,538.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-9-6283',0x01,0,413.98,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(10,615,1,6,309,8,'BATERIA ENERJET 19P130 12V 1370AMP G120 L','','','ICE10','',NULL,NULL,657.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-10-4507',0x01,0,505.76,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(11,615,1,6,309,8,'BATERIA ENERJET 21P144 12V 1482AMP G4D L','','','ICE11','',NULL,NULL,692.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-11-9114',0x01,0,532.55,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(12,616,1,6,309,8,'BAT BOLT HD40-38P 12V 38AH-275 G40 L','','','ICE12','',NULL,NULL,170.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-12-4835',0x01,0,133.45,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(13,616,1,6,309,8,'BAT BOLT HD42-11 12V 60AH-432 G42 L','','','ICE13','',NULL,NULL,231.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-13-8025',0x01,0,185.19,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(14,616,1,6,309,8,'BAT BOLT HD24-11 12V 69AH-506 G24 L','','','ICE14','',NULL,NULL,239.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-14-7818',0x01,0,191.31,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(15,616,1,6,309,8,'BAT BOLT HD27-11 12V 71AH-506 G27 L','','','ICE15','',NULL,NULL,293.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-15-5884',0x01,0,202.34,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(16,616,1,6,309,8,'BAT BOLT HD48-15 12V 88AH-604 G48 L','','','ICE16','',NULL,NULL,297.9,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-16-7248',0x01,0,229.19,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(17,616,1,6,309,8,'BATERIA BOLT MF57220 12V 75AH CJ48','','','ICE17','',NULL,NULL,320.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-17-4336',0x01,0,224.4,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(18,616,1,6,309,8,'BAT BOLT HD120-19 12V 128AH-855 G120 L','','','ICE18','',NULL,NULL,465.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-18-6097',0x01,0,372.61,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(19,616,1,6,309,8,'BAT BOLT HD150-21 12V 142AH-950 G4D L','','','ICE19','',NULL,NULL,523.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-19-5097',0x01,0,418.8,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(20,616,1,6,309,8,'BATERIA BOLT HD150-23L 12V 156AH G4D','','','ICE20','',NULL,NULL,556.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-20-4122',0x01,0,440.9,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(21,616,1,6,309,8,'BATERIA BOLT MF90D26FL 12V 72AH CJ24','','','ICE21','',NULL,NULL,293.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-21-9933',0x01,0,208.1,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(22,616,1,6,309,8,'BATERIA BOLT MF44B19FL 12V 40AH CJNS40','','','ICE22','',NULL,NULL,188.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-22-2205',0x01,0,157,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(23,617,1,6,310,8,'LLANTA GOOD YEAR 6.50-13 G8 TAXI (CONVENCIONAL)','','','ICE24','',NULL,NULL,192,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-23-4756',0x01,0,195,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(24,618,1,6,310,8,'LLANTA EQI 5.00-12 8PR 85J LT','','','ICE25','',NULL,NULL,117.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-24-9951',0x01,0,98,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(25,619,1,6,309,8,'BATERIA SOLITE EFM M42R 12V 40AH CJNS40','','','ICE26','',NULL,NULL,226.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-25-6202',0x01,0,161.5,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(26,619,1,6,309,8,'BATERIA SOLITE 42B19 12V 38AH G40 L','','','ICE27','',NULL,NULL,268.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-26-7309',0x01,0,191.69,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(27,619,1,6,309,8,'BATERIA SOLITE MF55066 12V 50AH G36 L','','','ICE28','',NULL,NULL,334,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-27-3564',0x01,0,238.59,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(28,619,1,6,309,8,'BATERIA SOLITE MF55040 12V 50AH G42 L','','','ICE29','',NULL,NULL,334,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-28-9463',0x01,0,238.59,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(29,619,1,6,309,8,'BATERIA SOLITE CMF56318 12V 63AH G48 L','','','ICE30','',NULL,NULL,407.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-29-7610',0x01,0,291.19,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(30,619,1,6,309,8,'BATERIA SOLITE 75D23 12V 65AH G35 L','','','ICE31','',NULL,NULL,380.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-30-1736',0x01,0,271.48,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(31,619,1,6,309,8,'BATERIA SOLITE 85D26 12V 70AH G24 L','','','ICE32','',NULL,NULL,427.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-31-3678',0x01,0,305.49,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(32,619,1,6,309,8,'BATERIA SOLITE 105D31 12V 90AH G27 L','','','ICE33','',NULL,NULL,497.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-32-5190',0x01,0,355.49,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(33,619,1,6,309,8,'BATERIA SOLITE CMF120 12V 120AH G120 L','','','ICE34','',NULL,NULL,804.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-33-3445',0x01,0,574.47,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(34,620,1,6,311,8,'BATERIA CAPSA U1R450 12V 450/250AMP GU1 L','','','ICE35','',NULL,NULL,140.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-34-7152',0x01,0,116.93,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(35,620,1,6,312,8,'BBATERIA CAPSA NS40L670 12V 670/340 G40','','','ICE36','',NULL,NULL,234.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-35-4808',0x01,0,213.36,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(36,620,1,6,312,8,'BATERIA CAPSA 42I700 12V 700/470 AMP CJ42','','','ICE37','',NULL,NULL,319.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-36-3360',0x01,0,245.97,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(37,620,1,6,312,8,'BATERIA CAPSA 24R800 12V 800/450 AMP CJ24','','','ICE38','',NULL,NULL,259.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-37-9942',0x01,0,216,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(38,620,1,6,312,8,'BATERIA CAPSA 27R 1150 12V G27 L','','','ICE39','',NULL,NULL,369.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-38-4575',0x01,0,308,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(39,620,1,6,312,8,'BATERIA CAPSA 4DLTI 1900 12V 1900/1060 AMP CJ4D','','','ICE40','',NULL,NULL,638.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-39-9302',0x01,0,531.859,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(40,621,1,6,309,8,'BATERIA RECORD RNS 40 12V 458AMP G40 L','','','ICE41','',NULL,NULL,241.9,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-40-8175',0x01,0,188.5,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(41,621,1,6,309,8,'BATERIA RECORD RNS 45 12V 550AMP G40 L','','','ICE42','',NULL,NULL,252.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-41-7316',0x01,0,210.39,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(42,621,1,6,309,8,'BATERIA RECORD RFF 65 12V 582AMP G60 L','','','ICE43','',NULL,NULL,267,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-42-1751',0x01,0,222.48,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(43,621,1,6,309,8,'BATERIA RECORD RW 60 12V 655AMP G42 L','','','ICE44','',NULL,NULL,261,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-43-4454',0x01,0,217.48,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(44,621,1,6,309,8,'BATERIA RECORD RW 70 12V 695AMP G42 L','','','ICE45','',NULL,NULL,304.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-44-1334',0x01,0,253.62,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(45,621,1,6,309,8,'BATERIA RECORD RMB 85 12V 785AMP G48 L','','','ICE46','',NULL,NULL,334.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-45-5397',0x01,0,278.6,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(46,621,1,6,309,8,'BATERIA RECORD RC 60 12V 609AMP G24 L','','','ICE47','',NULL,NULL,252.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-46-8381',0x01,0,210.12,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(47,621,1,6,309,8,'BATERIA RECORD RC 65 12V 786AMP G24 L','','','ICE48','',NULL,NULL,268.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-47-3011',0x01,0,224.04,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(48,621,1,6,309,8,'BATERIA RECORD RC 70 12V 791AMP G24 L','','','ICE49','',NULL,NULL,299.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-48-8775',0x01,0,249.48,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(49,621,1,6,309,8,'BATERIA RECORD RF 65 12V 655AMP G27 L','','','ICE50','',NULL,NULL,302.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-49-7790',0x01,0,251.92,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(50,621,1,6,309,8,'BATERIA RECORD RF 75 12V 791AMP G27 L','','','ICE51','',NULL,NULL,360.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-50-9226',0x01,0,300.58,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(51,621,1,6,309,8,'BATERIA RECORD RF 95 12V 900AMP G27 L','','','ICE52','',NULL,NULL,392.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-51-2781',0x01,0,326.77,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(52,621,1,6,309,8,'BATERIA RECORD RT 115 (TE) 12V 1200AMP G31 R','','','ICE53','',NULL,NULL,477.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-52-3373',0x01,0,397.88,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(53,621,1,6,309,8,'BATERIA RECORD RT 115 (NO)12V 1200AMP G31 R','','','ICE54','',NULL,NULL,489.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-53-9378',0x01,0,407.98,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(54,621,1,6,309,8,'BATERIA RECORD RT 130 12V 1380AMP G4D L','','','ICE55','',NULL,NULL,583.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-54-7474',0x01,0,486.28,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(55,621,1,6,309,8,'BATERIA RECORD RT 165 12V 1670AMP G4D R','','','ICE56','',NULL,NULL,725.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-55-3223',0x01,0,604.44,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(56,621,1,6,309,8,'BATERIA RECORD RT 202 12V 1826AMP G8D R','','','ICE57','',NULL,NULL,772.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-56-4703',0x01,0,643.6,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(57,619,1,6,309,8,'BATERIA SOLITE 55B24 12V 45AH G60 L','','','ICE58','',NULL,NULL,299.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-57-6028',0x01,0,214.05,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(58,622,1,6,309,8,'BAT ETNA HL-11 12V 57AH-516 G40 L','','','ICE59','',NULL,NULL,274.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-58-9387',0x01,0,228.651,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(59,623,1,6,309,8,'BATERIA ENERLITE FH-1215 12V 90AH CJ27','','','ICE60','',NULL,NULL,348,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-59-2266',0x01,0,290,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(60,622,1,6,309,8,'BAT ETNA SU-1217 (TE) 12V 113AH G31 L','','','ICE61','',NULL,NULL,551.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-60-8199',0x01,0,388.21,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(61,624,1,6,313,8,'BATERIA MOTO ZERO 12N5-3B 12V 5AH L','','','ICE62','',NULL,NULL,66.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-61-7491',0x01,0,39.03,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(62,624,1,6,313,8,'BATERIA MOTO ZERO YTX4L-BS 12V 4AH L','','','ICE63','',NULL,NULL,65.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-62-9728',0x01,0,38.67,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(63,624,1,6,313,8,'BATERIA MOTO ZERO YTX5L-BS 12V 5AH L','','','ICE64','',NULL,NULL,71.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-63-5117',0x01,0,42.09,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(64,624,1,6,313,8,'BATERIA MOTO ZERO 12N6.5-3B 12V 6.5AH L','','','ICE65','',NULL,NULL,74.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-64-7111',0x01,0,43.79,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(65,624,1,6,313,8,'BATERIA MOTO ZERO YTX7L-BS 12V 7AH L','','','ICE66','',NULL,NULL,91.9,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-65-4301',0x01,0,54.07,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(66,624,1,6,313,8,'BATERIA MOTO ZERO YTX7A-BS 12V 7AH R','','','ICE67','',NULL,NULL,92.9,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-66-7677',0x01,0,54.67,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(67,624,1,6,313,8,'BATERIA MOTO ZERO YTX9-BS 12V 9AH R','','','ICE68','',NULL,NULL,106.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-67-5553',0x01,0,62.63,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(68,624,1,6,313,8,'BATERIA MOTO ZERO 12N9-4B 12V 9AH R','','','ICE69','',NULL,NULL,100.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-68-6074',0x01,0,59.01,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(69,625,1,6,313,8,'BATERIA MOTO OXFORD YB5L-BS 12V 5AH','','','ICE70','',NULL,NULL,104.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-69-2512',0x01,0,61.63,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(70,625,1,6,313,8,'BATERIA MOTO OXFORD YTX9-BS 12V 9AH','','','ICE71','',NULL,NULL,155.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-70-8542',0x01,0,91.48,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(71,626,1,6,313,8,'BATERIA MOTO YUASA 12N5-3B 12V 5AH','','','ICE72','',NULL,NULL,127.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-71-8639',0x01,0,85,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(72,626,1,6,313,8,'BATERIA MOTO YUASA YTX7L-BS 12V 6AH','','','ICE73','',NULL,NULL,235.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-72-8009',0x01,0,156.71,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(73,626,1,6,313,8,'BATERIA YUASA NP7-12 12V 7AH','','','ICE74','',NULL,NULL,130.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-73-1259',0x01,0,81.7,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(74,626,1,6,313,8,'BATERIA MOTO YUASA YB3-B 12V 3AH','','','ICE75','',NULL,NULL,77.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-74-9604',0x01,0,51.6,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(75,626,1,6,313,8,'BATERIA MOTO YUASA YB7B-B 12V 7AH','','','ICE76','',NULL,NULL,127.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-75-6927',0x01,0,85.1,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(76,626,1,6,313,8,'BATERIA MOTO YUASA YB4L-BS 12V 4AH','','','ICE77','',NULL,NULL,88.9,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-76-5440',0x01,0,59.29,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(77,627,1,6,313,8,'BATERIA MOTO OUTDO 6N6L-BS 6V 6AH','','','ICE78','',NULL,NULL,59.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-77-6190',0x01,0,35,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(78,620,1,6,313,8,'BATERIA MOTO CAPSA CB4L-B 12V 4AH','','','ICE79','',NULL,NULL,57,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-78-6388',0x01,0,38,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(79,620,1,6,313,8,'BATERIA MOTO CAPSA 12N7B-4A 12V 7AH','','','ICE80','',NULL,NULL,78,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-79-5672',0x01,0,52,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(80,620,1,6,313,8,'BATERIA MOTO CAPSA 12N7A-3A 12V 7AH','','','ICE81','',NULL,NULL,93,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-80-9685',0x01,0,62,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(81,622,1,6,309,8,'BATERIA ETNA FF-11 12V 60AH G60 L','','','ICE82','',NULL,NULL,320.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-81-2627',0x01,0,256.209,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(82,628,1,6,309,8,'BATERIA MOTO BMT 12N7A-3A 12V 7AH','','','ICE83','',NULL,NULL,76.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-82-7349',0x01,0,51,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(83,629,1,6,309,8,'BATERIA CSB NP7 MH14533 12V 7AH GEL','','','ICE84','',NULL,NULL,48.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-83-3336',0x01,0,27,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(84,630,1,6,314,8,'MANTENIMIENTO DE BATERIA','','','ICE85','',NULL,NULL,11,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-84-9849',0x01,0,1,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(85,630,1,6,314,8,'CARGA DE BATERIA \"LENTA Y RAPIDA\"......','','','ICE87','',NULL,NULL,11,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-85-6070',0x01,0,1,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(86,624,1,6,315,8,'BATERIA ZERO T-800 12V 245AMP CJU1 (TRIMOTO)','','','ICE90','',NULL,NULL,134.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-86-3579',0x01,0,112.26,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(87,622,1,6,309,8,'BAT ETNA HL-11P 12V 57AH-516 G40 L','','','ICE91','',NULL,NULL,274.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-87-1382',0x01,0,228.651,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(88,619,1,6,309,8,'BATERIA SOLITE CMF50AL 12V 50AH G35 L','','','ICE92','',NULL,NULL,317.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-88-5004',0x01,0,226.8,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(89,625,1,6,313,8,'BATERIA OXFORD 6-EVF-35 M. ELECTRICA 12V 35AH','','','ICE93','',NULL,NULL,385.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-89-2410',0x01,0,226.55,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(90,631,1,6,316,8,'ARO BAJAJ KGM JCPBS6 4.00-8 (FIERRO) 4H','','','ICE94','',NULL,NULL,52.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-90-4638',0x01,0,33,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(91,632,1,6,316,8,'ARO BAJAJ KGM 4.00-8 SPL (FIERRO)','','','ICE95','',NULL,NULL,46.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-91-8274',0x01,0,33,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(92,631,1,6,316,8,'ARO BAJAJ TVS JTKNEW 4.00-8 PSD (FIERRO) 4H','','','ICE96','',NULL,NULL,59.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-92-4316',0x01,0,33,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(93,633,1,6,317,8,'CAMARA S&M 4.00-8 TR87C NATURAL','','','ICE97','',NULL,NULL,17.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-93-2663',0x01,0,7,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(94,634,1,6,317,8,'CAMARA BAJAJ ARL 4.00-8TR87C (INDIA)','','','ICE98','',NULL,NULL,21.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-94-7205',0x01,0,9.5,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(95,635,1,6,317,8,'CAMARA JVX 3.00-17/2.75-17 TR4','','','ICE99','',NULL,NULL,20.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-95-2556',0x01,0,8.2,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(96,635,1,6,317,8,'CAMARA JVX 3.00-18/2.75-18 TR4','','','ICE100','',NULL,NULL,21.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-96-9555',0x01,0,8.5,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(97,636,1,6,310,8,'LLANTA VGOOD 90/90-10 SCOOTER','','','ICE101','',NULL,NULL,78,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-97-1527',0x01,0,39,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(98,636,1,6,310,8,'LLANTA VGOOD 130/60-13 SCOOTER','','','ICE102','',NULL,NULL,114,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-98-1693',0x01,0,57,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(99,633,1,6,310,8,'LLANTA S&M 3.50-10 SCOOTER WSX-039TT','','','ICE103','',NULL,NULL,86,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-99-9690',0x01,0,43,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(100,634,1,6,310,8,'LLANTA ARL 90/100-10 SCOOTER','','','ICE104','',NULL,NULL,77,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-100-1255',0x01,0,55,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(101,634,1,6,310,8,'LLANTA ARL 90/90-12 SCOOTER','','','ICE105','',NULL,NULL,108,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-101-5021',0x01,0,60,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(102,637,1,6,318,8,'BORNE BOSCH PAR P (- +)','','','ICE106','',NULL,NULL,16.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-102-8109',0x01,0,6.7,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(103,638,1,6,319,8,'BORNE BOSCH UNI P (- +)','','','ICE107','',NULL,NULL,8.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-103-9634',0x01,0,3.35,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(104,639,1,6,320,8,'TUERCA CONICA 12X1.50','','','ICE108','',NULL,NULL,8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-104-2640',0x01,0,4,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(105,639,1,6,320,8,'TUERCA TUBULAR 12X1.50','','','ICE109','',NULL,NULL,22,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-105-9826',0x01,0,2,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(106,639,1,6,320,8,'TUERCA TUBULAR 12X1.25','','','ICE110','',NULL,NULL,5.9,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-106-2210',0x01,0,3.5,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(107,639,1,6,318,8,'LLAVE HEXAGONAL','','','ICE111','',NULL,NULL,20,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-107-7143',0x01,0,0.1,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(108,639,1,6,310,8,'VALVULA DE NEUMATICO TR4','','','ICE112','',NULL,NULL,5.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-108-3080',0x01,0,0.5,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(109,616,1,6,309,8,'BAT BOLT HD60-11 12V 53AH-385 G60 L','','','ICE113','',NULL,NULL,224.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-109-5299',0x01,0,183.81,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(110,615,1,6,309,8,'BATERIA ENERJET MT43 12V 430AMP (TX) G36 L','','','ICE114','',NULL,NULL,199.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-110-8842',0x01,0,153.16,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(111,615,1,6,309,8,'BATERIA ENERJET MTF900 12V 355AMP GU1','','','ICE115','',NULL,NULL,167.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-111-1545',0x01,0,128.89,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(112,639,1,6,320,8,'TUERCA CONICA 12X1.25 (CABEZAL)','','','ICE116','',NULL,NULL,10,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-112-1534',0x01,0,5,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(113,639,1,6,321,8,'PERNO TUBULAR 12X1.25 (HEXAGONAL)','','','ICE117','',NULL,NULL,8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-113-8293',0x01,0,4,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(114,639,1,6,321,8,'PERNO HEXAGONAL 12X1.50','','','ICE118','',NULL,NULL,8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-114-9916',0x01,0,4,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(115,639,1,6,320,8,'TUERCA BALA 12X1.50, 12X1.25 BLAK, BLUE','','','ICE119','',NULL,NULL,10.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-115-7171',0x01,0,6,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(116,639,1,6,318,8,'SEGURO DE BATERIA (TAXI)','','','ICE120','',NULL,NULL,18,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-116-5942',0x01,0,9,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(117,640,1,6,309,8,'BATERIA SILVER CAST NS40LSC 12V 540/320 AMPCJNS40','','','ICE121','',NULL,NULL,181.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-117-9730',0x01,0,151.2,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(118,640,1,6,309,8,'BATERIA SILVER CAST 42ISC 12V 600/370 AMP C42','','','ICE122','',NULL,NULL,223.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-118-7758',0x01,0,185.99,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(119,627,1,6,313,8,'BATERIA MOTO OUTDO YB5L-B 12V 5AH (12N5) L','','','ICE123','',NULL,NULL,78.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-119-4873',0x01,0,43.64,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(120,627,1,6,313,8,'BATERIA MOTO OUTDO UTX4L-BS 12V 4AH (YTX4) L','','','ICE124','',NULL,NULL,63.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-120-1750',0x01,0,35.25,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(121,627,1,6,313,8,'BATERIA MOTO OUTDO UTX5L-BS 12V 5AH (YTX5) L','','','ICE125','',NULL,NULL,73.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-121-5057',0x01,0,40.87,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(122,627,1,6,313,8,'BATERIA MOTO OUTDO YB6.5L-BS 12V 6.5AH (12N6.5) L','','','ICE126','',NULL,NULL,86.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-122-4386',0x01,0,48.03,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(123,627,1,6,313,8,'BATERIA MOTO OUTDO UTX7L-BS 12V 7AH (YTX7) L','','','ICE127','',NULL,NULL,97.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-123-9886',0x01,0,53.98,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(124,627,1,6,313,8,'BATERIA MOTO OUTDO NACR7A 12V 7AH (YTX7A) R','','','ICE128','',NULL,NULL,103.9,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-124-3102',0x01,0,57.74,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(125,627,1,6,313,8,'BATERIA MOTO OUTDO YB7L-BS 12V 7AH (12N7) L','','','ICE129','',NULL,NULL,109.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-125-3082',0x01,0,60.83,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(126,627,1,6,313,8,'BATERIA MOTO OUTDO UTX9-BS 12V 9AH (YTX9) R','','','ICE130','',NULL,NULL,129.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-126-4231',0x01,0,71.9,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(127,635,1,6,316,8,'ARO BAJAJ 4.00-8 DEPORTIVO (ROJO, VERDE, AMARILLO, NEGRO,AZUL, BLANCO)','','','ICE131','',NULL,NULL,105,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-127-2328',0x01,0,75,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(128,615,1,6,309,8,'BATERIA ENERJET 13S85 12V 1015AMP G24 L','','','ICE132','',NULL,NULL,361,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-128-6311',0x01,0,277.7,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(129,637,1,6,318,8,'BORNE BOSCH PAR G (- +)','','','ICE133','',NULL,NULL,16.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-129-1477',0x01,0,6.7,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(130,637,1,6,318,8,'BORNE BOSCH UNI G (- +)','','','ICE134','',NULL,NULL,8.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-130-7224',0x01,0,3.35,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(131,621,1,6,309,8,'BATERIA RECORD RP35-40B19 12V 35AH G40','','','ICE135','',NULL,NULL,232.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-131-6187',0x01,0,193.43,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(132,641,1,6,309,8,'BATERIA JJS NS40L 12V 40AH 616 CJNS40','','','ICE136','',NULL,NULL,175.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-132-3277',0x01,0,135,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(133,634,1,6,317,8,'CAMARA MOTO ARL 3.00-18 TR4 (INDIA)','','','ICE137','',NULL,NULL,23.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-133-8476',0x01,0,9.5,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(134,634,1,6,317,8,'CAMARA MOTO ARL 3.00-17 TR4 (INDIA)','','','ICE138','',NULL,NULL,23.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-134-3789',0x01,0,9.5,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(135,616,1,6,309,8,'BATERIA BOLT HD42-13R 12V 73AH G42 L','','','ICE139','',NULL,NULL,273.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-135-6204',0x01,0,216.7,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(136,620,1,6,313,8,'BATERIA MOTO CAPSA YTX9-FA 12V 9AH','','','ICE140','',NULL,NULL,121.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-136-1106',0x01,0,81,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(137,620,1,6,313,8,'BATERIA MOTO CAPSA YTX5L-FA 12V 5AH','','','ICE141','',NULL,NULL,90,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-137-5015',0x01,0,60,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(138,619,1,6,309,8,'BATERIA SOLITE AGM80 12V 80AH CCA800 G48','','','ICE142','',NULL,NULL,979.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-138-5767',0x01,0,699.52,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(139,616,1,6,309,8,'BATERIA BOLT MF105D31L/R 12V 90AH CJ27','','','ICE143','',NULL,NULL,314,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-139-1829',0x01,0,261.7,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(140,642,1,6,310,8,'LLANTA VIKRANT 7.50-16 16PR POS','','','ICE144','',NULL,NULL,549.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-140-5493',0x01,0,460,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(141,620,1,6,312,8,'BATERIA CAPSA 42I900 12V 900/770AMP G42 L','','','ICE145','',NULL,NULL,295.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-141-4004',0x01,0,246,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(142,637,1,6,309,8,'BATERIA BOSCH 40B19 12V 35AH G40 L','','','ICE146','',NULL,NULL,277.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-142-8499',0x01,0,198.33,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(143,637,1,6,309,8,'BATERIA BOSCH 46B24 12V 45AH G60 L','','','ICE147','',NULL,NULL,505.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-143-1801',0x01,0,337,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(144,637,1,6,309,8,'BATERIA BOSCH S5 47D 12V 47AH G36 L','','','ICE148','',NULL,NULL,538.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-144-4086',0x01,0,359,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(145,637,1,6,309,8,'BAT BOSCH 95D31 12V 90AH-730 G27 L','','','ICE151','',NULL,NULL,617.9,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-145-2427',0x01,0,386.16,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(146,627,1,6,313,8,'BATERIA MOTO OUTDO YB9-BS 12V 9AH (12N9) R','','','ICE152','',NULL,NULL,110.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-146-6328',0x01,0,61.4,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(147,620,1,6,313,8,'BATERIA MOTO CAPSA 12N9-4BFA 12V 9AH','','','ICE153','',NULL,NULL,121.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-147-4182',0x01,0,81,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(148,620,1,6,313,8,'BATERIA MOTO CAPSA YTX4L-FA 12V 4AH','','','ICE154','',NULL,NULL,79.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-148-1400',0x01,0,53,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(149,620,1,6,313,8,'BATERIA MOTO CAPSA YTX7A-FA 12V 7AH','','','ICE155','',NULL,NULL,109.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-149-2063',0x01,0,73,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(150,620,1,6,313,8,'BATERIA MOTO CAPSA 12N5-3FA 12V 5AH','','','ICE156','',NULL,NULL,90,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-150-3748',0x01,0,60,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(151,620,1,6,313,8,'BATERIA MOTO CAPSA YTX7L-FA 12V 7AH','','','ICE157','',NULL,NULL,102,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-151-4174',0x01,0,68,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(152,640,1,6,309,8,'BATERIA SILVER CAST 24RSC 12V 700/370 AMP CJ24','','','ICE158','',NULL,NULL,222.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-152-1072',0x01,0,185.2,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(153,621,1,6,309,8,'BATERIA RECORD RHK 80 12V 715AMP G35 L','','','ICE159','',NULL,NULL,336.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-153-3091',0x01,0,280.58,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(154,642,1,6,310,8,'LLANTA VIKRANT 7.00-15 POS 114/109M STAR LUG 12PR','','','ICE160','',NULL,NULL,0,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-154-7401',0x01,0,416,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(155,643,1,6,310,8,'LLANTA GENERAL 18570R13 86T ALTIMAX RT','','','ICE161','',NULL,NULL,126,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-155-7850',0x01,0,105,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(156,637,1,6,309,8,'BATERIA BOSCH S5 95DM 12V 95AH G49 L','','','ICE163','',NULL,NULL,802.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-156-1234',0x01,0,535,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(157,644,1,6,309,8,'BATERIA ROCKET 40B19 12V 35AH G40 L','','','ICE164','',NULL,NULL,209.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-157-4057',0x01,0,155.29,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(158,637,1,6,309,8,'BATERIA BOSCH S4 55D 12V 55AH G42 L','','','ICE165','',NULL,NULL,438.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-158-4864',0x01,0,292.52,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(159,645,1,6,309,8,'BATERIA BESTE NS60L/R 12V 45AH CJNS60','','','ICE166','',NULL,NULL,206.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-159-4813',0x01,0,178,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(160,645,1,6,309,8,'BATERIA BESTE NX110-5L 12V 70AH CJ24','','','ICE167','',NULL,NULL,318,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-160-4098',0x01,0,265,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(161,645,1,6,309,8,'BATERIA BESTE NX120L/R-7 12V 90AH CJ27','','','ICE168','',NULL,NULL,341.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-161-1028',0x01,0,284.388,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(162,645,1,6,309,8,'BATERIA BESTE 31S PERN 12V 100AH CJ31','','','ICE169','',NULL,NULL,420,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-162-5584',0x01,0,350,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(163,645,1,6,309,8,'BATERIA BESTE 40B19L 12V 35AH NJNS40 L','','','ICE170','',NULL,NULL,166.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-163-8396',0x01,0,139,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(164,645,1,6,309,8,'BATERIA BESTE 55D23L 12V 60AH CJ35','','','ICE171','',NULL,NULL,260.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-164-2168',0x01,0,217,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(165,645,1,6,309,8,'BATERIA BESTE 55530 12V 55AH CJ42 L','','','ICE172','',NULL,NULL,180.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-165-9364',0x01,0,150.21,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(166,646,1,6,310,8,'LLANTA HABILEAD 215/70R15C 109/107T RS01 TL','','','ICE173','',NULL,NULL,247.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-166-6325',0x01,0,206,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(167,620,1,6,311,8,'BATERIA CAPSA U1R300 12V MTX CJU1','','','ICE174','',NULL,NULL,247.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-167-8739',0x01,0,206,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(168,637,1,6,309,8,'BAT BOSCH 575.065 12V 75AH-650 G48 L','','','ICE175','',NULL,NULL,538.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-168-3255',0x01,0,347.45,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(169,615,1,6,309,8,'BATERIA ENERJET 13W75 12V 939AMP G42 L','','','ICE176','',NULL,NULL,340.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-169-7717',0x01,0,261.67,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(170,615,1,6,309,8,'BATERIA ENERJET MTF-7P 12V 270AMP GU1 L','','','ICE177','',NULL,NULL,164.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-170-3881',0x01,0,126.71,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(171,626,1,6,313,8,'BATERIA YUASA NP1.2 12V 1.2AH','','','ICE178','',NULL,NULL,98,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-171-8303',0x01,0,49,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(172,647,1,6,317,8,'CÁMARA NEXEN 19.5L-24 TE','','','ICE179','',NULL,NULL,336,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-172-2565',0x01,0,224,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(173,620,1,6,312,8,'BATERIA CAPSA 27R1000 12V 1000/640 G27 L','','','ICE180','',NULL,NULL,381.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-173-2540',0x01,0,317.73,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(174,625,1,6,313,8,'BATERIA MOTO OXFORD YTX5L-BS 12V 5AH','','','ICE181','',NULL,NULL,107.9,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-174-6631',0x01,0,63.5,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(175,625,1,6,313,8,'BATERIA MOTO OXFORD YTX7L-BS 12V 7AH','','','ICE182','',NULL,NULL,133.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-175-3100',0x01,0,78.5,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(176,648,1,6,310,8,'LLANTA AUFINE 12.00R20 TRAC R301','','','ICE183','',NULL,NULL,1332,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-176-2832',0x01,0,1110,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(177,649,1,6,322,8,'BTU-G36 BAT. USADA (MT30/MT38) MOTOTAXI /SGNDA','','','ICE184','',NULL,NULL,24,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-177-8831',0x01,0,20,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(178,620,1,6,312,8,'BATERIA CAPSA 48I1000 12V 1000/600 AMP G48 L','','','ICE185','',NULL,NULL,380.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-178-1164',0x01,0,304.538,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(179,649,1,6,322,8,'BTU-G40 BAT. USADA (9D/11D) TICO /SGNDA','','','ICE186','',NULL,NULL,24,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-179-6955',0x01,0,20,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(180,649,1,6,322,8,'BTU-G60 BAT. USADA (9T,13T) TOYOTA /SGNDA','','','ICE187','',NULL,NULL,24,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-180-5053',0x01,0,20,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(181,649,1,6,322,8,'BTU-G42 BAT. USADA (9W/13W) CHATA /SGNDA','','','ICE188','',NULL,NULL,48,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-181-8956',0x01,0,40,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(182,649,1,6,322,8,'BTU-G27 BAT. USADA (11M/15M) MEDIANA /SGNDA','','','ICE189','',NULL,NULL,48,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-182-8710',0x01,0,40,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(183,649,1,6,322,8,'BTU-G24 BAT. USADA (9S,13S) STANDAR /SGNDA','','','ICE190','',NULL,NULL,48,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-183-6418',0x01,0,48,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(184,649,1,6,322,8,'BTU-G31 BAT. USADA (17P,17W) 17PLAC /SGNDA','','','ICE191','',NULL,NULL,54,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-184-3720',0x01,0,45,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(185,649,1,6,322,8,'BTU-G120 BAT. USADA (19P) LAPIZ /SGNDA','','','ICE192','',NULL,NULL,84,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-185-3406',0x01,0,70,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(186,649,1,6,322,8,'BTU-G4D BAT. USADA (21P,25P) GRANDE /SGNDA','','','ICE193','',NULL,NULL,96,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-186-9286',0x01,0,80,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(187,649,1,6,322,8,'BTU-G8D BAT. USADA (27P,33P) EXTRA GRAND /SGNDA','','','ICE194','',NULL,NULL,120,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-187-1890',0x01,0,100,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(188,649,1,6,322,8,'BTU-GU1 BAT. USADA (MTF,U1R) MOTO /SGNDA','','','ICE195','',NULL,NULL,12,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-188-1767',0x01,0,10,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(189,649,1,6,322,8,'BTU-BAT. USADA (MOTO LINEAL) /SGNDA','','','ICE196','',NULL,NULL,0.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-189-8815',0x01,0,0.1,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(190,647,1,6,317,8,'CAMARA NEXEN 12-16.5 SKS','','','ICE197','',NULL,NULL,111,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-190-5812',0x01,0,75,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(191,637,1,6,309,8,'BATERIA BOSCH 100D23 12V 70AH G35 AMS L','','','ICE198','',NULL,NULL,480.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-191-2474',0x01,0,320.378,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(192,650,1,6,310,8,'LLANTA ALLIANCE 19.5L-24 INDUSTRIAL 533 151 AB 12PR TL','','','ICE199','',NULL,NULL,2496,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-192-3909',0x01,0,1920,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(193,651,1,6,317,8,'CAMARA MASTER 12.5/80-18','','','ICE200','',NULL,NULL,156,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-193-8725',0x01,0,104,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(194,624,1,6,313,8,'BATERIA MOTO ZERO 12N7A-3A 12V 7AH L','','','ICE201','',NULL,NULL,83.9,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-194-1633',0x01,0,49.36,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(195,624,1,6,313,8,'BATERIA MOTO ZERO YTX14L-BS 12V 14AH','','','ICE202','',NULL,NULL,151.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-195-9107',0x01,0,89.18,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(196,624,1,6,313,8,'BBATERIA MOTO ZERO YTX20L-BS 12V 20AH','','','ICE203','',NULL,NULL,210.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-196-3745',0x01,0,3,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(197,626,1,6,313,8,'BATERIA MOTO YUASA NP10-6 6V 10AH','','','ICE204','',NULL,NULL,96,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-197-4214',0x01,0,60,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(198,626,1,6,313,8,'BATERIA MOTO YUASA YTX5L-BS 12V 4AH','','','ICE205','',NULL,NULL,198.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-198-1718',0x01,0,123.93,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(199,619,1,6,309,8,'BATERIA SOLITE CMF 59042 12V 90AH G65 L','','','ICE206','',NULL,NULL,586.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-199-5388',0x01,0,418.95,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(200,645,1,6,309,8,'BATERIA BESTE N150 12V 150AH G4D L','','','ICE207','',NULL,NULL,553.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-200-1811',0x01,0,461,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(201,637,1,6,309,8,'BATERIA BOSCH 105D26 12V 85AH G24 AMS L','','','ICE208','',NULL,NULL,563.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-201-4371',0x01,0,375.49,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(202,637,1,6,309,8,'BATERIA BOSCH 580035 12V 80AH G65 L','','','ICE209','',NULL,NULL,983.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-202-3868',0x01,0,655.4,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(203,652,1,6,309,8,'BATERIA MOURA ME50JR 12V 50AH G60 /ME380J L','','','ICE210','',NULL,NULL,292.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-203-1433',0x01,0,208.76,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(204,626,1,6,313,8,'BATERÍA MOTO YUASA TTZ10S 12V 9AH','','','ICE211','',NULL,NULL,412.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-204-8390',0x01,0,275,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(205,620,1,6,311,8,'BATERÍA CAPSA 36I650 12V 650/300AMP G36 L','','','ICE212','',NULL,NULL,163.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-205-8705',0x01,0,120.86,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(206,620,1,6,312,8,'BATERÍA CAPSA 65 1100 12V 1000/700AMP G65 R','','','ICE213','',NULL,NULL,513.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-206-5853',0x01,0,380.13,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(207,653,1,6,310,8,'LLANTA TECHSHIELD 12.00R20 POS TM902 TRACCION TCF P. OSO','','','ICE214','',NULL,NULL,1050,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-207-1128',0x01,0,875,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(208,654,1,6,310,8,'LLANTA PLUSWAY 12.5/80-18 R4 12PR','','','ICE215','',NULL,NULL,760.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-208-8841',0x01,0,585,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(209,616,1,6,309,8,'BATERIA BOLT MF56077 12V 60AH CJ42','','','ICE216','',NULL,NULL,246,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-209-5335',0x01,0,205,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(210,622,1,6,309,8,'BAT ETNA S-1227 12V 183AH-1190 G8D R','','','ICE217','',NULL,NULL,878.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-210-5714',0x01,0,675.57,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(211,633,1,6,317,8,'CAMARA S&M 3.00-18 TR4C NATURAL','','','ICE218','',NULL,NULL,14,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-211-2079',0x01,0,7,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(212,619,1,6,309,8,'BATERIA SOLITE 95D23 12V 68AH G35 L','','','ICE219','',NULL,NULL,503.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-212-4305',0x01,0,335.61,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(213,655,1,6,310,8,'LLANTA SUNFULL 12.00R20 TRAC ST986 MINERA','','','ICE220','',NULL,NULL,1164,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-213-8776',0x01,0,970,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(214,620,1,6,312,8,'BATERIA CAPSA NS60LS700 12V 700/410 G60 L','','','ICE221','',NULL,NULL,290.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-214-7437',0x01,0,232.64,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(215,620,1,6,312,8,'BATERIA CAPSA 42I800 12V 800/520 AMP G42 L','','','ICE222','',NULL,NULL,302.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-215-1058',0x01,0,241.762,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(216,620,1,6,312,8,'BATERIA CAPSA 24R950 12V 950/580 AMP G24 L','','','ICE223','',NULL,NULL,315.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-216-2822',0x01,0,252.61,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(217,620,1,6,312,8,'BATERIA CAPSA 30H1600 (TE) 12V 1600/800 G31 R','','','ICE224','',NULL,NULL,490,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-217-2609',0x01,0,391.976,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(218,620,1,6,312,8,'BATERIA CAPSA 8DI2600 12V 2600/1300 G8D R','','','ICE225','',NULL,NULL,821.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-218-6481',0x01,0,657.411,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(219,620,1,6,312,8,'BATERIA CAPSA 36I750 12V 750/420 AMP CJ36','','','ICE226','',NULL,NULL,230.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-219-9483',0x01,0,192.2,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(220,656,1,6,310,8,'LLANTA ROADX 185/70R14 RXMOTION H 11 88T','','','ICE227','',NULL,NULL,157.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-220-4701',0x01,0,131,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(221,657,1,6,310,8,'LLANTA DESTONE 205/60R15 91H WR9001','','','ICE228','',NULL,NULL,198,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-221-3875',0x01,0,165,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(222,642,1,6,310,8,'LLANTA VIKRANT 7.50-16 DEL 16PR 124/120 TRACK KING','','','ICE229','',NULL,NULL,534,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-222-8910',0x01,0,445,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(223,658,1,6,323,8,'BATERIA OXFORD XF4-6 6V','','','ICE230','',NULL,NULL,0.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-223-2318',0x01,0,0.1,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(224,625,1,6,313,8,'BATERIA MOTO OXFORD XF4-6 6V 4AH','','','ICE231','',NULL,NULL,38,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-224-4760',0x01,0,19,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(225,659,1,6,310,8,'LLANTA OSC TIRES 19.5L-24 04510 R4 16PR TL','','','ICE232','',NULL,NULL,2013.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-225-2075',0x01,0,1678,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(226,660,1,6,309,8,'BATERIA MAC 24R1300 MG 12V 1300/950 AMP CJ24','','','ICE233','',NULL,NULL,435,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-226-7233',0x01,0,310.7,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(227,660,1,6,309,8,'BATERIA MAC 42I1100 MG 12V 1100/770 AMP CJ42','','','ICE234','',NULL,NULL,413,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-227-5622',0x01,0,295,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(228,639,1,6,318,8,'SEGURO DE BATERIA (AUTO M)','','','ICE235','',NULL,NULL,18,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-228-1871',0x01,0,9,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(229,615,1,6,309,8,'BATERIA ENERJET 13T68 12V 860AMP G60 L','','','ICE236','',NULL,NULL,336.9,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-229-1581',0x01,0,259.15,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(230,615,1,6,309,8,'BATERIA ENERJET 27P190 12V 1680AMP G8D R','','','ICE237','',NULL,NULL,853.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-230-2026',0x01,0,656.38,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(231,615,1,6,309,8,'BATERIA ENERJET 23P159 12V 1573AMP G4D L','','','ICE238','',NULL,NULL,721,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-231-9757',0x01,0,554.59,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(232,615,1,6,309,8,'BBATERIA ENERJET 15M99 12V 1162AMP G27 L','','','ICE239','',NULL,NULL,450,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-232-3705',0x01,0,3,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(233,615,1,6,309,8,'BBATERIA ENERJET 13S85 12V 1015AMP G24 L','','','ICE240','',NULL,NULL,361,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-233-5676',0x01,0,3,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(234,620,1,6,312,8,'BBATERIA CAPSA 42I900 12V 900/770AMP G42 L','','','ICE241','',NULL,NULL,294.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-234-8680',0x01,0,3,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(235,633,1,6,317,8,'CAMARA S&M 3.00-17 TR4 PREMIUN','','','ICE242','',NULL,NULL,17,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-235-8490',0x01,0,8.5,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(236,649,1,6,322,8,'BTU-G48 BAT. USADA (MB90, MBI) MEDIANA /SGNDA','','','ICE243','',NULL,NULL,80,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-236-4587',0x01,0,40,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(237,649,1,6,322,8,'BTU-G35 BAT. USADA (RHK) STANDAR /SGNDA','','','ICE244','',NULL,NULL,80,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-237-7836',0x01,0,40,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(238,661,1,6,324,8,'S-G60 BAT. USADA (9T,13T) SEMI/','','','ICE245','',NULL,NULL,90,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-238-9101',0x01,0,30,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(239,661,1,6,324,8,'S-G42 BAT. USADA (9W/13W) SEMI/','','','ICE246','',NULL,NULL,150,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-239-7554',0x01,0,60,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(240,649,1,6,324,8,'S-G27 BAT. USADA (11M/15M) SEMI/','','','ICE247','',NULL,NULL,150,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-240-2640',0x01,0,60,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(241,661,1,6,324,8,'S-G24 BAT. USADA (9S,13S) SEMI/','','','ICE248','',NULL,NULL,150,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-241-2379',0x01,0,60,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(242,661,1,6,324,8,'S-G31 BAT. USADA (17P,17W) SEMI/','','','ICE249','',NULL,NULL,189,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-242-9861',0x01,0,70,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(243,661,1,6,324,8,'S-G120 BAT. USADA (19P) SEMI/','','','ICE250','',NULL,NULL,250,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-243-4434',0x01,0,100,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(244,661,1,6,324,8,'S-G4D BAT. USADA (21P,25P) SEMI/','','','ICE251','',NULL,NULL,286,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-244-4925',0x01,0,130,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(245,649,1,6,324,8,'S-G8D BAT. USADA (27P,33P) SEMI/','','','ICE252','',NULL,NULL,286,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-245-7614',0x01,0,130,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(246,661,1,6,324,8,'S-GML BAT. USADA (M. LINEAL) SEMI/','','','ICE253','',NULL,NULL,20,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-246-6042',0x01,0,5,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(247,661,1,6,324,8,'S-G48 BAT. USADA (MB90, MBI) SEMI/','','','ICE254','',NULL,NULL,150,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-247-7042',0x01,0,60,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(248,661,1,6,324,8,'S-G35 BAT. USADA (RHK80) SEMI/','','','ICE255','',NULL,NULL,150,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-248-7142',0x01,0,60,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(249,662,1,6,310,8,'LLANTA WIDEWAY 185/70R13 86T','','','ICE257','',NULL,NULL,134.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-249-6006',0x01,0,112,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(250,658,1,6,323,8,'(AS','','','ICE258','',NULL,NULL,1.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-250-3491',0x01,0,1,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(251,625,1,6,325,8,'BATERIA OXFORD YTZ10S 12V 8.6AH','','','ICE259','',NULL,NULL,175.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-251-9693',0x01,0,117.23,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(252,660,1,6,326,8,'BBATERIA MAC 48IST1300 MG 12V 1300/925 AMP CJ48','','','ICE260','',NULL,NULL,423,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-252-2866',0x01,0,3,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(253,616,1,6,309,8,'BATERIA BOLT HD27-13 12V 85AH G27 L','','','ICE261','',NULL,NULL,316.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-253-3219',0x01,0,264,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(254,616,1,6,309,8,'BATERIA BOLT 55B24L 12V 46AH CJNS60','','','ICE262','',NULL,NULL,248.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-254-7401',0x01,0,207.26,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(255,627,1,6,313,8,'BATERIA MOTO OUTDO YB7BL-BS 12V 7AH (12N7A) L','','','ICE263','',NULL,NULL,95.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-255-1752',0x01,0,52.88,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(256,663,1,6,310,8,'LLANTA ZMAX 225/55R18 HT GALLOPRO 98H','','','ICE264','',NULL,NULL,260.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-256-7561',0x01,0,217,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(257,664,1,6,311,8,'BATERIA SFX 6QW18 12V 18AH CJU1R','','','ICE265','',NULL,NULL,111.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-257-6223',0x01,0,86,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(258,621,1,6,309,8,'BATERIA RECORD RW 65 12V 690AMP G42 L','','','ICE266','',NULL,NULL,290.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-258-9213',0x01,0,241.98,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(259,615,1,6,312,8,'BATERIA ENERJET MB90L 12V 1070AMP G48 LMS L','','','ICE267','',NULL,NULL,400.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-259-4301',0x01,0,307.8,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(260,620,1,6,312,8,'BATERIA CAPSA 24R900 12V 900/540 AMP G24 L','','','ICE268','',NULL,NULL,312,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-260-6332',0x01,0,249.609,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(261,665,1,6,310,8,'LLANTA MAGNA 19.5L-24 MB410 12PR','','','ICE269','',NULL,NULL,2616,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-261-8202',0x01,0,2180,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(262,666,1,6,310,8,'LLANTA CHAOYANG 145/70R12','','','ICE270','',NULL,NULL,93.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-262-3927',0x01,0,78,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(263,661,1,6,324,8,'S-G36 BAT. USADA (MT30/MT38) SEMI/','','','ICE271','',NULL,NULL,90,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-263-5606',0x01,0,30,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(264,661,1,6,324,8,'S-G40 BAT. USADA (9D/11D) SEMI/','','','ICE272','',NULL,NULL,90,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-264-1915',0x01,0,30,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(265,667,1,6,310,8,'LLANTA MAXXIS 205/60R14','','','ICE273','',NULL,NULL,252,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-265-7542',0x01,0,210,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(266,620,1,6,311,8,'BATERIA CAPSA U1R500 12V 500/360AMP GU1 L','','','ICE274','',NULL,NULL,143.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-266-6354',0x01,0,119.573,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(267,668,1,6,310,8,'LLANTA HIFLY 11.00R20 TRAC 152/149K HH317 18PR','','','ICE275','',NULL,NULL,1340.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-267-4762',0x01,0,1117.2,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(268,616,1,6,309,8,'BATERIA BOLT HD27-15 12V 97AH G27 L','','','ICE276','',NULL,NULL,328.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-268-1494',0x01,0,273.657,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(269,621,1,6,309,8,'BATERIA RECORD RMB 90 12V 790AMP G48 L','','','ICE277','',NULL,NULL,371.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-269-5097',0x01,0,309.41,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(270,669,1,6,310,8,'LLANTA SUNWIDE 205/60R15','','','ICE278','',NULL,NULL,167.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-270-3505',0x01,0,139.7,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(271,615,1,6,309,8,'BATERIA ENERJET S78L 12V 860AMP G24 LMS L','','','ICE279','',NULL,NULL,352.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-271-4781',0x01,0,270.91,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(272,615,1,6,309,8,'BATERIA ENERJET W60L 12V 730AMP G42 LMS L','','','ICE280','',NULL,NULL,332,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-272-5718',0x01,0,255.39,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(273,615,1,6,309,8,'BATERIA ENERJET DA80R 12V 900AMP G35 LMS L','','','ICE281','',NULL,NULL,409.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-273-9291',0x01,0,315.21,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(274,660,1,6,326,8,'BBATERIA MAC 27R1400 MG 12V 1400/980 AMP CJ27','','','ICE282','',NULL,NULL,453,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-274-9876',0x01,0,3,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(275,670,1,6,310,8,'LLANTA HANKOOK 155/70R12','','','ICE283','',NULL,NULL,153.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-275-6678',0x01,0,128,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(276,670,1,6,310,8,'LLANTA HANKOOK 245/75R16 MT RT05 DYNAPRO','','','ICE284','',NULL,NULL,774,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-276-7303',0x01,0,645,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(277,615,1,6,309,8,'BATERIA ENERJET MT38 12V 447AMP G36 L','','','ICE285','',NULL,NULL,186.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-277-4332',0x01,0,143.56,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(278,671,1,6,309,8,'BATERÍA TOPOWER 70D-PE 12V 70AH CJ48','','','ICE286','',NULL,NULL,350,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-278-9167',0x01,0,250,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(279,620,1,6,312,8,'BATERIA CAPSA 4D1800 12V 1800/940 G4D L','','','ICE287','',NULL,NULL,656,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-279-2414',0x01,0,524.8,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(280,615,1,6,309,8,'BATERIA ENERJET M95L 12V 1200AMP G27 LMS L','','','ICE288','',NULL,NULL,456.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-280-3184',0x01,0,351.02,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(281,672,1,6,310,8,'LLANTA KUSTONE 165/60R14 RADIAL P0925H','','','ICE289','',NULL,NULL,109.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-281-9570',0x01,0,91,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(282,646,1,6,310,8,'LLANTA MIRAGE 185/70R13 86T L166 MIRAGE','','','ICE290','',NULL,NULL,117.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-282-1412',0x01,0,98,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(283,666,1,6,309,8,'LLANTA MOTO LINEAL CHAOYANG 2.75-17 H-616','','','ICE291','',NULL,NULL,71.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-283-9691',0x01,0,42,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(284,666,1,6,310,8,'LLANTA MOTO LINEAL CHAOYANG 3.00-17 H-882','','','ICE292','',NULL,NULL,97.9,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-284-1718',0x01,0,57.6,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(285,666,1,6,310,8,'LLANTA MOTO LINEAL CHAOYANG 90/90-17 H-626','','','ICE293','',NULL,NULL,112.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-285-5165',0x01,0,66,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(286,673,1,6,310,8,'LLANTA JSL 4.00-8 TT 15M SET/C','','','ICE294','',NULL,NULL,74.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-286-5221',0x01,0,57,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(287,672,1,6,310,8,'LLANTA KUSTONE 195/60R15 QUIET Q7 88H TL','','','ICE295','',NULL,NULL,164.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-287-8680',0x01,0,137,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(288,633,1,6,317,8,'CAMARA S&M 3.00-17 TR4 NATURAL','','','ICE296','',NULL,NULL,17.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-288-8473',0x01,0,7,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(289,663,1,6,310,8,'LLANTA ZMAX 185R14C VANMEJOR 102/100R C30','','','ICE297','',NULL,NULL,180,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-289-9245',0x01,0,150,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(290,674,1,6,318,8,'COMPRENSORA PORTATIL 12V 100PSI (PLOMO)','','','ICE298','',NULL,NULL,105,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-290-9226',0x01,0,70,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(291,664,1,6,313,8,'BATERIA SFX YTZ10 12V 10AH AGM','','','ICE299','',NULL,NULL,127.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-291-7260',0x01,0,85.2,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(292,664,1,6,313,8,'BATERIA SFX YTX7A-BS 12V 7AH GEL','','','ICE300','',NULL,NULL,67.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-292-9416',0x01,0,45.2,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(293,615,1,6,309,8,'BATERIA ENERJET D40 12V 550AMP G40 LMS L','','','ICE301','',NULL,NULL,283.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-293-5361',0x01,0,218.09,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(294,621,1,6,309,8,'BATERIA RECORD N 100 12V 1200AMP G100 R','','','ICE303','',NULL,NULL,489,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-294-9851',0x01,0,407.47,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(295,615,1,6,309,8,'BATERIA ENERJET 11D56P 12V 650AMP G40 L','','','ICE304','',NULL,NULL,287.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-295-6087',0x01,0,221.42,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(296,675,1,6,310,8,'LLANTA ROYAL BLACK 265/65R17 AT 110T','','','ICE305','',NULL,NULL,362.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-296-1284',0x01,0,302,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(297,642,1,6,310,8,'LLANTA VIKRANT 7.00-16 DEL TRACK KING 142','','','ICE306','',NULL,NULL,616.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-297-9680',0x01,0,514,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(298,626,1,6,309,8,'BATERIA YUASA NP4-6 12V 4AH','','','ICE307','',NULL,NULL,52.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-298-6991',0x01,0,32.8,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(299,619,1,6,309,8,'BATERIA SOLITE CMF57412 12V 74AH G48 L','','','ICE308','',NULL,NULL,468.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-299-1379',0x01,0,334.89,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(300,619,1,6,309,8,'BATERIA SOLITE AGM70 12V 70AH CJ48 AGM','','','ICE309','',NULL,NULL,893.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-300-5101',0x01,0,638.1,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(301,664,1,6,310,8,'LLANTA SFX 5.00-12','','','ICE310','',NULL,NULL,146.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-301-9774',0x01,0,136,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(302,676,1,6,309,8,'BAT SUNTEC 42B19 12V 40AH-340 G40 L','','','ICE311','',NULL,NULL,152.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-302-1503',0x01,0,126.9,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(303,615,1,6,309,8,'BATERIA ENERJET T48LS 12V 660A G60 LMS L','','','ICE312','',NULL,NULL,309.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-303-7060',0x01,0,258,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(304,633,1,6,310,8,'LLANTA S&M 4.00-8 WSX096 8PR','','','ICE313','',NULL,NULL,75.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-304-8517',0x01,0,58,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(305,637,1,6,309,8,'BATERIA BOSCH 545.042 12V 45AH G36 AMS L','','','ICE314','',NULL,NULL,378.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-305-1190',0x01,0,252.2,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(306,676,1,6,309,8,'BAT SUNTEC 55B24 12V 55AH-430 G60 L','','','ICE315','',NULL,NULL,212.9,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-306-8381',0x01,0,157.7,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(307,626,1,6,325,8,'BATERIA YUASA YTX14-BS 12V 12AH','','','ICE316','',NULL,NULL,588.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-307-5220',0x01,0,420.14,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(308,640,1,6,309,8,'BATERIA SILVER CAST NS60L 12V 650/320AMP CJNS60','','','ICE317','',NULL,NULL,223.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-308-1713',0x01,0,186.47,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(309,615,1,6,327,8,'BATERÍA ENERJET 58010L4 12V 85AH 780CCA G65 EFB L','','','ICE318','',NULL,NULL,653.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-309-5953',0x01,0,467,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(310,617,1,6,310,8,'LLANTA GOOD YEAR 23.5-25 L-3/E-3/G-3 HRL E/L-3A 20PR','','','ICE319','',NULL,NULL,9477.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-310-7227',0x01,0,8241.15,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(311,677,1,6,309,8,'BATERÍA KINGPOWER 36B20P 12V 36AH 330 G40 L','','','ICE320','',NULL,NULL,167.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-311-5748',0x01,0,130,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(312,677,1,6,309,8,'BATERÍA KINGPOWER 54434 12V 44AH 480 G36 L','','','ICE321','',NULL,NULL,194.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-312-9306',0x01,0,155.48,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(313,677,1,6,309,8,'BATERÍA KINGPOWER 56090 12V 60AH 580 G42 L','','','ICE322','',NULL,NULL,222.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-313-5362',0x01,0,177.94,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(314,677,1,6,309,8,'BATERÍA KINGPOWER 55D23 12V 60AH 550 G35 L','','','ICE323','',NULL,NULL,230.9,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-314-1632',0x01,0,192.43,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(315,677,1,6,309,8,'BATERÍA KINGPOWER 56821 12V 68AH 680 G48 L','','','ICE324','',NULL,NULL,266.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-315-1238',0x01,0,213.26,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(316,677,1,6,309,8,'BATERÍA KINGPOWER 105D31 12V 90AH 750 G27 L','','','ICE325','',NULL,NULL,331.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-316-7132',0x01,0,264.98,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(317,624,1,6,313,8,'BATERIA MOTO ZERO 12N7-3B 2V 7AH R','','','ICE326','',NULL,NULL,97.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-317-6941',0x01,0,57.1,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(318,678,1,6,310,8,'LLANTA TOPRUNNER 23.5-25L QH812 TCF 3/L 20PR','','','ICE327','',NULL,NULL,4080,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-318-9986',0x01,0,3400,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(319,663,1,6,310,8,'LLANTA ZMAX 185/70R14','','','ICE328','',NULL,NULL,136.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-319-3094',0x01,0,114,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(320,679,1,6,310,8,'LLANTA HAIDA 185/65R15','','','ICE329','',NULL,NULL,150,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-320-1565',0x01,0,125,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(321,645,1,6,309,8,'BATERIA BESTE N200 12V 200AH CJ8D','','','ICE330','',NULL,NULL,594.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-321-8657',0x01,0,495.59,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(322,644,1,6,309,8,'BATERIA ROCKET NX110 12V 70AH G24 L','','','ICE331','',NULL,NULL,358.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-322-2701',0x01,0,265.53,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(323,644,1,6,309,8,'BATERIA ROCKET N150 12V 150AH G4D L','','','ICE332','',NULL,NULL,650.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-323-5591',0x01,0,481.79,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(324,644,1,6,309,8,'BATERIA ROCKET 60044 12V 100AH G100 L','','','ICE333','',NULL,NULL,473.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-324-6175',0x01,0,338,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(325,677,1,6,309,8,'BATERIA KINGPOWER 46B24S 12V 46AH 400 G60 L','','','ICE334','',NULL,NULL,186.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-325-1671',0x01,0,148.95,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(326,625,1,6,309,8,'BSTERIA OXFORD 6-DZM20 12V 20AH','','','ICE335','',NULL,NULL,223.9,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-326-1216',0x01,0,149.24,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(327,622,1,6,309,8,'BAT ETNA V-13 12V 87AH-608 G24 R','','','ICE336','',NULL,NULL,385.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-327-1550',0x01,0,285.49,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(328,677,1,6,309,8,'BATERIA KINGPOWER 31S-750 (NO) 12V 100AH G31 R','','','ICE337','',NULL,NULL,363.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-328-9750',0x01,0,302.68,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(329,676,1,6,309,8,'BAT SUNTEC 56318 12V 63AH-540 G48 L','','','ICE338','',NULL,NULL,293,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-329-9984',0x01,0,209.3,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(330,641,1,6,309,8,'BATERIA JJS NS60 12V 636AMP CJNS60','','','ICE339','',NULL,NULL,186,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-330-5097',0x01,0,155,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(331,641,1,6,309,8,'BATERIA JJS 42J 12V 628AMP CJ42','','','ICE340','',NULL,NULL,192,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-331-8801',0x01,0,160,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(332,680,1,6,309,8,'BATERUA ECONOBAT W-42 12V 50AH CJ42','','','ICE341','',NULL,NULL,198,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-332-7456',0x01,0,165,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(333,681,1,6,310,8,'LLANTA COMFORSER 205/55R16 HF805 94W XL','','','ICE342','',NULL,NULL,168,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-333-3493',0x01,0,125,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(334,620,1,6,312,8,'BATERIA CAPSA 27R1150 12V 1150/710 G27 L','','','ICE343','',NULL,NULL,407.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-334-2332',0x01,0,325.837,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(335,676,1,6,309,8,'BAT SUNTEC 55457 12V 60AH-480 G42 L','','','ICE344','',NULL,NULL,217.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-335-9105',0x01,0,167,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(336,637,1,6,309,8,'BAT BOSCH 560.064 12V 60AH-640 G42 L','','','ICE345','',NULL,NULL,490.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-336-8442',0x01,0,350.45,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(337,637,1,6,309,8,'BATERIA BOSCH 66HP S4 70D 12V 70AH G48 L','','','ICE346','',NULL,NULL,413,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-337-7793',0x01,0,275.35,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(338,619,1,6,309,8,'BATERIA SOLITE CMF56219 12V 62AH G42 L','','','ICE347','',NULL,NULL,380.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-338-1993',0x01,0,271.84,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(339,625,1,6,309,8,'BATERIA OXFORD 6-DZM-14 12V 14AH','','','ICE348','',NULL,NULL,120.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-339-2389',0x01,0,100.56,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(340,682,1,6,310,8,'LLANTA VTOVIC 4.00-8 CM-013 8PR DEL BAJAJ','','','ICE349','',NULL,NULL,70,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-340-5505',0x01,0,56,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(341,682,1,6,310,8,'LLANTA VTOVIC 4.00-8 CM-007 8PR MIX BAJAJ','','','ICE350','',NULL,NULL,58.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-341-9790',0x01,0,47,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(342,683,1,6,317,8,'CAMARA VTV 4.00-8 PARA BAJAJ TR87C NATURAL','','','ICE351','',NULL,NULL,12.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-342-9518',0x01,0,6.2,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(343,615,1,6,309,8,'BATERIA ENERJET 11T56 12V 680AMP G60 L','','','ICE352','',NULL,NULL,317,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-343-5440',0x01,0,243.88,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(344,676,1,6,309,8,'BAT SUNTEC 80D26 12V 70AH-600 G24 L','','','ICE353','',NULL,NULL,282.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-344-1761',0x01,0,226.2,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(345,676,1,6,309,8,'BAT SUNTEC 95D31 12V 80AH-670 G27 L','','','ICE354','',NULL,NULL,320.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-345-7302',0x01,0,256.2,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(346,615,1,6,309,8,'BATERÍA ENERJET 11S71 12V 858 AMP G24 L','','','ICE355','',NULL,NULL,328.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-346-3812',0x01,0,252.9,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(347,662,1,6,310,8,'LLANTA WIDEWAY 215/70R16 AT 100/97R WEYONE','','','ICE356','',NULL,NULL,303.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-347-6108',0x01,0,253,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(348,621,1,6,309,8,'BATERIA RECORD RT 158 12V 1449AMP G4D L','','','ICE357','',NULL,NULL,658.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-348-9003',0x01,0,549.02,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(349,615,1,6,327,8,'BATERIA ENERJET 59510L5 12V 100AH CCA925 G100 EFB L','','','ICE358','',NULL,NULL,741.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-349-4598',0x01,0,529.63,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(350,615,1,6,327,8,'BATERIA ENERJET 57010L3 12V 75AH 600CCA G48 EFB L','','','ICE359','',NULL,NULL,616.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-350-3608',0x01,0,440.05,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(351,615,1,6,327,8,'BATERIA ENERJET Q85-D23L 12V 70AH 690CCA G35 EFB L','','','ICE360','',NULL,NULL,525.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-351-8897',0x01,0,375.53,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(352,684,1,6,310,8,'LLANTA MAXZEZ 12R22.5 POS','','','ICE361','',NULL,NULL,887.9,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-352-9053',0x01,0,739.95,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(353,674,1,6,318,8,'COMPRENSORA PORTATIL 12V 100PSI (ROJO)','','','ICE362','',NULL,NULL,127.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-353-8979',0x01,0,85,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(354,637,1,6,309,8,'BATERIA BOSCH 75B24LS 12V 55AH G60 AMS L','','','ICE363','',NULL,NULL,416.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-354-5299',0x01,0,277.47,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(355,644,1,6,309,8,'BATERIA ROCKET AGM80 12V 80AH 800A G65 EFB L','','','ICE364','',NULL,NULL,612.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-355-8580',0x01,0,437.44,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(356,644,1,6,309,8,'BATERIA ROCKET AGM92 12V 92AH 850A EFB L','','','ICE365','',NULL,NULL,749.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-356-9378',0x01,0,535.577,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(357,685,1,6,310,8,'LLANTA CEAT 4.00-8 TT BULAND 6PR','','','ICE366','',NULL,NULL,99.9,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-357-8761',0x01,0,83,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(358,615,1,6,309,8,'BATERIA ENERJET 56010L5 12V 65AH 600CCA G42 EFB L','','','ICE367','',NULL,NULL,531.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-358-5124',0x01,0,379.46,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(359,686,1,6,310,8,'LLANTA CORE KING 4.00-8 8PR BAJAJ','','','ICE368','',NULL,NULL,73.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-359-4302',0x01,0,61,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(360,615,1,6,309,8,'BATERIA ENERJET 25P170 12V 1150AMP G4D R','','','ICE369','',NULL,NULL,793.9,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-360-9155',0x01,0,610.72,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(361,620,1,6,312,8,'BATERIA CAPSA NS60LS770 12V 770/490 G60 L','','','ICE370','',NULL,NULL,323.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-361-2117',0x01,0,258.577,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(362,621,1,6,309,8,'BATERIA RECORD RMB 100 12V 900AMP G48 L/R','','','ICE371','',NULL,NULL,483.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-362-2274',0x01,0,372.09,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(363,621,1,6,309,8,'BATERIA RECORD RFF 67 12V 600AMP G60 L','','','ICE372','',NULL,NULL,296.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-363-6274',0x01,0,247.21,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(364,615,1,6,309,8,'BATERIA ENERJET 13M87 12V 87AH G27 L','','','ICE373','',NULL,NULL,427.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-364-8530',0x01,0,328.89,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(365,624,1,6,313,8,'BATERIA MOTO ZERO YTX20L-BS 12V 20AH','','','ICE374','',NULL,NULL,210.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-365-2719',0x01,0,123.74,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(366,626,1,6,309,8,'BATERIA YUASA YTZ6V 12V 5AH MOTO','','','ICE375','',NULL,NULL,263.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-366-5483',0x01,0,164.43,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(367,626,1,6,309,8,'BATERIA YUASA YTZ7S 12V 6AH MOTO','','','ICE376','',NULL,NULL,367.9,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-367-4829',0x01,0,229.96,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(368,636,1,6,310,8,'LLANTA VGOOD 5.00-12 8PR KW53 POST','','','ICE377','',NULL,NULL,114,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-368-6360',0x01,0,95,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(369,636,1,6,310,8,'LLANTA VGOOD 5.00-12 8PR KW214 MIX','','','ICE378','',NULL,NULL,115.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-369-8763',0x01,0,96,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(370,633,1,6,317,8,'CAMARA S&M 3.00-17 TR4 MOTOCICLETA','','','ICE379','',NULL,NULL,12.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-370-3476',0x01,0,6.2,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(371,687,1,6,310,8,'LLANTA TVS 4.00-8 6PR 76F AUTO150 TYRES','','','ICE380','',NULL,NULL,98.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-371-8121',0x01,0,79,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(372,652,1,6,309,8,'BATERIA MOURA ME40SR 12V 40AH G40 /ME310S L','','','ICE381','',NULL,NULL,210.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-372-2879',0x01,0,168.5,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(373,652,1,6,309,8,'BATERIA MOURA ME50ED 12V 50AH G36 /ME510ED L','','','ICE382','',NULL,NULL,292.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-373-8881',0x01,0,208.76,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(374,652,1,6,309,8,'BATERIA MOURA ME60GD 12V 60AH G42 /ME570GD L','','','ICE383','',NULL,NULL,284.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-374-3198',0x01,0,219,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(375,652,1,6,309,8,'BATERIA MOURA ME80RD 12V 75AH G24 /ME710RD L','','','ICE384','',NULL,NULL,423.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-375-7217',0x01,0,302.48,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(376,652,1,6,309,8,'BATERIA MOURA ME75KD 12V 75AH G48 /ME680KD L','','','ICE385','',NULL,NULL,327.9,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-376-3445',0x01,0,252.2,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(377,652,1,6,309,8,'BATERIA MOURA ME90TD 12V 90AH G27 /ME810TD L','','','ICE386','',NULL,NULL,416,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-377-6014',0x01,0,320,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(378,652,1,6,309,8,'BATERIA MOURA ME150BD 12V 150AH G4D /ME1080BD L','','','ICE387','',NULL,NULL,725.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-378-6792',0x01,0,558.2,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(379,688,1,6,310,8,'LLANTA DOUPRO 12.00R20 ST901 20PR MIX SURCO','','','ICE388','',NULL,NULL,864,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-379-2145',0x01,0,720,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(380,616,1,6,309,8,'BAT BOLT HD27-11 12V 71AH-506 G27 R','','','ICE389','',NULL,NULL,293.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-380-1919',0x01,0,202.34,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(381,644,1,6,309,8,'BATERIA ROCKET NS60S 12V 45AH G60 L','','','ICE390','',NULL,NULL,276.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-381-3124',0x01,0,205.07,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(382,644,1,6,309,8,'BATERIA ROCKET 54459 12V 50AH G36 L','','','ICE391','',NULL,NULL,245,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-382-5611',0x01,0,175,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(383,644,1,6,309,8,'BATERIA ROCKET 55457K 12V 54AH G42 L','','','ICE392','',NULL,NULL,246.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-383-6045',0x01,0,182.38,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(384,616,1,6,309,8,'BAT BOLT HD24-11 12V 69AH-506 G24 R','','','ICE393','',NULL,NULL,239.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-384-6382',0x01,0,191.31,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(385,615,1,6,309,8,'BATERIA ENERJET 15MB90 12V 1051AMP G48 R','','','ICE394','',NULL,NULL,386.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-385-2491',0x01,0,297.4,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(386,615,1,6,309,8,'BATERIA ENERJET DS50 12V 550AMP G40 R','','','ICE395','',NULL,NULL,267.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-386-2347',0x01,0,205.48,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(387,615,1,6,309,8,'BATERIA ENERJET DS50P 12V 550AMP G40 L','','','ICE396','',NULL,NULL,267.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-387-6812',0x01,0,205.48,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(388,615,1,6,309,8,'BATERIA ENERJET 15M99 12V 1162AMP G27 R','','','ICE397','',NULL,NULL,450,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-388-8945',0x01,0,346.12,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(389,615,1,6,309,8,'BATERIA ENERJET 13S85 12V 1015AMP G24 R','','','ICE398','',NULL,NULL,361,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-389-2103',0x01,0,277.7,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(390,615,1,6,309,8,'BATERIA ENERJET 13W75 12V 939AMP G42 R','','','ICE399','',NULL,NULL,340.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-390-6518',0x01,0,261.67,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(391,689,1,6,310,8,'LLANTA ANTYRE 295/80R22.5 POS TB707 152/148L 18PR','','','ICE400','',NULL,NULL,960,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-391-2900',0x01,0,800,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(392,621,1,6,309,8,'BATERIA RECORD RNS 45P 12V 550AMP G40 L','','','ICE401','',NULL,NULL,252.9,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-392-3149',0x01,0,210.78,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(393,621,1,6,309,8,'BATERIA RECORD RFF 65 12V 582AMP G60 R','','','ICE402','',NULL,NULL,267,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-393-5812',0x01,0,222.48,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(394,621,1,6,309,8,'BATERIA RECORD RC 60 12V 609AMP G24 R','','','ICE403','',NULL,NULL,258.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-394-6042',0x01,0,215.17,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(395,621,1,6,309,8,'BATERIA RECORD RC 65 12V 786AMP G24 R','','','ICE404','',NULL,NULL,268.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-395-8799',0x01,0,224.04,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(396,621,1,6,309,8,'BATERIA RECORD RC 70 12V 791AMP G24 R','','','ICE405','',NULL,NULL,299.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-396-7894',0x01,0,249.48,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(397,621,1,6,309,8,'BATERIA RECORD RF 65 12V 655AMP G27 R','','','ICE406','',NULL,NULL,302.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-397-5343',0x01,0,251.92,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(398,621,1,6,309,8,'BATERIA RECORD RF 75 12V 791AMP G27 R','','','ICE407','',NULL,NULL,360.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-398-9073',0x01,0,300.58,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(399,621,1,6,309,8,'BATERIA RECORD RF 95 12V 900AMP G27 R','','','ICE408','',NULL,NULL,392.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-399-4239',0x01,0,326.77,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(400,621,1,6,309,8,'BATERIA RECORD RHK 80 12V 715AMP G35 R','','','ICE409','',NULL,NULL,336.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-400-4377',0x01,0,280.58,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(401,621,1,6,309,8,'BATERIA RECORD RW 65 12V 690AMP G42 R','','','ICE410','',NULL,NULL,290.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-401-5170',0x01,0,241.98,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(402,685,1,6,310,8,'LLANTA CEAT 4.50-10 SCUTER MAX SPEED','','','ICE411','',NULL,NULL,195,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-402-1549',0x01,0,150,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(403,615,1,6,309,8,'BATERIA ENERJET 23P159 12V 1573AMP G4D R','','','ICE412','',NULL,NULL,721,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-403-1679',0x01,0,554.59,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(404,621,1,6,309,8,'BATERIA RECORD RW 60 12V 655AMP G42 R','','','ICE415','',NULL,NULL,261,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-404-2111',0x01,0,217.48,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(405,652,1,6,309,8,'BATERIA MOURA ME100 (NO) 12V 100AH G31 /ME880H R','','','ICE416','',NULL,NULL,554.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-405-6050',0x01,0,395.8,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(406,620,1,6,312,8,'BATERIA CAPSA 31T 1600 (NO)12V 1600/800 G31 R','','','ICE417','',NULL,NULL,485.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-406-7574',0x01,0,388.19,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(407,620,1,6,312,8,'BATERIA CAPSA 4D 1800 12V 1800/940 G4D L','','','ICE418','',NULL,NULL,613.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-407-6869',0x01,0,510.9,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(408,676,1,6,309,8,'BAT SUNTEC MF54316 12V 43AH-400 G36 L','','','ICE419','',NULL,NULL,203.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-408-5408',0x01,0,169.7,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(409,690,1,6,328,8,'PANEL SOLAR INCORPORADO 12V 8AH BS-S3720','','','ICE420','',NULL,NULL,204,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-409-4706',0x01,0,170,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(410,622,1,6,309,8,'BATERIA ETNA HL-09 12V 45AH G40 L','','','ICE421','',NULL,NULL,263.9,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-410-2150',0x01,0,211.094,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(411,622,1,6,309,8,'BAT ETNA FF-13 12V 70AH-560 G60 L','','','ICE422','',NULL,NULL,326.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-411-1762',0x01,0,267.72,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(412,622,1,6,309,8,'BATERIA ETNA W-11 12V 63AH G42 L','','','ICE423','',NULL,NULL,309.9,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-412-3569',0x01,0,247.925,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(413,622,1,6,309,8,'BAT ETNA W-13 12V 75AH-530 G42 L','','','ICE424','',NULL,NULL,360.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-413-6823',0x01,0,257.71,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(414,622,1,6,309,8,'BAT ETNA V-13 12V 87AH-608 G24 L','','','ICE425','',NULL,NULL,374.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-414-5193',0x01,0,277.56,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(415,622,1,6,309,8,'BAT ETNA S-1215 12V 88AH-604 G48 L','','','ICE426','',NULL,NULL,393.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-415-6358',0x01,0,307.1,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(416,622,1,6,309,8,'BAT ETNA FH-1215 12V 102AH-709 G27 L','','','ICE427','',NULL,NULL,465.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-416-4037',0x01,0,339.48,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(417,622,1,6,309,8,'BAT ETNA FH-1215 12V 102AH-709 G27 R','','','ICE428','',NULL,NULL,465.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-417-9436',0x01,0,339.48,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(418,622,1,6,309,8,'BAT ETNA S-1223 12V 155AH-1045 G4D L','','','ICE429','',NULL,NULL,738.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-418-9462',0x01,0,547.16,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(419,691,1,6,309,8,'BATERIA GEL AK 8GMF 12V 38AH G36','','','ICE430','',NULL,NULL,344.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-419-2713',0x01,0,265,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(420,691,1,6,309,8,'BATERIA GEL AK FT12-150 12V 150AH G8D','','','ICE431','',NULL,NULL,1131,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-420-4913',0x01,0,870,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(421,692,1,6,309,8,'BATERIA OKAYAMA MF616 12V 40AH G40 L','','','ICE432','',NULL,NULL,147.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-421-6753',0x01,0,123,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(422,692,1,6,309,8,'BATERIA OKAYAMA MF621 12V 65AH G24 L','','','ICE433','',NULL,NULL,250.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-422-4086',0x01,0,209,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(423,692,1,6,309,8,'BATERIA OKAYAMA MF616P 12V 40AH G40 L','','','ICE434','',NULL,NULL,147.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-423-1480',0x01,0,123,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(424,637,1,6,309,8,'BATERIA BOSCH Q85 12V 65AH G35 EFB L','','','ICE436','',NULL,NULL,1119.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-424-9543',0x01,0,746.24,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(425,637,1,6,309,8,'BATERIA BOSCH Q85L 12V 65AH G35 EFB L','','','ICE437','',NULL,NULL,1013.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-425-3077',0x01,0,675.56,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(426,693,1,6,310,8,'LLANTA ONYX 12.00R20 POS HO168 (MINERA) TCF 20PR','','','ICE438','',NULL,NULL,1001,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-426-5880',0x01,0,910,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(427,694,1,6,329,8,'*','','','ICE439','',NULL,NULL,1.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-427-3242',0x01,0,0.1,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(428,621,1,6,309,8,'BATERIA RECORD RT 202 12V 1826AMP G8D L','','','ICE440','',NULL,NULL,767.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-428-7359',0x01,0,643.6,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(429,621,1,6,309,8,'BATERIA RECORD RC 80 12V 850AMP G24 L','','','ICE441','',NULL,NULL,378.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-429-9024',0x01,0,315.62,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(430,621,1,6,309,8,'BATERIA RECORD RC 90 12V 920AMP G24 L','','','ICE442','',NULL,NULL,413.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-430-8965',0x01,0,317.91,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(431,621,1,6,309,8,'BATERIA RECORD RHK 80 12V 760AMP TX G35 L','','','ICE443','',NULL,NULL,414,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-431-3740',0x01,0,318.48,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(432,621,1,6,309,8,'BATERIA RECORD RW 75 12V 720AMP G42 L','','','ICE444','',NULL,NULL,358,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-432-2290',0x01,0,286.39,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(433,621,1,6,309,8,'BATERIA RECORD RWS 50 12V 520AMP G36 L','','','ICE445','',NULL,NULL,233.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-433-1597',0x01,0,194.69,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(434,615,1,6,309,8,'BBATERIA ENERJET MTF-7 12V 270AMP GU1 L','','','ICE446','',NULL,NULL,164.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-434-5248',0x01,0,3,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(435,677,1,6,309,8,'BATERIA KINGPOWER 80D26 12V 70AH 630 G24 L','','','ICE448','',NULL,NULL,292.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-435-3061',0x01,0,234.24,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(436,676,1,6,309,8,'BAT SUNTEC 55D23 12V 60AH-550 G35 L','','','ICE449','',NULL,NULL,256.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-436-1786',0x01,0,197.2,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(437,644,1,6,309,8,'BATERIA ROCKET NX110 12V 70AH G24 R','','','ICE450','',NULL,NULL,331.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-437-4129',0x01,0,245.23,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(438,644,1,6,309,8,'BATERIA ROCKET NX120 12V 90AH G27 L','','','ICE451','',NULL,NULL,415.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-438-6598',0x01,0,296.636,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(439,644,1,6,309,8,'BATERIA ROCKET NX120 12V 90AH G27 R','','','ICE452','',NULL,NULL,429.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-439-3161',0x01,0,318.07,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(440,644,1,6,309,8,'BATERIA ROCKET N200 12V 200AH G8D L','','','ICE453','',NULL,NULL,846.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-440-3256',0x01,0,651.29,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(441,644,1,6,309,8,'BATERIA ROCKET 50D20 12V 55AH G35 L','','','ICE454','',NULL,NULL,250.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-441-7299',0x01,0,179.014,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(442,644,1,6,309,8,'BATERIA ROCKET 55D23 12V 60AH G35 L','','','ICE455','',NULL,NULL,297.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-442-4211',0x01,0,212.62,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(443,644,1,6,309,8,'BATERIA ROCKET 56219 12V 62AH G42 L','','','ICE456','',NULL,NULL,309.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-443-3008',0x01,0,221.022,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(444,644,1,6,309,8,'BATERIA ROCKET 57220 12V 72AH G48 L','','','ICE457','',NULL,NULL,374.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-444-7257',0x01,0,267.23,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(445,637,1,6,309,8,'BAT BOSCH 95D31 12V 90AH-730 G27 R','','','ICE458','',NULL,NULL,617.9,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-445-9922',0x01,0,386.16,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(446,637,1,6,309,8,'BATERIA BOSCH 580.73 12V 80AH G100 AMS L','','','ICE459','',NULL,NULL,618.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-446-4683',0x01,0,433.29,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(447,695,1,6,330,8,'ESPEJO LATERALES CR7 12170 M. L. (PAR) NEGRO','','','ICE460','',NULL,NULL,60,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-447-4838',0x01,0,20,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(448,695,1,6,330,8,'ESPEJO RETROVISOR BAIHUO MY-7366 CAMARA2 1080P','','','ICE461','',NULL,NULL,152,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-448-3645',0x01,0,76,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(449,695,1,6,330,8,'MINI CAMARA BAIHUO MY-7596 INCL RETROVISOR','','','ICE462','',NULL,NULL,165,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-449-5956',0x01,0,66,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(450,695,1,6,330,8,'INFLADOR PORTATIL CARSUN C1399-2 12V 120W','','','ICE463','',NULL,NULL,92,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-450-4400',0x01,0,46,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(451,695,1,6,330,8,'CAMARA SOAK AHD-130 RETROCESO BLACK','','','ICE464','',NULL,NULL,51,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-451-5836',0x01,0,17,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(452,695,1,6,330,8,'MINI VENTILADOR DESKTOP DP13011 5V 1A PINK','','','ICE465','',NULL,NULL,30,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-452-5824',0x01,0,12,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(453,695,1,6,330,8,'LAMPARA LOVE PM12073 ASTRONAUT OSO ESP','','','ICE466','',NULL,NULL,33,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-453-4050',0x01,0,11,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(454,695,1,6,330,8,'RELOJ PARA MESA GAOSIIO DS-5618 LASER BLANCO','','','ICE467','',NULL,NULL,52.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-454-9037',0x01,0,21,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(455,695,1,6,330,8,'ASTRONAUTA PROYECTOR XY0136 BLUETO + CONT','','','ICE468','',NULL,NULL,56,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-455-5121',0x01,0,28,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(456,696,1,6,310,8,'LLANTA AUSTONE 265/65R17 AT 112T SP-302TL','','','ICE469','',NULL,NULL,288,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-456-4285',0x01,0,240,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(457,695,1,6,330,8,'RELOJ PARED GH8010 JH36153 ALARMA','','','ICE470','',NULL,NULL,92,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-457-6820',0x01,0,46,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(458,695,1,6,330,8,'MINI PARLANTE ZJ-08 FP-11583 BLUETO BLANCO','','','ICE471','',NULL,NULL,48,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-458-7454',0x01,0,24,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(459,695,1,6,330,8,'MINI LAMPARA SPEAKER FP-11580 4 IN1 5V 15W BLANCO','','','ICE472','',NULL,NULL,94,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-459-9597',0x01,0,47,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(460,695,1,6,330,8,'CONTROL SAITAKE D16115 MULTIFUNCIONAL','','','ICE473','',NULL,NULL,212,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-460-9976',0x01,0,106,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(461,695,1,6,330,8,'CONTROL AOBING MAX PD16116 ANDROID KEYMAP','','','ICE474','',NULL,NULL,147.9,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-461-1834',0x01,0,87,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(462,695,1,6,330,8,'MINI CONSOLA R36S PP11900 1GB','','','ICE475','',NULL,NULL,222,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-462-8512',0x01,0,111,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(463,695,1,6,330,8,'MINI CONSOLA GAME PP12329 M28PRO','','','ICE476','',NULL,NULL,387.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-463-7282',0x01,0,242,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(464,626,1,6,309,8,'BATERÍA YUASA REW45-12 12V 8AH','','','ICE477','',NULL,NULL,185.9,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-464-8689',0x01,0,116.2,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(465,626,1,6,309,8,'BATERÍA YUASA NP12-12 12V 12AH','','','ICE478','',NULL,NULL,276.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-465-8221',0x01,0,172.9,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(466,625,1,6,309,8,'BATERÍA OXFORD XF7-12 12V 7AH','','','ICE479','',NULL,NULL,89.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-466-9372',0x01,0,44.87,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(467,619,1,6,309,8,'BATERÍA SOLITE 58510 12V 85AH G65','','','ICE480','',NULL,NULL,583.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-467-4181',0x01,0,416.95,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(468,695,1,6,330,8,'ESPEJO LATERALES CR7 12170 M. L. (PAR) AZUL','','','ICE481','',NULL,NULL,60,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-468-7754',0x01,0,20,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(469,695,1,6,330,8,'MINI VENTILADOR DESKTOP DP13011 5V 1A VERDE','','','ICE482','',NULL,NULL,30,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-469-2830',0x01,0,12,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(470,695,1,6,330,8,'MINI LAMPARA SPEAKER FP-11580 4 IN1 5V 15W NEGRO','','','ICE483','',NULL,NULL,94,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-470-1623',0x01,0,47,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(471,695,1,6,330,8,'MINI PARLANTE ZJ-08 FP-11583 BLUETO NEGRO','','','ICE484','',NULL,NULL,48,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-471-6271',0x01,0,24,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(472,695,1,6,330,8,'RELOJ PARA MESA GAOSIIO DS-5618 LASER CELESTE','','','ICE485','',NULL,NULL,52.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-472-9622',0x01,0,21,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(473,695,1,6,330,8,'RELOJ PARA MESA GAOSIIO DS-5618 LASER ROSA','','','ICE486','',NULL,NULL,52.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-473-7350',0x01,0,21,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(474,695,1,6,330,8,'RELOJ PARA MESA GAOSIIO DS-5618 LASER VERDE','','','ICE487','',NULL,NULL,52.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-474-3982',0x01,0,21,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(475,695,1,6,330,8,'LAMPARA LOVE PM12073 ASTRONAUT VIAJERO SOL','','','ICE488','',NULL,NULL,33,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-475-5940',0x01,0,11,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(476,695,1,6,330,8,'LAMPARA LOVE PM12073 ASTRONAUT ESCAL LUNA','','','ICE489','',NULL,NULL,33,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-476-2037',0x01,0,11,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(477,695,1,6,330,8,'LAMPARA LOVE PM12073 ASTRONAUT PLANETA LUM','','','ICE490','',NULL,NULL,33,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-477-8060',0x01,0,11,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(478,695,1,6,330,8,'LAMPARA LOVE PM12073 ASTRONAUT ESFE COSMICA','','','ICE491','',NULL,NULL,33,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-478-8685',0x01,0,11,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(479,697,1,6,309,8,'BATERIA RECORD MAX RM-45 12V 370A 45AH G36 L','','','ICE492','',NULL,NULL,217.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-479-3041',0x01,0,167.38,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(480,697,1,6,309,8,'BATERIA RECORD MAX RM-50 12V 420A 52AH G42 L','','','ICE493','',NULL,NULL,236.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-480-8039',0x01,0,181.82,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(481,697,1,6,309,8,'BATERIA RECORD MAX RM50D26 12V 350A 50AH G24 L','','','ICE494','',NULL,NULL,223.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-481-3684',0x01,0,172.02,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(482,697,1,6,309,8,'BATERIA RECORD MAX RM-60D23 12V 450A 60AH G35 L','','','ICE495','',NULL,NULL,282.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-482-6847',0x01,0,217.41,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(483,697,1,6,309,8,'BATERIA RECORD MAX RM-60D31 12V 500A 65AH G27 L','','','ICE496','',NULL,NULL,267.9,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-483-8339',0x01,0,206.1,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(484,697,1,6,309,8,'BATERIA RECORD MAX RM-65D26 12V 500A 65AH G24 L','','','ICE497','',NULL,NULL,267.9,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-484-8381',0x01,0,191.36,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(485,697,1,6,309,8,'BATERIA RECORD MAX RM-70 12V 500A 65AH G48 L','','','ICE498','',NULL,NULL,306.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-485-6850',0x01,0,236,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(486,698,1,6,310,8,'LLANTA YUANXING 120/80-17','','','ICE499','',NULL,NULL,138,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-486-3715',0x01,0,115,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(487,622,1,6,309,8,'BBAT ETNA W-13 12V 75AH-530 G42 L','','','ICE500','',NULL,NULL,360.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-487-3145',0x01,0,3,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(488,615,1,6,309,8,'BATERIA ENERJET MB80 12V 920AMP G48 L','','','ICE501','',NULL,NULL,370.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-488-6760',0x01,0,284.9,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(489,615,1,6,318,8,'SILICONA ENERJET 120ML COCO PROT. U. V.','','','7759863000100','',NULL,NULL,7.9,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-489-1432',0x01,0,0.1,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(490,699,1,6,331,8,'PANETON TOTTUS 900G','','','ICE503','',NULL,NULL,12.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-490-5234',0x01,0,11.7,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(491,685,1,6,310,8,'LLANTA CEAT 4.00-8 X3 BULAND 6PR TT','','','ICE504','',NULL,NULL,123.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-491-4234',0x01,0,88,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(492,685,1,6,310,8,'LLANTA CEAT 140/70-17 66H ZOOM XL','','','ICE505','',NULL,NULL,209.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-492-3139',0x01,0,161,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(493,685,1,6,310,8,'LLANTA CEAT 120/80-17 61P X5 GRIPP TL AT','','','ICE506','',NULL,NULL,230.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-493-1549',0x01,0,171,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(494,685,1,6,310,8,'LLANTA CEAT 100/90-17 55P GRIPP TT AT','','','ICE507','',NULL,NULL,203.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-494-9793',0x01,0,151,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(495,685,1,6,310,8,'LLANTA CEAT 130/70-17 XL ZOOM HT','','','ICE508','',NULL,NULL,224.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-495-3087',0x01,0,166,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(496,700,1,6,318,8,'SILICONA EDEN AROMA FRESA 120 ML','','','ICE509','',NULL,NULL,8.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-496-7633',0x01,0,2.7,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(497,700,1,6,318,8,'SILICONA EDEN AROMA COCO 120 ML','','','ICE510','',NULL,NULL,8.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-497-6393',0x01,0,2.7,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(498,700,1,6,318,8,'SILICONA EDEN AROMA FRESA 300 ML','','','ICE511','',NULL,NULL,14.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-498-3240',0x01,0,4.7,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(499,700,1,6,318,8,'SILICONA EDEN AROMA COCO 300 ML','','','ICE512','',NULL,NULL,14.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-499-5675',0x01,0,4.7,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(500,700,1,6,318,8,'ABRILLANTADOR EDEN PARA NEUMÁTICOS 120 ML','','','ICE513','',NULL,NULL,11.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-500-8460',0x01,0,2.9,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(501,700,1,6,318,8,'ABRILLANTADOR EDEN PARA NEUMÁTICOS 300 ML','','','ICE514','',NULL,NULL,19.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-501-3319',0x01,0,4.4,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(502,615,1,6,309,8,'BATERIA ENERJET 27P190 12V 1680AMP G8D L','','','ICE515','',NULL,NULL,853.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-502-1634',0x01,0,656.38,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(503,615,1,6,309,8,'BATERIA ENERJET 11D56 12V 56AMP G40 L','','','ICE516','',NULL,NULL,287.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-503-8244',0x01,0,221.42,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(504,701,1,6,310,8,'LLANTA KAPSEN 225/65R17 ATR02 PRACTICALMAX','','','ICE517','',NULL,NULL,279.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-504-4249',0x01,0,215,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(505,666,1,6,310,8,'LLANTA CHAOYANG 165/65R13','','','ICE518','',NULL,NULL,94.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-505-1068',0x01,0,78.6,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(506,702,1,6,318,8,'CONTROLADOR PARA PANEL SOLAR','','','ICE519','',NULL,NULL,108,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-506-2453',0x01,0,90,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(507,703,1,6,310,8,'LLANTA LING LONG 155/70R12','','','ICE520','',NULL,NULL,85.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-507-7438',0x01,0,71.5,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(508,637,1,6,309,8,'BATERIA BOSCH 105D26 12V 85AH G24 AMS R','','','ICE521','',NULL,NULL,557.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-508-3999',0x01,0,278.958,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(509,704,1,6,309,8,'BAT BULK 55D-10 12V 42AH-410 G42 L','','','ICE522','',NULL,NULL,205.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-509-6317',0x01,0,167.14,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(510,615,1,6,318,8,'LIMPIA PARABRISAS ENERJET 1L','','','7759863000094','',NULL,NULL,12.9,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-510-3464',0x01,0,0.1,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(511,635,1,6,310,8,'LLANTA JVX 100/90-10 SCOOTER SPEEDO','','','ICE524','',NULL,NULL,92,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-511-6682',0x01,0,46,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(512,664,1,6,310,8,'LLANTA MOTO LINEAL SFX 90/100-17','','','ICE525','',NULL,NULL,132,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-512-8795',0x01,0,110,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(513,700,1,6,332,8,'DENSIMETRO','','','ICE526','',NULL,NULL,42,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-513-5655',0x01,0,21,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(514,700,1,6,332,8,'PROBADOR DE BATERIA PS-100','','','ICE527','',NULL,NULL,427.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-514-5575',0x01,0,285,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(515,705,1,6,318,8,'LIMPIADOR DE FRENOZ Y PIEZAS (AEROSOL) 440ML','','','7759863000056','',NULL,NULL,24.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-515-2399',0x01,0,9.9,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(516,705,1,6,318,8,'LIMPIADOR DE CARBURADORES 3BSI (AEROSOL) 440ML','','','7759863000032','',NULL,NULL,24.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-516-3073',0x01,0,9.9,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(517,705,1,6,318,8,'AFLOJATODO 3BSI MULTIUZOS (AEROSOL) 440ML','','','7759863000018','',NULL,NULL,19,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-517-2721',0x01,0,8.2,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(518,705,1,6,318,8,'LIMPIA CONTACTOS 3BSI (AEROSOL) 440ML','','','7759863000025','',NULL,NULL,24.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-518-9807',0x01,0,9.9,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(519,705,1,6,318,8,'LUBRICANTE DE CADENAS 3BSI (AEROSOL) 440ML','','','7759863000049','',NULL,NULL,24.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-519-7253',0x01,0,9.9,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(520,705,1,6,318,8,'DESENGRASANTE DE CADENAS 3BSI (AEROSOL) 440ML','','','ICE533','',NULL,NULL,24.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-520-2471',0x01,0,9.9,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(521,706,1,6,316,8,'ARO ROMAX QC1261 17*9.0-6*139.7 ET 0 CB 106.1 SATTIN','','','ICE536','',NULL,NULL,382.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-521-5869',0x01,0,294,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(522,707,1,6,316,8,'ARO BELEN SW372 12X6.0-4*114.3 ET -9 CB 73','','','ICE537','',NULL,NULL,166.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-522-1358',0x01,0,128.25,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(523,708,1,6,316,8,'ARO ROMAX 353 12X4.5-(4)*114.3 ET 15 CB 73.1 (16TT4P1S)TI','','','ICE538','',NULL,NULL,165.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-523-2070',0x01,0,127.52,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(524,709,1,6,316,8,'ARO BELEN 1066 13X5.5-8*100/114.3 ET 30 CB 73.1','','','ICE539','',NULL,NULL,194.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-524-2249',0x01,0,149.5,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(525,710,1,6,316,8,'ARO ROMAX 1459 13X7.0-(8)X100/114.3 ET 0 CB 73.1','','','ICE540','',NULL,NULL,801.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-525-8141',0x01,0,641,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(526,710,1,6,316,8,'ARO ROMAX QC2057 13X6.0-(8)X100/114.3 ET 10 CB 73.1 (16TT4P1S)','','','ICE541','',NULL,NULL,766.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-526-4852',0x01,0,638.8,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(527,709,1,6,316,8,'ARO BELEN SW275 13X6.0-(4)X100/4*114.3 (16TT4P1S)','','','ICE542','',NULL,NULL,798.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-527-9182',0x01,0,638.8,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(528,710,1,6,316,8,'ARO ROMAX 6154 13X7.0-8*100/114.3 ET 0 CB 73.1','','','ICE543','',NULL,NULL,194.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-528-8598',0x01,0,149.5,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(529,710,1,6,316,8,'ARO ROMAX 8063 13X7.0-8*100/114.3 ET -10 CB 73.1','','','ICE544','',NULL,NULL,194.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-529-8369',0x01,0,149.5,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(530,710,1,6,316,8,'ARO ROMAX 826 13X7.0-(8)*100/114.3 ET 20 CB 73.1 B MACHINED (16TT4P1S)','','','ICE545','',NULL,NULL,196.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-530-4213',0x01,0,151.22,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(531,710,1,6,316,8,'ARO ROMAX QC1518 13X6.5-8*100/114.3 ET 0 CB 73.1','','','ICE546','',NULL,NULL,194.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-531-8370',0x01,0,149.5,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(532,710,1,6,316,8,'ARO ROMAX 7088 13X6.0-8*100/114.3 ET 10 CB 73.1','','','ICE547','',NULL,NULL,194.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-532-5055',0x01,0,149.5,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(533,710,1,6,316,8,'ARO ROMAX QC1518B 13X6.5-(8)*100/114.3 ET 0 CB 73.1 BLUE (16TT4P1S)','','','ICE548','',NULL,NULL,194.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-533-5766',0x01,0,149.5,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(534,710,1,6,316,8,'ARO ROMAX 6213 13X7.0-8*100/114.3 ET 0 CB 73.1 RED','','','ICE549','',NULL,NULL,194.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-534-6027',0x01,0,149.5,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(535,710,1,6,316,8,'ARO ROMAX JF801 13X6.0-8*100/114.3 ET 0 CB 73.1 BRONZE','','','ICE550','',NULL,NULL,196.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-535-9298',0x01,0,151.2,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(536,710,1,6,316,8,'ARO ROMAX 1465 13X8.0-8*100/114.3 ET 0 CB 73.1 BP','','','ICE551','',NULL,NULL,199.9,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-536-6652',0x01,0,153.75,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(537,711,1,6,316,8,'ARO ROMAX 1459 14X7.0-8*100/114.3 ET 0 CB 73.1 RED','','','ICE552','',NULL,NULL,218.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-537-7266',0x01,0,168.2,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(538,711,1,6,316,8,'ARO ROMAX 1459 14X7.0-8*100/114.3 ET 0 CB 73.1 UNDER','','','ICE553','',NULL,NULL,216.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-538-3680',0x01,0,166.5,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(539,711,1,6,316,8,'ARO ROMAX 8155 14X6.0-8*100/114.3 ET 10 CB 73.1 RED','','','ICE554','',NULL,NULL,218.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-539-7653',0x01,0,168.2,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(540,712,1,6,316,8,'ARO BELEN SW2176 14X8.0-4*100 ET -25 CB 73.1 BP','','','ICE555','',NULL,NULL,219.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-540-3981',0x01,0,169.05,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(541,711,1,6,316,8,'ARO ROMAX 708 14X6.5-8*100/114.3 ET 0 CB 73.1 SATTIN','','','ICE556','',NULL,NULL,218.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-541-4973',0x01,0,168.2,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(542,712,1,6,316,8,'ARO BELEN SW1777 14X7.0-8*100/114.3 ET 0 CB 73.1 BLACK','','','ICE557','',NULL,NULL,216.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-542-6452',0x01,0,166.5,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(543,712,1,6,316,8,'ARO BELEN SW915 14X7.0-5*114.3 ET 0 CB 73.1 BLACK','','','ICE558','',NULL,NULL,249.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-543-4609',0x01,0,192,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(544,711,1,6,316,8,'ARO ROMAX 8064 14X7.0-8*100/114.3 ET -10 CB 73.1 BLP','','','ICE559','',NULL,NULL,216.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-544-3257',0x01,0,166.5,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(545,711,1,6,316,8,'ARO ROMAX 797 14X7.0-8*100/114.3 ET 0 CB 73.1 BLACK','','','ICE560','',NULL,NULL,216.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-545-2829',0x01,0,166.5,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(546,711,1,6,316,8,'ARO ROMAX 6198 14X6.0-8*100/114.3 RED','','','ICE561','',NULL,NULL,216.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-546-8957',0x01,0,166.5,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(547,713,1,6,316,8,'ARO NISOSHI NS-030(145078) 14X6.0-8*100/114.3 ET 30 CB 73.1 BLUE','','','ICE562','',NULL,NULL,218.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-547-1110',0x01,0,168.2,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(548,711,1,6,316,8,'ARO ROMAX 1207 14X7.0-8*100/114.3 ET 0 CB 73.1 BLACK','','','ICE563','',NULL,NULL,218.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-548-4737',0x01,0,168.2,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(549,714,1,6,316,8,'ARO ROMAX QC829 15X8.0-5*114.3 ET 0 CB 73.1 SATTIN MATTE','','','ICE564','',NULL,NULL,282.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-549-8975',0x01,0,217.5,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(550,714,1,6,316,8,'ARO ROMAX QC1852 15X8.0-5*114.3 ET 0 CB 73.1 SATTIN MATTE BLACK','','','ICE565','',NULL,NULL,292.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-550-2861',0x01,0,224.7,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(551,714,1,6,316,8,'ARO ROMAX QC2043 15X8.0-5*114.3 ET -10 CB 73.1 BLACK MACHINED','','','ICE566','',NULL,NULL,292.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-551-9526',0x01,0,224.7,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(552,710,1,6,316,8,'ARO ROMAX 1431 13X5.5-(8)X100/114.3 ET 30 CB 73.1 BLUE UNDERCUT','','','ICE567','',NULL,NULL,758.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-552-4591',0x01,0,632,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(553,714,1,6,316,8,'ARO ROMAX X1164 15X7.0-8*100/114.3 ET 10 CB 73.1 BL MACHINED','','','ICE568','',NULL,NULL,292.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-553-6418',0x01,0,224.7,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(554,714,1,6,316,8,'ARO ROMAX 1518 15X8.0-8*100/114.3 ET 0 CB 73.1 SATTIN','','','ICE569','',NULL,NULL,233,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-554-1767',0x01,0,179.25,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(555,714,1,6,316,8,'ARO ROMAX 1459 15X8.0-8*100/114.3 ET -10 CB 73.1 RED','','','ICE570','',NULL,NULL,233,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-555-1563',0x01,0,179.25,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(556,714,1,6,316,8,'ARO ROMAX X2020 15X7.0-8*100/114.3 ET 0 CB 73.1 BLUE','','','ICE571','',NULL,NULL,230.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-556-3858',0x01,0,177.55,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(557,714,1,6,316,8,'ARO ROMAX X2021 15X7.0-8*100/114.3 ET 5 CB 73.1 BLUE','','','ICE572','',NULL,NULL,230.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-557-9207',0x01,0,177.55,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(558,714,1,6,316,8,'ARO ROMAX 1002 15X6.5-8*100/114.3 RED(RED BP)','','','ICE573','',NULL,NULL,230.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-558-4880',0x01,0,177.55,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(559,714,1,6,316,8,'ARO ROMAX 7084 15X7.0-(8)*100/114.3 SAND BRONZE (16TT4P1S)','','','ICE574','',NULL,NULL,230.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-559-3186',0x01,0,177.55,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(560,715,1,6,316,8,'ARO BELEN 1066 15X7.0-8*100/114.3 ET 15 CB 73.1 RED UNDERCUT','','','ICE575','',NULL,NULL,230.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-560-4770',0x01,0,177.55,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(561,715,1,6,316,8,'ARO BELEN 1066 15X7.0-10*100/114.3 ET 15 CB 73.1 RED','','','ICE576','',NULL,NULL,230.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-561-6117',0x01,0,177.55,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(562,714,1,6,316,8,'ARO ROMAX 5479 15X7.0-8*100/114.3 RED','','','ICE577','',NULL,NULL,230.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-562-5567',0x01,0,177.55,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(563,711,1,6,316,8,'ARO ROMAX 1509 14X6.0-(8)*100/114.3 ET -5 CB 73.1 RED BP (16TT4P1S)','','','ICE578','',NULL,NULL,221.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-563-8839',0x01,0,170.62,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(564,714,1,6,316,8,'ARO ROMAX 2023 15X7.0-8*100/114.3 ET 0 CB 73.1 RED','','','ICE579','',NULL,NULL,230.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-564-3810',0x01,0,177.55,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(565,714,1,6,316,8,'ARO ROMAX 5490 15X7.0-8*100/114.3 ET 35 CB 73.1 SATTIN','','','ICE580','',NULL,NULL,230.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-565-5932',0x01,0,177.55,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(566,714,1,6,316,8,'ARO ROMAX QC662 15X8.0-8*100/114.3 ET 10 CB 73.1 RED LINE','','','ICE581','',NULL,NULL,233,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-566-4592',0x01,0,179.25,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(567,715,1,6,316,8,'ARO BELEN SW257 15X10.5-4*114.3 ET -32 CB 73.1 BLKM','','','ICE582','',NULL,NULL,238.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-567-6869',0x01,0,183.5,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(568,714,1,6,316,8,'ARO ROMAX QC5206 15X9.0-10*100/114.3 ET 0 CB 73.1 BLACK','','','ICE583','',NULL,NULL,234.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-568-7258',0x01,0,180.1,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(569,714,1,6,316,8,'ARO ROMAX 826 15X8.0-10*100/114.3 ET 20 CB 73.1 BLACK','','','ICE584','',NULL,NULL,233,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-569-9693',0x01,0,179.25,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(570,706,1,6,316,8,'ARO ROMAX X021 17X8.5-10*100/114.3 ET 0 CB 73.1 BLACK','','','ICE585','',NULL,NULL,338,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-570-8465',0x01,0,260,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(571,706,1,6,316,8,'ARO ROMAX 8134 17X9.0-(10)*100/114.3 ET 10 CB 73.1 BLACK FACE (20TT4P1S)','','','ICE586','',NULL,NULL,1388,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-571-4213',0x01,0,1067.8,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(572,706,1,6,316,8,'ARO ROMAX 1453 17X10.0-6*139.7 ET -15 CB 106.1 BLKM','','','ICE587','',NULL,NULL,382.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-572-6976',0x01,0,294,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(573,716,1,6,316,8,'ARO WILCROXX X171170 17X8.5-6*139.7 ET 0 CB 110.1 MATT BLACK','','','ICE588','',NULL,NULL,382.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-573-2482',0x01,0,294,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(574,706,1,6,316,8,'ARO ROMAX 5533 17X7.5-6*139.7E T 15 CB 106.1 BLACK','','','ICE589','',NULL,NULL,382.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-574-2011',0x01,0,294,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(575,716,1,6,316,8,'ARO WILCROXX X172038 17X9.0-6*139.7 ET -12 CB 110.1 MATT','','','ICE590','',NULL,NULL,382.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-575-2320',0x01,0,294,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(576,706,1,6,316,8,'ARO ROMAX JT124 17*9.0-6*139.7 ET -12 CB 106.1 GLOSS BLACK','','','ICE591','',NULL,NULL,402,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-576-3431',0x01,0,309.2,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(577,706,1,6,316,8,'ARO ROMAX JT124 17X9.0-6*139.7 ET 0 CB 106.1 GLOSS BLACK','','','ICE592','',NULL,NULL,393.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-577-6989',0x01,0,302.5,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(578,706,1,6,316,8,'ARO ROMAX 1833 17X9.0-6*139.7 ET -10 CB 106.1 SATTIN','','','ICE593','',NULL,NULL,382.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-578-4392',0x01,0,294,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(579,706,1,6,316,8,'ARO ROMAX 1032 17X9.25-10*100/114.3 ET 18 CB 73.1 SATTIN','','','ICE594','',NULL,NULL,338,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-579-4183',0x01,0,260,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(580,706,1,6,316,8,'ARO ROMAX 0708 17X9.0-10*100/114.3 ET 22 CB 73.1 SATTIN','','','ICE595','',NULL,NULL,338,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-580-2379',0x01,0,260,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(581,706,1,6,316,8,'ARO ROMAX 1261 17X9.0-5*114.3 ET 0 CB 73.1 SATTIN MATTE','','','ICE596','',NULL,NULL,382.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-581-4414',0x01,0,294,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(582,706,1,6,316,8,'ARO ROMAX 1706 17X9.0-6*139.7 ET 0 CB 106.1 BLACK MACHINED','','','ICE597','',NULL,NULL,396.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-582-2503',0x01,0,304.98,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(583,717,1,6,316,8,'ARO QC QC662 15X8.0-8*100/114.3 ET 10 CB 73.1BLUE LINE','','','ICE598','',NULL,NULL,233,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-583-8523',0x01,0,179.25,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(584,711,1,6,316,8,'ARO ROMAX 1508 14X7.0-8*100/114.3 ET -10 CB 73.1 RED BP','','','ICE599','',NULL,NULL,240.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-584-9227',0x01,0,184.71,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(585,711,1,6,316,8,'ARO ROMAX QC1680 14X7.0-8*100/114.3 ET 0 CB 73.1 BLACK M','','','ICE600','',NULL,NULL,0,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-585-9023',0x01,0,168.2,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(586,711,1,6,316,8,'ARO ROMAX 8154 14X7.0-(8)X100/114.3 ET 0 CB 73.1 DARK GUN METAL FACE MACHINED LIP','','','ICE601','',NULL,NULL,917.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-586-8251',0x01,0,734,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(587,639,1,6,320,8,'TUERCA TUBULAR 10X1.25 TICO','','','ICE602','',NULL,NULL,8.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-587-1917',0x01,0,0.1,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(588,666,1,6,310,8,'LLANTA CHAOYANG 185/70R13','','','ICE603','',NULL,NULL,119.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-588-5699',0x01,0,99.24,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(589,718,1,6,310,8,'LLANTA MICHELIN 265/65R17 AT 112H TL LTX TRAIL ST MI','','','ICE604','',NULL,NULL,1001,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-589-7030',0x01,0,715,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(590,719,1,6,310,8,'LLANTA PRIMEX 14.00-24 G3000 HD TL 16PR','','','ICE605','',NULL,NULL,1768,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-590-9336',0x01,0,1360,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(591,693,1,6,310,8,'LLANTA ONYX 12.00R24','','','ICE606','',NULL,NULL,1100,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-591-2955',0x01,0,1000,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(592,720,1,6,310,8,'LLANTA KENDA 2.75-17 MOTO LINEAL','','','ICE607','',NULL,NULL,91,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-592-1989',0x01,0,70,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(593,626,1,6,313,8,'BATERIA YUASA YB14L-A2 12V 14AH FP','','','ICE608','',NULL,NULL,384.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-593-4958',0x01,0,256.07,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(594,721,1,6,310,8,'LLANTA WINDA 155/65R13','','','ICE609','',NULL,NULL,106.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-594-2643',0x01,0,88.5,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(595,630,1,6,314,8,'AUXILIO MECANICO','','','ICE610','',NULL,NULL,15,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-595-6114',0x01,0,0.1,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(596,630,1,6,314,8,'ACIDO ELECTROLITO DSD 1250','','','ICE611','',NULL,NULL,8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-596-1986',0x01,0,0.1,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(597,630,1,6,314,8,'PARCHE DE BATERIA A TEMPERATURA','','','ICE612','',NULL,NULL,40,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-597-5510',0x01,0,20,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(598,630,1,6,314,8,'REPARACION DE POSTE DE BATERIA','','','ICE613','',NULL,NULL,30,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-598-1505',0x01,0,18,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(599,722,1,6,310,8,'LLANTA ONIX 185/65R15','','','ICE614','',NULL,NULL,132.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-599-3244',0x01,0,110.2,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(600,693,1,6,310,8,'LLANTA ONIX 185/60R15','','','ICE615','',NULL,NULL,132.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-600-4606',0x01,0,110.1,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(601,705,1,6,318,8,'LIMPIADOR DE FRENOZ Y PIEZAS (AEROSOL) 280ML','','','ICE616','',NULL,NULL,12,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-601-3172',0x01,0,5.2,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(602,705,1,6,318,8,'LIMPIADOR DE CARBURADORES 3BSI (AEROSOL) 280ML','','','ICE617','',NULL,NULL,12,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-602-7017',0x01,0,5.2,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(603,705,1,6,318,8,'DESENGRASANTE DE CADENAS 3BSI (AEROSOL) 280ML','','','7759863000674','',NULL,NULL,12,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-603-5848',0x01,0,5.2,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(604,705,1,6,318,8,'AFLOJATODO 3BSI MULTIUSOS (AEROSOL) 280ML','','','7759863000292','',NULL,NULL,12,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-604-2843',0x01,0,5.2,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(605,661,1,6,324,8,'S-GU1 BAT. USADA (MTF,U1R) SEMI/','','','ICE620','',NULL,NULL,60,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-605-9386',0x01,0,20,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(606,723,1,6,310,8,'LLANTA LIMA CAUCHO 6.50-13','','','ICE621','',NULL,NULL,174,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-606-4795',0x01,0,145,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(607,723,1,6,310,8,'LLANTA LIMA CAUCHO 5.00-12','','','ICE622','',NULL,NULL,162,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-607-3710',0x01,0,135,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(608,637,1,6,309,8,'BATERIA BOSCH LN5 12V 92AH AGM 850A G48 L','','','ICE623','',NULL,NULL,1253.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-608-8079',0x01,0,835.64,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(609,671,1,6,309,8,'BATERÍA TOPOWER TPU1R 12V 18AH G1 L','','','ICE624','',NULL,NULL,149.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-609-8168',0x01,0,115.23,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(610,693,1,6,310,8,'LLANTA ONYX 205/55R16','','','ICE625','',NULL,NULL,145.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-610-1581',0x01,0,120.97,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(611,705,1,6,318,8,'LUBRICANTE DE CADENAS 3BSI (AEROSOL) 280ML','','','7759863000322','',NULL,NULL,12,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-611-9527',0x01,0,5.2,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(612,667,1,6,310,8,'LLANTA MAXXIS 215/55R17','','','ICE627','',NULL,NULL,456,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-612-3133',0x01,0,380,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(613,724,1,6,310,8,'LLANTA TIRON 12-16.5 SKSL 675','','','ICE628','',NULL,NULL,1620,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-613-5056',0x01,0,1350,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(614,617,1,6,310,8,'LLANTA GOOD YEAR 265/70R16 AT','','','ICE629','',NULL,NULL,936,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-614-9059',0x01,0,780,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(615,650,1,6,310,8,'LLANTA ALLIANCE 12.5/80-18 (PATA DE OSO) 14 PR','','','ICE630','',NULL,NULL,1560,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-615-8872',0x01,0,1300,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(616,617,1,6,310,8,'LLANTA GOOD YEAR 7.50-16 POS CHASQUI 12PR','','','ICE631','',NULL,NULL,912,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-616-8239',0x01,0,760,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(617,725,1,6,310,8,'LLANTA GOODRIDE 12.00-20 POS CL736 20PR (MIN)','','','ICE632','',NULL,NULL,2280,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-617-6593',0x01,0,1900,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(618,700,1,6,318,8,'PERNO Y TUERCA 10PZS (CARGUERO) KIT','','','ICE633','',NULL,NULL,4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-618-2085',0x01,0,0.01,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(619,615,1,6,331,8,'PANETON ENERJET 500G','','','ICE634','',NULL,NULL,8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-619-8077',0x01,0,0.01,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(620,698,1,6,310,8,'LLANTA YUANXING 3.00-18 P21','','','ICE635','',NULL,NULL,90,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-620-5167',0x01,0,60,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(621,663,1,6,310,8,'LLANTA ZMAX 175/65R14 LANDGEMA 82H','','','ICE636','',NULL,NULL,125,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-621-2637',0x01,0,97.5,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(622,681,1,6,310,8,'LLANTA COMFORSER 225/70R16','','','ICE637','',NULL,NULL,274.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-622-4668',0x01,0,183,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(623,656,1,6,310,8,'LLANTA ROADX 195R15C RXQUEST C02','','','ICE638','',NULL,NULL,239.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-623-7387',0x01,0,199.5,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(624,726,1,6,310,8,'LLANTA MARSHAL 265/65R17 AT51','','','ICE639','',NULL,NULL,480,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-624-2757',0x01,0,400,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(625,698,1,6,310,8,'LLANTA DE MOTO YUANXING 275-17','','','ICE640','',NULL,NULL,60,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-625-1513',0x01,0,50,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(626,727,1,6,309,8,'BATERIA ENERMAX 12N5-3B 12V','','','ICE641','',NULL,NULL,57.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-626-6603',0x01,0,48,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(627,728,1,6,310,8,'LLANTA JK TYRE 7.50-16 DEL RIB 124/121M','','','ICE642','',NULL,NULL,0,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-627-8690',0x01,0,360,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(628,712,1,6,316,8,'ARO BELEN 10122 14X6.0-8*100/114.3 ET 35 CB 73.1 RED UNDERCUT','','','ICE643','',NULL,NULL,215.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-628-1737',0x01,0,166.025,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(629,711,1,6,316,8,'ARO ROMAX 5211 14X7.0-8*100/114.3 ET -10 CB 73.1 GUN LP','','','ICE644','',NULL,NULL,221.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-629-4722',0x01,0,170.62,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(630,711,1,6,316,8,'ARO ROMAX 8138 14*7.0-8*100/114.3 ET 0 CB 73.1 BLACK FACE','','','ICE645','',NULL,NULL,215.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-630-4785',0x01,0,166.025,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(631,711,1,6,316,8,'ARO ROMAX 1459 14X7.0-(8)*100/114.3 ET 0 CB 73.1 BLUE BP (16TT4P1S)','','','ICE646','',NULL,NULL,218,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-631-7217',0x01,0,167.72,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(632,711,1,6,316,8,'ARO ROMAX AK195 14*7.0-8*100/114.3 ET 10 CB 73.1 BMF','','','ICE647','',NULL,NULL,218,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-632-1727',0x01,0,167.72,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(633,729,1,6,316,8,'ARO NISOSHI JF801 13X6.0-(8)*100/114.3 ET 10 CB 73.1 MATT BLACK+RED (16TT4P1S)','','','ICE648','',NULL,NULL,193.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-633-8973',0x01,0,149.075,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(634,710,1,6,316,8,'ARO ROMAX 1459 13X7.0-8*100/114.3 ET 0 CB 73.1 RED BP','','','ICE649','',NULL,NULL,193.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-634-9592',0x01,0,149.075,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(635,710,1,6,316,8,'ARO ROMAX 2058 13X6.0-8*100/114.3 ET 25 CB 73.1 RED UNDERCUT BLACK','','','ICE650','',NULL,NULL,193.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-635-6092',0x01,0,149.075,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(636,706,1,6,316,8,'ARO ROMAX 1495 17X9.0-6*139.7 ET 0 CB 106.1 BLKM','','','ICE651','',NULL,NULL,381.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-636-5375',0x01,0,293.15,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(637,730,1,6,316,8,'ARO TOP 8044 17X9.0-6*139.7 ET 0 CB 106.1 BLACK MILLING','','','ICE652','',NULL,NULL,403.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-637-7916',0x01,0,310.1,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(638,706,1,6,316,8,'ARO ROMAX DX203 17X9.0-6*139.7 ET -5 CB 110.1 BLACK MACHINE FACE+','','','ICE653','',NULL,NULL,392.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-638-3477',0x01,0,301.625,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(639,706,1,6,316,8,'ARO ROMAX JT227 17X9.0-(6)*139.7 ET -12 CB 106.1 BLACK RED MILLING (24TC4P)','','','ICE654','',NULL,NULL,403.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-639-6349',0x01,0,310.1,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(640,706,1,6,316,8,'ARO ROMAX JT227 17X9.0-(6)*139.7 ET -12 CB 106.1 DARK GUN (24TC4P)','','','ICE655','',NULL,NULL,392.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-640-7792',0x01,0,301.625,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(641,714,1,6,316,8,'ARO ROMAX QC5206 15X9.0-8*100/114.3 ET 0 CB 73.1 BLACK FACE MACHINED LIP','','','ICE656','',NULL,NULL,235.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-641-9929',0x01,0,181.28,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(642,710,1,6,316,8,'ARO ROMAX 7088 13X6.0-5*100 ET 10 CB 73.1 BLACK MACHINED FACE(BP)','','','ICE657','',NULL,NULL,193.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-642-5584',0x01,0,149.1,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(643,710,1,6,316,8,'ARO ROMAX 363 13X5.5-8*100/114.3 ET 20 CB 73.1 RED BP','','','ICE658','',NULL,NULL,193.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-643-6999',0x01,0,149.1,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(644,709,1,6,316,8,'ARO BELEN 1066 13X5.5-8*100/114.3 ET 30 CB 73.1 BLUE UNDERCUT BLACK MACHINED FACE','','','ICE659','',NULL,NULL,193.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-644-2598',0x01,0,149.075,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(645,709,1,6,316,8,'ARO BELEN SW2060 13X8.0-4*100 ET -7 CB 73.1 SILVER','','','ICE660','',NULL,NULL,199.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-645-9079',0x01,0,153.3,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(646,731,1,6,316,8,'ARO RW 803.162930 14X7.0-100/114.3X8 ET 0 CB 73.1 BLACK MACHINE','','','ICE661','',NULL,NULL,215.9,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-646-2165',0x01,0,166.1,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(647,711,1,6,316,8,'ARO ROMAX 6213 14X7.0-(8)*100/114.3 ET 0 CB 73.1 RED LINE BLACK(16TT4P1S)','','','ICE662','',NULL,NULL,218,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-647-2550',0x01,0,167.72,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(648,732,1,6,310,8,'LLANTA COMPASAL 215/45R17','','','ICE663','',NULL,NULL,165.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-648-5653',0x01,0,138,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(649,733,1,6,310,8,'LLANTA ZETUR 205/55R17','','','ICE664','',NULL,NULL,210,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-649-3120',0x01,0,175,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(650,698,1,6,310,8,'LLANTA YUANXING 100/80R17 POS','','','ICE665','',NULL,NULL,90,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-650-8517',0x01,0,75,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(651,698,1,6,310,8,'LLANTA YUANXING 250R17 DEL','','','ICE666','',NULL,NULL,78,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-651-2513',0x01,0,65,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(652,710,1,6,316,8,'ARO ROMAX 1440 13X6.0-(8)*100/114.3 ET 35 CB 73.1 BLACK MACHINE (16TT4P1S)','','','ICE667','',NULL,NULL,199.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-652-1128',0x01,0,153.72,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(653,710,1,6,316,8,'ARO ROMAX 5819 13X6.0-(8)X100/114.3 ET10 CB73.1 RED UNDERCUT (16TT4P1S)','','','ICE668','',NULL,NULL,778.9,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-653-1381',0x01,0,623.08,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(654,729,1,6,316,8,'ARO NISOSHI NS-091 13X6.0-8*100/114.3 ET 10 CB 73.1 MATT BLACK+RED','','','ICE669','',NULL,NULL,189.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-654-5021',0x01,0,150.77,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(655,729,1,6,316,8,'ARO NISOSHI NS-059 13X7.0-(8)*100/114.3 ET 0 CB 73.1 MCB+BRONZE(16TT4P1S)','','','ICE670','',NULL,NULL,196,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-655-9081',0x01,0,150.77,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(656,709,1,6,316,8,'ARO BELEN HX005 13X5.5-(8)*100/114.3 ET 35 CB 73.1 BLUE UNDERCUT BLACK (16TT4P1S)','','','ICE671','',NULL,NULL,199.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-656-4792',0x01,0,153.72,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(657,710,1,6,316,8,'ARO ROMAX QC2057 13X6.0-(8)X100/114.3 ET 10 CB 73.1 BLUE UNDERCUT (16TT4P1S)','','','ICE672','',NULL,NULL,774.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-657-4956',0x01,0,645.6,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(658,711,1,6,316,8,'ARO ROMAX QC1518 14X7.0-8*100/114.3 ET 0 CB 73.1 BLUE COATING LINE BLACK MACHINED','','','ICE673','',NULL,NULL,218,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-658-8426',0x01,0,167.72,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(659,711,1,6,316,8,'ARO ROMAX X1063 14X6.5-8*100/114.3 ET 0 CB 73.1 RED UNDERCUT BLACK MACHINE','','','ICE674','',NULL,NULL,218,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-659-6622',0x01,0,167.72,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(660,713,1,6,316,8,'ARO NISOSHI NS-093 14X6.0-8*100/114.3 ET 35 CB 73.1 MATT BLACK','','','ICE675','',NULL,NULL,218,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-660-4627',0x01,0,167.72,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(661,710,1,6,316,8,'ARO ROMAX 1459 13X7.0-8*100/114.3 ET 0 CB 73.1 UNDER BP','','','ICE676','',NULL,NULL,196,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-661-1797',0x01,0,150.77,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(662,647,1,6,317,8,'CAMARA NEXEN 16.5 RETRO','','','ICE677','',NULL,NULL,90,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-662-7181',0x01,0,75,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(663,734,1,6,310,8,'LLANTA ONIX 165/60R14','','','ICE678','',NULL,NULL,122.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-663-9660',0x01,0,120,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(664,703,1,6,310,8,'LLANTA LING LONG 185/70R13','','','ICE679','',NULL,NULL,2.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-664-9795',0x01,0,90,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(665,693,1,6,310,8,'LLANTA ONYX 165/65R14','','','ICE680','',NULL,NULL,94.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-665-8058',0x01,0,78.8,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(666,728,1,6,310,8,'LLANTA JK TYRE 12-16.5 IND & MIN JETTRAX HD TL MX 12 PR','','','ICE681','',NULL,NULL,1056,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-666-5552',0x01,0,880,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(667,681,1,6,310,8,'LLANTA COMFORSER 185/60R15','','','ICE682','',NULL,NULL,120,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-667-2805',0x01,0,100,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(668,704,1,6,309,8,'BATERÍA BULK BKNS60 12V 45AH G60','','','ICE683','',NULL,NULL,214.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-668-5857',0x01,0,153,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(669,698,1,6,310,8,'LLANTA YUANXING 5.00-12','','','ICE684','',NULL,NULL,120,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-669-7258',0x01,0,100,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(670,735,1,6,310,8,'LLANTA JOYROOAD 155/70R12','','','ICE685','',NULL,NULL,85,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-670-2192',0x01,0,68,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(671,736,1,6,310,8,'LLANTA MAZZINI 155/70R12','','','ICE686','',NULL,NULL,138,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-671-5944',0x01,0,115,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(672,737,1,6,309,8,'BBATERIA VARTA 36I ST 12V 780/420AMP G36','','','ICE687','',NULL,NULL,198,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-672-8405',0x01,0,3,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(673,738,1,6,310,8,'LLANTA FIREMAX 12.00R20 MIXTA FM18 TCF 20PR','','','ICE688','',NULL,NULL,847,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-673-6549',0x01,0,770,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(674,696,1,6,310,8,'LLANTA AUSTONE 235/70R16','','','ICE689','',NULL,NULL,213.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-674-3321',0x01,0,178,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(675,708,1,6,316,8,'ARO ROMAX 363 12X5.0-(4)*114.3 ET 20 CB 73.1 RED BP (16TT4P1S)TI','','','ICE690','',NULL,NULL,636,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-675-6820',0x01,0,530,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(676,707,1,6,316,8,'ARO BELEN SW200 12X5.5-(4)*114.3 ET -5 CB 73.1 BP (16TT4P1S)TI','','','ICE691','',NULL,NULL,175,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-676-4726',0x01,0,125.03,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(677,710,1,6,316,8,'ARO ROMAX JA179 13X8.0-4*100 ET -10 CB 73.1 BLPX','','','ICE692','',NULL,NULL,198.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-677-8577',0x01,0,152.91,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(678,711,1,6,316,8,'ARO ROMAX 708 14X6.5-8*100/114.3 ET 10 CB 73.1 BLACK MACHINED FACE','','','ICE693','',NULL,NULL,218.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-678-8670',0x01,0,168.12,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(679,711,1,6,316,8,'ARO ROMAX 1459 14X7.0-8*100/114.3 ET 0 CB 73.1 RED BLKM','','','ICE694','',NULL,NULL,218.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-679-3929',0x01,0,168.12,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(680,711,1,6,316,8,'ARO ROMAX 8155 14X6.0-(8)*100/114.3 ET 10 CB 73.1 BLACK MACHINED (16TT4P1S)','','','ICE695','',NULL,NULL,218.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-680-9728',0x01,0,168.12,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(681,739,1,6,316,8,'ARO RALLY 4.00-8 BAJAJ \"4 GRIS','','','ICE696','',NULL,NULL,46.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-681-5996',0x01,0,26,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(682,739,1,6,316,8,'ARO RALLY 6.00-8 BAJAJ \"6 PLATA','','','ICE697','',NULL,NULL,73.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-682-6775',0x01,0,49,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(683,739,1,6,316,8,'ARO RALLY 6.00-8 BAJAJ \"6 ROJO','','','ICE698','',NULL,NULL,73.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-683-7528',0x01,0,49,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(684,739,1,6,316,8,'ARO RALLY 6.00-8 BAJAJ \"6 AZUL','','','ICE699','',NULL,NULL,73.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-684-1881',0x01,0,49,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(685,739,1,6,316,8,'ARO RALLY 6.00-8 BAJAJ \"6 BLANCO','','','ICE700','',NULL,NULL,73.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-685-4203',0x01,0,49,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(686,739,1,6,316,8,'ARO RALLY 6.00-8 BAJAJ \"6 NEGRO','','','ICE701','',NULL,NULL,73.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-686-1125',0x01,0,49,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(687,677,1,6,309,8,'BATERIA KINGPOWER 55D23 12V 60AH 550 G35 R','','','ICE702','',NULL,NULL,230.9,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-687-2518',0x01,0,192.43,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(688,740,1,6,310,8,'LLANTA MIRAGE 195/60R15','','','ICE703','',NULL,NULL,120,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-688-8963',0x01,0,100,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(689,714,1,6,316,8,'ARO ROMAX QC5194 15X8-(8)100/114.3 BLACK LIP (16TT4P1S)','','','ICE704','',NULL,NULL,240.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-689-8737',0x01,0,184.98,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(690,714,1,6,316,8,'ARO ROMAX QC1841 15X8.0-(8)*100/114.3 ET 0 CB 73.1 GREEN MILLING GLOSS BLACK (16TT4P1S)','','','ICE705','',NULL,NULL,240.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-690-9965',0x01,0,184.98,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(691,714,1,6,316,8,'ARO ROMAX DX616 15X8.25-8*100/114.3 ET 0 CB 73.1 HYPER BLACK','','','ICE706','',NULL,NULL,2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-691-1830',0x01,0,199.78,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(692,710,1,6,316,8,'ARO ROMAX C161 13X6.0-(8)*100/114.3 ET 0 CB 73.1 BLACK (16TT4P1S)','','','ICE707','',NULL,NULL,199.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-692-5580',0x01,0,153.72,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(693,712,1,6,316,8,'ARO BELEN SW2155 14X7.0-(4)*100 BLACK (16T T4P1S)','','','ICE708','',NULL,NULL,224,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-693-8812',0x01,0,172.31,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(694,711,1,6,316,8,'ARO ROMAX 1729 14X6.0-(8)*100/114.3 ET 10 CB 73.1 BLUE UNDERCUT (16TT4P1S)','','','ICE709','',NULL,NULL,242.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-694-2868',0x01,0,186.45,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(695,709,1,6,316,8,'ARO BELEN SW275 13X6.0-(8)X100/114.3 ET 0 CB 73.1 SILVER (14TT4P1S)','','','ICE710','',NULL,NULL,798.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-695-4232',0x01,0,638.8,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(696,715,1,6,316,8,'ARO BELEN SW275 15X9.0-(10)X100/114.3 ET 0 CB 73.1 BLACK LP (20TT4P1S)','','','ICE711','',NULL,NULL,1009.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-696-6210',0x01,0,807.54,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(697,714,1,6,316,8,'ARO ROMAX AK195 15X8.25-(10)*100/114.3 ET 10 CB 73.1 BMF (20TT4P1S)','','','ICE712','',NULL,NULL,239.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-697-3014',0x01,0,184.14,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(698,714,1,6,316,8,'ARO ROMAX 272 15X9.0-(8)*100/114.3 ET 0 CB 73.1 BMF (16TT4P1S)','','','ICE713','',NULL,NULL,239.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-698-4120',0x01,0,184.14,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(699,714,1,6,316,8,'ARO ROMAX 5490 15X7.0-(10)*100/114.3 ET 35 CB 73.1 SATTIN MATTE BLACK (20TT4P1S)','','','ICE714','',NULL,NULL,237.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-699-7030',0x01,0,182.45,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(700,714,1,6,316,8,'ARO ROMAX 7084 15X7.0-(10)*100/114.3 ET 35 CB 73.1 SATTIN MATTE BLACK (20TT4P1S)','','','ICE715','',NULL,NULL,237.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-700-9859',0x01,0,182.45,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(701,706,1,6,316,8,'ARO ROMAX JT227 17X9.0-(5)*114.3 ET -12 CB 73.1 RED MILLING (20TT4P1S)','','','ICE716','',NULL,NULL,402,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-701-6211',0x01,0,309.2,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(702,706,1,6,316,8,'ARO ROMAX FBX203 17X9.0-(5)*114.3 ET 0 CB 73.1 GLOSS BLACK MILLING (20TT4P1S)','','','ICE717','',NULL,NULL,402,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-702-1821',0x01,0,309.2,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(703,706,1,6,316,8,'ARO ROMAX QC1820 17X9.0-(6)*139.7 ET 0 CB 108 SATTIN MATTE BLACK MILLING (24TC4P)','','','ICE718','',NULL,NULL,402,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-703-4968',0x01,0,309.2,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(704,706,1,6,316,8,'ARO ROMAX 1526 17X9.0-(6)*139.7 ET -5 CB 106.1 BLKM (24TC4P)','','','ICE719','',NULL,NULL,396.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-704-2857',0x01,0,304.98,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(705,706,1,6,316,8,'ARO ROMAX X1171 17X9.0-(6)*139.7 ET 0 CB 106.1 BLACK MACHINED FACE (24TC4P)','','','ICE720','',NULL,NULL,402,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-705-4227',0x01,0,309.2,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(706,706,1,6,316,8,'ARO ROMAX X1060 17X9.0-(10)*100/114.3 AND LIP CHROME RIVETS (20TT4P1S)','','','ICE721','',NULL,NULL,349.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-706-7452',0x01,0,268.64,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(707,706,1,6,316,8,'ARO ROMAX 1008 17X9.25-(10)*100/114.3 ET 18 CB 73.1 HYPER BLACK (20TT4P1S)','','','ICE722','',NULL,NULL,352.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-707-5859',0x01,0,271.18,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(708,706,1,6,316,8,'ARO ROMAX JC253 17X9.0-(5)*100/5*114.3 ET 5 CB 73.1 BLKM (20TT4P1S)','','','ICE723','',NULL,NULL,347,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-708-7712',0x01,0,266.95,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(709,706,1,6,316,8,'ARO ROMAX 2006 17X9.0-(6)*139.7 ET -10 CB 106.1 SATTIN MATTE BLACK (24TC4P)','','','ICE724','',NULL,NULL,391,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-709-1298',0x01,0,300.75,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(710,706,1,6,316,8,'ARO ROMAX QC1852 17X9.0-6*139.7 ET 0 CB 106.1 SATTIN MATTE BLACK MILLING WINDOWS','','','ICE725','',NULL,NULL,402,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-710-4028',0x01,0,309.2,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(711,706,1,6,316,8,'ARO ROMAX 837 17X9.0-6*139.7 ET 0 CB 106.1 SATTIN MATTE BLACK BRONCE RIVETS','','','ICE726','',NULL,NULL,396.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-711-2513',0x01,0,304.98,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(712,741,1,6,316,8,'ARO WILCROXX 1006 17X9.0-(6)*139.7 ET 0 CB 110.1 MCB (24TC4P)','','','ICE727','',NULL,NULL,396.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-712-3077',0x01,0,304.98,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(713,714,1,6,316,8,'ARO ROMAX QC1852 15X8.0-5*114.3 ET 0 CB 73.1 BLACK MACHINED FACE BLACK LIP','','','ICE728','',NULL,NULL,292.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-713-4163',0x01,0,224.7,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(714,714,1,6,316,8,'ARO ROMAX QC2043 15X8.0-(5)*114.3 ET -10 CB 73.1 BLACK MACHINED (20TC4P)','','','ICE729','',NULL,NULL,292.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-714-9333',0x01,0,224.7,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(715,671,1,6,309,8,'BATERIA TOPOWER TPEFB110D 12V G100B L','','','ICE730','',NULL,NULL,758.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-715-1568',0x01,0,505.61,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(716,671,1,6,309,8,'BATERIA TOPOWER TPEFB95D 12V G65II L','','','ICE731','',NULL,NULL,706.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-716-1710',0x01,0,470.99,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(717,671,1,6,309,8,'BATERIA TOPOWER TPEFB70D 12V 70AH G48 L','','','ICE732','',NULL,NULL,560.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-717-1993',0x01,0,373.39,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(718,671,1,6,309,8,'BATERIA TOPOWER TPQ85 12V G35 L','','','ICE733','',NULL,NULL,455.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-718-1330',0x01,0,303.73,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(719,671,1,6,309,8,'BATERIA TOPOWER TPN140 12V G120 L','','','ICE734','',NULL,NULL,600.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-719-3014',0x01,0,428.73,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(720,671,1,6,309,8,'BATERIA TOPOWER TP170D 12V','','','ICE735','',NULL,NULL,719.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-720-4190',0x01,0,479.58,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(721,671,1,6,309,8,'BATERIA TOPOWER MT40D 12V G36 L','','','ICE736','',NULL,NULL,180.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-721-3847',0x01,0,138.59,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(722,742,1,6,317,8,'CAMARA GETOP RUBBER 17.5R25','','','ICE737','',NULL,NULL,360,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-722-5981',0x01,0,300,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(723,639,1,6,320,8,'TUERCA CONICA 14X1.50 (CABEZAL)','','','ICE738','',NULL,NULL,10,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-723-7943',0x01,0,1,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(724,677,1,6,309,8,'BATERIA KINGPOWER 105D31 12V 90AH 550 G27 R','','','ICE739','',NULL,NULL,331.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-724-8736',0x01,0,264.98,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(725,642,1,6,310,8,'LLANTA VIKRANT 8.25-16 POS','','','ICE740','',NULL,NULL,828,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-725-4885',0x01,0,690,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(726,743,1,6,310,8,'LLANTA KRAIN 275-17','','','ICE741','',NULL,NULL,120,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-726-9714',0x01,0,100,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(727,744,1,6,333,8,'BAERIA HANZO YTX5L-BS 12V-5AH','','','ICE742','',NULL,NULL,120,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-727-2511',0x01,0,60,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(728,745,1,6,309,8,'BATERIA HONGJU 12N5L-BS','','','ICE743','',NULL,NULL,72,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-728-7058',0x01,0,60,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(729,727,1,6,309,8,'BATERIA ENERMAX YTX9 12V','','','ICE744','',NULL,NULL,73.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-729-5386',0x01,0,61,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(730,647,1,6,317,8,'CAMARA NEXEN 12.00R24','','','ICE745','',NULL,NULL,132,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-730-9060',0x01,0,110,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(731,746,1,6,314,8,'ACTIVACIÓN DE BATERIA','','','ICE746','',NULL,NULL,12,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-731-1487',0x01,0,0.1,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(732,621,1,6,309,8,'BATERIA RECORD U1R200 12V 200AMP GU1 L','','','ICE747','',NULL,NULL,178.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-732-2087',0x01,0,118.97,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(733,705,1,6,318,8,'SILICONA 3BSI FRESA MAX 120ML','','','ICE748','',NULL,NULL,6.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-733-3567',0x01,0,2.1,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(734,705,1,6,318,8,'SILICONA 3BSI COCO MAX 120ML','','','ICE749','',NULL,NULL,6.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-734-5672',0x01,0,2.1,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(735,705,1,6,318,8,'SILICONA 3BSI CHICLE MAX 120ML','','','ICE750','',NULL,NULL,6.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-735-7219',0x01,0,2.1,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(736,693,1,6,310,8,'LLANTA ONYX 5.00-12 NY07','','','ICE751','',NULL,NULL,100.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-736-9173',0x01,0,84,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(737,647,1,6,317,8,'CÁMARA NEXEN 7.00-16 TR75A','','','ICE752','',NULL,NULL,75,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-737-6872',0x01,0,50,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(738,747,1,6,310,8,'LLANTA ROADBOSS 245/75R16 MT X6 120/116Q 10PR','','','ICE753','',NULL,NULL,357.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-738-4316',0x01,0,310.5,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(739,701,1,6,310,8,'LLANTA KAPSEN 275/45R20 HP RS26 110W','','','ICE754','',NULL,NULL,307.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-739-6913',0x01,0,246.1,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(740,748,1,6,310,8,'LLANTA FARROAD 155/70R12 ECOPLUS 73Q TL','','','ICE755','',NULL,NULL,87.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-740-3938',0x01,0,69.75,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(741,708,1,6,316,8,'ARO ROMAX 7088 12X5.0-(4)X114.3 ET 15 CB 73.1 RED CLEAR (16TT4P1S)TI','','','ICE756','',NULL,NULL,174.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-741-6121',0x01,0,134.25,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(742,708,1,6,316,8,'ARO ROMAX 5001 12X5.0-(4)X114.3 ET 15 CB 73.1 BP (14TT4P1S)TI','','','ICE757','',NULL,NULL,174.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-742-7505',0x01,0,134.25,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(743,708,1,6,316,8,'ARO ROMAX 6051 12X5.0-(4)X114.3 ET 20 CB 73.1 RED BP (16TT4P1S)TI','','','ICE758','',NULL,NULL,174.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-743-2167',0x01,0,134.25,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(744,708,1,6,316,8,'ARO ROMAX 363 12X5.0-(4)X114.3 ET 20 CB 73.1 BLUE BP (16TT4P1S)TI','','','ICE759','',NULL,NULL,636,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-744-5975',0x01,0,530,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(745,729,1,6,316,8,'ARO NISOSHI NS-093 13X5.5-(8)X100/114.3 ET 30 CB 73.1 MB+RED (16TT4P1S)','','','ICE760','',NULL,NULL,210.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-745-8482',0x01,0,162.09,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(746,749,1,6,316,8,'ARO ROMAX QC1680 13X7.0-8X100/114.3 ET 0 CB 73.1 BLACK FACE MACHINED LIP','','','ICE761','',NULL,NULL,0,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-746-1302',0x01,0,0,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(747,710,1,6,316,8,'ARO ROMAX E137 13X6.0-(8)*100-114.3 ET 15 CB 73.1 BLACK MAHCINE (16TT4P1S)','','','ICE762','',NULL,NULL,208.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-747-1316',0x01,0,160.35,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(748,749,1,6,316,8,'ARO ROMAX X132020 13X7.0-(8)X100/114.3 ET 0 CB 73.1 BLACK MACHINED (16TT4P1S)','','','ICE763','',NULL,NULL,798.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-748-6423',0x01,0,638.8,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(749,749,1,6,316,8,'ARO ROMAX 6037 13X6.0-8X100/114.3 ET 15 CB 73.1 BLKM','','','ICE764','',NULL,NULL,0,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-749-9478',0x01,0,0,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(750,749,1,6,316,8,'ARO ROMAX QC2056 13X6.0-(8)X100/114.3 ET 25 CB 73.1 BLACK MACHINED (16TT4P1S)','','','ICE765','',NULL,NULL,753.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-750-1721',0x01,0,638.8,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(751,710,1,6,316,8,'ARO ROMAX 5087 13X6.0-(8)X100/114.3 ET 0 CB 73.1 BP (16TT4P1S)','','','ICE766','',NULL,NULL,206,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-751-6581',0x01,0,158.45,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(752,749,1,6,316,8,'ARO ROMAX 1459 13X7.0-(8)X100/114.3 ET 0 CB 73.1 GOLD BLACK (16TT4P1S)','','','ICE767','',NULL,NULL,213,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-752-5381',0x01,0,163.83,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(753,750,1,6,316,8,'ARO QC QC1518 13X6.5-(8)X100/114.3 ET 0 CB 73.1 MB (16TT4P1S)','','','ICE768','',NULL,NULL,208.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-753-8101',0x01,0,160.35,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(754,712,1,6,316,8,'ARO BELEN SW AF027 14X6.0-(8)X100/114.3 GOLD UNDERCUT (16TT4P1S)','','','ICE769','',NULL,NULL,242.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-754-2696',0x01,0,186.45,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(755,711,1,6,316,8,'ARO ROMAX QC1168A 14X6.0-(8)X100/114.3 BRONCE CLEAR (16TT4P1S)','','','ICE770','',NULL,NULL,242.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-755-8892',0x01,0,186.45,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(756,711,1,6,316,8,'ARO ROMAX 6164 14X6.0-(8)X100/114.3 ET 0 CB 73.1 BLACK MACHINED (16TT4P1S)','','','ICE771','',NULL,NULL,240.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-756-4300',0x01,0,184.71,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(757,711,1,6,316,8,'ARO ROMAX 1484 14X7.0-(8)X100/114.3 ET 20 CB 73.1 BP (16TT4P1S)','','','ICE772','',NULL,NULL,240.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-757-7090',0x01,0,184.71,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(758,711,1,6,316,8,'ARO ROMAX QC826 14X7.0-(8)X100-114.3 ET 10 CB 73.1 BLACK MACHINED FACE (16TT4P1S)','','','ICE773','',NULL,NULL,909,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-758-3978',0x01,0,727.2,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(759,711,1,6,316,8,'ARO ROMAX 826 14X7.0-(8)X100/114.3 ET 20 CB 73.1 BLACK FACE MACHINED LIP (16TT4P1S)','','','ICE774','',NULL,NULL,242.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-759-8961',0x01,0,186.45,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(760,711,1,6,316,8,'ARO ROMAX 1291 14X7.0-(4)X100 ET -25 CB 73.1 BLACK MACHINED (16T T4P1S)','','','ICE775','',NULL,NULL,240.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-760-9732',0x01,0,184.71,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(761,751,1,6,316,8,'ARO RW 803.163586 14X6.0-100/114.3X8 ET 35 CB 73.1 BLACK MACHINE/UNDERCUT POLISHED','','','ICE776','',NULL,NULL,0,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-761-2894',0x01,0,0,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(762,711,1,6,316,8,'ARO ROMAX 8032 14X6.0-(8)X100/114.3 ET 20 CB 73.1 BLUE UNDERCUT BLACK MACHINED FACE 16TT4P1S)','','','ICE777','',NULL,NULL,242.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-762-3563',0x01,0,186.45,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(763,752,1,6,316,8,'ARO ROMAX 1508 14*7.0-8*100/114.3 ET -10 CB 73.1 U BP','','','ICE778','',NULL,NULL,0,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-763-7607',0x01,0,0,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(764,711,1,6,316,8,'ARO ROMAX QC662 14X6.0-(8)X100/114.3 ET 20 CB 73.1 BLUE LINE BLACK (16TT4P1S','','','ICE779','',NULL,NULL,242.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-764-2987',0x01,0,186.45,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(765,752,1,6,316,8,'ARO ROMAX 1285 14X6.0-(8)X100/114 ET 35 CB 73.1 RED BP','','','ICE780','',NULL,NULL,242.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-765-2057',0x01,0,186.45,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(766,740,1,6,310,8,'LLANTA MIRAGE 225/45R18 MR-182 102W','','','ICE781','',NULL,NULL,198,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-766-7607',0x01,0,165,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(767,721,1,6,310,8,'LLANTA WINDA 225/40R18 (225/40ZR18) WH18 TL 92W','','','ICE782','',NULL,NULL,200,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-767-1204',0x01,0,160,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(768,615,1,6,309,8,'BATERIA ENERJET NS40 12V 40AH TORITO G40','','','ICE783','',NULL,NULL,194.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-768-1419',0x01,0,155.5,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(769,753,1,6,318,8,'KIT WHEELS (16)T.T-12X1.50 + (4)V-TR4','','','ICE784','',NULL,NULL,64,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-769-2052',0x01,0,0.1,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(770,753,1,6,318,8,'KIT WHEELS (16)T.T-10X1.25 + (4)V-TR4','','','ICE785','',NULL,NULL,64,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-770-9312',0x01,0,0.1,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(771,753,1,6,318,8,'KIT WHEELS (20)T.T-12X1.50 + (4)V-TR4','','','ICE786','',NULL,NULL,80,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-771-4797',0x01,0,2.1,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(772,753,1,6,318,8,'KIT DE ACCESORIOS (20TC4P)','','','ICE787','',NULL,NULL,86.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-772-6370',0x01,0,2.1,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(773,753,1,6,318,8,'KIT WHEELS (24)T.C-12X1.50 + (4)V-TR4','','','ICE788','',NULL,NULL,96,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-773-3357',0x01,0,0.1,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(774,666,1,6,310,8,'LLANTA CHAOYANG 185/70R14 MAX T70 88S','','','ICE789','',NULL,NULL,136.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-774-4144',0x01,0,115,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(775,701,1,6,310,8,'LLANTA KAPSEN 225/70R16 AT','','','ICE790','',NULL,NULL,237.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-775-9233',0x01,0,198,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(776,747,1,6,310,8,'LLANTA ROADBOSS LT265/70R16 MT 117/114Q X6 8PR','','','ICE791','',NULL,NULL,368,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-776-2341',0x01,0,320,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(777,754,1,6,310,8,'LLANTA HILO 195/60R14','','','ICE792','',NULL,NULL,294,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-777-6983',0x01,0,245,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(778,755,1,6,310,8,'LLANTA ZEXTOUR 165/65R13 ES655 77T','','','ICE793','',NULL,NULL,96,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-778-4099',0x01,0,80,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(779,663,1,6,310,8,'LLANTA ZMAX LT235/75R15 MT MAXATRAVES','','','ICE794','',NULL,NULL,286.9,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-779-9603',0x01,0,239.06,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(780,756,1,6,309,8,'BATERIA OVERALL 12N7-3A','','','ICE795','',NULL,NULL,78,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-780-8780',0x01,0,65,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(781,757,1,6,310,8,'LLANTA TRIANGLE 215/55R18 TH202','','','ICE796','',NULL,NULL,241.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-781-5657',0x01,0,201.44,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(782,745,1,6,309,8,'BATERIA HONGJU 12N7L-BS','','','ICE797','',NULL,NULL,86.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-782-1713',0x01,0,70,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(783,734,1,6,310,8,'LLANTA ANNAITE 205/55R16 94XLV','','','ICE798','',NULL,NULL,145.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-783-7226',0x01,0,121.44,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(784,662,1,6,310,8,'LLANTA WIDEWAY LT265/70R17 MT POWERWAY','','','ICE799','',NULL,NULL,445.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-784-9115',0x01,0,371,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(785,652,1,6,309,8,'BATERIA MOURA ME150BI 12V 150AH G4D /ME1080BI R','','','ICE800','',NULL,NULL,725.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-785-4978',0x01,0,558.2,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(786,652,1,6,309,8,'BATERIA MOURA ME90TI 12V 90AH G27 /ME810TI R','','','ICE801','',NULL,NULL,416,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-786-8282',0x01,0,320,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(787,621,1,6,309,8,'BATERIA RECORD RC 80 12V 850AMP G24 R','','','ICE802','',NULL,NULL,378.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-787-1937',0x01,0,315.62,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(788,745,1,6,309,8,'BATERIA HONGJU YTX7L-BS','','','ICE803','',NULL,NULL,78,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-788-1332',0x01,0,65,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(789,623,1,6,309,8,'BATERIA ENERLITE 58043MF 12V80AH','','','ICE804','',NULL,NULL,0,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-789-3993',0x01,0,0,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(790,758,1,6,317,8,'CAMARA JK 14.00','','','ICE805','',NULL,NULL,0,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-790-6118',0x01,0,20,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(791,727,1,6,309,8,'BATERIA MOTO ENERMAX YTX7A 12V','','','ICE806','',NULL,NULL,63.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-791-7241',0x01,0,53,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(792,759,1,6,317,8,'CAMARA 12.00-24','','','ICE807','',NULL,NULL,156,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-792-1456',0x01,0,130,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(793,740,1,6,310,8,'LLANTA MIRAGE 155/70R12 MR-162','','','ICE808','',NULL,NULL,175.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-793-1918',0x01,0,76,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(794,615,1,6,309,8,'BATERIA ENERJET 17T114 (TE) 12V 1218AMP G31 L','','','ICE809','',NULL,NULL,538.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-794-6821',0x01,0,413.98,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(795,745,1,6,309,8,'BATERIA MOTO HONGJU YTX7A-BS','','','ICE810','',NULL,NULL,0,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-795-3596',0x01,0,75,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(796,760,1,6,309,8,'BATERIA XCCED G40 12V 38AH R','','','ICE811','',NULL,NULL,30,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-796-9212',0x01,0,25,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(797,721,1,6,310,8,'LLANTA WINDA P265/65R17 AT 112S','','','ICE812','',NULL,NULL,336.9,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-797-1632',0x01,0,280.72,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(798,655,1,6,310,8,'LLANTA DOUPRO 12.00R20 TRAC ST852 20PR MINERA TCF','','','ICE813','',NULL,NULL,960,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-798-5660',0x01,0,800,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(799,693,1,6,310,8,'LLANTA ONYX 12.00R20 MIXTA HO105 TCF 20PR','','','ICE814','',NULL,NULL,969.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-799-2614',0x01,0,807.56,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(800,696,1,6,310,8,'LLANTA AUSTONE LT265/65R17 MT MASPIRE 120/117Q PR','','','ICE815','',NULL,NULL,386,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-800-1608',0x01,0,321.68,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(801,755,1,6,310,8,'LLANTA ZEXTOUR 12.00R20 MIXTA SUPERMIXTA ZR18+20PR TCF','','','ICE816','',NULL,NULL,1003.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-801-6895',0x01,0,836,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(802,727,1,6,309,8,'BATERIA MOTO ENERMAX YTX4L','','','ICE817','',NULL,NULL,51.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-802-7210',0x01,0,43,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(803,676,1,6,309,8,'BAT SUNTEC 95D31 12V 80AH-670 G27 R','','','ICE818','',NULL,NULL,320.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-803-2849',0x01,0,256.2,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(804,676,1,6,309,8,'BAT SUNTEC MFU1 12V 19AH-250 G1 L','','','ICE819','',NULL,NULL,146.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-804-4165',0x01,0,116.9,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(805,727,1,6,309,8,'BATERIA DE MOTO ENERMAX 12N9-3B','','','ICE820','',NULL,NULL,108,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-805-1028',0x01,0,90,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(806,615,1,6,309,8,'BATERÍA ENERJET 19N128 12V 1250AMP G100 R','','','ICE821','',NULL,NULL,604.9,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-806-9772',0x01,0,483.88,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(807,641,1,6,309,8,'BATERÍA JJS 99D27 12V 80AH G27 L','','','ICE822','',NULL,NULL,285,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-807-7636',0x01,0,150,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(808,676,1,6,309,8,'BAT SUNTEC 42B19P 12V 38AH-340 G40 L','','','ICE823','',NULL,NULL,158.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-808-1640',0x01,0,126.9,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(809,761,1,6,318,8,'SEGURO DE BATERÍA TIPO UÑA','','','ICE824','',NULL,NULL,19.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-809-6176',0x01,0,6.5,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(810,663,1,6,310,8,'LLANTA ZMAX 195/65R15','','','ICE825','',NULL,NULL,120,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-810-2189',0x01,0,100,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(811,693,1,6,310,8,'LLANTA ONYX LT245/75R16 MT 120/116Q 10PR','','','ICE826','',NULL,NULL,0,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-811-5267',0x01,0,278.4,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(812,701,1,6,310,8,'LLANTA KAPSEN 195R14 RS01 TL 8PR','','','ICE827','',NULL,NULL,183.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-812-5384',0x01,0,147,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(813,734,1,6,310,8,'LLANTA ANNAITE 215/55R18','','','ICE828','',NULL,NULL,264,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-813-8897',0x01,0,220,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(814,732,1,6,310,8,'LLANTA COMPASAL 205/55R15 BLAZER HP 88V TL','','','ICE829','',NULL,NULL,150,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-814-7168',0x01,0,120,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(815,762,1,6,310,8,'LLANTA GTRADIAL LT225/70R16 AT ADVENTURO OWL 6PR TL','','','ICE830','',NULL,NULL,283.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-815-4794',0x01,0,227,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(816,763,1,6,317,8,'CAMARA TSK 130/60-13','','','ICE831','',NULL,NULL,18,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-816-7667',0x01,0,15,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(817,676,1,6,309,8,'BAT SUNTEC 55D23 12V 60AH-550 G35 R','','','ICE832','',NULL,NULL,256.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-817-7038',0x01,0,197.2,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(818,734,1,6,310,8,'LLANTA ANNAITE 195R15C AN900 106/104R 8PR CH','','','ICE833','',NULL,NULL,193.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-818-9441',0x01,0,155,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(819,622,1,6,309,8,'BATERIA ETNA S-1219 12V 130AH-855 G120 L','','','ICE834','',NULL,NULL,664,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-819-7882',0x01,0,510.79,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(820,623,1,6,309,8,'BATERIA ENERLITE S-1219','','','ICE835','',NULL,NULL,612.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-820-7584',0x01,0,490,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(821,764,1,6,316,8,'ARO ROMAX 7088 12X5.0-(4)X114.3 ET 15 CB 73.1 BLACK FACE(16TT4P1S)TI','','','ICE836','',NULL,NULL,648.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-821-8176',0x01,0,564,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(822,764,1,6,316,8,'ARO ROMAX 339C 12X4.5-(4)X114.3 ET 15 CB 73.1 VIOLET BP','','','ICE837','',NULL,NULL,662.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-822-2050',0x01,0,530,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(823,709,1,6,316,8,'ARO BELEN SW2060 13X8.0-(4)X100 ET -7 CB 73.1 BLP','','','ICE838','',NULL,NULL,819.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-823-3130',0x01,0,666,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(824,709,1,6,316,8,'ARO BELEN SW1210 13X6.0-(8)X100/114.3 ET 0 CB 73.1 SILVER','','','ICE839','',NULL,NULL,774.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-824-8233',0x01,0,645.6,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(825,749,1,6,316,8,'ARO ROMAX 6093 13X7.0-(4)X100/4*114.3 ET 0 CB 73.1 BLUE BP','','','ICE840','',NULL,NULL,774.7,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-825-8580',0x01,0,645.6,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(826,749,1,6,316,8,'ARO ROMAX C173S 13X6.0-(8)X100-114.3 ET 15 CB 73.1 MATTE BRONZE','','','ICE841','',NULL,NULL,758.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-826-1095',0x01,0,632,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(827,749,1,6,316,8,'ARO ROMAX QC5194 13X8.0-(8)X100-114.3 ET -7 CB 73.1 BLACK FACE MACHINED LIP','','','ICE842','',NULL,NULL,785.9,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-827-2457',0x01,0,666,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(828,749,1,6,316,8,'ARO ROMAX 1459 13X7.0-(8)X100/114.3 ET 0 CB 73.1 SILVER','','','ICE843','',NULL,NULL,766.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-828-7178',0x01,0,638.8,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(829,709,1,6,316,8,'ARO BELEN SW1753 13X6.0-(8)X100/114.3 ET 0 CB 73.1 ORANGE CLEAR COATING FACE','','','ICE844','',NULL,NULL,766.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-829-4693',0x01,0,638.8,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(830,752,1,6,316,8,'ARO ROMAX 1569 14X7.0-(8)X100/114.3 ET -10 CB 73.1 BLP','','','ICE845','',NULL,NULL,917.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-830-6156',0x01,0,734,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(831,752,1,6,316,8,'ARO ROMAX 144029 14X6.0-(8)X100/114.3 BLUE CLEAR COATING FACE','','','ICE846','',NULL,NULL,896.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-831-9791',0x01,0,717,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(832,711,1,6,316,8,'ARO ROMAX 1546 14X6.0-(4)X100 ET 35 CB 73.1 BP','','','ICE847','',NULL,NULL,945.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-832-1841',0x01,0,727.2,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(833,714,1,6,316,8,'ARO ROMAX 8052B 15X8.0-(10)X100/114.3 ET -10 CB 73.1 RED MILL','','','ICE848','',NULL,NULL,1263.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-833-9188',0x01,0,972,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(834,714,1,6,316,8,'ARO ROMAX X1276 15X7.0-(10)X100/114.3 ET 0 CB 73.1 BLACK MACHINED FACE(BP)','','','ICE849','',NULL,NULL,1015.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-834-8992',0x01,0,812.2,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(835,714,1,6,316,8,'ARO ROMAX 1434 15X6.5-(8)X100/114.3 ET 35 CB 73.1 HB','','','ICE850','',NULL,NULL,1015.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-835-9426',0x01,0,812.2,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(836,765,1,6,316,8,'ARO NISOSHI NS-024(X151240) 15X8.25-(8)X100/114.3 ET -12 CB 73.1 MB+RED UC','','','ICE851','',NULL,NULL,1055.9,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-836-2827',0x01,0,812.2,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(837,766,1,6,316,8,'ARO BAJAJ 4.00-8 2.5\" PLAT+AZUL DEPORT','','','ICE852','',NULL,NULL,62.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-837-6072',0x01,0,46,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(838,766,1,6,316,8,'ARO BAJAJ 4.00-8 2.5\" PLAT+BLANCO DEPORT','','','ICE853','',NULL,NULL,62.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-838-7873',0x01,0,46,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(839,766,1,6,316,8,'ARO BAJAJ 4.00-8 2.5\" PLAT+ROJO DEPORT','','','ICE854','',NULL,NULL,62.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-839-9948',0x01,0,46,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(840,632,1,6,316,8,'ARO BAJAJ 4.00-8 2.5\" BLANCO+AZUL DEPORT','','','ICE855','',NULL,NULL,61.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-840-2474',0x01,0,39.5,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(841,632,1,6,316,8,'ARO BAJAJ 4.00-8 2.5\" NEGRO+ROJO DEPORT','','','ICE856','',NULL,NULL,61.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-841-4842',0x01,0,39.5,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(842,633,1,6,310,8,'LLANTA S&M 5.00-12 8PR FURGON','','','ICE857','',NULL,NULL,120.9,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-842-6677',0x01,0,93,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(843,634,1,6,310,8,'LLANTA ARL 4.00-8 8PR PLATINUM','','','ICE858','',NULL,NULL,98.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-843-8098',0x01,0,76,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(844,615,1,6,309,8,'BATERIA ENERJET 13DA84 12V 908AMP G35 L','','','ICE859','',NULL,NULL,0,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-844-7644',0x01,0,0,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(845,615,1,6,309,8,'BATERIA ENERJET 33P234 12V 2030AMP G8D L','','','ICE860','',NULL,NULL,0,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-845-2536',0x01,0,0,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(846,673,1,6,310,8,'LLANTA JSL 4.00-8 TT 15X SET/C','','','ICE861','',NULL,NULL,74.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-846-8208',0x01,0,57,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(847,673,1,6,310,8,'LLANTA JSL 4.00-8 TT 15S SET/C','','','ICE862','',NULL,NULL,87.1,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-847-6869',0x01,0,67,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(848,673,1,6,310,8,'LLANTA JSL 4.00-8 TT 15R SET/C','','','ICE863','',NULL,NULL,93.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-848-3193',0x01,0,67,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(849,673,1,6,310,8,'LLANTA JSL 4.00-8 TT 15J SET/C','','','ICE864','',NULL,NULL,88.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-849-3309',0x01,0,57,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(850,673,1,6,310,8,'LLANTA JSL 5.00-12 16F','','','ICE865','',NULL,NULL,126.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-850-6049',0x01,0,101,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(851,673,1,6,310,8,'LLANTA JSL 2.75-17 TL 09E','','','ICE866','',NULL,NULL,86.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-851-8437',0x01,0,56,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(852,673,1,6,310,8,'LLANTA JSL 3.00-17 TL 8D','','','ICE867','',NULL,NULL,94.6,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-852-4215',0x01,0,61,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(853,673,1,6,310,8,'LLANTA JSL 2.75-18 TL 81A','','','ICE868','',NULL,NULL,82.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-853-1789',0x01,0,55,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(854,673,1,6,310,8,'LLANTA JSL 3.00-18 TL 21A-2','','','ICE869','',NULL,NULL,100.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-854-7928',0x01,0,65,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(855,705,1,6,318,8,'LIQUIDO DE FRENO 3BSI DOT3 4OZ','','','ICE870','',NULL,NULL,9.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-855-7914',0x01,0,3.7,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(856,705,1,6,318,8,'CREMA PARA MANOS 3BSI 1LT CITRICO','','','ICE871','',NULL,NULL,30.3,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-856-2672',0x01,0,15.15,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(857,705,1,6,318,8,'SILICONA 3BSI CRISTAL CHICLE 280ML SPRAY','','','ICE872','',NULL,NULL,13.2,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-857-7057',0x01,0,6,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(858,767,1,6,310,8,'LLANTA STEELIES 205/55R15','','','ICE873','',NULL,NULL,168,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-858-1859',0x01,0,140,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(859,768,1,6,310,8,'LLANTA KUMHO 225/65R17 SOLUS TA21 TL 102T','','','ICE874','',NULL,NULL,350.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-859-2101',0x01,0,292,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(860,755,1,6,310,8,'LLANTA ZEXTOUR 205/55R16 ES655 CONFORT TL 91V','','','ICE875','',NULL,NULL,150,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-860-5010',0x01,0,125,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(861,749,1,6,316,8,'ARO ROMAX 6036 13X6.0-(8)X100/114.3 ET 5 CB 73.1','','','ICE876','',NULL,NULL,785.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-861-2956',0x01,0,628.4,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(862,769,1,6,316,8,'ARO ROMAX X1276 15X7.0-(10)X100/114.3 ET 0 CB 73.1','','','ICE877','',NULL,NULL,996.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-862-5996',0x01,0,797.4,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(863,749,1,6,316,8,'ARO ROMAX 8113 13X6\"-(8)X100/114.3 ET 0 CB 73.1 RED LINE','','','ICE878','',NULL,NULL,802.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-863-5382',0x01,0,641.92,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(864,769,1,6,316,8,'ARO ROMAX 1518 15X8.0\"-(8)X100/114.3 ET 20 CB 73.1','','','ICE879','',NULL,NULL,1009.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-864-6595',0x01,0,807.54,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(865,769,1,6,316,8,'ARO ROMAX QC826 15X8.0-(10)X100/114.3 ET 0 CB 73.1','','','ICE880','',NULL,NULL,1008.8,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-865-3763',0x01,0,807,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(866,769,1,6,316,8,'ARO ROMAX DX649 15X8.25-(10)X100/114.3 ET 15 CB 73.1','','','ICE881','',NULL,NULL,1009.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-866-8559',0x01,0,807.54,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(867,769,1,6,316,8,'ARO ROMAX QC843 15X8.25\"-(8)X100-114.3 ET 0 CB 73.1','','','ICE882','',NULL,NULL,1009.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-867-9310',0x01,0,807.54,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(868,770,1,6,310,8,'LLANTA VITOUR 145R13C V888 91/89R 10PR CH','','','ICE883','',NULL,NULL,144,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-868-8102',0x01,0,120,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(869,763,1,6,317,8,'CAMARA TSK 550-13','','','ICE884','',NULL,NULL,24,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-869-3692',0x01,0,20,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(870,627,1,6,309,8,'BATERIA OUTDO NACRX9 12V 7AH (YTX9) R','','','ICE885','',NULL,NULL,212.5,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-870-6679',0x01,0,141.68,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(871,627,1,6,313,8,'BATERIA OUTDO NACRX5L 12V 3.5AH (YTX5) L','','','ICE886','',NULL,NULL,116.9,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-871-5041',0x01,0,77.91,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(872,627,1,6,313,8,'BATERIA OUTDO NACRX7L 12V 4.5AH (UTX7L) L','','','ICE887','',NULL,NULL,149,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-872-9685',0x01,0,99.36,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(873,627,1,6,313,8,'BATERIA OUTDO NACR7A 12V 4.5AH (UTX7A) R','','','ICE888','',NULL,NULL,154.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-873-5898',0x01,0,99.62,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(874,701,1,6,310,8,'LLANTA KAPSEN 295-80R22.5 DEL','','','ICE889','',NULL,NULL,1140,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-874-5155',0x01,0,1140,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(875,732,1,6,310,8,'LLANTA COMPASAL 295-80R22.5 POS TORTUGA','','','ICE890','',NULL,NULL,1356,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-875-6367',0x01,0,1356,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(876,681,1,6,310,8,'LLANTA COMFORSER LT265/65R17 MT','','','ICE891','',NULL,NULL,384,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-876-1482',0x01,0,320,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(877,771,1,6,310,8,'LLANTA GALAXY 19.5L-24 SSJUMBO HULK 14PR','','','ICE892','',NULL,NULL,3336,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-877-9205',0x01,0,2780,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(878,650,1,6,310,8,'LLANTA ALLIANCE 19.5L-24 INDUSTRIAL 533 151AB TL 12PR','','','ICE893','',NULL,NULL,2186.4,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-878-2378',0x01,0,1822,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,''),(879,772,1,6,310,8,'LLANTA TIMSUN 110/70-17','','','ICE894','',NULL,NULL,228,NULL,NULL,0,0,'2026-10-02 15:44:33','superadmin',NULL,NULL,'1790973873-879-4566',0x01,0,190,NULL,0,NULL,'','','','',0,NULL,NULL,NULL,0,0.0000,0.0000,'[]',0,'');
/*!40000 ALTER TABLE `Gen_Producto` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Gen_Producto_Totales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Gen_Producto_Totales` (
  `IdAlmacen` int(11) NOT NULL,
  `IdProducto` int(11) NOT NULL,
  `TotalCompras` double DEFAULT NULL,
  `TotalVentas` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Gen_Producto_Totales` WRITE;
/*!40000 ALTER TABLE `Gen_Producto_Totales` DISABLE KEYS */;
INSERT INTO `Gen_Producto_Totales` VALUES (1,2,12,0),(1,3,14,0),(1,4,6,0),(1,5,8,0),(1,6,8,0),(1,7,42,0),(1,8,4,0),(1,9,2,0),(1,10,3,0),(1,11,4,0),(1,12,86,0),(1,13,16,0),(1,14,6,0),(1,15,10,0),(1,16,4,0),(1,18,3,0),(1,19,13,0),(1,27,2,0),(1,32,1,0),(1,35,2,0),(1,40,19,0),(1,41,66,0),(1,42,5,0),(1,43,30,0),(1,45,7,0),(1,46,35,0),(1,47,36,0),(1,48,20,0),(1,49,27,0),(1,52,4,0),(1,53,10,0),(1,54,5,0),(1,55,10,0),(1,60,2,0),(1,61,2,0),(1,62,7,0),(1,65,3,0),(1,66,5,0),(1,67,4,0),(1,72,6,0),(1,73,8,0),(1,83,3,0),(1,84,80,0),(1,85,22,0),(1,87,6,0),(1,88,3,0),(1,90,10,0),(1,91,22,0),(1,93,48,0),(1,95,19,0),(1,96,1,0),(1,97,7,0),(1,99,8,0),(1,101,2,0),(1,102,4,0),(1,104,110,0),(1,106,96,0),(1,107,19,0),(1,108,5,0),(1,109,4,0),(1,110,10,0),(1,111,6,0),(1,112,79,0),(1,114,1,0),(1,115,77,0),(1,116,6,0),(1,119,10,0),(1,120,7,0),(1,121,6,0),(1,122,9,0),(1,123,10,0),(1,124,4,0),(1,125,8,0),(1,126,8,0),(1,128,36,0),(1,129,5,0),(1,130,10,0),(1,142,6,0),(1,145,5,0),(1,146,3,0),(1,153,19,0),(1,157,12,0),(1,168,4,0),(1,169,45,0),(1,171,2,0),(1,177,3,0),(1,179,21,0),(1,180,3,0),(1,181,10,0),(1,182,13,0),(1,183,2,0),(1,184,9,0),(1,185,2,0),(1,186,7,0),(1,187,8,0),(1,188,1,0),(1,195,1,0),(1,197,11,0),(1,201,6,0),(1,203,3,0),(1,210,2,0),(1,211,2,0),(1,218,2,0),(1,224,5,0),(1,228,2,0),(1,229,4,0),(1,230,3,0),(1,231,7,0),(1,232,4,0),(1,233,5,0),(1,236,4,0),(1,237,6,0),(1,238,1,0),(1,250,27,0),(1,255,9,0),(1,263,5,0),(1,264,8,0),(1,269,3,0),(1,286,43,0),(1,294,13,0),(1,298,2,0),(1,299,1,0),(1,302,25,0),(1,305,3,0),(1,306,5,0),(1,317,9,0),(1,323,4,0),(1,327,1,0),(1,329,8,0),(1,335,7,0),(1,340,1,0),(1,342,3,0),(1,343,3,0),(1,344,1,0),(1,345,9,0),(1,346,1,0),(1,353,1,0),(1,354,3,0),(1,357,104,0),(1,359,1,0),(1,360,5,0),(1,363,3,0),(1,364,2,0),(1,365,2,0),(1,366,2,0),(1,371,8,0),(1,372,15,0),(1,373,2,0),(1,374,13,0),(1,375,1,0),(1,376,3,0),(1,377,6,0),(1,378,2,0),(1,380,10,0),(1,381,3,0),(1,382,2,0),(1,383,14,0),(1,384,1,0),(1,385,3,0),(1,386,12,0),(1,387,3,0),(1,388,6,0),(1,389,6,0),(1,390,2,0),(1,392,3,0),(1,393,2,0),(1,394,10,0),(1,395,7,0),(1,396,5,0),(1,397,4,0),(1,399,11,0),(1,401,5,0),(1,402,2,0),(1,403,4,0),(1,404,6,0),(1,405,4,0),(1,406,2,0),(1,411,2,0),(1,413,2,0),(1,414,4,0),(1,415,1,0),(1,416,3,0),(1,417,1,0),(1,418,2,0),(1,420,3,0),(1,425,1,0),(1,428,3,0),(1,429,3,0),(1,430,1,0),(1,433,8,0),(1,436,5,0),(1,437,2,0),(1,440,1,0),(1,441,4,0),(1,445,2,0),(1,447,8,0),(1,448,6,0),(1,449,6,0),(1,450,6,0),(1,451,6,0),(1,452,3,0),(1,453,2,0),(1,454,1,0),(1,455,2,0),(1,457,6,0),(1,458,2,0),(1,459,2,0),(1,460,1,0),(1,461,3,0),(1,462,3,0),(1,463,3,0),(1,468,4,0),(1,469,2,0),(1,470,1,0),(1,471,2,0),(1,472,2,0),(1,473,1,0),(1,474,1,0),(1,475,3,0),(1,476,4,0),(1,477,1,0),(1,478,1,0),(1,489,1,0),(1,490,2,0),(1,491,24,0),(1,493,2,0),(1,494,3,0),(1,495,3,0),(1,496,429,0),(1,497,367,0),(1,498,1,0),(1,499,1,0),(1,500,47,0),(1,501,1,0),(1,502,2,0),(1,508,2,0),(1,509,20,0),(1,510,5,0),(1,511,5,0),(1,513,1,0),(1,514,1,0),(1,515,11,0),(1,516,10,0),(1,517,24,0),(1,518,5,0),(1,519,15,0),(1,520,21,0),(1,525,4,0),(1,527,4,0),(1,571,4,0),(1,589,1,0),(1,595,11,0),(1,596,105,0),(1,597,45,0),(1,598,11,0),(1,601,38,0),(1,602,11,0),(1,603,12,0),(1,604,51,0),(1,609,31,0),(1,611,29,0),(1,618,70,0),(1,619,10,0),(1,653,4,0),(1,657,8,0),(1,675,4,0),(1,681,12,0),(1,682,15,0),(1,683,4,0),(1,684,5,0),(1,686,14,0),(1,696,4,0),(1,715,2,0),(1,716,1,0),(1,717,1,0),(1,719,1,0),(1,723,17,0),(1,731,59,0),(1,732,40,0),(1,734,2,0),(1,735,4,0),(1,744,4,0),(1,750,4,0),(1,768,25,0),(1,769,22,0),(1,770,3,0),(1,771,5,0),(1,773,2,0),(1,785,2,0),(1,787,1,0),(1,794,4,0),(1,803,5,0),(1,804,13,0),(1,806,2,0),(1,808,2,0),(1,809,17,0),(1,817,4,0),(1,819,2,0),(1,822,4,0),(1,823,4,0),(1,824,4,0),(1,826,4,0),(1,831,4,0),(1,833,4,0),(1,835,4,0),(1,836,4,0),(1,837,12,0),(1,838,36,0),(1,839,10,0),(1,840,16,0),(1,841,10,0),(1,842,6,0),(1,843,8,0),(1,844,1,0),(1,845,2,0),(1,846,12,0),(1,847,11,0),(1,848,7,0),(1,849,10,0),(1,850,10,0),(1,851,6,0),(1,852,6,0),(1,853,2,0),(1,854,2,0),(1,855,23,0),(1,856,12,0),(1,861,4,0),(1,863,4,0),(1,864,4,0),(1,865,4,0),(1,866,4,0),(1,867,4,0),(1,870,4,0),(1,871,3,0),(1,872,4,0),(1,873,4,0);
/*!40000 ALTER TABLE `Gen_Producto_Totales` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Gen_ProductoBloque`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Gen_ProductoBloque` (
  `IdBloque` int(11) NOT NULL AUTO_INCREMENT,
  `Bloque` varchar(255) DEFAULT NULL,
  `PorcentajeMin` float DEFAULT NULL,
  `PorcentajeMax` float DEFAULT NULL,
  `FechaReg` datetime DEFAULT NULL,
  `UsuarioReg` varchar(255) DEFAULT NULL,
  `FechaMod` datetime DEFAULT NULL,
  `UsuarioMod` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`IdBloque`),
  UNIQUE KEY `BLOQUE_UNIQUE` (`Bloque`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Gen_ProductoBloque` WRITE;
/*!40000 ALTER TABLE `Gen_ProductoBloque` DISABLE KEYS */;
INSERT INTO `Gen_ProductoBloque` VALUES (0,'-',NULL,NULL,NULL,NULL,NULL,NULL),(5,'PVL',16.5,33.2,'2017-09-16 13:30:34','Jeam','2018-05-08 18:36:30','Jeam'),(6,'PEB',33.3,50,'2017-10-02 06:24:24','Jeam','2018-05-08 18:37:13','Jeam'),(7,'PE',50.1,66.6,'2017-10-02 06:25:02','Jeam','2018-05-08 18:37:43','Jeam'),(8,'PSE',66.7,95,'2017-10-02 06:27:17','Jeam','2018-05-08 18:38:19','Jeam'),(9,'PI',44.4,83.3,'2017-10-02 06:28:19','Jeam','2018-05-08 18:39:02','Jeam'),(10,'PBR',1,16.5,'2017-10-02 06:29:22','Jeam','2018-05-08 18:39:26','Jeam'),(11,'PP',23,66.7,'2017-10-02 06:30:25','Jeam','2018-05-08 18:39:57','Jeam'),(12,'--',50,99.9,'2017-10-24 09:12:25','Jeam','2018-05-08 18:34:48','Jeam');
/*!40000 ALTER TABLE `Gen_ProductoBloque` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Gen_ProductoCategoria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Gen_ProductoCategoria` (
  `IdProductoCategoria` int(11) NOT NULL AUTO_INCREMENT,
  `ProductoCategoria` varchar(255) DEFAULT NULL,
  `Anulado` bit(1) DEFAULT NULL,
  `FechaReg` datetime DEFAULT NULL,
  `UsuarioReg` varchar(255) DEFAULT NULL,
  `FechaMod` datetime DEFAULT NULL,
  `UsuarioMod` varchar(255) DEFAULT NULL,
  `IdProductoCategoriaSub` int(11) DEFAULT NULL,
  PRIMARY KEY (`IdProductoCategoria`),
  UNIQUE KEY `ProductoCategoria_UNIQUE` (`ProductoCategoria`)
) ENGINE=InnoDB AUTO_INCREMENT=334 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Gen_ProductoCategoria` WRITE;
/*!40000 ALTER TABLE `Gen_ProductoCategoria` DISABLE KEYS */;
INSERT INTO `Gen_ProductoCategoria` VALUES (309,'BATERIA',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL,NULL),(310,'LLANTA',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL,NULL),(311,'BATERIA A. M C',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL,NULL),(312,'BATERIA A.',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL,NULL),(313,'BATERIA M. L',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL,NULL),(314,'SERVICIO',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL,NULL),(315,'BATERIA M. C',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL,NULL),(316,'ARO',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL,NULL),(317,'CAMARA',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL,NULL),(318,'ACCESORIO',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL,NULL),(319,'BORNE',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL,NULL),(320,'TUERCA',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL,NULL),(321,'PERNO',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL,NULL),(322,'BTU',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL,NULL),(323,'PROFORMA',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL,NULL),(324,'SEGUNDA',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL,NULL),(325,'BATERIA M.',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL,NULL),(326,'BONO',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL,NULL),(327,'BATERIA EFB',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL,NULL),(328,'PANEL',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL,NULL),(329,'GARANTIA',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL,NULL),(330,'TECNOLOGIA',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL,NULL),(331,'OFERTA',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL,NULL),(332,'HERRAMIENTA',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL,NULL),(333,'BATERIA.',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL,NULL);
/*!40000 ALTER TABLE `Gen_ProductoCategoria` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Gen_ProductoCompuesto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Gen_ProductoCompuesto` (
  `IdProductoCompuesto` int(11) NOT NULL AUTO_INCREMENT,
  `ProductoCompuesto` varchar(255) DEFAULT NULL,
  `Anulado` bit(1) DEFAULT NULL,
  `FechaReg` datetime DEFAULT NULL,
  `UsuarioReg` varchar(255) DEFAULT NULL,
  `FechaMod` datetime DEFAULT NULL,
  `UsuarioMod` varchar(255) DEFAULT NULL,
  `Hash` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`IdProductoCompuesto`),
  UNIQUE KEY `ProductoCompuesto_UNIQUE` (`ProductoCompuesto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Gen_ProductoCompuesto` WRITE;
/*!40000 ALTER TABLE `Gen_ProductoCompuesto` DISABLE KEYS */;
/*!40000 ALTER TABLE `Gen_ProductoCompuesto` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Gen_ProductoCompuestoDet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Gen_ProductoCompuestoDet` (
  `Gen_Producto_IdProducto` int(11) NOT NULL,
  `Gen_ProductoCompuesto_IdProductoCompuesto` int(11) NOT NULL,
  PRIMARY KEY (`Gen_Producto_IdProducto`,`Gen_ProductoCompuesto_IdProductoCompuesto`),
  KEY `fk_Gen_Producto_has_Gen_ProductoCompuesto_Gen_ProductoCompu_idx` (`Gen_ProductoCompuesto_IdProductoCompuesto`),
  KEY `fk_Gen_Producto_has_Gen_ProductoCompuesto_Gen_Producto1_idx` (`Gen_Producto_IdProducto`),
  CONSTRAINT `fk_Gen_Producto_has_Gen_ProductoCompuesto_Gen_Producto1` FOREIGN KEY (`Gen_Producto_IdProducto`) REFERENCES `Gen_Producto` (`IdProducto`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_Gen_Producto_has_Gen_ProductoCompuesto_Gen_ProductoCompues1` FOREIGN KEY (`Gen_ProductoCompuesto_IdProductoCompuesto`) REFERENCES `Gen_ProductoCompuesto` (`IdProductoCompuesto`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Gen_ProductoCompuestoDet` WRITE;
/*!40000 ALTER TABLE `Gen_ProductoCompuestoDet` DISABLE KEYS */;
/*!40000 ALTER TABLE `Gen_ProductoCompuestoDet` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Gen_ProductoDet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Gen_ProductoDet` (
  `IdProducto` int(11) NOT NULL,
  `IdProductoDet` int(11) NOT NULL,
  `Cantidad` float DEFAULT NULL,
  PRIMARY KEY (`IdProducto`,`IdProductoDet`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Gen_ProductoDet` WRITE;
/*!40000 ALTER TABLE `Gen_ProductoDet` DISABLE KEYS */;
/*!40000 ALTER TABLE `Gen_ProductoDet` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Gen_ProductoFormaFarmaceutica`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Gen_ProductoFormaFarmaceutica` (
  `IdProductoFormaFarmaceutica` int(11) NOT NULL AUTO_INCREMENT,
  `ProductoFormaFarmaceutica` varchar(255) DEFAULT NULL,
  `Anulado` bit(1) DEFAULT NULL,
  `FechaReg` datetime DEFAULT NULL,
  `UsuarioReg` varchar(255) DEFAULT NULL,
  `FechaMod` datetime DEFAULT NULL,
  `UsuarioMod` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`IdProductoFormaFarmaceutica`)
) ENGINE=InnoDB AUTO_INCREMENT=77 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Gen_ProductoFormaFarmaceutica` WRITE;
/*!40000 ALTER TABLE `Gen_ProductoFormaFarmaceutica` DISABLE KEYS */;
INSERT INTO `Gen_ProductoFormaFarmaceutica` VALUES (1,'INYECCION',0x00,'2017-07-05 14:32:45','Jeam','2017-08-07 08:30:33','Jeam'),(2,'LATA',0x00,'2017-08-01 13:34:42','Jeam','2017-08-05 13:03:15','Jeam'),(3,'AM AGUA MINERAL',0x00,'2017-08-01 14:17:32','Jeam','2018-03-03 13:37:23','Jeam'),(4,'GRAGEA',0x01,'2017-08-01 16:55:35','Jeam','2017-08-05 22:21:29','Jeam'),(5,'OVULO',0x00,'2017-08-02 22:28:53','Jeam','2017-08-05 13:01:13','Jeam'),(6,'EXTRACTO',0x00,'2017-08-03 14:35:32','Jeam','2017-08-06 05:54:26','Jeam'),(8,'JARABE',0x00,'2017-08-03 16:40:04','Jeam','2017-08-05 22:24:39','Jeam'),(9,'COLIRIO',0x00,'2017-08-04 06:46:17','Jeam','2017-08-05 22:25:38','Jeam'),(10,'CREMA',0x00,'2017-08-04 07:44:37','Jeam','2017-08-05 22:26:04','Jeam'),(11,'PS PASTA',0x00,'2017-08-04 12:51:45','Jeam','2018-03-03 13:48:43','Jeam'),(12,'TB TABLETA',0x00,'2017-08-04 13:24:13','Jeam','2018-03-03 13:52:06','Jeam'),(13,'PM POMADA / UNGUENTO',0x00,'2017-08-04 14:56:33','Jeam','2018-03-03 13:53:02','Jeam'),(14,'UNIDAD',0x00,'2017-08-05 06:53:27','Jeam','2017-08-09 08:32:49','Jeam'),(15,'GEL',0x00,'2017-08-05 07:22:49','Jeam','2017-08-05 22:28:16','Jeam'),(16,'SL SOLUCION',0x00,'2017-08-05 07:35:24','Jeam','2018-03-03 14:15:39','Jeam'),(17,'ROLLO',0x00,'2017-08-05 09:06:32','Jeam','2017-08-06 05:35:30','Jeam'),(18,'AGUJA',0x00,'2017-08-05 10:13:45','Jeam',NULL,NULL),(19,'PL POLVO',0x00,'2017-08-05 11:06:33','Jeam','2018-03-03 13:54:47','Jeam'),(20,'CS CAPSULA',0x00,'2017-08-05 14:24:00','Jeam','2018-03-03 13:58:33','Jeam'),(21,'PL PALETA DE MADERA ',0x00,'2017-08-05 15:24:21','Jeam','2018-03-03 13:24:22','Jeam'),(22,'SUSPENCION',0x00,'2017-08-06 07:24:04','Jeam',NULL,NULL),(23,'LOCION',0x00,'2017-08-06 07:28:41','Jeam',NULL,NULL),(24,'CI ACEITE',0x00,'2017-08-06 07:30:14','Jeam','2018-03-03 14:01:32','Jeam'),(25,'SHAMPOO / ACONDICIONADOR',0x00,'2017-08-06 07:44:13','Jeam',NULL,NULL),(26,'BIBERON',0x00,'2017-08-06 07:47:59','Jeam','2017-09-20 05:46:53','Jeam'),(27,'CN CONDON',0x00,'2017-08-06 08:54:19','Jeam','2018-03-03 14:04:54','Jeam'),(28,'AC AGUA DE COLONIA',0x00,'2017-08-06 09:28:04','Jeam','2018-03-03 13:37:51','Jeam'),(29,'TROCISCO',0x00,'2017-08-06 09:41:52','Jeam',NULL,NULL),(30,'TS TOALLA SANITARIA',0x00,'2017-08-06 10:02:58','Jeam','2018-03-03 14:07:25','Jeam'),(31,'JL JALEA',0x00,'2017-08-06 10:11:04','Jeam','2018-03-03 13:40:09','Jeam'),(32,'HD HILO',0x00,'2017-08-07 06:13:10','Jeam','2018-03-03 14:18:35','Jeam'),(33,'BARRA',0x00,'2017-08-07 10:14:15','Jeam',NULL,NULL),(34,'CL COLADO',0x00,'2017-08-08 08:13:10','Jeam','2018-03-03 13:44:56','Jeam'),(35,'GOTAS',0x00,'2017-08-08 09:41:20','Jeam',NULL,NULL),(36,'AC COLONIA ',0x00,'2017-08-08 14:55:15','Jeam','2018-03-03 13:43:09','Jeam'),(37,'JN JABON',0x00,'2017-08-09 06:13:38','Jeam','2018-03-03 13:41:05','Jeam'),(38,'AP AGUA DE PERFUME',0x00,'2017-08-09 06:36:01','Jeam','2018-03-03 13:38:14','Jeam'),(39,'AEROSOL',0x00,'2017-08-09 07:04:20','Jeam',NULL,NULL),(40,'SACHET',0x00,'2017-08-09 08:57:31','Jeam','2017-09-05 08:27:49','Jeam'),(41,'SPRAY',0x00,'2017-08-09 09:43:44','Jeam',NULL,NULL),(42,'POTE',0x00,'2017-08-09 13:13:10','Jeam','2017-08-09 13:16:01','Jeam'),(43,'FRASCO',0x00,'2017-08-09 14:57:02','Jeam',NULL,NULL),(44,'AMPOLLAS',0x00,'2017-08-09 15:26:03','Jeam',NULL,NULL),(45,'CHUPON',0x00,'2017-08-09 20:42:15','Jeam',NULL,NULL),(46,'CAJA',0x00,'2017-08-10 05:48:34','Jeam',NULL,NULL),(47,'.....',0x00,'2017-08-10 06:19:32','Jeam','2018-03-03 14:28:13','Jeam'),(48,'BLOQUEADOR',0x00,'2017-08-10 08:15:12','Jeam','2018-03-03 14:46:06','Jeam'),(49,'PF PROTECTORES / PROFILACTICOS',0x00,'2017-08-10 14:02:17','Jeam','2018-03-03 13:27:07','Jeam'),(50,'............',0x00,'2017-08-10 19:13:29','Jeam','2018-03-03 14:00:09','Jeam'),(51,'GASA',0x00,'2017-08-12 10:00:55','Jeam',NULL,NULL),(52,'SOBRE',0x00,'2017-08-12 13:07:57','Jeam',NULL,NULL),(53,'GL GALON',0x00,'2017-08-13 07:57:40','Jeam','2018-03-03 14:17:07','Jeam'),(54,'HILO SUTURA',0x00,'2017-08-13 11:46:53','Jeam','2018-03-03 14:17:51','Jeam'),(55,'GUANTES',0x00,'2017-08-14 06:15:09','Jeam',NULL,NULL),(56,'........',0x00,'2017-08-14 07:03:05','Jeam','2018-03-03 14:24:59','Jeam'),(57,'GM GOMAS',0x00,'2017-08-14 08:00:17','Jeam','2018-03-03 14:29:08','Jeam'),(58,'JERINGA',0x00,'2017-08-14 08:04:05','Jeam','2017-08-14 08:17:04','Jeam'),(59,'TUBO',0x00,'2017-08-14 09:40:21','Jeam',NULL,NULL),(60,'PR PARCHES',0x00,'2017-08-14 10:08:44','Jeam','2018-03-03 13:49:44','Jeam'),(61,'ENEMA',0x00,'2017-08-14 13:36:32','Jeam',NULL,NULL),(62,'HOJA BISTURI',0x00,'2017-08-15 05:51:54','Jeam',NULL,NULL),(63,'PÑ PAÑAL',0x00,'2017-08-15 06:02:31','Jeam','2018-03-03 14:38:26','Jeam'),(64,'LAXANTE',0x00,'2017-08-15 07:42:38','Jeam','2018-03-03 14:34:53','Jeam'),(65,'EB ENJUAGUE BUCAL',0x00,'2017-08-15 09:41:20','Jeam','2018-03-03 14:30:50','Jeam'),(66,'CM COMPRIMIDOS',0x00,'2017-08-16 09:06:52','Jeam','2018-03-03 13:46:35','Jeam'),(67,'...............',0x00,'2017-08-16 20:46:45','Jeam','2018-03-03 14:45:49','Jeam'),(68,'TARRO',0x00,'2017-08-17 12:07:53','Jeam',NULL,NULL),(69,'TH TOALLITAS HUMEDAS',0x00,'2017-08-20 08:37:24','Jeam','2018-03-03 14:08:23','Jeam'),(70,'VIAL',0x00,'2017-08-23 12:30:53','Jeam',NULL,NULL),(71,'EV EQUIPO DE VENOCLISIS',0x00,'2017-08-24 07:06:05','Jeam','2018-03-03 14:39:54','Jeam'),(72,'PT PASTILLA',0x00,'2017-08-24 07:30:40','Jeam','2018-03-03 13:51:08','Jeam'),(73,'....... ',0x00,'2017-08-30 13:07:31','Jeam','2018-03-03 14:43:30','Jeam'),(74,'..........',0x00,'2017-09-01 14:06:48','Jeam','2018-03-03 13:56:06','Jeam'),(75,'REPELENTE',0x00,'2017-09-02 07:59:02','Jeam',NULL,NULL),(76,'CEPILLO',0x00,'2017-09-28 12:12:48','Jeam',NULL,NULL);
/*!40000 ALTER TABLE `Gen_ProductoFormaFarmaceutica` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Gen_ProductoMarca`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Gen_ProductoMarca` (
  `IdProductoMarca` int(11) NOT NULL AUTO_INCREMENT,
  `ProductoMarca` varchar(255) DEFAULT NULL,
  `Anulado` bit(1) DEFAULT NULL,
  `FechaReg` datetime DEFAULT NULL,
  `UsuarioReg` varchar(255) DEFAULT NULL,
  `FechaMod` datetime DEFAULT NULL,
  `UsuarioMod` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`IdProductoMarca`),
  UNIQUE KEY `ProductoMarca_UNIQUE` (`ProductoMarca`)
) ENGINE=InnoDB AUTO_INCREMENT=773 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Gen_ProductoMarca` WRITE;
/*!40000 ALTER TABLE `Gen_ProductoMarca` DISABLE KEYS */;
INSERT INTO `Gen_ProductoMarca` VALUES (615,'ENERJET',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(616,'BOLT',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(617,'GOOD YEAR',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(618,'EQI',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(619,'SOLITE',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(620,'CAPSA',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(621,'RECORD',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(622,'ETNA',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(623,'ENERLITE',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(624,'ZERO',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(625,'OXFORD',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(626,'YUASA',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(627,'OUTDO',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(628,'BMT',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(629,'CSB',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(630,'ICE',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(631,'KGM',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(632,'KGM#8',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(633,'S&M',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(634,'ARL',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(635,'JVX',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(636,'VGOOD',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(637,'BOSCH',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(638,'PLOMO',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(639,'STANDAR',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(640,'SILVER CAST',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(641,'JJS',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(642,'VIKRANT',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(643,'GENERAL',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(644,'ROCKET',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(645,'BESTE',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(646,'HABILEAD',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(647,'NEXEN',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(648,'AUFINE',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(649,'CHATARRA',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(650,'ALLIANCE',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(651,'MASTER',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(652,'MOURA',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(653,'TECHSHIELD',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(654,'PLUSWAY',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(655,'DOUPRO',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(656,'ROADX',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(657,'DESTONE',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(658,'COTIZACION',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(659,'OSC',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(660,'MAC',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(661,'SEMINUEVO',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(662,'WIDEWAY',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(663,'ZMAX',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(664,'SFX',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(665,'MAGNA',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(666,'CHAOYANG',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(667,'MAXXIS',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(668,'HIFLY',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(669,'SUNWIDE',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(670,'HANKOOK',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(671,'TOPOWER',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(672,'KUSTONE',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(673,'JSL',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(674,'KONG',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(675,'ROYAL BLACK',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(676,'SUNTEC',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(677,'KINGPOWER',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(678,'TOPRUNNER',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(679,'HAIDA',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(680,'ECONOBAT',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(681,'COMFORSER',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(682,'VTOVIC',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(683,'VITOVIC',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(684,'MAXZEZ',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(685,'CEAT',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(686,'CORE KING',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(687,'TVS',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(688,'DOURPO',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(689,'ANTYRE',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(690,'BOSSNEY',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(691,'AK',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(692,'OKAYAMA',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(693,'ONYX',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(694,'GARANTIA',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(695,'LANTUN',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(696,'AUSTONE',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(697,'RECORD MAX',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(698,'YUANXING',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(699,'TOTUS',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(700,'EDEN',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(701,'KAPSEN',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(702,'SM',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(703,'LING LONG',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(704,'BULK',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(705,'3BSI',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(706,'ROMAX#17',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(707,'BELEN#12',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(708,'ROMAX#12',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(709,'BELEN#13',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(710,'ROMAX#13',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(711,'ROMAX#14',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(712,'BELEN#14',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(713,'NISOSHI#14',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(714,'ROMAX#15',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(715,'BELEN#15',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(716,'WILCROX#17',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(717,'QC#15',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(718,'MICHELIN',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(719,'PRIMEX',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(720,'KENDA',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(721,'WINDA',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(722,'ONIX',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(723,'LIMA CAUCHO',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(724,'TIRON',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(725,'GOODRIDE',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(726,'MARSHAL',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(727,'ENERMAX',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(728,'JK TYRE',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(729,'NISOSHI#13',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(730,'TOP#17',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(731,'RW#13',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(732,'COMPASAL',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(733,'ZETUR',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(734,'ANNAITE',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(735,'JOYROOAD',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(736,'MAZZINI',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(737,'VARTA',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(738,'FIREMAX',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(739,'RALLY#8',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(740,'MIRAGE',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(741,'WILCROXX#17',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(742,'GETOP RUBBER',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(743,'KRAIN',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(744,'HANZO',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(745,'HONGJU',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(746,'ACTIVACIÓN',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(747,'ROADBOSS',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(748,'FARROAD',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(749,'ROMAX #13',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(750,'QC#13',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(751,'RW #14',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(752,'ROMAX #14',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(753,'RAPIDASH',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(754,'HILO',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(755,'ZEXTOUR',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(756,'OVERALL',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(757,'TRIANGLE',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(758,'JK',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(759,'CAMARA',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(760,'XCCED',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(761,'SEGURO',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(762,'GTRADIAL',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(763,'TSK',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(764,'ROMAX #12',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(765,'NISOSHI#15',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(766,'JVX#8',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(767,'STEELIES',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(768,'KUMHO',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(769,'ROMAX #15',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(770,'VITOUR',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(771,'GALAXY',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL),(772,'TIMSUN',0x00,'2026-10-02 15:44:33','superadmin',NULL,NULL);
/*!40000 ALTER TABLE `Gen_ProductoMarca` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Gen_ProductoMedicion`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Gen_ProductoMedicion` (
  `IdProductoMedicion` int(11) NOT NULL AUTO_INCREMENT,
  `ProductoMedicion` varchar(255) DEFAULT NULL,
  `Descripcion` varchar(255) DEFAULT NULL,
  `Codigo` varchar(45) DEFAULT NULL,
  `Anulado` tinyint(1) DEFAULT 0,
  `FechaReg` datetime DEFAULT NULL,
  `UsuarioReg` varchar(255) DEFAULT NULL,
  `FechaMod` datetime DEFAULT NULL,
  `UsuarioMod` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`IdProductoMedicion`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Gen_ProductoMedicion` WRITE;
/*!40000 ALTER TABLE `Gen_ProductoMedicion` DISABLE KEYS */;
INSERT INTO `Gen_ProductoMedicion` VALUES (6,'UNIDAD','number of international units','NIU',0,'2017-08-03 13:51:13','Jeam','2026-10-02 15:44:33','superadmin');
/*!40000 ALTER TABLE `Gen_ProductoMedicion` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Gen_ProductoModelo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Gen_ProductoModelo` (
  `IdProductoModelo` int(11) NOT NULL AUTO_INCREMENT,
  `ProductoModelo` varchar(255) DEFAULT NULL,
  `Anulado` bit(1) DEFAULT NULL,
  `FechaReg` datetime DEFAULT NULL,
  `UsuarioReg` varchar(255) DEFAULT NULL,
  `FechaMod` datetime DEFAULT NULL,
  `UsuarioMod` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`IdProductoModelo`),
  UNIQUE KEY `ProductoModelo_UNIQUE` (`ProductoModelo`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Gen_ProductoModelo` WRITE;
/*!40000 ALTER TABLE `Gen_ProductoModelo` DISABLE KEYS */;
/*!40000 ALTER TABLE `Gen_ProductoModelo` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Gen_ProductoTalla`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Gen_ProductoTalla` (
  `IdProductoTalla` int(11) NOT NULL AUTO_INCREMENT,
  `ProductoTalla` varchar(255) DEFAULT NULL,
  `Anulado` tinyint(1) DEFAULT NULL,
  `FechaReg` datetime DEFAULT NULL,
  `UsuarioReg` varchar(255) DEFAULT NULL,
  `FechaMod` datetime DEFAULT NULL,
  `UsuarioMod` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`IdProductoTalla`),
  UNIQUE KEY `ProductoTalla_UNIQUE` (`ProductoTalla`)
) ENGINE=MyISAM AUTO_INCREMENT=291 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Gen_ProductoTalla` WRITE;
/*!40000 ALTER TABLE `Gen_ProductoTalla` DISABLE KEYS */;
INSERT INTO `Gen_ProductoTalla` VALUES (4,'S',0,NULL,NULL,NULL,NULL),(5,'M',0,NULL,NULL,NULL,NULL),(6,'L',0,NULL,NULL,NULL,NULL),(7,'XL',0,NULL,NULL,NULL,NULL),(8,'STD',0,NULL,NULL,NULL,NULL),(9,'XXL',0,NULL,NULL,NULL,NULL),(10,'3XL',0,NULL,NULL,NULL,NULL),(11,'39',0,NULL,NULL,NULL,NULL),(12,'40',0,NULL,NULL,NULL,NULL),(13,'41',0,NULL,NULL,NULL,NULL),(14,'42',0,NULL,NULL,NULL,NULL),(15,'43',0,NULL,NULL,NULL,NULL),(16,'28',0,NULL,NULL,NULL,NULL),(17,'30',0,NULL,NULL,NULL,NULL),(18,'32',0,NULL,NULL,NULL,NULL),(19,'34',0,NULL,NULL,NULL,NULL),(20,'36',0,NULL,NULL,NULL,NULL),(21,'38',0,NULL,NULL,NULL,NULL),(22,'XS',0,NULL,NULL,NULL,NULL),(23,'26',0,NULL,NULL,NULL,NULL),(24,'DAMA',0,NULL,NULL,NULL,NULL),(25,'31',0,NULL,NULL,NULL,NULL),(26,'14',0,NULL,NULL,NULL,NULL),(27,'4',0,NULL,NULL,NULL,NULL),(28,'6',0,NULL,NULL,NULL,NULL),(29,'10',0,NULL,NULL,NULL,NULL),(30,'8',0,NULL,NULL,NULL,NULL),(31,'12',0,NULL,NULL,NULL,NULL),(32,'16',0,NULL,NULL,NULL,NULL),(33,'16X',0,NULL,NULL,NULL,NULL),(34,'2',0,NULL,NULL,NULL,NULL),(35,'12CM',0,NULL,NULL,NULL,NULL),(36,'24M',0,NULL,NULL,NULL,NULL),(37,'M/L',0,NULL,NULL,NULL,NULL),(38,'15',0,NULL,NULL,NULL,NULL),(39,'17-1/2',0,NULL,NULL,NULL,NULL),(40,'L-XXL',0,NULL,NULL,NULL,NULL),(41,'S-M',0,NULL,NULL,NULL,NULL),(42,'2XL',0,NULL,NULL,NULL,NULL),(43,'AXL',0,NULL,NULL,NULL,NULL),(44,'M-L',0,NULL,NULL,NULL,NULL),(45,'14-1/2',0,NULL,NULL,NULL,NULL),(46,'15-1/2',0,NULL,NULL,NULL,NULL),(47,'16-1/2',0,NULL,NULL,NULL,NULL),(48,'17',0,NULL,NULL,NULL,NULL),(49,'44',0,NULL,NULL,NULL,NULL),(50,'L -M',0,NULL,NULL,NULL,NULL),(51,'4XL',0,NULL,NULL,NULL,NULL),(52,'L- XL',0,NULL,NULL,NULL,NULL),(53,'35',0,NULL,NULL,NULL,NULL),(54,'37',0,NULL,NULL,NULL,NULL),(55,'33',0,NULL,NULL,NULL,NULL),(56,'4-1/2',0,NULL,NULL,NULL,NULL),(57,'3-1/2',0,NULL,NULL,NULL,NULL),(58,'5',0,NULL,NULL,NULL,NULL),(59,'5-1/2',0,NULL,NULL,NULL,NULL),(60,'6-1/2',0,NULL,NULL,NULL,NULL),(61,'7',0,NULL,NULL,NULL,NULL),(62,'7-1/2',0,NULL,NULL,NULL,NULL),(63,'8-1/2',0,NULL,NULL,NULL,NULL),(64,'9-1/2',0,NULL,NULL,NULL,NULL),(65,'35-1/2',0,NULL,NULL,NULL,NULL),(66,'9',0,NULL,NULL,NULL,NULL),(67,'8.5',0,NULL,NULL,NULL,NULL),(68,'7.5',0,NULL,NULL,NULL,NULL),(69,'18',0,NULL,NULL,NULL,NULL),(70,'S/M',0,NULL,NULL,NULL,NULL),(71,'S/M/L',0,NULL,NULL,NULL,NULL),(72,'9.5',0,NULL,NULL,NULL,NULL),(73,'O/S',0,NULL,NULL,NULL,NULL),(74,'XXXL',0,NULL,NULL,NULL,NULL),(75,'7-/12',0,NULL,NULL,NULL,NULL),(76,'X16',0,NULL,NULL,NULL,NULL),(77,'S.M.L.',0,NULL,NULL,NULL,NULL),(78,'S.M.L.XL',0,NULL,NULL,NULL,NULL),(79,'6.5',0,NULL,NULL,NULL,NULL),(80,'3.5',0,NULL,NULL,NULL,NULL),(81,'4.5',0,NULL,NULL,NULL,NULL),(82,'5.5',0,NULL,NULL,NULL,NULL),(83,'1',0,NULL,NULL,NULL,NULL),(84,'3',0,NULL,NULL,NULL,NULL),(85,'15 1/2',0,NULL,NULL,NULL,NULL),(86,'16 1/2',0,NULL,NULL,NULL,NULL),(87,'17 1/2',0,NULL,NULL,NULL,NULL),(88,'16/ 1/2',0,NULL,NULL,NULL,NULL),(89,'27',0,NULL,NULL,NULL,NULL),(90,'29',0,NULL,NULL,NULL,NULL),(91,'90',0,'2018-06-14 10:09:25',NULL,NULL,NULL),(92,'89',0,'2018-06-14 10:15:38',NULL,NULL,NULL),(93,'50',0,'2018-06-14 14:20:39',NULL,NULL,NULL),(94,'18-1/2',0,'2018-07-23 16:26:02',NULL,NULL,NULL),(95,'30M',0,'2018-08-24 19:52:19',NULL,NULL,NULL),(96,'18M',0,'2018-12-04 18:54:13',NULL,NULL,NULL),(97,'36M',0,'2018-12-04 18:55:01',NULL,NULL,NULL),(98,'12M',0,'2018-12-04 18:55:46',NULL,NULL,NULL),(99,'6M',0,'2018-12-04 19:05:36',NULL,NULL,NULL),(100,'ST',0,'2019-05-09 14:45:51',NULL,NULL,NULL),(101,'L/XL',0,'2019-08-07 13:03:53',NULL,NULL,NULL),(102,'5XL',0,'2019-12-29 11:06:15',NULL,NULL,NULL),(103,'43/44',0,'2020-08-07 11:45:43',NULL,NULL,NULL),(104,'41/42',0,'2020-08-07 11:46:02',NULL,NULL,NULL),(105,'39/40',0,'2020-08-07 11:46:10',NULL,NULL,NULL),(106,'37/38',0,'2020-08-07 11:46:21',NULL,NULL,NULL),(107,'45',0,'2020-08-08 10:16:43',NULL,NULL,NULL),(108,'36/37',0,'2020-08-09 21:50:41',NULL,NULL,NULL),(109,'38/39',0,'2020-08-09 21:51:09',NULL,NULL,NULL),(110,'25',0,'2020-09-21 18:55:24',NULL,NULL,NULL),(111,'6-9',0,'2020-10-08 08:31:25',NULL,NULL,NULL),(112,'10-13',0,'2020-10-08 08:34:28',NULL,NULL,NULL),(113,'2-5',0,'2020-10-08 08:34:57',NULL,NULL,NULL),(114,'28/30',0,'2020-11-16 19:36:41',NULL,NULL,NULL),(115,'32/34',0,'2020-11-16 19:36:50',NULL,NULL,NULL),(116,'24',0,'2020-12-07 12:12:52',NULL,NULL,NULL),(117,'10.5',0,'2021-03-16 14:25:49',NULL,NULL,NULL),(118,' 34',0,'2021-08-06 22:17:11',NULL,NULL,NULL),(119,'33/34',0,'2021-08-09 17:46:46',NULL,NULL,NULL),(120,'40.5',0,'2021-08-09 19:51:24',NULL,NULL,NULL),(121,'42.5',0,'2021-08-09 20:09:29',NULL,NULL,NULL),(122,'41.5',0,'2021-08-09 20:37:08',NULL,NULL,NULL),(123,'43.5',0,'2021-08-09 20:38:05',NULL,NULL,NULL),(124,'38.5',0,'2021-08-09 21:10:47',NULL,NULL,NULL),(125,'35.5',0,'2021-08-12 10:25:19',NULL,NULL,NULL),(126,'34.5',0,'2021-08-12 10:33:24',NULL,NULL,NULL),(127,'37.5',0,'2021-08-12 10:55:34',NULL,NULL,NULL),(128,'36.5',0,'2021-08-12 11:06:48',NULL,NULL,NULL),(129,'44.5',0,'2021-08-12 14:49:04',NULL,NULL,NULL),(130,'23',0,'2021-08-13 19:02:42',NULL,NULL,NULL),(131,'35/36',0,'2021-08-13 19:04:37',NULL,NULL,NULL),(132,'CAMELLO',0,'2021-11-13 14:04:16',NULL,NULL,NULL),(133,'SRT',0,'2022-01-19 02:42:15',NULL,NULL,NULL),(134,'0',0,'2022-01-19 10:39:54',NULL,NULL,NULL),(135,'1-4',0,'2022-01-20 16:29:31',NULL,NULL,NULL),(136,'1-2',0,'2022-01-20 16:29:39',NULL,NULL,NULL),(137,'0-1',0,'2022-01-20 16:29:50',NULL,NULL,NULL),(138,'72',0,'2022-01-20 19:06:44',NULL,NULL,NULL),(139,'SM',0,'2022-01-21 11:18:23',NULL,NULL,NULL),(140,'M/M',0,'2022-01-21 11:30:21',NULL,NULL,NULL),(141,'9-12',0,'2022-01-21 19:10:18',NULL,NULL,NULL),(142,'C',0,'2022-01-29 19:39:23',NULL,NULL,NULL),(143,'A',0,'2022-01-29 19:39:44',NULL,NULL,NULL),(144,'D',0,'2022-01-29 19:40:38',NULL,NULL,NULL),(145,'B',0,'2022-01-29 19:42:24',NULL,NULL,NULL),(146,'3-4',0,'2022-02-02 16:23:07',NULL,NULL,NULL),(147,'G',0,'2022-02-03 19:31:53',NULL,NULL,NULL),(148,'4.6',0,'2022-02-03 21:12:48',NULL,NULL,NULL),(149,'8.10',0,'2022-02-03 21:12:58',NULL,NULL,NULL),(150,'36A',0,'2022-02-18 19:23:58',NULL,NULL,NULL),(151,'36B',0,'2022-02-18 19:24:19',NULL,NULL,NULL),(152,'38B',0,'2022-02-18 19:24:27',NULL,NULL,NULL),(153,'40B',0,'2022-02-18 19:24:42',NULL,NULL,NULL),(154,'42B',0,'2022-02-18 19:24:49',NULL,NULL,NULL),(155,'34A',0,'2022-02-18 19:25:27',NULL,NULL,NULL),(156,'34B',0,'2022-02-18 19:25:48',NULL,NULL,NULL),(157,'32A',0,'2022-02-18 19:29:18',NULL,NULL,NULL),(158,'32B',0,'2022-02-22 17:40:17',NULL,NULL,NULL),(159,'46',0,'2022-03-04 13:45:37',NULL,NULL,NULL),(160,'48',0,'2022-03-04 13:45:49',NULL,NULL,NULL),(161,'34-B',0,'2022-03-04 19:52:10',NULL,NULL,NULL),(162,'36-B',0,'2022-03-04 19:52:40',NULL,NULL,NULL),(163,'32-B',0,'2022-03-04 20:07:51',NULL,NULL,NULL),(164,'38-B',0,'2022-03-05 10:41:00',NULL,NULL,NULL),(165,'40-B',0,'2022-03-05 10:44:53',NULL,NULL,NULL),(166,'36-C',0,'2022-03-05 12:48:38',NULL,NULL,NULL),(167,'2-3',0,'2022-03-10 09:18:33',NULL,NULL,NULL),(168,'4-5',0,'2022-03-10 09:18:48',NULL,NULL,NULL),(169,'6-7',0,'2022-03-10 09:19:07',NULL,NULL,NULL),(170,'8-9',0,'2022-03-10 09:19:33',NULL,NULL,NULL),(171,'0-0',0,'2022-03-11 15:48:03',NULL,NULL,NULL),(172,'B-B',0,'2022-03-11 16:14:20',NULL,NULL,NULL),(173,'38C',0,'2022-03-11 18:01:31',NULL,NULL,NULL),(174,'38D',0,'2022-03-15 16:32:36',NULL,NULL,NULL),(175,'NA',0,'2022-05-14 13:12:32',NULL,NULL,NULL),(176,'36C',0,'2022-07-25 09:48:49',NULL,NULL,NULL),(177,'TU',0,'2022-07-25 10:10:40',NULL,NULL,NULL),(178,'E',0,'2022-07-25 10:55:48',NULL,NULL,NULL),(179,'S/P',0,'2022-08-13 17:20:56',NULL,NULL,NULL),(180,'T1',0,'2022-08-13 17:29:02',NULL,NULL,NULL),(181,'T2',0,'2022-08-13 17:29:08',NULL,NULL,NULL),(182,'T3',0,'2022-08-13 17:29:12',NULL,NULL,NULL),(183,'5BB',0,'2023-01-20 20:21:25',NULL,NULL,NULL),(184,'4-6-8',0,'2023-04-24 11:37:17',NULL,NULL,NULL),(185,'10-12-14',0,'2023-04-24 11:42:42',NULL,NULL,NULL),(186,'1½ PLZ',0,'2023-04-24 12:01:50',NULL,NULL,NULL),(187,'2 PLZ',0,'2023-04-24 12:04:09',NULL,NULL,NULL),(188,'2½ PLZ.',0,'2023-04-24 12:07:23',NULL,NULL,NULL),(189,'S M L',0,'2023-04-24 15:54:19',NULL,NULL,NULL),(190,'35-41',0,'2023-04-24 18:02:54',NULL,NULL,NULL),(191,'S/N',0,'2023-04-27 11:30:40',NULL,NULL,NULL),(192,'SN.',0,'2023-04-27 11:41:05',NULL,NULL,NULL),(193,'CHICOS',0,'2023-04-27 14:37:01',NULL,NULL,NULL),(194,'N',0,'2023-04-27 16:18:35',NULL,NULL,NULL),(195,'U',0,'2023-04-27 17:12:23',NULL,NULL,NULL),(196,'V',0,'2023-04-27 17:19:30',NULL,NULL,NULL),(197,'40 X 60',0,'2023-05-01 17:24:49',NULL,NULL,NULL),(198,' 1½',0,'2023-05-02 10:57:49',NULL,NULL,NULL),(199,'3 PLZ.',0,'2023-05-02 11:18:13',NULL,NULL,NULL),(200,'.',0,'2023-05-02 11:30:32',NULL,NULL,NULL),(201,'32 AL 38',0,'2023-05-02 15:23:56',NULL,NULL,NULL),(202,'ADULTO',0,'2023-05-02 15:31:22',NULL,NULL,NULL),(203,'2 PLZ.',0,'2023-05-02 15:42:20',NULL,NULL,NULL),(204,'  0 2 4',0,'2023-05-16 12:57:12',NULL,NULL,NULL),(205,'0 2 4',0,'2023-05-16 13:01:03',NULL,NULL,NULL),(206,'28-32',0,'2023-07-19 12:11:03',NULL,NULL,NULL),(207,'VARIOS',0,'2023-07-19 12:42:10',NULL,NULL,NULL),(208,'26 -34',0,'2023-07-19 12:45:56',NULL,NULL,NULL),(209,'26-34',0,'2023-07-19 12:57:36',NULL,NULL,NULL),(210,'10-12-14- 16',0,'2023-07-20 11:25:21',NULL,NULL,NULL),(211,'2-4-6-8',0,'2023-07-20 12:00:06',NULL,NULL,NULL),(212,'S-M-L',0,'2023-07-20 14:13:42',NULL,NULL,NULL),(213,'28- 34',0,'2023-07-20 14:31:28',NULL,NULL,NULL),(214,'4-14',0,'2023-07-20 15:08:48',NULL,NULL,NULL),(215,'36-40',0,'2023-07-21 17:55:05',NULL,NULL,NULL),(216,'16-16',0,'2023-07-21 18:38:21',NULL,NULL,NULL),(217,'8-16',0,'2023-07-24 10:31:09',NULL,NULL,NULL),(218,'2-8',0,'2023-07-24 10:33:23',NULL,NULL,NULL),(219,'2-14',0,'2023-07-24 10:59:03',NULL,NULL,NULL),(220,'2-12',0,'2023-07-24 11:03:27',NULL,NULL,NULL),(221,'2-6',0,'2023-07-24 11:08:14',NULL,NULL,NULL),(222,'8-12',0,'2023-07-24 11:12:28',NULL,NULL,NULL),(223,'2-4-6-8-10-12-14',0,'2023-07-24 11:28:49',NULL,NULL,NULL),(224,'1-8',0,'2023-07-24 14:38:03',NULL,NULL,NULL),(225,'S-M-L-XL',0,'2023-07-24 15:07:02',NULL,NULL,NULL),(226,'2-10',0,'2023-07-25 07:50:12',NULL,NULL,NULL),(227,'0-6',0,'2023-07-25 07:58:06',NULL,NULL,NULL),(228,'4-16',0,'2023-07-25 08:08:33',NULL,NULL,NULL),(229,'10 -13',0,'2023-07-25 11:55:54',NULL,NULL,NULL),(230,'6 - 9',0,'2023-07-25 11:57:39',NULL,NULL,NULL),(231,'JUVENIL',0,'2023-07-26 10:34:27',NULL,NULL,NULL),(232,'6-14',0,'2023-07-26 10:59:56',NULL,NULL,NULL),(233,'M-L-XL',0,'2023-07-26 11:40:38',NULL,NULL,NULL),(234,'M-L-XL-XXL',0,'2023-07-26 11:45:36',NULL,NULL,NULL),(235,'0-14',0,'2023-07-28 08:14:58',NULL,NULL,NULL),(236,'34-44',0,'2023-07-28 08:40:32',NULL,NULL,NULL),(237,'40-50',0,'2023-07-28 08:58:03',NULL,NULL,NULL),(238,'34-36-38',0,'2023-07-28 09:59:03',NULL,NULL,NULL),(239,'28-34',0,'2023-07-28 10:04:15',NULL,NULL,NULL),(240,'1-2-3',0,'2023-07-28 11:14:41',NULL,NULL,NULL),(241,'4-5-6',0,'2023-07-28 11:16:00',NULL,NULL,NULL),(242,'8-9-10',0,'2023-07-28 11:19:41',NULL,NULL,NULL),(243,'23-35',0,'2023-07-29 10:23:47',NULL,NULL,NULL),(244,'35-38',0,'2023-07-29 10:25:44',NULL,NULL,NULL),(245,'M-Z',0,'2023-07-29 10:40:23',NULL,NULL,NULL),(246,'6-16',0,'2023-07-29 11:02:47',NULL,NULL,NULL),(247,'DELGADA',0,'2023-07-29 11:08:48',NULL,NULL,NULL),(248,'ANCHA',0,'2023-07-29 11:17:10',NULL,NULL,NULL),(249,'VARIADOS',0,'2023-07-29 11:31:39',NULL,NULL,NULL),(250,'JUNIOR',0,'2023-07-31 10:52:22',NULL,NULL,NULL),(251,'NIÑO',0,'2023-07-31 10:56:42',NULL,NULL,NULL),(252,'6-8-10',0,'2023-07-31 12:09:22',NULL,NULL,NULL),(253,'12-14-16',0,'2023-07-31 12:18:40',NULL,NULL,NULL),(254,'10-12',0,'2023-08-04 18:44:00',NULL,NULL,NULL),(255,'6-8',0,'2023-08-05 09:20:27',NULL,NULL,NULL),(256,'14-16',0,'2023-08-05 09:38:44',NULL,NULL,NULL),(257,'28-30',0,'2023-08-05 09:43:18',NULL,NULL,NULL),(258,'32-34',0,'2023-08-05 09:45:22',NULL,NULL,NULL),(259,'23-27',0,'2023-08-06 11:25:41',NULL,NULL,NULL),(260,'23-26',0,'2023-08-07 12:13:32',NULL,NULL,NULL),(261,'27-32',0,'2023-08-07 12:17:05',NULL,NULL,NULL),(262,'33-37',0,'2023-08-07 12:18:44',NULL,NULL,NULL),(263,'38-41',0,'2023-08-07 12:20:47',NULL,NULL,NULL),(264,'14 - L',0,'2023-08-16 17:04:54',NULL,NULL,NULL),(265,'N.36',0,'2023-08-30 13:00:33',NULL,NULL,NULL),(266,'N.24',0,'2023-08-30 13:01:04',NULL,NULL,NULL),(267,'SN',0,'2023-12-05 15:45:11',NULL,NULL,NULL),(268,'14-26',0,'2024-02-06 09:54:24',NULL,NULL,NULL),(269,'36-38',0,'2024-02-06 10:10:33',NULL,NULL,NULL),(270,'38-42',0,'2024-02-07 19:03:14',NULL,NULL,NULL),(271,'6.9',0,'2024-02-09 10:11:58',NULL,NULL,NULL),(272,'13-13',0,'2024-02-09 10:13:16',NULL,NULL,NULL),(273,'16-S-M',0,'2024-02-20 17:20:33',NULL,NULL,NULL),(274,'28-30-32-34',0,'2024-02-22 17:47:10',NULL,NULL,NULL),(275,'6-8-10-12-14- 16',0,'2024-08-29 15:36:40',NULL,NULL,NULL),(276,'2 1/2 PLZ',0,'2024-08-29 15:58:52',NULL,NULL,NULL),(277,'CERO',0,'2024-11-29 19:18:37',NULL,NULL,NULL),(278,'SML',0,'2024-12-17 16:11:00',NULL,NULL,NULL),(279,'26- 34',0,'2024-12-17 18:52:35',NULL,NULL,NULL),(280,'SIN TALLA ',0,'2024-12-23 15:57:43',NULL,NULL,NULL),(281,'30-38',0,'2025-01-04 15:08:06',NULL,NULL,NULL),(282,'30-35',0,'2025-02-10 10:06:32',NULL,NULL,NULL),(283,'36-39',0,'2025-02-10 10:10:44',NULL,NULL,NULL),(284,'S M ',0,'2025-03-21 07:37:10',NULL,NULL,NULL),(285,'SML-XL',0,'2025-06-07 08:20:15',NULL,NULL,NULL),(286,'T4-14',0,'2025-06-07 08:47:45',NULL,NULL,NULL),(287,'T2- 8',0,'2025-06-07 08:55:12',NULL,NULL,NULL),(288,'14 1/2',0,'2025-08-06 09:52:44',NULL,NULL,NULL),(289,'18 1/2',0,'2025-08-07 14:27:04',NULL,NULL,NULL),(290,'08',0,'2025-10-13 11:27:53',NULL,NULL,NULL);
/*!40000 ALTER TABLE `Gen_ProductoTalla` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Lo_Almacen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Lo_Almacen` (
  `IdAlmacen` int(11) NOT NULL AUTO_INCREMENT,
  `Almacen` varchar(255) DEFAULT NULL,
  `Anulado` bit(1) DEFAULT NULL,
  `FechaReg` datetime DEFAULT NULL,
  `UsuarioReg` varchar(255) DEFAULT NULL,
  `FechaMod` datetime DEFAULT NULL,
  `UsuarioMod` varchar(255) DEFAULT NULL,
  `Direccion` varchar(255) DEFAULT NULL,
  `EsPrincipal` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`IdAlmacen`),
  UNIQUE KEY `IdAlmacen_UNIQUE` (`IdAlmacen`),
  UNIQUE KEY `Almacen_UNIQUE` (`Almacen`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Lo_Almacen` WRITE;
/*!40000 ALTER TABLE `Lo_Almacen` DISABLE KEYS */;
INSERT INTO `Lo_Almacen` VALUES (1,'PRINCIPAL',0x00,NULL,NULL,NULL,NULL,'',1);
/*!40000 ALTER TABLE `Lo_Almacen` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Lo_Impuesto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Lo_Impuesto` (
  `IdImpuesto` int(11) NOT NULL AUTO_INCREMENT,
  `Impuesto` varchar(255) DEFAULT NULL,
  `VaEnDetalle` bit(1) DEFAULT NULL,
  PRIMARY KEY (`IdImpuesto`),
  UNIQUE KEY `UNIQUE` (`Impuesto`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Lo_Impuesto` WRITE;
/*!40000 ALTER TABLE `Lo_Impuesto` DISABLE KEYS */;
INSERT INTO `Lo_Impuesto` VALUES (1,'PERCEPCION',NULL),(2,'ISC',NULL),(3,NULL,NULL);
/*!40000 ALTER TABLE `Lo_Impuesto` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Lo_Movimiento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Lo_Movimiento` (
  `IdMovimientoTipo` int(11) NOT NULL,
  `IdProveedor` int(11) NOT NULL,
  `Serie` varchar(255) NOT NULL,
  `Numero` int(11) NOT NULL,
  `MovimientoFecha` datetime DEFAULT NULL,
  `IdAlmacenOrigen` int(11) DEFAULT NULL,
  `IdAlmacenDestino` int(11) DEFAULT NULL,
  `Observacion` text DEFAULT NULL,
  `Anulado` bit(1) DEFAULT NULL,
  `FechaReg` datetime DEFAULT NULL,
  `UsuarioReg` varchar(255) DEFAULT NULL,
  `FechaMod` datetime DEFAULT NULL,
  `UsuarioMod` varchar(255) DEFAULT NULL,
  `Hash` varchar(255) DEFAULT NULL,
  `Percepcion` float DEFAULT NULL,
  `FechaStock` datetime DEFAULT NULL,
  `EsCredito` tinyint(1) DEFAULT NULL,
  `FechaVenCredito` datetime DEFAULT NULL,
  `FechaPeriodoTributario` int(11) NOT NULL,
  `TipoCambio` float DEFAULT NULL,
  `Moneda` varchar(255) DEFAULT NULL,
  `PartidaDist` varchar(255) DEFAULT NULL,
  `PartidaProv` varchar(255) DEFAULT NULL,
  `PartidaDpto` varchar(255) DEFAULT NULL,
  `LlegadaDist` varchar(255) DEFAULT NULL,
  `LlegadaProv` varchar(255) DEFAULT NULL,
  `LlegadaDpto` varchar(255) DEFAULT NULL,
  `DestinatarioRazonSocial` varchar(255) DEFAULT NULL,
  `DestinatarioRUC` varchar(255) DEFAULT NULL,
  `TransporteNumPlaca` varchar(255) DEFAULT NULL,
  `TransporteNumContrato` varchar(255) DEFAULT NULL,
  `TransporteNumLicencia` varchar(255) DEFAULT NULL,
  `TransporteRazonSocial` varchar(255) DEFAULT NULL,
  `TransporteRUC` varchar(255) DEFAULT NULL,
  `IdDocVenta` int(11) DEFAULT NULL,
  `CodSunatModifica` varchar(255) DEFAULT NULL,
  `NroComprobanteModifica` varchar(255) DEFAULT NULL,
  `NotaIdMotivo` varchar(255) DEFAULT NULL,
  `NotaDescMotivo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`IdMovimientoTipo`,`IdProveedor`,`Serie`,`Numero`),
  UNIQUE KEY `unique` (`Hash`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Lo_Movimiento` WRITE;
/*!40000 ALTER TABLE `Lo_Movimiento` DISABLE KEYS */;
INSERT INTO `Lo_Movimiento` VALUES (4,107,'INICIAL',1,'2026-10-02 15:51:06',-1,1,'INGRESO INICIAL DE INVENTARIO | Archivo: iceden estock 2.xlsx',0x00,'2026-10-02 15:51:06','superadmin',NULL,NULL,'INI-20261002225106-f68c0ee6',0,'2026-10-02 15:51:06',0,NULL,202610,1,'PEN',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL);
/*!40000 ALTER TABLE `Lo_Movimiento` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Lo_MovimientoDetalle`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Lo_MovimientoDetalle` (
  `hashMovimiento` varchar(255) NOT NULL,
  `IdProducto` int(11) NOT NULL,
  `Cantidad` float NOT NULL,
  `TieneIgv` bit(1) DEFAULT NULL,
  `Precio` float NOT NULL,
  `ISC` float DEFAULT NULL,
  `FLETE` float DEFAULT NULL,
  `IdLote` int(11) DEFAULT NULL,
  `FechaVen` date DEFAULT NULL,
  `PesoTotal` varchar(255) DEFAULT NULL,
  `Descripcion` text DEFAULT NULL,
  PRIMARY KEY (`hashMovimiento`,`IdProducto`,`Precio`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Lo_MovimientoDetalle` WRITE;
/*!40000 ALTER TABLE `Lo_MovimientoDetalle` DISABLE KEYS */;
INSERT INTO `Lo_MovimientoDetalle` VALUES ('INI-20261002225106-f68c0ee6',2,12,0x00,297.4,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 3'),('INI-20261002225106-f68c0ee6',3,14,0x00,232.824,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 4'),('INI-20261002225106-f68c0ee6',4,6,0x00,205.48,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 5'),('INI-20261002225106-f68c0ee6',5,8,0x00,228.5,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 6'),('INI-20261002225106-f68c0ee6',6,8,0x00,248.1,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 7'),('INI-20261002225106-f68c0ee6',7,42,0x00,346.12,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 8'),('INI-20261002225106-f68c0ee6',8,4,0x00,426.42,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 9'),('INI-20261002225106-f68c0ee6',9,2,0x00,413.98,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 10'),('INI-20261002225106-f68c0ee6',10,3,0x00,505.76,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 11'),('INI-20261002225106-f68c0ee6',11,4,0x00,532.55,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 12'),('INI-20261002225106-f68c0ee6',12,86,0x00,133.45,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 13'),('INI-20261002225106-f68c0ee6',13,16,0x00,185.19,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 14'),('INI-20261002225106-f68c0ee6',14,6,0x00,191.31,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 15'),('INI-20261002225106-f68c0ee6',15,10,0x00,202.34,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 16'),('INI-20261002225106-f68c0ee6',16,4,0x00,229.19,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 17'),('INI-20261002225106-f68c0ee6',18,3,0x00,372.61,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 19'),('INI-20261002225106-f68c0ee6',19,13,0x00,418.8,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 20'),('INI-20261002225106-f68c0ee6',27,2,0x00,238.59,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 28'),('INI-20261002225106-f68c0ee6',32,1,0x00,355.49,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 33'),('INI-20261002225106-f68c0ee6',35,2,0x00,213.36,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 36'),('INI-20261002225106-f68c0ee6',40,19,0x00,188.5,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 41'),('INI-20261002225106-f68c0ee6',41,66,0x00,210.39,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 42'),('INI-20261002225106-f68c0ee6',42,5,0x00,222.48,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 43'),('INI-20261002225106-f68c0ee6',43,30,0x00,217.48,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 44'),('INI-20261002225106-f68c0ee6',45,7,0x00,278.6,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 46'),('INI-20261002225106-f68c0ee6',46,35,0x00,210.12,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 47'),('INI-20261002225106-f68c0ee6',47,36,0x00,224.04,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 48'),('INI-20261002225106-f68c0ee6',48,20,0x00,249.48,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 49'),('INI-20261002225106-f68c0ee6',49,27,0x00,251.92,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 50'),('INI-20261002225106-f68c0ee6',52,4,0x00,397.88,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 53'),('INI-20261002225106-f68c0ee6',53,10,0x00,407.98,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 54'),('INI-20261002225106-f68c0ee6',54,5,0x00,486.28,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 55'),('INI-20261002225106-f68c0ee6',55,10,0x00,604.44,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 56'),('INI-20261002225106-f68c0ee6',60,2,0x00,388.21,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 61'),('INI-20261002225106-f68c0ee6',61,2,0x00,39.03,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 62'),('INI-20261002225106-f68c0ee6',62,7,0x00,38.67,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 63'),('INI-20261002225106-f68c0ee6',65,3,0x00,54.07,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 66'),('INI-20261002225106-f68c0ee6',66,5,0x00,54.67,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 67'),('INI-20261002225106-f68c0ee6',67,4,0x00,62.63,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 68'),('INI-20261002225106-f68c0ee6',72,6,0x00,156.71,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 73'),('INI-20261002225106-f68c0ee6',73,8,0x00,81.7,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 74'),('INI-20261002225106-f68c0ee6',83,3,0x00,27,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 84'),('INI-20261002225106-f68c0ee6',84,80,0x00,1,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 85'),('INI-20261002225106-f68c0ee6',85,22,0x00,1,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 86'),('INI-20261002225106-f68c0ee6',87,6,0x00,228.651,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 88'),('INI-20261002225106-f68c0ee6',88,3,0x00,226.8,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 89'),('INI-20261002225106-f68c0ee6',90,10,0x00,33,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 91'),('INI-20261002225106-f68c0ee6',91,22,0x00,33,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 92'),('INI-20261002225106-f68c0ee6',93,48,0x00,7,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 94'),('INI-20261002225106-f68c0ee6',95,19,0x00,8.2,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 96'),('INI-20261002225106-f68c0ee6',96,1,0x00,8.5,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 97'),('INI-20261002225106-f68c0ee6',97,7,0x00,39,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 98'),('INI-20261002225106-f68c0ee6',99,8,0x00,43,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 100'),('INI-20261002225106-f68c0ee6',101,2,0x00,60,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 102'),('INI-20261002225106-f68c0ee6',102,4,0x00,6.7,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 103'),('INI-20261002225106-f68c0ee6',104,110,0x00,4,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 105'),('INI-20261002225106-f68c0ee6',106,96,0x00,3.5,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 107'),('INI-20261002225106-f68c0ee6',107,19,0x00,0.1,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 108'),('INI-20261002225106-f68c0ee6',108,5,0x00,0.5,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 109'),('INI-20261002225106-f68c0ee6',109,4,0x00,183.81,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 110'),('INI-20261002225106-f68c0ee6',110,10,0x00,153.16,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 111'),('INI-20261002225106-f68c0ee6',111,6,0x00,128.89,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 112'),('INI-20261002225106-f68c0ee6',112,79,0x00,5,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 113'),('INI-20261002225106-f68c0ee6',114,1,0x00,4,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 115'),('INI-20261002225106-f68c0ee6',115,77,0x00,6,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 116'),('INI-20261002225106-f68c0ee6',116,6,0x00,9,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 117'),('INI-20261002225106-f68c0ee6',119,10,0x00,43.64,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 120'),('INI-20261002225106-f68c0ee6',120,7,0x00,35.25,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 121'),('INI-20261002225106-f68c0ee6',121,6,0x00,40.87,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 122'),('INI-20261002225106-f68c0ee6',122,9,0x00,48.03,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 123'),('INI-20261002225106-f68c0ee6',123,10,0x00,53.98,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 124'),('INI-20261002225106-f68c0ee6',124,4,0x00,57.74,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 125'),('INI-20261002225106-f68c0ee6',125,8,0x00,60.83,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 126'),('INI-20261002225106-f68c0ee6',126,8,0x00,71.9,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 127'),('INI-20261002225106-f68c0ee6',128,36,0x00,277.7,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 129'),('INI-20261002225106-f68c0ee6',129,5,0x00,6.7,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 130'),('INI-20261002225106-f68c0ee6',130,10,0x00,3.35,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 131'),('INI-20261002225106-f68c0ee6',142,6,0x00,198.33,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 143'),('INI-20261002225106-f68c0ee6',145,5,0x00,386.16,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 146'),('INI-20261002225106-f68c0ee6',146,3,0x00,61.4,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 147'),('INI-20261002225106-f68c0ee6',153,19,0x00,280.58,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 154'),('INI-20261002225106-f68c0ee6',157,12,0x00,155.29,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 158'),('INI-20261002225106-f68c0ee6',168,4,0x00,347.45,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 169'),('INI-20261002225106-f68c0ee6',169,45,0x00,261.67,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 170'),('INI-20261002225106-f68c0ee6',171,2,0x00,49,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 172'),('INI-20261002225106-f68c0ee6',177,3,0x00,20,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 178'),('INI-20261002225106-f68c0ee6',179,21,0x00,20,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 180'),('INI-20261002225106-f68c0ee6',180,3,0x00,20,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 181'),('INI-20261002225106-f68c0ee6',181,10,0x00,40,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 182'),('INI-20261002225106-f68c0ee6',182,13,0x00,40,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 183'),('INI-20261002225106-f68c0ee6',183,2,0x00,48,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 184'),('INI-20261002225106-f68c0ee6',184,9,0x00,45,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 185'),('INI-20261002225106-f68c0ee6',185,2,0x00,70,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 186'),('INI-20261002225106-f68c0ee6',186,7,0x00,80,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 187'),('INI-20261002225106-f68c0ee6',187,8,0x00,100,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 188'),('INI-20261002225106-f68c0ee6',188,1,0x00,10,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 189'),('INI-20261002225106-f68c0ee6',195,1,0x00,89.18,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 196'),('INI-20261002225106-f68c0ee6',197,11,0x00,60,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 198'),('INI-20261002225106-f68c0ee6',201,6,0x00,375.49,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 202'),('INI-20261002225106-f68c0ee6',203,3,0x00,208.76,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 204'),('INI-20261002225106-f68c0ee6',210,2,0x00,675.57,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 211'),('INI-20261002225106-f68c0ee6',211,2,0x00,7,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 212'),('INI-20261002225106-f68c0ee6',218,2,0x00,657.411,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 219'),('INI-20261002225106-f68c0ee6',224,5,0x00,19,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 225'),('INI-20261002225106-f68c0ee6',228,2,0x00,9,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 229'),('INI-20261002225106-f68c0ee6',229,4,0x00,259.15,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 230'),('INI-20261002225106-f68c0ee6',230,3,0x00,656.38,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 231'),('INI-20261002225106-f68c0ee6',231,7,0x00,554.59,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 232'),('INI-20261002225106-f68c0ee6',232,4,0x00,3,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 233'),('INI-20261002225106-f68c0ee6',233,5,0x00,3,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 234'),('INI-20261002225106-f68c0ee6',236,4,0x00,40,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 237'),('INI-20261002225106-f68c0ee6',237,6,0x00,40,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 238'),('INI-20261002225106-f68c0ee6',238,1,0x00,30,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 239'),('INI-20261002225106-f68c0ee6',250,27,0x00,1,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 251'),('INI-20261002225106-f68c0ee6',255,9,0x00,52.88,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 256'),('INI-20261002225106-f68c0ee6',263,5,0x00,30,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 264'),('INI-20261002225106-f68c0ee6',264,8,0x00,30,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 265'),('INI-20261002225106-f68c0ee6',269,3,0x00,309.41,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 270'),('INI-20261002225106-f68c0ee6',286,43,0x00,57,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 287'),('INI-20261002225106-f68c0ee6',294,13,0x00,407.47,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 295'),('INI-20261002225106-f68c0ee6',298,2,0x00,32.8,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 299'),('INI-20261002225106-f68c0ee6',299,1,0x00,334.89,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 300'),('INI-20261002225106-f68c0ee6',302,25,0x00,126.9,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 303'),('INI-20261002225106-f68c0ee6',305,3,0x00,252.2,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 306'),('INI-20261002225106-f68c0ee6',306,5,0x00,157.7,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 307'),('INI-20261002225106-f68c0ee6',317,9,0x00,57.1,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 318'),('INI-20261002225106-f68c0ee6',323,4,0x00,481.79,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 324'),('INI-20261002225106-f68c0ee6',327,1,0x00,285.49,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 328'),('INI-20261002225106-f68c0ee6',329,8,0x00,209.3,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 330'),('INI-20261002225106-f68c0ee6',335,7,0x00,167,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 336'),('INI-20261002225106-f68c0ee6',340,1,0x00,56,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 341'),('INI-20261002225106-f68c0ee6',342,3,0x00,6.2,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 343'),('INI-20261002225106-f68c0ee6',343,3,0x00,243.88,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 344'),('INI-20261002225106-f68c0ee6',344,1,0x00,226.2,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 345'),('INI-20261002225106-f68c0ee6',345,9,0x00,256.2,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 346'),('INI-20261002225106-f68c0ee6',346,1,0x00,252.9,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 347'),('INI-20261002225106-f68c0ee6',353,1,0x00,85,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 354'),('INI-20261002225106-f68c0ee6',354,3,0x00,277.47,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 355'),('INI-20261002225106-f68c0ee6',357,104,0x00,83,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 358'),('INI-20261002225106-f68c0ee6',359,1,0x00,61,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 360'),('INI-20261002225106-f68c0ee6',360,5,0x00,610.72,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 361'),('INI-20261002225106-f68c0ee6',363,3,0x00,247.21,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 364'),('INI-20261002225106-f68c0ee6',364,2,0x00,328.89,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 365'),('INI-20261002225106-f68c0ee6',365,2,0x00,123.74,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 366'),('INI-20261002225106-f68c0ee6',366,2,0x00,164.43,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 367'),('INI-20261002225106-f68c0ee6',371,8,0x00,79,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 372'),('INI-20261002225106-f68c0ee6',372,15,0x00,168.5,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 373'),('INI-20261002225106-f68c0ee6',373,2,0x00,208.76,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 374'),('INI-20261002225106-f68c0ee6',374,13,0x00,219,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 375'),('INI-20261002225106-f68c0ee6',375,1,0x00,302.48,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 376'),('INI-20261002225106-f68c0ee6',376,3,0x00,252.2,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 377'),('INI-20261002225106-f68c0ee6',377,6,0x00,320,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 378'),('INI-20261002225106-f68c0ee6',378,2,0x00,558.2,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 379'),('INI-20261002225106-f68c0ee6',380,10,0x00,202.34,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 381'),('INI-20261002225106-f68c0ee6',381,3,0x00,205.07,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 382'),('INI-20261002225106-f68c0ee6',382,2,0x00,175,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 383'),('INI-20261002225106-f68c0ee6',383,14,0x00,182.38,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 384'),('INI-20261002225106-f68c0ee6',384,1,0x00,191.31,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 385'),('INI-20261002225106-f68c0ee6',385,3,0x00,297.4,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 386'),('INI-20261002225106-f68c0ee6',386,12,0x00,205.48,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 387'),('INI-20261002225106-f68c0ee6',387,3,0x00,205.48,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 388'),('INI-20261002225106-f68c0ee6',388,6,0x00,346.12,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 389'),('INI-20261002225106-f68c0ee6',389,6,0x00,277.7,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 390'),('INI-20261002225106-f68c0ee6',390,2,0x00,261.67,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 391'),('INI-20261002225106-f68c0ee6',392,3,0x00,210.78,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 393'),('INI-20261002225106-f68c0ee6',393,2,0x00,222.48,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 394'),('INI-20261002225106-f68c0ee6',394,10,0x00,215.17,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 395'),('INI-20261002225106-f68c0ee6',395,7,0x00,224.04,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 396'),('INI-20261002225106-f68c0ee6',396,5,0x00,249.48,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 397'),('INI-20261002225106-f68c0ee6',397,4,0x00,251.92,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 398'),('INI-20261002225106-f68c0ee6',399,11,0x00,326.77,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 400'),('INI-20261002225106-f68c0ee6',401,5,0x00,241.98,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 402'),('INI-20261002225106-f68c0ee6',402,2,0x00,150,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 403'),('INI-20261002225106-f68c0ee6',403,4,0x00,554.59,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 404'),('INI-20261002225106-f68c0ee6',404,6,0x00,217.48,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 405'),('INI-20261002225106-f68c0ee6',405,4,0x00,395.8,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 406'),('INI-20261002225106-f68c0ee6',406,2,0x00,388.19,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 407'),('INI-20261002225106-f68c0ee6',411,2,0x00,267.72,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 412'),('INI-20261002225106-f68c0ee6',413,2,0x00,257.71,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 414'),('INI-20261002225106-f68c0ee6',414,4,0x00,277.56,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 415'),('INI-20261002225106-f68c0ee6',415,1,0x00,307.1,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 416'),('INI-20261002225106-f68c0ee6',416,3,0x00,339.48,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 417'),('INI-20261002225106-f68c0ee6',417,1,0x00,339.48,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 418'),('INI-20261002225106-f68c0ee6',418,2,0x00,547.16,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 419'),('INI-20261002225106-f68c0ee6',420,3,0x00,870,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 421'),('INI-20261002225106-f68c0ee6',425,1,0x00,675.56,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 426'),('INI-20261002225106-f68c0ee6',428,3,0x00,643.6,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 429'),('INI-20261002225106-f68c0ee6',429,3,0x00,315.62,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 430'),('INI-20261002225106-f68c0ee6',430,1,0x00,317.91,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 431'),('INI-20261002225106-f68c0ee6',433,8,0x00,194.69,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 434'),('INI-20261002225106-f68c0ee6',436,5,0x00,197.2,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 437'),('INI-20261002225106-f68c0ee6',437,2,0x00,245.23,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 438'),('INI-20261002225106-f68c0ee6',440,1,0x00,651.29,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 441'),('INI-20261002225106-f68c0ee6',441,4,0x00,179.014,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 442'),('INI-20261002225106-f68c0ee6',445,2,0x00,386.16,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 446'),('INI-20261002225106-f68c0ee6',447,8,0x00,20,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 448'),('INI-20261002225106-f68c0ee6',448,6,0x00,76,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 449'),('INI-20261002225106-f68c0ee6',449,6,0x00,66,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 450'),('INI-20261002225106-f68c0ee6',450,6,0x00,46,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 451'),('INI-20261002225106-f68c0ee6',451,6,0x00,17,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 452'),('INI-20261002225106-f68c0ee6',452,3,0x00,12,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 453'),('INI-20261002225106-f68c0ee6',453,2,0x00,11,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 454'),('INI-20261002225106-f68c0ee6',454,1,0x00,21,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 455'),('INI-20261002225106-f68c0ee6',455,2,0x00,28,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 456'),('INI-20261002225106-f68c0ee6',457,6,0x00,46,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 458'),('INI-20261002225106-f68c0ee6',458,2,0x00,24,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 459'),('INI-20261002225106-f68c0ee6',459,2,0x00,47,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 460'),('INI-20261002225106-f68c0ee6',460,1,0x00,106,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 461'),('INI-20261002225106-f68c0ee6',461,3,0x00,87,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 462'),('INI-20261002225106-f68c0ee6',462,3,0x00,111,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 463'),('INI-20261002225106-f68c0ee6',463,3,0x00,242,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 464'),('INI-20261002225106-f68c0ee6',468,4,0x00,20,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 469'),('INI-20261002225106-f68c0ee6',469,2,0x00,12,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 470'),('INI-20261002225106-f68c0ee6',470,1,0x00,47,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 471'),('INI-20261002225106-f68c0ee6',471,2,0x00,24,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 472'),('INI-20261002225106-f68c0ee6',472,2,0x00,21,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 473'),('INI-20261002225106-f68c0ee6',473,1,0x00,21,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 474'),('INI-20261002225106-f68c0ee6',474,1,0x00,21,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 475'),('INI-20261002225106-f68c0ee6',475,3,0x00,11,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 476'),('INI-20261002225106-f68c0ee6',476,4,0x00,11,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 477'),('INI-20261002225106-f68c0ee6',477,1,0x00,11,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 478'),('INI-20261002225106-f68c0ee6',478,1,0x00,11,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 479'),('INI-20261002225106-f68c0ee6',489,1,0x00,0.1,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 490'),('INI-20261002225106-f68c0ee6',490,2,0x00,11.7,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 491'),('INI-20261002225106-f68c0ee6',491,24,0x00,88,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 492'),('INI-20261002225106-f68c0ee6',493,2,0x00,171,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 494'),('INI-20261002225106-f68c0ee6',494,3,0x00,151,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 495'),('INI-20261002225106-f68c0ee6',495,3,0x00,166,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 496'),('INI-20261002225106-f68c0ee6',496,429,0x00,2.7,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 497'),('INI-20261002225106-f68c0ee6',497,367,0x00,2.7,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 498'),('INI-20261002225106-f68c0ee6',498,1,0x00,4.7,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 499'),('INI-20261002225106-f68c0ee6',499,1,0x00,4.7,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 500'),('INI-20261002225106-f68c0ee6',500,47,0x00,2.9,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 501'),('INI-20261002225106-f68c0ee6',501,1,0x00,4.4,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 502'),('INI-20261002225106-f68c0ee6',502,2,0x00,656.38,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 503'),('INI-20261002225106-f68c0ee6',508,2,0x00,278.958,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 509'),('INI-20261002225106-f68c0ee6',509,20,0x00,167.14,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 510'),('INI-20261002225106-f68c0ee6',510,5,0x00,0.1,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 511'),('INI-20261002225106-f68c0ee6',511,5,0x00,46,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 512'),('INI-20261002225106-f68c0ee6',513,1,0x00,21,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 514'),('INI-20261002225106-f68c0ee6',514,1,0x00,285,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 515'),('INI-20261002225106-f68c0ee6',515,11,0x00,9.9,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 516'),('INI-20261002225106-f68c0ee6',516,10,0x00,9.9,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 517'),('INI-20261002225106-f68c0ee6',517,24,0x00,8.2,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 518'),('INI-20261002225106-f68c0ee6',518,5,0x00,9.9,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 519'),('INI-20261002225106-f68c0ee6',519,15,0x00,9.9,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 520'),('INI-20261002225106-f68c0ee6',520,21,0x00,9.9,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 521'),('INI-20261002225106-f68c0ee6',525,4,0x00,641,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 526'),('INI-20261002225106-f68c0ee6',527,4,0x00,638.8,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 528'),('INI-20261002225106-f68c0ee6',571,4,0x00,1067.8,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 572'),('INI-20261002225106-f68c0ee6',589,1,0x00,715,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 590'),('INI-20261002225106-f68c0ee6',595,11,0x00,0.1,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 596'),('INI-20261002225106-f68c0ee6',596,105,0x00,0.1,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 597'),('INI-20261002225106-f68c0ee6',597,45,0x00,20,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 598'),('INI-20261002225106-f68c0ee6',598,11,0x00,18,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 599'),('INI-20261002225106-f68c0ee6',601,38,0x00,5.2,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 602'),('INI-20261002225106-f68c0ee6',602,11,0x00,5.2,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 603'),('INI-20261002225106-f68c0ee6',603,12,0x00,5.2,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 604'),('INI-20261002225106-f68c0ee6',604,51,0x00,5.2,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 605'),('INI-20261002225106-f68c0ee6',609,31,0x00,115.23,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 610'),('INI-20261002225106-f68c0ee6',611,29,0x00,5.2,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 612'),('INI-20261002225106-f68c0ee6',618,70,0x00,0.01,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 619'),('INI-20261002225106-f68c0ee6',619,10,0x00,0.01,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 620'),('INI-20261002225106-f68c0ee6',653,4,0x00,623.08,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 654'),('INI-20261002225106-f68c0ee6',657,8,0x00,645.6,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 658'),('INI-20261002225106-f68c0ee6',675,4,0x00,530,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 676'),('INI-20261002225106-f68c0ee6',681,12,0x00,26,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 682'),('INI-20261002225106-f68c0ee6',682,15,0x00,49,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 683'),('INI-20261002225106-f68c0ee6',683,4,0x00,49,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 684'),('INI-20261002225106-f68c0ee6',684,5,0x00,49,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 685'),('INI-20261002225106-f68c0ee6',686,14,0x00,49,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 687'),('INI-20261002225106-f68c0ee6',696,4,0x00,807.54,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 697'),('INI-20261002225106-f68c0ee6',715,2,0x00,505.61,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 716'),('INI-20261002225106-f68c0ee6',716,1,0x00,470.99,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 717'),('INI-20261002225106-f68c0ee6',717,1,0x00,373.39,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 718'),('INI-20261002225106-f68c0ee6',719,1,0x00,428.73,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 720'),('INI-20261002225106-f68c0ee6',723,17,0x00,1,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 724'),('INI-20261002225106-f68c0ee6',731,59,0x00,0.1,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 732'),('INI-20261002225106-f68c0ee6',732,40,0x00,118.97,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 733'),('INI-20261002225106-f68c0ee6',734,2,0x00,2.1,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 735'),('INI-20261002225106-f68c0ee6',735,4,0x00,2.1,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 736'),('INI-20261002225106-f68c0ee6',744,4,0x00,530,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 745'),('INI-20261002225106-f68c0ee6',750,4,0x00,638.8,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 751'),('INI-20261002225106-f68c0ee6',768,25,0x00,155.5,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 769'),('INI-20261002225106-f68c0ee6',769,22,0x00,0.1,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 770'),('INI-20261002225106-f68c0ee6',770,3,0x00,0.1,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 771'),('INI-20261002225106-f68c0ee6',771,5,0x00,2.1,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 772'),('INI-20261002225106-f68c0ee6',773,2,0x00,0.1,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 774'),('INI-20261002225106-f68c0ee6',785,2,0x00,558.2,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 786'),('INI-20261002225106-f68c0ee6',787,1,0x00,315.62,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 788'),('INI-20261002225106-f68c0ee6',794,4,0x00,413.98,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 795'),('INI-20261002225106-f68c0ee6',803,5,0x00,256.2,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 804'),('INI-20261002225106-f68c0ee6',804,13,0x00,116.9,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 805'),('INI-20261002225106-f68c0ee6',806,2,0x00,483.88,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 807'),('INI-20261002225106-f68c0ee6',808,2,0x00,126.9,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 809'),('INI-20261002225106-f68c0ee6',809,17,0x00,6.5,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 810'),('INI-20261002225106-f68c0ee6',817,4,0x00,197.2,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 818'),('INI-20261002225106-f68c0ee6',819,2,0x00,510.79,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 820'),('INI-20261002225106-f68c0ee6',822,4,0x00,530,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 823'),('INI-20261002225106-f68c0ee6',823,4,0x00,666,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 824'),('INI-20261002225106-f68c0ee6',824,4,0x00,645.6,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 825'),('INI-20261002225106-f68c0ee6',826,4,0x00,632,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 827'),('INI-20261002225106-f68c0ee6',831,4,0x00,717,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 832'),('INI-20261002225106-f68c0ee6',833,4,0x00,972,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 834'),('INI-20261002225106-f68c0ee6',835,4,0x00,812.2,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 836'),('INI-20261002225106-f68c0ee6',836,4,0x00,812.2,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 837'),('INI-20261002225106-f68c0ee6',837,12,0x00,46,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 838'),('INI-20261002225106-f68c0ee6',838,36,0x00,46,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 839'),('INI-20261002225106-f68c0ee6',839,10,0x00,46,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 840'),('INI-20261002225106-f68c0ee6',840,16,0x00,39.5,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 841'),('INI-20261002225106-f68c0ee6',841,10,0x00,39.5,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 842'),('INI-20261002225106-f68c0ee6',842,6,0x00,93,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 843'),('INI-20261002225106-f68c0ee6',843,8,0x00,76,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 844'),('INI-20261002225106-f68c0ee6',844,1,0x00,0,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 845'),('INI-20261002225106-f68c0ee6',845,2,0x00,0,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 846'),('INI-20261002225106-f68c0ee6',846,12,0x00,57,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 847'),('INI-20261002225106-f68c0ee6',847,11,0x00,67,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 848'),('INI-20261002225106-f68c0ee6',848,7,0x00,67,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 849'),('INI-20261002225106-f68c0ee6',849,10,0x00,57,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 850'),('INI-20261002225106-f68c0ee6',850,10,0x00,101,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 851'),('INI-20261002225106-f68c0ee6',851,6,0x00,56,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 852'),('INI-20261002225106-f68c0ee6',852,6,0x00,61,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 853'),('INI-20261002225106-f68c0ee6',853,2,0x00,55,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 854'),('INI-20261002225106-f68c0ee6',854,2,0x00,65,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 855'),('INI-20261002225106-f68c0ee6',855,23,0x00,3.7,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 856'),('INI-20261002225106-f68c0ee6',856,12,0x00,15.15,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 857'),('INI-20261002225106-f68c0ee6',861,4,0x00,628.4,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 862'),('INI-20261002225106-f68c0ee6',863,4,0x00,641.92,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 864'),('INI-20261002225106-f68c0ee6',864,4,0x00,807.54,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 865'),('INI-20261002225106-f68c0ee6',865,4,0x00,807,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 866'),('INI-20261002225106-f68c0ee6',866,4,0x00,807.54,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 867'),('INI-20261002225106-f68c0ee6',867,4,0x00,807.54,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 868'),('INI-20261002225106-f68c0ee6',870,4,0x00,141.68,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 871'),('INI-20261002225106-f68c0ee6',871,3,0x00,77.91,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 872'),('INI-20261002225106-f68c0ee6',872,4,0x00,99.36,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 873'),('INI-20261002225106-f68c0ee6',873,4,0x00,99.62,NULL,NULL,0,NULL,NULL,'Ingreso inicial desde Excel, fila 874');
/*!40000 ALTER TABLE `Lo_MovimientoDetalle` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Lo_MovimientoDetalleImpuesto`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Lo_MovimientoDetalleImpuesto` (
  `HashMovimientoDetalle` varchar(255) DEFAULT NULL,
  `IdImpuesto` int(11) DEFAULT NULL,
  `Importe` float DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Lo_MovimientoDetalleImpuesto` WRITE;
/*!40000 ALTER TABLE `Lo_MovimientoDetalleImpuesto` DISABLE KEYS */;
/*!40000 ALTER TABLE `Lo_MovimientoDetalleImpuesto` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Lo_MovimientoTipo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Lo_MovimientoTipo` (
  `IdMovimientoTipo` int(11) NOT NULL AUTO_INCREMENT,
  `TipoMovimiento` varchar(255) DEFAULT NULL,
  `Tipo` int(11) DEFAULT NULL,
  `VaRegCompra` bit(1) DEFAULT NULL,
  `CodSunat` varchar(255) DEFAULT NULL,
  `TipoMovSunat` varchar(255) DEFAULT NULL,
  `Anulado` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`IdMovimientoTipo`),
  UNIQUE KEY `TipoMovimiento2` (`TipoMovimiento`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=18 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Lo_MovimientoTipo` WRITE;
/*!40000 ALTER TABLE `Lo_MovimientoTipo` DISABLE KEYS */;
INSERT INTO `Lo_MovimientoTipo` VALUES (1,'COMPRA FACTURA',0,0x01,'01','02',0),(2,'COMPRA BOLETA',0,0x01,'03','02',0),(3,'COMPRA TICKET',0,0x01,'12','02',0),(4,'INGRESO',0,0x01,'0','99',0),(5,'SALIDA X DETERIORO',1,0x01,'0','13',1),(6,'TRASLADOS',2,0x01,'0','0',0),(9,'SALIDA',1,0x01,'99','12',0),(10,'DONACION',1,0x00,'00','00',1),(11,'SOBRANTES',0,0x00,'00','00',1),(12,'FALTANTES',1,0x00,'00','00',1),(13,'DEVOLUCION DE CLIENTE',0,0x01,'00','00',0),(15,'DEVOLUCION A PROVEEDOR',1,0x01,'00','00',0),(16,'RECEPCION DE DONACION',0,0x00,'00','00',1),(17,'GUIA REM. SALIDA VENTA',1,0x00,'00','00',1);
/*!40000 ALTER TABLE `Lo_MovimientoTipo` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Lo_OrdenCompra`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Lo_OrdenCompra` (
  `IdOrdenCompra` int(11) NOT NULL AUTO_INCREMENT,
  `IdAlmacen` int(11) DEFAULT NULL,
  `IdProveedor` int(11) NOT NULL,
  `FechaOrdenCompra` datetime DEFAULT NULL,
  `Total` float DEFAULT NULL,
  `Anio` varchar(45) DEFAULT NULL,
  `Numero` varchar(45) DEFAULT NULL,
  `Estado` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`IdOrdenCompra`,`IdProveedor`)
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Lo_OrdenCompra` WRITE;
/*!40000 ALTER TABLE `Lo_OrdenCompra` DISABLE KEYS */;
/*!40000 ALTER TABLE `Lo_OrdenCompra` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Lo_OrdenCompraDet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Lo_OrdenCompraDet` (
  `IdOrdenCompra` int(11) NOT NULL,
  `IdProducto` int(11) NOT NULL,
  `Cantidad` float DEFAULT NULL,
  `Precio` float DEFAULT NULL,
  PRIMARY KEY (`IdOrdenCompra`,`IdProducto`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Lo_OrdenCompraDet` WRITE;
/*!40000 ALTER TABLE `Lo_OrdenCompraDet` DISABLE KEYS */;
/*!40000 ALTER TABLE `Lo_OrdenCompraDet` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Lo_OrdenCompraMovimiento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Lo_OrdenCompraMovimiento` (
  `IdOrdenCompra` int(11) NOT NULL,
  `HashMovimiento` varchar(80) NOT NULL,
  `FechaReg` datetime NOT NULL,
  PRIMARY KEY (`IdOrdenCompra`),
  UNIQUE KEY `UX_OrdenCompraMovimiento_Hash` (`HashMovimiento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Lo_OrdenCompraMovimiento` WRITE;
/*!40000 ALTER TABLE `Lo_OrdenCompraMovimiento` DISABLE KEYS */;
/*!40000 ALTER TABLE `Lo_OrdenCompraMovimiento` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Lo_Proveedor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Lo_Proveedor` (
  `IdProveedor` int(11) NOT NULL AUTO_INCREMENT,
  `Proveedor` varchar(255) DEFAULT NULL,
  `Ruc` varchar(255) DEFAULT NULL,
  `Direccion` text DEFAULT NULL,
  `Observacion` text DEFAULT NULL,
  `Anulado` bit(1) DEFAULT NULL,
  `FechaReg` datetime DEFAULT NULL,
  `UsuarioReg` varchar(255) DEFAULT NULL,
  `FechaMod` datetime DEFAULT NULL,
  `UsuarioMod` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`IdProveedor`),
  UNIQUE KEY `UNIQUE` (`Proveedor`) USING BTREE,
  UNIQUE KEY `UNIQUE2` (`Ruc`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=108 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Lo_Proveedor` WRITE;
/*!40000 ALTER TABLE `Lo_Proveedor` DISABLE KEYS */;
INSERT INTO `Lo_Proveedor` VALUES (107,'INVENTARIO INICIAL','00000000000','','Proveedor interno para movimientos de apertura de inventario.',0x00,'2026-10-02 15:51:06','superadmin',NULL,NULL);
/*!40000 ALTER TABLE `Lo_Proveedor` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `prodstock`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prodstock` (
  `IdProducto` int(11) NOT NULL,
  `ProductoMarca` varchar(255) DEFAULT NULL,
  `ProductoCategoria` varchar(255) DEFAULT NULL,
  `FormaFarmaceutica` varchar(255) DEFAULT NULL,
  `Producto` varchar(255) DEFAULT NULL,
  `Stock` float DEFAULT NULL,
  PRIMARY KEY (`IdProducto`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `prodstock` WRITE;
/*!40000 ALTER TABLE `prodstock` DISABLE KEYS */;
INSERT INTO `prodstock` VALUES (316,'BALACK','ALGODÓN','INYECCION','POLO-316',4),(317,'BALACK','ALGODÓN','INYECCION','POLO-317',0),(318,'BALACK','ALGODÓN','INYECCION','POLO-318',0),(319,'BALACK','ALGODÓN','INYECCION','POLO-319',2),(296,'BOSTON','INTERIOR','INYECCION','CALZONCILLO-296',8),(297,'BOSTON','INTERIOR','INYECCION','CALZONCILLO-297',13),(298,'BOSTON','INTERIOR','INYECCION','CALZONCILLO-298',12),(299,'BOSTON','INTERIOR','INYECCION','CALZONCILLO-299',17),(300,'BOSTON','INTERIOR','INYECCION','CALZONCILLO-300',8),(301,'BOSTON','INTERIOR','INYECCION','CALZONCILLO-301',4),(302,'BOSTON','INTERIOR','INYECCION','CALZONCILLO-302',13),(303,'BOSTON','INTERIOR','INYECCION','CALZONCILLO-303',14),(304,'BOSTON','ACCESORIOS','INYECCION','BIVIDI-304',10),(305,'BOSTON','ACCESORIOS','INYECCION','BIVIDI-305',10),(306,'BOSTON','ACCESORIOS','INYECCION','BIVIDI-306',11),(307,'BOSTON','ACCESORIOS','INYECCION','BIVIDI-307',19),(308,'BOSTON','ACCESORIOS','INYECCION','BIVIDI-308',11),(309,'BOSTON','ACCESORIOS','INYECCION','BOXER-309',14),(310,'BOSTON','ACCESORIOS','INYECCION','BOXER-310',14),(311,'BOSTON','ACCESORIOS','INYECCION','BOXER-311',14),(312,'BOSTON','ACCESORIOS','INYECCION','BOXER-312',5),(206,'BRONCO/LOIS','JEANS','INYECCION','SHORT-206',10),(207,'BRONCO/LOIS','JEANS','INYECCION','SHORT-207',2),(208,'BRONCO/LOIS','JEANS','INYECCION','SHORT-208',1),(209,'BRONCO/LOIS','JEANS','INYECCION','SHORT-209',1),(210,'BRONCO/LOIS','JEANS','INYECCION','SHORT-210',9),(211,'BRONCO/LOIS','JEANS','INYECCION','SHORT-211',11),(212,'BRONCO/LOIS','JEANS','INYECCION','SHORT-212',7),(213,'BRONCO/LOIS','JEANS','INYECCION','SHORT-213',2),(214,'BRONCO/LOIS','JEANS','INYECCION','PANTALON-214',0),(215,'BRONCO/LOIS','JEANS','INYECCION','PANTALON-215',0),(216,'BRONCO/LOIS','JEANS','INYECCION','PANTALON-216',1),(217,'BRONCO/LOIS','JEANS','INYECCION','PANTALON-217',3),(218,'BRONCO/LOIS','JEANS','INYECCION','PANTALON-218',5),(219,'BRONCO/LOIS','JEANS','INYECCION','PANTALON-219',11),(220,'BRONCO/LOIS','JEANS','INYECCION','PANTALON-220',8),(221,'BRONCO/LOIS','JEANS','INYECCION','PANTALON-221',6),(222,'BRONCO/LOIS','ALGODÓN','INYECCION','POLO-222',8),(223,'BRONCO/LOIS','ALGODÓN','INYECCION','POLO-223',12),(224,'BRONCO/LOIS','ALGODÓN','INYECCION','POLO-224',9),(225,'BRONCO/LOIS','ALGODÓN','INYECCION','POLO-225',16),(68,'DONATELLI','VESTIR','INYECCION','CAMISA-VE68CA',14),(69,'DONATELLI','VESTIR','INYECCION','CAMISA -VE69CA',38),(70,'DONATELLI','VESTIR','INYECCION','CAMISA-VE70CA',12),(71,'DONATELLI','VESTIR','INYECCION','CAMISA-71',11),(72,'DONATELLI','VESTIR','INYECCION','CAMISA-VE72CA',50),(73,'DONATELLI','VESTIR','INYECCION','CAMISA-VE73CA',21),(74,'DONATELLI','VESTIR','INYECCION','CAMISA-VE74CA',14),(75,'DONATELLI','VESTIR','INYECCION','CAMISA-VE75CA',12),(76,'DONATELLI','VESTIR','INYECCION','CAMISA-VE76CA',9),(77,'DONATELLI','VESTIR','INYECCION','CAMISA-VE77CA',6),(78,'DONATELLI','VESTIR','INYECCION','PANTALON-VE78PA',5),(79,'DONATELLI','VESTIR','INYECCION','PANTALON-VE79PA',9),(80,'DONATELLI','VESTIR','INYECCION','PANTALON-VE80PA',4),(81,'DONATELLI','VESTIR','INYECCION','PANTALON-VE81PA',9),(82,'DONATELLI','VESTIR','INYECCION','PANTALON-VE82PA',11),(83,'DONATELLI','VESTIR','INYECCION','PANTALON -VE83PA',8),(85,'DONATELLI','VESTIR','INYECCION','CORBATAS-VE85CO',3),(443,'DONATELLI','VESTIR','INYECCION','CAMISA-VE443CA',0),(444,'DONATELLI','VESTIR','INYECCION','CAMISA-444',0),(445,'DONATELLI','VESTIR','INYECCION','CAMISA-445',0),(446,'DONATELLI','VESTIR','INYECCION','CAMISA-446',2),(447,'DONATELLI','VESTIR','INYECCION','CAMISA-447',1),(448,'DONATELLI','VESTIR','INYECCION','TERNOS-VE448TE',2),(449,'DONATELLI','VESTIR','INYECCION','TERNOS-VE449TE',2),(450,'DONATELLI','VESTIR','INYECCION','TERNOS-VE450TE',2),(451,'DONATELLI','VESTIR','INYECCION','TERNOS-VE451TE',2),(452,'DONATELLI','VESTIR','INYECCION','TERNOS-VE452TE',2),(371,'DONNY','ACCESORIOS','INYECCION','MEDIAS-AC371ME',47),(469,'EROS','JEANS','INYECCION','SHORT-JE469SH',5),(470,'EROS','JEANS','INYECCION','SHORT-JE470SH',7),(471,'EROS','JEANS','INYECCION','SHORT-JE471SH',3),(472,'EROS','JEANS','INYECCION','SHORT-JE472SH',6),(473,'EROS','JEANS','INYECCION','SHORT-JE473SH',6),(474,'EROS','JEANS','INYECCION','SHORT-JE474SH',5),(475,'EROS','JEANS','INYECCION','SHORT-JE475SH',9),(476,'EROS','JEANS','INYECCION','SHORT-JE476SH',0),(477,'EROS','DRILL','INYECCION','SHORT-DR477SH',9),(478,'EROS','DRILL','INYECCION','SHORT-DR478SH',10),(479,'EROS','DRILL','INYECCION','SHORT-DR479SH',9),(480,'EROS','DRILL','INYECCION','SHORT-DR480SH',0),(481,'EROS','DRILL','INYECCION','SHORT-DR481SH',12),(482,'EROS','DRILL','INYECCION','SHORT-DR482SH',8),(483,'EROS','DRILL','INYECCION','SHORT-DR483SH',9),(484,'EROS','DRILL','INYECCION','SHORT-DR484SH',1),(143,'FILIPPO ALPI','DRILL','INYECCION','PANTALON-143',16),(144,'FILIPPO ALPI','DRILL','INYECCION','PANTALON-144',10),(145,'FILIPPO ALPI','DRILL','INYECCION','PANTALON-145',7),(146,'FILIPPO ALPI','DRILL','INYECCION','PANTALON-146',11),(147,'FILIPPO ALPI','DRILL','INYECCION','PANTALON-147',18),(148,'FILIPPO ALPI','DRILL','INYECCION','PANTALON-148',27),(149,'FILIPPO ALPI','DRILL','INYECCION','PANTALON-149',25),(150,'FILIPPO ALPI','DRILL','INYECCION','PANTALON-150',17),(151,'FILIPPO ALPI','DRILL','INYECCION','PANTALON-151',7),(152,'FILIPPO ALPI','DRILL','INYECCION','PANTALON-152',7),(153,'FILIPPO ALPI','DRILL','INYECCION','PANTALON-153',3),(154,'FILIPPO ALPI','DRILL','INYECCION','PANTALON-154',4),(155,'FILIPPO ALPI','DRILL','INYECCION','PANTALON-155',8),(156,'FILIPPO ALPI','DRILL','INYECCION','PANTALON-156',8),(157,'FILIPPO ALPI','DRILL','INYECCION','PANTALON-157',8),(158,'FILIPPO ALPI','DRILL','INYECCION','PANTALON-158',5),(159,'FILIPPO ALPI','DRILL','INYECCION','SHORT-159',9),(160,'FILIPPO ALPI','DRILL','INYECCION','SHORT-160',12),(161,'FILIPPO ALPI','DRILL','INYECCION','SHORT-161',7),(162,'FILIPPO ALPI','DRILL','INYECCION','SHORT-162',11),(163,'FILIPPO ALPI','DRILL','INYECCION','SHORT-163',7),(164,'FILIPPO ALPI','DRILL','INYECCION','SHORT-164',6),(165,'FILIPPO ALPI','DRILL','INYECCION','SHORT-165',9),(166,'FILIPPO ALPI','DRILL','INYECCION','SHORT-166',3),(167,'FILIPPO ALPI','DRILL','INYECCION','SHORT-167',18),(168,'FILIPPO ALPI','DRILL','INYECCION','SHORT-168',16),(169,'FILIPPO ALPI','DRILL','INYECCION','SHORT-169',19),(170,'FILIPPO ALPI','DRILL','INYECCION','SHORT-170',11),(171,'FILIPPO ALPI','DRILL','INYECCION','SHORT-171',1),(172,'FILIPPO ALPI','DRILL','INYECCION','SHORT-172',0),(173,'FILIPPO ALPI','DRILL','INYECCION','SHORT-173',0),(174,'FILIPPO ALPI','DRILL','INYECCION','SHORT-174',0),(175,'FILIPPO ALPI','JEANS','INYECCION','SHORT-175',4),(176,'FILIPPO ALPI','JEANS','INYECCION','SHORT-176',5),(177,'FILIPPO ALPI','JEANS','INYECCION','SHORT-177',2),(178,'FILIPPO ALPI','JEANS','INYECCION','SHORT-178',5),(179,'FILIPPO ALPI','JEANS','INYECCION','SHORT-179',5),(180,'FILIPPO ALPI','JEANS','INYECCION','SHORT-180',1),(181,'FILIPPO ALPI','JEANS','INYECCION','SHORT-181',0),(182,'FILIPPO ALPI','JEANS','INYECCION','PANTALON-182',6),(183,'FILIPPO ALPI','JEANS','INYECCION','PANTALON-183',2),(184,'FILIPPO ALPI','JEANS','INYECCION','PANTALON-184',8),(185,'FILIPPO ALPI','JEANS','INYECCION','PANTALON-185',9),(186,'FILIPPO ALPI','JEANS','INYECCION','PANTALON-186',8),(187,'FILIPPO ALPI','JEANS','INYECCION','PANTALON-187',8),(188,'FILIPPO ALPI','JEANS','INYECCION','PANTALON-188',6),(189,'FILIPPO ALPI','JEANS','INYECCION','PANTALON-189',0),(122,'FOSTER','VESTIR','INYECCION','PANTALON-122',15),(128,'FOSTER','VESTIR','INYECCION','PANTALON-128',4),(129,'FOSTER','VESTIR','INYECCION','PANTALON-129',7),(130,'FOSTER','VESTIR','INYECCION','PANTALON-130',1),(131,'FOSTER','VESTIR','INYECCION','PANTALON-131',5),(132,'FOSTER','VESTIR','INYECCION','PANTALON-132',3),(133,'FOSTER','VESTIR','INYECCION','PANTALON-133',12),(134,'FOSTER','VESTIR','INYECCION','PANTALON-134',4),(135,'FOSTER','VESTIR','INYECCION','PANTALON-135',1),(136,'FOSTER','VESTIR','INYECCION','PANTALON-136',3),(137,'FOSTER','VESTIR','INYECCION','PANTALON-137',1),(138,'FOSTER','VESTIR','INYECCION','PANTALON-138',1),(139,'FOSTER','VESTIR','INYECCION','PANTALON-139',2),(140,'FOSTER','VESTIR','INYECCION','PANTALON-140',1),(141,'FOSTER','VESTIR','INYECCION','PANTALON-141',0),(142,'FOSTER','VESTIR','INYECCION','PANTALON-142',2),(329,'FOSTER','VESTIR','INYECCION','PANTALON-329',9),(330,'FOSTER','VESTIR','INYECCION','PANTALON-330',3),(331,'FOSTER','VESTIR','INYECCION','PANTALON-331',0),(332,'FOSTER','VESTIR','INYECCION','PANTALON-332',0),(333,'FOSTER','VESTIR','INYECCION','PANTALON-333',0),(334,'FOSTER','VESTIR','INYECCION','PANTALON-334',7),(335,'FOSTER','VESTIR','INYECCION','PANTALON-335',0),(336,'FOSTER','VESTIR','INYECCION','PANTALON-336',0),(233,'GINO  MARCONY´S','VESTIR','INYECCION','CAMISA-233',22),(234,'GINO  MARCONY´S','VESTIR','INYECCION','CAMISA-VE234CA',19),(235,'GINO  MARCONY´S','VESTIR','INYECCION','CAMISA-235',13),(236,'GINO  MARCONY´S','VESTIR','INYECCION','CAMISA-236',5),(237,'GINO  MARCONY´S','VESTIR','INYECCION','CAMISA-237',20),(238,'GINO  MARCONY´S','VESTIR','INYECCION','CAMISA-238',18),(239,'GINO  MARCONY´S','VESTIR','INYECCION','CAMISA-239',14),(240,'GINO  MARCONY´S','VESTIR','INYECCION','CAMISA-240',16),(241,'GINO  MARCONY´S','VESTIR','INYECCION','CAMISA-241',15),(242,'GINO  MARCONY´S','VESTIR','INYECCION','CAMISA-242',12),(243,'GINO  MARCONY´S','VESTIR','INYECCION','CAMISA-243',10),(244,'GINO  MARCONY´S','VESTIR','INYECCION','CAMISA-244',13),(313,'GINO  MARCONY´S','VESTIR','INYECCION','CAMISA-313',1),(314,'GINO  MARCONY´S','VESTIR','INYECCION','CAMISA-314',0),(315,'GINO  MARCONY´S','VESTIR','INYECCION','CAMISA-315',2),(457,'GINO  MARCONY´S','VESTIR','INYECCION','CAMISA-VE457CA',7),(288,'GINO PAGANI','VESTIR','INYECCION','CAMISA-288',10),(289,'GINO PAGANI','VESTIR','INYECCION','CAMISA-289',7),(290,'GINO PAGANI','VESTIR','INYECCION','CAMISA-290',12),(291,'GINO PAGANI','VESTIR','INYECCION','CAMISA-291',9),(292,'GINO PAGANI','VESTIR','INYECCION','CAMISA-292',3),(293,'GINO PAGANI','VESTIR','INYECCION','CAMISA-293',7),(294,'GINO PAGANI','VESTIR','INYECCION','CAMISA-294',5),(295,'GINO PAGANI','VESTIR','INYECCION','CAMISA-295',0),(8,'HANS','DRILL','INYECCION','PANTALON-8',12),(92,'HANS','VESTIR','INYECCION','CAMISA -VE92CA',4),(93,'HANS','VESTIR','INYECCION','CAMISA -VE93CA',8),(94,'HANS','VESTIR','INYECCION','CAMISA -VE94CA',5),(95,'HANS','VESTIR','INYECCION','CAMISA -VE95CA',2),(96,'HANS','VESTIR','INYECCION','CAMISA -VE96CA',3),(97,'HANS','VESTIR','INYECCION','CAMISA -VE97CA',7),(98,'HANS','VESTIR','INYECCION','CAMISA -VE98CA',15),(99,'HANS','VESTIR','INYECCION','PANTALON -VE99PA',4),(100,'HANS','VESTIR','INYECCION','PANTALON-VE100PA',0),(101,'HANS','VESTIR','INYECCION','PANTALON-VE101PA',2),(102,'HANS','VESTIR','INYECCION','PANTALON-VE102PA',1),(103,'HANS','VESTIR','INYECCION','PANTALON-VE103PA',4),(104,'HANS','VESTIR','INYECCION','PANTALON-VE104PA',0),(105,'HANS','VESTIR','INYECCION','PANTALON-VE105PA',0),(106,'HANS','VESTIR','INYECCION','PANTALON-VE106PA',5),(107,'HANS','VESTIR','INYECCION','PANTALON-VE107PA',1),(108,'HANS','VESTIR','INYECCION','PANTALON-VE108PA',3),(109,'HANS','VESTIR','INYECCION','PANTALON-VE109PA',4),(110,'HANS','VESTIR','INYECCION','PANTALON-VE110PA',4),(111,'HANS','VESTIR','INYECCION','PANTALON-VE111PA',0),(112,'HANS','VESTIR','INYECCION','PANTALON-VE112PA',0),(113,'HANS','VESTIR','INYECCION','PANTALON-VE113PA',11),(114,'HANS','VESTIR','INYECCION','PANTALON-VE114PA',7),(115,'HANS','VESTIR','INYECCION','PANTALON-VE115PA',6),(116,'HANS','VESTIR','INYECCION','PANTALON-VE116PA',4),(117,'HANS','VESTIR','INYECCION','PANTALON-VE117PA',6),(118,'HANS','VESTIR','INYECCION','PANTALON-VE118PA',6),(119,'HANS','VESTIR','INYECCION','PANTALON-VE119PA',7),(120,'HANS','VESTIR','INYECCION','PANTALON-VE120PA',8),(121,'HANS','VESTIR','INYECCION','PANTALON-VE121PA',3),(123,'HANS','DRILL','INYECCION','PANTALON-DR123PA',6),(124,'HANS','DRILL','INYECCION','PANTALON-124',6),(125,'HANS','DRILL','INYECCION','PANTALON-DR125PA',13),(126,'HANS','DRILL','INYECCION','PANTALON-126',10),(127,'HANS','DRILL','INYECCION','PANTALON-DR127PA',4),(337,'HANS','VESTIR','INYECCION','PANTALON-VE337PA',4),(338,'HANS','VESTIR','INYECCION','PANTALON-VE338PA',4),(339,'HANS','VESTIR','INYECCION','PANTALON-VE339PA',0),(340,'HANS','VESTIR','INYECCION','PANTALON-VE340PA',0),(341,'HANS','VESTIR','INYECCION','PANTALON-VE341PA',3),(342,'HANS','VESTIR','INYECCION','PANTALON-VE342PA',1),(343,'HANS','VESTIR','INYECCION','PANTALON-VE343PA',0),(86,'IMPORTADO','VESTIR','INYECCION','CORBATAS-VE86CO',129),(87,'IMPORTADO','VESTIR','INYECCION','CORBATAS-VE87CO',88),(88,'IMPORTADO','VESTIR','INYECCION','MICHI-VE88MI',58),(89,'IMPORTADO','VESTIR','INYECCION','MICHI -VE89MI',110),(90,'IMPORTADO','VESTIR','INYECCION','MICHI-VE90MI',35),(91,'IMPORTADO','VESTIR','INYECCION','MICHI -VE91MI',11),(1,'JOHN HOLDEN','VESTIR','INYECCION','CAMISA-VE1CA',0),(11,'JOHN HOLDEN','VESTIR','INYECCION','CAMISA-VE11CA',-8),(13,'JOHN HOLDEN','VESTIR','INYECCION','CAMISA-VE13CA',5),(14,'JOHN HOLDEN','VESTIR','INYECCION','CAMISA-VE14CA',4),(15,'JOHN HOLDEN','VESTIR','INYECCION','CAMISA-VE15CA',3),(16,'JOHN HOLDEN','VESTIR','INYECCION','CAMISA-VE16CA',2),(17,'JOHN HOLDEN','VESTIR','INYECCION','CAMISA-VE17CA',-2),(18,'JOHN HOLDEN','VESTIR','INYECCION','CAMISA-VE18CA',0),(19,'JOHN HOLDEN','VESTIR','INYECCION','CAMISA-VE19CA',-4),(20,'JOHN HOLDEN','VESTIR','INYECCION','CAMISA-VE20CA',-1),(21,'JOHN HOLDEN','VESTIR','INYECCION','CAMISA-VE21CA',0),(22,'JOHN HOLDEN','VESTIR','INYECCION','CAMISA-VE22CA',-4),(23,'JOHN HOLDEN','VESTIR','INYECCION','CAMISA -VE23CA',1),(24,'JOHN HOLDEN','VESTIR','INYECCION','CAMISA-VE24CA',0),(25,'JOHN HOLDEN','VESTIR','INYECCION','CAMISA -VE25CA',2),(26,'JOHN HOLDEN','VESTIR','INYECCION','CAMISA-VE26CA',-3),(27,'JOHN HOLDEN','VESTIR','INYECCION','CAMISA-VE27CA',0),(28,'JOHN HOLDEN','VESTIR','INYECCION','PANTALON-VE28PA',3),(29,'JOHN HOLDEN','VESTIR','INYECCION','PANTALON-VE29PA',11),(30,'JOHN HOLDEN','VESTIR','INYECCION','PANTALON-VE30PA',11),(31,'JOHN HOLDEN','VESTIR','INYECCION','PANTALON-VE31PA',11),(32,'JOHN HOLDEN','VESTIR','INYECCION','PANTALON-VE32PA',7),(33,'JOHN HOLDEN','VESTIR','INYECCION','PANTALON-VE33PA',9),(34,'JOHN HOLDEN','VESTIR','INYECCION','PANTALON-VE34PA',10),(35,'JOHN HOLDEN','VESTIR','INYECCION','PANTALON-VE35PA',5),(36,'JOHN HOLDEN','VESTIR','INYECCION','PANTALON-VE36PA',2),(37,'JOHN HOLDEN','VESTIR','INYECCION','PANTALON-VE37PA',0),(38,'JOHN HOLDEN','VESTIR','INYECCION','PANTALON-VE38PA',8),(39,'JOHN HOLDEN','VESTIR','INYECCION','PANTALON-VE39PA',7),(40,'JOHN HOLDEN','VESTIR','INYECCION','PANTALON-VE40PA',8),(41,'JOHN HOLDEN','VESTIR','INYECCION','PANTALON-VE41PA',6),(42,'JOHN HOLDEN','VESTIR','INYECCION','PANTALON -VE42PA',10),(43,'JOHN HOLDEN','VESTIR','INYECCION','PANTALON -VE43PA',5),(44,'JOHN HOLDEN','VESTIR','INYECCION','PANTALON-VE44PA',1),(45,'JOHN HOLDEN','VESTIR','INYECCION','PANTALON-VE45PA',9),(46,'JOHN HOLDEN','VESTIR','INYECCION','PANTALON -VE46PA',6),(47,'JOHN HOLDEN','VESTIR','INYECCION','PANTALON-VE47PA',9),(48,'JOHN HOLDEN','VESTIR','INYECCION','PANTALON -VE48PA',13),(49,'JOHN HOLDEN','VESTIR','INYECCION','PANTALON -VE49PA',7),(50,'JOHN HOLDEN','VESTIR','INYECCION','PANTALON -VE50PA',6),(51,'JOHN HOLDEN','VESTIR','INYECCION','PANTALON-VE51PA',0),(52,'JOHN HOLDEN','ACCESORIOS','INYECCION','PAÑUELO-AC52PA',24),(53,'JOHN HOLDEN','ACCESORIOS','INYECCION','BOXER -AC53BO',14),(54,'JOHN HOLDEN','ACCESORIOS','INYECCION','BOXER -54',4),(55,'JOHN HOLDEN','ACCESORIOS','INYECCION','BOXER -AC55BO',7),(56,'JOHN HOLDEN','ACCESORIOS','INYECCION','BOXER -AC56BO',12),(57,'JOHN HOLDEN','ACCESORIOS','INYECCION','MEDIAS-AC57ME',157),(58,'JOHN HOLDEN','VESTIR','INYECCION','CAMISA-VE58CA',0),(59,'JOHN HOLDEN','VESTIR','INYECCION','CAMISA-VE59CA',1),(60,'JOHN HOLDEN','VESTIR','INYECCION','CAMISA -VE60CA',0),(61,'JOHN HOLDEN','VESTIR','INYECCION','CAMISA-VE61CA',0),(62,'JOHN HOLDEN','VESTIR','INYECCION','CAMISA-VE62CA',0),(63,'JOHN HOLDEN','VESTIR','INYECCION','CAMISA-VE63CA',33),(64,'JOHN HOLDEN','VESTIR','INYECCION','CAMISA -VE64CA',9),(65,'JOHN HOLDEN','VESTIR','INYECCION','CAMISA-VE65CA',18),(66,'JOHN HOLDEN','VESTIR','INYECCION','CAMISA-VE66CA',22),(67,'JOHN HOLDEN','VESTIR','INYECCION','CAMISA-VE67CA',15),(84,'JOHN HOLDEN','VESTIR','INYECCION','CORBATAS-VE84CO',54),(328,'JOHN HOLDEN','VESTIR','INYECCION','PANTALON-VE328PA',3),(362,'JOHN HOLDEN','ALGODÓN','INYECCION','POLO-AL362PO',2),(363,'JOHN HOLDEN','ALGODÓN','INYECCION','POLO-AL363PO',2),(364,'JOHN HOLDEN','ALGODÓN','INYECCION','POLO-AL364PO',7),(365,'JOHN HOLDEN','ALGODÓN','INYECCION','POLO-365',6),(431,'JOHN HOLDEN','ACCESORIOS','INYECCION','SET X3-AC431SE',0),(485,'JOHN HOLDEN','VESTIR','INYECCION','TEST 1-VE485TE',0),(345,'LACOSTE','PIQUE','INYECCION','POLO-PI345PO',6),(346,'LACOSTE','PIQUE','INYECCION','POLO-PI346PO',5),(347,'LACOSTE','PIQUE','INYECCION','POLO-PI347PO',7),(348,'LACOSTE','PIQUE','INYECCION','POLO-PI348PO',12),(349,'LACOSTE','PIQUE','INYECCION','POLO-PI349PO',0),(320,'MATISSE','ALGODÓN','INYECCION','POLO-320',25),(321,'MATISSE','ALGODÓN','INYECCION','POLO-321',15),(322,'MATISSE','ALGODÓN','INYECCION','POLO-322',9),(323,'MATISSE','ALGODÓN','INYECCION','POLO-323',15),(344,'NACIONAL','SACO','INYECCION','BLAZER-SA344BL',54),(372,'NACIONAL','ACCESORIOS','INYECCION','PAÑUELO-372',22),(373,'NACIONAL','ACCESORIOS','INYECCION','PAÑUELO-AC373PA',14),(374,'NACIONAL','ACCESORIOS','INYECCION','BILLETERA-AC374BI',3),(375,'NACIONAL','ACCESORIOS','INYECCION','BILLETERA-AC375BI',2),(376,'NACIONAL','ACCESORIOS','INYECCION','TIRANTE-AC376TI',7),(377,'NACIONAL','ACCESORIOS','INYECCION','TIRANTE',35);
/*!40000 ALTER TABLE `prodstock` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Seg_Usuario`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Seg_Usuario` (
  `Usuario` varchar(255) NOT NULL,
  `IdUsuarioPerfil` int(11) NOT NULL,
  `Password` varchar(255) DEFAULT NULL,
  `NombreUsuario` varchar(255) DEFAULT NULL,
  `Anulado` tinyint(1) DEFAULT NULL,
  `FechaReg` datetime DEFAULT NULL,
  `UsuarioReg` varchar(255) DEFAULT NULL,
  `FechaMod` datetime DEFAULT NULL,
  `UsuarioMod` varchar(255) DEFAULT NULL,
  `IdAlmacen` int(11) DEFAULT NULL,
  `IdDocVentaPuntoVenta` int(11) DEFAULT NULL,
  PRIMARY KEY (`Usuario`,`IdUsuarioPerfil`),
  UNIQUE KEY `Usuario_UNIQUE` (`Usuario`),
  KEY `fk_Usuario_UsuarioPerfil1_idx` (`IdUsuarioPerfil`),
  CONSTRAINT `fk_Usuario_UsuarioPerfil1` FOREIGN KEY (`IdUsuarioPerfil`) REFERENCES `Seg_UsuarioPerfil` (`IdUsuarioPerfil`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Seg_Usuario` WRITE;
/*!40000 ALTER TABLE `Seg_Usuario` DISABLE KEYS */;
INSERT INTO `Seg_Usuario` VALUES ('admin',1,'34e213e56df7ff77ae8ab7141f775f42a2f54239fdf8fc4a5636fece66b5eaa2','ADMIN',0,'0000-00-00 00:00:00','-','0000-00-00 00:00:00','-',1,1),('JOB',3,'7cf5be448e0a9a25e9b9f1f3c92bc07f0fe36f13c252118aa949a5d78df688ca','JOB',0,NULL,NULL,NULL,NULL,1,1),('PAOLA',31,'b370d07ea692995a8eb88ce019989ae0ea96c66d2f31343ba6599a735a52c989','PAOLA ',0,NULL,NULL,NULL,NULL,1,1),('sunat',1,'a665a45920422f9d417e4867efdc4fb8a04a1f3fff1fa07e998e86f7f7a27ae3','sunat',0,'2022-09-30 14:33:39',NULL,'2022-09-30 14:33:39',NULL,1,1),('superadmin',1,'8c6976e5b5410415bde908bd4dee15dfb167a9c873fc4bb8a81f6f2ab448a918','ADMIN',0,'0000-00-00 00:00:00','-','0000-00-00 00:00:00','-',1,1);
/*!40000 ALTER TABLE `Seg_Usuario` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Seg_UsuarioModulo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Seg_UsuarioModulo` (
  `IdUsuarioModulo` int(11) NOT NULL AUTO_INCREMENT,
  `UsuarioModulo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`IdUsuarioModulo`),
  UNIQUE KEY `UsuarioModulo_UNIQUE` (`UsuarioModulo`)
) ENGINE=InnoDB AUTO_INCREMENT=56 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Seg_UsuarioModulo` WRITE;
/*!40000 ALTER TABLE `Seg_UsuarioModulo` DISABLE KEYS */;
INSERT INTO `Seg_UsuarioModulo` VALUES (6,'almacenes'),(33,'bajaelectronico'),(55,'CajaBancos.php'),(10,'categorias'),(35,'cierrecaja'),(27,'cliente'),(30,'clientes'),(24,'consultarRUC'),(31,'emitirelectronico'),(32,'emitirelectronicoboleta'),(28,'empresa'),(50,'formato37.php'),(54,'FrmCb_CajaBanco.php'),(46,'Gen_ProductoForm.php'),(36,'index.php'),(52,'LOGITICA'),(44,'Lo_BuscarVencimiento.php'),(51,'Lo_InventarioForm.php'),(3,'lo_kardex.php'),(43,'lo_kardexform.php'),(7,'lo_regmovimiento.php'),(9,'Lo_ReporteStockForm.php'),(45,'Lo_ReporteStockOrdenCompra.php'),(11,'marcas'),(15,'mediciones'),(22,'monedas'),(20,'movimiento'),(21,'movimientos'),(26,'preorden'),(5,'productos'),(18,'productosdet'),(49,'proformas'),(19,'proveedores'),(39,'puntoventa'),(4,'reporte'),(47,'reportes.php'),(12,'tallas'),(29,'usuarios'),(53,'VENTA'),(25,'ventas'),(37,'ve_cierreform.php'),(8,'ve_regventaform.php'),(38,'ve_reimpresionDocumentos.php'),(42,'V_ExpertoForm.php'),(41,'V_VentaCliente.php'),(1,'V_VentaForm.php');
/*!40000 ALTER TABLE `Seg_UsuarioModulo` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Seg_UsuarioModulo_has_UsuarioPerfil`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Seg_UsuarioModulo_has_UsuarioPerfil` (
  `IdUsuarioModulo` int(11) NOT NULL,
  `IdUsuarioPerfil` int(11) NOT NULL,
  `Lectura` bit(1) DEFAULT NULL,
  `Escritura` bit(1) DEFAULT NULL,
  PRIMARY KEY (`IdUsuarioModulo`,`IdUsuarioPerfil`),
  KEY `fk_UsuarioModulo_has_UsuarioPerfil_UsuarioPerfil1_idx` (`IdUsuarioPerfil`),
  KEY `fk_UsuarioModulo_has_UsuarioPerfil_UsuarioModulo1_idx` (`IdUsuarioModulo`),
  CONSTRAINT `fk_UsuarioModulo_has_UsuarioPerfil_UsuarioModulo1` FOREIGN KEY (`IdUsuarioModulo`) REFERENCES `Seg_UsuarioModulo` (`IdUsuarioModulo`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_UsuarioModulo_has_UsuarioPerfil_UsuarioPerfil1` FOREIGN KEY (`IdUsuarioPerfil`) REFERENCES `Seg_UsuarioPerfil` (`IdUsuarioPerfil`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Seg_UsuarioModulo_has_UsuarioPerfil` WRITE;
/*!40000 ALTER TABLE `Seg_UsuarioModulo_has_UsuarioPerfil` DISABLE KEYS */;
INSERT INTO `Seg_UsuarioModulo_has_UsuarioPerfil` VALUES (1,1,0x01,0x01),(1,2,0x01,0x00),(1,3,0x01,0x01),(1,17,0x01,0x01),(1,31,0x01,0x01),(3,1,0x01,0x01),(3,3,0x00,0x00),(3,4,0x01,0x01),(4,3,0x01,0x01),(4,4,0x01,0x01),(4,17,0x00,0x00),(5,3,0x01,0x00),(5,4,0x01,0x01),(5,31,0x01,0x01),(6,1,0x01,0x01),(6,3,0x01,0x00),(6,4,0x01,0x01),(6,17,0x01,0x01),(6,31,0x01,0x01),(7,4,0x01,0x01),(8,4,0x01,0x01),(9,3,0x01,0x01),(9,4,0x01,0x01),(9,17,0x01,0x01),(9,31,0x00,0x00),(10,3,0x01,0x01),(10,4,0x01,0x01),(11,3,0x01,0x01),(11,4,0x01,0x01),(12,3,0x01,0x01),(12,4,0x01,0x01),(15,3,0x01,0x01),(18,3,0x01,0x01),(19,3,0x01,0x01),(20,1,0x01,0x01),(20,3,0x01,0x01),(21,3,0x01,0x01),(22,3,0x01,0x01),(24,3,0x01,0x01),(24,31,0x01,0x01),(25,3,0x01,0x01),(25,17,0x01,0x01),(25,31,0x01,0x01),(26,3,0x01,0x01),(27,3,0x01,0x01),(28,3,0x01,0x01),(29,3,0x01,0x01),(30,3,0x01,0x01),(31,3,0x01,0x01),(32,3,0x01,0x01),(33,3,0x01,0x01),(35,3,0x01,0x01),(36,3,0x01,0x01),(36,31,0x01,0x01),(37,3,0x01,0x01),(37,31,0x01,0x01),(38,3,0x01,0x01),(38,31,0x01,0x01),(39,3,0x01,0x01),(41,3,0x01,0x01),(42,3,0x01,0x01),(45,3,0x01,0x01),(46,3,0x01,0x01),(49,3,0x01,0x01),(50,4,0x01,0x01),(51,4,0x00,0x00),(52,31,0x01,0x01),(53,17,0x01,0x01),(53,31,0x01,0x01),(54,3,0x01,0x01),(55,31,0x01,0x01);
/*!40000 ALTER TABLE `Seg_UsuarioModulo_has_UsuarioPerfil` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Seg_UsuarioPerfil`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Seg_UsuarioPerfil` (
  `IdUsuarioPerfil` int(11) NOT NULL AUTO_INCREMENT,
  `UsuarioPerfil` varchar(255) DEFAULT NULL,
  `Anulado` bit(1) DEFAULT NULL,
  `FechaReg` datetime DEFAULT NULL,
  `UsuarioReg` varchar(255) DEFAULT NULL,
  `FechaMod` datetime DEFAULT NULL,
  `UsuarioMod` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`IdUsuarioPerfil`),
  UNIQUE KEY `UsuarioPerfil_UNIQUE` (`UsuarioPerfil`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Seg_UsuarioPerfil` WRITE;
/*!40000 ALTER TABLE `Seg_UsuarioPerfil` DISABLE KEYS */;
INSERT INTO `Seg_UsuarioPerfil` VALUES (1,'administrador',0x00,'0000-00-00 00:00:00','-','2021-08-09 17:26:10','-'),(2,'Digitador',0x00,'0000-00-00 00:00:00','-','0000-00-00 00:00:00','-'),(3,'vendedor',NULL,'2018-06-18 15:54:54',NULL,'2026-07-09 19:10:28',NULL),(4,'contador',NULL,'2018-10-24 12:48:43',NULL,'2019-04-17 21:34:19',NULL),(17,'vendedora',NULL,'2019-03-12 17:51:24',NULL,'2021-08-04 22:15:23',NULL),(31,'caja',0x00,'2021-08-09 17:25:52','admin','2026-07-11 17:15:18',NULL);
/*!40000 ALTER TABLE `Seg_UsuarioPerfil` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `sunat_resumen`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sunat_resumen` (
  `IdSunatResumen` int(11) NOT NULL AUTO_INCREMENT,
  `Serie` varchar(255) DEFAULT NULL,
  `Secuencia` int(11) DEFAULT NULL,
  `CodSunat` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`IdSunatResumen`),
  UNIQUE KEY `IdSunatResumen_UNIQUE` (`IdSunatResumen`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `sunat_resumen` WRITE;
/*!40000 ALTER TABLE `sunat_resumen` DISABLE KEYS */;
/*!40000 ALTER TABLE `sunat_resumen` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `tblKardex`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblKardex` (
  `d1` varchar(255) DEFAULT ' ',
  `d2` varchar(255) DEFAULT ' ',
  `d3` varchar(255) DEFAULT ' ',
  `d4` varchar(255) DEFAULT ' ',
  `d5` varchar(255) DEFAULT ' '
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `tblKardex` WRITE;
/*!40000 ALTER TABLE `tblKardex` DISABLE KEYS */;
INSERT INTO `tblKardex` VALUES ('KARDEX',' ',' ',' ',' '),(' ',' ',' ',' ',' '),('PRODUCTO','PANTALON-RO1PA',' ',' ',' '),('DESDE','2017-10-01',' ',' ',' '),('HASTA','2018-10-18',' ',' ',' '),(' ',' ',' ',' ',' '),('FECHA','DETALLE','ENTRADA','SALIDA','SALDO'),(' ','SALDO ANTERIOR',' ',' ','0'),('2018-06-23','INGRESO --1','1','','1');
/*!40000 ALTER TABLE `tblKardex` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `tblKardexvalor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tblKardexvalor` (
  `d1` varchar(255) DEFAULT ' ',
  `d2` varchar(255) DEFAULT ' ',
  `d3` varchar(255) DEFAULT ' ',
  `d4` varchar(255) DEFAULT ' ',
  `d5` varchar(255) DEFAULT ' ',
  `d6` varchar(255) DEFAULT ' ',
  `d7` varchar(255) DEFAULT ' ',
  `d8` varchar(255) DEFAULT ' ',
  `d9` varchar(255) DEFAULT ' ',
  `d10` varchar(255) DEFAULT ' ',
  `d11` varchar(255) DEFAULT ' '
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `tblKardexvalor` WRITE;
/*!40000 ALTER TABLE `tblKardexvalor` DISABLE KEYS */;
INSERT INTO `tblKardexvalor` VALUES ('KARDEX VALORIZADO',' ',' ',' ',' ',' ',' ',' ',' ',' ',' '),(' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' '),('PRODUCTO','PANTALON-RO1PA',' ',' ',' ',' ',' ',' ',' ',' ',' '),('AÑO','2018',' ',' ',' ',' ',' ',' ',' ',' ',' '),(' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' '),('DESDE ','2018-01-01',' ',' ',' ',' ',' ',' ',' ',' ',' '),('HASTA ','2018-12-31',' ',' ',' ',' ',' ',' ',' ',' ',' '),(' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' '),('FECHA','DETALLE','ENTRADA','P/C','TOTAL','SALIDA','P/C','TOTAL','SALDO','P/C','TOTAL'),('2017-01-01','SALDO INICIAL','0','0','0','','','','0','0','0');
/*!40000 ALTER TABLE `tblKardexvalor` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Ve_DocVenta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Ve_DocVenta` (
  `idDocVenta` int(11) NOT NULL AUTO_INCREMENT,
  `IdDocVentaPuntoVenta` int(11) NOT NULL,
  `IdCliente` int(11) NOT NULL,
  `IdTipoDoc` int(11) NOT NULL,
  `IdAlmacen` int(11) NOT NULL,
  `Serie` varchar(255) DEFAULT NULL,
  `Numero` int(11) DEFAULT NULL,
  `FechaDoc` datetime DEFAULT NULL,
  `Anulado` tinyint(1) DEFAULT 0,
  `FechaReg` datetime DEFAULT NULL,
  `UsuarioReg` varchar(255) DEFAULT NULL,
  `FechaMod` datetime DEFAULT NULL,
  `UsuarioMod` varchar(255) DEFAULT NULL,
  `Hash` varchar(255) DEFAULT NULL,
  `IdCierre` int(11) DEFAULT NULL,
  `EsCredito` bit(1) DEFAULT NULL,
  `FechaCredito` datetime DEFAULT NULL,
  `Estado` tinyint(1) DEFAULT 1,
  `Hash_cpe` varchar(255) DEFAULT NULL,
  `Hash_cdr` varchar(255) DEFAULT NULL,
  `Msj_sunat` text DEFAULT NULL,
  `AnuladoDesc` varchar(255) DEFAULT NULL,
  `Ticket` varchar(255) DEFAULT NULL,
  `PagoCon` float DEFAULT NULL,
  `CampoDireccion` varchar(255) DEFAULT NULL,
  `ResumenFecha` varchar(100) DEFAULT NULL,
  `ResumenCorrelativo` int(11) DEFAULT NULL,
  `SunatResponse` longtext DEFAULT NULL,
  `ErrorSunatResponse` longtext DEFAULT NULL,
  `CodSunatModifica` varchar(255) DEFAULT NULL,
  `NroComprobanteModifica` varchar(255) DEFAULT NULL,
  `NotaIdMotivo` varchar(255) DEFAULT NULL,
  `NotaDescMotivo` varchar(255) DEFAULT NULL,
  `BajaFecha` varchar(100) DEFAULT NULL,
  `BajaCorrelativo` int(11) DEFAULT NULL,
  `BajaMotivo` varchar(255) DEFAULT NULL,
  `RequestException` longtext DEFAULT NULL,
  `NombreOrganizacion` text DEFAULT NULL,
  `EsOrganizacion` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`idDocVenta`),
  UNIQUE KEY `idDocVenta_UNIQUE` (`idDocVenta`),
  KEY `fk_Ve_DocVenta_Ve_DocVentaPuntoVenta_idx` (`IdDocVentaPuntoVenta`),
  KEY `fk_Ve_DocVenta_Ve_DocVentaCliente1_idx` (`IdCliente`),
  KEY `fk_Ve_DocVenta_Ve_DocVentaTipoDoc1_idx` (`IdTipoDoc`),
  KEY `fk_Ve_DocVenta_Lo_Almacen1_idx` (`IdAlmacen`),
  CONSTRAINT `fk_Ve_DocVenta_Lo_Almacen1` FOREIGN KEY (`IdAlmacen`) REFERENCES `Lo_Almacen` (`IdAlmacen`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_Ve_DocVenta_Ve_DocVentaCliente1` FOREIGN KEY (`IdCliente`) REFERENCES `Ve_DocVentaCliente` (`IdCliente`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_Ve_DocVenta_Ve_DocVentaPuntoVenta` FOREIGN KEY (`IdDocVentaPuntoVenta`) REFERENCES `Ve_DocVentaPuntoVenta` (`IdDocVentaPuntoVenta`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_Ve_DocVenta_Ve_DocVentaTipoDoc1` FOREIGN KEY (`IdTipoDoc`) REFERENCES `Ve_DocVentaTipoDoc` (`IdTipoDoc`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=2056 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Ve_DocVenta` WRITE;
/*!40000 ALTER TABLE `Ve_DocVenta` DISABLE KEYS */;
/*!40000 ALTER TABLE `Ve_DocVenta` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Ve_DocVentaCliente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Ve_DocVentaCliente` (
  `IdCliente` int(11) NOT NULL AUTO_INCREMENT,
  `Cliente` varchar(255) DEFAULT NULL,
  `DniRuc` varchar(255) DEFAULT NULL,
  `Direccion` longtext DEFAULT NULL,
  `Telefono` longtext DEFAULT NULL,
  `Email` varchar(255) DEFAULT NULL,
  `Anulado` int(11) DEFAULT NULL,
  `FechaReg` datetime DEFAULT NULL,
  `UsuarioReg` varchar(255) DEFAULT NULL,
  `FechaMod` datetime DEFAULT NULL,
  `UsuarioMod` varchar(255) DEFAULT NULL,
  `Puntos` float DEFAULT 0,
  `Direccion2` varchar(255) DEFAULT NULL,
  `Direccion3` varchar(255) DEFAULT NULL,
  `FechaNacimiento` date DEFAULT NULL,
  `EsVIP` tinyint(4) DEFAULT 0,
  `NombreComercial` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`IdCliente`),
  UNIQUE KEY `IdCliente_UNIQUE` (`IdCliente`),
  UNIQUE KEY `CLIENTE_UNIQUE` (`Cliente`),
  UNIQUE KEY `DNIRUC_UNIQUE` (`DniRuc`)
) ENGINE=InnoDB AUTO_INCREMENT=10332 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Ve_DocVentaCliente` WRITE;
/*!40000 ALTER TABLE `Ve_DocVentaCliente` DISABLE KEYS */;
/*!40000 ALTER TABLE `Ve_DocVentaCliente` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Ve_DocVentaDescuento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Ve_DocVentaDescuento` (
  `IdDocVentaDescuento` int(11) NOT NULL AUTO_INCREMENT,
  `PorCada` float DEFAULT NULL,
  `Puntos` float DEFAULT 1,
  `ValorPunto` float DEFAULT NULL,
  PRIMARY KEY (`IdDocVentaDescuento`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Ve_DocVentaDescuento` WRITE;
/*!40000 ALTER TABLE `Ve_DocVentaDescuento` DISABLE KEYS */;
INSERT INTO `Ve_DocVentaDescuento` VALUES (1,10,1,0.2);
/*!40000 ALTER TABLE `Ve_DocVentaDescuento` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Ve_DocVentaDet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Ve_DocVentaDet` (
  `IdDocVentaDet` int(11) NOT NULL AUTO_INCREMENT,
  `IdDocVenta` int(11) NOT NULL,
  `IdProducto` int(11) NOT NULL,
  `Cantidad` float DEFAULT NULL,
  `Precio` float DEFAULT NULL,
  `Descuento` float NOT NULL DEFAULT 0,
  `PrecioCosto` float DEFAULT NULL,
  `PorcentajeLiquidacion` float DEFAULT 0,
  `Descripcion` text DEFAULT NULL,
  `Presentacion` text DEFAULT NULL,
  `CantidadxPresentacion` float DEFAULT NULL,
  PRIMARY KEY (`IdDocVentaDet`),
  KEY `fk_Ve_DocVentaDet_Ve_DocVenta1_idx` (`IdDocVenta`),
  KEY `fk_Ve_DocVentaDet_Gen_Producto_idx` (`IdProducto`),
  CONSTRAINT `fk_Ve_DocVentaDet_Gen_Producto` FOREIGN KEY (`IdProducto`) REFERENCES `Gen_Producto` (`IdProducto`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_Ve_DocVentaDet_Ve_DocVenta1` FOREIGN KEY (`IdDocVenta`) REFERENCES `Ve_DocVenta` (`idDocVenta`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=3169 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Ve_DocVentaDet` WRITE;
/*!40000 ALTER TABLE `Ve_DocVentaDet` DISABLE KEYS */;
/*!40000 ALTER TABLE `Ve_DocVentaDet` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Ve_DocVentaMetodoPago`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Ve_DocVentaMetodoPago` (
  `IdMetodoPago` int(11) NOT NULL AUTO_INCREMENT,
  `MetodoPago` varchar(255) DEFAULT NULL,
  `EsTarjeta` bit(1) DEFAULT NULL,
  PRIMARY KEY (`IdMetodoPago`),
  UNIQUE KEY `MetodoPago_UNIQUE` (`MetodoPago`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Ve_DocVentaMetodoPago` WRITE;
/*!40000 ALTER TABLE `Ve_DocVentaMetodoPago` DISABLE KEYS */;
INSERT INTO `Ve_DocVentaMetodoPago` VALUES (1,'Efectivo',0x00),(2,'Visa',0x01),(3,'Mastercard',0x01);
/*!40000 ALTER TABLE `Ve_DocVentaMetodoPago` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Ve_DocVentaMetodoPagoDet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Ve_DocVentaMetodoPagoDet` (
  `IdDocVenta` int(11) NOT NULL,
  `IdMetodoPago` int(11) NOT NULL,
  `Importe` float(15,2) NOT NULL,
  `NroTarjeta` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Ve_DocVentaMetodoPagoDet` WRITE;
/*!40000 ALTER TABLE `Ve_DocVentaMetodoPagoDet` DISABLE KEYS */;
/*!40000 ALTER TABLE `Ve_DocVentaMetodoPagoDet` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Ve_DocVentaNotaCredito`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Ve_DocVentaNotaCredito` (
  `IdDocVentaNotaCredito` int(11) NOT NULL AUTO_INCREMENT,
  `idDocVenta` int(11) NOT NULL,
  `Hash_cpe` varchar(255) DEFAULT NULL,
  `Hash_cdr` varchar(255) DEFAULT NULL,
  `Msj_sunat` text DEFAULT NULL,
  `Ticket` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`IdDocVentaNotaCredito`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Ve_DocVentaNotaCredito` WRITE;
/*!40000 ALTER TABLE `Ve_DocVentaNotaCredito` DISABLE KEYS */;
/*!40000 ALTER TABLE `Ve_DocVentaNotaCredito` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Ve_DocVentaPuntoVenta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Ve_DocVentaPuntoVenta` (
  `IdDocVentaPuntoVenta` int(11) NOT NULL AUTO_INCREMENT,
  `PuntoVenta` varchar(255) DEFAULT NULL,
  `SerieDocVenta` varchar(255) DEFAULT NULL,
  `SerieImpresora` varchar(255) DEFAULT NULL,
  `RutaImpresora` longtext DEFAULT NULL,
  `Anulado` bit(1) DEFAULT NULL,
  `FechaReg` datetime DEFAULT NULL,
  `UsuarioReg` varchar(255) DEFAULT NULL,
  `FechaMod` datetime DEFAULT NULL,
  `UsuarioMod` varchar(255) DEFAULT NULL,
  `IdImpresora` int(11) DEFAULT NULL,
  PRIMARY KEY (`IdDocVentaPuntoVenta`),
  UNIQUE KEY `IdDocVentaPuntoVenta_UNIQUE` (`IdDocVentaPuntoVenta`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Ve_DocVentaPuntoVenta` WRITE;
/*!40000 ALTER TABLE `Ve_DocVentaPuntoVenta` DISABLE KEYS */;
INSERT INTO `Ve_DocVentaPuntoVenta` VALUES (1,'PRINCIPAL','001','FF698761','\\\\PCHINOL\\ticket',0x00,NULL,NULL,NULL,NULL,1);
/*!40000 ALTER TABLE `Ve_DocVentaPuntoVenta` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Ve_DocVentaPuntoVentaDet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Ve_DocVentaPuntoVentaDet` (
  `IdDocVentaPuntoVenta` int(11) NOT NULL,
  `IdDocVentaTipoDoc` int(11) NOT NULL,
  `Serie` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`IdDocVentaPuntoVenta`,`IdDocVentaTipoDoc`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Ve_DocVentaPuntoVentaDet` WRITE;
/*!40000 ALTER TABLE `Ve_DocVentaPuntoVentaDet` DISABLE KEYS */;
INSERT INTO `Ve_DocVentaPuntoVentaDet` VALUES (1,1,'004'),(1,2,'004'),(1,3,'004'),(1,4,'0'),(1,5,'004'),(1,6,'F004'),(1,7,'B004'),(1,8,'F104'),(1,9,'002'),(1,10,'T004'),(1,11,'_C04'),(2,1,'005'),(2,2,'005'),(2,3,'005'),(2,4,'0'),(2,5,'005'),(2,6,'F005'),(2,7,'B005'),(2,8,'F005'),(2,9,'005'),(2,10,'T005'),(2,11,'_C05');
/*!40000 ALTER TABLE `Ve_DocVentaPuntoVentaDet` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Ve_DocVentaRemision`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Ve_DocVentaRemision` (
  `IdDocVentaRemision` int(11) NOT NULL AUTO_INCREMENT,
  `IdDocVenta` int(11) NOT NULL,
  `FechaReg` datetime DEFAULT NULL,
  `FechaTraslado` datetime DEFAULT NULL,
  `DireccionPartida` varchar(255) DEFAULT NULL,
  `DistritoPartida` varchar(255) DEFAULT NULL,
  `ProvinciaPartida` varchar(255) DEFAULT NULL,
  `DepartamentoPartida` varchar(255) DEFAULT NULL,
  `DireccionLlegada` varchar(255) DEFAULT NULL,
  `DistritoLlegada` varchar(255) DEFAULT NULL,
  `ProvinciaLlegada` varchar(255) DEFAULT NULL,
  `DepartamentoLlegada` varchar(255) DEFAULT NULL,
  `RazonSocialDestinatario` varchar(255) DEFAULT NULL,
  `RucDestinatario` varchar(255) DEFAULT NULL,
  `EmpresaTransporte` varchar(255) DEFAULT NULL,
  `RucEmpresaTransporte` varchar(45) DEFAULT NULL,
  `MarcaPlacaTransporte` varchar(255) DEFAULT NULL,
  `LicenciaConductor` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`IdDocVentaRemision`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Ve_DocVentaRemision` WRITE;
/*!40000 ALTER TABLE `Ve_DocVentaRemision` DISABLE KEYS */;
/*!40000 ALTER TABLE `Ve_DocVentaRemision` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Ve_DocVentaTipoDoc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Ve_DocVentaTipoDoc` (
  `IdTipoDoc` int(11) NOT NULL AUTO_INCREMENT,
  `TipoDoc` varchar(255) DEFAULT NULL,
  `VaRegVenta` tinyint(1) DEFAULT NULL,
  `CodSunat` varchar(255) DEFAULT NULL,
  `TipoDocSunat` varchar(255) DEFAULT NULL,
  `TieneIgv` tinyint(1) DEFAULT 0,
  `LimiteItems` int(11) DEFAULT NULL,
  `CodigoIgv` varchar(45) DEFAULT '10',
  `Orden` int(11) DEFAULT 0,
  `EsElectronico` tinyint(1) DEFAULT 0,
  `Anulado` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`IdTipoDoc`),
  UNIQUE KEY `TipoDoc_UNIQUE` (`TipoDoc`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Ve_DocVentaTipoDoc` WRITE;
/*!40000 ALTER TABLE `Ve_DocVentaTipoDoc` DISABLE KEYS */;
INSERT INTO `Ve_DocVentaTipoDoc` VALUES (1,'FACTURA',1,'01','01',0,7,'20',0,0,1),(2,'BOLETA',1,'03','01',0,8,'20',0,0,1),(3,'NOTA DE VENTA',1,'12','01',0,0,'20',0,0,0),(6,'FACTURA ELEC.',1,'01','01',0,10,'20',0,1,0),(7,'BOLETA ELEC.',1,'03','01',0,8,'20',0,1,0),(9,'BOLETA SERIE 2',1,'03','01',0,0,'20',0,0,1),(10,'TICKET SEPARACIÓN DE PRENDAS',1,'12','01',0,0,'20',0,0,0),(11,'NOTA DE CREDITO ELEC.',1,'07','01',0,0,'20',0,1,0);
/*!40000 ALTER TABLE `Ve_DocVentaTipoDoc` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Ve_ExpertoDiagnostico`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Ve_ExpertoDiagnostico` (
  `IdDiagnostico` int(11) NOT NULL AUTO_INCREMENT,
  `Diagnostico` varchar(255) NOT NULL,
  `Problema` text NOT NULL,
  `Edad` int(11) DEFAULT NULL,
  `Observacion` text DEFAULT NULL,
  `FechaReg` datetime DEFAULT NULL,
  `UsuarioReg` varchar(255) DEFAULT NULL,
  `FechaMod` datetime DEFAULT NULL,
  `UsuarioMod` varchar(255) DEFAULT NULL,
  `Hash` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`IdDiagnostico`),
  UNIQUE KEY `ENFERMEDAD_UNIQUE` (`Diagnostico`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=86 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Ve_ExpertoDiagnostico` WRITE;
/*!40000 ALTER TABLE `Ve_ExpertoDiagnostico` DISABLE KEYS */;
INSERT INTO `Ve_ExpertoDiagnostico` VALUES (41,'OTITIS MEDIA AGUDA','DEL OIDO',4110002,'El Dx. se determina con supuración de oído o tímpano rojo. El tratamiento es ambulatorio','2017-10-04 07:48:19','jeam','2018-04-12 10:55:33','jeam','1507128499'),(42,'ORZUELO.','DEL OJO.',11110500,'El Dx. se determina con pequeño nodulo blanco rojizo en el parpado.','2017-10-26 06:58:01','jeam','2018-04-12 10:55:51','jeam','1509026281'),(47,'CANDIDIASIS ORAL.','ORAL.',11110500,'El Dx. se determina con placas blanca en la cavidad oral.','2017-11-04 07:27:05','jeam','2018-04-12 11:02:18','jeam','1509805625'),(44,'MASTOIDITIS','DE OIDO',4110002,'Tiene un solo signo','2017-10-31 15:24:52','jeam','2018-04-12 10:57:17','jeam','1509488692'),(48,'AFTAS BUCALES','ORAL',11110500,'El Dx. Se determina por erupción bucal de color amarillo y rojo alrededor.','2017-11-04 07:48:05','jeam','2018-04-12 11:02:53','jeam','1509806885'),(49,'ESTREÑIMIENTO ','DIGESTIVO.',11110500,'El Dx. Se determina deposiciones duras y secas, menos de 3 deposiciones por semana.','2017-11-04 08:07:50','jeam','2018-04-12 11:05:36','jeam','1509808070'),(50,'TIÑA CORPORIS.','DERMICO.',11110500,'El Dx. Se determina erupcion cutanea en forma de medallones circulares de borde escamoso y vesiculoso.','2017-11-04 08:39:37','jeam','2018-04-12 11:06:09','jeam','1509809977'),(51,'DIABETES.','',64111200,'El Dx. Se determina con sed excesiva.','2017-11-04 09:08:21','jeam','2018-04-12 11:06:35','jeam','1509811701'),(52,'CONJUNTIVITIS','DEL OJO.',64111200,'Secreción purulenta amarillo - verdosa, con inflamación y enrojecimientos de los ojos.','2017-11-04 09:32:55','jeam','2018-04-12 11:07:51','jeam','1509813175'),(53,'INFECCION DEL TRACTO URINARIO.','DEL TRACTO URINARIO.',64111200,'Dolor ardor al orinar.','2017-11-04 09:45:37','jeam','2017-11-04 10:21:50','jeam','1509813937'),(54,'HIPERTENSION ARTERIAL.','',64111200,'El Dx. Se determina zumbido de oídos, visión borrosa.','2017-11-04 10:05:31','jeam','2018-04-12 11:08:54','jeam','1509815131'),(55,'ARTRITIS.','',64111200,'El Dx. Se determina rigidez de las articulaciones, inflamación e hinchazón.','2017-11-04 10:51:13','jeam','2018-04-12 11:09:47','jeam','1509817873'),(56,'LINFOGRANULOMA VENEREO.','ITS',64111300,'El Dx. Se determina tumefacción y dolor en los ganglios linfáticos. ','2017-11-06 06:00:18','jeam','2018-04-12 11:10:16','jeam','1509973218'),(57,'GONORREA.','ITS',64111300,'El Dx. Se determina secreción blanco, amarillo o verden en el pene.','2017-11-06 06:11:46','jeam','2018-04-12 11:10:57','jeam','1509973906'),(58,'HERPES.','ITS.',64111300,'El Dx. se determina con con ulceras o llagas genitales.','2017-11-06 06:28:13','jeam','2018-04-12 11:12:24','jeam','1509974893'),(59,'BALANITIS.','ITS.',64111300,'El Dx. Se determina con inflamación del glande y el prepucio.','2017-11-06 06:36:39','jeam',NULL,NULL,'1509975399'),(60,'ENFERMEDAD PELVICA INFLAMATORIA.','ITS.',64111300,'El Dx. Se determina con dolor abdominal inferior.','2017-11-06 06:49:45','jeam','2017-11-10 11:31:00','jeam','1509976185'),(61,'DISENTERIA.','INTESTINAL.',4110002,'El Dx. Se determina con sangre en heces.','2017-11-06 06:57:19','jeam','2018-04-19 16:30:29','jeam','1509976639'),(62,'PARASITOSIS','INTESTINAL.',4110002,'El Dx. Se determina cuando no se desparasito en los últimos 4 meses.','2017-11-06 09:57:52','jeam',NULL,NULL,'1509987472'),(63,'RESFRIADO COMÚN.','RESPIRATORIO.',4110002,'El tratamiento es ambulatorio.','2017-11-06 10:10:19','jeam',NULL,NULL,'1509988219'),(64,'LITIASIS BILIAR.','',64111200,'El Dx. Se determina con dolor en cuadrante superior derecho del abdomen que se irradia al omóplato derecho o la espalda.','2017-11-10 06:25:16','jeam','2018-04-12 11:38:40','jeam','1510320316'),(65,'APENDICITIS.','',64111200,'El Dx. Se determina dolor abdominal agudo tipo cólico en la fosa iliaca derecha.','2017-11-10 06:43:37','jeam','2018-04-12 11:40:12','jeam','1510321417'),(66,'CELULITIS ','DERMICO.',64111200,'El tratamiento es ambulatorio.','2017-11-10 07:21:55','jeam','2018-04-12 11:14:13','jeam','1510323715'),(67,'SINUSITIS.','RESPIRATORIO.',64111200,'El tratamiento es ambulatorio.','2017-11-10 07:32:46','jeam','2018-04-12 11:15:03','jeam','1510324366'),(68,'HEPATITIS A','',64111200,'El tratamiento es ambulatorio.','2017-11-10 08:01:39','jeam','2018-04-12 11:41:30','jeam','1510326099'),(69,'FARINGOAMIGDALITIS ESTREPTOCOCICA.','RESPIRATORIO.',11110500,'El tratamiento es ambulatorio.','2017-11-10 08:20:07','jeam','2018-04-12 11:16:26','jeam','1510327207'),(70,'PAROTIDITIS.','',11110500,'El tratamiento es ambulatorio.','2017-11-10 08:28:36','jeam',NULL,NULL,'1510327716'),(71,'ESCABIOSIS.','DERMICO',11110500,'El tratamiento es ambulatorio.','2017-11-10 08:53:49','jeam','2018-04-12 11:16:57','jeam','1510329229'),(72,'PIE DE ATLETA.','DERMICO.',11110500,'El tratamiento es ambulatorio.','2017-11-10 09:08:00','jeam','2018-04-12 11:17:38','jeam','1510330080'),(73,'FARINGOAMIGDALITIS VIRAL.','RESPIRATORIO.',11110500,'El tratamiento es ambulatorio.','2017-11-10 09:13:57','jeam','2018-04-12 11:17:50','jeam','1510330437'),(74,'VAGINOSIS.','ITS.',64111300,'El tratamiento es ambulatorio.','2017-11-10 09:33:28','jeam','2018-04-12 11:18:56','jeam','1510331608'),(75,'CANDIDIASIS.','ITS',64111300,'El tratamiento es ambulatorio.','2017-11-10 09:47:04','jeam','2018-04-12 11:18:23','jeam','1510332424'),(76,'GRANULOMA INGUINAL.','ITS.',64111300,'El tratamiento es ambulatorio.','2017-11-10 11:39:28','jeam','2018-04-12 11:19:31','jeam','1510339168'),(77,'PEDICULOSIS PUBIS.','ITS.',64111300,'El tratamiento es ambulatorio.','2017-11-10 11:55:47','jeam','2018-04-12 11:19:47','jeam','1510340147'),(78,'NEUMONIA','RESPIRATORIO.',4110002,'El Dx. Se determina con respiracion rapida.','2017-11-10 12:11:55','jeam','2018-04-12 11:20:51','jeam','1510341115'),(79,'SOBA.','RESPIRATORIO.',4110002,'El tratamiento es ambulatorio.','2017-11-10 13:08:50','jeam','2018-04-12 11:21:23','jeam','1510344530'),(80,'CRUP MODERADO.','RESPIRATORIO.',4110002,'El tratamiento es ambulatorio.','2017-11-10 13:35:53','jeam','2018-04-12 11:21:39','jeam','1510346153'),(81,'DESIDRATACION.','INTESTINAL.',4110002,'El tratamiento es ambulatorio.','2017-11-10 13:51:50','jeam','2018-04-12 11:21:55','jeam','1510347110'),(82,'CISTITIS.','',4110002,'El tratamiento es ambulatorio.','2017-11-11 06:38:31','jeam','2018-04-12 11:22:34','jeam','1510407511'),(83,'MENOPAUSIA.','CLIMATERIO.',55110040,'El tratamiento es ambulatorio.','2017-11-11 11:13:44','jeam','2018-04-12 11:23:36','jeam','1510424024'),(84,'MUJER EN EDAD FERTIL','ANTICONCEPTIVO ORAL DE RUTINA.',39111500,'El anticonceptivo es de rutina via oral.','2017-11-11 11:53:45','jeam','2018-04-12 11:23:52','jeam','1510426425'),(85,'MUJER EN EDAD FERTIL.','ANTICONCEPTIVO ORAL DE EMERGENCIA.',39111500,'La administracion es via oral dentro de las 72 horas despues de las relaciones sexuales sin proteccion.','2017-11-11 12:15:57','jeam','2018-04-12 11:24:19','jeam','1510427757');
/*!40000 ALTER TABLE `Ve_ExpertoDiagnostico` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Ve_ExpertoDiagnosticoSintomaDet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Ve_ExpertoDiagnosticoSintomaDet` (
  `IdDiagnostico` int(11) NOT NULL,
  `IdSintoma` int(11) NOT NULL,
  `Fechareg` datetime DEFAULT NULL,
  `UsuarioReg` varchar(255) DEFAULT NULL,
  `FechaMod` datetime DEFAULT NULL,
  `UsuarioMod` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`IdDiagnostico`,`IdSintoma`),
  KEY `Sintomas` (`IdSintoma`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Ve_ExpertoDiagnosticoSintomaDet` WRITE;
/*!40000 ALTER TABLE `Ve_ExpertoDiagnosticoSintomaDet` DISABLE KEYS */;
INSERT INTO `Ve_ExpertoDiagnosticoSintomaDet` VALUES (41,37,'2018-04-12 10:55:33','jeam',NULL,NULL),(41,38,'2018-04-12 10:55:33','jeam',NULL,NULL),(43,37,'2017-11-02 08:25:38','jeam',NULL,NULL),(42,48,'2018-04-12 10:55:51','jeam',NULL,NULL),(42,45,'2018-04-12 10:55:51','jeam',NULL,NULL),(42,43,'2018-04-12 10:55:51','jeam',NULL,NULL),(43,38,'2017-11-02 08:25:38','jeam',NULL,NULL),(44,40,'2018-04-12 10:57:17','jeam',NULL,NULL),(45,38,'2017-11-02 11:57:47','jeam',NULL,NULL),(46,40,'2017-11-01 18:14:30','jeam',NULL,NULL),(45,40,'2017-11-02 11:57:47','jeam',NULL,NULL),(44,46,'2018-04-12 10:57:17','jeam',NULL,NULL),(47,44,'2018-04-12 11:02:18','jeam',NULL,NULL),(47,49,'2018-04-12 11:02:18','jeam',NULL,NULL),(48,51,'2018-04-12 11:02:53','jeam',NULL,NULL),(47,40,'2018-04-12 11:02:18','jeam',NULL,NULL),(48,44,'2018-04-12 11:02:53','jeam',NULL,NULL),(49,53,'2018-04-12 11:05:36','jeam',NULL,NULL),(49,52,'2018-04-12 11:05:36','jeam',NULL,NULL),(50,54,'2018-04-12 11:06:09','jeam',NULL,NULL),(51,56,'2018-04-12 11:06:35','jeam',NULL,NULL),(51,57,'2018-04-12 11:06:35','jeam',NULL,NULL),(51,58,'2018-04-12 11:06:35','jeam',NULL,NULL),(52,61,'2018-04-12 11:07:51','jeam',NULL,NULL),(53,65,'2017-11-04 10:21:50','jeam',NULL,NULL),(53,66,'2017-11-04 10:21:50','jeam',NULL,NULL),(53,64,'2017-11-04 10:21:50','jeam',NULL,NULL),(54,70,'2018-04-12 11:08:54','jeam',NULL,NULL),(54,69,'2018-04-12 11:08:54','jeam',NULL,NULL),(54,68,'2018-04-12 11:08:54','jeam',NULL,NULL),(52,44,'2018-04-12 11:07:51','jeam',NULL,NULL),(53,67,'2017-11-04 10:21:50','jeam',NULL,NULL),(54,59,'2018-04-12 11:08:54','jeam',NULL,NULL),(55,72,'2018-04-12 11:09:47','jeam',NULL,NULL),(55,44,'2018-04-12 11:09:47','jeam',NULL,NULL),(56,74,'2018-04-12 11:10:16','jeam',NULL,NULL),(57,75,'2018-04-12 11:10:57','jeam',NULL,NULL),(57,64,'2018-04-12 11:10:57','jeam',NULL,NULL),(58,77,'2018-04-12 11:12:24','jeam',NULL,NULL),(59,78,'2017-11-06 06:36:39','jeam',NULL,NULL),(59,79,'2017-11-06 06:36:39','jeam',NULL,NULL),(59,80,'2017-11-06 06:36:39','jeam',NULL,NULL),(60,82,'2017-11-10 11:31:00','jeam',NULL,NULL),(60,81,'2017-11-10 11:31:00','jeam',NULL,NULL),(61,83,'2018-04-19 16:30:29','jeam',NULL,NULL),(62,84,'2017-11-06 09:57:52','jeam',NULL,NULL),(62,85,'2017-11-06 09:57:52','jeam',NULL,NULL),(62,86,'2017-11-06 09:57:52','jeam',NULL,NULL),(62,87,'2017-11-06 09:57:52','jeam',NULL,NULL),(63,88,'2017-11-06 10:10:19','jeam',NULL,NULL),(63,89,'2017-11-06 10:10:19','jeam',NULL,NULL),(63,90,'2017-11-06 10:10:19','jeam',NULL,NULL),(63,91,'2017-11-06 10:10:19','jeam',NULL,NULL),(64,92,'2018-04-12 11:38:40','jeam',NULL,NULL),(64,93,'2018-04-12 11:38:40','jeam',NULL,NULL),(64,94,'2018-04-12 11:38:40','jeam',NULL,NULL),(65,96,'2018-04-12 11:40:12','jeam',NULL,NULL),(65,97,'2018-04-12 11:40:12','jeam',NULL,NULL),(65,98,'2018-04-12 11:40:12','jeam',NULL,NULL),(52,62,'2018-04-12 11:07:51','jeam',NULL,NULL),(66,102,'2018-04-12 11:14:13','jeam',NULL,NULL),(66,101,'2018-04-12 11:14:13','jeam',NULL,NULL),(66,100,'2018-04-12 11:14:13','jeam',NULL,NULL),(66,99,'2018-04-12 11:14:13','jeam',NULL,NULL),(67,105,'2018-04-12 11:15:03','jeam',NULL,NULL),(67,104,'2018-04-12 11:15:03','jeam',NULL,NULL),(67,103,'2018-04-12 11:15:03','jeam',NULL,NULL),(68,106,'2018-04-12 11:41:30','jeam',NULL,NULL),(68,107,'2018-04-12 11:41:30','jeam',NULL,NULL),(68,108,'2018-04-12 11:41:30','jeam',NULL,NULL),(68,95,'2018-04-12 11:41:30','jeam',NULL,NULL),(69,110,'2018-04-12 11:16:26','jeam',NULL,NULL),(70,111,'2017-11-10 08:28:36','jeam',NULL,NULL),(70,112,'2017-11-10 08:28:36','jeam',NULL,NULL),(70,113,'2017-11-10 08:28:36','jeam',NULL,NULL),(70,40,'2017-11-10 08:28:36','jeam',NULL,NULL),(71,116,'2018-04-12 11:16:57','jeam',NULL,NULL),(71,115,'2018-04-12 11:16:57','jeam',NULL,NULL),(71,114,'2018-04-12 11:16:57','jeam',NULL,NULL),(72,118,'2018-04-12 11:17:38','jeam',NULL,NULL),(72,79,'2018-04-12 11:17:38','jeam',NULL,NULL),(72,117,'2018-04-12 11:17:38','jeam',NULL,NULL),(69,109,'2018-04-12 11:16:26','jeam',NULL,NULL),(73,121,'2018-04-12 11:17:50','jeam',NULL,NULL),(73,120,'2018-04-12 11:17:50','jeam',NULL,NULL),(74,119,'2018-04-12 11:18:56','jeam',NULL,NULL),(74,82,'2018-04-12 11:18:56','jeam',NULL,NULL),(75,82,'2018-04-12 11:18:23','jeam',NULL,NULL),(75,122,'2018-04-12 11:18:23','jeam',NULL,NULL),(74,122,'2018-04-12 11:18:56','jeam',NULL,NULL),(75,125,'2018-04-12 11:18:23','jeam',NULL,NULL),(76,130,'2018-04-12 11:19:31','jeam',NULL,NULL),(76,129,'2018-04-12 11:19:31','jeam',NULL,NULL),(76,128,'2018-04-12 11:19:31','jeam',NULL,NULL),(77,132,'2018-04-12 11:19:47','jeam',NULL,NULL),(77,131,'2018-04-12 11:19:47','jeam',NULL,NULL),(78,133,'2018-04-12 11:20:51','jeam',NULL,NULL),(78,40,'2018-04-12 11:20:51','jeam',NULL,NULL),(79,140,'2018-04-12 11:21:23','jeam',NULL,NULL),(79,139,'2018-04-12 11:21:23','jeam',NULL,NULL),(79,138,'2018-04-12 11:21:23','jeam',NULL,NULL),(79,137,'2018-04-12 11:21:23','jeam',NULL,NULL),(79,136,'2018-04-12 11:21:23','jeam',NULL,NULL),(79,135,'2018-04-12 11:21:23','jeam',NULL,NULL),(80,142,'2018-04-12 11:21:39','jeam',NULL,NULL),(80,141,'2018-04-12 11:21:39','jeam',NULL,NULL),(80,40,'2018-04-12 11:21:39','jeam',NULL,NULL),(81,149,'2018-04-12 11:21:55','jeam',NULL,NULL),(81,148,'2018-04-12 11:21:55','jeam',NULL,NULL),(81,147,'2018-04-12 11:21:55','jeam',NULL,NULL),(81,146,'2018-04-12 11:21:55','jeam',NULL,NULL),(81,145,'2018-04-12 11:21:55','jeam',NULL,NULL),(81,144,'2018-04-12 11:21:55','jeam',NULL,NULL),(82,151,'2018-04-12 11:22:34','jeam',NULL,NULL),(82,150,'2018-04-12 11:22:34','jeam',NULL,NULL),(82,64,'2018-04-12 11:22:34','jeam',NULL,NULL),(83,155,'2018-04-12 11:23:36','jeam',NULL,NULL),(83,154,'2018-04-12 11:23:36','jeam',NULL,NULL),(83,153,'2018-04-12 11:23:36','jeam',NULL,NULL),(83,152,'2018-04-12 11:23:36','jeam',NULL,NULL),(83,123,'2018-04-12 11:23:36','jeam',NULL,NULL),(83,82,'2018-04-12 11:23:36','jeam',NULL,NULL),(84,158,'2018-04-12 11:23:52','jeam',NULL,NULL),(85,158,'2018-04-12 11:24:19','jeam',NULL,NULL),(41,39,'2018-04-12 10:55:33','jeam',NULL,NULL),(41,40,'2018-04-12 10:55:33','jeam',NULL,NULL),(47,50,'2018-04-12 11:02:18','jeam',NULL,NULL),(50,55,'2018-04-12 11:06:09','jeam',NULL,NULL),(51,59,'2018-04-12 11:06:35','jeam',NULL,NULL),(51,60,'2018-04-12 11:06:35','jeam',NULL,NULL),(52,63,'2018-04-12 11:07:51','jeam',NULL,NULL),(52,91,'2018-04-12 11:07:51','jeam',NULL,NULL),(54,71,'2018-04-12 11:08:54','jeam',NULL,NULL),(55,73,'2018-04-12 11:09:47','jeam',NULL,NULL),(57,76,'2018-04-12 11:10:57','jeam',NULL,NULL),(58,74,'2018-04-12 11:12:24','jeam',NULL,NULL),(64,40,'2018-04-12 11:38:40','jeam',NULL,NULL),(65,40,'2018-04-12 11:40:12','jeam',NULL,NULL),(68,40,'2018-04-12 11:41:30','jeam',NULL,NULL),(74,79,'2018-04-12 11:18:56','jeam',NULL,NULL),(74,64,'2018-04-12 11:18:56','jeam',NULL,NULL),(72,119,'2018-04-12 11:17:38','jeam',NULL,NULL),(75,127,'2018-04-12 11:18:23','jeam',NULL,NULL),(78,134,'2018-04-12 11:20:51','jeam',NULL,NULL),(80,143,'2018-04-12 11:21:39','jeam',NULL,NULL),(83,156,'2018-04-12 11:23:36','jeam',NULL,NULL),(83,157,'2018-04-12 11:23:36','jeam',NULL,NULL),(64,95,'2018-04-12 11:38:40','jeam',NULL,NULL),(61,81,'2018-04-19 16:30:29','jeam',NULL,NULL),(61,95,'2018-04-19 16:30:29','jeam',NULL,NULL),(61,40,'2018-04-19 16:30:29','jeam',NULL,NULL);
/*!40000 ALTER TABLE `Ve_ExpertoDiagnosticoSintomaDet` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Ve_ExpertoSintoma`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Ve_ExpertoSintoma` (
  `IdSintoma` int(11) NOT NULL AUTO_INCREMENT,
  `Sintoma` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`IdSintoma`),
  UNIQUE KEY `SINTOMA_UNIQUE` (`Sintoma`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=159 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Ve_ExpertoSintoma` WRITE;
/*!40000 ALTER TABLE `Ve_ExpertoSintoma` DISABLE KEYS */;
INSERT INTO `Ve_ExpertoSintoma` VALUES (43,'inflamacion de los parpados.'),(37,'Supuración visible del oído que comenzó menos de 14 días  '),(38,'Tímpano rojo'),(39,'Dolor de oido'),(40,'Fiebre'),(44,'Dolor.'),(45,'pequeño nodulo blanco rojizo en el parpado.'),(46,'Tumefacción dolorosa al tacto de tras de la oreja'),(47,'Dificultad para respirar'),(48,'Dolor en zona de abceso.'),(49,'Placas blancas en mucosa de la lengua.'),(50,'Placas blancas en labios, encias y boca.'),(51,'Erupcion bucal de color amarillo y rojo alrededor de forma ovalada.'),(52,'Menos de tres deposiciones por semana.'),(53,'Deposiciones intestinales duras y secas.'),(54,'Forma de medallones circulares de borde escamoso y vesciluso.'),(55,'erupcion cutanea de borde pruriginoso y centro escamoso.'),(56,'Sed excesiva.'),(57,'Miccion frecuente.'),(58,'Cansancio'),(59,'Vision borrosa.'),(60,'Perder la sensibilidad de los pies o sentir hormigueo.'),(61,'Secrecion purulenta amarillo o verdosa.'),(62,'Inflamacion de los ojos.'),(63,'Costras que se forman durante la noche.'),(64,'Dolor ardor al orinar.'),(65,'Aumento del numero de micciones durante la noche.'),(66,'Dolor o presion en la parte baja del abdomen y espalda.'),(67,'Presencia de sangre en la orina.'),(68,'Dolor de cabeza.'),(69,'Zumbido de oidos'),(70,'Mareos'),(71,'Valor de la presion arterial mayor a lo normal.'),(72,'Rigidez en las articulaciones.'),(73,'Inflamación de las articulaciones.'),(74,'Tumefacion y dolor en los ganglios linfáticos.'),(75,'Hinchazón del escroto.'),(76,'Secreción blanco amarillo, verdoso.'),(77,'Ulcera o llagas genitales.'),(78,'Dolor y enrojecimiento  e  inflamacion de glande y el prepucio.'),(79,'Mal olor.'),(80,'Picor intenso.'),(81,'Dolor abdominal inferior'),(82,'Dolor durante las relaciones sexuales.'),(83,'Diarrea con sangre'),(84,'No se desparasito en los ultimos 4 meses.'),(85,'abdomen voluminoso.'),(86,'Diarrea persistente.'),(87,'Picazon en el ano.'),(88,'Secreción nasal.'),(89,'Estornudos.'),(90,'Bloque de la cavidad nariz.'),(91,'Enrojecimiento de los ojos.'),(92,'Dolor en el cuadrante superior derecho del abdomen que se irradia al omoplato derecho o la espalda.'),(93,'Dolor en 30 a 90 minutos despues de haber ingerido comidas rápidas.'),(94,'Dolor que dura de 30 minutos a 3 horas e incluso días.'),(95,'Nauseas y vómitos'),(96,'Dolor abdominal agudo tipo cólico en la fosa iliaca derecha.'),(97,'Incremento rapido de la intensidad del dolor.'),(98,'Dolor que se incrementa al caminar o toser.'),(99,'Inflamación de piel.'),(100,'Enrojecimiento de la piel.'),(101,'Hinchazon cutanea brillante y estirada.'),(102,'Calor en el area enrojecida sin limites definidos.'),(103,'Dolor y pesades en la zona  ojos , frente y cara.'),(104,'Secreción nasal espesa.'),(105,'Obstrucción  nasal.'),(106,'Orina de color oscuro.'),(107,'Heces de color claro.'),(108,'Ictericia cutanea, ojos.'),(109,'Ganglios linfaticos del cuello crecidos y dolorosos.'),(110,'Garganta eritematosa con exudados blancos.'),(111,'Hinchazon dolorosa entre la oreja  y el angulo de la mandibula.'),(112,'Dolor en la masticacion, deglucion.'),(113,'Sordera.'),(114,'Prurito intenso de predominio nocturno.'),(115,'Surcos acarinos en los pliegues cutaneos.'),(116,'Nodulos rojisos marrones en zonas de pliegues cubiertas.'),(117,'Descamasion y grietas en los pliegues interdigitales.'),(118,'Vesiculas de contenido claro.'),(119,'Picazon.'),(120,'Garganta eritematosa.'),(121,'Con o sin exudados en las amigdalas.'),(122,'Flujo vaginal.'),(123,'Alteraciones mestruales.'),(124,'Prurito.'),(125,'Enrojecimiento e inflamacion de la vulva.'),(126,'Ardor.'),(127,'Ardor y prurito en los labios y en la vagina.'),(128,'Protuberancias carnosas rojas y pequeñas en los genitales o area peri anal.'),(129,'Genitales y la piel circundante pierden color.'),(130,'Destruye el tejido genital.'),(131,'Mancha roja que causa picor.'),(132,'Liendres o ladillas adheridas a la base del pelo pubico.'),(133,'Respiracion rapida.'),(134,'Sibilancias.'),(135,'Tos con predominio en la noche.'),(136,'Tos aumenta progresivamente de intensidad y frecuencia.'),(137,'Enrojecimiento de la cara.'),(138,'c/s ahogo.'),(139,'c/s agitacion.'),(140,'c/s silbido del pecho.'),(141,'Tos con voz ronca.'),(142,'C/s estridor al esfuerzo.'),(143,'Afonia.'),(144,'Intranquilo.'),(145,'Irritable.'),(146,'Ojos undidos.'),(147,'Boca y lengua seca.'),(148,'Bebe avidamente con sed.'),(149,'Signo de pliegue cutaneo.'),(150,'Aumento en numero de micciones en escasa cantidad.'),(151,'Sensacion de vacio incompleto en la evacuacion de la orina.'),(152,'Tener entre 40 a 55 años.'),(153,'Bochorno y sudoracion.'),(154,'Hemorragia anormal.'),(155,'Sequedad vaginal.'),(156,'Infecciones urinarias frecuentes.'),(157,'Ausencia de mestruacion igual o mayor de 12 meses.'),(158,'Control natal.');
/*!40000 ALTER TABLE `Ve_ExpertoSintoma` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Ve_ExpertoTratamiento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Ve_ExpertoTratamiento` (
  `IdTratamiento` int(11) NOT NULL AUTO_INCREMENT,
  `IdDiagnostico` int(11) NOT NULL,
  `IdCompuesto` int(11) NOT NULL,
  `Observacion` text DEFAULT NULL,
  `TomasXDia` float DEFAULT NULL,
  `DosisXPeso` float DEFAULT NULL,
  `UnidadDosisXPeso` varchar(255) DEFAULT NULL,
  `Concentracion` float DEFAULT NULL,
  `NroDias` int(11) DEFAULT NULL,
  `FechaReg` datetime DEFAULT NULL,
  `UsuarioReg` varchar(255) DEFAULT NULL,
  `FechaMod` datetime DEFAULT NULL,
  `UsuarioMod` varchar(255) DEFAULT NULL,
  `Hash` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`IdTratamiento`)
) ENGINE=MyISAM AUTO_INCREMENT=255 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Ve_ExpertoTratamiento` WRITE;
/*!40000 ALTER TABLE `Ve_ExpertoTratamiento` DISABLE KEYS */;
INSERT INTO `Ve_ExpertoTratamiento` VALUES (101,0,0,'El tratamiento es ví oral',2,5,'null',5,7,'2017-10-04 07:34:21','Jeam','2018-04-12 08:10:32','Jeam','1507127661'),(102,0,0,'El tratamiento es vía oral y se dosifica con la trimetropina',2,5,'null',5,7,'2017-10-04 07:38:54','Jeam','2018-04-12 08:21:28','Jeam','1507127934'),(103,41,70,'El tratamiento es vía oral y es para aliviar los síntomas de dolor y la fiebre.',3,12,'ML.',5,3,'2017-10-04 07:41:09','Jeam','2018-04-12 10:55:30','Jeam','1507128069'),(104,0,50,'',3,15,NULL,5,7,'2017-10-24 14:15:46','Jeam',NULL,NULL,'1508879746'),(105,0,50,'',3,15,NULL,5,7,'2017-10-24 14:15:47','Jeam',NULL,NULL,'1508879747'),(106,0,50,'Antibiótico conjugado, la dosificación se hace con la amoxicilina',3,15,NULL,5,7,'2017-10-24 15:01:06','Jeam',NULL,NULL,'1508882466'),(107,0,50,'via poral expresado como amoxicilina ',3,10,NULL,5,7,'2017-10-25 09:21:32','Jeam',NULL,NULL,'1508948492'),(108,42,514,'',3,30,'ML.',5,5,'2017-10-26 06:55:49','Jeam','2018-04-12 10:55:48','Jeam','1509026149'),(109,0,116,'',1,10,NULL,5,3,'2017-10-30 13:14:02','Jeam',NULL,NULL,'1509394442'),(110,0,44,'',2,2,NULL,2,2,'2017-10-31 13:51:24','Jeam',NULL,NULL,'1509483084'),(111,43,44,'',3,3,NULL,3,3,'2017-10-31 13:52:07','Jeam',NULL,NULL,'1509483127'),(112,0,50,'Antibiótico compuesto, se dosifica con la amoxicilina y es vía oral',3,15,NULL,5,7,'2017-10-31 15:17:08','Jeam',NULL,NULL,'1509488228'),(113,0,50,'',3,15,NULL,5,7,'2017-10-31 15:18:25','Jeam',NULL,NULL,'1509488305'),(114,0,206,'',1,50000,NULL,5,3,'2017-10-31 15:19:40','Jeam',NULL,NULL,'1509488380'),(115,0,206,'Antibiotico de vía I. M.',1,50000,NULL,5,3,'2017-10-31 15:28:37','Jeam',NULL,NULL,'1509488917'),(116,0,206,'Antibiótico de vía I.M. ',1,50000,NULL,5,3,'2017-10-31 16:09:53','Jeam',NULL,NULL,'1509491393'),(117,0,206,'',1,50000,NULL,5,3,'2017-10-31 16:11:49','Jeam',NULL,NULL,'1509491509'),(127,0,48,'2',2,2,NULL,2,2,'2017-11-02 10:45:10','Jeam',NULL,NULL,'1509644710'),(119,46,44,'das',3,45,NULL,2,5,'2017-11-01 18:14:19','Jeam',NULL,NULL,'1509585259'),(120,0,46,'',3,1,NULL,2,2,'2017-11-02 08:24:29','Jeam',NULL,NULL,'1509636269'),(121,0,47,'',5,3,NULL,4,3,'2017-11-02 08:25:33','Jeam',NULL,NULL,'1509636333'),(122,0,206,'-',1,50000,NULL,5,3,'2017-11-02 08:28:27','Jeam',NULL,NULL,'1509636507'),(123,0,103,'-',5,5,NULL,5,7,'2017-11-02 08:55:17','Jeam',NULL,NULL,'1509638117'),(126,0,45,'1',1,1,NULL,1,1,'2017-11-02 10:39:03','Jeam',NULL,NULL,'1509644343'),(125,45,45,'aaaaa',2,3,NULL,4,1,'2017-11-02 09:28:47','Jeam','2017-11-02 10:37:29','Jeam','1509640127'),(128,0,44,'23',32,23,NULL,23,23,'2017-11-02 10:53:04','Jeam',NULL,NULL,'1509645184'),(129,0,44,'12',12,12,NULL,12,12,'2017-11-02 10:54:49','Jeam',NULL,NULL,'1509645289'),(130,0,44,'2',2,2,NULL,2,2,'2017-11-02 10:56:23','Jeam',NULL,NULL,'1509645383'),(131,0,47,'2',2,2,NULL,2,2,'2017-11-02 10:57:10','Jeam',NULL,NULL,'1509645430'),(132,0,47,'2',2,2,NULL,2,2,'2017-11-02 10:58:46','Jeam',NULL,NULL,'1509645526'),(133,0,48,'2',2,2,NULL,2,2,'2017-11-02 10:59:16','Jeam',NULL,NULL,'1509645556'),(134,0,46,'2',2,2,NULL,2,2,'2017-11-02 11:00:04','Jeam',NULL,NULL,'1509645604'),(135,0,45,'2',2,2,NULL,2,2,'2017-11-02 11:01:35','Jeam',NULL,NULL,'1509645695'),(136,0,48,'3',2,2,'ml',3,2,'2017-11-02 11:22:39','Jeam',NULL,NULL,'1509646959'),(137,0,924,'334',23,23,'gr',34,23,'2017-11-02 11:25:38','Jeam',NULL,NULL,'1509647138'),(138,0,921,'2',2,2,'2',2,2,'2017-11-02 11:30:53','Jeam',NULL,NULL,'1509647453'),(139,45,44,'2',2,2,'fedf',2,2,'2017-11-02 11:40:07','Jeam','2017-11-02 11:56:22','Jeam','1509648007'),(140,0,924,'234',12,23,'sdf',24,23,'2017-11-02 11:56:41','Jeam','2017-11-02 11:56:51','Jeam','1509649001'),(141,44,206,'-',1,50000,'ML.',5,3,'2017-11-02 14:01:14','Jeam','2018-04-12 10:57:09','Jeam','1509656474'),(142,0,179,'-',1,17,'MG.',5,1,'2017-11-02 14:02:58','Jeam',NULL,NULL,'1509656578'),(143,44,179,'-',1,17,'ML.',2,3,'2017-11-02 15:33:26','Jeam','2018-04-12 10:57:13','Jeam','1509662006'),(144,0,260,'-',1,20,'MG.',2,3,'2017-11-03 15:45:31','Jeam',NULL,NULL,'1509749131'),(145,44,260,'-',1,20,'ML.',2,3,'2017-11-03 15:46:52','Jeam','2018-04-12 10:57:16','Jeam','1509749212'),(146,47,417,'La administración es tópica pequeños toques en la cavidad oral.',3,2,'pinceladas',30,5,'2017-11-04 06:46:53','Jeam','2018-04-12 11:02:14','Jeam','1509803213'),(147,47,70,'',3,10,'ML.',5,3,'2017-11-04 06:48:11','Jeam','2018-04-12 11:02:16','Jeam','1509803291'),(148,47,183,'',1,50,'ML.',3.5,1,'2017-11-04 07:26:48','Jeam','2018-04-12 11:02:11','Jeam','1509805608'),(149,48,417,'Se determina con placas blanca en la cavidad oral.',3,2,'pinceladas',30,5,'2017-11-04 07:37:42','Jeam','2018-04-12 11:02:27','Jeam','1509806262'),(150,48,581,'',2,10,'ML.',5,30,'2017-11-04 07:42:34','Jeam','2018-04-12 11:02:33','Jeam','1509806554'),(151,48,874,'',2,22,'TAB.',1,30,'2017-11-04 07:43:48','Jeam','2018-04-12 11:02:45','Jeam','1509806628'),(152,48,70,'',3,10,'ML.',5,3,'2017-11-04 07:44:30','Jeam','2018-04-12 11:02:51','Jeam','1509806670'),(153,49,507,'',1,10,'ML',5,2,'2017-11-04 08:05:41','Jeam','2018-04-12 11:05:28','Jeam','1509807941'),(154,49,861,'',2,1,'UNIDAD',1,10,'2017-11-04 08:06:28','Jeam','2018-04-12 11:05:32','Jeam','1509807988'),(155,50,902,'Administracion topica dermica.',2,1,'APLICACION.',1,15,'2017-11-04 08:37:42','Jeam','2018-04-12 11:06:07','Jeam','1509809862'),(156,51,422,'',1,10,'TAB.',1,30,'2017-11-04 08:57:56','Jeam','2018-04-12 11:06:27','Jeam','1509811076'),(157,51,420,'',1,1,'TAB.',1,30,'2017-11-04 08:59:50','Jeam','2018-04-12 11:06:33','Jeam','1509811190'),(158,52,679,'',3,1,'APLICACION.',1,7,'2017-11-04 09:23:55','Jeam','2018-04-12 11:07:40','Jeam','1509812635'),(159,52,61,'',3,10,'TAB.',1,5,'2017-11-04 09:25:11','Jeam','2018-04-12 11:07:46','Jeam','1509812711'),(160,52,410,'',1,3,'ML.',2,3,'2017-11-04 09:28:08','Jeam','2018-04-12 11:07:51','Jeam','1509812888'),(161,54,133,'',1,0.5,'TAB.',1,30,'2017-11-04 10:18:01','Jeam','2018-04-12 11:08:37','Jeam','1509815881'),(162,55,251,'',1,0.33,'TAB.',1,7,'2017-11-04 10:50:11','Jeam','2018-04-12 11:09:38','Jeam','1509817811'),(163,55,923,'',1,0.44,'TAB.',1,7,'2017-11-04 10:50:56','Jeam','2018-04-12 11:09:45','Jeam','1509817856'),(164,56,141,'La administracion es por via oral.',2,11,'TAB.',1,3,'2017-11-06 05:52:13','Jeam','2018-04-12 11:10:01','Jeam','1509972733'),(165,56,124,'La administracion es dosis unica solo de 250 mg.',1,20,'ML.',3.5,1,'2017-11-06 05:56:29','Jeam','2018-04-12 11:10:07','Jeam','1509972989'),(166,56,319,'La administración es por vía oral.',4,11,'TAB.',1,15,'2017-11-06 05:59:28','Jeam','2018-04-12 11:10:14','Jeam','1509973168'),(167,57,124,'L a administracion es solo la dosis unica de 250 mg.',1,20,'ML.',3.5,1,'2017-11-06 06:06:15','Jeam','2018-04-12 11:10:35','Jeam','1509973575'),(168,57,141,'La administración es por vía oral en una dosis única de 500 mg.',1,11,'TAB.',1,1,'2017-11-06 06:08:02','Jeam','2018-04-12 11:10:41','Jeam','1509973682'),(169,57,319,'La administracion es por via oral de 500 mg dosis unica.',1,11,'TAB.',1,1,'2017-11-06 06:09:33','Jeam','2018-04-12 11:10:47','Jeam','1509973773'),(170,58,824,'La administración es por vía oral.',3,8.8,'TAB.',1,10,'2017-11-06 06:27:17','Jeam','2018-04-12 11:12:23','Jeam','1509974837'),(171,59,132,'La administración es tópica.',14,1,'',1,2,'2017-11-06 06:33:43','Jeam',NULL,NULL,'1509975223'),(172,0,240,'La administración es por vía oral.',3,10,'MG',1,3,'2017-11-06 06:40:39','Jeam',NULL,NULL,'1509975639'),(173,0,141,'La administración es por vía oral como dosis única.',1,11,'MG',1,1,'2017-11-06 06:42:20','Jeam','2017-11-06 06:43:49','Jeam','1509975740'),(174,60,240,'La administración es por vía oral.',3,10,'MG',1,3,'2017-11-06 06:46:46','Jeam',NULL,NULL,'1509976006'),(175,60,141,'La administración es por va oral dosis única de 500 mg.',1,10,'mg',1,1,'2017-11-06 06:47:35','Jeam',NULL,NULL,'1509976055'),(176,60,124,'La administración es por I.M con una dosis única de 250 mg.',1,20,'MG',3.5,1,'2017-11-06 06:48:42','Jeam',NULL,NULL,'1509976122'),(177,61,103,'La administración es por vía oral.',2,5,'mg',5,7,'2017-11-06 06:54:15','Jeam',NULL,NULL,'1509976455'),(178,61,195,'La administración es por vía oral.',2,5,' mg',5,7,'2017-11-06 06:55:22','Jeam',NULL,NULL,'1509976522'),(179,0,340,'La administración es por vía oral.',4,1.5,'mg',5,6,'2017-11-06 06:56:40','Jeam',NULL,NULL,'1509976600'),(180,62,169,'La administración es por vía oral, como dosis unica.',2,2.2,'MG',5,1,'2017-11-06 09:54:45','Jeam',NULL,NULL,'1509987285'),(181,63,266,'La administración es vía oral.',1,0.22,'mg',5,5,'2017-11-06 10:05:45','Jeam',NULL,NULL,'1509987945'),(182,63,116,'La administración es vía oral.',3,9,'mg',5,3,'2017-11-06 10:07:11','Jeam',NULL,NULL,'1509988031'),(183,64,476,'La administración es vía endovenosa.',2,0.8,'ML.',1,3,'2017-11-10 06:18:44','Jeam','2018-04-12 11:12:35','Jeam','1510319924'),(184,64,179,'La administración es vía endovenosa.',2,20,'ML.',2,3,'2017-11-10 06:20:46','Jeam','2018-04-12 11:38:38','Jeam','1510320046'),(185,64,840,'La administración es vía endovenosa.',2,0.4,'ML.',2,3,'2017-11-10 06:22:31','Jeam','2018-04-12 11:12:47','Jeam','1510320151'),(186,64,880,'La administración es vía oral.',2,1,'TAB.',1,30,'2017-11-10 06:24:02','Jeam','2018-04-12 11:13:08','Jeam','1510320242'),(187,64,252,'La administración es por vía oral.',3,1,'TAB.',1,3,'2017-11-10 06:25:07','Jeam','2018-04-12 11:13:14','Jeam','1510320307'),(188,0,296,'La administración es por vía oral dosis única de 1 gramo.',1,15,'MG',1,1,'2017-11-10 06:34:12','Jeam','2017-11-10 06:34:55','Jeam','1510320852'),(189,65,296,'La administración es por vía oral dosis única de 1 gramo.',1,15,'TAB.',1,1,'2017-11-10 06:35:48','Jeam','2018-04-12 11:13:27','Jeam','1510320948'),(190,65,410,'La administracion es por IM.',2,3.5,'ML.',2,3,'2017-11-10 06:37:21','Jeam','2018-04-12 11:13:30','Jeam','1510321041'),(191,65,141,'La administración es por vía oral.',2,10,'TAB.',1,3,'2017-11-10 06:38:24','Jeam','2018-04-12 11:24:57','Jeam','1510321104'),(192,65,124,'La administración es por vía I.M.',3,20,'ML.',3.5,1,'2017-11-10 06:39:47','Jeam','2018-04-12 11:13:41','Jeam','1510321187'),(193,66,190,'La administración es por vía endovenosa.',3,10,'ML.',4,3,'2017-11-10 07:09:24','Jeam','2018-04-12 11:13:58','Jeam','1510322964'),(194,66,61,'La administración es por vía oral.',3,10,'TAB.',1,5,'2017-11-10 07:10:15','Jeam','2018-04-12 11:14:04','Jeam','1510323015'),(195,66,166,'La administración es por vía endovenosa.',3,30,'ML.',3.5,3,'2017-11-10 07:13:08','Jeam','2018-04-12 11:14:11','Jeam','1510323188'),(196,67,61,'La administración es por vía oral.',3,10,'TAB.',1,5,'2017-11-10 07:29:21','Jeam','2018-04-12 11:14:48','Jeam','1510324161'),(197,67,87,'La administración es por vía oral.',1,10,'TAB.',1,3,'2017-11-10 07:31:36','Jeam','2018-04-12 11:14:55','Jeam','1510324296'),(198,67,107,'L a administración es por vía oral.',3,15,'ML.',1,5,'2017-11-10 07:32:44','Jeam','2018-04-12 11:15:01','Jeam','1510324364'),(199,68,696,'La administración es por vía oral.',3,5,'ML',100,30,'2017-11-10 07:59:49','Jeam','2018-04-12 11:15:17','Jeam','1510325989'),(200,69,183,'La administración es vía I.M. como dosis única.',1,50000,'ML',5,1,'2017-11-10 08:15:27','Jeam','2018-04-12 11:16:13','Jeam','1510326927'),(201,69,514,'La administracion es por via oral.',4,7.5,'ML.',5,3,'2017-11-10 08:16:42','Jeam','2018-04-12 11:16:19','Jeam','1510327002'),(202,69,50,'La administración es por vía oral.',3,10,'ML.',5,5,'2017-11-10 08:18:09','Jeam','2018-04-12 11:16:24','Jeam','1510327089'),(203,70,81,'La administración es por vía oral.',2,10,'MG',5,3,'2017-11-10 08:24:03','Jeam',NULL,NULL,'1510327443'),(204,70,634,'La administracion e spor via oral.',3,10,'MG',5,3,'2017-11-10 08:25:26','Jeam',NULL,NULL,'1510327526'),(205,71,54,'La administración es tópica.',2,1,'APLICACION.',42,15,'2017-11-10 08:41:31','Jeam','2018-04-12 11:16:46','Jeam','1510328491'),(206,0,54,'La administración es tópica.',2,1,'',42,15,'2017-11-10 08:41:31','Jeam',NULL,NULL,'1510328491'),(207,71,835,'La administración es vía oral.',1,2.5,'ML.',5,3,'2017-11-10 08:49:35','Jeam','2018-04-12 11:16:56','Jeam','1510328975'),(208,0,604,'La administración es tópica.',2,1,'mg',20,15,'2017-11-10 09:00:32','Jeam',NULL,NULL,'1510329632'),(209,72,132,'La administración es tópica.',2,1,'APLICACION.',20,15,'2017-11-10 09:02:09','Jeam','2018-04-12 11:17:18','Jeam','1510329729'),(210,0,162,'La administración es vía oral.',2,10,'mg',5,5,'2017-11-10 09:03:37','Jeam',NULL,NULL,'1510329817'),(211,72,691,'La administración es vía oral.',2,10,'ML.',5,5,'2017-11-10 09:04:27','Jeam','2018-04-12 11:17:24','Jeam','1510329867'),(212,73,70,'La administración es vía oral.',3,10,'ML.',5,3,'2017-11-10 09:12:50','Jeam','2018-04-12 11:17:49','Jeam','1510330370'),(213,74,296,'La administracion via oral con una dosis unica de 1 g.',1,20,'OVULO.',1,1,'2017-11-10 09:28:10','Jeam','2018-04-12 11:18:42','Jeam','1510331290'),(214,74,820,'La administración es intra vaginal.',1,100,'OVULO.',1,7,'2017-11-10 09:31:24','Jeam','2018-04-12 11:18:50','Jeam','1510331484'),(215,75,577,'La administración es intra vaginal.',1,1,'OVULO.',1,7,'2017-11-10 09:42:00','Jeam','2018-04-12 11:18:14','Jeam','1510332120'),(216,75,375,'La administracion es oral 1 capsula semanal por tres meses.',1,1,'cap.',1,4,'2017-11-10 09:44:29','Jeam','2018-04-12 11:18:21','Jeam','1510332269'),(217,76,319,'La administración es por vía oral.',4,10,'TAB.',1,21,'2017-11-10 11:33:56','Jeam','2018-04-12 11:19:09','Jeam','1510338836'),(218,76,141,'La administracion es por via oral.',2,15,'TAB.',1,21,'2017-11-10 11:34:38','Jeam','2018-04-12 11:19:15','Jeam','1510338878'),(219,76,101,'La administracion es por via oral.',2,16,'TAB.',1,21,'2017-11-10 11:36:28','Jeam','2018-04-12 11:19:22','Jeam','1510338988'),(220,77,760,'La administracion es topica.',1,1,'aplicacion.',60,3,'2017-11-10 11:44:40','Jeam','2018-04-12 11:19:46','Jeam','1510339480'),(221,78,108,'La administracion es por via oral previa preparacion.',3,15,'ML.',5,7,'2017-11-10 12:04:42','Jeam','2018-04-12 11:20:15','Jeam','1510340682'),(222,78,117,'La administracion es por via oral.',3,15,'ML.',5,7,'2017-11-10 12:05:34','Jeam','2018-04-12 11:20:21','Jeam','1510340734'),(223,78,103,'La administracion es por via oral.',2,5,'ML.',5,7,'2017-11-10 12:06:15','Jeam','2018-04-12 11:20:18','Jeam','1510340775'),(224,78,50,'La administracion es via oral.',3,15,'ML.',5,7,'2017-11-10 12:07:25','Jeam','2018-04-12 11:20:27','Jeam','1510340845'),(225,78,206,'La administracion es via intramuscular,',1,50000,'ML.',5,3,'2017-11-10 12:08:23','Jeam','2018-04-12 11:20:33','Jeam','1510340903'),(226,78,179,'La administracion por via intramuscular.',1,20,'ML.',2,1,'2017-11-10 12:09:36','Jeam','2018-04-12 11:20:43','Jeam','1510340976'),(227,78,634,'La administracion es por via oral.',3,10,'ML.',4,3,'2017-11-10 12:11:00','Jeam','2018-04-12 11:20:48','Jeam','1510341060'),(228,79,888,'La administracion es inhalatoria.',3,2,'inhalaciones por dosis.',200,3,'2017-11-10 13:00:57','Jeam','2018-04-12 11:21:08','Jeam','1510344057'),(229,79,904,'La administracion es por via oral.',3,0.15,'ML.',5,3,'2017-11-10 13:04:23','Jeam','2018-04-12 11:21:13','Jeam','1510344263'),(230,79,302,'La administracion es por via oral.',2,0.6,'ML.',5,3,'2017-11-10 13:05:26','Jeam','2018-04-12 11:21:18','Jeam','1510344326'),(231,80,302,'La administracion por via oral.',2,0.6,'ML.',5,3,'2017-11-10 13:34:38','Jeam','2018-04-12 11:21:38','Jeam','1510346078'),(232,81,454,'La administracion es por via oral con frecuencia como rehidratante.',12,75,'ML.',60,5,'2017-11-10 13:48:58','Jeam','2018-04-12 11:21:53','Jeam','1510346938'),(233,61,291,'La administracion es via oral.',4,1.5,'mg',5,6,'2017-11-11 05:38:23','Jeam',NULL,NULL,'1510403903'),(234,82,50,'La administracion es por via oral.',3,10,'ML.',5,7,'2017-11-11 06:28:39','Jeam','2018-04-12 11:22:11','Jeam','1510406919'),(235,82,159,'La administracion es via oral.',3,10,'ML.',5,5,'2017-11-11 06:33:36','Jeam','2018-04-12 11:22:18','Jeam','1510407216'),(236,82,412,'La administracion es via intra muscular.',1,5,'ML.',2,3,'2017-11-11 06:34:22','Jeam','2018-04-12 11:22:25','Jeam','1510407262'),(237,82,92,'La administracion es via intramuscular.',1,1.5,'ML.',2,3,'2017-11-11 06:35:33','Jeam','2018-04-12 11:22:32','Jeam','1510407333'),(238,83,64,'La administaracion es via oral 1 semanal por 1 mes.',1,10,'TAB.',1,4,'2017-11-11 11:07:34','Jeam','2018-04-12 11:22:49','Jeam','1510423654'),(239,83,765,'LA administracion es via oral y intravaginal.',1,1,'OVULO.',1,7,'2017-11-11 11:08:41','Jeam','2018-04-12 11:22:59','Jeam','1510423721'),(240,83,127,'La administracion es por via oral.',1,1,'TAB.',1,30,'2017-11-11 11:09:26','Jeam','2018-04-12 11:23:06','Jeam','1510423766'),(241,83,540,'La administracion es via oral.',1,1,'CAP.',1,15,'2017-11-11 11:10:13','Jeam','2018-04-12 11:23:21','Jeam','1510423813'),(242,83,907,'La administracion es por via oral.',1,1,'TAB.',1,12,'2017-11-11 11:11:07','Jeam','2018-04-12 11:23:29','Jeam','1510423867'),(243,84,293,'La administracion es via oral de rutina de 21 dias mas 7 dias.',1,1,'gragea',1,21,'2017-11-11 11:50:18','Jeam','2018-04-12 11:23:50','Jeam','1510426218'),(244,85,330,'La administracion es via oral.',1,1,'TAB.',1,1,'2017-11-11 12:02:33','Jeam','2018-04-12 11:24:07','Jeam','1510426953'),(245,85,894,'La administracion es via oral tomar las dos grageas juntas.',1,2,'TAB.',1,2,'2017-11-11 12:07:09','Jeam','2018-04-12 11:24:17','Jeam','1510427229'),(246,0,191,'La administracion es via intramuslar.',1,1,'ML',1,1,'2017-11-11 12:26:24','Jeam',NULL,NULL,'1510428384'),(247,0,339,'La administracion es via intramuscular profunda intervalod e 28 dias.',1,1,'ML',1,1,'2017-11-11 12:32:11','Jeam',NULL,NULL,'1510428731'),(248,0,584,'La administracion es via intramuscular profunda.',1,1,'ML',1,1,'2017-11-11 12:33:45','Jeam',NULL,NULL,'1510428825'),(249,0,612,'La administarcion es via intramuscular profunda.',1,2,'ml',1,1,'2017-11-11 12:34:28','Jeam',NULL,NULL,'1510428868'),(250,0,613,'La administracion es via intramuscular profunda cada 3 meses.',1,2,'ML',2,1,'2017-11-11 12:43:27','Jeam',NULL,NULL,'1510429407'),(251,0,613,'La administracion es via intramuscular profunda con el inicio de los primeros 5 dias de la mestruacion y intervalos de 84 dias.',1,2,'ml',2,1,'2017-11-11 13:12:29','Jeam',NULL,NULL,'1510431149'),(252,41,103,'LA DOSIFICACION SE HACE CON TRIMETROPINA',2,5,'ML.',5,7,'2018-04-12 08:23:14','Jeam','2018-04-18 08:32:44','Jeam','1523542994'),(253,65,179,'LA ADMINISTRACIÓN ES VÍA ENDOVENOSA.',2,20,'ML',2,3,'2018-04-12 11:40:05','Jeam',NULL,NULL,'1523554805'),(254,68,179,'La administración es vía endovenosa.',2,20,'ML',2,3,'2018-04-12 11:41:28','Jeam',NULL,NULL,'1523554888');
/*!40000 ALTER TABLE `Ve_ExpertoTratamiento` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Ve_PreOrden`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Ve_PreOrden` (
  `IdPreOrden` int(11) NOT NULL AUTO_INCREMENT,
  `IdCliente` int(11) DEFAULT NULL,
  `FechaReg` datetime DEFAULT NULL,
  `UsuarioReg` varchar(255) DEFAULT NULL,
  `FechaMod` datetime DEFAULT NULL,
  `UsuarioMod` varchar(255) DEFAULT NULL,
  `Estado` varchar(3) DEFAULT NULL,
  PRIMARY KEY (`IdPreOrden`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Ve_PreOrden` WRITE;
/*!40000 ALTER TABLE `Ve_PreOrden` DISABLE KEYS */;
/*!40000 ALTER TABLE `Ve_PreOrden` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Ve_PreOrdenDet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Ve_PreOrdenDet` (
  `IdPreOrden` int(11) DEFAULT NULL,
  `IdProducto` int(11) DEFAULT NULL,
  `Cantidad` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Ve_PreOrdenDet` WRITE;
/*!40000 ALTER TABLE `Ve_PreOrdenDet` DISABLE KEYS */;
/*!40000 ALTER TABLE `Ve_PreOrdenDet` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Ve_Proforma`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Ve_Proforma` (
  `IdProforma` int(11) NOT NULL AUTO_INCREMENT,
  `Numero` int(11) DEFAULT NULL,
  `Anio` int(11) DEFAULT NULL,
  `IdCliente` int(11) DEFAULT NULL,
  `FechaReg` datetime DEFAULT NULL,
  `UsuarioReg` varchar(255) DEFAULT NULL,
  `FechaMod` datetime DEFAULT NULL,
  `UsuarioMod` varchar(255) DEFAULT NULL,
  `Anulado` tinyint(4) NOT NULL DEFAULT 0,
  `EsFavoritoFlag` tinyint(1) DEFAULT 0,
  `Descripcion` text DEFAULT NULL,
  `DiasValidos` int(11) DEFAULT 7,
  `EsDescripcion` tinyint(1) DEFAULT 0,
  `DescripcionProforma` text DEFAULT NULL,
  `DescripcionProforma2` text DEFAULT NULL,
  `DescripcionProforma3` text DEFAULT NULL,
  `DescripcionProforma4` text DEFAULT NULL,
  `DescripcionProforma5` text DEFAULT NULL,
  `EsCreditoProforma` tinyint(1) DEFAULT 0,
  `FechaCreditoProforma` date DEFAULT NULL,
  PRIMARY KEY (`IdProforma`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Ve_Proforma` WRITE;
/*!40000 ALTER TABLE `Ve_Proforma` DISABLE KEYS */;
/*!40000 ALTER TABLE `Ve_Proforma` ENABLE KEYS */;
UNLOCK TABLES;
DROP TABLE IF EXISTS `Ve_ProformaDet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `Ve_ProformaDet` (
  `IdProformaDet` int(11) NOT NULL AUTO_INCREMENT,
  `IdProforma` int(11) DEFAULT NULL,
  `IdProducto` int(11) DEFAULT NULL,
  `Cantidad` float DEFAULT NULL,
  `Precio` float DEFAULT NULL,
  `Descuento` float DEFAULT 0,
  `Descripcion` text DEFAULT NULL,
  PRIMARY KEY (`IdProformaDet`)
) ENGINE=MyISAM AUTO_INCREMENT=44 DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

LOCK TABLES `Ve_ProformaDet` WRITE;
/*!40000 ALTER TABLE `Ve_ProformaDet` DISABLE KEYS */;
INSERT INTO `Ve_ProformaDet` VALUES (34,19,257,1,45,0,NULL),(35,20,1,1,88,0,NULL),(36,21,56,1,40,0,NULL),(37,22,123,1,98,0,NULL),(38,23,494,1,15,0,NULL),(39,23,57,1,18,0,NULL),(40,24,55,1,38,0,NULL),(41,24,148,1,140,0,NULL),(42,25,504,1,128,0,NULL),(43,26,253,1,55,0,NULL);
/*!40000 ALTER TABLE `Ve_ProformaDet` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbCb_ListarCajaBanco` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbCb_ListarCajaBanco`(IN `var_IdCuenta` INT, IN `var_FechaDoc` DATETIME)
BEGIN
    (SELECT
	CB.IdCajaBanco,
	CB.Concepto,
	CASE TCB.Tipo
		WHEN 1 THEN 0
        ELSE CB.Importe
	END as 'Ingresos',
    CASE TCB.Tipo
		WHEN 1 THEN CB.Importe
        ELSE 0
	END as 'Salida'
    FROM Cb_CajaBanco as CB
    INNER JOIN Cb_TipoCajaBanco as TCB
    ON CB.IdTipoCajaBanco = TCB.IdTipoCajaBanco
    WHERE CB.IdCuenta = var_IdCuenta AND CB.FechaDoc = var_FechaDoc
	ORDER BY TCB.Tipo,CB.IdCajaBanco);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbCb_ListarCuenta` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbCb_ListarCuenta`()
BEGIN
	SELECT IdCuenta, Cuenta, Anulado FROM Cb_Cuenta;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbCb_ListarDocAplicados` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbCb_ListarDocAplicados`(IN `var_IdCliente` INT)
BEGIN
Select
Ve_DocVenta.IdDocVenta,
Ve_DocVenta.FechaDoc,
Ve_DocVenta.FechaCredito,
CONCAT(CASE WHEN Ve_DocVentaTipoDoc.CodSunat ='01' THEN
	'F'
ELSE
	CASE WHEN Ve_DocVentaTipoDoc.CodSunat='03' THEN
		'B'
	ELSE
		CASE WHEN Ve_DocVentaTipoDoc.CodSunat='12' THEN
			'T'
		ELSE
			'OTRO'
		END
	END
END,Ve_DocVenta.Serie,'-' , convert(Ve_DocVenta.Numero, char) )as Correlativo,
Round(Sum(Ve_DocVentaDet.Precio*Ve_DocVentaDet.Cantidad),2) as Total,
Round((SELECT IfNull((SELECT Sum(Cb_CajaBancoDet.Importe) From Cb_CajaBancoDet Where Tipo='VE' And Cb_CajaBancoDet.IdDocDet=Ve_DocVenta.IdDocVenta),0)),2) as Aplicado,
Round(Sum(Ve_DocVentaDet.Precio*Ve_DocVentaDet.Cantidad),2) -
Round((SELECT IfNull((SELECT Sum(Cb_CajaBancoDet.Importe) From Cb_CajaBancoDet Where Tipo='VE' And Cb_CajaBancoDet.IdDocDet=Ve_DocVenta.IdDocVenta),0)),2) as Saldo
From Ve_DocVenta
Inner Join Ve_DocVentaDet On Ve_DocVenta.IdDocVenta=Ve_DocVentaDet.IdDocVenta
Inner Join Ve_DocVentaTipoDoc On Ve_DocVenta.IdTipoDoc=Ve_DocVentaTipoDoc.IdTipoDoc
Where EsCredito=1 and Ve_DocVenta.IdCliente=var_IdCliente
Group by
Ve_DocVenta.IdDocVenta,
Ve_DocVenta.FechaDoc,
Ve_DocVenta.FechaCredito,
Ve_DocVentaTipoDoc.CodSunat,
Ve_DocVenta.Serie,
Ve_DocVenta.Numero
HAVING Round(Sum(Ve_DocVentaDet.Precio*Ve_DocVentaDet.Cantidad),2) -
Round((SELECT IfNull((SElect Sum(Cb_CajaBancoDet.Importe) From Cb_CajaBancoDet Where Tipo='VE' And Cb_CajaBancoDet.IdDocDet=Ve_DocVenta.IdDocVenta),0)),2)>0
Order by FechaDoc;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbCb_ListarDocAplicadosProveedor` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbCb_ListarDocAplicadosProveedor`(IN `var_IdProveedor` INT)
BEGIN
SELECT
Lo_Movimiento.Hash as Id,
Lo_Movimiento.MovimientoFecha as FechaDoc,
Lo_Movimiento.FechaVenCredito as FechaCredito,
CONCAT(CASE WHEN Lo_MovimientoTipo.CodSunat ='01' THEN
	'F'
ELSE
	CASE WHEN Lo_MovimientoTipo.CodSunat='03' THEN
		'B'
	ELSE
		CASE WHEN Lo_MovimientoTipo.CodSunat='12' THEN
			'T'
		ELSE
			'OTRO'
		END
	END
END,Lo_Movimiento.Serie,'-' , convert(Lo_Movimiento.Numero, char) )as Correlativo,
Round(Sum(Lo_MovimientoDetalle.Precio*Lo_MovimientoDetalle.Cantidad),2) as Total,
Round((SELECT IfNull((SELECT Sum(Cb_CajaBancoDet.Importe) From Cb_CajaBancoDet Where Tipo='MO' And Cb_CajaBancoDet.Hash=Lo_Movimiento.Hash),0)),2) as Aplicado,
Round(SUM(Lo_MovimientoDetalle.Precio*Lo_MovimientoDetalle.Cantidad),2) -
Round((SELECT IfNull((SELECT Sum(Cb_CajaBancoDet.Importe) From Cb_CajaBancoDet Where Tipo='MO' And Cb_CajaBancoDet.Hash=Lo_Movimiento.Hash),0)),2) as Saldo
From Lo_Movimiento
Inner Join Lo_MovimientoDetalle On Lo_Movimiento.Hash=Lo_MovimientoDetalle.hashMovimiento
Inner Join Lo_MovimientoTipo On Lo_Movimiento.IdMovimientoTipo=Lo_MovimientoTipo.IdMovimientoTipo
Where EsCredito=1 and Lo_Movimiento.IdProveedor=var_IdProveedor
Group by
Lo_Movimiento.Hash,
Lo_Movimiento.MovimientoFecha,
Lo_Movimiento.FechaVenCredito,
Lo_MovimientoTipo.CodSunat,
Lo_Movimiento.Serie,
Lo_Movimiento.Numero
HAVING Round(Sum(Lo_MovimientoDetalle.Precio*Lo_MovimientoDetalle.Cantidad),2) -
Round((SELECT IfNull((Select Sum(Cb_CajaBancoDet.Importe) From Cb_CajaBancoDet Where Tipo='MO' And Cb_CajaBancoDet.Hash=Lo_Movimiento.Hash),0)),2)>0
Order by MovimientoFecha;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbCb_ListarTipoOpe` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbCb_ListarTipoOpe`()
BEGIN
	SELECT IdTipoCajaBanco, TipoCajaBanco, Tipo FROM Cb_TipoCajaBanco;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbGen_CambiarPrecioXBloque` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbGen_CambiarPrecioXBloque`(IN `var_Bloque` VARCHAR(255), IN `var_Porcentaje` VARCHAR(255), IN `var_PrecioXMayorIgual` BIT)
BEGIN
		DECLARE v_IdProducto Int;
		DECLARE v_PrecioCosto FLOAT;
		DECLARE v_PorcentajeUtilidad FLOAT;
		DECLARE v_PrecioVenta FLOAT;
		DECLARE v_PrecioVentaFinal FLOAT;
		DECLARE done INT DEFAULT FALSE;
		declare cur1 cursor for
						Select IdProducto,PrecioCosto,PorcentajeUtilidad,Gen_Producto.PrecioContado
						from Gen_Producto
						Inner JOIN Gen_ProductoCategoria On Gen_Producto.IdProductoCategoria=Gen_ProductoCategoria.IdProductoCategoria
						Inner Join Gen_ProductoMarca On Gen_ProductoMarca.IdProductoMarca=Gen_Producto.IdProductoMarca
						Inner Join Gen_ProductoFormaFarmaceutica On Gen_ProductoFormaFarmaceutica.IdProductoFormaFarmaceutica=Gen_Producto.IdProductoFormaFarmaceutica
						Inner Join Gen_ProductoBloque On Gen_Producto.IdBloque=Gen_ProductoBloque.IdBloque
						where Gen_ProductoBloque.Bloque=var_Bloque and Gen_Producto.Anulado=0;
		declare continue handler for not found set done=1;
    set done = 0;
    open cur1;
igmLoop: loop
        fetch cur1 into v_IdProducto,v_PrecioCosto,v_PorcentajeUtilidad,v_PrecioVenta;
        if done = 1 then leave igmLoop; end if;
							SET v_PrecioVentaFinal=v_PrecioCosto*(var_Porcentaje/100);
							IF var_PrecioXMayorIgual=1 THEN
									UPDATE Gen_Producto
									SET PorcentajeUtilidad=var_Porcentaje,
											PrecioVenta=v_PrecioVentaFinal,
											PrecioPorMayor=v_PrecioVentaFinal,
											StockMinimo=1
									WHERE Gen_Producto.IdProducto=v_IdProducto;
							ELSE
									UPDATE Gen_Producto
									SET
											PorcentajeUtilidad=var_Porcentaje,
											PrecioVenta=v_PrecioVentaFinal
									WHERE Gen_Producto.IdProducto=v_IdProducto;
							END IF;
    end loop igmLoop;
    close cur1;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbGen_CompuestoGuardar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbGen_CompuestoGuardar`(IN `var_Compuesto` VARCHAR(255), IN `var_UsuarioReg` VARCHAR(255))
BEGIN
SET @Hash2=(Select UNIX_TIMESTAMP());
INSERT INTO Gen_ProductoCompuesto(Gen_ProductoCompuesto.ProductoCompuesto, Gen_ProductoCompuesto.Anulado, Gen_ProductoCompuesto.FechaReg, Gen_ProductoCompuesto.UsuarioReg, Gen_ProductoCompuesto.`Hash`)
	VALUES (
		var_Compuesto,
		0,
		NOW(),
		var_UsuarioReg,
		@Hash2
);
SELECT Gen_ProductoCompuesto.IdProductoCompuesto FROM Gen_ProductoCompuesto WHERE Gen_ProductoCompuesto.`Hash` = @Hash2;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbGen_ListarProductoBloque` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbGen_ListarProductoBloque`()
BEGIN
select IdBloque, Bloque, PorcentajeMin, PorcentajeMax from Gen_ProductoBloque;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbGen_ListarProductoDet` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbGen_ListarProductoDet`(IN `var_Producto` INT)
BEGIN
SELECT Gen_Producto.IdProducto, Gen_Producto.Producto, Gen_ProductoDet.Cantidad FROM Gen_ProductoDet
INNER JOIN Gen_Producto ON Gen_Producto.IdProducto = Gen_ProductoDet.IdProductoDet
 WHERE Gen_ProductoDet.IdProducto = var_Producto;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbGen_ProductoCompuestoGuardar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbGen_ProductoCompuestoGuardar`(IN `var_Compuesto` INT, IN `var_Producto` INT)
BEGIN
INSERT INTO Gen_ProductoCompuestoDet(Gen_ProductoCompuestoDet.Gen_ProductoCompuesto_IdProductoCompuesto, Gen_ProductoCompuestoDet.Gen_Producto_IdProducto)
	VALUES (
		var_Compuesto,
		var_Producto
);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbGen_ProductoDetGuardar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbGen_ProductoDetGuardar`(IN `var_idProducto` INT, IN `var_idProductoDet` INT, IN `var_cantidad` FLOAT)
BEGIN
INSERT INTO Gen_ProductoDet(Gen_ProductoDet.IdProducto, Gen_ProductoDet.IdProductoDet, Gen_ProductoDet.Cantidad)
VALUES (var_idProducto, var_idProductoDet, var_cantidad);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbGen_ProductoGuardar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbGen_ProductoGuardar`(IN `var_ProductoMarca` VARCHAR(255), IN `var_FormaFarmaceutica` VARCHAR(255), IN `var_Medicion` VARCHAR(255), IN `var_Categoria` VARCHAR(255), IN `var_Producto` VARCHAR(255), IN `var_ProductoDesc` TEXT, IN `var_ProductoDescCorto` VARCHAR(255), IN `var_Codigo` VARCHAR(255), IN `var_CodigoBarra` VARCHAR(255), IN `var_Dosis` VARCHAR(255), IN `var_PrecioContado` FLOAT, IN `var_PrecioPorMayor` FLOAT, IN `var_StockPorMayor` FLOAT, IN `var_ControlaStock` BIT, IN `var_StockPorMin` FLOAT, IN `var_Usuario` VARCHAR(255), IN `var_PrecioCosto` FLOAT, IN `var_VentaEstrategica` BIT, IN `var_PrecioUtilidad` FLOAT, IN `var_Bloque` VARCHAR(255))
BEGIN
SET @Hash2=(Select UNIX_TIMESTAMP());
INSERT INTO Gen_Producto(Gen_Producto.IdProductoMarca, Gen_Producto.IdProductoFormaFarmaceutica, Gen_Producto.IdProductoMedicion, Gen_Producto.IdProductoCategoria,
													Gen_Producto.Producto, Gen_Producto.ProductoDesc, Gen_Producto.ProductoDescCorto, Gen_Producto.CodigoBarra, Gen_Producto.Codigo, Gen_Producto.Dosis,
													Gen_Producto.precioContado,Gen_Producto.PrecioPorMayor,Gen_Producto.StockPorMayor, Gen_Producto.ControlaStock, Gen_Producto.StockMinimo, Gen_Producto.PrecioCosto, Gen_Producto.VentaEstrategica, Gen_Producto.PorcentajeUtilidad, Gen_Producto.IdBloque,Gen_Producto.Anulado, Gen_Producto.FechaReg, Gen_Producto.UsuarioReg,
													Gen_Producto.Hash)
						VALUES(
							(SELECT PM.IdProductoMarca FROM Gen_ProductoMarca AS PM WHERE PM.ProductoMarca  = var_ProductoMarca),
							(SELECT PFF.IdProductoFormaFarmaceutica FROM Gen_ProductoFormaFarmaceutica AS PFF WHERE PFF.ProductoFormaFarmaceutica = var_FormaFarmaceutica),
							(SELECT PME.IdProductoMedicion FROM Gen_ProductoMedicion AS PME WHERE PME.ProductoMedicion = var_Medicion),
							(SELECT PC.IdProductoCategoria FROM Gen_ProductoCategoria AS PC WHERE PC.ProductoCategoria = var_Categoria),
							var_Producto,
							var_ProductoDesc,
							var_ProductoDescCorto,
							var_CodigoBarra,
							var_Codigo,
							var_Dosis,
							var_PrecioContado,
							var_PrecioPorMayor,
							var_StockPorMayor,
							var_ControlaStock ,
							var_StockPorMin ,
							var_PrecioCosto,
							var_VentaEstrategica,
							var_PrecioUtilidad,
							 (SELECT PB.IdBloque FROM Gen_ProductoBloque AS PB WHERE PB.Bloque = var_Bloque),
							 0,
							 now(),
							var_Usuario,
							@Hash2
							);
Update Gen_Producto Set 
Codigo=
		Concat((Select SUBSTRING(Gen_ProductoCategoria.ProductoCategoria,1,2) from Gen_ProductoCategoria Where Gen_ProductoCategoria.IdProductoCategoria=Gen_Producto.IdProductoCategoria),
						(Select SubString(Gen_ProductoFormaFarmaceutica.ProductoFormaFarmaceutica,1,2)
from Gen_ProductoFormaFarmaceutica
Where Gen_ProductoFormaFarmaceutica.IdProductoFormaFarmaceutica=Gen_Producto.IdProductoFormaFarmaceutica),
(Select SubString(Gen_ProductoMarca.ProductoMarca,1,2) 
from Gen_ProductoMarca
Where Gen_ProductoMarca.IdProductoMarca=Gen_Producto.IdProductoMarca),
(Select SubString(Gen_ProductoMedicion.ProductoMedicion,1,2) 
from Gen_ProductoMedicion
Where Gen_ProductoMedicion.IdProductoMedicion=Gen_Producto.IdProductoMedicion),
LPAD(IdProducto, 5, '0'));
SELECT Gen_Producto.IdProducto FROM Gen_Producto WHERE Hash=@Hash2;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbLo_Kardex` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbLo_Kardex`(IN `var_Producto` VARCHAR(255), IN `var_FechaIni` DATE, IN `var_FechaFin` DATE, IN `var_Tipo` INT)
BEGIN
	CREATE TABLE IF NOT EXISTS `tblKardex` (
  `d1` varchar(255) DEFAULT ' ',
  `d2` varchar(255) DEFAULT ' ',
  `d3` varchar(255) DEFAULT ' ',
	`d4` varchar(255) DEFAULT ' ',
	`d5` varchar(255) DEFAULT ' ');
	Delete from tblKardex;
	INSERT INTO tblKardex (d1) VALUES ('KARDEX');
	INSERT INTO tblKardex (d1) VALUES (' ');
	INSERT INTO tblKardex (d1,d2) VALUES ('PRODUCTO',var_Producto);
	INSERT INTO tblKardex (d1,d2) VALUES ('DESDE',var_FechaIni);
	INSERT INTO tblKardex (d1,d2) VALUES ('HASTA',var_FechaFin);
	INSERT INTO tblKardex (d1) VALUES (' ');
	INSERT INTO tblKardex (d1,d2,d3,d4,d5) VALUES ('FECHA','DETALLE','ENTRADA','SALIDA','SALDO');
	call SbLo_StockAnt (var_Producto,var_FechaIni);
	SET @SALDO=@StockAnt;
	SET @INGRESOS=0;
	SET @SALIDA=@StockAnt;
	SET @FechaSel=var_FechaIni;
	WHILE (DATEDIFF(var_FechaFin,var_FechaIni)>=0) DO
		 CALL SbLo_Kardex_IngresosUND (var_Producto,var_FechaIni,var_Tipo);
		 CALL SbLo_Kardex_IngresosCAJA (var_Producto,var_FechaIni,var_Tipo);
		 CALL SbLo_Kardex_SalidaUND (var_Producto,var_FechaIni,var_Tipo);
		  CALL SbLo_Kardex_SalidaCAJA (var_Producto,var_FechaIni,var_Tipo);
		 CALL SbLo_Kardex_SalidaVentaUND (var_Producto,var_FechaIni);
		 CALL SbLo_Kardex_SalidaVentaCAJA (var_Producto,var_FechaIni);
		SET var_FechaIni =DATE_ADD(var_FechaIni, INTERVAL 1 DAY);
	END WHILE;
 Select * From tblKardex;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbLo_KardexValorizadoINGRESOS` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbLo_KardexValorizadoINGRESOS`(IN `var_Producto` VARCHAR(255), IN `var_FechaSel` DATE, IN `var_StockInical` FLOAT, IN `var_PrecioInicial` FLOAT, OUT `var_StockFinal` FLOAT, OUT `var_PrecioFinal` FLOAT, IN `var_Tipo` INT)
BEGIN
		DECLARE V1_Saldo Float;
		DECLARE V1_Precio FLOAT;
		DECLARE V1_Total FLOAT;
		DECLARE v_cantidad  FLOAT;
		DECLARE v_Precio FLOAT;
		DECLARE v_Total FLOAT;
		declare v_doc varchar(100);
		DECLARE done INT DEFAULT FALSE;
		declare cur1 cursor for
						SELECT CONCAT(Lo_MovimientoTipo.TipoMovimiento,' ' , Lo_Movimiento.Serie ,'-',Lo_Movimiento.Numero) AS DocMovimiento,
						Lo_MovimientoDetalle.Cantidad,Lo_MovimientoDetalle.Precio, Lo_MovimientoDetalle.Cantidad*Lo_MovimientoDetalle.Precio as Total
						FROM Lo_Movimiento
						Inner Join Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
						INNER JOIN Lo_MovimientoTipo On Lo_MovimientoTipo.IdMovimientoTipo=Lo_Movimiento.IdMovimientoTipo
						WHERE 
                        Lo_MovimientoTipo.VaRegCompra = 1 AND 
                        CASE WHEN var_Tipo=0 THEN Lo_Movimiento.MovimientoFecha=var_FechaSel ELSE Lo_Movimiento.FechaStock=var_FechaSel END and Lo_MovimientoDetalle.IdProducto in
						(SELECT Gen_Producto.IdProducto FROM Gen_Producto WHERE Gen_Producto.Producto=var_Producto )
						AND Lo_Movimiento.IdAlmacenDestino>0;
		declare continue handler for not found set done=1;
    set done = 0;
    open cur1;
		SET V1_Saldo=var_StockInical;
		SET V1_Precio=var_PrecioInicial;
		SET V1_Total=V1_Saldo*V1_Precio;
    igmLoop: loop
        fetch cur1 into v_doc,v_cantidad,v_Precio,v_Total;
        if done = 1 then leave igmLoop; end if;
							SET V1_Saldo=V1_Saldo+v_cantidad;
							SET V1_Precio=ROUND((v_Total+V1_Total)/V1_Saldo,2);
							SET V1_Total=Round(V1_Saldo*V1_Precio,2);
							INSERT INTO tblKardexvalor (d1,d2,d3,d4,d5,d6,d7,d8,d9,d10,d11) VALUES (var_FechaSel,
																																											v_doc,v_cantidad,
																																											v_Precio,
																																											v_Total,
																																											'',
																																											'',
																																											'',
																																											V1_Saldo,
																																											Round(V1_Precio,2),
																																											Round(V1_Total,2));
    end loop igmLoop;
    close cur1;
		SET var_StockFinal=V1_Saldo;
		SET var_PrecioFinal=V1_Precio;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbLo_KardexValorizadoINGRESOS_Caja` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbLo_KardexValorizadoINGRESOS_Caja`(IN `var_Producto` VARCHAR(255), IN `var_FechaSel` DATE, IN `var_StockInical` FLOAT, IN `var_PrecioInicial` FLOAT, OUT `var_StockFinal` FLOAT, OUT `var_PrecioFinal` FLOAT, IN `var_Tipo` INT)
BEGIN
		DECLARE V1_Saldo Float;
		DECLARE V1_Precio FLOAT;
		DECLARE V1_Total FLOAT;
		DECLARE v_cantidad  FLOAT;
		DECLARE v_Precio FLOAT;
		DECLARE v_Total FLOAT;
		declare v_doc varchar(100);
		DECLARE done INT DEFAULT FALSE;
		declare cur1 cursor for
						SELECT CONCAT(Lo_MovimientoTipo.TipoMovimiento,' ' , Lo_Movimiento.Serie ,'-',Lo_Movimiento.Numero) AS DocMovimiento,
								Lo_MovimientoDetalle.Cantidad*
										(SELECT Gen_ProductoDet.Cantidad FROM Gen_ProductoDet WHERE Gen_ProductoDet.IdProductoDet in (
										SELECT Gen_Producto.IdProducto FROM Gen_Producto WHERE Gen_Producto.Producto=var_Producto) and Gen_ProductoDet.IdProducto=Lo_MovimientoDetalle.IdProducto LIMIT 1 ),
								Round((Lo_MovimientoDetalle.Precio/
										(SELECT Gen_ProductoDet.Cantidad FROM Gen_ProductoDet WHERE Gen_ProductoDet.IdProductoDet in (
										SELECT Gen_Producto.IdProducto FROM Gen_Producto WHERE Gen_Producto.Producto=var_Producto) and Gen_ProductoDet.IdProducto=Lo_MovimientoDetalle.IdProducto LIMIT 1 )),2),
								Round(Lo_MovimientoDetalle.Cantidad*
										(SELECT Gen_ProductoDet.Cantidad FROM Gen_ProductoDet WHERE Gen_ProductoDet.IdProductoDet in (
										SELECT Gen_Producto.IdProducto FROM Gen_Producto WHERE Gen_Producto.Producto=var_Producto) and Gen_ProductoDet.IdProducto=Lo_MovimientoDetalle.IdProducto LIMIT 1 )
								*Round((Lo_MovimientoDetalle.Precio/
										(SELECT Gen_ProductoDet.Cantidad FROM Gen_ProductoDet WHERE Gen_ProductoDet.IdProductoDet in (
										SELECT Gen_Producto.IdProducto FROM Gen_Producto WHERE Gen_Producto.Producto=var_Producto) and Gen_ProductoDet.IdProducto=Lo_MovimientoDetalle.IdProducto LIMIT 1 )),2),2)
						FROM Lo_Movimiento
						Inner Join Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
						INNER JOIN Lo_MovimientoTipo On Lo_MovimientoTipo.IdMovimientoTipo=Lo_Movimiento.IdMovimientoTipo
						WHERE 
                        Lo_MovimientoTipo.VaRegCompra = 1 AND 
                        CASE WHEN var_Tipo=0 THEN Lo_Movimiento.MovimientoFecha=var_FechaSel ELSE Lo_Movimiento.FechaStock=var_FechaSel END and Lo_MovimientoDetalle.IdProducto in
						(SELECT Gen_ProductoDet.IdProducto FROM Gen_ProductoDet WHERE Gen_ProductoDet.IdProductoDet=(
										SELECT Gen_Producto.IdProducto FROM Gen_Producto WHERE Gen_Producto.Producto=var_Producto ))
						AND Lo_Movimiento.IdAlmacenDestino>0;
		declare continue handler for not found set done=1;
    set done = 0;
    open cur1;
		SET V1_Saldo=Round(var_StockInical,2);
		SET V1_Precio=Round(var_PrecioInicial,2);
		SET V1_Total=Round(V1_Saldo*V1_Precio,2);
    igmLoop: loop
        fetch cur1 into v_doc,v_cantidad,v_Precio,v_Total;
        if done = 1 then leave igmLoop; end if;
							SET V1_Saldo=V1_Saldo+v_cantidad;
							SET V1_Precio=ROUND((v_Total+V1_Total)/V1_Saldo,2);
							SET V1_Total=Round(V1_Saldo*V1_Precio,2);
							INSERT INTO tblKardexvalor (d1,d2,d3,d4,d5,d6,d7,d8,d9,d10,d11) VALUES (var_FechaSel,
																																											v_doc,
																																											Round(v_cantidad,2),
																																											Round(v_Precio,2),
																																											Round(v_Total,2),
																																											'',
																																											'',
																																											'',
																																											V1_Saldo,
																																											Round(V1_Precio,2),
																																											Round(V1_Total,2));
    end loop igmLoop;
    close cur1;
		SET var_StockFinal=V1_Saldo;
		SET var_PrecioFinal=V1_Precio;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbLo_KardexValorizadoIniciar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbLo_KardexValorizadoIniciar`(IN `Var_Producto` VARCHAR(255), IN `var_anno` INT, IN `Var_StockInical` FLOAT, IN `Var_PrecioInicial` FLOAT)
BEGIN
	DECLARE COSTO Float;
	DECLARE var_FechaFin varchar(255);
	DECLARE var_FechaIni varchar(255);
	SET COSTO=Var_PrecioInicial*Var_StockInical;
	CREATE TABLE IF NOT EXISTS `tblKardexvalor` (
  `d1` varchar(255) DEFAULT ' ',
  `d2` varchar(255) DEFAULT ' ',
  `d3` varchar(255) DEFAULT ' ',
	`d4` varchar(255) DEFAULT ' ',
	`d5` varchar(255) DEFAULT ' ',
	`d6` varchar(255) DEFAULT ' ',
	`d7` varchar(255) DEFAULT ' ',
  `d8` varchar(255) DEFAULT ' ',
	`d9` varchar(255) DEFAULT ' ',
	`d10` varchar(255) DEFAULT ' ',
	`d11` varchar(255) DEFAULT ' ');
	Delete from tblKardexvalor;
	SET var_FechaFin=CONCAT(var_anno,'-12-31');
	SET var_FechaIni=CONCAT(var_anno,'-01-01');
	INSERT INTO tblKardexvalor (d1) VALUES ('KARDEX VALORIZADO');
	INSERT INTO tblKardexvalor (d1) VALUES (' ');
	INSERT INTO tblKardexvalor (d1,d2) VALUES ('PRODUCTO',var_Producto);
	INSERT INTO tblKardexvalor (d1,d2) VALUES ('AÑO',var_anno);
	INSERT INTO tblKardexvalor (d1) VALUES (' ');
	INSERT INTO tblKardexvalor (d1,d2) VALUES ('DESDE ',var_FechaIni);
	INSERT INTO tblKardexvalor (d1,d2) VALUES ('HASTA ',var_FechaFin);
	INSERT INTO tblKardexvalor (d1) VALUES (' ');
	INSERT INTO tblKardexvalor (d1,d2,d3,d4,d5,d6,d7,d8,d9,d10,d11) VALUES ('FECHA','DETALLE','ENTRADA','P/C','TOTAL','SALIDA','P/C','TOTAL','SALDO','P/C','TOTAL');
	INSERT INTO tblKardexvalor (d1,d2,d3,d4,d5,d6,d7,d8,d9,d10,d11) VALUES ('2017-01-01','SALDO INICIAL',Var_StockInical,Var_PrecioInicial,COSTO,'','','',Var_StockInical,Var_PrecioInicial,COSTO);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbLo_KardexValorizadoSalidaVenta_CAJA` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbLo_KardexValorizadoSalidaVenta_CAJA`(IN `var_Producto` VARCHAR(255), IN `var_FechaSel` DATE, IN `var_StockInical` FLOAT, IN `var_PrecioInicial` FLOAT, OUT `var_StockFinal` FLOAT, OUT `var_PrecioFinal` FLOAT)
BEGIN
		DECLARE V1_Saldo Float;
		DECLARE V1_Precio FLOAT;
		DECLARE V1_Total FLOAT;
		DECLARE v_cantidad  FLOAT;
		DECLARE v_Precio FLOAT;
		DECLARE v_Total FLOAT;
		declare v_doc varchar(100);
		DECLARE done INT DEFAULT FALSE;
		declare cur1 cursor for
						SELECT CONCAT(Ve_DocVentaTipoDoc.TipoDoc,' ' , Ve_DocVenta.Serie ,'-',Ve_DocVenta.Numero) AS DocMovimiento,
								Ve_DocVentaDet.Cantidad*
										(SELECT Gen_ProductoDet.Cantidad FROM Gen_ProductoDet WHERE Gen_ProductoDet.IdProductoDet in  (
										SELECT Gen_Producto.IdProducto FROM Gen_Producto WHERE Gen_Producto.Producto=var_Producto ) and Gen_ProductoDet.IdProducto=Ve_DocVentaDet.IdProducto LIMIT 1 ),
								Round(Ve_DocVentaDet.Precio/
								(SELECT Gen_ProductoDet.Cantidad FROM Gen_ProductoDet WHERE Gen_ProductoDet.IdProductoDet in  (
										SELECT Gen_Producto.IdProducto FROM Gen_Producto WHERE Gen_Producto.Producto=var_Producto ) and Gen_ProductoDet.IdProducto=Ve_DocVentaDet.IdProducto LIMIT 1 ),2),
							'0' as Total
						FROM Ve_DocVenta
						INNER JOIN Ve_DocVentaDet On Ve_DocVenta.IdDocVenta=Ve_DocVentaDet.IdDocVenta
						INNER JOIN Ve_DocVentaTipoDoc On Ve_DocVentaTipoDoc.IdTipoDoc=Ve_DocVenta.IdTipoDoc
						WHERE convert(Ve_DocVenta.FechaDoc,date)=var_FechaSel and Ve_DocVentaDet.IdProducto in
						(SELECT Gen_ProductoDet.IdProducto FROM Gen_ProductoDet WHERE Gen_ProductoDet.IdProductoDet in (
										SELECT Gen_Producto.IdProducto FROM Gen_Producto WHERE Gen_Producto.Producto=var_Producto ))
                                        AND Ve_DocVenta.Anulado = 0;
		declare continue handler for not found set done=1;
    set done = 0;
    open cur1;
		SET V1_Saldo=Round(var_StockInical,2);
		SET V1_Precio=Round(var_PrecioInicial,2);
		SET V1_Total=Round(V1_Saldo*V1_Precio,2);
    igmLoop: loop
        fetch cur1 into v_doc,v_cantidad,v_Precio,v_Total;
        if done = 1 then leave igmLoop; end if;
							SET V1_Saldo=V1_Saldo-v_cantidad;
							SET V1_Total=Round(V1_Saldo*V1_Precio,2);
							SET v_Total=Round(V1_Precio *v_cantidad,2);
							INSERT INTO tblKardexvalor (d1,d2,d3,d4,d5,d6,d7,d8,d9,d10,d11) VALUES (var_FechaSel,
																																											v_doc,
																																											'',
																																											'',
																																											'',
																																											Round(v_cantidad,2),
																																											Round(V1_Precio,2),
																																											Round(v_Total,2),
																																											V1_Saldo,
																																											Round(V1_Precio,2),
																																											Round(V1_Total,2));
    end loop igmLoop;
    close cur1;
		SET var_StockFinal=V1_Saldo;
		SET var_PrecioFinal=V1_Precio;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbLo_KardexValorizadoSalidaVenta_UND` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbLo_KardexValorizadoSalidaVenta_UND`(IN `var_Producto` VARCHAR(255), IN `var_FechaSel` DATE, IN `var_StockInical` FLOAT, IN `var_PrecioInicial` FLOAT, OUT `var_StockFinal` FLOAT, OUT `var_PrecioFinal` FLOAT)
BEGIN
		DECLARE V1_Saldo Float;
		DECLARE V1_Precio FLOAT;
		DECLARE V1_Total FLOAT;
		DECLARE v_cantidad  FLOAT;
		DECLARE v_Precio FLOAT;
		DECLARE v_Total FLOAT;
		declare v_doc varchar(100);
		DECLARE done INT DEFAULT FALSE;
		declare cur1 cursor for
						SELECT CONCAT(Ve_DocVentaTipoDoc.TipoDoc,' ' , Ve_DocVenta.Serie ,'-',Ve_DocVenta.Numero) AS DocMovimiento,Ve_DocVentaDet.Cantidad ,
						Ve_DocVentaDet.Precio,Ve_DocVentaDet.Precio*Ve_DocVentaDet.Cantidad
						FROM Ve_DocVenta
						Inner Join Ve_DocVentaDet On Ve_DocVenta.IdDocVenta=Ve_DocVentaDet.IdDocVenta
						INNER JOIN Ve_DocVentaTipoDoc On Ve_DocVentaTipoDoc.IdTipoDoc=Ve_DocVenta.IdTipoDoc
						WHERE convert(Ve_DocVenta.FechaDoc,date)=var_FechaSel and Ve_DocVentaDet.IdProducto in
						(SELECT Gen_Producto.IdProducto FROM Gen_Producto WHERE Gen_Producto.Producto=var_Producto)
                        AND Ve_DocVenta.Anulado = 0;
		declare continue handler for not found set done=1;
    set done = 0;
    open cur1;
		SET V1_Saldo=Round(var_StockInical,2);
		SET V1_Precio=Round(var_PrecioInicial,2);
		SET V1_Total=Round(V1_Saldo*V1_Precio,2);
    igmLoop: loop
        fetch cur1 into v_doc,v_cantidad,v_Precio,v_Total;
        if done = 1 then leave igmLoop; end if;
							SET V1_Saldo=V1_Saldo-v_cantidad;
							SET V1_Total=Round(V1_Saldo*V1_Precio,2);
							SET v_Total=Round(V1_Precio *v_cantidad,2);
							INSERT INTO tblKardexvalor (d1,d2,d3,d4,d5,d6,d7,d8,d9,d10,d11) VALUES (var_FechaSel,
																																											v_doc,
																																											'',
																																											'',
																																											'',
																																											Round(v_cantidad,2),
																																											Round(V1_Precio,2),
																																											Round(v_Total,2),
																																											V1_Saldo,
																																											Round(V1_Precio,2),
																																											Round(V1_Total,2));
    end loop igmLoop;
    close cur1;
		SET var_StockFinal=V1_Saldo;
		SET var_PrecioFinal=V1_Precio;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbLo_KardexValorizadoSalida_CAJA` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbLo_KardexValorizadoSalida_CAJA`(IN `var_Producto` VARCHAR(255), IN `var_FechaSel` DATE, IN `var_StockInical` FLOAT, IN `var_PrecioInicial` FLOAT, OUT `var_StockFinal` FLOAT, OUT `var_PrecioFinal` FLOAT, IN `var_Tipo` INT)
BEGIN
		DECLARE V1_Saldo Float;
		DECLARE V1_Precio FLOAT;
		DECLARE V1_Total FLOAT;
		DECLARE v_cantidad  FLOAT;
		DECLARE v_Precio FLOAT;
		DECLARE v_Total FLOAT;
		declare v_doc varchar(100);
		DECLARE done INT DEFAULT FALSE;
		declare cur1 cursor for
						SELECT CONCAT(Lo_MovimientoTipo.TipoMovimiento,' ' , Lo_Movimiento.Serie ,'-',Lo_Movimiento.Numero) AS DocMovimiento,
								Lo_MovimientoDetalle.Cantidad*
										(SELECT Gen_ProductoDet.Cantidad FROM Gen_ProductoDet WHERE Gen_ProductoDet.IdProductoDet in  (
										SELECT Gen_Producto.IdProducto FROM Gen_Producto WHERE Gen_Producto.Producto=var_Producto ) and Gen_ProductoDet.IdProducto=Lo_MovimientoDetalle.IdProducto LIMIT 1 ),
								Round((Lo_MovimientoDetalle.Precio/
										(SELECT Gen_ProductoDet.Cantidad FROM Gen_ProductoDet WHERE Gen_ProductoDet.IdProductoDet in (
										SELECT Gen_Producto.IdProducto FROM Gen_Producto WHERE Gen_Producto.Producto=var_Producto) and Gen_ProductoDet.IdProducto=Lo_MovimientoDetalle.IdProducto LIMIT 1 )),2),
								Round(Lo_MovimientoDetalle.Cantidad*
										(SELECT Gen_ProductoDet.Cantidad FROM Gen_ProductoDet WHERE Gen_ProductoDet.IdProductoDet in (
										SELECT Gen_Producto.IdProducto FROM Gen_Producto WHERE Gen_Producto.Producto=var_Producto) and Gen_ProductoDet.IdProducto=Lo_MovimientoDetalle.IdProducto LIMIT 1 )
								*Round((Lo_MovimientoDetalle.Precio/
										(SELECT Gen_ProductoDet.Cantidad FROM Gen_ProductoDet WHERE Gen_ProductoDet.IdProductoDet in (
										SELECT Gen_Producto.IdProducto FROM Gen_Producto WHERE Gen_Producto.Producto=var_Producto) and Gen_ProductoDet.IdProducto=Lo_MovimientoDetalle.IdProducto LIMIT 1 )),2),2)
						FROM Lo_Movimiento
						Inner Join Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
						INNER JOIN Lo_MovimientoTipo On Lo_MovimientoTipo.IdMovimientoTipo=Lo_Movimiento.IdMovimientoTipo
						WHERE 
                        Lo_MovimientoTipo.VaRegCompra = 1 AND 
                        CASE WHEN var_Tipo=0 THEN Lo_Movimiento.MovimientoFecha=var_FechaSel ELSE Lo_Movimiento.FechaStock=var_FechaSel END and Lo_MovimientoDetalle.IdProducto in
						(SELECT Gen_ProductoDet.IdProducto FROM Gen_ProductoDet WHERE Gen_ProductoDet.IdProductoDet=(
										SELECT Gen_Producto.IdProducto FROM Gen_Producto WHERE Gen_Producto.Producto=var_Producto ))
						AND Lo_Movimiento.IdAlmacenOrigen>0;
		declare continue handler for not found set done=1;
    set done = 0;
    open cur1;
		SET V1_Saldo=Round(var_StockInical,2);
		SET V1_Precio=Round(var_PrecioInicial,2);
		SET V1_Total=Round(V1_Saldo*V1_Precio,2);
    igmLoop: loop
        fetch cur1 into v_doc,v_cantidad,v_Precio,v_Total;
        if done = 1 then leave igmLoop; end if;
							SET V1_Saldo=V1_Saldo-v_cantidad;
							SET V1_Total=Round(V1_Saldo*V1_Precio,2);
							SET v_Total=Round(V1_Precio *v_cantidad,2);
							INSERT INTO tblKardexvalor (d1,d2,d3,d4,d5,d6,d7,d8,d9,d10,d11) VALUES (var_FechaSel,
																																											v_doc,
																																											'',
																																											'',
																																											'',
																																											Round(v_cantidad,2),
																																											Round(V1_Precio,2),
																																											Round(v_Total,2),
																																											V1_Saldo,
																																											Round(V1_Precio,2),
																																											Round(V1_Total,2));
    end loop igmLoop;
    close cur1;
		SET var_StockFinal=V1_Saldo;
		SET var_PrecioFinal=V1_Precio;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbLo_KardexValorizadoSalida_UND` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbLo_KardexValorizadoSalida_UND`(IN `var_Producto` VARCHAR(255), IN `var_FechaSel` DATE, IN `var_StockInical` FLOAT, IN `var_PrecioInicial` FLOAT, OUT `var_StockFinal` FLOAT, OUT `var_PrecioFinal` FLOAT, IN `var_Tipo` INT)
BEGIN
		DECLARE V1_Saldo Float;
		DECLARE V1_Precio FLOAT;
		DECLARE V1_Total FLOAT;
		DECLARE v_cantidad  FLOAT;
		DECLARE v_Precio FLOAT;
		DECLARE v_Total FLOAT;
		declare v_doc varchar(100);
		DECLARE done INT DEFAULT FALSE;
		declare cur1 cursor for
						SELECT CONCAT(Lo_MovimientoTipo.TipoMovimiento,' ' , Lo_Movimiento.Serie ,'-',Lo_Movimiento.Numero) AS DocMovimiento,Lo_MovimientoDetalle.Cantidad,
						Lo_MovimientoDetalle.Precio, Round(Lo_MovimientoDetalle.Cantidad*Lo_MovimientoDetalle.Precio,2)
						FROM Lo_Movimiento
						Inner Join Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
						INNER JOIN Lo_MovimientoTipo On Lo_MovimientoTipo.IdMovimientoTipo=Lo_Movimiento.IdMovimientoTipo
						WHERE 
                        Lo_MovimientoTipo.VaRegCompra = 1 AND 
                        CASE WHEN var_Tipo=0 THEN Lo_Movimiento.MovimientoFecha=var_FechaSel ELSE Lo_Movimiento.FechaStock=var_FechaSel END and Lo_MovimientoDetalle.IdProducto in
						(SELECT Gen_Producto.IdProducto FROM Gen_Producto WHERE Gen_Producto.Producto=var_Producto)
						AND Lo_Movimiento.IdAlmacenOrigen>0;
		declare continue handler for not found set done=1;
    set done = 0;
    open cur1;
		SET V1_Saldo=Round(var_StockInical,2);
		SET V1_Precio=Round(var_PrecioInicial,2);
		SET V1_Total=Round(V1_Saldo*V1_Precio,2);
    igmLoop: loop
        fetch cur1 into v_doc,v_cantidad,v_Precio,v_Total;
        if done = 1 then leave igmLoop; end if;
							SET V1_Saldo=V1_Saldo-v_cantidad;
							SET V1_Total=Round(V1_Saldo*V1_Precio,2);
							SET v_Total=Round(V1_Precio *v_cantidad,2);
							INSERT INTO tblKardexvalor (d1,d2,d3,d4,d5,d6,d7,d8,d9,d10,d11) VALUES (var_FechaSel,
																																											v_doc,
																																											'',
																																											'',
																																											'',
																																											Round(v_cantidad,2),
																																											Round(V1_Precio,2),
																																											Round(v_Total,2),
																																											V1_Saldo,
																																											Round(V1_Precio,2),
																																											Round(V1_Total,2));
    end loop igmLoop;
    close cur1;
		SET var_StockFinal=V1_Saldo;
		SET var_PrecioFinal=V1_Precio;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbLo_Kardex_IngresosCAJA` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbLo_Kardex_IngresosCAJA`(IN `var_Producto` VARCHAR(255), IN `var_FechaSel` DATE, IN `var_Tipo` INT)
BEGIN
		DECLARE v_cantidad  FLOAT;
		declare v_doc varchar(100);
		DECLARE done INT DEFAULT FALSE;
		declare cur1 cursor for
						SELECT CONCAT(Lo_MovimientoTipo.TipoMovimiento,' ' , Lo_Movimiento.Serie ,'-',Lo_Movimiento.Numero) AS DocMovimiento,
								Lo_MovimientoDetalle.Cantidad*
										(SELECT Gen_ProductoDet.Cantidad FROM Gen_ProductoDet WHERE Gen_ProductoDet.IdProductoDet in (
										SELECT Gen_Producto.IdProducto FROM Gen_Producto WHERE Gen_Producto.Producto=var_Producto) and Gen_ProductoDet.IdProducto=Lo_MovimientoDetalle.IdProducto LIMIT 1 )
						FROM Lo_Movimiento
						Inner Join Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
						INNER JOIN Lo_MovimientoTipo On Lo_MovimientoTipo.IdMovimientoTipo=Lo_Movimiento.IdMovimientoTipo
						WHERE 
                        Lo_MovimientoTipo.VaRegCompra = 1 AND 
                        CASE WHEN var_Tipo=0 THEN Lo_Movimiento.MovimientoFecha=var_FechaSel ELSE Lo_Movimiento.FechaStock=var_FechaSel END and Lo_MovimientoDetalle.IdProducto in
						(SELECT Gen_ProductoDet.IdProducto FROM Gen_ProductoDet WHERE Gen_ProductoDet.IdProductoDet=(
										SELECT Gen_Producto.IdProducto FROM Gen_Producto WHERE Gen_Producto.Producto=var_Producto ))
						AND Lo_Movimiento.IdAlmacenDestino>0;
		declare continue handler for not found set done=1;
    set done = 0;
    open cur1;
igmLoop: loop
        fetch cur1 into v_doc,v_cantidad;
        if done = 1 then leave igmLoop; end if;
							SET @SALDO=@SALDO+v_cantidad;
              INSERT INTO tblKardex (d1,d2,d3,d4,d5) VALUES (var_FechaSel,v_doc,v_cantidad,'',@SALDO);
    end loop igmLoop;
    close cur1;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbLo_Kardex_IngresosUND` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbLo_Kardex_IngresosUND`(IN `var_Producto` VARCHAR(255), IN `var_FechaSel` DATE, IN `var_Tipo` INT)
BEGIN
		DECLARE v_cantidad  FLOAT;
		declare v_doc varchar(100);
		DECLARE done INT DEFAULT FALSE;
		declare cur1 cursor for
						SELECT CONCAT(Lo_MovimientoTipo.TipoMovimiento,' ' , Lo_Movimiento.Serie ,'-',Lo_Movimiento.Numero) AS DocMovimiento,Lo_MovimientoDetalle.Cantidad
						FROM Lo_Movimiento
						Inner Join Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
						INNER JOIN Lo_MovimientoTipo On Lo_MovimientoTipo.IdMovimientoTipo=Lo_Movimiento.IdMovimientoTipo
						WHERE 
                        Lo_MovimientoTipo.VaRegCompra = 1 AND 
                        (CASE WHEN var_Tipo=0 THEN Lo_Movimiento.MovimientoFecha=var_FechaSel ELSE Lo_Movimiento.FechaStock=var_FechaSel END ) and Lo_MovimientoDetalle.IdProducto in
						(SELECT Gen_Producto.IdProducto FROM Gen_Producto WHERE Gen_Producto.Producto=var_Producto )
						AND Lo_Movimiento.IdAlmacenDestino>0;
		declare continue handler for not found set done=1;
    set done = 0;
    open cur1;
igmLoop: loop
        fetch cur1 into v_doc,v_cantidad;
        if done = 1 then leave igmLoop; end if;
							SET @SALDO=@SALDO+v_cantidad;
              INSERT INTO tblKardex (d1,d2,d3,d4,d5) VALUES (var_FechaSel,v_doc,v_cantidad,'',@SALDO);
    end loop igmLoop;
    close cur1;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbLo_Kardex_SalidaCAJA` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbLo_Kardex_SalidaCAJA`(IN `var_Producto` VARCHAR(255), IN `var_FechaSel` DATE, IN `var_Tipo` INT)
BEGIN
		DECLARE v_cantidad  FLOAT;
		declare v_doc varchar(100);
		DECLARE done INT DEFAULT FALSE;
		declare cur1 cursor for
						SELECT CONCAT(Lo_MovimientoTipo.TipoMovimiento,' ' , Lo_Movimiento.Serie ,'-',Lo_Movimiento.Numero) AS DocMovimiento,
								Lo_MovimientoDetalle.Cantidad*
										(SELECT Gen_ProductoDet.Cantidad FROM Gen_ProductoDet WHERE Gen_ProductoDet.IdProductoDet in  (
										SELECT Gen_Producto.IdProducto FROM Gen_Producto WHERE Gen_Producto.Producto=var_Producto ) and Gen_ProductoDet.IdProducto=Lo_MovimientoDetalle.IdProducto LIMIT 1 )
						FROM Lo_Movimiento
						Inner Join Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
						INNER JOIN Lo_MovimientoTipo On Lo_MovimientoTipo.IdMovimientoTipo=Lo_Movimiento.IdMovimientoTipo
						WHERE 
                        Lo_MovimientoTipo.VaRegCompra = 1 AND 
                        CASE WHEN var_Tipo=0 THEN Lo_Movimiento.MovimientoFecha=var_FechaSel ELSE Lo_Movimiento.FechaStock=var_FechaSel END and Lo_MovimientoDetalle.IdProducto in
						(SELECT Gen_ProductoDet.IdProducto FROM Gen_ProductoDet WHERE Gen_ProductoDet.IdProductoDet=(
										SELECT Gen_Producto.IdProducto FROM Gen_Producto WHERE Gen_Producto.Producto=var_Producto ))
						AND Lo_Movimiento.IdAlmacenOrigen>0;
		declare continue handler for not found set done=1;
    set done = 0;
    open cur1;
igmLoop: loop
        fetch cur1 into v_doc,v_cantidad;
        if done = 1 then leave igmLoop; end if;
							SET @SALDO=@SALDO-v_cantidad;
              INSERT INTO tblKardex (d1,d2,d3,d4,d5) VALUES (var_FechaSel,v_doc,'',v_cantidad,@SALDO);
    end loop igmLoop;
    close cur1;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbLo_Kardex_SalidaUND` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbLo_Kardex_SalidaUND`(IN `var_Producto` VARCHAR(255), IN `var_FechaSel` DATE, IN `var_Tipo` INT)
BEGIN
		DECLARE v_cantidad  FLOAT;
		declare v_doc varchar(100);
		DECLARE done INT DEFAULT FALSE;
		declare cur1 cursor for
						SELECT CONCAT(Lo_MovimientoTipo.TipoMovimiento,' ' , Lo_Movimiento.Serie ,'-',Lo_Movimiento.Numero) AS DocMovimiento,Lo_MovimientoDetalle.Cantidad
						FROM Lo_Movimiento
						Inner Join Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
						INNER JOIN Lo_MovimientoTipo On Lo_MovimientoTipo.IdMovimientoTipo=Lo_Movimiento.IdMovimientoTipo
						WHERE 
                        Lo_MovimientoTipo.VaRegCompra = 1 AND 
                        CASE WHEN var_Tipo=0 THEN Lo_Movimiento.MovimientoFecha=var_FechaSel ELSE Lo_Movimiento.FechaStock=var_FechaSel END and Lo_MovimientoDetalle.IdProducto in
						(SELECT Gen_Producto.IdProducto FROM Gen_Producto WHERE Gen_Producto.Producto=var_Producto)
						AND Lo_Movimiento.IdAlmacenOrigen>0;
		declare continue handler for not found set done=1;
    set done = 0;
    open cur1;
igmLoop: loop
        fetch cur1 into v_doc,v_cantidad;
        if done = 1 then leave igmLoop; end if;
							SET @SALDO=@SALDO-v_cantidad;
              INSERT INTO tblKardex (d1,d2,d3,d4,d5) VALUES (var_FechaSel,v_doc,'',v_cantidad,@SALDO);
    end loop igmLoop;
    close cur1;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbLo_Kardex_SalidaVentaCAJA` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbLo_Kardex_SalidaVentaCAJA`(IN `var_Producto` VARCHAR(255), IN `var_FechaSel` DATE)
BEGIN
		DECLARE v_cantidad  FLOAT;
		declare v_doc varchar(100);
		DECLARE done INT DEFAULT FALSE;
		declare cur1 cursor for
						SELECT CONCAT(Ve_DocVentaTipoDoc.TipoDoc,' ' , Ve_DocVenta.Serie ,'-',Ve_DocVenta.Numero) AS DocMovimiento,
								Ve_DocVentaDet.Cantidad*
										(SELECT Gen_ProductoDet.Cantidad FROM Gen_ProductoDet WHERE Gen_ProductoDet.IdProductoDet in  (
										SELECT Gen_Producto.IdProducto FROM Gen_Producto WHERE Gen_Producto.Producto=var_Producto ) and Gen_ProductoDet.IdProducto=Ve_DocVentaDet.IdProducto LIMIT 1 )
						FROM Ve_DocVenta
						INNER JOIN Ve_DocVentaDet On Ve_DocVenta.IdDocVenta=Ve_DocVentaDet.IdDocVenta
						INNER JOIN Ve_DocVentaTipoDoc On Ve_DocVentaTipoDoc.IdTipoDoc=Ve_DocVenta.IdTipoDoc
						WHERE convert(Ve_DocVenta.FechaDoc,date)=var_FechaSel and Ve_DocVentaDet.IdProducto in
						(SELECT Gen_ProductoDet.IdProducto FROM Gen_ProductoDet WHERE Gen_ProductoDet.IdProductoDet in (
										SELECT Gen_Producto.IdProducto FROM Gen_Producto WHERE Gen_Producto.Producto=var_Producto ));
		declare continue handler for not found set done=1;
    set done = 0;
    open cur1;
igmLoop: loop
        fetch cur1 into v_doc,v_cantidad;
        if done = 1 then leave igmLoop; end if;
							SET @SALDO=@SALDO-v_cantidad;
              INSERT INTO tblKardex (d1,d2,d3,d4,d5) VALUES (var_FechaSel,v_doc,'',v_cantidad,@SALDO);
    end loop igmLoop;
    close cur1;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbLo_Kardex_SalidaVentaUND` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbLo_Kardex_SalidaVentaUND`(IN `var_Producto` VARCHAR(255), IN `var_FechaSel` DATE)
BEGIN
		DECLARE v_cantidad  FLOAT;
		declare v_doc varchar(100);
		DECLARE done INT DEFAULT FALSE;
		declare cur1 cursor for
						SELECT CONCAT(Ve_DocVentaTipoDoc.TipoDoc,' ' , Ve_DocVenta.Serie ,'-',Ve_DocVenta.Numero) AS DocMovimiento,Ve_DocVentaDet.Cantidad
						FROM Ve_DocVenta
						Inner Join Ve_DocVentaDet On Ve_DocVenta.IdDocVenta=Ve_DocVentaDet.IdDocVenta
						INNER JOIN Ve_DocVentaTipoDoc On Ve_DocVentaTipoDoc.IdTipoDoc=Ve_DocVenta.IdTipoDoc
						WHERE convert(Ve_DocVenta.FechaDoc,date)=var_FechaSel and Ve_DocVentaDet.IdProducto in
						(SELECT Gen_Producto.IdProducto FROM Gen_Producto WHERE Gen_Producto.Producto=var_Producto);
		declare continue handler for not found set done=1;
    set done = 0;
    open cur1;
igmLoop: loop
        fetch cur1 into v_doc,v_cantidad;
        if done = 1 then leave igmLoop; end if;
							SET @SALDO=@SALDO-v_cantidad;
              INSERT INTO tblKardex (d1,d2,d3,d4,d5) VALUES (var_FechaSel,v_doc,'',v_cantidad,@SALDO);
    end loop igmLoop;
    close cur1;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbLo_ListarAlmacen` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbLo_ListarAlmacen`()
BEGIN
SELECT * FROM Lo_Almacen;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbLo_ListarMovimiento` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbLo_ListarMovimiento`()
BEGIN
SELECT IdMovimientoTipo, TipoMovimiento, Tipo, VaRegCompra, CodSunat FROM Lo_MovimientoTipo;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbLo_ListarProveedor` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbLo_ListarProveedor`()
BEGIN
SELECT * FROM Lo_Proveedor;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbLo_MovimientoAnular` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbLo_MovimientoAnular`(IN `var_Hash` VARCHAR(255), IN `var_Anular` BIT)
BEGIN
		UPDATE Lo_Movimiento Set Lo_Movimiento.Anulado=var_Anular Where Lo_Movimiento.`Hash`=var_Hash;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbLo_MovimientoDetGuardar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbLo_MovimientoDetGuardar`(IN `var_Hash` VARCHAR(255), IN `var_Producto` VARCHAR(255), IN `var_Cantidad` FLOAT, IN `var_TieneIgv` BIT, IN `var_Precio` FLOAT, IN `var_ISC` FLOAT, IN `var_FLETE` FLOAT, IN `var_IdLote` INT, IN `var_FechaVen` DATE, IN `var_PesoTotal` VARCHAR(255), IN `var_PrecioContado` FLOAT)
BEGIN
	INSERT INTO Lo_MovimientoDetalle(hashMovimiento, IdProducto, Cantidad, TieneIgv, Precio, ISC, FLETE, IdLote, FechaVen, PesoTotal)
	VALUES(
	var_Hash,
	(SELECT IdProducto FROM Gen_Producto WHERE Producto = var_Producto),
	var_Cantidad,
	var_TieneIgv,
	var_Precio,
var_ISC ,
var_FLETE ,
var_IdLote ,
var_FechaVen,
var_PesoTotal
	);
UPDATE Gen_Producto SET PrecioCosto = var_Precio, PrecioContado = var_PrecioContado WHERE Producto = var_Producto;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbLo_MovimientoEliminar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbLo_MovimientoEliminar`(IN `var_Hash` VARCHAR(255))
BEGIN
    -- Crear una tabla temporal para almacenar los productos afectados
    CREATE TEMPORARY TABLE IF NOT EXISTS Temp_ProductosAfectados (IdProducto INT);

    -- Insertar los IdProducto de los registros que serán eliminados
    INSERT INTO Temp_ProductosAfectados (IdProducto)
    SELECT DISTINCT IdProducto FROM Lo_MovimientoDetalle WHERE hashMovimiento = var_Hash;

    -- Eliminar los registros de detalle y movimiento
    DELETE FROM Lo_MovimientoDetalle WHERE hashMovimiento = var_Hash;
    DELETE FROM Lo_Movimiento WHERE Hash = var_Hash;

    -- Actualizar los productos afectados después de eliminar los registros
    UPDATE Gen_Producto
    SET 
        TotalCompras = COALESCE((
            SELECT 
                SUM(
                    CASE 
                        WHEN Lo_MovimientoTipo.Tipo = 0 THEN Lo_MovimientoDetalle.Cantidad  -- Sumar compras
                        ELSE -Lo_MovimientoDetalle.Cantidad  -- Restar ventas
                    END
                )
            FROM Lo_MovimientoDetalle
            INNER JOIN Lo_Movimiento ON Lo_MovimientoDetalle.hashMovimiento = Lo_Movimiento.Hash 
            INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo = Lo_MovimientoTipo.IdMovimientoTipo   
            WHERE Lo_MovimientoDetalle.IdProducto = Gen_Producto.IdProducto
            GROUP BY Lo_MovimientoDetalle.IdProducto
        ), 0)
    WHERE IdProducto IN (SELECT IdProducto FROM Temp_ProductosAfectados);

    -- Eliminar la tabla temporal
    DROP TEMPORARY TABLE Temp_ProductosAfectados;

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbLo_MovimientoGuardar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbLo_MovimientoGuardar`(IN `var_MovimientoTipo` VARCHAR(255), IN `var_Proveedor` VARCHAR(255), IN `var_Serie` VARCHAR(255), IN `var_Numero` INT, IN `var_Fecha` DATETIME, IN `var_AlmacenOrigen` INT, IN `var_AlmacenDestino` INT, IN `var_Observacion` TEXT, IN `var_Usuario` VARCHAR(255), IN `var_FechaStock` DATE, IN `var_Percepcion` FLOAT, IN `var_EsCredito` BIT, IN `fechaCredito` DATE, IN `var_FechaPeriodoT` INT, IN `var_TipoCambio` FLOAT, IN `var_Moneda` VARCHAR(255), IN `var_PartidaDist` VARCHAR(255), IN `var_PartidaProv` VARCHAR(255), IN `var_PartidaDpto` VARCHAR(255), IN `var_LlegadaDist` VARCHAR(255), IN `var_LlegadaProv` VARCHAR(255), IN `var_LlegadaDpto` VARCHAR(255), IN `var_DestinatarioRazonSocial` VARCHAR(255), IN `var_DestinatarioRUC` VARCHAR(255), IN `var_TransporteNumPlaca` VARCHAR(255), IN `var_TransporteNumContrato` VARCHAR(255), IN `var_TransporteNumLicencia` VARCHAR(255), IN `var_TransporteRazonSocial` VARCHAR(255), IN `var_TransporteRUC` VARCHAR(255), IN `var_IdDocVenta` INT)
BEGIN
	SET @Hash2=(SELECT UNIX_TIMESTAMP());
	INSERT INTO Lo_Movimiento(Lo_Movimiento.IdMovimientoTipo, Lo_Movimiento.IdProveedor, Lo_Movimiento.Serie, Lo_Movimiento.Numero, Lo_Movimiento.MovimientoFecha, Lo_Movimiento.IdAlmacenOrigen, Lo_Movimiento.IdAlmacenDestino, Lo_Movimiento.Observacion, Lo_Movimiento.Anulado, Lo_Movimiento.FechaReg, Lo_Movimiento.UsuarioReg, Lo_Movimiento.`Hash`, Lo_Movimiento.FechaStock, Lo_Movimiento.Percepcion, Lo_Movimiento.EsCredito, Lo_Movimiento.FechaVenCredito, Lo_Movimiento.FechaPeriodoTributario, Lo_Movimiento.TipoCambio, Lo_Movimiento.Moneda,
    Lo_Movimiento.PartidaDist, Lo_Movimiento.PartidaProv, Lo_Movimiento.PartidaDpto, Lo_Movimiento.LlegadaDist, Lo_Movimiento.LlegadaProv, Lo_Movimiento.LlegadaDpto, Lo_Movimiento.DestinatarioRazonSocial, Lo_Movimiento.DestinatarioRUC, Lo_Movimiento.TransporteNumPlaca, 
    Lo_Movimiento.TransporteNumContrato, Lo_Movimiento.TransporteNumLicencia, Lo_Movimiento.TransporteRazonSocial, Lo_Movimiento.TransporteRUC, Lo_Movimiento.IdDocVenta)
	VALUES(
		(SELECT IdMovimientoTipo FROM Lo_MovimientoTipo WHERE TipoMovimiento = var_MovimientoTipo),
		(SELECT IdProveedor FROM Lo_Proveedor WHERE Proveedor = var_Proveedor),
		var_Serie,
		var_Numero,
		var_Fecha,
		var_AlmacenOrigen,
		var_AlmacenDestino,
		var_Observacion,
		0,
		now(),
		var_Usuario,
		@Hash2,
		var_FechaStock,
		var_Percepcion,
		var_EsCredito ,
		fechaCredito ,
		var_FechaPeriodoT,
		var_TipoCambio,
		var_Moneda,
        var_PartidaDist,
        var_PartidaProv,
        var_PartidaDpto,
        var_LlegadaDist,
        var_LlegadaProv,
        var_LlegadaDpto,
        var_DestinatarioRazonSocial,
        var_DestinatarioRUC,
        var_TransporteNumPlaca,
        var_TransporteNumContrato,
        var_TransporteNumLicencia,
        var_TransporteRazonSocial,
        var_TransporteRUC,
        var_IdDocVenta
		
	);
	SELECT Lo_Movimiento.`Hash` FROM Lo_Movimiento WHERE Lo_Movimiento.`Hash` = @Hash2;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbLo_ProveedorGuardar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbLo_ProveedorGuardar`(IN `var_Proveedor` VARCHAR(255), IN `var_Ruc` VARCHAR(255), IN `var_Direccion` TEXT, IN `var_Observacion` TEXT, IN `var_Usuario` VARCHAR(255))
BEGIN
INSERT INTO Lo_Proveedor(Proveedor, Ruc, Direccion, Observacion, Anulado, FechaReg, UsuarioReg)
VALUES(
var_Proveedor,
var_Ruc,
var_Direccion,
var_Observacion,
0,
NOW(),
var_Usuario
);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbLo_RegCompraContable` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbLo_RegCompraContable`(IN `var_RegVenta` BIT, IN `var_PeriodoT` INT)
BEGIN
		SELECT
	Lo_Movimiento.`Hash` as IdMovimiento,
	Lo_Movimiento.MovimientoFecha,
	Lo_Movimiento.IdMovimientoTipo,
	Lo_MovimientoTipo.TipoMovimiento,
	Lo_Movimiento.Serie,
	Lo_Movimiento.Numero,
	Lo_Proveedor.Proveedor,
	CASE WHEN Lo_Movimiento.IdAlmacenOrigen>0 THEN
		(Select Lo_Almacen.Almacen From Lo_Almacen Where Lo_Almacen.IdAlmacen=Lo_Movimiento.IdAlmacenOrigen)
	ELSE
		'-'
	END AS AlmacenOrigen,
	CASE WHEN Lo_Movimiento.IdAlmacenDestino>0 THEN
		(Select Lo_Almacen.Almacen From Lo_Almacen Where Lo_Almacen.IdAlmacen=Lo_Movimiento.IdAlmacenDestino)
	ELSE
		'-'
	END AS AlmacenDestino,
	Lo_Movimiento.Observacion,
	Lo_Movimiento.Anulado,
	(SELECT
Sum(ROUND(Lo_MovimientoDetalle.Cantidad*Lo_MovimientoDetalle.Precio,2)) as SUBTOTAL
FROM
Lo_MovimientoDetalle
WHERE Lo_MovimientoDetalle.hashMovimiento=Lo_Movimiento.`Hash`) as SUBTOTAL,
(SELECT
Sum(CASE WHEN Lo_MovimientoDetalle.TieneIgv=1 THEN
	ROUND((Lo_MovimientoDetalle.Cantidad*Lo_MovimientoDetalle.Precio)*
	(Select Igv From GEN_EMPRESA),2)
ELSE
	0
END) as IGV
FROM
Lo_MovimientoDetalle
WHERE Lo_MovimientoDetalle.hashMovimiento=Lo_Movimiento.`Hash`) as IGV,
(SELECT
Sum(ROUND(Lo_MovimientoDetalle.Cantidad*Lo_MovimientoDetalle.Precio,2)) +
Sum(CASE WHEN Lo_MovimientoDetalle.TieneIgv=1 THEN
	ROUND((Lo_MovimientoDetalle.Cantidad*Lo_MovimientoDetalle.Precio)*
	(Select Igv From GEN_EMPRESA),2)
ELSE
	0
END) as TOTAL
FROM
Lo_MovimientoDetalle
WHERE Lo_MovimientoDetalle.hashMovimiento=Lo_Movimiento.`Hash`) as TOTAL,
	Lo_Movimiento.FechaReg,
	Lo_Movimiento.UsuarioReg,
	Lo_Movimiento.FechaMod,
	Lo_Movimiento.UsuarioMod,
(SELECT SUM(CASE WHEN ISNULL(Lo_MovimientoDetalle.ISC)=1 THEN  0 ELSE Lo_MovimientoDetalle.ISC END) FROM Lo_MovimientoDetalle WHERE Lo_MovimientoDetalle.hashMovimiento=Lo_Movimiento.`Hash`) as ISC,
(SELECT SUM(CASE WHEN ISNULL(Lo_MovimientoDetalle.FLETE)=1 THEN  0 ELSE Lo_MovimientoDetalle.FLETE END) FROM Lo_MovimientoDetalle WHERE Lo_MovimientoDetalle.hashMovimiento=Lo_Movimiento.`Hash`) as FLETE,
CASE WHEN ISNULL(Lo_Movimiento.Percepcion)=1 THEN 0 ELSE Lo_Movimiento.Percepcion END AS Percepcion,
Lo_Movimiento.TipoCambio,
Lo_Movimiento.Moneda
FROM
	Lo_Movimiento
	INNER JOIN Lo_MovimientoTipo On Lo_Movimiento.IdMovimientoTipo=Lo_MovimientoTipo.IdMovimientoTipo
	INNER JOIN Lo_Proveedor On Lo_Movimiento.IdProveedor=Lo_Proveedor.IdProveedor
WHERE
	Lo_MovimientoTipo.VaRegCompra=var_RegVenta and Lo_Movimiento.FechaPeriodoTributario = var_PeriodoT
ORDER BY Lo_MovimientoTipo.Tipo,Lo_Movimiento.IdProveedor ,Lo_Movimiento.Serie,Lo_Movimiento.Numero;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbLo_RegMovimiento` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbLo_RegMovimiento`(IN `var_RegVenta` BIT, IN `var_FechaIni` DATE, IN `var_FechaFin` DATE)
BEGIN
		SELECT
	Lo_Movimiento.`Hash` as IdMovimiento,
	Lo_Movimiento.MovimientoFecha,
	Lo_Movimiento.IdMovimientoTipo,
    
    Lo_MovimientoTipo.Tipo,
	Lo_MovimientoTipo.CodSunat,
	Lo_MovimientoTipo.TipoMovimiento,
	Lo_Movimiento.Serie,
	Lo_Movimiento.EsCredito,
	Lo_Movimiento.FechaVenCredito,
	Lo_Movimiento.Numero,
	Lo_Movimiento.FechaPeriodoTributario,
	Lo_Proveedor.Proveedor,
	CASE WHEN Lo_Movimiento.IdAlmacenOrigen>0 THEN
		(Select Lo_Almacen.Almacen From Lo_Almacen Where Lo_Almacen.IdAlmacen=Lo_Movimiento.IdAlmacenOrigen)
	ELSE
		'-'
	END AS AlmacenOrigen,
	CASE WHEN Lo_Movimiento.IdAlmacenDestino>0 THEN
		(Select Lo_Almacen.Almacen From Lo_Almacen Where Lo_Almacen.IdAlmacen=Lo_Movimiento.IdAlmacenDestino)
	ELSE
		'-'
	END AS AlmacenDestino,
	Lo_Movimiento.Observacion,
	Lo_Movimiento.Anulado,
	(SELECT
Sum(ROUND(Lo_MovimientoDetalle.Cantidad*Lo_MovimientoDetalle.Precio,2)) as SUBTOTAL
FROM
Lo_MovimientoDetalle
WHERE Lo_MovimientoDetalle.hashMovimiento=Lo_Movimiento.`Hash`) as SUBTOTAL,
(SELECT
Sum(CASE WHEN Lo_MovimientoDetalle.TieneIgv=1 THEN
	ROUND((Lo_MovimientoDetalle.Cantidad*Lo_MovimientoDetalle.Precio)*
	(Select Igv From GEN_EMPRESA),2)
ELSE
	0
END) as IGV
FROM
Lo_MovimientoDetalle
WHERE Lo_MovimientoDetalle.hashMovimiento=Lo_Movimiento.`Hash`) as IGV,
(SELECT
Sum(ROUND(Lo_MovimientoDetalle.Cantidad*Lo_MovimientoDetalle.Precio,2)) +
 Sum(ROUND(CASE WHEN ISNULL(Lo_MovimientoDetalle.ISC)=1 THEN  0 ELSE Lo_MovimientoDetalle.ISC END,2))
+ SUM(ROUND(CASE WHEN ISNULL(Lo_MovimientoDetalle.FLETE)=1 THEN  0 ELSE Lo_MovimientoDetalle.FLETE END,2))
+CASE WHEN ISNULL(Lo_Movimiento.Percepcion)=1 THEN 0 ELSE Lo_Movimiento.Percepcion END
+Sum(CASE WHEN Lo_MovimientoDetalle.TieneIgv=1 THEN
	ROUND((Lo_MovimientoDetalle.Cantidad*Lo_MovimientoDetalle.Precio)*
	(Select Igv From GEN_EMPRESA),2)
ELSE
	0
END) as TOTAL
FROM
Lo_MovimientoDetalle
WHERE Lo_MovimientoDetalle.hashMovimiento=Lo_Movimiento.`Hash`) as TOTAL,
	Lo_Movimiento.FechaReg,
	Lo_Movimiento.UsuarioReg,
	Lo_Movimiento.FechaMod,
	Lo_Movimiento.UsuarioMod,
(SELECT SUM(ROUND(CASE WHEN ISNULL(Lo_MovimientoDetalle.ISC)=1 THEN  0 ELSE Lo_MovimientoDetalle.ISC END,2)) FROM Lo_MovimientoDetalle WHERE Lo_MovimientoDetalle.hashMovimiento=Lo_Movimiento.`Hash`) as ISC,
(SELECT SUM(ROUND(CASE WHEN ISNULL(Lo_MovimientoDetalle.FLETE)=1 THEN  0 ELSE Lo_MovimientoDetalle.FLETE END,2)) FROM Lo_MovimientoDetalle WHERE Lo_MovimientoDetalle.hashMovimiento=Lo_Movimiento.`Hash`) as FLETE,
CASE WHEN ISNULL(Lo_Movimiento.Percepcion)=1 THEN 0 ELSE Lo_Movimiento.Percepcion END AS Percepcion,
Lo_Movimiento.TipoCambio,
Lo_Movimiento.Moneda
FROM
	Lo_Movimiento
	INNER JOIN Lo_MovimientoTipo On Lo_Movimiento.IdMovimientoTipo=Lo_MovimientoTipo.IdMovimientoTipo
	INNER JOIN Lo_Proveedor On Lo_Movimiento.IdProveedor=Lo_Proveedor.IdProveedor
WHERE
	Lo_MovimientoTipo.VaRegCompra=var_RegVenta and Lo_Movimiento.MovimientoFecha BETWEEN var_FechaIni and var_FechaFin
ORDER BY Lo_Movimiento.MovimientoFecha DESC;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbLo_Stock` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbLo_Stock`(IN `var_Almacen` VARCHAR(255), IN `Var_Producto` VARCHAR(255))
BEGIN
		DECLARE var_Fecha DATE;
		DECLARE v_IdProducto Int;
		DECLARE v_Marca varchar(255);
		DECLARE v_Categoria varchar(255);
		DECLARE v_FormaFarmaceutica varchar(255);
		DECLARE v_cantidad  FLOAT;
		declare v_Producto varchar(100);
		DECLARE done INT DEFAULT FALSE;
		declare cur1 cursor for
						Select IdProducto,Gen_ProductoMarca.ProductoMarca,Gen_ProductoCategoria.ProductoCategoria,Gen_ProductoFormaFarmaceutica.ProductoFormaFarmaceutica, Producto,0 as Stock
						from Gen_Producto
						Inner JOIN Gen_ProductoCategoria On Gen_Producto.IdProductoCategoria=Gen_ProductoCategoria.IdProductoCategoria
						Inner Join Gen_ProductoMarca On Gen_ProductoMarca.IdProductoMarca=Gen_Producto.IdProductoMarca
						Inner Join Gen_ProductoFormaFarmaceutica On Gen_ProductoFormaFarmaceutica.IdProductoFormaFarmaceutica=Gen_Producto.IdProductoFormaFarmaceutica
						where Producto LIKE CONCAT('%',Var_Producto,'%') and Gen_Producto.ControlaStock=1;
		declare continue handler for not found set done=1;
		SET var_Fecha=(Select CURDATE());
		SET @Fecha=DATE_ADD(var_Fecha, INTERVAL 1 DAY);
    set done = 0;
		delete from prodstock;
    open cur1;
igmLoop: loop
        fetch cur1 into v_IdProducto,v_Marca,v_Categoria,v_FormaFarmaceutica,v_Producto,v_cantidad;
        if done = 1 then leave igmLoop; end if;
							CALL SbLo_StockIngresoUnd (var_Almacen,v_Producto,@Fecha,@IngresoUnd);
							CALL SbLo_StockSalidaUnd (var_Almacen,v_Producto,@Fecha,@SalidaUnd);
							CALL SbLo_StockIngresoCaja (var_Almacen,v_Producto,@Fecha,@IngresoCaja);
							CALL SbLo_StockSalidaCaja (var_Almacen,v_Producto,@Fecha,@SalidaCaja);
							CALL SbLo_StockDocVentaUnd (var_Almacen,v_Producto,@Fecha,@DocVentaUnd);
							CALL SbLo_StockDocVentaCaja (var_Almacen,v_Producto,@Fecha,@DocVentaCaja);
							Set @Ingresos=@IngresoUnd+@IngresoCaja;
							Set @Salidas=@SalidaCaja+@SalidaUnd+@DocVentaUnd+@DocVentaCaja;
							Set @Stock=@Ingresos-@Salidas;
							Insert into prodstock (IdProducto,ProductoMarca,ProductoCategoria,FormaFarmaceutica,Producto,Stock) values (v_IdProducto,v_Marca,v_Categoria,v_FormaFarmaceutica, v_Producto,@Stock);
    end loop igmLoop;
    close cur1;
		Select prodstock.IdProducto as numero, ProductoMarca as marca,ProductoCategoria as categoria,FormaFarmaceutica as formafarmaceutica, prodstock.Producto as Producto,Stock as stock ,
		Gen_Producto.PrecioContado,
		Gen_Producto.PrecioPorMayor,
		Gen_Producto.StockPorMayor,
		Gen_Producto.Codigo,
		Gen_Producto.VentaEstrategica,
		Gen_ProductoMedicion.ProductoMedicion,
        
        Gen_Producto.CodigoBarra
		from prodstock
		INNER JOIN Gen_Producto ON Gen_Producto.IdProducto = prodstock.IdProducto
		INNER JOIN Gen_ProductoMedicion ON Gen_ProductoMedicion.IdProductoMedicion = Gen_Producto.IdProductoMedicion
		where prodstock.Producto LIKE CONCAT('%',Var_Producto,'%') order by stock desc;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbLo_StockAnt` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbLo_StockAnt`(IN `var_Producto` VARCHAR(255), IN `var_FechaIni` DATE)
BEGIN
	Set @StockAnt=(SELECT
		CASE WHEN Gen_Producto.ControlaStock=TRUE THEN
		((SELECT IFNULL((SELECT
				SUM(Lo_MovimientoDetalle.Cantidad)
			FROM
				Lo_Movimiento
				INNER JOIN Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
				INNER JOIN Lo_MovimientoTipo On Lo_MovimientoTipo.IdMovimientoTipo=Lo_Movimiento.IdMovimientoTipo
			WHERE
Lo_MovimientoTipo.VaRegCompra = 1 AND 
				Lo_MovimientoDetalle.IdProducto=Gen_Producto.IdProducto
				AND Lo_Movimiento.IdAlmacenDestino>0
				AND Lo_Movimiento.MovimientoFecha<var_FechaIni
				AND Lo_Movimiento.Anulado=false),0)) +
			(SELECT IFNULL((Select Sum(Lo_MovimientoDetalle.Cantidad)*
				(SELECT Gen_ProductoDet.Cantidad
					FROM Gen_ProductoDet
					WHERE Gen_ProductoDet.IdProducto=Lo_MovimientoDetalle.IdProducto and Gen_ProductoDet.IdProductoDet=Gen_Producto.IdProducto)
			From Lo_Movimiento
			INNER JOIN Lo_MovimientoDetalle ON Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
			where Lo_MovimientoDetalle.IdProducto In
					(Select Gen_ProductoDet.IdProducto FROM Gen_ProductoDet Where Gen_ProductoDet.IdProductoDet=Gen_Producto.IdProducto)
			and Lo_Movimiento.Anulado=0
			AND Lo_Movimiento.IdAlmacenDestino>0
			AND Lo_Movimiento.MovimientoFecha<var_FechaIni
			),0)) ) -
		((SELECT IFNULL((SELECT
				SUM(Lo_MovimientoDetalle.Cantidad)
			FROM
				Lo_Movimiento
				INNER JOIN Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
				INNER JOIN Lo_MovimientoTipo On Lo_MovimientoTipo.IdMovimientoTipo=Lo_Movimiento.IdMovimientoTipo
			WHERE
				Lo_MovimientoDetalle.IdProducto=Gen_Producto.IdProducto
				AND Lo_Movimiento.IdAlmacenOrigen>0
				AND Lo_Movimiento.MovimientoFecha<var_FechaIni
				AND Lo_Movimiento.Anulado=false),0)) +
			(SELECT IFNULL((Select Sum(Lo_MovimientoDetalle.Cantidad)*
				(SELECT Gen_ProductoDet.Cantidad
					FROM Gen_ProductoDet
					WHERE Gen_ProductoDet.IdProducto=Lo_MovimientoDetalle.IdProducto and Gen_ProductoDet.IdProductoDet=Gen_Producto.IdProducto)
			From Lo_Movimiento
			INNER JOIN Lo_MovimientoDetalle ON Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
			where Lo_MovimientoDetalle.IdProducto In
					(Select Gen_ProductoDet.IdProducto FROM Gen_ProductoDet Where Gen_ProductoDet.IdProductoDet=Gen_Producto.IdProducto)
			and Lo_Movimiento.Anulado=0
			AND Lo_Movimiento.IdAlmacenOrigen>0
			AND Lo_Movimiento.MovimientoFecha<var_FechaIni),0)) +
			(SELECT IFNULL((SELECT
			Sum(Ve_DocVentaDet.Cantidad)
			FROM Ve_DocVentaDet
			INNER JOIN Ve_DocVenta On Ve_DocVenta.idDocVenta=Ve_DocVentaDet.IdDocVenta
			WHERE
			Ve_DocVenta.FechaDoc<var_FechaIni
			AND Ve_DocVentaDet.IdProducto=Gen_Producto.IdProducto),0)) +
					(SELECT IFNULL((Select
				Sum(Ve_DocVentaDet.Cantidad*
				(Select Cantidad From Gen_ProductoDet Where Gen_ProductoDet.IdProductoDet=Gen_Producto.IdProducto and Gen_ProductoDet.IdProducto=Ve_DocVentaDet.IdProducto))
			From
				Ve_DocVentaDet
			INNER JOIN Ve_DocVenta On Ve_DocVenta.idDocVenta=Ve_DocVentaDet.IdDocVenta
			WHERE Ve_DocVenta.FechaDoc<var_FechaIni and
				Ve_DocVentaDet.IdProducto in
				(Select IdProducto From Gen_ProductoDet Where Gen_ProductoDet.IdProductoDet=Gen_Producto.IdProducto)),0)))
			ELSE '-' END as STOCK
	FROM
		Gen_Producto
		INNER JOIN Gen_ProductoCategoria On Gen_ProductoCategoria.IdProductoCategoria=Gen_Producto.IdProductoCategoria
	WHERE
		Gen_Producto.Anulado=false
		AND Gen_Producto.ControlaStock=TRUE
		AND Gen_Producto.Producto=var_Producto
	ORDER BY
		Gen_ProductoCategoria.ProductoCategoria,Gen_Producto.Producto);
	INSERT INTO tblKardex (d2,d5) VALUES ('SALDO ANTERIOR',@StockAnt);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbLo_StockDocVentaCaja` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbLo_StockDocVentaCaja`(IN `var_Almacen` VARCHAR(255), IN `Var_Producto` VARCHAR(255), IN `var_Fecha` DATE, OUT `var_Cantidad` FLOAT)
BEGIN
		Set @IdProducto=(SELECT IdProducto From Gen_Producto Where Gen_Producto.Producto=Var_Producto);
		Set @Cantidad=(Select IFNULL((Select
											Sum(Ve_DocVentaDet.Cantidad*IFNULL((SELECT Gen_ProductoDet.Cantidad FROM Gen_ProductoDet Where Gen_ProductoDet.IdProductoDet= @IdProducto and Gen_ProductoDet.IdProducto=Ve_DocVentaDet.IdProducto ),0))
									FROM Ve_DocVenta
									INNER JOIN Ve_DocVentaDet On Ve_DocVenta.idDocVenta=Ve_DocVentaDet.IdDocVenta
									WHERE
										Ve_DocVenta.IdAlmacen in (SELECT IdAlmacen From Lo_Almacen Where Almacen LIKE CONCAT('%',
                           var_Almacen,
                           '%'))
										and Ve_DocVentaDet.IdProducto in
														(SELECT Gen_ProductoDet.IdProducto FROM Gen_ProductoDet Where Gen_ProductoDet.IdProductoDet= @IdProducto )
										and Ve_DocVenta.Anulado=0
										and Ve_DocVenta.FechaDoc<var_Fecha),0));
		SET var_Cantidad=@Cantidad;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbLo_StockDocVentaUnd` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbLo_StockDocVentaUnd`(IN `var_Almacen` VARCHAR(255), IN `Var_Producto` VARCHAR(255), IN `var_Fecha` DATE, OUT `var_Cantidad` FLOAT)
BEGIN
		Set @IdProducto=(SELECT IdProducto From Gen_Producto Where Gen_Producto.Producto=Var_Producto);
		Set @Cantidad=(Select IFNULL((Select
											Sum(Ve_DocVentaDet.Cantidad)
									FROM Ve_DocVenta
									INNER JOIN Ve_DocVentaDet On Ve_DocVenta.idDocVenta=Ve_DocVentaDet.IdDocVenta
									WHERE
										Ve_DocVenta.IdAlmacen in (SELECT IdAlmacen From Lo_Almacen Where Almacen LIKE CONCAT('%',
                           var_Almacen,
                           '%'))
										and Ve_DocVentaDet.IdProducto =@IdProducto
										and Ve_DocVenta.Anulado=0
										and Ve_DocVenta.FechaDoc<var_Fecha),0));
		SET var_Cantidad=@Cantidad;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbLo_StockIngresoCaja` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbLo_StockIngresoCaja`(IN `var_Almacen` VARCHAR(255), IN `Var_Producto` VARCHAR(255), IN `var_Fecha` DATE, OUT `var_Cantidad` FLOAT)
BEGIN
		Set @IdProducto=(SELECT IdProducto From Gen_Producto Where Gen_Producto.Producto=Var_Producto);
		Set @Cantidad=(Select IFNULL((Select
											Sum(Lo_MovimientoDetalle.Cantidad*IFNULL((SELECT Gen_ProductoDet.Cantidad FROM Gen_ProductoDet Where Gen_ProductoDet.IdProductoDet= @IdProducto and Gen_ProductoDet.IdProducto=Lo_MovimientoDetalle.IdProducto ),0))
									FROM Lo_Movimiento
									INNER JOIN Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo = Lo_MovimientoTipo.IdMovimientoTipo
								
									WHERE
Lo_MovimientoTipo.VaRegCompra = 1 AND 
										Lo_Movimiento.IdAlmacenDestino in (SELECT IdAlmacen From Lo_Almacen Where Almacen LIKE CONCAT('%',
                           var_Almacen,
                           '%'))
										and Lo_MovimientoDetalle.IdProducto in
														(SELECT Gen_ProductoDet.IdProducto FROM Gen_ProductoDet Where Gen_ProductoDet.IdProductoDet= @IdProducto )
										and Lo_Movimiento.Anulado=0
										and Lo_Movimiento.MovimientoFecha<var_Fecha),0));
		SET var_Cantidad=@Cantidad;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbLo_StockIngresosUnd` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbLo_StockIngresosUnd`(IN `var_Almacen` VARCHAR(255), IN `Var_Producto` VARCHAR(255), IN `var_Fecha` DATE)
BEGIN
		set @Fecha=DATE_ADD(var_Fecha, INTERVAL 1 DAY);
		Set @IdProducto=(SELECT IdProducto From Gen_Producto Where Gen_Producto.Producto=Var_Producto);
		Set @Cantidad=(Select
											Sum(Lo_MovimientoDetalle.Cantidad)
									FROM Lo_Movimiento
									INNER JOIN Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
									WHERE
										Lo_Movimiento.IdAlmacenDestino in (SELECT IdAlmacen From Lo_Almacen Where Almacen LIKE CONCAT('%',
                           var_Almacen,
                           '%'))
										and Lo_MovimientoDetalle.IdProducto=@IdProducto
										and Lo_Movimiento.Anulado=0
										and Lo_Movimiento.MovimientoFecha<@Fecha);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbLo_StockIngresoUnd` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbLo_StockIngresoUnd`(IN `var_Almacen` VARCHAR(255), IN `Var_Producto` VARCHAR(255), IN `var_Fecha` DATE, OUT `var_Cantidad` FLOAT)
BEGIN
		Set @IdProducto=(SELECT IdProducto From Gen_Producto Where Gen_Producto.Producto=Var_Producto);
		Set @Cantidad=(Select IFNULL((Select
											Sum(Lo_MovimientoDetalle.Cantidad)
									FROM Lo_Movimiento
									INNER JOIN Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
									INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo = Lo_MovimientoTipo.IdMovimientoTipo
									WHERE
										Lo_MovimientoTipo.VaRegCompra = 1 AND 
										Lo_Movimiento.IdAlmacenDestino in (SELECT IdAlmacen From Lo_Almacen Where Almacen LIKE CONCAT('%',
                           var_Almacen,
                           '%'))
										and Lo_MovimientoDetalle.IdProducto=@IdProducto
										and Lo_Movimiento.Anulado=0
										and Lo_Movimiento.MovimientoFecha<var_Fecha),0));
		SET var_Cantidad=@Cantidad;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbLo_StockSalidaCaja` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbLo_StockSalidaCaja`(IN `var_Almacen` VARCHAR(255), IN `Var_Producto` VARCHAR(255), IN `var_Fecha` DATE, OUT `var_Cantidad` FLOAT)
BEGIN
		Set @IdProducto=(SELECT IdProducto From Gen_Producto Where Gen_Producto.Producto=Var_Producto);
		Set @Cantidad=(Select IFNULL((Select
											Sum(Lo_MovimientoDetalle.Cantidad*IFNULL((SELECT Gen_ProductoDet.Cantidad FROM Gen_ProductoDet Where Gen_ProductoDet.IdProductoDet= @IdProducto and Gen_ProductoDet.IdProducto=Lo_MovimientoDetalle.IdProducto ),0))
									FROM Lo_Movimiento
									INNER JOIN Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
				INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo = Lo_MovimientoTipo.IdMovimientoTipo
						
									WHERE
		Lo_MovimientoTipo.VaRegCompra = 1 AND 
										Lo_Movimiento.IdAlmacenOrigen in (SELECT IdAlmacen From Lo_Almacen Where Almacen LIKE CONCAT('%',
                           var_Almacen,
                           '%'))
										and Lo_MovimientoDetalle.IdProducto in
														(SELECT Gen_ProductoDet.IdProducto FROM Gen_ProductoDet Where Gen_ProductoDet.IdProductoDet= @IdProducto )
										and Lo_Movimiento.Anulado=0
										and Lo_Movimiento.MovimientoFecha<var_Fecha),0));
		SET var_Cantidad=@Cantidad;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbLo_StockSalidaUnd` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbLo_StockSalidaUnd`(IN `var_Almacen` VARCHAR(255), IN `Var_Producto` VARCHAR(255), IN `var_Fecha` DATE, OUT `var_Cantidad` FLOAT)
BEGIN
		Set @IdProducto=(SELECT IdProducto From Gen_Producto Where Gen_Producto.Producto=Var_Producto);
		Set @Cantidad=(Select IFNULL((Select
											Sum(Lo_MovimientoDetalle.Cantidad)
									FROM Lo_Movimiento
									INNER JOIN Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
		INNER JOIN Lo_MovimientoTipo ON Lo_Movimiento.IdMovimientoTipo = Lo_MovimientoTipo.IdMovimientoTipo
									
									WHERE
										Lo_MovimientoTipo.VaRegCompra = 1 AND 
										Lo_Movimiento.IdAlmacenOrigen in (SELECT IdAlmacen From Lo_Almacen Where Almacen LIKE CONCAT('%',
                           var_Almacen,
                           '%'))
										and Lo_MovimientoDetalle.IdProducto=@IdProducto
										and Lo_Movimiento.Anulado=0
										and Lo_Movimiento.MovimientoFecha<var_Fecha),0));
		SET var_Cantidad=@Cantidad;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbLo_StockValoriado` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbLo_StockValoriado`(IN `Var_Producto` VARCHAR(255), IN `Var_StockInical` FLOAT, IN `Var_PrecioInicial` FLOAT, IN `var_anno` INT, IN `var_Tipo` INT)
BEGIN
	DECLARE v_Fecha DATE;
	DECLARE V1_Precio Float;
	DECLARE V1_Saldo FLOAT;
	DECLARE V1_Total Float;
		DECLARE done INT DEFAULT FALSE;
		declare cur1 cursor for
						SELECT t.FechaDoc
						FROM
						(SELECT case when var_Tipo=0 THEN date(Lo_Movimiento.MovimientoFecha) ELSE date(Lo_Movimiento.FechaStock) END as FechaDoc
						FROM Lo_Movimiento
						GROUP BY Lo_Movimiento.MovimientoFecha,Lo_Movimiento.FechaStock
						UNION
						SELECT DAte(Ve_DocVenta.FechaDoc) as FechaDoc
						FROM Ve_DocVenta
						GROUP BY Ve_DocVenta.FechaDoc) t ;
		declare continue handler for not found set done=1;
		open cur1;
		CALL SbLo_KardexValorizadoIniciar(Var_Producto,var_anno,Var_StockInical,Var_PrecioInicial);
		SET V1_Saldo=Round(Var_StockInical,2);
		SET V1_Precio=Round(Var_PrecioInicial,2);
		SET V1_Total=Round(V1_Saldo*V1_Precio,2);
    igmLoop: loop
        fetch cur1 into v_Fecha;
        if done = 1 then leave igmLoop; end if;
				CALL SbLo_KardexValorizadoINGRESOS(Var_Producto,v_Fecha,V1_Saldo,V1_Precio,V1_Saldo,V1_Precio,var_Tipo);
				CALL SbLo_KardexValorizadoINGRESOS_Caja(Var_Producto,v_Fecha,V1_Saldo,V1_Precio,V1_Saldo,V1_Precio,var_Tipo);
				CALL SbLo_KardexValorizadoSalida_UND(Var_Producto,v_Fecha,V1_Saldo,V1_Precio,V1_Saldo,V1_Precio,var_Tipo);
				CALL SbLo_KardexValorizadoSalida_CAJA(Var_Producto,v_Fecha,V1_Saldo,V1_Precio,V1_Saldo,V1_Precio,var_Tipo);
				CALL SbLo_KardexValorizadoSalidaVenta_UND(Var_Producto,v_Fecha,V1_Saldo,V1_Precio,V1_Saldo,V1_Precio);
				CALL SbLo_KardexValorizadoSalidaVenta_CAJA(Var_Producto,v_Fecha,V1_Saldo,V1_Precio,V1_Saldo,V1_Precio);
    end loop igmLoop;
    close cur1;
	Select * From tblKardexvalor;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbLo_Stock_Cursor` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbLo_Stock_Cursor`(IN `var_Almacen` VARCHAR(255), IN `Var_Producto` VARCHAR(255))
BEGIN
	DECLARE var_Fecha DATE;
	DECLARE v_IdProducto Int;
	DECLARE v_Marca varchar(255);
	DECLARE v_Categoria varchar(255);
	DECLARE v_FormaFarmaceutica varchar(255);
	DECLARE v_cantidad  FLOAT;
	declare v_Producto varchar(100);
	DECLARE done INT DEFAULT FALSE;
	declare cur1 cursor for
		Select IdProducto,Gen_ProductoMarca.ProductoMarca,Gen_ProductoCategoria.ProductoCategoria,Gen_ProductoFormaFarmaceutica.ProductoFormaFarmaceutica, Producto,0 as Stock
		from Gen_Producto
		Inner JOIN Gen_ProductoCategoria On Gen_Producto.IdProductoCategoria=Gen_ProductoCategoria.IdProductoCategoria
		Inner Join Gen_ProductoMarca On Gen_ProductoMarca.IdProductoMarca=Gen_Producto.IdProductoMarca
		Inner Join Gen_ProductoFormaFarmaceutica On Gen_ProductoFormaFarmaceutica.IdProductoFormaFarmaceutica=Gen_Producto.IdProductoFormaFarmaceutica
		where Producto LIKE CONCAT('%',Var_Producto,'%') and Gen_Producto.ControlaStock=1;
	declare continue handler for not found set done=1;
	SET var_Fecha=(Select CURDATE());
	SET @Fecha=DATE_ADD(var_Fecha, INTERVAL 1 DAY);
	set done = 0;
	delete from prodstock;
    open cur1;
igmLoop: loop
        fetch cur1 into v_IdProducto,v_Marca,v_Categoria,v_FormaFarmaceutica,v_Producto,v_cantidad;
        if done = 1 then leave igmLoop; end if;
		CALL SbLo_StockIngresoUnd (var_Almacen,v_Producto,@Fecha,@IngresoUnd);
		CALL SbLo_StockSalidaUnd (var_Almacen,v_Producto,@Fecha,@SalidaUnd);
		CALL SbLo_StockIngresoCaja (var_Almacen,v_Producto,@Fecha,@IngresoCaja);
		CALL SbLo_StockSalidaCaja (var_Almacen,v_Producto,@Fecha,@SalidaCaja);
		CALL SbLo_StockDocVentaUnd (var_Almacen,v_Producto,@Fecha,@DocVentaUnd);
		CALL SbLo_StockDocVentaCaja (var_Almacen,v_Producto,@Fecha,@DocVentaCaja);
		Set @Ingresos=@IngresoUnd+@IngresoCaja;
	Set @Salidas=@SalidaCaja+@SalidaUnd+@DocVentaUnd+@DocVentaCaja;
	Set @Stock=@Ingresos-@Salidas;
	Insert into prodstock (IdProducto,ProductoMarca,ProductoCategoria,FormaFarmaceutica,Producto,Stock) values (v_IdProducto,v_Marca,v_Categoria,v_FormaFarmaceutica, v_Producto,@Stock);
    end loop igmLoop;
    close cur1;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbProductoModificar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbProductoModificar`(IN `var_IdProducto` INT, IN `var_ProductoMarca` VARCHAR(255), IN `var_ProductoFormaFarmaceutica` VARCHAR(255), IN `var_ProductoMedicion` VARCHAR(255), IN `var_ProductoCategoria` VARCHAR(255), IN `var_Producto` VARCHAR(255), IN `var_ProductoDesc` TEXT, IN `var_ProductoDescCorto` VARCHAR(255), IN `var_CodigoBarra` VARCHAR(255), IN `var_Codigo` VARCHAR(255), IN `var_Dosis` VARCHAR(255), IN `var_PrecioContado` FLOAT, IN `var_PrecioXMayor` FLOAT, IN `var_StockXMayor` FLOAT, IN `var_ControlStock` BIT(1), IN `var_StockMinimo` FLOAT, IN `var_Usuario` VARCHAR(255), IN `var_PrecioCosto` FLOAT, IN `var_VentaEstrategica` BIT, IN `var_PrecioUtilidad` FLOAT, IN `var_Bloque` VARCHAR(255))
BEGIN
UPDATE Gen_Producto
SET
		IdProductoMarca=(Select IdProductoMarca From Gen_ProductoMarca Where ProductoMarca=var_ProductoMarca LIMIT 1),
		IdProductoFormaFarmaceutica=(Select Gen_ProductoFormaFarmaceutica.IdProductoFormaFarmaceutica From Gen_ProductoFormaFarmaceutica Where ProductoFormaFarmaceutica=var_ProductoFormaFarmaceutica LIMIT 1),
		IdProductoMedicion=(Select IdProductoMedicion From Gen_ProductoMedicion Where ProductoMedicion=var_ProductoMedicion LIMIT 1),
		IdProductoCategoria=(Select Gen_ProductoCategoria.IdProductoCategoria From Gen_ProductoCategoria Where Gen_ProductoCategoria.ProductoCategoria=var_ProductoCategoria LIMIT 1),
		Producto=var_Producto,
		ProductoDesc=var_ProductoDesc,
		ProductoDescCorto=var_ProductoDescCorto,
		Dosis=var_Dosis,
		Codigo=var_Codigo,
		CodigoBarra=var_CodigoBarra,
    PrecioContado=var_PrecioContado,
		PrecioPorMayor=var_PrecioXMayor,
		StockPorMayor=var_StockXMayor,
		ControlaStock = var_ControlStock,
		StockMinimo = var_StockMinimo,
		PrecioCosto = var_PrecioCosto,
		VentaEstrategica = var_VentaEstrategica,
		PorcentajeUtilidad = var_PrecioUtilidad,
		IdBloque = (Select Gen_ProductoBloque.IdBloque from Gen_ProductoBloque where Gen_ProductoBloque.Bloque = var_Bloque),
		Anulado=0,
		FechaMod=Now(),
		UsuarioMod=var_Usuario
WHERE
		Gen_Producto.IdProducto=var_IdProducto;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbSeg_ListarUsuarioPerfil` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbSeg_ListarUsuarioPerfil`()
BEGIN
SELECT
Seg_Usuario.Usuario,
Seg_UsuarioPerfil.UsuarioPerfil
FROM
Seg_Usuario
INNER JOIN Seg_UsuarioPerfil ON Seg_Usuario.IdUsuarioPerfil = Seg_UsuarioPerfil.IdUsuarioPerfil;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbVe_BuscarProductoXCompuesto` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbVe_BuscarProductoXCompuesto`(IN `var_CriterioBuscar` VARCHAR(255))
BEGIN
SELECT
Gen_Producto.IdProducto,
Gen_ProductoMarca.ProductoMarca,
Gen_ProductoFormaFarmaceutica.ProductoFormaFarmaceutica,
Gen_ProductoMedicion.ProductoMedicion,
Gen_ProductoCategoria.ProductoCategoria,
Gen_Producto.Producto,
Gen_Producto.ProductoDesc,
Gen_Producto.ProductoDescCorto,
Gen_Producto.CodigoBarra,
Gen_Producto.Codigo,
Gen_Producto.Dosis,
Gen_Producto.PrecioContado,
Gen_Producto.PrecioPorMayor,
Gen_Producto.StockPorMayor
FROM
Gen_ProductoCompuesto
INNER JOIN Gen_ProductoCompuestoDet ON Gen_ProductoCompuestoDet.Gen_ProductoCompuesto_IdProductoCompuesto = Gen_ProductoCompuesto.IdProductoCompuesto
INNER JOIN Gen_Producto ON Gen_ProductoCompuestoDet.Gen_Producto_IdProducto = Gen_Producto.IdProducto
INNER JOIN Gen_ProductoMarca ON Gen_Producto.IdProductoMarca = Gen_ProductoMarca.IdProductoMarca
INNER JOIN Gen_ProductoFormaFarmaceutica ON Gen_Producto.IdProductoFormaFarmaceutica = Gen_ProductoFormaFarmaceutica.IdProductoFormaFarmaceutica
INNER JOIN Gen_ProductoMedicion ON Gen_Producto.IdProductoMedicion = Gen_ProductoMedicion.IdProductoMedicion
INNER JOIN Gen_ProductoCategoria ON Gen_Producto.IdProductoCategoria = Gen_ProductoCategoria.IdProductoCategoria
WHERE Gen_ProductoCompuesto.ProductoCompuesto like CONCAT('%', var_CriterioBuscar, '%');
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbVe_BuscarTratamiento` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbVe_BuscarTratamiento`(IN `var_Diagnostico` VARCHAR(255), IN `var_Edad` INT)
BEGIN
SELECT DISTINCT
Ve_ExpertoTratamiento.NroDias,
Ve_ExpertoTratamiento.TomasXDia,
Ve_ExpertoTratamiento.DosisXPeso,
Ve_ExpertoTratamiento.Concentracion,
Gen_ProductoCompuesto.ProductoCompuesto,
Ve_ExpertoTratamiento.IdCompuesto as IdProductoCompuesto,
Ve_ExpertoTratamiento.UnidadDosisXPeso as UnidadDosisXPeso,
Ve_ExpertoTratamiento.TomasXDia,
Ve_ExpertoTratamiento.NroDias
FROM
Ve_ExpertoTratamiento
INNER JOIN Gen_ProductoCompuesto ON Gen_ProductoCompuesto.IdProductoCompuesto = Ve_ExpertoTratamiento.IdCompuesto
INNER JOIN Gen_ProductoCompuestoDet ON Gen_ProductoCompuestoDet.Gen_ProductoCompuesto_IdProductoCompuesto = Gen_ProductoCompuesto.IdProductoCompuesto
INNER JOIN Ve_ExpertoDiagnostico ON Ve_ExpertoDiagnostico.IdDiagnostico = Ve_ExpertoTratamiento.IdDiagnostico
WHERE
Ve_ExpertoDiagnostico.Diagnostico = var_Diagnostico AND Ve_ExpertoDiagnostico.Edad >= var_Edad;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbVe_DocVentaAnular` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbVe_DocVentaAnular`(IN `var_IdDocVenta` INT)
BEGIN
    
    UPDATE Ve_DocVenta 
    SET Anulado = 1 
    WHERE idDocVenta = var_IdDocVenta;

    UPDATE Gen_Producto
    SET 
        TotalVentas = COALESCE((
            SELECT SUM(
                CASE 
                    WHEN Ve_DocVenta.Anulado = 1 THEN 0
                    WHEN Ve_DocVentaTipoDoc.CodSunat = '07' THEN -Ve_DocVentaDet.Cantidad
                    ELSE Ve_DocVentaDet.Cantidad 
                END
            ) 
            FROM Ve_DocVentaDet
            JOIN Ve_DocVenta ON Ve_DocVentaDet.IdDocVenta = Ve_DocVenta.IdDocVenta
            JOIN Ve_DocVentaTipoDoc ON Ve_DocVentaTipoDoc.IdTipoDoc = Ve_DocVenta.IdTipoDoc
            WHERE Ve_DocVentaDet.IdProducto = Gen_Producto.IdProducto
            GROUP BY Ve_DocVentaDet.IdProducto
        ), 0)
    WHERE IdProducto IN (
        SELECT DISTINCT IdProducto 
        FROM Ve_DocVentaDet 
        WHERE IdDocVenta = var_IdDocVenta
    );

END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbVe_DocVentaEliminar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbVe_DocVentaEliminar`(IN `var_IdDocVenta` INT)
BEGIN
		DELETE FROM Ve_DocVentaDet WHERE Ve_DocVentaDet.IdDocVenta=var_IdDocVenta;
		DELETE FROM Ve_DocVenta WHERE Ve_DocVenta.idDocVenta=var_IdDocVenta;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbVe_ExpertoDiagnosticoBuscar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbVe_ExpertoDiagnosticoBuscar`(IN `var_CriterioEdadBuscar` INT, IN `var_CriterioBuscar` VARCHAR(255))
BEGIN
SELECT
Ve_ExpertoDiagnostico.IdDiagnostico,
Ve_ExpertoDiagnostico.Diagnostico,
Ve_ExpertoDiagnostico.Problema,
Ve_ExpertoDiagnostico.Edad,
Ve_ExpertoDiagnostico.FechaReg,
Ve_ExpertoDiagnostico.UsuarioReg,
Ve_ExpertoDiagnostico.FechaMod,
Ve_ExpertoDiagnostico.UsuarioMod
FROM
Ve_ExpertoDiagnostico
WHERE Ve_ExpertoDiagnostico.Diagnostico like CONCAT('%', var_CriterioBuscar, '%') and Ve_ExpertoDiagnostico.Edad<var_CriterioEdadBuscar
Order by Ve_ExpertoDiagnostico.Edad LIMIT 1;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbVe_ExpertoDiagnosticoGuardar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbVe_ExpertoDiagnosticoGuardar`(IN `var_Diagnostico` VARCHAR(255), IN `var_Problema` TEXT, IN `var_Edad` INT, IN `var_Obs` TEXT, IN `var_UsuarioReg` VARCHAR(255))
BEGIN
SET @Hash2=(Select UNIX_TIMESTAMP());
INSERT INTO Ve_ExpertoDiagnostico(Ve_ExpertoDiagnostico.Diagnostico, Ve_ExpertoDiagnostico.Problema, Ve_ExpertoDiagnostico.Edad, Ve_ExpertoDiagnostico.Observacion, Ve_ExpertoDiagnostico.FechaReg, Ve_ExpertoDiagnostico.UsuarioReg, Ve_ExpertoDiagnostico.Hash)
	VALUES (
		var_Diagnostico,
		var_Problema,
		var_Edad,
        var_Obs,
		NOW(),
		var_UsuarioReg,
		@Hash2
);
SELECT Ve_ExpertoDiagnostico.IdDiagnostico FROM Ve_ExpertoDiagnostico WHERE Ve_ExpertoDiagnostico.Hash = @Hash2;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbVe_ExpertoDiagnosticoModificar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbVe_ExpertoDiagnosticoModificar`(IN `var_IdDiagnostico` INT, IN `var_Diagnostico` VARCHAR(255), IN `var_Problema` TEXT, IN `var_Edad` INT, IN `var_Obs` TEXT, IN `var_Usuario` VARCHAR(255))
BEGIN
UPDATE Ve_ExpertoDiagnostico SET
Ve_ExpertoDiagnostico.Diagnostico = var_Diagnostico,
Ve_ExpertoDiagnostico.Problema = var_Problema,
Ve_ExpertoDiagnostico.Edad = var_Edad,
Ve_ExpertoDiagnostico.Observacion = var_Obs,
Ve_ExpertoDiagnostico.UsuarioMod = var_Usuario,
Ve_ExpertoDiagnostico.FechaMod = NOW()
WHERE Ve_ExpertoDiagnostico.IdDiagnostico = var_IdDiagnostico;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbVe_ExpertoDiagnosticoSintomaDet` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbVe_ExpertoDiagnosticoSintomaDet`(IN `var_Diagnostico` INT, IN `var_Sintoma` INT, IN `var_UsuarioReg` VARCHAR(255))
BEGIN
INSERT INTO Ve_ExpertoDiagnosticoSintomaDet(Ve_ExpertoDiagnosticoSintomaDet.IdDiagnostico, Ve_ExpertoDiagnosticoSintomaDet.IdSintoma, Ve_ExpertoDiagnosticoSintomaDet.Fechareg, Ve_ExpertoDiagnosticoSintomaDet.UsuarioReg)
	VALUES (
	var_Diagnostico,
	var_Sintoma,
	NOW(),
	var_UsuarioReg);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbVe_ExpertoDiagnosticoXSintomaBuscar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbVe_ExpertoDiagnosticoXSintomaBuscar`(IN `var_EdadBuscar` INT, IN `var_CriterioSintomaBuscar` TEXT, IN `var_Numero` INT)
BEGIN
set @Ssql:=CONCAT('SELECT
Ve_ExpertoDiagnostico.IdDiagnostico,
Ve_ExpertoDiagnostico.Diagnostico,
Ve_ExpertoDiagnostico.Problema,
Ve_ExpertoDiagnostico.Edad,
Ve_ExpertoDiagnostico.Observacion
FROM
Ve_ExpertoDiagnostico
INNER JOIN Ve_ExpertoDiagnosticoSintomaDet ON Ve_ExpertoDiagnostico.IdDiagnostico = Ve_ExpertoDiagnosticoSintomaDet.IdDiagnostico
INNER JOIN Ve_ExpertoSintoma ON Ve_ExpertoDiagnosticoSintomaDet.IdSintoma=Ve_ExpertoSintoma.Idsintoma
 WHERE Ve_ExpertoDiagnostico.Edad >=',var_EdadBuscar,' and Ve_ExpertoSintoma.Sintoma in (',var_CriterioSintomaBuscar,')
GROUP BY
Ve_ExpertoDiagnostico.IdDiagnostico,
Ve_ExpertoDiagnostico.Diagnostico,
Ve_ExpertoDiagnostico.Problema,
Ve_ExpertoDiagnostico.Edad,
Ve_ExpertoDiagnostico.Observacion
HAVING Count(Ve_ExpertoDiagnostico.IdDiagnostico)=',var_Numero ,'
ORder by Ve_ExpertoDiagnostico.Edad;');
PREPARE myquery FROM @Ssql;
EXECUTE myquery;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbVe_ExpertoSintomaBuscar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbVe_ExpertoSintomaBuscar`(IN `var_CriterioBuscar` VARCHAR(255))
BEGIN
set @criterio:=CONCAT("'%", var_CriterioBuscar, "%'");
set @Ssql:=CONCAT('SELECT
Ve_ExpertoSintoma.IdSintoma,
Ve_ExpertoSintoma.Sintoma
FROM
Ve_ExpertoSintoma
WHERE (Ve_ExpertoSintoma.Sintoma like', @criterio,')  ;');
PREPARE myquery FROM @Ssql;
EXECUTE myquery;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbVe_ExpertoTratamientoActualizarD` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbVe_ExpertoTratamientoActualizarD`(IN `var_Tratamiento` INT, IN `var_Diagnostico` INT)
BEGIN
UPDATE Ve_ExpertoTratamiento SET Ve_ExpertoTratamiento.IdDiagnostico = var_Diagnostico
	WHERE Ve_ExpertoTratamiento.IdTratamiento = var_Tratamiento;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbVe_GuardarDocVenta` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbVe_GuardarDocVenta`(IN `var_PuntoVenta` VARCHAR(255), IN `var_TipoDocVenta` VARCHAR(255), IN `var_Cliente` VARCHAR(255), IN `var_Almacen` VARCHAR(255), IN `var_FechaDoc` DATETIME, IN `var_Usuario` VARCHAR(255), IN `var_Credito` BIT, IN `var_FechaCredito` DATETIME, IN `var_Serie` VARCHAR(45))
BEGIN
SET @Hash2=(Select UNIX_TIMESTAMP());
INSERT INTO Ve_DocVenta (IdDocVentaPuntoVenta,IdCliente,IdTipoDoc,IdAlmacen,Serie,Numero,FechaDoc,Anulado,FechaReg,UsuarioReg,Hash, EsCredito, FechaCredito)
VALUES (
				(Select IdDocVentaPuntoVenta From Ve_DocVentaPuntoVenta Where Ve_DocVentaPuntoVenta.PuntoVenta=var_PuntoVenta),
				(Select IdCliente From Ve_DocVentaCliente Where Ve_DocVentaCliente.Cliente=var_Cliente),
				(Select IdTipoDoc From Ve_DocVentaTipoDoc Where Ve_DocVentaTipoDoc.TipoDoc=var_TipoDocVenta),
				(Select IdAlmacen From Lo_Almacen Where Lo_Almacen.Almacen=var_Almacen),
				var_Serie,
				(Select Ifnull((Select VE.Numero From Ve_DocVenta VE WHERE VE.IdDocVentaPuntoVenta=(Select IdDocVentaPuntoVenta From Ve_DocVentaPuntoVenta Where Ve_DocVentaPuntoVenta.PuntoVenta=var_PuntoVenta) and  VE.Serie=var_Serie and VE.IdTipoDoc=(Select IdTipoDoc From Ve_DocVentaTipoDoc Where Ve_DocVentaTipoDoc.TipoDoc=var_TipoDocVenta) ORDER BY VE.Numero DESC limit 1),0))+1,
				var_FechaDoc,0,now(),var_Usuario,@Hash2, var_Credito, var_FechaCredito );
Select IdDocVenta From Ve_DocVenta Where Hash=@Hash2;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbVe_GuardarDocVentaDet` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbVe_GuardarDocVentaDet`(IN `var_IdDocVenta` INT, IN `var_IdProducto` INT, IN `var_Cant` FLOAT, IN `var_Precio` FLOAT)
BEGIN
INSERT INTO Ve_DocVentaDet (IdDocVenta,IdProducto,Cantidad,Precio)
VALUES
				(var_IdDocVenta,
				var_IdProducto,
				var_Cant,
				var_Precio);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbVe_GuardarMetodoPagoDet` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbVe_GuardarMetodoPagoDet`(IN `var_IdDocVenta` INTEGER, IN `var_MetodoPago` VARCHAR(255), IN `var_Importe` FLOAT, IN `var_NroTarjeta` VARCHAR(255))
BEGIN
INSERT INTO Ve_DocVentaMetodoPagoDet (IdDocVenta,IdMetodoPago,Importe,NroTarjeta)
VALUES (
				var_IdDocVenta,
				(Select IdMetodoPago From Ve_DocVentaMetodoPago Where MetodoPago=var_MetodoPago),
				var_Importe,
				var_NroTarjeta);
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbVe_GuardarTratamiento` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbVe_GuardarTratamiento`(IN `var_IdTratamiento` INT, IN `var_Diagnostico` VARCHAR(255), IN `var_Compuesto` VARCHAR(255), IN `var_Observacion` VARCHAR(255), IN `var_TomaXDia` INT, IN `var_NroDia` INT, IN `var_DiasXPeso` FLOAT, IN `var_CantSol` FLOAT, IN `var_Usuario` VARCHAR(255), IN `var_UnidadXPeso` VARCHAR(255))
BEGIN
SET @Hash2=(Select UNIX_TIMESTAMP());
IF var_IdTratamiento=0 THEN
	INSERT INTO Ve_ExpertoTratamiento (IdDiagnostico,IdCompuesto,Observacion,TomasXDia,NroDias,FechaReg, DosisXPeso, UnidadDosisXPeso, Concentracion,UsuarioReg,Hash)
	VALUES (
				0,
				(Select IdProductoCompuesto From Gen_ProductoCompuesto Where Gen_ProductoCompuesto.ProductoCompuesto=var_Compuesto),
				var_Observacion,
				var_TomaXDia,var_NroDia,now(), var_DiasXPeso,var_UnidadXPeso , var_CantSol ,var_Usuario,@Hash2);
ELSE
	UPDATE Ve_ExpertoTratamiento
	Set
			IdCompuesto=(Select Gen_ProductoCompuesto.IdProductoCompuesto From Gen_ProductoCompuesto Where Gen_ProductoCompuesto.ProductoCompuesto=var_Compuesto),
		Observacion=var_Observacion,TomasXDia=var_TomaXDia,NroDias=var_NroDia,FechaMod=NOW(), DosisXPeso=var_DiasXPeso, UnidadDosisXPeso = var_UnidadXPeso, Concentracion= var_CantSol, UsuarioMod=var_Usuario
	WHERE Ve_ExpertoTratamiento.IdTratamiento=var_IdTratamiento;
END IF;
Select Ve_ExpertoTratamiento.IdTratamiento,
CONCAT(Gen_ProductoCompuesto.ProductoCompuesto," NroDias (",CONVERT(Ve_ExpertoTratamiento.NroDias,CHAR),") NroTomas (",CONVERT(Ve_ExpertoTratamiento.TomasXDia,CHAR) , ")") as Tratamiento,
Gen_ProductoCompuesto.ProductoCompuesto,
Ve_ExpertoTratamiento.NroDias,
Ve_ExpertoTratamiento.TomasXDia,
Ve_ExpertoTratamiento.Observacion,
Ve_ExpertoTratamiento.DosisXPeso,
Ve_ExpertoTratamiento.Concentracion,
Ve_ExpertoTratamiento.UnidadDosisXPeso
From Ve_ExpertoTratamiento
Inner Join Gen_ProductoCompuesto On Ve_ExpertoTratamiento.IdCompuesto=Gen_ProductoCompuesto.IdProductoCompuesto
Where Ve_ExpertoTratamiento.Hash=@Hash2;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbVe_ListarCierre` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbVe_ListarCierre`()
BEGIN
SELECT
Ve_DocVenta.idDocVenta,
Ve_DocVenta.FechaDoc,
Ve_DocVentaTipoDoc.TipoDoc,
Ve_DocVenta.Serie,
Ve_DocVenta.Numero,
SUM(Ve_DocVentaDet.Cantidad * Ve_DocVentaDet.Precio) as Total,
Ve_DocVenta.EsCredito,
IFNULL((SELECT SUM(Ve_DocVentaMetodoPagoDet.Importe) FROM Ve_DocVentaMetodoPagoDet WHERE Ve_DocVentaMetodoPagoDet.IdDocVenta = Ve_DocVenta.idDocVenta AND Ve_DocVentaMetodoPagoDet.IdMetodoPago = 1), 0) AS Efectivo,
IFNULL((SELECT SUM(Ve_DocVentaMetodoPagoDet.Importe) FROM Ve_DocVentaMetodoPagoDet WHERE Ve_DocVentaMetodoPagoDet.IdDocVenta = Ve_DocVenta.idDocVenta AND Ve_DocVentaMetodoPagoDet.IdMetodoPago = 2), 0) AS Visa,
IFNULL((SELECT SUM(Ve_DocVentaMetodoPagoDet.Importe) FROM Ve_DocVentaMetodoPagoDet WHERE Ve_DocVentaMetodoPagoDet.IdDocVenta = Ve_DocVenta.idDocVenta AND Ve_DocVentaMetodoPagoDet.IdMetodoPago = 3), 0) AS Mastercard
FROM Ve_DocVenta
INNER JOIN Ve_DocVentaDet ON Ve_DocVentaDet.IdDocVenta = Ve_DocVenta.idDocVenta
INNER JOIN Ve_DocVentaTipoDoc ON Ve_DocVentaTipoDoc.IdTipoDoc = Ve_DocVenta.IdTipoDoc
WHERE Ve_DocVenta.IdCierre IS NULL
GROUP BY Ve_DocVenta.idDocVenta;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbVe_ListarCompuestoXDiagnostico` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbVe_ListarCompuestoXDiagnostico`(IN `var_Diagnostico` INT)
BEGIN
SELECT
Ve_ExpertoSintoma.IdSintoma,
Ve_ExpertoSintoma.Sintoma
FROM
Ve_ExpertoDiagnosticoSintomaDet
INNER JOIN Ve_ExpertoSintoma ON Ve_ExpertoDiagnosticoSintomaDet.IdSintoma = Ve_ExpertoSintoma.IdSintoma
WHERE
Ve_ExpertoDiagnosticoSintomaDet.IdDiagnostico = `var_Diagnostico` ;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbVe_ListarCompuestoXProducto` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbVe_ListarCompuestoXProducto`(IN `var_IdProducto` INT)
BEGIN
SELECT Gen_ProductoCompuesto.IdProductoCompuesto, Gen_ProductoCompuesto.ProductoCompuesto
FROM Gen_ProductoCompuesto
INNER JOIN Gen_ProductoCompuestoDet ON  Gen_ProductoCompuestoDet.Gen_ProductoCompuesto_IdProductoCompuesto = Gen_ProductoCompuesto.IdProductoCompuesto
INNER JOIN Gen_Producto ON Gen_Producto.IdProducto = Gen_ProductoCompuestoDet.Gen_Producto_IdProducto
WHERE Gen_Producto.IdProducto = var_IdProducto;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbVe_ListarPreOrden` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbVe_ListarPreOrden`()
BEGIN
SELECT po.IdPreOrden, cl.DniRuc, cl.Cliente,
    SUM(pod.Cantidad) as Cantidad,
    SUM(PRO.PrecioContado * pod.Cantidad) AS Total,
    po.FechaReg 
FROM Ve_PreOrden AS po 
INNER JOIN Ve_DocVentaCliente AS cl ON po.IdCliente = cl.IdCliente
INNER JOIN Ve_PreOrdenDet AS pod ON po.IdPreOrden = pod.IdPreOrden
INNER JOIN Gen_Producto AS PRO ON pod.IdProducto = PRO.IdProducto
GROUP BY cl.DniRuc
ORDER BY po.FechaReg DESC;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbVe_ListarProductoXCompuesto` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbVe_ListarProductoXCompuesto`(IN `var_IdProductoCompuesto` INT, IN `var_ProductoCompuesto` VARCHAR(255))
BEGIN
IF var_ProductoCompuesto='00000' THEN
SELECT Gen_Producto.IdProducto, Gen_Producto.Producto, Gen_Producto.Dosis
FROM Gen_Producto
INNER JOIN Gen_ProductoCompuestoDet ON  Gen_ProductoCompuestoDet.Gen_Producto_IdProducto = Gen_Producto.IdProducto
INNER JOIN Gen_ProductoCompuesto ON Gen_ProductoCompuesto.IdProductoCompuesto = Gen_ProductoCompuestoDet.Gen_ProductoCompuesto_IdProductoCompuesto
WHERE Gen_ProductoCompuesto.IdProductoCompuesto = var_IdProductoCompuesto;
ELSEIF var_IdProductoCompuesto=00000 THEN
SELECT Gen_Producto.IdProducto, Gen_Producto.Producto, Gen_Producto.PrecioContado, Gen_Producto.PrecioPorMayor, Gen_Producto.Dosis
FROM Gen_Producto
INNER JOIN Gen_ProductoCompuestoDet ON  Gen_ProductoCompuestoDet.Gen_Producto_IdProducto = Gen_Producto.IdProducto
INNER JOIN Gen_ProductoCompuesto ON Gen_ProductoCompuesto.IdProductoCompuesto = Gen_ProductoCompuestoDet.Gen_ProductoCompuesto_IdProductoCompuesto
WHERE Gen_ProductoCompuesto.ProductoCompuesto = var_ProductoCompuesto;
END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbVe_ProductoSeleccionar` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbVe_ProductoSeleccionar`(IN `var_Almacen` VARCHAR(255))
BEGIN
	SELECT
		Gen_Producto.IdProducto as numero,
		UPPER(Gen_Producto.Producto) as PRODUCTO,
		Gen_Producto.PrecioContado,
		Gen_Producto.PrecioPorMayor,
		Gen_Producto.StockPorMayor,
		Gen_ProductoFormaFarmaceutica.ProductoFormaFarmaceutica,
		Gen_ProductoMarca.ProductoMarca,
Gen_Producto.Codigo,
Gen_ProductoMedicion.ProductoMedicion,
Gen_Producto.StockMinimo as STOCK,
Gen_Producto.VentaEstrategica
	FROM
		Gen_Producto
		INNER JOIN Gen_ProductoCategoria On Gen_ProductoCategoria.IdProductoCategoria=Gen_Producto.IdProductoCategoria
		INNER JOIN Gen_ProductoMarca On Gen_ProductoMarca.IdProductoMarca=Gen_Producto.IdProductoMarca
		INNER JOIN Gen_ProductoMedicion On Gen_ProductoMedicion.IdProductoMedicion=Gen_Producto.IdProductoMedicion
		INNER JOIN Gen_ProductoFormaFarmaceutica On Gen_ProductoFormaFarmaceutica.IdProductoFormaFarmaceutica=Gen_Producto.IdProductoFormaFarmaceutica
	WHERE
		Gen_Producto.Anulado=false
	ORDER BY
		Gen_Producto.Producto;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbVe_RegDocVenta` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbVe_RegDocVenta`(IN `var_RegVenta` BIT, IN `var_FechaIni` DATE, IN `var_FechaFin` DATE)
BEGIN
		SELECT
		Ve_DocVenta.idDocVenta,
		Ve_DocVenta.UsuarioReg,
		Ve_DocVenta.FechaDoc,
		Ve_DocVentaTipoDoc.CodSunat,
		Ve_DocVentaTipoDoc.TipoDoc,
		Ve_DocVenta.Anulado,
		Ve_DocVenta.Serie,
		Ve_DocVenta.Numero,
		IFNULL((Select Sum(Round((Ve_DocVentaDet.Precio* Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento,2)) FROM Ve_DocVentaDet Where Ve_DocVentaDet.IdDocVenta=Ve_DocVenta.idDocVenta),0) as SubTotal,
		IF(Ve_DocVentaTipoDoc.TieneIgv = 1, 
        
        ROUND((IFNULL((SELECT SUM(Round((Ve_DocVentaDet.Precio* Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento,2)) FROM Ve_DocVentaDet WHERE Ve_DocVentaDet.IdDocVenta=Ve_DocVenta.idDocVenta),0)) -
        (IFNULL((SELECT SUM(Round((Ve_DocVentaDet.Precio* Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento,2)) FROM Ve_DocVentaDet WHERE Ve_DocVentaDet.IdDocVenta=Ve_DocVenta.idDocVenta),0)) / 1.18, 2)
        
        , 0) as Igv,
		IFNULL((Select Sum(Round((Ve_DocVentaDet.Precio* Ve_DocVentaDet.Cantidad) - Ve_DocVentaDet.Descuento,2)) FROM Ve_DocVentaDet Where Ve_DocVentaDet.IdDocVenta=Ve_DocVenta.idDocVenta),0) as Total
FROM
	Ve_DocVenta
	INNER JOIN Ve_DocVentaTipoDoc On Ve_DocVenta.IdTipoDoc=Ve_DocVentaTipoDoc.IdTipoDoc
WHERE
	Ve_DocVentaTipoDoc.VaRegVenta=var_RegVenta and Ve_DocVenta.Fechadoc BETWEEN CAST(var_FechaIni AS DATETIME) and CONCAT(var_FechaFin,' 23:59:59')
ORDER BY  Ve_DocVenta.Fechadoc DESC;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbVe_ReporteUtilidadBruta` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbVe_ReporteUtilidadBruta`(IN `var_FechaIni` DATE, IN `var_FechaFin` DATE)
BEGIN
  SELECT
	Gen_Producto.IdProducto,
	Gen_Producto.Producto,
	Sum(Ve_DocVentaDet.Cantidad) AS Cantidad,
	Gen_Producto.PrecioCosto,
	Ve_DocVentaDet.Precio as PrecioVenta,
	round(sum(Ve_DocVentaDet.Cantidad) * Ve_DocVentaDet.Precio, 2) as TotalVenta,
	round(Gen_Producto.PrecioCosto * sum(Ve_DocVentaDet.Cantidad), 2) as TotalCosto,
	round(sum(Ve_DocVentaDet.Cantidad) * Ve_DocVentaDet.Precio - Gen_Producto.PrecioCosto * sum(Ve_DocVentaDet.Cantidad), 2) as UtilidadBruta
	FROM Ve_DocVenta
	INNER JOIN Ve_DocVentaDet ON Ve_DocVentaDet.IdDocVenta = Ve_DocVenta.idDocVenta
	INNER JOIN Gen_Producto ON Ve_DocVentaDet.IdProducto = Gen_Producto.IdProducto
	WHERE DATE(Ve_DocVenta.FechaDoc) BETWEEN var_FechaIni and var_FechaFin
	Group by Gen_Producto.IdProducto,
	Gen_Producto.Producto,Ve_DocVentaDet.Precio;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `SbVe_VentasXFechasXUsuario` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `SbVe_VentasXFechasXUsuario`(IN `var_FechaDocIni` DATETIME, IN `var_FechaDocFin` DATETIME, IN `var_Usuario` VARCHAR(255))
BEGIN
SELECT
	Ve_DocVenta.FechaDoc,
	Ve_DocVentaPuntoVenta.PuntoVenta,
	Ve_DocVentaTipoDoc.TipoDoc,
	Ve_DocVentaTipoDoc.CodSunat,
	Ve_DocVenta.Serie,
	Ve_DocVenta.Numero,
	Ve_DocVentaCliente.DniRuc,
	CASE WHEN Ve_DocVenta.Anulado=1 THEN 'ANULADO' ELSE Ve_DocVentaCliente.Cliente END as Cliente,
	Ve_DocVenta.Anulado,
	TRUNCATE((Select Sum(Ve_DocVentaDet.Precio*Ve_DocVentaDet.Cantidad) From Ve_DocVentaDet Where Ve_DocVentaDet.IdDocVenta=Ve_DocVenta.idDocVenta),2) as SubTotal,
	0.00 as Igv,
	TRUNCATE((Select Sum(Ve_DocVentaDet.Precio*Ve_DocVentaDet.Cantidad) From Ve_DocVentaDet Where Ve_DocVentaDet.IdDocVenta=Ve_DocVenta.idDocVenta),2) as Total
	FROM
	`Ve_DocVenta`
	INNER JOIN `Ve_DocVentaCliente` ON Ve_DocVenta.IdCliente = Ve_DocVentaCliente.IdCliente
	INNER JOIN `Ve_DocVentaPuntoVenta` ON Ve_DocVenta.IdDocVentaPuntoVenta = Ve_DocVentaPuntoVenta.IdDocVentaPuntoVenta
	INNER JOIN `Ve_DocVentaTipoDoc` ON Ve_DocVenta.IdTipoDoc = Ve_DocVentaTipoDoc.IdTipoDoc
	WHERE  (Ve_DocVenta.FechaDoc Between var_FechaDocIni + ' 00:00:00' and var_FechaDocFin + ' 23:59:59') and Ve_DocVenta.UsuarioReg=var_Usuario and Ve_DocVenta.IdCierre is NULL
	ORDER BY Ve_DocVenta.FechaDoc,
	Ve_DocVentaPuntoVenta.PuntoVenta,
	Ve_DocVentaTipoDoc.TipoDoc,
	Ve_DocVentaTipoDoc.CodSunat,
	Ve_DocVenta.Serie,
	Ve_DocVenta.Numero;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `Sb_BuscarDeudor` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `Sb_BuscarDeudor`(IN `Deudor` VARCHAR(255), IN `TipoDeudor` INT)
BEGIN
	IF TipoDeudor=1 THEN
		SELECT DISTINCT Ve_DocVenta.idDocVenta as Serie, FechaDoc, FechaCredito, Numero,
		(SELECT SUM(Ve_DocVentaDet.Cantidad*Ve_DocVentaDet.Precio)FROM Ve_DocVentaDet WHERE Ve_DocVentaDet.IdDocVenta= Ve_DocVenta.idDocVenta) as Total,
	(Select IFNULL((Select  sum(Cb_CajaBancoDet.Importe) from Cb_CajaBancoDet WHERE Cb_CajaBancoDet.IdDocDet=Ve_DocVenta.IdDocVenta),0)) as Cancelado
			FROM Ve_DocVenta
		INNER JOIN Ve_DocVentaCliente ON Ve_DocVentaCliente.IdCliente = Ve_DocVenta.IdCliente
		INNER JOIN Ve_DocVentaDet ON Ve_DocVentaDet.IdDocVenta = Ve_DocVenta.idDocVenta
		INNER JOIN Cb_CajaBancoDet ON Cb_CajaBancoDet.IdDocDet = Ve_DocVenta.idDocVenta
		WHERE Ve_DocVentaCliente.Cliente=Deudor AND Ve_DocVenta.FechaCredito IS NOT NULL ORDER BY FechaCredito ASC;
	ELSEIF TipoDeudor=2 THEN
		SELECT Serie, MovimientoFecha as FechaDoc, FechaVenCredito as FechaCredito, Numero FROM Lo_Movimiento
		INNER JOIN Lo_Proveedor ON Lo_Proveedor.IdProveedor = Lo_Movimiento.IdProveedor
		INNER JOIN Cb_CajaBancoDet ON Cb_CajaBancoDet.`Hash`=Lo_Movimiento.`Hash`
		WHERE Lo_Proveedor.Proveedor=Deudor ORDER BY FechaCredito ASC;
	END IF;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `Sb_ListaDeProductosXBloque` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `Sb_ListaDeProductosXBloque`(IN `var_Bloque` VARCHAR(255), IN `var_PorcentajeNuevo` FLOAT)
BEGIN
	Select 
			P.IdProducto,
			P.Producto,
			IFNull((Select 
						CASE WHEN Gen_Producto.IdProducto=P.IdProducto THEN
							Round(Lo_MovimientoDetalle.Precio,2)
						ELSE
							CASE WHEN (SELECT Gen_Producto.ControlaStock FROM Gen_Producto Where Gen_Producto.IdProducto=P.IdProducto)=TRUE THEN
								Round(Lo_MovimientoDetalle.Precio/(SElect Gen_ProductoDet.CAntidad From Gen_ProductoDet Where Gen_ProductoDet.IdProductoDet=P.IdProducto and Gen_ProductoDet.IdProducto=Gen_Producto.IdProducto),2)
							ELSE
								Round(Lo_MovimientoDetalle.Precio*(SElect Gen_ProductoDet.CAntidad From Gen_ProductoDet Where Gen_ProductoDet.IdProducto=P.IdProducto and Gen_ProductoDet.IdProductoDet=Gen_Producto.IdProducto),2)
							END
						END as Precio
					From 
						Lo_Movimiento 
						Inner Join Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
						Inner Join Gen_Producto On Lo_MovimientoDetalle.IdProducto=Gen_Producto.IdProducto
						Inner Join Gen_ProductoBloque On Gen_Producto.IdBloque=Gen_ProductoBloque.IdBloque
					Where 
						Lo_MovimientoDetalle.IdProducto = P.IdProducto or Lo_MovimientoDetalle.IdProducto In (
				(SElect Gen_ProductoDet.IdProducto From Gen_ProductoDet
										Where IdProductoDet=P.IdProducto or IdProducto=P.IdProducto)
						
						)
						and Lo_MovimientoDetalle.Precio>0
					Order by Lo_Movimiento.MovimientoFecha desc
			 LIMIT 0,1),0) as PrecioCosto,
			P.PrecioContado,
			CASE WHEN IFNull((Select 
						CASE WHEN Gen_Producto.IdProducto=P.IdProducto THEN
							Round(Lo_MovimientoDetalle.Precio,2)
						ELSE
							CASE WHEN (SELECT Gen_Producto.ControlaStock FROM Gen_Producto Where Gen_Producto.IdProducto=P.IdProducto)=TRUE THEN
								Round(Lo_MovimientoDetalle.Precio/(SElect Gen_ProductoDet.CAntidad From Gen_ProductoDet Where Gen_ProductoDet.IdProductoDet=P.IdProducto and Gen_ProductoDet.IdProducto=Gen_Producto.IdProducto),2)
							ELSE
								Round(Lo_MovimientoDetalle.Precio*(SElect Gen_ProductoDet.CAntidad From Gen_ProductoDet Where Gen_ProductoDet.IdProducto=P.IdProducto and Gen_ProductoDet.IdProductoDet=Gen_Producto.IdProducto),2)
							END
						END as Precio
					From 
						Lo_Movimiento 
						Inner Join Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
						Inner Join Gen_Producto On Lo_MovimientoDetalle.IdProducto=Gen_Producto.IdProducto
						Inner Join Gen_ProductoBloque On Gen_Producto.IdBloque=Gen_ProductoBloque.IdBloque
					Where 
						Lo_MovimientoDetalle.IdProducto = P.IdProducto or Lo_MovimientoDetalle.IdProducto In (
				(SElect Gen_ProductoDet.IdProducto From Gen_ProductoDet
										Where IdProductoDet=P.IdProducto or IdProducto=P.IdProducto)
						
						)
						and Lo_MovimientoDetalle.Precio>0
					Order by Lo_Movimiento.MovimientoFecha desc
			 LIMIT 0,1),0)>0 THEN
				Round( 100-((IFNull((Select 
							CASE WHEN Gen_Producto.IdProducto=P.IdProducto THEN
								Round(Lo_MovimientoDetalle.Precio,2)
							ELSE
								CASE WHEN (SELECT Gen_Producto.ControlaStock FROM Gen_Producto Where Gen_Producto.IdProducto=P.IdProducto)=TRUE THEN
									Round(Lo_MovimientoDetalle.Precio/(SElect Gen_ProductoDet.CAntidad From Gen_ProductoDet Where Gen_ProductoDet.IdProductoDet=P.IdProducto and Gen_ProductoDet.IdProducto=Gen_Producto.IdProducto),2)
								ELSE
									Round(Lo_MovimientoDetalle.Precio*(SElect Gen_ProductoDet.CAntidad From Gen_ProductoDet Where Gen_ProductoDet.IdProducto=P.IdProducto and Gen_ProductoDet.IdProductoDet=Gen_Producto.IdProducto),2)
								END
							END as Precio
						From 
							Lo_Movimiento 
							Inner Join Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
							Inner Join Gen_Producto On Lo_MovimientoDetalle.IdProducto=Gen_Producto.IdProducto
							Inner Join Gen_ProductoBloque On Gen_Producto.IdBloque=Gen_ProductoBloque.IdBloque
						Where 
							Lo_MovimientoDetalle.IdProducto = P.IdProducto or Lo_MovimientoDetalle.IdProducto In (
				(SElect Gen_ProductoDet.IdProducto From Gen_ProductoDet
										Where IdProductoDet=P.IdProducto or IdProducto=P.IdProducto)
						
						)
						and Lo_MovimientoDetalle.Precio>0
						Order by Lo_Movimiento.MovimientoFecha desc
				 LIMIT 0,1),0)/P.PrecioContado)*100),2)
		ELSE
			0
		END as Utilidad,
		var_PorcentajeNuevo as PorcentajeNuevo,
		
		CASE WHEN IFNull((Select 
						CASE WHEN Gen_Producto.IdProducto=P.IdProducto THEN
							Round(Lo_MovimientoDetalle.Precio,2)
						ELSE
							CASE WHEN (SELECT Gen_Producto.ControlaStock FROM Gen_Producto Where Gen_Producto.IdProducto=P.IdProducto)=TRUE THEN
								Round(Lo_MovimientoDetalle.Precio/(SElect Gen_ProductoDet.CAntidad From Gen_ProductoDet Where Gen_ProductoDet.IdProductoDet=P.IdProducto and Gen_ProductoDet.IdProducto=Gen_Producto.IdProducto),2)
							ELSE
								Round(Lo_MovimientoDetalle.Precio*(SElect Gen_ProductoDet.CAntidad From Gen_ProductoDet Where Gen_ProductoDet.IdProducto=P.IdProducto and Gen_ProductoDet.IdProductoDet=Gen_Producto.IdProducto),2)
							END
						END as Precio
					From 
						Lo_Movimiento 
						Inner Join Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
						Inner Join Gen_Producto On Lo_MovimientoDetalle.IdProducto=Gen_Producto.IdProducto
						Inner Join Gen_ProductoBloque On Gen_Producto.IdBloque=Gen_ProductoBloque.IdBloque
					Where 
						Lo_MovimientoDetalle.IdProducto = P.IdProducto or Lo_MovimientoDetalle.IdProducto In (
				(SElect Gen_ProductoDet.IdProducto From Gen_ProductoDet
										Where IdProductoDet=P.IdProducto or IdProducto=P.IdProducto)
						
						)
						and Lo_MovimientoDetalle.Precio>0
					Order by Lo_Movimiento.MovimientoFecha desc
			 LIMIT 0,1),0)>0 THEN
						
					(Select 
						CASE WHEN Gen_Producto.IdProducto=P.IdProducto THEN
							Round(Lo_MovimientoDetalle.Precio,2)
						ELSE
							CASE WHEN (SELECT Gen_Producto.ControlaStock FROM Gen_Producto Where Gen_Producto.IdProducto=P.IdProducto)=TRUE THEN
								Round(Lo_MovimientoDetalle.Precio/(SElect Gen_ProductoDet.CAntidad From Gen_ProductoDet Where Gen_ProductoDet.IdProductoDet=P.IdProducto and Gen_ProductoDet.IdProducto=Gen_Producto.IdProducto),2)
							ELSE
								Round(Lo_MovimientoDetalle.Precio*(SElect Gen_ProductoDet.CAntidad From Gen_ProductoDet Where Gen_ProductoDet.IdProducto=P.IdProducto and Gen_ProductoDet.IdProductoDet=Gen_Producto.IdProducto),2)
							END
						END as Precio
					From 
						Lo_Movimiento 
						Inner Join Lo_MovimientoDetalle On Lo_Movimiento.`Hash`=Lo_MovimientoDetalle.hashMovimiento
						Inner Join Gen_Producto On Lo_MovimientoDetalle.IdProducto=Gen_Producto.IdProducto
						Inner Join Gen_ProductoBloque On Gen_Producto.IdBloque=Gen_ProductoBloque.IdBloque
					Where 
						Lo_MovimientoDetalle.IdProducto = P.IdProducto or Lo_MovimientoDetalle.IdProducto In (
				(SElect Gen_ProductoDet.IdProducto From Gen_ProductoDet
										Where IdProductoDet=P.IdProducto or IdProducto=P.IdProducto)
						
						)
						and Lo_MovimientoDetalle.Precio>0
					Order by Lo_Movimiento.MovimientoFecha desc
			 LIMIT 0,1)/(1-(var_PorcentajeNuevo/100))
			ELSE
			0
			END as PrecioVentaNuevo
		
From 
	Gen_Producto P
	Inner JOIN Gen_ProductoBloque B On P.IdBloque=B.IdBloque
Where 
	B.Bloque=var_Bloque
ORder by P.IdProducto;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `Sb_ListarProductoInv` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `Sb_ListarProductoInv`()
begin
select * from Gen_Producto where ControlaStock = 1 and Anulado = 0;
end ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `Sb_ListarProductosRegMov` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `Sb_ListarProductosRegMov`(IN `var_idMov` VARCHAR(255))
BEGIN
	Select  Lo_MovimientoDetalle.hashMovimiento, Gen_Producto.Codigo, Gen_ProductoFormaFarmaceutica.ProductoFormaFarmaceutica, Gen_Producto.Producto, Gen_Producto.PrecioContado, Gen_ProductoMedicion.ProductoMedicion, Lo_MovimientoDetalle.Cantidad, Lo_MovimientoDetalle.TieneIgv, Lo_MovimientoDetalle.Precio,
 Gen_ProductoMarca.ProductoMarca
	from Lo_MovimientoDetalle
	INNER JOIN Gen_Producto on Gen_Producto.IdProducto = Lo_MovimientoDetalle.IdProducto
	INNER JOIN Gen_ProductoFormaFarmaceutica ON Gen_ProductoFormaFarmaceutica.IdProductoFormaFarmaceutica = Gen_Producto.IdProductoFormaFarmaceutica
	INNER JOIN Gen_ProductoMedicion ON Gen_ProductoMedicion.IdProductoMedicion = Gen_Producto.IdProductoMedicion
	INNER JOIN Gen_ProductoMarca ON Gen_ProductoMarca.IdProductoMarca = Gen_Producto.IdProductoMarca
	WHERE Lo_MovimientoDetalle.hashMovimiento = var_idMov;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `Sb_ListarProductosRegVenta` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `Sb_ListarProductosRegVenta`(IN `var_DocVenta` INT)
BEGIN
Select Gen_Producto.Producto, Gen_Producto.Codigo, Gen_ProductoFormaFarmaceutica.ProductoFormaFarmaceutica, Gen_ProductoMedicion.ProductoMedicion, Ve_DocVentaDet.IdDocVenta, Ve_DocVentaDet.Cantidad, Ve_DocVentaDet.Precio,
Gen_ProductoMarca.ProductoMarca
 from Gen_Producto
	INNER JOIN Ve_DocVentaDet on Ve_DocVentaDet.IdProducto = Gen_Producto.IdProducto
	INNER JOIN Gen_ProductoFormaFarmaceutica ON Gen_ProductoFormaFarmaceutica.IdProductoFormaFarmaceutica = Gen_Producto.IdProductoFormaFarmaceutica
	INNER JOIN Gen_ProductoMedicion ON Gen_ProductoMedicion.IdProductoMedicion = Gen_Producto.IdProductoMedicion
INNER JOIN Gen_ProductoMarca ON Gen_ProductoMarca.IdProductoMarca = Gen_Producto.IdProductoMarca
	WHERE Ve_DocVentaDet.IdDocVenta = var_DocVenta;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `Sb_ListarTratamientoXDiagnostico` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `Sb_ListarTratamientoXDiagnostico`(IN `var_diagnostico` INT)
BEGIN
Select Ve_ExpertoTratamiento.IdTratamiento,
CONCAT(Gen_ProductoCompuesto.ProductoCompuesto," NroDias (",CONVERT(Ve_ExpertoTratamiento.NroDias,CHAR),") NroTomas (",CONVERT(Ve_ExpertoTratamiento.TomasXDia,CHAR) , ")") as Tratamiento,
Gen_ProductoCompuesto.ProductoCompuesto,
Ve_ExpertoTratamiento.NroDias,
Ve_ExpertoTratamiento.TomasXDia,
Ve_ExpertoTratamiento.Observacion,
Ve_ExpertoTratamiento.DosisXPeso,
Ve_ExpertoTratamiento.Concentracion,
Ve_ExpertoTratamiento.UnidadDosisXPeso
From Ve_ExpertoTratamiento
Inner Join Gen_ProductoCompuesto On Ve_ExpertoTratamiento.IdCompuesto=Gen_ProductoCompuesto.IdProductoCompuesto
where Ve_ExpertoTratamiento.IdDiagnostico = var_diagnostico;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'NO_AUTO_VALUE_ON_ZERO' */ ;
/*!50003 DROP PROCEDURE IF EXISTS `Sb_VerificarMovimiento` */;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = utf8mb4 */ ;
/*!50003 SET character_set_results = utf8mb4 */ ;
/*!50003 SET collation_connection  = utf8mb4_general_ci */ ;
DELIMITER ;;
CREATE PROCEDURE `Sb_VerificarMovimiento`(IN `var_MovimientoTipo` VARCHAR(255), IN `var_Proveedor` VARCHAR(255), IN `var_Serie` VARCHAR(255), IN `var_Numero` INT)
BEGIN
	SELECT IFNULL((SELECT COUNT(*) FROM Lo_Movimiento
	INNER JOIN Lo_MovimientoTipo ON Lo_MovimientoTipo.IdMovimientoTipo = Lo_Movimiento.IdMovimientoTipo
	INNER JOIN Lo_Proveedor ON Lo_Proveedor.IdProveedor = Lo_Movimiento.IdProveedor
	WHERE Lo_MovimientoTipo.TipoMovimiento = var_MovimientoTipo
	AND Lo_Proveedor.Proveedor = var_Proveedor
	AND Lo_Movimiento.Serie = var_Serie
	AND Lo_Movimiento.Numero = var_Numero),0) as EXISTE;
END ;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

