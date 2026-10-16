from pathlib import Path
p=Path('src/components/PreOrdenFormDet.vue');s=p.read_text(encoding='utf-8').replace('<i class="fas fa-camera" aria-hidden="true"></i> Cámara','<i class="fas fa-barcode" aria-hidden="true"></i> Escanear',1).replace('<inventory-camera v-if="showProjectScanner"','<inventory-camera :continuous="true" v-if="showProjectScanner"',1).replace('    readProjectBarcode(code) {\n      this.showProjectScanner=false;','    readProjectBarcode(code) {\n      if(this.loadingBarcode) return;',1);p.write_text(s,encoding='utf-8')
p=Path('src/components/InventoryCamera.vue');s=p.read_text(encoding='utf-8').replace(':visible="true" title=',':visible="true" :no-close-on-backdrop="continuous" title=',1).replace('export default {','export default {\n  props:{continuous:{type:Boolean,default:false}},',1)
s=s.replace("    visibilityChanged() { if (document.hidden) this.$emit('close'); },","    visibilityChanged() { if(this.continuous){if(document.hidden){this.stop();this._paused=true;}else if(this._paused){this._paused=false;this.start();}}else if(document.hidden)this.$emit('close'); },",1)
needle="    detected(value) { const code = this._accept(value); if (!code) return; this.stop(); if (navigator.vibrate) navigator.vibrate(80); this.$emit('code', code); },";assert needle in s;s=s.replace(needle,"""    detected(value) {
      const code=this._accept(value);if(!code) return;
      if(this.continuous){const now=Date.now();if(this._lastReadAt && now-this._lastReadAt<2500)return;this._lastReadAt=now;this.status='LEÍDO: '+code+' · PUEDES ESCANEAR OTRO PRODUCTO';}
      else this.stop();
      if(navigator.vibrate)navigator.vibrate(80);
      this.$emit('code',code);
    },""",1);p.write_text(s,encoding='utf-8')
