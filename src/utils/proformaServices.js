import catalog from '../../static/data/proforma-services.json';
export const proposalServices=catalog;
export const defaultProposalServices=()=>catalog.filter(item=>item.default).map(item=>item.id);
export const selectedProposalServices=ids=>catalog.filter(item=>ids.includes(item.id));
export const annualPlanHeading='PLAN ANUAL INTEGRAL DE FACTURACIÓN ELECTRÓNICA';
export function proformaAnnualPlan(value) {
 const paragraphs=String(value||'').replace(/\r\n/g,'\n').split(/\n\s*\n/);
 const block=paragraphs.find(p=>p.trim().startsWith(annualPlanHeading+'\n'));
 if(!block)return {text:String(value||''),plan:null};
 const lines=block.trim().split('\n');
 const price=lines.find(line=>line.startsWith('Valor anual: '));
 if(!price)return {text:String(value||''),plan:null};
 const services=lines.filter(line=>line.startsWith('• ')).map(line=>{const parts=line.slice(2).split(' — ');return {name:parts.shift(),description:parts.join(' — ')};});
 return {text:paragraphs.filter(p=>p!==block).join('\n\n').trim(),plan:{title:annualPlanHeading,price:price.slice(13),terms:lines.slice(1).filter(line=>!line.startsWith('• ')&&!line.startsWith('Valor anual: ')),services}};
}
export function applyProposalServices(value,ids,options={}) {
 const next={...value};
 const sections={alcance:'SERVICIOS INCLUIDOS',entregables:'ACOMPAÑAMIENTO E IMPLEMENTACIÓN',condiciones:'SOPORTE Y ATENCIÓN'};
 const selected=selectedProposalServices(ids),annual=ids.includes('annual_billing'),amount=Number(options.annualFee);
 if(annual&&(!Number.isFinite(amount)||amount<=0))throw Error('Selecciona el valor anual de facturación electrónica.');
 for(const key of Object.keys(sections)){
  const heading=sections[key];
  const paragraphs=String(next[key]||'').replace(/\r\n/g,'\n').split(/\n\s*\n/).filter(paragraph=>!paragraph.trim().startsWith(heading+'\n')&&!paragraph.trim().startsWith(annualPlanHeading+'\n'));
  const items=selected.filter(item=>item.id!=='annual_billing'&&item.target===key&&(!annual||!item.annualPlan));
  if(items.length)paragraphs.push(heading+'\n'+items.map(item=>'• '+item.text).join('\n'));
  if(annual&&key==='condiciones')paragraphs.push(annualPlanHeading+'\nValor anual: S/ '+amount.toFixed(2)+'\nEl precio de la licencia incluye el primer año del plan integral. Esta anualidad no se cobra adicionalmente al adquirir la licencia.\nA partir del segundo año, la renovación del plan integral es anual por S/ '+amount.toFixed(2)+', según las condiciones acordadas.\n'+selected.filter(item=>item.annualPlan).map(item=>'• '+item.name+' — '+item.text).join('\n'));
  next[key]=paragraphs.map(text=>text.trim()).filter(Boolean).join('\n\n');
 }
 return next;
}
