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

              <b-table small
              ref="tablaProductos"
              :sort-by.sync="sortBy"
              :sort-desc.sync="sortDesc"
              striped
              hover
              id="tabla-productos" 
              :busy.sync="isBusy" 
              :items="myProvider" 
              :fields="fields"
              :per-page="perPage"
              :current-page="currentPage">
              </b-table>
          </b-col>
          <b-row>
              <b-col md="6" class="my-1">
                  <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" />
              </b-col>
          </b-row>
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
                        { key: 'ProductoCategoria', label: 'Categoría', sortable: true, thStyle: { width: '20%'}},
                        { key: 'Producto', sortable: true, thStyle: { width: '20%'}},
                        { key: 'ProductoMarca', label: 'Laboratorio', sortable: true },
                        { key: 'ProductoFormaFarmaceutica', label: 'Forma Farmacéutica', sortable: true, thStyle: { width: '10%'} },
                        { key: 'FechaVen', sortable: true, variant: '', class:'text-center'},
                        { key: 'stock', sortable: false, variant: '', class:'text-center'}
                ],
                items:[],
                totalRows: 50,
                perPage: 10,
                almacen: {
                    IdAlmacen: 1,
                    Almacen: 'PRINCIPAL'
                },
                currentPage: 1,
                pageOptions: [ 5, 10, 15, 50, 100 ],
                sortBy: 'IdProducto',
                sortDesc: false,
                productoSelect: {},
                kardexSelect: [],
                items: [],
                itemsStock: [],
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
            myProvider(ctx) {
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
        }
    }
</script>
<style>
</style>