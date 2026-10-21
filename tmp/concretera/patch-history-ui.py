from pathlib import Path
p=Path('server/concretera/resources/js/concretera-operaciones.js')
s=p.read_text(encoding='utf-8')
old="+btn('cxOpRecipeSource','Ver la guía que enviaste')+"
assert s.count(old)==1
s=s.replace(old,"+btn('cxOpRecipeSource','Ver la guía que enviaste')+btn('cxOpMayRecipes','Ver dosificaciones de mayo')+")
old="    $('cxOpRecipeSource').onclick=call(async()=>"
assert s.count(old)==1
# Open a separate tab to preserve all unsaved scheduling inputs.
s=s.replace(old,"    $('cxOpMayRecipes').onclick=()=>window.open('/views/Concretera.php?tab=operacion&recetas=mayo','_blank','noopener');\n"+old)
s=s.replace('El área técnica registra y aprueba; no se infieren dosificaciones del Excel.','El área técnica completa y aprueba las recetas. El informe de mayo aporta antecedentes de cemento y fórmulas de aditivos pendientes de revisión.')
old="+(can('TECNICA')?btn('cxOpDesign','Registrar versión técnica'):'')+table(['Diseño'"
assert s.count(old)==1
s=s.replace(old,"+btn('cxOpHistory','Revisar dosificaciones de mayo')+(can('TECNICA')?btn('cxOpDesign','Registrar versión técnica'):'')+table(['Diseño'")
old="    if($('cxOpDesign'))$('cxOpDesign').onclick=call(()=>designForm());"
assert s.count(old)==1
s=s.replace(old,"    $('cxOpHistory').onclick=call(()=>recipeHistory());\n    if(new URLSearchParams(location.search).get('recetas')==='mayo'&&!local.historyOpened){local.historyOpened=true;await recipeHistory();}\n"+old)
start=s.index('  async function designForm()')
end=s.index('  async function viewDesign(',start)
s=s[:start]+'''  async function recipeHistory(volume='1'){
    const scope=state.company.id,r=await api('recipe-history',{volumen:volume});if(scope!==state.company.id)return;
    const rows=r.variantes;
    dialog('Recetas · antecedentes de mayo',msg('Estos antecedentes permiten preparar una receta. Falta completar y aprobar el diseño técnico antes de producir.','info')+
      '<div class="cx-op-metrics"><div><small>Hojas diarias</small><strong>'+r.hojas.length+'</strong></div><div><small>Cargas históricas</small><strong>'+r.filasHistoricas+'</strong></div><div><small>Variantes de cemento</small><strong>'+rows.length+'</strong></div></div>'+
      '<div class="cx-op-toolbar">'+field('historyVolume','Volumen de consulta (m³)',r.volumenConsulta,'number',true,'min="0.01" max="100000" step="0.01"')+btn('cxHistoryCalculate','Calcular para estos m³')+'</div>'+msg('El pedido de f’c 140 no tiene una dosificación en este archivo. No se usará una resistencia distinta para completarlo.')+
      table(['Resistencia f’c','Cemento kg / m³','Cargas','Cemento para '+number(r.volumenConsulta)+' m³','Fórmulas históricas de aditivos',''],rows.map(v=>'<tr><td>'+fmt(v.resistencia)+' kg/cm²</td><td><strong>'+fmt(v.cementoKgPorM3)+'</strong></td><td>'+v.registros+'</td><td><strong>'+fmt(v.consulta.cementoKg)+' kg</strong></td><td>'+(v.porcentajeJ===null?'Revisar fórmula':'J: '+fmt(v.porcentajeJ)+' % del cemento<br>K: '+fmt(v.porcentajeK)+' % del cemento')+'<small>Producto y unidad por confirmar</small></td><td><button type="button" class="cx-btn" data-history="'+esc(v.id)+'">Ver origen y preparar</button></td></tr>'))+
      '<h3>Completar antes de aprobar</h3><ul>'+r.incidencias.map(i=>'<li>'+esc(i)+'</li>').join('')+'</ul><details><summary>Archivo y mapeo de importación</summary><p>'+esc(r.archivo)+'</p><p>SHA256: '+esc(r.sha256)+'</p>'+table(['Columna','Significado'],Object.entries(r.mapeo).map(([c,t])=>'<tr><td>'+esc(c)+'</td><td>'+esc(t)+'</td></tr>'))+'</details>');
    $('cxHistoryCalculate').onclick=call(async()=>{const input=$('cxDialogBody').querySelector('[name=historyVolume]');if(!input.reportValidity())return;await recipeHistory(input.value);});
    document.querySelectorAll('[data-history]').forEach(b=>b.onclick=()=>historyDetail(r,rows.find(v=>v.id===b.dataset.history)));
  }
  function historyDetail(source,v){
    const matching=state.products.filter(p=>p.oferta?.clase==='CONCRETO'&&Number(p.oferta.resistencia)===Number(v.resistencia));
    const references=v.origenes.map(o=>o.hoja+'!'+o.rango);
    const provenance=source.archivo+'; SHA256 '+source.sha256+'; variante '+v.id+'; '+references.slice(0,3).join('; ')+'; H=F*G; I=F*G (calculado); J=H*1/100; K=H*0.05/100.';
    dialog('Antecedente f’c '+number(v.resistencia)+' · cemento '+number(v.cementoKgPorM3)+' kg/m³',
      msg('Variante histórica incompleta. Seleccionar esta variante no la aprueba ni modifica el pedido.','info')+
      '<p><strong>Cemento: '+fmt(v.cementoKgPorM3)+' kg por m³.</strong> Para '+fmt(source.volumenConsulta)+' m³: '+fmt(v.consulta.cementoKg)+' kg.</p><p>J = '+fmt(v.porcentajeJ)+' % y K = '+fmt(v.porcentajeK)+' % del cemento. No se asignan kg ni litros a esos aditivos hasta confirmar su producto y unidad.</p>'+
      '<p>Faltan arena, piedra, agua, bases, edad, slump y aprobación. «Cemento utilizado» está calculado con la misma fórmula que el nominal y no constituye pesaje real.</p>'+
      '<div class="cx-actions">'+btn('cxHistoryBack','Volver a variantes')+(can('TECNICA')&&matching.length===1&&v.porcentajeJ!==null?btn('cxHistoryPrepare','Preparar versión técnica',true):'')+'</div>'+
      (!matching.length?msg('No hay un producto comercial de esta resistencia. El histórico se conserva sin crear productos nuevos.'):matching.length>1?msg('Hay varios productos de esta resistencia. Registre la versión desde el formulario técnico y seleccione el producto correspondiente.'):'')+
      table(['Hoja y celdas','Volumen histórico m³','Servicio / observación','Fórmulas de la carga'],v.origenes.map(o=>'<tr><td>'+esc(o.hoja+'!'+o.rango)+'</td><td>'+fmt(o.volumenM3)+'</td><td>'+esc(o.servicio)+(o.observacionTecnica?'<small>'+esc(o.observacionTecnica)+'</small>':'')+'</td><td>'+Object.entries(o.formulas).map(([c,f])=>esc(c+o.fila+': '+f)).join('<br>')+'</td></tr>'))+'<p class="cx-help">Origen: '+esc(source.archivo)+' · SHA256 '+esc(source.sha256)+'</p>');
    $('cxHistoryBack').onclick=call(()=>recipeHistory(source.volumenConsulta));
    if($('cxHistoryPrepare'))$('cxHistoryPrepare').onclick=call(()=>designForm({variant:v,product:matching[0].id,provenance}));
  }
  async function designForm(seed=null){
    const ms=await api('materials'),k=key();
    if(!ms.length)throw Error('Primero registre cemento, agregados, agua y aditivos en Concretera → Insumos. El antecedente de mayo ya está disponible y no se perderá.');
    const products=state.products.filter(p=>p.oferta&&p.oferta.clase==='CONCRETO');
    const v=seed?.variant,issues=seed?'ANTECEDENTE DE MAYO INCOMPLETO. Completar arena, piedra y agua; confirmar base del cemento, productos/unidades de J y K, edad, slump, descarga y vigencia técnica. No es una receta aprobada.':'';
    let lines=[];
    form('Nueva versión técnica de receta',msg(seed?'Cemento recuperado del informe de mayo. Seleccione explícitamente su insumo y complete el diseño. Esta versión se guardará como borrador con incidencias.':'Solo personal técnico autorizado. Registre el diseño revisado por el responsable técnico.','info')+
      g(select('idProducto','Producto comercial',opts(products,seed?.product))+select('idAlmacen','Planta ERP',opts(local.context.plantas,local.context.plantas.length===1?local.context.plantas[0].id:null))+
      field('codigo','Código de diseño (mismo código para una nueva versión)',v?v.id:'','text',true)+field('nombre','Nombre del diseño',v?'Antecedente mayo f’c '+v.resistencia+' · C '+v.cementoKgPorM3:'','text',true)+
      field('resistencia','Resistencia f’c (kg/cm²)',v?v.resistencia:'','number',true,'min="0.01" step="0.01"')+field('edadDias','Edad de diseño (días)','','number',true,'min="1" step="1"')+
      field('slumpMin','Slump mínimo','','number',true,'min="0" step="0.01"')+field('slumpMax','Slump máximo','','number',true,'min="0" step="0.01"')+
      select('unidadSlump','Unidad slump','<option value="">Seleccione…</option><option value="pulg">Pulgadas</option><option value="mm">Milímetros</option>')+
      select('descarga','Descarga','<option value="">Seleccione…</option><option value="CAIDA_LIBRE">Caída libre</option><option value="BOMBEADO">Bombeado</option>')+
      area('incidencias','Ambigüedades o incidencias pendientes (impiden aprobar)',issues)+field('motivo','Origen / motivo de esta versión',seed?seed.provenance:'','text',true))+
      '<h3>Componentes nominales por 1 m³</h3><div id="cxOpComponents"></div>'+btn('cxOpAddComponent','Agregar componente'),
      async f=>{const components=lines.filter(l=>l.el.isConnected).map(l=>{const el=l.el,m=ms.find(x=>String(x.IdProducto)===el.querySelector('[name=material]').value);if(!m)throw Error('Seleccione el insumo de cada componente.');return {idProducto:m.IdProducto,cantidadNominal:el.querySelector('[name=qty]').value,unidad:m.Unidad,base:m.BaseStock};});
        const data=value(f);if(seed){data.motivo=seed.provenance+' Revisión: '+data.motivo;data.incidencias=issues+' '+data.incidencias;}
        const out=await api('design',{...data,unidadResistencia:'kg/cm2',componentes:components,idempotencia:k},true);await ctx();await viewDesign(out.id);},'Guardar versión en borrador');
    if(seed)$('cxForm').elements.motivo.value='Completar y revisar antecedente histórico.';
    function add(cement=false){const el=document.createElement('section');el.className='cx-line';
      const materials=cement?ms.filter(m=>m.Tipo==='CEMENTO'&&m.Unidad==='kg'):ms;
      el.innerHTML=(cement?'<p><strong>Cemento del antecedente: '+fmt(v.cementoKgPorM3)+' kg/m³.</strong> Seleccione el cemento del almacén y revise su base.</p>':'')+
        g(select('material','Insumo / unidad / base',opts(materials.map(m=>({id:m.IdProducto,nombre:m.Producto+' · '+m.Unidad+' · '+m.BaseStock}))))+
        field('qty','Cantidad nominal por m³',cement?v.cementoKgPorM3:'','number',true,'min="0.01" step="0.01"'))+'<button type="button" class="cx-btn">Quitar</button>';
      el.querySelector('button').onclick=()=>el.remove();$('cxOpComponents').appendChild(el);lines.push({el});}
    add(!!seed);$('cxOpAddComponent').onclick=()=>add();
  }
''' +s[end:]
p.write_text(s,encoding='utf-8')
print('Historical recipe review integrated; existing scheduling calculations unchanged.')
