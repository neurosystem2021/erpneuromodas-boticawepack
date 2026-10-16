const fs=require('fs'),assert=require('assert'),vm=require('vm'),babel=require('babel-core');
let source=require('vue-template-compiler').parseComponent(fs.readFileSync('src/views/reportes/ReporteProyectos.vue','utf8')).script.content;
source=source.replace(/import config from[^;]+;/,'const config={API:"/api/"};').replace('export default','module.exports =');
const sandbox={module:{exports:{}},console};vm.runInNewContext(babel.transform(source,{babelrc:false,presets:[['env',{targets:{node:'current'}}]]}).code,sandbox);
const component=sandbox.module.exports;let requested;
const state={API:'/api/',fechaIni:'2026-08-30',fechaFin:'2026-10-14',idAlmacen:0,axios:{get:(url,options)=>{requested=options.params;return Promise.resolve({data:[{IdPreOrden:175,UsuarioReg:'admin'}]});}},$toasted:{error:message=>{throw Error(message);}}};
component.methods.myProvider.call(state,{filter:'A&B',sortBy:'UsuarioReg',sortDesc:false}).then(rows=>{assert.equal(requested.FechaIni,'2026-08-30');assert.equal(requested.FechaFin,'2026-10-14');assert.equal(requested.filter,'A&B');assert.equal(state.totalRows,1);assert.equal(rows[0].UsuarioReg,'admin');assert(!requested.limit);console.log('Project search: date order, text params, filtered count and creator OK');});
