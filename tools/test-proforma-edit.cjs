const fs=require('fs'),assert=require('assert/strict');
const source=fs.readFileSync('src/views/Ventas.vue','utf8');
const body=source.slice(source.indexOf('    async guardarProforma() {')+'    async guardarProforma() {'.length,source.indexOf('    async guardarVenta(')).replace(/},\s*$/,'');
const save=new (Object.getPrototypeOf(async function(){}).constructor)(body);
async function run(id,fail=false){
 const calls=[];let closed=0;const sale={IdProforma:id,productos:[{IdProducto:1,cantidad:2,precio:50,descuento:0}],cliente:{IdCliente:1},vendedor:{Usuario:'admin'}};
 const vm={ventas:[sale],index:0,diasValidosProf:7,API:'/api/index.php/',axios:{get:async()=>({data:{csrf:'test-token'}}),post:async(url,data,config)=>{calls.push({url,data,config});if(fail)throw {response:{data:{error:'already billed'}}};return {data:{insertId:id||99}};}},$toasted:{show(){},success(){},error(){}},eliminarVentana(){closed++;}};
 global.window={open(){}};await save.call(vm);return {calls,closed,vm};
}
(async()=>{let r=await run(14);assert.equal(r.calls[0].url,'/api/index.php/proformas');assert.equal(r.calls[0].data.IdProforma,14);assert.equal(r.closed,1);r=await run(null);assert.equal(r.calls[0].url,'/api/index.php/proformas');r=await run(14,true);assert.equal(r.closed,0);assert.equal(r.vm.ventas[0].productos.length,1);assert.equal(r.vm.inProgress,false);console.log('PASS point-of-sale creates quotes without overwriting the source; draft retained after failure.');})().catch(e=>{console.error(e);process.exit(1)});
