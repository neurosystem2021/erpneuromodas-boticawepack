from pathlib import Path
p=Path(r'C:\xampp\htdocs\boticav1\api\index.php');s=p.read_text(encoding='utf-8')
expr='''EXISTS(SELECT 1 FROM Ve_DocVenta documento
            INNER JOIN Ve_DocVentaDet detalle ON detalle.IdDocVenta=documento.idDocVenta
            LEFT JOIN Ve_PreOrden proyecto ON proyecto.IdPreOrden=Ve_PreOrdenDet.IdPreOrden
            WHERE (documento.IdPreOrden=Ve_PreOrdenDet.IdPreOrden OR documento.idDocVenta=proyecto.IdDocVenta)
            AND detalle.IdProducto=Ve_PreOrdenDet.IdProducto AND IFNULL(documento.Anulado,0)=0) AS Facturado,'''
for route in ["$app->get('/preorden/detalle'","$app->get('/proyecto/detalle'"]:
 a=s.index(route);b=s.index('});',a)+3;part=s[a:b];needle='COALESCE(SUM(Ve_PreOrdenDetPago.Monto),0) as MontoPagado,';assert needle in part;part=part.replace(needle,expr+'\n        '+needle,1);s=s[:a]+part+s[b:]
p=Path('integration/project-invoice/api/index.php');p.parent.mkdir(parents=True,exist_ok=True);p.write_text(s,encoding='utf-8')
p=Path('src/components/PreOrdenFormDet.vue');s=p.read_text(encoding='utf-8');s=s.replace("'bg-facturado':prod.Pagado","'bg-facturado':prod.Facturado==1").replace('prod.Pagado==1','prod.Facturado==1').replace('prod.Pagado ==1','prod.Facturado ==1').replace("? 'Facturado' : ''","? 'Facturado' : 'Sin comprobante'")
p.write_text(s,encoding='utf-8')
