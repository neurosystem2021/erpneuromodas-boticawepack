<template>
<b-container class="nst-screen nst-screen--remisiontransportista">
  <b-card class=" vh-100">
  <section class="nst-panel nst-panel--filters"><b-row>
    <b-col cols="12" md="6" lg="6" class="px-5">
      <b-row>
        <b-col cols="12">
          <b-form-group  class="mt-0">
              <label class="ocultarMovil">DOC. REMITENTE</label>
               <b-input-group>
                  <input type="text"  class="form-control" v-model="remitente.docremitente">
                  <b-button @click="buscarReminte" variant="info" ><i class="fa fa-search" aria-hidden="true"></i></b-button>
                </b-input-group>                                  
          </b-form-group>
        </b-col>
        <b-col cols="12">
          <b-form-group  class="mt-0">
              <label class="ocultarMovil">RAZÓN SOCIAL</label>
              <input type="text" v-model="remitente.razonSocial" class="form-control">
          </b-form-group>
        </b-col>
      </b-row>
      <hr>
      <b-row>
                <b-col cols="12">
                  <b-form-group  class="mt-0">
                      <label class="ocultarMovil">TRANSPORTISTA</label>
                      <autocomplete
                          required="required"
                          :ref="`inputProveedor`"
                          :url="API + 'proveedores'"
                          anchor="ProveedorRuc"
                          label="writer"
                          placeholder=""
                          :onInput="(val) => newDniRuc = val"
                          :on-select="setProveedor" 
                          :onFocus="focusAutocomplete.bind(null, `inputProveedor`)"
                          :classes="{input: 'form-control'}">
                      </autocomplete>
                  </b-form-group>
                </b-col>
                <b-col cols="12">
                  <b-form-group  class="mt-0">
                      <label class="ocultarMovil" >DIRECCIÓN FISCAL</label>
                      <textarea v-model="proveedor.Direccion" class="form-control"  style="field-sizing: content;"/>
                  </b-form-group>
                </b-col>
      </b-row>
      <hr>
      <b-row>
                <b-col cols="12" md="6" lg="6"> 
                  <b-form-group  class="">
                      <label class="ocultarMovil">DNI CHOFER</label>
                      <b-input-group>
                        <input type="text" v-model="chofer.documento" class="form-control">
                        <b-button @click="buscarChofer" variant="info" ><i class="fa fa-search" aria-hidden="true"></i></b-button>
                      </b-input-group>
               
                  </b-form-group>
                </b-col>
                <b-col cols="12" md="6" lg="6"> 
                  <b-form-group  class="">
                      <label class="ocultarMovil">LICENCIA CHOFER</label>
                      <input type="text" v-model="chofer.licencia"  class="form-control">
                  </b-form-group>
                </b-col>
                <b-col cols="12">
                  <b-form-group  class="">
                      <label class="ocultarMovil">DATOS DEL CHOFER</label>
                      <input type="text" v-model="chofer.razonSocial" class="form-control">
                  </b-form-group>
                </b-col>
      </b-row>
      <hr>
      <b-row>
                <b-col cols="12" md="6" lg="6"> 
                  <b-form-group  class="mt-0">
                      <label class="ocultarMovil">PLACA DEL VEHÍCULO</label>
                      <input type="text" v-model="chofer.placa" class="form-control">
                  </b-form-group>
                </b-col>
                <b-col cols="12" md="6" lg="6"> 
                  <b-form-group  class="mt-0">
                      <label class="ocultarMovil">MARCA</label>
                      <input type="text"  v-model="chofer.marca" class="form-control">
                  </b-form-group>
                </b-col>
      </b-row>
    </b-col>
       <b-col cols="12" md="6" lg="6" class="px-5">
          <b-row>
            <b-col cols="12">
              <b-form-group  class="mt-0">
                  <label class="ocultarMovil">CLIENTE</label>
                  <autocomplete
                      required="required"
                      :ref="`inputCliente`"
                      :url="API + 'clientes'"
                      anchor="ClienteDniRuc"
                      label="writer"
                      placeholder=""
                      :onInput="(val) => newDniRuc = val"
                      :on-select="setCliente" 
                      :onFocus="focusAutocomplete.bind(null, `inputCliente`)"
                      :classes="{input: 'form-control'}">
                  </autocomplete>
              </b-form-group>
            </b-col>
          </b-row>
          <hr>
          <b-row>
            <!-- <b-col cols="12"> 
                <b-form-group  class="">
                  <label class="ocultarMovil">UBIG ORIGEN</label>
                  <input type="text"  class="form-control" v-model="ubigeoorigen">
                </b-form-group>
            </b-col> -->
            <b-col cols="12" md="4" lg="4"> 
                <b-form-group  class="">
                  <label class="ocultarMovil">UBIG ORIGEN</label>
                  <input type="text"  class="form-control" v-model="ubigeoorigen">
                </b-form-group>
            </b-col>
           <b-col cols="12" md="8" lg="8"> 
              <b-form-group  class="">
                <label class="ocultarMovil" >DIRECCIÓN ORIGEN</label>
                <textarea  class="form-control" v-model="direccionorigen"  style="field-sizing: content;"/>
              </b-form-group>
            </b-col>
            <b-col cols="12" md="4" lg="4"> 
                <b-form-group  class="">
                  <label class="ocultarMovil">UBIG DESTINO</label>
                  <input type="text"  class="form-control" v-model="ubigeodestino">
                </b-form-group>
            </b-col>
            <b-col cols="12" md="8" lg="8"> 
                <b-form-group  class="">
                  <label class="ocultarMovil" >DIRECCIÓN DESTINO</label>
                  <textarea  class="form-control" v-model="direcciondestino"  style="field-sizing: content;"/>
                </b-form-group>
            </b-col>
            <b-col cols="12" md="6" lg="6">
              <b-form-group  class="">
                  <label class="ocultarMovil">FECHA</label>
                  <input type="date" v-model="fecha" class="form-control">
              </b-form-group>
            </b-col>
            <b-col cols="12" md="6" lg="6">
              <b-form-group  class="">
                  <label class="ocultarMovil">NRO. GUÍA REMISIÓN</label>
                  <input type="text" v-model="guiaremision" class="form-control">
              </b-form-group>
            </b-col>
             <b-col cols="12" md="6" lg="6">
              <b-form-group  class="">
                  <label class="ocultarMovil">MODALIDAD</label>
                  <b-form-select v-model="modalidad" :options="modalidades"></b-form-select>
              </b-form-group>
            </b-col>
             <b-col cols="12" md="6" lg="6">
              <b-form-group  class="">
                  <label class="ocultarMovil">MOTIVO</label>
                  <b-form-select v-model="motivo" :options="motivos"></b-form-select>
              </b-form-group>
            </b-col>
          </b-row>
      </b-col>
     
      <b-col cols="12" class="px-5">
              <div class="dvTitle ">
                  <span class="spnTitle "> DETALLE</span>
              </div>
              <b-row>
                  <b-col cols="12" md="4" lg="4"> 
                       <b-form-group class="">
                          <label>Doc. Referencia:</label>
                          <autocomplete
                              required="required"
                              ref="inputDocRef"
                              :url="API + 'ventas/lista/'+puntoVenta.IdDocVentaPuntoVenta"
                              anchor="NroComprobante"
                              initValue=""
                              label="writer"
                              placeholder=""
                              :on-select="setDocRef"
                              :onFocus="focusAutocomplete.bind(null, 'inputDocRef')"
                              :classes="{input: 'form-control'}">
                          </autocomplete>
                      </b-form-group>
                  </b-col>
                  <b-col cols="12">
                    <b-table :items="productos" :fields="fields" responsive="" show-empty empty-text="No hay registros para los filtros seleccionados." empty-filtered-text="No se encontraron coincidencias." >
                      <template slot="Cantidad" slot-scope="data">
                        {{ parseFloat((data.item.cantidad) * (data.item.cantidadPres)).toFixed(2) }}
                      </template>
                    </b-table>
                  </b-col>
                  <b-col style="text-align: center;" class="mt-5">
                    <b-button variant="success" @click="generarGuiaRemision">GENERAR GUÍA REMISIÓN</b-button>
                  </b-col>
              </b-row>
            </b-col>
          
          </b-row></section>
    </b-card>
</b-container>
</template>

<script>
import config from "@/store/config.js";
export default {
  data() {
    return {
      docremitente: "",
      API: config.API,
      CodSunatModifica: "",
      NroComprobanteModifica: "",
      productos: [],
      almacen: {},
      puntoVenta: {},
      cliente: {},
      proveedor: {},
      chofer: {
        documento: "",
        razonSocial: "",
        licencia: "",
        placa: "",
        marca: ""
      },
      remitente: {
        docremitente: "",
        razonSocial: ""
      },
      fields: [
        { key: "CodigoBarra", label: "Cód." },
        { key: "Producto" },
        { key: "Cantidad" }
      ],
      ubigeoorigen: "",
      ubigeodestino: "",
      direccionorigen: "",
      direcciondestino: "",
      fecha: "",
      guiaremision: "",
      motivo: "",
      modalidad: "",
      motivos: [
        { value: "01", text: "Venta" },
        { value: "02", text: "Compra" },
        { value: "04", text: "Traslado entre establecimientos" },
        { value: "08", text: "Importación" },
        { value: "13", text: "Otros" }
      ],
      modalidades: [
        { value: "01", text: "Público" },
        { value: "02", text: "Privado" }
      ]
    };
  },
  mounted() {
    this.getVendedor();
    this.fecha = this.$moment().format("YYYY-MM-DD");
  },
  methods: {
    async buscarDNIRUC(document) {
      let datos = {};
      let tipo = "";
      switch (document.length) {
        case 8:
          tipo = "DNI";
          break;

        case 11:
          tipo = "RUC";
          break;

        default:
          tipo = false;
          break;
      }

      if (!tipo) {
        return;
      }
      await this.axios
        .get(this.API + "consultarRUC", {
          params: {
            type: tipo,
            numero: document
          }
        })
        .then(res => {
          datos = res.data;
        });
      return datos;
    },
    async buscarReminte() {
      if (
        this.remitente.docremitente.length != 8 &&
        this.remitente.docremitente.length != 11
      ) {
        return;
      }
      let val = await this.buscarDNIRUC(this.remitente.docremitente);

      if (val.razonSocial) {
        this.remitente.razonSocial = val.razonSocial;
      } else {
        this.remitente.razonSocial =
          val.apellidoMaterno + " " + val.apellidoPaterno + ", " + val.nombres;
      }
    },
    async buscarChofer() {
      if (
        this.chofer.documento.length != 8 &&
        this.chofer.documento.length != 11
      ) {
        return;
      }
      let val = await this.buscarDNIRUC(this.chofer.documento);

      if (val.razonSocial) {
        this.chofer.razonSocial = val.razonSocial;
      } else {
        this.chofer.razonSocial =
          val.apellidoMaterno + " " + val.apellidoPaterno + ", " + val.nombres;
      }
    },
    focusAutocomplete(ref) {
      this.$refs[ref].handleDoubleClick();
    },
    async setCliente(obj, dni = false) {
      this.cliente = obj;
    },
    async setProveedor(obj, dni = false) {
      console.log(obj);
      this.proveedor = obj;
    },
    getVendedor() {
      let usuario = sessionStorage.getItem("User");
      this.axios
        .get(this.API + "usuarios/" + usuario)
        .then(response => {
          //   this.setVendedor(response.data);
          this.loadAlmacen(response.data.IdAlmacen);
          this.loadPuntoVenta(response.data.IdDocVentaPuntoVenta);
        })
        .catch(error => {
          console.log(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
        });
    },
    loadAlmacen(id) {
      this.axios.get(this.API + "almacenes/id/" + id).then(res => {
        this.setAlmacen(res.data);
      });
    },
    setAlmacen(obj) {
      this.almacen = obj;
    },
    loadPuntoVenta(id) {
      this.axios.get(this.API + "puntoventa/id/" + id).then(res => {
        this.setPuntoVenta(res.data);
      });
    },
    setPuntoVenta(obj) {
      this.puntoVenta = obj;
    },

    setDocRef(obj) {
      console.log(obj);
      this.axios
        .get(this.API + "venta/detalle/comprobante", {
          params: {
            NroComprobante: obj.NroComprobante,
            idDocVenta: obj.IdDocVenta,
            IdDocVentaPuntoVenta: this.puntoVenta.IdDocVentaPuntoVenta
          }
        })
        .then(response => {
          this.axios
            .get(this.API + "cliente", {
              params: {
                idCliente: response.data[0].IdCliente
              }
            })
            .then(response => {
              if (response.data) {
                this.$refs.inputCliente.setValue(response.data.Cliente);
                this.cliente = response.data;
              } else {
                this.$toasted.show("No se encontro al cliente");
              }
            })
            .catch(error => {
              console.log(error);
            });
          this.productos = [];
          response.data.map(item => {
            item.estadoPxP = false;
            let preciosProd = [];
            item.precioCont = item.PrecioContado;
            preciosProd = JSON.parse(item.PreciosPorProducto || "[]");
            let fPP = preciosProd.filter(p => p.cantidad === 0);

            if (!item.PreciosPorProducto || fPP.length === 4) {
              item.estadoPxP = true;
            }
            this.productos.push(item);
          });
        });
    },
    async generarGuiaRemision() {
      // ===============================
      // 1️⃣ Validaciones básicas
      // ===============================

      if (!this.remitente.docremitente) {
        this.$toasted.show("Documento del remitente requerido.");
        return;
      }

      if (!this.remitente.razonSocial) {
        this.$toasted.show("Razón social del remitente requerida.");
        return;
      }

      if (!this.cliente || !this.cliente.Cliente) {
        this.$toasted.show("Cliente / destinatario requerido.");
        return;
      }

      if (!this.ubigeoorigen) {
        this.$toasted.show("Ubigeo de origen requerido.");
        return;
      }

      if (!this.ubigeodestino) {
        this.$toasted.show("Ubigeo de destino requerido.");
        return;
      }

      if (!this.direccionorigen) {
        this.$toasted.show("Dirección de origen requerida.");
        return;
      }

      if (!this.direcciondestino) {
        this.$toasted.show("Dirección de destino requerida.");
        return;
      }

      if (!this.guiaremision) {
        this.$toasted.show("Número de guía de remisión requerido.");
        return;
      }

      if (!this.motivo) {
        this.$toasted.show("Motivo de traslado requerido.");
        return;
      }

      if (!this.modalidad) {
        this.$toasted.show("Modalidad de transporte requerida.");
        return;
      }

      if (!this.productos || this.productos.length === 0) {
        this.$toasted.show(
          "Debe registrar al menos un producto para el traslado."
        );
        return;
      }

      // ===============================
      // 2️⃣ Reglas SUNAT (lógica negocio)
      // ===============================

      // ❌ Motivo no válido para transporte privado
      if (this.modalidad === "02" && this.motivo === "09") {
        this.$toasted.show(
          "El motivo de traslado no es válido para transporte privado."
        );
        return;
      }

      // ✔️ Traslado entre establecimientos (motivo 04)
      // El destinatario debe ser la misma empresa (mismo RUC/DNI)
      if (this.motivo === "04") {
        if (this.cliente.DniRuc !== this.remitente.docremitente) {
          this.$toasted.show(
            "En traslado entre establecimientos, el destinatario debe ser la misma empresa."
          );
          return;
        }
      }

      // ✔️ Transporte público → proveedor obligatorio
      if (this.modalidad === "01") {
        if (Object.keys(this.proveedor).length == 0) {
          this.$toasted.show(
            "Debe registrar proveedor cuando la modalidad es transporte público."
          );
          return;
        }
      }

      // ❌ Transporte privado → NO proveedor
      if (
        this.modalidad === "02" &&
        Object.keys(this.proveedor).length > 0
      ) {
        this.$toasted.show(
          "No debe registrar proveedor cuando el transporte es privado."
        );
        return;
      }

      // ✔️ Transporte privado → chofer y vehículo obligatorios
      if (this.modalidad === "02") {
        if (
          !this.chofer.documento ||
          !this.chofer.razonSocial ||
          !this.chofer.licencia ||
          !this.chofer.placa
        ) {
          this.$toasted.show(
            "Para transporte privado debe registrar chofer y vehículo."
          );
          return;
        }
      }

      // ===============================
      // 3️⃣ Armado del JSON final
      // ===============================

      const data = {
        tipoGuia: "09", // GRE REMITENTE
        cliente: { ...this.cliente },
        proveedor: { ...this.proveedor },
        remitente: { ...this.remitente },
        chofer: {...this.chofer},
        proveedor: {...this.proveedor},
        productos: { ...this.productos },
        ubigeoorigen: this.ubigeoorigen,
        ubigeodestino: this.ubigeodestino,
        direccionorigen: this.direccionorigen,
        direcciondestino: this.direcciondestino,
        fecha: this.fecha,
        guiaremision: this.guiaremision,
        motivo: this.motivo,
        modalidad: this.modalidad
      };

      // ===============================
      // 4️⃣ Envío al backend
      // ===============================

      try {
        const res = await this.axios.post(
          `${this.API}/guia-remision-remitente`,
          data
        );

        this.$toasted.show("Guía de Remisión generada correctamente.");
        console.log(res.data);

        return res.data;
      } catch (error) {
        console.error(error);
        this.$toasted.show(
          "Error al generar la guía de remisión. Verifique los datos."
        );
      }
    }
  }
};
</script>

<style>
.dvTitle {
  text-align: center;
  font-size: 1.2rem;
  font-weight: bold;
  margin-top: 2%;
}
</style>
