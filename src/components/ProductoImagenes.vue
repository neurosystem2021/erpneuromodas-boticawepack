<template>
  <b-form-group label="Imágenes del producto (hasta 3)">
    <div class="product-images-grid">
      <div v-for="(image, index) in images" :key="index" class="product-image-slot">
        <strong>{{ index === 0 ? 'Principal' : 'Imagen ' + (index + 1) }}</strong>
        <img v-if="previews[index] || image" :src="previews[index] || '/resources/images/productos/' + image" :alt="'Imagen ' + (index + 1) + ' del producto'">
        <span v-else class="product-image-empty">Sin imagen</span>
        <input type="file" accept="image/jpeg,image/png,image/webp" :aria-label="'Seleccionar imagen ' + (index + 1)" :disabled="busy || loading" @change="select($event, index)">
        <button v-if="files[index]" type="button" class="btn btn-sm btn-outline-secondary" @click="cancel(index)">Cancelar cambio</button>
      </div>
    </div>
    <small>JPG, PNG o WebP. Máximo 5 MB por imagen. Se guardan junto con el producto.</small>
    <b-alert :show="!!error" variant="danger">{{ error }}</b-alert>
  </b-form-group>
</template>
<script>
import config from '@/store/config';
export default {
  props: ['productId', 'primary'],
  data() { return { images: [this.primary || '', '', ''], files: [null, null, null], previews: ['', '', ''], busy: false, loading: false, error: '', request: 0 }; },
  mounted() { this.load(); },
  watch: { productId() { if (!this.busy) this.load(); }, primary(value) { this.$set(this.images, 0, value || ''); } },
  beforeDestroy() { this.request++; this.previews.forEach(url => { if (url) URL.revokeObjectURL(url); }); },
  methods: {
    async load() {
      const request = ++this.request;
      this.files.forEach((file, index) => this.cancel(index));
      this.images = [this.primary || '', '', '']; this.error = '';
      if (!this.productId) return;
      this.loading = true;
      try {
        const response = await this.axios.get(config.API + 'producto/imagenes/' + this.productId);
        if (!response.data || !Array.isArray(response.data.imagenes)) throw new Error('Respuesta de imágenes no válida.');
        if (request === this.request) this.images = response.data.imagenes;
      } catch (error) { if (request === this.request) this.error = 'No se pudieron cargar las imágenes. Cierra y vuelve a abrir el producto antes de cambiarlas.'; }
      finally { if (request === this.request) this.loading = false; }
    },
    cancel(index) { if (this.previews[index]) URL.revokeObjectURL(this.previews[index]); this.$set(this.previews, index, ''); this.$set(this.files, index, null); },
    select(event, index) {
      const file = event.target.files[0]; event.target.value = '';
      if (!file) return;
      if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 5 * 1024 * 1024) { this.$toasted.error('Usa una imagen JPG, PNG o WebP de hasta 5 MB.'); return; }
      if (this.error) { this.$toasted.error(this.error); return; }
      this.cancel(index); this.$set(this.files, index, file); this.$set(this.previews, index, URL.createObjectURL(file));
    },
    async upload(id) {
      if (!this.files.some(Boolean)) return;
      this.busy = true;
      try {
        const form = new FormData();
        this.files.forEach((file, index) => { if (file) form.append(index === 0 ? 'imagen' : 'imagen' + (index + 1), file); });
        const response = await this.axios.post(config.API + 'producto/imagen/' + id, form);
        if (!response.data || !response.data.insertId) throw new Error('No se guardaron las imágenes.');
        this.images = response.data.imagenes;
        this.files.forEach((file, index) => this.cancel(index));
      } finally { this.busy = false; }
    }
  }
};
</script>
<style>
.product-images-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; }
.product-image-slot { display: flex; flex-direction: column; gap: 7px; min-width: 0; padding: 8px; border: 1px solid #d6e6ee; border-radius: 10px; }
.product-image-slot img, .product-image-empty { width: 100%; height: 110px; object-fit: contain; background: #f5fafc; border-radius: 7px; }
.product-image-empty { display: flex; align-items: center; justify-content: center; color: #78909e; font-size: 11px; }
.product-image-slot input { width: 100%; font-size: 10px; }
@media(max-width: 575px) { .product-images-grid { grid-template-columns: 1fr; } }
</style>
