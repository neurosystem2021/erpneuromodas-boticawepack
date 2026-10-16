from pathlib import Path
p=Path(r'C:\xampp\htdocs\boticav1\api\index.php');s=p.read_text(encoding='utf-8');needle='(Ve_PreOrdenTotal.granTotal - Cb_CajaBancoAdelanto.sumAdelanto) AS granTotalCalculado,';a=s.index(needle);b=s.index("$app->post('/habitaciones/alquilar'",a);part=s[a:b].replace(needle,'''(IFNULL(Ve_PreOrdenTotal.granTotal,0) - IFNULL(Cb_CajaBancoAdelanto.sumAdelanto,0)) AS granTotalCalculado,
            IFNULL(Ve_PreOrdenTotal.granTotal,0) AS TotalServicio,
            IFNULL(Cb_CajaBancoAdelanto.sumAdelanto,0) AS TotalAdelantos,
            GREATEST(IFNULL(Ve_PreOrdenTotal.granTotal,0)-IFNULL(Cb_CajaBancoAdelanto.sumAdelanto,0),0) AS SaldoPendiente,''',1).replace('Cb_TipoCajaBanco.Tipo = 0','Cb_TipoCajaBanco.Tipo = 0 AND IFNULL(Cb_CajaBanco.Anulado,0)=0');s=s[:a]+part+s[b:];p=Path('integration/project-card-totals/api/index.php');p.parent.mkdir(parents=True,exist_ok=True);p.write_text(s,encoding='utf-8')
p=Path('src/views/Habitaciones.vue');s=p.read_text(encoding='utf-8');a=s.index('<div class="project-amount">');b=s.index('</div>',a)+6;s=s[:a]+'''<div class="project-amount"><span>Importe del proyecto</span><strong>S/ {{ hab.TotalServicio | moneda }}</strong><div class="project-payment-line"><span>Adelanto</span><b>S/ {{ hab.TotalAdelantos | moneda }}</b></div><div class="project-payment-line project-payment-balance"><span>Saldo pendiente</span><b>S/ {{ hab.SaldoPendiente | moneda }}</b></div></div>'''+s[b:]
s=s.replace('      this.selectProducto = hab;','      this.selectProducto = hab;\n      this.IdProforma = hab.IdProforma || null;',1)
s=s.replace('            this.cargarAdelantos();','            this.cargarAdelantos();\n            this.getHabitaciones();',1)
p.write_text(s,encoding='utf-8')
