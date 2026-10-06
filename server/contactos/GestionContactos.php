<?php
include_once __DIR__ . '/validateUser.php';
require_once __DIR__ . '/../models/DBManager.php';

$db = getMysqliLink();
$tipo = ($_GET['tipo'] ?? 'clientes') === 'proveedores' ? 'proveedores' : 'clientes';
$esSuperadmin = strtolower(trim((string) ($_SESSION['user'] ?? ''))) === 'superadmin';
$tabla = $tipo === 'clientes' ? 'Ve_DocVentaCliente' : 'Lo_Proveedor';
$idCampo = $tipo === 'clientes' ? 'IdCliente' : 'IdProveedor';
$nombreCampo = $tipo === 'clientes' ? 'Cliente' : 'Proveedor';

function responder($data, $status = 200)
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

if (isset($_GET['accion'])) {
    $accion = $_GET['accion'];

    if ($accion === 'listar') {
        $q = trim((string) ($_GET['q'] ?? ''));
        $like = '%' . $q . '%';
        if ($tipo === 'clientes') {
            $stmt = $db->prepare("SELECT IdCliente,Cliente,DniRuc,Direccion,Telefono,Email,EsVIP,FechaNacimiento FROM Ve_DocVentaCliente WHERE (Anulado<>1 OR Anulado IS NULL) AND (Cliente LIKE ? OR DniRuc LIKE ?) ORDER BY Cliente,DniRuc LIMIT 1000");
            $stmt->bind_param('ss', $like, $like);
        } else {
            $stmt = $db->prepare("SELECT IdProveedor,Proveedor,Ruc,Direccion,Telefono,Observacion FROM Lo_Proveedor WHERE Proveedor LIKE ? OR Ruc LIKE ? OR Telefono LIKE ? ORDER BY Proveedor,Ruc LIMIT 1000");
            $stmt->bind_param('sss', $like, $like, $like);
        }
        $stmt->execute();
        responder($stmt->get_result()->fetch_all(MYSQLI_ASSOC));
    }

    if ($accion === 'guardar') {
        $data = json_decode(file_get_contents('php://input'), true) ?: array();
        $id = (int) ($data[$idCampo] ?? 0);
        $nombre = trim((string) ($data[$nombreCampo] ?? ''));
        if ($nombre === '') responder(array('error' => 'El nombre es obligatorio.'), 422);

        if ($tipo === 'clientes') {
            $doc = trim((string) ($data['DniRuc'] ?? ''));
            $dir = trim((string) ($data['Direccion'] ?? ''));
            $tel = trim((string) ($data['Telefono'] ?? ''));
            $email = trim((string) ($data['Email'] ?? ''));
            $vip = !empty($data['EsVIP']) ? 1 : 0;
            $nac = trim((string) ($data['FechaNacimiento'] ?? ''));
            $stmt = $db->prepare("UPDATE Ve_DocVentaCliente SET Cliente=?,DniRuc=?,Direccion=?,Telefono=?,Email=?,EsVIP=?,FechaNacimiento=NULLIF(?,'') WHERE IdCliente=?");
            $stmt->bind_param('sssssisi', $nombre, $doc, $dir, $tel, $email, $vip, $nac, $id);
        } else {
            $ruc = trim((string) ($data['Ruc'] ?? ''));
            $dir = trim((string) ($data['Direccion'] ?? ''));
            $tel = trim((string) ($data['Telefono'] ?? ''));
            $obs = trim((string) ($data['Observacion'] ?? ''));
            $stmt = $db->prepare("UPDATE Lo_Proveedor SET Proveedor=?,Ruc=?,Direccion=?,Telefono=?,Observacion=? WHERE IdProveedor=?");
            $stmt->bind_param('sssssi', $nombre, $ruc, $dir, $tel, $obs, $id);
        }
        $stmt->execute();
        responder(array('ok' => true));
    }

    if ($accion === 'crear') {
        if ($tipo !== 'clientes') responder(array('error' => 'Esta acción solo permite crear clientes.'), 422);

        $data = json_decode(file_get_contents('php://input'), true) ?: array();
        $doc = preg_replace('/\D+/', '', (string) ($data['DniRuc'] ?? ''));
        $nombre = trim((string) ($data['Cliente'] ?? ''));
        $dir = trim((string) ($data['Direccion'] ?? ''));
        $tel = trim((string) ($data['Telefono'] ?? ''));
        $email = trim((string) ($data['Email'] ?? ''));
        $vip = !empty($data['EsVIP']) ? 1 : 0;
        $nac = trim((string) ($data['FechaNacimiento'] ?? ''));

        if (!in_array(strlen($doc), array(8, 11), true)) {
            responder(array('error' => 'Ingrese un DNI de 8 dígitos o un RUC de 11 dígitos.'), 422);
        }
        if ($nombre === '') responder(array('error' => 'El nombre del cliente es obligatorio.'), 422);
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            responder(array('error' => 'Ingrese un correo electrónico válido.'), 422);
        }

        $existente = $db->prepare("SELECT IdCliente FROM Ve_DocVentaCliente WHERE DniRuc=? LIMIT 1");
        $existente->bind_param('s', $doc);
        $existente->execute();
        if ($existente->get_result()->fetch_assoc()) {
            responder(array('error' => 'Ya existe un cliente con ese DNI/RUC.'), 409);
        }

        $stmt = $db->prepare("INSERT INTO Ve_DocVentaCliente(Cliente,DniRuc,Direccion,Telefono,Email,EsVIP,FechaNacimiento,Anulado,FechaReg) VALUES(?,?,?,?,?,?,NULLIF(?,''),0,NOW())");
        $stmt->bind_param('sssssis', $nombre, $doc, $dir, $tel, $email, $vip, $nac);
        $stmt->execute();
        responder(array('ok' => true, 'IdCliente' => $stmt->insert_id));
    }

    if ($accion === 'exportar') {
        if (!$esSuperadmin) responder(array('error' => 'Solo SUPERADMIN puede exportar.'), 403);

        require_once __DIR__ . '/../api/vendor/autoload.php';
        $sheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $ws = $sheet->getActiveSheet();
        $ws->setTitle(ucfirst($tipo));
        $headers = $tipo === 'clientes'
            ? array('Documento', 'Cliente', 'Dirección', 'Teléfono', 'Email', 'VIP', 'Fecha nacimiento')
            : array('RUC', 'Proveedor', 'Dirección', 'Teléfono', 'Observación');
        $ws->fromArray($headers, null, 'A1');
        $sql = $tipo === 'clientes'
            ? "SELECT DniRuc,Cliente,Direccion,Telefono,Email,IF(EsVIP=1,'SI','NO'),FechaNacimiento FROM Ve_DocVentaCliente WHERE (Anulado<>1 OR Anulado IS NULL) ORDER BY Cliente,DniRuc"
            : "SELECT Ruc,Proveedor,Direccion,Telefono,Observacion FROM Lo_Proveedor ORDER BY Proveedor,Ruc";
        $rows = $db->query($sql)->fetch_all(MYSQLI_NUM);
        if ($rows) $ws->fromArray($rows, null, 'A2');
        $last = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
        $ws->getStyle("A1:{$last}1")->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $ws->getStyle("A1:{$last}1")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF075247');
        foreach (range(1, count($headers)) as $column) $ws->getColumnDimensionByColumn($column)->setAutoSize(true);
        $ws->setAutoFilter("A1:{$last}1");
        $ws->freezePane('A2');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $tipo . '-' . date('Ymd') . '.xlsx"');
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($sheet))->save('php://output');
        exit;
    }

    if ($accion === 'importar') {
        if (!$esSuperadmin) responder(array('error' => 'Solo SUPERADMIN puede importar.'), 403);
        if (empty($_FILES['archivo']['tmp_name'])) responder(array('error' => 'Seleccione un archivo Excel.'), 422);

        require_once __DIR__ . '/../api/vendor/autoload.php';
        $rows = \PhpOffice\PhpSpreadsheet\IOFactory::load($_FILES['archivo']['tmp_name'])->getActiveSheet()->toArray(null, true, true, false);
        array_shift($rows);
        $procesados = 0;
        foreach ($rows as $row) {
            $doc = trim((string) ($row[0] ?? ''));
            $nombre = trim((string) ($row[1] ?? ''));
            if ($doc === '' || $nombre === '') continue;

            if ($tipo === 'clientes') {
                $dir = trim((string) ($row[2] ?? ''));
                $tel = trim((string) ($row[3] ?? ''));
                $email = trim((string) ($row[4] ?? ''));
                $vip = strtoupper(trim((string) ($row[5] ?? ''))) === 'SI' ? 1 : 0;
                $nac = trim((string) ($row[6] ?? ''));
                $stmt = $db->prepare("INSERT INTO Ve_DocVentaCliente(Cliente,DniRuc,Direccion,Telefono,Email,EsVIP,FechaNacimiento,Anulado,FechaReg) VALUES(?,?,?,?,?,?,NULLIF(?,''),0,NOW()) ON DUPLICATE KEY UPDATE Cliente=VALUES(Cliente),Direccion=VALUES(Direccion),Telefono=VALUES(Telefono),Email=VALUES(Email),EsVIP=VALUES(EsVIP),FechaNacimiento=VALUES(FechaNacimiento)");
                $stmt->bind_param('sssssds', $nombre, $doc, $dir, $tel, $email, $vip, $nac);
            } else {
                $dir = trim((string) ($row[2] ?? ''));
                $tel = trim((string) ($row[3] ?? ''));
                $obs = trim((string) ($row[4] ?? ''));
                $stmt = $db->prepare("INSERT INTO Lo_Proveedor(Proveedor,Ruc,Direccion,Telefono,Observacion,FechaReg) VALUES(?,?,?,?,?,NOW()) ON DUPLICATE KEY UPDATE Proveedor=VALUES(Proveedor),Direccion=VALUES(Direccion),Telefono=VALUES(Telefono),Observacion=VALUES(Observacion)");
                $stmt->bind_param('sssss', $nombre, $doc, $dir, $tel, $obs);
            }
            $stmt->execute();
            $procesados++;
        }
        responder(array('ok' => true, 'procesados' => $procesados));
    }
}
?><!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Gestión de <?php echo htmlspecialchars($tipo, ENT_QUOTES, 'UTF-8'); ?></title>
  <?php include __DIR__ . '/linker.php'; ?>
  <style>
    :root{--gc-dark:#05382f;--gc-main:#075247;--gc-light:#0b7467;--gc-cyan:#16c8f4;--gc-bg:#f1f8f6;--gc-soft:#eef7f5;--gc-line:#bad7d1;--gc-text:#173247;--gc-blue:#4357df}
    *{box-sizing:border-box}
    body{margin:0;color:var(--gc-text);background:var(--gc-bg);font-family:Arial,sans-serif}
    .gc{width:calc(100% - 44px);max-width:1780px;margin:22px auto;padding:0}
    .gc-head{display:flex;min-height:104px;padding:21px 26px;align-items:center;gap:16px;border:1px solid rgba(22,200,244,.2);border-radius:20px;color:#fff;background:linear-gradient(115deg,var(--gc-dark),var(--gc-main) 62%,var(--gc-light));box-shadow:0 14px 30px rgba(5,56,47,.16)}
    .gc-head>i{font-size:34px}
    .gc-head h1{margin:0;font-size:30px;font-weight:900;line-height:1}
    .gc-head p{margin:7px 0 0;color:#d7f2ec;font-size:13px}
    .gc-actions{display:flex;margin-left:auto;gap:10px}
    .gc button,.gc .export{display:inline-flex;min-height:48px;padding:0 17px;align-items:center;justify-content:center;gap:7px;border:0;border-radius:11px;color:#fff;background:var(--gc-blue);box-shadow:0 6px 0 #2737aa;font-size:14px;font-weight:900;text-decoration:none;cursor:pointer;white-space:nowrap}
    .gc .export{background:#11b960;box-shadow:0 6px 0 #07833f}
    .gc .gc-new{background:linear-gradient(135deg,#0b9f86,#087468);box-shadow:0 6px 0 #045247}
    .gc-card{margin-top:15px;padding:18px;border:1px solid var(--gc-line);border-radius:17px;background:#fff;box-shadow:0 12px 28px rgba(7,21,34,.06)}
    .gc-search{width:100%;height:48px;padding:0 15px;border:1px solid var(--gc-line);border-radius:12px;color:var(--gc-text);font-size:15px;outline:0}
    .gc-search:focus{border-color:var(--gc-cyan);box-shadow:0 0 0 3px rgba(22,200,244,.14)}
    .gc-table-status{display:flex;padding:12px 2px 8px;align-items:center;justify-content:space-between;color:#5c746f;font-size:12px;font-weight:800}
    .gc-table-status strong{color:var(--gc-main);font-size:13px}
    .gc-table-wrap{width:100%;overflow-x:auto;border:1px solid #d5e5e1;border-radius:13px;background:#fff;scrollbar-color:#8eb7af #edf5f3;scrollbar-width:thin}
    .gc-table{width:100%;min-width:1500px;border-collapse:separate;border-spacing:0;table-layout:fixed}
    .gc-table th{position:sticky;top:0;z-index:2;height:46px;padding:10px 12px;color:#fff;background:#0a665a;font-size:12px;font-weight:900;text-align:left;white-space:nowrap}
    .gc-table th:first-child{border-radius:11px 0 0 0}
    .gc-table th:last-child{border-radius:0 11px 0 0;text-align:center}
    .gc-table td{height:62px;padding:8px 9px;border-bottom:1px solid #d8e7e3;background:#fff;vertical-align:middle}
    .gc-table tbody tr:nth-child(even) td{background:var(--gc-soft)}
    .gc-table tbody tr:hover td{background:#e2f5f1}
    .gc-table input{width:100%;height:42px;min-width:0;padding:0 11px;border:1px solid #b8d3cd;border-radius:9px;color:#173247;background:#fff;font-size:13px;outline:0}
    .gc-table input:focus{border-color:#0ea5a0;box-shadow:0 0 0 3px rgba(14,165,160,.13)}
    .gc-table td.gc-cell-name input{font-size:16px;font-weight:900;letter-spacing:.01em}
    .gc-table td.gc-cell-address input{font-size:14px;font-weight:700}
    .gc-table .gc-col-document{width:155px}
    .gc-table .gc-col-name{width:310px}
    .gc-table .gc-col-address{width:330px}
    .gc-table .gc-col-phone{width:165px}
    .gc-table .gc-col-email{width:250px}
    .gc-table .gc-col-vip{width:86px}
    .gc-table .gc-col-date{width:165px}
    .gc-table .gc-col-action{width:125px}
    .gc-table.gc-table-proveedores{min-width:1320px}
    .gc-table.gc-table-proveedores .gc-col-name{width:330px}
    .gc-table.gc-table-proveedores .gc-col-address{width:360px}
    .gc-table.gc-table-proveedores .gc-col-observation{width:300px}
    .gc-save{width:100%;min-height:42px!important;padding:0 10px!important;font-size:12px!important;box-shadow:0 4px 0 #2737aa!important}
    input.gc-file{display:none!important}
    .gc-modal[hidden]{display:none}
    .gc-modal{position:fixed;z-index:10000;inset:0;display:flex;padding:18px;align-items:center;justify-content:center;background:rgba(4,25,35,.72);backdrop-filter:blur(5px)}
    .gc-modal-card{width:min(720px,100%);max-height:calc(100vh - 36px);overflow:auto;border:1px solid rgba(22,200,244,.34);border-radius:20px;background:#fff;box-shadow:0 28px 70px rgba(2,17,27,.38)}
    .gc-modal-head{display:flex;padding:19px 22px;align-items:center;gap:13px;color:#fff;background:linear-gradient(120deg,var(--gc-dark),var(--gc-main))}
    .gc-modal-head>i{display:flex;width:42px;height:42px;align-items:center;justify-content:center;border-radius:12px;color:var(--gc-dark);background:#9eefff;font-size:20px}
    .gc-modal-head h2{margin:0;font-size:22px;font-weight:900}.gc-modal-head p{margin:4px 0 0;color:#d7f2ec;font-size:12px}
    .gc-modal-close{width:38px;min-height:38px!important;margin-left:auto;padding:0!important;border:1px solid rgba(255,255,255,.25)!important;border-radius:50%!important;background:rgba(255,255,255,.1)!important;box-shadow:none!important;font-size:18px!important}
    .gc-modal-body{display:grid;padding:20px 22px;grid-template-columns:repeat(2,minmax(0,1fr));gap:15px}
    .gc-field{display:flex;min-width:0;flex-direction:column;gap:6px}.gc-field-wide{grid-column:1/-1}
    .gc-field label{color:#385b55;font-size:11px;font-weight:900;text-transform:uppercase}
    .gc-field input{width:100%;height:44px;padding:0 12px;border:1px solid var(--gc-line);border-radius:10px;color:var(--gc-text);font-size:14px;outline:0}
    .gc-field input:focus{border-color:var(--gc-cyan);box-shadow:0 0 0 3px rgba(22,200,244,.14)}
    .gc-check{display:flex;min-height:44px;padding:0 13px;align-items:center;gap:9px;border:1px solid var(--gc-line);border-radius:10px;background:var(--gc-soft);font-weight:800}
    .gc-check input{width:18px;height:18px}
    .gc-modal-actions{display:flex;padding:0 22px 22px;justify-content:flex-end;gap:10px}
    .gc-modal-actions .gc-cancel{color:#294557;background:#e8f0f2;box-shadow:0 6px 0 #c5d5d9}
    .gc-modal-actions .gc-create{background:linear-gradient(90deg,#075247,#0b8c78);box-shadow:0 6px 0 #034b40}
    @media(max-width:900px){
      .gc{width:calc(100% - 20px);margin:12px auto}
      .gc-head{padding:18px;align-items:flex-start;flex-direction:column}
      .gc-head h1{font-size:26px}
      .gc-actions{width:100%;margin:2px 0 0;flex-wrap:wrap}
      .gc-actions>*{flex:1}
      .gc-card{padding:10px}
      .gc-table-status{padding-right:4px;padding-left:4px}
      .gc-modal{padding:10px}.gc-modal-body{padding:16px;grid-template-columns:1fr}.gc-field-wide{grid-column:auto}.gc-modal-actions{padding:0 16px 18px}.gc-modal-actions button{flex:1;padding:0 9px!important}
    }
  </style>
</head>
<body class="<?php echo ($_GET['modo'] ?? '') === 'app' ? 'modo-app' : 'modo-escritorio'; ?>">
<?php include __DIR__ . '/header.php'; ?>
<main class="gc">
  <header class="gc-head">
    <i class="fa fa-<?php echo $tipo === 'clientes' ? 'users' : 'truck'; ?>"></i>
    <div>
      <h1><?php echo ucfirst($tipo); ?></h1>
      <p>Buscar, revisar y editar registros</p>
    </div>
    <?php if ($tipo === 'clientes' || $esSuperadmin): ?>
      <div class="gc-actions">
        <?php if ($tipo === 'clientes'): ?>
          <button class="gc-new" type="button" onclick="abrirNuevoCliente()"><i class="fa fa-user-plus"></i> Nuevo cliente</button>
        <?php endif; ?>
        <?php if ($esSuperadmin): ?>
        <a class="export" href="?tipo=<?php echo $tipo; ?>&accion=exportar"><i class="fa fa-file-excel-o"></i> Exportar Excel</a>
        <button type="button" onclick="document.getElementById('archivo').click()"><i class="fa fa-upload"></i> Importar Excel</button>
        <input class="gc-file" id="archivo" type="file" accept=".xlsx,.xls" onchange="importar(this)">
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </header>
  <section class="gc-card">
    <input id="buscar" class="gc-search" placeholder="Buscar por nombre o documento" oninput="buscarDebounce()">
    <div class="gc-table-status"><span>Edición directa de registros</span><strong id="totalRegistros">0 registros</strong></div>
    <div id="tabla" class="gc-table-wrap"></div>
  </section>
</main>
<?php if ($tipo === 'clientes'): ?>
<div id="modalNuevoCliente" class="gc-modal" hidden onclick="cerrarNuevoClienteDesdeFondo(event)">
  <form class="gc-modal-card" onsubmit="crearCliente(event)">
    <header class="gc-modal-head">
      <i class="fa fa-user-plus"></i>
      <div><h2>Nuevo cliente</h2><p>Registre sus datos para usarlo inmediatamente en ventas.</p></div>
      <button type="button" class="gc-modal-close" onclick="cerrarNuevoCliente()" aria-label="Cerrar">&times;</button>
    </header>
    <div class="gc-modal-body">
      <div class="gc-field"><label for="nuevoDocumento">DNI / RUC *</label><input id="nuevoDocumento" name="DniRuc" required inputmode="numeric" maxlength="11" placeholder="8 u 11 dígitos"></div>
      <div class="gc-field"><label for="nuevoNombre">Nombre o razón social *</label><input id="nuevoNombre" name="Cliente" required maxlength="180" placeholder="Nombre completo del cliente"></div>
      <div class="gc-field gc-field-wide"><label for="nuevoDireccion">Dirección</label><input id="nuevoDireccion" name="Direccion" maxlength="250" placeholder="Dirección principal"></div>
      <div class="gc-field"><label for="nuevoTelefono">Teléfono</label><input id="nuevoTelefono" name="Telefono" inputmode="tel" maxlength="30" placeholder="Número de contacto"></div>
      <div class="gc-field"><label for="nuevoEmail">Correo electrónico</label><input id="nuevoEmail" name="Email" type="email" maxlength="150" placeholder="correo@ejemplo.com"></div>
      <div class="gc-field"><label for="nuevoNacimiento">Fecha de nacimiento</label><input id="nuevoNacimiento" name="FechaNacimiento" type="date"></div>
      <div class="gc-field"><label>Clasificación</label><label class="gc-check"><input name="EsVIP" type="checkbox" value="1"> Cliente VIP</label></div>
    </div>
    <footer class="gc-modal-actions">
      <button type="button" class="gc-cancel" onclick="cerrarNuevoCliente()"><i class="fa fa-times"></i> Cancelar</button>
      <button id="guardarNuevoCliente" type="submit" class="gc-create"><i class="fa fa-save"></i> Guardar cliente</button>
    </footer>
  </form>
</div>
<?php endif; ?>
<script>
const tipo=<?php echo json_encode($tipo); ?>;
let registros=[];
let timer;
const escapar=valor=>String(valor??'').replace(/[&<>"']/g,caracter=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[caracter]));

async function cargar(){
  const q=document.getElementById('buscar').value;
  registros=await fetch(`?tipo=${tipo}&accion=listar&q=${encodeURIComponent(q)}`).then(respuesta=>respuesta.json());
  render();
}

function buscarDebounce(){
  clearTimeout(timer);
  timer=setTimeout(cargar,220);
}

function render(){
  const esCliente=tipo==='clientes';
  const headers=esCliente
    ? ['Documento','Cliente','Dirección','Teléfono','Email','VIP','Nacimiento','Acción']
    : ['RUC','Proveedor','Dirección','Teléfono','Observación','Acción'];
  const keys=esCliente
    ? ['DniRuc','Cliente','Direccion','Telefono','Email','EsVIP','FechaNacimiento']
    : ['Ruc','Proveedor','Direccion','Telefono','Observacion'];
  const clases=esCliente
    ? ['document','name','address','phone','email','vip','date','action']
    : ['document','name','address','phone','observation','action'];
  const colgroup=`<colgroup>${clases.map(clase=>`<col class="gc-col-${clase}">`).join('')}</colgroup>`;
  const filas=registros.map((registro,indice)=>`<tr>${keys.map((key,posicion)=>{
    const tipoInput=key==='EsVIP'?'type="number" min="0" max="1"':key==='Telefono'?'type="tel" inputmode="tel"':'type="text"';
    const claseCelda=posicion===1?'gc-cell-name':posicion===2?'gc-cell-address':'';
    return `<td class="${claseCelda}"><input data-i="${indice}" data-k="${key}" value="${escapar(registro[key])}" ${tipoInput}></td>`;
  }).join('')}<td><button class="gc-save" onclick="guardar(${indice})"><i class="fa fa-save"></i> Guardar</button></td></tr>`).join('');
  const vacio=`<tr><td colspan="${headers.length}" style="padding:28px;text-align:center;font-weight:800;color:#5c746f">No se encontraron registros.</td></tr>`;
  document.getElementById('tabla').innerHTML=`<table class="gc-table ${esCliente?'gc-table-clientes':'gc-table-proveedores'}">${colgroup}<thead><tr>${headers.map(header=>`<th>${header}</th>`).join('')}</tr></thead><tbody>${filas||vacio}</tbody></table>`;
  document.getElementById('totalRegistros').textContent=`${registros.length} ${registros.length===1?'registro':'registros'}`;
}

async function guardar(indice){
  document.querySelectorAll(`[data-i="${indice}"]`).forEach(input=>registros[indice][input.dataset.k]=input.value);
  const respuesta=await fetch(`?tipo=${tipo}&accion=guardar`,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(registros[indice])});
  const datos=await respuesta.json();
  alert(datos.error||'Registro actualizado correctamente');
}

function abrirNuevoCliente(){
  const modal=document.getElementById('modalNuevoCliente');
  if(!modal)return;
  modal.hidden=false;
  document.body.style.overflow='hidden';
  setTimeout(()=>document.getElementById('nuevoDocumento').focus(),50);
}

function cerrarNuevoCliente(){
  const modal=document.getElementById('modalNuevoCliente');
  if(!modal)return;
  modal.hidden=true;
  document.body.style.overflow='';
}

function cerrarNuevoClienteDesdeFondo(evento){
  if(evento.target&&evento.target.id==='modalNuevoCliente')cerrarNuevoCliente();
}

async function crearCliente(evento){
  evento.preventDefault();
  const formulario=evento.currentTarget;
  const boton=document.getElementById('guardarNuevoCliente');
  const datos=Object.fromEntries(new FormData(formulario).entries());
  datos.DniRuc=String(datos.DniRuc||'').replace(/\D/g,'');
  datos.EsVIP=formulario.elements.EsVIP.checked?1:0;
  if(![8,11].includes(datos.DniRuc.length)){
    alert('Ingrese un DNI de 8 dígitos o un RUC de 11 dígitos.');
    document.getElementById('nuevoDocumento').focus();
    return;
  }
  boton.disabled=true;
  boton.innerHTML='<i class="fa fa-spinner fa-spin"></i> Guardando...';
  try{
    const respuesta=await fetch(`?tipo=clientes&accion=crear`,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(datos)});
    const resultado=await respuesta.json();
    if(!respuesta.ok||resultado.error)throw new Error(resultado.error||'No se pudo guardar el cliente.');
    formulario.reset();
    cerrarNuevoCliente();
    document.getElementById('buscar').value='';
    await cargar();
    alert('Cliente creado correctamente.');
  }catch(error){
    alert(error.message||'No se pudo guardar el cliente.');
  }finally{
    boton.disabled=false;
    boton.innerHTML='<i class="fa fa-save"></i> Guardar cliente';
  }
}

document.addEventListener('keydown',evento=>{
  if(evento.key==='Escape')cerrarNuevoCliente();
});

async function importar(input){
  if(!input.files||!input.files[0])return;
  const formulario=new FormData();
  formulario.append('archivo',input.files[0]);
  const respuesta=await fetch(`?tipo=${tipo}&accion=importar`,{method:'POST',body:formulario});
  const datos=await respuesta.json();
  alert(datos.error||`${datos.procesados} registros importados`);
  input.value='';
  cargar();
}

cargar();
</script>
</body>
</html>
