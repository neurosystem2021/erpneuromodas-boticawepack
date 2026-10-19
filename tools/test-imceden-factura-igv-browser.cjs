const assert=require('assert/strict'),fs=require('fs'),path=require('path');
const {chromium}=require('../.tmp-order-workbook/node_modules/playwright');
(async()=>{const browser=await chromium.launch({channel:'chrome',headless:true});try{
 const page=await browser.newPage({viewport:{width:1440,height:1050}}),errors=[];let issued=0;
 page.on('pageerror',e=>errors.push(e.message));
 await page.addInitScript(()=>{sessionStorage.setItem('User','caja-prueba');sessionStorage.setItem('IdPerfil','16');sessionStorage.setItem('IdAlmacen','1');});
 const types=[{IdTipoDoc:6,TipoDoc:'FACTURA ELEC.',CodSunat:'01',TieneIgv:'0',CodigoIgv:'20',EsElectronico:1},{IdTipoDoc:7,TipoDoc:'BOLETA ELEC.',CodSunat:'03',TieneIgv:0,CodigoIgv:'20',EsElectronico:1},{IdTipoDoc:8,TipoDoc:'FACTURA/IGV ELEC.',CodSunat:'01',TieneIgv:'1',CodigoIgv:'10',EsElectronico:1}];
 await page.route('**/*',async r=>{const req=r.request(),u=new URL(req.url());if(u.hostname!=='localhost')return r.abort();
  if(process.env.IMCEDEN_PREVIEW_DIST==='1'&&u.pathname.startsWith('/nuevo/')){const base=path.resolve('dist'),file=path.resolve(base,u.pathname.slice(7));if(!file.startsWith(base+path.sep)||!fs.existsSync(file))return r.abort();return r.fulfill({path:file});}
  if(!u.pathname.startsWith('/api/'))return r.continue();if(req.method()!=='GET'){issued++;return r.abort();}let data=[];const p=u.pathname.replace(/\/+/g,'/');
  if(p.includes('/usuarios/'))data={Usuario:'caja-prueba',IdUsuarioPerfil:16,IdAlmacen:1,Almacen:'PRINCIPAL',DescuentoActivo:1,IdDocVentaPuntoVenta:1,PuntoVenta:'PRINCIPAL'};
  else if(p.endsWith('/cliente')||p.includes('/cliente/'))data={IdCliente:1,Cliente:'CLIENTE DE PRUEBA',DniRuc:'20000000001',Puntos:0};
  else if(p.endsWith('/ventas/tipos'))data=types;
  else if(p.endsWith('/ventas/numero'))data={serie:u.searchParams.get('idTipoDoc')==='8'?'F104':'F004',numero:43};
  else if(p.includes('/puntoventa/id/'))data={IdDocVentaPuntoVenta:1,PuntoVenta:'PRINCIPAL'};
  else if(p.includes('/almacenes/id/'))data={IdAlmacen:1,Almacen:'PRINCIPAL'};
  else if(p.endsWith('/proformas/count'))data={total:0};
  return r.fulfill({contentType:'application/json',body:JSON.stringify(data)});
 });
 await page.goto('http://localhost/nuevo/index.html#/ventas');await page.waitForFunction(()=>{const find=v=>{if(v.$data&&'ventaActiva'in v.$data&&Array.isArray(v.ventas))return v;for(const c of v.$children||[]){const r=find(c);if(r)return r;}};window.pos=find(document.querySelector('#app').__vue__);return pos&&pos.ventas.length;});await page.waitForTimeout(250);
 await page.evaluate(type=>{pos.ventas[0].productos=[{IdProducto:1,Producto:'PRUEBA IGV INCLUIDO',precio:118,cantidad:1,descuento:0,PrecioCosto:50,ProductoPresentacion:'UNIDAD'}];pos.setTipoVenta(type);},types[2]);await page.waitForFunction(()=>pos.ventas[0].Serie==='F104');
 const values=()=>page.evaluate(()=>({base:pos.getBaseGravada(),tax:pos.getIgvVenta(),total:pos.getTotal(),type:pos.ventas[0].tipoVenta,series:pos.ventas[0].Serie}));
 let v=await values();assert.equal(v.base,'100.00');assert.equal(v.tax,'18.00');assert.equal(v.total,'118.00');assert.equal(v.type.IdTipoDoc,8);assert.equal(v.type.CodigoIgv,'10');assert.equal(v.series,'F104');assert.match(await page.locator('.pos-tax-igv').textContent(),/18\.00/);
 await page.evaluate(()=>{pos.ventas[0].productos[0].cantidad=2;pos.ventas[0].productos[0].descuento=23.6;});v=await values();assert.equal(v.base,'180.00');assert.equal(v.tax,'32.40');assert.equal(v.total,'212.40');
 await page.evaluate(()=>{const second=JSON.parse(JSON.stringify(pos.ventas[0]));second.tipoVenta={IdTipoDoc:6,TieneIgv:'0',CodSunat:'01'};pos.ventas.push(second);});assert.deepEqual(await page.evaluate(()=>[pos.ventaConIgv(0),pos.ventaConIgv(1)]),[true,false]);
 await page.evaluate(type=>pos.setTipoVenta(type),types[0]);await page.waitForFunction(()=>pos.ventas[0].Serie==='F004');assert.equal(await page.locator('.pos-tax-igv').count(),0);assert.equal(await page.evaluate(()=>pos.tieneIgv[0]),false);assert.equal((await values()).total,'212.40');
 assert.equal(issued,0);assert.deepEqual(errors,[]);console.log('PASS compiled IMCEDEN POS: FACTURA/IGV ELEC. selection, F104 series, S/118 = base100 + IGV18, quantities and discounts, no extra tax charge, per-sale isolation and original non-VAT invoice unchanged. All API calls mocked; no invoice issued or sent to SUNAT.');
}finally{await browser.close();}})().catch(e=>{console.error(e);process.exit(1);});
