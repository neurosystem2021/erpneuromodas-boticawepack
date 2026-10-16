const fs=require('fs'),c=require('vue-template-compiler');
for(const file of ['src/views/Ventas.vue','src/components/PedidosWeb.vue']){const r=c.compile(c.parseComponent(fs.readFileSync(file,'utf8')).template.content);if(r.errors.length)throw Error(r.errors.join(';'));console.log(file+': OK');}
const s=fs.readFileSync('src/views/Ventas.vue','utf8');if(s.indexOf('class="btnAction pos-btn-web-orders"')>s.indexOf('<proformas :idAlmacen='))throw Error('Button order');console.log('Button above Ver Proformas verified');process.exit(0);
