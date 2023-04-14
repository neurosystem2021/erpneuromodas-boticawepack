<template>
  <div>
      <b-container class="bv-example-row">
        <b-row>
          <b-col md="8" class="my-3" style="margin: 0px auto;">
            <b-row>
              <b-col style="text-align: center; padding-top:10px; text-align: center; border-radius: 10px 0px 0px 10px; border: 0.5px white solid; color: #fff;background: #0a2164;">
                <b>Total de Balones<br>
                {{cantidadBalones}}</b>
              </b-col>
              <b-col style="text-align: center; text-align: center; border: 0.5px white solid; color: #fff;background: #0a2164;">
                <b>Total de Envaces Vacios<br>
                {{stock}}</b>
              </b-col>
              <b-col style="text-align: center; text-align: center; border: 0.5px white solid; color: #fff;background: #0a2164;">
                <b>Cantidad de Balones Prestados<br>
                {{cantidadPrestado}}</b>
              </b-col>
              <b-col style="text-align: center; text-align: center; border-radius: 0px 10px 10px 0px; border: 0.5px white solid; color: #fff;background: #0a2164;">
                <b>Cantidad de GLP (Contenido)<br>
                {{cantidadGases}}</b>
              </b-col>
            </b-row>
          </b-col>
        </b-row>

        <b-row>
          <b-col md="3" class="my-1">
            <b-form-group>
              <autocomplete
                  required="required"
                  ref="inputBalonEnvase"
                  :url="API + 'productos/balonesEnvase'"
                  anchor="Producto"
                  label="writer"
                  :on-select="setBalonesEnvase"
                  :onFocus="focusAutocomplete.bind(null, 'inputBalonEnvase')"
                  :classes="{input: 'form-control'}">
              </autocomplete>
            </b-form-group>
          </b-col>
          <b-col md="2" class="my-1">
            <b-form-group>
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
          <b-col md="5" class="my-1">
            <b-form-group horizontal label="Buscar" class="mb-0">
              <b-input-group>
                <b-form-input v-model="filter" placeholder="Buscar" @input="currentPage = 1" />
                <b-input-group-append>
                  <b-btn :disabled="!filter" @click="filter = ''">Limpiar</b-btn>
                </b-input-group-append>
              </b-input-group>
            </b-form-group>
          </b-col>
          <b-col md="2" class="my-1">
            <b-form-group class="mb-0">
              <b-form-select :options="pageOptions" v-model="perPage" @input="currentPage = 1"/>
            </b-form-group>
          </b-col>
        </b-row>
        <b-row>
          <b-col md="12">
            <b-table small
            ref="tablaProductos"
            :filter="filter"
            :sort-by.sync="sortBy"
            :sort-desc.sync="sortDesc"
            striped
            hover
            @row-clicked="rowClick"
            id="my-table" 
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
       <producto-devolver ref="productoDevolver" @update="$refs.tablaProductos.refresh()"></producto-devolver>
       <b-row align-h="center" class="text-center">
            <b-col cols="0">
              <b-button variant="primary" size="lg" @click="getBalonesTotales">REPORTE TOTAL DE LOS BALONES</b-button>
            </b-col>
        </b-row>
        <br />
        <b-row align-h="center" class="text-center">
            <b-col cols="4">
              <b-button variant="success" size="lg" @click="devolverProducto">Devolver Producto</b-button>
            </b-col>
        </b-row>
        <br /><br />
        <b-modal size="lg" modal-class="modal-md" v-model="showPrestados" title="Lista Detallada">
          <b-alert show variant="warning">{{ productoSelect.Producto }}</b-alert>
          <productos-prestados :producto="productoSelect"></productos-prestados>
        </b-modal>
      </b-container>
  </div>
</template>

<script>
import config from '@/store/config.js'
import ProductoDevolver from '@/components/ProductoDevolverForm'
import ProductosPrestados from '@/components/ProductosPrestados'

export default {
  mounted() {
    
    //this.loadFirstAlmacen()
    this.getAlmacenById()
    this.loadFirstEnvase()
  },
  data() {
    return {
      API: config.API,
      showPrestados: false,
      pageOptions: [ 5, 10, 15, 50, 100 ],
      filter: null,
      sortBy: 'Gen_Producto.IdProducto',
      sortDesc: false,
      productoSelect: {},
      items: [],
      isBusy: false,
      fields: [
            { key: 'IdProducto', label: 'ID', sortable: true },
            { key: 'CodigoBarra', label: 'Codigo', sortable: true },
            { key: 'Producto', sortable: true },
            { key: 'ProductoMarca', label: 'Marca', sortable: true },
            { key: 'ProductoMedicion', label: 'Medición', sortable: true },
            { key: 'Prestados', label: 'Prestados', sortable: true },
      ],
      totalRows: 50,
      perPage: 10,
      currentPage: 1,
      almacen: {
        IdAlmacen: 1
      },
      productos: {
        IdProducto : null,
        IdProductoContenido : null
      },
      cantidadBalones: 0,
      cantidadPrestado: 0,
      cantidadGases: 0,
      stock: 0,
      url:''
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
      loadFirstEnvase () {
      this.axios.get(this.API + 'balonesEnvase/primero')
        .then(res => {
          this.$refs.inputBalonEnvase.setValue(res.data.Producto)
          this.setBalonesEnvase(res.data)
        })
      },
      getBalonesTotales() {
        this.axios.get(this.API + 'balonesvacios' + '?idAlmacen='+this.almacen.IdAlmacen)
        .then(res => {
            this.$refs.inputBalonEnvase.setValue('TODOS')
            this.cantidadBalones = res.data.cantidadEnvases;
            this.stock = res.data.envasesVacios;
            this.cantidadGases = res.data.cantidadGases;
            this.productos.IdProducto = null;
            this.$refs.tablaProductos.refresh()
        })
      },
      getBalonesVacios() {
        this.axios.get(this.API + 'balonesvacios', {
          params: {
            idProducto: this.productos.IdProducto,
            idAlmacen: this.almacen.IdAlmacen,
            idProductoContenido: this.productos.IdProductoContenido
          }
        }).then(res => {
            this.cantidadBalones = res.data.cantidadEnvases;
            this.stock = res.data.envasesVacios;
            this.cantidadGases = res.data.cantidadGases;
          })
      },
      setBalonesEnvase(obj) {
        this.productos = obj;
        this.productos.IdProducto = obj.IdProducto
        this.productos.IdProductoContenido = obj.IdProductoContenido
        setTimeout(() => {
          Vue.nextTick(() => this.$refs.tablaProductos.refresh())
        }, 100);
        this.getBalonesVacios()
      },
      setAlmacen (obj) {
        this.almacen = obj
        setTimeout(() => {
          Vue.nextTick(() => this.$refs.tablaProductos.refresh())
          if (this.productos.IdProducto==null) {
          this.getBalonesTotales()    
          }
          else {
            this.getBalonesVacios()
          }
        }, 100);
      },
      focusAutocomplete (ref) {
        // this.$refs[ref].type = ""
        this.$refs[ref].handleDoubleClick()
      },
      rowClick (item, index) {
        this.productoSelect = item
        this.showPrestados = true
        // this.$refs.productoForm.show = true;
        //this.$refs.productoForm.producto.IdProducto = 'a'
        //console.log(this.$refs.productoForm.$refs.productoForm)
      },
      actualizarTabla() {
        this.$refs.tablaProductos.refresh()
      },
      devolverProducto () {
          this.productoSelect = {}
          this.$refs.productoDevolver.show = true
          // this.$refs.productoForm.show = true

      },
      myProvider (ctx) {
        // Here we don't set isBusy prop, so busy state will be handled by table itself
        // this.isBusy = true
        if (this.productos.IdProducto) {
          this.url = this.API + 'productos/prestados?idAlmacen='+this.almacen.IdAlmacen+ '&idProducto='+this.productos.IdProducto+ '&limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter='+ (ctx.filter||'')+'&sortBy='+ctx.sortBy
          this.url += '&sortDesc='+(ctx.sortDesc ? 1 : 0 )
        }
        else {
          this.url = this.API + 'productos/prestados?idAlmacen='+this.almacen.IdAlmacen + '&limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter='+ (ctx.filter||'')+'&sortBy='+ctx.sortBy
          this.url += '&sortDesc='+(ctx.sortDesc ? 1 : 0 )
        }
        let promise = this.axios.get( this.url )

        return promise.then((result) => {
          this.items = result.data
          var cont = 0;
          result.data.map(d => {
            cont += parseInt(d['Prestados']);
          })
          this.cantidadPrestado  = cont  
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
      }
  },
  components: {
    ProductoDevolver,
    ProductosPrestados
  }
}
</script>

<style>

</style>
