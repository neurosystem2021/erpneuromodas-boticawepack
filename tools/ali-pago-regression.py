from pathlib import Path
r=Path(__file__).resolve().parents[1]
p=r/'tools/test-ali-checkout.cjs';s=p.read_text(encoding='utf-8')
s=s.replace('let saves=0,code=', 'let prepares=0,confirms=0,saves=0,code=')
s=s.replace('ultimaVenta:0,guardarVenta:', "ultimaVenta:0,aliPrepareCheckout:async()=>{prepares++;return {pending:'review'}},aliConfirmCheckout:async()=>{confirms++;return {pending:''}},guardarVenta:")
s=s.replace("await assert.rejects(()=>run.call(ctx,{type:'pay'}));await assert.rejects(()=>run.call(ctx,{type:'confirm'}));", "await run.call(ctx,{type:'pay'});await run.call(ctx,{type:'confirm'});assert.equal(prepares,1);assert.equal(confirms,1);")
s=s.replace('automated payment held for confirmation/idempotency phase','payment delegates to prepared/idempotent checkout and never directly calls manual save');p.write_text(s,encoding='utf-8')
p=r/'tools/deploy-ali-pago.ps1';s=p.read_text(encoding='utf-8').replace('No PHP header, fiscal service, certificate or database changes.','No PHP header, fiscal service or certificate changes. The additive operation-table migration is installed separately.');p.write_text(s,encoding='utf-8')
print('Current checkout dispatcher and deployment notes aligned with ALIPAGO.')
