<template>
    <div>
        <b-button @click="show=true" variant="primary" class="product-catalog-multiple__button">
            <i class="fas fa-barcode"></i>
            <span>Códigos múltiples</span>
        </b-button>
        
        <b-form @submit="imprimirCodigoMulti"> 
            <b-modal id="modalCodigoMulti" 
                size="lg" v-model="show"
                modal-class="barcode-multi-modal"
                dialog-class="barcode-multi-dialog"
                content-class="barcode-multi-content"
                header-class="barcode-multi-header"
                body-class="barcode-multi-body"
                footer-class="barcode-multi-footer"
                no-close-on-backdrop>

                <template slot="modal-title">
                    <div class="barcode-multi-title">
                        <span class="barcode-multi-title__icon"><i class="fas fa-barcode"></i></span>
                        <span>
                            <strong>Códigos de barras múltiples</strong>
                            <small>Prepara e imprime varias etiquetas en una sola operación</small>
                        </span>
                    </div>
                </template>

                <section class="barcode-multi-search">
                    <div class="barcode-multi-section-title">
                        <span class="barcode-multi-section-title__icon"><i class="fas fa-search"></i></span>
                        <span>
                            <strong>Agregar productos</strong>
                            <small>Busca por nombre, código, modelo, marca o presentación</small>
                        </span>
                    </div>
                    <div class="barcode-multi-autocomplete">
                             <autocomplete
                                 ref="autocompleteProductoCode"
                                 :url="API + 'productos'"
                                :onShouldRenderChild="renderChild"
                                :customParams="{sortBy: 'IdProducto', sortDesc: 0}"
                                anchor="Producto"
                                label="writer"
                                 placeholder="Agregar Producto"
                                 :on-select="agregarPro"
                                 :onFocus="focusAutocomplete.bind(null, 'autocompleteProductoCode')"
                                 :classes="{input: 'form-control'}">
                             </autocomplete>
                    </div>
                </section>

                <section class="barcode-multi-list">
                    <div class="barcode-multi-list__heading">
                        <div>
                            <strong>Productos seleccionados</strong>
                            <small>Ajusta la cantidad de etiquetas antes de imprimir</small>
                        </div>
                        <span class="barcode-multi-count">{{ productos.length }} {{ productos.length === 1 ? 'producto' : 'productos' }}</span>
                    </div>

                    <div class="barcode-multi-table-wrap" v-if="productos.length">
                        <table class="barcode-multi-table">
                            <thead>
                                <tr>
                                    <th>Cantidad</th>
                                    <th>Producto</th>
                                    <th>Marca</th>
                                    <th>Modelo</th>
                                    <th>Color</th>
                                    <th>Talla</th>
                                    <th>Género</th>
                                    <th>Precio</th>
                                    <th aria-label="Acciones"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(prod, index) in productos" :key="index">
                                    <td>
                                        <div class="barcode-multi-quantity">
                                            <button type="button" @click="prod.cantidad > 1 && prod.cantidad--" aria-label="Disminuir cantidad">−</button>
                                            <input required min="1" type="number" v-model="prod.cantidad"/>
                                            <button type="button" @click="prod.cantidad++" aria-label="Aumentar cantidad">+</button>
                                        </div>
                                    </td>
                                    <td><strong class="barcode-multi-product">{{prod.Producto}}</strong></td>
                                    <td>{{prod.ProductoMarca}}</td>
                                    <td>{{prod.ProductoModelo}}</td>
                                    <td>{{prod.Color}}</td>
                                    <td>{{prod.ProductoTalla}}</td>
                                    <td>{{prod.Genero}}</td>
                                    <td><span class="barcode-multi-price">S/ {{prod.PrecioContado>0?prod.PrecioContado:0}}</span></td>
                                    <td>
                                        <button type="button" class="barcode-multi-delete" @click="quitarProducto(index)" aria-label="Eliminar producto">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="barcode-multi-empty" v-else>
                        <span class="barcode-multi-empty__icon"><i class="fas fa-barcode"></i></span>
                        <strong>Aún no agregaste productos</strong>
                        <small>Usa el buscador superior para preparar tus etiquetas.</small>
                    </div>
                </section>

                <div class="barcode-multi-actions">
                    <button class="barcode-multi-print" :disabled="productos.length==0" type="submit">
                        <i class="fas fa-print"></i>
                        <span>Imprimir códigos de barra</span>
                        <small v-if="productos.length">{{ productos.length }} seleccionados</small>
                    </button>
                </div>

                <div slot="modal-footer" class="barcode-multi-footer__inner">
                    <span><i class="fas fa-shield-alt"></i> Verifica las cantidades antes de imprimir</span>
                    <button type="button" class="barcode-multi-close" @click="show=false">
                        <i class="fas fa-times"></i> Cerrar
                    </button>
                </div>
            </b-modal>
        </b-form>
    </div>
</template>
<script>
import config from '@/store/config.js'
    export default {
        mounted () {
        },
        data () {
            return {
                API: config.API,
                show: false,
                productos:[]
            }
        },
        watch: {
            show (val) {
                if(val==true){
                    this.productos = []
                    this.$refs.autocompleteProductoCode.setValue('')
                }
            },
        },
        methods: {
            renderChild(data) {
                return `
                    <b>${data.Producto}</b> / ${data.ProductoCategoria} / ${data.ProductoMarca} / ${data.ProductoModelo} / ${data.ProductoTalla} / ${data.Color} / ${data.Genero} - <b> S/ ${data.PrecioContado>0?data.PrecioContado:0}</b>
                `
            },
            focusAutocomplete (ref) {
                this.$refs[ref].type = ""
                this.$refs[ref].handleDoubleClick()
            },
            agregarPro(obj){
                let index = this.productos.findIndex(prod => prod.IdProducto == obj.IdProducto)
                if (index >= 0) {
                    this.$toasted.show('El producto ya está agregado')
                    return
                }
                obj.cantidad = 1
                this.productos.push(obj)
                this.$refs.autocompleteProductoCode.setValue('') 
            },
            quitarProducto(index){
                this.productos.splice(index, 1)
            },
            imprimirCodigoMulti(evt){
                evt.preventDefault()
                let finalArray = this.productos.map((er)=>{
                    return [parseInt(er.IdProducto),parseInt(er.cantidad)]
                })
                let stringJson = JSON.stringify(finalArray)
                window.open('/views/codigoBarra.php?array=' +stringJson,'_blank');
            }
        }
    }
</script>
<style></style>
