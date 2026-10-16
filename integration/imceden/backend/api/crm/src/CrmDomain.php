<?php

final class CrmAccess
{
    public static function sessionContext(array $session, array $config)
    {
        if (empty($session['user'])) {
            throw new RuntimeException('AUTH_REQUIRED', 401);
        }

        return array(
            'tenant_id' => (int) $config['tenant_id'],
            'user' => (string) $session['user'],
            'profile_id' => isset($session['idPerfil']) ? (int) $session['idPerfil'] : 0,
            'warehouse_id' => isset($session['idAlmacen']) ? (int) $session['idAlmacen'] : 0,
        );
    }

    public static function canWrite(array $context, array $config)
    {
        return in_array((int) $context['profile_id'], $config['write_profiles'], true);
    }

    public static function validCsrf(array $session, $token)
    {
        return !empty($session['login_csrf'])
            && is_string($token)
            && hash_equals((string) $session['login_csrf'], $token);
    }
}

final class CrmNormalizer
{
    public static function phone($value)
    {
        $digits = preg_replace('/\D+/', '', (string) $value);
        if (strpos($digits, '51') === 0 && strlen($digits) > 9) {
            $digits = substr($digits, 2);
        }
        return strlen($digits) >= 7 ? $digits : null;
    }

    public static function email($value)
    {
        $email = strtolower(trim((string) $value));
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }
}

final class CrmRfmCalculator
{
    public static function calculate(array $customer, $asOfDate)
    {
        $asOf = new DateTimeImmutable($asOfDate);
        $last = !empty($customer['last_purchase_at'])
            ? new DateTimeImmutable($customer['last_purchase_at'])
            : null;
        $recency = $last ? max(0, (int) $last->diff($asOf)->format('%r%a')) : 9999;
        $frequency = max(0, (int) ($customer['purchase_count'] ?? 0));
        $monetary = max(0, (float) ($customer['total_spent'] ?? 0));

        $r = $recency <= 30 ? 5 : ($recency <= 60 ? 4 : ($recency <= 120 ? 3 : ($recency <= 240 ? 2 : 1)));
        $f = $frequency >= 20 ? 5 : ($frequency >= 10 ? 4 : ($frequency >= 5 ? 3 : ($frequency >= 2 ? 2 : 1)));
        $m = $monetary >= 2000 ? 5 : ($monetary >= 1000 ? 4 : ($monetary >= 500 ? 3 : ($monetary >= 100 ? 2 : 1)));

        return array(
            'recency_days' => $recency,
            'frequency' => $frequency,
            'monetary' => round($monetary, 2),
            'r_score' => $r,
            'f_score' => $f,
            'm_score' => $m,
            'rfm_score' => sprintf('%d%d%d', $r, $f, $m),
        );
    }
}

final class CrmSegmentEvaluator
{
    public static function matches(array $customer, array $rules)
    {
        foreach ($rules as $field => $expected) {
            $actual = $customer[$field] ?? null;
            if (is_array($expected)) {
                if (isset($expected['min']) && $actual < $expected['min']) return false;
                if (isset($expected['max']) && $actual > $expected['max']) return false;
                if (isset($expected['in']) && !in_array($actual, $expected['in'], true)) return false;
            } elseif ($actual != $expected) {
                return false;
            }
        }
        return true;
    }
}
