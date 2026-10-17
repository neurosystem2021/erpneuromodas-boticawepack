# Agenda personal

El foquito de la derecha abre un diario privado para cada usuario activo, incluido el cliente `admin`. Se puede arrastrar y volver a acoplar al borde. La posición y la preferencia de voz se guardan por usuario en el navegador; las notas se guardan en la base de datos.

- Escribir una tarea y pulsar **Agregar** crea una nota sin alarma.
- La fecha de arriba permite consultar un día exacto, avanzar o retroceder y volver a **Hoy**. Las notas nuevas pertenecen a la fecha seleccionada, aunque no tengan alarma. **Pendientes** y **Todas** permiten consultar otros días.
- **Fecha y aviso** permite activar el recordatorio, elegir fecha y hora de Lima, anticipación, frecuencia de avisos y repetición diaria o semanal.
- El check completa y tacha la tarea. Al completar una tarea repetitiva se crea una sola siguiente ocurrencia futura.
- Las tareas completadas sin repetición se pueden volver a dejar pendientes desmarcando su check.
- El pececito puede mostrar el aviso con el panel cerrado y con la voz apagada. Los avisos funcionan mientras el sistema está abierto y se revisan cada 15 segundos en la pestaña visible.
- La voz necesita interacción con el navegador y respeta las notificaciones urgentes de soporte. No se solicitan permisos de notificaciones del sistema operativo.

## Privacidad e integración

`agenda-api.php` registra `/agenda/contexto`, `/agenda/notas` y `/agenda/avisos`. Cada consulta y modificación usa el usuario de sesión; no admite elegir otro propietario. Incluso SUPERADMIN solo ve sus propias notas. Las escrituras exigen `X-Agenda-CSRF` y versión para evitar cambios concurrentes.

Los datos viven en `Rhu_DiarioPersonal`, separados de `Rhu_DiarioTarea` y de los tickets. La columna `Recordar` distingue notas simples de recordatorios. Los recordatorios personales anteriores se conservan y pasan a avisarse por la agenda, sin duplicarse en el tablero laboral.

En una instalación existente aplicar `upgrade-agenda-personal.sql`, copiar `agenda-api.php` junto a los archivos del diario y registrar su función después de `diario-api.php` en la API Slim, antes del manejador 404. Compilar y publicar el frontend con `AgendaPersonal.vue` incluido en `App.vue`. La instalación local se realizó con los scripts `tools/install-agenda-personal.php` y `.ps1`, después de restaurar y comprobar un respaldo privado.
