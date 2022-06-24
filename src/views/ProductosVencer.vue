<template>
  <div>
      <b-container fluid style="width: calc(100% - 20px)" class="bv-example-row">
        <b-row>
          <b-col md="12" class="text-center"><h2>Actualización de Fecha de Vencimiento</h2></b-col>
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
            <b-form-group>
              <label>Fecha Inicio:</label>
              <b-form-input
                v-model="fechaDesde"
                type="date"
                step="1"
                placeholder="Fecha Inicio">
              </b-form-input>
            </b-form-group>
          </b-col>
          <b-col md="2">
            <b-form-group>
              <label>Fecha Fin:</label>
              <b-form-input
                v-model="fechaHasta"
                type="date"
                step="1"
                placeholder="Fecha Inicio">
              </b-form-input>
            </b-form-group>
          </b-col>
          <b-col md="2" class="text-center">
            <b-form-group>
              <br>
              <b-button @click="generarFechaVen" variant="primary" size="lg">Generar</b-button>
            </b-form-group>
          </b-col>
        </b-row>
        <hr>
        <b-row>
          <b-col md="6" class="my-1">
            <b-form-group horizontal label="Buscar" class="mb-0">
              <b-input-group>
                <b-form-input placeholder="Buscar" v-model="busqueda"/>
                <b-input-group-append>
                  <b-btn :disabled="!busqueda" variant="success" @click="buscarProd">Buscar</b-btn>
                </b-input-group-append>
                <b-input-group-append>
                  <b-btn :disabled="!busqueda" @click="filter = '';busqueda=''">Limpiar</b-btn>
                </b-input-group-append>
              </b-input-group>
            </b-form-group>
            <br />
          </b-col>
          <b-col md="4" class="my-1">
            <b-row>
            <b-col md="8">

              <b-form-group horizontal label="Mostrar" class="mb-0">
                <b-form-select :options="pageOptions" v-model="perPage" @input="currentPage = 1"/>
              </b-form-group>
            </b-col>
            <b-col md="4">
              <p style="margin-top: 6px;">de <b class="text-success">{{ this.totalRows }}</b> Productos</p>
            </b-col>
            </b-row>
          </b-col>
          <b-col md="2" style="display: flex;
          justify-content: flex-start;
          align-items: center;">
            <b-form-group>
              <b-form-checkbox
                  @change="(val)=>cambiarMayor(val)"
                  v-model="condicionStock"
                  :value="1"
                  :unchecked-value="0">
                  Stock Mayor a 0
              </b-form-checkbox>
            </b-form-group>
          </b-col>

          <b-table small
            ref="tablaProductos"
            :filter="filter"
            :sort-by.sync="sortBy"
            :sort-desc.sync="sortDesc"
            striped
            hover
            id="my-table" 
            :busy.sync="isBusy" 
            :items="myProvider" 
            :fields="fields"
            :per-page="perPage"
            :current-page="currentPage">
                <template slot="FechaVen" slot-scope="data">
                  <div class="text-center" :class="getFechaVen(data.item.IdProducto)" @click.stop="() => false">
                    {{ data.item.FechaVen?data.item.FechaVen:'Sin Fecha' }}
                  </div>
                </template>
                <template slot="stock" slot-scope="data">
                  <div class="text-center" :class="getStock(data.item.IdProducto) > 0 ? 'table-success' : 'table-danger'" @click.stop="() => false">
                    {{ getStock(data.item.IdProducto) }}
                  </div>
                </template>
                <template slot="Editar" slot-scope="data">
                  <div class="text-center" @click.stop="() => false">
                    <b-button variant="primary" size="sm" @click="EditarFechaVen(data.item)"><i class="fas fa-pencil-alt fa-sm"></i></b-button>
                  </div>
                </template>
            </b-table>
            <b-row>
                <b-col md="6" class="my-1">
                    <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" />
                </b-col>
            </b-row>
        </b-row>
        <fecha-ven-form ref="fechaVenForm" :fechaven-update="productoSelect" :cbGuardar="actualizarTabla"></fecha-ven-form>
        <br />
        <br /><br />
      </b-container>
  </div>
</template>

<script>
import config from '@/store/config.js'
import FechaVenForm from '@/components/FechaVenForm'


export default {
  mounted() {
    this.axios.get(this.API + 'productos/count').then((response) => {
      this.totalRows = response.data.total
    })
  },
  data() {
    return {
      API: config.API,
      pageOptions: [ 5, 10, 15, 50, 100, 200, 500 ],
      filter: '',
      sortBy: 'IdProducto',
      sortDesc: false,
      productoSelect: {},
      items: [],
      isBusy: false,
      fields: [
            { key: 'CodigoBarra', label: 'Codigo', sortable: true },
            { key: 'Producto', sortable: true },
            { key: 'ProductoFormaFarmaceutica', label: 'Forma Farmaceutica', sortable: true},
            { key: 'ProductoMarca', label: 'Laboratorio', sortable: true },
            { key: 'IdLote', label: 'Lote'},
            //{ key: 'HashMovimiento', label: 'Hash Mov', sortable: true },
            { key: 'FechaVen',label:'Fecha Vencimiento' },
            { key: 'Editar',label:'Editar', sortable: true },
            { key: 'SerieMov', label: 'Serie' },
            { key: 'NumeroMov', label: 'Numero'},
            { key: 'stock', sortable: true }
      ],
      totalRows: 50,
      perPage: 10,
      currentPage: 1,
      almacen: {
        IdAlmacen: 1,
        Almacen: 'PRINCIPAL'
      },
      fechaDesde: this.$moment().format("YYYY-01-01"),
      fechaHasta: this.$moment().format("YYYY-12-31"),
      busqueda:'',
      condicionStock:1
    }
  },
  methods: {
      /*rowClick (item, index) {
        this.productoSelect = item
        this.$refs.productoForm.show = true;
        //this.$refs.productoForm.producto.IdProducto = 'a'
        //console.log(this.$refs.productoForm.$refs.productoForm)
      },*/
      focusAutocomplete (ref) {
      // this.$refs[ref].type = ""
        this.$refs[ref].handleDoubleClick()
      },
      generarFechaVen(){
        if (this.fechaDesde == undefined || this.fechaDesde=='' || this.fechaDesde.length>10 ) {
          if(this.fechaDesde.length>10){
            this.$toasted.show('La fecha de inicio no tiene formato correcto')
            return
          }
          this.$toasted.show('Ingrese fecha de inicio')
          return
        }
        if (this.fechaHasta == undefined || this.fechaHasta=='' || this.fechaHasta.length>10 ) {
          if(this.fechaHasta.length>10){
            this.$toasted.show('La fecha de fin no tiene formato correcto')
            return
          }
          this.$toasted.show('Ingrese fecha de fin')
          return
        }
        this.filter = ''
        this.busqueda = ''
        Vue.nextTick(() => this.$refs.tablaProductos.refresh())
      },
      buscarProd(){
        if(this.filter == this.busqueda){
           Vue.nextTick(() => this.$refs.tablaProductos.refresh())
        }
        this.filter = this.busqueda
        this.currentPage = 1
      },
      cambiarMayor(val){
        Vue.nextTick(() => this.$refs.tablaProductos.refresh())
      },
      EditarFechaVen(item){
        this.productoSelect = item
        this.productoSelect.IdAlmacen = this.almacen.IdAlmacen
        this.$refs.fechaVenForm.show = true;
      },
      getStock (idProducto) {
        let index = this.items.findIndex(item => item.IdProducto == idProducto)
        return this.items[index] && this.items[index].stock
      },
      getFechaVen (idProducto) {
        let index = this.items.findIndex(item => item.IdProducto == idProducto)

        if(this.items[index] && this.items[index].FechaVen){
          var dt = new Date()
          var anio = dt.getFullYear()
          var mes =  dt.getMonth() + 1
          var dia = dt.getDate()
          var fechaVen = new Date(this.items[index].FechaVen).getTime()
          
          var hoy = anio + "-" + mes + "-" + dia
          hoy = new Date(hoy).getTime()
          var en4meses = anio + '-' + (mes + 4) + '-' + dia
          en4meses = new Date(en4meses).getTime()

          if(fechaVen && fechaVen < hoy) {
            return 'table-danger'
          }

          if(fechaVen >= hoy && fechaVen <= en4meses) {
            return 'table-warning'
          }

          return 'table-success'
        }else{
          return 'table-dark'
        }
      },
      setAlmacen (obj) {
        console.log(obj)
        this.almacen = obj
        Vue.nextTick(() => this.$refs.tablaProductos.refresh())
      },
      actualizarTabla() {
        this.$refs.tablaProductos.refresh()
      },
      myProvider (ctx) {
        // Here we don't set isBusy prop, so busy state will be handled by table itself
        // this.isBusy = true
        let url = this.API + 'productos/stock/'+this.almacen.IdAlmacen+'?fechaIni='+this.fechaDesde+'&fechaFin='+this.fechaHasta+(this.condicionStock==1?'&condiStock=1':'')+((('')+this.filter).trim()==''?'&fven=1':'')+'&limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter='+ (ctx.filter||'')+'&sortBy='+ctx.sortBy
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
      FechaVenForm
  }
}
</script>


<style>

</style>
