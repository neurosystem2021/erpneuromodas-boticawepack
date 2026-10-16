import {chromium} from 'playwright';
import assert from 'node:assert/strict';
const browser=await chromium.launch({channel:'chrome',headless:true});
try {
 const page=await browser.newPage({viewport:{width:1400,height:950}});
 let authorizeDelay=0,deny=false,offline=false,searches=[],mutations=0;
 await page.addInitScript(()=>{
  localStorage.setItem('aliVoicePreferences',JSON.stringify({voice:'browser',readAloud:false,continuous:true}));
  window.recognizers=[];
  window.SpeechRecognition=class{constructor(){window.aliSpeech=this;window.recognizers.push(this)}start(){this.onstart?.()}abort(){this.aborted=true}say(text){const row=[{transcript:text}];row.isFinal=true;this.onresult?.({resultIndex:0,results:[row]})}};
 });
 await page.route('**/api/index.php/**',async route=>{
  const req=route.request(),url=new URL(req.url());
  if(url.pathname.endsWith('/ali/config'))return route.fulfill({json:{user:'maria',configured:false,enabled:false,csrf:'test',pos:{read:true,write:true}}});
  if(url.pathname.endsWith('/ali/autorizar')){
   if(offline)return route.abort('internetdisconnected');
   const {action}=req.postDataJSON();
   if(authorizeDelay)await new Promise(r=>setTimeout(r,authorizeDelay));
   return route.fulfill({status:deny?403:['pay','confirm'].includes(action)?409:200,json:deny?{error:'No tienes permiso.'}:['pay','confirm'].includes(action)?{error:'Cobro requiere confirmación e idempotencia.'}:{authorized:true}}).catch(()=>{});
  }
  if(req.method()==='POST'){mutations++;throw Error('Unexpected business mutation');}
  if(url.pathname.includes('/productos/stock/')&&['lenta','rapida'].includes(url.searchParams.get('q'))){
   const q=url.searchParams.get('q');searches.push(q);
   if(q==='lenta')await new Promise(r=>setTimeout(r,1100));
   return route.fulfill({json:[{IdProducto:q==='lenta'?1:2,Producto:q==='lenta'?'VIEJO':'NUEVO <img src=x onerror=window.aliXss=1>',CodigoBarra:'12345',Stock:10,PrecioContado:5,ProductoCategoria:'CAMARAS',ProductoMarca:'DAHUA',ProductoModelo:'4MP'}]}).catch(()=>{});
  }
  return route.continue();
 });
 await page.goto('http://localhost/nuevo/index.html#/ventas',{waitUntil:'networkidle'});
 await page.evaluate(()=>{const find=v=>{if(v.aliExecute)return v;for(const c of v.$children||[]){const r=find(c);if(r)return r}};window.pos=find(document.querySelector('#app').__vue__);window.pos.ventas[0].almacen.IdAlmacen=1;window.ali=document.querySelector('.ali-host').__vue__;window.executed=[];const original=ali.execute;ali.execute=a=>{executed.push(a.type);return original(a)}});
 const ready=()=>page.waitForFunction(()=>!window.ali.busy&&(window.ali.listening||(!window.ali.awake&&window.ali._wakeRecognition)),{},{timeout:4000}).catch(async e=>{console.log(await page.evaluate(()=>({listening:ali.listening,busy:ali.busy,wake:ali.wakeEnabled,continuous:ali.continuous,state:ali.operationState,status:ali.status})));throw e});
 const say=async t=>{await ready();await page.evaluate(t=>window.aliSpeech.say(t),t)};
 await page.locator('.ali-wake-toggle').click();await say('Hola ALI');await ready();
 authorizeDelay=500;
 await say('código de barras');await page.waitForFunction(()=>window.ali.busy);
 await page.evaluate(()=>window.aliSpeech.say('ALI silencio'));
 await page.waitForTimeout(800);
 assert.equal(await page.evaluate(()=>window.ali.wakeEnabled||window.ali.listening||window.ali.busy),false);
 assert.equal(await page.evaluate(()=>window.executed.includes('focus')),false,'Stopped authorization cannot execute late action');
 const count=await page.evaluate(()=>window.recognizers.length);await page.waitForTimeout(400);
 assert.equal(await page.evaluate(()=>window.recognizers.length),count,'No automatic microphone restart');
 authorizeDelay=0;await page.locator('.ali-wake-toggle').click();await say('Hola ALI');await ready();
 await say('busca lenta');await page.waitForFunction(()=>window.ali.busy&&window.ali._activeAction==='search');
 await page.waitForTimeout(100);await page.evaluate(()=>window.aliSpeech.say('busca rapida'));
 await page.waitForFunction(()=>window.ali.results[0]?.IdProducto===2&&!window.ali.busy);
 await page.waitForTimeout(1200);
 assert.deepEqual(searches,['lenta','rapida']);
 assert.equal(await page.evaluate(()=>window.ali.results[0].IdProducto),2,'Only latest result survives');
 const rendered=await page.evaluate(()=>{const c=window.pos.$refs.carrito0;return (Array.isArray(c)?c[0]:c).renderChild(window.ali.results[0])});
 assert.ok(rendered.includes('1. ')&&rendered.includes('&lt;img'));
 assert.equal(await page.evaluate(()=>window.aliXss),undefined);
 await say('DNI 12345678');await ready();
 assert.ok(!(await page.evaluate(()=>JSON.stringify(window.ali.history))).includes('12345678'));
 await say('pagar');await ready();assert.match(await page.evaluate(()=>window.ali.status),/idempotencia/);
 offline=true;await say('total');await ready();assert.match(await page.evaluate(()=>window.ali.status),/Sin conexión/);
 offline=false;await say('total');await ready();assert.match(await page.evaluate(()=>window.ali.status),/El total es/);
 deny=true;await say('total');await page.waitForFunction(()=>!window.ali.wakeEnabled&&!window.ali.listening);
 assert.equal(await page.evaluate(()=>window.ali.status),'No tienes permiso.');
 deny=false;await page.evaluate(()=>{window.ali.continuous=false});await page.waitForTimeout(50);
 await page.evaluate(()=>window.ali.toggleWake());await ready();await page.evaluate(()=>window.aliSpeech.say('total'));
 await page.waitForFunction(()=>!window.ali.busy&&!window.ali.listening);
 assert.equal(await page.evaluate(()=>window.ali.wakeEnabled),false);
 assert.equal(await page.evaluate(()=>JSON.parse(localStorage.aliVoicePreferences).continuous),false);
 assert.equal(mutations,0);assert.equal(await page.locator('.ali-modal:visible').count(),0);
 console.log('PASS full stop during pending authorization, no restart, reactivation, newest-search wins, numbered escaped results, masked history, permission revocation, one-shot persistence, no sale POSTs.');
} finally {await browser.close()}
