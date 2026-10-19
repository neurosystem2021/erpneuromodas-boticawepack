const fs=require('fs'),assert=require('assert/strict');const {chromium}=require('../.tmp-order-workbook/node_modules/playwright');
(async()=>{const b=await chromium.launch({channel:'chrome',headless:true});try{
 const source=fs.readFileSync('src/components/ProformaPresentacion.vue','utf8');
 const template=source.slice(source.indexOf('<template>')+10,source.indexOf('</template>\n<script>'));
 const script=source.match(/<script>([\s\S]*?)<\/script>/)[1].replace(/import \{[^;]+;/,'const proposalServices=[],defaultProposalServices=()=>[],applyProposalServices=()=>({});').replace('export default','return');
 const css=source.match(/<style scoped>([\s\S]*?)<\/style>/)[1];const page=await b.newPage({viewport:{width:1000,height:700}});const errors=[];page.on('pageerror',e=>errors.push(e.message));
 await page.setContent('<style>body{font-family:Arial;padding:25px}'+css+'</style><div id="app"></div>');await page.addScriptTag({path:'node_modules/vue/dist/vue.js'});
 await page.evaluate(({template,script})=>{const component=new Function(script)();component.template=template;window.app=new Vue({data:{disabled:false,value:{tipo:'productos',alcance:'Alcance guardado',entregables:'Entrega guardada',condiciones:'Condiciones guardadas'}},components:{Presentation:component},template:'<Presentation v-model="value" :active="false" :disabled="disabled"/>'}).$mount('#app');},{template,script});
 assert.equal(await page.locator('.presentation-choice').count(),3);assert.equal(await page.locator('.presentation-choice[aria-pressed=true]').innerText().then(t=>t.includes('Productos comerciales')),true);
 await page.getByRole('button',{name:/03 Producto destacado/}).click();await page.waitForFunction(()=>window.app.value.formatoProducto==='destacado');assert.equal(await page.evaluate(()=>app.value.tipo),'productos');assert.equal(await page.evaluate(()=>app.value.alcance),'Alcance guardado');
 await page.getByRole('button',{name:/02 Sistemas/}).click();await page.waitForSelector('.proposal-assistant');assert.equal(await page.evaluate(()=>app.value.tipo),'sistemas');assert.equal(await page.evaluate(()=>app.value.formatoProducto),'catalogo');
 await page.getByRole('button',{name:/01 Productos comerciales/}).click();await page.waitForFunction(()=>!document.querySelector('.proposal-assistant'));assert.equal(await page.evaluate(()=>app.value.condiciones),'Condiciones guardadas');
 await page.evaluate(()=>app.disabled=true);await page.waitForFunction(()=>document.querySelectorAll('.presentation-choice:disabled').length===3);
 await page.screenshot({path:'output/proforma-tres-presentaciones.png'});await page.setViewportSize({width:390,height:800});assert.equal(await page.evaluate(()=>document.documentElement.scrollWidth>innerWidth),false);assert.deepEqual(errors,[]);
 console.log('PASS three accessible presentation buttons, product format switching, system text preservation, disabled state and mobile width. Isolated UI; no real data written.');
}finally{await b.close();}})().catch(e=>{console.error(e);process.exit(1)});
