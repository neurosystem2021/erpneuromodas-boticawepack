import {chromium} from 'playwright';
import assert from 'node:assert/strict';
const browser=await chromium.launch({channel:'chrome',headless:true});
try{
 const page=await browser.newPage({viewport:{width:1600,height:1000}});const errors=[];page.on('pageerror',e=>errors.push(e.message));
 await page.route('**/api/index.php/**',async route=>{if(route.request().method()!=='GET')return route.abort();return route.continue();});
 await page.goto('http://localhost/nuevo/index.html#/reimpresion',{waitUntil:'networkidle'});
 await page.locator('.document-actions').first().waitFor();
 const row=page.locator('.document-actions').first();assert.equal(await row.locator('.action-button').count(),6);
 assert.equal(await row.locator('.whatsapp-button').evaluate(el=>getComputedStyle(el).backgroundColor),'rgb(37, 211, 102)');
 assert.match(await row.locator('.pdf-button').getAttribute('href'),/imprimirpdf\/\d+\?descargar=1/);
 await row.locator('.whatsapp-button').click();await page.getByText('Enviar comprobante por WhatsApp',{exact:true}).waitFor();await page.getByRole('button',{name:'Cancelar',exact:true}).click();
 await row.locator('.email-button').click();await page.getByText('Enviar comprobante por correo',{exact:true}).waitFor();await page.getByRole('button',{name:'Cancelar',exact:true}).click();await page.locator('.swal2-container').waitFor({state:'hidden'});
 await page.screenshot({path:'.tmp-order-workbook/reprint-desktop.png',fullPage:true});
 const [guide]=await Promise.all([page.waitForEvent('popup'),row.locator('.guide-button').click()]);await guide.waitForURL(/#\/remision\/ver\//);await guide.close();
 await page.setViewportSize({width:393,height:852});await page.screenshot({path:'.tmp-order-workbook/reprint-mobile.png',fullPage:true});assert(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+1));
 assert.deepEqual(errors,[]);console.log('Six document actions, green WhatsApp, WhatsApp/email dialogs cancelled, guide route and mobile layout verified. No messages sent.');
}finally{await browser.close();}
