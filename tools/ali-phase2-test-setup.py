from pathlib import Path
p=Path('src/components/AliAssistant.vue');s=p.read_text(encoding='utf-8')
old="if(this._activeAction==='search'&&parseAliCommand(raw,{field:this.activeField}).type==='search'){this._dictationQueue=[];this.execute({type:'cancelSearch'}).catch(()=>{})}"
new="if(this._activeAction==='search'&&parseAliCommand(raw,{field:this.activeField}).type==='search'){this._runVersion=(this._runVersion||0)+1;this.busy=false;this._dictationQueue=[];this.execute({type:'cancelSearch'}).catch(()=>{});this.command=raw;this.run();return}"
assert old in s;s=s.replace(old,new,1);p.write_text(s,encoding='utf-8')
for name in ['test-ali-responsive.mjs','test-ali-voice-variety.mjs']:
 p=Path('.tmp-order-workbook')/name;s=p.read_text(encoding='utf-8-sig');needle="  if(req.method()==='POST')" if name=='test-ali-responsive.mjs' else "  if(request.method()==='POST')"
 s=s.replace(needle,"  if("+('url.pathname' if name=='test-ali-responsive.mjs' else 'request.url()')+".endsWith('/ali/autorizar'))return route.fulfill({json:{authorized:true}});\n"+needle,1);p.write_text(s,encoding='utf-8')
p=Path('tools/test-ali-checkout.cjs');s=p.read_text(encoding='utf-8');s=s.replace("await run.call(ctx,{type:'pay'});assert.equal(saves,1);await assert.rejects(()=>run.call(ctx,{type:'pay'}));assert.equal(saves,1);", "await assert.rejects(()=>run.call(ctx,{type:'pay'}));await assert.rejects(()=>run.call(ctx,{type:'confirm'}));assert.equal(saves,0);")
s=s.replace('direct payment and duplicate guard.', 'automated payment held for confirmation/idempotency phase.');p.write_text(s,encoding='utf-8')
