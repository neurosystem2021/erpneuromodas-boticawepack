# Limitaciones y siguiente fase

Esta entrega establece un MVP seguro y navegable. La API HTTP inicial es de lectura;
las altas y modificaciones de tareas, segmentos, campañas y puntos deben añadirse
en la siguiente fase con CSRF, validación por permiso granular y auditoría.

También quedan para la siguiente fase:

- Cliente 360 con detalle de platos/categorías favoritas.
- Gráficos de tendencias, retención y recompra.
- Exportación CSV/XLSX con límites.
- Ejecutor incremental por checkpoint y cola de reintentos.
- Adaptadores de mensajería; siempre deben iniciar en modo simulado.
- Pruebas de integración con base efímera y E2E de navegador.
- Mapeo de los permisos `crm.*` dentro de la administración de perfiles.

No debe habilitarse en producción hasta completar esas pruebas y la lista de
liberación.
