const fs=require('fs'),c=require('vue-template-compiler');
for(const f of ['src/components/PedidosWeb.vue','src/views/Ventas.vue']){const parsed=c.parseComponent(fs.readFileSync(f,'utf8'));const errors=c.compile(parsed.template.content).errors;if(errors.length)throw Error(errors.join(';'));}
const source=c.parseComponent(fs.readFileSync('src/components/PedidosWeb.vue','utf8')).script.content;
const start=source.indexOf('async load(){')+'async load(){'.length,end=source.indexOf('\n  async cargar',start);
const body=source.slice(start,end).trim().replace(/},$/,'');
const run=new (Object.getPrototypeOf(async function(){}).constructor)(body);
let count=2,notices=0,actions=0;
const ctx={loading:false,previous:null,query:'',status:'pendientes',show:false,axios:{get:async()=>({data:{pendientes:count,pedidos:[]}})},$emit:()=>{},$toasted:{show:(text,options)=>{notices++;if(options.action.text==='VER PEDIDOS')actions++;}}};
(async()=>{await run.call(ctx);await run.call(ctx);count=3;await run.call(ctx);if(notices!==2||actions!==2||ctx.pending!==3)throw Error('Alert behavior failed');console.log('Alerts verified: existing pending, new order, no repeated alert, open-orders action');})().then(()=>process.exit(0)).catch(e=>{console.error(e);process.exit(1)});
