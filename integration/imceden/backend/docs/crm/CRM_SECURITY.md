# Resumen de seguridad

- El módulo está apagado por defecto.
- Reutiliza la sesión PHP; no duplica credenciales.
- El tenant procede de configuración del servidor, no del cliente HTTP.
- Todas las consultas del repositorio filtran por `tenant_id`.
- Las consultas usan sentencias preparadas.
- El sincronizador sólo lee tablas operativas y escribe tablas `crm_*`.
- Las escrituras HTTP permanecen cerradas en el MVP.
- El seed contiene identidades ficticias y dominios `.invalid`.
- No se registran contraseñas, tokens, tarjetas ni datos de pago.
- El rollback operativo inmediato es apagar el feature flag.

Riesgo heredado: el repositorio contiene configuración histórica de base de datos.
Debe migrarse a secretos externos en una tarea independiente y rotarse cualquier
credencial que haya sido expuesta.
