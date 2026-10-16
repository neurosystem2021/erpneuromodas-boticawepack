<?php

require_once dirname(__DIR__, 2) . '/api/crm/src/CrmMessagingService.php';

function messagingAssert($condition, $message)
{
    if (!$condition) throw new RuntimeException($message);
}

$config = array(
    'messaging_enabled' => false,
    'messaging_provider' => 'simulation',
    'messaging_base_url' => '',
    'messaging_instance' => '',
    'messaging_api_key' => '',
    'ai_enabled' => false,
    'ai_provider' => 'simulation',
    'ai_model' => '',
    'ai_api_key' => '',
    'ai_system_prompt' => 'Responde como vendedor humano y deriva si no conoces la respuesta.',
);
$service = new CrmMessagingService($config);
$status = $service->providerStatus();
messagingAssert($status['mode'] === 'simulation', 'El modo seguro inicial debe ser simulación.');
messagingAssert($status['enabled'] === false, 'El envío real debe iniciar apagado.');

$draft = $service->assistantDraft('¿Cuál es el horario?', array(array(
    'trigger_text' => 'horario',
    'response_text' => 'Atendemos de lunes a sábado.',
    'handoff' => 0,
)));
messagingAssert($draft['provider'] === 'rule', 'Debe priorizar reglas locales.');
messagingAssert($draft['draft'] === 'Atendemos de lunes a sábado.', 'La respuesta de regla cambió.');

$fallback = $service->assistantDraft('Una consulta desconocida', array());
messagingAssert($fallback['provider'] === 'simulation', 'La IA debe permanecer simulada sin configuración.');
messagingAssert($fallback['handoff'] === true, 'El fallback seguro debe derivar a una persona.');

echo "CrmMessagingTest OK\n";
