<template>
  <div>
      <b-container fluid class="bv-example-row">
        <b-row>
          <b-col md="7"  offset-md="5" class="my-1">
            <b-row>
              <b-col md="3">
                <b-form-group>
                  <label>Almacen:</label>
                  <autocomplete
                      required="required"
                      ref="inputAlmacen"
                      :url="API + 'almacenes'"
                      anchor="Almacen"
                      initValue="VENTA"
                      label="writer"
                      placeholder=""
                      :on-select="setAlmacen" 
                      :onFocus="focusAutocomplete.bind(null, 'inputAlmacen')"
                      :classes="{input: 'form-control'}">
                  </autocomplete>
                </b-form-group>
              </b-col>
              <b-col md="3">
                <b-form-group label="Tipo de Muestra">
                  <b-form-radio-group button-variant="primary" buttons v-model="tipoStock" 
                    name="butons1" 
                    :options="[
                      {text: 'Stock > 0', value: 'mayor'},
                      {text: 'Stock <= 0', value: 'menor'}
                    ]"
                    @input="val => generateFilter('stock', val)"></b-form-radio-group>
                </b-form-group>
              </b-col>
              <b-col md="3">
                <b-form-group label="Limpiar Filtros">
                  <b-button :disabled="!filter" @click="limpiarFiltros">Limpiar</b-button>
                </b-form-group>
                <br />
              </b-col>
              <b-col md="3">
                <b-form-group label="Mostrar">
                  <b-form-select :options="pageOptions" v-model="perPage" @input="currentPage = 1"/>
                </b-form-group>
              </b-col>
            </b-row>
          </b-col>
        </b-row>
        <b-row>
          <b-col md="6">
            <b-row>
              <b-col md="3">
                <b-form-group>
                  <label>Categoría:</label>
                  <autocomplete
                    :url="API + 'categorias'"
                    ref="inputCategoria"
                    anchor="ProductoCategoria"
                    label="writer"
                    :on-select="val => generateFilter('categoria', val.ProductoCategoria)"
                    :classes="{input: 'form-control upper'}">
                  </autocomplete>
                </b-form-group>
              </b-col>
              <b-col md="3">
                <b-form-group>
                  <label>Producto:</label>
                  <autocomplete
                    :url="API + 'productos'"
                    ref="inputProducto"
                    anchor="Producto"
                    label="writer"
                    :on-select="val => generateFilter('producto', val.Producto)"                    
                    :onInput="val => generateFilter('producto', val)"
                    :classes="{input: 'form-control upper'}">
                  </autocomplete>
                </b-form-group>
              </b-col>
              <b-col md="3">
                <b-form-group>
                  <label>Marca:</label>
                  <autocomplete
                    :url="API + 'marcas'"
                    ref="inputMarca"
                    anchor="ProductoMarca"
                    label="writer"
                    :on-select="val => generateFilter('marca', val.ProductoMarca)"
                    :classes="{input: 'form-control upper'}">
                  </autocomplete>
                </b-form-group>
              </b-col>  
              <b-col md="3">
                <b-form-group>
                  <label>Modelo:</label>
                  <autocomplete
                    :url="API + 'productos/modelos'"
                    ref="inputModelo"
                    anchor="ProductoModelo"
                    label="writer"
                    :on-select="val => generateFilter('modelo', val.ProductoModelo)"
                    :classes="{input: 'form-control upper'}">
                  </autocomplete>
                </b-form-group>
              </b-col>
            </b-row>
          </b-col>
          <b-col md="6">
            <b-row>
              <b-col md="3">
                <b-form-group>
                  <label>Color:</label>
                  <autocomplete
                    :url="API + 'productos/colores'"
                    ref="inputColor"
                    anchor="Color"
                    label="writer"
                    :on-select="val => generateFilter('color', val.Color)"
                    :classes="{input: 'form-control upper'}">
                  </autocomplete>
                </b-form-group>
              </b-col>  
              <b-col md="3">
                <b-form-group>
                  <label>Talla:</label>
                  <autocomplete
                    :url="API + 'tallas'"
                    ref="inputTalla"
                    anchor="ProductoTalla"
                    label="writer"
                    :on-select="val => generateFilter('talla', val.ProductoTalla)"
                    :classes="{input: 'form-control upper'}">
                  </autocomplete>
                </b-form-group>
              </b-col> 
              <b-col md="3">
                <b-form-group>
                  <label>Genero:</label>
                  <autocomplete
                    :url="API + 'productos/generos'"
                    ref="inputGenero"
                    anchor="Genero"
                    label="writer"
                    :on-select="val => generateFilter('genero', val.Genero)"
                    :classes="{input: 'form-control upper'}">
                  </autocomplete>
                </b-form-group>
              </b-col>
              <b-col md="3">
                <b-form-group>
                  <label>Botapie/Manga:</label>
                  <autocomplete
                    :url="API + 'productos/botapies'"
                    ref="inputBotapie"
                    anchor="Botapie"
                    label="writer"
                    :on-select="val => generateFilter('botapie', val.Botapie)"
                    :classes="{input: 'form-control upper'}">
                  </autocomplete>
                </b-form-group>
              </b-col> 
            </b-row>
          </b-col>
        </b-row>
        <b-row>
          <b-col md="12" class="my-1">
            <table class="table border b-table table-footer table-sm table-detalle">
              <thead>
                <tr>
                  <th>Producto</th>
                  <th class="text-center">Precio de compra</th>
                  <th class="text-center">Precio de venta</th>
                  <th class="text-center">Stock</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>{{ productoSelect.Producto }}</td>
                  <td class="text-center">{{ productoSelect.PrecioCosto }}</td>
                  <td class="text-center">{{ productoSelect.PrecioContado }}</td>
                  <td class="text-center table-info">{{ productoSelect.stock }}</td>
                </tr>
              </tbody>
            </table>
          </b-col>
          
          <b-col md="12">
            <b-table small
              ref="tablaProductos"
              :filter="filter"
              :sort-by.sync="sortBy"
              :sort-desc.sync="sortDesc"
              striped
              hover
              @row-clicked="rowClick"
              @row-dblclicked="rowDblClick"
              id="tabla-productos" 
              :busy.sync="isBusy" 
              :items="myProvider" 
              :fields="fields"
              :per-page="perPage"
              :current-page="currentPage"></b-table>
          </b-col>
          <b-row>
              <b-col md="6" class="my-1">
                  <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" />
              </b-col>
          </b-row>
        </b-row>
        <br />
        <b-row align-h="center" class="text-center">
            <b-col cols="4">
              <b-button variant="success" size="lg" :href="'/api/index.php/reporte/stock?idAlmacen=' + almacen.IdAlmacen">Exportar</b-button>
            </b-col>
        </b-row>
        <br /><br />
      </b-container>

      <b-modal id="kardex" size="lg" modal-class="modal-xl" v-model="showKardex" title="Kardex de producto">
        <b-alert show variant="warning">{{ titleKardex }}</b-alert>
        <producto-kardex :producto="productoSelect" :kardex="kardexSelect"></producto-kardex>
      </b-modal>
  </div>
</template>

<script>
import config from '@/store/config.js'
import ProductoKardex from '@/components/ProductoKardex'

export default {
  mounted() {
    this.axios.get(this.API + 'productos/count').then((response) => {
      this.totalRows = response.data.total
    })
  },
  data() {
    return {
      API: config.API,
      tipoStock: '',
      showKardex: false,
      almacen: {
        IdAlmacen: 1,
        Almacen: 'VENTA'
      },
      pageOptions: [ 5, 10, 15, 50, 100 ],
      filters: {},
      filter: '',
      sortBy: 'IdProducto',
      sortDesc: false,
      productoSelect: {},
      kardexSelect: [],
      items: [],
      isBusy: false,
      fields: [
            { key: 'CodigoBarra', label: 'Código', sortable: true },
            { key: 'ProductoCategoria', label: 'Categoría', sortable: true},
            { key: 'Producto', sortable: true },
            { key: 'ProductoMarca', label: 'Marca', sortable: true },
            { key: 'ProductoModelo', label: 'Modelo', sortable: true },
            { key: 'Color', sortable: true },
            { key: 'ProductoTalla', label: 'Talla', sortable: true },
            { key: 'Genero', sortable: true },
            { key: 'Botapie', sortable: true },
            { key: 'stock', sortable: false, variant: 'danger', class:'text-center'}
      ],
      totalRows: 50,
      perPage: 10,
      currentPage: 1
    }
  },
  computed: {
    titleKardex () {
      return 'KARDEX - / ' + this.productoSelect.CodigoBarra + ' / ' + this.productoSelect.Producto + ' / ' + 
      this.productoSelect.ProductoMarca + ' / ' + this.productoSelect.ProductoModelo + ' / ' + this.productoSelect.Color
      this.productoSelect.ProductoTalla 
    }
  },
  methods: {
    focusAutocomplete (ref) {
      // this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick()
    },
    limpiarClassRows () {
      let rows = this.$refs.tablaProductos.$el.rows
      Object.keys(rows).forEach(function (key) {
        rows[key].classList.remove('table-info')
      });
    },
    setAlmacen (obj) {
      this.almacen = obj
      this.limpiarFiltros()
      console.log(this.filter)
      Vue.nextTick(() => this.$refs.tablaProductos.refresh())
    },
    rowClick (item, index, e) {
      this.limpiarClassRows()
      e.target.parentNode.classList.add('table-info')

      this.productoSelect = item
    },
    rowDblClick (item) {
      this.productoSelect = item
      this.showKardex = true

      this.axios.get(this.API + 'productos/kardex/' + this.productoSelect.IdProducto + '?limit=0', {
          params: {
              idAlmacen: this.almacen.IdAlmacen,
              fechaHasta: this.$moment().format("YYYY-MM-DDTH:mm:ss")
          }
      }).then((response) => {
          this.kardexSelect = response.data
      })
    },
    actualizarTabla() {
      this.$refs.tablaProductos.refresh()
    },
    myProvider (ctx) {
      // Here we don't set isBusy prop, so busy state will be handled by table itself
      // this.isBusy = true
      let url = this.API + 'productos/stock?idAlmacen=' + this.almacen.IdAlmacen + '&limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter='+ (ctx.filter||'')+'&sortBy='+ctx.sortBy
      console.log(url)
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
    limpiarFiltros () {
      this.tipoStock = ''
      this.filters = {}
      this.filter = ''
      this.$refs.inputCategoria.setValue('')
      this.$refs.inputProducto.setValue('')
      this.$refs.inputMarca.setValue('')
      this.$refs.inputModelo.setValue('')
      this.$refs.inputColor.setValue('')
      this.$refs.inputTalla.setValue('')
      this.$refs.inputGenero.setValue('')
      this.$refs.inputBotapie.setValue('')

      this.productoSelect = {}
      this.limpiarClassRows()
    },
    generateFilter (key, value) {
      this.filters[key] = value
      
      let filter = ''
      for (var key in this.filters) {
        filter += '&filter[' + key + ']=' + this.filters[key]
      }

      this.filter = filter
    },
    exportarProductos () {

    }
  },
  components: {
    ProductoKardex
  }
}
</script>

<style scope>
#tabla-productos tr {
  cursor: pointer;
}
.table-detalle {
  height: 80px;
}
.table-detalle th {
  color: #fff;
  background-color: #6c757d; 
}
.table-detalle tbody td {
  font-size: 18px;
  font-weight: bold;
  color: #2980b9;
}
.table-detalle thead th:first-child {
  max-width: 300px;
}
@media (min-width: 992px) {
    .modal-xl .modal-dialog {
        max-width: 1200px;
    }
}
</style>
