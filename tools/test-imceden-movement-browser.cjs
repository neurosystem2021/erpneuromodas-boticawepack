const assert=require('assert/strict'),fs=require('fs'),path=require('path');
const {chromium}=require('../.tmp-order-workbook/node_modules/playwright');
(async()=>{const browser=await chromium.launch({channel:'chrome',headless:true});try{
 const page=await browser.newPage({viewport:{width:1440,height:1000}});const errors=[],sent=[];let reply={status:422,data:{error:'Stock insuficiente. Disponible: 2.'}},delay=0;
 page.on('pageerror',e=>errors.push(e.message));
 await page.addInitScript(()=>{sessionStorage.setItem('User','caja-prueba');sessionStorage.setItem('IdPerfil','16');sessionStorage.setItem('IdAlmacen','1');});
 await page.route('**/*',async route=>{const req=route.request(),url=new URL(req.url());if(url.hostname!=='localhost')return route.abort();
  if(process.env.IMCEDEN_PREVIEW_DIST==='1'&&url.pathname.startsWith('/nuevo/')){const base=path.resolve('dist'),file=path.resolve(base,url.pathname.slice(7));if(!file.startsWith(base+path.sep)||!fs.existsSync(file))return route.abort();return route.fulfill({path:file});}
  if(!url.pathname.startsWith('/api/'))return route.continue();const p=url.pathname.replace(/\/+/g,'/');let data=[];
  if(req.method()==='POST'&&p.endsWith('/movimientos')){sent.push(req.postDataJSON());if(delay)await new Promise(r=>setTimeout(r,delay));if(reply.abort)return route.abort();return route.fulfill({status:reply.status,contentType:'application/json',body:JSON.stringify(reply.data)});}
  if(p.includes('/almacenes/id/'))data={IdAlmacen:1,Almacen:'ORIGEN'};
  if(p.endsWith('/movimientos/numero'))data={NuevoNumero:1};
  if(p.includes('/usuarios/'))data={Usuario:'caja-prueba',IdAlmacen:1};
  return route.fulfill({status:200,contentType:'application/json',body:JSON.stringify(data)});
 });
 await page.goto('http://localhost/nuevo/index.html#/');
 await page.waitForFunction(()=>{const find=v=>{if(v.$data&&'movimiento'in v.$data&&'errorGuardado'in v.$data)return v;for(const c of v.$children||[]){const r=find(c);if(r)return r;}};window.mov=find(document.querySelector('#app').__vue__);return window.mov&&mov.almacenActual;});
 await page.evaluate(()=>{mov.$swal=async()=>({isConfirmed:true});});
 const seed=async(kind=2)=>page.evaluate(kind=>{mov.movimiento={EsCredito:0,alterarProductos:kind===2?0:1,Moneda:'PEN',FechaPeriodoTributario:'202610',MovimientoFecha:'2026-10-18T10:00:00',FechaStock:'2026-10-18',TipoCambio:1,proveedor:{IdProveedor:kind===2?0:1,Proveedor:'PROVEEDOR DE PRUEBA'},movimientoTipo:{IdMovimientoTipo:kind===2?6:1,Tipo:kind,TipoMovimiento:kind===2?'TRASLADOS':'COMPRA FACTURA'},almacenOrigen:{IdAlmacen:kind===2?1:-1,Almacen:'ORIGEN'},almacenDestino:{IdAlmacen:2,Almacen:'DESTINO'},Serie:kind===2?'TR':'F001',Numero:10,user:'caja-prueba'};mov.$store.state.cart.productos=[{IdProducto:1,Producto:'PRODUCTO DE PRUEBA',cantidad:3,precio:12,total:36,subtotal:36,igv:0,nuevoPrecioContado:15,stock:0,Descripcion:'Nota conservada'}];},kind);
 const save=()=>page.evaluate(async()=>{await mov.guardarMovimiento({preventDefault(){}});});
 const retained=async()=>{const state=await page.evaluate(()=>({count:mov.productos.length,desc:mov.productos[0]&&mov.productos[0].Descripcion,series:mov.movimiento.Serie,busy:mov.inProgress}));assert.equal(state.count,1);assert.equal(state.desc,'Nota conservada');assert.equal(state.busy,false);assert.ok(state.series);assert.ok(await page.locator('[role=alert]').first().isVisible());};
 await seed();await save();assert.equal(sent.length,1);assert.equal(sent[0].movimiento.almacenDestino.IdAlmacen,2);assert.equal(sent[0].productos[0].stock,0);assert.match(await page.locator('[role=alert]').first().textContent(),/Stock insuficiente/);await retained();
 reply={status:500,data:{error:'No se pudo guardar el detalle. Falta el dato requerido Descripcion.',reference:'PRUEBA123'}};await seed(0);await save();await retained();assert.match(await page.locator('[role=alert]').first().textContent(),/Descripcion.*PRUEBA123/);
 reply={status:200,data:{}};await save();await retained();assert.match(await page.locator('[role=alert]').first().textContent(),/no confirmó/);
 reply={abort:true};await save();await retained();assert.match(await page.locator('[role=alert]').first().textContent(),/No se recibió confirmación/);
 const before=sent.length;await seed();await page.evaluate(()=>{mov.movimiento.almacenDestino.IdAlmacen=-1;});await save();assert.equal(sent.length,before);assert.match(await page.locator('[role=alert]').first().textContent(),/almacén de destino/);
 await seed();await page.evaluate(()=>{mov.productos[0].cantidad=0;});await save();assert.equal(sent.length,before);assert.match(await page.locator('[role=alert]').first().textContent(),/cantidad/);
 reply={status:200,data:{hash:'1792300000123456',ok:true}};delay=150;await seed();await page.evaluate(async()=>{await Promise.all([mov.guardarMovimiento({preventDefault(){}}),mov.guardarMovimiento({preventDefault(){}})]);});assert.equal(sent.length,before+1);
 assert.deepEqual(await page.evaluate(()=>({count:mov.productos.length,last:mov.ultimoMovimiento,busy:mov.inProgress,error:mov.errorGuardado})),{count:0,last:'1792300000123456',busy:false,error:''});
 assert.deepEqual(errors,[]);console.log('PASS compiled inventory UI: transfer with stale preview reaches authoritative server; purchase and transfer failures show persistent specific errors; all inputs retained; missing confirmation/network warning; invalid destination/quantity blocked; double submission prevented; form cleared only on confirmed success. All API calls mocked, no real stock or purchases changed.');
}finally{await browser.close();}})().catch(e=>{console.error(e);process.exit(1);});
