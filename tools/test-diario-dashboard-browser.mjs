import {createRequire} from 'node:module';
import fs from 'node:fs';
import assert from 'node:assert/strict';
const require=createRequire(new URL('../.tmp-order-workbook/package.json',import.meta.url));const {chromium}=require('playwright');const compiler=createRequire(import.meta.url)('vue-template-compiler');
const main=compiler.parseComponent(fs.readFileSync('src/views/DiarioActividades.vue','utf8'));
const performance=compiler.parseComponent(fs.readFileSync('src/components/DiarioRendimiento.vue','utf8'));
const dashboard=compiler.parseComponent(fs.readFileSync('src/components/DiarioDashboard.vue','utf8'));
const helpers=fs.readFileSync('src/utils/diario.js','utf8').replace(/export /g,'')+'\n'+fs.readFileSync('src/utils/diarioAppearance.js','utf8').replace(/export /g,'');
const script=p=>p.script.content.replace(/^import .*;\s*$/gm,'').replace('export default','return');
const browser=await chromium.launch({channel:'chrome',headless:true});
try{
 const page=await browser.newPage({viewport:{width:1400,height:1100}});
 await page.setContent('<html><head><meta charset="utf-8"></head><body><div class="diary-page"><div id="performance"></div><div id="dashboard"></div></div></body></html>');
 await page.addStyleTag({content:fs.readFileSync('node_modules/bootstrap/dist/css/bootstrap.css','utf8')+[main,performance,dashboard].map(p=>p.styles.map(s=>s.content).join('\n')).join('\n')});
 await page.addScriptTag({content:fs.readFileSync('node_modules/vue/dist/vue.js','utf8')});
 await page.evaluate(({helpers,ps,pt,ds,dt})=>{
  Vue.component('b-modal',{props:['value','title'],template:'<section v-if="value"><h2>{{title}}</h2><slot/></section>'});
  const p=new Function(ps)();p.template=pt;
  const d=new Function('config','DiarioAdjuntos','DiarioSoporte',helpers+'\n'+ds)({API:'/api/'},{template:'<div></div>'},{template:'<div></div>'});d.template=dt;
  const base={Id:1,Titulo:'Revisar stock',ResponsableNombre:'Ana Torres',Cargo:'Almacén',Estado:'concluida',Objetivo:10,Realizado:10,Unidad:'productos',Calidad:5,Reconocimiento:'Buen trabajo en la revisión',Inicio:'2026-10-14 08:00:00',Fin:'2026-10-14 09:00:00',FinReal:'2026-10-14 08:50:00'};
  window.perf=new Vue({...p,propsData:{tasks:[base,{...base,Id:2,Titulo:'Actualizar precios',Calidad:4,Realizado:5},{...base,Id:3,Estado:'pendiente',Calidad:null,Realizado:0}]}}).$mount('#performance');
  const summary={total:120,concluidas:80,validadas:70,aTiempo:60,vencidas:10,calidad:4.5,logro:72.5};
  window.requests=[];Vue.prototype.axios={get:async url=>{requests.push(url);if(url.endsWith('/eventos'))return {data:[{Id:1,Usuario:'Ana',Accion:'nota',Texto:'Registro tecnico verificado',Fecha:'2026-10-14 10:00:00'}]};const page=Number(new URL('http://fixture'+url).searchParams.get('pagina'));return {data:{resumen:summary,equipo:[{key:'e:1',nombre:'Ana Torres',cargo:'Almacén',total:120,concluidas:80,validadas:70,calidad:4.5,cumplimiento:66.7,puntualidad:75,logrados:87,posibles:120,logro:72.5,vencidas:10,segundos:3600}],estados:{pendiente:30,en_curso:10,concluida:80},calificaciones:{1:0,2:0,3:10,4:20,5:40},actividades:Array.from({length:page===2?20:100},(_,i)=>({...base,Id:(page-1)*100+i+1})),pagina:page,paginas:2}}}};
  window.dash=new Vue({...d,propsData:{context:{superadmin:true,usuarios:[],empleados:[]}}}).$mount('#dashboard');
 },{helpers,ps:script(performance),pt:performance.template.content,ds:script(dashboard),dt:dashboard.template.content});
 await page.waitForSelector('.diary-dashboard table');
 assert.equal(await page.evaluate(()=>perf.average),'4.50');assert.equal(await page.evaluate(()=>perf.achieved),1);
 await page.locator('.diary-performance select').selectOption('5');assert.equal(await page.locator('.diary-performance tbody tr').count(),1);
 await page.getByRole('button',{name:'Siguiente',exact:true}).click();await page.waitForFunction(()=>dash.data.pagina===2);await page.waitForFunction(()=>document.querySelectorAll('.diary-dashboard .diary-team:last-of-type tbody tr').length===20);assert.equal(await page.locator('.diary-dashboard .diary-team').last().locator('tbody tr').count(),20);assert.equal(await page.evaluate(()=>dash.data.resumen.total),120);
 await page.locator('.diary-dashboard input[type=date]').first().fill('2026-10-02');assert.equal(await page.evaluate(()=>dash.dirty),true);
 await page.getByRole('button',{name:'Consultar reporte',exact:true}).click();await page.waitForFunction(()=>!dash.loading);assert.ok(await page.evaluate(()=>requests[requests.length-1].includes('desde=2026-10-02')));
 await page.getByRole('button',{name:'Ver reporte detallado',exact:true}).first().click();await page.waitForFunction(()=>dash.events.length===1);await page.getByText('Registro tecnico verificado',{exact:true}).waitFor();assert.equal(await page.getByText('Registro tecnico verificado',{exact:true}).count(),1);await page.evaluate(()=>{dash.detailOpen=false;dash.selected=null});
 await page.screenshot({path:'output/diario-dashboard-desktop.png',fullPage:true});
 await page.setViewportSize({width:393,height:852});assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),true);
 await page.screenshot({path:'output/diario-dashboard-mobile.png',fullPage:true});
 console.log('PASS employee grade average and goal filtering, dashboard date filters, full totals across pages and desktop/mobile layout. All requests mocked.');
}finally{await browser.close()}
process.exit(0);
