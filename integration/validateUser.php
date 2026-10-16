<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

$now = time();
if (isset($_SESSION['expire']) && $now > $_SESSION['expire']) {
  session_destroy();
  header('Location: ' . 'http://' . $_SERVER['HTTP_HOST']);
  exit();
}

if (!isset($_SESSION['user'])) {
  ?>
  <script>
    window.location.href = "/"
  </script>
  <?php
    //header('Location: ' . 'http://' . $_SERVER['HTTP_HOST']);
    exit();
}

$modulo = basename($_SERVER['PHP_SELF']);
$permisos = $_SESSION['permisos'] ?? array();
// IMCEDEN MODULE PERMISSIONS: additions inherit only their matching existing scope.
$imcedenPermissionParents = array('ReporteSireMovimiento.php'=>'ReporteRegMovimiento.php','ReporteSireVentas.php'=>'ReporteRegistroVentas.php','VentasApp.php'=>'V_VentaForm.php','TiendaVirtual.php'=>'V_VentaForm.php','KpiDashboard.php'=>'Dashboard.php');
if (!isset($permisos[$modulo]) && isset($imcedenPermissionParents[$modulo]) && isset($permisos[$imcedenPermissionParents[$modulo]])) $permisos[$modulo] = $permisos[$imcedenPermissionParents[$modulo]];

$permiso = isset($permisos[$modulo]) ? $permisos[$modulo] : false ;

if ($permiso) {
  if ($permiso['Lectura'] == 0) {
    include_once('error/noLectura.php');
    exit();
  }

}else {
  $parent = explode("/", $_SERVER['PHP_SELF']);
  $parent = $parent[1];
  $segments = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
  if ($parent != 'controllers' && $_SESSION['idPerfil'] != '1' && ($segments[0]!='api' && $segments[1]!='index.php')) {
    include_once('error/noAcceso.php');
    exit();
  }

  ?>
  
  <?php
}
?>
