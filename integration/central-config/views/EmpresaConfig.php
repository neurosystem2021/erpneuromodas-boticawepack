<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION["user"]) || (isset($_SESSION["expire"]) && time() > $_SESSION["expire"])) { header("Location: /index.php"); exit; }
include_once 'linker.php';
require_once __DIR__ . '/../models/EmpresaConfig.php';
if (strtolower(trim((string) ($_SESSION['user'] ?? ''))) !== 'superadmin') {
    include_once 'error/noAcceso.php'; exit;
}
if (empty($_SESSION['empresa_config_csrf'])) $_SESSION['empresa_config_csrf'] = bin2hex(random_bytes(32));
$config = empresaConfigObtener();
$e = $config['empresa']; $m = $config['meta'];
function h($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function meta($m, $key, $default = '') { return isset($m[$key]) ? $m[$key] : $default; }
$logoPrincipal = meta($m, 'logo_principal', 'resources/images/logo.png');
$logoTicket = meta($m, 'logo_ticket', meta($m, 'logo_gris', 'resources/images/logo-ticket.png'));
$imagenProforma = meta($m, 'imagen_proforma', 'resources/images/banner-proforma.jpg');
if (!is_file(__DIR__ . '/../' . ltrim($logoPrincipal, '/'))) $logoPrincipal = 'resources/images/logo.png';
if (!is_file(__DIR__ . '/../' . ltrim($logoTicket, '/'))) $logoTicket = 'resources/images/logo-ticket.png';
if (!is_file(__DIR__ . '/../' . ltrim($imagenProforma, '/'))) $imagenProforma = 'resources/images/banner-proforma.jpg';
?>
<!DOCTYPE html>
<html lang="es">
<head><meta charset="UTF-8"><title>Configuración de empresa</title></head>
<body>
<?php include 'header.php'; ?>
<link rel="stylesheet" href="/resources/css/central-config.css?v=20261012-1">
<style>
  :root{--ec-navy:#092b4b;--ec-blue:#079bd3;--ec-pale:#edf8fd;--ec-line:#b9ddef;--ec-green:#08a85a;--ec-text:#082844;--ec-muted:#647b8d}
  body{background:#eef7fb!important;color:var(--ec-text)}
  .ec-page{max-width:1200px;margin:18px auto 42px;padding:0 18px;font-family:Inter,"Segoe UI",Arial,sans-serif}
  .ec-hero{display:flex;align-items:center;gap:14px;padding:18px 22px;border-radius:16px;color:#fff;background:linear-gradient(105deg,var(--ec-navy),#12a8df);box-shadow:0 14px 34px rgba(9,43,75,.16)}
  .ec-hero-icon{display:grid;place-items:center;width:44px;height:44px;border-radius:11px;background:rgba(255,255,255,.15);font-size:20px}.ec-hero h1{margin:0 0 3px;font-size:22px;font-weight:800}.ec-hero p{margin:0;color:rgba(255,255,255,.82);font-size:12px}
  .ec-card{margin-top:14px;padding:20px;border:1px solid #cce5f1;border-radius:16px;background:#fff;box-shadow:0 12px 34px rgba(9,43,75,.07)}
  .ec-grid{display:grid;grid-template-columns:280px minmax(0,1fr);gap:24px}.ec-assets{padding:18px;border:1px solid var(--ec-line);border-radius:14px;background:var(--ec-pale)}
  .asset-box{padding-bottom:18px;margin-bottom:18px;border-bottom:1px solid #c8dfeb;text-align:center}.asset-box:last-child{margin:0;padding:0;border:0}.asset-box h3{margin:0 0 10px;font-size:13px}.asset-preview{display:grid;place-items:center;height:130px;padding:12px;margin-bottom:10px;border:1.5px dashed #54b7e3;border-radius:14px;background:#fff;overflow:hidden}.asset-preview.small{height:92px}.asset-preview img{max-width:100%;max-height:100%;object-fit:contain}
  .file-button{display:inline-flex;align-items:center;justify-content:center;gap:7px;width:100%;padding:10px;border-radius:8px;color:#fff;background:#078dcc;font-size:11px;font-weight:800;cursor:pointer}.file-button input{display:none}.asset-help{display:block;margin-top:7px;color:var(--ec-muted);font-size:9px}
  .section-title{display:flex;align-items:center;justify-content:space-between;margin:0 0 14px}.section-title h2{margin:0;font-size:17px;font-weight:850}.section-title span{color:var(--ec-muted);font-size:10px}
  .form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}.span-2{grid-column:span 2}.field label{display:block;margin:0 0 6px;font-size:10px;font-weight:850}.field input,.field textarea,.field select{width:100%;min-height:43px;padding:10px 12px;border:1px solid var(--ec-line);border-radius:8px;outline:0;background:#fff;color:var(--ec-text);font-size:12px;transition:.2s}.field textarea{height:68px;resize:vertical}.field input:focus,.field textarea:focus,.field select:focus{border-color:#079bd3;box-shadow:0 0 0 3px rgba(7,155,211,.1)}
  .environment{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin:14px 0}.environment label{display:flex;gap:11px;padding:13px;border:1px solid #c8dce7;border-radius:11px;cursor:pointer;background:#fff}.environment label:has(input:checked){border-color:#079bd3;background:#effaff;box-shadow:0 0 0 2px rgba(7,155,211,.1)}.environment input{margin-top:2px}.environment strong{display:block;font-size:11px}.environment small{display:block;margin-top:3px;color:var(--ec-muted);font-size:9px;line-height:1.35}
  .sunat-box{margin-top:18px;padding:16px;border:1px solid var(--ec-line);border-radius:13px;background:#f2faff}.sunat-title{display:flex;justify-content:space-between;align-items:center;margin-bottom:14px}.sunat-title h2{margin:0;font-size:16px}.badge-status{padding:6px 10px;border-radius:999px;background:#e5edf1;color:#64737d;font-size:9px;font-weight:800}
  .cert-row{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:12px;padding:12px;border:1px dashed #48addb;border-radius:10px;background:#fff}.cert-row strong{display:block;font-size:10px}.cert-row small{color:var(--ec-muted);font-size:9px}.cert-button{padding:9px 12px;border-radius:7px;color:#fff;background:#0879b8;font-size:10px;font-weight:800;cursor:pointer;white-space:nowrap}.cert-button input{display:none}
  .ec-note{display:flex;align-items:center;gap:9px;margin-top:14px;padding:11px 14px;border:1px solid var(--ec-line);border-radius:10px;background:#eff9fe;color:#40657c;font-size:10px}.ec-actions{display:flex;justify-content:flex-end;margin-top:14px}.save-button{padding:12px 18px;border:0;border-radius:9px;color:#fff;background:var(--ec-green);font-size:11px;font-weight:850;box-shadow:0 10px 22px rgba(8,168,90,.2);cursor:pointer}.save-button:disabled{opacity:.6;cursor:wait}
  .toast-config{position:fixed;right:22px;bottom:22px;z-index:9999;max-width:390px;padding:14px 18px;border-radius:10px;color:#fff;background:#092b4b;box-shadow:0 12px 30px rgba(0,0,0,.22);font-size:12px;transform:translateY(100px);opacity:0;transition:.25s}.toast-config.show{transform:none;opacity:1}.toast-config.error{background:#b52c39}
  @media(max-width:800px){.ec-page{padding:0 10px;margin-top:10px}.ec-grid{grid-template-columns:1fr}.ec-assets{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}.asset-box{margin:0;padding:0;border:0}.asset-preview{height:90px}.form-grid{grid-template-columns:1fr}.span-2{grid-column:span 1}.environment{grid-template-columns:1fr}.ec-card{padding:12px}.ec-hero{padding:14px}.ec-hero h1{font-size:18px}}
  @media(max-width:520px){.ec-assets{grid-template-columns:1fr}.asset-preview{height:115px}.cert-row{align-items:flex-start;flex-direction:column}.cert-button{width:100%;text-align:center}.ec-actions .save-button{width:100%}.section-title{align-items:flex-start;flex-direction:column;gap:4px}}
</style>

<main class="ec-page">
  <header class="ec-hero"><div class="ec-hero-icon"><i class="fa fa-building"></i></div><div><h1>Datos de empresa</h1><p>Identidad corporativa, datos fiscales y configuración del emisor electrónico.</p></div></header>
  <form class="ec-card" id="empresaForm" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?php echo h($_SESSION['empresa_config_csrf']); ?>"><input type="hidden" name="id_empresa" value="<?php echo h($e['IDEMPRESA'] ?? 1); ?>">
    <div class="ec-grid">
      <aside class="ec-assets">
        <div class="asset-box"><h3>Logo corporativo</h3><div class="asset-preview"><img id="preview-logo_principal" src="/<?php echo h($logoPrincipal); ?>" alt="Logo corporativo"></div><label class="file-button"><i class="fa fa-cloud-upload"></i>Cambiar logo principal<input type="file" name="logo_principal" accept="image/png,image/jpeg,image/webp" data-preview="preview-logo_principal"></label><small class="asset-help">PNG, JPG o WEBP · máximo 3 MB</small></div>
        <div class="asset-box"><h3>Logo para tickets</h3><div class="asset-preview small"><img id="preview-logo_ticket" src="/<?php echo h($logoTicket); ?>" alt="Logo para tickets"></div><label class="file-button"><i class="fa fa-print"></i>Subir logo para ticket<input type="file" name="logo_ticket" accept="image/png,image/jpeg,image/webp" data-preview="preview-logo_ticket"></label><small class="asset-help">Se recomienda PNG monocromático.</small></div>
        <div class="asset-box"><h3>Cabecera de proformas</h3><div class="asset-preview small"><img id="preview-imagen_proforma" src="/<?php echo h($imagenProforma); ?>" alt="Cabecera para proformas"></div><label class="file-button"><i class="fa fa-image"></i>Subir imagen de proforma<input type="file" name="imagen_proforma" accept="image/png,image/jpeg,image/webp" data-preview="preview-imagen_proforma"></label><small class="asset-help">Formato horizontal · máximo 4 MB</small></div>
      </aside>
      <section>
        <div class="section-title"><h2>Información fiscal y comercial</h2><span>Los campos con * son obligatorios</span></div>
        <div class="form-grid">
          <div class="field span-2"><label>Razón social *</label><input name="razon_social" required value="<?php echo h($e['RAZONSOCIAL'] ?? ''); ?>"></div>
          <div class="field span-2"><label>Nombre comercial</label><input name="nombre_comercial" value="<?php echo h(meta($m, 'nombre_comercial', 'Comercial Genesis')); ?>" placeholder="Nombre visible en el sistema"></div>
          <div class="field"><label>RUC del emisor *</label><input name="ruc" required maxlength="11" inputmode="numeric" value="<?php echo h($e['RUC'] ?? ''); ?>"></div>
          <div class="field"><label>IGV (%)</label><input name="igv" type="number" min="0" max="100" step="0.01" value="<?php echo h(isset($e['IGV']) ? ((float)$e['IGV'] * 100) : 18); ?>"></div>
          <div class="field span-2"><label>Dirección fiscal *</label><textarea name="direccion" required><?php echo h($e['DIRECCION'] ?? ''); ?></textarea></div>
          <div class="field"><label>Teléfono</label><input name="telefono" value="<?php echo h($e['TELEFONO'] ?? ''); ?>"></div><div class="field"><label>Celular</label><input name="celular" value="<?php echo h($e['CELULAR'] ?? ''); ?>"></div>
          <div class="field"><label>Teléfono de soporte</label><input name="telefono_soporte" value="<?php echo h(meta($m, 'telefono_soporte', '997 578 199')); ?>"></div><div class="field"><label>Correo</label><input name="email" type="email" value="<?php echo h($e['EMAIL'] ?? ''); ?>"></div>
          <div class="field"><label>Ubigeo</label><input name="ubigeo" maxlength="10" value="<?php echo h($e['UBIGEO'] ?? ''); ?>"></div><div class="field"><label>Departamento</label><input name="departamento" value="<?php echo h($e['DEPARTAMENTO'] ?? ''); ?>"></div>
          <div class="field"><label>Provincia</label><input name="provincia" value="<?php echo h($e['PROVINCIA'] ?? ''); ?>"></div><div class="field"><label>Distrito</label><input name="distrito" value="<?php echo h($e['DISTRITO'] ?? ''); ?>"></div>
        </div>
        <div class="environment"><label><input type="radio" name="sunat_entorno" value="FE_BETA" <?php echo meta($m,'sunat_entorno','FE_BETA')==='FE_BETA'?'checked':''; ?>><span><strong>🧪 BETA / PRUEBAS</strong><small>Validar firmas y envíos sin validez fiscal.</small></span></label><label><input type="radio" name="sunat_entorno" value="FE_PRODUCCION" <?php echo meta($m,'sunat_entorno')==='FE_PRODUCCION'?'checked':''; ?>><span><strong>🚀 PRODUCCIÓN</strong><small>Comprobantes reales enviados a SUNAT.</small></span></label></div>
        <div class="sunat-box"><div class="sunat-title"><h2><i class="fa fa-shield"></i> Facturación electrónica SUNAT</h2><span class="badge-status"><?php echo !empty($m['certificado'])?'Certificado cargado':'Pendiente'; ?></span></div>
          <div class="form-grid"><div class="field"><label>Usuario SOL</label><input name="usuario_sol" value="<?php echo h(meta($m,'usuario_sol')); ?>"></div><div class="field"><label>Nueva clave SOL</label><input name="clave_sol" type="password" autocomplete="new-password" placeholder="Dejar vacío para conservar"></div><div class="field"><label>Nueva contraseña del certificado</label><input name="clave_certificado" type="password" autocomplete="new-password" placeholder="Dejar vacío para conservar"></div><div class="field"><label>Certificado actual</label><input readonly value="<?php echo h(meta($m,'certificado_nombre',!empty($m['certificado'])?'Certificado almacenado':'Pendiente')); ?>"></div></div>
          <div class="cert-row"><div><strong>● Certificado digital PFX o P12</strong><small>Seleccione el certificado emitido para el RUC de la empresa.</small></div><label class="cert-button"><i class="fa fa-lock"></i> Seleccionar certificado<input type="file" name="certificado" accept=".pfx,.p12"></label></div>
        </div>
      </section>
    </div>
    <div class="ec-note"><i class="fa fa-refresh"></i>Esta información funciona como fuente central para el acceso, identidad corporativa y configuración fiscal.</div>
    <div class="ec-actions"><button class="save-button" id="saveButton" type="submit"><i class="fa fa-floppy-o"></i> Guardar y sincronizar</button></div>
  </form>
</main><div class="toast-config" id="configToast" role="status"></div>
<script>
document.querySelectorAll('input[type=file][data-preview]').forEach(function(input){input.addEventListener('change',function(){var file=this.files[0],target=document.getElementById(this.dataset.preview);if(file&&target)target.src=URL.createObjectURL(file);});});
document.getElementById('empresaForm').addEventListener('submit',function(e){e.preventDefault();var button=document.getElementById('saveButton'),toast=document.getElementById('configToast');button.disabled=true;button.innerHTML='<i class="fa fa-spinner fa-spin"></i> Guardando...';fetch('/controllers/EmpresaConfigGuardar.php',{method:'POST',body:new FormData(this),credentials:'same-origin'}).then(function(r){return r.json().then(function(data){return {ok:r.ok,data:data};});}).then(function(result){toast.textContent=result.data.message;toast.className='toast-config show'+(result.ok?'':' error');if(result.ok)setTimeout(function(){location.reload();},1200);}).catch(function(){toast.textContent='No se pudo conectar con el servidor.';toast.className='toast-config show error';}).finally(function(){button.disabled=false;button.innerHTML='<i class="fa fa-floppy-o"></i> Guardar y sincronizar';setTimeout(function(){toast.classList.remove('show');},4500);});});
</script>
</body></html>
