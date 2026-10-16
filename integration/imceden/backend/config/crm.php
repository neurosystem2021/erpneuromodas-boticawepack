<?php

function crmEnvironmentBoolean($name, $default = false)
{
    $value = getenv($name);
    if ($value === false || $value === '') {
        return $default;
    }

    return filter_var($value, FILTER_VALIDATE_BOOLEAN);
}

function crmConfig()
{
    $defaultAiPrompt = 'Eres un asesor humano y amable de una tienda de ropa. Responde en español '
        . 'con naturalidad, empatía y brevedad. Ayuda a elegir prendas, tallas y colores sin presionar. '
        . 'No inventes precios, disponibilidad, promociones ni datos del negocio. '
        . 'Cuando no tengas información suficiente, indica que un asesor continuará la atención.';
    $config = array(
        'enabled' => crmEnvironmentBoolean('CRM_ENABLED', false),
        'demo_mode' => crmEnvironmentBoolean('CRM_DEMO_MODE', false),
        'tenant_id' => max(1, (int) (getenv('CRM_TENANT_ID') ?: 1)),
        'sync_batch_size' => max(1, min(1000, (int) (getenv('CRM_SYNC_BATCH_SIZE') ?: 250))),
        'messaging_enabled' => crmEnvironmentBoolean('CRM_MESSAGING_ENABLED', false),
        'messaging_provider' => strtolower((string) (getenv('CRM_MESSAGING_PROVIDER') ?: 'simulation')),
        'messaging_base_url' => rtrim((string) (getenv('CRM_MESSAGING_BASE_URL') ?: ''), '/'),
        'messaging_instance' => (string) (getenv('CRM_MESSAGING_INSTANCE') ?: ''),
        'messaging_api_key' => (string) (getenv('CRM_MESSAGING_API_KEY') ?: ''),
        'messaging_batch_limit' => max(1, min(100, (int) (getenv('CRM_MESSAGING_BATCH_LIMIT') ?: 20))),
        'ai_enabled' => crmEnvironmentBoolean('CRM_AI_ENABLED', false),
        'ai_provider' => strtolower((string) (getenv('CRM_AI_PROVIDER') ?: 'simulation')),
        'ai_model' => (string) (getenv('CRM_AI_MODEL') ?: ''),
        'ai_api_key' => (string) (getenv('CRM_AI_API_KEY') ?: ''),
        'ai_system_prompt' => (string) (getenv('CRM_AI_SYSTEM_PROMPT') ?: $defaultAiPrompt),
        'ai_auto_reply_enabled' => crmEnvironmentBoolean('CRM_AI_AUTO_REPLY_ENABLED', false),
        'write_profiles' => array_values(array_filter(array_map(
            'intval',
            explode(',', getenv('CRM_WRITE_PROFILES') ?: '1')
        ))),
    );

    $localConfigFile = __DIR__ . '/crm.local.php';
    if (is_file($localConfigFile)) {
        $localConfig = require $localConfigFile;
        if (is_array($localConfig)) {
            $config = array_merge($config, $localConfig);
        }
    }

    $featureConfigFile = __DIR__ . '/crm.feature.local.json';
    if (is_file($featureConfigFile)) {
        $featureConfig = json_decode((string) file_get_contents($featureConfigFile), true);
        if (is_array($featureConfig) && array_key_exists('enabled', $featureConfig)) {
            $config['enabled'] = $featureConfig['enabled'] === true;
        }
    }

    $aiConfigFile = __DIR__ . '/crm.ai.local.json';
    if (is_file($aiConfigFile)) {
        $aiConfig = json_decode((string) file_get_contents($aiConfigFile), true);
        if (is_array($aiConfig)) {
            $config = array_merge($config, array_intersect_key($aiConfig, array(
                'ai_enabled' => true,
                'ai_provider' => true,
                'ai_model' => true,
                'ai_api_key' => true,
                'ai_system_prompt' => true,
                'ai_auto_reply_enabled' => true,
            )));
        }
    }

    return $config;
}

function crmSaveFeatureState($enabled, $actor)
{
    if (strcasecmp(trim((string) $actor), 'superadmin') !== 0) {
        throw new RuntimeException('FORBIDDEN', 403);
    }
    $saved = array(
        'enabled' => $enabled === true,
        'updated_at' => date('c'),
        'updated_by' => 'superadmin',
    );
    $path = __DIR__ . '/crm.feature.local.json';
    $json = json_encode($saved, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false || file_put_contents($path, $json . PHP_EOL, LOCK_EX) === false) {
        throw new RuntimeException('No se pudo guardar el estado del CRM.', 500);
    }
    @chmod($path, 0600);
    return $saved;
}

function crmSaveAiConfig(array $input)
{
    $provider = strtolower(trim((string) ($input['provider'] ?? 'simulation')));
    if (!in_array($provider, array('simulation', 'gemini', 'openai'), true)) {
        throw new InvalidArgumentException('Proveedor de IA no válido.');
    }
    $model = trim((string) ($input['model'] ?? ''));
    $allowedModels = array(
        'simulation' => array(''),
        'gemini' => array(
            'gemini-3.6-flash',
            'gemini-3.5-flash-lite',
            'gemini-3.5-flash',
            'gemini-3.1-pro-preview',
        ),
        'openai' => array(
            'gpt-5.6-luna',
            'gpt-5.6-terra',
            'gpt-5.6-sol',
        ),
    );
    if (!in_array($model, $allowedModels[$provider], true)) {
        throw new InvalidArgumentException('Modelo de IA no válido.');
    }
    $prompt = trim((string) ($input['system_prompt'] ?? ''));
    $promptLength = function_exists('mb_strlen') ? mb_strlen($prompt, 'UTF-8') : strlen($prompt);
    if ($promptLength < 40 || $promptLength > 16000) {
        throw new InvalidArgumentException('Las instrucciones deben tener entre 40 y 16000 caracteres.');
    }

    $path = __DIR__ . '/crm.ai.local.json';
    $existing = array();
    if (is_file($path)) {
        $decoded = json_decode((string) file_get_contents($path), true);
        if (is_array($decoded)) $existing = $decoded;
    }
    $apiKey = trim((string) ($input['api_key'] ?? ''));
    if ($apiKey === '') $apiKey = (string) ($existing['ai_api_key'] ?? '');
    if (strlen($apiKey) > 500) throw new InvalidArgumentException('La clave API no es válida.');

    $enabled = !empty($input['enabled']) && $provider !== 'simulation';
    if ($enabled && ($model === '' || $apiKey === '')) {
        throw new InvalidArgumentException('Seleccione un modelo e ingrese una clave API.');
    }
    $saved = array(
        'ai_enabled' => $enabled,
        'ai_provider' => $provider,
        'ai_model' => $model,
        'ai_api_key' => $apiKey,
        'ai_system_prompt' => $prompt,
        'ai_auto_reply_enabled' => $enabled && !empty($input['auto_reply_enabled']),
    );
    $json = json_encode($saved, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false || file_put_contents($path, $json . PHP_EOL, LOCK_EX) === false) {
        throw new RuntimeException('AI_CONFIG_WRITE_FAILED', 500);
    }
    @chmod($path, 0600);
    return $saved;
}

function crmIsEnabled()
{
    $config = crmConfig();
    return $config['enabled'] === true;
}

function crmPublicConfig(array $config)
{
    return array(
        'messaging_enabled' => $config['messaging_enabled'] === true,
        'messaging_provider' => $config['messaging_provider'],
        'messaging_instance_configured' => $config['messaging_instance'] !== '',
        'ai_enabled' => $config['ai_enabled'] === true,
        'ai_provider' => $config['ai_provider'],
        'ai_model' => $config['ai_model'],
        'ai_key_configured' => $config['ai_api_key'] !== '',
        'ai_system_prompt' => $config['ai_system_prompt'],
        'ai_auto_reply_enabled' => !empty($config['ai_auto_reply_enabled']),
    );
}
