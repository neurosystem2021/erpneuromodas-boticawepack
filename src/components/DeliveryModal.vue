<template>
  <div>
    <b-button
      block
      @click="show = true"
      variant="info"
      class="btnAction"
      size="md"
      v-bind:class="{ parpadea: pedidos.length > 0 }"
    >
      <img src="static/logo.png" style="width: 24px" /> Delivery
      Pedi2.pe</b-button
    >
    <b-modal
      id="modalPedidos"
      title="DELIVERY PEDI2.PE"
      size="lg"
      v-model="show"
      header-bg-variant="primary"
      headerTextVariant="light"
      body-bg-variant="light"
      body-text-variant="dark"
      centered
      ok-only
      no-close-on-backdrop
      no-close-on-esc
      ok-title="Cerrar"
    >
      <b-container
        fluid
        style="width: calc(100% - 50px)"
        class="bv-example-row"
        id="conten"
      >
        <div>
          <b-row>
            <b-col md="7">
              <h2>
                Lista de pedidos:
                <b-button variant="outline-info" @click="getPedidos"
                  >Actualizar Lista</b-button
                >
              </h2>
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th scope="col" class="text-center">Código</th>
                    <th scope="col" class="text-center">Estado</th>
                    <th scope="col" class="text-center">Cliente</th>
                    <th scope="col" class="text-center">Motorizado</th>
                    <th scope="col" class="text-center">Acciones</th>
                  </tr>
                </thead>
                <tbody v-for="(pedido, index) in pedidos" :key="index">
                  <tr>
                    <td scope="row" class="text-center font-weight-bold">
                      {{ pedido.IdPedido }}
                    </td>
                    <td v-if="pedido.Estado == 'E'" class="text-center">
                      <b-badge variant="dark">EN ESPERA</b-badge>
                    </td>
                    <td v-else-if="pedido.Estado == 'PE'" class="text-center">
                      <b-badge variant="success">PREPARANDO</b-badge>
                    </td>
                    <td v-else-if="pedido.Estado == 'PU'" class="text-center">
                      <b-badge variant="primary">CAMINO AL CLIENTE</b-badge>
                    </td>
                    <td v-else-if="pedido.Estado == 'UC'" class="text-center">
                      <b-badge variant="primary"
                        >EN LA UBICACION DEL CLIENTE</b-badge
                      >
                    </td>
                    <td v-else class="text-center">
                      <b-badge variant="danger">FINALIZADO</b-badge>
                    </td>
                    <td class="text-center">
                      <b>{{ pedido.Cliente }}</b>
                    </td>
                    <td v-if="pedido.IdMotorizado == null" class="text-center">
                      <b-badge variant="danger">NO ASIGNADO</b-badge>
                    </td>
                    <td v-else class="text-center">
                      <b>{{ pedido.Motorizado }}</b>
                    </td>
                    <td class="text-center">
                      <b-button
                        variant="success"
                        size="sm"
                        v-b-toggle="'' + pedido.IdPedido"
                        ><i class="fas fa-eye"></i
                      ></b-button>
                      <b-button
                        v-show="pedido.Estado != 'E'"
                        :id="'popover-motorizado' + pedido.IdPedido"
                        variant="warning"
                        size="sm"
                        @click="getMotorizados(pedido.IdPedido)"
                        ><i class="fas fa-bicycle"></i
                      ></b-button>
                      <b-popover
                        :show.sync="pedido.showPopover"
                        :target="'popover-motorizado' + pedido.IdPedido"
                        :title="
                          'Asignar motorizado al pedido : ' + pedido.IdPedido
                        "
                      >
                        <table>
                          <thead>
                            <tr>
                              <td class="text-center"><b>Nombre</b></td>
                              <td class="text-center"><b>Estado</b></td>
                              <td class="text-center"><b>Línea</b></td>
                              <td class="text-center"><b>Asignación</b></td>
                            </tr>
                          </thead>
                          <tbody>
                            <tr
                              v-for="(motorizado, index) in motorizados"
                              :key="index"
                              style="
                                border-top: 1px solid black;
                                border-bottom: 1px solid black;
                              "
                            >
                              <td class="text-center font-weight-bold">
                                {{ motorizado.Motorizado }}
                              </td>

                              <td v-if="motorizado.ContadorViajes > 0">
                                <b-badge
                                  v-if="
                                    motorizado.IdMotorizado ==
                                    pedido.IdMotorizado
                                  "
                                  variant="primary"
                                  class="text-center"
                                  >EN ESTE VIAJE</b-badge
                                >
                                <b-badge
                                  v-else
                                  variant="warning"
                                  class="text-center"
                                  >EN OTRO VIAJE</b-badge
                                >
                              </td>

                              <td v-else>
                                <b-badge variant="info" class="text-center"
                                  >DISPONIBLE</b-badge
                                >
                              </td>

                              <td v-if="motorizado.online" class="text-center">
                                <b-badge variant="success">online</b-badge>
                              </td>
                              <td v-else>
                                <b-badge variant="secondary" class="text-center"
                                  >offline</b-badge
                                >
                              </td>

                              <td
                                v-if="motorizado.ContadorViajes > 0"
                                class="text-center"
                              >
                                <label>-------</label>
                              </td>
                              <td v-else>
                                <b-button
                                  v-if="pedido.IdMotorizado == null"
                                  variant="success"
                                  size="sm"
                                  @click="
                                    asignarMotorizado(
                                      pedido.IdPedido,
                                      pedido.IdCliente,
                                      pedido.Cliente,
                                      motorizado.IdMotorizado,
                                      motorizado.Motorizado
                                    )
                                  "
                                  >Asignar</b-button
                                >
                                <b-button
                                  v-else
                                  variant="warning"
                                  size="sm"
                                  @click="
                                    cambiarMotorizado(
                                      pedido.IdPedido,
                                      pedido.IdCliente,
                                      pedido.Cliente,
                                      motorizado.IdMotorizado,
                                      motorizado.Motorizado,
                                      pedido.IdMotorizado,
                                      pedido.Motorizado
                                    )
                                  "
                                  >Cambiar</b-button
                                >
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </b-popover>
                      <b-button
                        size="sm"
                        v-if="pedido.Estado != 'F'"
                        variant="info"
                        @click="mostrarClienteMapa(pedido.IdPedido)"
                        ><i class="fas fa-map-marker-alt"></i
                      ></b-button>
                      <b-button
                        size="sm"
                        v-if="pedido.Estado != 'E'"
                        variant="danger"
                        @click="
                          cancelarPedido(pedido.IdPedido, pedido.IdCliente)
                        "
                        ><i class="fas fa-times fa-lg"></i
                      ></b-button>
                    </td>
                  </tr>
                  <tr>
                    <td colspan="5">
                      <b-collapse :id="'' + pedido.IdPedido" class="mt-1">
                        <b-card>
                          <b-row>
                            <b-col md="11">
                              <label
                                >Cliente:
                                <span class="font-weight-bold">{{
                                  pedido.Cliente
                                }}</span></label
                              >
                              <br />
                              <label
                                >Teléfono del cliente:
                                <span class="font-weight-bold">{{
                                  pedido.TelefonoCliente
                                }}</span></label
                              >
                              <br />
                              <label
                                >Dirección:
                                <span class="font-weight-bold">{{
                                  pedido.Direccion
                                }}</span></label
                              >
                              <br />
                              <label
                                >Referencia:
                                <span class="font-weight-bold">
                                  {{ pedido.Referencia }}</span
                                ></label
                              >
                              <br />
                              <label
                                >Método de Pago:
                                <span class="font-weight-bold">{{
                                  pedido.MetodoPago
                                }}</span></label
                              >
                              <br />
                              <label
                                >Fecha y Hora de solicitud:
                                <span class="font-weight-bold">{{
                                  $moment(pedido.FechaReg).format(
                                    "YYYY-MM-DD HH:mm:ss"
                                  )
                                }}</span></label
                              >
                            </b-col>
                            <b-col md="1">
                              <i
                                v-b-toggle="'' + pedido.IdPedido"
                                class="fas fa-minus"
                                style="
                                  font-size: 26px;
                                  color: gray;
                                  cursor: pointer;
                                "
                              ></i>
                            </b-col>
                          </b-row>
                          <br />
                          <b-alert show variant="info" style="font-weight: bold"
                            >Productos</b-alert
                          >
                          <table class="table table-striped">
                            <thead>
                              <tr>
                                <th scope="col" class="text-center">#</th>
                                <th scope="col" class="text-center">
                                  Producto
                                </th>
                                <th scope="col" class="text-center">
                                  Cantidad
                                </th>
                                <th scope="col" class="text-center">Precio</th>
                                <th scope="col" class="text-center">
                                  Sub. Total
                                </th>
                                <th scope="col" class="text-center">
                                  Indicaciones
                                </th>
                              </tr>
                            </thead>
                            <tbody>
                              <tr
                                v-for="(detalle, index2) in JSON.parse(
                                  pedido.Productos
                                )"
                                :key="index2"
                              >
                                <td
                                  scope="row"
                                  class="text-center"
                                  style="
                                    text-align: center;
                                    vertical-align: middle;
                                  "
                                >
                                  {{ index2 + 1 }}
                                </td>
                                <td
                                  scope="row"
                                  class="text-center font-weight-bold"
                                  style="
                                    width: 25% !important;
                                    font-size: 0.8rem;
                                  "
                                >
                                  {{ detalle.Producto }}
                                </td>
                                <td
                                  scope="row"
                                  class="font-weight-bold"
                                  style="
                                    text-align: center;
                                    vertical-align: middle;
                                  "
                                >
                                  {{ detalle.Cantidad }}
                                </td>
                                <td
                                  scope="row"
                                  class="font-weight-bold"
                                  style="
                                    text-align: center;
                                    vertical-align: middle;
                                  "
                                >
                                  S/ {{ detalle.Precio.toFixed(2) }}
                                </td>
                                <td
                                  scope="row"
                                  class="font-weight-bold"
                                  style="
                                    text-align: center;
                                    vertical-align: middle;
                                  "
                                >
                                  S/
                                  {{
                                    (detalle.Precio * detalle.Cantidad).toFixed(
                                      2
                                    )
                                  }}
                                </td>
                                <td
                                  scope="row"
                                  style="
                                    text-align: center;
                                    vertical-align: middle;
                                    width: 170px;
                                  "
                                  v-if="
                                    detalle.Indicacion == '' ||
                                    detalle.Indicacion == null
                                  "
                                >
                                  <b-badge variant="warning">No tiene</b-badge>
                                </td>
                                <td
                                  scope="row"
                                  style="
                                    text-align: center;
                                    vertical-align: middle;
                                    width: 170px;
                                  "
                                  v-else
                                >
                                  {{ detalle.Indicacion }}
                                </td>
                              </tr>
                            </tbody>
                          </table>
                          <label
                            >Costo de delivery:
                            <span class="font-weight-bold">
                              S/ {{ pedido.PrecioDelivery }}</span
                            ></label
                          >
                          <br />
                          <label
                            >Total en productos:
                            <span class="font-weight-bold">
                              S/ {{ pedido.PrecioProductos }}</span
                            ></label
                          >
                          <br />
                          <div class="text-center">
                            <h2 class="font-weight-bold">
                              Total: S/
                              {{
                                (
                                  parseFloat(pedido.PrecioDelivery) +
                                  parseFloat(pedido.PrecioProductos)
                                ).toFixed(2)
                              }}
                            </h2>
                          </div>
                          <br />
                          <div class="text-center">
                            <b-button
                              v-if="pedido.Estado == 'E'"
                              size="lg"
                              variant="success"
                              @click="
                                aceptarPedido(pedido.IdPedido, pedido.IdCliente)
                              "
                              >Aceptar</b-button
                            >
                            <b-button
                              v-if="pedido.Estado == 'E'"
                              size="lg"
                              variant="danger"
                              @click="rechazarPedido(pedido.IdPedido)"
                              >Rechazar</b-button
                            >
                            <b-button
                              v-if="pedido.Estado != 'E'"
                              size="lg"
                              variant="info"
                              @click="volverRealizarVenta(pedido.IdPedido)"
                              >Volver a Realizar Venta</b-button
                            >
                            <b-form-text v-if="pedido.Estado != 'E'"
                              >Si no realizo la venta del pedido, puede
                              intentarlo nuevamente. Previamente el sistema
                              validará que no exista una venta de este
                              pedido.</b-form-text
                            >
                          </div>
                        </b-card>
                      </b-collapse>
                    </td>
                  </tr>
                </tbody>
              </table>
            </b-col>
            <b-col md="5">
              <b-row>
                <b-col md="12" style="margin-top: 10px">
                  <b-button
                    variant="outline-primary"
                    @click="emitirConsultaMotorizado"
                    >Mostrar Motorizados</b-button
                  >
                  <b-badge variant="success" style="font-size: 18px"
                    >En Linea: {{ motorizadosOnline.length }}
                  </b-badge>
                </b-col>
              </b-row>
              <hr />
              <b-row>
                <b-col md="12">
                  <h4>
                    MAPA TIEMPO REAL:
                    <b-badge v-if="mostrarMotorizados" variant="primary"
                      >MOSTRANDO MOTORIZADOS</b-badge
                    >
                    <b-badge v-if="mostrarCliente" variant="info">{{
                      mostrarCliente
                    }}</b-badge>
                  </h4>
                  <div id="mapid"></div>
                </b-col>
              </b-row>
            </b-col>
          </b-row>
        </div>
      </b-container>
    </b-modal>
  </div>
</template>
<script>
import config from "@/store/config.js";
import L from "leaflet";
import "leaflet/dist/leaflet.css";
delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
  iconRetinaUrl: require("leaflet/dist/images/marker-icon-2x.png"),
  iconUrl: require("leaflet/dist/images/marker-icon.png"),
  shadowUrl: require("leaflet/dist/images/marker-shadow.png")
});
export default {
  mounted() {
    this.escucharCanceladoPedidos();
    this.escucharMotorizadoOnline();
    this.escucharCoordsMotorizado();
    this.escucharEstadoPedido();
    this.getEmpresaDatos();
  },
  beforeDestroy() {
    this.sockets.unsubscribe("lista-on-motorizado");
    this.sockets.unsubscribe("cancelado-pedido");
    this.sockets.unsubscribe("coordenadas-motorizado-nueva");
    this.sockets.unsubscribe("estado-pedido-actualizado-plataforma");
    this.$socket.disconnect();
    if (this.map) {
      this.map.remove();
    }
  },
  data() {
    return {
      API: config.API,
      APINODE: "http://198.211.103.92:5000",
      mymap: null,
      show: false,
      pedidos: [],
      clienteLayer: null,
      empresaLayer: null,
      titulo: "",
      infoPedido: {},
      idPedidoSelect: null,
      pedidoSelect: {
        Telefono: null,
        Direccion: null,
        Referencia: null,
        MetodoPago: null,
        FechaReg: this.$moment().format("YYYY-MM-DD HH:mm:ss"),
        IdMotorizado: null
      },
      motorizados: [],
      motorizadosOnline: [],
      mostrarMotorizados: true,
      mostrarCliente: "",
      iconMoto: L.icon({
        iconUrl: require("../../static/moto.png"),
        popupAnchor: [0, -30],
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        shadowSize: [41, 41]
      }),
      iconCasa: L.icon({
        iconUrl: require("../../static/casa.png"),
        popupAnchor: [0, -30],
        iconSize: [25, 41],
        iconAnchor: [12, 41],
        shadowSize: [41, 41]
      }),
      idEmpresa: null,
      empresa: {},
      pedidoIdMapa: null,
      usuario: sessionStorage.getItem("User"),
      showPopover: false,
      title: "Punto de Venta"
    };
  },
  watch: {
    show(val) {
      if (val == true) {
        this.mostrarMotorizados = true;
        this.mostrarCliente = "";
        this.pedidoMotorizadoId = null;
        this.pedidos = [];
        if (this.mymap) {
          this.mymap.remove();
        }
        setTimeout(() => {
          this.loadMapa();
        }, 500);
      }
    },
    title(val) {
      window.parent.document.title = val;
    }
  },
  sockets: {
    test: function(data) {
      console.log("test triggered", data);
    },
    connect() {
      console.log("connected"),
        this.$socket.emit(
          "conectar",
          {
            tipo: "PLATAFORMA",
            objeto: {
              iddb: this.empresa.IdEmpresa,
              urlplat: this.empresa.FacturaUrl,
              idalmacen: this.empresa.IdAlmacen
            }
          },
          resp => {}
        );
    },
    disconnect() {
      console.log("server disconnected");
    }
  },
  methods: {
    loadMapa() {
      if (this.idEmpresa != null) {
        this.mymap = new L.map("mapid", {
          touchZoom: false,
          attributionControl: false,
          scrollWheelZoom: false,
          doubleClickZoom: false,
          closePopupOnClick: false
        }).setView([this.empresa.Latitud, this.empresa.Longitud], 13);
        L.tileLayer(
          "https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token=pk.eyJ1IjoibWFwYm94IiwiYSI6ImNpejY4NXVycTA2emYycXBndHRqcmZ3N3gifQ.rJcFIG214AriISLbB6B5aw",
          {
            maxZoom: 22,
            id: "mapbox/streets-v11",
            accessToken:
              "pk.eyJ1IjoicmVuem85MjIxIiwiYSI6ImNraDRhZ2NxbzA5eHEycW92d2Y1cGhldWUifQ.1zWCqGLtZfq_VSSWJx23Pg"
          }
        ).addTo(this.mymap);
        this.clienteLayer = L.layerGroup().addTo(this.mymap);
        this.empresaLayer = L.layerGroup().addTo(this.mymap);
        this.mymap.closePopup();
        this.getPedidos();
      } else {
        this.$toasted.show(
          "Servicio de delivery no disponible, porfavor actualice la pagina"
        );
      }
    },
    getEmpresaDatos() {
      let hostUrl = sessionStorage.getItem("HostUrl");
      let idAlmacen = sessionStorage.getItem("IdAlmacen");
      if (hostUrl != null && idAlmacen != null) {
        this.axios
          .get(this.APINODE + "/api/plataforma/empresa", {
            params: {
              HostUrl: hostUrl,
              IdAlmacen: idAlmacen
            }
          })
          .then(res => {
            if (res.data.success == true) {
              this.idEmpresa = res.data.data.IdEmpresa;
              this.empresa = res.data.data;
              this.$socket.connect();
              this.getPedidos();
            }
          })
          .catch(err => {
            this.idEmpresa = null;
            this.empresa = {};
            // this.$swal({
            //   icon: "error",
            //   title: "Sin servicio de delivery",
            //   text: "No hay conexión con el servidor de delivery",
            //   //target: document.getElementById('conten'),
            //   footer: "Por favor cierre sesion e ingrese nuevamente",
            //   timer:"1000"
            // });
          });
      } else {
        this.idEmpresa = null;
        this.empresa = {};
        // this.$swal({
        //   icon: "error",
        //   title: "Error en la autentificación de delivery",
        //   text: "No hay conexión con el servidor de delivery",
        //   target: document.getElementById("conten"),
        //   footer: "Por favor cierre sesion e ingrese nuevamente",
        //   timer: 3000,
        // });
      }
    },
    getPedidos() {
      if (this.idEmpresa != null) {
        this.axios
          .get(this.APINODE + "/api/plataforma/pedidos", {
            params: {
              IdEmpresa: this.idEmpresa
            }
          })
          .then(res => {
            if (res.data.success == true) {
              this.pedidos = res.data.data;
              this.title = "(" + this.pedidos.length + ") Pedidos de Delivery";
              this.pedidos.map(p => {
                p.showPopover = false;
              });
              if (this.pedidoIdMapa != null) {
                let existe = this.pedidos.some(
                  p => p.IdPedido == this.pedidoIdMapa
                );
                if (!existe) {
                  if (this.mymap) {
                    this.emitirConsultaMotorizado();
                  }
                }
              } else {
                if (this.mymap) {
                  this.emitirConsultaMotorizado();
                }
              }
            } else if (res.data.success == false) {
              this.pedidos = [];
              this.title = "Punto de Venta";
              if (this.mymap) {
                this.emitirConsultaMotorizado();
              }
            } else {
              this.pedidos = [];
              if (this.mymap) {
                this.emitirConsultaMotorizado();
              }
            }
          })
          .catch(err => {
            this.pedidos = [];
            console.log(err);
            this.title = "Sin Delivery, Actualice";
          });
      } else {
        this.title = "Sin Delivery, Actualice";
        this.$toasted.show(
          "Servicio de delivery no disponible, porfavor actualice la pagina"
        );
      }
    },
    escucharCanceladoPedidos() {
      this.sockets.subscribe("cancelado-pedido", data => {
        this.$toasted.show("Ha sido cancelado el pedido de " + data.nombre);
        if (this.pedidoIdMapa == data.idPedido) {
          this.emitirConsultaMotorizado();
        }
        this.getPedidos();
      });
    },
    escucharMotorizadoOnline() {
      this.sockets.subscribe("lista-on-motorizado", data => {
        let motorizadosIdLista = data.map(a => a.iddb);
        let motoOnline = this.motorizadosOnline.map(a => a.iddb);
        //En linea
        this.motorizados.map(m => {
          let existe = motorizadosIdLista.some(ml => ml == m.IdMotorizado);
          if (existe) {
            m.online = true;
          } else {
            m.online = false;
          }
        });
        let motorizadosFinal = [...this.motorizados];
        this.$nextTick(() => {
          this.motorizados = motorizadosFinal;
        });
        //Coordenadas
        motoOnline.map(m => {
          let existe = motorizadosIdLista.some(ml => ml == m);
          console.log(existe);
          if (!existe) {
            console.log("entra existe");
            let indexmoto = this.motorizadosOnline.findIndex(a => a.iddb == m);
            console.log(indexmoto);
            if (indexmoto !== -1) {
              console.log("borra");
              if (this.motorizadosOnline[indexmoto].marker != null) {
                this.mymap.removeLayer(
                  this.motorizadosOnline[indexmoto].marker
                );
              }
              this.motorizadosOnline.splice(indexmoto, 1);
            }
          }
        });
      });
    },
    escucharCoordsMotorizado() {
      this.sockets.subscribe("coordenadas-motorizado-nueva", data => {
        if (this.mymap != null) {
          if (this.mostrarMotorizados) {
            let motorizadoIndex = this.motorizadosOnline.findIndex(
              m => m.iddb == data.iddb
            );
            if (motorizadoIndex == -1) {
              data.marker = new L.marker([data.lat, data.lon], {
                icon: this.iconMoto
              })
                .bindTooltip(
                  "<b style='font-size:0.8rem'>" + data.nombres + "</b>"
                )
                .openTooltip();
              data.marker.addTo(this.mymap);
              this.motorizadosOnline.push(data);
            } else {
              if (this.motorizadosOnline[motorizadoIndex].marker != null) {
                this.mymap.removeLayer(
                  this.motorizadosOnline[motorizadoIndex].marker
                );
              }
              this.motorizadosOnline[motorizadoIndex].marker = new L.marker(
                [data.lat, data.lon],
                { icon: this.iconMoto }
              )
                .bindTooltip(
                  "<b style='font-size:0.8rem'>" + data.nombres + "</b>"
                )
                .openTooltip();
              this.motorizadosOnline[motorizadoIndex].marker.addTo(this.mymap);
            }
          } else {
            if (this.pedidoMotorizadoId != null) {
              let motorizadoIndex = this.motorizadosOnline.findIndex(
                m => m.iddb == this.pedidoMotorizadoId
              );
              if (motorizadoIndex == -1) {
                data.marker = new L.marker([data.lat, data.lon], {
                  icon: this.iconMoto
                })
                  .bindTooltip(
                    "<b style='font-size:0.8rem'>" + data.nombres + "</b>"
                  )
                  .openTooltip();
                data.marker.addTo(this.mymap);
                this.motorizadosOnline.push(data);
              } else {
                if (this.motorizadosOnline[motorizadoIndex].marker != null) {
                  this.mymap.removeLayer(
                    this.motorizadosOnline[motorizadoIndex].marker
                  );
                }
                this.motorizadosOnline[motorizadoIndex].marker = new L.marker(
                  [data.lat, data.lon],
                  { icon: this.iconMoto }
                )
                  .bindTooltip(
                    "<b style='font-size:0.8rem'>" + data.nombres + "</b>"
                  )
                  .openTooltip();
                this.motorizadosOnline[motorizadoIndex].marker.addTo(
                  this.mymap
                );
              }
            } else {
              this.motorizadosOnline = [];
            }
          }
        }
      });
    },
    escucharEstadoPedido() {
      this.sockets.subscribe("estado-pedido-actualizado-plataforma", data => {
        let indexPedido = this.pedidos.findIndex(
          p => p.IdPedido == data.idpedido
        );
        if (indexPedido !== -1) {
          this.pedidos[indexPedido].Estado = data.estado;
          if (data.estado == "F") {
            this.$toasted.success(
              "El pedido de " +
                this.pedidos[indexPedido].Cliente +
                " ha finalizado.!!"
            );
            if (this.pedidoIdMapa == data.idpedido) {
              this.emitirConsultaMotorizado();
            }
            this.getPedidos();
          }
        }
      });
    },
    aceptarPedido(idPedido, idCliente) {
      this.axios
        .post(this.APINODE + "/api/plataforma/pedidos/aceptar", {
          IdPedido: idPedido,
          Usuario: this.usuario || "admin"
        })
        .then(res => {
          if (res.data.success == true) {
            this.emitirEstado(idPedido, idCliente, "PE");
            let pedidoIndex = this.pedidos.findIndex(
              p => p.IdPedido === idPedido
            );
            if (pedidoIndex !== -1) {
              this.$nextTick(() => {
                this.pedidos[pedidoIndex].Estado = "PE";
                this.pedidos[pedidoIndex].UsuarioReg = res.data.usuario;
              });
              setTimeout(() => {
                //this.imprimirPedido(idPedido)
                this.emitirConsultaMotorizado();
              }, 400);
              let pedido = this.pedidos.find(p => p.IdPedido == idPedido);
              if (pedido != undefined) {
                this.$emit("onPagarDelivery", pedido);
                this.show = false;
              }
            }
          } else if (res.data.success == false) {
            this.getPedidos();
          }
        })
        .catch(err => {
          console.log(err);
        });
    },
    cancelarPedido(idPedido, idCliente) {
      let pedido = this.pedidos[
        this.pedidos.findIndex(p => p.IdPedido === idPedido)
      ];
      this.$swal({
        target: document.getElementById("conten"),
        title:
          "Se cancelará el pedido de: " + pedido.Cliente + " esta seguro? ",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, cancelar",
        cancelButtonText: "No, atrás"
      }).then(result => {
        if (result.isConfirmed) {
          this.axios
            .post(this.APINODE + "/api/plataforma/pedido/cancelar", {
              IdPedido: idPedido
            })
            .then(res => {
              if (res.data.success == true) {
                this.emitirEstado(
                  idPedido,
                  idCliente,
                  "C",
                  pedido.IdMotorizado
                );
                if (this.pedidoIdMapa == idPedido) {
                  this.emitirConsultaMotorizado();
                }
                this.$toasted.success("Pedido cancelado!");
                this.getPedidos();
              } else if (res.data.success == false) {
                this.getPedidos();
                this.$toasted.success(
                  "El tiene otro estado, se actualizarán los pedidos!"
                );
              }
            })
            .catch(err => {
              console.log(err);
            });
        }
      });
    },
    rechazarPedido(idpedido) {
      let pedido = this.pedidos[
        this.pedidos.findIndex(p => p.IdPedido === idpedido)
      ];
      let r = confirm(
        "¿Esta seguro de rechazar la solicitud de " + pedido.Cliente + "?"
      );
      if (r) {
        this.axios
          .post(this.APINODE + "/api/plataforma/pedido/rechazar", {
            IdPedido: pedido.IdPedido
          })
          .then(res => {
            if (res.data.success == true) {
              this.emitirEstado(
                pedido.IdPedido,
                pedido.IdCliente,
                "C",
                pedido.IdMotorizado
              );
              this.$toasted.success("¡Pedido rechazado!");
              this.getPedidos();
            } else if (res.data.success == false) {
              this.$toasted.success(res.data.msg);
              this.getPedidos();
            }
          })
          .catch(err => {
            console.log(err);
          });
      } else {
      }
    },
    volverRealizarVenta(idpedido) {
      let pedido = this.pedidos.find(p => p.IdPedido == idpedido);
      if (pedido != undefined) {
        this.axios
          .get(this.API + "venta/pedido/existe", {
            params: {
              IdPedido: idpedido
            }
          })
          .then(response => {
            if (response.data.idDocVenta) {
              this.$swal({
                icon: "info",
                title: "Ya existe una venta para este pedido",
                target: document.getElementById("conten"),
                html:
                  "Tipo: <strong>" +
                  response.data.TipoDoc +
                  "</strong> " +
                  "<br> Comprobante: <strong>" +
                  response.data.Serie +
                  "-" +
                  response.data.Numero +
                  "</strong>" +
                  "<br> Fecha y Hora <strong>" +
                  response.data.FechaDoc +
                  "</strong>" +
                  "<br> Realizado por: <strong>" +
                  response.data.UsuarioReg +
                  "</strong>",
                showConfirmButton: true,
                showDenyButton: true,
                confirmButtonText: "Ir a reimpresión de documentos",
                confirmButtonColor: "#3880ff",
                denyButtonText: "Ir a registro de ventas",
                footer:
                  "Puede anular la venta en el registro de ventas e intentarlo nuevamente."
              }).then(result => {
                /* Read more about isConfirmed, isDenied below */
                if (result.isConfirmed) {
                  window.open("/views/ve_reimpresionDocumentos.php", "_blank");
                } else if (result.isDenied) {
                  window.open("/views/ve_regventaform.php", "_blank");
                }
              });
            } else {
              this.$emit("onPagarDelivery", pedido);
              this.show = false;
            }
          })
          .catch(error => {
            this.$toasted.show("Hubo un error, no hay conexion");
          });
      }
    },
    emitirEstado(idPedido, idCliente, estado, idmotorizado = null) {
      this.$socket.emit("estado-pedido-emitir-plataforma", {
        IdCliente: idCliente,
        IdPedido: idPedido,
        Estado: estado,
        IdMotorizado: idmotorizado
      });
    },
    emitirNuevoViaje(IdMotorizado, IdCliente, Cliente) {
      this.$socket.emit("nuevo-viaje-solicitado", {
        idmotorizado: IdMotorizado,
        idpedido: IdCliente,
        cliente: Cliente
      });
    },
    emitirConsultaMotorizado() {
      this.mostrarMotorizados = true;
      this.mostrarCliente = "";
      this.pedidoMotorizadoId = null;
      this.pedidoIdMapa = null;
      if (this.empresaLayer !== undefined) {
        this.empresaLayer.clearLayers();
      }
      if (this.clienteLayer !== undefined) {
        this.clienteLayer.clearLayers();
      }
      this.$socket.emit("motorizado-on-consulta", {
        facturaurl: this.empresa.FacturaUrl,
        idalmacen: this.empresa.IdAlmacen
      });
    },
    mostrarClienteMapa(idPedido) {
      this.mostrarMotorizados = false;
      this.pedidoIdMapa = idPedido;
      this.motorizadosOnline.map(e => {
        if (e.marker != null) {
          this.mymap.removeLayer(e.marker);
        }
      });
      this.motorizadosOnline = [];
      let pedido = this.pedidos[
        this.pedidos.findIndex(p => p.IdPedido === idPedido)
      ];
      if (this.empresaLayer !== undefined) {
        this.empresaLayer.clearLayers();
        L.marker(
          [Number(pedido.EmpresaLatitud), Number(pedido.EmpresaLongitud)],
          { icon: this.iconCasa }
        )
          .addTo(this.empresaLayer)
          .bindPopup("<b>" + pedido.RazonSocial + "</b>")
          .openPopup();
      }
      if (this.clienteLayer !== undefined) {
        this.clienteLayer.clearLayers();
        L.marker([Number(pedido.Latitud), Number(pedido.Longitud)])
          .addTo(this.clienteLayer)
          .bindPopup("<b>" + pedido.Cliente + "</b>")
          .openPopup();
      }
      if (pedido.IdMotorizado != null) {
        this.pedidoMotorizadoId = pedido.IdMotorizado;
      }
      this.mostrarCliente = "MOSTRANDO PEDIDO DE: " + pedido.Cliente;
    },
    getMotorizados(idpedido) {
      this.pedidos.map(p => (p.showPopover = false));
      let indexPedido = this.pedidos.findIndex(p => p.IdPedido == idpedido);
      if (indexPedido !== -1) {
        this.pedidos[indexPedido].showPopover = true;
      }
      if (this.idEmpresa != null) {
        this.axios
          .get(this.APINODE + "/api/motorizados", {
            params: {
              IdEmpresa: this.idEmpresa
            }
          })
          .then(res => {
            if (res.data.success == true) {
              this.motorizados = res.data.motorizados;
              this.$socket.emit("motorizado-on-consulta", {
                facturaurl: this.empresa.FacturaUrl,
                idalmacen: this.empresa.IdAlmacen
              });
            } else if (res.data.success == false) {
              this.motorizados = [];
            }
          })
          .catch(err => {
            console.log(err);
          });
      } else {
        this.$toasted.show(
          "Servicio de delivery no disponible, porfavor actualice la pagina"
        );
      }
    },
    asignarMotorizado(idpedido, idcliente, cliente, idmotorizado, motorizado) {
      this.axios
        .post(this.APINODE + "/api/plataforma/pedido/motorizado/asignar", {
          IdPedido: idpedido,
          IdMotorizado: idmotorizado,
          Motorizado: motorizado
        })
        .then(res => {
          if (res.data.success == 1) {
            this.$toasted.show(res.data.msg);
            this.getMotorizados();
            this.getPedidos();
          } else if (res.data.success == 2) {
            this.$toasted.success("¡Motorizado Asignado!");
            this.emitirNuevoViaje(idmotorizado, idcliente, cliente);
            this.getPedidos();
          } else if (res.data.success == 3) {
            this.getPedidos();
          }
        })
        .catch(err => {
          console.log(err);
        });
    },
    cambiarMotorizado(
      idpedido,
      idcliente,
      cliente,
      idmotorizadonuevo,
      motorizadonuevo,
      idmotorizadoanterior,
      motorizadoanterior
    ) {
      this.axios
        .post(this.APINODE + "/api/plataforma/pedido/motorizado/cambiar", {
          IdPedido: idpedido,
          IdMotorizadoNuevo: idmotorizadonuevo,
          MotorizadoNuevo: motorizadonuevo,
          IdMotorizadoAnterior: idmotorizadoanterior,
          MotorizadoNuevoAnterior: motorizadoanterior
        })
        .then(res => {
          if (res.data.success == 1) {
            this.$toasted.show(res.data.msg);
            this.getMotorizados();
            this.getPedidos();
          } else if (res.data.success == 2) {
            this.$toasted.success("¡Motorizado Cambiado!");
            this.emitirNuevoViaje(idmotorizadonuevo, idcliente, cliente);
            //this.emitirNuevoViaje(idmotorizadoanterior,idcliente,cliente)
            this.getPedidos();
          } else if (res.data.success == 3) {
            this.getPedidos();
          }
        })
        .catch(err => {
          console.log(err);
        });
    },
    enviarPreparar() {
      this.axios
        .post(this.APINODE + "/api/pedido/preparar", {
          IdPedido: this.idPedidoSelect
        })
        .then(res => {
          if (res.data.success == 1) {
            this.axios
              .post(this.API + "preorden/plataforma", {
                InfoPedido: this.infoPedido
              })
              .then(resp => {
                if (resp.data.insertId) {
                  window.open(
                    "/imprimir/index.php?IdMesa=" + resp.data.IdMesa,
                    "_blank"
                  );
                  this.$toasted.success(res.data.msg);
                  this.$refs.modalPedido.hide();
                }
              })
              .catch(error => {});
          } else if (res.data.success == 3) {
            this.$refs.modalPedido.hide();
            this.$toasted.show(res.data.msg);
            this.getPedidos();
          } else if (res.data.success == 2) {
            this.$toasted.show(res.data.msg);
          }
        })
        .catch(err => {
          console.log(err);
        });
    },
    cerrarModal() {
      this.$refs.modalPedidos.hide();
    }
  },
  components: {}
};
</script>

<style>
#mapid {
  width: 100%;
  height: 80vh;
}
.modal-header .close {
  /*display:none;*/
}
.container-print * {
  width: 75mm;
  padding: 3mm 0.5mm 3mm 0.5m;
  box-sizing: border-box;
  font-size: 3.5mm;
  font-family: monospace;
}
.popover {
  max-width: 420px !important;
}
#modalPedidos .modal-dialog {
  max-width: 95% !important;
}
.parpadea {
  animation-name: parpadeo;
  animation-duration: 1s;
  animation-timing-function: linear;
  animation-iteration-count: infinite;
  -webkit-animation-name: parpadeo;
  -webkit-animation-duration: 1s;
  -webkit-animation-timing-function: linear;
  -webkit-animation-iteration-count: infinite;
  color: black !important;
  border-color: black !important;
  font-weight: bold !important;
}
@-moz-keyframes parpadeo {
  0% {
    opacity: 1;
  }
  50% {
    opacity: 0;
  }
  100% {
    opacity: 1;
  }
}
@-webkit-keyframes parpadeo {
  0% {
    opacity: 1;
  }
  50% {
    opacity: 0;
  }
  100% {
    opacity: 1;
  }
}
@keyframes parpadeo {
  0% {
    opacity: 1;
  }
  50% {
    opacity: 0;
  }
  100% {
    opacity: 1;
  }
}
</style>
