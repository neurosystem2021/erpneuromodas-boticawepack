const assert=require('assert/strict'),fs=require('fs'),path=require('path');
const {chromium}=require('../.tmp-order-workbook/node_modules/playwright');
(async()=>{const browser=await chromium.launch({channel:'chrome',headless:true});try{
 const page=await browser.newPage({viewport:{width:1440,height:1000}});let closed=false,newTurn=false;const posts=[],errors=[];
 page.on('pageerror',e=>errors.push(e.message));page.on('dialog',dialog=>dialog.accept());
 await page.addInitScript(()=>{sessionStorage.setItem('User','caja-prueba');sessionStorage.setItem('IdPerfil','16');sessionStorage.setItem('IdAlmacen','1');});
 await page.route('**/*',async route=>{const request=route.request(),url=new URL(request.url());if(url.hostname!=='localhost')return route.abort();if(process.env.IMCEDEN_PREVIEW_DIST==='1'&&url.pathname.startsWith('/nuevo/')){const base=path.resolve('dist'),file=path.resolve(base,url.pathname.slice('/nuevo/'.length));if(!file.startsWith(base+path.sep)||!fs.existsSync(file))return route.abort();return route.fulfill({path:file});}if(!url.pathname.startsWith('/api/'))return route.continue();let data=[];
  if(url.pathname.includes('/usuarios/'))data={Usuario:'caja-prueba',NombreUsuario:'Caja de prueba',IdUsuarioPerfil:16,IdAlmacen:1};
  else if(url.pathname.endsWith('/cuentas'))data=[{IdCuenta:1,Cuenta:'CAJA PRINCIPAL'},{IdCuenta:2,Cuenta:'BANCO BCP'}];
  else if(url.pathname.includes('/tienda/'))data={RAZONSOCIAL:'IMCEDEN · VERIFICACIÓN'};
  else if(url.pathname.endsWith('/cierrecaja')&&request.method()==='POST'){posts.push(request.postDataJSON());closed=true;data={idCierraCaja:999999,idTurno:1};}
  else if(url.pathname.endsWith('/cierrecaja/ingresos')){if(!closed)data=url.searchParams.get('soloBancos')==='1'?[{IdCajaBanco:419,IdCuenta:2,Cuenta:'BANCO BCP',FechaDoc:'2026-10-16 09:05:17',Concepto:'Referencia bancaria de prueba',Importe:1017.57,Anulado:0}]:[{IdCajaBanco:374,IdCuenta:1,FechaDoc:'2026-10-14 13:35:56',Concepto:'Vale ingreso anterior de prueba',Importe:69.4,Anulado:0},{IdCajaBanco:418,IdCuenta:1,FechaDoc:'2026-10-16 08:39:08',Concepto:'Vale ingreso de prueba',Importe:9.9,Anulado:0}];else if(newTurn&&url.searchParams.get('soloBancos')!=='1')data=[{IdCajaBanco:424,IdCuenta:1,FechaDoc:'2026-10-18 10:00:00',Concepto:'Movimiento del nuevo turno',Importe:25,Anulado:0}];}
  else if(url.pathname.endsWith('/cierrecaja/salidas')&&!closed)data=url.searchParams.get('soloBancos')==='1'?[]:[{IdCajaBanco:375,IdCuenta:1,FechaDoc:'2026-10-14 14:34:35',Concepto:'Compra de prueba',Importe:50,Anulado:0},{IdCajaBanco:376,IdCuenta:1,FechaDoc:'2026-10-14 20:37:26',Concepto:'Gasolina de prueba',Importe:10,Anulado:0}];
  await route.fulfill({status:200,contentType:'application/json',body:JSON.stringify(data)});
 });
 await page.goto('http://localhost/nuevo/index.html#/reportes/cierrecaja');
 await page.waitForFunction(()=>{const find=v=>{if(v.$data&&'idCuentaCajaPrincipal'in v.$data)return v;for(const child of v.$children||[]){const x=find(child);if(x)return x;}};window.cashView=find(document.querySelector('#app').__vue__);return window.cashView&&cashView.ingresos.length===2&&cashView.salidas.length===2&&cashView.ingresosTarjeta.length===1;});
 assert.equal(await page.evaluate(()=>Math.round(cashView.totalIngresos*100)),7930);assert.equal(await page.evaluate(()=>cashView.totalSalidas),60);
 await page.evaluate(()=>{cashView.turno=1;});await page.getByRole('button',{name:'Cerrar Caja',exact:true}).click();
 await page.waitForFunction(()=>cashView.ingresos.length===0&&cashView.salidas.length===0&&cashView.ingresosTarjeta.length===0&&cashView.salidasTarjeta.length===0);
 assert.equal(posts.length,1);assert.equal(posts[0].user,'caja-prueba');assert.equal(posts[0].idCuenta,1);
 fs.mkdirSync('output',{recursive:true});await page.screenshot({path:'output/imceden-cierre-siguiente-turno.png',fullPage:true});
 newTurn=true;await page.evaluate(()=>cashView.loadData());await page.waitForFunction(()=>cashView.ingresos.length===1&&cashView.ingresos[0].IdCajaBanco===424);assert.equal(await page.evaluate(()=>cashView.totalIngresos),25);assert.deepEqual(errors,[]);
 console.log('PASS published IMCEDEN frontend: previous cash and bank movements loaded, selected user sent, all lists refreshed empty after simulated close, only new movement in following turn. All API requests mocked; no real close sent.');
}finally{await browser.close();}})().catch(error=>{console.error(error);process.exit(1);});
