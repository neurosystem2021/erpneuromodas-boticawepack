<?php

final class CrmMessagingService
{
    private $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function providerStatus()
    {
        $provider = $this->config['messaging_provider'];
        $configured = $provider === 'simulation'
            || ($this->config['messaging_base_url'] !== ''
                && $this->config['messaging_instance'] !== ''
                && $this->config['messaging_api_key'] !== '');

        return array(
            'enabled' => $this->config['messaging_enabled'] === true,
            'provider' => $provider,
            'configured' => $configured,
            'mode' => $this->config['messaging_enabled'] && $provider !== 'simulation'
                ? 'live' : 'simulation',
            'instance' => $this->config['messaging_instance'] !== ''
                ? $this->config['messaging_instance'] : null,
        );
    }

    public function startConnection()
    {
        $this->assertMessagingConfigured();
        if ($this->config['messaging_provider'] === 'wppconnect') {
            return $this->gatewayRequest('POST', '/session/start', array());
        }
        if ($this->config['messaging_provider'] === 'evolution') {
            return $this->gatewayRequest('POST', '/instance/create', array(
                'instanceName' => $this->config['messaging_instance'],
                'integration' => 'WHATSAPP-BAILEYS',
                'qrcode' => true,
            ));
        }
        throw new RuntimeException('MESSAGING_PROVIDER_UNSUPPORTED', 409);
    }

    public function connectionStatus()
    {
        $this->assertMessagingConfigured();
        if ($this->config['messaging_provider'] === 'wppconnect') {
            return $this->gatewayRequest('GET', '/session/status');
        }
        if ($this->config['messaging_provider'] === 'evolution') {
            return $this->gatewayRequest(
                'GET', '/instance/connectionState/' . rawurlencode($this->config['messaging_instance']),
                array(), false
            );
        }
        throw new RuntimeException('MESSAGING_PROVIDER_UNSUPPORTED', 409);
    }

    public function connectionQr()
    {
        $this->assertMessagingConfigured();
        if ($this->config['messaging_provider'] === 'wppconnect') {
            return $this->gatewayRequest('GET', '/session/qr');
        }
        if ($this->config['messaging_provider'] === 'evolution') {
            return $this->gatewayRequest(
                'GET', '/instance/connect/' . rawurlencode($this->config['messaging_instance']),
                array(), false
            );
        }
        throw new RuntimeException('MESSAGING_PROVIDER_UNSUPPORTED', 409);
    }

    public function disconnectConnection()
    {
        $this->assertMessagingConfigured();
        if ($this->config['messaging_provider'] === 'wppconnect') {
            return $this->gatewayRequest('POST', '/session/logout', array());
        }
        throw new RuntimeException('MESSAGING_PROVIDER_UNSUPPORTED', 409);
    }

    public function sendText($phone, $message)
    {
        $this->assertMessagingConfigured();
        if (!$this->config['messaging_enabled']) {
            throw new RuntimeException('MESSAGING_SEND_DISABLED', 409);
        }
        $digits = preg_replace('/\D+/', '', (string) $phone);
        $message = trim((string) $message);
        if (strlen($digits) < 8 || $message === '') {
            throw new InvalidArgumentException('Teléfono y mensaje válidos son obligatorios.');
        }
        if ($this->config['messaging_provider'] === 'wppconnect') {
            return $this->gatewayRequest('POST', '/messages/send', array(
                'phone' => $digits,
                'message' => $message,
            ));
        }
        throw new RuntimeException('MESSAGING_SEND_PROVIDER_UNSUPPORTED', 409);
    }

    public function sendMedia($phone, array $media, $caption)
    {
        $this->assertMessagingConfigured();
        if (!$this->config['messaging_enabled']) throw new RuntimeException('MESSAGING_SEND_DISABLED', 409);
        $digits = preg_replace('/\D+/', '', (string) $phone);
        if (strlen($digits) < 8 || empty($media['data_uri'])) {
            throw new InvalidArgumentException('Destino y archivo válidos son obligatorios.');
        }
        if ($this->config['messaging_provider'] === 'wppconnect') {
            return $this->gatewayRequest('POST', '/messages/send-media', array(
                'phone' => $digits,
                'media' => $media['data_uri'],
                'filename' => $media['filename'],
                'caption' => trim((string) $caption),
            ), true, 90);
        }
        throw new RuntimeException('MESSAGING_SEND_PROVIDER_UNSUPPORTED', 409);
    }

    public function sendChatText($chatId, $message)
    {
        $this->assertMessagingConfigured();
        if (!$this->config['messaging_enabled']) {
            throw new RuntimeException('MESSAGING_SEND_DISABLED', 409);
        }
        $chatId = trim((string) $chatId);
        $message = trim((string) $message);
        if (!preg_match('/^[a-zA-Z0-9:_-]+@(?:c\\.us|lid)$/', $chatId) || $message === '') {
            throw new InvalidArgumentException('Chat y mensaje válidos son obligatorios.');
        }
        if ($this->config['messaging_provider'] === 'wppconnect') {
            return $this->gatewayRequest('POST', '/messages/send-chat', array(
                'chat_id' => $chatId,
                'message' => $message,
            ));
        }
        throw new RuntimeException('MESSAGING_SEND_PROVIDER_UNSUPPORTED', 409);
    }

    public function resolveContacts(array $chatIds)
    {
        $this->assertMessagingConfigured();
        if ($this->config['messaging_provider'] !== 'wppconnect') {
            throw new RuntimeException('MESSAGING_CONTACT_RESOLUTION_UNSUPPORTED', 409);
        }
        $result = $this->gatewayRequest('POST', '/contacts/resolve', array(
            'chat_ids' => array_values(array_slice($chatIds, 0, 200)),
        ));
        return is_array($result['contacts'] ?? null) ? $result['contacts'] : array();
    }

    private function assertMessagingConfigured()
    {
        $scheme = parse_url($this->config['messaging_base_url'], PHP_URL_SCHEME);
        if (!in_array($scheme, array('http', 'https'), true)
            || $this->config['messaging_instance'] === ''
            || $this->config['messaging_api_key'] === '') {
            throw new RuntimeException('MESSAGING_NOT_CONFIGURED', 409);
        }
    }

    private function gatewayRequest($method, $path, array $payload = array(), $includeName = true, $timeout = 30)
    {
        $url = $this->config['messaging_base_url'] . $path;
        if ($includeName) {
            $url .= (strpos($url, '?') === false ? '?' : '&')
                . 'name=' . rawurlencode($this->config['messaging_instance']);
        }
        $curl = curl_init($url);
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT => (int) $timeout,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'apikey: ' . $this->config['messaging_api_key'],
            ),
        );
        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = json_encode($payload, JSON_UNESCAPED_UNICODE);
        }
        curl_setopt_array($curl, $options);
        $body = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);
        if ($body === false || $error !== '') throw new RuntimeException('MESSAGING_GATEWAY_OFFLINE', 503);
        $decoded = json_decode($body, true);
        if (!is_array($decoded)) throw new RuntimeException('MESSAGING_INVALID_RESPONSE', 502);
        if ($status >= 400) {
            throw new RuntimeException((string) ($decoded['error'] ?? 'MESSAGING_PROVIDER_ERROR'), $status);
        }
        return $decoded;
    }

    public function assistantDraft($message, array $rules)
    {
        $message = trim((string) $message);
        if ($message === '') throw new InvalidArgumentException('El mensaje es obligatorio.');

        foreach ($rules as $rule) {
            if ((int) ($rule['is_active'] ?? 1) === 1
                && !empty($rule['trigger_text'])
                && stripos($message, (string) $rule['trigger_text']) !== false) {
                return array(
                    'provider' => 'rule',
                    'draft' => (string) $rule['response_text'],
                    'handoff' => (bool) $rule['handoff'],
                );
            }
        }

        if (!$this->config['ai_enabled'] || $this->config['ai_provider'] === 'simulation') {
            return array(
                'provider' => 'simulation',
                'draft' => 'Gracias por escribirnos. Un asesor continuará con su consulta en breve.',
                'handoff' => true,
            );
        }

        if ($this->config['ai_api_key'] === '' || $this->config['ai_model'] === '') {
            throw new RuntimeException('AI_NOT_CONFIGURED', 409);
        }

        if ($this->config['ai_provider'] === 'openai') {
            return $this->openAiDraft($message);
        }
        if ($this->config['ai_provider'] === 'gemini') {
            return $this->geminiDraft($message);
        }
        throw new RuntimeException('AI_PROVIDER_UNSUPPORTED', 409);
    }

    private function openAiDraft($message)
    {
        $payload = array(
            'model' => $this->config['ai_model'],
            'instructions' => $this->config['ai_system_prompt'],
            'input' => $message,
        );
        $response = $this->jsonRequest(
            'https://api.openai.com/v1/responses',
            $payload,
            array('Authorization: Bearer ' . $this->config['ai_api_key'])
        );
        $draft = '';
        if (!empty($response['output_text'])) {
            $draft = (string) $response['output_text'];
        } elseif (!empty($response['output'][0]['content'][0]['text'])) {
            $draft = (string) $response['output'][0]['content'][0]['text'];
        }
        if ($draft === '') throw new RuntimeException('AI_EMPTY_RESPONSE', 502);
        return array('provider' => 'openai', 'draft' => $draft, 'handoff' => false);
    }

    private function geminiDraft($message)
    {
        $payload = array(
            'system_instruction' => array('parts' => array(array(
                'text' => $this->config['ai_system_prompt'],
            ))),
            'contents' => array(array(
                'role' => 'user',
                'parts' => array(array('text' => $message)),
            )),
        );
        $model = $this->config['ai_model'];
        try {
            $response = $this->geminiRequest($model, $payload);
        } catch (RuntimeException $error) {
            $fallback = 'gemini-3.5-flash-lite';
            if ($error->getMessage() !== 'AI_RATE_LIMIT' || $model === $fallback) throw $error;
            $response = $this->geminiRequest($fallback, $payload);
            $model = $fallback;
        }
        $draft = $response['candidates'][0]['content']['parts'][0]['text'] ?? '';
        if ($draft === '') throw new RuntimeException('AI_EMPTY_RESPONSE', 502);
        return array('provider' => 'gemini:' . $model, 'draft' => $draft, 'handoff' => false);
    }

    private function geminiRequest($model, array $payload)
    {
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . rawurlencode($model)
            . ':generateContent?key=' . rawurlencode($this->config['ai_api_key']);
        return $this->jsonRequest($url, $payload);
    }

    private function jsonRequest($url, array $payload, array $headers = array())
    {
        $curl = curl_init($url);
        $headers[] = 'Content-Type: application/json';
        curl_setopt_array($curl, array(
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
        ));
        $body = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);
        if ($status === 429) throw new RuntimeException('AI_RATE_LIMIT', 429);
        if ($body === false || $error !== '' || $status < 200 || $status >= 300) {
            throw new RuntimeException('AI_PROVIDER_ERROR', 502);
        }
        $decoded = json_decode($body, true);
        if (!is_array($decoded)) throw new RuntimeException('AI_INVALID_RESPONSE', 502);
        return $decoded;
    }
}
