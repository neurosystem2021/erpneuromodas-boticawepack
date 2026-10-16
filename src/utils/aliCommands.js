export function aliDigits(value) {
 const words={cero:'0',uno:'1',un:'1',dos:'2',tres:'3',cuatro:'4',cinco:'5',seis:'6',siete:'7',ocho:'8',nueve:'9'};
 const parts=String(value||'').trim().split(/\s+/).filter(Boolean);
 if(!parts.length)return '';
 const normalized=parts.map(x=>words[x]!==undefined?words[x]:x.replace(/[.,-]/g,''));
 if(normalized.every(x=>/^\d+$/.test(x)))return normalized.join('');
 const cardinal={...words,diez:10,once:11,doce:12,trece:13,catorce:14,quince:15,dieciseis:16,diecisiete:17,dieciocho:18,diecinueve:19,veinte:20,veintiuno:21,veintidos:22,veintitres:23,veinticuatro:24,veinticinco:25,veintiseis:26,veintisiete:27,veintiocho:28,veintinueve:29,treinta:30,cuarenta:40,cincuenta:50,sesenta:60,setenta:70,ochenta:80,noventa:90,cien:100,ciento:100,doscientos:200,trescientos:300,cuatrocientos:400,quinientos:500,seiscientos:600,setecientos:700,ochocientos:800,novecientos:900};
 let total=0,group=0;
 for(const word of parts){if(word==='y')continue;if(word==='mil'){total+=(group||1)*1000;group=0;}else if(cardinal[word]!==undefined)group+=Number(cardinal[word]);else return '';}
 return total+group<=999999?String(total+group):'';

}
export function parseAliCommand(raw, context = {}) {
 let text=String(raw||'').normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLowerCase().replace(/[.,!?¿¡:;\/]/g,' ').replace(/\s+/g,' ').trim();
 text=text.replace(/^(?:aly|alli|al i|a li)\b\s*/,'ali ').replace(/^ali(?=ventas?|clientes?|pago\b|actualizar\b)/,'ali ');
 if(/^(?:ali\s*pago)\s+(?:confirmar|confirmado|condirmado)$/.test(text))return {type:'confirm'};
 if(/^(?:ali\s*pago|ali\s+pagar|ali\s+paga)$/.test(text))return {type:'pay'};
 if (/^(?:muchas )?gracias (?:ali|ally)$/.test(text))return {type:'sleep'};
 if (/^(hola(?:\s+(?:ali|ally))?|buenos dias(?:\s+ali)?|buenas tardes(?:\s+ali)?|buenas noches(?:\s+ali)?|ali|ally)$/.test(text)) return {type:'greet'};
 text=text.replace(/^(?:hola\s+)?(?:ali|ally)\b\s*/, '').replace(/^por favor\s+|\s+por favor$/g,'').trim();
 if(/^(?:actualizar|actualiza|actualizar y limpiar todo|limpiar todo|limpia todo|actualizar y limpiar todo y (?:ponerse|ponerme) en (?:el )?buscador de codigo de barras?)$/.test(text))return {type:'cancel',reset:true};
 if(/^(?:ventas|venta)$/.test(text))return {type:'focus',field:'barcode',salesOnly:true};
 text=text.replace(/^tipo venta\b/,'tipo de venta');
 if(text==='tipo de venta')return {type:'focus',field:'documentType'};
 if(context.field==='documentType'&&/^(?:boleta|factura|nota)(?: (?:elec|elect|electronica|electronico|de venta))?$/.test(text))text='tipo de venta '+text;
 if(/^clientes?$/.test(text))return {type:'focus',field:'customer'};
 if(/^(?:buscar|busca)$/.test(text)&&['barcode','customer'].includes(context.field))return {type:'accept'};
 if(/^(?:silencio|deja de escuchar|dejar de escuchar|desactivate|apagate|apagar microfono|detener microfono|deten el microfono)$/.test(text))return {type:'stopAssistant'};
 if(/^(?:acepta|aceptar|acepte|listo|termine|terminado|agregalo|agregarlo|enter|enter por favor)$/.test(text))return {type:'accept'};
 if(/^(?:borra|borrar|limpia|limpiar|corrige|corregir)(?: el)?(?: codigo| codigo de barras?)?$/.test(text))return {type:'clearField'};
 if(/^(?:borra|borrar|quita)(?: el)? ultimo(?: digito| numero)?$/.test(text))return {type:'backspace'};
 const suffix=text.match(/^(?:selecciona(?:r)?|elige|elegir|agrega(?:r)?|anade)\s+.*?(?:termina(?:do)?(?: en)?|codigo(?: de barras?)?)\s+(?:el codigo\s+|codigo\s+|en\s+)?(.+)$/);
 if(suffix){const digits=aliDigits(suffix[1]);if(digits)return {type:'selectCode',suffix:digits};}
 const barcodeFocus=/^(?:(?:ve|ir|anda|vaya)(?: al)?\s+)?(?:(?:escribe|escribir|escriba|pon|poner|coloca|colocar|dictar)(?: en| el| al)?\s+)?(?:escanea o escriba |escanee o escriba )?(?:el )?codigo(?: de barras?)?$/.test(text);
 if(/^(?:buscar|busca)(?: el| un)? producto por codigo(?: de barras?)?$/.test(text))return {type:'focus',field:'barcode'};
 if(/^(?:buscar|busca|buscador) por codigo(?: de barras?)?$/.test(text))return {type:'focus',field:'barcode'};
 if(/^(?:buscar|busca|buscador) por nombre$|^(?:salir|sal|salir de|sal de)(?: el)? codigo(?: de barras?)?$|^(?:cambia|cambiar|vuelve|volver)(?: al)? buscador(?: de productos| por nombre)?$/.test(text))return {type:'focus',field:'search'};
 if(barcodeFocus)return {type:'focus',field:'barcode'};
 if(/^(?:(?:ve|ir|anda|vaya)(?: al)?\s+)?(?:agregar productos?|buscador(?: de productos)?|buscar productos?)(?: por nombre)?$/.test(text))return {type:'focus',field:'search'};
 const finish=/(?: y)? (?:aceptar|acepta|acepte|listo|termine|terminado)$/.test(text);if(finish)text=text.replace(/(?: y)? (?:aceptar|acepta|acepte|listo|termine|terminado)$/,'').trim();
 const document=text.match(/^(?:(?:pon|poner|coloca|colocar|escribe|escribir|cliente|selecciona|busca)(?: el| al cliente| cliente| documento)?\s+)?(dni|ruc)(?: del cliente| cliente)?(?: es| numero| número)?\s+(.+)$/);
 if(document)return context.field==='customer'?{type:'customerInput',number:aliDigits(document[2]),replace:true}:{type:'customer',kind:document[1],number:aliDigits(document[2])};
 const barcode=text.match(/^(?:(?:ve|ir|anda|vaya)(?: al)?\s+)?codigo de barras?(?: y)?(?: pon| coloca| escribe| escribir| numero| busca| buscar)?\s+(.+)$/)||text.match(/^(?:escribe|pon|coloca|busca)\s+(?:el )?codigo(?: de barras?)?\s+(.+)$/);
 if(barcode){const digits=aliDigits(barcode[1]);if(digits)return {type:'barcodeInput',number:digits,replace:true,accept:finish||/^(?:busca|buscar) /.test(text)};}
 if(/^(?:ve|ir|anda|vaya)(?: al)? (?:codigo de barras?|buscador|buscador de productos)$/.test(text))return {type:'focus',field:text.includes('codigo')?'barcode':'search'};
 const type=text.match(/^tipo de venta\s+(boleta|factura|nota)(?:\s+(?:elec|elect|electronica|electronico|de venta))?$/);
 if(type)return {type:'documentType',code:type[1]==='factura'?'01':type[1]==='boleta'?'03':'note',label:type[1]};
 if(/^(?:(?:dame|dime|quiero|una)\s+)?(?:(?:una|la)\s+)?frase(?: motivadora)?(?: de hoy| del dia)?$|^motivame$/.test(text))return {type:'encouragement'};
 if (/^(ayuda|que puedes hacer|como te uso|que comandos tienes)$/.test(text))return {type:'help'};
 if (/^(cancelar|cancela|detener|para)$/.test(text)) return {type:'cancel'};
 if (/^(confirmar|confirmado|condirmado|confirmar pago|confirmo el pago|confirmar cobro|confirmo pago|confirmar alipago|confirmo alipago)$/.test(text)) return {type:'confirm'};
 if (/^(pagar|paga|cobra|cobrar|preparar pago|prepara el pago|quiero pagar|quiero cobrar)$/.test(text)) return {type:'pay'};
 if (/^(total|cuanto es|cuanto falta|dime el total|cual es el total|cuanto tengo que pagar)$/.test(text)) return {type:'total'};
 if (/^(?:muestra(?:me)?\s+|abre\s+|abrir\s+|ver\s+)?(?:los\s+)?(?:metodos?|formas?) de pago$/.test(text)) return {type:'methods'};
 const paymentText=text.replace(/\bya pe\b/g,'yape').replace(/\byapeplin\b/g,'yape plin').replace(/\byap\b/g,'yape').replace(/\bplim\b/g,'plin').replace(/yape (?:y|o) plin/g,'yape plin').replace(/metodo pago/g,'metodo de pago').replace(/tarjeta(?: de)? (?:credito|debito)/g,'tarjeta').replace(/transferencia bancaria/g,'transferencia');
 const pay=paymentText.match(/^(?:(?:(?:quiero|necesito)\s+)?(?:pago|pagar|paga|pon|ponme|poner|coloca|colocar|selecciona|seleccionar|cambia|cambiar|metodo de pago|forma de pago|metodo)(?: el)?(?: metodo de pago| forma de pago| pago| metodo)?(?: a| en| con| por)?\s+)?(efectivo|yape(?: plin)?|plin|tarjeta|transferencia)$/);
 if(pay)return {type:'method',key:({efectivo:'efectivo',yape:'yape','yape plin':'yape',plin:'yape',tarjeta:'visa',transferencia:'mastercard'})[pay[1]],label:({efectivo:'Efectivo',yape:'Yape/Plin','yape plin':'Yape/Plin',plin:'Yape/Plin',tarjeta:'Tarjeta',transferencia:'Transferencia'})[pay[1]]};
 const select=text.match(/^(?:agrega|agregar|anade|selecciona)(?: el| la)?(?: producto| resultado| opcion)?\s+(uno|dos|tres|cuatro|cinco|primero|primera|segundo|segunda|tercero|tercera|cuarto|cuarta|quinto|quinta|\d+)$/);if(select)return {type:'add',index:({uno:1,dos:2,tres:3,cuatro:4,cinco:5,primero:1,primera:1,segundo:2,segunda:2,tercero:3,tercera:3,cuarto:4,cuarta:4,quinto:5,quinta:5})[select[1]]||Number(select[1])};
 const learn=text.match(/^(?:explica(?:me)?|informacion de|aprender sobre|videos de|mas de|hablame de)\s+(.+)$/);if(learn)return {type:'learn',query:learn[1].replace(/^(?:el|la|un|una)\s+/,'')};
 text=text.replace(/^(?:ve|anda|vaya) al buscador(?: de productos)? y /,'').replace(/^en el buscador /,'');
 const search=text.match(/^(?:(?:quiero|necesito)\s+(?:buscar|encontrar)|busca|buscar|buscame|encuentra|muestrame|muestra|mostrar|agrega|agregar)\s+(.+)$/);if(search)return {type:'search',query:search[1].replace(/^(?:el producto|un producto|productos?|el|la|los|las|un|una|unos|unas)\s+/,'')};
 const digits=aliDigits(text.replace(/^(?:escribe|escribir|pon|coloca|numero)\s+/,''));
 if(context.field==='customer'&&digits)return {type:'customerInput',number:digits,replace:false};
 if(context.field==='barcode'&&digits)return {type:'barcodeInput',number:digits,replace:false,accept:finish};
 if(context.field==='search'&&text&&!/^(?:ya|ya ya|ok|bien|bueno|si|no)$/.test(text))return {type:'search',query:text};
 return {type:'unknown'};
}
export function parseAliWake(raw, previous = '', age = Infinity) {
 const normalize=value=>String(value||'').normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLowerCase().replace(/[.,!?¿¡:;]/g,' ').replace(/\s+/g,' ').trim();
 const text=normalize(raw);
 const direct=text.match(/^(?:ali|ally|aly|alli|al i|a li)\s*(ventas?|clientes?|pago|actualizar|actualiza|limpiar todo|limpia todo)\b(.*)$/);if(direct)return {wake:true,command:'ali '+direct[1]+direct[2]};
 if(/^tipo(?: de)? venta(?:\s+(?:boleta|factura|nota)(?:\s+(?:elec|elect|electronica|electronico|de venta))?)?$/.test(text))return {wake:true,command:text};
 const candidates=[text];
 if(/^(hola|ola)$/.test(normalize(previous))&&age<2000)candidates.push(normalize(previous)+' '+text);
 for(const value of candidates){const match=value.match(/^(?:hola|ola)\s*(?:ali|aly|ally|alli|al i)\b\s*(.*)$/);if(match)return {wake:true,command:/^(?:hola|ola)\s*(?:ali|aly|ally|alli|al i)$/.test(match[1])?'':match[1]};}
 return {wake:false,command:''};
}

export function aliNormalizeProductQuery(raw) {
 const numbers={uno:1,una:1,dos:2,tres:3,cuatro:4,cinco:5,seis:6,siete:7,ocho:8,nueve:9,diez:10};
 return String(raw||'').normalize('NFD').replace(/[\u0300-\u036f]/g,'').replace(/['"\\%_]/g,' ')
  .replace(/\bcamaras\b/gi,'camara').replace(/\bhik vision\b/gi,'hikvision').replace(/\bdaua\b/gi,'dahua')
  .replace(/\b(?:de\s+)?(uno|una|dos|tres|cuatro|cinco|seis|siete|ocho|nueve|diez|\d+)\s*(?:mega ?pixeles|megapixel(?:es)?|mp)\b/gi,(_,n)=>(numbers[n.toLowerCase()]||n)+'MP')
  .replace(/\s+/g,' ').trim().slice(0,160);
}
