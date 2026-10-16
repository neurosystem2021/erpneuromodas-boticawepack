<template>
  <main class="nst-screen inventory-app confront-page" :class="{ 'confront-mobile-collapsed': !showHeader }">
    <button type="button" class="confront-mobile-header-toggle" :aria-expanded="String(showHeader)" @click="showHeader = !showHeader"><i :class="showHeader ? 'fas fa-eye-slash' : 'fas fa-eye'"></i><span>{{ showHeader ? 'OCULTAR CABECERA Y FILTROS' : 'MOSTRAR CABECERA Y FILTROS' }}</span><i :class="showHeader ? 'fas fa-chevron-up' : 'fas fa-chevron-down'"></i></button>
    <div class="confront-workspace">
    <section class="inventory-settings">
      <label>ALMACÉN<select class="form-control" :value="warehouseId" :disabled="busy || loading" @change="changeWarehouse($event.target.value)"><option value="">SELECCIONA ALMACÉN</option><option v-for="item in warehouses" :key="item.IdAlmacen" :value="item.IdAlmacen">{{ item.Almacen }}</option></select></label>
      <label>AGREGAR POR CATEGORÍA<select class="form-control" v-model="categoryId" :disabled="busy || loading || !warehouseId" @change="loadCategory"><option value="">SELECCIONA CATEGORÍA</option><option v-for="item in categories" :key="item.IdProductoCategoria" :value="item.IdProductoCategoria">{{ item.ProductoCategoria }}</option></select></label>
      <a v-if="warehouseId" class="inventory-secondary" :href="API + 'reporte/confrontacion/total?idAlmacen=' + warehouseId" target="_blank" rel="noopener">LISTADO COMPLETO <i class="fas fa-external-link-alt"></i></a>
    </section>
    <div class="inventory-stats"><span><strong>{{ products.length }}</strong> PRODUCTOS</span><span><strong>{{ counted }}</strong> CONTADOS</span><span :class="{ 'is-pending': pending }"><strong>{{ pending }}</strong> PENDIENTES</span></div>
    <section class="inventory-search">
      <form @submit.prevent="lookupBarcode">
        <label for="inventory-code"><i class="fas fa-barcode"></i> CÓDIGO DE BARRAS</label>
        <div class="inventory-barcode"><input id="inventory-code" ref="codeInput" v-model="barcode" type="text" autocomplete="off" placeholder="ESCANEA O ESCRIBE EL CÓDIGO" :disabled="busy || !warehouseId"><button type="button" class="inventory-camera-button" :disabled="busy || !warehouseId" @click="camera = true" aria-label="ESCANEAR CON CÁMARA"><i class="fas fa-barcode"></i><span>ESCANEAR</span></button><button type="submit" :disabled="busy || !warehouseId || !barcode.trim()" aria-label="BUSCAR CÓDIGO"><i class="fas fa-search"></i></button></div>
      </form>
      <label for="inventory-search-name">BUSCAR PRODUCTO</label>
      <div class="inventory-voice-search"><input id="inventory-search-name" v-model="query" class="form-control" placeholder="NOMBRE, MARCA O MODELO" :disabled="!warehouseId || busy" @input="scheduleSearch" @keydown.enter.prevent="scheduleSearch" autocomplete="off"><button type="button" :class="{ listening }" :disabled="!warehouseId || busy" :aria-pressed="String(listening)" aria-label="BUSCAR POR VOZ" @click="toggleVoice"><i class="fas fa-microphone"></i></button><button type="button" :disabled="!warehouseId || busy || query.trim().length &lt; 2" aria-label="BUSCAR PRODUCTO" @click="scheduleSearch"><i class="fas fa-search"></i></button></div><small class="inventory-voice-hint" role="status">{{ listening ? "ESCUCHANDO… DI EL NOMBRE DEL PRODUCTO" : "ESCRIBE O TOCA EL MICRÓFONO PARA BUSCAR" }}</small>
      <div v-if="searching" class="inventory-feedback" role="status">BUSCANDO…</div>
      <ul v-if="results.length" class="inventory-results"><li v-for="item in results" :key="item.IdProducto"><button type="button" @click="addProduct(item, false)"><strong>{{ item.Producto }}</strong><small>{{ item.CodigoBarra }} · {{ item.ProductoMarca }}</small><span>AGREGAR +</span></button></li></ul>
    </section>
    <p v-if="message" role="status" class="inventory-feedback">{{ message }}</p>
    <p v-if="error" role="alert" class="inventory-error">{{ error }}</p>
    <section class="inventory-count-list" aria-label="PRODUCTOS DEL INVENTARIO">
      <div class="inventory-list-heading"><span>PRODUCTO / CÓDIGO</span><span>CANTIDAD FÍSICA</span></div>
      <article v-for="(item, index) in visibleProducts" :key="item.IdProducto" class="inventory-count-card" :class="{ 'is-counted': item.cantidad !== null, 'is-recent': recent === item.IdProducto }">
        <div class="inventory-product-info"><small>{{ item.CodigoBarra }}</small><h2 :title="item.Producto">{{ item.Producto }}</h2><span>{{ item.cantidad === null ? 'PENDIENTE DE CONTAR' : 'CONTADO' }}</span></div>
        <div class="inventory-quantity"><button type="button" :disabled="busy || !(Number(item.cantidad) > 0)" @click="stepQuantity(item, -1)" :aria-label="'RESTAR CANTIDAD DE ' + item.Producto">−</button><input type="number" min="0" step="0.001" inputmode="decimal" :value="item.cantidad" placeholder="—" :disabled="busy" :aria-label="'CANTIDAD FÍSICA DE ' + item.Producto" @input="setQuantity(item, $event.target.value)"><button type="button" :disabled="busy" @click="stepQuantity(item, 1)" :aria-label="'SUMAR CANTIDAD DE ' + item.Producto">+</button></div>
        <button type="button" class="inventory-remove" :disabled="busy" @click="removeProduct((page - 1) * 25 + index)" :aria-label="'QUITAR ' + item.Producto"><i class="fas fa-times"></i></button>
      </article>
      <div v-if="!products.length" class="inventory-empty"><i class="fas fa-boxes"></i><h2>COMIENZA TU CONTEO</h2><p>USA LA CÁMARA, UN LECTOR O BUSCA EL PRODUCTO.</p><p>CADA ESCANEO SUMA UNA UNIDAD. PUEDES AJUSTAR LA CANTIDAD.</p></div>
    </section>
    <nav v-if="products.length > 25" class="inventory-pagination" aria-label="PÁGINAS DE INVENTARIO"><button type="button" :disabled="page === 1" @click="page--">‹</button><span>{{ page }} / {{ pageCount }} · 25 POR PÁGINA</span><button type="button" :disabled="page >= pageCount" @click="page++">›</button></nav><footer class="inventory-app-actions"><button type="button" class="inventory-primary" :disabled="busy || !products.length || !warehouseId" @click="generateReport(false)">{{ busy ? 'GENERANDO…' : 'CONFRONTAR INVENTARIO' }}</button><button type="button" class="inventory-secondary" :disabled="busy || !products.length || !warehouseId" @click="generateReport(true)">LISTADO DE CONTEO</button></footer>
    </div>
    <p class="inventory-report-note">CONFRONTAR GENERA EL REPORTE DE DIFERENCIAS. EL CONTEO NO MODIFICA EL STOCK.<br><button type="button" class="inventory-new-count" :disabled="busy || !products.length" @click="newCount">INICIAR NUEVO CONTEO</button></p>
    <inventory-camera v-if="camera" @close="camera = false" @code="onCameraCode" />
  </main>
</template>
<script>
import config from '@/store/config';
import InventoryCamera from '@/components/InventoryCamera';
import { inventoryQuantity } from '@/utils/inventoryScan';
export default {
  components: { InventoryCamera },
  data() { return { showHeader: true, listening: false, API: config.API, warehouses: [], categories: [], warehouseId: '', categoryId: '', products: [], barcode: '', query: '', page: 1, results: [], searching: false, busy: false, loading: true, camera: false, message: '', error: '', recent: null }; },
  computed: { pageCount() { return Math.max(1, Math.ceil(this.products.length / 25)); }, visibleProducts() { return this.products.slice((this.page - 1) * 25, this.page * 25); }, counted() { return this.products.filter(item => item.cantidad !== null).length; }, pending() { return this.products.length - this.counted; } },
  created() { this._searchVersion = 0; this._draftUser = sessionStorage.getItem('User') || ''; },
  watch: { products: { deep: true, handler() { this.saveDraft(); } } },
  async mounted() {
    try {
      const responses = await Promise.all([this.axios.get(this.API + 'almacenes', { params: { limit: 10000 } }), this.axios.get(this.API + 'categorias', { params: { limit: 10000 } }).catch(() => ({ data: [] }))]);
      this.warehouses = Array.isArray(responses[0].data) ? responses[0].data : [];
      this.categories = Array.isArray(responses[1].data) ? responses[1].data : [];
      const tarapaca = this.warehouses.find(item => String(item.Almacen || '').normalize('NFD').replace(/[\u0300-\u036f]/g, '').trim().toUpperCase() === 'TARAPACA');
      if (tarapaca) { this.warehouseId = String(tarapaca.IdAlmacen); this.restoreDraft(); }
      else this.error = 'NO SE ENCONTRÓ EL ALMACÉN TARAPACA. SELECCIONA EL ALMACÉN PARA COMENZAR.';
    } catch (_) { this.error = 'NO SE PUDIERON CARGAR LOS ALMACENES. RECARGA LA PÁGINA.'; }
    finally { this.loading = false; }
    window.addEventListener('beforeunload', this.warnUnsaved);
  },
  beforeDestroy() { if (this._recognition) this._recognition.abort(); clearTimeout(this._searchTimer); ++this._searchVersion; window.removeEventListener('beforeunload', this.warnUnsaved); },
  methods: {
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
    },
    async newCount() {
      const result = await this.$swal({ icon: 'warning', title: 'NUEVO CONTEO', text: 'SE VACIARÁ EL BORRADOR DE ESTE ALMACÉN. GENERA EL REPORTE ANTES DE CONTINUAR.', showCancelButton: true, confirmButtonText: 'INICIAR NUEVO', cancelButtonText: 'VOLVER', focusCancel: true });
      if (result.isConfirmed) { this.products = []; this.message = 'LISTO PARA UN NUEVO CONTEO.'; this.error = ''; this.saveDraft(); }
    },
    saveDraft() {
      if (!this._draftUser || !this.warehouseId) return;
      try { localStorage.setItem('nst-inventory-count-v1:' + this._draftUser + ':' + this.warehouseId, JSON.stringify(this.products)); }
      catch (_) { this.error = 'NO SE PUDO GUARDAR EL BORRADOR EN ESTE DISPOSITIVO. GENERA EL REPORTE ANTES DE SALIR.'; }
    },
    restoreDraft() {
      if (!this._draftUser || !this.warehouseId) return;
      try {
        const rows = JSON.parse(localStorage.getItem('nst-inventory-count-v1:' + this._draftUser + ':' + this.warehouseId) || '[]');
        if (Array.isArray(rows)) this.products = rows.filter(row => row && row.IdProducto && typeof row.Producto === 'string').slice(0, 10000).map(row => ({ IdProducto: row.IdProducto, CodigoBarra: String(row.CodigoBarra || ''), Producto: row.Producto, cantidad: inventoryQuantity(row.cantidad) }));
        if (this.products.length) this.message = 'BORRADOR RECUPERADO EN ESTE DISPOSITIVO.';
      } catch (_) { this.error = 'NO SE PUDO RECUPERAR EL BORRADOR.'; }
    },
    warnUnsaved(event) { if (this.products.length) { event.preventDefault(); event.returnValue = ''; } },
    async changeWarehouse(id) {
      if (id === String(this.warehouseId)) return;
      if (this.products.length) { const answer = await this.$swal({ icon: 'warning', title: 'CAMBIAR ALMACÉN', text: 'EL CONTEO ACTUAL SE VACIARÁ. GENERA TU REPORTE ANTES DE CAMBIAR.', showCancelButton: true, confirmButtonText: 'CAMBIAR ALMACÉN', cancelButtonText: 'CONTINUAR CONTANDO', focusCancel: true }); if (!answer.isConfirmed) { this.$forceUpdate(); return; } }
      this.saveDraft(); this.warehouseId = String(id); this.products = []; this.query = ''; this.results = []; this.message = ''; this.error = ''; ++this._searchVersion; this.restoreDraft();
    },
    scheduleSearch() { clearTimeout(this._searchTimer); const version = ++this._searchVersion; this.results = []; this.searching = false; this.message = ''; this.error = ''; if (this.query.trim().length < 2) return; this._searchTimer = setTimeout(() => this.searchProducts(version), 250); },
    async searchProducts(version) {
      this.searching = true;
      try { const response = await this.axios.get(this.API + 'productos/puntoventa/stock/' + this.warehouseId, { params: { q: this.query.trim().replace(/['"\\]/g, ' '), limit: 25 } }); if (version === this._searchVersion) { this.results = (Array.isArray(response.data) ? response.data : []).slice(0, 25); this.message = ''; if (!this.results.length) this.message = 'NO SE ENCONTRARON PRODUCTOS.'; } }
      catch (_) { if (version === this._searchVersion) this.error = 'NO SE PUDO BUSCAR. REVISA LA CONEXIÓN.'; }
      finally { if (version === this._searchVersion) this.searching = false; }
    },
    async lookupBarcode() {
      const code = this.barcode.trim(); if (!code || this.busy || !this.warehouseId) return;
      if (!/^[\w .\-/+]+$/.test(code) || code.length > 128) { this.error = 'CÓDIGO NO VÁLIDO. REVISA LA ETIQUETA.'; return; }
      this.busy = true; this.error = ''; this.message = '';
      try { const response = await this.axios.get(this.API + 'productos/id/0', { params: { codigoBarra: code } }); if (response.data && response.data.IdProducto) { this.addProduct(response.data, true); this.barcode = ''; } else { this.error = 'NO SE ENCONTRÓ EL CÓDIGO ' + code + '. REVISA LA ETIQUETA.'; } }
      catch (_) { this.error = 'NO SE PUDO CONSULTAR EL CÓDIGO. REVISA LA CONEXIÓN.'; }
      finally { this.busy = false; this.$nextTick(() => { if (window.matchMedia('(pointer: fine)').matches && this.$refs.codeInput) this.$refs.codeInput.focus(); }); }
    },
    onCameraCode(code) { this.camera = false; this.barcode = code; this.lookupBarcode(); },
    addProduct(product, scanned) {
      if (!product || !product.IdProducto || !this.warehouseId) return;
      let item = this.products.find(row => String(row.IdProducto) === String(product.IdProducto));
      if (item) { if (scanned) item.cantidad = Math.round(((Number(item.cantidad) || 0) + 1) * 1000) / 1000; }
      else { item = { IdProducto: product.IdProducto, CodigoBarra: String(product.CodigoBarra || ''), Producto: product.Producto, cantidad: 1 }; this.products.unshift(item); this.page = 1; }
      this.recent = item.IdProducto; this.message = scanned ? 'LECTURA CONFIRMADA · ' + item.Producto + ' · CANTIDAD ' + item.cantidad : 'PRODUCTO AGREGADO · CANTIDAD 1.';
      this.query = ''; this.results = []; this.searching = false; ++this._searchVersion;
    },
    setQuantity(item, value) { item.cantidad = inventoryQuantity(value); },
    stepQuantity(item, delta) { item.cantidad = Math.max(0, Math.round(((Number(item.cantidad) || 0) + delta) * 1000) / 1000); },
    async removeProduct(index) { const item = this.products[index]; const result = await this.$swal({ icon: 'warning', title: 'QUITAR PRODUCTO', text: item.Producto, footer: 'SE QUITARÁ SOLO DEL CONTEO ACTUAL.', showCancelButton: true, confirmButtonText: 'SÍ, QUITAR', cancelButtonText: 'CONSERVAR', focusCancel: true, reverseButtons: true, buttonsStyling: false, customClass: { container: 'inventory-confirm-overlay', popup: 'inventory-confirm-dialog', icon: 'inventory-confirm-icon', title: 'inventory-confirm-title', content: 'inventory-confirm-content', htmlContainer: 'inventory-confirm-content', actions: 'inventory-confirm-actions', confirmButton: 'inventory-confirm-remove', cancelButton: 'inventory-confirm-cancel', footer: 'inventory-confirm-footer' } }); if (result.isConfirmed) { this.products.splice(this.products.indexOf(item), 1); this.page = Math.min(this.page, this.pageCount); } },
    async loadCategory() {
      if (!this.categoryId || this.busy || !this.warehouseId) return; this.busy = true; this.error = '';
      try { const response = await this.axios.get(this.API + 'productos/categoria/carga', { params: { IdCategoria: this.categoryId } }); if (!Array.isArray(response.data)) throw new Error(); response.data.forEach(item => this.addProduct(item, false)); this.message = 'CATEGORÍA CARGADA. SE CONSERVARON LAS CANTIDADES YA CONTADAS.'; }
      catch (_) { this.error = 'NO SE PUDO CARGAR LA CATEGORÍA.'; }
      finally { this.categoryId = ''; this.busy = false; }
    },
    async generateReport(blank) {
      if (this.busy || !this.warehouseId || !this.products.length) return;
      if (!blank && this.pending) { this.error = 'FALTAN ' + this.pending + ' PRODUCTOS POR CONTAR. ESCRIBE 0 CUANDO NO HAYA EXISTENCIAS.'; return; }
      this.busy = true; this.error = '';
      try {
        const response = await this.axios.post(this.API + (blank ? 'reporte/preconfrontacion' : 'reporte/confrontacion'), { Productos: this.products.map(item => ({ IdProducto: item.IdProducto, CodigoBarra: item.CodigoBarra, Producto: item.Producto, cantidad: item.cantidad || 0 })), IdProductos: this.products.map(item => item.IdProducto), IdAlmacen: Number(this.warehouseId) });
        if (response.data && response.data.error) throw new Error(response.data.error);
        // Existing report endpoints return a redirect script. Extract only the known local XLSX path.
        const match = String(response.data).match(/\/api\/reporte\/[^"'<>]+\.xlsx/);
        if (!match) throw new Error('NO SE RECIBIÓ EL ARCHIVO DEL REPORTE.');
        this.reportUrl = match[0];
        const anchor = document.createElement('a'); anchor.href = this.reportUrl; anchor.download = ''; document.body.appendChild(anchor); anchor.click(); anchor.remove();
        this.message = 'REPORTE GENERADO. TU CONTEO SE CONSERVA PARA REVISARLO.';
      } catch (error) { this.error = error.message || 'NO SE PUDO GENERAR EL REPORTE.'; }
      finally { this.busy = false; }
    }
  }
};
</script>

<style scoped>
.inventory-app{--inventory-red:#c51d39;color:#38242b!important;background:#fff8fa!important;padding:16px!important;max-width:1100px;margin:auto}
.confront-hero{background:linear-gradient(120deg,#861930,#ce2744)!important;color:white!important;border-radius:18px!important;padding:20px!important}.confront-hero h1{font-size:24px!important;color:white!important}.inventory-eyebrow,.confront-hero p{color:#ffdee6!important}.confront-hero-icon{background:#ffffff20!important;color:white!important}
.inventory-settings,.inventory-search,.inventory-count-card{background:white!important;border:1px solid #f0dce2!important;border-radius:16px!important;padding:16px!important}.inventory-stats{gap:8px!important}.inventory-stats>span{background:#fff!important;border:1px solid #f0dce2!important;padding:12px!important;border-radius:12px!important}.inventory-stats strong{color:#c51d39!important}
.inventory-barcode{display:flex;gap:8px}.inventory-barcode input{min-width:0;flex:1}.inventory-barcode button,.inventory-primary,.inventory-voice-search button{background:#c51d39!important;color:white!important;border:0!important;border-radius:12px!important;min-height:46px;min-width:46px}.inventory-barcode button:disabled,.inventory-primary:disabled,.inventory-voice-search button:disabled{opacity:.45}.inventory-list-heading{background:#861930!important}.inventory-secondary{color:#a51b36!important;background:#fff0f4!important}.inventory-empty i{color:#c51d39!important}
.inventory-voice-search{display:flex;gap:8px;align-items:center}.inventory-voice-search input{min-width:0;flex:1}.inventory-voice-search button.listening{background:#861930!important;box-shadow:0 0 0 5px #c51d3925}.inventory-voice-hint{display:block;margin-top:8px;color:#9b5264;font-size:10px}.inventory-results button{min-height:56px}.inventory-app-actions{position:sticky;bottom:0;background:#fff9fb!important;padding:12px!important;gap:8px!important}
@media(max-width:600px){.inventory-app{padding:10px!important}.confront-hero{padding:14px!important}.confront-hero h1{font-size:18px!important}.confront-hero p{font-size:10px!important}.inventory-settings{display:grid!important;grid-template-columns:1fr!important;gap:10px!important;padding:12px!important}.inventory-settings label{font-size:11px!important}.inventory-settings select{height:44px!important}.inventory-stats{display:grid!important;grid-template-columns:repeat(3,minmax(0,1fr))!important}.inventory-stats>span{font-size:9px!important;padding:10px 6px!important}.inventory-stats strong{font-size:22px!important}.inventory-search{padding:12px!important}.inventory-barcode input{font-size:12px!important;width:0!important}.inventory-camera-button span{display:none}.inventory-barcode button{padding:10px!important;width:46px!important}.inventory-count-card{display:grid!important;grid-template-columns:1fr auto!important;gap:10px!important}.inventory-product-info{grid-column:1/-1}.inventory-product-info h2{font-size:13px!important}.inventory-quantity input{width:70px!important}.inventory-app-actions button{font-size:10px!important;min-height:46px!important;padding:10px!important}.inventory-list-heading{font-size:10px!important}}

/* Compact physical count: product, quantity and remove share one row. */
.inventory-app.confront-page .inventory-count-card{grid-template-columns:minmax(0,1fr) 150px 32px!important;min-height:0!important;height:auto!important;gap:10px!important;padding:10px 12px!important;margin-top:6px!important;border-radius:10px!important;position:relative!important}
.inventory-app.confront-page .inventory-product-info{grid-column:auto!important;min-width:0!important;padding:0!important;margin:0!important}
.inventory-app.confront-page .inventory-product-info h2{font-size:12px!important;line-height:1.35!important;margin:3px 0!important}
.inventory-app.confront-page .inventory-product-info small{font-size:10px!important;line-height:1.2!important}
.inventory-app.confront-page .inventory-product-info span{font-size:8px!important;line-height:1.2!important;color:#a21f3a!important}
.inventory-app.confront-page .inventory-quantity{display:flex!important;gap:4px!important;align-items:center!important;grid-column:auto!important;width:auto!important;margin:0!important}
.inventory-app.confront-page .inventory-quantity button{width:40px!important;min-width:40px!important;flex:0 0 40px!important;height:40px!important;padding:0!important;font-size:20px!important;border-radius:8px!important;background:#fff0f4!important;border:1px solid #efcad4!important;color:#b21f3b!important}
.inventory-app.confront-page .inventory-quantity input{width:62px!important;flex:1!important;height:40px!important;font-size:15px!important;padding:2px!important;border-radius:8px!important;border-color:#efcad4!important;color:#772039!important}
.inventory-app.confront-page .inventory-remove{position:static!important;width:32px!important;height:40px!important;padding:0!important;grid-column:auto!important;align-self:center!important;margin:0!important}
@media(max-width:600px){
.inventory-app.confront-page .inventory-count-card{grid-template-columns:minmax(0,1fr) 116px 28px!important;gap:5px!important;padding:9px 8px!important}
.inventory-app.confront-page .inventory-product-info h2{font-size:10px!important;line-height:1.35!important}
.inventory-app.confront-page .inventory-quantity button{width:34px!important;min-width:34px!important;flex-basis:34px!important;height:40px!important}
.inventory-app.confront-page .inventory-quantity input{width:40px!important;font-size:14px!important}
.inventory-app.confront-page .inventory-remove{width:28px!important;height:40px!important}
.inventory-app.confront-page .inventory-list-heading{grid-template-columns:minmax(0,1fr) 149px!important;padding:9px 8px!important;gap:5px!important;font-size:9px!important}
.inventory-app.confront-page .inventory-app-actions{padding:8px 0!important;margin-top:8px!important}
}


/* Dense rows, 25 products per page. */
.inventory-app.confront-page .inventory-count-card{display:grid!important;grid-template-columns:minmax(0,1fr) 112px 28px!important;grid-template-rows:auto!important;gap:4px!important;padding:4px 6px!important;margin:0!important;border-radius:0!important;border-width:0 0 1px!important;min-height:42px!important;align-items:center!important}
.inventory-app.confront-page .inventory-product-info{display:flex!important;align-items:center!important;gap:5px!important;grid-column:1!important;grid-row:1!important;overflow:hidden!important}
.inventory-app.confront-page .inventory-product-info small{display:block!important;flex:none!important;font-size:9px!important;max-width:42px!important;overflow:hidden!important;text-overflow:ellipsis!important}
.inventory-app.confront-page .inventory-product-info h2{display:block!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;font-size:11px!important;line-height:20px!important;margin:0!important;min-width:0!important}
.inventory-app.confront-page .inventory-product-info span{display:none!important}
.inventory-app.confront-page .inventory-quantity{grid-column:2!important;grid-row:1!important;gap:2px!important;width:112px!important}
.inventory-app.confront-page .inventory-quantity button{width:34px!important;min-width:34px!important;flex:0 0 34px!important;height:34px!important;font-size:18px!important;border-radius:6px!important}
.inventory-app.confront-page .inventory-quantity input{width:40px!important;min-width:0!important;height:34px!important;font-size:13px!important}
.inventory-app.confront-page .inventory-remove{position:static!important;grid-column:3!important;grid-row:1!important;width:28px!important;height:34px!important;align-self:center!important}
.inventory-app.confront-page .inventory-results button{display:flex!important;align-items:center!important;gap:6px!important;min-height:38px!important;padding:6px!important}.inventory-app.confront-page .inventory-results strong{flex:1;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-size:11px!important}.inventory-app.confront-page .inventory-results small{font-size:9px!important;max-width:65px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}.inventory-app.confront-page .inventory-results span{flex:none;color:#b21f3b!important;font-size:9px!important}
.inventory-pagination{display:flex;align-items:center;justify-content:center;gap:12px;padding:8px;font-size:10px;color:#9d203a}.inventory-pagination button{width:34px;height:34px;background:#fff0f4;border:1px solid #efcad4;border-radius:6px;color:#b21f3b;font-size:20px}
.inventory-app.confront-page .inventory-feedback{padding:5px 8px!important;margin:5px 0!important;font-size:10px!important}
@media(max-width:600px){.inventory-app.confront-page .inventory-settings{grid-template-columns:1fr 1fr!important;padding:8px!important;gap:6px!important}.inventory-app.confront-page .inventory-settings> a{grid-column:1/-1!important;padding:6px!important;min-height:30px!important}.inventory-app.confront-page .inventory-settings select{height:36px!important;font-size:11px!important}.inventory-app.confront-page .inventory-stats>span{padding:6px!important;display:flex!important;align-items:center!important;gap:5px!important}.inventory-app.confront-page .inventory-stats strong{font-size:17px!important}.inventory-app.confront-page .inventory-search{padding:8px!important}.inventory-app.confront-page .inventory-count-list{margin-top:6px!important}}

</style>
