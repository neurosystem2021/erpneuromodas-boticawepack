const fs=require('fs'),path=require('path'),assert=require('assert');
const {chromium}=require('../../../.tmp-order-workbook/node_modules/playwright');
(async()=>{
 const out=path.resolve('output/concretera/dual-brand');fs.mkdirSync(out,{recursive:true});
 const browser=await chromium.launch({channel:'chrome',headless:true});
 const page=await browser.newPage({viewport:{width:1920,height:1080}});
 await page.route('**/*',async route=>{const u=new URL(route.request().url());if(u.hostname!=='localhost'&&u.protocol!=='data:')return route.abort();if(u.pathname==='/views/__brand_preview.php')return route.fulfill({contentType:'text/html',body:fs.readFileSync('output/wuanukomix-pos-imceden-fixture.html','utf8').replaceAll('http:///','http://localhost/').replace(/<iframe\b[\s\S]*?<\/iframe>/gi,'')});return route.continue();});
 await page.goto('http://localhost/views/__brand_preview.php',{waitUntil:'domcontentloaded'});
 await page.locator('.erp-nav-logo').waitFor();
 await page.waitForFunction(()=>Array.from(document.querySelectorAll('#navBar img')).every(i=>i.complete));
 const header=await page.locator('#navBar').evaluate(n=>Array.from(n.querySelectorAll('img')).filter(i=>i.getBoundingClientRect().width>0).map(i=>({src:i.getAttribute('src'),width:i.naturalWidth,x:i.getBoundingClientRect().x,fit:getComputedStyle(i).objectFit})));
 console.log(header);assert(header.some(i=>i.src.includes('transparente')&&i.width===1486));assert(header.some(i=>i.src.includes('neurosystem-menu-original')&&i.width===600));
 assert(header.find(i=>i.src.includes('transparente')).x<header.find(i=>i.src.includes('neurosystem-menu-original')).x);
 await page.locator('#navBar').screenshot({path:path.join(out,'cabecera-1920.png')});
 const brandBounds=await page.locator('.navbar-brand').boundingBox();const firstMenuBounds=await page.locator('#myNavbar>ul>li:visible').first().boundingBox();assert(firstMenuBounds.x>=brandBounds.x+brandBounds.width,'El menÃº se superpone al logo');
 await page.setViewportSize({width:1366,height:768});assert(await page.locator('.wuanuko-mobile-neuro').isVisible());console.log(await page.locator('#navBar .navbar-header').evaluate(n=>({width:n.getBoundingClientRect().width,display:getComputedStyle(n).display,html:n.outerHTML.slice(-220)})));await page.locator('#navBar').screenshot({path:path.join(out,'cabecera-1366.png')});
 await page.goto('http://localhost/',{waitUntil:'domcontentloaded'});await page.waitForFunction(()=>document.querySelector('.brand-plate img')?.naturalWidth===1486);
 await page.waitForTimeout(1800);await page.locator('.brand-plate').screenshot({path:path.join(out,'login-logo.png')});
 const tickets=[];
 for(const [company,type] of [[2,'01'],[3,'03']]){
  await page.route('**/__ticket_preview',r=>r.fulfill({contentType:'text/html',body:fs.readFileSync(`output/concretera/logo-formatos/ticket-${company}-${type}.html`,'utf8')}));
  await page.goto('http://localhost/__ticket_preview',{waitUntil:'domcontentloaded'});
  await page.waitForFunction(()=>Array.from(document.images).filter(i=>i.src.includes('logo')).every(i=>i.complete));
  const logo=await page.locator('img[src*="wuanuko-mix-logo-transparente"]').evaluate(i=>({width:i.naturalWidth,fit:getComputedStyle(i).objectFit}));assert.equal(logo.width,1486);assert.equal(logo.fit,'contain');
  const text=await page.locator('body').innerText();assert(text.includes(company===2?'20573152531':'20602664989'));
  await page.locator('.ticket').screenshot({path:path.join(out,`ticket-${company}.png`)});tickets.push({company,type,logo});
  await page.unroute('**/__ticket_preview');
 }
 // SPA adapter must replace legacy images and leave Neurosystem alone.
 await page.route('**/__adapter_preview',r=>r.fulfill({contentType:'text/html',body:'<img id="old" src="/resources/images/banner-proforma.jpg"><img id="neuro" src="/resources/images/neurologo.png"><script src="/resources/js/wuanuko-branding.js"></script>'}));
 await page.goto('http://localhost/__adapter_preview',{waitUntil:'load'});
 assert((await page.locator('#old').getAttribute('src')).includes('transparente'));assert.equal(await page.locator('#neuro').getAttribute('src'),'/resources/images/neurologo.png');
 fs.writeFileSync(path.join(out,'browser-verification.json'),JSON.stringify({header,tickets,adapter:true},null,2));await browser.close();console.log('PASS cabecera, login, dos tickets y adaptador SPA');
})().catch(e=>{console.error(e);process.exit(1)});



