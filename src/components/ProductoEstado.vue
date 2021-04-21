<template>
    <div> 
        <b-row class="mb-1"> 
            <b-col md="12">
                <b-form-group>
                <label>Almacen:</label>
                <autocomplete
                    required="required"
                    ref="inputAlmacen"
                    :url="API + 'almacenes'"
                    anchor="Almacen"
                    initValue="PRINCIPAL"
                    label="writer"
                    placeholder=""
                    :on-select="setAlmacen" 
                    :onFocus="focusAutocomplete.bind(null, 'inputAlmacen')"
                    :classes="{input: 'form-control'}">
                </autocomplete>
                </b-form-group>
            </b-col> 
            <b-col md="6" style="border-right: 1px solid #E9ECEF">
                <h5 class="mb-3 text-center">Productos a vencer (Próximos 2 meses) </h5>
                <b-table small
                ref="tablaProductos"
                :sort-by.sync="sortBy"
                :sort-desc.sync="sortDesc"
                striped
                hover
                id="tabla-productos" 
                :busy.sync="isBusy" 
                :items="myProviderVenc" 
                :fields="fields"
                :per-page="perPage"
                :current-page="currentPage">
                </b-table>
                <b-row>
                    <b-col md="6" class="my-1">
                    <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" />
                    </b-col>
                </b-row>     
             </b-col>
             <b-col md="6"  style="border-left: 1px solid #E9ECEF">
                <h5 class="mb-3 text-center">Productos bajos en stock (según Stock Mínimo) </h5>
                <b-table small
                ref="tablaProductos2"
                :sort-by.sync="sortBy2"
                :sort-desc.sync="sortDesc2"
                striped
                hover
                id="tabla-productos2" 
                :busy.sync="isBusy" 
                :items="myProviderStock" 
                :fields="fields2"
                :per-page="perPage2"
                :current-page="currentPage2">
                </b-table>
                <b-row>
                    <b-col md="6" class="my-1">
                    <b-pagination :total-rows="totalRows" :per-page="perPage2" v-model="currentPage2" class="my-0" />
                    </b-col>
                </b-row>     
             </b-col>
        </b-row>
    </div>
</template>
<script>
import config from '@/store/config.js'
    export default {
        mounted () {
        this.axios.get(this.API + 'productos/count').then((response) => {
            this.totalRows = response.data.total
        })
        this.$refs.inputAlmacen.$refs.input.disabled = true
        },
        data () {
            return {
                API: config.API,
                fields: [
                        { key: 'CodigoBarra', label: 'Código', sortable: true },
                        { key: 'Producto', sortable: true, thStyle: { width: '20%'}},
                        { key: 'ProductoMarca', label: 'Laboratorio', sortable: true },
                        { key: 'FechaVen', sortable: true, variant: '', class:'text-center'},
                        { key: 'stock', sortable: true, variant: '', class:'text-center'}
                ],
                fields2: [
                        { key: 'CodigoBarra', label: 'Código', sortable: true },
                        { key: 'Producto', sortable: true, thStyle: { width: '20%'}},
                        { key: 'ProductoMarca', label: 'Laboratorio', sortable: true },
                        { key: 'StockMinimo', sortable: true, variant: '', class:'text-center'},
                        { key: 'stock', label: 'Stock Actual', sortable: true, variant: '', class:'text-center'}
                ],
                items:[],
                totalRows: 50,
                perPage: 7,
                perPage2: 7,
                almacen: {
                    IdAlmacen: 1,
                    Almacen: 'PRINCIPAL'
                },
                currentPage: 1,
                currentPage2: 1,
                cur:2,
                sortBy: 'IdProducto',
                sortDesc: false,
                sortBy2: 'IdProducto',
                sortDesc2: false,
                productoSelect: {},
                kardexSelect: [],
                items: [],
                isBusy: false,
                idAlmacen:0,
            }
        },
        computed:{

        },
        methods: {
            setAlmacen (obj) {
                console.log(obj)
                this.almacen = obj
                this.limpiarFiltros()
                console.log(this.filter)
                Vue.nextTick(() => this.$refs.tablaProductos.refresh())
            },
            focusAutocomplete (ref) {
            // this.$refs[ref].type = ""
            this.$refs[ref].handleDoubleClick()
            },
            myProviderVenc(ctx) {
            let url = this.API + 'productos/stock/'+this.almacen.IdAlmacen  + '?vencido=2&limit='+ctx.perPage+'&page='+ctx.currentPage+'&sortBy='+ctx.sortBy
            url += '&sortDesc='+(ctx.sortDesc ? 1 : 0 )
            let promise = this.axios.get( url )

            return promise.then((result) => {
                this.items = result.data
                //this.totalRows = this.items.length
                // Here we could override the busy state, setting isBusy to false
                // this.isBusy = false
               return(this.items)
            }).catch(error => {
                // Here we could override the busy state, setting isBusy to false
                // this.isBusy = false
                // Returning an empty array, allows table to correctly handle busy state in case of error
                return []
            })
            },
            myProviderStock(ctx2) {
            console.log(ctx2);
            let url = this.API + 'productos/stock/'+this.almacen.IdAlmacen  + '?filter[minimo]=1&limit='+ctx2.perPage+'&page='+ctx2.currentPage+'&sortBy='+ctx2.sortBy
            url += '&sortDesc='+(ctx2.sortDesc ? 1 : 0 )
            let promise = this.axios.get( url )

            return promise.then((result) => {
                this.items = result.data
                //this.totalRows = this.items.length
                // Here we could override the busy state, setting isBusy to false
                // this.isBusy = false
               return(this.items)
            }).catch(error => {
                // Here we could override the busy state, setting isBusy to false
                // this.isBusy = false
                // Returning an empty array, allows table to correctly handle busy state in case of error
                return []
            })
            },
        }
    }
</script>
<style>
</style>