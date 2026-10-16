# API CRM

Base: `/api/crm/index.php?route=<ruta>`.

La API usa la cookie de sesión del sistema. Con `CRM_ENABLED=false` responde 404;
sin sesión responde 401. Todas las consultas reciben `tenant_id` desde configuración,
nunca desde parámetros del navegador.

| Ruta | Respuesta |
|---|---|
| `health` | Estado y tenant activo |
| `dashboard` | Clientes, tareas, campañas y puntos |
| `customers&q=` | Clientes y métricas RFM |
| `segments` | Segmentos |
| `tasks` | Cola de seguimiento |
| `campaigns` | Campañas |
| `sync/status` | Últimas sincronizaciones |

Las operaciones de escritura HTTP quedan cerradas en este MVP salvo implementación
posterior con perfil autorizado, validación CSRF y auditoría.
