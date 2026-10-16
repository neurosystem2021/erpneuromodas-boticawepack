export function createDescriptionDictation(options) {
  const recognition = new options.Recognition();
  const later = options.setTimeout || setTimeout, cancel = options.clearTimeout || clearTimeout;
  let active=false, timer=null, restart=null, base=options.initialText || '', segment='';
  const text=() => [base.trim(),segment.trim()].filter(Boolean).join(' ');
  const clear=() => { if(timer!==null) cancel(timer); timer=null; };
  const stop=() => { active=false; clear(); if(restart!==null) cancel(restart); options.onState(false); try { recognition.stop(); } catch(error) {} };
  const wait=() => { clear(); if(active) timer=later(stop,10000); };
  recognition.lang='es-PE'; recognition.continuous=true; recognition.interimResults=true;
  recognition.onstart=() => { if(timer===null) wait(); };
  recognition.onspeechstart=clear;
  recognition.onspeechend=wait;
  recognition.onresult=event => {
    segment=Array.from(event.results).map(result=>result[0].transcript).join(' ');
    options.onText(text()); wait();
  };
  recognition.onend=() => {
    base=text(); segment='';
    if(active) restart=later(() => { if(active) { try { recognition.start(); } catch(error) { stop(); } } },200);
  };
  recognition.onerror=event => {
    if(event.error==='no-speech') return;
    stop(); options.onError(event.error);
  };
  return {
    start() { active=true; options.onState(true); try { recognition.start(); } catch(error) { stop(); options.onError('start'); } },
    stop,
    dispose() { active=false; clear(); if(restart!==null) cancel(restart); recognition.onend=null; recognition.onresult=null; recognition.onerror=null; recognition.onstart=null; recognition.onspeechstart=null; recognition.onspeechend=null; try { recognition.abort(); } catch(error) {} }
  };
}
