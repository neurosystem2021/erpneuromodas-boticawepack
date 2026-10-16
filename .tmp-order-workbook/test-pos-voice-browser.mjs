import {chromium} from 'playwright';import assert from 'node:assert/strict';
const browser=await chromium.launch({channel:'chrome',headless:true});
try{
 const page=await browser.newPage({viewport:{width:1400,height:950}});
 await page.addInitScript(()=>{
  sessionStorage.setItem('User','superadmin');
  class FakeSpeech {constructor(){window.testSpeech=this;}start(){this.onstart&&this.onstart();}stop(){this.onend&&this.onend();}abort(){}say(text,final){const row=[{transcript:text}];row.isFinal=final;this.onresult&&this.onresult({results:[row]});}end(){this.onend&&this.onend();}}
  window.SpeechRecognition=window.webkitSpeechRecognition=FakeSpeech;
 });
 await page.route('**/api/index.php/**',async route=>{if(route.request().method()!=='GET')return route.abort();return route.continue();});
 await page.goto('http://localhost/nuevo/index.html#/ventas',{waitUntil:'networkidle'});
 const mic=page.locator('.pos-search-voice').first(),input=page.locator('#txtAddProduct').first();await mic.waitFor();
 await mic.click();await page.evaluate(()=>window.testSpeech.say('camara',false));await input.waitFor();await page.waitForFunction(()=>document.querySelector('#txtAddProduct').value==='camara');
 await page.evaluate(()=>{window.testSpeech.say('camara de seguridad',true);window.testSpeech.end();});
 await page.waitForFunction(()=>document.querySelector('#txtAddProduct').value==='camara de seguridad');await page.waitForTimeout(800);assert.equal(await input.inputValue(),'camara de seguridad');
 await input.blur();await input.focus();await page.waitForTimeout(300);assert.equal(await input.inputValue(),'camara de seguridad');
 await mic.click();await page.evaluate(()=>{window.testSpeech.say('mouse',true);window.testSpeech.end();});await page.waitForFunction(()=>document.querySelector('#txtAddProduct').value==='mouse');await page.waitForTimeout(800);assert.equal(await input.inputValue(),'mouse');
 await mic.click();await page.evaluate(()=>window.testSpeech.end());assert.equal(await input.inputValue(),'mouse');
 await page.setViewportSize({width:393,height:852});await mic.click();await page.evaluate(()=>window.testSpeech.say('teclado',false));await page.waitForFunction(()=>document.querySelector('#txtAddProduct').value==='teclado');await page.waitForFunction(()=>document.querySelector('.pos-search-voice').classList.contains('pos-voice-listening'));await page.evaluate(()=>window.testSpeech.end());await page.waitForTimeout(500);assert.equal(await input.inputValue(),'teclado');
 await page.screenshot({path:'.tmp-order-workbook/pos-voice-fixed.png',fullPage:true});console.log('Browser regression: interim/final text retained after focus and rerender, second search, silent session, mobile text and active microphone verified with simulated speech. No sales saved.');
}finally{await browser.close();}
