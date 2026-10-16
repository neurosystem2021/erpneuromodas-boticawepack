<?php
require_once __DIR__ . '/../models/EmpresaConfig.php';
$headerEmpresaConfig = empresaConfigObtener();
$headerMenuPlanes = json_decode(empresaConfigValor($headerEmpresaConfig, 'menu_planes_json', '{}'), true);
if (!is_array($headerMenuPlanes)) $headerMenuPlanes = array();
$headerMenuPlanActivo = empresaConfigValor($headerEmpresaConfig, 'menu_plan_activo', 'COMPLETO');
$headerMenuPlanConfig = isset($headerMenuPlanes[$headerMenuPlanActivo]) && is_array($headerMenuPlanes[$headerMenuPlanActivo]) ? $headerMenuPlanes[$headerMenuPlanActivo] : array();
$headerMenuDeshabilitados = isset($headerMenuPlanConfig['disabled']) && is_array($headerMenuPlanConfig['disabled']) ? $headerMenuPlanConfig['disabled'] : array();
$headerGruposDeshabilitados = isset($headerMenuPlanConfig['disabled_groups']) && is_array($headerMenuPlanConfig['disabled_groups']) ? $headerMenuPlanConfig['disabled_groups'] : array();
$headerZonaNombre = empresaConfigValor($headerEmpresaConfig, 'sistema_zona_horaria', 'America/Lima');
try { $headerZonaSistema = new DateTimeZone($headerZonaNombre); } catch (Throwable $e) { $headerZonaNombre = 'America/Lima'; $headerZonaSistema = new DateTimeZone($headerZonaNombre); }
$headerLogoEmpresa = empresaConfigValor($headerEmpresaConfig, 'logo_principal', 'resources/images/cabecera.png');
$headerVigenciaActiva = empresaConfigValor($headerEmpresaConfig, 'vigencia_aviso_activo', '0') === '1';
$headerVigenciaEstado = strtoupper(empresaConfigValor($headerEmpresaConfig, 'vigencia_estado_pago', 'PENDIENTE'));
$headerVigenciaHasta = empresaConfigValor($headerEmpresaConfig, 'vigencia_hasta', '');
$headerFechaLimitePago = empresaConfigValor($headerEmpresaConfig, 'vigencia_fecha_ultimo_pago', '');
$headerFechaAvisoVigencia = preg_match('/^\d{4}-\d{2}-\d{2}$/', $headerFechaLimitePago) ? $headerFechaLimitePago : $headerVigenciaHasta;
$headerVigenciaAnticipacion = max(1, min(365, (int) empresaConfigValor($headerEmpresaConfig, 'vigencia_dias_anticipacion', '20')));
$headerVigenciaDias = null;
$headerBloqueoDias = null;
$headerVigenciaClase = 'vigencia-info';
$headerVigenciaTexto = '';
$headerVigenciaIcono = empresaConfigValor($headerEmpresaConfig, 'vigencia_icono', 'bell');
if (!in_array($headerVigenciaIcono, array('bell','clock-o','exclamation-triangle','info-circle','shield','hourglass-half'), true)) $headerVigenciaIcono = 'bell';
$headerVigenciaVelocidad = max(6, min(90, (int) empresaConfigValor($headerEmpresaConfig, 'vigencia_velocidad_segundos', '22')));
$headerVigenciaEmoji = empresaConfigValor($headerEmpresaConfig, 'vigencia_emoji', '');
$headerVigenciaGif = empresaConfigValor($headerEmpresaConfig, 'vigencia_gif', '');
$headerPagoActivo = empresaConfigValor($headerEmpresaConfig, 'pago_flotante_activo', '0') === '1';
$headerPagoWhatsapp = preg_replace('/\D+/', '', empresaConfigValor($headerEmpresaConfig, 'pago_whatsapp', ''));
$headerPagoCuentas = trim(empresaConfigValor($headerEmpresaConfig, 'pago_cuentas', ''));
$headerPagoQr = empresaConfigValor($headerEmpresaConfig, 'pago_qr', '');
$headerPagoBase = rtrim(str_replace('\\', '/', dirname(dirname((string) ($_SERVER['SCRIPT_NAME'] ?? '')))), '/.');
$headerPagoQrUrl = $headerPagoQr !== '' ? (($headerPagoBase ? $headerPagoBase . '/' : '/') . ltrim($headerPagoQr, '/')) : '';
$headerVigenciaGifUrl = '';
if ($headerVigenciaGif !== '') {
  $headerBasePath = rtrim(str_replace('\\', '/', dirname(dirname((string) ($_SERVER['SCRIPT_NAME'] ?? '')))), '/.');
  $headerVigenciaGifUrl = ($headerBasePath ? $headerBasePath . '/' : '/') . ltrim($headerVigenciaGif, '/');
}
if ($headerVigenciaEstado !== 'PAGADO' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $headerFechaAvisoVigencia)) {
  try {
    $headerZona = $headerZonaSistema;
    $headerHoy = new DateTime('today', $headerZona);
    $headerFin = DateTime::createFromFormat('!Y-m-d', $headerFechaAvisoVigencia, $headerZona);
    if ($headerFin) {
      $headerVigenciaDias = (int) $headerHoy->diff($headerFin)->format('%r%a');
      $headerVigenciaClase = $headerVigenciaDias < 0 ? 'vigencia-vencida' : ($headerVigenciaDias <= 7 ? 'vigencia-urgente' : ($headerVigenciaDias <= 30 ? 'vigencia-advertencia' : 'vigencia-info'));
      $headerVigenciaTexto = empresaConfigValor($headerEmpresaConfig, 'vigencia_mensaje', 'Su servicio está próximo a vencer. Comuníquese con soporte para mantenerlo activo.');
    }
  } catch (Throwable $e) { $headerVigenciaDias = null; }
}
$headerPaginaActual = strtolower(basename((string) ($_SERVER['PHP_SELF'] ?? '')));
$headerEsVentasApp = $headerPaginaActual === 'ventasapp.php';
$headerEsPuntoVenta = in_array($headerPaginaActual, array('v_ventaform.php', 'v_ventaformnuevo.php'), true);
if ($headerEsVentasApp) {
  $headerVigenciaActiva = empresaConfigValor($headerEmpresaConfig, 'ventas_app_aviso_activo', '0') === '1';
  $headerVigenciaEstado = strtoupper(empresaConfigValor($headerEmpresaConfig, 'ventas_app_estado_pago', 'PAGADO'));
  $headerFechaLimitePago = empresaConfigValor($headerEmpresaConfig, 'ventas_app_fecha_limite', '');
  $headerFechaAvisoVigencia = $headerFechaLimitePago;
  $headerVigenciaAnticipacion = max(1, min(365, (int) empresaConfigValor($headerEmpresaConfig, 'ventas_app_dias_anticipacion', '20')));
  $headerVigenciaTexto = empresaConfigValor($headerEmpresaConfig, 'ventas_app_mensaje', 'La vigencia de Ventas App esta proxima a vencer. Comuniquese con soporte.');
  $headerVigenciaDias = null;
  if ($headerVigenciaEstado !== 'PAGADO' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $headerFechaAvisoVigencia)) {
    $headerAppFin = DateTime::createFromFormat('!Y-m-d', $headerFechaAvisoVigencia, $headerZonaSistema);
    if ($headerAppFin) {
      $headerVigenciaDias = (int) (new DateTime('today', $headerZonaSistema))->diff($headerAppFin)->format('%r%a');
      $headerVigenciaClase = $headerVigenciaDias < 0 ? 'vigencia-vencida' : ($headerVigenciaDias <= 7 ? 'vigencia-urgente' : ($headerVigenciaDias <= 30 ? 'vigencia-advertencia' : 'vigencia-info'));
    }
  }
}
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $headerFechaLimitePago)) {
  try {
    $headerBloqueoZona = $headerZonaSistema;
    $headerBloqueoHoy = new DateTime('today', $headerBloqueoZona);
    $headerBloqueoFecha = DateTime::createFromFormat('!Y-m-d', $headerFechaLimitePago, $headerBloqueoZona);
    if ($headerBloqueoFecha) $headerBloqueoDias = (int) $headerBloqueoHoy->diff($headerBloqueoFecha)->format('%r%a');
  } catch (Throwable $e) { $headerBloqueoDias = null; }
}
$headerBloqueoAppActivo = empresaConfigValor($headerEmpresaConfig, 'ventas_app_bloqueo_activo', '0') === '1';
$headerPuntoVentaBloqueado = (($headerEsPuntoVenta) || ($headerEsVentasApp && $headerBloqueoAppActivo)) && $headerVigenciaEstado !== 'PAGADO' && $headerBloqueoDias !== null && $headerBloqueoDias <= 0;
$headerMostrarAvisoPago = $headerVigenciaActiva && $headerVigenciaDias !== null && $headerVigenciaDias <= $headerVigenciaAnticipacion;
$headerSocialActivo = empresaConfigValor($headerEmpresaConfig, 'social_aviso_activo', '0') === '1';
$headerSocialMensaje = empresaConfigValor($headerEmpresaConfig, 'social_mensaje', '');
$headerSocialTipo = strtoupper(empresaConfigValor($headerEmpresaConfig, 'social_tipo', 'FESTEJO'));
$headerSocialDesde = empresaConfigValor($headerEmpresaConfig, 'social_desde', '');
$headerSocialHasta = empresaConfigValor($headerEmpresaConfig, 'social_hasta', '');
$headerSocialHoraInicio = empresaConfigValor($headerEmpresaConfig, 'social_hora_inicio', '00:00');
$headerSocialHoraFin = empresaConfigValor($headerEmpresaConfig, 'social_hora_fin', '23:59');
$headerSocialIntervalo = max(1, min(1440, (int) empresaConfigValor($headerEmpresaConfig, 'social_intervalo_minutos', '30')));
$headerSocialDuracion = max(5, min(120, (int) empresaConfigValor($headerEmpresaConfig, 'social_duracion_segundos', '12')));
$headerSocialAhora = new DateTime('now', $headerZonaSistema);
$headerSocialFecha = $headerSocialAhora->format('Y-m-d');
$headerSocialHora = $headerSocialAhora->format('H:i');
$headerSocialEnHorario = $headerSocialHoraInicio <= $headerSocialHoraFin
    ? ($headerSocialHora >= $headerSocialHoraInicio && $headerSocialHora <= $headerSocialHoraFin)
    : ($headerSocialHora >= $headerSocialHoraInicio || $headerSocialHora <= $headerSocialHoraFin);
$headerSocialProgramado = $headerSocialActivo && $headerSocialMensaje !== '' && $headerSocialFecha >= $headerSocialDesde && $headerSocialFecha <= $headerSocialHasta && $headerSocialEnHorario;
$headerSocialAvisos = json_decode(empresaConfigValor($headerEmpresaConfig, 'social_avisos_json', '[]'), true);
$headerSocialActivos = array();
if (is_array($headerSocialAvisos)) foreach ($headerSocialAvisos as $aviso) {
  if (!is_array($aviso) || trim((string)($aviso['mensaje'] ?? '')) === '') continue;
  $ad = (string)($aviso['desde'] ?? ''); $ah = (string)($aviso['hasta'] ?? ''); $hi = (string)($aviso['hora_inicio'] ?? '00:00'); $hf = (string)($aviso['hora_fin'] ?? '23:59');
  $enHora = $hi <= $hf ? ($headerSocialHora >= $hi && $headerSocialHora <= $hf) : ($headerSocialHora >= $hi || $headerSocialHora <= $hf);
  if ($headerSocialFecha < $ad || $headerSocialFecha > $ah || !$enHora) continue;
  $icono = strtolower((string)($aviso['icono'] ?? 'star')); if (!in_array($icono,array('star','heart','gift','birthday-cake','bell','smile-o','info-circle'),true)) $icono='star';
  $headerSocialActivos[] = array('id'=>(string)($aviso['id']??md5(json_encode($aviso))),'tipo'=>strtolower((string)($aviso['tipo']??'festejo')),'icono'=>$icono,'emoji'=>(string)($aviso['emoji']??''),'gif'=>(string)($aviso['gif']??''),'mensaje'=>(string)$aviso['mensaje'],'intervalo'=>max(1,min(1440,(int)($aviso['intervalo']??30))),'duracion'=>max(5,min(120,(int)($aviso['duracion']??15))));
}
// Los avisos sociales se alimentan exclusivamente de su lista programada.
// No usar los campos antiguos evita mezclar el aviso de renovación con festejos.
$headerSocialProgramado = !empty($headerSocialActivos);
define("SITE_URL", "http://" . $_SERVER['SERVER_NAME'] . '/');
define("", SITE_URL . "views/Gen_ProductoForm.php");
define("Forma_Farmaceutica", SITE_URL . "views/Gen_ProductoFormaFarmaceuticaForm.php");
define("Medicion", SITE_URL . "views/Gen_ProductoMedicionForm.php");
define("Marca", SITE_URL . "views/Gen_ProductoMarcaForm.php");
define("Categoria", SITE_URL . "views/Gen_ProductoCategoriaForm.php");
define("Compuesto", SITE_URL . "views/Gen_ProductoCompuestoForm.php");
define("PuntoVenta", SITE_URL . "views/V_VentaForm.php");
define("VentasApp", SITE_URL . "views/VentasApp.php");
define("TiendaVirtual", SITE_URL . "views/TiendaVirtual.php");
define("CRM", SITE_URL . "views/CRM.php");
define("PreOrden", SITE_URL . "views/V_PreOrden.php");
define("Experto", SITE_URL . "views/V_ExpertoForm.php");
define("Inventario", SITE_URL . "views/Lo_InventarioForm.php");
define("RegVenta", SITE_URL . "views/ve_regventaform.php");
define("Cierre", SITE_URL . "views/ve_cierreform.php");
define("CambioPrecioBloque", SITE_URL . "views/ve_cambiopreciobloque.php");
define("Reimpresion", SITE_URL . "views/ve_reimpresionDocumentos.php");
define("ReporteUtilidadBruta", SITE_URL . "views/ve_reporteUtilidadBruta.php");
define("RegMovimiento", SITE_URL . "views/lo_regmovimiento.php");
define("RegCompraContable", SITE_URL . "views/lo_regcompracontable.php");
define("ReporteStock", SITE_URL . "views/Lo_ReporteStockForm.php");
define("ReporteKardex", SITE_URL . "views/lo_kardexform.php");
define("ReporteKardexValorizado", SITE_URL . "views/lo_kardexvalorizadoform.php");
define("Login", SITE_URL . "index.php");
define("CajaYBanco", SITE_URL . "views/FrmCb_CajaBanco.php");
define("Logout", SITE_URL . "controllers/logout.php");
// define("Usuarios", SITE_URL . "views/Seg_UsuarioForm.php");
define("Roles", SITE_URL . "views/Seg_UsuarioPerfil.php");
define("CajaYBancoBuscador", SITE_URL . "views/FrmCb_CajaBancoBuscador.php");
define("BuscarVencimiento", SITE_URL . "views/Lo_BuscarVencimiento.php");
define("GenerarCodigoBarra", SITE_URL . "views/Lo_GenerarCodigoBarra.php");
define("Modelo", SITE_URL . "views/Gen_ProductoModelo.php");
define("ReporteUtilidadBrutaPorUsuario", SITE_URL . "views/ReporteUtilidadBrutaPorUsuario.php");
define("ReporteMovmientoPorProveedor", SITE_URL . "views/ReporteMovmientoPorProveedor.php");
define("ReporteRankinCliente", SITE_URL . "views/ReporteRankinCliente.php");
define("ListadoImpresoras", SITE_URL . "views/ListadoImpresoras.php");
define("lo_ordenes", SITE_URL . "views/lo_ordenes.php");
define("lo_ordencompra", SITE_URL . "views/lo_ordencompra.php");
define("Usuarios", SITE_URL . "views/Seguridad.php");
define("PlanesMenu", SITE_URL . "views/Seg_PlanesMenu.php");
define("EmpresaConfig", SITE_URL . "views/EmpresaConfig.php");
define("ComunicadosVigencia", SITE_URL . "views/Seg_ComunicadosVigencia.php");
define("ConfigurarEtiquetas", SITE_URL . "views/Seg_ConfigurarEtiquetas.php");
define("ConfigurarCorreo", SITE_URL . "views/Seg_CorreoConfig.php");
define("ve_reporteutilidabruta", SITE_URL . "views/ve_reporteutilidabruta.php");
define("ReporteRegistroVentas", SITE_URL . "views/ReporteRegistroVentas.php");
define("CajaBancos", SITE_URL . "views/CajaBancos.php");
define("reporteregmovimiento", SITE_URL . "views/reporteregmovimiento.php");
define("DashBoard", SITE_URL . "views/DashBoard.php");
define("KardexValorizado", SITE_URL . "views/KardexValorizado.php");
define("reportes", SITE_URL . "views/reportes.php");
define("KpiDashboard", SITE_URL . "views/KpiDashboard.php");
define("Lo_CajaBanco", SITE_URL . "views/Lo_CajaBanco.php");
define("CierreReporte", SITE_URL . "/views/v_cierrereporte.php");
define("Lo_MovimientoXML", SITE_URL . "views/Lo_MovimientoXML.php");
define("BusquedaPorModelo", SITE_URL . "views/BusquedaPorModelo.php");
define("ReporteSireMovimiento", SITE_URL . "views/ReporteSireMovimiento.php");
define("ReporteSireVentas", SITE_URL . "views/ReporteSireVentas.php");

date_default_timezone_set($headerZonaNombre);
?>

<script>
  sessionStorage.clear();
  sessionStorage.setItem('User', "<?php echo $_SESSION['user'] ?>");
  sessionStorage.setItem('IdPerfil', "<?php echo $_SESSION['idPerfil'] ?>")
  sessionStorage.setItem('IdAlmacen', "<?php echo $_SESSION['idAlmacen'] ?>")
  sessionStorage.setItem('Escritura', "<?php echo json_encode($permiso['Escritura']); ?>");

  function datos() {
    var compra = document.getElementById('compra');
    var venta = document.getElementById('venta');
    var estado = document.getElementById('tipoCambioEstado');
    var bloque = document.getElementById('tipoCambioSunat');
    if (!compra || !venta) return;

    var guardado = null;
    try { guardado = JSON.parse(localStorage.getItem('tipoCambioSunat') || 'null'); } catch (e) {}

    fetch('/api/index.php/tipo-cambio-sunat', { credentials: 'same-origin', cache: 'no-store' })
      .then(function (res) {
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return res.json();
      })
      .then(function (data) {
        var c = Number(data.compra);
        var v = Number(data.venta);
        if (!Number.isFinite(c) || !Number.isFinite(v) || c <= 0 || v <= 0) throw new Error('Cotizacion invalida');
        compra.textContent = c.toFixed(3);
        venta.textContent = v.toFixed(3);
        if (estado) estado.textContent = data.fecha ? data.fecha : 'Actualizado';
        if (bloque) {
          bloque.classList.remove('is-loading', 'is-stale');
          bloque.title = 'Tipo de cambio SUNAT' + (data.fuente ? ' - ' + data.fuente : '') + (data.fecha ? ' - ' + data.fecha : '');
        }
        try { localStorage.setItem('tipoCambioSunat', JSON.stringify(data)); } catch (e) {}
      })
      .catch(function () {
        if (guardado && Number(guardado.compra) > 0 && Number(guardado.venta) > 0) {
          compra.textContent = Number(guardado.compra).toFixed(3);
          venta.textContent = Number(guardado.venta).toFixed(3);
          if (estado) estado.textContent = 'Ultima consulta';
          if (bloque) bloque.classList.add('is-stale');
          return;
        }
        compra.textContent = '--';
        venta.textContent = '--';
        if (estado) estado.textContent = 'No disponible';
        if (bloque) bloque.classList.add('is-stale');
      });
  }
  document.addEventListener('DOMContentLoaded', datos);
</script>
<style>
  /** cambiar el color de la cab */
  .navbar-style {
    text-transform: uppercase;
    padding: 10px 15px;

    /* background: #004D40; */
    font-size: 2rem;
    font-weight: bold;
  }

  .navbar-brand {
    color: #ffffff;
    padding: auto !important;
  }

  .company-brand-logo {
    position: relative;
    display: flex !important;
    align-items: center;
    justify-content: center;
    width: 126px;
    height: 66px;
    margin-top: -8px;
    margin-left: 4px;
    overflow: visible;
    border-radius: 16px;
    isolation: isolate;
  }

  .company-brand-logo::before {
    content: "";
    position: absolute;
    inset: 7px 10px;
    z-index: -1;
    border-radius: 14px;
    background: rgba(201, 43, 226, .3);
    filter: blur(15px);
    animation: companyLogoGlow 2.8s ease-in-out infinite;
  }

  .company-brand-logo::after {
    content: "";
    position: absolute;
    top: 9px;
    bottom: 9px;
    left: -28px;
    width: 20px;
    border-radius: 50%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,.9), transparent);
    filter: blur(2px);
    transform: skewX(-18deg);
    animation: companyLogoShine 3.8s ease-in-out infinite;
    pointer-events: none;
  }

  .company-brand-logo img {
    width: 112px !important;
    max-height: 58px;
    margin: 0 !important;
    object-fit: contain;
    filter: drop-shadow(0 4px 7px rgba(0,0,0,.3)) brightness(1.12) saturate(1.15);
    animation: companyLogoPulse 2.8s ease-in-out infinite;
  }

  @keyframes companyLogoGlow {
    0%,100% { opacity: .35; transform: scale(.93); }
    50% { opacity: .9; transform: scale(1.08); }
  }
  @keyframes companyLogoPulse {
    0%,100% { transform: scale(1); }
    50% { transform: scale(1.045); }
  }
  @keyframes companyLogoShine {
    0%,62% { left: -28px; opacity: 0; }
    70% { opacity: .8; }
    86%,100% { left: 128px; opacity: 0; }
  }
  @media (prefers-reduced-motion: reduce) {
    .company-brand-logo::before,.company-brand-logo::after,.company-brand-logo img { animation: none !important; }
  }

  .dropdown {

    padding: 2px 2px;
    border-radius: 20px;
    margin: 5px 10px;
    color: #ffffff !important;
    /* background: #EEEEEE; */
  }

  .navli {
    padding: 5px;
    border: 0 0 1px 0 solid #ffffff !important;
    font-weight: bold !important;
    color: #ffffff !important;
  }

  .imgLogo {
    margin-top: 15px;
    max-height: 30px !important;

  }

  .imgLogoNeuro {
    max-width: 200px !important;

  }

  .navbar .dropdown:hover .dropdown-menu {
    display: block;
  }

  .navli {
    font-weight: bold;
    font-size: 2rem;
  }

  .dropdown-menu {
    left: 0 !important;
    right: auto !important;

  }

  .navbar-nav {
    font-size: 2rem !important;
  }

  .navbar-nav>li>a {
    font-size: 1.03em !important;
    padding: 10px 0px;
  }

  .navbar-nav>li>a img {
    width: 1.5em;
    margin: 0;
  }
</style>

<style>
  @media (max-width: 1024px) {

    .navbar-header .navbar-toggle {
      display: block !important;
    }

    #myNavbar {
      display: none !important;
    }

    #myNavbar.in {
      display: block !important;
      overflow-x: visible;
      -webkit-overflow-scrolling: touch;
    }
  }

  .icon-bar {
    background: #ffffff !important;
  }
</style>

<link rel="stylesheet" href="/resources/css/header-premium.css?v=<?php echo @filemtime(__DIR__ . '/../resources/css/header-premium.css') ?: time(); ?>">
<link rel="stylesheet" href="/resources/css/petrol-screen-theme.css?v=<?php echo @filemtime(__DIR__ . '/../resources/css/petrol-screen-theme.css') ?: time(); ?>">

<script>
  // Evita que el navbar se cierre al hacer clic en un dropdown
  $(document).on('click', '.navbar-collapse.in .dropdown-toggle', function(e) {
    e.stopPropagation(); // evita que el evento burbujee y cierre el navbar
  });
</script>
<nav class="navbar navbar-style " id="navBar">
  <div class="container-fluid">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand company-brand-logo" href="<?php echo Login; ?>"><img src="/<?php echo htmlspecialchars(ltrim($headerLogoEmpresa, '/'), ENT_QUOTES, 'UTF-8'); ?>" alt="Logo de la empresa"></a>
    </div>
    <div class="collapse navbar-collapse" id="myNavbar">
      <ul class="nav navbar-nav navbar-right">
        <li class="dropdown">
          <a class="dropdown-toggle" data-toggle="dropdown" href="#"> <i class="fa fa-barcode" aria-hidden="true"></i> Producto
            <span class="caret"></span></a>
          <ul class="dropdown-menu dropdown-menu-right">
            <li class="navli"><a href="/views/Gen_ProductoForm.php" class="text-primary-color"><i class="fa fa-cube"></i> Modificar Producto</a></li>
            <li class="navli"><a href="/views/ListadoImpresoras.php" class="text-primary-color"><i class="fa fa-print"></i> Impresoras</a></li>
            <li role="separator" class="divider"></li>
            <li class="navli"><a href="/views/GestionContactos.php?tipo=clientes" class="text-primary-color"><i class="fa fa-users"></i> Clientes</a></li>
            <li class="navli"><a href="/views/GestionContactos.php?tipo=proveedores" class="text-primary-color"><i class="fa fa-truck"></i> Proveedores</a></li>
            <!--<li class="navli"><a href="<?php echo Marca; ?>" >Marca</a></li>-->
            <!--<li class="navli"><a href="<?php echo Forma_Farmaceutica; ?>">Forma Farmaceutica</a></li>-->
            <!--<li class="navli"><a href="<?php echo Modelo; ?>">Modelo</a></li>
          <li class="navli"><a href="<?php echo Medicion; ?>">Medicion</a></li>
          <li class="navli"><a href="<?php echo Categoria; ?>">Categoria</a></li>-->
            <!--<li class="navli"><a href="<?php echo Compuesto; ?>">Compuesto</a></li>-->
          </ul>
        </li>

        <li class="dropdown">
          <a href="" class="dropdown-toggle" data-toggle="dropdown"> <i class="fa fa-clipboard"></i> Logistica
            <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li class="navli"><a href="<?php echo Inventario; ?>">REGISTRO DE COMPRAS</a></li>
            <li class="navli"><a href="<?php echo Lo_MovimientoXML; ?>">CARGAR COMPRAS XML</a></li>
            <!--<li class="navli"><a href="<?php echo GenerarCodigoBarra; ?>">CODIGO DE BARRA</a></li>-->
            <li role="separator" class="divider"></li>

            <!--<li class="dropdown-header">KARDEX CONTABLE</li>-->
            <li class="navli"><a href="<?php echo KardexValorizado; ?>">1. KARDEX VALORIZADO</a></li>
            <!--<li class="navli"><a href="<?php echo ReporteKardex . "?Tipo=0"; ?>">1. KARDEX</a></li>-->
            <!--<li class="navli"><a href="<?php echo ReporteKardexValorizado . "?Tipo=0"; ?>">1. KARDEX VALORIZADO</a></li>-->
            <!--<li class="dropdown-header">KARDEX FECHA STOCK</li>
            <li class="navli"><a href="<?php echo ReporteKardex . "?Tipo=1"; ?>">1. KARDEX</a></li>
            <li class="navli"><a href="<?php echo ReporteKardexValorizado . "?Tipo=1"; ?>">2. KARDEX VALORIZADO</a></li>-->

            <li class="dropdown-header">KARDEX</li>
            <li class="navli"><a href="/views/lo_kardex.php">1. KARDEX</a></li>
            <li class="navli"><a href="/views/formato37.php">FORMATO 3.7</a></li>
            <li class="navli"><a href="/views/lo_reportemasmovimiento.php">ARTICULOS CON MAS MOVIMIENTOS</a></li>
            <li role="separator" class="divider"></li>
            <li class="navli"><a href="<?php echo ReporteStock; ?>"></a></li>
            <li class="navli"><a href="<?php echo ReporteStock; ?>">STOCK</a></li>
            <!-- <li class="navli"><a href="<?php echo RegMovimiento; ?>">REGISTRO DE MOVIMIENTO</a></li> -->
            <li class="navli"><a href="<?php echo reporteregmovimiento; ?>">REGISTRO DE MOVIMIENTO</a></li>
            <li class="navli"><a href="<?php echo ReporteMovmientoPorProveedor; ?>">REPORTE MOVIMIENTO POR PROVEEDOR</a></li>
            <li class="navli"><a href="<?php echo ReporteRankinCliente; ?>">REPORTE COMPRAS POR CLIENTE</a></li>
            <!--<li class="navli"><a href="<?php echo RegCompraContable; ?>">REGISTRO DE COMPRA CONTABLE</a></li>-->
            <li role="separator" class="divider"></li>
            <li class="navli"><a href="<?php echo lo_ordencompra; ?>">NUEVA ORDEN COMPRA</a></li>
            <li class="navli"><a href="<?php echo lo_ordenes; ?>">LISTADO ORDENES COMPRA</a></li>
            <li role="separator" class="divider"></li>
            <li class="navli"><a href="/views/lo_confrontacion.php">Confrontación de Inventario</a></li>
            <li class="navli"><a href="<?php echo ReporteSireMovimiento; ?>"><img src="../resources/images/sire.png" class="imgLogo" /></a></li>
            <!--<li class="navli"><a href="<?php echo BuscarVencimiento; ?>">PRODUCTOS POR VENCER</a></li>-->
          </ul>
        </li>
        <!--<li class="navli"><a href="<?php echo Experto; ?>">Sistema Experto</a></li>-->
        <li class="dropdown">
          <a href="" class="dropdown-toggle" data-toggle="dropdown"> <i class="fa fa-cart-plus"></i> Ventas
            <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li class="navli"><a href="<?php echo DashBoard; ?>">DashBoard</a></li>
                                <li class="navli"><a href="<?php echo PuntoVenta; ?>">Punto de venta</a></li>
                                <li class="navli"><a href="<?php echo VentasApp; ?>"><i class="fa fa-mobile"></i> Ventas App</a></li>
            <li class="navli"><a href="<?php echo TiendaVirtual; ?>"><i class="fa fa-shopping-bag"></i> Tienda virtual</a></li>
            <li class="navli"><a href="<?php echo BusquedaPorModelo; ?>">Busqueda Producto Por Modelo</a></li>
            <li role="separator" class="divider"></li>
            <!-- <li class="navli"><a href="<?php echo RegVenta; ?>">Registro de Ventas</a></li> -->
            <li class="navli"><a href="<?php echo ReporteRegistroVentas; ?>">Registro de Ventas</a></li>
            <li class="navli"><a href="<?php echo SITE_URL . "views/ve_regventaformvendedor.php" ?>">Registro de Ventas VENDEDOR</a></li>
            <li class="navli"><a href="<?php echo Cierre; ?>">Cierre de Caja</a></li>
            <li class="navli"><a href="<?php echo CierreReporte; ?>">Reporte Cierre de Caja</a></li>
            <!--<li class="navli"><a href="<?php echo CambioPrecioBloque; ?>">Cambio de precio por bloque</a></li>-->
            <li role="separator" class="divider"></li>
            <li class="navli"><a href="<?php echo Reimpresion; ?>">Reimpresión de Documentos</a></li>
            <!-- <li class="navli"><a href="<?php echo ReporteUtilidadBruta; ?>">Reporte Utilidad Bruta</a></li> -->
            <li class="navli"><a href="<?php echo ve_reporteutilidabruta; ?>">Reporte Utilidad Bruta</a></li>
            <li class="navli"><a href="<?php echo ReporteUtilidadBrutaPorUsuario; ?>">Reporte Utilidad Bruta Por Usuario</a></li>
            <li role="separator" class="divider"></li>
            <li class="navli"><a href="/views/remision.php">Generar guía de Remisión</a></li>
            <li class="navli"><a href="<?php echo ReporteSireVentas; ?>"><img src="../resources/images/sire.png" class="imgLogo" /></a></li>
          </ul>
        </li>

        <li class="dropdown">
          <a class="dropdown-toggle" data-toggle="dropdown" href="#"> <i class="fa fa-file" aria-hidden="true"></i> Facturación
            <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li class="navli"><a href="/views/facturacion.php"> Facturación Electrónica </a></li>
            <li class="navli"><a href="/api/sunat/pag_cliente/" target="_blank"> Página Cliente </a></li>
            <li class="navli"><a href="http://emision.factura.vip/" target="_blank"> Consulta CPE </a></li>
            <li role="separator" class="divider"></li>
            <li class="navli"><a href="/views/remisionLista.php"> Emitir guía de Remisión</a></li>
          </ul>

        </li>


        <li class="dropdown">
          <a class="dropdown-toggle" data-toggle="dropdown" href="#"> <i class="fa fa-bar-chart" aria-hidden="true"></i> Reportes
            <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li class="dropdown-header"><i class="fa fa-line-chart"></i> KPI / INTELIGENCIA DE NEGOCIO</li>
            <li class="navli"><a href="<?php echo KpiDashboard; ?>"><i class="fa fa-dashboard"></i> Dashboard Gerencial</a></li>
            <li class="navli"><a href="<?php echo KpiDashboard; ?>#ventas"><i class="fa fa-area-chart"></i> KPI Ventas</a></li>
            <li class="navli"><a href="<?php echo KpiDashboard; ?>#productos"><i class="fa fa-tags"></i> KPI Productos</a></li>
            <li class="navli"><a href="<?php echo KpiDashboard; ?>#inventario"><i class="fa fa-cubes"></i> KPI Inventario</a></li>
            <li class="navli"><a href="<?php echo KpiDashboard; ?>#vendedores"><i class="fa fa-trophy"></i> KPI Vendedores</a></li>
            <li class="navli"><a href="<?php echo KpiDashboard; ?>#clientes"><i class="fa fa-users"></i> KPI Clientes</a></li>
            <li class="navli"><a href="<?php echo KpiDashboard; ?>#sucursales"><i class="fa fa-building"></i> KPI Sucursales</a></li>
            <li class="navli"><a href="<?php echo KpiDashboard; ?>#alertas"><i class="fa fa-bell"></i> Alertas KPI</a></li>
            <li role="separator" class="divider"></li>
            <li class="navli"><a href="/views/reporteestadistico.php"> Reporte Estadistico </a></li>
            <li class="navli"><a href="/views/reportestockminimo.php"> Reporte Stock Mínimo </a></li>
            <li class="navli"><a href="/views/reportesdeudacliente.php"> Reporte Deuda Clientes </a></li>
            <li class="navli"><a href="/views/reportesdeudaproveedor.php"> Reporte Deuda Proveedores </a></li>
            <li class="navli"><a href="/views/reportesrankingclientes.php"> Reporte Ranking Clientes </a></li>
            <li class="navli"><a href="<?php echo reportes; ?>"> Reporte </a></li>
          </ul>

        </li>

        <li class="dropdown">
          <a class="dropdown-toggle" data-toggle="dropdown" href="#"> <i class="fa fa-credit-card-alt" aria-hidden="true"></i> Caja y Banco
            <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <!-- <li class="navli"><a href="<?php echo CajaYBanco; ?>"> Caja y Banco</a></li> -->
            <li class="navli"><a href="<?php echo CajaBancos; ?>"> Caja y Banco</a></li>
            <!-- <li class="navli"><a href="<?php echo CajaYBancoBuscador; ?>">Buscador de Caja y Banco</a></li> -->
            <li class="navli"><a href="<?php echo Lo_CajaBanco; ?>">Buscador de Caja y Banco</a></li>
          </ul>

        </li>
        <li class="dropdown">
          <a class="dropdown-toggle" data-toggle="dropdown" href="#"> <i class="fa fa-shield" aria-hidden="true"></i> Seguridad
            <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li class="navli"><a href="<?php echo Usuarios; ?>">Usuarios</a></li>
            <?php if (strtolower(trim((string) ($_SESSION['user'] ?? ''))) === 'superadmin'): ?>
              <li class="navli"><a href="<?php echo EmpresaConfig; ?>"><i class="fa fa-building-o"></i> Datos de empresa</a></li>
              <li class="navli"><a href="<?php echo ComunicadosVigencia; ?>"><i class="fa fa-bell"></i> Comunicados y vigencia</a></li>
              <li class="navli"><a href="<?php echo ConfigurarEtiquetas; ?>"><i class="fa fa-tags"></i> Configurar etiquetas</a></li>
              <li class="navli"><a href="<?php echo ConfigurarCorreo; ?>"><i class="fa fa-envelope"></i> Configuración de correo</a></li>
              <li class="navli" data-plan-manager="1"><a href="<?php echo PlanesMenu; ?>"><i class="fa fa-toggle-on"></i> Planes y módulos</a></li>
            <?php endif; ?>
            <!--<li class="navli"><a href="/views/gestionalmacenes.php">Gestion Almacenes</a></li>-->
          </ul>
        </li>
        <li class="dropdown crm-nav-item">
          <a class="crm-nav-link" href="<?php echo CRM; ?>"><i class="fa fa-line-chart" aria-hidden="true"></i> CRM</a>
        </li>
        <?php if (false): ?>
          <li class="dropdown">
            <a href="#" onclick="window.open('http://app.factura.vip/client/login/20603883650','winname','directories=0,titlebar=0,toolbar=0,location=0,status=0,menubar=0,scrollbars=no,resizable=no,width=600,height=700');"><b>APP</b></a>
          </li>
        <?php endif; ?>
        <li class="dropdown">
          <a href="<?php echo Logout; ?>"> <i class="fa fa-sign-out" aria-hidden="true"></i></a>
        </li>
        <li class="navli exchange-rate-item">
          <div id="tipoCambioSunat" class="exchange-rate-card is-loading" aria-label="Tipo de cambio SUNAT">
            <span class="exchange-rate-title"><i class="fa fa-usd" aria-hidden="true"></i> SUNAT <small id="tipoCambioEstado">Consultando</small></span>
            <span class="exchange-rate-values">
              <span><small>Compra</small><b id="compra">--</b></span>
              <span><small>Venta</small><b id="venta">--</b></span>
            </span>
          </div>
        </li>
        <li class="navli">
          <img src="/resources/images/neurologo.png" width="140px" style="margin-left: 10px; margin-top: 10px">
        </li>
      </ul>
      <!-- <ul class="nav navbar-nav navbar-right"> -->
      <!-- <li class="navli"><a href=""><i class="fa fa-user-circle"></i> Usuario</a></li> -->
      <!-- <li class="navli"><a href=""><i class="fa fa-sign-out"></i> Cerrar Sesion</a></li> -->
      <!-- </ul> -->
    </div>
  </div>
</nav>
<script>
(function(){
  var disabled=<?php echo json_encode(array_values($headerMenuDeshabilitados), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); ?>;
  var disabledGroups=<?php echo json_encode(array_values($headerGruposDeshabilitados), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); ?>;
  var isSuperadmin=<?php echo strtolower(trim((string)($_SESSION['user']??'')))==='superadmin'?'true':'false'; ?>;
  function clean(v){return String(v||'').replace(/\s+/g,' ').trim().toLowerCase()}
  function key(a){try{var u=new URL(a.href,location.href);return u.origin===location.origin?u.pathname.toLowerCase():u.href.toLowerCase()}catch(e){return String(a.getAttribute('href')||'').toLowerCase()}}
  document.addEventListener('DOMContentLoaded',function(){
    var nav=document.getElementById('navBar');if(!nav)return;
    var submenuIcons=[
      [/cliente|ranking cliente|compras por cliente/,'users'],[/proveedor/,'truck'],[/producto|articulo|modelo|marca|categoria|compuesto|medicion|forma farmaceutica/,'cube'],
      [/inventario|stock|kardex|confrontacion/,'cubes'],[/movimiento|traslado|ingreso|salida/,'exchange'],[/orden.*compra|compra contable/,'shopping-basket'],
      [/punto de venta|ventas app|tienda virtual|pre.?orden/,'shopping-cart'],[/registro de venta|venta/,'file-text-o'],[/cierre/,'calculator'],[/reimpresion|impresora/,'print'],
      [/facturacion|factura|boleta|cpe/,'file-text'],[/remision|guia/,'truck'],[/dashboard|estadistico|kpi|inteligencia/,'line-chart'],[/reporte|sire|formato/,'bar-chart'],
      [/caja|banco|deuda|pago/,'credit-card'],[/usuario|vendedor/,'user'],[/rol|perfil|permiso|seguridad/,'shield'],[/empresa|vigencia|plan/,'building-o'],
      [/correo/,'envelope'],[/etiqueta|codigo/,'tags'],[/configur/,'cog'],[/buscar|busqueda/,'search'],[/proforma/,'clipboard'],[/alerta|comunicado/,'bell']
    ];
    nav.querySelectorAll('.dropdown-menu li.navli>a').forEach(function(a){
      var label=clean(a.textContent),icon='folder-open-o';if(!label)return;
      var currentIcon=a.querySelector(':scope>i.fa'),image=a.querySelector(':scope>img');
      if(image)return;
      if(currentIcon&&Array.from(currentIcon.classList).some(function(name){return /^fa-.+/.test(name)&&name!=='fa-'}))return;
      if(currentIcon)currentIcon.remove();
      for(var i=0;i<submenuIcons.length;i++){if(submenuIcons[i][0].test(label)){icon=submenuIcons[i][1];break}}
      var node=document.createElement('i');node.className='fa fa-'+icon;node.setAttribute('aria-hidden','true');a.insertBefore(node,a.firstChild);
    });
    nav.querySelectorAll('#myNavbar>ul>li.dropdown').forEach(function(group){
      var toggle=group.querySelector(':scope>a.dropdown-toggle'),menu=group.querySelector(':scope>ul.dropdown-menu');if(!toggle||!menu)return;
      var groupName=clean(toggle.textContent);
      if(disabledGroups.indexOf(groupName)!==-1&&!(isSuperadmin&&groupName==='seguridad'))group.style.display='none';
      menu.querySelectorAll(':scope>li.navli>a[href]').forEach(function(a){
        if(isSuperadmin&&a.closest('[data-plan-manager="1"]'))return;
        if(disabled.indexOf(key(a))!==-1)a.closest('li').style.display='none';
      });
      var visible=Array.from(menu.querySelectorAll(':scope>li.navli')).some(function(li){return li.style.display!=='none'});
      if(!visible)group.style.display='none';
    });
    var crmItem=nav.querySelector('.crm-nav-item'),crmLink=crmItem&&crmItem.querySelector(':scope>a[href]');
    if(crmItem&&crmLink&&(disabledGroups.indexOf('crm')!==-1||disabled.indexOf(key(crmLink))!==-1))crmItem.style.display='none';
  });
})();
</script>
<style>
#navBar .dropdown-menu li.navli>a{display:flex;align-items:center;gap:10px}
#navBar .dropdown-menu li.navli>a>i.fa{width:18px;flex:0 0 18px;color:#174a76;text-align:center;font-size:14px}
#navBar .dropdown-menu li.navli>a:hover>i.fa,#navBar .dropdown-menu li.navli.active>a>i.fa{color:inherit}
</style>

<?php if ($headerSocialProgramado): ?>
<style>
  .social-announcement{position:relative;z-index:90;display:none;overflow:hidden;height:43px;border-bottom:1px solid rgba(0,0,0,.08);font-family:Inter,"Segoe UI",Arial,sans-serif;box-shadow:0 6px 16px rgba(9,43,75,.09)}.social-announcement.is-visible{display:flex;align-items:center}.social-announcement.festejo{color:#693d00;background:linear-gradient(90deg,#fff1b8,#ffd76d,#ffe99f)}.social-announcement.saludo{color:#4d277d;background:linear-gradient(90deg,#eee0ff,#d9bdff,#eadcff)}.social-announcement.informativo{color:#075879;background:linear-gradient(90deg,#dff6ff,#bcecff,#d9f5ff)}.social-announcement-track{position:absolute;display:inline-flex;align-items:center;gap:10px;min-width:max-content;white-space:nowrap;font-size:13px;font-weight:850;will-change:transform}.social-announcement-icon{display:grid;place-items:center;width:29px;height:29px;border-radius:10px;color:#fff;background:rgba(0,0,0,.18);animation:socialIconBounce 1.2s ease-in-out infinite}@keyframes socialTicker{from{transform:translateX(100vw)}to{transform:translateX(-100%)}}@keyframes socialIconBounce{50%{transform:translateY(-4px) scale(1.15) rotate(7deg)}}@media(max-width:700px){.social-announcement{height:39px}.social-announcement-track{font-size:11px}}
</style>
<div class="social-announcement" id="socialAnnouncement" role="status" aria-live="polite"><div class="social-announcement-track"><span class="social-announcement-icon"><i class="fa fa-star"></i></span><span class="social-announcement-media"></span><span class="social-announcement-text"></span><span class="social-announcement-media social-announcement-media-end"></span></div></div>
<script>
document.addEventListener('DOMContentLoaded',function(){var box=document.getElementById('socialAnnouncement');if(!box)return;var notices=<?php echo json_encode($headerSocialActivos, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES); ?>,validity=document.querySelector('.system-validity-alert'),index=0,base=<?php echo json_encode(rtrim(str_replace('\\','/',dirname(dirname((string)($_SERVER['SCRIPT_NAME']??'')))),'/.')); ?>,track=box.querySelector('.social-announcement-track');if(!notices.length)return;if(validity)validity.style.display='none';function cargar(n,reiniciar){box.className='social-announcement '+n.tipo+' is-visible';track.querySelector('i').className='fa fa-'+n.icono;track.querySelector('.social-announcement-text').textContent=n.mensaje;track.querySelectorAll('.social-announcement-media').forEach(function(m){m.innerHTML='';m.style.fontSize='';if(n.gif){var img=document.createElement('img');img.src=(base?base+'/':'/')+String(n.gif).replace(/^\//,'');img.alt='';img.style.cssText='width:29px;height:29px;object-fit:contain';m.appendChild(img)}else if(n.emoji){m.textContent=n.emoji;m.style.fontSize='20px'}});if(reiniciar){track.style.animation='none';void track.offsetWidth}track.style.animation='socialTicker '+Math.max(5,Number(n.duracion)||15)+'s linear infinite'}cargar(notices[0],true);track.addEventListener('animationiteration',function(){if(notices.length<2)return;index=(index+1)%notices.length;cargar(notices[index],false)})});
</script>
<?php endif; ?>

<?php if ($headerMostrarAvisoPago): ?>
<style>
  .system-validity-alert{position:relative;z-index:50;overflow:hidden;height:43px;border-bottom:1px solid rgba(0,0,0,.08);font-family:Inter,"Segoe UI",Arial,sans-serif;font-size:13px;font-weight:700;box-shadow:0 6px 16px rgba(9,43,75,.08)}
  .system-validity-alert .validity-track{position:absolute;top:0;display:inline-flex;align-items:center;gap:10px;height:43px;min-width:max-content;white-space:nowrap;will-change:transform;animation:validityTicker <?php echo $headerVigenciaVelocidad; ?>s linear infinite}
  .system-validity-alert .validity-icon,.system-validity-alert .validity-media{display:grid;place-items:center;width:29px;height:29px;flex:0 0 29px;border:1px solid rgba(255,255,255,.72);border-radius:10px;color:#fff;background:linear-gradient(135deg,#ef334f,#b91c1c);box-shadow:0 5px 14px rgba(185,28,28,.38);animation:validityIcon 1.35s ease-in-out infinite}
  .system-validity-alert .validity-media{margin-left:-5px;border:0;background:transparent;box-shadow:none;font-size:19px}.system-validity-alert .validity-gif{display:block;width:29px;height:29px;border-radius:0;object-fit:contain;background:transparent}.system-validity-alert .validity-emoji{filter:drop-shadow(0 2px 2px rgba(0,0,0,.25))}
  .system-validity-alert .validity-copy{display:inline-flex;align-items:center;text-shadow:0 1px 0 rgba(255,255,255,.28)}.system-validity-alert strong{font-weight:950}.system-validity-alert .validity-days{display:inline-flex;align-items:center;margin-left:10px;padding:5px 13px;border:1px solid rgba(255,255,255,.65);border-radius:999px;color:#fff;background:linear-gradient(135deg,#dc2626,#b91c1c);white-space:nowrap;box-shadow:0 5px 14px rgba(153,27,27,.35);text-shadow:none;font-weight:900}
  .system-validity-alert.vigencia-info{color:#fff;background:linear-gradient(100deg,#075985,#0284c7 52%,#06b6d4)}.system-validity-alert.vigencia-advertencia{color:#102a43;background:linear-gradient(100deg,#ffd43b,#ffb703 55%,#ffe066)}.system-validity-alert.vigencia-urgente{color:#fff;background:linear-gradient(100deg,#ea580c,#f97316 52%,#ef4444)}.system-validity-alert.vigencia-vencida{color:#fff;background:linear-gradient(100deg,#991b1b,#dc2626 55%,#be123c)}
  @keyframes validityTicker{from{transform:translateX(100vw)}to{transform:translateX(-100%)}}@keyframes validityIcon{0%,100%{transform:rotate(-7deg) scale(1)}50%{transform:rotate(9deg) scale(1.16)}}
  @media(max-width:700px){.system-validity-alert{height:39px;font-size:11px}.system-validity-alert .validity-track{height:39px}.system-validity-alert .validity-icon{width:27px;height:27px;flex-basis:27px}}
  @media(prefers-reduced-motion:reduce){.system-validity-alert .validity-track{position:static;justify-content:center;width:100%;animation:none}.system-validity-alert .validity-icon{animation:none}}
</style>
<div class="system-validity-alert <?php echo $headerVigenciaClase; ?>" role="alert"><div class="validity-track">
  <span class="validity-icon"><i class="fa fa-<?php echo htmlspecialchars($headerVigenciaIcono, ENT_QUOTES, 'UTF-8'); ?>"></i></span>
  <?php if ($headerVigenciaGifUrl !== ''): ?><span class="validity-media"><img class="validity-gif" src="<?php echo htmlspecialchars($headerVigenciaGifUrl, ENT_QUOTES, 'UTF-8'); ?>" alt=""></span><?php elseif ($headerVigenciaEmoji !== ''): ?><span class="validity-media"><span class="validity-emoji"><?php echo htmlspecialchars($headerVigenciaEmoji, ENT_QUOTES, 'UTF-8'); ?></span></span><?php endif; ?>
  <span class="validity-copy"><strong><?php echo $headerVigenciaDias < 0 ? 'Servicio vencido:' : 'Renovación próxima:'; ?></strong>&nbsp;<?php echo htmlspecialchars($headerVigenciaTexto, ENT_QUOTES, 'UTF-8'); ?>
    <span class="validity-days"><?php echo $headerVigenciaDias < 0 ? 'Venció hace ' . abs($headerVigenciaDias) . ' día' . (abs($headerVigenciaDias) === 1 ? '' : 's') : ($headerVigenciaDias === 0 ? 'Vence hoy' : 'Quedan ' . $headerVigenciaDias . ' día' . ($headerVigenciaDias === 1 ? '' : 's')); ?></span>
  </span>
  <?php if ($headerVigenciaGifUrl !== ''): ?><span class="validity-media"><img class="validity-gif" src="<?php echo htmlspecialchars($headerVigenciaGifUrl, ENT_QUOTES, 'UTF-8'); ?>" alt=""></span><?php elseif ($headerVigenciaEmoji !== ''): ?><span class="validity-media"><span class="validity-emoji"><?php echo htmlspecialchars($headerVigenciaEmoji, ENT_QUOTES, 'UTF-8'); ?></span></span><?php endif; ?>
  </div>
</div>
<?php endif; ?>

<?php if ($headerPuntoVentaBloqueado): ?>
<style>
  body{overflow:hidden!important}.pos-expired-lock{position:fixed;inset:0;z-index:2147483000;display:grid;place-items:center;padding:24px;background:linear-gradient(135deg,rgba(5,28,49,.97),rgba(7,70,105,.97));font-family:Inter,"Segoe UI",Arial,sans-serif}.pos-expired-card{position:relative;overflow:hidden;width:min(620px,100%);padding:38px;border:1px solid rgba(255,255,255,.17);border-radius:25px;color:#fff;background:linear-gradient(145deg,rgba(255,255,255,.14),rgba(255,255,255,.07));box-shadow:0 28px 90px rgba(0,0,0,.4);text-align:center;backdrop-filter:blur(15px)}.pos-expired-card:before{content:"";position:absolute;right:-90px;top:-130px;width:280px;height:280px;border:44px solid rgba(255,255,255,.05);border-radius:50%}.pos-expired-icon{position:relative;display:grid;place-items:center;width:82px;height:82px;margin:0 auto 21px;border:1px solid rgba(255,255,255,.2);border-radius:23px;background:linear-gradient(135deg,#ef334f,#bd2039);box-shadow:0 15px 35px rgba(214,38,63,.35);font-size:34px}.pos-expired-card small{display:block;margin-bottom:9px;color:#8edbff;font-size:11px;font-weight:900;letter-spacing:.15em}.pos-expired-card h1{margin:0 0 12px;font-size:30px;font-weight:900}.pos-expired-card p{max-width:500px;margin:0 auto;color:#d7eaf3;font-size:15px;line-height:1.55}.pos-expired-date{display:inline-flex;align-items:center;gap:8px;margin:23px 0;padding:9px 14px;border-radius:999px;color:#ffdce2;background:rgba(229,54,78,.17);font-size:12px;font-weight:800}.pos-expired-actions{display:flex;justify-content:center;gap:11px;flex-wrap:wrap}.pos-expired-actions a,.pos-expired-actions button{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-width:170px;padding:13px 18px;border-radius:12px;color:#fff!important;text-decoration:none!important;font-size:13px;font-weight:850;transition:.2s;cursor:pointer}.pos-expired-actions a:hover,.pos-expired-actions button:hover{transform:translateY(-2px);filter:brightness(1.07)}.pos-expired-manage{border:0;background:linear-gradient(135deg,#08a963,#13c77a);box-shadow:0 10px 25px rgba(8,169,99,.25)}.pos-expired-logout{border:1px solid rgba(255,255,255,.24);background:rgba(255,255,255,.1)}.pos-payment-modal{position:absolute;inset:0;z-index:5;display:none;place-items:center;padding:20px;background:rgba(3,24,42,.88);backdrop-filter:blur(7px)}.pos-payment-modal.open{display:grid}.pos-payment-box{position:relative;width:min(430px,100%);max-height:88vh;overflow:auto;padding:20px;border-radius:20px;color:#17324a;background:#fff;box-shadow:0 25px 70px rgba(0,0,0,.38);text-align:center}.pos-payment-box h2{margin:0 35px 5px;font-size:22px}.pos-payment-box>p{margin:0 0 15px;color:#60778a}.pos-payment-close{position:absolute;right:13px;top:13px;width:34px;height:34px;border:0;border-radius:10px;color:#fff;background:#e33d55;cursor:pointer}.pos-payment-qr{display:block;max-width:220px;max-height:220px;margin:0 auto 14px;border:1px solid #d5e7ef;border-radius:14px;object-fit:contain}.pos-payment-accounts{padding:13px 15px;border-radius:13px;background:#eef8fc;text-align:left;line-height:1.65;white-space:pre-line}.pos-payment-whatsapp{display:flex;align-items:center;justify-content:center;gap:8px;margin-top:13px;padding:13px;border-radius:12px;color:#fff!important;background:linear-gradient(135deg,#08a963,#16c77b);font-weight:850;text-decoration:none!important}
  @media(max-width:600px){.pos-expired-card{padding:28px 18px}.pos-expired-card h1{font-size:24px}.pos-expired-actions a{width:100%}}
</style>
<section class="pos-expired-lock" role="alertdialog" aria-modal="true" aria-labelledby="posExpiredTitle">
  <div class="pos-expired-card"><span class="pos-expired-icon"><i class="fa fa-lock"></i></span><small>FECHA LÍMITE DE PAGO CUMPLIDA</small><h1 id="posExpiredTitle"><?php echo $headerEsVentasApp ? 'Ventas App bloqueada' : 'Punto de venta bloqueado'; ?></h1><p>La fecha límite de pago ha finalizado y el pago continúa pendiente. Registre el pago o amplíe la fecha para volver a emitir ventas<?php echo $headerEsVentasApp ? ' desde la app' : ''; ?>.</p><span class="pos-expired-date"><i class="fa fa-calendar-times-o"></i> Fecha límite: <?php echo htmlspecialchars(date('d/m/Y', strtotime($headerFechaLimitePago)), ENT_QUOTES, 'UTF-8'); ?></span><div class="pos-expired-actions">
    <button class="pos-expired-manage" id="posPaymentOpen" type="button"><i class="fa fa-qrcode"></i> Ver medios de pago</button>
    <a class="pos-expired-logout" href="<?php echo Logout; ?>"><i class="fa fa-sign-out"></i> Cerrar sesión</a>
  </div><?php if (strtolower(trim((string) ($_SESSION['user'] ?? ''))) === 'superadmin'): ?><div style="margin-top:15px"><a href="<?php echo ComunicadosVigencia; ?>" style="color:#aee9ff;font-size:12px"><i class="fa fa-cog"></i> Administrar vigencia</a></div><?php endif; ?></div>
  <div class="pos-payment-modal" id="posPaymentModal"><div class="pos-payment-box"><button class="pos-payment-close" id="posPaymentClose" type="button"><i class="fa fa-times"></i></button><h2><i class="fa fa-credit-card"></i> Medios de pago</h2><p>Escanee el QR o utilice una de las cuentas disponibles.</p><?php if ($headerPagoQrUrl !== ''): ?><img class="pos-payment-qr" src="<?php echo htmlspecialchars($headerPagoQrUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="QR de Yape o Plin"><?php endif; ?><?php if ($headerPagoCuentas !== ''): ?><div class="pos-payment-accounts"><?php echo htmlspecialchars($headerPagoCuentas, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?><?php if ($headerPagoWhatsapp !== ''): ?><a class="pos-payment-whatsapp" target="_blank" rel="noopener" href="https://wa.me/<?php echo htmlspecialchars($headerPagoWhatsapp, ENT_QUOTES, 'UTF-8'); ?>?text=<?php echo rawurlencode('Hola, deseo consultar sobre el pago y la renovación del servicio.'); ?>"><i class="fa fa-whatsapp"></i> Consultar por WhatsApp</a><?php endif; ?></div></div>
</section>
<script>(function(){var modal=document.getElementById('posPaymentModal'),open=document.getElementById('posPaymentOpen'),close=document.getElementById('posPaymentClose');if(!modal||!open)return;open.addEventListener('click',function(){modal.classList.add('open')});close.addEventListener('click',function(){modal.classList.remove('open')});modal.addEventListener('click',function(e){if(e.target===modal)modal.classList.remove('open')})})();</script>
<?php endif; ?>

<?php if ($headerPagoActivo && $headerMostrarAvisoPago && !$headerPuntoVentaBloqueado && ($headerPagoWhatsapp !== '' || $headerPagoCuentas !== '' || $headerPagoQr !== '')): ?>
<style>
.payment-float{position:fixed;right:24px;bottom:25px;z-index:2147482000;font-family:Inter,"Segoe UI",Arial,sans-serif}.payment-float-button{position:relative;display:flex;align-items:center;justify-content:center;width:62px;height:62px;border:3px solid #fff;border-radius:50%;color:#fff;background:linear-gradient(135deg,#ef4444,#f97316);box-shadow:0 12px 30px rgba(220,38,38,.38);cursor:pointer;animation:paymentPulse 1.45s ease-in-out infinite}.payment-float-button i{font-size:25px}.payment-float-button:after{content:"PAGAR";position:absolute;right:51px;padding:7px 11px;border-radius:9px;color:#fff;background:#b91c1c;font-size:11px;font-weight:900;letter-spacing:.05em;box-shadow:0 6px 16px rgba(0,0,0,.2)}.payment-panel{position:absolute;right:0;bottom:76px;width:min(355px,calc(100vw - 30px));overflow:hidden;border:1px solid #cde4ef;border-radius:19px;background:#fff;box-shadow:0 24px 65px rgba(5,42,70,.28);opacity:0;visibility:hidden;transform:translateY(14px) scale(.96);transform-origin:bottom right;transition:.22s}.payment-float.open .payment-panel{opacity:1;visibility:visible;transform:none}.payment-panel-head{display:flex;align-items:center;justify-content:space-between;padding:17px 18px;color:#fff;background:linear-gradient(110deg,#082f54,#087baa,#09a8ce)}.payment-panel-head strong{display:block;font-size:16px}.payment-panel-head small{display:block;margin-top:3px;color:#d8f5ff}.payment-close{width:32px;height:32px;border:0;border-radius:9px;color:#fff;background:rgba(255,255,255,.16);cursor:pointer}.payment-panel-body{padding:16px}.payment-qr{display:block;max-width:190px;max-height:190px;margin:0 auto 14px;border:1px solid #d7e8f0;border-radius:13px;object-fit:contain}.payment-accounts{padding:13px 14px;border-radius:12px;color:#17324a;background:#f0f8fc;font-size:13px;font-weight:700;line-height:1.65;white-space:pre-line}.payment-whatsapp{display:flex;align-items:center;justify-content:center;gap:8px;margin-top:13px;padding:12px;border-radius:12px;color:#fff!important;background:linear-gradient(135deg,#08a95f,#16c878);font-weight:850;text-decoration:none!important;box-shadow:0 9px 20px rgba(8,169,95,.22)}@keyframes paymentPulse{0%,100%{transform:scale(1);filter:brightness(1)}50%{transform:scale(1.1);filter:brightness(1.18);box-shadow:0 0 0 12px rgba(239,68,68,.12),0 15px 34px rgba(220,38,38,.42)}}@media(max-width:650px){.payment-float{right:15px;bottom:17px}.payment-float-button{width:56px;height:56px}.payment-float-button:after{display:none}}
</style>
<aside class="payment-float" id="paymentFloat"><section class="payment-panel" aria-hidden="true"><header class="payment-panel-head"><div><strong><i class="fa fa-credit-card"></i> Medios de pago</strong><small>Seleccione su forma de pago</small></div><button class="payment-close" type="button" aria-label="Cerrar"><i class="fa fa-times"></i></button></header><div class="payment-panel-body">
<?php if ($headerPagoQrUrl !== ''): ?><img class="payment-qr" src="<?php echo htmlspecialchars($headerPagoQrUrl, ENT_QUOTES, 'UTF-8'); ?>" alt="QR para pagar"><?php endif; ?>
<?php if ($headerPagoCuentas !== ''): ?><div class="payment-accounts"><?php echo nl2br(htmlspecialchars($headerPagoCuentas, ENT_QUOTES, 'UTF-8')); ?></div><?php endif; ?>
<?php if ($headerPagoWhatsapp !== ''): ?><a class="payment-whatsapp" target="_blank" rel="noopener" href="https://wa.me/<?php echo htmlspecialchars($headerPagoWhatsapp, ENT_QUOTES, 'UTF-8'); ?>?text=<?php echo rawurlencode('Hola, deseo información para realizar mi pago.'); ?>"><i class="fa fa-whatsapp"></i> Contactar por WhatsApp</a><?php endif; ?>
</div></section><button class="payment-float-button" type="button" aria-label="Ver medios de pago"><i class="fa fa-money"></i></button></aside>
<script>(function(){var box=document.getElementById('paymentFloat');if(!box)return;var panel=box.querySelector('.payment-panel');function toggle(open){box.classList.toggle('open',open);panel.setAttribute('aria-hidden',open?'false':'true')}box.querySelector('.payment-float-button').addEventListener('click',function(){toggle(!box.classList.contains('open'))});box.querySelector('.payment-close').addEventListener('click',function(){toggle(false)});document.addEventListener('click',function(e){if(!box.contains(e.target))toggle(false)});document.addEventListener('keydown',function(e){if(e.key==='Escape')toggle(false)})})();</script>
<?php endif; ?>


<div id="loading"></div>

<script>
  sessionStorage.clear();
  sessionStorage.setItem('User', "<?php echo strtolower(trim($_SESSION['user'])) ?>");
  sessionStorage.setItem('IdPerfil', "<?php echo $_SESSION['idPerfil'] ?>")
  sessionStorage.setItem('IdAlmacen', "<?php echo $_SESSION['idAlmacen'] ?>")
  sessionStorage.setItem('HostUrl', "<?php echo $_SESSION['hostUrl'] ?>")
</script>
