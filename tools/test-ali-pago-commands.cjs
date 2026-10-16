const fs=require('fs'),assert=require('assert/strict');
const source=fs.readFileSync('src/utils/aliCommands.js','utf8').replace(/export /g,'');const parse=new Function(source+';return parseAliCommand;')();
for(const phrase of ['ALIPAGO','alipago','Ali pago','ALI pagar','ALI paga'])for(const field of ['barcode','search','payment',''])assert.equal(parse(phrase,{field}).type,'pay',phrase+' in '+field);
for(const phrase of ['confirmar pago','ALI confirmar pago','confirmo el pago','confirmar ALIPAGO'])assert.equal(parse(phrase).type,'confirm');
assert.notEqual(parse('el cliente dijo alipago').type,'pay');assert.notEqual(parse('no confirmar pago').type,'confirm');assert.equal(parse('ALI silencio').type,'stopAssistant');
console.log('PASS exact ALIPAGO/ALI pago aliases override all active fields, explicit confirmation only, quoted phrases do not trigger a payment.');
