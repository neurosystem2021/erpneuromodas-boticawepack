const fs=require('fs'),assert=require('assert');const s=fs.readFileSync('src/components/ProductosCarritoNuevo.vue','utf8');
const ctx={productos:[],$options:{filters:{redondear:v=>Number(v).toFixed(2)}}};
for(const name of ['getSubtotal','getIgv','getTotal']){const start=s.indexOf('    '+name+'(index) {')+('    '+name+'(index) {').length;const end=s.indexOf('\n    },',start);ctx[name]=new Function('Vue','return function(index){'+s.slice(start,end)+'} ')({set:(obj,k,v)=>obj[k]=v}).bind(ctx);}
ctx.productos=[{cantidad:2,precio:700,TieneIgv:0,descuento:0},{cantidad:3,precio:30,TieneIgv:0,descuento:5},{cantidad:2,precio:100,TieneIgv:1,descuento:0}];
assert.equal(ctx.getTotal(0),'1400.00');assert.equal(ctx.getTotal(1),'85.00');assert.equal(ctx.getTotal(2),'236.00');ctx.productos[0].cantidad=3;assert.equal(ctx.getTotal(0),'2100.00');console.log('Mobile totals: initial values, quantity changes, tax and discount OK');
