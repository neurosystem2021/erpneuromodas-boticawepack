const assert=require('assert/strict'),fs=require('fs');
const {chromium}=require('../.tmp-order-workbook/node_modules/playwright');
(async()=>{const b=await chromium.launch({channel:'chrome',headless:true});try{
const page=await b.newPage(),errors=[];page.on('pageerror',e=>errors.push(e.message));let scenario='html';
await page.addInitScript(()=>{const original=setTimeout;window.setTimeout=(f,n,...args)=>original(f,n===15000?40:n,...args);});
await page.route('**/*',async r=>{const u=new URL(r.request().url());assert.equal(u.hostname,'login-fixture.invalid');
 if(u.pathname==='/controllers/validarLogin.php'){
  assert.equal(r.request().method(),'POST');assert.ok(r.request().postData().includes('nameUser=fixture'));
  if(scenario==='html')return r.fulfill({status:200,contentType:'text/html',body:'Invalid query: fixture'});
  if(scenario==='unavailable')return r.fulfill({status:503,json:{isLogin:false,message:'Base de datos no disponible.'}});
  if(scenario==='wrong')return r.fulfill({status:401,json:{isLogin:false,message:'Datos incorrectos.'}});
  if(scenario==='network')return r.abort();
  if(scenario==='timeout'){await new Promise(resolve=>setTimeout(resolve,100));return r.abort();}
  return r.fulfill({status:200,json:{isLogin:true,redirect:'/views/V_VentaForm.php'}});
 }
 return r.fulfill({contentType:'text/html',body:'<form onsubmit="acceder(event)"><input id="usr"><input id="pass"><div id="errorLogin"></div><button id="btnAcceder" type="submit">Ingresar</button></form>'});
});
const checks=[];
for(const test of ['html','unavailable','wrong','network','timeout','success']){
 scenario=test;await page.goto('https://login-fixture.invalid/');await page.addScriptTag({content:fs.readFileSync('server/production-login/login.js','utf8')});
 await page.locator('#usr').fill('fixture');await page.locator('#pass').fill('fixture-password');await page.locator('#btnAcceder').click();
 if(test==='success')await page.waitForURL('**/views/V_VentaForm.php');
 else{await page.waitForFunction(()=>!document.getElementById('btnAcceder').disabled&&document.getElementById('errorLogin').innerText.length>0);const message=await page.locator('#errorLogin').innerText();assert.ok(!message.includes('Invalid query:'));}
 checks.push(test);
}
assert.deepEqual(errors,[]);console.log(JSON.stringify({pass:checks,no_uncaught_errors:true,all_http_mocked:true}));
}finally{await b.close();}})().catch(e=>{console.error(e);process.exitCode=1;});
