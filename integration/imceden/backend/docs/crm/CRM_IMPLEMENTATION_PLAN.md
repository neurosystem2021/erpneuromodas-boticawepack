# Plan de implementación CRM

## Bloque B: seguridad y feature flag

- Configuración `CRM_ENABLED=false`.
- Adaptador de sesión, tenant y permisos.
- Health check aislado.
- Pruebas de flag, autenticación e IDOR.

## Bloque C: datos

- Migración aditiva `up`.
- Rollback exclusivo de tablas `crm_*`.
- Seed ficticio separado.
- Prueba idempotente de migración sin bases productivas.

## Bloque D: API

- Front controller `/api/crm/index.php`.
- Router pequeño compatible con el PHP actual.
- Dashboard, clientes, segmentos, tareas, campañas y fidelización.
- Respuestas JSON uniformes, paginación y correlation ID.

## Bloque E: sincronización

- Repositorios de lectura para clientes/ventas.
- Checkpoints por stream.
- Upserts CRM idempotentes.
- Métricas RFM y favoritos.

## Bloque F: frontend

- Vue 3 + Vite aislado en `crm-web/`.
- Dashboard, Clientes, Cliente 360, Segmentos, Campañas, Tareas,
  Fidelización, Reportes y Configuración.
- Proveedor de campañas simulado.

## Bloque G: integración

- Vista `/crm` independiente.
- Enlace condicional en menú.
- Retorno explícito a NeuroRest.
- Ninguna dependencia nueva en Vue 2.

## Bloque H: pruebas

- Unitarias: normalización, RFM, segmentos, puntos y permisos.
- Integración: tenant, sync, idempotencia, auditoría y rollback.
- E2E local con datos ficticios.
- Regresión: CRM apagado no altera login ni módulos operativos.

## Bloque I: documentación

- Instalación y ejecución local.
- OpenAPI.
- Manual de usuario.
- Seguridad, limitaciones, piloto y rollback.

## Entrega

No se hará merge ni despliegue. La rama quedará revisable con resultados de pruebas,
riesgos conocidos y pasos manuales.
# Extensión: comunicaciones e IA

1. Migración aditiva para conexiones, conversaciones, mensajes, reglas y cola.
2. API de estado, bandeja, plantillas, preparación de campaña y prueba del bot.
3. Adaptador de proveedor sin secretos en base de datos.
4. Interfaz de Comunicaciones y Configuración.
5. Pruebas de consentimiento, idempotencia, tenant y configuración segura.
6. Activación inicial sólo en modo simulación.
