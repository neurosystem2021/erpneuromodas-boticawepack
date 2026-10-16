from pathlib import Path
p=Path('src/components/ProductosCarritoNuevo.vue')
s=p.read_text(encoding='utf-8')
s=s.replace('abrirImagen(producto.Producto, producto.Imagen', 'abrirImagen(producto, producto.Imagen').replace('abrirImagen(productos[data.index].Producto,productos[data.index].Imagen','abrirImagen(productos[data.index],productos[data.index].Imagen')
a=s.index('      <div class="pos-product-image-stage">');b=s.index('    </b-modal>',a)
s=s[:a]+'''      <div class="pos-product-detail">
        <div class="pos-product-gallery">
          <div class="pos-product-image-stage">
            <b-img :src="productoGaleria[productoVista] || productoUrl" :alt="productoNombre" class="imagenGrande" @error="setAltImg"></b-img>
            <button v-if="productoGaleria.length > 1" class="pos-gallery-prev" aria-label="Imagen anterior" @click="productoVista = (productoVista + productoGaleria.length - 1) % productoGaleria.length">&#8249;</button>
            <button v-if="productoGaleria.length > 1" class="pos-gallery-next" aria-label="Imagen siguiente" @click="productoVista = (productoVista + 1) % productoGaleria.length">&#8250;</button>
          </div>
          <div class="pos-gallery-thumbs"><button v-for="(foto,i) in productoGaleria" :key="foto" :class="{selected:i===productoVista}" :aria-label="'Ver imagen '+(i+1)" @click="productoVista=i"><img :src="foto" @error="setAltImg" alt="Vista del producto"></button></div>
        </div>
        <div class="pos-product-information">
          <small>{{ productoDetalle.categoria || productoDetalle.ProductoCategoria }} · {{ productoDetalle.marca || productoDetalle.ProductoMarca }}</small>
          <h2>{{ productoNombre }}</h2>
          <strong class="pos-detail-price">S/ {{ Number(productoDetalle.PrecioContado || 0).toFixed(2) }}</strong>
          <p class="pos-detail-description">{{ productoDetalle.descripcion_detallada || productoDetalle.ProductoDesc || productoDetalle.descripcion || productoDetalle.ProductoDescCorto || 'Sin descripción adicional.' }}</p>
          <div class="pos-detail-attributes"><div><small>CÓDIGO</small><b>{{ productoDetalle.CodigoBarra || productoDetalle.codigo || productoDetalle.IdProducto }}</b></div><div><small>MODELO</small><b>{{ productoDetalle.modelo || productoDetalle.ProductoModelo || '—' }}</b></div></div>
          <p class="pos-detail-stock">Stock en tienda: {{ productoDetalle.stock != null ? productoDetalle.stock : 'Consultar' }}</p>
        </div>
      </div>
''' +s[b:]
s=s.replace('      productoNombre: "",','      productoDetalle: {},\n      productoGaleria: [],\n      productoVista: 0,\n      productoNombre: "",')
a=s.index('    abrirImagen(producto, url) {');b=s.index('    bloquearPrecio(data)',a)
s=s[:a]+'''    abrirImagen(producto, url) {
      const item = typeof producto === 'object' ? producto : {Producto: producto};
      this.productoNombre = item.Producto;
      this.productoDetalle = Object.assign({}, item);
      this.productoUrl = url;
      this.productoVista = 0;
      this.productoGaleria = [url];
      this.modalImagen = true;
      const id = item.IdProducto;
      if (id) fetch('/api/tienda/index.php?q=' + encodeURIComponent(item.CodigoBarra || item.Producto)).then(r=>r.json()).then(rows=>{
        if (!this.modalImagen || this.productoDetalle.IdProducto !== id) return;
        const detail = Array.isArray(rows) && rows.find(row=>String(row.id)===String(id));
        if (!detail) return;
        this.productoDetalle = Object.assign({}, detail, item);
        const photos = [detail.imagen, detail.imagen2, detail.imagen3].filter(Boolean);
        this.productoGaleria = photos.length ? Array.from(new Set(photos)) : [url];
      }).catch(()=>{});
    },
''' +s[b:]
s+='''\n<style>
.pos-product-detail{display:grid;grid-template-columns:minmax(0,1.1fr) minmax(0,1fr);gap:28px}.pos-product-gallery{min-width:0}.pos-product-detail .pos-product-image-stage{position:relative;height:440px;min-height:0;padding:12px}.pos-product-detail .imagenGrande{width:100%;height:100%;max-height:100%;object-fit:contain}.pos-gallery-prev,.pos-gallery-next{position:absolute;top:46%;border:1px solid #dbe5ec;border-radius:50%;background:white;width:42px;height:42px;color:#079b72;font-size:30px}.pos-gallery-prev{left:8px}.pos-gallery-next{right:8px}.pos-gallery-thumbs{display:flex;gap:10px;margin-top:12px;overflow-x:auto}.pos-gallery-thumbs button{padding:4px;background:white;border:2px solid #e5edf1;border-radius:12px;flex:0 0 80px;height:80px}.pos-gallery-thumbs button.selected{border-color:#00a77b}.pos-gallery-thumbs img{width:100%;height:100%;object-fit:contain}.pos-product-information>small{color:#098b75;font-weight:700}.pos-product-information h2{font-size:25px;line-height:1.3;margin:12px 0}.pos-detail-price{display:block;font-size:30px;color:#039878}.pos-detail-description{white-space:pre-line;overflow-wrap:anywhere;margin:22px 0;line-height:1.65}.pos-detail-attributes{display:grid;grid-template-columns:1fr 1fr;gap:10px}.pos-detail-attributes>div{background:#f0f7fa;border:1px solid #deebf0;border-radius:12px;padding:12px;overflow-wrap:anywhere}.pos-detail-attributes small,.pos-detail-attributes b{display:block}.pos-detail-stock{margin-top:20px;color:#07826c;font-weight:700}@media(max-width:767px){.pos-product-detail{grid-template-columns:1fr;gap:20px}.pos-product-detail .pos-product-image-stage{height:280px}.pos-product-information h2{font-size:21px}.pos-gallery-thumbs button{flex-basis:64px;height:64px}.pos-product-image-modal .modal-body{padding:12px!important}}
</style>
'''
p.write_text(s,encoding='utf-8')
