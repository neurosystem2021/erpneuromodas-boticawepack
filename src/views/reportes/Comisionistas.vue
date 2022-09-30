<template>
  <div>
    <h2 align-h="center" class="text-center" >Reporte de Comisionistas</h2>
      <b-container class="bv-example-row">
        <b-row>
          <b-col md="4" class="my-1">
            <b-form-group>
                  <label>Buscar:</label>
              <b-input-group>
                <b-form-input v-model="filter" placeholder="Buscar" @input="currentPage = 1" />
                <b-input-group-append>
                  <b-btn :disabled="!filter" @click="filter = ''">Limpiar</b-btn>
                </b-input-group-append>
              </b-input-group>
            </b-form-group>
            <b-form-group>
              <label>Fecha Inicio:</label>
              <b-form-input v-model="fechaInicio"
                type="date"
                placeholder=""
                @input="$refs.tablaProductos.refresh()"></b-form-input>
                <input type="time" v-model="horaIni" class="form-control"  @input="$refs.tablaProductos.refresh()">
            </b-form-group>
          </b-col>
          <b-col md="4">
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
                <b-form-group>
                  <label>Fecha Fin:</label>
                  <b-form-input v-model="fechaFin"
                    type="date"
                    placeholder=""
                    @input="$refs.tablaProductos.refresh()"></b-form-input>
                    <input type="time" v-model="horaFinal" class="form-control"   @input="$refs.tablaProductos.refresh()">
                </b-form-group>
          </b-col>
           <b-col md="2" class="my-1">
              Total Comisión
              <div class="total-comision">{{ totalComision }}</div>
              <hr>
              <b-button variant="success" size="md" block :href="hrefXls" target="_blank"><i class='fas fa-file-excel'></i> Exportar</b-button>
          </b-col>
          <b-col md="2" class="my-1">
              Total Saldo Comisión
              <div class="total-comision">{{ totalSaldoComision }}</div>
              <hr>
          </b-col>

          <b-table small
            ref="tablaProductos"
            :filter="filter"
            :sort-by.sync="sortBy"
            :sort-desc.sync="sortDesc"
            striped
            hover
            id="my-table" 
            @row-dblclicked="rowDblClick"
            :busy.sync="isBusy" 
            :items="myProvider" 
            :fields="fields"
            :per-page="perPage"
            :current-page="currentPage">
            
            <template slot="pagar" slot-scope="data">
              <div class="input-group mb-3">
                <input type="text" class="form-control" placeholder="Monto" style="width:30px">
                <div class="input-group-append">
                  <button class="btn btn-outline-success" type="button" id="button-addon2" @click="(e) => pagarAComisionista(e, data.item)">Pagar</button>
                </div>
              </div>
            </template>
          </b-table>
            <b-row>
                <b-col md="6" class="my-1">
                    <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" />
                </b-col>
            </b-row>
        </b-row>
        <!--<b-row align-h="center" class="text-center">
            <b-col cols="4">
              <b-button variant="success" size="lg" :href="'/api/index.php/reporte/deudaclientes2?idalmacen=' + almacen.IdAlmacen" target="_blank">Exportar</b-button>
            </b-col>
        </b-row>-->
        <br /><br />
      </b-container>

      <b-modal id="pagos" size="lg" modal-class="modal-xl" v-model="showPagos" title="pagos a comisionista">
        <b-alert show variant="warning">{{ clienteSelect.DniRuc }} - {{ clienteSelect.Cliente }}</b-alert>
        <PagosComisionista :cliente="clienteSelect" v-if="clienteSelect.IdCliente"/> 
      </b-modal>
  </div>
</template>

<script>
import config from '@/store/config.js'
import PagosComisionista from '@/components/PagosComisionista'
export default {
  mounted() {
    this.axios.get(this.API + 'clientes/comisionistas/count?idAlmacen=' + this.almacen.IdAlmacen ).then((response) => {
      this.totalRows = response.data.total
    })
  },
  watch: {
    items (items) {
      let total = 0;
      let saldo = 0;
      items.map(v => {
        total += v.ComisionTotal
        saldo += v.Comision
        
      })
      this.totalComision = Math.round(total)
      this.totalSaldoComision = Math.round(saldo)
    }
  },
  computed: {
      hrefXls () {
        return '/api/index.php/reporte/comisionistas?idAlmacen=' + this.almacen.IdAlmacen+(this.fechaFin?'&fechaFin=' + this.fechaFin + ' ' +  this.horaFinal:'')+(this.fechaInicio?'&fechaInicio=' + this.fechaInicio  + ' ' +  this.horaIni:'');
      }
  },
  data() {
    return {
      API: config.API,
      totalComision: 0,
      totalSaldoComision: 0,
      clienteSelect: {},
      showPagos: false,
      pageOptions: [ 5, 10, 15, 50, 100 ],
      filter: '',
      sortBy: 'IdDocVenta',
      sortDesc: false,
      items: [],
      isBusy: false,
      fields: [
            { key: 'DniRuc', label: 'DNI/RUC', sortable: true },
            { key: 'Cliente', label: 'Cliente', sortable: true },
            { key: 'ComisionTotal', label: 'Total Comisión', sortable: true},
            { key: 'Comision', label: 'Saldo', sortable: true},
            { key: 'pagar', label: 'Pagar' }
      ],
      totalRows: 50,
      perPage: 10,
      currentPage: 1,
      almacen: {
        IdAlmacen: 0
      },
      fechaInicio: null,
      horaIni: '00:00:00',
      horaFinal: '23:59:00',
      fechaFin: this.$moment().format("YYYY-MM-DD")
    }
  },
  methods: {
    pagarAComisionista (e, cliente) {
      let importe = e.target.parentElement.previousElementSibling.value
       if( importe <= cliente.Comision ){
          let r = confirm('Desea pagar al comisionista')
         if (r) {
            this.axios.post(this.API + 'cliente/comisionistas/pagar', {
              cliente: cliente,
              importe: importe
            })
            .then((response) => {
              this.$toasted.success('Pago a comisionista realizado')
              Vue.nextTick(() => this.$refs.tablaProductos.refresh())
            })
            .catch((error) => {
              console.log(error)
              this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
            })
          }
        }else{
           this.$toasted.show('El pago no puede ser mayor al saldo')
        }
     
    },
    rowDblClick (item) {
      this.clienteSelect = item
      this.showPagos = true;
    },
    focusAutocomplete (ref) {
      // this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick()
    },
    setAlmacen (obj) {
      this.almacen = obj
      Vue.nextTick(() => this.$refs.tablaProductos.refresh())
    },
    myProvider (ctx) {
      // Here we don't set isBusy prop, so busy state will be handled by table itself
      // this.isBusy = true
      // console.log(ctx.filter)
      // let url = this.API + 'productos/stock?idAlmacen='+this.almacen.IdAlmacen+'&limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter[producto]='+ (ctx.filter||'')+'&filter[minimo]=1&sortBy='+ctx.sortBy
      // url += '&sortDesc='+(ctx.sortDesc ? 1 : 0 )
      //  let promise = this.axios.get( this.API + 'cliente/comisionistas?idAlmacen=' + this.almacen.IdAlmacen + '&q=' + ctx.filter + '&limit=' + ctx.perPage + '&page=' + ctx.currentPage )
      
      let params = '?idAlmacen=' + this.almacen.IdAlmacen + '&q=' + ctx.filter;
      
      if (this.fechaInicio) {
        params += '&fechaInicio=' + this.fechaInicio  + ' ' +  this.horaIni;
      }
      if (this.fechaFin) {
        params += '&fechaFin=' + this.fechaFin + ' ' +  this.horaFinal;
      }
      let promise = this.axios.get( this.API + 'cliente/comisionistas' + params )
      return promise.then((response) => {
        this.items = response.data.map(d => {
          d.ComisionTotal = Math.round(d.ComisionTotal)
          d.Comision = Math.round(d.Comision)
          return d
        })
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
      PagosComisionista
  }
}
</script>

<style scoped>
@media print {
  .pagination {
    display: none;
  }
}
.total-comision {
  font-weight: bold;
  text-align: center;
  color: #ffffff;
  background-color: #ff7675;
  padding: 10px;
  font-size: 20px;
}
</style>