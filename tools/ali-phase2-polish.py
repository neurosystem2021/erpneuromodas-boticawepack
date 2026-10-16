from pathlib import Path
p=Path('src/views/Ventas.vue');s=p.read_text(encoding='utf-8')
a=s.index("      if(action.type==='pay'||action.type==='confirm'){");b=s.index("      throw new Error('Comando no disponible.');",a)
s=s[:a]+"      if(action.type==='pay'||action.type==='confirm')throw new Error('El cobro por ALI requiere la fase de confirmación e idempotencia. Usa el botón Pagar del POS.');\n"+s[b:]
s=s.replace('const results=rows.slice(0,5);','const results=rows.slice(0,25);',1)
p.write_text(s,encoding='utf-8')
p=Path('src/components/ProductosCarritoNuevo.vue');s=p.read_text(encoding='utf-8')
s=s.replace('    renderChild(data) {', '''    renderChild(data) {
      const original = data;
      const list = this.$refs.autocompleteProducto && this.$refs.autocompleteProducto.json || [];
      const resultIndex = list.indexOf(original);
      const ordinal = resultIndex >= 0 ? (resultIndex + 1) + '. ' : '';
      const escape = value => String(value == null ? '' : value).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
      data = Object.assign({}, data);
      ['Producto','CodigoBarra','ProductoCategoria','ProductoModelo','ProductoMarca','PrecioContado'].forEach(key => { data[key] = escape(data[key]); });''',1)
s=s.replace('${data.Producto || "Producto sin nombre"} - ${data.CodigoBarra', '${ordinal}${data.Producto || "Producto sin nombre"} - ${data.CodigoBarra',1)
p.write_text(s,encoding='utf-8')
p=Path('integration/ali-api.php');s=p.read_text(encoding='utf-8')
a=s.index("if($q->getUri()->getPath()&&");b=s.index('session_write_close();try{$capabilities',a)
s=s[:a]+'''$path=$q->getUri()->getPath();$bucket=strpos($path,'/ali/autorizar')!==false?'actions':(strpos($path,'/ali/voz')!==false?'voice':(strpos($path,'/ali/producto')!==false?'learning':null));if($bucket){$times=array_values(array_filter($_SESSION['ali_limits'][$bucket]??[],fn($t)=>$t>time()-60));$limit=['actions'=>120,'voice'=>30,'learning'=>10][$bucket];if(count($times)>=$limit){session_write_close();return $r->withJson(['error'=>'Espere un minuto antes de consultar otra vez.'],429);}$times[]=time();$_SESSION['ali_limits'][$bucket]=$times;}'''+s[b:]
p.write_text(s,encoding='utf-8')
p=Path('src/components/AliAssistant.vue');s=p.read_text(encoding='utf-8')
s=s.replace("this.history.unshift({command:clean(command)", "this.history.unshift({command:parseAliCommand(command).type==='customer'?'Seleccionar cliente (documento oculto)':clean(command)",1)
p.write_text(s,encoding='utf-8')
