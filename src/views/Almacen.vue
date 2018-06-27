<template>
  <div>
      <b-container class="bv-example-row">
        <b-row>
          <b-col md="6"  offset-md="6" class="my-1">
            <b-row>
              <b-col md="4">
                <b-form-group label="Tipo de Muestra">
                  <b-form-checkbox-group button-variant="primary" buttons v-model="tipoStock" 
                    name="butons1" 
                    :options="[
                      {text: 'Stock > 0', value: 'mayor'},
                      {text: 'Stock <= 0', value: 'menor'}
                    ]"
                    @input="val => generateFilter('stock', val)"></b-form-checkbox-group>
                </b-form-group>
              </b-col>
              <b-col md="4" class="text-center">
                <b-button :disabled="!filter" @click="limpiarFiltros">Limpiar</b-button>
                <br />
              </b-col>
              <b-col md="4">
                <b-form-select :options="pageOptions" v-model="perPage" @input="currentPage = 1"/>
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
                    :onInput="val => generateFilter('categoria', val)"
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
                    :onInput="val => generateFilter('marca', val)"
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
                    :onInput="val => generateFilter('modelo', val)"
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
                    :onInput="val => generateFilter('color', val)"
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
                    :onInput="val => generateFilter('talla', val)"
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
                    :onInput="val => generateFilter('genero', val)"
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
                    :onInput="val => generateFilter('botapie', val)"
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
                  <th>Precio de compra</th>
                  <th>Precio de venta</th>
                  <th>Stock</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td>{{ productoSelect.Producto }}</td>
                  <td>{{ productoSelect.PrecioCosto }}</td>
                  <td>{{ productoSelect.PrecioContado }}</td>
                  <td>{{ productoSelect.stock }}</td>
                </tr>
              </tbody>
            </table>
            <br />
          </b-col>
          

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
            <b-row>
                <b-col md="6" class="my-1">
                    <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" />
                </b-col>
            </b-row>
        </b-row>
        <br />
        <b-row align-h="center" class="text-center">
            <b-col cols="4">
              <b-button variant="success" size="lg" @click="exportarProductos">Exportar</b-button>
            </b-col>
        </b-row>
        <br /><br />
      </b-container>

      <b-modal id="kardex" size="xl" v-model="showKardex" title="Kardex de producto">
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
      tipoStock: [],
      showKardex: false,
      idAlmacen: 1,
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
            { key: 'stock', sortable: false }
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
      rowClick (item, index, e) {
        let rows = this.$refs.tablaProductos.$el.rows
        Object.keys(rows).forEach(function (key) {
          rows[key].classList.remove('table-info')
        });

        e.target.parentNode.classList.add('table-info')

        this.productoSelect = item
      },
      rowDblClick (item) {
        this.productoSelect = item
        this.showKardex = true

        this.axios.get(this.API + 'productos/kardex/' + this.productoSelect.IdProducto + '?limit=0', {
            params: {
                idAlmacen: this.idAlmacen,
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
        let url = this.API + 'productos/stock?idAlmacen=' + this.idAlmacen + '&limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter='+ (ctx.filter||'')+'&sortBy='+ctx.sortBy
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
        this.tipoStock = []
        this.filter = ''
        this.$refs.inputCategoria.setValue('')
        this.$refs.inputProducto.setValue('')
        this.$refs.inputMarca.setValue('')
        this.$refs.inputModelo.setValue('')
        this.$refs.inputColor.setValue('')
        this.$refs.inputTalla.setValue('')
        this.$refs.inputGenero.setValue('')
        this.$refs.inputBotapie.setValue('')
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
    .modal-xl {
        max-width: 1200px;
    }
}
</style>
