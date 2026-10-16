<?php

require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/api/crm/src/CrmRepository.php';
require_once dirname(__DIR__, 2) . '/api/crm/src/CrmMessagingService.php';

$repository = new CrmRepository(appMysqliConnection(), 1);
$service = new CrmMessagingService(array(
    'messaging_enabled' => false,
    'messaging_provider' => 'simulation',
    'messaging_base_url' => '',
    'messaging_instance' => '',
    'messaging_api_key' => '',
    'ai_enabled' => false,
    'ai_provider' => 'simulation',
    'ai_model' => '',
    'ai_api_key' => '',
));

echo json_encode(array(
    'provider' => $service->providerStatus(),
    'outbox_rows' => count($repository->outbox()),
    'bot_rules' => count($repository->botRules()),
    'templates' => count($repository->messageTemplates()),
), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), PHP_EOL;
