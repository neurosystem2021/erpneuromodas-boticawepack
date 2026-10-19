const fs=require('fs'),assert=require('assert/strict');
const {chromium}=require('../.tmp-order-workbook/node_modules/playwright');
(async()=>{
 const browser=await chromium.launch({channel:'chrome',headless:true});
 try {
  const context=await browser.newContext({viewport:{width:1440,height:1050}});
  await context.addCookies([{name:'PHPSESSID',value:JSON.parse(fs.readFileSync('tools/.tmp-accounting-session.json')).id,domain:'localhost',path:'/'}]);
  let writes=0;
  await context.route('**/*',route=>{const req=route.request();if(new URL(req.url()).hostname!=='localhost')return route.abort();if(req.method()==='POST'&&req.url().includes('/proforma-contract.php')){writes++;return route.abort();}return route.continue();});
  const page=await context.newPage();const errors=[];page.on('pageerror',e=>errors.push(e.message));
  await page.goto('http://localhost/nuevo/index.html#/reportes/proforma/18');
  await page.getByRole('button',{name:'Generar contrato',exact:true}).click();
  await page.waitForSelector('.contract-payment');
  const selector=page.getByLabel('Presentación de las cuentas bancarias');
  assert.equal(await selector.inputValue(),'imagen');
  await page.locator('.contract-bank-preview').scrollIntoViewIfNeeded();
  await page.waitForFunction(()=>{const image=document.querySelector('.contract-bank-preview img');return image&&image.complete&&image.naturalWidth===1180;});
  await selector.selectOption('texto');await page.waitForFunction(()=>!document.querySelector('.contract-bank-preview'));
  await page.getByLabel('Cuentas y datos para el pago *').fill('Cuenta de prueba para verificar la opción escrita');
  await selector.selectOption('ambos');await page.waitForSelector('.contract-bank-preview img');
  assert.equal(await page.getByLabel('Cuentas y datos para el pago *').inputValue(),'Cuenta de prueba para verificar la opción escrita');
  await page.locator('.contract-bank-preview').screenshot({path:'output/contrato-cuentas-opciones.png'});
  await page.setViewportSize({width:390,height:844});
  assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth),false);
  assert.deepEqual(errors,[]);assert.equal(writes,0);
  console.log('PASS installed bank account image/text/both options, actual bank image loaded, mobile width and no real saves.');
 } finally {await browser.close();}
})().catch(e=>{console.error(e);process.exit(1)});
