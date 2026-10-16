<?php
return function ($request, $response) {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    $db = $this->db;
    if (empty($_SESSION['user'])) return $response->withJson(array('error'=>'Inicie sesión nuevamente.'),401);
    $idTipo=(int)$request->getParam('IdTipoCajaBanco');$idCuenta=(int)$request->getParam('IdCuenta');
    $fecha=trim((string)$request->getParam('FechaDoc'));$concepto=trim((string)$request->getParam('Concepto'));$importe=(float)$request->getParam('Importe');
    $cliente=(int)$request->getParam('IdCliente');$proveedor=(int)$request->getParam('IdProveedor');
    $aplicados=$request->getParam('AplicadoDocVenta') ?: array();
    $date=DateTime::createFromFormat('!Y-m-d',$fecha,new DateTimeZone('America/Lima'));
    if(!$idTipo||!$idCuenta||!$date||$date->format('Y-m-d')!==$fecha||$concepto===''||mb_strlen($concepto)>255||!is_finite($importe)||$importe<=0||!is_array($aplicados))return $response->withJson(array('error'=>'Seleccione cuenta, tipo, fecha válida, concepto e importe mayor a cero.'),422);
    $stmt=$db->prepare('SELECT Tipo FROM Cb_TipoCajaBanco WHERE IdTipoCajaBanco=?');$stmt->execute(array($idTipo));$operation=$stmt->fetch(PDO::FETCH_ASSOC);
    $stmt=$db->prepare('SELECT IdCuenta FROM Cb_Cuenta WHERE IdCuenta=?');$stmt->execute(array($idCuenta));
    if(!$operation||!$stmt->fetchColumn())return $response->withJson(array('error'=>'La cuenta o el tipo de operación no existe.'),422);
    $tipo=(int)$operation['Tipo'];
    if(in_array($idTipo,array(1,2),true)&&(($tipo===1&&!$proveedor)||($tipo===0&&!$cliente)))return $response->withJson(array('error'=>'Seleccione el proveedor o cliente del movimiento.'),422);
    if(in_array($idTipo,array(1,2),true)&&!count($aplicados))return $response->withJson(array('error'=>'Debe aplicar el pago a una deuda pendiente antes de guardar.'),422);
    $appliedTotal=0;
    foreach($aplicados as $item){if(empty($item['Id'])||!isset($item['Aplicado'])||!is_finite((float)$item['Aplicado'])||(float)$item['Aplicado']<=0)return $response->withJson(array('error'=>'Revise los documentos aplicados.'),422);$appliedTotal+=(float)$item['Aplicado'];}
    if(round($appliedTotal,2)>round($importe,2))return $response->withJson(array('error'=>'El importe aplicado supera el movimiento.'),422);
    if(in_array($idTipo,array(1,2),true)&&abs(round($appliedTotal,2)-round($importe,2))>0.001)return $response->withJson(array('error'=>'El importe debe coincidir con el total aplicado a los documentos.'),422);
    try {
        $db->beginTransaction();
        $stmt=$db->prepare('INSERT INTO Cb_CajaBanco(IdTipoCajaBanco,IdCuenta,FechaDoc,Concepto,Importe,IdProveedor,IdCliente,UsuarioReg) VALUES(?,?,?,?,?,?,?,?)');
        $stmt->execute(array($idTipo,$idCuenta,$fecha.' '.(new DateTime('now',new DateTimeZone('America/Lima')))->format('H:i:s'),$concepto,$importe,$proveedor,$cliente,$_SESSION['user']));
        $id=(int)$db->lastInsertId();
        foreach($aplicados as $item){
            if(!empty($item['IdLetraCaja'])){$stmt=$db->prepare('UPDATE Cb_LetraCaja SET Cancelado=1,IdCajaBanco=? WHERE IdLetraCaja=?');$stmt->execute(array($id,(int)$item['IdLetraCaja']));}
            $stmt=$db->prepare('INSERT INTO Cb_CajaBancoDet(IdCajaBanco,IdDocDet,Importe,Hash,Tipo) VALUES(?,?,?,?,?)');
            $stmt->execute(array($id,(int)$item['Id'],(float)$item['Aplicado'],$tipo===1?(string)$item['Id']:null,$tipo===1?'MO':'VE'));
        }
        $db->commit();return $response->withJson($id);
    }catch(Throwable $e){if($db->inTransaction())$db->rollBack();error_log('[NST-CASH-SAVE] '.$e->getMessage());return $response->withJson(array('error'=>'No se pudo guardar el movimiento. Intente nuevamente.'),500);}
};
