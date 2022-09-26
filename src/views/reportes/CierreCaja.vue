<template>
  <div :class="{'fondo-empresa0': (this.empresa === 'modasm'),'fondo-empresa1': (this.empresa === 'modasgio'),'fondo-empresa2': (this.empresa === 'modasjes'),'fondo-empresa3': (this.empresa === 'modasjun'),'fondo-empresa4': (this.empresa === 'modaspau')}">
    <br>
    <b-container class="bv-example-row">
      <div>
          <div class="no-print">
              <div class="float-left"  v-if="!this.$route.params.id">
                  <b-button-group size="sm">
                      <select class="form-control" v-model="empresa" @change="cambiarEmpresa" style="font-weight: bold; width: 400px">
                          <option value="modasm">EMP. MODASM (PRENDAS DE VESTIR)</option>
                          <option value="modasgio">EMP. MODASGIO (LOCERIA, CRISTALERIA Y MENAJE)</option>
                          <option value="modasjes">EMP. MODASJES (PLASTIQUERIA)</option>
                          <option value="modasjun">EMP. MODASJUN (PASAMANERIA Y BAZAR)</option>
                          <option value="modaspau">EMP MODASPAU (ALUMNIO Y ELECTRICIDAD)</option>
                      </select>
                  </b-button-group>
              </div>
              <b-row align-h="end" class=" no-print">
                    <!--<label v-if="!this.$route.params.id" >Vendedor:</label>
                <b-col cols="3" v-if="!this.$route.params.id">
                    <b-form-group size="sm" >
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
                </b-col>-->
                <b-col cols="7">
                    <b-button-group size="sm" v-if="!this.$route.params.id">
                        <b-form-select v-model="turno" :options="turnos" />
                    </b-button-group>
                    <b-button-group size="sm">
                        <b-button variant="outline-success" @click="cerrarCaja" v-if="!this.$route.params.id">Cerrar Caja</b-button>
                    </b-button-group>
                    <b-button-group size="sm">
                        <cierres-caja :empresa="empresa"></cierres-caja>
                    </b-button-group>
                    <b-button-group size="sm">
                        <b-button variant="outline-primary" @click="imprimir">IMPRIMIR</b-button>
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
      <b-row>
        <h5>Ventas Efectivo</h5><br>
        <table class="table b-table table-sm">
          <thead>
            <tr>
                <th>Recibo</th><th>Fecha</th><th>Productos</th><th>Estado</th><th style="width:90px">Pagado</th>
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
                <div><i><small><b>Cliente:</b> {{ item.Cliente }}</small></i></div>

              </td>
              <!--<td>{{ item.EfectivoDesc }}</td>-->
              <td>{{ estadoVenta(item) }}</td>
              <td>S/. {{ item.Efectivo | redondear }}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="3"></td>
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
              <th>Recibo</th><th>Fecha</th><th>Productos</th><th>Detalle</th><th>Estado</th><th>Pagado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in ventasVisa" :key="index" :class="{anulado: item.Anulado}">
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
                <div><i><small><b>Cliente:</b> {{ item.Cliente }}</small></i></div>

              </td>
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
                <th>Recibo</th><th>Fecha</th><th>Productos</th><th>Detalle</th><th>Estado</th><th>Pagado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in ventasMastercard" :key="index" :class="{anulado: item.Anulado}">
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
                <div><i><small><b>Cliente:</b> {{ item.Cliente }}</small></i></div>
                    
              </td>
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
              <th>Recibo</th><th>Fecha</th><th>Productos</th><th>Estado</th><th>Pagado</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in ventasCredito" :key="index" :class="{anulado: item.Anulado}">
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
                <div><i><small><b>Cliente:</b> {{ item.Cliente }}</small></i></div>


              </td>
              <td>{{ estadoVenta(item) }}</td>
              <td>S/. {{ item.Total | redondear }}</td>
            </tr>
          </tbody>
          <tfoot>
            <tr>
              <td colspan="3"></td>
              <td><b>Total Credito</b></td>
              <td><b>S/. {{ totalVentasCredito | redondear }}</b></td>
            </tr>
          </tfoot>
        </table>
      </b-row>


      <b-row>
        <b-col v-if="totalIngresos">
          <h5>Ingresos Caja Principal</h5><br>
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
      

      <b-row>
        <b-col v-if="totalIngresosTarjeta">
          <h5>Ingresos de Cuentas Bancarias </h5><br>
          <table class="table b-table table-sm">
            <thead>
              <tr>
                <th>ID</th><th>Cuenta</th><th>Fecha</th><th>Detalle</th><th>Ingreso</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, index) in ingresosTarjeta" :key="index">
                <td>{{ item.IdCajaBanco }}</td>
                <td>{{ item.Cuenta }}</td>
                <td>{{ item.FechaDoc }}</td>
                <td>{{ item.Concepto }}</td>
                <td>S/. {{ item.Importe | redondear }}</td>
              </tr>
            </tbody>
            <tfoot>
              <tr>
                <td colspan="2"></td>
                <td class="total"><b>Total Ingresos tarjeta</b></td>
                <td class="total"><b>S/. {{ totalIngresosTarjeta | redondear }}</b></td>
              </tr>
            </tfoot>
          </table>
        </b-col>
      </b-row>

      <b-row>
          <b-col v-if="totalAdelantos">
            <h5>Descontar Adelantos</h5><br>
            <table class="table b-table table-sm adelantos">
              <thead>
                <tr>
                  <th>ID</th><th>Fecha</th><th>Detalle</th><th>Ingreso</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(item, index) in adelantos" :key="index">
                  <td>{{ item.IdCajaBanco }}</td>
                  <td>{{ item.FechaDoc }}</td>
                  <td>{{ item.Concepto }} ({{ item.Serie }}-{{ item.Numero }})</td>
                  <td>S/. {{ item.Importe | redondear }}</td>
                </tr>
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="2"></td>
                  <td class="total"><b>Total Adelantos</b></td>
                  <td class="total"><b>S/. {{ totalAdelantos | redondear }}</b></td>
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
  beforeMount () {
    let empresa = sessionStorage.getItem('empresa') || 'modasjes';

    switch (this.$route.query.empresa) {
      case 'modasm':{
        empresa = 'modasm';
        break;
      }
        
      case 'modasgio':{
        empresa = 'modasgio';
        break;
      } 
      
      case 'modasjun':{
        empresa = 'modasjun';
        break;
      }

      case 'modaspau':{
        empresa = 'modaspau';
        break;
      }
    
      default:
        break;
    }
    
    // Interceptos XMLHttpRequest
    let oldXHROpen = window.XMLHttpRequest.prototype.open;
    window.XMLHttpRequest.prototype.open = function(method, url, async, user, password) {
        // do something with the method, url and etc.
        var newUrl = url.indexOf(location.protocol + '//') !== -1 ? url : location.protocol + '//' + location.host + url;
        newUrl = new URL(newUrl);
        newUrl.searchParams.append('empresa', empresa);
        url = newUrl.toString();
        arguments[1] = url
        return oldXHROpen.apply(this, arguments);
    }
  },
  mounted () {
    if (this.$route.params.id) {
      this.getCierre(this.$route.params.id)
      this.loadData()
    } else {
        //this.getVendedor()
      this.loadData()
    }

    if (sessionStorage.getItem('empresa')) {
      this.empresa = sessionStorage.getItem('empresa');
    }
  },
  data () {
    return {
      API: config.API,
      ventas: [],
      ingresos: [],
      ingresosTarjeta: [],
      salidas: [],
	  adelantos: [],
      turno: null,
      turnos: [
        { value: null, text: 'Seleccione un Turno' },
        { value: 1, text: 'Mañana' },
        { value: 2, text: 'Tarde' },
        { value: 3, text: 'Noche' }
      ],
      cierre: {},
      vendedor: {},
      empresa: 'modasjes'
    }
  },
  computed: {
    getFechaCierre () {
      //return this.$route.params.id ? 
    },
    granTotal () {
      return this.totalVentasEfectivo + this.totalVentasVisa + this.totalVentasMastercard + this.totalIngresos - this.totalSalidas - this.totalAdelantosActivos
    },
    granTotalSinPagoCliente () {
      return this.totalVentasEfectivo + this.totalVentasVisa + this.totalVentasMastercard  - this.totalSalidas
    },
    totalVentas () {
      return this.totalVentasEfectivo + this.totalVentasVisa + this.totalVentasMastercard
    },
    totalSalidas () {
      let total = 0
      this.salidas.forEach(salida => {
        if (!salida.Anulado) total += salida.Importe
      });
      return total 
    },
    totalIngresosTarjeta () {
      let total = 0
      this.ingresosTarjeta.forEach(ingresoTar => {
        if (!ingresoTar.Anulado) total += ingresoTar.Importe
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
  	totalAdelantos () {
  		let total = 0
  		this.adelantos.forEach(ade => {
  			if (!ade.Anulado) total += ade.Importe
  		})
  		return total
  	},
    totalAdelantosActivos () {
  		let total = 0
  		this.adelantos.forEach(ade => {
  			if (!ade.Anulado  &&  !ade.AnuladoVenta && !ade.EsCreditoVenta) total += ade.Importe
  		})
  		return total
  	},
    totalVentasEfectivo () {
      let total = 0
      this.ventasEfectivo.forEach(venta => {
        if (!venta.Anulado && venta.CodSunat!='07') total += venta.Efectivo
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
    cambiarEmpresa () {
        sessionStorage.setItem('empresa', this.empresa)
        window.location.reload();
        return false;
    },
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
            //this.$refs.inputVendedor.setValue(response.data.NombreUsuario)
            // this.$refs.inputVendedor.$refs.input.disabled = true

            if(response.data.IdUsuarioPerfil !== 1) {
              //this.$refs.inputVendedor.$refs.input.disabled = true
            }
        })
        .catch((error) => {
            console.log(error)
            this.$toasted.show('Ha ocurrido un error, vuelva a intentarlo')
        })
    },
    imprimir () {
      window.print();
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
          idTurno:      this.turno,
          user: sessionStorage.getItem("User"),
          //user:         this.vendedor.Usuario,
          ingresos:     parseFloat(this.granTotal + this.totalSalidas).toFixed(2),
          salidas:      this.totalSalidas,
          total:        this.granTotal,
          totalVentas:  this.totalVentas
        }).then(res => {
          this.loadData()
          this.$toasted.show('¡Caja cerrada!')
          // console.log(res)
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
    getVentas () {
      let params = {
        idCierre: this.$route.params.id
      }
      if (this.vendedor.Usuario) {
        //params.usuario = this.vendedor.Usuario
      }
      // cierre de caja enviar el vendedor
      this.axios.get(this.API + 'cierrecaja/ventas', {
        params: params
      })
      .then(res => {
        this.ventas = res.data
        this.loadDetalles()
		this.getAdelantos()
      }).catch(err => {
        console.log(err)
      })
    },
    getIngresos () {
      let params = {
        idCierre: this.$route.params.id
      }
      if (this.vendedor.Usuario) {
        //params.usuario = this.vendedor.Usuario
      }
      this.axios.get(this.API + 'cierrecaja/ingresos',{
        params: params
      })
      .then(res => {
        this.ingresos = res.data.filter((ing)=> ing.EsTarjeta==0)
        this.ingresosTarjeta = res.data.filter((ing)=> ing.EsTarjeta==1)
      }).catch(err => {
        console.log(err)
      }) 
    },
    getSalidas () {
      let params = {
        idCierre: this.$route.params.id
      }
      if (this.vendedor.Usuario) {
        //params.usuario = this.vendedor.Usuario
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
    getAdelantos() {
        let params = {
          idDocVentas: this.ventas.map(v => v.idDocVenta)
        }
        this.axios.get(this.API + 'cierrecaja/ingresos/adelantos', {
          params: params
        })
        .then(res => {
          this.adelantos = res.data
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
/*th {
  background-color: #f1f1f1;
}
.total {
  background-color: #e4ff95;
}
.total-salida {
  background-color: #ffd4d3;  
}
.anulado td{
  text-decoration: line-through;
}
.credito {
  background: #f9f9f9;
  padding: 10px;
}
.si-print {
  display: none;
}*/
th, td {
  font-size: .9em;
  vertical-align: middle;
}
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
.adelantos {
  background-color: #ffe8c5;
}
.fondo-empresa0 {
    background: #f8fafb;
}
.fondo-empresa1 {
    background: #eff9ff;
}
.fondo-empresa2 {
    background: #d7f3f4;
}
.fondo-empresa3 {
    background: #f3f3dc;
}
.fondo-empresa4 {
    background: #d2f9d5;
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
