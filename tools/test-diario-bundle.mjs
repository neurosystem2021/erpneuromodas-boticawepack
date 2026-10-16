import {createRequire} from 'node:module';
import fs from 'node:fs';
import path from 'node:path';
import assert from 'node:assert/strict';
const require=createRequire(new URL('../.tmp-order-workbook/package.json',import.meta.url));const {chromium}=require('playwright');
const browser=await chromium.launch({channel:'chrome',headless:true});
try{
 const page=await browser.newPage({viewport:{width:1400,height:1000}});const errors=[];page.on('pageerror',e=>errors.push(e.message));let saved=0,personalSaved=0;
 await page.route('**/*',async route=>{
  const url=new URL(route.request().url());
  if(url.hostname!=='diario.local')return route.fulfill({status:204,body:''});
  if(url.pathname.startsWith('/nuevo/')){const file=path.resolve('dist',decodeURIComponent(url.pathname.slice(7)));assert.ok(file.startsWith(path.resolve('dist')+path.sep));if(!fs.existsSync(file))return route.fulfill({status:404,body:''});const ext=path.extname(file);return route.fulfill({body:fs.readFileSync(file),contentType:{'.html':'text/html','.js':'application/javascript','.css':'text/css','.svg':'image/svg+xml'}[ext]||'application/octet-stream'})}
  if(url.pathname.endsWith('/diario/contexto'))return route.fulfill({json:{user:'worker',admin:false,csrf:'fixture'}});
  if(url.pathname.endsWith('/diario/soporte/diario'))return route.fulfill({json:{dias:[]}});
  if(url.pathname.endsWith('/diario/personales')){if(route.request().method()==='POST'){personalSaved++;return route.fulfill({json:{Id:9999}})}return route.fulfill({json:{recordatorios:[],limitado:false}});}
  if(url.pathname.endsWith('/diario/recordatorios'))return route.fulfill({json:{ahora:'2026-10-14 12:00:00',tareas:[]}});
  if(url.pathname.endsWith('/diario/tareas')&&route.request().method()==='POST'){saved++;return route.fulfill({json:{Id:8888}})}
  if(url.pathname.endsWith('/diario/tareas'))return route.fulfill({json:{tareas:[],limitado:false,ahora:'2026-10-14 12:00:00'}});
  return route.fulfill({json:[]});
 });
 await page.goto('http://diario.local/nuevo/index.html#/diario',{waitUntil:'domcontentloaded'});
 await page.getByRole('heading',{name:'Centro de trabajo',exact:true}).waitFor();
 assert.ok(await page.locator('.diary-luxe').evaluate(e=>getComputedStyle(e).backgroundImage.includes('radial-gradient')),'premium background is present in the production build');
 await page.getByRole('button',{name:'+ Nueva tarea'}).first().click();
 await page.getByPlaceholder('¿Qué vas a realizar?').fill('Actividad de prueba del bundle');
 await page.getByRole('button',{name:'Guardar actividad',exact:true}).click();
 await page.waitForFunction(()=>!document.querySelector('.modal.show'));
 assert.equal(saved,1);assert.equal(errors.length,0,errors.join('\n'));
 await page.getByRole('button',{name:'Mis recordatorios personales',exact:false}).click();
 await page.getByRole('button',{name:'+ Recordatorio personal',exact:true}).click();
 await page.locator('.modal.show input').first().fill('Mi recordatorio privado');
 await page.getByRole('button',{name:'Guardar recordatorio',exact:true}).click();
 await page.waitForFunction(()=>!document.querySelector('.modal.show'));
 assert.equal(personalSaved,1);
 assert.equal(await page.getByRole('button',{name:'Productividad del equipo',exact:true}).count(),0,'employee has no team workspace');
 await page.setViewportSize({width:393,height:852});assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth<=window.innerWidth),true);
 await page.screenshot({path:'output/diario-bundle-mobile.png',fullPage:true});
 console.log('PASS actual production bundle, route, Bootstrap modal, worker task creation, reminder control and mobile width. All HTTP mocked.');
}finally{await browser.close()}
process.exit(0);
