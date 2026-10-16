from pathlib import Path
root=Path('integration/cash-save/api');root.mkdir(parents=True,exist_ok=True)
(root/'cash-movement-save.php').write_text('''<?php
return function ($request, $response) {
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
    $appliedTotal=0;
    foreach($aplicados as $item){if(empty($item['Id'])||!isset($item['Aplicado'])||!is_finite((float)$item['Aplicado'])||(float)$item['Aplicado']<=0)return $response->withJson(array('error'=>'Revise los documentos aplicados.'),422);$appliedTotal+=(float)$item['Aplicado'];}
    if(round($appliedTotal,2)>round($importe,2))return $response->withJson(array('error'=>'El importe aplicado supera el movimiento.'),422);
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
''',encoding='utf-8')
p=root/'index.php';s=Path('C:/xampp/htdocs/boticav1/api/index.php').read_text(encoding='utf-8');a=s.index("$app->post('/movimiento/guardarMovimiento'");b=s.index("$app->get('/reporte/rankingvendedor'",a);s=s[:a]+"$app->post('/movimiento/guardarMovimiento', require __DIR__ . '/cash-movement-save.php');\n\n"+s[b:];p.write_text(s,encoding='utf-8')
p=Path('src/views/CajaBancos.vue');s=p.read_text(encoding='utf-8');s=s.replace('      bloquearAplicar: false,','      guardandoMovimiento: false,\n      errorMovimiento: "",\n      bloquearAplicar: false,',1)
s=s.replace('<b-button :disabled="activarBtnGuardar()" variant="success" @click="GuardarMovimiento()">Guardar</b-button>', '''<b-alert :show="!!errorMovimiento" variant="danger">{{ errorMovimiento }}</b-alert>
                  <p v-if="(Number(IdTipoCajaBanco) === 1 || Number(IdTipoCajaBanco) === 2) && !lstAplicado.length" class="text-muted small">Sin documentos aplicados: se registrará como movimiento a cuenta.</p>
                  <b-button :disabled="activarBtnGuardar() || guardandoMovimiento" variant="success" @click="GuardarMovimiento()">{{ guardandoMovimiento ? 'Guardando...' : 'Guardar' }}</b-button>''',1)
s=s.replace('    async GuardarMovimiento() {\n      let data', '''    async GuardarMovimiento() {
      if (this.guardandoMovimiento || this.activarBtnGuardar()) return;
      this.guardandoMovimiento = true;
      this.errorMovimiento = '';
      let data''',1)
a=s.index('      await this.axios.post(`${this.API}movimiento/guardarMovimiento`, data);');b=s.index('\n    limpiarLuegoPago()',a)
s=s[:a]+'''      try {
        const response = await this.axios.post(`${this.API}movimiento/guardarMovimiento`, data);
        if (!Number(response.data)) throw new Error('El servidor no confirmó el guardado.');
        await this.cargarOperaciones();
        this.limpiarLuegoPago();
        this.$toasted.success('Movimiento guardado correctamente.');
      } catch (error) {
        this.errorMovimiento = (error.response && error.response.data && error.response.data.error) || error.message || 'No se pudo guardar el movimiento.';
      } finally { this.guardandoMovimiento = false; }
    },'''+s[b:]
a=s.index('    activarBtnGuardar() {');b=s.index('\n  },\n  computed:',a)
s=s[:a]+'''    activarBtnGuardar() {
      const operation = this.lstTipoOpe.find(v => Number(v.IdTipoCajaBanco) === Number(this.IdTipoCajaBanco));
      if (!operation || !this.IdCuenta || !this.FechaDoc || !String(this.Concepto || '').trim() || !Number.isFinite(Number(this.Importe)) || Number(this.Importe) <= 0) return true;
      if ([1, 2].includes(Number(operation.IdTipoCajaBanco))) {
        if (Number(operation.Tipo) === 1 && !(this.Proveedor && this.Proveedor.IdProveedor)) return true;
        if (Number(operation.Tipo) === 0 && !(this.Cliente && this.Cliente.IdCliente)) return true;
      }
      return false;
    }'''+s[b:]
p.write_text(s,encoding='utf-8')
