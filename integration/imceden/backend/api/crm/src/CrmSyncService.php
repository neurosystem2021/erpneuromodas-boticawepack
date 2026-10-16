<?php

final class CrmSyncService
{
    private $db;
    private $tenantId;

    public function __construct(mysqli $db, $tenantId)
    {
        $this->db = $db;
        $this->tenantId = (int) $tenantId;
    }

    public function preview($limit, $offset = 0)
    {
        $limit = min(1000, max(1, (int) $limit));
        $sql = "SELECT c.IdCliente source_id, COALESCE(NULLIF(TRIM(c.Cliente),''),'Cliente sin nombre') display_name,
                       NULLIF(TRIM(c.DniRuc),'') document_number, c.Telefono phone, c.Email email,
                       MAX(v.FechaDoc) last_purchase_at, COUNT(DISTINCT v.idDocVenta) purchase_count,
                       COALESCE(SUM(d.Cantidad * d.Precio - COALESCE(d.Descuento,0)),0) total_spent
                FROM Ve_DocVentaCliente c
                LEFT JOIN Ve_DocVenta v ON v.IdCliente=c.IdCliente AND (v.Anulado=0 OR v.Anulado IS NULL)
                LEFT JOIN Ve_DocVentaDet d ON d.IdDocVenta=v.idDocVenta
                WHERE c.IdCliente>0 AND (c.Anulado=0 OR c.Anulado IS NULL)
                GROUP BY c.IdCliente, c.Cliente, c.DniRuc, c.Telefono, c.Email
                ORDER BY c.IdCliente LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) throw new RuntimeException('No se pudo preparar la lectura de clientes.');
        $offset = max(0, (int) $offset);
        $stmt->bind_param('ii', $limit, $offset);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        foreach ($rows as &$row) {
            $row['phone'] = CrmNormalizer::phone($row['phone']);
            $row['email'] = CrmNormalizer::email($row['email']);
            $row = array_merge($row, CrmRfmCalculator::calculate($row, date('Y-m-d')));
        }
        return $rows;
    }

    public function apply(array $rows)
    {
        $sql = "INSERT INTO crm_customer
                  (tenant_id, external_customer_id, display_name, document_number, phone, email,
                   last_purchase_at, purchase_count, total_spent, recency_days, r_score, f_score, m_score,
                   rfm_score, source_updated_at)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW())
                ON DUPLICATE KEY UPDATE display_name=VALUES(display_name),
                  document_number=VALUES(document_number), phone=VALUES(phone), email=VALUES(email),
                  last_purchase_at=VALUES(last_purchase_at), purchase_count=VALUES(purchase_count),
                  total_spent=VALUES(total_spent), recency_days=VALUES(recency_days),
                  r_score=VALUES(r_score), f_score=VALUES(f_score), m_score=VALUES(m_score),
                  rfm_score=VALUES(rfm_score), source_updated_at=NOW(), is_active=1";
        $stmt = $this->db->prepare($sql);
        if (!$stmt) throw new RuntimeException('No se pudo preparar la proyección CRM.');
        $this->db->begin_transaction();
        try {
            foreach ($rows as $row) {
                $sourceId = (string) $row['source_id'];
                $spent = (float) $row['monetary'];
                $stmt->bind_param(
                    'issssssidiiiis',
                    $this->tenantId, $sourceId, $row['display_name'], $row['document_number'],
                    $row['phone'], $row['email'], $row['last_purchase_at'], $row['frequency'],
                    $spent, $row['recency_days'], $row['r_score'], $row['f_score'],
                    $row['m_score'], $row['rfm_score']
                );
                $stmt->execute();
            }
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollback();
            throw $e;
        } finally {
            $stmt->close();
        }
        return count($rows);
    }
}
