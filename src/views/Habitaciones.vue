<template>
  <div>
    
    <b-container class="bv-example-row">
    <div class="piso" v-for="(pi, i) in pisos" :key="i">
    <div v-if="getMostrarPiso(pi)">
      <br>
        <b-alert show class="text-center"><b>{{ pi === 1 ? 'SERVICIOS HÚANUCO' : 'SERVICIOS PUCALLPA' }}</b></b-alert>
      <br>
      <b-button variant="success" size="sm"
        id="nuevoProyecto"
        >
        <!-- @click="showNuevoProyecto=!showNuevoProyecto" -->
      Agregar Proyecto
      </b-button>
      <!-- <b-modal title="Empezar Proyecto" v-model="mdlNuevoProyecto" v-if="mdlNuevoProyecto" hide-footer
      no-close-on-backdrop> -->
      <b-popover  v-if="showNuevoProyecto" target="nuevoProyecto" title="Empezar Proyecto">
      <label>Seleccione un cliente:</label>
      <b-input-group>
        <autocomplete
          required="required"
          :url="API + 'clientes'"
          anchor="ClienteDniRuc"
          label="writer"
          placeholder=""
          :onInput="(val) => newDniRuc = val"
          :on-select="setCliente" 
          :classes="{input: 'form-control'}"
          id="cboCliente"
          >
        </autocomplete>
        <b-input-group-append>
          <clientes-form :ruc="newDniRuc" @onAddCliente="setCliente"></clientes-form>
      
      
      <!-- <clientes-form @onAddCliente="(obj) => { setCliente(obj, index) }"></clientes-form> -->
                                      
      <!-- <clientes-form @onAddCliente="(obj) => { setCliente(obj, index) }"></clientes-form> -->
        </b-input-group-append>
      </b-input-group>
      <br>
    <label>Descripcion:</label>
      <textarea 
        class="form-control"
        placeholder="Descripción"
        cols="12" rows="3"
        style="width: 100%"
        v-model="selectCliente.Descripcion">
      </textarea>
        <br>
      <label>Fecha Inicio:</label>
      <b-input-group>
      <b-form-input v-model="selectCliente.FechaAlquilerInicio"
        type="date"
        placeholder="Fecha Inicio" />
      </b-input-group>
      <br>
      <label>Fecha Fin:</label>
      <b-input-group>
      <b-form-input v-model="selectCliente.FechaAlquilerFin"
      type="date"
      placeholder="Fecha Fin"></b-form-input>
      </b-input-group>
      <br>
      <b-button variant="success" size="sm" @click="nuevoProyecto()">Empezar Proyecto</b-button>	
    </b-popover>				
      <!-- </b-modal> -->
      <!-- <b-btn v-show="hab.EstadoHabitacion !== 3" :id="i + 'popover-alquilar' + index" variant="danger" size="sm">Empezar</b-btn>	
              <b-popover  v-if="showPopover" :target="i + 'popover-alquilar' + index" title="Empezar Proyecto">
                <label>Seleccione un cliente:</label>
                <b-input-group>
                  <autocomplete
                      required="required"
                      :ref="'inputCliente2' + index"
                      :url="API + 'clientes'"
                      anchor="ClienteDniRuc"
                      label="writer"
                      placeholder=""
                      :onInput="(val) => newDniRuc = val"
                      :on-select="setCliente" 
                      :classes="{input: 'form-control'}">
                  </autocomplete>
                  <b-input-group-append>
                    <!--<proveedores-tabla></proveedores-tabla>
                    <clientes-form @onAddCliente="(obj) => { setCliente(obj, index) }"></clientes-form>
                  </b-input-group-append>
                </b-input-group>
                <br>
                <label>Descripcion:</label>
                <textarea 
                  class="form-control"
                  placeholder="Descripción"
                  cols="12" rows="3"
                  style="width: 100%"
                  v-model="selectCliente.Descripcion">
                </textarea>

                <br>
                <label>Fecha Inicio:</label>
                <b-input-group>
                  <b-form-input v-model="selectCliente.FechaAlquilerInicio"
                    type="date"
                    placeholder="Fecha Inicio"></b-form-input>
                </b-input-group>
                <br>
                <label>Fecha Fin:</label>
                <b-input-group>
                  <b-form-input v-model="selectCliente.FechaAlquilerFin"
                    type="date"
                    placeholder="Fecha Fin"></b-form-input>
                </b-input-group>
                <br>

                <b-button variant="success" size="sm" @click="alquilarHab(hab)">Empezar Proyecto</b-button>	
              </b-popover> -->
      
    </div>
        <b-row class="mt-3">
      <b-col class="habitacion" md="3" v-for="(hab, index) in getHabitacionesPiso(pi)" :key="index">
        <b-card :bg-variant="getVariant(hab)" :class="hab.EstadoHabitacion==3?getClaseTexto(hab):'text-center text-light'">
                
            <h5>{{ hab.Producto }}</h5>
            <h5>{{ hab.Cliente }}</h5>
            <h5>S/ {{ hab.granTotalCalculado | moneda }}</h5>
            <i><h6 v-if="hab.IdProforma"><b>Prof.: </b>S/ {{ hab.TotalProf | moneda }}</h6></i>

            <ul class="detalle" v-if="hab.EstadoHabitacion == 2">
              <li><b>{{ hab.Cliente }}</b> </li>
              <li class="text-warning"><b>FECHA: {{ formatFecha(hab.FechaReserva) }}</b></li>
              <li v-if="hab.vuelo"><br>Vuelo: {{ hab.Vuelo }}</li>
            </ul>
            <ul class="detalle" v-if="hab.EstadoHabitacion == 3">
              <li class="text-center text-dark bg-white">
                <b><u>FECHA</u></b><br>
                <b>{{ hab.FechaAlquiler }} - {{ hab.FechaAlquilerFin }}</b>
              </li>
            </ul>
            <div slot="footer">
              <b-btn v-show="hab.EstadoHabitacion !== 1" @click="liberarHab(hab)" variant="success" size="sm">Liberar</b-btn>	
              <!--<b-btn :id="i + 'popover-reservar' + index" variant="warning" size="sm">Reservar</b-btn>-->
              <!--<b-popover v-if="showPopover"  :target="i + 'popover-reservar' + index" title="Reservar Habitación">
                <label>Seleccione un cliente:</label>
                <autocomplete
                    required="required"
                    :ref="'inputCliente1' + index"
                    :url="API + 'clientes'"
                    anchor="ClienteDniRuc"
                    label="writer"
                    placeholder=""
                    :on-select="setCliente" 
                    :classes="{input: 'form-control'}">
                </autocomplete>
                <label for="inputLive">Fecha Reserva:</label>
                <b-form-input v-model="fechaReserva"
                  type="date"></b-form-input>

                <label for="inputLive">Vuelo:</label>
                <b-form-input v-model="vuelo"
                  type="text"></b-form-input>
                <br />
                <b-button variant="success" size="sm" @click="reservarHab(hab)">Reservar Habitación</b-button>	
              </b-popover>-->
              <b-btn v-show="hab.EstadoHabitacion !== 3" :id="i + 'popover-alquilar' + index" variant="danger" size="sm">Empezar</b-btn>	
              <b-popover  v-if="showPopover" :target="i + 'popover-alquilar' + index" title="Empezar Proyecto">
                <label>Seleccione un cliente:</label>
                <b-input-group>
                  <autocomplete
                      required="required"
                      :ref="'inputCliente2' + index"
                      :url="API + 'clientes'"
                      anchor="ClienteDniRuc"
                      label="writer"
                      placeholder=""
                      :onInput="(val) => newDniRuc = val"
                      :on-select="setCliente" 
                      :classes="{input: 'form-control'}">
                  </autocomplete>
                  <b-input-group-append>
                    <!--<proveedores-tabla></proveedores-tabla>-->
                    <clientes-form @onAddCliente="(obj) => { setCliente(obj, index) }"></clientes-form>
                  </b-input-group-append>
                </b-input-group>
                <br>
                <label>Descripcion:</label>
                <textarea 
                  class="form-control"
                  placeholder="Descripción"
                  cols="12" rows="3"
                  style="width: 100%"
                  v-model="selectCliente.Descripcion">
                </textarea>

                <!--<br />
                <label>Lugar Procedencia:</label>
                <b-input-group>
                  <b-form-input v-model="selectCliente.LugarProcedencia"
                    type="text"
                    placeholder="Lugar Procedencia"></b-form-input>
                </b-input-group>
                <br>
                <label>Medio de transporte:</label>
                <b-input-group>
                  <b-form-input v-model="selectCliente.MedioTransporte"
                    type="text"
                    placeholder="Medio de transporte"></b-form-input>
                </b-input-group>
                <br>
                <label>Próximo destino:</label>
                <b-input-group>
                  <b-form-input v-model="selectCliente.ProximoDestino"
                    type="text"
                    placeholder="Próximo destino"></b-form-input>
                </b-input-group>-->
                <br>
                <label>Fecha Inicio:</label>
                <b-input-group>
                  <b-form-input v-model="selectCliente.FechaAlquilerInicio"
                    type="date"
                    placeholder="Fecha Inicio"></b-form-input>
                </b-input-group>
                <br>
                <label>Fecha Fin:</label>
                <b-input-group>
                  <b-form-input v-model="selectCliente.FechaAlquilerFin"
                    type="date"
                    placeholder="Fecha Fin"></b-form-input>
                </b-input-group>
                <br>

                <b-button variant="success" size="sm" @click="alquilarHab(hab)">Empezar Proyecto</b-button>	
              </b-popover>
              <b-button v-if="hab.IdPreOrden && hab.EstadoHabitacion == 3" @click="openCarrito(hab)" variant="primary" size="sm">+</b-button>
            </div>
        </b-card>
      </b-col>
    </b-row>
    </div>
    </b-container>

  <b-form @submit.prevent="guardarProductos" @keydown.enter.prevent="()=>{}"  >
    <b-modal v-model="show" class="modalProyecto" id="carrito" size="lg" no-close-on-backdrop :title="'Cesta de compras - Proyecto ' + selectProducto.Producto " ref="modalCarrito">

      <div id="contentModal" >
          <div class="row">
          <div id="headerCamiones" class="col-md-12">
            <div class="text-left">
              <h4>
                Código : {{ selectProducto.IdProducto }}
              </h4>
            </div>
            <div class="text-right horaFechaImpreso"> Fecha y Hora del impreso: <span id="fechaHoraImp" style="font-weight:bold"></span></div>
            <div class="text-left" style="margin-bottom: 20px;">Fecha del Proyecto: <b> Inicio: {{ selectProducto.FechaAlquiler }} - Fin: {{ selectProducto.FechaAlquilerFin }}</b></div>
            <div class="text-center" style="margin-bottom: 20px;"><b>{{ selectProducto.DniRuc }} - {{ selectProducto.Cliente }}</b></div>
            <div style="margin-bottom: 20px;">
              <label>Descripción de Proyecto:</label>
              <b-row>
                <b-col md="9">
                  <textarea
                  :disabled="!editarDescripcion"
                  class="form-control"
                  placeholder="Descripción"
                  cols="12" rows="3"
                  style="width: 100%"
                  v-model="selectProducto.Descripcion"/>								
                </b-col>
                <b-col md="3">
                  <b-button v-if="!editarDescripcion" style="margin-top:5px" variant="primary" size="sm" @click="editarDescripcionFun()"><i class="fas fa-pencil-alt fa-sm"></i> Editar Descripcion</b-button>
                  <b-button v-if="editarDescripcion" style="margin-top:5px" variant="success" size="sm" @click="guardarDescripcion()"><i class="fas fa-save fa-sm"></i> Guardar Descripcion </b-button>
                </b-col>
                <b-col md="3"  class="mt-2">
                  <b-input-group>
                    <b-form-input
                      v-model="cantidadCodigoBarra"
                      type="number"
                      style="width:80px;"
                    ></b-form-input>
                    <b-input-group-append>
                      <b-btn variant="info" target="_blank" :href="'/views/codigoBarra.php?idProducto=' + selectProducto.IdProducto + '&cantidad=' + cantidadCodigoBarra ">Código de barra</b-btn>
                    </b-input-group-append>
                  </b-input-group>
                </b-col>
              </b-row>
            </div>
            <div class="text-left" v-if="proformaTotal">
              <b style="padding: 4px; background: #ebf0ff;">
                <b>PROFORMA:</b> {{ codProforma }} <br> S/ {{ proformaTotal }}
              </b>
            </div>
            <br>
            
            <b-row>
              <b-col md="8" id="productosProduccion">
                <pre-orden-form-det v-if="show"
                :id-preorden="selectProducto.IdPreOrden"
                :id-producto="selectProducto.IdProducto"
                :esProyecto="true"
                ref="preOrdenProductos"
                 @onUpdateProductos="calcularManoObra"
                ></pre-orden-form-det>

                <b-row>
                  <b-col md="6">
                    <table class="table b-table border table-footer table-sm text-center" >
                    <tbody>
                      <tr><td>Mano de obra bruta</td></tr>
                      <tr><td><h3>{{ totalManoObraBruta | redondear }}</h3></td></tr>
                      <tr><td>Mano de obra - Materiales</td></tr>
                      <tr><td><h3 :class="{'text-danger': totalManoObraMenoMat <= 0}">{{ totalManoObraMenoMat | redondear }}</h3></td></tr>
                    </tbody>
                    </table>
                  </b-col>
                  <b-col md="6">
                    <table class="table b-table border table-footer table-sm text-center" >
                    <tbody>
                      <tr><td>Total de Gastos</td></tr>
                      <tr><td><h3>{{ totalGastos | redondear }}</h3></td></tr>
                      <tr><td>Mano de obra neta + Operativos</td></tr>
                      <tr><td><h3 :class="{'text-danger': totalManoObraNeta <= 0}">{{ totalManoObraNeta | redondear }}</h3></td></tr>
                    </tbody>
                    </table>
                  </b-col>
                </b-row>

              </b-col> 
              <b-col md="4">

                <label><b>Gastos</b></label>
                <input v-model="selectProducto.Gasto"
                    size="sm"
                    type="number"
                    class="form-control"
                    step="0.01"
                    placeholder="Gasto S/."/>
                <b-input-group>
                  <b-form-input v-model="selectProducto.GastoDesc"
                    size="sm"
                    type="text"
                    placeholder="Descripción"></b-form-input>
                  <b-button @click="insertarGasto" variant="primary" size="sm">+</b-button>
                  <b-form-text id="inputLiveHelp">Los gastos se reflejará en Caja y Banco.</b-form-text>
                </b-input-group>
                <br>
                <table class="table b-table table-sm">
                  <thead>
                    <tr>
                    <th>Fecha</th>
                    <th>Concepto</th>
                    <th>Cantidad</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(ade, index) in gastos" :key="index">
                      <td>{{ ade.FechaDoc }}</td>
                      <td>{{ ade.Concepto.split(" - ").pop() }}</td>
                      <td>{{ ade.Tipo ? '-' + ade.Importe : ade.Importe }}</td>
                    </tr>
                    <tr class="bg-info text-white">
                      <td colspan="2">TOTAL</td>
                      <td>{{ totalGastos }}</td>
                    </tr>
                  </tbody>
                </table>

                <hr />

                <label><b>Adelantos</b></label>
                <input v-model="selectProducto.Adelanto"
                    size="sm"
                    type="number"
                    class="form-control"
                    step="0.01"
                    placeholder="Adelanto S/."/>
                <b-input-group>
                  <b-form-input v-model="selectProducto.AdelantoDesc"
                    size="sm"
                    type="text"
                    placeholder="Descripción"></b-form-input>
                  <b-button @click="insertarAdelanto" variant="primary" size="sm">+</b-button>
                  <b-form-text id="inputLiveHelp">Adelanto se reflejará en Caja y Banco.</b-form-text>
                </b-input-group>

                <br />
                <table class="table b-table table-sm">
                  <thead>
                    <tr>
                    <th>Fecha</th>
                    <th>Concepto</th>
                    <th>Cantidad</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(ade, index) in adelantos" :key="index">
                      <td>{{ ade.FechaDoc }}</td>
                      <td>{{ ade.Concepto.split(" - ").pop() }}</td>
                      <td>{{ ade.Tipo ? '-' + ade.Importe : ade.Importe }}</td>
                    </tr>
                    <tr class="bg-info text-white">
                      <td colspan="2">TOTAL</td>
                      <td>{{ totalAdelantos }}</td>
                    </tr>
                  </tbody>
                </table>
              </b-col>
            </b-row>
            
            <div class="text-center">
              <h4>Gran total: {{ totalSumaProductos }}</h4> 
            </div>
          
          </div>
          </div>
      </div>

      <div slot="modal-footer" >
        <div style="left: 1rem;position: absolute;">
          <b-btn  variant="info" type="submit" target="_blank" :href="'/views/V_VentaForm.php?idPreOrden=' + selectProducto.IdPreOrden + '&idCliente=' + selectProducto.IdClienteReserva">
          Pagar Ahora
          </b-btn>
          <b-btn  variant="" @click="imprimir">
            Imprimir
          </b-btn>
          <div style="float: right; margin-left: 10px;">
            <proformas @onSelectProforma="selectProforma" @mounted="mountedProforma"></proformas>
          </div>
        </div>
        <b-btn  variant="success" type="submit">
        Guardar
        </b-btn>
        <b-btn  variant="secondary" @click="show=false">
        Cerrar
        </b-btn>
      </div>
    </b-modal>
  </b-form>
  </div>
</template>
<script>
import { mapActions, mapState } from "vuex";
import config from "@/store/config.js";
import PreOrdenFormDet from "@/components/PreOrdenFormDet";
import ClientesForm from "@/components/ClientesForm";
import Proformas from "@/components/ProformaLista.vue";

export default {
  mounted() {
    this.getHabitaciones();
  },
  data() {
    return {
      mdlNuevoProyecto: false,
      API: config.API,
      show: false,
      showPopover: true,
      showNuevoProyecto: true,
      habitaciones: [],
      selectCliente: {},
      selectProducto: {},
      vuelo: "",
      fechaReserva: this.$moment().format("YYYY-MM-DD"),
      pisos: [1, 2],
      adelantos: [],
      gastos: [],
      montoTotal: 0,
      IdProforma: null,
      proformaTotal: null,
      codProforma: null,
      profTotHab: 0,
      // proformaTotalCard: null,
      // habitacionesPiso: []

      //nuevas funciones
      totalManoObraBruta: 0,
      totalManoObraMenoMat: 0,
      totalManoObraNeta: 0,
      totalSumaProductos: 0,
      totalGastos: 0,
      totalAdelantos: 0,
      editarDescripcion: false,
      newDniRuc: "",
      cantidadCodigoBarra: 1
    };
  },
  computed: {
    ...mapState({
      productos: state => state.cart.productos
    })
    /* 		totalAdelantos() {
    let sumatoria = 0
    this.adelantos.map(ad => {
      if (ad.Tipo) {
        sumatoria -= parseFloat(ad.Importe)
      } else {
        sumatoria += parseFloat(ad.Importe)
      }
    })

    return sumatoria;
  },
  totalGastos() {
    let sumatoria = 0
    this.gastos.map(ad => {
      sumatoria += parseFloat(ad.Importe)
    })

    return sumatoria;
  }, */
  },
  methods: {
    abrirMdlNuevoProyecto() {
      this.mdlNuevoProyecto = true;
    },
    mountedProforma(modal) {
      document.body.appendChild(modal);
    },
    selectProforma(proforma) {
      this.show = true;
      this.$nextTick(() => {
        proforma.productos.map(p => {
          this.$refs.preOrdenProductos.productosACargar.push(p);
        });

        this.IdProforma = proforma.IdProforma;
      });
    },
    cargarAdelantos() {
      this.axios
        .get(this.API + "preorden/cajaybanco", {
          params: {
            IdPreOrden: this.selectProducto.IdPreOrden,
            TipoCajaBanco: 0
          }
        })
        .then(res => {
          this.adelantos = res.data;
          this.calculoGastosAdelantos();
        });
    },
    cargarGastos() {
      this.axios
        .get(this.API + "preorden/cajaybanco", {
          params: {
            IdPreOrden: this.selectProducto.IdPreOrden,
            TipoCajaBanco: 1
          }
        })
        .then(res => {
          this.gastos = res.data;
          this.calculoGastosAdelantos();
        });
    },
    insertarAdelanto() {
      let usuario = sessionStorage.getItem("User");

      if (this.selectProducto.Adelanto <= 0) {
        return;
      }

      let r = confirm("¿Desea Insertar un adelanto?");

      if (r) {
        this.axios
          .post(this.API + "preorden/cajaybanco", {
            producto: this.selectProducto,
            concepto:
              "Adelanto Proyecto " +
              this.selectProducto.Producto +
              " - " +
              this.selectProducto.AdelantoDesc,
            IdPreOrden: this.selectProducto.IdPreOrden,
            IdHabitacion: this.selectProducto.IdProducto,
            Adelanto: this.selectProducto.Adelanto,
            Usuario: usuario
          })
          .then(res => {
            // this.adelantos.push(res.data)
            this.cargarAdelantos();
            this.selectProducto.Adelanto = "";
            this.selectProducto.Producto = "";
            this.selectProducto.AdelantoDesc = "";
          });
      }
    },

    insertarGasto() {
      let usuario = sessionStorage.getItem("User");

      if (this.selectProducto.Gasto <= 0) {
        return;
      }

      let r = confirm("¿Desea Insertar un gasto?");

      if (r) {
        this.axios
          .post(this.API + "preorden/cajaybanco/gasto", {
            producto: this.selectProducto,
            concepto:
              "Gasto Proyecto " +
              this.selectProducto.Producto +
              " - " +
              this.selectProducto.GastoDesc,
            IdPreOrden: this.selectProducto.IdPreOrden,
            IdHabitacion: this.selectProducto.IdProducto,
            Gasto: this.selectProducto.Gasto,
            Usuario: usuario
          })
          .then(res => {
            // this.adelantos.push(res.data)
            this.cargarGastos();
            this.selectProducto.Gasto = "";
            this.selectProducto.Producto = "";
            this.selectProducto.GastoDesc = "";
          });
      }
    },

    getMostrarPiso(piso) {
      let id = this.$route.query.id ? this.$route.query.id : 1;
      return piso == id;
    },
    getHabitacionesPiso(piso) {
      return this.habitaciones.filter(hab => {
        return hab.Piso === piso;
      });
    },
    guardarProductos() {
      this.axios
        .post(this.API + "preorden/detalle", {
          IdPreOrden: this.selectProducto.IdPreOrden,
          IdHabitacion: this.selectProducto.IdProducto,
          productos: this.$refs.preOrdenProductos.productos,
          IdProforma: this.IdProforma
        })
        .then(res => {
          this.$toasted.success("Guardado correctamente");
          this.getHabitaciones();
          this.show = false;
        });
    },
    openCarrito(hab) {
      console.log(hab);
      this.proformaTotal = null;
      this.selectProducto = hab;
      this.$refs.modalCarrito.show();
      this.cargarAdelantos();
      this.cargarGastos();
      this.cargarProformaTotal(this.selectProducto.IdProforma);
    },
    cargarProformaTotal(idProforma) {
      if (idProforma) {
        this.axios
          .get(this.API + "preorden/proforma", {
            params: { idProforma: idProforma }
          })
          .then(res => {
            this.proformaTotal = res.data.Total;
            this.codProforma = res.data.IdProforma;
            this.codProforma = res.data.Anio + " - " + res.data.Numero;
          });
      }
    },
    getHabitaciones() {
      let id = this.$route.query.id ? this.$route.query.id : 1;
      this.axios
        .get(
          this.API +
            "habitaciones?sortBy=Gen_Producto.IdProducto&sortDesc=DESC&pisoAlm=" +
            id
        )
        .then(res => {
          this.habitaciones = res.data;
          /* this.pisos.forEach(piso => {
        this.habitacionesPiso[piso] = this.getHabitacionesPiso(piso)
      }) */
        });
    },
    getClaseTexto(hab) {
      if (hab.FechaAlquilerFin < this.$moment().format("YYYY-MM-DD")) {
        return "text-center text-light";
      }
      return "text-center text-dark";
    },
    getVariant(hab) {
      if (hab.EstadoHabitacion == 3) {
        if (hab.FechaAlquilerFin < this.$moment().format("YYYY-MM-DD"))
          return "danger";
        return "warning";
      }
      if (hab.EstadoHabitacion == 2) return "warning";
      //if (hab.EstadoHabitacion == 3) return 'danger'
      if (hab.EstadoHabitacion == 1 && hab.EstaSucio == 1) return "secondary";
      return "success";
    },
    reservarHab(hab) {
      if (!this.selectCliente.Cliente) {
        this.$toasted.error("Seleccione un cliente.");
      }

      this.axios
        .post(this.API + "habitaciones/reservar", {
          IdProducto: hab.IdProducto,
          IdCliente: this.selectCliente.IdCliente,
          FechaReserva: this.fechaReserva,
          Vuelo: this.vuelo,
          pisoAlm: this.$route.query.id
        })
        .then(res => {
          this.getHabitaciones();
          this.limpiarPopover();
          this.limpiarDespues();
          this.$toasted.success(
            "La Habitación: " + hab.Producto + " ha sido reservada"
          );
          this.showPopover = false;
          this.$nextTick(() => {
            this.showPopover = true;
          });
        });
    },
    liberarHab(hab) {
      let r = confirm(
        "¿Está seguro que desea liberar el proyecto? Asegurese de haber realizado la venta"
      );

      if (r) {
        this.axios
          .post(this.API + "habitaciones/liberar", {
            IdProducto: hab.IdProducto
          })
          .then(res => {
            this.getHabitaciones();
            this.limpiarPopover();
            this.$toasted.success(
              "El proyecto: " + hab.Producto + " ha sido liberado"
            );
            this.showPopover = false;
            this.$nextTick(() => {
              this.showPopover = true;
            });
          });
      }
    },
    async nuevoProyecto() {
      let data = {
        // IdProducto: hab.IdProducto,
        IdCliente: this.selectCliente.IdCliente,
        FechaReserva: this.fechaReserva,
        Vuelo: this.vuelo,
        Descripcion: this.selectCliente.Descripcion,
        FechaAlquilerInicio: this.selectCliente.FechaAlquilerInicio,
        FechaAlquilerFin: this.selectCliente.FechaAlquilerFin,
        pisoAlm: this.$route.query.id
      };
      await this.axios
        .post(this.API + "producto/registro_proyecto", data)
        .then(res => {
          this.getHabitaciones();
          this.limpiarPopover();
          this.limpiarDespues();
          this.$toasted.success("La Habitación: ha sido reservada");
          this.showPopover = false;
          this.selectCliente = {};
          this.mdlNuevoProyecto = false;
        });

      this.getHabitaciones();
    },
    alquilarHab(hab) {
      if (!this.selectCliente.Cliente) {
        this.$toasted.error("Seleccione un cliente.");
      }

      let r = confirm("¿Está seguro que desea empezar el proyecto?");

      if (r) {
        this.axios
          .post(this.API + "habitaciones/alquilar", {
            IdProducto: hab.IdProducto,
            IdCliente: this.selectCliente.IdCliente,
            cliente: this.selectCliente,
            usuario: sessionStorage.getItem("User")
          })
          .then(res => {
            this.getHabitaciones();
            this.limpiarPopover();
            this.limpiarDespues();
            this.$toasted.success(
              "El proyecto: " + hab.Producto + " ha sido iniciado."
            );
            this.showPopover = false;
            this.$nextTick(() => {
              this.showPopover = true;
            });
          });
      }
    },
    setCliente(obj, index) {
      if (index >= 0) {
        this.$refs["inputCliente2" + index][0].setValue(obj.Cliente);
        this.newDniRuc = "";
      }
      this.selectCliente = obj;
    },
    /*focusAutocomplete (ref) {
    this.$refs[ref][0].handleDoubleClick()
  },*/
    limpiarPopover() {
      //this.cliente = {}
      //this.$refs.inputCliente.setValue('')
    },
    limpiarDespues() {
      this.selectCliente = {};
      this.selectProducto = {};
    },
    formatFecha(fecha) {
      return this.$moment(fecha, "YYYY-MM-DD hh:mm:ss").format("YYYY-MM-DD");
    },
    imprimir() {
      document.getElementById("fechaHoraImp").innerHTML = this.$moment().format(
        "YYYY-MM-DD HH:mm:ss"
      );
      var elem = "contentModal";
      var cssHead =
        '<head><link rel="stylesheet" type="text/css" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css"></head>';

      var mywindow = window.open("", "PRINT", "height=600,width=1000");
      mywindow.document.write(cssHead);
      mywindow.document.write(
        '<h5 style="text-align:center">' +
          document.querySelector("#carrito .modal-title").textContent +
          "</h5> <hr>"
      );
      mywindow.document.write(document.getElementById(elem).innerHTML);

      for (
        var i = 0;
        i < mywindow.document.querySelectorAll("input, button, a").length;
        ++i
      ) {
        mywindow.document.querySelectorAll("input, button, a")[
          i
        ].style.display =
          "none";
      }

      mywindow.document.getElementById("headerCamiones").className =
        "col-md-12";
      mywindow.document.getElementById("productosProduccion").className =
        "col-md-8";

      mywindow.document.close(); // necessary for IE >= 10
      mywindow.focus(); // necessary for IE >= 10*/

      mywindow.onload = function() {
        setTimeout(function() {
          mywindow.print();
          // mywindow.close();
        }, 500);
      };

      return true;
    },
    sumProductos() {
      let total = 0;
      if (this.$refs.preOrdenProductos) {
        total = this.$refs.preOrdenProductos.sumProductos - this.totalAdelantos;
      }
      return Math.abs(total).toFixed(2);
    },
    /* 		totManoObraBruta() {
    let total = 0
    if (this.$refs.preOrdenProductos) {
      total = this.$refs.preOrdenProductos.manoDeObraBruta
    }
    return total?total.toFixed(2):0
  }, */
    /* 		totManoObraMenoMat() {
    let total = 0
    if (this.$refs.preOrdenProductos) {
      total = this.$refs.preOrdenProductos.manoDeObraMenosMat
    }
    return total?total.toFixed(2):0
  }, */
    /* 		totManoObraNeta() {
    let total = 0
    if (this.$refs.preOrdenProductos) {
      total = this.$refs.preOrdenProductos.manoDeObraMenosMat - this.totalGastos
    }
    return total?total.toFixed(2):0
  }, */
    calcularManoObra(e) {
      let pManoObra = e.find(p => p.CodigoBarra === "MANODEOBRA");
      let precio = pManoObra ? pManoObra.Precio : 0;
      let cantidad = pManoObra
        ? pManoObra.Cantidad == undefined
          ? 0
          : pManoObra.Cantidad
        : 0;
      this.totalManoObraBruta = Number(precio * cantidad);
      this.totalManoObraMenoMat = Number(precio * cantidad);
      e.map(preorden => {
        if (preorden.EsManoDeObra == 1) {
          this.totalManoObraMenoMat =
            this.totalManoObraMenoMat - preorden.Precio * preorden.Cantidad;
        }
      });
      this.calculoGastosAdelantos();
    },
    calculoGastosAdelantos() {
      let sumatoriaGas = 0;
      this.gastos.map(ad => {
        sumatoriaGas += parseFloat(ad.Importe);
      });
      this.totalGastos = sumatoriaGas;
      this.totalManoObraNeta = this.totalManoObraMenoMat - this.totalGastos;

      let sumatoriaAde = 0;
      this.adelantos.map(ad => {
        if (ad.Tipo) {
          sumatoriaAde -= parseFloat(ad.Importe);
        } else {
          sumatoriaAde += parseFloat(ad.Importe);
        }
      });
      this.totalAdelantos = sumatoriaAde;

      if (this.$refs.preOrdenProductos) {
        this.totalSumaProductos = Math.abs(
          this.$refs.preOrdenProductos.sumProductos - this.totalAdelantos
        ).toFixed(2);
        if (this.$refs.preOrdenProductos.productos) {
          this.$refs.preOrdenProductos.productos.map(pro => {
            if (pro.CodigoBarra == "GASTOS") {
              this.totalManoObraNeta += pro.Cantidad * pro.Precio || 0;
            }
          });
        }
      }
    },
    editarDescripcionFun() {
      this.editarDescripcion = !this.editarDescripcion;
    },
    guardarDescripcion() {
      this.axios
        .post(this.API + "proyecto/descripcion", {
          IdPreOrden: this.selectProducto.IdPreOrden,
          Descripcion: this.selectProducto.Descripcion
        })
        .then(res => {
          this.editarDescripcion = false;
          this.$toasted.info("Descripcion guardada correctamente");
        })
        .catch(error => {
          this.$toasted.show("No se guardo la descripcion, hubo un error");
        });
    }
  },
  components: {
    PreOrdenFormDet,
    ClientesForm,
    Proformas
  }
};
</script>
<style>
.modalProyecto .modal-dialog {
  width: 90% !important; /* Cambia el 80% al ancho que necesitas */
  max-width: 90% !important;
}
@media print {
  body .printable,
  body .modal {
    display: block;
  }
  #pisos {
    visibility: hidden;
  }
  .modal {
    /**Remove scrollbar for printing.**/
    overflow: visible !important;
    position: absolute;
  }
  .modal-dialog {
    visibility: visible !important;
    /**Remove scrollbar for printing.**/
    overflow: visible !important;
  }
  .modal-footer {
    visibility: hidden;
  }
  .horaFechaImpreso {
    display: block;
  }
}
</style>

<style scoped>
h5 {
  font-size: 1.2rem;
}
h6 {
  font-size: 1rem;
}
.card-footer {
  padding-left: 0;
  padding-right: 0;
}
.habitacion {
  margin-bottom: 10px;
}
.detalle {
  list-style: none;
  padding: 0;
  margin-bottom: 0px;
  text-align: left;
  font-size: 1rem;
}
.horaFechaImpreso {
  display: none;
}
</style>
