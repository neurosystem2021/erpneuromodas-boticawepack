<template>
  <div>
      <b-container fluid class="bv-example-row">
        <b-row>
          <b-col md="12"  class="my-1">
            <b-row>
              <b-col md="2" v-if="(idPerfil !== 1)">
                <div class="suma-stock text-center">
                  <b>Stock <br> Valorizado</b>
                  <br />
                  <button class="btn btn-warning mb-2" v-if="!verValorizado" @click="clickVerValorizado">ver</button>
                  <div v-if="verValorizado"> S/. {{ sumaValorizado | moneda }} </div>
                </div>
              </b-col>
              <b-col md="2" v-if="(idPerfil !== 1)">
                <div class="suma-stock text-center">
                  <b>Stock Valorizado<br> Sin Utilidad</b>
                  <br />
                  <button class="btn btn-warning mb-2" v-if="!verValorizado" @click="clickVerValorizado">ver</button> 
                  <div v-if="verValorizado"> S/. {{ sumaValorizadoSin | moneda }} </div>
                </div>
              </b-col>
              <b-col md="2">
                <div class="suma-stock text-center">
                  <b>Stock <br>General</b>
                  <div> {{ sumaStock | redondear(3) }} </div>
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
                      initValue="PRINCIPAL"
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
              <b-col md="1">
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
              <!--<b-col md="3">
                <b-form-group>
                  <label>Codigo de Barra:</label>
                  <autocomplete
                    :url="API + 'marcas'"
                    ref="inputCodigoBarra"
                    anchor="CodigoBarra"
                    label="writer"
                    :on-select="val => generateFilter('marca', val.ProductoMarca)"
                    :onInput="(val) => !val ? generateFilter('marca', val) : false"
                    :classes="{input: 'form-control upper'}">
                  </autocomplete>
                </b-form-group>
              </b-col>-->
              <b-col md="3">
                <!--<b-form-group>
                  <label>Modelo:</label>
                  <autocomplete
                    :url="API + 'productos/modelos'"
                    ref="inputModelo"
                    anchor="ProductoModelo"
                    label="writer"
                    :on-select="val => generateFilter('modelo', val.ProductoModelo)"
                    :onInput="(val) => !val ? generateFilter('modelo', val) : false"
                    :classes="{input: 'form-control upper'}">
                  </autocomplete>
                </b-form-group>-->
              </b-col>
            </b-row>
          </b-col>
          <b-col md="6">
            <b-row>
              <b-col md="3">
                <!--<b-form-group>
                  <label>Color:</label>
                  <autocomplete
                    :url="API + 'productos/colores'"
                    ref="inputColor"
                    anchor="Color"
                    label="writer"
                    :on-select="val => generateFilter('color', val.Color)"
                    :onInput="(val) => !val ? generateFilter('color', val) : false"
                    :classes="{input: 'form-control upper'}">
                  </autocomplete>
                </b-form-group>-->
              </b-col>  
              <b-col md="3">
                <!--<b-form-group>
                  <label>Talla:</label>
                  <autocomplete
                    :url="API + 'tallas'"
                    ref="inputTalla"
                    anchor="ProductoTalla"
                    label="writer"
                    :on-select="val => generateFilter('talla', val.ProductoTalla)"
                    :onInput="(val) => !val ? generateFilter('talla', val) : false"
                    :classes="{input: 'form-control upper'}">
                  </autocomplete>
                </b-form-group>-->
              </b-col> 
              <b-col md="3">
                <!--<b-form-group>
                  <label>Genero:</label>
                  <autocomplete
                    :url="API + 'productos/generos'"
                    ref="inputGenero"
                    anchor="Genero"
                    label="writer"
                    :on-select="val => generateFilter('genero', val.Genero)"
                    :onInput="(val) => !val ? generateFilter('genero', val) : false"
                    :classes="{input: 'form-control upper'}">
                  </autocomplete>
                </b-form-group>-->
              </b-col>
              <b-col md="3">
                <!--<b-form-group>
                  <label>Botapie/Manga:</label>
                  <autocomplete
                    :url="API + 'productos/botapies'"
                    ref="inputBotapie"
                    anchor="Botapie"
                    label="writer"
                    :on-select="val => generateFilter('botapie', val.Botapie)"
                    :onInput="(val) => !val ? generateFilter('botapie', val) : false"
                    :classes="{input: 'form-control upper'}">
                  </autocomplete>
                </b-form-group>-->
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

          <b-modal id="modalImagen" size="lg" ref="modalImagen" hide-footer hide-header>
            <div class="d-block text-center">
              <h3 style="text-decoration: underline;">{{productoNombre}}</h3>
            </div>
            <b-row style="display:flex; justify-content:center; align-items:center">
              <b-img :src="productoUrl" class="imagenGrande"></b-img>
            </b-row>
          </b-modal>
          
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
                <template slot="presentaciones" slot-scope="data">
                  <b-button size="sm" variant="info" @click="showPresentacion(data.item)"><i class="fas fa-list"></i></b-button>
                </template>

                <template slot="ImagenProducto" slot-scope="data">
                  <b-button @click="abrirImagen(data.item.Producto, data.item.Imagen == null || data.item.Imagen==''?'static/no-photo.png': '/resources/images/productos/' + data.item.Imagen)" style="margin-top:2px;" :id="'popover-imagen' + data.item.IdProducto" variant="success" size="sm"><i class="fas fa-image"></i></b-button>	
                  <b-popover placement="top" :target="'popover-imagen' + data.item.IdProducto" :title="data.item.Producto" triggers="hover">
                    <div style="text-align:center">
                      <b-img style="border-radius: 10px; width:200px" fluid :src="data.item.Imagen == null || data.item.Imagen==''?'static/no-photo.png': '/resources/images/productos/' + data.item.Imagen" alt="" @error="setAltImg" ></b-img>	
                    </div>
                  </b-popover>
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
              <b-button variant="success" size="lg" :href="'/api/index.php/reporte/stock?controlaStock=1&idAlmacen=' + almacen.IdAlmacen + filter" target="_blank">Exportar</b-button>
            </b-col>
        </b-row>
        <br /><br />
      </b-container>

      <b-modal id="kardex" size="lg" modal-class="modal-xl" v-model="showKardex" title="Kardex de producto">
      
        <!--<b-row>
          <b-col md="6">
            <b-form-group>
              <label>Fecha Inicio:</label>
              <b-form-input v-model="fechaInicio"
                type="date"
                placeholder=""
                :value="fechaInc"
                @input="val => generateFilter('fechaInicio', val) "></b-form-input>
            </b-form-group>
          </b-col>
          <b-col md="6">
            <b-form-group>
              <label>Fecha Fin:</label>
              <b-form-input v-model="fechaFin"
              type="date"
              placeholder=""
              @input="val => generateFilter('fechaFin', val) "></b-form-input>
            </b-form-group>
          </b-col>
        </b-row>
        <p>{{kardexSelect}}</p>-->

        <b-alert show variant="warning">{{ titleKardex }}</b-alert>
        <producto-kardex :producto="productoSelect" :kardex="kardexSelect"></producto-kardex>
      </b-modal>

      <b-modal size="md" ref="modalCant" hide-footer hide-header>
        <div class="d-block text-center">
          <h3 style="text-decoration:underline">{{ objProd.Producto}}</h3>
          <h5>Presentaciones</h5>
        </div>
        <hr>
        <b-row>
          <b-col cols="6">
            <b-card
              align="center"
              class="mb-3 contentCard">
              <b-row>
                <b-col md="12" class="nomCart">
                  <div>
                    <b-row class="nomCart2">
                      <h6 class="text-uppercase">{{ objProd.ProductoMedicion }}</h6>
                    </b-row>
                    <b-row class="nomCart2">
                       <h6 style="margin-right:5px">Stock:</h6><h5><b-badge v-if="objProd.stock<=0" variant="danger">{{ objProd.stock }}</b-badge>
                       <b-badge v-else variant="success">{{ objProd.stock }}</b-badge></h5>
                    </b-row>
                  </div>
                </b-col>
              </b-row>
            </b-card>
          </b-col>
          <b-col cols="6" v-for="objPro in PresentacionesProd" :key="objPro.IdProducto">
            
            <b-card
              align="center"
              class="mb-3 contentCard"
              v-if="objPro.cantidad>0">
              <b-row>
                <b-col md="12" class="nomCart">
                  <div>
                    <b-row class="nomCart2">
                      <h6 class="text-uppercase">{{ objPro.nombre }}</h6>
                    </b-row>
                    <b-row class="nomCart2">
                       <h6 style="margin-right:5px">Stock:</h6><h5><b-badge v-if="(objProd.stock/objPro.cantidad)<=0" variant="danger">{{ (objProd.stock/objPro.cantidad).toFixed(2)}}</b-badge>
                       <b-badge v-else variant="success">{{ (objProd.stock/objPro.cantidad).toFixed(2)}}</b-badge></h5>
                    </b-row>
                  </div>
                </b-col>
              </b-row>
            </b-card>
          </b-col>
        </b-row>
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
      sumaStock: '...',
      sumaValorizado: '...',
      sumaValorizadoSin: '...',
      APISTOCK: 'productos',
      tipoStock: '',
      showKardex: false,
      almacen: {
        IdAlmacen: 1,
        Almacen: 'PRINCIPAL'
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
            { key: 'ImagenProducto', label:'Img.'},
            { key: 'Producto', sortable: true },
            { key: 'ProductoMarca', label: 'Marca', sortable: true },
            { key: 'stock', sortable: false, variant: '', class:'text-center'},
            { key: 'presentaciones', label: 'Stock P.', sortable: false, class:'text-center'}
      ],
      totalRows: 50,
      perPage: 10,
      currentPage: 1,
      idPerfil: sessionStorage.getItem('IdPerfil'),
      verValorizado: false,
      productoNombre:'',
      productoUrl:'',
      objProd:{},
      PresentacionesProd:[]
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
            idAlmacen: this.almacen.IdAlmacen,
            limit: items.length
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
        this.getSumaValorizadoSin()

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
      this.productoSelect.ProductoMarca + ' / ' + this.productoSelect.ProductoFormaFarmaceutica
    }
  },
  methods: {
    abrirImagen(producto,url){
      this.productoNombre = producto
      this.productoUrl = url
      this.$refs['modalImagen'].show()
    },
    setAltImg(event) { 
        event.target.src = "static/no-photo.png" 
    },
    clickVerValorizado() {
      let pass = window.prompt('Escriba su contraseña')
      if (pass) {
        this.axios.post(this.API + 'login', {
          user: sessionStorage.getItem('User'),
          password: pass
        }).then(r => {
          if (r.data) {
            this.verValorizado = true;
          }
        })
      }
    },
    showPresentacion(prod){
      this.objProd = prod
      this.objProd.stock = this.getStock(prod.IdProducto)
      this.PresentacionesProd = []
      if(prod.PreciosPorProducto !='' && prod.PreciosPorProducto !=null){
          this.PresentacionesProd = JSON.parse(prod.PreciosPorProducto)
      }
      this.$refs['modalCant'].show()
    },
    getSumaValorizado () {
      this.axios.get(this.API + 'productos/stock' + '?controlaStock=1&idAlmacen=' + this.almacen.IdAlmacen + '&filter='+ (this.filter||'') + '&filter[stock]=mayor&sumaValorizado=1')
        .then( response => {
          this.sumaValorizado = (response.data[0].sumaValorizado);
        })
    },
    getSumaValorizadoSin () {
      this.axios.get(this.API + 'productos/stock' + '?controlaStock=1&idAlmacen=' + this.almacen.IdAlmacen + '&filter='+ (this.filter||'') + '&filter[stock]=mayor&sumaValorizadoSin=1')
        .then( response => {
          this.sumaValorizadoSin = (response.data[0].sumaValorizadoSin);
        })
    },
    getSumaStock () {
      this.axios.get(this.API + 'productos/stock' + '?controlaStock=1&idAlmacen=' + this.almacen.IdAlmacen + '&filter='+ (this.filter||'') + '&filter[stock]=mayor&sumaStock=1')
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
      this.PresentacionesProd = JSON.parse(obj.PreciosPorProducto || '[]')
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
      let url = this.API + this.APISTOCK + '?controlaStock=1&idAlmacen=' + this.almacen.IdAlmacen + '&limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter='+ (ctx.filter||'')+'&sortBy='+ctx.sortBy
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
      this.$refs.inputCategoria.setValue('')
      this.$refs.inputProducto.setValue('')
      this.$refs.inputMarca.setValue('')
      // this.$refs.inputCodigoBarra.setValue('')
      // this.$refs.inputModelo.setValue('')
      // this.$refs.inputColor.setValue('')
      // this.$refs.inputTalla.setValue('')
      // this.$refs.inputGenero.setValue('')
      // this.$refs.inputBotapie.setValue('')

      this.productoSelect = {}
      this.limpiarClassRows()
      console.log(this.filter)
    },
    generateFilter (key, value) {
      this.filters[key] = value
      
      let filter = ''
      for (var key in this.filters) {
        filter += '&filter[' + key + ']=' + this.filters[key]
        
        if(key == 'stock' || key == 'minimo') {
          // si la consulta se hace al stock jalar de otro endpoint
          this.APISTOCK = 'productos/stock'
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
.nomCart {
  display: flex;
  align-items: center;
  justify-content: center;
  border-right: 2px solid black;
}
.nomCart2 {
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
