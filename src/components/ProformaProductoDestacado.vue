<template>
 <article class="quote-featured-product">
  <div class="featured-photo"><img v-if="product.Imagen &amp;&amp; !imageMissing" :src="'/resources/images/productos/'+encodeURIComponent(product.Imagen)" :alt="product.Producto" @error="imageMissing=true"><span v-else class="featured-no-photo">Imagen no disponible</span></div>
  <div class="featured-copy"><span class="featured-eyebrow">PRODUCTO {{String(index+1).padStart(2,'0')}}</span><h2>{{product.Producto}}</h2><h3 v-if="details.length || specs.length">CARACTERÍSTICAS DEL PRODUCTO</h3><p v-for="(detail,i) in details" :key="'detail-'+i">{{detail}}</p><ul v-if="specs.length"><li v-for="(item,i) in specs" :key="i">{{item}}</li></ul></div>
  <div class="featured-pricing"><div><span>CANTIDAD</span><strong>{{product.Cantidad}}</strong></div><template v-if="Number(product.DisabledTotal || 0)===0"><div><span>PRECIO UNITARIO</span><strong>{{money(product.Precio)}}</strong></div><div v-if="Number(product.Descuento)>0"><span>DESCUENTO</span><strong>{{money(product.Descuento)}}</strong></div><div class="featured-amount"><span>IMPORTE</span><strong>{{money(product.Cantidad*product.Precio-Number(product.Descuento || 0))}}</strong></div></template></div>
 </article>
</template>
<script>
export default {
 props:{product:{type:Object,required:true},index:{type:Number,default:0}},
 data(){return {imageMissing:false};},
 computed:{
  technicalParts(){return String(this.product.Descripcion || '').split('Características técnicas:\n');},
  details(){const description=this.technicalParts.length>1?this.technicalParts[0]:this.product.Descripcion;return [...new Set([this.product.ProductoDesc,this.product.ProductoDesc2,this.product.ProductoDesc3,description].map(v=>String(v || '').trim()).filter(Boolean))];},
  specs(){return (this.technicalParts[1] || '').split(/\r?\n/).map(v=>v.replace(/^\d+[.)]\s*/,'').trim()).filter(Boolean);}
 },
 watch:{'product.Imagen'(){this.imageMissing=false;}},
 methods:{money(value){return 'S/ '+Number(value).toLocaleString('es-PE',{minimumFractionDigits:2,maximumFractionDigits:2});}}
};
</script>
