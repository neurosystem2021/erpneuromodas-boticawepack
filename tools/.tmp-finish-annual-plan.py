from pathlib import Path
paths=['src/components/ProformaPresentacion.vue','integration/proforma-edit/proforma-pdf-render.php','integration/ali-proposal-assistant.php','tools/test-proposal-services.cjs','tools/test-proforma-modules.cjs','tools/test-proforma-modules.php','tools/.tmp-proforma-modules-ui.cjs']
for name in paths:
 p=Path(name);s=p.read_text(encoding='utf-8')
 if name.endswith('ProformaPresentacion.vue'):
  s=s.replace("'Propuesta para '+this.rubro", "'Propuesta de sistemas de '+this.rubro")
 if name.endswith('proforma-pdf-render.php'):
  s=s.replace("mb_strtoupper(trim($line),'UTF-8')", "mb_strtoupper(preg_replace('/^Propuesta para\\s+/iu','PROPUESTA DE SISTEMAS DE ',trim($line)),'UTF-8')")
 if name.endswith('ali-proposal-assistant.php'):
  s=s.replace('Redacta un objetivo ejecutivo', 'Inicia el alcance con Propuesta de sistemas de seguido del rubro o descripcion de la solucion. Redacta un objetivo ejecutivo')
 if name.endswith('test-proposal-services.cjs'):
  s=s.replace('Primer año de facturación electrónica incluido','Plan anual integral de facturación electrónica').replace('Valor anual de facturación electrónica (S/)','Valor anual del plan integral (S/)')
  s=s.replace('assert.match(value.alcance,/RUC/);assert.match(value.entregables,/VPS/);','assert.match(value.condiciones,/RUC/);assert.match(value.condiciones,/VPS/);')
  s=s.replace("await p.getByRole('button',{name:'Seleccionar recomendados',exact:true}).click();", "await p.getByRole('button',{name:'Seleccionar plan anual integral',exact:true}).click();assert.equal(await p.locator('.proposal-service-list input:checked').count(),16);")
  s=s.replace("const compose=new Function('catalog',helper+';return applyProposalServices;')(catalog);", "const utils=new Function('catalog',helper+';return {applyProposalServices,proformaAnnualPlan};')(catalog);const compose=utils.applyProposalServices;\nconst plan=compose({condiciones:'Texto personalizado'},catalog.filter(s=>s.annualPlan).map(s=>s.id).concat('annual_billing'),{annualFee:900});const parsed=utils.proformaAnnualPlan(plan.condiciones);assert.equal(parsed.text,'Texto personalizado');assert.equal(parsed.plan.services.length,15);assert.equal(parsed.plan.price,'S/ 900.00');assert.equal(compose(plan,catalog.filter(s=>s.annualPlan).map(s=>s.id).concat('annual_billing'),{annualFee:900}).condiciones,plan.condiciones);fs.writeFileSync('output/proposal-services-fixture.json',JSON.stringify({...plan,alcance:'Propuesta de sistemas de Market / bodega.\\n\\nObjetivo: mejorar el control de ventas e inventario.\\n\\nMódulos incluidos: Punto de venta; Inventario y stock.',entregables:'Configuración inicial y capacitación acordadas.'}));")
 if 'test-proforma-modules' in name or name.endswith('.tmp-proforma-modules-ui.cjs'):
  s=s.replace('PROPUESTA DE SISTEMAS PARA MARKETS','PROPUESTA DE SISTEMAS DE MARKET / BODEGA').replace('PROPUESTA DE SISTEMAS PARA FERRETERÍA','PROPUESTA DE SISTEMAS DE FERRETERÍA')
 p.write_text(s,encoding='utf-8')
