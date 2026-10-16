const assert = require('assert');
const load = require('./load-source-module');
const {partitionCashMovements, sumMovementAmounts, groupBankMovements} = load('src/utils/cashAccountMovements.js');
const {cashClosingTicket} = load('src/utils/cashClosingTicket.js');
const movements = [{IdCuenta:'2',Cuenta:'Banco BCP',Importe:'1000'}, {IdCuenta:2,Cuenta:'Banco BCP',Importe:'3000'}, {IdCuenta:1,Importe:'25'}, {IdCuenta:1,Importe:'90',Anulado:1}];
const split = partitionCashMovements(movements);
const groups=load('src/utils/cashAccountMovements.js').groupBankMovements(split.bank.concat([{IdCuenta:3,Cuenta:'Bancos Varios',Importe:1900}]));
assert.deepEqual(groups.map(group=>group.total),[4000,1900]);
assert.deepEqual(groups.map(group=>group.account),['Banco BCP','Bancos Varios']);
assert.equal(sumMovementAmounts(split.cash),25);
assert.equal(sumMovementAmounts(split.bank),4000);
assert.equal(2932.55-sumMovementAmounts(partitionCashMovements(movements.slice(0,2)).cash),2932.55);
assert.equal(sumMovementAmounts(partitionCashMovements([{IdCuenta:2,Importe:500}]).cash),0);
const payrollPayments=[
  {IdCajaBanco:6,IdCuenta:1,Cuenta:'Caja Principal',Concepto:'PLANILLA ADELANTO - PERSONAL',Importe:500},
  {IdCajaBanco:7,IdCuenta:2,Cuenta:'Banco BCP',Concepto:'PLANILLA ADELANTO - PERSONAL',Importe:300},
  {IdCajaBanco:8,IdCuenta:3,Cuenta:'Bancos Varios',Concepto:'PLANILLA SUELDO - PERSONAL',Importe:200}
];
const payrollSplit=partitionCashMovements(payrollPayments);
assert.equal(sumMovementAmounts(payrollSplit.cash),500);
assert.equal(sumMovementAmounts(payrollSplit.bank),500);
assert.deepEqual(groupBankMovements(payrollSplit.bank).map(x=>x.account),['Banco BCP','Bancos Varios']);
assert.equal(payrollSplit.cash.concat(payrollSplit.bank).filter(x=>/^PLANILLA\s/i.test(x.Concepto)).length,3);
assert.equal(1000-sumMovementAmounts(payrollSplit.cash),500);
const html=cashClosingTicket({sales:[],company:{},grandTotal:2932.55,bankMovements:split.bank.map(item=>Object.assign({},item,{direction:'Salida'}))});
assert(html.includes('Banco BCP'));
assert(html.includes('MOVIMIENTOS BANCARIOS (REFERENCIA)'));
assert(html.includes('TOTAL FINAL DE CAJA</span><b>S/ 2932.55'));
console.log('Bank references excluded from cash; cash movements and cancelled amounts verified.');
