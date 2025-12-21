<template>
  <b-container>
    <b-row>
         <b-col cols="6" class="">
            <b-card class=" vh-100">
                <div class="dvTitle dvTitleSup">
                    <span class="spnTitle">CLIENTE</span>
                </div>
                <b-form-group  class="mt-5">
                    <label class="ocultarMovil">Cliente</label>
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
                <b-form-group  class="my-0">
                    <label class="ocultarMovil">UBIGEO ORIGEN</label>
                    <input type="text"  class="form-control">
                </b-form-group>
                <b-form-group  class="my-0">
                    <label class="ocultarMovil">DIRECCIÓN ORIGEN</label>
                    <input type="text"  class="form-control">
                </b-form-group>
                <b-form-group  class="my-0">
                    <label class="ocultarMovil">UBIGEO DESTINO</label>
                    <input type="text"  class="form-control">
                </b-form-group>
                <b-form-group  class="my-0">
                    <label class="ocultarMovil">DIRECCIÓN DESTINO</label>
                    <input type="text"  class="form-control">
                </b-form-group>
                <b-form-group  class="my-0">
                    <label class="ocultarMovil">MOTIVO</label>
                    <input type="text"  class="form-control">
                </b-form-group>
                <b-form-group  class="my-0">
                    <label class="ocultarMovil">NRO. GUÍA REMISIÓN</label>
                    <input type="text"  class="form-control">
                </b-form-group>
            </b-card>
        </b-col>
        <b-col cols="6" class="vh-100">
            <b-card class=" vh-100"> 
                <div class="dvTitle dvTitleSup">
                    <span class="spnTitle">CHOFER</span>
                </div>
                <b-form-group  class="mt-5">
                    <label class="ocultarMovil">PLACA DEL VEHÍCULO</label>
                    <input type="text"  class="form-control">
                </b-form-group>
                <b-form-group  class="my-0">
                    <label class="ocultarMovil">DNI CHOFER</label>
                    <input type="text"  class="form-control">
                </b-form-group>
                <b-form-group  class="my-0">
                    <label class="ocultarMovil">LICENCIA CHOFER</label>
                    <input type="text"  class="form-control">
                </b-form-group>
              </b-card>
        </b-col>
        <b-col cols="12">
            <b-card>
                <div class="dvTitle dvTitleSup">
                    <span class="spnTitle "> DETALLE</span>
                </div>
                <b-row>
                    <b-col cols="4">
                         <b-form-group class="mt-5">
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
                    <b-col cols="4"></b-col>
                    <b-col cols="12">dddd</b-col>
                </b-row>
            </b-card>
        </b-col>
    </b-row>
   
    
  </b-container>
</template>

<script>
import config from "@/store/config.js";
export default {
  data() {
    return {
      API: config.API,
      CodSunatModifica: "",
      NroComprobanteModifica: "",
      productos: [],
      almacen: {},
      puntoVenta: {}
    };
  },
  mounted() {
    this.getVendedor();
  },
  methods: {
    focusAutocomplete(ref) {
      this.$refs[ref].handleDoubleClick();
    },
    async setCliente(obj, dni = false) {
      console.log(obj);
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
      CodSunatModifica = obj.CodSunat;
      NroComprobanteModifica = obj.NroComprobante;

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
                this.venta.cliente = response.data;
              } else {
                this.$toasted.show("No se encontro al cliente");
              }
            })
            .catch(error => {
              console.log(error);
            });
          response.data.map(item => {
            item.estadoPxP = false;
            let preciosProd = [];
            item.precioCont = item.PrecioContado;
            preciosProd = JSON.parse(item.PreciosPorProducto || "[]");
            let fPP = preciosProd.filter(p => p.cantidad === 0);

            if (!item.PreciosPorProducto || fPP.length === 4) {
              item.estadoPxP = true;
            }
            this.add([item, () => {}, false]);
          });
          //   this.venta.NotaIdMotivo = null;
          //   this.bloquearInput = true;
          //   this.obtenerStok({ IdAlmacen: this.venta.almacen.IdAlmacen });
        });
    }
  }
};
</script>

<style>
.clsFormulario {
  border: 1px solid #bdbdbd;
  border-radius: 25px;
  padding: 5% 5%;
}
.dvTitle {
  text-align: center;
  /* border: 1px solid blue; */
}

.spnTitle {
  min-width: 400px !important;
}
.dvTitle .spnTitle {
  text-align: center;
  border: 1px solid #bdbdbd;
  background: #ffffff;
  padding: 2% 25%;
  border-radius: 25px;
}
</style>
