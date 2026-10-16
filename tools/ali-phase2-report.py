from pathlib import Path
import hashlib, json, zipfile, re
from html import escape

root = Path(__file__).resolve().parents[1]
live = Path('C:/xampp/htdocs/boticav1')
baseline = root / 'backups/ali-baseline-20261013-192156/baseline.zip'
out = root / 'docs/ali'
with zipfile.ZipFile(baseline) as archive:
    checks = {}
    for name in ['api/index.php','api/sunat/sunat.php','imprimir/index.php','views/header.php','views/V_VentaForm.php']:
        checks['live/' + name] = archive.read('live/' + name) == (live / name).read_bytes()
    name = 'src/views/VentasFacturacion.vue'
    checks[name] = archive.read('workspace/' + name) == (root / name).read_bytes()
    before = archive.read('workspace/src/views/Ventas.vue').decode('utf-8').replace('\r\n','\n')
    after = (root / 'src/views/Ventas.vue').read_text(encoding='utf-8')
    def manual_save(text):
        return text.split('    async guardarVenta(evt) {',1)[1].split('    async obtenerSeriePorProducto(id) {',1)[0]
    checks['Ventas.vue::guardarVenta'] = manual_save(before) == manual_save(after)
assert all(checks.values()), checks
files = ['src/components/AliAssistant.vue','src/utils/aliCommands.js','src/views/Ventas.vue','src/components/ProductosCarritoNuevo.vue','integration/ali-api.php','integration/ali-pos-security.php']
proof = {'phase':2,'build':'6afbe6ec3f37160cc780','manual_and_fiscal_unchanged':checks,'modified_application_files':files,'sql_migrations':[],
         'deployment_backup':'C:/xampp/htdocs/boticav1/.ali-phase2-backup-20261013-195621',
         'tests':{'commands':'passed','courtesy':'passed','mocked_checkout_hold':'passed','permission_contracts':'passed','private_config':'passed','voice_options':'passed','live_auth_csrf_expiry':'passed','browser_responsiveness':'passed','browser_voice_variety_idle':'passed','browser_phase2_cancellation_stop_permissions':'passed'},
         'limits':['Micrófono físico y audio real no evaluados por los tests de navegador.','La reproducción de voz pausa el reconocimiento para evitar eco. El botón de parada permanece disponible.','El despachador ALI no corrige todavía la autorización de todas las rutas heredadas.','Carrito ampliado, alta conversacional de clientes, cobro idempotente y function calling operacional pendientes de fases siguientes.','Emisión excluida por instrucción del usuario; instalación local sin certificados.']}
(out / 'evidencia-fase-2.json').write_text(json.dumps(proof,ensure_ascii=False,indent=2),encoding='utf-8')
state = json.loads((out/'estado-fases.json').read_text(encoding='utf-8'))
state.update(phase_2='implementada_y_verificada',application_changes_this_phase=True,test_environment='ERP_local_sin_certificados_no_probar_emision',fiscal_changes_authorized=False,phase_4='pendiente_pagos_confirmacion_idempotencia_emision_excluida',phase_2_evidence='docs/ali/evidencia-fase-2.json')
(out/'estado-fases.json').write_text(json.dumps(state,ensure_ascii=False,indent=2),encoding='utf-8')
rows=''.join('<tr><td>'+escape(k)+'</td><td>Verificado sin cambios</td></tr>' for k in checks)
file_rows=''.join('<li><code>'+escape(f)+'</code></li>' for f in files)
html='''<!doctype html><html lang="es"><meta charset="utf-8"><title>ALI · Entrega fase 2</title><style>body{font:16px/1.55 system-ui;max-width:1040px;margin:40px auto;padding:0 24px;color:#173c4b}h1,h2{color:#087e8b}table{border-collapse:collapse;width:100%}td,th{padding:10px;border:1px solid #cbdde4;text-align:left}code{background:#edf5f8;padding:2px 5px}aside{background:#e7f6f2;padding:18px;border-radius:12px}</style>
<h1>ALI · Fase 2: voz, búsqueda y controles iniciales</h1><p>13 de octubre de 2026 · Rama <code>feature/ali-control-inteligente-20261013</code>.</p>
<aside>Instalado en el ERP local. No se modificaron la facturación, los certificados, los correlativos ni datos de ventas. No hubo llamadas de prueba a SUNAT ni consumo de OpenAI en esta fase.</aside>
<h2>Funciones entregadas</h2><ul><li>Parada completa con botón o «ALI, silencio», «deja de escuchar», «desactívate». No reinicia el micrófono automáticamente. Reactivación explícita visible.</li><li>Escucha continua configurable y modo de una orden. Se conservan Hola ALI, Gracias ALI, voces, saludo según hora, usuario y dos oportunidades de inactividad.</li><li>Estados operativos e historial temporal con números largos ocultos. Sin grabación permanente de audio ni apertura automática del panel ALI.</li><li>Búsqueda natural: «muéstrame cámaras de cuatro megapíxeles», nombres, categorías, modelos y variantes de marcas. Lista numerada de hasta 25 resultados reales.</li><li>La búsqueda más reciente cancela la anterior. Una sola consulta de catálogo por intención y ningún resultado obsoleto reemplaza el actual.</li><li>Sesión vigente, permisos reales de POS, escritura y almacén, CSRF, acciones permitidas y límites independientes para órdenes, voz y consultas IA.</li><li>Se bloqueó el antiguo cobro automático por ALI, que carecía de confirmación e idempotencia. Pagar manual conserva su función original. El alta automática de un cliente inexistente también queda detenida hasta incorporar el formulario seguro.</li></ul>
<h2>Archivos de aplicación</h2><ul>'''+file_rows+'''</ul><p>Despliegue: <code>tools/deploy-ali-phase2.ps1</code>. Pruebas PHP/Node: <code>tools/test-ali-phase2-*</code>, además de regresiones existentes. Evidencia estructurada: <code>docs/ali/evidencia-fase-2.json</code>.</p>
<h2>Pruebas y resultados</h2><p>Compilación webpack correcta. Pruebas de intérprete, cortesía, opciones de voz, configuración privada, permisos, CSRF, vencimiento de sesión y bloqueo de cobro aprobadas. Navegador Chrome: salida del modo código, cuatro métodos de pago, cliente fixture, una búsqueda real de catálogo, órdenes durante audio demorado 4,5 s, parada durante autorización, cancelación de búsquedas, HTML de resultados escapado, revocación de permisos, recuperación de red y escucha de una orden. Comparación de 13 voces, preferencias, móvil e inactividad aprobadas con audio simulado.</p><p>Las pruebas usan fixtures para mutaciones; no crearon clientes, ventas ni pagos. Las pruebas de lectura usaron el catálogo real y sesiones temporales. No se ensayó el micrófono físico ni se midió latencia de audio real bajo carga.</p>
<h2>Preservación verificada contra el respaldo</h2><table><tr><th>Archivo o función</th><th>Resultado</th></tr>'''+rows+'''</table>
<h2>Instalación y reversión</h2><p>Instalación aplicada: <code>node build/build.js</code> y <code>powershell -NoProfile -ExecutionPolicy Bypass -File tools/deploy-ali-phase2.ps1</code>. Se copiaron primero el adaptador de permisos y después la API ALI; los bundles son aditivos y el índice se cambia al final. No hay migraciones SQL.</p><p>Respaldo de despliegue: <code>C:\\xampp\\htdocs\\boticav1\\.ali-phase2-backup-20261013-195621</code>. Para revertir, restaurar <code>ali-api.php</code> del respaldo a <code>api/ali-api.php</code> y el contenido respaldado de <code>nuevo</code> sobre su ruta original; conservar los bundles anteriores permite volver al índice anterior. El helper nuevo sin referencia puede permanecer. Respaldo integral previo: <code>backups/ali-baseline-20261013-192156/baseline.zip</code>.</p>
<h2>Riesgos y trabajo pendiente</h2><p>Este control protege las entradas ALI; las rutas heredadas no quedan endurecidas globalmente por este cambio. Durante reproducción se pausa el reconocimiento para evitar que ALI se escuche a sí misma; el botón de parada sigue disponible. Quedan pendientes carrito ampliado, formularios de clientes, confirmaciones e idempotencia de cobro, despachador operacional IA y auditoría persistente. La emisión electrónica se excluye por la instrucción posterior del usuario: no tocar facturación y no hay certificados.</p></html>'''
(out/'entrega-fase-2.html').write_text(html,encoding='utf-8')
print('PASS fiscal files and manual checkout unchanged against phase 1 baseline. Phase 2 report and evidence generated.')
