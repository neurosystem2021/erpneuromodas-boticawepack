from pathlib import Path
p=Path('src/components/AliAssistant.vue');s=p.read_text(encoding='utf-8');s=s.replace('<div class="ali-host">','<div :class="[\'ali-host\',{\'is-awake\':awake}]">',1);s=s.replace("voice.onend=()=>{this._speaking=false;this.scheduleWake()}","this._aliUtterance=voice;voice.onend=()=>{if(this._aliUtterance!==voice)return;this._aliUtterance=null;this._speaking=false;this.scheduleWake()}",1);s=s.replace('if(window.speechSynthesis)window.speechSynthesis.cancel();this._speaking=false;', 'this._aliUtterance=null;if(window.speechSynthesis)window.speechSynthesis.cancel();this._speaking=false;',1);p.write_text(s,encoding='utf-8')
p=Path('static/css/pos-layout-fix.css');s=p.read_text(encoding='utf-8');s+='''
/* ALI se ubica bajo el menú del contenedor del punto de venta. */
.ali-host .ali-goldfish{top:12px!important;right:18px!important;bottom:auto!important}
@media(max-width:767px){.ali-host .ali-goldfish{top:8px!important;right:10px!important}.ali-host .ali-goldfish .ali-fish-art{width:110px!important;height:80px!important}.ali-host.is-awake .ali-modal .modal-dialog{margin-top:130px!important;max-height:calc(100dvh - 140px)!important}.ali-host.is-awake .ali-modal .modal-content{max-height:calc(100dvh - 140px)!important}}
''';p.write_text(s,encoding='utf-8')
