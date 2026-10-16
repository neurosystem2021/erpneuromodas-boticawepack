from pathlib import Path
p=Path('src/components/PreOrdenFormDet.vue');s=p.read_text(encoding='utf-8');a=s.index('        <input v-model="loadCode"');b=s.index('      </b-input-group>',a);s=s[:a]+s[b:]
a=s.index('    </b-form-group>')+len('    </b-form-group>');s=s[:a]+'''
    <section class="project-barcode-search"><label for="projectProductBarcode"><i class="fas fa-barcode" aria-hidden="true"></i> Buscar producto por código de barras</label><div class="project-barcode-controls"><input id="projectProductBarcode" v-model="loadCode" @keydown.enter.prevent="loadBarcode" type="text" class="form-control" placeholder="Escanea o escribe el código" autocomplete="off" inputmode="text"/><b-button type="button" variant="primary" :disabled="loadingBarcode || !loadCode.trim()" @click="loadBarcode"><i class="fas fa-search" aria-hidden="true"></i> Buscar</b-button><b-button type="button" variant="success" :disabled="loadingBarcode" @click="openProjectScanner"><i class="fas fa-camera" aria-hidden="true"></i> Cámara</b-button></div><small>Usa la cámara, un lector USB o escribe el código.</small></section>
    <inventory-camera v-if="showProjectScanner" @close="showProjectScanner=false" @code="readProjectBarcode"/>
'''+s[a:]
s=s.replace('import config from','import InventoryCamera from "@/components/InventoryCamera";\nimport config from',1).replace('export default {','export default {\n  components:{InventoryCamera},\n  beforeDestroy(){if(this._scanAudio){this._scanAudio.close().catch(()=>{});this._scanAudio=null;}},',1).replace('      API: config.API,','      API: config.API,\n      showProjectScanner:false,\n      loadingBarcode:false,',1)
a=s.index('    loadBarcode(e) {');b=s.index('    addProductoKit(data) {',a)
s=s[:a]+'''    openProjectScanner() {
      const Audio=window.AudioContext || window.webkitAudioContext;
      if(Audio){try { if(!this._scanAudio)this._scanAudio=new Audio();this._scanAudio.resume().catch(()=>{}); }catch(error){} }
      this.showProjectScanner=true;
    },
    readProjectBarcode(code) {
      this.showProjectScanner=false;
      this.loadCode=String(code);
      if(this._scanAudio){try{const oscillator=this._scanAudio.createOscillator(),gain=this._scanAudio.createGain();oscillator.connect(gain);gain.connect(this._scanAudio.destination);oscillator.frequency.value=1100;gain.gain.setValueAtTime(.12,this._scanAudio.currentTime);gain.gain.exponentialRampToValueAtTime(.001,this._scanAudio.currentTime+.15);oscillator.start();oscillator.stop(this._scanAudio.currentTime+.15);}catch(error){} }
      this.loadBarcode();
    },
    async loadBarcode(e) {
      if(e && e.preventDefault)e.preventDefault();
      const code=String(this.loadCode || '').trim();
      if(!code || this.loadingBarcode)return;
      this.loadingBarcode=true;
      try {
        const response=await this.axios.get(this.API+'productos/id/'+encodeURIComponent(code),{params:{codigoBarra:code}});
        if(response.data && response.data.IdProducto){this.addProducto(response.data);this.loadCode='';}
        else this.$toasted.error('No se encontró un producto con ese código.');
      }catch(error){this.$toasted.error('No se pudo buscar el código. Intente nuevamente.');}
      finally{this.loadingBarcode=false;}
    },

'''+s[b:];p.write_text(s,encoding='utf-8')
