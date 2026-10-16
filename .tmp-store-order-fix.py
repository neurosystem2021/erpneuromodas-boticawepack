from pathlib import Path
root=Path('deploy-src/store-order-fix');root.mkdir(parents=True,exist_ok=True)
s=Path('C:/xampp/htdocs/boticav1/api/tienda/index.php').read_text(encoding='utf-8')
block='''    if ($action === 'pedido') {
        if (($_SERVER['REQUEST_METHOD']??'GET')!=='POST') tiendaJsonError('Método no permitido.',405);
        $items=$body['productos']??array();
        if(!is_array($items)||!count($items)||count($items)>100) tiendaJsonError('Carrito inválido.',422);
        $clientId=(int)($_SESSION['tienda_cliente_id']??0);
        if(!$clientId) $clientId=(int)$pdo->query("SELECT IdCliente FROM Ve_DocVentaCliente WHERE Cliente LIKE '%VARIOS%' AND COALESCE(Anulado,0)=0 ORDER BY IdCliente LIMIT 1")->fetchColumn();
        if(!$clientId) tiendaJsonError('No se encontró el cliente para registrar el pedido.',422);
        $details=array();
        $product=$pdo->prepare('SELECT IdProducto,COALESCE(PrecioWeb,PrecioContado) precio FROM Gen_Producto WHERE IdProducto=? AND Anulado=0');
        foreach($items as $item){
            $qty=(float)($item['cantidad']??0);if(!is_finite($qty)||$qty<=0||$qty>100000) tiendaJsonError('Cantidad inválida.',422);
            $product->execute(array((int)($item['IdProducto']??0)));$p=$product->fetch();
            if(!$p) tiendaJsonError('Un producto ya no está disponible.',422);
            $details[]=array((int)$p['IdProducto'],$qty,(float)$p['precio']);
        }
        $pdo->beginTransaction();
        try{
            $year=(int)date('Y');
            $number=(int)$pdo->query('SELECT COALESCE(MAX(Numero),0)+1 FROM Ve_Proforma WHERE Anio='.$year.' FOR UPDATE')->fetchColumn();
            $stmt=$pdo->prepare("INSERT INTO Ve_Proforma(Anio,Numero,IdCliente,FechaReg,UsuarioReg,DiasValidos,Descripcion) VALUES(?,?,?,NOW(),'TIENDA',7,?)");
            $stmt->execute(array($year,$number,$clientId,$clientId===(int)($_SESSION['tienda_cliente_id']??0)?'Pedido desde tienda virtual':'Pedido web sin registro; contacto por WhatsApp'));
            $id=(int)$pdo->lastInsertId();
            $stmt=$pdo->prepare("INSERT INTO Ve_ProformaDet(IdProforma,IdProducto,Descripcion,Cantidad,Precio,Descuento,DisabledTotal) VALUES(?,?,'',?,?,0,0)");
            foreach($details as $d)$stmt->execute(array($id,$d[0],$d[1],$d[2]));
            $pdo->commit();
        }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
        echo json_encode(array('insertId'=>$id,'Numero'=>$number));exit;
    }
'''
s=s.replace("    if ($action === 'auth-status') {",block+"    if ($action === 'auth-status') {")
(root/'api.php').write_text(s,encoding='utf-8')
s=Path('C:/xampp/htdocs/boticav1/tienda/index.php').read_text(encoding='utf-8')
a=s.index('function sendGuestWhatsApp(){');b=s.index('\n',a)
s=s[:a]+"function sendGuestWhatsApp(){closeAuth();checkout(true)}"+s[b:]
s=s.replace('async function checkout(){','async function checkout(guest=false){',1)
s=s.replace('if(!currentCustomer){toggleCart(false);','if(!currentCustomer&&!guest){toggleCart(false);',1)
s=s.replace('const client=currentCustomer;','const client=currentCustomer||{nombre:\'Cliente web sin registro\',telefono:\'\',correo:\'\',direccion:\'\'};',1)
s=s.replace("if(!client||!client.IdCliente)throw new Error('No se encontró el cliente VARIOS.');",'')
s=s.replace("fetch(api+'/proformas',{method:'POST'", "fetch(API+'?action=pedido',{method:'POST'",1)
(root/'index.php').write_text(s,encoding='utf-8')
