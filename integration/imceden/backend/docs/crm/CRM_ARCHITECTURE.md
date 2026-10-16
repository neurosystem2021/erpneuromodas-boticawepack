# Arquitectura CRM NeuroRest

## Principios

El CRM es un módulo adicional. NeuroRest sigue operando aunque el CRM esté desactivado,
no tenga migraciones instaladas o su SPA/API fallen.

## Componentes

```text
NeuroRest PHP/Vue2
  ├─ menú condicional (CRM_ENABLED=false por defecto)
  ├─ sesión PHP existente
  └─ tablas operativas (solo lectura desde CRM)

/api/crm
  ├─ adaptador de sesión y tenant
  ├─ autorización por permisos crm.*
  ├─ repositorios crm_* parametrizados
  ├─ dashboard, clientes, tareas, campañas y fidelización
  ├─ sincronizador incremental
  └─ health/logs independientes

/crm
  └─ SPA Vue 3 + Vite, compilada de forma independiente
```

## Feature flag

- Variable global: `CRM_ENABLED`, valor por defecto `false`.
- Ajuste por tenant: `crm_settings.crm_enabled`.
- La variable global debe habilitar el módulo antes de consultar el ajuste del tenant.
- Con el flag apagado, el menú no aparece y `/api/crm/*` devuelve 404 controlado.

## Seguridad

- Sesión actual reutilizada; no hay tabla CRM de contraseñas.
- `tenant_id` se resuelve en servidor y nunca se acepta libremente desde el cliente.
- Cada repositorio recibe el tenant como primer filtro.
- Operaciones mutables requieren permiso explícito y auditoría.
- Consultas preparadas para filtros, paginación y escrituras CRM.
- Endpoints internos requieren sesión administrativa y token local no versionado.

## Sincronización

1. Leer checkpoint `(tenant_id, stream)`.
2. Consultar clientes/ventas con ID mayor al checkpoint en lotes.
3. Normalizar e insertar/actualizar únicamente proyecciones CRM.
4. Usar clave única `(tenant_id, source_id)` e idempotency key.
5. Registrar errores aislados sin abortar el lote completo.
6. Avanzar checkpoint solo hasta el último registro confirmado.

## Rollback

1. Establecer `CRM_ENABLED=false`.
2. Retirar enlace del menú sin tocar el núcleo.
3. Detener sincronizador.
4. Conservar datos para diagnóstico o ejecutar migración `down` únicamente sobre `crm_*`.
5. La operación de NeuroRest no depende de ninguna tabla CRM.
# Centro de comunicaciones (extensión)

El CRM incorpora una capa de mensajería aislada bajo `/api/crm/messaging/*`.
Las credenciales del proveedor nunca se almacenan en las tablas ni se entregan
al navegador: se leen exclusivamente desde variables de entorno.

El flujo es:

1. El CRM selecciona únicamente clientes del tenant actual.
2. Valida teléfono y consentimiento antes de crear un destinatario.
3. Registra el mensaje en `crm_message_outbox` con clave de idempotencia.
4. En modo `simulation` no realiza ninguna llamada externa.
5. Un proveedor real podrá procesar la cola sólo con
   `CRM_MESSAGING_ENABLED=true`.
6. Los webhooks entrantes deberán normalizarse en `crm_conversation` y
   `crm_message`; nunca escriben en ventas, caja o inventario.

El asistente usa un adaptador con los proveedores `simulation`, `openai` y
`gemini`. Las respuestas son borradores hasta que una regla explícita permita
automatización; siempre existe derivación a una persona.
