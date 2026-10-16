# Diccionario de datos CRM

## Fuentes operativas de solo lectura

| Tabla | Clave | Uso CRM | Restricción |
|---|---|---|---|
| `GEN_EMPRESA` | `IDEMPRESA` | tenant/restaurante | Solo lectura |
| `Lo_Almacen` | `IdAlmacen` | sucursal habitual | Solo lectura |
| `Ve_DocVentaCliente` | `IdCliente` | identidad fuente | Solo lectura |
| `Ve_DocVenta` | `idDocVenta` | compra, fecha, total y canal | Solo lectura |
| `Ve_DocVentaDet` | `IdDocVentaDet` | platos y cantidades | Solo lectura |
| `Gen_Producto` | `IdProducto` | plato/producto favorito | Solo lectura |
| `Gen_ProductoCategoria` | `IdProductoCategoria` | categoría favorita | Solo lectura |
| `Seg_Usuario` | `Usuario` | responsable/actor | Solo lectura |

## Identidad de cliente

Orden de coincidencia:

1. `(tenant_id, source_id)`.
2. Teléfono normalizado dentro del tenant.
3. Correo normalizado dentro del tenant.
4. Coincidencias ambiguas se registran como error; nunca se fusionan automáticamente.

## Entidades CRM

Todas las tablas usan `BIGINT UNSIGNED`, `tenant_id`, timestamps UTC/lógicos y claves
foráneas solo entre tablas `crm_*`.

- `crm_customer_profiles`: proyección de identidad, contacto normalizado y preferencias.
- `crm_customer_metrics`: primera/última compra, visitas, gasto, ticket, RFM y favoritos.
- `crm_tags`, `crm_customer_tags`: clasificación manual o automática.
- `crm_segments`, `crm_segment_rules`, `crm_segment_members`: segmentación reproducible.
- `crm_interactions`: llamadas, mensajes, visitas, reclamos, felicitaciones y notas.
- `crm_tasks`: seguimiento con estado, prioridad, responsable y vencimiento.
- `crm_campaigns`, `crm_campaign_recipients`, `crm_message_templates`: campañas en borrador/simulación.
- `crm_loyalty_accounts`, `crm_loyalty_movements`: saldo y libro mayor auditable.
- `crm_consents`: consentimiento por canal.
- `crm_sync_checkpoints`, `crm_sync_errors`: sincronización incremental e idempotente.
- `crm_audit_logs`: auditoría de acciones importantes.
- `crm_settings`: feature flags y parámetros por tenant.

## Tipos y reglas

- Dinero: `DECIMAL(15,2)`, nunca `FLOAT`.
- Estados: `VARCHAR` con validación en servicio y `CHECK` donde MariaDB lo soporte.
- Teléfono/correo: valores normalizados separados del valor de presentación.
- Metadatos: JSON opcional, sin secretos ni información de pago.
- Borrado: soft delete solo en catálogos y perfiles CRM.
- Claves únicas: siempre incluyen `tenant_id`.
