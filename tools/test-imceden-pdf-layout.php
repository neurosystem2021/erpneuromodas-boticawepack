<?php
// Render isolated fixtures using the installed PDF engine. No HTTP, SQL or CPE writes.
ini_set('display_errors', '0');
$root = dirname(__DIR__);
$engine = 'C:/xampp/htdocs/boticav1/api/sunat/plugins/dompdf';
$template = $argv[1] ?? $root . '/server/pdf/comprobante.php';
$fixture = $argv[2] ?? '';
$output = $argv[3] ?? '';
if (!is_file($fixture) || $output === '') throw new RuntimeException('Fixture and output required.');
$_SERVER['DOCUMENT_ROOT'] = 'C:/xampp/htdocs/boticav1';
function empresaConfigObtener() { return array(); }
function empresaConfigValor($config, $key, $default = '') {
    return '../boticawebpack/tmp/pdfs/imceden-f004-65/asset-22.jpeg';
}
$source = file_get_contents($template);
$source = str_replace("file_get_contents('php://input')", 'file_get_contents(' . var_export($fixture, true) . ')', $source, $inputCount);
$source = str_replace("@file_put_contents(\$ruta . \$fichero . '.pdf', \$pdf);", '// No persistent CPE write in isolated rendering.', $source, $writeCount);
$source = str_replace("if (!is_dir(\$ruta)) {", 'if (false) {', $source, $directoryCount);
if ($inputCount !== 1 || $writeCount !== 1 || $directoryCount !== 1) throw new RuntimeException('Unexpected template IO.');
$source = str_replace('__DIR__', var_export($engine, true), $source);
chdir($engine);
ob_start();
eval('?>' . $source);
$rendered = ob_get_clean();
if (substr($rendered, 0, 5) !== '%PDF-') throw new RuntimeException('Invalid rendered PDF.');
file_put_contents($output, $rendered);
fwrite(STDERR, basename($output) . ': ' . strlen($rendered) . " bytes\n");
