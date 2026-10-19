const fs=require('fs'),assert=require('assert/strict');const {request}=require('../.tmp-order-workbook/node_modules/playwright');
(async()=>{const api=await request.newContext({baseURL:'http://localhost',extraHTTPHeaders:{Cookie:'PHPSESSID='+JSON.parse(fs.readFileSync('tools/.tmp-accounting-session.json')).id}});try{
 const first=await api.get('/api/proforma-contract.php?id=18');const data=await first.json();assert(first.ok(),JSON.stringify(data));assert.equal(data.numeroContrato,'200-2026');assert.equal(data.numeroAsignado,false);
 const second=await api.get('/api/proforma-contract.php?id=17');assert.equal((await second.json()).numeroContrato,'200-2026');
 const invalid=await api.post('/api/proforma-contract.php',{headers:{'X-Contract-CSRF':data.csrf},data:{IdProforma:18,sourceHash:data.sourceHash}});assert.equal(invalid.status(),422);
 const after=await api.get('/api/proforma-contract.php?id=18');const current=await after.json();assert.equal(current.numeroContrato,'200-2026');assert.equal(current.contract.id,data.contract.id);assert.equal(current.contract.version,data.contract.version);
 if(data.contract){const old=await api.get(data.contract.url+'&html=1');assert(old.ok());const html=await old.text();assert(!html.includes('ANEXO A'));assert(html.includes('CONTRATO DE LICENCIA Y SERVICIOS DE SISTEMAS'));}
 console.log('PASS installed next number 200, previews and failed validation consume no sequence, existing contract version unchanged and original rendering preserved. No contract or payment saved.');
}finally{await api.dispose();}})().catch(e=>{console.error(e);process.exit(1)});
