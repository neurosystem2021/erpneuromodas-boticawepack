from pathlib import Path
p=Path('src/views/reportes/Confrontacion.vue')
s=p.read_text(encoding='utf-8-sig')
s=s.replace('<label for="inventory-code">CÓDIGO DE BARRAS</label>','<label for="inventory-code"><i class="fas fa-barcode"></i> CÓDIGO DE BARRAS</label>')
old='<input id="inventory-search-name" v-model="query" class="form-control" placeholder="NOMBRE, MARCA O MODELO" :disabled="!warehouseId || busy" @input="scheduleSearch" autocomplete="off">'
s=s.replace(old,'<div class="inventory-voice-search">'+old+'<button type="button" :class="{ listening }" :disabled="!warehouseId || busy" :aria-pressed="String(listening)" aria-label="BUSCAR POR VOZ" @click="toggleVoice"><i class="fas fa-microphone"></i></button><button type="button" :disabled="!warehouseId || busy || query.trim().length &lt; 2" aria-label="BUSCAR PRODUCTO" @click="scheduleSearch"><i class="fas fa-search"></i></button></div><small class="inventory-voice-hint" role="status">{{ listening ? "ESCUCHANDO… DI EL NOMBRE DEL PRODUCTO" : "ESCRIBE O TOCA EL MICRÓFONO PARA BUSCAR" }}</small>')
s=s.replace('showHeader: true, API:', 'showHeader: true, listening: false, API:',1)
s=s.replace("beforeDestroy() { clearTimeout", "beforeDestroy() { if (this._recognition) this._recognition.abort(); clearTimeout",1)
s=s.replace("const responses = await Promise.all([this.axios.get(this.API + 'almacenes', { params: { limit: 10000 } }), this.axios.get(this.API + 'categorias', { params: { limit: 10000 } })]);", "const responses = await Promise.all([this.axios.get(this.API + 'almacenes', { params: { limit: 10000 } }), this.axios.get(this.API + 'categorias', { params: { limit: 10000 } }).catch(() => ({ data: [] }))]);")
s=s.replace('  methods: {', '''  methods: {
    toggleVoice() {
      if (this.listening && this._recognition) { this._recognition.stop(); return; }
      const Recognition = window.SpeechRecognition || window.webkitSpeechRecognition;
      if (!Recognition) { this.error = 'ESTE NAVEGADOR NO ADMITE VOZ. USA CHROME O ESCRIBE EL PRODUCTO.'; return; }
      const recognition = new Recognition(); this._recognition = recognition;
      recognition.lang = 'es-PE'; recognition.continuous = false; recognition.interimResults = false;
      recognition.onstart = () => { this.listening = true; this.error = ''; };
      recognition.onresult = event => { this.query = event.results[0][0].transcript.trim(); this.scheduleSearch(); };
      recognition.onerror = event => { this.listening = false; this.error = event.error === 'not-allowed' ? 'PERMITE EL MICRÓFONO EN EL NAVEGADOR PARA BUSCAR POR VOZ.' : 'NO SE PUDO ESCUCHAR. INTENTA DE NUEVO O ESCRIBE EL PRODUCTO.'; };
      recognition.onend = () => { this.listening = false; };
      try { recognition.start(); } catch (_) { this.listening = false; this.error = 'NO SE PUDO ACTIVAR EL MICRÓFONO.'; }
    },''',1)
s+='''
<style scoped>
.inventory-app{--inventory-red:#c51d39;color:#38242b!important;background:#fff8fa!important;padding:16px!important;max-width:1100px;margin:auto}
.confront-hero{background:linear-gradient(120deg,#861930,#ce2744)!important;color:white!important;border-radius:18px!important;padding:20px!important}.confront-hero h1{font-size:24px!important;color:white!important}.inventory-eyebrow,.confront-hero p{color:#ffdee6!important}.confront-hero-icon{background:#ffffff20!important;color:white!important}
.inventory-settings,.inventory-search,.inventory-count-card{background:white!important;border:1px solid #f0dce2!important;border-radius:16px!important;padding:16px!important}.inventory-stats{gap:8px!important}.inventory-stats>span{background:#fff!important;border:1px solid #f0dce2!important;padding:12px!important;border-radius:12px!important}.inventory-stats strong{color:#c51d39!important}
.inventory-barcode{display:flex;gap:8px}.inventory-barcode input{min-width:0;flex:1}.inventory-barcode button,.inventory-primary,.inventory-voice-search button{background:#c51d39!important;color:white!important;border:0!important;border-radius:12px!important;min-height:46px;min-width:46px}.inventory-barcode button:disabled,.inventory-primary:disabled,.inventory-voice-search button:disabled{opacity:.45}.inventory-list-heading{background:#861930!important}.inventory-secondary{color:#a51b36!important;background:#fff0f4!important}.inventory-empty i{color:#c51d39!important}
.inventory-voice-search{display:flex;gap:8px;align-items:center}.inventory-voice-search input{min-width:0;flex:1}.inventory-voice-search button.listening{background:#861930!important;box-shadow:0 0 0 5px #c51d3925}.inventory-voice-hint{display:block;margin-top:8px;color:#9b5264;font-size:10px}.inventory-results button{min-height:56px}.inventory-app-actions{position:sticky;bottom:0;background:#fff9fb!important;padding:12px!important;gap:8px!important}
@media(max-width:600px){.inventory-app{padding:10px!important}.confront-hero{padding:14px!important}.confront-hero h1{font-size:18px!important}.confront-hero p{font-size:10px!important}.inventory-settings{display:grid!important;grid-template-columns:1fr!important;gap:10px!important;padding:12px!important}.inventory-settings label{font-size:11px!important}.inventory-settings select{height:44px!important}.inventory-stats{display:grid!important;grid-template-columns:repeat(3,minmax(0,1fr))!important}.inventory-stats>span{font-size:9px!important;padding:10px 6px!important}.inventory-stats strong{font-size:22px!important}.inventory-search{padding:12px!important}.inventory-barcode input{font-size:12px!important;width:0!important}.inventory-camera-button span{display:none}.inventory-barcode button{padding:10px!important;width:46px!important}.inventory-count-card{display:grid!important;grid-template-columns:1fr auto!important;gap:10px!important}.inventory-product-info{grid-column:1/-1}.inventory-product-info h2{font-size:13px!important}.inventory-quantity input{width:70px!important}.inventory-app-actions button{font-size:10px!important;min-height:46px!important;padding:10px!important}.inventory-list-heading{font-size:10px!important}}
</style>
'''
p.write_text(s,encoding='utf-8')
