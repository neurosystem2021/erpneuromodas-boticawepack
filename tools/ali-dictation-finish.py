from pathlib import Path
p=Path('src/utils/aliCommands.js');s=p.read_text(encoding='utf-8');a=s.index(' const parts=');b=s.index('\n}\nexport function parseAliCommand',a)
s=s[:a]+''' const parts=String(value||'').trim().split(/\\s+/).filter(Boolean);
 if(!parts.length)return '';
 const normalized=parts.map(x=>words[x]!==undefined?words[x]:x.replace(/[.,-]/g,''));
 if(normalized.every(x=>/^\\d+$/.test(x)))return normalized.join('');
 const cardinal={...words,diez:10,once:11,doce:12,trece:13,catorce:14,quince:15,dieciseis:16,diecisiete:17,dieciocho:18,diecinueve:19,veinte:20,veintiuno:21,veintidos:22,veintitres:23,veinticuatro:24,veinticinco:25,veintiseis:26,veintisiete:27,veintiocho:28,veintinueve:29,treinta:30,cuarenta:40,cincuenta:50,sesenta:60,setenta:70,ochenta:80,noventa:90,cien:100,ciento:100,doscientos:200,trescientos:300,cuatrocientos:400,quinientos:500,seiscientos:600,setecientos:700,ochocientos:800,novecientos:900};
 let total=0,group=0;
 for(const word of parts){if(word==='y')continue;if(word==='mil'){total+=(group||1)*1000;group=0;}else if(cardinal[word]!==undefined)group+=Number(cardinal[word]);else return '';}
 return total+group<=999999?String(total+group):'';
''' +s[b:]
a=s.index(" const document=text.match(");s=s[:a]+" const finish=/(?: y)? (?:aceptar|acepta|acepte|listo|termine|terminado)$/.test(text);if(finish)text=text.replace(/(?: y)? (?:aceptar|acepta|acepte|listo|termine|terminado)$/,'').trim();\n"+s[a:]
s=s.replace("number:digits,replace:true}","number:digits,replace:true,accept:finish}",1).replace("number:digits,replace:false}","number:digits,replace:false,accept:finish}",1)
s=s.replace(".replace(/yape y plin/g,'yape plin')", ".replace(/yape (?:y|o) plin/g,'yape plin').replace(/metodo pago/g,'metodo de pago')",1);p.write_text(s,encoding='utf-8')
p=Path('src/components/AliAssistant.vue');s=p.read_text(encoding='utf-8');s=s.replace("this.busy=true;try{\n if(action.type==='barcodeInput')", "this.busy=true;try{\n const acceptAfter=action.type==='barcodeInput'&&action.accept;\n if(action.type==='barcodeInput')",1);s=s.replace("const result=await this.execute(action);", "let result=await this.execute(action);if(acceptAfter)result=await this.execute({type:'barcode',number:action.number});",1)
p.write_text(s,encoding='utf-8')
p=Path('src/components/ProductosCarritoNuevo.vue');s=p.read_text(encoding='utf-8');a=s.index('    async procesarCodigoBarra(codigo)');b=s.index('    async obtenerStockProducto',a);part=s[a:b].replace('        return;','        return true;');part=part.replace('this.$toasted.show("No se encontró ningún producto");\n        return true;', 'this.$toasted.show("No se encontró ningún producto");\n        return false;');part=part.replace('      });\n    },','      });\n      return true;\n    },');s=s[:a]+part+s[b:];p.write_text(s,encoding='utf-8')
p=Path('src/views/Ventas.vue');s=p.read_text(encoding='utf-8');s=s.replace("await cart.procesarCodigoBarra(action.number);cart.loadCode='';", "const found=await cart.procesarCodigoBarra(action.number);if(found===false)throw new Error('No encontré ese código. Puedes corregirlo o decir borrar código.');cart.loadCode='';",1);p.write_text(s,encoding='utf-8')
