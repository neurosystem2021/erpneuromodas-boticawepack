import {createRequire} from 'node:module';
const require=createRequire(new URL('../.tmp-order-workbook/package.json',import.meta.url));
const {chromium}=require('playwright');
import assert from 'node:assert/strict';
const browser=await chromium.launch({channel:'chrome',headless:true});
try{
 const page=await browser.newPage({viewport:{width:1400,height:950}});
 let searchCalls=0;
 await page.addInitScript(()=>{
  localStorage.setItem('aliVoicePreferences',JSON.stringify({voice:'coral',style:'infantil',readAloud:true}));
  window.Audio=class{play(){setTimeout(()=>this.onended&&this.onended(),5);return Promise.resolve()}pause(){}};
  window.SpeechRecognition=class{constructor(){window.aliSpeech=this}start(){this.onstart&&this.onstart()}abort(){}say(text){const r=[{transcript:text}];r.isFinal=true;this.onresult&&this.onresult({resultIndex:0,results:[r]})}};
 });
 await page.route('**/api/index.php/**',async route=>{
  const req=route.request(),url=new URL(req.url());
  if(url.pathname.endsWith('/ali/config'))return route.fulfill({json:{user:'maria',configured:true,enabled:true,model:'gpt-4.1-mini',superadmin:true,csrf:'test-only'}});
  if(url.pathname.endsWith('/ali/voz')){await new Promise(resolve=>setTimeout(resolve,4500));try{await route.fulfill({status:200,contentType:'audio/mpeg',body:'fake-audio'})}catch{}return;}
  if(url.pathname.endsWith('/cliente')&&url.searchParams.get('dni')==='12345678')return route.fulfill({json:{IdCliente:9,Cliente:'CLIENTE PRUEBA',DniRuc:'12345678'}});
  if(url.pathname.endsWith('/ali/autorizar'))return route.fulfill({json:{authorized:true}});
  if(req.method()==='POST')throw Error('Unexpected mutation');
  if(url.pathname.includes('/productos/stock/')&&url.searchParams.get('q')==='cinta')searchCalls++;
  return route.continue();
 });
 await page.goto('http://localhost/nuevo/index.html#/ventas',{waitUntil:'networkidle'});
 await page.evaluate(()=>{
  const find=v=>{if(v&&v.aliExecute)return v;for(const child of v.$children||[]){const result=find(child);if(result)return result}};
  window.pos=find(document.querySelector('#app').__vue__);window.pos.ventas[0].almacen.IdAlmacen=1;
 });
 const ready=()=>page.waitForFunction(()=>{const a=document.querySelector('.ali-host').__vue__;return a._wakeRecognition&&a.listening&&!a.busy&&!a.talking},{},{timeout:2500});
 const say=async text=>{await ready();await page.evaluate(t=>window.aliSpeech.say(t),text)};
 await page.locator('.ali-wake-toggle').click();
 const start=Date.now();
 await page.evaluate(()=>window.aliSpeech.say('Hola ALI'));
 await ready();
 assert.ok(Date.now()-start<2500,'Listening must resume before slow audio arrives');
 assert.equal(await page.evaluate(()=>document.querySelector('.ali-host').__vue__.voiceLoading),true);
 await say('Buscar producto por código');
 await page.waitForFunction(()=>document.activeElement.id==='txtBarcode');
 await say('Salir de código de barras');
 await page.waitForFunction(()=>document.activeElement.id==='txtAddProduct');
 await say('busca cinta');
 await page.waitForFunction(()=>document.querySelector('.ali-host').__vue__.activeField==='search'&&!document.querySelector('.ali-host').__vue__.busy);
 await page.waitForTimeout(700);
 assert.equal(searchCalls,1,'One search request, without duplicate autocomplete query');
 const cart=await page.evaluate(()=>{const c=window.pos.$refs.carrito0;return (Array.isArray(c)?c[0]:c).$refs.autocompleteProducto.json.length});
 assert.ok(cart>0,'Actual results shown');
 for(const method of ['Yape','Efectivo','Tarjeta','Transferencia']){
  await say('metodo de pago '+method);
  await page.waitForFunction(()=>document.querySelector('.ali-host').__vue__.activeField==='payment');
 }
 await say('DNI 12345678');
 await page.waitForFunction(()=>window.pos.ventas[0].cliente&&window.pos.ventas[0].cliente.DniRuc==='12345678');
 await say('Total');
 await page.waitForFunction(()=>document.querySelector('.ali-host').__vue__.status.startsWith('El total es'));
 assert.equal(await page.locator('.ali-modal:visible').count(),0);
 console.log('PASS commands during 4.5s audio delay, exit barcode, real product search with one GET, all 4 methods, fixture client and total. No real sales or OpenAI calls.');
}finally{await browser.close()}
