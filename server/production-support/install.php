<?php
// Deploy versioned code only. Database provisioning is a separate explicit CLI step.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$options = getopt('', ['root:', 'backup:']);
$target = realpath($options['root'] ?? '');
$backup = $options['backup'] ?? '';
if (!$target || !$backup || file_exists($backup)) throw new RuntimeException('Use --root=/ruta/ERP --backup=/ruta/privada/nueva');
$parent = realpath(dirname($backup));
if (!$parent || strpos(strtolower(str_replace('\\','/',$parent)).'/', strtolower(str_replace('\\','/',$target)).'/') === 0) throw new RuntimeException('El respaldo debe estar fuera del directorio público del ERP.');
if (!is_file($target.'/config/database.php') || !is_file($target.'/api/index.php')) throw new RuntimeException('No se encuentra la estructura del ERP.');
$api = file_get_contents($target.'/api/index.php');
if (strpos($api, 'support-connector.php') !== false) throw new RuntimeException('Este sistema tiene un conector cliente; no se convertirá en central automáticamente.');
$hook = '$registerSupport=require __DIR__.\'/support-api.php\';$registerSupport($app);';
if (strpos($api, $hook) === false) throw new RuntimeException('Falta el registro de la API central; revisar la instalación antes de continuar.');
$source = dirname(__DIR__, 2).'/integration/helpdesk';
$files = [];
foreach (['support-api.php','support-runtime.php','support-service.php','support-activity.php','support-report.php','support-performance.php','support-satisfaction.php'] as $name) $files['api/'.$name] = $source.'/'.$name;
foreach (['setup.php','config-files.php','support.local.example.php','.htaccess','LEEME.txt'] as $name) $files['tools/support/'.$name] = $source.'/runtime/'.$name;
foreach (glob($source.'/migrations/*.up.sql') as $file) $files['tools/support/migrations/'.basename($file)] = $file;
$files['tools/support/support-clientes-nstarapaca.zip'] = dirname(__DIR__,2).'/output/support-clientes-nstarapaca.zip';
foreach ($files as $file) if (!is_file($file)) throw new RuntimeException('Paquete incompleto.');
if (!mkdir($backup,0700,true)) throw new RuntimeException('No se pudo crear el respaldo privado.');
foreach ($files as $relative => $file) {
    $dest = $target.'/'.$relative;
    if (is_file($dest)) { $saved=$backup.'/'.$relative; if(!is_dir(dirname($saved)))mkdir(dirname($saved),0700,true); if(!copy($dest,$saved))throw new RuntimeException('No se pudo respaldar '.$relative); }
    if (!is_dir(dirname($dest))) mkdir(dirname($dest),0755,true);
    if (!copy($file,$dest.'.support-tmp') || !rename($dest.'.support-tmp',$dest)) throw new RuntimeException('No se pudo instalar '.$relative);
}
echo "Código central actualizado. Configuración privada y bases preservadas. Ejecuta tools/support/setup.php --check.\n";
