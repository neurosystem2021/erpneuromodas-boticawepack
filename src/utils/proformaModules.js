import guide from '../../static/data/proforma-module-guide.json';

const normalize=value=>String(value||'').normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLowerCase().replace(/\s+/g,' ').trim().replace(/[.]+$/,'');
export function proformaSystemTitle(scope,products=[]) {
  const text=String(scope||'');
  const match=/^Propuesta (?:para|de sistemas de)\s+([^\r\n]+)/im.exec(text);
  const business=match?match[1].trim().replace(/[.]+$/,''):'';
  const context=business||products.map(product=>product.Producto||'').join(' ');
  const description=context.replace(/^sistemas?\s+(?:a medida\s+)?(?:de|para)\s+/i,'').trim();
  return 'PROPUESTA DE SISTEMAS DE '+(description||'GESTIÓN EMPRESARIAL').toLocaleUpperCase('es-PE');
}
export function proformaModulePresentation(scope) {
  const text=String(scope||'');
  const marker=/M[oó]dulos incluidos:\s*/i.exec(text);
  if(!marker)return {text,modules:[]};
  const tail=text.slice(marker.index+marker[0].length);
  const end=tail.search(/\r?\n\s*\r?\n/);
  const list=end<0?tail:tail.slice(0,end);
  const remaining=end<0?'':tail.slice(end);
  const modules=[],seen=new Set();
  list.split(/[;\n]+/).map(name=>name.trim().replace(/[.]+$/,'')).filter(Boolean).forEach(name=>{
    const entry=guide.modules.find(module=>module.aliases.some(alias=>normalize(alias)===normalize(name)));
    const key=entry?entry.key:normalize(name);
    if(seen.has(key))return;seen.add(key);
    modules.push(entry?{...entry,paths:guide.icons[entry.icon]}:{key,name,description:'Función incluida en esta propuesta, con configuración según el alcance acordado.',benefit:'Su detalle se valida durante la definición de la solución.',paths:guide.icons.settings});
  });
  return {text:(text.slice(0,marker.index)+remaining).trim(),modules};
}
