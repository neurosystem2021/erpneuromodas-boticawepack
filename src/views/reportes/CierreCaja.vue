<template>
  <div>
    <br>
    <b-container class="bv-example-row">
      <div>
          <div class="float-right no-print">
              <b-row>
                <b-col cols="2" v-if="!this.$route.params.id">
                    <label v-if="!this.$route.params.id" v-show="false">Vendedor:</label>
                    <b-form-group size="sm" v-show="false">
                        <autocomplete
                            required="required"
                            ref="inputVendedor"
                            :url="API + 'usuarios'"
                            anchor="NombreUsuario"
                            label="writer"
                            :on-select="setVendedor" 
                            :onFocus="focusAutocomplete.bind(null, 'inputVendedor')"
                            :classes="{input: 'form-control'}">
                        </autocomplete>
                    </b-form-group>
                </b-col>
                <b-col cols="12">
                    <b-button-group size="sm" v-if="!this.$route.params.id">
                        <b-form-select v-model="turno" :options="turnos" />
                    </b-button-group>
                    <b-button-group size="sm">
                        <b-button variant="outline-success" @click="cerrarCaja" v-if="!this.$route.params.id">Cerrar Caja</b-button>
                    </b-button-group>
                    <b-button-group size="sm">
                        <cierres-caja></cierres-caja>
                    </b-button-group>
                    <b-button-group size="sm">
                        <b-button variant="outline-primary" @click="imprimir">IMPRIMIR</b-button>
                    </b-button-group>
                    <b-button-group size="sm">
                        <b-button variant="outline-info" @click="imprimirTicket">IMPRIMIR TICKET</b-button>
                    </b-button-group>
                </b-col>
              </b-row>
                     </div>
      </div>
      <br class="no-print" /><br class="no-print" />

      <h2 class="text-center">Cierre de Caja</h2> 
      <h6 class="text-center" v-if="cierre.FechaCierre">Fecha: {{ cierre.FechaCierre }}</h6>
      <h6 class="text-center" v-if="cierre.IdTurno">Turno: {{ cierre.IdTurno == 1 ? 'MAÑANA' : (cierre.IdTurno == 2? 'TARDE':'NOCHE') }}</h6>
            <div v-if="cierre.UsuarioReg">Empleado: <b>{{ cierre.UsuarioReg }}</b></div>
      <br>
      <div class="no-print">
        <b-row id="contentModal">
          <b-col cols="4" style="margin-left:-12px">
          <table id="tablaRes1" class="table b-table table-sm table-striped table-bordered">
            <tbody>
              <tr>
                <td>Total En Caja S/.</td>
                <td>{{ granTotal | moneda }}</td>
              </tr>
            </tbody>
          </table>          
          <table id="tablaRes2" class="table b-table table-sm table-striped table-bordered">
            <tbody>
              <tr v-if="false">
                <td>Salida S/.</td>
                <td>{{ (granTotalSinPagoCliente + totalVentasCredito) | moneda }} </td>
              </tr>
              <tr>
                <td>Credito S/.</td>
                <td>{{ totalVentasCredito | moneda }} </td>
              </tr>
              <tr>
                <td>Contado S/.</td>
                <td>{{ granTotalSinPagoCliente | moneda }} </td>
              </tr>
            </tbody>
          </table>
          <table id="tablaRes3" class="table b-table table-sm table-striped table-bordered">
            <tbody>
              <tr>
                <td>Efectivo S/.</td>
                <td>{{ totalVentasEfectivo | moneda }} </td>
              </tr>
              <tr>
                <td>Visa S/.</td>
                <td>{{ totalVentasVisa | moneda }} </td>
              </tr>
              <tr>
                <td>Mastercard S/.</td>
                <td>{{ totalVentasMastercard | moneda }} </td>
              </tr>
              <tr>
                <td>Ingresos S/.</td>
                <td>{{ totalIngresos | moneda }} </td>
              </tr>
              <tr>
                <td>Salidas S/.</td>
                <td>{{ totalSalidas | moneda }} </td>
              </tr>
            </tbody>
          </table>
          </b-col>
        </b-row>
      </div>

      <b-row>
        <h5>Ventas Efectivo</h5><br>
        <table class="table b-table table-sm">
          <thead>
            <tr>
              <th>Recibo</th><th>Fecha</th><th>Cliente</th><th>Detalle</th><th>Estado</th><th>Pagado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in ventasEfectivo" :key="index" :class="{anulado: item.Anulado}">
              <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
              <td>{{ item.FechaDoc }}</td>
              <td>{{ item.Cliente }}</td>
              <td>{{ item.EfectivoDesc }}</td>
              <td>{{ estadoVenta(item) }}</td>
              <td>S/. {{ item.Efectivo | redondear }}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="4"></td>
              <td class="total"><b>Total Efectivo</b></td>
              <td class="total"><b>S/. {{ totalVentasEfectivo | redondear }}</b></td>
            </tr>
          </tfoot>
        </table>
      </b-row>


      <b-row>
        <h5>Ventas Visa</h5><br>
        <table class="table b-table table-sm">
          <thead>
            <tr>
              <th>Recibo</th><th>Fecha</th><th>Cliente</th><th>Detalle</th><th>Estado</th><th>Pagado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in ventasVisa" :key="index" :class="{anulado: item.Anulado}">
              <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
              <td>{{ item.FechaDoc }}</td>
              <td>{{ item.Cliente }}</td>
              <td>{{ item.VisaDesc }}</td>
              <td>{{ estadoVenta(item) }}</td>
              <td>S/. {{ item.Visa | redondear }}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="4"></td>
              <td class="total"><b>Total Visa</b></td>
              <td class="total"><b>S/. {{ totalVentasVisa | redondear }}</b></td>
            </tr>
          </tfoot>
        </table>
      </b-row>

      

      <b-row>
        <h5>Ventas Mastercard</h5><br>
        <table class="table b-table table-sm">
          <thead>
            <tr>
              <th>Recibo</th><th>Fecha</th><th>Cliente</th><th>Detalle</th><th>Estado</th><th>Pagado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in ventasMastercard" :key="index" :class="{anulado: item.Anulado}">
              <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
              <td>{{ item.FechaDoc }}</td>
              <td>{{ item.Cliente }}</td>
              <td>{{ item.MastercardDesc }}</td>
              <td>{{ estadoVenta(item) }}</td>
              <td>S/. {{ item.Mastercard | redondear }}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="4"></td>
              <td class="total"><b>Total Mastercard</b></td>
              <td class="total"><b>S/. {{ totalVentasMastercard | redondear }}</b></td>
            </tr>
          </tfoot>
        </table>
      </b-row>

      <b-row class="credito">
        <h5>Ventas a Credito</h5><br>
        <table class="table b-table table-sm">
          <thead>
            <tr>
              <th>Recibo</th><th>Fecha</th><th>Cliente</th><th>Estado</th><th>Pagado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in ventasCredito" :key="index" :class="{anulado: item.Anulado}">
              <td>{{ item.TipoDoc + '-' + item.Serie + '-' + item.Numero }}</td>
              <td>{{ item.FechaDoc }}</td>
              <td>{{ item.Cliente }}</td>
              <td>{{ estadoVenta(item) }}</td>
              <td>S/. {{ item.Total | redondear }}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="3"></td>
              <td><b>Total Credito</b></td>
              <td><b>S/. {{ totalVentasCredito | moneda | redondear }}</b></td>
            </tr>
          </tfoot>
        </table>
      </b-row>


      <b-row>
        <b-col v-if="totalIngresos">
          <h5>Ingresos</h5><br>
          <table class="table b-table table-sm">
            <thead>
              <tr>
                <th>ID</th><th>Fecha</th><th>Detalle</th><th>Ingreso</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in ingresos" :key="index">
                <td>{{ item.IdCajaBanco }}</td>
                <td>{{ item.FechaDoc }}</td>
                <td>{{ item.Concepto }}</td>
                <td>S/. {{ item.Importe | redondear }}</td>
              </tr>
            </tbody>
            <tfoot>
              <tr>
                <td colspan="2"></td>
                <td class="total"><b>Total Ingresos</b></td>
                <td class="total"><b>S/. {{ totalIngresos | redondear }}</b></td>
              </tr>
            </tfoot>
          </table>
        </b-col>
        <b-col v-if="totalSalidas">
          <h5>Salidas</h5><br>
          <table class="table b-table table-sm">
            <thead>
              <tr>
                <th>ID</th><th>Fecha</th><th>Detalle</th><th>Salida</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in salidas" :key="index">
                <td>{{ item.IdCajaBanco }}</td>
                <td>{{ item.FechaDoc }}</td>
                <td>{{ item.Concepto }}</td>
                <td>S/. {{ item.Importe | redondear }}</td>
              </tr>
            </tbody>
            <tfoot>
              <tr>
                <td colspan="2"></td>
                <td class="total-salida"><b>Total Salidas</b></td>
                <td class="total-salida"><b>S/. {{ totalSalidas | redondear }}</b></td>
              </tr>
            </tfoot>
          </table>
        </b-col>
      </b-row>
      

      <br>

      <b-alert variant="success" class="text-center" show><b>GRAN TOTAL: S/. {{ granTotal | moneda }}</b></b-alert>
    </b-container>
  </div>
</template>
<script>
import config from '@/store/config.js'
import CierresCaja from '@/components/CierresCaja.vue'

export default {
  mounted () {
    this.loadData()
    if (this.$route.params.id) {
      this.getCierre(this.$route.params.id)
    } else {
        this.getVendedor()
    }
  },
  data () {
    return {
      API: config.API,
      ventas: [],
      ingresos: [],
      salidas: [],
      turno: null,
      turnos: [
        { value: null, text: 'Seleccione un Turno' },
        { value: 1, text: 'Mañana' },
        { value: 2, text: 'Tarde' },
        { value: 3, text: 'Noche' }
      ],
      cierre: {},
      vendedor: {}
    }
  },
  computed: {
    getFechaCierre () {
      //return this.$route.params.id ? 
    },
    granTotal () {
      return this.totalVentasEfectivo + this.totalVentasVisa + this.totalVentasMastercard + this.totalIngresos - this.totalSalidas
    },
    granTotalSinPagoCliente () {
      return this.totalVentasEfectivo + this.totalVentasVisa + this.totalVentasMastercard  - this.totalSalidas
    },
    totalSalidas () {
      let total = 0
      this.salidas.forEach(salida => {
        if (!salida.Anulado) total += salida.Importe
      });
      return total 
    },
    totalIngresos () {
      let total = 0
      this.ingresos.forEach(ingreso => {
        if (!ingreso.Anulado) total += ingreso.Importe
      });
      return total 
    },
    totalVentasEfectivo () {
      let total = 0
      this.ventasEfectivo.forEach(venta => {
        if (!venta.Anulado) total += venta.Efectivo
      });
      return total
    },
    ventasEfectivo () {
      return this.ventas.filter(venta => {
        return venta.Efectivo > 0 && !venta.EsCredito
      })
    },
    totalVentasVisa () {
      let total = 0
      this.ventasVisa.forEach(venta => {
        if (!venta.Anulado) total += venta.Visa
      });
      return total
    },
    ventasVisa () {
      return this.ventas.filter(venta => {
        return venta.Visa > 0  && !venta.EsCredito
      })
    },
    totalVentasMastercard () {
      let total = 0
      this.ventasMastercard.forEach(venta => {
        if (!venta.Anulado) total += venta.Mastercard
      });
      return total
    },
    ventasMastercard () {
      return this.ventas.filter(venta => {
        return venta.Mastercard > 0  && !venta.EsCredito
      })
    },
    totalVentasCredito () {
      let total = 0
      this.ventasCredito.forEach(venta => {
        if (!venta.Anulado) total += venta.Total
      });
      return total
    },
    ventasCredito () {
      return this.ventas.filter(venta => {
        return venta.EsCredito
      })
    }
  },
  methods: {
    getVendedor () {
        let usuario = sessionStorage.getItem('User')
        this.axios.get(this.API + 'usuarios/' + usuario)
        .then((response) => {
            this.setVendedor(response.data)
            this.$refs.inputVendedor.setValue(response.data.NombreUsuario)
            this.$refs.inputVendedor.$refs.input.disabled = true
        })
        .catch((error) => {
            console.log(error)
            this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
        })
    },
    imprimir () {
      window.print();
    },
    imprimirTicket() {
			var left = (screen.width/2)-(800/2);
			var top = (screen.height/2)-(800/2);
			var myWindow = window.open ('', 'Imprimir Ticket', 'toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=no, resizable=no, copyhistory=no, width=800, height=800, top='+top+', left='+left);
      myWindow.document.write("<div style='text-align:center'><h2> Cierre de Caja </h2></div>");
      if(this.cierre.FechaCierre){
        myWindow.document.write("<label><b> Fecha: "+this.cierre.FechaCierre+"</b></label><br>");
      }
      if(this.cierre.IdTurno){
        myWindow.document.write("<label><b> Turno: "+(this.cierre.IdTurno == 1 ? 'MAÑANA' : (this.cierre.IdTurno == 2? 'TARDE':'NOCHE'))+"</b></label><br>");
      }
      if(this.cierre.UsuarioReg){
        myWindow.document.write("<label><b> Empleado: "+this.cierre.UsuarioReg+"</b></label><br><br>");
      }
      
      myWindow.document.write("<table style='border: 1px solid; width:230px'>"+document.getElementById('tablaRes1').innerHTML+"</table><br>");
      myWindow.document.write("<table style='border: 1px solid; width:230px'>"+document.getElementById('tablaRes2').innerHTML+"</table><br>");
      myWindow.document.write("<table style='border: 1px solid; width:230px'>"+document.getElementById('tablaRes3').innerHTML+"</table><br>");
			myWindow.document.close();
			myWindow.focus();
			myWindow.print();
			myWindow.close();
		},
    loadData () {
      this.getVentas()
      this.getIngresos()
      this.getSalidas()
    },
    setVendedor (obj) {
        this.vendedor = obj
        this.loadData()
    },
    cerrarCaja () {
      if (!this.turno) {
        this.$toasted.show('Seleccione un Turno')
        return
      }
      let r = confirm('¿Está seguro de cerrar la caja?')
      if (r) {
        this.axios.post(this.API + 'cierrecaja', {
          idTurno: this.turno,
          //user: sessionStorage.getItem("User")
          user: this.vendedor.Usuario
        }).then(res => {
          this.loadData()
          this.$toasted.show('¡Caja cerrada!')
          console.log(res)
        })
      }
    },
    focusAutocomplete (ref) {
        // this.$refs[ref].type = ""
        this.$refs[ref].handleDoubleClick()
    },
    estadoVenta (venta) {
        return venta.Anulado ? 'ANULADO' : (venta.EsCredito ? 'CREDITO' : 'APLICADO')
    },
    getVentas () {
      let params = {
        idCierre: this.$route.params.id
      }
      if (this.vendedor.Usuario) {
        // params.usuario = this.vendedor.Usuario
      }
      // cierre de caja enviar el vendedor
      this.axios.get(this.API + 'cierrecaja/ventas', {
        params: params
      })
      .then(res => {
        this.ventas = res.data
      }).catch(err => {
        console.log(err)
      })
    },
    getIngresos () {
      let params = {
        idCierre: this.$route.params.id,
        idCuenta: 1
      }
      if (this.vendedor.Usuario) {
        // params.usuario = this.vendedor.Usuario
      }
      this.axios.get(this.API + 'cierrecaja/ingresos',{
        params: params
      })
      .then(res => {
        this.ingresos = res.data
      }).catch(err => {
        console.log(err)
      }) 
    },
    getSalidas () {
      let params = {
        idCierre: this.$route.params.id,
        idCuenta: 1
      }
      if (this.vendedor.Usuario) {
        // params.usuario = this.vendedor.Usuario
      }
      this.axios.get(this.API + 'cierrecaja/salidas', {
        params: params
      })
      .then(res => {
        this.salidas = res.data
      }).catch(err => {
        console.log(err)
      }) 
    },
    getCierre(id) {
      this.axios.get(this.API + 'cierrecaja', {
        params: {
          idCierre: id
        }
      }).then(res =>{
        this.cierre = res.data[0]
      })
    }
  },
  components: {
    CierresCaja
  }
}
</script>
<style scoped>
th {
  background-color: #f1f1f1;
}
.total {
  background-color: #e4ff95;
}
.total-salida {
  background-color: #ffd4d3;  
}
.anulado td{
  /*font-style: italic;*/
  text-decoration: line-through;
}
.credito {
  background: #f9f9f9;
  padding: 10px;
}
.si-print {
  display: none;
}
@media print {
  .no-print {
    display: none;
  }
  .si-print {
    display: block;
  }
}
</style>
