<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();

require_once __DIR__ . '/../models/EmpresaConfig.php';
$headerEmpresaConfig = empresaConfigObtener();
$headerMenuPlanes = json_decode(empresaConfigValor($headerEmpresaConfig, 'menu_planes_json', '{}'), true);
if (!is_array($headerMenuPlanes)) $headerMenuPlanes = array();
$headerMenuPlanActivo = empresaConfigValor($headerEmpresaConfig, 'menu_plan_activo', 'COMPLETO');
$headerMenuPlanConfig = isset($headerMenuPlanes[$headerMenuPlanActivo]) && is_array($headerMenuPlanes[$headerMenuPlanActivo]) ? $headerMenuPlanes[$headerMenuPlanActivo] : array();
$headerMenuDeshabilitados = isset($headerMenuPlanConfig['disabled']) && is_array($headerMenuPlanConfig['disabled']) ? $headerMenuPlanConfig['disabled'] : array();
$headerGruposDeshabilitados = isset($headerMenuPlanConfig['disabled_groups']) && is_array($headerMenuPlanConfig['disabled_groups']) ? $headerMenuPlanConfig['disabled_groups'] : array();
require_once __DIR__.'/../models/ModuleAccess.php';
if(strcasecmp((string)($_SESSION['user']??''),'superadmin')!==0){
 $moduleUserRules=moduleAccessPolicy()['users'][$_SESSION['user']??'']??[];
 $headerMenuDeshabilitados=array_values(array_unique(array_merge($headerMenuDeshabilitados,$moduleUserRules['disabled']??[])));
 $headerGruposDeshabilitados=array_values(array_unique(array_merge($headerGruposDeshabilitados,$moduleUserRules['disabled_groups']??[])));
}
$headerZonaNombre = empresaConfigValor($headerEmpresaConfig, 'sistema_zona_horaria', 'America/Lima');
try { $headerZonaSistema = new DateTimeZone($headerZonaNombre); } catch (Throwable $e) { require_once __DIR__.'/../models/ModuleAccess.php';
if(strcasecmp((string)($_SESSION['user']??''),'superadmin')!==0){
 $moduleUserRules=moduleAccessPolicy()['users'][$_SESSION['user']??'']??[];
 $headerMenuDeshabilitados=array_values(array_unique(array_merge($headerMenuDeshabilitados,$moduleUserRules['disabled']??[])));
 $headerGruposDeshabilitados=array_values(array_unique(array_merge($headerGruposDeshabilitados,$moduleUserRules['disabled_groups']??[])));
}
$headerZonaNombre = 'America/Lima'; $headerZonaSistema = new DateTimeZone($headerZonaNombre); }
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

define("EmpresaConfig", "/views/EmpresaConfig.php");
define("ComunicadosVigencia", "/views/Seg_ComunicadosVigencia.php");
define("ConfigurarEtiquetas", "/views/Seg_ConfigurarEtiquetas.php");
define("ConfigurarCorreo", "/views/Seg_CorreoConfig.php");
define("PlanesMenu", "/views/Seg_PlanesMenu.php");
define("SITE_URL", "http://" . $_SERVER['SERVER_NAME'] . '/');
define("", SITE_URL . "views/Gen_ProductoForm.php");
define("Forma_Farmaceutica", SITE_URL . "views/Gen_ProductoFormaFarmaceuticaForm.php");
define("Medicion", SITE_URL . "views/Gen_ProductoMedicionForm.php");
define("Marca", SITE_URL . "views/Gen_ProductoMarcaForm.php");
define("Categoria", SITE_URL . "views/Gen_ProductoCategoriaForm.php");
define("Compuesto", SITE_URL . "views/Gen_ProductoCompuestoForm.php");
define("Clientes", SITE_URL . "views/V_VentaCliente.php");
define("PuntoVenta", SITE_URL . "views/V_VentaForm.php");
define("PreOrden", SITE_URL . "views/V_PreOrden.php");
define("Experto", SITE_URL . "views/V_ExpertoForm.php");
define("Inventario", SITE_URL . "views/Lo_InventarioForm.php");
define("RegVenta", SITE_URL . "views/ve_regventaform.php");
define("Cierre", SITE_URL . "views/ve_cierreform.php");
define("CierreReporte", SITE_URL . "/views/v_cierrereporte.php");
define("CambioPrecioBloque", SITE_URL . "views/ve_cambiopreciobloque.php");
define("Reimpresion", SITE_URL . "views/ve_reimpresionDocumentos.php");
// define("ReporteUtilidadBruta", SITE_URL . "views/ve_reporteUtilidadBruta.php");
define("ReporteUtilidadBruta", SITE_URL . "views/ReportUtilidadBruta.php");
define("RegMovimiento", SITE_URL . "views/lo_regmovimiento.php");
define("RegCompraContable", SITE_URL . "views/lo_regcompracontable.php");
define("ReporteStock", SITE_URL . "views/Lo_ReporteStockForm.php");
define("ReporteKardex", SITE_URL . "views/lo_kardexform.php");
define("ReporteKardexValorizado", SITE_URL . "views/lo_kardexvalorizadoform.php");
define("Login", SITE_URL . "index.php");
// define("CajaYBanco", SITE_URL . "views/FrmCb_CajaBanco.php");
define("CajaYBanco", SITE_URL . "views/CajaBancos.php");
define("Logout", SITE_URL . "controllers/logout.php");
define("Usuarios", SITE_URL . "views/Seg_UsuarioForm.php");
define("Roles", SITE_URL . "views/Seg_UsuarioPerfil.php");
define("CajaYBancoBuscador", SITE_URL . "views/FrmCb_CajaBancoBuscador.php");
define("BuscarVencimiento", SITE_URL . "views/Lo_BuscarVencimiento.php");
define("GenerarCodigoBarra", SITE_URL . "views/Lo_GenerarCodigoBarra.php");
define("Traslado", SITE_URL . "views/Lo_Traslado.php");
define("Modelo", SITE_URL . "views/Gen_ProductoModelo.php");
define("ReportUtilidadBrutaPorVendedor", SITE_URL . "views/ReportUtilidadBrutaPorVendedor.php");
define("V_RegOferta", SITE_URL . "views/V_RegOferta.php");
define("Ve_HistorialVentaKit", SITE_URL . "views/Ve_HistorialVentaKit.php");
define("ReporteUtilidadBrutaPorUsuario", SITE_URL . "views/ReporteUtilidadBrutaPorUsuario.php");
define("lo_ordenes", SITE_URL . "views/lo_ordenes.php");
define("lo_ordencompra", SITE_URL . "views/lo_ordencompra.php");
define("Lo_MovimientoXML", SITE_URL . "views/Lo_MovimientoXML.php");
define("BuscadorCajaBanco", SITE_URL . "views/BuscadorCajaBanco.php");
define("ReporteRegMovimiento", SITE_URL . "views/ReporteRegMovimiento.php");
define("ReporteRegistroVentas", SITE_URL . "views/ReporteRegistroVentas.php");
define("Dashboard", SITE_URL . "views/Dashboard.php");
define("V_Usuario", SITE_URL . "views/V_Usuario.php");
define("VentasDelivery", SITE_URL . "views/VentasDelivery.php");
define("ReporteProyectosEmpleado", SITE_URL . "views/ReporteProyectosEmpleado.php");

date_default_timezone_set('America/Lima');
?>
<script>
  async function consultaPago() {
    try {
      const url = 'https://admin.factura.vip:5000/api/v1/sistema/aviso?Url=<?php echo $_SERVER['HTTP_HOST'] ?>';
      const response = await fetch(url);
      const data = await response.json();
      if (data.success) {
        setTimeout(() => {
          if (document.getElementsByTagName("iframe")[0]) {
            document.getElementsByTagName("iframe")[0].style.height = ($(window).height() - 120) + 'px';
          }
          let divAviso = document.getElementById("avisoPago");
          divAviso.style.display = "block";
          divAviso.className = data.clase;
          divAviso.insertAdjacentHTML('afterbegin', "<marquee scrollamount='13' direction='left' style='font-size: 1.8rem; font-weight: bold; margin-bottom: -4px;'><span id='avisoPagoLabel'></span></marquee>");
          document.getElementById("avisoPagoLabel").innerHTML = data.msg;
          if (data.pago == 1) {
            let btnPago = document.getElementById("botonPago")
            btnPago.style.display = "block";
          }

        }, 1000);

      }
    } catch (error) {
      console.log("Error en consulta super admin");
    }
  }
  consultaPago();
</script>

<script>
  function datos() {
    fetch('https://deperu.com/api/rest/cotizaciondolar.json')
      .then(res => res.json())
      .then(data => {
        var cotizacion = data.Cotizacion[0];
        var compra = cotizacion.Compra;
        var venta = cotizacion.Venta;

        if ($.isNumeric(compra) && $.isNumeric(venta)) {
          $('#compra').html(compra)
          $('#venta').html(venta)
        }
      })
  }
  datos();
</script>
<style>
  .erp-topbar {
        --erp-navy: #050914;
        --erp-blue: #2563ff;
        --erp-cyan: #00e5ff;
    --erp-mint: #42e8b4;
        --erp-ruby: #ff3154;
        --erp-wine: #160b1b;
        --erp-gold: #00e5ff;
        --erp-champagne: #e9fbff;
    --erp-line: rgba(0, 229, 255, 0.28);
    position: relative;
    z-index: 1040;
    min-height: 66px;
    margin: 0 !important;
    padding: 0 16px;
    overflow: visible;
    border: 0 !important;
    border-radius: 0 0 20px 20px;
    text-transform: uppercase;
    background:
      radial-gradient(circle at 5% -90%, rgba(0, 229, 255, 0.25), transparent 34%),
      radial-gradient(circle at 92% 180%, rgba(255, 49, 84, 0.25), transparent 40%),
      linear-gradient(112deg, #040812 0%, #071522 48%, #170b19 100%) !important;
    box-shadow: 0 14px 34px rgba(1, 7, 17, 0.42), inset 0 1px 0 rgba(125, 238, 255, 0.09), inset 0 -1px 0 rgba(0, 229, 255, 0.19);
    isolation: isolate;
  }

  .erp-topbar::before {
    position: absolute;
    z-index: 0;
    inset: 0;
    content: "";
    overflow: hidden;
    border-radius: inherit;
    pointer-events: none;
    background:
      linear-gradient(105deg, transparent 10%, rgba(113, 237, 255, 0.13) 26%, transparent 42%) -70vw 0 / 70vw 100% no-repeat,
      linear-gradient(90deg, transparent, rgba(0, 229, 255, 0.19), rgba(132, 87, 255, 0.18), rgba(255, 49, 84, 0.18), transparent) 0 100% / 100% 1px no-repeat;
    animation: erpHeaderSweep 6.6s ease-in-out infinite;
  }

  .erp-topbar::after {
    position: absolute;
    z-index: 0;
    right: 4%;
    bottom: -1px;
    left: 4%;
    height: 2px;
    content: "";
    pointer-events: none;
    background: linear-gradient(90deg, transparent, var(--erp-cyan), #8457ff, var(--erp-ruby), transparent);
    filter: drop-shadow(0 0 8px rgba(0, 229, 255, 0.56));
  }

  .erp-topbar > .container-fluid {
    position: relative;
    z-index: 1;
    display: flex;
    width: 100%;
    min-height: 66px;
    align-items: center;
    padding: 0;
  }

  .erp-topbar .navbar-header {
    display: flex;
    flex: 0 0 auto;
    align-items: center;
    margin: 0;
  }

  .erp-topbar .navbar-brand {
    position: relative;
    height: auto;
    margin: 0 13px 0 0 !important;
    padding: 10px 12px;
    border: 1px solid rgba(0, 229, 255, 0.30);
    border-radius: 12px;
    color: #ffffff !important;
    line-height: 1;
    font-size: 15px;
    font-weight: 800;
    letter-spacing: 0.055em;
    text-shadow: 0 0 15px rgba(0, 229, 255, 0.30);
    background: linear-gradient(145deg, rgba(7, 30, 45, 0.92), rgba(40, 13, 32, 0.82));
    box-shadow: 0 7px 18px rgba(1, 8, 18, 0.30), inset 0 1px 0 rgba(125, 238, 255, 0.13);
    transition: transform 0.22s ease, border-color 0.22s ease, box-shadow 0.22s ease;
  }

  .erp-topbar .navbar-brand::before {
    display: inline-block;
    width: 7px;
    height: 7px;
    margin-right: 8px;
    border-radius: 50%;
    content: "";
    vertical-align: 2px;
    background: var(--erp-mint);
    box-shadow: 0 0 0 4px rgba(66, 232, 180, 0.12), 0 0 14px var(--erp-mint);
    animation: erpStatusPulse 1.8s ease-in-out infinite;
  }

  .erp-topbar .navbar-brand:hover,
  .erp-topbar .navbar-brand:focus {
    border-color: rgba(0, 229, 255, 0.72);
    box-shadow: 0 9px 24px rgba(3, 127, 181, 0.24), 0 0 18px rgba(255, 49, 84, 0.14), inset 0 1px 0 rgba(255, 255, 255, 0.18);
    transform: translateY(-2px);
  }

  .erp-topbar #myNavbar {
    display: flex !important;
    flex: 1 1 auto;
    min-width: 0;
    height: auto !important;
    align-items: center;
    justify-content: center;
    padding: 0;
    overflow: visible !important;
    border: 0;
    box-shadow: none;
  }

  .erp-topbar .navbar-nav {
    display: flex;
    flex: 1 1 auto;
    float: none;
    width: 100%;
    align-items: center;
    justify-content: center;
    gap: 4px;
    margin: 0;
  }

  .erp-topbar .navbar-nav::before,
  .erp-topbar .navbar-nav::after {
    display: none !important;
    content: none !important;
  }

  .erp-topbar .navbar-nav > li {
    float: none;
    font-size: 12px !important;
  }

  .erp-topbar .navbar-nav > li > a {
    --erp-button-a: rgba(69, 9, 26, 0.94);
    --erp-button-b: rgba(151, 20, 54, 0.84);
    --erp-button-c: rgba(205, 113, 43, 0.62);
    position: relative;
    display: flex;
    min-height: 40px;
    align-items: center;
    padding: 10px 9px;
    overflow: hidden;
    border: 1px solid rgba(247, 200, 112, 0.32);
    border-radius: 13px;
    color: rgba(255, 248, 231, 0.96) !important;
    font-size: 12px;
    font-weight: 750;
    line-height: 1;
    letter-spacing: 0.015em;
    white-space: nowrap;
    background: linear-gradient(118deg, var(--erp-button-a), var(--erp-button-b), var(--erp-button-c)) !important;
    background-size: 220% 220% !important;
    box-shadow: 0 8px 20px rgba(42, 3, 15, 0.30), 0 0 0 1px rgba(255, 216, 139, 0.04), inset 0 1px 0 rgba(255, 239, 207, 0.20), inset 0 -1px 0 rgba(255, 173, 75, 0.12);
    animation: erpButtonFlow 5.2s ease-in-out infinite;
    isolation: isolate;
    transition: transform 0.22s cubic-bezier(.2, .8, .2, 1), border-color 0.22s ease, box-shadow 0.22s ease, background 0.22s ease;
  }

  .erp-topbar .navbar-nav > li:nth-child(3n + 2) > a {
    --erp-button-a: rgba(48, 14, 20, 0.96);
    --erp-button-b: rgba(124, 44, 23, 0.88);
    --erp-button-c: rgba(221, 148, 53, 0.62);
    animation-delay: -1.4s;
  }

  .erp-topbar .navbar-nav > li:nth-child(3n + 3) > a {
    --erp-button-a: rgba(71, 9, 39, 0.96);
    --erp-button-b: rgba(158, 18, 63, 0.86);
    --erp-button-c: rgba(211, 77, 37, 0.68);
    animation-delay: -2.8s;
  }

  .erp-topbar .navbar-nav > li > a::before {
    position: absolute;
    top: -70%;
    left: -55%;
    width: 35%;
    height: 240%;
    content: "";
    pointer-events: none;
    z-index: 0;
    background: linear-gradient(90deg, transparent, rgba(255, 235, 190, 0.58), transparent);
    transform: rotate(22deg) translateX(-220%);
    animation: erpButtonSheen 6.4s ease-in-out infinite;
  }

  .erp-topbar .navbar-nav > li > a:hover::before,
  .erp-topbar .navbar-nav > li.open > a::before {
    animation-duration: 1.35s;
  }

  .erp-topbar .navbar-nav > li > a::after {
    position: absolute;
    z-index: 0;
    inset: 1px;
    border: 1px solid rgba(255, 227, 166, 0.18);
    border-radius: 11px;
    content: "";
    pointer-events: none;
    box-shadow: inset 0 0 16px rgba(255, 198, 92, 0.05);
    animation: erpButtonEdge 3.2s ease-in-out infinite;
  }

  .erp-topbar .navbar-nav > li > a > .fa {
    margin-right: 6px;
    color: #ffd98d;
    font-size: 13px;
    text-shadow: 0 0 13px rgba(242, 189, 98, 0.68);
    transition: transform 0.22s ease, color 0.22s ease;
  }

  .erp-topbar .navbar-nav > li > a:hover,
  .erp-topbar .navbar-nav > li > a:focus,
  .erp-topbar .navbar-nav > li.open > a,
  .erp-topbar .navbar-nav > li.open > a:hover,
  .erp-topbar .navbar-nav > li.open > a:focus,
  .erp-topbar .navbar-nav > li.erp-current > a {
    border-color: rgba(255, 221, 151, 0.78);
    color: #ffffff !important;
    background: linear-gradient(120deg, rgba(128, 11, 42, 0.96), rgba(220, 29, 65, 0.92), rgba(225, 151, 52, 0.82)) !important;
    background-size: 220% 220% !important;
    box-shadow: 0 12px 28px rgba(164, 14, 51, 0.40), 0 0 20px rgba(242, 189, 98, 0.16), 0 0 0 1px rgba(255, 225, 164, 0.20), inset 0 1px 0 rgba(255, 247, 229, 0.32);
    transform: translateY(-3px) scale(1.025);
  }

  .erp-topbar .navbar-nav > li > a:hover > .fa,
  .erp-topbar .navbar-nav > li.open > a > .fa,
  .erp-topbar .navbar-nav > li.erp-current > a > .fa {
    color: #ffffff;
    transform: translateY(-1px) scale(1.08);
  }

  .erp-topbar .caret {
    margin-left: 7px;
    border-top-color: #ffd98d !important;
    transition: transform 0.24s ease;
  }

  .erp-topbar .open > a .caret {
    transform: rotate(180deg);
  }

  .erp-topbar .dropdown-menu {
    top: calc(100% + 9px);
    min-width: 270px;
    max-width: min(360px, calc(100vw - 24px));
    max-height: calc(100vh - 92px);
    margin: 0;
    padding: 9px;
    overflow-x: hidden;
    overflow-y: auto;
    border: 1px solid rgba(242, 189, 98, 0.34);
    border-radius: 15px;
    background:
      radial-gradient(circle at 90% 0, rgba(219, 31, 67, 0.18), transparent 55%),
      linear-gradient(145deg, rgba(29, 12, 18, 0.98), rgba(11, 10, 14, 0.99)) !important;
    box-shadow: 0 24px 55px rgba(28, 2, 11, 0.54), 0 0 24px rgba(242, 189, 98, 0.08), inset 0 1px 0 rgba(255, 233, 191, 0.10);
    backdrop-filter: blur(18px);
    -webkit-backdrop-filter: blur(18px);
    transform-origin: top center;
  }

  .erp-topbar .open > .dropdown-menu {
    animation: erpDropdownIn 0.24s cubic-bezier(.2, .8, .2, 1) both;
  }

  .erp-topbar .navbar-nav > li:nth-last-child(-n+5) > .dropdown-menu {
    right: 0;
    left: auto;
  }

  .erp-topbar .dropdown-menu > li > a {
    position: relative;
    display: flex;
    min-height: 37px;
    align-items: center;
    margin: 2px 0;
    padding: 8px 12px 8px 18px;
    border: 1px solid transparent;
    border-radius: 9px;
    border-color: rgba(242, 189, 98, 0.07);
    color: rgba(255, 241, 215, 0.92) !important;
    font-size: 11px;
    font-weight: 650;
    line-height: 1.35;
    letter-spacing: 0.025em;
    white-space: normal;
    background: linear-gradient(100deg, rgba(132, 17, 49, 0.18), rgba(199, 89, 35, 0.11), rgba(255, 220, 153, 0.035)) !important;
    background-size: 220% 100% !important;
    animation: erpSubmenuFlow 7.2s ease-in-out infinite;
    transition: color 0.18s ease, transform 0.18s ease, border-color 0.18s ease, background 0.18s ease !important;
  }

  .erp-topbar .dropdown-menu > li > a::before {
    position: absolute;
    left: 7px;
    width: 3px;
    height: 14px;
    border-radius: 999px;
    content: "";
    background: linear-gradient(var(--erp-gold), var(--erp-ruby));
    box-shadow: 0 0 10px rgba(242, 189, 98, 0.58);
    opacity: 0.35;
    transform: scaleY(0.45);
    transition: opacity 0.18s ease, transform 0.18s ease;
  }

  .erp-topbar .dropdown-menu > li > a:hover,
  .erp-topbar .dropdown-menu > li > a:focus,
  .erp-topbar .dropdown-menu > li > a.erp-current-link {
    border-color: rgba(255, 221, 151, 0.30);
    color: #ffffff !important;
    background: linear-gradient(90deg, rgba(179, 21, 56, 0.40), rgba(214, 122, 43, 0.25)) !important;
    box-shadow: 0 7px 16px rgba(88, 4, 27, 0.24), inset 0 1px 0 rgba(255, 238, 202, 0.10);
    transform: translateX(5px);
  }

  .erp-topbar .dropdown-menu > li > a:hover::before,
  .erp-topbar .dropdown-menu > li > a:focus::before,
  .erp-topbar .dropdown-menu > li > a.erp-current-link::before {
    opacity: 1;
    transform: scaleY(1);
  }

  .erp-topbar .dropdown-menu .divider {
    height: 1px;
    margin: 7px 7px;
    overflow: hidden;
    background: linear-gradient(90deg, transparent, rgba(242, 189, 98, 0.38), rgba(219, 31, 67, 0.28), transparent);
  }

  .erp-topbar .dropdown-menu::-webkit-scrollbar {
    width: 7px;
  }

  .erp-topbar .dropdown-menu::-webkit-scrollbar-track {
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.04);
  }

  .erp-topbar .dropdown-menu::-webkit-scrollbar-thumb {
    border-radius: 999px;
    background: linear-gradient(var(--erp-gold), var(--erp-ruby));
  }

  .erp-topbar .erp-market {
    display: flex;
    min-height: 40px;
    flex-direction: column;
    justify-content: center;
    margin-left: 2px;
    padding: 5px 9px;
    border: 1px solid rgba(242, 189, 98, 0.22);
    border-radius: 11px;
    color: #fff0cf;
    font-size: 9.5px;
    font-weight: 800;
    line-height: 1.35;
    letter-spacing: 0.035em;
    white-space: nowrap;
    background: linear-gradient(145deg, rgba(91, 12, 35, 0.56), rgba(34, 15, 19, 0.62));
  }

  .erp-topbar .erp-logo-item {
    display: flex;
    align-items: center;
  }

  .erp-topbar .erp-nav-logo {
    display: block;
    width: 104px;
    max-height: 43px;
    margin: 0 0 0 5px;
    object-fit: contain;
    filter: drop-shadow(0 5px 10px rgba(0, 0, 0, 0.28));
    transition: transform 0.25s ease, filter 0.25s ease;
  }

  .erp-topbar .erp-nav-logo:hover {
    filter: drop-shadow(0 7px 14px rgba(242, 189, 98, 0.30));
    transform: translateY(-2px) scale(1.025);
  }

  .erp-topbar .navbar-toggle {
    margin: 10px 0 10px auto;
    padding: 10px;
    border: 1px solid rgba(242, 189, 98, 0.46) !important;
    border-radius: 11px;
    background: linear-gradient(145deg, rgba(189, 20, 57, 0.72), rgba(219, 139, 46, 0.55)) !important;
    box-shadow: 0 9px 20px rgba(77, 4, 24, 0.34), inset 0 1px 0 rgba(255, 240, 210, 0.20);
  }

  .erp-topbar .navbar-toggle .icon-bar {
    width: 22px;
    height: 2px;
    border-radius: 999px;
    background: #fff3da !important;
    box-shadow: 0 0 8px rgba(242, 189, 98, 0.60);
  }

  @keyframes erpHeaderSweep {
    0%, 58%, 100% { background-position: -70vw 0, 0 100%; }
    78% { background-position: 150vw 0, 0 100%; }
  }

  @keyframes erpDropdownIn {
    from { opacity: 0; transform: translateY(-8px) scale(0.97); }
    to { opacity: 1; transform: translateY(0) scale(1); }
  }

  @keyframes erpButtonFlow {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
  }

  @keyframes erpButtonSheen {
    0%, 58%, 100% { transform: rotate(22deg) translateX(-220%); opacity: 0; }
    64% { opacity: 0.85; }
    78% { transform: rotate(22deg) translateX(520%); opacity: 0; }
  }

  @keyframes erpButtonEdge {
    0%, 100% {
      border-color: rgba(255, 227, 166, 0.14);
      box-shadow: inset 0 0 13px rgba(255, 198, 92, 0.04), 0 0 0 rgba(242, 189, 98, 0);
    }
    50% {
      border-color: rgba(255, 227, 166, 0.42);
      box-shadow: inset 0 0 18px rgba(255, 198, 92, 0.09), 0 0 13px rgba(242, 189, 98, 0.14);
    }
  }

  @keyframes erpSubmenuFlow {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
  }

  @keyframes erpStatusPulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.68; transform: scale(0.72); }
  }

  @media (max-width: 1440px) {
    .erp-topbar {
      min-height: 60px;
      padding: 0 12px;
      border-radius: 0 0 14px 14px;
    }

    .erp-topbar > .container-fluid {
      display: block;
      min-height: 60px;
    }

    .erp-topbar .navbar-header {
      float: none;
      width: 100%;
      min-height: 60px;
    }

    .erp-topbar .navbar-brand {
      order: 1;
      margin-right: auto !important;
    }

    .erp-topbar .navbar-toggle {
      display: block !important;
      order: 2;
      margin-right: 0;
      margin-left: auto;
    }

    .erp-topbar #myNavbar {
      display: none !important;
      width: 100%;
      max-height: calc(100vh - 74px) !important;
      margin: 0;
      padding: 8px 0 13px;
      overflow-x: hidden !important;
      overflow-y: auto !important;
      border-top: 1px solid rgba(242, 189, 98, 0.20);
      background: rgba(17, 8, 13, 0.66);
    }

    .erp-topbar #myNavbar.in {
      display: block !important;
      animation: erpMobileMenuIn 0.24s ease both;
    }

    .erp-topbar .navbar-nav {
      display: grid;
      width: 100%;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 6px;
      padding: 0;
      margin: 0;
    }

    .erp-topbar .navbar-nav > li {
      width: 100%;
    }

    .erp-topbar .navbar-nav > li > a {
      width: 100%;
      min-height: 43px;
      justify-content: flex-start;
      padding: 11px 13px;
    }

    .erp-topbar .dropdown-menu,
    .erp-topbar .navbar-nav > li:nth-last-child(-n+5) > .dropdown-menu {
      position: static;
      float: none;
      width: 100%;
      min-width: 0;
      max-width: none;
      max-height: none;
      margin: 5px 0 0;
      padding: 6px;
      border-radius: 11px;
      background: rgba(30, 11, 18, 0.70) !important;
      box-shadow: inset 0 1px 0 rgba(255, 229, 182, 0.07);
    }

    .erp-topbar .erp-market {
      min-height: 43px;
      margin: 0;
    }

    .erp-topbar .erp-logo-item {
      display: none;
    }
  }

  @media (max-width: 680px) {
    .erp-topbar {
      padding: 0 8px;
    }

    .erp-topbar .navbar-nav {
      grid-template-columns: 1fr;
    }

    .erp-topbar .navbar-brand {
      padding: 9px 10px;
      font-size: 13px;
    }
  }

  @keyframes erpMobileMenuIn {
    from { opacity: 0; transform: translateY(-7px); }
    to { opacity: 1; transform: translateY(0); }
  }

  @media (prefers-reduced-motion: reduce) {
    .erp-topbar,
    .erp-topbar *,
    .erp-topbar *::before,
    .erp-topbar *::after {
      animation-duration: 0.01ms !important;
      animation-iteration-count: 1 !important;
      transition-duration: 0.01ms !important;
    }
  }
  /* Neurosoft Tech Surface v2: interacción de superficie completa */
  .erp-topbar .navbar-nav > li > a {
    --tech-a: #00e5ff;
    --tech-b: #2563ff;
    --tech-c: #ff3154;
    position: relative !important;
    z-index: 0;
    isolation: isolate;
    overflow: hidden !important;
    border-color: rgba(77, 220, 242, 0.25) !important;
    color: #edfaff !important;
    text-shadow: 0 1px 10px rgba(0, 229, 255, 0.12) !important;
    background: linear-gradient(145deg, rgba(10, 27, 41, 0.96), rgba(24, 15, 31, 0.96)) !important;
    background-size: auto !important;
    box-shadow: 0 7px 18px rgba(0, 4, 12, 0.30), inset 0 1px 0 rgba(144, 239, 255, 0.08) !important;
    animation: none !important;
    transition: transform 0.30s cubic-bezier(.2,.85,.25,1), border-color 0.30s ease, box-shadow 0.30s ease, color 0.30s ease !important;
  }

  .erp-topbar .navbar-nav > li:nth-child(2n) > a {
    --tech-a: #17d7d4;
    --tech-b: #1678ff;
    --tech-c: #7b57ff;
  }

  .erp-topbar .navbar-nav > li:nth-child(3n) > a {
    --tech-a: #00d8ff;
    --tech-b: #654dff;
    --tech-c: #ff3154;
  }

  .erp-topbar .navbar-nav > li:nth-child(5n) > a {
    --tech-a: #26e2c4;
    --tech-b: #1684ff;
    --tech-c: #e62b70;
  }

  .erp-topbar .navbar-nav > li > a::before {
    position: absolute !important;
    z-index: -1 !important;
    inset: -1px !important;
    width: auto !important;
    height: auto !important;
    content: "" !important;
    border-radius: inherit !important;
    opacity: 1 !important;
    pointer-events: none !important;
    background: linear-gradient(110deg, var(--tech-a) 0%, var(--tech-b) 48%, var(--tech-c) 100%) !important;
    background-size: 220% 100% !important;
    transform: translate3d(-102%, 0, 0) !important;
    transform-origin: left center !important;
    animation: none !important;
    transition: transform 0.48s cubic-bezier(.16,1,.3,1) !important;
  }

  .erp-topbar .navbar-nav > li > a::after {
    position: absolute !important;
    z-index: 1 !important;
    inset: 1px !important;
    width: auto !important;
    height: auto !important;
    content: "" !important;
    border: 1px solid rgba(122, 231, 255, 0.10) !important;
    border-radius: 11px !important;
    opacity: 0.55 !important;
    pointer-events: none !important;
    background: transparent !important;
    box-shadow: inset 0 0 13px rgba(0, 229, 255, 0.05) !important;
    transform: none !important;
    animation: none !important;
    transition: border-color 0.28s ease, box-shadow 0.28s ease, opacity 0.28s ease !important;
  }

  .erp-topbar .navbar-nav > li > a:hover,
  .erp-topbar .navbar-nav > li > a:focus,
  .erp-topbar .navbar-nav > .open > a,
  .erp-topbar .navbar-nav > .open > a:hover,
  .erp-topbar .navbar-nav > .open > a:focus,
  .erp-topbar .navbar-nav > li > a.erp-current,
  .erp-topbar .navbar-nav > li.erp-current > a {
    color: #ffffff !important;
    border-color: rgba(129, 240, 255, 0.82) !important;
    background: linear-gradient(145deg, rgba(10, 27, 41, 0.98), rgba(24, 15, 31, 0.98)) !important;
    box-shadow: 0 12px 25px rgba(0, 174, 224, 0.24), 0 0 18px rgba(255, 49, 84, 0.16), inset 0 1px 0 rgba(255,255,255,0.22) !important;
    transform: translateY(-2px) scale(1.025) !important;
  }

  .erp-topbar .navbar-nav > li > a:hover::before,
  .erp-topbar .navbar-nav > li > a:focus::before,
  .erp-topbar .navbar-nav > .open > a::before,
  .erp-topbar .navbar-nav > li > a.erp-current::before,
  .erp-topbar .navbar-nav > li.erp-current > a::before {
    transform: translate3d(0, 0, 0) !important;
    animation: erpTechSurface 2.3s ease-in-out infinite !important;
  }

  .erp-topbar .navbar-nav > li > a:hover::after,
  .erp-topbar .navbar-nav > li > a:focus::after,
  .erp-topbar .navbar-nav > .open > a::after,
  .erp-topbar .navbar-nav > li > a.erp-current::after,
  .erp-topbar .navbar-nav > li.erp-current > a::after {
    border-color: rgba(238, 253, 255, 0.68) !important;
    opacity: 1 !important;
    box-shadow: inset 0 0 14px rgba(255,255,255,0.13), 0 0 11px rgba(0,229,255,0.42), 0 0 17px rgba(255,49,84,0.20) !important;
    animation: erpTechEdge 1.55s ease-in-out infinite !important;
  }

  .erp-topbar .navbar-nav > li > a .fa,
  .erp-topbar .navbar-nav > li > a .glyphicon {
    display: inline-flex !important;
    width: 22px;
    height: 22px;
    flex: 0 0 22px;
    align-items: center;
    justify-content: center;
    margin-right: 5px;
    border: 1px solid rgba(0, 229, 255, 0.22);
    border-radius: 7px;
    color: #66efff !important;
    background: rgba(0, 229, 255, 0.08);
    box-shadow: inset 0 0 10px rgba(0, 229, 255, 0.06);
    transition: color .25s ease, background .25s ease, transform .32s cubic-bezier(.2,.9,.2,1), box-shadow .25s ease !important;
  }

  .erp-topbar .navbar-nav > li > a:hover .fa,
  .erp-topbar .navbar-nav > li > a:focus .fa,
  .erp-topbar .navbar-nav > .open > a .fa,
  .erp-topbar .navbar-nav > li > a.erp-current .fa,
  .erp-topbar .navbar-nav > li.erp-current > a .fa,
  .erp-topbar .navbar-nav > li > a:hover .glyphicon,
  .erp-topbar .navbar-nav > li > a:focus .glyphicon,
  .erp-topbar .navbar-nav > .open > a .glyphicon,
  .erp-topbar .navbar-nav > li > a.erp-current .glyphicon,
  .erp-topbar .navbar-nav > li.erp-current > a .glyphicon {
    color: #ffffff !important;
    border-color: rgba(255,255,255,.48);
    background: rgba(255,255,255,.15);
    box-shadow: 0 0 13px rgba(255,255,255,.24);
    transform: rotate(-5deg) scale(1.10);
  }

  .erp-topbar .caret {
    color: #62edff !important;
    border-top-color: currentColor !important;
    filter: drop-shadow(0 0 4px rgba(0,229,255,.48));
    transition: transform .28s ease, color .28s ease !important;
  }

  .erp-topbar .open > a .caret,
  .erp-topbar a:hover .caret {
    color: #ffffff !important;
    transform: rotate(180deg);
  }

  .erp-topbar .dropdown-menu {
    border-color: rgba(0, 229, 255, 0.25) !important;
    background:
      radial-gradient(circle at 12% 0, rgba(0,229,255,.13), transparent 42%),
      radial-gradient(circle at 100% 100%, rgba(255,49,84,.13), transparent 46%),
      linear-gradient(145deg, rgba(5,17,29,.985), rgba(17,10,25,.985)) !important;
    box-shadow: 0 22px 46px rgba(0,5,14,.48), 0 0 22px rgba(0,229,255,.08), inset 0 1px 0 rgba(131,236,255,.10) !important;
  }

  .erp-topbar .dropdown-menu > li > a {
    --sub-a: rgba(0, 213, 239, .76);
    --sub-b: rgba(43, 95, 255, .76);
    --sub-c: rgba(255, 49, 84, .68);
    position: relative !important;
    z-index: 0;
    isolation: isolate;
    min-height: 38px;
    padding-left: 43px !important;
    overflow: hidden !important;
    border-color: rgba(83, 218, 240, .14) !important;
    color: #dff9ff !important;
    background: linear-gradient(135deg, rgba(12,31,45,.86), rgba(28,16,34,.86)) !important;
    background-size: auto !important;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.035) !important;
    animation: none !important;
    transition: transform .30s cubic-bezier(.2,.85,.25,1), border-color .28s ease, box-shadow .28s ease, color .28s ease !important;
  }

  .erp-topbar .dropdown-menu > li > a::before {
    position: absolute !important;
    z-index: -1 !important;
    inset: 0 !important;
    width: auto !important;
    height: auto !important;
    content: "" !important;
    border-radius: inherit !important;
    opacity: 1 !important;
    pointer-events: none !important;
    background: linear-gradient(105deg, var(--sub-a), var(--sub-b), var(--sub-c)) !important;
    background-size: 220% 100% !important;
    transform: translate3d(-102%,0,0) !important;
    transform-origin: left center !important;
    animation: none !important;
    transition: transform .42s cubic-bezier(.16,1,.3,1) !important;
  }

  .erp-topbar .dropdown-menu > li > a::after {
    position: absolute;
    z-index: 2;
    top: 50%;
    left: 12px;
    display: inline-flex;
    width: 22px;
    height: 22px;
    align-items: center;
    justify-content: center;
    border: 1px solid rgba(0,229,255,.28);
    border-radius: 7px;
    content: "\f105";
    color: #68efff;
    font-family: FontAwesome;
    font-size: 12px;
    line-height: 20px;
    text-align: center;
    background: rgba(0,229,255,.08);
    box-shadow: inset 0 0 9px rgba(0,229,255,.08);
    transform: translateY(-50%);
    transition: color .25s ease, background .25s ease, border-color .25s ease, transform .3s cubic-bezier(.2,.85,.25,1), box-shadow .25s ease;
  }

  .erp-topbar .navbar-nav > li:nth-child(1) .dropdown-menu > li > a::after { content: "\f1b2"; }
  .erp-topbar .navbar-nav > li:nth-child(2) .dropdown-menu > li > a::after { content: "\f0d1"; }
  .erp-topbar .navbar-nav > li:nth-child(3) .dropdown-menu > li > a::after { content: "\f07a"; }
  .erp-topbar .navbar-nav > li:nth-child(4) .dropdown-menu > li > a::after { content: "\f15c"; }
  .erp-topbar .navbar-nav > li:nth-child(5) .dropdown-menu > li > a::after { content: "\f080"; }
  .erp-topbar .navbar-nav > li:nth-child(6) .dropdown-menu > li > a::after { content: "\f09d"; }
  .erp-topbar .navbar-nav > li:nth-child(7) .dropdown-menu > li > a::after { content: "\f0c0"; }
  .erp-topbar .navbar-nav > li:nth-child(8) .dropdown-menu > li > a::after { content: "\f132"; }
  .erp-topbar .navbar-nav > li:has(> a .fa-barcode) .dropdown-menu > li > a::after { content: "\f1b2"; }
  .erp-topbar .navbar-nav > li:has(> a .fa-clipboard) .dropdown-menu > li > a::after { content: "\f0d1"; }
  .erp-topbar .navbar-nav > li:has(> a .fa-cart-plus) .dropdown-menu > li > a::after { content: "\f07a"; }
  .erp-topbar .navbar-nav > li:has(> a .fa-file) .dropdown-menu > li > a::after { content: "\f15c"; }
  .erp-topbar .navbar-nav > li:has(> a .fa-bar-chart) .dropdown-menu > li > a::after { content: "\f080"; }
  .erp-topbar .navbar-nav > li:has(> a .fa-credit-card-alt) .dropdown-menu > li > a::after { content: "\f09d"; }
  .erp-topbar .navbar-nav > li:has(> a .fa-users) .dropdown-menu > li > a::after { content: "\f0c0"; }
  .erp-topbar .navbar-nav > li:has(> a .fa-shield) .dropdown-menu > li > a::after { content: "\f132"; }

  .erp-topbar .dropdown-menu > li > a:hover,
  .erp-topbar .dropdown-menu > li > a:focus,
  .erp-topbar .dropdown-menu > li > a.erp-current,
  .erp-topbar .dropdown-menu > li > a.erp-current-link {
    color: #ffffff !important;
    border-color: rgba(116,235,255,.56) !important;
    background: linear-gradient(135deg, rgba(12,31,45,.92), rgba(28,16,34,.92)) !important;
    box-shadow: 0 8px 17px rgba(0,170,220,.15), inset 0 1px 0 rgba(255,255,255,.12) !important;
    transform: translateX(4px) !important;
  }

  .erp-topbar .dropdown-menu > li > a:hover::before,
  .erp-topbar .dropdown-menu > li > a:focus::before,
  .erp-topbar .dropdown-menu > li > a.erp-current::before,
  .erp-topbar .dropdown-menu > li > a.erp-current-link::before {
    transform: translate3d(0,0,0) !important;
    animation: erpTechSurface 2.15s ease-in-out infinite !important;
  }

  .erp-topbar .dropdown-menu > li > a:hover::after,
  .erp-topbar .dropdown-menu > li > a:focus::after,
  .erp-topbar .dropdown-menu > li > a.erp-current::after,
  .erp-topbar .dropdown-menu > li > a.erp-current-link::after {
    color: #ffffff;
    border-color: rgba(255,255,255,.58);
    background: rgba(255,255,255,.16);
    box-shadow: 0 0 12px rgba(255,255,255,.24);
    transform: translateY(-50%) rotate(-7deg) scale(1.08);
  }

  /* Iconos semánticos: cada submenú comunica su función real. */
  .erp-topbar .dropdown-menu > li > a::after {
    display: none !important;
    content: none !important;
  }

  .erp-topbar .dropdown-menu > li > a > .erp-submenu-icon {
    position: absolute !important;
    z-index: 2;
    top: 50%;
    left: 12px;
    display: inline-flex !important;
    width: 22px;
    height: 22px;
    align-items: center;
    justify-content: center;
    margin: 0 !important;
    border: 1px solid rgba(0,229,255,.28);
    border-radius: 7px;
    color: #68efff !important;
    font-size: 12px;
    line-height: 20px;
    text-align: center;
    text-shadow: 0 0 9px rgba(0,229,255,.42);
    background: rgba(0,229,255,.08);
    box-shadow: inset 0 0 9px rgba(0,229,255,.08);
    transform: translateY(-50%);
    transition: color .25s ease, background .25s ease, border-color .25s ease, transform .3s cubic-bezier(.2,.85,.25,1), box-shadow .25s ease !important;
  }

  .erp-topbar .dropdown-menu > li > a:hover > .erp-submenu-icon,
  .erp-topbar .dropdown-menu > li > a:focus > .erp-submenu-icon,
  .erp-topbar .dropdown-menu > li > a.erp-current-link > .erp-submenu-icon {
    color: #ffffff !important;
    border-color: rgba(255,255,255,.58);
    background: rgba(255,255,255,.16);
    box-shadow: 0 0 12px rgba(255,255,255,.24), inset 0 0 9px rgba(255,255,255,.10);
    transform: translateY(-50%) rotate(-7deg) scale(1.08);
  }

  .erp-topbar .dropdown-menu .divider {
    background: linear-gradient(90deg, transparent, rgba(0,229,255,.40), rgba(255,49,84,.34), transparent) !important;
  }

  .erp-topbar .dropdown-menu::-webkit-scrollbar-thumb {
    background: linear-gradient(var(--erp-cyan), #5b63ff, var(--erp-ruby)) !important;
  }

  .erp-topbar .erp-market {
    border-color: rgba(0,229,255,.20) !important;
    color: #dffaff !important;
    background: linear-gradient(145deg, rgba(8,29,44,.88), rgba(35,14,35,.84)) !important;
    box-shadow: inset 0 1px 0 rgba(139,239,255,.08), 0 6px 15px rgba(0,5,14,.22);
  }

  .erp-topbar .erp-nav-logo:hover {
    filter: drop-shadow(0 7px 13px rgba(0,229,255,.22)) drop-shadow(0 0 8px rgba(255,49,84,.16)) !important;
  }

  .erp-topbar .navbar-toggle {
    border-color: rgba(0,229,255,.42) !important;
    background: linear-gradient(125deg, rgba(0,190,225,.78), rgba(55,77,245,.78), rgba(238,42,88,.72)) !important;
    background-size: 220% 100% !important;
    box-shadow: 0 9px 20px rgba(0,94,150,.26), inset 0 1px 0 rgba(255,255,255,.20) !important;
    animation: erpTechSurface 3.2s ease-in-out infinite !important;
  }

  .erp-topbar .navbar-toggle .icon-bar {
    background: #ffffff !important;
    box-shadow: 0 0 8px rgba(0,229,255,.62) !important;
  }

  @media (max-width: 1440px) {
    .erp-topbar #myNavbar {
      border-top-color: rgba(0,229,255,.18) !important;
      background: linear-gradient(145deg, rgba(4,17,29,.96), rgba(22,10,26,.96)) !important;
    }

    .erp-topbar .dropdown-menu,
    .erp-topbar .navbar-nav > li:nth-last-child(-n+5) > .dropdown-menu {
      background: linear-gradient(145deg, rgba(6,24,38,.94), rgba(25,11,29,.94)) !important;
      box-shadow: inset 0 1px 0 rgba(112,232,255,.08) !important;
    }
  }

  /* Acabado Signature: profundidad, luz reactiva y color funcional. */
  .erp-topbar {
    background-size: 100% 100%, 100% 100%, 160% 100% !important;
    backdrop-filter: saturate(1.25) blur(18px);
    -webkit-backdrop-filter: saturate(1.25) blur(18px);
    animation: erpHeaderAurora 11s ease-in-out infinite;
  }

  .erp-topbar .navbar-brand {
    border-color: rgba(78,226,246,.34) !important;
    background:
      radial-gradient(circle at 16% 22%, rgba(63,255,208,.15), transparent 35%),
      linear-gradient(140deg, rgba(5,33,48,.96), rgba(37,12,35,.90)) !important;
    box-shadow: 0 8px 20px rgba(0,5,14,.30), inset 0 1px 0 rgba(167,244,255,.15), inset 0 -1px 0 rgba(255,49,84,.10) !important;
  }

  .erp-topbar .navbar-nav > li > a {
    border-color: rgba(73,213,236,.23) !important;
    background:
      linear-gradient(145deg, rgba(8,27,41,.97), rgba(24,14,31,.97)) !important;
    box-shadow: 0 8px 20px rgba(0,4,12,.31), inset 0 1px 0 rgba(151,240,255,.09), inset 0 -1px 0 rgba(255,49,84,.045) !important;
  }

  .erp-topbar .navbar-nav > li > a::after {
    background: radial-gradient(circle at var(--light-x, 50%) var(--light-y, 50%), rgba(255,255,255,.31), rgba(119,235,255,.10) 24%, transparent 48%) !important;
    opacity: 0 !important;
  }

  .erp-topbar .navbar-nav > li > a:hover::after,
  .erp-topbar .navbar-nav > li > a:focus::after,
  .erp-topbar .navbar-nav > .open > a::after,
  .erp-topbar .navbar-nav > li.erp-current > a::after {
    opacity: 1 !important;
  }

  .erp-topbar .dropdown-menu {
    padding: 10px !important;
    border-color: rgba(91,225,245,.30) !important;
    background:
      radial-gradient(circle at 12% -5%, rgba(0,229,255,.17), transparent 40%),
      radial-gradient(circle at 103% 105%, rgba(255,49,84,.17), transparent 44%),
      linear-gradient(145deg, rgba(4,17,29,.988), rgba(19,9,26,.99)) !important;
    box-shadow: 0 28px 58px rgba(0,4,13,.56), 0 0 30px rgba(0,229,255,.10), inset 0 1px 0 rgba(168,242,255,.13), inset 0 -1px 0 rgba(255,49,84,.11) !important;
    backdrop-filter: blur(24px) saturate(1.22) !important;
    -webkit-backdrop-filter: blur(24px) saturate(1.22) !important;
  }

  .erp-topbar .dropdown-menu::before {
    position: absolute;
    top: 0;
    right: 15px;
    left: 15px;
    height: 2px;
    border-radius: 999px;
    content: "";
    pointer-events: none;
    background: linear-gradient(90deg, transparent, #23e7ff, #7763ff, #ff3154, transparent);
    background-size: 220% 100%;
    filter: drop-shadow(0 0 7px rgba(0,229,255,.48));
    animation: erpTechSurface 3s ease-in-out infinite;
  }

  .erp-topbar .dropdown-menu > li {
    transform-origin: top center;
  }

  .erp-topbar .open > .dropdown-menu > li {
    animation: erpSubmenuReveal .34s cubic-bezier(.2,.85,.25,1) both;
    animation-delay: calc(var(--menu-index, 0) * 22ms);
  }

  .erp-topbar .dropdown-menu > li > a {
    margin: 3px 0 !important;
    border-color: rgba(94,211,232,.13) !important;
    border-radius: 10px !important;
    background: linear-gradient(135deg, rgba(9,30,44,.90), rgba(27,15,35,.89)) !important;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.045), 0 4px 11px rgba(0,3,10,.11) !important;
  }

  .erp-topbar .dropdown-menu > li > a::after {
    position: absolute !important;
    z-index: 1 !important;
    inset: 0 !important;
    display: block !important;
    width: auto !important;
    height: auto !important;
    border: 0 !important;
    border-radius: inherit !important;
    content: "" !important;
    pointer-events: none !important;
    background: radial-gradient(circle at var(--light-x, 50%) var(--light-y, 50%), rgba(255,255,255,.22), transparent 46%) !important;
    box-shadow: none !important;
    opacity: 0 !important;
    transform: none !important;
    transition: opacity .24s ease !important;
  }

  .erp-topbar .dropdown-menu > li > a:hover::after,
  .erp-topbar .dropdown-menu > li > a:focus::after,
  .erp-topbar .dropdown-menu > li > a.erp-current-link::after {
    border: 0 !important;
    background: radial-gradient(circle at var(--light-x, 50%) var(--light-y, 50%), rgba(255,255,255,.22), transparent 46%) !important;
    box-shadow: none !important;
    opacity: 1 !important;
    transform: none !important;
  }

  .erp-topbar .dropdown-menu > li > a.erp-tone-cyan {
    --sub-a: rgba(0,213,239,.78);
    --sub-b: rgba(43,95,255,.77);
    --sub-c: rgba(113,78,255,.70);
  }

  .erp-topbar .dropdown-menu > li > a.erp-tone-violet {
    --sub-a: rgba(111,77,255,.79);
    --sub-b: rgba(181,54,235,.73);
    --sub-c: rgba(255,49,111,.68);
  }

  .erp-topbar .dropdown-menu > li > a.erp-tone-emerald {
    --sub-a: rgba(18,196,154,.79);
    --sub-b: rgba(0,160,210,.73);
    --sub-c: rgba(37,99,255,.68);
  }

  .erp-topbar .dropdown-menu > li > a.erp-tone-amber {
    --sub-a: rgba(231,151,44,.82);
    --sub-b: rgba(241,91,62,.76);
    --sub-c: rgba(238,46,100,.70);
  }

  .erp-topbar .dropdown-menu > li > a.erp-tone-rose {
    --sub-a: rgba(242,53,96,.80);
    --sub-b: rgba(174,48,181,.75);
    --sub-c: rgba(92,74,255,.70);
  }

  .erp-topbar .dropdown-menu > li > a > .erp-submenu-icon {
    --icon-color: #69efff;
    --icon-rgb: 0,229,255;
    border-color: rgba(var(--icon-rgb),.38) !important;
    color: var(--icon-color) !important;
    text-shadow: 0 0 10px rgba(var(--icon-rgb),.56) !important;
    background: linear-gradient(145deg, rgba(var(--icon-rgb),.18), rgba(var(--icon-rgb),.07)) !important;
    box-shadow: inset 0 1px 0 rgba(255,255,255,.11), 0 0 12px rgba(var(--icon-rgb),.12) !important;
  }

  .erp-topbar .dropdown-menu > li > a > .erp-submenu-icon.erp-icon-violet {
    --icon-color: #b9a7ff;
    --icon-rgb: 139,92,246;
  }

  .erp-topbar .dropdown-menu > li > a > .erp-submenu-icon.erp-icon-emerald {
    --icon-color: #66f1c7;
    --icon-rgb: 24,211,160;
  }

  .erp-topbar .dropdown-menu > li > a > .erp-submenu-icon.erp-icon-amber {
    --icon-color: #ffc36a;
    --icon-rgb: 245,158,58;
  }

  .erp-topbar .dropdown-menu > li > a > .erp-submenu-icon.erp-icon-rose {
    --icon-color: #ff8ba2;
    --icon-rgb: 255,66,111;
  }

  .erp-topbar .dropdown-menu > li > a:hover > .erp-submenu-icon,
  .erp-topbar .dropdown-menu > li > a:focus > .erp-submenu-icon,
  .erp-topbar .dropdown-menu > li > a.erp-current-link > .erp-submenu-icon {
    color: #ffffff !important;
    border-color: rgba(255,255,255,.68) !important;
    background: linear-gradient(145deg, rgba(255,255,255,.25), rgba(255,255,255,.11)) !important;
    box-shadow: 0 0 16px rgba(var(--icon-rgb),.52), inset 0 1px 0 rgba(255,255,255,.30) !important;
    transform: translateY(-50%) rotate(-8deg) scale(1.12) !important;
  }

  @keyframes erpHeaderAurora {
    0%, 100% { background-position: 0 0, 0 0, 0% 50%; }
    50% { background-position: 0 0, 0 0, 100% 50%; }
  }

  @keyframes erpSubmenuReveal {
    from { opacity: 0; transform: translateY(-7px) scale(.985); filter: blur(2px); }
    to { opacity: 1; transform: translateY(0) scale(1); filter: blur(0); }
  }

  @keyframes erpTechSurface {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
  }

  @keyframes erpTechEdge {
    0%, 100% { opacity: .72; filter: brightness(1); }
    50% { opacity: 1; filter: brightness(1.28); }
  }

  @media (prefers-reduced-motion: reduce) {
    .erp-topbar .navbar-nav > li > a::before,
    .erp-topbar .navbar-nav > li > a::after,
    .erp-topbar .dropdown-menu > li > a::before,
    .erp-topbar .dropdown-menu > li > a::after {
      animation: none !important;
      transition-duration: .01ms !important;
    }
  }
</style>

<script>
  // Evita que el navbar se cierre al hacer clic en un dropdown
  $(document).on('click', '.navbar-collapse.in .dropdown-toggle', function(e) {
    e.stopPropagation(); // evita que el evento burbujee y cierre el navbar
  });

  $(function() {
    var currentPath = window.location.pathname.toLowerCase().replace(/\/+$/, '');

    $('#myNavbar a[href]').each(function() {
      var href = $(this).attr('href');
      if (!href || href === '#' || href.indexOf('javascript:') === 0) {
        return;
      }

      var target = document.createElement('a');
      target.href = href;
      var targetPath = target.pathname.toLowerCase().replace(/\/+$/, '');

      var sameSection = true;
      if (targetPath === '/views/contabilidad.php') {
        var targetTab = new URLSearchParams(target.search).get('tab') || 'dashboard';
        var currentTab = new URLSearchParams(window.location.search).get('tab') || 'dashboard';
        sameSection = targetTab === currentTab;
      }
      if (targetPath && targetPath === currentPath && sameSection) {
        $(this).addClass('erp-current-link');
        $(this).closest('.dropdown').addClass('erp-current');
      }
    });

    var submenuIconRules = [
      [/categorias.*impresoras/, 'fa-tags'],
      [/modificar.*producto/, 'fa-pencil-square-o'],
      [/cargar.*xml/, 'fa-file-code-o'],
      [/articulos.*movimiento/, 'fa-line-chart'],
      [/nuevo.*kardex/, 'fa-book'],
      [/listado.*ordenes/, 'fa-list-alt'],
      [/nueva.*orden/, 'fa-cart-arrow-down'],
      [/confrontacion/, 'fa-balance-scale'],
      [/registro.*movimiento/, 'fa-random'],
      [/registro.*compras/, 'fa-shopping-basket'],
      [/traslado|tralado/, 'fa-exchange'],
      [/^stock$/, 'fa-cubes'],
      [/punto.*venta/, 'fa-desktop'],
      [/^clientes?$/, 'fa-users'],
      [/pre.*orden/, 'fa-clock-o'],
      [/oferta/, 'fa-tags'],
      [/proyect.*empleado/, 'fa-id-badge'],
      [/ventas?.*delivery/, 'fa-motorcycle'],
      [/ventas?.*vendedor/, 'fa-user-circle'],
      [/registro.*ventas?/, 'fa-file-text-o'],
      [/registro.*proyectos?/, 'fa-briefcase'],
      [/proyectos?.*tarapaca/, 'fa-building-o'],
      [/reporte.*cierre/, 'fa-calculator'],
      [/cierre.*caja/, 'fa-lock'],
      [/reimpresion/, 'fa-print'],
      [/u\.?\s*bruta.*usuario/, 'fa-user-plus'],
      [/utilidad/, 'fa-line-chart'],
      [/guia.*remision/, 'fa-truck'],
      [/historial/, 'fa-history'],
      [/facturacion.*electronica/, 'fa-file-text'],
      [/pagina.*cliente/, 'fa-external-link'],
      [/consulta.*cpe/, 'fa-search'],
      [/dashboard/, 'fa-tachometer'],
      [/estadistico/, 'fa-pie-chart'],
      [/stock.*minimo/, 'fa-level-down'],
      [/deuda.*clientes?/, 'fa-user-times'],
      [/deuda.*proveedores?/, 'fa-truck'],
      [/ranking/, 'fa-trophy'],
      [/comisionistas?/, 'fa-percent'],
      [/^reportes?$/, 'fa-bar-chart'],
      [/buscador.*caja/, 'fa-search'],
      [/caja.*banco/, 'fa-credit-card'],
      [/^operaciones?$/, 'fa-cogs'],
      [/configuracion/, 'fa-sliders'],
      [/^usuarios?$/, 'fa-user-circle-o']
    ];

    var parentFallbackIcons = {
      'fa-barcode': 'fa-cube',
      'fa-clipboard': 'fa-exchange',
      'fa-cart-plus': 'fa-file-text-o',
      'fa-file': 'fa-file-text',
      'fa-bar-chart': 'fa-bar-chart',
      'fa-credit-card-alt': 'fa-credit-card',
      'fa-users': 'fa-cogs',
      'fa-shield': 'fa-shield'
    };

    $('#myNavbar .dropdown-menu > li > a').each(function() {
      var submenuLink = $(this);
      var normalizedLabel = submenuLink.text().toLowerCase();

      if (normalizedLabel.normalize) {
        normalizedLabel = normalizedLabel.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
      }

      normalizedLabel = normalizedLabel.replace(/\s+/g, ' ').trim();
      var selectedIcon = '';

      $.each(submenuIconRules, function(_, rule) {
        if (!selectedIcon && rule[0].test(normalizedLabel)) {
          selectedIcon = rule[1];
        }
      });

      if (!selectedIcon) {
        var parentToggleIcon = submenuLink.closest('.dropdown').children('.dropdown-toggle').find('.fa').first();
        $.each(parentFallbackIcons, function(parentClass, fallbackIcon) {
          if (!selectedIcon && parentToggleIcon.hasClass(parentClass)) {
            selectedIcon = fallbackIcon;
          }
        });
      }

      selectedIcon = submenuLink.attr('data-accounting-icon') || selectedIcon;
      var iconTone = 'cyan';
      var violetIcons = ['fa-users', 'fa-user-circle', 'fa-user-circle-o', 'fa-user-plus', 'fa-user-times', 'fa-id-badge', 'fa-briefcase', 'fa-building-o'];
      var emeraldIcons = ['fa-line-chart', 'fa-bar-chart', 'fa-pie-chart', 'fa-tachometer', 'fa-calculator', 'fa-percent', 'fa-credit-card', 'fa-shopping-basket', 'fa-cubes'];
      var amberIcons = ['fa-clock-o', 'fa-tag', 'fa-tags', 'fa-truck', 'fa-motorcycle', 'fa-exchange', 'fa-cart-arrow-down', 'fa-history'];
      var roseIcons = ['fa-lock', 'fa-shield', 'fa-print', 'fa-level-down'];

      if ($.inArray(selectedIcon, violetIcons) !== -1) {
        iconTone = 'violet';
      } else if ($.inArray(selectedIcon, emeraldIcons) !== -1) {
        iconTone = 'emerald';
      } else if ($.inArray(selectedIcon, amberIcons) !== -1) {
        iconTone = 'amber';
      } else if ($.inArray(selectedIcon, roseIcons) !== -1) {
        iconTone = 'rose';
      }

      submenuLink.children('.fa').remove();
      submenuLink.addClass('erp-tone-' + iconTone);
      submenuLink.parent().css('--menu-index', submenuLink.parent().index());
      $('<i>', {
        'class': 'fa ' + (selectedIcon || 'fa-circle-o') + ' erp-submenu-icon erp-icon-' + iconTone,
        'aria-hidden': 'true'
      }).prependTo(submenuLink);
    });

    $('.erp-topbar .navbar-nav > li > a, #myNavbar .dropdown-menu > li > a')
      .on('mousemove', function(event) {
        var bounds = this.getBoundingClientRect();
        this.style.setProperty('--light-x', Math.round(event.clientX - bounds.left) + 'px');
        this.style.setProperty('--light-y', Math.round(event.clientY - bounds.top) + 'px');
      })
      .on('mouseleave', function() {
        this.style.setProperty('--light-x', '50%');
        this.style.setProperty('--light-y', '50%');
      });
  });
</script>

<nav id="navBar" class="navbar navbar-default erp-topbar">
  <div class="container-fluid">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#myNavbar">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="<?php echo Login; ?>"><?php if (!empty($headerEmpresaConfig['meta']['logo_principal'])): ?><img src="/<?php echo htmlspecialchars(ltrim($headerLogoEmpresa,'/'),ENT_QUOTES,'UTF-8'); ?>" alt="<?php echo htmlspecialchars(empresaConfigValor($headerEmpresaConfig,'nombre_comercial','NEUROSOFT'),ENT_QUOTES,'UTF-8'); ?>" style="max-width:150px;max-height:42px;object-fit:contain"><?php else: ?>NEUROSOFT<?php endif; ?></a>
    </div>
    <div class="collapse navbar-collapse navbar-rigth" id="myNavbar">
      <ul class="nav navbar-nav">
<?php require_once __DIR__.'/../models/DiaryAccess.php'; $headerDiaryAccess=diaryAccessIdentity(appPdoConnection(),(string)($_SESSION['user']??'')); if($headerDiaryAccess['allowed']): ?><li class="dropdown" id="diario-reportes-menu"><a class="dropdown-toggle" data-toggle="dropdown" href="#"><i class="fa fa-calendar-check-o" aria-hidden="true"></i> Diario de actividades <span class="caret"></span></a><ul class="dropdown-menu"><li><a href="/views/diario.php">Tablero y mis actividades</a></li><?php if(strcasecmp((string)($_SESSION['user']??''),'superadmin')===0): ?><li><a href="/views/diario.php?reportes=1"><i class="fa fa-bar-chart"></i> Reportes e indicadores por empleado</a></li><?php endif; ?></ul></li><?php endif; ?>

        <li class="dropdown">
          <a class="dropdown-toggle" data-toggle="dropdown" href="#">
            <i class="fa fa-barcode" aria-hidden="true"></i> Producto
            <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="/views/Gen_ProductoForm.php" class="text-primary-color"><i class="fa fa-"></i>Modificar Producto</a></li>
            <li><a href="/views/tecnico-experto.php" class="text-primary-color"><i class="fa fa-wrench" aria-hidden="true"></i>Técnico experto</a></li>
            <li><a href="/views/Gen_ProductosSinMovimiento.php" class="text-primary-color"><i class="fa fa-clock-o"></i>Productos sin movimiento</a></li>
            <li><a href="/views/Gen_Impresora.php" class="text-primary-color"><i class="fa fa-"></i>Categorias / Impresoras</a></li>
            <!--<li><a href="<?php echo Marca; ?>" >Marca</a></li>-->
            <!--<li><a href="<?php echo Forma_Farmaceutica; ?>">Forma Farmaceutica</a></li>-->
            <!--<li><a href="<?php echo Modelo; ?>">Modelo</a></li>
          <li><a href="<?php echo Medicion; ?>">Medicion</a></li>
          <li><a href="<?php echo Categoria; ?>">Categoria</a></li>-->
            <!--<li><a href="<?php echo Compuesto; ?>">Compuesto</a></li>-->
          </ul>
        </li>

        <li class="dropdown">
          <a href="" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-clipboard"></i> Logistica
            <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="<?php echo Inventario; ?>">REGISTRO DE COMPRAS</a></li>
            <li><a href="<?php echo Lo_MovimientoXML; ?>">CARGAR COMPRAS XML</a></li>
            <li><a href="<?php echo Traslado; ?>">TRALADOS</a></li>
            <!--<li><a href="<?php echo GenerarCodigoBarra; ?>">CODIGO DE BARRA</a></li>-->
            <li role="separator" class="divider"></li>
            <!--<li class="dropdown-header">KARDEX CONTABLE</li>-->
            <!--<li><a href="<?php echo ReporteKardex . "?Tipo=0"; ?>">1. KARDEX</a></li>-->
            <!--<li><a href="<?php echo ReporteKardexValorizado . "?Tipo=0"; ?>">1. KARDEX VALORIZADO</a></li>-->
            <!--<li class="dropdown-header">KARDEX FECHA STOCK</li>-->
            <!--<li><a href="<?php echo ReporteKardex . "?Tipo=1"; ?>">1. KARDEX</a></li>-->
            <!--<li><a href="<?php echo ReporteKardexValorizado . "?Tipo=1"; ?>">2. KARDEX VALORIZADO</a></li>-->
            <li><a href="/views/lo_reportemasmovimiento.php">ARTICULOS CON MAS MOVIMIENTOS</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="/views/lo_kardex.php">NUEVO KARDEX</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="<?php echo ReporteStock; ?>">STOCK</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="<?php echo lo_ordenes; ?>">LISTADO ORDENES COMPRA</a></li>
            <li><a href="<?php echo lo_ordencompra; ?>">NUEVA ORDEN COMPRA</a></li>
            <li role="separator" class="divider"></li>
            <li class="navli"><a href="/views/lo_confrontacion.php">Confrontación de Inventario</a></li>
            <!-- <li><a href="<?php echo RegMovimiento; ?>">REGISTRO DE MOVIMIENTO</a></li> -->
            <li><a href="<?php echo ReporteRegMovimiento; ?>">REGISTRO DE MOVIMIENTO </a></li>
<li><a href="/views/ReporteSireMovimiento.php">SIRE COMPRAS</a></li>

            <!--<li><a href="<?php echo RegCompraContable; ?>">REGISTRO DE COMPRA CONTABLE</a></li>-->
            <li role="separator" class="divider"></li>
            <!--<li><a href="<?php echo BuscarVencimiento; ?>">PRODUCTOS POR VENCER</a></li>-->
          </ul>
        </li>

        <!--<li><a href="<?php echo Experto; ?>">Sistema Experto</a></li>-->
        <li class="dropdown">
          <a href="" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-cart-plus"></i> Ventas
            <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="<?php echo PuntoVenta; ?>">Punto de venta IA</a></li>
            <li><a href="<?php echo PuntoVenta; ?>?pos=imceden">Punto de venta</a></li>
<li><a href="/views/VentasApp.php">VENTAS APP</a></li>
<li><a href="/views/TiendaVirtual.php">TIENDA VIRTUAL</a></li>
<li><a href="/views/ReporteSireVentas.php">SIRE VENTAS</a></li>
            <li><a href="<?php echo Clientes; ?>">Clientes </a></li>
            <li><a href="<?php echo PreOrden; ?>">Pre Orden</a></li>
            <li><a href="<?php echo V_RegOferta; ?>">Registro Ofertas</a></li>
            <li><a href="/views/proyectos.php?id=1">Proyectos TARAPACA</a></li>
            <!-- <li><a href="/views/proyectos.php?id=2">Proyectos </a></li> -->
            <li><a href="/views/reg_proyectos.php">Registro de Proyectos</a></li>
            <li role="separator" class="divider"></li>
            <!-- <li><a href="<?php echo RegVenta; ?>">Registro de Ventas</a></li> -->
            <li><a href="<?php echo ReporteRegistroVentas; ?>">Registro de Ventas </a></li>
            <li><a href="<?php echo ReporteProyectosEmpleado; ?>">Registro Proyects - Empleado </a></li>
            <li><a href="<?php echo VentasDelivery; ?>">Registro de Ventas Delivery</a></li>
            <li><a href="<?php echo SITE_URL . "views/ve_regventaformvendedor.php" ?>">Registro de Ventas VENDEDOR</a></li>
            <li><a href="<?php echo Cierre; ?>">Cierre de Caja</a></li>
            <li><a href="<?php echo CierreReporte; ?>">Reporte Cierre de Caja</a></li>
            <!--<li><a href="<?php echo CambioPrecioBloque; ?>">Cambio de precio por bloque</a></li>-->
            <li role="separator" class="divider"></li>
            <li><a href="<?php echo Reimpresion; ?>">Reimpresión de Documentos</a></li>
            <li><a href="<?php echo ReporteUtilidadBruta; ?>">Reporte Utilidad Bruta</a></li>
            <!-- <li><a href="<?php echo ReportUtilidadBrutaPorVendedor; ?>">Reporte Utilidad Bruta Por Vendedor</a></li> -->
            <li><a href="/views/ReporteGerencial.php">CONSOLIDADO GERENCIAL / EXCEL</a></li>
            <li><a href="<?php echo ReporteUtilidadBrutaPorUsuario; ?>">UTILIDAD BRUTA SERVICIOS / USUARIO</a></li>
            <li role="separator" class="divider"></li>
            <li><a href="/views/remision.php">Generar guía de Remisión</a></li>
            <li><a href="<?php echo Ve_HistorialVentaKit; ?>">Historial Venta Kit</a></li>
          </ul>
        </li>

        <?php if ($_SESSION['idPerfil'] == 1): ?>
          <li class="dropdown">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#"> <i class="fa fa-file" aria-hidden="true"></i> Facturación
              <span class="caret"></span></a>
            <ul class="dropdown-menu">
              <li><a href="/views/facturacion.php"> Facturación Electrónica </a></li>
              <li><a href="/api/sunat/pag_cliente/" target="_blank"> Página Cliente </a></li>
              <li><a href="http://emision.factura.vip/" target="_blank"> Consulta CPE </a></li>
              <li role="separator" class="divider"></li>
              <li><a href="/views/remisionLista.php"> Emitir guía de Remisión</a></li>
            </ul>

          </li>
        <?php endif; ?>
        <?php if ($_SESSION['idPerfil'] == 1): ?>
          <li class="dropdown">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#"> <i class="fa fa-bar-chart" aria-hidden="true"></i> Reportes
              <span class="caret"></span></a>
            <ul class="dropdown-menu">
              <li><a href="<?php echo Dashboard; ?>"> Dashboard</a></li>

<li><a href="/views/KpiDashboard.php">DASHBOARD KPI</a></li>
<li><a href="/views/KpiDashboard.php#ventas">KPI VENTAS</a></li>
<li><a href="/views/KpiDashboard.php#productos">KPI PRODUCTOS</a></li>
<li><a href="/views/KpiDashboard.php#inventario">KPI INVENTARIO</a></li>
<li><a href="/views/KpiDashboard.php#vendedores">KPI VENDEDORES</a></li>
<li><a href="/views/KpiDashboard.php#clientes">KPI CLIENTES</a></li>
<li><a href="/views/KpiDashboard.php#sucursales">KPI SUCURSALES</a></li>
<li><a href="/views/KpiDashboard.php#alertas">KPI ALERTAS</a></li>

              <li><a href="/views/reporteestadistico.php"> Reporte Estadistico </a></li>
              <li><a href="/views/reportehuespedes.php"> Registro de Proyectos </a></li>
              <li><a href="/views/reportestockminimo.php"> Reporte Stock Mínimo </a></li>
              <li><a href="/views/reportesdeudacliente.php"> Reporte Deuda Clientes </a></li>
              <li><a href="/views/reportesdeudaproveedor.php"> Reporte Deuda Proveedores </a></li>
              <li><a href="/views/reportesrankingclientes.php"> Reporte Ranking Clientes </a></li>
              <li><a href="/views/comisionistas.php"> Reporte Comisionistas </a></li>
              <li><a href="/views/planilla.php"><i class="fa fa-users"></i> Personal y planilla </a></li>
              <li><a href="/views/reportes.php">REPORTES</a></li>
            </ul>
          </li>
        <?php endif; ?>


        <li class="dropdown">
          <a class="dropdown-toggle" data-toggle="dropdown" href="#"> <i class="fa fa-credit-card-alt" aria-hidden="true"></i> Caja y Banco
            <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="<?php echo CajaYBanco; ?>"> Caja y Banco</a></li>
            <!-- <li><a href="<?php echo CajaYBancoBuscador; ?>">Buscador de Caja y Banco</a></li> -->
            <li><a href="<?php echo BuscadorCajaBanco; ?>">Buscador de Caja y Banco</a></li>
            <?php if(strtolower(trim((string)($_SESSION['user']??'')))==='superadmin'): ?><li><a href="<?php echo SITE_URL; ?>views/ReporteBancos.php">Reporte de bancos</a></li><li><a href="<?php echo SITE_URL; ?>views/SaldosBancos.php">Saldos de bancos</a></li><?php endif; ?>
          </ul>

        </li>



        <?php include __DIR__.'/../api/accounting-menu.php'; ?>
<?php include __DIR__.'/multiagente-menu.php'; ?>


        <?php if ($_SESSION['idPerfil'] == 1): ?>
          <li class="dropdown">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#"> <i class="fa fa-shield" aria-hidden="true"></i> Seguridad
              <span class="caret"></span></a>
            <ul class="dropdown-menu">
              <!-- <li><a href="<?php echo Usuarios; ?>">Usuarios</a></li> -->
              <li><a href="<?php echo V_Usuario; ?>">Usuarios</a></li>
<?php if (strcasecmp(trim((string)($_SESSION['user']??'')),'superadmin')===0): ?>
<li><a href="/views/migrador-historico.php">Migrador de compras y ventas</a></li>
<?php endif; ?>

<?php if (strtolower(trim((string)($_SESSION["user"]??""))) === "superadmin"): ?>
<li class="navli" ><a href="/views/EmpresaConfig.php"><i class="fa fa-building-o"></i> Datos de empresa</a></li>
<li class="navli" ><a href="/views/Seg_ComunicadosVigencia.php"><i class="fa fa-bell"></i> Comunicados y vigencia</a></li>
<li class="navli" ><a href="/views/Seg_ConfigurarEtiquetas.php"><i class="fa fa-tags"></i> Configurar etiquetas</a></li>
<li class="navli" ><a href="/views/Seg_CorreoConfig.php"><i class="fa fa-envelope"></i> Configuración de correo</a></li>
<li class="navli" data-plan-manager="1"><a href="/views/Seg_PlanesMenu.php"><i class="fa fa-th-large"></i> Planes y módulos</a></li>
<?php endif; ?>
            </ul>
          </li>
        <?php endif; ?>
        <?php /*if ($_SESSION['idPerfil'] == 1): ?>
        <li class="dropdown">
          <a href="#" onclick="window.open('http://app.factura.vip/client/login/20573027125','winname','directories=0,titlebar=0,toolbar=0,location=0,status=0,menubar=0,scrollbars=no,resizable=no,width=600,height=700');"><b>APP</b></a>
        </li>
      <?php endif;*/ ?>
        <?php require_once __DIR__.'/../models/ConcreteraMenu.php'; if (cxConcreteraMenuVisible()): ?>
        <li class="dropdown"><a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-industry"></i> Concretera <span class="caret"></span></a><ul class="dropdown-menu"><li class="navli"><a href="/views/Concretera.php">Base comercial y pedidos</a></li><li class="navli"><a href="/views/Concretera.php?tab=facturacion">Facturación electrónica por empresa</a></li><li class="navli"><a href="/views/Concretera.php?tab=modulos">Módulos de cada empresa</a></li></ul></li>
        <?php endif; ?>
        <li class="crm-nav-item"><a href="/views/CRM.php"><i class="fa fa-comments"></i> CRM</a></li>
        <li class="dropdown">
          <a href="<?php echo Logout; ?>"> <i class="fa fa-sign-out" aria-hidden="true"></i></a>
        </li>
        <li>
          <div class="erp-market">
            Compra: <span id="compra"></span></br>
            Venta : <span id="venta"></span>
          </div>
        </li>
        <li class="navli erp-logo-item">
          <img class="erp-nav-logo" src="/resources/images/neurologo.png" alt="Neurosoft">
        </li>
      </ul>
      <!-- <ul class="nav navbar-nav navbar-right"> -->
      <!-- <li><a href=""><i class="fa fa-user-circle"></i> Usuario</a></li> -->
      <!-- <li><a href=""><i class="fa fa-sign-out"></i> Cerrar Sesion</a></li> -->
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
  function key(a){try{var u=new URL(a.href,location.href);return u.origin===location.origin?(u.pathname.toLowerCase()+(u.pathname.toLowerCase()==='/views/contabilidad.php'?'?tab='+(u.searchParams.get('tab')||'dashboard'):(u.pathname.toLowerCase()==='/views/diario.php'&&u.searchParams.get('reportes')?'?reportes=1':''))):u.href.toLowerCase()}catch(e){return String(a.getAttribute('href')||'').toLowerCase()}}
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
      menu.querySelectorAll(':scope>li>a[href]').forEach(function(a){
        if(isSuperadmin&&a.closest('[data-plan-manager="1"]'))return;
        if((disabled.indexOf(key(a))!==-1||disabled.indexOf(key(a).split('?')[0])!==-1))a.closest('li').style.display='none';
      });
      var visible=Array.from(menu.querySelectorAll(':scope>li')).some(function(li){return li.style.display!=='none'});
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




<div id="avisoPago" style="display: none; margin-top: -13px; margin-bottom: 0 !important; padding: 0 !important; text-align: center; ">
  <a id="botonPago" href="#" class="text-center font-size: 1.8rem; font-weight: bold;" data-toggle="modal" data-target="#modalPagoFact" style="color:red; font-size: 1.6rem; display:none"><i class="fa fa-hand-o-right" aria-hidden="true"></i><b> PAGAR AQUI </b><i class="fa fa-hand-o-left" aria-hidden="true"></i></a>
</div>
<div class="modal fade" id="modalPagoFact" tabindex="-1" role="dialog" aria-labelledby="modalPagoFact" aria-hidden="true">
  <div class="modal-dialog" style="max-width: 65% !important;">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title" id="myModalLabel"><b>METODOS DE PAGO NEUROSYSTEM PERU S.A.C.</b></h4>
      </div>
      <div class="modal-body">
        <div>
          <img src="https://admin.factura.vip:5000/img/config/metodosdepago.jpg" alt="Metodos de Pago NeuroSystem" style="width: -webkit-fill-available;">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>
<div id="loading"></div>

<script>
  sessionStorage.clear();
  sessionStorage.setItem('User', "<?php echo strtolower(trim($_SESSION['user'])) ?>");
  sessionStorage.setItem('IdPerfil', "<?php echo $_SESSION['idPerfil'] ?>")
  sessionStorage.setItem('IdAlmacen', "<?php echo $_SESSION['idAlmacen'] ?>")
  sessionStorage.setItem('HostUrl', "<?php echo $_SESSION['hostUrl'] ?>")
</script>

<!-- NST PREMIUM MODULE ADAPTER -->
<?php
$nstAssetBase = rtrim(str_replace('\\', '/', dirname(dirname($_SERVER['SCRIPT_NAME']))), '/') . '/nuevo';
$nstIndexFile = __DIR__ . '/../nuevo/index.html';
$nstAssetVersion = is_file($nstIndexFile) ? (string) filemtime($nstIndexFile) : 'premium';
?>
<link rel="stylesheet" href="<?php echo htmlspecialchars($nstAssetBase . '/static/css/nst-legacy.css?v=' . $nstAssetVersion, ENT_QUOTES, 'UTF-8'); ?>">
<script defer src="<?php echo htmlspecialchars($nstAssetBase . '/static/js/nst-legacy-ui.js?v=' . $nstAssetVersion, ENT_QUOTES, 'UTF-8'); ?>"></script>
<!-- END NST PREMIUM MODULE ADAPTER -->
