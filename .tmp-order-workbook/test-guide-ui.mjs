import {chromium} from 'playwright';
import assert from 'node:assert/strict';
const browser=await chromium.launch({channel:'chrome',headless:true});
try{
 const page=await browser.newPage({viewport:{width:1400,height:1000}});const errors=[];page.on('pageerror',e=>errors.push(e.message));page.on('console',m=>{if(m.type()==='error')console.log('Browser error',m.text());});page.on('response',async r=>{if(r.url().includes('venta/detalle/comprobante/guia')||r.url().includes('/cliente?'))console.log('Import request',r.url(),r.status(),(await r.text()).slice(0,120));});
 await page.route('**/api/index.php/**',async route=>{if(route.request().method()!=='GET')return route.abort();return route.continue();});
 await page.goto('http://localhost/nuevo/index.html#/remision',{waitUntil:'networkidle'});
 await page.locator('.nm-guide-hero').waitFor();
 assert.equal(await page.locator('.nm-ubigeo').count(),2);
 const origin=page.locator('.nm-ubigeo').first();await origin.locator('input').fill('100101');await origin.locator('.nm-ubigeo__results button').first().click();
 assert.equal(await page.locator('#codUbigeoOrigen').inputValue(),'100101');
 const select=origin.locator('[data-level=department]');const alternative=await select.evaluate(s=>Array.from(s.options).find(o=>o.value&&o.value!==s.value).value);await select.selectOption(alternative);
 assert.equal(await page.locator('#codUbigeoOrigen').inputValue(),'');
 await page.screenshot({path:'.tmp-order-workbook/guide-desktop.png',fullPage:true});
 await page.goto('http://localhost/nuevo/index.html#/remision-lista',{waitUntil:'networkidle'});await page.getByRole('link',{name:'+ Nueva guía'}).waitFor();assert.equal(await page.locator('.nm-guide-actionbar').count(),0);
 await page.goto('http://localhost/nuevo/index.html#/remision/ver/1/B012/1',{waitUntil:'networkidle'});await page.locator('.nm-guide-hero').waitFor();assert.match(await page.locator('.nm-guide-hero h1').innerText(),/comprobante/);
 await page.waitForFunction(()=>document.querySelector('#nroDocumentoCliente').value.length>0,{},{timeout:10000});assert.equal(await page.locator('#nroDocumentoCliente').inputValue(),'00000000');
 await page.setViewportSize({width:393,height:852});await page.goto('http://localhost/nuevo/index.html#/remision',{waitUntil:'networkidle'});await page.locator('.nm-guide-hero').waitFor();await page.screenshot({path:'.tmp-order-workbook/guide-mobile.png',fullPage:true});
 const width=await page.evaluate(()=>({page:document.documentElement.scrollWidth,viewport:window.innerWidth}));assert(width.page<=width.viewport+1,JSON.stringify(width));
 assert.deepEqual(errors,[]);console.log('IMCEDEN guide: desktop/mobile rendered; local ubigeos synchronize and clear; new-guide link and router cleanup verified. No POST requests allowed.');
}finally{await browser.close();}
