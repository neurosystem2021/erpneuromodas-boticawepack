<?php

declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once dirname(__DIR__, 2) . '/config/crm.php';
require_once dirname(__DIR__, 2) . '/config/database.php';
require_once __DIR__ . '/src/CrmDomain.php';
require_once __DIR__ . '/src/CrmRepository.php';
require_once __DIR__ . '/src/CrmSyncService.php';
require_once __DIR__ . '/src/CrmMessagingService.php';
require_once __DIR__ . '/src/CrmCustomerImportService.php';
require_once __DIR__ . '/src/CrmMediaService.php';

function crmJson($payload, $status = 200)
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

$config = crmConfig();
if (!$config['enabled']) crmJson(array('error' => 'CRM_NOT_ENABLED'), 404);

try {
    $context = CrmAccess::sessionContext($_SESSION, $config);
    $route = trim(isset($_GET['route']) ? (string) $_GET['route'] : 'health', '/');
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    $repository = new CrmRepository(appMysqliConnection(), $context['tenant_id']);
    $messaging = new CrmMessagingService($config);

    if ($method !== 'GET') {
        if (!CrmAccess::canWrite($context, $config)) crmJson(array('error' => 'FORBIDDEN'), 403);
        $csrf = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!CrmAccess::validCsrf($_SESSION, $csrf)) crmJson(array('error' => 'CSRF_INVALID'), 403);
        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) $input = array();
        if ($method === 'POST' && $route === 'tasks') {
            crmJson(array('data' => $repository->createTask($input, $context['user'])), 201);
        }
        if ($method === 'POST' && $route === 'customers/import') {
            $importer = new CrmCustomerImportService();
            $import = $importer->readUploadedFile($_FILES['file'] ?? array());
            crmJson(array('data' => $repository->importCustomers(
                $import['rows'], $import['rejected'], $context['user']
            )), 201);
        }
        if ($method === 'POST' && $route === 'messaging/media') {
            $mediaService = new CrmMediaService();
            crmJson(array('data' => $mediaService->upload($_FILES['file'] ?? array())), 201);
        }
        if ($method === 'PATCH' && preg_match('#^tasks/(\d+)$#', $route, $match)) {
            crmJson(array('data' => $repository->updateTask($match[1], $input, $context['user'])));
        }
        if ($method === 'DELETE' && preg_match('#^tasks/(\d+)$#', $route, $match)) {
            crmJson(array('data' => $repository->deleteTask($match[1], $context['user'])));
        }
        if ($method === 'POST' && $route === 'segments') {
            crmJson(array('data' => $repository->createSegment($input, $context['user'])), 201);
        }
        if ($method === 'POST' && $route === 'segments/preview') {
            crmJson(array('data' => $repository->previewSegment($input)));
        }
        if ($method === 'PATCH' && preg_match('#^segments/(\d+)$#', $route, $match)) {
            crmJson(array('data' => $repository->updateSegment($match[1], $input, $context['user'])));
        }
        if ($method === 'POST' && preg_match('#^segments/(\d+)/refresh$#', $route, $match)) {
            crmJson(array('data' => $repository->refreshSegment($match[1], $context['user'])));
        }
        if ($method === 'DELETE' && preg_match('#^segments/(\d+)$#', $route, $match)) {
            crmJson(array('data' => $repository->deleteSegment($match[1], $context['user'])));
        }
        if ($method === 'POST' && $route === 'campaigns') {
            crmJson(array('data' => $repository->createCampaign($input, $context['user'])), 201);
        }
        if ($method === 'DELETE' && preg_match('#^campaigns/(\d+)$#', $route, $match)) {
            crmJson(array('data' => $repository->deleteCampaign($match[1], $context['user'])));
        }
        if ($method === 'POST' && preg_match('#^campaigns/(\d+)/prepare$#', $route, $match)) {
            crmJson(array('data' => $repository->prepareCampaignOutbox(
                $match[1], $context['user'],
                $config['messaging_enabled'] && $config['messaging_provider'] !== 'simulation',
                $config['messaging_batch_limit']
            )), 201);
        }
        if ($method === 'POST' && $route === 'campaigns/audience') {
            crmJson(array('data' => $repository->campaignAudience($input)));
        }
        if ($method === 'POST' && $route === 'messaging/templates') {
            crmJson(array('data' => $repository->createMessageTemplate($input, $context['user'])), 201);
        }
        if ($method === 'POST' && $route === 'messaging/bot-rules') {
            crmJson(array('data' => $repository->createBotRule($input, $context['user'])), 201);
        }
        if ($method === 'PATCH' && preg_match('#^messaging/bot-rules/(\d+)$#', $route, $match)) {
            crmJson(array('data' => $repository->updateBotRule($match[1], $input, $context['user'])));
        }
        if ($method === 'DELETE' && preg_match('#^messaging/bot-rules/(\d+)$#', $route, $match)) {
            crmJson(array('data' => $repository->deleteBotRule($match[1], $context['user'])));
        }
        if ($method === 'POST' && preg_match('#^customers/(\d+)/consent$#', $route, $match)) {
            crmJson(array('data' => $repository->setCustomerConsent(
                $match[1], $input, $context['user']
            )));
        }
        if ($method === 'PATCH' && preg_match('#^customers/(\d+)$#', $route, $match)) {
            crmJson(array('data' => $repository->updateCustomer($match[1], $input, $context['user'])));
        }
        if ($method === 'POST' && $route === 'messaging/assistant/draft') {
            crmJson(array('data' => $messaging->assistantDraft(
                $input['message'] ?? '', $repository->botRules()
            )));
        }
        if ($method === 'POST' && $route === 'messaging/ai-settings') {
            $savedAiConfig = crmSaveAiConfig($input);
            $repository->recordAiSettingsUpdate($context['user'], $savedAiConfig);
            crmJson(array('data' => array(
                'ai_enabled' => $savedAiConfig['ai_enabled'],
                'ai_provider' => $savedAiConfig['ai_provider'],
                'ai_model' => $savedAiConfig['ai_model'],
                'ai_key_configured' => $savedAiConfig['ai_api_key'] !== '',
                'ai_system_prompt' => $savedAiConfig['ai_system_prompt'],
                'ai_auto_reply_enabled' => $savedAiConfig['ai_auto_reply_enabled'],
            )));
        }
        if ($method === 'POST' && $route === 'messaging/connection/start') {
            crmJson(array('data' => $messaging->startConnection()), 202);
        }
        if ($method === 'POST' && $route === 'messaging/connection/qr') {
            crmJson(array('data' => $messaging->connectionQr()));
        }
        if ($method === 'POST' && $route === 'messaging/connection/disconnect') {
            crmJson(array('data' => $messaging->disconnectConnection()));
        }
        if ($method === 'POST' && $route === 'messaging/send') {
            $contact = $repository->contactForDirectCampaign(
                $input['campaign_id'] ?? 0,
                $input['customer_id'] ?? 0,
                !empty($input['consent_confirmed']),
                $context['user']
            );
            $message = trim((string) ($input['message'] ?? ''));
            $message = str_replace('{{nombre}}', $contact['display_name'], $message);
            $mediaToken = trim((string) ($input['media_token'] ?? ''));
            if ($message === '' && $mediaToken === '') {
                throw new InvalidArgumentException('Escriba un mensaje o adjunte una imagen o video.');
            }
            if ($mediaToken !== '') {
                $mediaService = new CrmMediaService();
                $result = $messaging->sendMedia(
                    $contact['phone'], $mediaService->payload($mediaToken), $message
                );
            } else {
                $result = $messaging->sendText($contact['phone'], $message);
            }
            $saved = $repository->recordDirectCampaignSend(
                $input['campaign_id'], $contact['id'],
                $message !== '' ? $message : '[Archivo multimedia]',
                $result['id'] ?? '', $context['user'], $result['chat_id'] ?? ''
            );
            crmJson(array('data' => array_merge($saved, array(
                'customer_id' => (int) $contact['id'],
            ))));
        }
        if ($method === 'POST' && preg_match('#^messaging/conversations/(\d+)/send$#', $route, $match)) {
            $message = trim((string) ($input['message'] ?? ''));
            if ($message === '' || mb_strlen($message) > 4000) {
                throw new InvalidArgumentException('Escriba un mensaje de hasta 4000 caracteres.');
            }
            $conversation = $repository->conversationForReply($match[1]);
            $result = $messaging->sendChatText($conversation['external_chat_id'], $message);
            crmJson(array('data' => $repository->recordAgentReply(
                (int) $conversation['id'],
                $conversation['customer_id'] ? (int) $conversation['customer_id'] : null,
                $message, (string) ($result['id'] ?? ''), $context['user']
            )));
        }
        if ($method === 'POST' && preg_match('#^messaging/conversations/(\d+)/read$#', $route, $match)) {
            crmJson(array('data' => $repository->markConversationRead((int) $match[1])));
        }
        if ($method === 'PATCH' && preg_match('#^messaging/conversations/(\d+)/label$#', $route, $match)) {
            crmJson(array('data' => $repository->updateConversationLabel(
                (int) $match[1], $input['label'] ?? '', $context['user']
            )));
        }
        if ($method === 'DELETE' && preg_match('#^messaging/conversations/(\d+)$#', $route, $match)) {
            crmJson(array('data' => $repository->deleteConversation(
                (int) $match[1], $context['user']
            )));
        }
        if ($method === 'POST' && $route === 'messaging/inbox/sync-contacts') {
            $contacts = $messaging->resolveContacts($repository->conversationChatIds());
            crmJson(array('data' => $repository->updateConversationContacts($contacts)));
        }
        if ($method === 'POST' && $route === 'loyalty/adjustments') {
            crmJson(array('data' => $repository->adjustLoyalty($input, $context['user'])), 201);
        }
        if ($method === 'POST' && $route === 'sync/customers') {
            $runId = $repository->startSyncRun('pos_customers');
            $service = new CrmSyncService(appMysqliConnection(), $context['tenant_id']);
            try {
                $rows = $service->preview($config['sync_batch_size']);
                $processed = $service->apply($rows);
                $repository->finishSyncRun($runId, 'completed', $processed);
                crmJson(array('data' => array('processed' => $processed, 'run_id' => $runId)));
            } catch (Throwable $syncError) {
                $repository->finishSyncRun(
                    $runId, 'failed', 0, mb_substr($syncError->getMessage(), 0, 1000)
                );
                throw $syncError;
            }
        }
        crmJson(array('error' => 'METHOD_NOT_IMPLEMENTED'), 405);
    }

    if ($route === 'health') crmJson(array('ok' => true, 'tenant_id' => $context['tenant_id']));

    switch ($route) {
        case 'dashboard':
            $data = $repository->dashboard();
            break;
        case 'analytics':
            $data = $repository->analyticsDashboard($_GET['from'] ?? '', $_GET['to'] ?? '');
            break;
        case 'customers':
            $data = $repository->customers($_GET['q'] ?? '', $_GET['limit'] ?? 50);
            break;
        case 'tasks':
            $data = $repository->tasks($_GET['limit'] ?? 100);
            break;
        case 'campaigns':
            $data = $repository->campaigns();
            break;
        case 'segments':
            $data = $repository->segments();
            break;
        case 'loyalty':
            $data = array(
                'accounts' => $repository->loyalty(),
                'customers' => $repository->customers('', 200),
                'movements' => $repository->loyaltyLedger(100),
            );
            break;
        case 'sync/status':
            $data = $repository->syncStatus();
            break;
        case 'messaging/status':
            $data = array(
                'config' => crmPublicConfig($config),
                'provider' => $messaging->providerStatus(),
                'outbox' => $repository->outbox(),
            );
            break;
        case 'messaging/connection/status':
            $data = $messaging->connectionStatus();
            break;
        case 'messaging/conversations':
            $data = $repository->conversations($_GET['limit'] ?? 100);
            break;
        case 'messaging/inbox':
            $data = array(
                'config' => crmPublicConfig($config),
                'provider' => $messaging->providerStatus(),
                'conversations' => $repository->conversations($_GET['limit'] ?? 100),
            );
            break;
        case 'messaging/templates':
            $data = $repository->messageTemplates();
            break;
        case 'messaging/bot-rules':
            $data = $repository->botRules();
            break;
        default:
            if (preg_match('#^customers/(\d+)$#', $route, $match)) {
                $data = $repository->customer360($match[1]);
            } elseif (preg_match('#^messaging/conversations/(\d+)/messages$#', $route, $match)) {
                $data = $repository->messages($match[1], $_GET['limit'] ?? 100);
            } else {
                crmJson(array('error' => 'NOT_FOUND'), 404);
            }
    }
    crmJson(array('data' => $data));
} catch (InvalidArgumentException $e) {
    crmJson(array('error' => $e->getMessage()), 422);
} catch (RuntimeException $e) {
    $status = $e->getCode() >= 400 && $e->getCode() < 600 ? $e->getCode() : 500;
    if ($status === 500) error_log('CRM API: ' . $e->getMessage());
    crmJson(array('error' => $status === 500 ? 'CRM_INTERNAL_ERROR' : $e->getMessage()), $status);
} catch (Throwable $e) {
    error_log('CRM API: ' . $e->getMessage());
    crmJson(array('error' => 'CRM_INTERNAL_ERROR'), 500);
}
