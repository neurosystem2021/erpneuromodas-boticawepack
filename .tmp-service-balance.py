from pathlib import Path
root=Path(r'C:\xampp\htdocs\boticav1');out=Path('integration/service-balance');
for rel in ['clases/BnGeneral.php','api/index.php']:
 s=(root/rel).read_text(encoding='utf-8');start=s.index('function fn_devolverProyecto(') if rel.startswith('clases') else s.index("$app->get('/imprimirpdfreciboservicio/{id}'");end=s.index('function fn_devolverProductosOrdenCompra',start) if rel.startswith('clases') else s.index("$app->get('/ordenCompra'",start)
 part=s[start:end]
 needle='(Ve_PreOrdenTotal.granTotal - Cb_CajaBancoAdelanto.sumAdelanto) AS granTotalCalculado,'
 assert needle in part
 part=part.replace(needle,'''(IFNULL(Ve_PreOrdenTotal.granTotal,0) - IFNULL(Cb_CajaBancoAdelanto.sumAdelanto,0)) AS granTotalCalculado,
            IFNULL(Ve_PreOrdenTotal.granTotal,0) AS TotalServicio,
            IFNULL(Cb_CajaBancoAdelanto.sumAdelanto,0) AS TotalAdelantos,
            GREATEST(IFNULL(Ve_PreOrdenTotal.granTotal,0)-IFNULL(Cb_CajaBancoAdelanto.sumAdelanto,0),0) AS SaldoPendiente,''',1)
 part=part.replace('Cb_TipoCajaBanco.Tipo = 0','Cb_TipoCajaBanco.Tipo = 0 AND IFNULL(Cb_CajaBanco.Anulado,0) = 0')
 if rel=='api/index.php':part=part.replace("$id = $args['id'];","$id = (int) $args['id'];",1)
 s=s[:start]+part+s[end:];p=out/rel;p.parent.mkdir(parents=True,exist_ok=True);p.write_text(s,encoding='utf-8')
rel='imprimir/imprimirReciboServicio.php';s=(root/rel).read_text(encoding='utf-8');needle='    <div class="no-print service-share">';a=s.index(needle)
s=s[:a]+'''    <table width="100%" style="border-top:1px dashed #000;border-bottom:2px solid #000;margin:14px 0">
      <tr><td>Total del servicio</td><td class="text-right">S/ <?php echo number_format((float)($proyecto['TotalServicio'] ?? 0),2); ?></td></tr>
      <tr><td>Adelanto recibido</td><td class="text-right">S/ <?php echo number_format((float)($proyecto['TotalAdelantos'] ?? 0),2); ?></td></tr>
      <tr><td>SALDO PENDIENTE</td><td class="text-right">S/ <?php echo number_format((float)($proyecto['SaldoPendiente'] ?? 0),2); ?></td></tr>
    </table>
'''+s[a:]
s=s.replace("'description'=>$proyecto['Descripcion'] ?? ''","'description'=>$proyecto['Descripcion'] ?? '', 'total'=>number_format((float)($proyecto['TotalServicio'] ?? 0),2), 'advance'=>number_format((float)($proyecto['TotalAdelantos'] ?? 0),2), 'balance'=>number_format((float)($proyecto['SaldoPendiente'] ?? 0),2)")
s=s.replace("'Descripción: '+receipt.description]","'Descripción: '+receipt.description,'Total del servicio: S/ '+receipt.total,'Adelanto recibido: S/ '+receipt.advance,'Saldo pendiente: S/ '+receipt.balance]")
p=out/rel;p.parent.mkdir(parents=True,exist_ok=True);p.write_text(s,encoding='utf-8')
rel='api/sunat/plugins/dompdf/impirimirReciboServicio.php';s=(root/rel).read_text(encoding='utf-8');s=s.replace("print_r($cab['detalle']);",'')
needle="    <p>' . $cab['detalle']['Descripcion'] . '</p>"
s=s.replace(needle,needle+'''
    <table width="100%" style="margin-top:18px;border-top:1px dashed #000;border-bottom:2px solid #000">
      <tr><td>Total del servicio</td><td align="right">S/ ' . number_format((float)($cab['detalle']['TotalServicio'] ?? 0),2) . '</td></tr>
      <tr><td>Adelanto recibido</td><td align="right">S/ ' . number_format((float)($cab['detalle']['TotalAdelantos'] ?? 0),2) . '</td></tr>
      <tr><td><b>SALDO PENDIENTE</b></td><td align="right"><b>S/ ' . number_format((float)($cab['detalle']['SaldoPendiente'] ?? 0),2) . '</b></td></tr>
    </table>''',1)
p=out/rel;p.parent.mkdir(parents=True,exist_ok=True);p.write_text(s,encoding='utf-8')
p=Path('src/views/Habitaciones.vue');s=p.read_text(encoding='utf-8')
for name,label,field in [('insertarGasto','gasto','Gasto'),('insertarAdelanto','adelanto','Adelanto')]:
 s=s.replace('    '+name+'() {','    async '+name+'() {',1)
 needle='      let r = confirm("¿Desea Insertar un '+label+'?");'
 assert needle in s
 s=s.replace(needle,'''      const confirmation = await this.$swal({title:'Registrar '''+label+'''',text:'Se registrará un '''+label+''' de S/ ' + Number(this.selectProducto.'''+field+''').toFixed(2) + ' en este proyecto.',icon:'question',showCancelButton:true,confirmButtonText:'Sí, registrar',cancelButtonText:'Cancelar',confirmButtonColor:'#079e87',reverseButtons:true,customClass:{popup:'project-confirm-dialog'}});
      const r = confirmation.isConfirmed;''',1)
p.write_text(s,encoding='utf-8')
