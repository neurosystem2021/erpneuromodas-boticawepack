<template>
  <div>
      <b-container class="bv-example-row">
        <b-row>
          <b-col md="2" class="my-1">
            <b-form-group>
                <label>Almacen:</label>
                <autocomplete
                    required="required"
                    ref="inputAlmacen"
                    :url="API + 'almacenes'"
                    anchor="Almacen"
                    label="writer"
                    placeholder=""
                    :on-select="setAlmacen" 
                    :onFocus="focusAutocomplete.bind(null, 'inputAlmacen')"
                    :classes="{input: 'form-control'}">
                </autocomplete>
            </b-form-group>
          </b-col>
          <b-col md="4" class="my-1">
            <b-form-group >
              <label>Buscar Producto:</label>
              <autocomplete
                ref="autocompleteProductos"
                :url="API + 'productos'"
                :customParams="{ controlaStock: 1 }"
                anchor="Producto"
                label="writer"
                placeholder="Seleccionar Producto"
                :on-select="setProducto"
                :onFocus="focusAutocomplete.bind(null, 'autocompleteProductos')"
                :classes="{input: 'form-control'}">
              </autocomplete>

              <!--<b-input-group>
                <b-form-input v-model="filter" placeholder="Buscar" @input="currentPage = 1" />
                <b-input-group-append>
                  <b-btn :disabled="!filter" @click="filter = ''">Limpiar</b-btn>
                </b-input-group-append>
              </b-input-group>-->
            </b-form-group>

            
          </b-col>
          <b-col md="4" class="my-1">
            <b-form-group >
              <label>Buscar Similares</label>
              <autocomplete
                ref="autocompleteCompuestos"
                :url="APICompuestos"
                anchor="ProductoCompuesto"
                label="writer"
                placeholder="Seleccionar Compuesto"
                :on-select="setCompuesto"
                :onFocus="focusAutocomplete.bind(null, 'autocompleteCompuestos')"
                :classes="{input: 'form-control'}">
              </autocomplete>
            </b-form-group>

            <br />
          </b-col>
          <b-col md="2" style="margin-top: 10px;">
            <br />
            <b-btn :disabled="!filter" @click="limpiar">Limpiar</b-btn>
          </b-col>
           <b-col md="5" offset-md="7">
             
              <div>
                <label><b>Descripción, </b> </label>
                {{ productoSelect.ProductoDesc }}
              </div>
              <br />
           </b-col>
          <!--<b-col md="6" class="my-1">
            <b-row>
            <b-col md="8">

              <b-form-group horizontal label="Mostrar" class="mb-0">
                <b-form-select :options="pageOptions" v-model="perPage" @input="currentPage = 1"/>
              </b-form-group>
            </b-col>
            <b-col md="4">
              <p style="margin-top: 6px;">de <b class="text-success">{{ this.totalRows }}</b> Artículos</p>
            </b-col>
            </b-row>
          </b-col>-->

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
            :current-page="currentPage">
            <template slot="acciones" slot-scope="data">
                <b-button  variant="success" size="sm" target="_blank" :href="'/views/V_VentaForm.php?idProducto=' + data.item.IdProducto">Punto de venta</b-button>
            </template>
          </b-table>
            <b-row>
               <!-- <b-col md="6" class="my-1">
                    <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" />
                </b-col>-->
            </b-row>
        </b-row>
        <!--<producto-form ref="productoForm" :producto-update="productoSelect" :cbGuardar="actualizarTabla"></producto-form>-->
        <br />
        <!--<b-row align-h="center" class="text-center">
            <b-col cols="4">
              <b-button variant="success" size="lg" @click="nuevoProducto">Nuevo</b-button>
            </b-col>
        </b-row>-->
        <br /><br />
      </b-container>
  </div>
</template>

<script>
import config from '@/store/config.js'
import ProductoForm from '@/components/ProductoForm'

export default {
  mounted() {
    this.axios.get(this.API + 'productos/count').then((response) => {
      this.totalRows = response.data.total
    })
    this.loadFirstAlmacen();
  },
  data() {
    return {
      API: config.API,
      pageOptions: [ 5, 10, 15, 50, 100 ],
      almacen: {},
      filter: null,
      sortBy: 'IdProducto',
      sortDesc: false,
      productoSelect: {},
      items: [],
      isBusy: false,
      fields: [
            { key: 'Codigo', label: 'Cod', sortable: true },
            { key: 'Producto', sortable: true },
            { key: 'ProductoMarca', label: 'Laboratorio', sortable: true },
            { key: 'ProductoFormaFarmaceutica', label: 'Forma Farmaceutica', sortable: true },
            { key: 'ProductoMedicion', label: 'Medición', sortable: true},
            { key: 'stock', label: 'Stock', sortable: true},
            { key: 'acciones', label: 'Enviar'}
      ],
      totalRows: 50,
      perPage: 10,
      currentPage: 1
    }
  },
  computed: {
    APICompuestos () {
      if (this.productoSelect  && this.productoSelect.IdProducto) {
        return this.API + 'productos/compuestos/' + this.productoSelect.IdProducto
      }
      return this.API + 'productos/compuestos'
    },
  },
  methods: {
      loadFirstAlmacen () {
        this.axios.get(this.API + 'almacenes/primero')
            .then(res => {
                this.$refs.inputAlmacen.setValue(res.data.Almacen)
                this.almacen = res.data
            })
      },
      limpiar () {
        this.filter = ''
        this.$refs.autocompleteCompuestos.type = ""
        this.$refs.autocompleteProductos.type = ""
        this.productoSelect = {}
      },
      setAlmacen (obj) {
          this.almacen = obj
          this.$refs.inputAlmacen.setValue(obj.Almacen)
          this.limpiar();
      },
      rowClick (item, index) {
        /* this.productoSelect = item
        this.$refs.productoForm.show = true; */
        //this.$refs.productoForm.producto.IdProducto = 'a'
        //console.log(this.$refs.productoForm.$refs.productoForm)
      },
      actualizarTabla() {
        this.$refs.tablaProductos.refresh()
      },
      nuevoProducto () {
          this.productoSelect = {}
          this.$refs.productoForm.show = true
      },
      setProducto (obj) {
        this.productoSelect = obj
        this.$refs.autocompleteCompuestos.type = ""
      },
      setCompuesto (obj) {
        this.filter = String(obj.IdProductoCompuesto);
      },
      focusAutocomplete (ref) {
        // this.$refs[ref].type = ""
        this.$refs[ref].handleDoubleClick()
      },
      myProvider (ctx) {
        // Here we don't set isBusy prop, so busy state will be handled by table itself
        // this.isBusy = true
        let url = this.API + 'productos/similares?idAlmacen='+this.almacen.IdAlmacen+'&limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter='+ (ctx.filter||'')+'&sortBy='+ctx.sortBy
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
      }
  },
  components: {
      ProductoForm
  }
}
</script>

<style scoped>
#my-table{
  font-size: .9em;
}
</style>
