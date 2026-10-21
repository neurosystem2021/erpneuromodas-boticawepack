const assert=require('assert/strict'),fs=require('fs');
const {chromium}=require('../../.tmp-order-workbook/node_modules/playwright');
(async()=>{const browser=await chromium.launch({channel:'chrome',headless:true});try{
const page=await browser.newPage({viewport:{width:1500,height:1000}}),errors=[],failed=[];
page.on('pageerror',e=>errors.push(e.message));page.on('response',r=>{if(r.url().includes('/api/')&&r.status()>=400)failed.push({path:new URL(r.url()).pathname,status:r.status()});});
await page.route('**/*',r=>r.request().method()==='GET'&&new URL(r.request().url()).hostname==='localhost'?r.continue():r.abort());
await page.addInitScript(()=>{sessionStorage.setItem('User','admin');sessionStorage.setItem('IdAlmacen','1');sessionStorage.setItem('IdPerfil','16');sessionStorage.setItem('IdPuntoVenta','1');});
const checks=[];
async function check(anchor,min,label){
const input=page.locator('.autocomplete-wrapper').filter({has:page.locator('input')});
const found=await page.evaluate(anchor=>{for(const el of document.querySelectorAll('.autocomplete-wrapper')){const v=el.__vue__;if(v&&v.anchor===anchor&&el.getBoundingClientRect().width){el.setAttribute('data-live-check',anchor);return true;}}return false;},anchor);
assert.ok(found,label+' selector exists');await page.locator(`[data-live-check="${anchor}"] input`).click();
await page.waitForFunction(({anchor,min})=>{const el=document.querySelector(`[data-live-check="${anchor}"]`);return el&&Array.isArray(el.__vue__.json)&&el.__vue__.json.length>=min;},{anchor,min});
const result=await page.evaluate(anchor=>{const el=document.querySelector(`[data-live-check="${anchor}"]`),v=el.__vue__;return {anchor,rows:v.json.length,visible:v.showList,endpoint:v.url};},anchor);
assert.equal(result.visible,true);checks.push({label,...result});await page.screenshot({path:`tmp/imceden-movement-type/${anchor}-after.png`,fullPage:true});await page.locator('body').click({position:{x:1450,y:950}});
}
await page.goto('http://localhost/nuevo/index.html#/');await page.locator('.inventory-field--movement input').waitFor();await check('TipoMovimiento',8,'Movement types');
await page.goto('http://localhost/nuevo/index.html#/ventas');await page.locator('#inputTipoVenta0').waitFor();await check('TipoDoc',6,'Sale types');await check('Producto',1,'POS products');
assert.deepEqual(errors,[]);assert.deepEqual(failed,[]);fs.writeFileSync('tmp/imceden-movement-type/browser-after.json',JSON.stringify({checks,errors,failed,writesBlocked:true},null,2));console.log(JSON.stringify({checks,errors,failed,writesBlocked:true}));
}finally{await browser.close();}})().catch(e=>{console.error(e);process.exitCode=1;});
