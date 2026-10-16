<?php

final class CrmRepository
{
    private $db;
    private $tenantId;

    public function __construct(mysqli $db, $tenantId)
    {
        $this->db = $db;
        $this->tenantId = (int) $tenantId;
    }

    private function rows($sql, $types = '', array $params = array())
    {
        $stmt = $this->db->prepare($sql);
        if (!$stmt) throw new RuntimeException('No se pudo preparar la consulta CRM.');
        if ($types !== '') $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : array();
        $stmt->close();
        return $rows;
    }

    private function execute($sql, $types = '', array $params = array())
    {
        $stmt = $this->db->prepare($sql);
        if (!$stmt) throw new RuntimeException('No se pudo preparar la operación CRM.');
        if ($types !== '') $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected;
    }

    public function dashboard()
    {
        $sql = "SELECT
            (SELECT COUNT(*) FROM crm_customer WHERE tenant_id=? AND is_active=1) customers,
            (SELECT COUNT(*) FROM crm_task WHERE tenant_id=? AND status IN ('pending','in_progress')) open_tasks,
            (SELECT COUNT(*) FROM crm_campaign WHERE tenant_id=? AND status='active') active_campaigns,
            (SELECT COALESCE(SUM(balance),0) FROM crm_loyalty_account WHERE tenant_id=?) loyalty_points,
            (SELECT COUNT(*) FROM crm_conversation v
             WHERE v.tenant_id=? AND v.status<>'resolved'
               AND COALESCE((SELECT m.direction FROM crm_message m
                             WHERE m.tenant_id=v.tenant_id AND m.conversation_id=v.id
                             ORDER BY m.id DESC LIMIT 1),'inbound')='inbound') pending_conversations,
            (SELECT COUNT(*) FROM crm_conversation WHERE tenant_id=? AND sales_label='potential_buyer') potential_buyers,
            (SELECT COUNT(*) FROM crm_conversation WHERE tenant_id=? AND sales_label='sale_closed') closed_sales,
            (SELECT COUNT(*) FROM crm_customer WHERE tenant_id=? AND is_active=1 AND recency_days>90) at_risk_customers,
            (SELECT COUNT(*) FROM crm_customer WHERE tenant_id=? AND is_active=1 AND recency_days<=30) recent_customers,
            (SELECT COALESCE(SUM(total_spent),0) FROM crm_customer WHERE tenant_id=? AND is_active=1) tracked_revenue,
            (SELECT finished_at FROM crm_sync_run WHERE tenant_id=? AND status='completed' ORDER BY id DESC LIMIT 1) last_sync_at";
        $tenantIds = array_fill(0, 11, $this->tenantId);
        $rows = $this->rows($sql, str_repeat('i', count($tenantIds)), $tenantIds);
        return $rows[0] ?? array();
    }

    public function analyticsDashboard($from = '', $to = '')
    {
        $today = new DateTimeImmutable('today');
        $defaultFrom = $today->modify('-13 days');
        $fromDate = DateTimeImmutable::createFromFormat('!Y-m-d', (string) $from) ?: $defaultFrom;
        $toDate = DateTimeImmutable::createFromFormat('!Y-m-d', (string) $to) ?: $today;
        if ($fromDate > $toDate) throw new InvalidArgumentException('La fecha inicial no puede ser posterior a la fecha final.');
        $days = (int) $fromDate->diff($toDate)->days + 1;
        if ($days > 366) throw new InvalidArgumentException('El rango máximo permitido es de 366 días.');
        $fromSql = $fromDate->format('Y-m-d');
        $toSql = $toDate->modify('+1 day')->format('Y-m-d');

        $conversationRows = $this->rows(
            "SELECT
                COUNT(*) conversations,
                COALESCE(SUM(v.sales_label='potential_buyer'),0) potential_buyers,
                COALESCE(SUM(v.sales_label='sale_closed'),0) closed_sales,
                COALESCE(SUM(v.status<>'resolved' AND COALESCE(lm.direction,'inbound')='inbound'),0) pending_attention
             FROM crm_conversation v
             LEFT JOIN crm_message lm ON lm.id=(
                 SELECT MAX(last_message.id) FROM crm_message last_message
                 WHERE last_message.tenant_id=v.tenant_id AND last_message.conversation_id=v.id
             )
             WHERE v.tenant_id=? AND v.created_at>=? AND v.created_at<?",
            'iss', array($this->tenantId, $fromSql, $toSql)
        );
        $messageRows = $this->rows(
            "SELECT
                COUNT(*) messages_total,
                COALESCE(SUM(direction='inbound'),0) inbound_total,
                COALESCE(SUM(direction='outbound'),0) outbound_total,
                COUNT(*) messages_period
             FROM crm_message WHERE tenant_id=? AND created_at>=? AND created_at<?",
            'iss', array($this->tenantId, $fromSql, $toSql)
        );
        $customerRows = $this->rows(
            "SELECT COUNT(*) customers,COALESCE(SUM(total_spent),0) customer_revenue,
                    COALESCE(AVG(total_spent),0) average_customer_value
             FROM crm_customer WHERE tenant_id=? AND is_active=1",
            'i', array($this->tenantId)
        );
        $labels = $this->rows(
            "SELECT sales_label label,COUNT(*) total FROM crm_conversation
             WHERE tenant_id=? AND created_at>=? AND created_at<?
             GROUP BY sales_label ORDER BY total DESC",
            'iss', array($this->tenantId, $fromSql, $toSql)
        );
        $dailyRows = $this->rows(
            "SELECT DATE(created_at) day,
                    SUM(direction='inbound') inbound,
                    SUM(direction='outbound') outbound
             FROM crm_message
             WHERE tenant_id=? AND created_at>=? AND created_at<?
             GROUP BY DATE(created_at) ORDER BY day",
            'iss', array($this->tenantId, $fromSql, $toSql)
        );
        $taskRows = $this->rows(
            "SELECT status,COUNT(*) total FROM crm_task
             WHERE tenant_id=? AND created_at>=? AND created_at<? GROUP BY status",
            'iss', array($this->tenantId, $fromSql, $toSql)
        );
        $campaignRows = $this->rows(
            "SELECT status,COUNT(*) total FROM crm_campaign
             WHERE tenant_id=? AND created_at>=? AND created_at<? GROUP BY status",
            'iss', array($this->tenantId, $fromSql, $toSql)
        );

        $dailyByDate = array();
        foreach ($dailyRows as $row) $dailyByDate[$row['day']] = $row;
        $daily = array();
        for ($cursor = $fromDate; $cursor <= $toDate; $cursor = $cursor->modify('+1 day')) {
            $day = $cursor->format('Y-m-d');
            $row = $dailyByDate[$day] ?? array();
            $daily[] = array(
                'day' => $day,
                'label' => date('d/m', strtotime($day)),
                'inbound' => (int) ($row['inbound'] ?? 0),
                'outbound' => (int) ($row['outbound'] ?? 0),
            );
        }
        $summary = array_merge(
            $conversationRows[0] ?? array(),
            $messageRows[0] ?? array(),
            $customerRows[0] ?? array()
        );
        $potential = (int) ($summary['potential_buyers'] ?? 0);
        $closed = (int) ($summary['closed_sales'] ?? 0);
        $summary['conversion_rate'] = ($potential + $closed) > 0
            ? round(($closed * 100) / ($potential + $closed), 1)
            : 0;

        return array(
            'summary' => $summary,
            'labels' => $labels,
            'daily_messages' => $daily,
            'tasks' => $taskRows,
            'campaigns' => $campaignRows,
            'range' => array(
                'from' => $fromDate->format('Y-m-d'),
                'to' => $toDate->format('Y-m-d'),
                'days' => $days,
            ),
            'generated_at' => date('Y-m-d H:i:s'),
        );
    }

    public function customers($search = '', $limit = 50)
    {
        $search = '%' . trim($search) . '%';
        return $this->rows(
            "SELECT id, external_customer_id, display_name, document_number, phone, email,
                    last_purchase_at, purchase_count, total_spent, rfm_score, updated_at
             FROM crm_customer
             WHERE tenant_id=? AND is_active=1
               AND (?='%%' OR display_name LIKE ? OR document_number LIKE ? OR phone LIKE ?)
             ORDER BY COALESCE(last_purchase_at, created_at) DESC LIMIT ?",
            'issssi',
            array($this->tenantId, $search, $search, $search, $search, min(200, max(1, (int) $limit)))
        );
    }

    public function updateCustomer($customerId, array $input, $actor)
    {
        $customerId = (int) $customerId;
        $existing = $this->rows(
            'SELECT id FROM crm_customer WHERE tenant_id=? AND id=? AND is_active=1',
            'ii', array($this->tenantId, $customerId)
        );
        if (!$existing) throw new RuntimeException('CUSTOMER_NOT_FOUND', 404);
        $name = mb_substr(trim((string) ($input['display_name'] ?? '')), 0, 180);
        $document = mb_substr(preg_replace('/[^0-9A-Za-z-]/', '', trim((string) ($input['document_number'] ?? ''))), 0, 30);
        $phone = mb_substr(preg_replace('/[^0-9+]/', '', trim((string) ($input['phone'] ?? ''))), 0, 30);
        $email = mb_substr(strtolower(trim((string) ($input['email'] ?? ''))), 0, 180);
        if ($name === '') throw new InvalidArgumentException('El nombre del cliente es obligatorio.');
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('El correo electrónico no es válido.');
        }
        $stmt = $this->db->prepare(
            'UPDATE crm_customer SET display_name=?,document_number=NULLIF(?,\'\'),
                    phone=NULLIF(?,\'\'),email=NULLIF(?,\'\') WHERE tenant_id=? AND id=?'
        );
        $stmt->bind_param('ssssii', $name, $document, $phone, $email, $this->tenantId, $customerId);
        $stmt->execute();
        $stmt->close();
        $this->audit($actor, 'update', 'customer', (string) $customerId, array('display_name' => $name));
        return array('id' => $customerId, 'updated' => true);
    }

    public function importCustomers(array $rows, $rejected, $actor)
    {
        $created = 0;
        $updated = 0;
        $find = $this->db->prepare(
            "SELECT id FROM crm_customer WHERE tenant_id=? AND
             (phone=? OR (?<>'' AND document_number=?)) LIMIT 1"
        );
        $insert = $this->db->prepare(
            "INSERT INTO crm_customer
             (tenant_id,display_name,document_number,phone,email,source_updated_at)
             VALUES (?,?,?,?,?,NOW())"
        );
        $update = $this->db->prepare(
            "UPDATE crm_customer SET display_name=?,document_number=COALESCE(NULLIF(?,''),document_number),
             phone=?,email=COALESCE(NULLIF(?,''),email),source_updated_at=NOW(),is_active=1
             WHERE tenant_id=? AND id=?"
        );
        $this->db->begin_transaction();
        try {
            foreach ($rows as $row) {
                $document = (string) $row['document_number'];
                $phone = (string) $row['phone'];
                $name = (string) $row['display_name'];
                $email = (string) $row['email'];
                $find->bind_param('isss', $this->tenantId, $phone, $document, $document);
                $find->execute();
                $existing = $find->get_result()->fetch_assoc();
                if ($existing) {
                    $id = (int) $existing['id'];
                    $update->bind_param(
                        'ssssii', $name, $document, $phone,
                        $email, $this->tenantId, $id
                    );
                    $update->execute();
                    $updated++;
                } else {
                    $insert->bind_param(
                        'issss', $this->tenantId, $name, $document,
                        $phone, $email
                    );
                    $insert->execute();
                    $created++;
                }
            }
            $this->db->commit();
        } catch (Throwable $error) {
            $this->db->rollback();
            throw $error;
        } finally {
            $find->close();
            $insert->close();
            $update->close();
        }
        $summary = array('created' => $created, 'updated' => $updated, 'rejected' => (int) $rejected);
        $this->audit($actor, 'import', 'customer', 'batch', $summary);
        return $summary;
    }

    public function customer360($id)
    {
        $rows = $this->rows(
            "SELECT c.id,c.external_customer_id,c.display_name,c.document_number,c.phone,c.email,c.last_purchase_at,
                    c.purchase_count,c.total_spent,c.recency_days,c.rfm_score,
                    COALESCE(s.status,'unknown') whatsapp_consent
             FROM crm_customer c LEFT JOIN crm_consent s
               ON s.tenant_id=c.tenant_id AND s.customer_id=c.id AND s.channel='whatsapp'
             WHERE c.tenant_id=? AND c.id=? AND c.is_active=1",
            'ii', array($this->tenantId, (int) $id)
        );
        if (!$rows) throw new RuntimeException('CUSTOMER_NOT_FOUND', 404);
        $customer = $rows[0];
        $sourceId = (int) $customer['external_customer_id'];
        $purchases = $this->rows(
            "SELECT v.idDocVenta id, v.FechaDoc purchase_at,
                    CONCAT(COALESCE(v.Serie,''),'-',COALESCE(v.Numero,'')) document,
                    ROUND(COALESCE(SUM(d.Cantidad*d.Precio-COALESCE(d.Descuento,0)),0),2) total
             FROM Ve_DocVenta v LEFT JOIN Ve_DocVentaDet d ON d.IdDocVenta=v.idDocVenta
             WHERE v.IdCliente=? AND (v.Anulado=0 OR v.Anulado IS NULL)
             GROUP BY v.idDocVenta,v.FechaDoc,v.Serie,v.Numero ORDER BY v.FechaDoc DESC LIMIT 20",
            'i', array($sourceId)
        );
        $favorites = $this->rows(
            "SELECT p.IdProducto id,p.Producto product,ROUND(SUM(d.Cantidad),2) quantity,
                    ROUND(SUM(d.Cantidad*d.Precio-COALESCE(d.Descuento,0)),2) amount
             FROM Ve_DocVenta v JOIN Ve_DocVentaDet d ON d.IdDocVenta=v.idDocVenta
             JOIN Gen_Producto p ON p.IdProducto=d.IdProducto
             WHERE v.IdCliente=? AND (v.Anulado=0 OR v.Anulado IS NULL)
             GROUP BY p.IdProducto,p.Producto ORDER BY quantity DESC,amount DESC LIMIT 10",
            'i', array($sourceId)
        );
        $customer['average_ticket'] = $customer['purchase_count'] > 0
            ? round($customer['total_spent'] / $customer['purchase_count'], 2) : 0;
        $customer['recommended_action'] = $customer['purchase_count'] < 2
            ? 'Enviar bienvenida y beneficio para segunda visita'
            : ((int) $customer['recency_days'] > 30
                ? 'Campaña de reactivación'
                : 'Ofrecer su producto favorito');
        return array('customer' => $customer, 'purchases' => $purchases, 'favorites' => $favorites);
    }

    public function tasks($limit = 100)
    {
        return $this->rows(
            "SELECT t.id, t.title, t.description, t.status, t.priority, t.due_at,
                    t.assigned_user, t.customer_id, c.display_name customer_name
             FROM crm_task t LEFT JOIN crm_customer c
               ON c.id=t.customer_id AND c.tenant_id=t.tenant_id
             WHERE t.tenant_id=? ORDER BY FIELD(t.status,'pending','in_progress','done','cancelled'),
                    t.due_at IS NULL, t.due_at LIMIT ?",
            'ii',
            array($this->tenantId, min(200, max(1, (int) $limit)))
        );
    }

    public function campaigns()
    {
        return $this->rows(
            "SELECT id, name, channel, status, content_json, starts_at, ends_at, sent_count, success_count
             FROM crm_campaign WHERE tenant_id=? ORDER BY created_at DESC LIMIT 100",
            'i',
            array($this->tenantId)
        );
    }

    public function campaignAudience(array $input)
    {
        $segmentId = (int) ($input['segment_id'] ?? 0);
        if ($segmentId > 0) {
            $exists = $this->rows('SELECT id FROM crm_segment WHERE tenant_id=? AND id=? AND is_active=1', 'ii', array($this->tenantId, $segmentId));
            if (!$exists) throw new RuntimeException('SEGMENT_NOT_FOUND', 404);
            $customers = $this->rows(
                "SELECT c.id,c.display_name,c.phone,c.purchase_count,c.total_spent,c.recency_days,c.last_purchase_at,
                        COALESCE(s.status,'unknown') consent_status
                 FROM crm_segment_member m JOIN crm_customer c ON c.tenant_id=m.tenant_id AND c.id=m.customer_id
                 LEFT JOIN crm_consent s ON s.tenant_id=c.tenant_id AND s.customer_id=c.id AND s.channel='whatsapp'
                 WHERE m.tenant_id=? AND m.segment_id=? AND c.is_active=1
                 ORDER BY c.total_spent DESC,c.last_purchase_at DESC LIMIT 1000",
                'ii', array($this->tenantId, $segmentId)
            );
        } else {
            $rules = $this->normalizeSegmentRules($input['rules'] ?? array());
            $params = array();
            $types = '';
            $where = $this->segmentWhere($rules, $params, $types);
            $where = str_replace(array('tenant_id=?', 'is_active=1'), array('c.tenant_id=?', 'c.is_active=1'), $where);
            $customers = $this->rows(
                "SELECT c.id,c.display_name,c.phone,c.purchase_count,c.total_spent,c.recency_days,c.last_purchase_at,
                        COALESCE(s.status,'unknown') consent_status
                 FROM crm_customer c
                 LEFT JOIN crm_consent s ON s.tenant_id=c.tenant_id AND s.customer_id=c.id AND s.channel='whatsapp'
                 WHERE $where ORDER BY c.total_spent DESC,c.last_purchase_at DESC LIMIT 1000",
                $types, $params
            );
        }
        return array(
            'total' => count($customers),
            'with_phone' => count(array_filter($customers, function ($row) { return trim((string) $row['phone']) !== ''; })),
            'authorized' => count(array_filter($customers, function ($row) { return $row['consent_status'] === 'granted'; })),
            'customers' => $customers,
        );
    }

    public function segments()
    {
        return $this->rows(
            "SELECT id, name, description, rules_json, member_count, refreshed_at
             FROM crm_segment WHERE tenant_id=? AND is_active=1 ORDER BY name",
            'i',
            array($this->tenantId)
        );
    }

    public function syncStatus()
    {
        $history = $this->rows(
            "SELECT source_name, status, cursor_value, rows_processed, started_at, finished_at, error_message
             FROM crm_sync_run WHERE tenant_id=? ORDER BY id DESC LIMIT 20",
            'i',
            array($this->tenantId)
        );
        $crmCount = $this->rows(
            'SELECT COUNT(*) total FROM crm_customer WHERE tenant_id=? AND is_active=1',
            'i', array($this->tenantId)
        );
        $sourceCount = $this->rows(
            'SELECT COUNT(*) total FROM Ve_DocVentaCliente WHERE IdCliente>0 AND (Anulado=0 OR Anulado IS NULL)'
        );
        return array(
            'source_name' => 'Clientes del punto de venta',
            'source_total' => (int) ($sourceCount[0]['total'] ?? 0),
            'crm_total' => (int) ($crmCount[0]['total'] ?? 0),
            'last_run' => $history[0] ?? null,
            'history' => $history,
        );
    }

    public function startSyncRun($sourceName)
    {
        $status = 'running';
        $stmt = $this->db->prepare(
            'INSERT INTO crm_sync_run (tenant_id,source_name,status) VALUES (?,?,?)'
        );
        $stmt->bind_param('iss', $this->tenantId, $sourceName, $status);
        $stmt->execute();
        $id = (int) $stmt->insert_id;
        $stmt->close();
        return $id;
    }

    public function finishSyncRun($id, $status, $processed, $error = null)
    {
        if (!in_array($status, array('completed','failed'), true)) {
            throw new InvalidArgumentException('Estado de sincronización inválido.');
        }
        $stmt = $this->db->prepare(
            'UPDATE crm_sync_run SET status=?,rows_processed=?,error_message=?,finished_at=NOW()
             WHERE tenant_id=? AND id=?'
        );
        $runId = (int) $id;
        $processed = max(0, (int) $processed);
        $stmt->bind_param('sisii', $status, $processed, $error, $this->tenantId, $runId);
        $stmt->execute();
        $stmt->close();
        return array('id' => $runId, 'status' => $status, 'processed' => $processed);
    }

    public function conversations($limit = 100)
    {
        return $this->rows(
            "SELECT v.id,v.channel,v.status,v.sales_label,v.assigned_user,v.unread_count,v.last_message_at,
                    COALESCE(NULLIF(c.display_name,''),NULLIF(v.contact_name,''),'Contacto WhatsApp') customer_name,
                    COALESCE(NULLIF(c.phone,''),NULLIF(v.contact_phone,''),'Teléfono no disponible') phone,
                    COALESCE((SELECT m.direction FROM crm_message m
                              WHERE m.tenant_id=v.tenant_id AND m.conversation_id=v.id
                              ORDER BY m.id DESC LIMIT 1),'inbound') last_direction,
                    COALESCE((SELECT m.sender_type FROM crm_message m
                              WHERE m.tenant_id=v.tenant_id AND m.conversation_id=v.id
                              ORDER BY m.id DESC LIMIT 1),'customer') last_sender_type
             FROM crm_conversation v LEFT JOIN crm_customer c
               ON c.id=v.customer_id AND c.tenant_id=v.tenant_id
             WHERE v.tenant_id=? ORDER BY v.last_message_at DESC,v.id DESC LIMIT ?",
            'ii',
            array($this->tenantId, min(200, max(1, (int) $limit)))
        );
    }

    public function messages($conversationId, $limit = 100)
    {
        return $this->rows(
            "SELECT id,direction,sender_type,body,status,sent_at,created_at
             FROM crm_message WHERE tenant_id=? AND conversation_id=?
             ORDER BY id DESC LIMIT ?",
            'iii',
            array($this->tenantId, (int) $conversationId, min(200, max(1, (int) $limit)))
        );
    }

    public function messageTemplates()
    {
        return $this->rows(
            "SELECT id,name,channel,subject,body,is_active,updated_at
             FROM crm_message_template WHERE tenant_id=? ORDER BY is_active DESC,name",
            'i', array($this->tenantId)
        );
    }

    public function botRules()
    {
        return $this->rows(
            "SELECT id,name,trigger_text,response_text,priority,is_active,handoff,updated_at
             FROM crm_bot_rule WHERE tenant_id=? ORDER BY priority,id",
            'i', array($this->tenantId)
        );
    }

    public function outbox()
    {
        return $this->rows(
            "SELECT o.id,o.campaign_id,c.display_name customer_name,o.channel,o.destination,
                    o.status,o.attempts,o.available_at,o.sent_at,o.last_error
             FROM crm_message_outbox o JOIN crm_customer c
               ON c.id=o.customer_id AND c.tenant_id=o.tenant_id
             WHERE o.tenant_id=? ORDER BY o.id DESC LIMIT 200",
            'i', array($this->tenantId)
        );
    }

    private function audit($actor, $action, $entityType, $entityId, array $after)
    {
        $stmt = $this->db->prepare(
            'INSERT INTO crm_audit_log (tenant_id,actor,action,entity_type,entity_id,after_json)
             VALUES (?,?,?,?,?,?)'
        );
        $json = json_encode($after, JSON_UNESCAPED_UNICODE);
        $stmt->bind_param('isssss', $this->tenantId, $actor, $action, $entityType, $entityId, $json);
        $stmt->execute();
        $stmt->close();
    }

    public function recordAiSettingsUpdate($actor, array $settings)
    {
        $this->audit($actor, 'update', 'ai_settings', 'tenant', array(
            'enabled' => !empty($settings['ai_enabled']),
            'provider' => (string) ($settings['ai_provider'] ?? 'simulation'),
            'model' => (string) ($settings['ai_model'] ?? ''),
            'key_configured' => !empty($settings['ai_api_key']),
            'auto_reply_enabled' => !empty($settings['ai_auto_reply_enabled']),
        ));
    }

    public function receiveWhatsAppMessage($externalChatId, $externalMessageId, $phone, $body, $displayName = '')
    {
        $phone = preg_replace('/\D+/', '', (string) $phone);
        $phone = strlen($phone) > 9 ? substr($phone, -9) : $phone;
        $displayName = trim(mb_substr((string) $displayName, 0, 160));
        $customerId = null;
        $stmt = $this->db->prepare(
            "SELECT id FROM crm_customer
             WHERE tenant_id=? AND RIGHT(REPLACE(REPLACE(REPLACE(phone,' ',''),'-',''),'+',''),9)=?
             LIMIT 1"
        );
        $stmt->bind_param('is', $this->tenantId, $phone);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($result) $customerId = (int) $result['id'];

        $conversation = $this->rows(
            "SELECT id FROM crm_conversation
             WHERE tenant_id=? AND channel='whatsapp' AND external_chat_id=? LIMIT 1",
            'is', array($this->tenantId, $externalChatId)
        );
        if (!$conversation && $customerId) {
            $conversation = $this->rows(
                "SELECT id FROM crm_conversation
                 WHERE tenant_id=? AND channel='whatsapp' AND customer_id=?
                 ORDER BY last_message_at DESC,id DESC LIMIT 1",
                'ii', array($this->tenantId, $customerId)
            );
        }

        if ($conversation) {
            $conversationId = (int) $conversation[0]['id'];
            $stmt = $this->db->prepare(
                "UPDATE crm_conversation SET customer_id=COALESCE(customer_id,?),external_chat_id=?,
                   contact_name=COALESCE(NULLIF(?,''),contact_name),
                   contact_phone=COALESCE(NULLIF(?,''),contact_phone),
                   unread_count=unread_count+1,last_message_at=NOW(),status='open'
                 WHERE tenant_id=? AND id=?"
            );
            $stmt->bind_param(
                'isssii', $customerId, $externalChatId, $displayName, $phone,
                $this->tenantId, $conversationId
            );
            $stmt->execute();
            $stmt->close();
        } else {
            $stmt = $this->db->prepare(
                "INSERT INTO crm_conversation
                 (tenant_id,customer_id,channel,external_chat_id,contact_name,contact_phone,status,unread_count,last_message_at)
                 VALUES (?,?,'whatsapp',?,?,?,'open',1,NOW())"
            );
            $stmt->bind_param(
                'iisss', $this->tenantId, $customerId, $externalChatId, $displayName, $phone
            );
            $stmt->execute();
            $conversationId = (int) $stmt->insert_id;
            $stmt->close();
        }

        $stmt = $this->db->prepare(
            "INSERT IGNORE INTO crm_message
             (tenant_id,conversation_id,customer_id,direction,sender_type,body,status,external_message_id)
             VALUES (?,?,?,'inbound','customer',?,'received',?)"
        );
        $stmt->bind_param('iiiss', $this->tenantId, $conversationId, $customerId, $body, $externalMessageId);
        $stmt->execute();
        $created = $stmt->affected_rows > 0;
        $stmt->close();
        return array('created' => $created, 'conversation_id' => $conversationId, 'customer_id' => $customerId);
    }

    public function recordBotReply($conversationId, $customerId, $body, $externalMessageId)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO crm_message
             (tenant_id,conversation_id,customer_id,direction,sender_type,body,status,external_message_id,sent_at)
             VALUES (?,?,?,'outbound','bot',?,'sent',?,NOW())"
        );
        $stmt->bind_param('iiiss', $this->tenantId, $conversationId, $customerId, $body, $externalMessageId);
        $stmt->execute();
        $stmt->close();
        $this->markConversationAnswered($conversationId, 'waiting');
    }

    public function conversationForReply($conversationId)
    {
        $rows = $this->rows(
            "SELECT id,customer_id,external_chat_id FROM crm_conversation
             WHERE tenant_id=? AND id=? AND channel='whatsapp' LIMIT 1",
            'ii', array($this->tenantId, (int) $conversationId)
        );
        if (!$rows) throw new RuntimeException('CONVERSATION_NOT_FOUND', 404);
        return $rows[0];
    }

    public function recordAgentReply($conversationId, $customerId, $body, $externalMessageId, $actor)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO crm_message
             (tenant_id,conversation_id,customer_id,direction,sender_type,body,status,external_message_id,sent_at)
             VALUES (?,?,?,'outbound','agent',?,'sent',?,NOW())"
        );
        $stmt->bind_param('iiiss', $this->tenantId, $conversationId, $customerId, $body, $externalMessageId);
        $stmt->execute();
        $messageId = (int) $stmt->insert_id;
        $stmt->close();
        $this->markConversationAnswered($conversationId, 'waiting');
        $this->audit($actor, 'send', 'conversation', (string) $conversationId, array('message_id' => $messageId));
        return array('sent' => true, 'message_id' => $messageId);
    }

    public function markConversationRead($conversationId)
    {
        $stmt = $this->db->prepare(
            'UPDATE crm_conversation SET unread_count=0 WHERE tenant_id=? AND id=?'
        );
        $stmt->bind_param('ii', $this->tenantId, $conversationId);
        $stmt->execute();
        $stmt->close();
        return array('read' => true);
    }

    public function updateConversationLabel($conversationId, $label, $actor)
    {
        $allowed = array('new', 'potential_buyer', 'follow_up', 'sale_closed', 'not_interested');
        $label = strtolower(trim((string) $label));
        if (!in_array($label, $allowed, true)) {
            throw new InvalidArgumentException('Etiqueta comercial no válida.');
        }

        $conversationId = (int) $conversationId;
        $stmt = $this->db->prepare(
            'UPDATE crm_conversation SET sales_label=? WHERE tenant_id=? AND id=?'
        );
        $stmt->bind_param('sii', $label, $this->tenantId, $conversationId);
        $stmt->execute();
        $updated = $stmt->affected_rows;
        $stmt->close();
        if (!$updated) {
            $rows = $this->rows(
                'SELECT id FROM crm_conversation WHERE tenant_id=? AND id=?',
                'ii', array($this->tenantId, $conversationId)
            );
            if (!$rows) throw new RuntimeException('CONVERSATION_NOT_FOUND', 404);
        }
        $this->audit($actor, 'label', 'conversation', (string) $conversationId, array('sales_label' => $label));
        return array('id' => $conversationId, 'sales_label' => $label);
    }

    public function deleteConversation($conversationId, $actor)
    {
        $conversationId = (int) $conversationId;
        $rows = $this->rows(
            'SELECT id,channel,external_chat_id,status FROM crm_conversation
             WHERE tenant_id=? AND id=?',
            'ii', array($this->tenantId, $conversationId)
        );
        if (!$rows) throw new RuntimeException('CONVERSATION_NOT_FOUND', 404);

        $this->db->begin_transaction();
        try {
            $deletedMessages = $this->execute(
                'DELETE FROM crm_message WHERE tenant_id=? AND conversation_id=?',
                'ii', array($this->tenantId, $conversationId)
            );
            $this->execute(
                'DELETE FROM crm_conversation WHERE tenant_id=? AND id=?',
                'ii', array($this->tenantId, $conversationId)
            );
            $this->audit($actor, 'delete', 'conversation', (string) $conversationId, array(
                'conversation' => $rows[0], 'deleted_messages' => $deletedMessages,
            ));
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollback();
            throw $e;
        }
        return array(
            'id' => $conversationId,
            'deleted' => true,
            'deleted_messages' => $deletedMessages,
        );
    }

    private function markConversationAnswered($conversationId, $status)
    {
        $stmt = $this->db->prepare(
            'UPDATE crm_conversation SET status=?,unread_count=0,last_message_at=NOW() WHERE tenant_id=? AND id=?'
        );
        $stmt->bind_param('sii', $status, $this->tenantId, $conversationId);
        $stmt->execute();
        $stmt->close();
    }

    public function conversationChatIds()
    {
        $rows = $this->rows(
            "SELECT external_chat_id FROM crm_conversation
             WHERE tenant_id=? AND channel='whatsapp' AND external_chat_id IS NOT NULL
             ORDER BY id DESC LIMIT 200",
            'i', array($this->tenantId)
        );
        return array_column($rows, 'external_chat_id');
    }

    public function updateConversationContacts(array $contacts)
    {
        $updated = 0;
        $stmt = $this->db->prepare(
            "UPDATE crm_conversation SET
               contact_name=COALESCE(NULLIF(?,''),contact_name),
               contact_phone=COALESCE(NULLIF(?,''),contact_phone)
             WHERE tenant_id=? AND external_chat_id=?"
        );
        foreach ($contacts as $contact) {
            $chatId = trim((string) ($contact['chat_id'] ?? ''));
            if (!preg_match('/^[a-zA-Z0-9:_-]+@(?:c\.us|lid)$/', $chatId)) continue;
            $name = trim(mb_substr((string) ($contact['display_name'] ?? ''), 0, 160));
            $phone = preg_replace('/\D+/', '', (string) ($contact['phone'] ?? ''));
            $phone = strlen($phone) > 9 ? substr($phone, -9) : $phone;
            $stmt->bind_param('ssis', $name, $phone, $this->tenantId, $chatId);
            $stmt->execute();
            $updated += $stmt->affected_rows;
        }
        $stmt->close();
        return array('updated' => $updated);
    }

    public function createTask(array $input, $actor)
    {
        $title = trim((string) ($input['title'] ?? ''));
        if ($title === '') throw new InvalidArgumentException('El título es obligatorio.');
        $description = trim((string) ($input['description'] ?? ''));
        $priority = in_array($input['priority'] ?? '', array('low','normal','high'), true)
            ? $input['priority'] : 'normal';
        $dueAt = !empty($input['due_at']) ? str_replace('T', ' ', (string) $input['due_at']) : null;
        $customerId = !empty($input['customer_id']) ? (int) $input['customer_id'] : null;
        $assignedUser = mb_substr(trim((string) ($input['assigned_user'] ?? '')), 0, 100);
        if ($assignedUser === '') $assignedUser = null;
        $stmt = $this->db->prepare(
            "INSERT INTO crm_task
             (tenant_id,customer_id,title,description,status,priority,assigned_user,due_at,created_by)
             VALUES (?,?,?,?,'pending',?,?,?,?)"
        );
        $stmt->bind_param('iissssss', $this->tenantId, $customerId, $title, $description, $priority, $assignedUser, $dueAt, $actor);
        $stmt->execute();
        $id = (string) $stmt->insert_id;
        $stmt->close();
        $this->audit($actor, 'create', 'task', $id, array('title' => $title));
        return array('id' => (int) $id);
    }

    public function updateTask($id, array $input, $actor)
    {
        $taskId = (int) $id;
        $current = $this->rows(
            'SELECT * FROM crm_task WHERE tenant_id=? AND id=?',
            'ii', array($this->tenantId, $taskId)
        );
        if (!$current) throw new RuntimeException('TASK_NOT_FOUND', 404);
        $task = $current[0];
        $status = $input['status'] ?? $task['status'];
        if (!in_array($status, array('pending','in_progress','done','cancelled'), true)) {
            throw new InvalidArgumentException('Estado inválido.');
        }
        $title = trim((string) ($input['title'] ?? $task['title']));
        if ($title === '') throw new InvalidArgumentException('El título es obligatorio.');
        $description = trim((string) ($input['description'] ?? $task['description']));
        $priority = in_array($input['priority'] ?? $task['priority'], array('low','normal','high'), true)
            ? ($input['priority'] ?? $task['priority']) : 'normal';
        $customerId = array_key_exists('customer_id', $input)
            ? (!empty($input['customer_id']) ? (int) $input['customer_id'] : null)
            : $task['customer_id'];
        $assignedUser = array_key_exists('assigned_user', $input)
            ? mb_substr(trim((string) $input['assigned_user']), 0, 100)
            : $task['assigned_user'];
        if ($assignedUser === '') $assignedUser = null;
        $dueAt = array_key_exists('due_at', $input)
            ? (!empty($input['due_at']) ? str_replace('T', ' ', (string) $input['due_at']) : null)
            : $task['due_at'];
        $completedAt = $status === 'done' ? date('Y-m-d H:i:s') : null;
        $stmt = $this->db->prepare(
            'UPDATE crm_task SET customer_id=?,title=?,description=?,status=?,priority=?,
             assigned_user=?,due_at=?,completed_at=? WHERE tenant_id=? AND id=?'
        );
        $stmt->bind_param(
            'isssssssii',
            $customerId, $title, $description, $status, $priority,
            $assignedUser, $dueAt, $completedAt, $this->tenantId, $taskId
        );
        $stmt->execute();
        $stmt->close();
        $this->audit($actor, 'update', 'task', (string) $taskId, array(
            'title' => $title, 'status' => $status, 'priority' => $priority,
        ));
        return array('updated' => true);
    }

    public function deleteTask($id, $actor)
    {
        $taskId = (int) $id;
        $task = $this->rows(
            'SELECT id,title,status FROM crm_task WHERE tenant_id=? AND id=?',
            'ii', array($this->tenantId, $taskId)
        );
        if (!$task) throw new RuntimeException('TASK_NOT_FOUND', 404);
        $this->execute(
            'DELETE FROM crm_task WHERE tenant_id=? AND id=?',
            'ii', array($this->tenantId, $taskId)
        );
        $this->audit($actor, 'delete', 'task', (string) $taskId, $task[0]);
        return array('id' => $taskId, 'deleted' => true);
    }

    public function createSegment(array $input, $actor)
    {
        $name = trim((string) ($input['name'] ?? ''));
        if ($name === '') throw new InvalidArgumentException('El nombre es obligatorio.');
        $description = trim((string) ($input['description'] ?? ''));
        $rules = $this->normalizeSegmentRules($input['rules'] ?? array());
        $rulesJson = json_encode($rules, JSON_UNESCAPED_UNICODE);
        $stmt = $this->db->prepare(
            'INSERT INTO crm_segment (tenant_id,name,description,rules_json) VALUES (?,?,?,?)'
        );
        $stmt->bind_param('isss', $this->tenantId, $name, $description, $rulesJson);
        $stmt->execute();
        $id = (string) $stmt->insert_id;
        $stmt->close();
        $members = $this->refreshSegmentMembers((int) $id, $rules);
        $this->audit($actor, 'create', 'segment', $id, array('name' => $name));
        return array('id' => (int) $id, 'member_count' => $members);
    }

    public function previewSegment(array $input)
    {
        $rules = $this->normalizeSegmentRules($input['rules'] ?? array());
        $customers = $this->segmentCustomers($rules, 20);
        return array(
            'member_count' => $this->segmentCustomerCount($rules),
            'customers' => $customers,
            'rules' => $rules,
        );
    }

    public function updateSegment($segmentId, array $input, $actor)
    {
        $segmentId = (int) $segmentId;
        $existing = $this->rows(
            'SELECT id,name FROM crm_segment WHERE tenant_id=? AND id=? AND is_active=1',
            'ii', array($this->tenantId, $segmentId)
        );
        if (!$existing) throw new RuntimeException('SEGMENT_NOT_FOUND', 404);
        $name = trim((string) ($input['name'] ?? ''));
        if ($name === '') throw new InvalidArgumentException('El nombre es obligatorio.');
        $description = mb_substr(trim((string) ($input['description'] ?? '')), 0, 500);
        $rules = $this->normalizeSegmentRules($input['rules'] ?? array());
        $rulesJson = json_encode($rules, JSON_UNESCAPED_UNICODE);
        $this->execute(
            'UPDATE crm_segment SET name=?,description=?,rules_json=? WHERE tenant_id=? AND id=?',
            'sssii', array($name, $description, $rulesJson, $this->tenantId, $segmentId)
        );
        $members = $this->refreshSegmentMembers($segmentId, $rules);
        $this->audit($actor, 'update', 'segment', (string) $segmentId, array('name' => $name, 'member_count' => $members));
        return array('id' => $segmentId, 'member_count' => $members);
    }

    public function refreshSegment($segmentId, $actor)
    {
        $segmentId = (int) $segmentId;
        $rows = $this->rows(
            'SELECT rules_json FROM crm_segment WHERE tenant_id=? AND id=? AND is_active=1',
            'ii', array($this->tenantId, $segmentId)
        );
        if (!$rows) throw new RuntimeException('SEGMENT_NOT_FOUND', 404);
        $rules = $this->normalizeSegmentRules(json_decode((string) $rows[0]['rules_json'], true) ?: array());
        $members = $this->refreshSegmentMembers($segmentId, $rules);
        $this->audit($actor, 'refresh', 'segment', (string) $segmentId, array('member_count' => $members));
        return array('id' => $segmentId, 'member_count' => $members, 'refreshed_at' => date('Y-m-d H:i:s'));
    }

    public function deleteSegment($segmentId, $actor)
    {
        $segmentId = (int) $segmentId;
        $existing = $this->rows(
            'SELECT id,name FROM crm_segment WHERE tenant_id=? AND id=? AND is_active=1',
            'ii', array($this->tenantId, $segmentId)
        );
        if (!$existing) throw new RuntimeException('SEGMENT_NOT_FOUND', 404);
        $this->db->begin_transaction();
        try {
            $this->execute('DELETE FROM crm_segment_member WHERE tenant_id=? AND segment_id=?', 'ii', array($this->tenantId, $segmentId));
            $this->execute('UPDATE crm_segment SET is_active=0,member_count=0 WHERE tenant_id=? AND id=?', 'ii', array($this->tenantId, $segmentId));
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollback();
            throw $e;
        }
        $this->audit($actor, 'delete', 'segment', (string) $segmentId, $existing[0]);
        return array('id' => $segmentId, 'deleted' => true);
    }

    private function normalizeSegmentRules($input)
    {
        if (!is_array($input)) $input = array();
        $definitions = array(
            'frequency' => array('min' => 0, 'max' => 100000),
            'recency_days' => array('min' => 0, 'max' => 36500),
            'monetary' => array('min' => 0, 'max' => 999999999),
        );
        $rules = array();
        foreach ($definitions as $field => $bounds) {
            $candidate = isset($input[$field]) && is_array($input[$field]) ? $input[$field] : array();
            $rule = array();
            foreach (array('min', 'max') as $operator) {
                if (!isset($candidate[$operator]) || $candidate[$operator] === '') continue;
                $value = (float) $candidate[$operator];
                $value = max($bounds['min'], min($bounds['max'], $value));
                $rule[$operator] = $field === 'monetary' ? round($value, 2) : (int) $value;
            }
            if (isset($rule['min'], $rule['max']) && $rule['min'] > $rule['max']) {
                throw new InvalidArgumentException('El valor mínimo no puede ser mayor que el máximo.');
            }
            if ($rule) $rules[$field] = $rule;
        }
        if (!$rules) throw new InvalidArgumentException('Configure por lo menos una regla del segmento.');
        return $rules;
    }

    private function segmentWhere(array $rules, array &$params, &$types)
    {
        $map = array(
            'frequency' => 'purchase_count',
            'recency_days' => 'COALESCE(recency_days,99999)',
            'monetary' => 'total_spent',
        );
        $conditions = array('tenant_id=?', 'is_active=1');
        $params = array($this->tenantId);
        $types = 'i';
        foreach ($rules as $field => $rule) {
            if (!isset($map[$field])) continue;
            foreach (array('min' => '>=', 'max' => '<=') as $operator => $sqlOperator) {
                if (!isset($rule[$operator])) continue;
                $conditions[] = $map[$field] . $sqlOperator . '?';
                $params[] = $rule[$operator];
                $types .= $field === 'monetary' ? 'd' : 'i';
            }
        }
        return implode(' AND ', $conditions);
    }

    private function segmentCustomers(array $rules, $limit)
    {
        $params = array();
        $types = '';
        $where = $this->segmentWhere($rules, $params, $types);
        $params[] = min(10000, max(1, (int) $limit));
        $types .= 'i';
        return $this->rows(
            "SELECT id,display_name,phone,purchase_count,total_spent,recency_days,rfm_score
             FROM crm_customer WHERE $where
             ORDER BY total_spent DESC,last_purchase_at DESC LIMIT ?",
            $types, $params
        );
    }

    private function segmentCustomerCount(array $rules)
    {
        $params = array();
        $types = '';
        $where = $this->segmentWhere($rules, $params, $types);
        $rows = $this->rows("SELECT COUNT(*) total FROM crm_customer WHERE $where", $types, $params);
        return (int) ($rows[0]['total'] ?? 0);
    }

    private function refreshSegmentMembers($segmentId, array $rules)
    {
        $customers = $this->segmentCustomers($rules, 10000);
        $count = $this->segmentCustomerCount($rules);
        $this->db->begin_transaction();
        try {
            $this->execute('DELETE FROM crm_segment_member WHERE tenant_id=? AND segment_id=?', 'ii', array($this->tenantId, $segmentId));
            $stmt = $this->db->prepare(
                'INSERT INTO crm_segment_member (tenant_id,segment_id,customer_id) VALUES (?,?,?)'
            );
            foreach ($customers as $customer) {
                $customerId = (int) $customer['id'];
                $stmt->bind_param('iii', $this->tenantId, $segmentId, $customerId);
                $stmt->execute();
            }
            $stmt->close();
            $this->execute(
                'UPDATE crm_segment SET member_count=?,refreshed_at=NOW() WHERE tenant_id=? AND id=?',
                'iii', array($count, $this->tenantId, $segmentId)
            );
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollback();
            throw $e;
        }
        return $count;
    }

    public function createCampaign(array $input, $actor)
    {
        $name = trim((string) ($input['name'] ?? ''));
        if ($name === '') throw new InvalidArgumentException('El nombre es obligatorio.');
        $channel = in_array($input['channel'] ?? '', array('manual','email','whatsapp','sms'), true)
            ? $input['channel'] : 'manual';
        $segmentId = !empty($input['segment_id']) ? (int) $input['segment_id'] : null;
        $mediaToken = trim((string) ($input['media_token'] ?? ''));
        if ($mediaToken !== '' && !preg_match('/^[a-f0-9]{40}$/', $mediaToken)) {
            throw new InvalidArgumentException('El archivo adjunto no es válido.');
        }
        $mediaType = in_array($input['media_type'] ?? '', array('image','video'), true)
            ? $input['media_type'] : '';
        $content = json_encode(array(
            'message' => trim((string) ($input['message'] ?? '')),
            'media_token' => $mediaToken,
            'media_type' => $mediaType,
            'media_name' => mb_substr(trim((string) ($input['media_name'] ?? '')), 0, 180),
            'media_size' => max(0, (int) ($input['media_size'] ?? 0)),
        ), JSON_UNESCAPED_UNICODE);
        $stmt = $this->db->prepare(
            "INSERT INTO crm_campaign
             (tenant_id,segment_id,name,channel,status,content_json,created_by)
             VALUES (?,?,?,?,'draft',?,?)"
        );
        $stmt->bind_param('iissss', $this->tenantId, $segmentId, $name, $channel, $content, $actor);
        $stmt->execute();
        $id = (string) $stmt->insert_id;
        $stmt->close();
        $this->audit($actor, 'create', 'campaign', $id, array('name' => $name, 'status' => 'draft'));
        return array('id' => (int) $id, 'status' => 'draft');
    }

    public function deleteCampaign($campaignId, $actor)
    {
        $campaignId = (int) $campaignId;
        if ($campaignId < 1) throw new InvalidArgumentException('Campaña inválida.');
        $campaign = $this->rows(
            'SELECT id,name,status FROM crm_campaign WHERE tenant_id=? AND id=?',
            'ii', array($this->tenantId, $campaignId)
        );
        if (!$campaign) throw new RuntimeException('CAMPAIGN_NOT_FOUND', 404);

        $this->db->begin_transaction();
        try {
            $this->execute(
                'DELETE FROM crm_message_outbox WHERE tenant_id=? AND campaign_id=?',
                'ii', array($this->tenantId, $campaignId)
            );
            $this->execute(
                'DELETE FROM crm_campaign_recipient WHERE tenant_id=? AND campaign_id=?',
                'ii', array($this->tenantId, $campaignId)
            );
            $this->execute(
                'DELETE FROM crm_campaign WHERE tenant_id=? AND id=?',
                'ii', array($this->tenantId, $campaignId)
            );
            $this->audit($actor, 'delete', 'campaign', (string) $campaignId, $campaign[0]);
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollback();
            throw $e;
        }
        return array('id' => $campaignId, 'deleted' => true);
    }

    public function createMessageTemplate(array $input, $actor)
    {
        $name = trim((string) ($input['name'] ?? ''));
        $channel = in_array($input['channel'] ?? '', array('whatsapp','email','sms'), true)
            ? $input['channel'] : 'whatsapp';
        $subject = trim((string) ($input['subject'] ?? ''));
        $body = trim((string) ($input['body'] ?? ''));
        if ($name === '' || $body === '') {
            throw new InvalidArgumentException('Nombre y mensaje son obligatorios.');
        }
        $stmt = $this->db->prepare(
            'INSERT INTO crm_message_template (tenant_id,name,channel,subject,body)
             VALUES (?,?,?,?,?)'
        );
        $stmt->bind_param('issss', $this->tenantId, $name, $channel, $subject, $body);
        $stmt->execute();
        $id = (string) $stmt->insert_id;
        $stmt->close();
        $this->audit($actor, 'create', 'message_template', $id, array('name' => $name));
        return array('id' => (int) $id);
    }

    public function setCustomerConsent($customerId, array $input, $actor)
    {
        $customerId = (int) $customerId;
        $status = $input['status'] ?? '';
        if (!in_array($status, array('granted','denied','unknown'), true)) {
            throw new InvalidArgumentException('Estado de consentimiento inválido.');
        }
        $exists = $this->rows(
            'SELECT id FROM crm_customer WHERE tenant_id=? AND id=? AND is_active=1',
            'ii', array($this->tenantId, $customerId)
        );
        if (!$exists) throw new RuntimeException('CUSTOMER_NOT_FOUND', 404);
        $source = trim((string) ($input['source'] ?? 'registro_manual'));
        if ($source === '') $source = 'registro_manual';
        $recordedAt = date('Y-m-d H:i:s');
        $stmt = $this->db->prepare(
            "INSERT INTO crm_consent
             (tenant_id,customer_id,channel,status,source,recorded_at,created_by)
             VALUES (?,?,'whatsapp',?,?,?,?)
             ON DUPLICATE KEY UPDATE status=VALUES(status),source=VALUES(source),
               recorded_at=VALUES(recorded_at),created_by=VALUES(created_by)"
        );
        $stmt->bind_param(
            'iissss', $this->tenantId, $customerId, $status, $source, $recordedAt, $actor
        );
        $stmt->execute();
        $stmt->close();
        $this->audit($actor, 'consent', 'customer', (string) $customerId, array(
            'channel' => 'whatsapp', 'status' => $status, 'source' => $source,
        ));
        return array('customer_id' => $customerId, 'channel' => 'whatsapp', 'status' => $status);
    }

    public function contactForDirectCampaign($campaignId, $customerId, $confirmed, $actor)
    {
        if (!$confirmed) throw new InvalidArgumentException('Debe confirmar el consentimiento del cliente.');
        $rows = $this->rows(
            "SELECT c.id,c.display_name,c.phone,p.name campaign_name
             FROM crm_customer c JOIN crm_campaign p
               ON p.tenant_id=c.tenant_id AND p.id=? AND p.channel='whatsapp'
             WHERE c.tenant_id=? AND c.id=? AND c.is_active=1
               AND c.phone IS NOT NULL AND c.phone<>''",
            'iii', array((int) $campaignId, $this->tenantId, (int) $customerId)
        );
        if (!$rows) throw new RuntimeException('CAMPAIGN_CUSTOMER_NOT_FOUND', 404);
        $this->setCustomerConsent($customerId, array(
            'status' => 'granted',
            'source' => 'confirmacion_operador_campana',
        ), $actor);
        return $rows[0];
    }

    public function recordDirectCampaignSend($campaignId, $customerId, $message, $externalId, $actor, $externalChatId = '')
    {
        if (is_array($externalId)) {
            $externalId = $externalId['_serialized'] ?? ($externalId['id'] ?? '');
        }
        if (is_array($externalChatId)) {
            $externalChatId = $externalChatId['_serialized'] ?? '';
        }
        $occurredAt = date('Y-m-d H:i:s');
        $subject = 'Mensaje de campaña #' . (int) $campaignId;
        $notes = trim((string) $message);
        $stmt = $this->db->prepare(
            "INSERT INTO crm_interaction
             (tenant_id,customer_id,interaction_type,channel,subject,notes,occurred_at,created_by)
             VALUES (?,?,'campaign_message','whatsapp',?,?,?,?)"
        );
        $stmt->bind_param(
            'iissss', $this->tenantId, $customerId, $subject, $notes, $occurredAt, $actor
        );
        $stmt->execute();
        $interactionId = (string) $stmt->insert_id;
        $stmt->close();

        $customers = $this->rows(
            'SELECT display_name,phone FROM crm_customer WHERE tenant_id=? AND id=? LIMIT 1',
            'ii', array($this->tenantId, (int) $customerId)
        );
        if (!$customers) throw new RuntimeException('CUSTOMER_NOT_FOUND', 404);
        $customer = $customers[0];
        $digits = preg_replace('/\D+/', '', (string) $customer['phone']);
        if (strlen($digits) === 9) $digits = '51' . $digits;
        $chatId = trim((string) $externalChatId);
        if ($chatId === '') $chatId = $digits . '@c.us';

        $stmt = $this->db->prepare(
            "INSERT INTO crm_conversation
             (tenant_id,customer_id,channel,external_chat_id,contact_name,contact_phone,status,unread_count,last_message_at)
             VALUES (?,?,'whatsapp',?,?,?,'waiting',0,NOW())
             ON DUPLICATE KEY UPDATE customer_id=VALUES(customer_id),contact_name=VALUES(contact_name),
               contact_phone=VALUES(contact_phone),status='waiting',last_message_at=NOW()"
        );
        $stmt->bind_param(
            'iisss', $this->tenantId, $customerId, $chatId,
            $customer['display_name'], $customer['phone']
        );
        $stmt->execute();
        $stmt->close();

        $rows = $this->rows(
            "SELECT id FROM crm_conversation WHERE tenant_id=? AND channel='whatsapp' AND external_chat_id=? LIMIT 1",
            'is', array($this->tenantId, $chatId)
        );
        $conversationId = (int) $rows[0]['id'];
        $messageExternalId = trim((string) $externalId);
        if ($messageExternalId === '') {
            $messageExternalId = 'campaign:' . (int) $campaignId . ':interaction:' . $interactionId;
        }
        $stmt = $this->db->prepare(
            "INSERT IGNORE INTO crm_message
             (tenant_id,conversation_id,customer_id,direction,sender_type,body,status,external_message_id,sent_at)
             VALUES (?,?,?,'outbound','agent',?,'sent',?,NOW())"
        );
        $stmt->bind_param(
            'iiiss', $this->tenantId, $conversationId, $customerId, $notes, $messageExternalId
        );
        $stmt->execute();
        $stmt->close();
        $this->audit($actor, 'send', 'campaign_message', $interactionId, array(
            'campaign_id' => (int) $campaignId,
            'customer_id' => (int) $customerId,
            'external_id' => (string) $externalId,
        ));
        return array(
            'sent' => true,
            'interaction_id' => (int) $interactionId,
            'conversation_id' => $conversationId,
        );
    }

    public function createBotRule(array $input, $actor)
    {
        $name = trim((string) ($input['name'] ?? ''));
        $trigger = trim((string) ($input['trigger_text'] ?? ''));
        $response = trim((string) ($input['response_text'] ?? ''));
        if ($name === '' || $trigger === '' || $response === '') {
            throw new InvalidArgumentException('Nombre, activador y respuesta son obligatorios.');
        }
        $priority = max(1, min(1000, (int) ($input['priority'] ?? 100)));
        $handoff = !empty($input['handoff']) ? 1 : 0;
        $stmt = $this->db->prepare(
            'INSERT INTO crm_bot_rule
             (tenant_id,name,trigger_text,response_text,priority,handoff)
             VALUES (?,?,?,?,?,?)'
        );
        $stmt->bind_param('isssii', $this->tenantId, $name, $trigger, $response, $priority, $handoff);
        $stmt->execute();
        $id = (string) $stmt->insert_id;
        $stmt->close();
        $this->audit($actor, 'create', 'bot_rule', $id, array('name' => $name));
        return array('id' => (int) $id);
    }

    public function updateBotRule($ruleId, array $input, $actor)
    {
        $ruleId = (int) $ruleId;
        $existing = $this->rows('SELECT id FROM crm_bot_rule WHERE tenant_id=? AND id=?', 'ii', array($this->tenantId, $ruleId));
        if (!$existing) throw new RuntimeException('BOT_RULE_NOT_FOUND', 404);
        $name = trim((string) ($input['name'] ?? ''));
        $trigger = trim((string) ($input['trigger_text'] ?? ''));
        $response = trim((string) ($input['response_text'] ?? ''));
        if ($name === '' || $trigger === '' || $response === '') {
            throw new InvalidArgumentException('Nombre, activador y respuesta son obligatorios.');
        }
        $priority = max(1, min(1000, (int) ($input['priority'] ?? 100)));
        $handoff = !empty($input['handoff']) ? 1 : 0;
        $active = array_key_exists('is_active', $input) ? (!empty($input['is_active']) ? 1 : 0) : 1;
        $stmt = $this->db->prepare(
            'UPDATE crm_bot_rule SET name=?,trigger_text=?,response_text=?,priority=?,handoff=?,is_active=?
             WHERE tenant_id=? AND id=?'
        );
        $stmt->bind_param('sssiiiii', $name, $trigger, $response, $priority, $handoff, $active, $this->tenantId, $ruleId);
        $stmt->execute();
        $stmt->close();
        $this->audit($actor, 'update', 'bot_rule', (string) $ruleId, array('name' => $name, 'is_active' => $active));
        return array('id' => $ruleId, 'updated' => true);
    }

    public function deleteBotRule($ruleId, $actor)
    {
        $ruleId = (int) $ruleId;
        $existing = $this->rows('SELECT id,name FROM crm_bot_rule WHERE tenant_id=? AND id=?', 'ii', array($this->tenantId, $ruleId));
        if (!$existing) throw new RuntimeException('BOT_RULE_NOT_FOUND', 404);
        $this->execute('DELETE FROM crm_bot_rule WHERE tenant_id=? AND id=?', 'ii', array($this->tenantId, $ruleId));
        $this->audit($actor, 'delete', 'bot_rule', (string) $ruleId, $existing[0]);
        return array('id' => $ruleId, 'deleted' => true);
    }

    public function prepareCampaignOutbox($campaignId, $actor, $liveEnabled, $limit)
    {
        $campaignId = (int) $campaignId;
        $campaigns = $this->rows(
            "SELECT id,name,channel,content_json FROM crm_campaign
             WHERE tenant_id=? AND id=? AND status='draft'",
            'ii', array($this->tenantId, $campaignId)
        );
        if (!$campaigns) throw new RuntimeException('CAMPAIGN_NOT_FOUND_OR_NOT_DRAFT', 404);
        $campaign = $campaigns[0];
        if ($campaign['channel'] !== 'whatsapp') {
            throw new InvalidArgumentException('La cola automatizada sólo admite WhatsApp en esta versión.');
        }
        $content = json_decode($campaign['content_json'], true);
        $message = trim((string) ($content['message'] ?? ''));
        if ($message === '') throw new InvalidArgumentException('La campaña no tiene mensaje.');

        $customers = $this->rows(
            "SELECT c.id,c.display_name,c.phone
             FROM crm_customer c JOIN crm_consent s
               ON s.tenant_id=c.tenant_id AND s.customer_id=c.id
              AND s.channel='whatsapp' AND s.status='granted'
             WHERE c.tenant_id=? AND c.is_active=1 AND c.phone IS NOT NULL AND c.phone<>''
             ORDER BY c.id LIMIT ?",
            'ii', array($this->tenantId, min(100, max(1, (int) $limit)))
        );
        $status = $liveEnabled ? 'pending' : 'simulated';
        $created = 0;
        foreach ($customers as $customer) {
            $body = str_replace('{{nombre}}', $customer['display_name'], $message);
            $key = hash('sha256', $this->tenantId . '|' . $campaignId . '|' . $customer['id']);
            $stmt = $this->db->prepare(
                "INSERT IGNORE INTO crm_message_outbox
                 (tenant_id,campaign_id,customer_id,channel,destination,body,status,idempotency_key)
                 VALUES (?,?,?,'whatsapp',?,?,?,?)"
            );
            $customerId = (int) $customer['id'];
            $destination = (string) $customer['phone'];
            $stmt->bind_param(
                'iiissss',
                $this->tenantId, $campaignId, $customerId,
                $destination, $body, $status, $key
            );
            $stmt->execute();
            $created += max(0, $stmt->affected_rows);
            $stmt->close();
        }
        $this->audit($actor, 'prepare', 'campaign_outbox', (string) $campaignId, array(
            'created' => $created, 'mode' => $status,
        ));
        return array(
            'campaign_id' => $campaignId,
            'eligible' => count($customers),
            'created' => $created,
            'mode' => $status,
            'excluded_without_consent' => true,
        );
    }

    public function adjustLoyalty(array $input, $actor)
    {
        $customerId = (int) ($input['customer_id'] ?? 0);
        $points = (int) ($input['points'] ?? 0);
        $note = trim((string) ($input['note'] ?? ''));
        if ($customerId < 1 || $points === 0) throw new InvalidArgumentException('Cliente y puntos son obligatorios.');
        $sourceId = 'manual-' . bin2hex(random_bytes(12));
        $this->db->begin_transaction();
        try {
            if ($points < 0) {
                $current = $this->rows(
                    'SELECT balance FROM crm_loyalty_account WHERE tenant_id=? AND customer_id=? FOR UPDATE',
                    'ii', array($this->tenantId, $customerId)
                );
                $balance = isset($current[0]) ? (int) $current[0]['balance'] : 0;
                if ($balance < abs($points)) {
                    throw new InvalidArgumentException('El cliente no tiene puntos suficientes para este canje.');
                }
            }
            $stmt = $this->db->prepare(
                'INSERT INTO crm_loyalty_account (tenant_id,customer_id,balance,lifetime_earned,lifetime_redeemed)
                 VALUES (?,?,?,?,?) ON DUPLICATE KEY UPDATE balance=balance+VALUES(balance),
                 lifetime_earned=lifetime_earned+VALUES(lifetime_earned),
                 lifetime_redeemed=lifetime_redeemed+VALUES(lifetime_redeemed)'
            );
            $earned = max(0, $points);
            $redeemed = max(0, -$points);
            $stmt->bind_param('iiiii', $this->tenantId, $customerId, $points, $earned, $redeemed);
            $stmt->execute();
            $stmt->close();
            $account = $this->rows(
                'SELECT id,balance FROM crm_loyalty_account WHERE tenant_id=? AND customer_id=?',
                'ii', array($this->tenantId, $customerId)
            )[0];
            $movement = $points > 0 ? 'adjust' : 'redeem';
            $stmt = $this->db->prepare(
                "INSERT INTO crm_loyalty_ledger
                 (tenant_id,account_id,customer_id,movement_type,points,source_type,source_id,note,created_by)
                 VALUES (?,?,?,?,?,'manual',?,?,?)"
            );
            $accountId = (int) $account['id'];
            $stmt->bind_param('iiisisss', $this->tenantId, $accountId, $customerId, $movement, $points, $sourceId, $note, $actor);
            $stmt->execute();
            $stmt->close();
            $this->db->commit();
            $this->audit($actor, 'adjust', 'loyalty', (string) $customerId, array('points' => $points));
            return array('balance' => (int) $account['balance']);
        } catch (Throwable $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    public function loyalty()
    {
        return $this->rows(
            'SELECT a.customer_id,c.display_name,a.balance,a.lifetime_earned,a.lifetime_redeemed,a.updated_at
             FROM crm_loyalty_account a JOIN crm_customer c
               ON c.id=a.customer_id AND c.tenant_id=a.tenant_id
             WHERE a.tenant_id=? ORDER BY a.updated_at DESC',
            'i', array($this->tenantId)
        );
    }

    public function loyaltyLedger($limit = 100)
    {
        $limit = max(1, min(300, (int) $limit));
        return $this->rows(
            'SELECT l.id,l.customer_id,c.display_name,l.movement_type,l.points,l.source_type,
                    l.note,l.created_by,l.created_at
             FROM crm_loyalty_ledger l JOIN crm_customer c
               ON c.id=l.customer_id AND c.tenant_id=l.tenant_id
             WHERE l.tenant_id=? ORDER BY l.id DESC LIMIT ' . $limit,
            'i', array($this->tenantId)
        );
    }
}
