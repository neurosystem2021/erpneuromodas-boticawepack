<template>
  <div>
      <b-container fluid class="bv-example-row">
        <b-row>
          <b-col md="12"  class="my-1">
            <b-row>
              <b-col md="2">
                <div class="suma-stock text-center">
                  <b>Stock Valorizado</b>
                  <div> S/. {{ sumaValorizado | moneda }} </div>
                </div>
              </b-col>
              <b-col md="2">
                <div class="suma-stock text-center">
                  <b>Stock General</b>
                  <div> {{ sumaStock }} </div>
                </div>
              </b-col>
              <b-col md="2">
                <b-form-group>
                  <label>Almacen:</label>
                  <autocomplete
                      required="required"
                      ref="inputAlmacen"
                      :url="API + 'almacenes'"
                      anchor="Almacen"
                      initValue=""
                      label="writer"
                      placeholder=""
                      :on-select="setAlmacen" 
                      :onFocus="focusAutocomplete.bind(null, 'inputAlmacen')"
                      :classes="{input: 'form-control'}">
                  </autocomplete>
                </b-form-group>
              </b-col>
              <b-col md="2">
                <b-form-group label="Tipo de Muestra">
                  <b-form-radio-group button-variant="primary" buttons v-model="tipoStock" 
                    name="butons1" 
                    :options="[
                      {text: 'Stock > 0', value: 'mayor'},
                      {text: 'Stock <= 0', value: 'menor'}
                    ]"
                    @change="val => generateFilter('stock', val)"></b-form-radio-group>
                </b-form-group>
              </b-col>
              <b-col md="1">
                <b-form-group label="Limpiar ">
                  <b-button :disabled="!filter" @click="limpiarFiltros">Limpiar</b-button>
                </b-form-group>
                <br />
              </b-col>
              <!--<b-col md="1">
                <b-form-group label="Stock Mínimo">
                  <b-form-checkbox id="stock"
                     @change="val => generateFilter('minimo', val)"
                     :value="1"
                     :unchecked-value="0">

                  </b-form-checkbox>
                </b-form-group>
              </b-col>-->
              <b-col md="2">
                <b-form-group label="Mostrar">
                  <b-form-select :options="pageOptions" v-model="perPage" @input="currentPage = 1"/>
                </b-form-group>
              </b-col>
            </b-row>
          </b-col>
        </b-row>
        <b-row>
          <b-col md="12">
            <b-row>
              <b-col md="3">
                <b-form-group>
                  <label>Código de barra:</label>
                  <input ref="inputCodigoBarra" class="form-control" @keyup="(val) => generateFilter('codigo', val.target.value, true)"/>
                </b-form-group>
              </b-col>
            </b-row>
            <b-row>
              <br>
              <b-col md="3">
                <b-form-group>
                  <label>Categoría:</label>
                  <autocomplete
                    :url="API + 'categorias'"
                    ref="inputCategoria"
                    anchor="ProductoCategoria"
                    label="writer"
                    :on-select="val => generateFilter('categoria', val.ProductoCategoria)"
                    :onInput="(val) => !val ? generateFilter('categoria', val) : false"
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
                    :onInput="(val) => !val ? generateFilter('marca', val) : false"
                    :classes="{input: 'form-control upper'}">
                  </autocomplete>
                </b-form-group>
              </b-col>  
              <b-col md="3">
                <b-form-group>
                  <label>Modelo:</label>
                  <autocomplete
                    :url="API + 'modelos'"
                    ref="inputModelo"
                    anchor="ProductoModelo"
                    label="writer"
                    :on-select="val => generateFilter('modelo', val.ProductoModelo)"
                    :onInput="(val) => !val ? generateFilter('modelo', val) : false"
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
                  <td class="text-center table-info">{{ getStock(productoSelect.IdProducto) }}</td>
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
              :current-page="currentPage">

                <template slot="stock" slot-scope="data">
                  <div :class="getStock(data.item.IdProducto) > 0 ? 'table-success' : 'table-danger'" @click.stop="() => false">
                    {{ getStock(data.item.IdProducto) }}
                  </div>
                </template>

              </b-table>
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
              <b-button variant="success" size="lg" :href="'/api/index.php/reporte/stock?idAlmacen=' + almacen.IdAlmacen" target="_blank">Exportar</b-button>
            </b-col>
        </b-row>
        <br /><br />
      </b-container>

      <b-modal id="kardex" size="lg" modal-class="modal-xl" v-model="showKardex" title="Kardex de producto">
        <b-alert show variant="warning">{{ titleKardex }}</b-alert>
      <b-row>
        <b-col md="5">
          <b-form-group>
            <label>Fecha Inicio:</label>
            <b-form-input
              v-model="kardex.fechaDesde"
              type="date"
              step="1"
              placeholder="Fecha Inicio">
            </b-form-input>
          </b-form-group>
        </b-col>
        <b-col md="5">
          <b-form-group>
            <label>Fecha Fin:</label>
            <b-form-input
              v-model="kardex.fechaHasta"
              type="date"
              step="1"
              placeholder="Fecha Inicio">
            </b-form-input>
          </b-form-group>
        </b-col>
        <b-col md="2" class="text-center">
          <b-form-group>
            <br>
            <b-button @click="kardexFechaRango" variant="primary" size="lg">Generar</b-button>
          </b-form-group>
        </b-col>
      </b-row>
      <producto-kardex :producto="productoSelect" :kardex="kardexSelect"></producto-kardex>
      <b-row>
        <b-col class="text-center">
            <b-button variant="success" size="sm" :href="hrefXlsProducto" target="_blank">Exportar Excel Producto</b-button>
        </b-col>
      </b-row>
      <br>
    </b-modal>
  </div>
</template>

<script>
import config from '@/store/config.js'
import ProductoKardex from '@/components/ProductoKardex'

export default {
  mounted() {
    //this.loadFirstAlmacen()
    this.getAlmacenById()

    this.axios.get(this.API + 'productos/count').then((response) => {
      this.totalRows = response.data.total
    })
  },
  data() {
    return {
      API: config.API,
      sumaStock: '...',
      sumaValorizado: '...',
      APISTOCK: 'productos',
      tipoStock: '',
      showKardex: false,
      almacen: {
        IdAlmacen: 1
      },
      pageOptions: [ 5, 10, 15, 50, 100 ],
      filters: {},
      filter: '',
      sortBy: 'IdProducto',
      sortDesc: false,
      productoSelect: {},
      kardexSelect: [],
      items: [],
      itemsStock: [],
      isBusy: false,
      fields: [
            { key: 'CodigoBarra', label: 'Código', sortable: true },
            { key: 'ProductoCategoria', label: 'Categoría', sortable: true},
            { key: 'Producto', sortable: true },
            { key: 'ProductoMarca', label: 'Marca', sortable: true },
            { key: 'ProductoModelo', label: 'Modelo', sortable: true },
            { key: 'stock', sortable: false, variant: '', class:'text-center'}
      ],
      totalRows: 50,
      perPage: 10,
      currentPage: 1,
      kardex: {
        fechaDesde: this.$moment().format("YYYY-MM-DD"),
        fechaHasta: this.$moment().format("YYYY-MM-DD")
      }
    }
  },
  watch: {
    items (items) {
      //Vue.set(this.$refs.tablaProductos, 'localItems', [])
      //console.log(this.$refs.tablaProductos.localItems)
      //this.myProvider(false)

       this.axios.get(this.API + 'productos/puntoventa/stock', {
          params: {
            idProductos: items.map((item) => item.IdProducto),
            idAlmacen: this.almacen.IdAlmacen
          }
        }).then((response) => {
             this.itemsStock = response.data
        })
        .catch((error) => {
          console.log(error)
        })

        // Cargar la sumatoria
        this.getSumaStock()
        this.getSumaValorizado()

      /*console.log(items[0].hasOwnProperty('stock'))
      if (!items[0].hasOwnProperty('stock')) {
      //console.log(items)
        // ejecutar solo cuando los productos no tengan stock
        this.axios.get(this.API + 'productos/stock', {
          params: {
            idProductos: items.map((item) => item.IdProducto),
            idAlmacen: this.almacen.IdAlmacen
          }
        }).then((response) => {
         // console.log(response.data)
             this.items = []
        })
        .catch((error) => {
          console.log(error)
        })
      }*/
    }
  },
  computed: {
    titleKardex () {
      return 'KARDEX - / ' + this.productoSelect.CodigoBarra + ' / ' + this.productoSelect.Producto + ' / ' + 
      this.productoSelect.ProductoMarca + ' / ' + this.productoSelect.ProductoModelo 
    },
    hrefXlsProducto () {
      return ('/api/index.php/reporte/producto/kardex?idAlmacen=' + this.almacen.IdAlmacen + '&idProducto='+this.productoSelect.IdProducto+
      '&productoMarca='+this.productoSelect.ProductoMarca+'&producto='+this.productoSelect.Producto+
      '&codigoBarra='+this.productoSelect.CodigoBarra+'&fechaDesde='+this.kardex.fechaDesde+'&fechaHasta='+this.kardex.fechaHasta).replace('#','');
    }
  },
  methods: {
    /*loadFirstAlmacen () {
      this.axios.get(this.API + 'almacenes/primero')
        .then(res => {
          this.$refs.inputAlmacen.setValue(res.data.Almacen)
          this.setAlmacen(res.data)
          //this.almacen = res.data
        })
    },*/
    getAlmacenById(){
      let usuario = sessionStorage.getItem('User')
      this.axios.get(this.API + 'usuarios/' + usuario)
      .then((response) => {
        this.axios.get(this.API + 'almacenes/id/' + response.data.IdAlmacen)
          .then(res => {
            this.setAlmacen(response.data)
            this.$refs.inputAlmacen.setValue(res.data.Almacen)
            this.$refs.inputAlmacen.$refs.input.disabled = true
          })
      })
      .catch((error) => {
          console.log(error)
          this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
      })
    },
    getSumaValorizado () {
      this.axios.get(this.API + 'productos/puntoventa/stock' + '?idAlmacen=' + this.almacen.IdAlmacen + '&filter='+ (this.filter||'') + '&filter[stock]=mayor&sumaValorizado=1')
        .then( response => {
          this.sumaValorizado = (response.data[0].sumaValorizado);
        })
    },
    getSumaStock () {
      this.axios.get(this.API + 'productos/puntoventa/stock' + '?idAlmacen=' + this.almacen.IdAlmacen + '&filter='+ (this.filter||'') + '&filter[stock]=mayor&sumaStock=1')
        .then( response => {
          this.sumaStock = response.data[0].sumaStock
        })
    },
    getStock (idProducto) {
      let index = this.itemsStock.findIndex(item => item.IdProducto == idProducto)
      return this.itemsStock[index] && this.itemsStock[index].stock
    },
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
      Vue.nextTick(() => this.$refs.tablaProductos.refresh())
    },
    rowClick (item, index, e) {
      this.limpiarClassRows()
      e.target.parentNode.classList.add('table-info')

      this.productoSelect = item
    },
    rowDblClick (item) {
      this.kardexFechaRango();
      this.productoSelect = item
      this.showKardex = true
      this.kardex.fechaDesde = this.$moment().format("YYYY-MM-DD")
      this.kardex.fechaHasta = this.$moment().format("YYYY-MM-DD")

      this.axios.get(this.API + 'productos/kardex/' + this.productoSelect.IdProducto + '?limit=0', {
          params: {
              idAlmacen: this.almacen.IdAlmacen,
              fechaHasta: this.kardex.fechaHasta,
              fechaDesde: this.kardex.fechaDesde
          }
      }).then((response) => {
          this.kardexSelect = response.data
      })
    },
    kardexFechaRango(){
      if(this.kardex.fechaDesde !='' && this.kardex.fechaHasta != ''){
          this.axios.get(this.API + 'productos/kardex/' + this.productoSelect.IdProducto + '?limit=0', {
          params: {
              idAlmacen: this.almacen.IdAlmacen,
              fechaHasta: this.kardex.fechaHasta,
              fechaDesde: this.kardex.fechaDesde
          }
          }).then((response) => {
              this.kardexSelect = response.data
          })
      }
     
    },
    actualizarTabla() {
      this.$refs.tablaProductos.refresh()
    },
    myProvider (ctx) {
      // Here we don't set isBusy prop, so busy state will be handled by table itself
      // this.isBusy = true
      let url = this.API + this.APISTOCK + '?idAlmacen=' + this.almacen.IdAlmacen + '&limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter='+ (ctx.filter||'')+'&sortBy='+ctx.sortBy
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
      this.APISTOCK = 'productos'
      this.tipoStock = ''
      this.filters = {}
      this.filter = ''
      this.$refs.inputCodigoBarra.value=''
      this.$refs.inputCategoria.setValue('')
      this.$refs.inputProducto.setValue('')
      this.$refs.inputMarca.setValue('')
      this.productoSelect = {}
      this.limpiarClassRows()
    },
    generateFilter (key, value) {
      this.filters[key] = value
      
      let filter = ''
      for (var key in this.filters) {
        filter += '&filter[' + key + ']=' + this.filters[key]
        
        if(key == 'stock' || key == 'minimo') {
          // si la consulta se hace al stock jalar de otro endpoint
          this.APISTOCK = 'productos/puntoventa/stock'
        }
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
.suma-stock {
  background: #a6ccd2;
    color: #fff;
}
.suma-stock div {
  font-size: 32px;
  font-weight: bold;
}
@media (min-width: 992px) {
    .modal-xl .modal-dialog {
        max-width: 1200px;
    }
}
</style>
