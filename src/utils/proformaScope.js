const sentenceParts=text=>String(text||'').replace(/([.!?])\s+(?=[A-ZÁÉÍÓÚÑ¿¡])/g,'$1\n').split('\n').map(s=>s.trim()).filter(Boolean);
const moduleBoundary=/,\s+(?:y\s+)?(?=(?:Punto de venta|Inventario(?: y stock)?|Cat[aá]logo de productos(?: y servicios)?|Compras|Caja y bancos|Clientes|Reportes de ventas|Usuarios y accesos)\b)/i;
export function proformaTextBlocks(value,kind='scope') {
 const blocks=[];
 const add=(label,text,type='text',items=[])=>blocks.push({label,text,type,items});
 const paragraphs=String(value||'').replace(/\r\n/g,'\n').split(/\n\s*\n/).map(p=>p.trim()).filter(Boolean);
 for(const paragraph of paragraphs){
  const lines=paragraph.split('\n').map(s=>s.trim()).filter(Boolean);
  const heading=lines[0];
  if(lines.length>1&&/^(SERVICIOS INCLUIDOS|ACOMPAÑAMIENTO E IMPLEMENTACIÓN|SOPORTE Y ATENCIÓN)$/i.test(heading)){
   add(heading,'','services',lines.slice(1).map(line=>line.replace(/^[•\-*]\s*/,'')));continue;
  }
  if(kind!=='scope'){
   const items=lines.some(line=>/^[•\-*]|^\d+[.)]\s/.test(line))?lines.map(line=>line.replace(/^(?:[•\-*]|\d+[.)])\s*/,'')):sentenceParts(paragraph);
   add(kind==='delivery'?'PLAN DE ENTREGA':'CONDICIONES COMERCIALES','','list',items);continue;
  }
  for(const part of sentenceParts(paragraph)){
   if(/^Propuesta (?:para|de sistemas (?:de|para))\s+/i.test(part)){
    add('PROPUESTA',part.replace(/^Propuesta (?:para|de sistemas (?:de|para))\s+/i,'PROPUESTA DE SISTEMAS DE '),'intro');
   }else if(/^(?:El objetivo(?:\s+es)?|Objetivo\s*:)/i.test(part))add('OBJETIVO DEL PROYECTO',part,'objective');
   else if(/^Soluci[oó]n\s*:/i.test(part))add('SOLUCIÓN PROPUESTA',part,'solution');
   else if(/^Necesidades del cliente\s*:/i.test(part))add('NECESIDADES DEL CLIENTE',part,'needs');
   else if(/^Para ello\b/i.test(part)){
    const prefix=/^(Para ello se integran los m[oó]dulos de)\s+/i.exec(part);
    const text=prefix?part.slice(prefix[0].length):part;
    const items=text.split(moduleBoundary);
    add('OPERACIÓN DEL NEGOCIO',items.length>1?(prefix?prefix[1]:''):part,items.length>1?'operations':'text',items.length>1?items:[]);
   }else if(/^Adem[aá]s\b/i.test(part))add('COBERTURA DE LA SOLUCIÓN',part,'coverage');
   else add('DETALLE DEL ALCANCE',part);
  }
 }
 return blocks;
}
