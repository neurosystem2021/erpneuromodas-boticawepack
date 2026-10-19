const fs=require('fs'),assert=require('assert/strict');
const {request}=require('../.tmp-order-workbook/node_modules/playwright');
(async()=>{
 const api=await request.newContext({baseURL:'http://localhost',extraHTTPHeaders:{Cookie:'PHPSESSID='+JSON.parse(fs.readFileSync('tools/.tmp-accounting-session.json')).id}});
 try {
  const before=await(await api.get('/api/proforma-contract.php?id=18')).json();
  const response=await api.get('/api/proforma-contract.php?contrato=2&html=1');assert(response.ok());const html=await response.text();
  assert(html.includes('INFORMACIÓN DE PAGO · CUENTAS BANCARIAS'));
  assert.equal((html.match(/class="bank-accounts-image"/g)||[]).length,1);
  assert(html.includes(fs.readFileSync('resources/images/cuentas.png').toString('base64')));
  assert(html.includes(fs.readFileSync('static/images/consultor-jhon-rubrica-limpia.png').toString('base64')));
  assert(!html.includes('contract-plain'));
  const pdf=await api.get('/api/proforma-contract.php?contrato=2&descargar=1');assert(pdf.ok());assert(pdf.headers()['content-type'].includes('application/pdf'));
  fs.writeFileSync('output/pdf/contrato-200-2026-con-cuentas.pdf',await pdf.body());
  const after=await(await api.get('/api/proforma-contract.php?id=18')).json();assert.deepEqual(after.contract,before.contract);
  console.log('PASS actual contract: exact proforma bank image, clean signature, original design and unchanged saved version/terms. PDF downloaded through authenticated API. No POST requests.');
 } finally {await api.dispose();}
})().catch(e=>{console.error(e);process.exit(1)});
