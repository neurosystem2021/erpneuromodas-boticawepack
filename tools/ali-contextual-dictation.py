from pathlib import Path
p=Path('src/utils/aliCommands.js');s=p.read_text(encoding='utf-8');s=s.replace('parseAliCommand(raw)', 'parseAliCommand(raw, context = {})',1);s=s.replace('/[.,!?¿¡:;]/g','/[.,!?¿¡:;\\/]/g',1);needle=" const document=text.match(";a=s.index(needle)
s=s[:a]+''' if(/^(?:acepta|aceptar|acepte|listo|termine|terminado|agregalo|agregarlo|enter|enter por favor)$/.test(text))return {type:'accept'};
 if(/^(?:borra|borrar|limpia|limpiar|corrige|corregir)(?: el)?(?: codigo| codigo de barras?)?$/.test(text))return {type:'clearField'};
 if(/^(?:borra|borrar|quita)(?: el)? ultimo(?: digito| numero)?$/.test(text))return {type:'backspace'};
 const suffix=text.match(/^(?:selecciona(?:r)?|elige|elegir|agrega(?:r)?|anade)\\s+.*?(?:termina(?:do)?(?: en)?|codigo(?: de barras?)?)\\s+(?:el codigo\\s+|codigo\\s+|en\\s+)?(.+)$/);
 if(suffix){const digits=aliDigits(suffix[1]);if(digits)return {type:'selectCode',suffix:digits};}
 const barcodeFocus=/^(?:(?:ve|ir|anda|vaya)(?: al)?\\s+)?(?:(?:escribe|escribir|escriba|pon|poner|coloca|colocar|dictar)(?: en| el| al)?\\s+)?(?:escanea o escriba |escanee o escriba )?(?:el )?codigo(?: de barras?)?$/.test(text);
 if(barcodeFocus)return {type:'focus',field:'barcode'};
 if(/^(?:(?:ve|ir|anda|vaya)(?: al)?\\s+)?(?:agregar productos?|buscador(?: de productos)?|buscar productos?)(?: por nombre)?$/.test(text))return {type:'focus',field:'search'};
''' +s[a:]
s=s.replace("if(barcode)return {type:'barcode',number:aliDigits(barcode[1])};", "if(barcode){const digits=aliDigits(barcode[1]);if(digits)return {type:'barcodeInput',number:digits,replace:true};}",1)
a=s.index(' const pay=text.match(');b=s.index(' const select=text.match(',a)
s=s[:a]+''' const paymentText=text.replace(/\\bya pe\\b/g,'yape').replace(/\\byapeplin\\b/g,'yape plin').replace(/\\byap\\b/g,'yape').replace(/\\bplim\\b/g,'plin').replace(/yape y plin/g,'yape plin').replace(/tarjeta(?: de)? (?:credito|debito)/g,'tarjeta').replace(/transferencia bancaria/g,'transferencia');
 const pay=paymentText.match(/^(?:(?:(?:quiero|necesito)\\s+)?(?:pago|pagar|paga|pon|ponme|poner|coloca|colocar|selecciona|seleccionar|cambia|cambiar|metodo de pago|forma de pago|metodo)(?: el)?(?: metodo de pago| forma de pago| pago| metodo)?(?: a| en| con| por)?\\s+)?(efectivo|yape(?: plin)?|plin|tarjeta|transferencia)$/);
 if(pay)return {type:'method',key:({efectivo:'efectivo',yape:'yape','yape plin':'yape',plin:'yape',tarjeta:'visa',transferencia:'mastercard'})[pay[1]],label:({efectivo:'Efectivo',yape:'Yape/Plin','yape plin':'Yape/Plin',plin:'Yape/Plin',tarjeta:'Tarjeta',transferencia:'Transferencia'})[pay[1]]};
''' +s[b:]
s=s.replace(" return {type:'unknown'};", " const digits=aliDigits(text.replace(/^(?:escribe|escribir|pon|coloca|numero)\\s+/,''));\n if(context.field==='barcode'&&digits)return {type:'barcodeInput',number:digits,replace:false};\n if(context.field==='search'&&text&&!/^(?:ya|ya ya|ok|bien|bueno|si|no)$/.test(text))return {type:'search',query:text};\n return {type:'unknown'};",1);p.write_text(s,encoding='utf-8')
p=Path('src/components/AliAssistant.vue');s=p.read_text(encoding='utf-8');s=s.replace("command:'',status:","command:'',activeField:'',barcodeBuffer:'',status:",1)
s=s.replace("mounted(){this.refreshVoices();", "mounted(){document.addEventListener('focusin',this.trackField);this.refreshVoices();",1);s=s.replace("beforeDestroy(){if(window.speechSynthesis", "beforeDestroy(){document.removeEventListener('focusin',this.trackField);if(window.speechSynthesis",1)
s=s.replace(' refreshVoices(){', " trackField(event){if(event.target.id==='txtBarcode'){this.activeField='barcode';this.barcodeBuffer=event.target.value||''}else if(event.target.id==='txtAddProduct'){this.activeField='search';this.barcodeBuffer=''}},\n refreshVoices(){",1)
a=s.index(' async run(){');b=s.index(' learn(product)',a)
s=s[:a]+''' async run(){if(this.busy)return;let action=parseAliCommand(this.command,{field:this.activeField});if(action.type==='sleep'){this.handleWakeText('Gracias ALI');return}if(action.type==='greet')this.awake=true;if(action.type==='unknown'){this.say(this.activeField==='barcode'?'Estoy en código de barras. Dicta los números y di Aceptar para agregar el producto.':'Dime en qué campo trabajar: Agregar producto o Escriba código de barras. También puedes decir método de pago Yape/Plin, Efectivo, Tarjeta o Transferencia.');return}this.busy=true;try{
 if(action.type==='barcodeInput'){const number=action.replace?action.number:this.barcodeBuffer+action.number;if(number.length>40)throw Error('El código es demasiado largo. Di borrar código para empezar otra vez.');action={type:'barcodeDraft',number};}
 if(action.type==='backspace'||action.type==='clearField'){if(this.activeField!=='barcode')throw Error('Selecciona código de barras antes de corregir.');action={type:'barcodeDraft',number:action.type==='backspace'?this.barcodeBuffer.slice(0,-1):''};}
 if(action.type==='accept'&&this.activeField==='barcode'){if(!this.barcodeBuffer)throw Error('Primero dicta el código.');action={type:'barcode',number:this.barcodeBuffer};}
 if(action.type==='add'&&this.results[action.index-1])action.product=this.results[action.index-1];
 const result=await this.execute(action);if(result.field!==undefined)this.activeField=result.field;if(result.buffer!==undefined)this.barcodeBuffer=result.buffer;if(result.results)this.results=result.results;if(result.pending!==undefined)this.pending=result.pending;if(result.product)this.learn(result.product);this.say(result.message||'Listo.');
 }catch(e){this.say(e.message)}finally{this.busy=false}},
''' +s[b:]
s=s.replace("this.awake=false;this.pending='';this.execute({type:'cancel'})", "this.awake=false;this.activeField='';this.barcodeBuffer='';this.pending='';this.execute({type:'cancel'})",1)
p.write_text(s,encoding='utf-8')
