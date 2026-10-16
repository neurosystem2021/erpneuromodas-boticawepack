<?php

function staticAssert($condition, $message)
{
    if (!$condition) throw new RuntimeException($message);
}

$root = dirname(__DIR__, 2);
$up = file_get_contents($root . '/data/crm/001_crm_schema_up.sql');
$api = file_get_contents($root . '/api/crm/index.php');
$messagingUp = file_get_contents($root . '/data/crm/003_crm_messaging_up.sql');
$config = file_get_contents($root . '/config/crm.php');
$webhook = file_get_contents($root . '/api/crm/webhook.php');
$inboxUp = file_get_contents($root . '/data/crm/004_crm_inbox_contacts_up.sql');
$labelsUp = file_get_contents($root . '/data/crm/005_crm_conversation_labels_up.sql');
$repository = file_get_contents($root . '/api/crm/src/CrmRepository.php');
$gateway = file_get_contents($root . '/whatsapp-gateway/server.cjs');
$crmView = file_get_contents($root . '/views/CRM.php');
$crmFrontendApi = file_get_contents($root . '/crm/src/api.js');
$crmFrontendApp = file_get_contents($root . '/crm/src/App.vue');

staticAssert(stripos($up, 'DROP TABLE') === false, 'La migración UP contiene DROP.');
staticAssert(stripos($up, 'TRUNCATE') === false, 'La migración UP contiene TRUNCATE.');
preg_match_all('/CREATE TABLE IF NOT EXISTS\s+([a-zA-Z0-9_]+)/i', $up, $matches);
staticAssert(count($matches[1]) >= 8, 'Faltan entidades CRM.');
foreach ($matches[1] as $table) {
    staticAssert(strpos(strtolower($table), 'crm_') === 0, "Tabla fuera del prefijo CRM: $table");
}
staticAssert(strpos($api, "if (!\$config['enabled'])") !== false, 'La API no verifica el feature flag.');
staticAssert(strpos($api, 'sessionContext($_SESSION') !== false, 'La API no reutiliza la sesión.');

staticAssert(strpos($crmView, "header('Cache-Control: no-store") !== false, 'CRM view must not be cached after logout.');
staticAssert(strpos($crmFrontendApi, 'response.status !== 401') !== false, 'CRM frontend must detect an expired session.');
staticAssert(strpos($crmFrontendApi, "location.replace('/index.php')") !== false, 'CRM frontend must redirect to login.');
staticAssert(strpos($crmFrontendApp, "crmGet('health')") !== false, 'CRM must periodically verify its session.');
staticAssert(strpos($crmFrontendApp, 'v-else-if="loading"') === false, 'Success notices must not hide the active CRM screen.');

staticAssert(stripos($messagingUp, 'DROP TABLE') === false, 'Messaging migration contains DROP.');
staticAssert(stripos($messagingUp, 'TRUNCATE') === false, 'Messaging migration contains TRUNCATE.');
staticAssert(strpos($config, "'messaging_enabled' => crmEnvironmentBoolean('CRM_MESSAGING_ENABLED', false)") !== false, 'Real messaging must default to disabled.');
staticAssert(strpos($config, "'ai_enabled' => crmEnvironmentBoolean('CRM_AI_ENABLED', false)") !== false, 'AI must default to disabled.');
staticAssert(strpos($api, 'crmPublicConfig($config)') !== false, 'API must expose only filtered public configuration.');
staticAssert(strpos($config, "'ai_key_configured' => \$config['ai_api_key'] !== ''") !== false, 'The public config must expose only whether an AI key exists.');
staticAssert(strpos($config, "'ai_api_key' => \$config['ai_api_key']") === false, 'The public config must never expose the AI key.');
staticAssert(strpos(file_get_contents($root . '/.gitignore'), '/config/crm.ai.local.json') !== false, 'The local AI secret file must be ignored by Git.');
staticAssert(strpos($config, "'ai_auto_reply_enabled' => crmEnvironmentBoolean('CRM_AI_AUTO_REPLY_ENABLED', false)") !== false, 'Automatic replies must default to disabled.');
staticAssert(strpos($webhook, 'hash_equals') !== false, 'The inbound webhook must authenticate the local gateway.');
staticAssert(strpos($webhook, "empty(\$config['ai_auto_reply_enabled'])") !== false, 'The webhook must enforce the auto-reply flag.');
staticAssert(strpos($webhook, 'receiveWhatsAppMessage') < strpos($webhook, "empty(\$config['ai_auto_reply_enabled'])"), 'Inbound messages must be stored even when automatic replies are disabled.');
staticAssert(strpos($webhook, 'sendChatText($chatId') !== false, 'Automatic replies must target the original WhatsApp chat, including LID chats.');
staticAssert(stripos($inboxUp, 'DROP ') === false && stripos($inboxUp, 'TRUNCATE') === false, 'Inbox migration must be additive.');
staticAssert(stripos($labelsUp, 'DROP ') === false && stripos($labelsUp, 'TRUNCATE') === false, 'Conversation label migration must be additive.');
staticAssert(strpos($labelsUp, 'sales_label') !== false, 'Conversation label migration must add the sales label.');
staticAssert(strpos($repository, 'contact_phone') !== false, 'Inbox must persist the resolved contact phone.');
staticAssert(strpos($repository, 'updateConversationLabel') !== false, 'Repository must update conversation labels per tenant.');
staticAssert(strpos($repository, "'outbound','agent'") !== false, 'Manual replies must be recorded as agent messages.');
staticAssert(strpos($api, 'messaging/conversations/(\\d+)/send') !== false, 'Inbox must expose a protected manual reply route.');
staticAssert(strpos($api, 'messaging/conversations/(\\d+)/label') !== false, 'Inbox must expose a protected conversation label route.');
staticAssert(strpos($api, "case 'analytics':") !== false, 'CRM must expose the protected analytics dashboard.');
staticAssert(strpos($repository, 'analyticsDashboard') !== false, 'Repository must provide tenant-scoped CRM analytics.');
staticAssert(strpos($repository, 'previewSegment') !== false, 'Segments must provide a safe membership preview.');
staticAssert(strpos($repository, 'refreshSegmentMembers') !== false, 'Segments must calculate persisted tenant-scoped members.');
staticAssert(strpos($api, "segments/(\\d+)/refresh") !== false, 'Segments must expose a protected refresh route.');
staticAssert(strpos($gateway, 'getPnLidEntry') !== false, 'Gateway must resolve private WhatsApp LID identifiers.');
staticAssert(strpos($gateway, "'/contacts/resolve'") !== false, 'Gateway must expose authenticated contact resolution.');
staticAssert(strpos($gateway, 'withTimeout(resolveContact') !== false, 'Contact resolution must not block inbound message delivery.');
staticAssert(strpos($gateway, 'onAnyMessage') !== false, 'Gateway must register the compatible inbound event fallback.');
staticAssert(strpos($gateway, 'seenIncomingMessages') !== false, 'Multiple gateway events must be deduplicated.');
staticAssert(strpos($gateway, "'/session/logout'") !== false, 'Gateway must support explicitly unlinking WhatsApp.');
staticAssert(strpos($gateway, 'async function safeSendText') !== false, 'Text sending must bypass the broken WAPI getMessageById lookup.');
staticAssert(strpos($gateway, 'window.WPP.chat.sendTextMessage') !== false, 'Text sending must use the direct WPP API.');
staticAssert(strpos($api, 'messaging/connection/disconnect') !== false, 'CRM API must expose the protected unlink action.');
staticAssert(strpos($api, 'customers/import') !== false, 'CRM API must expose the protected customer import action.');
$customerImporter = file_get_contents($root . '/api/crm/src/CrmCustomerImportService.php');
staticAssert(strpos($customerImporter, "array('xlsx', 'csv')") !== false, 'Customer import must allow only supported spreadsheet formats.');
staticAssert(strpos($customerImporter, 'MAX_ROWS = 2000') !== false, 'Customer import must enforce a bounded row count.');
$mediaService = file_get_contents($root . '/api/crm/src/CrmMediaService.php');
staticAssert(strpos($mediaService, "'image/jpeg'") !== false && strpos($mediaService, "'video/mp4'") !== false, 'Campaign media must validate image and video MIME types.');
staticAssert(strpos($mediaService, 'random_bytes(20)') !== false, 'Uploaded media must use unguessable tokens.');
staticAssert(strpos($gateway, "'/messages/send-media'") !== false, 'Gateway must support campaign media delivery.');
staticAssert(strpos(file_get_contents($root . '/api/crm/src/CrmMessagingService.php'), "AI_RATE_LIMIT") !== false, 'Gemini rate limits must use a controlled fallback.');

echo "CrmStaticSafetyTest OK\n";
