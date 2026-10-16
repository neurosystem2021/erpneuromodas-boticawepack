from pathlib import Path
p=Path('src/components/ProductoImagenesReferencia.vue');s=p.read_text(encoding='utf-8-sig');p.write_text(s,encoding='utf-8')
p=Path('src/components/ProductoAsesorIA.vue');s=p.read_text(encoding='utf-8').replace("import {guideUrl", "import ProductoImagenesReferencia from './ProductoImagenesReferencia.vue';\nimport {guideUrl",1).replace('export default {data()',"export default {components:{ProductoImagenesReferencia},data()",1);s=s.replace(' <section class="product-guide-usage">', ''' <producto-imagenes-referencia v-if="show && settings" :product-id="product.IdProducto" :product-name="product.Producto" :model="draft.model" :csrf="settings.csrf" :can-edit="canEdit" />
 <section class="product-guide-usage">''',1);p.write_text(s,encoding='utf-8')
