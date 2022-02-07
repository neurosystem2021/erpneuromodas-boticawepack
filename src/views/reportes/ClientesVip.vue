<template>
  <div>
      <b-container class="bv-example-row">
        <br>
        <b-row>
            <b-col md="6" class="text-left">
                <h1>Clientes VIP y Puntos</h1>
            </b-col>
            <b-col md="6" class="text-right">
              <b-button variant="primary" size="md" @click="abrirVentasPuntos">VENTAS CON PUNTOS APLICADOS</b-button>
            </b-col>
        </b-row>
        <hr>
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
              <p style="margin-top: 6px;">de <b class="text-success">{{ this.totalRows }}</b> Clientes VIP</p>
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
            @row-clicked="rowClick"
            id="my-table" 
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
        <b-row align-h="center">
            <b-col md="12" class="text-right">
              <b-button variant="success" size="lg" :href="hrefXlsClientes" target="_blank">Exportar Excel Puntos Clientes</b-button>
            </b-col>
        </b-row>
        <br /><br />
      </b-container>
        <b-modal id="ventasCliente" size="lg" modal-class="modal-xl" v-model="showKardex" 
        ok-title="Cerrar" ok-only title="HISTORIAL DE PUNTOS APLICADOS DEL CLIENTE">
        <b-alert show variant="warning" class="font-weight-bold">{{ titleVentasPuntos }}</b-alert>
        <b-alert show variant="warning" class="font-weight-bold">Puntos Actuales: {{ clienteSelect.Puntos }} - Puntos Aplicados: {{ clienteSelect.PuntosAplicados }} </b-alert>
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
                            <th>SERIE</th>
                            <th>NUMERO</th>
                            <th>PUNTOS APLI.</th>
                            <th>MONTO VENTA</th>
                            <th>DESC. APLI.</th>
                            <th>MONTO PAGADO</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(venta, i) in historialCliente" :key="i">
                            <td>{{ venta.idDocVenta }}</td>
                            <td>{{ venta.FechaDoc }}</td>
                            <td style="text-transform:uppercase">{{ venta.UsuarioReg }}</td>
                            <td>{{ venta.Serie }}</td>
                            <td>{{ venta.Numero }}</td>
                            <td class="table-warning">{{ venta.PuntosAplicados }}</td>
                            <td class="table-primary">{{ venta.TotalVenta }}</td>
                            <td class="table-danger">{{ venta.DescuentoAplicado }}</td>
                            <td class="table-success">{{ venta.Pagado }}</td>
                        </tr>
                    </tbody>
                    <tfoot v-if="historialCliente.length>0">
                        <td colspan="5" class="font-weight-bold table-secondary">SUMATORIA</td>
                        <td class="font-weight-bold table-warning">{{ totalClienteAplicados }}</td>
                        <td class="font-weight-bold table-primary">S/ {{ totalClienteHistorial | moneda }}</td>
                        <td class="font-weight-bold table-danger">S/ {{ totalClienteDescuento | moneda }}</td>
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
        <b-row v-if="historialCliente.length==0 && estadoGenerarCliente==false && clienteSelect.PuntosAplicados>0 ">
            <b-col class="text-center">
                <b-form-text style="font-size:20px" text-variant="danger">... EL CLIENTE TIENE PUNTOS APLICADOS, PORFAVOR SELECCIONE FECHAS ANTERIORES ...</b-form-text>
                <br>
            </b-col>
        </b-row>
        <b-row v-if="historialCliente.length==0 && estadoGenerarCliente==false && clienteSelect.PuntosAplicados==0 ">
            <b-col class="text-center">
                <b-form-text style="font-size:20px" text-variant="danger">... EL CLIENTE NO TIENE PUNTOS APLICADOS ...</b-form-text>
                <br>
            </b-col>
        </b-row>
        <b-row>
          <b-col md="12" class="text-center">
              <b-button variant="success" size="md" :href="hrefXlsHistorialCliente" target="_blank">Exportar Excel Cliente</b-button>
          </b-col>
        </b-row>
        <br>
      </b-modal>
      <!-- VENTAS CON PUNTOS APLICADOS -->
      <b-modal id="ventasPuntos" size="lg" modal-class="modal-xl" v-model="showVentasPuntos" 
        ok-title="Cerrar" ok-only title="VENTAS CON PUNTOS APLICADOS">
        <b-row>
          <b-col md="5">
            <b-form-group>
              <label>Fecha Inicio:</label>
              <b-form-input
                v-model="ventas.fechaDesde"
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
                v-model="ventas.fechaHasta"
                type="date"
                step="1"
                placeholder="Fecha Inicio">
              </b-form-input>
            </b-form-group>
          </b-col>
          <b-col md="2" class="text-center">
            <b-form-group>
              <br>
              <b-button :disabled="estadoGenerarVentas" @click="ventasFechaRango" variant="primary" size="lg">Generar</b-button>
            </b-form-group>
          </b-col>
        </b-row>
        <b-row>
            <b-col md="12" class="text-center">
                <div class="table-responsive">
                    <table class="table table-striped b-table table-sm">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>FECHA</th>
                            <th>VENDEDOR</th>
                            <th>CLIENTE</th>
                            <th>SERIE</th>
                            <th>NUMERO</th>
                            <th>PUNTOS APLI.</th>
                            <th>MONTO VENTA</th>
                            <th>DESC. APLI.</th>
                            <th>MONTO PAGADO</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(venta, i) in historialVentas" :key="i">
                            <td>{{ venta.idDocVenta }}</td>
                            <td>{{ venta.FechaDoc }}</td>
                            <td style="text-transform:uppercase">{{ venta.UsuarioReg }}</td>
                            <td>{{ venta.Cliente }}</td>
                            <td>{{ venta.Serie }}</td>
                            <td>{{ venta.Numero }}</td>
                            <td class="table-warning">{{ venta.PuntosAplicados }}</td>
                            <td class="table-primary">{{ venta.TotalVenta }}</td>
                            <td class="table-danger">{{ venta.DescuentoAplicado }}</td>
                            <td class="table-success">{{ venta.Pagado }}</td>
                        </tr>
                    </tbody>
                    <tfoot v-if="historialVentas.length>0">
                        <td colspan="6" class="font-weight-bold table-secondary">SUMATORIA</td>
                        <td class="font-weight-bold table-warning" >{{ totalVentasAplicados }}</td>
                        <td class="font-weight-bold table-primary">S/ {{ totalVentasHistorial | moneda }}</td>
                        <td class="font-weight-bold table-danger">S/ {{ totalVentasDescuento | moneda }}</td>
                        <td class="font-weight-bold table-success">S/ {{ totalVentasPagado | moneda }}</td>
                    </tfoot>
                </table>
                </div>
            </b-col>
        </b-row>
        <b-row v-if="estadoGenerarVentas">
            <b-col class="text-center">
                <b-form-text style="font-size:20px" text-variant="success">... GENERANDO DATOS, ESPERE POR FAVOR ...</b-form-text>
                <br>
            </b-col>
        </b-row>
        <b-row v-if="historialVentas.length==0 && estadoGenerarVentas==false">
            <b-col class="text-center">
                <b-form-text style="font-size:20px" text-variant="danger">... NO HAY VENTAS CON PUNTOS APLICADOS, PORFAVOR SELECCIONE FECHAS ANTERIORES ...</b-form-text>
                <br>
            </b-col>
        </b-row>
        <b-row>
          <b-col md="12" class="text-center">
              <b-button variant="success" size="md" :href="hrefXlsHistorialVenta" target="_blank">Exportar Excel Venta</b-button>
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
    this.axios.get(this.API + 'vip/clientes/count').then((response) => {
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
            { key: 'Puntos', label:'Puntos Actuales', sortable: true },
            { key: 'PuntosAplicados',label:'Puntos Aplicados (Total)'},
      ],
      totalRows: 50,
      perPage: 10,
      currentPage:1,
      showKardex: false,
      ventasCliente: {
        fechaDesde: this.$moment().format("YYYY-MM-01"),
        fechaHasta: this.$moment().format("YYYY-MM-DD")
      },
      historialCliente: [],
      estadoGenerarCliente: true,
      totalClienteHistorial: 0,
      totalClienteAplicados: 0,
      totalClienteDescuento: 0,
      totalClientePagado: 0,
      //Ventas
      showVentasPuntos:false,
      ventas: {
        fechaDesde: this.$moment().format("YYYY-01-01"),
        fechaHasta: this.$moment().format("YYYY-MM-DD")
      },
      estadoGenerarVentas:true,
      historialVentas:[],
      totalVentasHistorial: 0,
      totalVentasAplicados: 0,
      totalVentasDescuento: 0,
      totalVentasPagado: 0
    }
  },
  computed: {
       titleVentasPuntos () {
         return 'CLIENTE VIP: ' + this.clienteSelect.DniRuc + ' - ' + this.clienteSelect.Cliente
       },
       hrefXlsClientes () {
        return ('/api/index.php/reporte/clientes/puntos');
       },
       hrefXlsHistorialCliente () {
        return ('/api/index.php/reporte/cliente/puntos?idCliente=' + this.clienteSelect.IdCliente + '&fechaDesde='+this.ventasCliente.fechaDesde+'&fechaHasta='+this.ventas.fechaHasta).replace('#','');
       },
       hrefXlsHistorialVenta () {
        return ('/api/index.php/reporte/ventas/puntos?fechaDesde='+this.ventas.fechaDesde+'&fechaHasta='+this.ventas.fechaHasta).replace('#','');
       },
  },
  methods: {
      rowClick (item, index) {
        this.clienteSelect = item
        this.showKardex = true
        this.estadoGenerarCliente = true
        this.historialCliente = []
        this.totalClienteHistorial = 0
        this.totalClienteAplicados = 0
        this.totalClienteDescuento = 0
        this.totalClientePagado = 0
        this.ventasCliente.fechaDesde = this.$moment().format("YYYY-MM-01")
        this.ventasCliente.fechaHasta = this.$moment().format("YYYY-MM-DD")        
            
        this.axios.get(this.API + 'vip/clientes/ventas', {
            params: {
                idCliente:  this.clienteSelect.IdCliente,
                fechaHasta: this.ventasCliente.fechaHasta,
                fechaDesde: this.ventasCliente.fechaDesde
            }
        }).then((response) => {
            this.estadoGenerarCliente = false
            this.historialCliente = response.data
            this.historialCliente.map((historia)=>{
                this.totalClienteHistorial += historia.TotalVenta || 0
                this.totalClienteAplicados += historia.PuntosAplicados || 0
                this.totalClienteDescuento += historia.DescuentoAplicado || 0
                this.totalClientePagado += historia.Pagado || 0
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
            this.totalClienteAplicados = 0
            this.totalClienteDescuento = 0
            this.totalClientePagado = 0
            this.axios.get(this.API + 'vip/clientes/ventas', {
            params: {
                idCliente:  this.clienteSelect.IdCliente,
                fechaHasta: this.ventasCliente.fechaHasta,
                fechaDesde: this.ventasCliente.fechaDesde
            }
            }).then((response) => {
                this.estadoGenerarCliente = false
                this.historialCliente = response.data
                this.historialCliente.map((historia)=>{
                    this.totalClienteHistorial += historia.TotalVenta || 0
                    this.totalClienteAplicados += historia.PuntosAplicados || 0
                    this.totalClienteDescuento += historia.DescuentoAplicado || 0
                    this.totalClientePagado += historia.Pagado || 0
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
      abrirVentasPuntos(){
        this.showVentasPuntos = true
        this.estadoGenerarVentas = true
        this.historialVentas = []
        this.totalVentasHistorial = 0
        this.totalVentasAplicados = 0
        this.totalVentasDescuento = 0
        this.totalVentasPagado = 0
        this.ventas.fechaDesde = this.$moment().format("YYYY-01-01")
        this.ventas.fechaHasta = this.$moment().format("YYYY-MM-DD")        
            
        this.axios.get(this.API + 'vip/ventas', {
            params: {
                fechaHasta: this.ventas.fechaHasta,
                fechaDesde: this.ventas.fechaDesde
            }
        }).then((response) => {
            this.estadoGenerarVentas = false
            this.historialVentas = response.data
            this.historialVentas.map((historia)=>{
                this.totalVentasHistorial += historia.TotalVenta || 0
                this.totalVentasAplicados += historia.PuntosAplicados || 0
                this.totalVentasDescuento += historia.DescuentoAplicado || 0
                this.totalVentasPagado += historia.Pagado || 0
            })
        }).catch((error)=>{
            this.$toasted.error("Ha ocurrido un error, vuelva a intentarlo")
        })
      },
      ventasFechaRango(){
        if(this.ventas.fechaDesde !='' && this.ventas.fechaHasta != ''){
            this.estadoGenerarVentas = true
            this.historialVentas = []
            this.totalVentasHistorial = 0
            this.totalVentasAplicados = 0
            this.totalVentasDescuento = 0
             this.totalVentasPagado = 0
            this.axios.get(this.API + 'vip/ventas', {
                params: {
                    fechaHasta: this.ventas.fechaHasta,
                    fechaDesde: this.ventas.fechaDesde
                }
            }).then((response) => {
                this.estadoGenerarVentas = false
                this.historialVentas = response.data
                this.historialVentas.map((historia)=>{
                    this.totalVentasHistorial += historia.TotalVenta || 0
                    this.totalVentasAplicados += historia.PuntosAplicados || 0
                    this.totalVentasDescuento += historia.DescuentoAplicado || 0
                    this.totalVentasPagado += historia.Pagado || 0
                })
            }).catch((error)=>{
                this.$toasted.error("Ha ocurrido un error, vuelva a intentarlo")
            })
        }else{
            this.$toasted.error('Por favor complete las fechas.')
        }
      },
      myProvider (ctx) {
        // Here we don't set isBusy prop, so busy state will be handled by table itself
        // this.isBusy = true
        let url = this.API + 'vip/clientes?limit='+ctx.perPage+'&page='+ctx.currentPage+'&q='+ (ctx.filter||'')+'&sortBy='+ctx.sortBy
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
    #ventasPuntos .modal-dialog{
        max-width: 65% !important;
    }
    #ventasCliente .modal-dialog{
        max-width: 65% !important;
    }
</style>
