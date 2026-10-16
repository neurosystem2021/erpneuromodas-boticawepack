const fs=require('fs'),assert=require('assert/strict'),{webcrypto}=require('crypto');
const source=fs.readFileSync('src/utils/aliCheckout.js','utf8').replace('export function','function').replace('export default','return');
const mixin=new Function('crypto',source)(webcrypto);
const sale={productos:[],totalCart:10};
const snapshot=new Function(fs.readFileSync('src/utils/aliCheckout.js','utf8').split('async function signature')[0].replace('export function','function')+';return aliPaymentSnapshot')();
(async()=>{
 const bytes=await webcrypto.subtle.digest('SHA-256',new TextEncoder().encode(snapshot(sale)));
 const signature=Buffer.from(bytes).toString('hex');let requests=0,finish=0;
 const ctx={ventas:[sale],index:0,aliCheckoutReview:{sale,signature,operation:'test'},inProgress:false,aliCheckoutSubmitting:false,aliCheckoutRequest:async()=>{requests++;await new Promise(r=>setTimeout(r,15));return {insertId:123}},aliFinishCheckout(){finish++}};
 const first=mixin.methods.aliConfirmCheckout.call(ctx,'csrf');
 await assert.rejects(()=>mixin.methods.aliConfirmCheckout.call(ctx,'csrf'));
 await first;assert.equal(requests,1);assert.equal(finish,1);assert.equal(ctx.inProgress,false);
 ctx.aliCheckoutReview.uncertain=true;await assert.rejects(()=>mixin.methods.aliConfirmCheckout.call(ctx,'csrf'));assert.equal(requests,1);
 ctx.aliCheckoutReview.uncertain=false;sale.totalCart=11;await assert.rejects(()=>mixin.methods.aliConfirmCheckout.call(ctx,'csrf'));assert.equal(requests,1);assert.equal(ctx.inProgress,false);
 console.log('PASS simultaneous confirmations issue one request; uncertain or changed sales cannot submit; locks release. No real sales.');
})().catch(e=>{console.error(e);process.exit(1)});
