import {createRequire} from 'node:module';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import path from 'node:path';
const require=createRequire(new URL('../.tmp-order-workbook/package.json',import.meta.url));
const {chromium}=require('playwright');
const browser=await chromium.launch({channel:'chrome',headless:true});
try{
 const page=await browser.newPage({viewport:{width:1400,height:950}});let prepares=0,confirms=0,manual=0,cancels=0,lastId='',loseResponse=false,completed=false;
 await page.addInitScript(()=>{localStorage.setItem('aliVoicePreferences',JSON.stringify({voice:'browser',readAloud:false}));window.SpeechRecognition=class{constructor(){window.speech=this}start(){this.onstart?.()}abort(){}say(text){const row=[{transcript:text}];row.isFinal=true;this.onresult?.({resultIndex:0,results:[row]})}}});
 const result=()=>({insertId:9999999,operation:lastId,state:'registrada',fiscal:'sin_envio_sunat'});
 await page.route('**/imprimir/usb.php',route=>route.fulfill({json:{ok:true,mensaje:'Mock printer accepted'}}));
 await page.route('**/api/index.php/**',async route=>{
  const q=route.request(),u=new URL(q.url());
  if(u.pathname.endsWith('/ali/config'))return route.fulfill({json:{user:'superadmin',configured:false,enabled:false,csrf:'fixture',pos:{read:true,write:true,checkout:true}}});
  if(u.pathname.endsWith('/ventas/tipos'))return route.fulfill({json:[{IdDocVentaTipoDoc:1,CodSunat:'01',TipoDoc:'FACTURA ELEC.'},{IdDocVentaTipoDoc:3,CodSunat:'03',TipoDoc:'BOLETA ELEC.'},{IdDocVentaTipoDoc:99,CodSunat:'99',TipoDoc:'NOTA DE VENTA'}]});if(u.pathname.endsWith('/cliente')&&u.searchParams.has('dni'))return route.fulfill({json:{IdCliente:2,DniRuc:u.searchParams.get('dni'),Cliente:'CLIENTE DOCUMENTO FIXTURE',Direccion:''}});if(u.pathname.endsWith('/ali/autorizar'))return route.fulfill({json:{authorized:true}});
  if(u.pathname.endsWith('/ali/pago/preparar')){prepares++;const body=q.postDataJSON();assert.equal(body.sale.totalCart,10);lastId=body.operation;return route.fulfill({json:{operation:lastId,state:'preparada',summary:{client:'CLIENTE FIXTURE',document:'BOLETA ELEC.',series:'BTST',items:[{quantity:1,name:'CAMARA FIXTURE',total:10}],payments:[{label:'Yape/Plin',amount:10}],discount:0,total:10,fiscal:'No se enviará a SUNAT.'}}});}
  if(u.pathname.endsWith('/ali/pago/confirmar')){confirms++;assert.deepEqual(q.postDataJSON(),{operation:lastId,verified:true});completed=true;await new Promise(r=>setTimeout(r,500));return loseResponse?route.abort('connectionreset'):route.fulfill({json:result()});}
  if(u.pathname.endsWith('/ali/pago/cancelar')){cancels++;return route.fulfill({json:{cancelled:!completed}});}
  if(u.pathname.includes('/ali/pago/estado/'))return route.fulfill({json:{state:completed?'completada':'preparada',result:completed?result():null}});
  if(q.method()==='POST'){assert.ok(u.pathname.endsWith('/ventas'),'Unexpected business mutation');manual++;return route.fulfill({json:{insertId:9999998}});}
  return route.continue();
 });
 await page.route('**/nuevo/**',async route=>{const relative=decodeURIComponent(new URL(route.request().url()).pathname.slice('/nuevo/'.length));const file=path.resolve('dist',relative);assert.ok(file.startsWith(path.resolve('dist')+path.sep));const ext=path.extname(file);await route.fulfill({body:fs.readFileSync(file),contentType:ext==='.html'?'text/html':ext==='.js'?'application/javascript':ext==='.css'?'text/css':'application/octet-stream'});});
 await page.goto('http://localhost/nuevo/index.html#/ventas',{waitUntil:'domcontentloaded',timeout:60000});
 await page.waitForSelector('.ali-host',{timeout:20000});
 await page.evaluate(()=>{const find=v=>{if(v.aliExecute)return v;for(const c of v.$children||[]){const r=find(c);if(r)return r}};window.pos=find(document.querySelector('#app').__vue__);window.ali=document.querySelector('.ali-host').__vue__});
 const fixture=async()=>{completed=false;await page.evaluate(()=>{const s=pos.ventas[pos.index];s.almacen={IdAlmacen:1};s.puntoVenta={IdDocVentaPuntoVenta:1};s.cliente={IdCliente:1,DniRuc:'00000000',Cliente:'CLIENTE FIXTURE'};s.vendedor={Usuario:'superadmin'};s.productos=[{IdProducto:1,Producto:'CAMARA FIXTURE',CodigoBarra:'FIXTURE',cantidad:1,precio:10,PrecioContado:10,PrecioCosto:1,stock:50,EsPadre:0,subtotal:10,total:10,descuento:0,Series:[],TienePresentacion:0}];s.totalCart=10;s.pago={efectivo:{IdMetodoPago:1,monto:0},visa:{IdMetodoPago:2,monto:0},mastercard:{IdMetodoPago:3,monto:0},yape:{IdMetodoPago:4,monto:10,descripcion:'OP-TEST'}};pos.venta=s;pos.actualizarTotal(10);});await page.waitForFunction(()=>Number(pos.totalCart)===10);};
 const ready=()=>page.waitForFunction(()=>!ali.busy&&ali._wakeRecognition,{},{timeout:5000});
 const say=async text=>{await ready();await page.evaluate(t=>speech.say(t),text);await page.waitForFunction(()=>!ali.busy,{},{timeout:10000});await ready();};
 await fixture();await page.evaluate(()=>{window.spoken=[];const originalSay=ali.say.bind(ali);ali.say=(text,...args)=>{spoken.push(text);return originalSay(text,...args)};pos.getNumeroVenta=async()=>{};const find=v=>{if(v.procesarCodigoBarra)return v;for(const c of v.$children||[]){const r=find(c);if(r)return r}};window.cart=find(pos);window.codes=[];cart.procesarCodigoBarra=async code=>{codes.push(code);return true}});await page.locator('.ali-wake-toggle').click();await say('ALI ventas');assert.equal(await page.evaluate(()=>ali.activeField),'barcode');assert.equal(await page.evaluate(()=>spoken.length),0,'Focus must not repeat instructions');await say('uno dos tres');assert.equal(await page.evaluate(()=>spoken.length),0,'Digit draft must stay silent');await say('buscar');assert.deepEqual(await page.evaluate(()=>codes),['123']);await page.locator('#txtBarcode').fill('456');await say('buscar');assert.deepEqual(await page.evaluate(()=>codes),['123','456']);await say('buscar camaras');assert.match(await page.evaluate(()=>ali.status),/codigo de barras/);await page.evaluate(()=>{ali.awake=false});await say('tipo de venta factura elec');assert.equal(await page.evaluate(()=>pos.ventas[pos.index].tipoVenta.CodSunat),'01');await say('tipo de venta nota');assert.equal(await page.evaluate(()=>pos.ventas[pos.index].tipoVenta.CodSunat),'99');await say('tipo de venta');await say('boleta elec');assert.equal(await page.evaluate(()=>pos.ventas[pos.index].tipoVenta.CodSunat),'03');await page.evaluate(()=>{ali.awake=false});await say('aliclientes');assert.equal(await page.evaluate(()=>ali.activeField),'customer');await page.locator('[data-ali-customer=true]').fill('12345678');await say('buscar');assert.equal(await page.evaluate(()=>pos.ventas[pos.index].cliente.IdCliente),2);await say('ALI clientes');await say('borrar');await say('dos cero cinco siete tres cero dos siete uno dos cinco');await say('buscar');assert.equal(await page.evaluate(()=>pos.ventas[pos.index].cliente.DniRuc),'20573027125');await say('ALI cliente');assert.equal(await page.locator('[data-ali-customer=true]').inputValue(),'');assert.equal(await page.locator('[data-ali-customer=true]').getAttribute('inputmode'),'numeric');await page.locator('[data-ali-customer=true]').fill('87654321');await page.waitForFunction(()=>pos.ventas[pos.index].cliente.DniRuc==='87654321');await say('ALI clientes');await page.locator('[data-ali-customer=true]').fill('20573027');await new Promise(r=>setTimeout(r,100));await page.locator('[data-ali-customer=true]').fill('20573027125');await page.waitForFunction(()=>pos.ventas[pos.index].cliente.DniRuc==='20573027125');await say('ALI actualizar');assert.equal(await page.evaluate(()=>pos.ventas[pos.index].productos.length),0);assert.equal(await page.evaluate(()=>ali.activeField),'barcode');assert.equal(await page.evaluate(()=>document.activeElement.id),'txtBarcode');await say('confirmado');assert.equal(confirms,0);await fixture();
 await page.evaluate(()=>pos.ventas[pos.index].pago.yape.monto=0);await say('ALIPAGO');
 assert.match(await page.locator('.ali-checkout-error').innerText(),/Los pagos no coinciden con la venta/);assert.equal(prepares,0,'Invalid POS data reports its exact reason before server preparation');
 await page.evaluate(()=>pos.ventas[pos.index].pago.yape.monto=10);
 await say('ALIPAGO');await page.locator('.ali-checkout-panel').waitFor();
 assert.equal(confirms,0);assert.equal(manual,0);assert.equal(await page.locator('.ali-modal:visible').count(),0);
 await say('ALIPAGO');assert.equal(confirms,0,'Repeated preparation cannot charge');
 await page.evaluate(()=>pos.ventas[pos.index].productos[0].cantidad=2);
 await say('confirmar pago');assert.equal(confirms,0,'Changed cart invalidates confirmation');
 await say('ALI actualizar');assert.equal(await page.locator('.ali-checkout-panel').count(),0);assert.equal(await page.evaluate(()=>pos.ventas[pos.index].productos.length),0);assert.equal(await page.evaluate(()=>ali.activeField),'barcode');
 await fixture();await say('ALIPAGO');
 await page.evaluate(()=>{speech.say('ALI pago confirmar');setTimeout(()=>speech.say('ALI pago confirmar'),25)});
 await page.waitForFunction(()=>!ali.busy&&pos.ultimaVenta===9999999);assert.equal(confirms,1);assert.equal(await page.evaluate(()=>pos.ventas[pos.index].productos.length),0);
 assert.equal(manual,0);assert.equal(await page.evaluate(()=>ali.status),'Venta realizada.');
 await fixture();await say('ALIPAGO');loseResponse=true;
 await page.evaluate(()=>speech.say('ALI pago confirmar'));await page.waitForFunction(()=>!ali.busy&&pos.aliCheckoutReview&&pos.aliCheckoutReview.uncertain);
 await page.locator('.ali-checkout-actions button').last().click();await page.waitForFunction(()=>!pos.aliCheckoutReview);
 assert.equal(confirms,2,'Status recovery never resubmits the sale');
 await fixture();await say('ALIPAGO');await page.setViewportSize({width:393,height:852});
 assert.equal(await page.evaluate(()=>{const el=document.querySelector('.ali-checkout-panel');return el.scrollWidth<=el.clientWidth+1}),true);
 await page.screenshot({path:'.tmp-order-workbook/ali-pago-mobile.png'});
 await say('cancelar');await page.evaluate(()=>pos.guardarVenta());await page.waitForFunction(()=>pos.ultimaVenta===9999998);assert.equal(manual,1,'Manual POS still calls existing /ventas');
 console.log('PASS ALIPAGO voice preparation, inline/mobile review, no automatic modal, changed cart refused, cancel, repeat confirmation suppressed, lost response recovered without new POST, manual POS route preserved. All writes mocked; no fiscal requests.');
}finally{await browser.close()}
