<?php

declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require_once dirname(__DIR__, 2) . '/config/crm.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once __DIR__ . '/src/CrmRepository.php';
require_once __DIR__ . '/src/CrmMessagingService.php';

function webhookJson($payload, $status = 200)
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

try {
    if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') webhookJson(array('error' => 'METHOD_NOT_ALLOWED'), 405);
    $config = crmConfig();
    $providedKey = (string) ($_SERVER['HTTP_APIKEY'] ?? '');
    if ($providedKey === '' || !hash_equals((string) $config['messaging_api_key'], $providedKey)) {
        webhookJson(array('error' => 'UNAUTHORIZED'), 401);
    }
    if (empty($config['enabled'])) webhookJson(array('error' => 'CRM_NOT_ENABLED'), 404);

    $input = json_decode((string) file_get_contents('php://input'), true);
    if (!is_array($input)) webhookJson(array('error' => 'INVALID_PAYLOAD'), 422);
    $chatId = trim((string) ($input['chat_id'] ?? ''));
    $messageId = trim((string) ($input['message_id'] ?? ''));
    $phone = trim((string) ($input['phone'] ?? ''));
    $displayName = trim((string) ($input['display_name'] ?? ''));
    $body = trim((string) ($input['body'] ?? ''));
    if ($chatId === '' || $messageId === '' || $body === '' || strlen($body) > 4000) {
        webhookJson(array('error' => 'INVALID_MESSAGE'), 422);
    }

    $repository = new CrmRepository(appMysqliConnection(), (int) $config['tenant_id']);
    $incoming = $repository->receiveWhatsAppMessage($chatId, $messageId, $phone, $body, $displayName);
    if (!$incoming['created']) webhookJson(array('accepted' => true, 'replied' => false, 'reason' => 'DUPLICATE'));
    if (empty($config['ai_auto_reply_enabled'])) {
        webhookJson(array('accepted' => true, 'replied' => false, 'reason' => 'AUTO_REPLY_DISABLED'));
    }

    $messaging = new CrmMessagingService($config);
    $draft = $messaging->assistantDraft($body, $repository->botRules());
    if (!empty($draft['handoff'])) {
        webhookJson(array('accepted' => true, 'replied' => false, 'reason' => 'HANDOFF_REQUIRED'));
    }
    $sent = $messaging->sendChatText($chatId, $draft['draft']);
    $repository->recordBotReply(
        $incoming['conversation_id'],
        $incoming['customer_id'],
        $draft['draft'],
        (string) ($sent['id'] ?? '')
    );
    webhookJson(array('accepted' => true, 'replied' => true));
} catch (Throwable $error) {
    error_log('CRM WhatsApp webhook: ' . $error->getMessage());
    webhookJson(array('error' => 'WEBHOOK_PROCESSING_FAILED'), 500);
}
