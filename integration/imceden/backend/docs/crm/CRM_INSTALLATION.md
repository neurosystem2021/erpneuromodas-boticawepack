# Instalación segura del CRM

## Precondiciones

- Usar exclusivamente la rama `feat/crm-restaurante`.
- Realizar respaldo verificable antes de cualquier migración.
- Probar primero en una copia no productiva.
- No guardar credenciales en Git.

## Aplicación

1. Configurar la base mediante variables `DB_*` o `config/database.local.php`.
2. Ejecutar `data/crm/001_crm_schema_up.sql` sobre la base de prueba.
3. Verificar que sólo se crearon tablas con prefijo `crm_`.
4. Mantener `CRM_ENABLED=false` durante las verificaciones.
5. La interfaz ya está compilada en `crm/dist`.
6. Para recompilar: `cd crm`, `npm ci`, `npm run build`.
7. Habilitar en prueba con `CRM_ENABLED=true` y `CRM_TENANT_ID=<empresa>`.
8. Ejecutar primero `php tasks/crm_sync.php` (dry run).
9. Sólo después de revisar el resultado, ejecutar `php tasks/crm_sync.php --apply`.

El seed `002_crm_demo_seed.sql` es opcional, usa datos ficticios y tenant `999`.

## Desactivación y rollback

La desactivación inmediata consiste en volver a `CRM_ENABLED=false`. Esto oculta el
menú y hace que la API responda 404, sin borrar información.

El archivo `001_crm_schema_down.sql` es destructivo y únicamente documenta el
rollback definitivo. Requiere aprobación, respaldo y ventana de mantenimiento.
# WhatsApp local por QR

1. Ejecutar `npm install` dentro de `whatsapp-gateway`.
2. Iniciar `whatsapp-gateway/start.ps1`.
3. Mantener el gateway escuchando sólo en `127.0.0.1`.
4. Configurar en `config/crm.local.php`:
   `messaging_provider=wppconnect`, URL local, nombre de instancia y una clave
   local no versionada.
5. Abrir **CRM → Comunicaciones → Generar código QR**.
6. Escanear desde WhatsApp → Dispositivos vinculados.

`CRM_MESSAGING_ENABLED=false` continúa bloqueando el procesamiento automático
de campañas. La vinculación no habilita por sí sola envíos masivos.
