import {createRequire} from 'node:module';
import {execFileSync} from 'node:child_process';
import assert from 'node:assert/strict';
const require=createRequire(new URL('../.tmp-order-workbook/package.json',import.meta.url));const {chromium}=require('playwright');
const sessionTool=new URL('./ali-browser-test-session.php',import.meta.url).pathname.replace(/^\/(.:)/,'$1');
const session=JSON.parse(execFileSync('C:/xampp/php/php.exe',[sessionTool,'create'],{encoding:'utf8'}));
const browser=await chromium.launch({channel:'chrome',headless:true});
try{
 const context=await browser.newContext({viewport:{width:1400,height:950}});await context.addCookies([{name:'PHPSESSID',value:session.id,domain:'localhost',path:'/',httpOnly:true}]);const page=await context.newPage();let prepares=0,blocked=0;
 await page.addInitScript(()=>{sessionStorage.setItem('User','superadmin');localStorage.setItem('aliVoicePreferences',JSON.stringify({voice:'browser',readAloud:false}));window.SpeechRecognition=class{constructor(){window.speech=this}start(){this.onstart?.()}abort(){}say(text){const row=[{transcript:text}];row.isFinal=true;this.onresult?.({resultIndex:0,results:[row]})}}});
 await page.route('**/api/index.php/**',async route=>{const q=route.request(),u=new URL(q.url());if(q.method()==='POST'){if(u.pathname.endsWith('/ali/pago/preparar'))prepares++;else if(!['/ali/autorizar','/ali/pago/cancelar'].some(p=>u.pathname.endsWith(p))){blocked++;return route.abort();}}return route.continue();});
 await page.goto('http://localhost/nuevo/index.html#/ventas',{waitUntil:'networkidle'});
 await page.evaluate(()=>{const find=v=>{if(v.aliExecute)return v;for(const c of v.$children||[]){const r=find(c);if(r)return r}};window.pos=find(document.querySelector('#app').__vue__);window.ali=document.querySelector('.ali-host').__vue__});
 await page.waitForFunction(()=>pos.ventas[pos.index].cliente.IdCliente&&pos.ventas[pos.index].comisionista.IdCliente&&pos.ventas[pos.index].vendedor.Usuario==='superadmin');
 const ready=()=>page.waitForFunction(()=>!ali.busy&&ali._wakeRecognition,{},{timeout:6000});const say=async text=>{await ready();await page.evaluate(t=>speech.say(t),text);await page.waitForFunction(()=>!ali.busy,{},{timeout:15000});await ready();};
 await page.locator('.ali-wake-toggle').click();await say('Hola ALI');await say('Buscar producto por código');await say('uno aceptar');
 await page.waitForFunction(()=>pos.ventas[pos.index].productos.some(p=>String(p.CodigoBarra)==='1'));
 await page.evaluate(()=>{const refs=pos.$refs['carrito'+pos.index],cart=Array.isArray(refs)?refs[0]:refs;const p=pos.ventas[pos.index].productos[0];p.cantidad=2;p.descuento='';cart.actualizarCantidadProducto(0,2);cart.actualizarProd(p);});
 await page.waitForFunction(()=>Number(pos.totalCart)===240);await say('método de pago Yape');await say('ALIPAGO');
 await page.locator('.ali-checkout-panel').waitFor({timeout:5000});assert.equal(await page.locator('.ali-checkout-total').innerText(),'S/ 240.00');
 assert.equal(await page.evaluate(()=>pos.aliCheckoutReview.summary.payments[0].label),'Yape/Plin');assert.equal(await page.locator('.ali-modal:visible').count(),0);assert.equal(await page.locator('.ali-checkout-error').count(),0);
 await page.screenshot({path:'.tmp-order-workbook/ali-pago-real-summary.png'});
 assert.equal(prepares,1);assert.equal(blocked,0);await say('cancelar');assert.equal(await page.locator('.ali-checkout-panel').count(),0);
 await page.screenshot({path:'.tmp-order-workbook/ali-pago-real-defaults.png'});
 console.log('PASS real ERP browser defaults and real API: voice barcode 1, two cameras = S/240, actual TIENDA/default client/blank address/optional serial, Yape, ALIPAGO summary and cancellation. No mocked financial validation, no sale confirmation or SUNAT/OpenAI requests.');
}finally{await browser.close();execFileSync('C:/xampp/php/php.exe',[sessionTool,'destroy',session.id]);}
