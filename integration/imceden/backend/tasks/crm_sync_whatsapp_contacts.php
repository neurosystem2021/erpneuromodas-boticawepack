<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/config/crm.php';
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/api/crm/src/CrmRepository.php';
require_once dirname(__DIR__) . '/api/crm/src/CrmMessagingService.php';

$config = crmConfig();
$repository = new CrmRepository(appMysqliConnection(), (int) $config['tenant_id']);
$messaging = new CrmMessagingService($config);
$contacts = $messaging->resolveContacts($repository->conversationChatIds());
$result = $repository->updateConversationContacts($contacts);

echo 'Contactos consultados: ' . count($contacts)
    . '; conversaciones actualizadas: ' . (int) $result['updated'] . PHP_EOL;
