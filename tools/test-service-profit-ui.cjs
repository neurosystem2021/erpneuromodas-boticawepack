const fs=require('fs'),assert=require('assert'),compiler=require('vue-template-compiler');
const sfc=compiler.parseComponent(fs.readFileSync('src/views/reportes/ReporteUtilidadBrutaPorUsuario.vue','utf8'));
assert.deepEqual(compiler.compile(sfc.template.content).errors,[]);
const options=new Function('config',sfc.script.content.replace(/^import .*;\s*$/gm,'').replace('export default','return'))({API:'/api/'});
const vm={$moment:()=>({startOf(){return this},format:()=> '2026-10-16'}),$toasted:{error(){}}};Object.assign(vm,options.methods);Object.assign(vm,options.data.call(vm));
for(const [key,get] of Object.entries(options.computed))Object.defineProperty(vm,key,{get:()=>get.call(vm)});
vm.axios={get:async(url,request)=>{assert.equal(request.params.usuarioReg,'');assert.equal(request.params.ServicioFlag,'1');assert.equal(request.params.MercaderiaFlag,'0');return {data:[{UsuarioReg:'ana',TotalVenta:190,TotalCosto:40,UtilidadBruta:150},{UsuarioReg:'luis',TotalVenta:130,TotalCosto:0,UtilidadBruta:130}]};}};
(async()=>{await vm.generar();assert.equal(vm.totales.UtilidadBruta,280);assert.equal(vm.porUsuario.length,2);assert.equal(vm.dirty,false);vm.usuarioReg='ana';assert.equal(vm.dirty,true);vm.fechaInicio='2026-10-17';await vm.generar();assert(vm.error);console.log('PASS Vue template, service defaults, all users, totals, grouped summary, changed filters and invalid dates.');})().catch(e=>{console.error(e);process.exitCode=1});
