<template>
  <div>
      <b-container class="bv-example-row">
        <b-row>
          <b-col md="6" class="my-1">
            <b-form-group horizontal label="Buscar" class="mb-0">
              <b-input-group>
                <b-form-input v-model="filter" placeholder="Buscar" @input="currentPage = 1" />
                <b-input-group-append>
                  <b-btn :disabled="!filter" @click="filter = ''">Limpiar</b-btn>
                </b-input-group-append>
              </b-input-group>
            </b-form-group>
            <br />
          </b-col>
          <b-col md="6" class="my-1">
            <b-row>
            <b-col md="8">

              <b-form-group horizontal label="Mostrar" class="mb-0">
                <b-form-select :options="pageOptions" v-model="perPage" @input="currentPage = 1"/>
              </b-form-group>
            </b-col>
            <b-col md="4">
              <p style="margin-top: 6px;">de <b class="text-success">{{ this.totalRows }}</b> Clientes</p>
            </b-col>
            </b-row>
          </b-col>

          <b-table small
            ref="tablaClientes"
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

              <template slot="Editar" slot-scope="data">
                <div>
                    <b-button size="sm" variant="primary" @click="verInfo(data.item)">Editar</b-button>
                </div>
              </template>

              <template slot="Reporte" slot-scope="data">
                <div>
                    <b-button size="sm" variant="success" @click="verVentas(data.item)">Ventas</b-button>
                </div>
              </template>
                      
            </b-table>
            <b-row>
                <b-col md="6" class="my-1">
                    <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" />
                </b-col>
            </b-row>
        </b-row>
        <cliente-form ref="clienteForm" :clienteSelect="clienteSelect" @onAddCliente="actualizarTabla"></cliente-form>
        <br />
        <b-row align-h="center" class="text-center">
            <b-col cols="4">
              <b-button variant="success" size="lg" @click="nuevoCliente">Nuevo</b-button>
            </b-col>
        </b-row>
        <br /><br />
      </b-container>
        <b-modal id="historialCliente" size="lg" modal-class="modal-xl" v-model="showVentas" 
        ok-title="Cerrar" ok-only title="HISTORIAL DE VENTAS DEL CLIENTE">
        <b-alert show variant="warning" class="font-weight-bold">{{ titleCliente }}</b-alert>
        <b-row>
          <b-col md="5">
            <b-form-group>
              <label>Fecha Inicio:</label>
              <b-form-input
                v-model="ventasCliente.fechaDesde"
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
                v-model="ventasCliente.fechaHasta"
                type="date"
                step="1"
                placeholder="Fecha Inicio">
              </b-form-input>
            </b-form-group>
          </b-col>
          <b-col md="2" class="text-center">
            <b-form-group>
              <br>
              <b-button :disabled="estadoGenerarCliente" @click="ventasClienteFechaRango" variant="primary" size="lg">Generar</b-button>
            </b-form-group>
          </b-col>
        </b-row>
        <b-row>
            <b-col md="12" class="text-center">
                <div class="table-responsive">
                    <table class="table table-striped b-table table-sm">
                    <thead>
                        <tr>
                            <th>ID VENTA</th>
                            <th>FECHA</th>
                            <th>VENDEDOR</th>
                            <th>DOC.</th>
                            <th>SERIE</th>
                            <th>NUMERO</th>
                            <th>ANULADO</th>  
                            <th>MONTO VENTA</th>
                            <th>DESCUENTO</th>
                            <th>MONTO PAGADO</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(venta, i) in historialCliente" :key="i" :class="{'table-danger': venta.Anulado == 1}">
                            <td>{{ venta.idDocVenta }}</td>
                            <td>{{ venta.FechaDoc }}</td>
                            <td style="text-transform:uppercase">{{ venta.UsuarioReg }}</td>
                            <td style="text-transform:uppercase">{{ venta.TipoDoc }}</td>
                            <td>{{ venta.Serie }}</td>
                            <td>{{ venta.Numero }}</td>
                            <td>
                              <b-badge v-if="venta.Anulado==0" variant="success">NO</b-badge>
                              <b-badge v-else variant="danger">SI</b-badge>
                            </td> 
                            <td class="table-primary">{{ venta.IdTipoDoc==9?'-':''}}{{ venta.TotalVenta }}</td>
                            <td class="table-info">{{ venta.DescuentoAplicado }}</td>
                            <td class="table-success">{{ venta.IdTipoDoc==9?'-':''}}{{ venta.Pagado }}</td>
                        </tr>
                    </tbody>
                    <tfoot v-if="historialCliente.length>0">
                        <td colspan="7" class="font-weight-bold table-secondary">SUMATORIA</td>
                        <td class="font-weight-bold table-primary">S/ {{ totalClienteHistorial | moneda }}</td>
                        <td class="font-weight-bold table-info">S/ {{ totalClienteDescuento | moneda }}</td>
                        <td class="font-weight-bold table-success">S/ {{ totalClientePagado | moneda }}</td>
                    </tfoot>
                </table>
                </div>
            </b-col>
        </b-row>
        <b-row v-if="estadoGenerarCliente">
            <b-col class="text-center">
                <b-form-text style="font-size:20px" text-variant="success">... GENERANDO DATOS, ESPERE POR FAVOR ...</b-form-text>
                <br>
            </b-col>
        </b-row>
        <b-row v-if="historialCliente.length==0 && estadoGenerarCliente==false  ">
            <b-col class="text-center">
                <b-form-text style="font-size:20px" text-variant="danger">... EL CLIENTE NO TIENE VENTAS, PORFAVOR SELECCIONE FECHAS ANTERIORES ...</b-form-text>
                <br>
            </b-col>
        </b-row>
        <b-row>
          <b-col md="12" class="text-center">
              <b-button variant="success" size="md" :href="hrefXlsHistorialCliente" target="_blank">Exportar Excel Ventas del Cliente</b-button>
          </b-col>
        </b-row>
        <br>
      </b-modal>
  </div>
</template>

<script>
import config from '@/store/config.js'
import ClienteForm from '@/components/ClientesFormEdit'

export default {
  mounted() {
    this.axios.get(this.API + 'clientes/count').then((response) => {
      this.totalRows = response.data.total
    })
  },
  data() {
    return {
      API: config.API,
      pageOptions: [ 5, 10, 15, 50, 100 ],
      filter: null,
      sortBy: 'IdCliente',
      sortDesc: false,
      clienteSelect: {},
      items: [],
      isBusy: false,
      fields: [
            { key: 'Cliente', sortable: true },
            { key: 'DniRuc', sortable: true },
            { key: 'Direccion', sortable: true },
            { key: 'Puntos', sortable: true },
            { key: 'EsVIP', sortable: true },
            'Editar',
            'Reporte' 
      ],
      totalRows: 50,
      perPage: 10,
      currentPage: 1,
      showVentas:false,
      ventasCliente: {
        fechaDesde: this.$moment().format("YYYY-MM-01"),
        fechaHasta: this.$moment().format("YYYY-MM-DD")
      },
      estadoGenerarCliente:true,
      historialCliente:[],
      totalClienteHistorial: 0,
      totalClienteDescuento: 0,
      totalClientePagado: 0,
    }
  },
  computed: {
       titleCliente () {
         return 'CLIENTE: ' + this.clienteSelect.DniRuc + ' - ' + this.clienteSelect.Cliente
       },
       hrefXlsHistorialCliente () {
        return ('/api/index.php/reporte/cliente/ventas?idCliente=' + this.clienteSelect.IdCliente + '&fechaDesde='+this.ventasCliente.fechaDesde+'&fechaHasta='+this.ventasCliente.fechaHasta).replace('#','');
       },
  },
  methods: {
      verInfo(item){
        this.clienteSelect = item
        this.$refs.clienteForm.show = true;
      },
      verVentas (item) {
        this.clienteSelect = item
        this.showVentas = true
        this.estadoGenerarCliente = true
        this.historialCliente = []
        this.totalClienteHistorial = 0
        this.totalClienteDescuento = 0
        this.totalClientePagado = 0
        this.ventasCliente.fechaDesde = this.$moment().format("YYYY-MM-01")
        this.ventasCliente.fechaHasta = this.$moment().format("YYYY-MM-DD")        
            
        this.axios.get(this.API + 'ventas/cliente', {
            params: {
                idCliente:  this.clienteSelect.IdCliente,
                fechaHasta: this.ventasCliente.fechaHasta,
                fechaDesde: this.ventasCliente.fechaDesde
            }
        }).then((response) => {
            this.estadoGenerarCliente = false
            this.historialCliente = response.data
            this.historialCliente.map((historia)=>{
                if(historia.Anulado==0){
                  if(historia.IdTipoDoc==9){
                    this.totalClienteHistorial -= historia.TotalVenta || 0
                    this.totalClienteDescuento -= historia.DescuentoAplicado || 0
                    this.totalClientePagado -= historia.Pagado || 0
                  }else{
                    this.totalClienteHistorial += historia.TotalVenta || 0
                    this.totalClienteDescuento += historia.DescuentoAplicado || 0
                    this.totalClientePagado += historia.Pagado || 0
                  }

                }
            })

        }).catch((error)=>{
            this.$toasted.error("Ha ocurrido un error, vuelva a intentarlo")
        })
      },
      ventasClienteFechaRango(){
        if(this.ventasCliente.fechaDesde !='' && this.ventasCliente.fechaHasta != ''){
            this.estadoGenerarCliente = true
            this.historialCliente = []
            this.totalClienteHistorial = 0
            this.totalClienteDescuento = 0
            this.totalClientePagado = 0
            this.axios.get(this.API + 'ventas/cliente', {
            params: {
                idCliente:  this.clienteSelect.IdCliente,
                fechaHasta: this.ventasCliente.fechaHasta,
                fechaDesde: this.ventasCliente.fechaDesde
            }
            }).then((response) => {
                this.estadoGenerarCliente = false
                this.historialCliente = response.data
                this.historialCliente.map((historia)=>{
                if(historia.Anulado==0){
                  if(historia.IdTipoDoc==9){
                    this.totalClienteHistorial -= historia.TotalVenta || 0
                    this.totalClienteDescuento -= historia.DescuentoAplicado || 0
                    this.totalClientePagado -= historia.Pagado || 0
                  }else{
                    this.totalClienteHistorial += historia.TotalVenta || 0
                    this.totalClienteDescuento += historia.DescuentoAplicado || 0
                    this.totalClientePagado += historia.Pagado || 0
                  }
                }
            })
            }).catch((error)=>{
                this.$toasted.error("Ha ocurrido un error, vuelva a intentarlo")
            })
        }else{
            this.$toasted.error('Por favor complete las fechas.')
        }
      },
      actualizarTabla() {
        this.$refs.tablaClientes.refresh()
      },
      nuevoCliente () {
          this.clienteSelect = {}
          this.$refs.clienteForm.show = true

      },
      myProvider (ctx) {
        // Here we don't set isBusy prop, so busy state will be handled by table itself
        // this.isBusy = true
        let url = this.API + 'clientes?limit='+ctx.perPage+'&page='+ctx.currentPage+'&q='+ (ctx.filter||'')+'&sortBy='+ctx.sortBy
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
      ClienteForm
  }
}
</script>

<style>
    #historialCliente .modal-dialog{
        max-width: 65% !important;
    }
</style>
