# Registro de riesgos CRM

| ID | Riesgo | Prob. | Impacto | Mitigación |
|---|---|---:|---:|---|
| R1 | Acceso entre restaurantes | Media | Crítico | tenant resuelto por servidor, filtro obligatorio y pruebas IDOR |
| R2 | Escritura accidental en ventas/caja | Baja | Crítico | repositorios operativos de solo lectura y revisión SQL automatizada |
| R3 | Credenciales preexistentes versionadas | Alta | Crítico | no copiar; recomendar rotación y variables fuera de Git |
| R4 | CRM degrada el POS | Baja | Alto | SPA/API separadas, carga no bloqueante y flag apagado |
| R5 | Duplicación en sincronización | Media | Alto | claves únicas, checkpoint e idempotency key |
| R6 | Datos históricos incompletos | Alta | Medio | métricas con calidad/fecha de cobertura visible |
| R7 | Fusión incorrecta de clientes | Media | Alto | no fusionar coincidencias dudosas |
| R8 | SQL lento sobre ventas | Media | Alto | lotes por ID, límites e índices solo en tablas CRM |
| R9 | Mensajes enviados sin autorización | Baja | Crítico | proveedor simulado; envío real desactivado |
| R10 | Migración incompatible con MariaDB | Media | Alto | SQL idempotente, prechecks y rollback separado |
| R11 | Pruebas heredadas destructivas | Alta | Alto | no ejecutarlas; crear harness CRM con base dedicada explícita |
| R12 | PII en logs/exportaciones | Media | Alto | minimización, masking, permisos y límites |
| R13 | Monolito sin gestor uniforme | Alta | Medio | módulo CRM autocontenido y comandos documentados |
| R14 | Sesión PHP caducada en SPA | Media | Medio | respuestas 401 consistentes y retorno seguro a NeuroRest |

## Riesgo residual aceptado para MVP

- La fuente operativa no ofrece un outbox; la sincronización por ID puede retrasar cambios
  sobre registros antiguos. Se documentará una reconciliación periódica acotada.
- El tenant del núcleo es esencialmente uno por despliegue; el CRM mantendrá `tenant_id`
  en todas sus tablas para permitir evolución y defensa adicional.
# Riesgos del centro de comunicaciones

- **Bloqueo o desconexión de WhatsApp Web:** las conexiones QR no oficiales
  pueden cambiar o ser restringidas. Mitigación: número de prueba, límites,
  botón de pausa y migración futura a Meta Cloud API.
- **Mensajes no consentidos:** todo destinatario queda excluido salvo
  consentimiento `granted`. El operador no puede saltar esta validación desde
  la interfaz.
- **Duplicados:** cada mensaje usa una clave idempotente única por tenant,
  campaña y cliente.
- **Exposición de secretos:** URL, token y claves de IA viven únicamente en
  variables de entorno y nunca se devuelven al frontend.
- **Respuesta incorrecta de IA:** el modo predeterminado es simulación y las
  respuestas son borradores con transferencia humana.
- **Costo inesperado:** mensajería e IA están desactivadas por defecto y tienen
  límites configurables por lote.
- **Dependencias de WhatsApp Web:** WPPConnect 2.2.4 reporta vulnerabilidades
  transitivas en su árbol npm. El gateway queda limitado a `127.0.0.1`, exige
  clave, no acepta tráfico externo y no se aprueba para producción. Para
  producción se mantiene como recomendación Meta Cloud API o un proveedor
  auditado.
