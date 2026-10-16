from pathlib import Path
p=Path('src/components/AliAssistant.vue');s=p.read_text(encoding='utf-8')
s=s.replace("armIdle(reset=false){clearTimeout(this._idleTimer);", "armIdle(reset=false){clearTimeout(this._idleTimer);this._idleTimer=null;",1)
s=s.replace("this._idleTimer=setTimeout(()=>this.onIdle(),20000)", "this._idleTimer=setTimeout(()=>{this._idleTimer=null;this.onIdle()},20000)",1)
s=s.replace("say(text,voiceChoice=this.voiceChoice,preview=false){clearTimeout(this._idleTimer);", "say(text,voiceChoice=this.voiceChoice,preview=false){clearTimeout(this._idleTimer);this._idleTimer=null;",1)
s=s.replace("stopWake(){clearTimeout(this._idleTimer);", "stopWake(){clearTimeout(this._idleTimer);this._idleTimer=null;",1)
s=s.replace("scheduleWake(){this.armIdle();", "scheduleWake(){if(!this._idleTimer)this.armIdle();",1)
p.write_text(s,encoding='utf-8')
