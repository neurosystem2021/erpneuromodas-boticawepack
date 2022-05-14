<template>
    <div>
        <b-button @click="show=true" variant="primary">Códigos Múltiples</b-button>
        
        <b-form @submit="imprimirCodigoMulti"> 
            <b-modal id="modalCodigoMulti" 
                title="Codigos de Barras Múltiples" 
                size="lg" v-model="show"
                header-bg-variant="dark"
                headerTextVariant="light"
                body-bg-variant="light"
                body-text-variant="dark"
                no-close-on-backdrop>
                
                <b-row>
                    <b-col md="12">
                        <b-form-group>
                            <label>Agregar Productos:</label>
                            <b-input-group>
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
                            </b-input-group>
                        </b-form-group>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col md="12">
                        <table class="table table-striped b-table table-sm">
                            <thead>
                                <tr>
                                    <th style="width:6%">CANTIDAD</th>
                                    <th class="text-center">PRODUCTO</th>
                                    <th class="text-center">MARCA</th>
                                    <th class="text-center">MODELO</th>
                                    <th class="text-center">COLOR</th>
                                    <th class="text-center">TALLA</th>
                                    <th class="text-center">GENERO</th>
                                    <th class="text-center">BOTAPIE</th>
                                    <th class="text-center">PRECIO</th>
                                    <th></th>
                                </tr>
                                
                            </thead>
                            <tbody>
                                <tr v-for="(prod, index) in productos" :key="index">
                                    <td style="width:6%"><input required min="1" type="number" class="form-control" v-model="prod.cantidad"/></td>
                                    <td class="text-center">{{prod.Producto}}</td>
                                    <td class="text-center">{{prod.ProductoMarca}}</td>
                                    <td class="text-center">{{prod.ProductoModelo}}</td>
                                    <td class="text-center">{{prod.Color}}</td>
                                    <td class="text-center">{{prod.ProductoTalla}}</td>
                                    <td class="text-center">{{prod.Genero}}</td>
                                    <td class="text-center">{{prod.Botapie}}</td>
                                    <td class="text-center">{{prod.PrecioContado>0?prod.PrecioContado:0}}</td>
                                    <td><b-button variant="danger" size="sm" @click="quitarProducto(index)"><i class="fas fa-times fa-lg"></i></b-button></td>
                                </tr>
                            </tbody>
                        </table>
                    </b-col>
                </b-row>
                <b-row>
                    <b-col md="12" class="text-center">
                        <b-button :disabled="productos.length==0" variant="success" type="submit">Imprimir Códigos de Barra</b-button>
                    </b-col>
                </b-row>
                <div slot="modal-footer" >
                    <b-btn size="sm" variant="secondary" @click="show=false">
                        Cerrar
                    </b-btn>
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
                    <b>${data.Producto}</b> / ${data.ProductoCategoria} / ${data.ProductoMarca} / ${data.ProductoModelo} / ${data.ProductoTalla} / ${data.Color} / ${data.Genero} / ${data.Botapie} - <b> S/ ${data.PrecioContado>0?data.PrecioContado:0}</b>
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
<style>
    #modalCodigoMulti .modal-dialog{
    width: 85% !important;
  }
</style>
