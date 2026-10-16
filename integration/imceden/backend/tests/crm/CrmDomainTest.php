<?php

require_once dirname(__DIR__, 2) . '/config/crm.php';
require_once dirname(__DIR__, 2) . '/api/crm/src/CrmDomain.php';

function crmAssert($condition, $message)
{
    if (!$condition) throw new RuntimeException($message);
}

putenv('CRM_ENABLED');
$localConfigFile = dirname(__DIR__, 2) . '/config/crm.local.php';
if (!is_file($localConfigFile)) {
    crmAssert(crmIsEnabled() === false, 'CRM debe estar apagado por defecto.');
} else {
    crmAssert(crmIsEnabled() === true, 'La activación local explícita debe respetarse.');
}

$config = crmConfig();
$config['tenant_id'] = 7;
$context = CrmAccess::sessionContext(array(
    'user' => 'demo',
    'idPerfil' => 1,
    'idAlmacen' => 2,
), $config);
crmAssert($context['tenant_id'] === 7, 'El tenant debe venir de la configuración.');
crmAssert(CrmAccess::canWrite($context, $config), 'El perfil administrador debe escribir.');
crmAssert(CrmAccess::validCsrf(array('login_csrf' => 'token-seguro'), 'token-seguro'), 'CSRF válido rechazado.');
crmAssert(!CrmAccess::validCsrf(array('login_csrf' => 'token-seguro'), 'otro-token'), 'CSRF inválido aceptado.');

$denied = false;
try {
    CrmAccess::sessionContext(array(), $config);
} catch (RuntimeException $e) {
    $denied = $e->getCode() === 401;
}
crmAssert($denied, 'Una sesión anónima debe rechazarse.');

crmAssert(CrmNormalizer::phone('+51 987-654-321') === '987654321', 'Teléfono inválido.');
crmAssert(CrmNormalizer::email(' Demo@Example.COM ') === 'demo@example.com', 'Correo inválido.');
crmAssert(CrmNormalizer::email('sin-correo') === null, 'Correo defectuoso no debe aceptarse.');

$rfm = CrmRfmCalculator::calculate(array(
    'last_purchase_at' => '2026-07-20',
    'purchase_count' => 12,
    'total_spent' => 1250,
), '2026-07-26');
crmAssert($rfm['rfm_score'] === '544', 'Cálculo RFM inesperado.');
crmAssert(CrmSegmentEvaluator::matches($rfm, array(
    'recency_days' => array('max' => 30),
    'frequency' => array('min' => 10),
)), 'El cliente debe pertenecer al segmento.');

echo "CrmDomainTest OK\n";
