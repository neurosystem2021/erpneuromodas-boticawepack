<template>
  <div>
    <br>
    <b-container class="bv-example-row">
      <b-row  align-h="end" class=" no-print">
              <label v-if="!this.$route.params.id">Vendedor:</label>
          <b-col cols="3" v-if="!this.$route.params.id">
            <b-form-group size="sm">
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
          <b-col cols="7">
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
          </b-col>
      </b-row>
      <br class="no-print" /> 

      <h3 class="text-center">Cierre de Caja</h3> 
      <h4 class="si-print">Cierre de Caja </h4>
      <hr>
      <div v-if="cierre.FechaCierre">Fecha Fin:  <b>{{ cierre.FechaCierre }}</b></div>
      <div v-if="cierre.IdTurno">Turno: <b>{{ cierre.IdTurno == 1 ? 'MAÑANA' : 'TARDE' }}</b></div>
      <div v-if="cierre.UsuarioReg">Empleado: <b>{{ cierre.UsuarioReg }}</b></div>
      <br>
      <b-row>
        <b-col cols="4">
        <table class="table b-table table-sm table-striped table-bordered">
          <tbody>
            <tr>
              <td>Ingreso Neto S/.</td>
              <td>{{ granTotal | moneda }}</td>
            </tr>
          </tbody>
        </table>          
        <table class="table b-table table-sm table-striped table-bordered">
          <tbody>
            <tr>
              <td>Total Ventas S/.</td>
              <td>{{ (this.totalVentasEfectivo ) | moneda }} </td>
            </tr>
            <tr>
              <td>Ventas Credito S/.</td>
              <td>{{ totalVentasCredito | moneda }} </td>
            </tr>
            <tr>
              <td>Ventas Visa/Mastercard S/.</td>
              <td>{{ totalVentasVisa + totalVentasMastercard | moneda }}</td>
            </tr>
            <!-- <tr>
              <td>Contado S/.</td>
              <td>{{ granTotalSinPagoCliente | moneda }} </td>
            </tr> -->
          </tbody>
        </table>
        <table class="table b-table table-sm table-striped table-bordered">
          <tbody>
            <tr>
              <td>Efectivo S/.</td>
              <td>{{ totalVentasEfectivo | moneda }} </td>
            </tr>
            <!-- <tr>
              <td>Visa S/.</td>
              <td>{{ totalVentasVisa | moneda }} </td>
            </tr>
            <tr>
              <td>Mastercard S/.</td>
              <td>{{ totalVentasMastercard | moneda }} </td>
            </tr> -->
            <tr>
              <td>Ingresos S/.</td>
              <td>{{ totalIngresos | moneda }} </td>
            </tr>
             <tr>
              <td>Egresos S/.</td>
              <td>{{ totalSalidas | moneda }} </td>
            </tr>
          </tbody>
        </table>
        </b-col>
      </b-row>

      <b-row  class="no-print">
        <b-col>
<!--           <table class="table b-table table-sm table-striped table-bordered">
            <thead>
              <tr>
                <th>Fecha</th>
                <th>TipoDoc</th>
                <th>Serie</th>
                <th>Numero</th>
                <th>Total</th>
                <th>Condición Venta</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(venta, index) in ventas" :key="index">
                <td>{{ venta.FechaDoc }}</td>
                <td>{{ venta.TipoDoc }}</td>
                <td>{{ venta.Serie }}</td>
                <td>{{ venta.Numero }}</td>
                <td>{{ venta.Total | moneda }}</td>
                <td>{{ estadoVenta(venta) }}</td>
              </tr>
              <tr v-for="(item, index) in ingresos" :key="index">
                <td>{{ item.FechaDoc }}</td>
                <td>RECIBO</td>
                <td>{{ item.Concepto }}</td>
                <td>S/. {{ item.Importe | redondear }}</td>
              </tr>
            </tbody>
          </table> -->
        </b-col>
      </b-row>
      

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
              <td>
                
                <table class="tabla-detalle">
                  <thead>
                    <tr>
                      <th>Codigo</th>
                      <th>Producto</th>
                      <th>Cantidad</th>
                      <th>Precio</th>
                      <th>Descuento</th>
                      <th v-if="item.TieneIgv">Subtotal</th>
                      <th v-if="item.TieneIgv">IGV</th>
                      <th>Total</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(prod, index) in item.productos" :key="index">
                      <td>{{ prod.CodigoBarra }}</td>
                      <td class="detalle-producto">{{ prod.Producto }}</td>
                      <td>{{ prod.Cantidad }}</td>
                      <td>{{ prod.Precio }}</td>
                      <td>{{ prod.Descuento }}</td>
                      <td v-if="item.TieneIgv">
                        {{ prod.Subtotal - (prod.Subtotal * 0.18) | redondear }}
                      </td>
                      <td v-if="item.TieneIgv">
                        {{ prod.Subtotal * 0.18 | redondear}}
                      </td>
                      <td>{{ prod.Subtotal | redondear }}</td>
                    </tr>
                  </tbody>
                </table>
                <div><i><b>Cliente:</b> {{ item.Cliente }}</i></div>

              </td>
              <td>{{ item.EfectivoDesc }}</td>
              <td>{{ estadoVenta(item) }}</td>
              <td>S/. {{ item.Efectivo }}</td>
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
              <td><b>S/. {{ totalVentasCredito | moneda }}</b></td>
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
                <td>{{ item.TipoCajaBanco + ' - ' + item.Cliente + ' - ' + item.Concepto }}</td>
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
                <td>{{ item.TipoCajaBanco + ' - ' + item.Proveedor + ' - ' + item.Concepto }}</td>
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

      <b-alert variant="success" class="text-center no-print" show><b>GRAN TOTAL: S/. {{ granTotal | moneda }}</b></b-alert>
    </b-container>
  </div>
</template>
<script>
import config from '@/store/config.js'
import CierresCaja from '@/components/CierresCaja.vue'

export default {
  mounted () {
    if (this.$route.params.id) {
      this.getCierre(this.$route.params.id)
      this.loadData()
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
        { value: 2, text: 'Tarde' }
      ],
      cierre: {},
      vendedor: {},
      almacen: {}
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
    getDetalles (idDocVenta) {
      let detalles = this.itemsDetalle.filter(item => item.IdDocVenta == idDocVenta)
      //let index = this.items.findIndex((item) => item.idDocVenta == idDocVenta)
      //this.items[index].productos = detalles
      return detalles
    },
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
    setVendedor (obj) {
        this.vendedor = obj
        this.loadData()
    },
    setAlmacen (obj) {
      this.almacen = obj;
      this.loadData()
    },
    imprimir () {
      window.print();
    },
    loadData () {
      this.getVentas()
      this.getIngresos()
      this.getSalidas()
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
          user: this.vendedor.Usuario,
          idAlmacen: this.almacen.IdAlmacen,
          ingresos: parseFloat(this.granTotal + this.totalSalidas).toFixed(2),
          salidas: this.totalSalidas,
          total: this.granTotal
        }).then(res => {
          this.loadData()
          this.$toasted.show('¡Caja cerrada!')
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
        params.usuario = this.vendedor.Usuario
      }
      if (this.almacen.IdAlmacen) {
        params.IdAlmacen = this.almacen.IdAlmacen
      }
      // cierre de caja enviar el vendedor
      this.axios.get(this.API + 'cierrecaja/ventas', {
        params: params
      })
      .then(res => {
        this.ventas = res.data
        this.loadDetalles()
      }).catch(err => {
        console.log(err)
      })
    },
    getIngresos () {
      let params = {
        idCierre: this.$route.params.id
      }
      if (this.vendedor.Usuario) {
        params.usuario = this.vendedor.Usuario
      }
      if (this.almacen.IdAlmacen) {
        params.IdAlmacen = this.almacen.IdAlmacen
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
        idCierre: this.$route.params.id
      }
      if (this.vendedor.Usuario) {
        params.usuario = this.vendedor.Usuario
      }
      if (this.almacen.IdAlmacen) {
        params.IdAlmacen = this.almacen.IdAlmacen
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
    },
    loadDetalles () {
      this.axios.get(this.API + 'ventas/detalle', {
          params: {
            idDocVentas: this.ventas.map((item) => item.idDocVenta),
          }
        }).then((response) => {
            this.itemsDetalle = response.data
            this.ventas = this.ventas.map((item) => {
              item.productos = this.getDetalles(item.idDocVenta)
              return item
            })
        })
        .catch((error) => {
          console.log(error)
        })
    },
    /*getPenultimoCierre(id) {
      this.axios.get(this.API + 'cierrecaja/penultimo', {
        params: {
          idCierre: id
        }
      }).then(res =>{
        this.cierre = res.data[0]
      })
    }*/
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
.tabla-detalle {
}
.tabla-detalle th {
  background-color: #ffffff;
  font-weight: normal;
}
.tabla-detalle th, .tabla-detalle td {
  font-size: .9em;
}
.detalle-producto {
  width: 270px;
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
