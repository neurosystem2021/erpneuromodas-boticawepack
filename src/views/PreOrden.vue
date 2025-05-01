<template>
  <b-container fluid>
    <b-row>
      <b-col cols="12" lg="4" xl="4"> 
            Almacén
            <b-form-select v-model="idAlmacen" 
                 value-field="IdAlmacen"
                 text-field="Almacen"
                 disabled
                :options="lstAlmances"></b-form-select>
        </b-col>
        <b-col cols="12" lg="4" xl="4"> 
            Fecha:
            <input type="date" v-model="fechaEntrega" class="form-control">
        </b-col>
        <b-col cols="12" lg="4" xl="4"> 
          Hora de Entrega
          <input type="time" class="form-control" required
          v-model="HoraEntrega"
          >
        </b-col>
        
        <b-col cols="12" class="mt-2"> 
            <autocomplete
                required="required"
                ref="inputCliente"
                :url="API + 'clientes'"
                anchor="ClienteDniRuc"
                label="writer"
                placeholder="Cliente"
                class="font-weight-bold"
                :onInput="(val) => newDniRuc = val"
                :on-select="setCliente" 
                :onFocus="focusAutocomplete.bind(null, 'inputCliente')"
                :classes="{input: 'form-control'}">
            </autocomplete>
        </b-col>
        <b-col cols="12" lg="7" xl="7" v-if="idAlmacen" class="mt-2 mx-0"> 
          <b-input-group class="mt-2 mb-2">
            <autocomplete
                ref="autocompleteProducto"
                :url="APIProductos"
                :customParams="{ limit: '25' }"
                :onShouldRenderChild="renderChild"
                anchor="Producto"
                label="writer"
                placeholder="Agregar Producto"
                :on-select="setProducto"
                :onFocus="focusAutocomplete.bind(null, 'autocompleteProducto')"
                :classes="{input: 'form-control'}">
            </autocomplete>
            <b-input-group-append>
                <b-button variant="info" @click="startRecognition">
                  <i class="fas fa-microphone"></i>
                </b-button
                >
            </b-input-group-append>
          </b-input-group>
        </b-col>
        <b-col cols="12" lg="5" xl="5" v-if="idAlmacen" class="mt-2 mx-0">
          <b-input-group class="mt-2 mb-2">

            <input v-model="loadCode"
            @keydown.enter.prevent="loadBarcode"
            ref="barcodeInput"
            type="text"
            class="form-control"
            placeholder="Codigo de Barra" />
            <b-input-group-append>
                <b-button variant="primary" @click="loadBarcode"><i class="fas fa-search"></i></b-button>
              </b-input-group-append>
          </b-input-group>
        </b-col>
        <b-col cols="12">
          <div class="clsTblProductos mt-3">

          
          <b-table striped hover responsive="false" :fields="headerTable" class="tblPRoductos" :items="productos"> 
                  <template slot="stock" slot-scope="data">
                    <span style="font-weight: bold; font-size: 0.75rem">
                      {{ data.item.stock }}
                    </span>
                  </template>
                  <template slot="Producto" slot-scope="data">
                    <span style="font-weight: bold; font-size: 0.75rem">
                      {{ data.item.Producto }}
                    </span>
                  </template>
                  <template slot="pres" slot-scope="data">
                    <b-button   
                      v-if="data.item.tienePresentacion"
                      size="sm"
                      variant="info"
                      @click="showPresentacion(data.item,data.index)" 
                      style="margin-top:2px;font-size: 0.75rem"
                      >
                      <i class="fas fa-list"></i>
                    </b-button>
      
                     
                     
                  </template>
                  <template slot="img" slot-scope="data">
                    <b-img
                  style="border-radius: 10px"
                  fluid
                  :src="'/resources/images/productos/' + data.item.Imagen"
                  @error="setAltImg"
                  alt=""
                ></b-img>
                </template>
                  <template slot="presentacion" slot-scope="data">
                    <b-form-input
                      v-model="data.item.Descripcion"
                      type="text"
                      style="text-align:center; font-weight:bold; width: 80px;font-size: 0.8rem"
                      autocomplete="false"
                      disabled
                      ></b-form-input>
                     
                     
                  </template>
                  <template slot="cantidad" slot-scope="data">
                    <input
                      v-model="data.item.cantidad"
                      type="number"
                      style="text-align:center; font-weight:bold; width: 75px;font-size: 0.8rem"
                      autocomplete="false"
                      :id="`cantidad${data.index}`"
                      class="form-control"
                      />
                      <p style="color: rgb(0, 123, 255); text-align: center; font-weight: bold;" 
                      v-if="data.item.CantidadPresentacion">
                        {{ data.item.CantidadPresentacion }} unid.
                      </p>
                  </template>
                  <template slot="precio" slot-scope="data">
                    <input
                      :disabled="data.item.bloquearInputPrecio"
                      v-model="data.item.precio"
                      min="0"
                      step="0.00001"
                      type="number"
                      @focus="abrirModalTouch(data.index,2)"
                      style="text-align:center; font-weight:bold; width: 85px;font-size: 0.8rem"
                      autocomplete="false"  @input="metodoPagoCambio(1)"
                      class="form-control"
                      />
                     
                  </template>
                  <template slot="descuento" slot-scope="data">
                    <input
                      v-model="data.item.descuento"
                      type="number"
                      style="text-align:center; font-weight:bold; width: 75px;font-size: 0.8rem"
                      autocomplete="false"
                      @focus="abrirModalTouch(data.index,3)"
                      class="form-control"
                      />
                     
                  </template>
                  <template slot="subtotal" slot-scope="data">
                    <span style="font-size: 0.8rem">
                      {{ parseFloat(getSubTotalDetalle(data.item)).toFixed(2) }}

                    </span>
                     
                  </template>
                  <template slot="total" slot-scope="data">
                 <span style="font-size: 0.8rem">
                   {{ parseFloat( getTotalDetalle(data.item)).toFixed(2) }}

                 </span>
                     
                  </template>
                  <template slot="acciones" slot-scope="data">
                    <b-button 
                      class="mx-auto mt-1"
                      variant="danger"
                      size="sm" 
                      @click="quitarProducto(data.index)">
                     <i class="fas fa-times fa-lg"></i>
                    </b-button>

                  
                    <b-button 
                    class="mx-auto mt-1"
                      variant="info"
                      size="sm"
                      v-if="mostrarEditarProducto()"
                      @click="editarProducto(data.item,data.index)"
                      >
                     <i class="fas fa-pencil-alt fa-sm"></i>
                    </b-button>
          
                     
                  </template>

          </b-table>
        </div>
        </b-col>
       
    </b-row>
    <b-row class="mt-2">
      
        <b-col cols="12" lg="4" xl="4">
          <b-input-group prepend="Prof. válida por:" append="días" 
          class="my-2">
              <b-form-input
                v-model="diasValidosProf"
                min="0"
                value="0"
                type="number"
              ></b-form-input>
          </b-input-group>
        </b-col>
       
        <b-col cols="12" lg="4" xl="4" order-lg="2"	order-xl="2" order="3" >
            <b-button @click="guardar()"  variant="success" :disabled="distabledBtnGuardar" class="btnGuardar">Guardar</b-button>
        </b-col>

        <b-col cols="12" lg="4" xl="4">
          <table class="table">
            <tr>
              <td style="background: #1A237E; color: white; font-weight: bold;">Total</td>
              <td style="background: #C5CAE9;  font-weight: bold;">{{ totalCarrito }}</td>
            </tr>
          </table>
        </b-col>
       
      </b-row>
    <b-modal v-model="modalPresentacion" size="lg" hide-footer no-close-on-esc>
      <center>
        <h3 style="text-decoration: underline;">{{producto.Producto}}</h3>
      </center>
      <b-row>
          <b-col cols="6">
                <b-card
                align="center"
                class="mb-3 contentCard"
                @click="cambiarPresentacion(
                  producto,
                  false
                )"
                >
                <b-row>
                    <b-col md="6" class="nomCart">
                      <div>
                          <b-row class="nomCart2">
                            <center> <h6 class="text-uppercase"> {{producto.ProductoPresentacion}} </h6> </center>
                            </b-row>
                            Stock <span style="padding: 5px 10px; border-radius: 5px ;background-color:#28a745;color:#fff"> {{ producto.stock }} </span>
                      </div>
                      </b-col>
                      <b-col md="6" class="text-left">
                      <h5>x1</h5>
                      <span><b >Precio C: </b>S/ {{ producto.PrecioCosto | redondear(2) }}</span><br>
                      <b>Precio V: </b>S/ {{ producto.PrecioContado | redondear(2) }}
                    </b-col>
                </b-row>
                </b-card>
            </b-col>

            
          <b-col cols="6"  v-for="(pres,index) in producto.PreciosPorProducto" :key="index">
                <b-card
                align="center"
                class="mb-3 contentCard"
                @click="cambiarPresentacion(
                  pres,
                  true
                )"
                >
                <b-row>
                    <b-col md="6" class="nomCart">
                      <div>
                          <b-row class="nomCart2">
                            <center> <h6 class="text-uppercase"> {{pres.nombre}} </h6> </center>
                            </b-row>
                            Stock <span style="padding: 5px 10px; border-radius: 5px ;background-color:#28a745;color:#fff">
                            {{  parseFloat(producto.stock/pres.cantidad).toFixed(2) }}
                            </span>
                      </div>
                      </b-col>
                      <b-col md="6" class="text-left">
                      <h5>x{{ pres.cantidad }}</h5>
                      <span><b >Precio C: </b>S/ {{ pres.precioc | redondear(2) }}</span><br>
                      <b>Precio V: </b>S/ {{ pres.precio | redondear(2) }}
                    </b-col>
                </b-row>
                </b-card>
            </b-col>
      </b-row>
    </b-modal>
  </b-container>
</template>

<script>
import config from "@/store/config.js";
export default {
  data() {
    return {
      API: config.API,
      idAlmacen: "",
      modalPresentacion: false,
      lstAlmances: [],
      producto: {},
      fechaEntrega: "",
      HoraEntrega: "08:00",
      cliente: {},
      productos: [],
      loadCode: "",
      index: 0,
      diasValidosProf: "",
      venta: {
        EsCredito: 0,
        Serie: "",
        Numero: "",
        cliente: {},
        comisionista: {},
        tipoVenta: {
          IdTipoDoc: 3,
          TipoDoc: "NOTA DE VENTA",
          LimitItems: 0,
          CodSunat: "12"
        },
        almacen: {
          //IdAlmacen: 1,
          //Almacen: 'PRINCIPAL'
        },
        puntoVenta: {
          //IdDocVentaPuntoVenta: 1,
          //PuntoVenta: 'PRINCIPAL'
        },
        vendedor: {},
        adelantosPreOrden: [],
        FechaCredito: this.$moment().format("YYYY-MM-DD"),
        FechaDoc: this.$moment().format("YYYY-MM-DDTH:mm:ss"),
        pago: {
          efectivo: { IdMetodoPago: 1, monto: 0 },
          visa: { IdMetodoPago: 2, monto: 0 },
          mastercard: { IdMetodoPago: 3, monto: 0 },
          transferencia: { IdMetodoPago: 4, monto: 0 }
        },
        IdPreOrden: [],
        CampoDireccion: "Direccion",
        EsOrganizacion: 0,
        NombreOrganizacion: "",
        NroComprobanteModifica: "",
        EsSeparado: 0,
        Separado: 0,
        CodSunatModifica: "",
        NotaIdMotivo: "",
        NotaDescMotivo: "",
        EsDelivery: false,
        nombreLlevar: "",
        DireccionEntrega: "",
        diasValidosProf: 1,
        idProforma: 0,
        anioNumeroProforma: ""
      },
      headerTable: [
        {
          key: "stock",
          label: "Stock",
          variant: "info",
          class: "text-center",
          thStyle: { width: "5%" }
        },
        { key: "Producto", thStyle: { width: "53%" } },
        { key: "img", label: "Imagen" },
        { key: "pres", label: "", thStyle: { width: "5%" } },
        {
          key: "presentacion",
          label: "Pres.",
          thStyle: { width: "5%" }
        },
        { key: "cantidad", label: "Cant.", thStyle: { width: "7%" } },
        { key: "precio", label: "Precio", thStyle: { width: "5%" } },
        {
          key: "subtotal",
          label: "Sub Total",
          variant: "success",
          class: "text-center",
          thStyle: { width: "5%" }
        },
        { key: "descuento", label: "Desc.", thStyle: { width: "5%" } },
        {
          key: "total",
          label: "Total",
          variant: "danger",
          class: "text-center",
          thStyle: { width: "5%" }
        },
        { key: "acciones", label: "", thStyle: { width: "5%" } }
      ]
    };
  },
  mounted() {
    this.fechaEntrega = this.$moment().format("YYYY-MM-DD");
    this.obtenerAlmance();
  },
  methods: {
    startRecognition() {
      if (!("webkitSpeechRecognition" in window)) {
        alert("Lo siento, tu navegador no soporta la búsqueda por voz.");
        return;
      }

      this.recognition = new webkitSpeechRecognition();
      this.recognition.lang = "es-ES";
      this.recognition.continuous = false;
      this.recognition.interimResults = false;

      this.recognition.onstart = () => {
        console.log("Speech recognition service has started");
      };

      this.recognition.onresult = event => {
        if (event.results.length > 0) {
          this.query = event.results[0][0].transcript;
          this.search();
        }
      };

      this.recognition.onerror = event => {
        console.error("Speech Recognition Error:", event);
        alert(`Error en el reconocimiento de voz: ${event.error}`);
      };

      this.recognition.onend = async () => {
        console.log("Speech recognition service disconnected");
        console.log("query", this.query);
        if (!this.query) {
          alert("No se pudo captar ninguna palabra");
        }
      };

      try {
        this.recognition.start();
      } catch (error) {
        console.error("Error starting recognition:", error);
        alert("No se pudo iniciar el reconocimiento de voz.");
      }
    },
    search() {
      const inputElement = this.$refs["autocompleteProducto"].$el.querySelector(
        "input"
      );
      inputElement.focus();
      setTimeout(() => {
        inputElement.value = this.query;
        const event = new Event("input", { bubbles: true });
        inputElement.dispatchEvent(event);
      }, 100);
    },
    async obtenerAlmance() {
      await this.axios(`${this.API}almacenes`).then(res => {
        if (res.data) {
          this.lstAlmances = res.data;
        }
      });
      this.idAlmacen = sessionStorage.getItem("IdAlmacen");
      this.venta.almacen = this.lstAlmances.filter(
        v => v.IdAlmacen == this.idAlmacen
      )[0];
    },
    focusAutocomplete(ref) {
      this.$refs[ref].type = "";
      this.$refs[ref].handleDoubleClick();
    },
    setCliente(obj) {
      console.log(obj);
      this.venta.cliente = obj;
    },
    async setProducto(obj) {
      if (this.productos.some(v => v.IdProducto == obj.IdProducto)) {
        this.$toasted.error("El producto ya se encuentra en la lista");
        return false;
      }

      let prod = await this.obtenerStockBuscador(obj.IdProducto);
      let pres = [];

      if (prod.PreciosPorProducto) {
        let newPres = JSON.parse(prod.PreciosPorProducto);
        pres = newPres.filter(v => v.cantidad > 0);
      }
      obj.PreciosPorProducto = pres;
      obj.cantidad = 1;
      obj.descuento = 0;
      obj.precio = obj.PrecioContado;
      obj.Descripcion = prod.ProductoPresentacion
        ? prod.ProductoPresentacion
        : "UNIDAD";
      obj.ProductoPresentacion = prod.ProductoPresentacion
        ? prod.ProductoPresentacion
        : "UNIDAD";
      obj.tienePresentacion = pres.some(v => v.cantidad > 0);
      this.productos.unshift(obj);
      this.$nextTick(() => {
        let element = document.getElementById("Cantidad0");
        element.focus();
        element.select();
      });
    },
    async loadBarcode(e) {
      if (e) {
        e.preventDefault();
      }
      let idProducto = this.loadCode;

      let prod = await this.axios.get(this.API + "productos/id/" + idProducto, {
        params: {
          codigoBarra: this.loadCode
        }
      });
      if (prod.data) {
        this.setProducto(prod.data);
      } else {
        this.$toasted.show("No se encontró ningun producto");
      }
      this.loadCode = "";
    },
    renderChild(data) {
      if (this.idAlmacen) {
        let style = "ss";
        if (data.stock <= 0) {
          style = "danger";
        }
        // cambiar color los que faltan 4 meses por vencer
        if (data.stock > 0) {
          style = "success";
        }
        return `
         <b class="bg-${style}" style="color: #fff; padding: 0 6px">${
          data.stock
            ? Number.isInteger(data.stock)
              ? data.stock
              : data.stock.toFixed(2)
            : data.stock
        }</b> | <b>${data.Producto} - ${data.ProductoMarca}</b> | <b>S/ ${
          data.PrecioContado
            ? data.PrecioContado.toFixed(2)
            : data.PrecioContado
        }</b> 
      `;
      }
      return `
        <b>${data.Producto}</b> / ${data.ProductoCategoria} / ${
        data.ProductoMarca
      }
      `;
    },
    eliminarProducto(index) {
      this.productos.splice(index, 1);
    },
    async guardar() {
      this.inProgress = true;
      event.preventDefault();
      this.venta.productos = this.productos;
      this.venta.diasValidosProf = this.diasValidosProf;
      this.venta.IdRepartidor = this.IdRepartidor;
      this.venta.fechaEntrega = this.fechaEntrega;
      this.venta.HoraEntrega = this.HoraEntrega;
      await this.axios
        .post(this.API + "proformas?id_empresa=" + this.empresa, this.venta)
        .then(async response => {
          this.$toasted.info("Orden de Pedido Guardada con exito");
          //this.limpiarDespuesVenta()
          // this.loadClienteVarios();
          window.open(
            "/nuevo/index.html#/reportes/proforma/" +
              response.data.insertId +
              "?id_empresa=" +
              this.empresa,
            "_blank"
          );
          setTimeout(() => {
            window.location.reload();
          }, 100);
        })
        .catch(error => {
          console.error(error);
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
          this.inProgress = false;
        });
      this.inProgress = false;
    },
    getSubTotalDetalle(prod) {
      let subtotal = prod.cantidad * prod.precio;
      return subtotal;
    },
    getTotalDetalle(prod) {
      let total =
        prod.cantidad * prod.precio - (prod.descuento ? prod.descuento : 0);
      return total;
    },
    mostrarEditarProducto() {
      if (this.idPerfil != 1) {
        return false;
      }
      if (this.ventas.length > 1) {
        return false;
      }
      return true;
    },
    async obtenerStockBuscador(IdProducto) {
      let res = {};
      await this.axios(this.API + "productos/puntoventa/stock", {
        params: {
          idProductos: [IdProducto],
          idAlmacen: this.idAlmacen
        }
      }).then(response => {
        res = response.data[0];
        // return response.data;
      });
      return res;
    },
    showPresentacion(prod, index) {
      this.producto = { ...prod };
      this.index = index;
      this.modalPresentacion = true;
    },
    cambiarPresentacion(obj, esPresentacion) {
      let producto = { ...this.producto };
      if (esPresentacion) {
        producto.Descripcion = obj.nombre;
        producto.CantidadPresentacion = obj.cantidad;
        producto.cantidad = 1;
        producto.descuento = 0;
        producto.bloquearInputPrecio = true;
        producto.precio = obj.precio;
        producto.presentacion = JSON.stringify(obj);
      } else {
        producto.Descripcion = obj.ProductoPresentacion;
        producto.CantidadPresentacion = null;
        producto.cantidad = 1;
        producto.descuento = 0;
        producto.bloquearInputPrecio = false;
        producto.precio = producto.PrecioContado;
        producto.presentacion = null;
      }

      Vue.set(this.productos, this.index, producto);
      this.modalPresentacion = false;
    },
    setAltImg(event) {
      event.target.src = "static/no-photo.png";
    }
  },
  computed: {
    APIProductos() {
      if (this.idAlmacen) {
        return this.API + "productos/puntoventa/stock/" + this.idAlmacen;
      } else {
        return this.API + "productos";
      }
    },
    totalCarrito() {
      let monto = 0;
      if (this.productos.length > 0) {
        monto = this.productos.reduce((sum, producto) => {
          return sum + parseFloat(producto.cantidad * producto.precio);
        }, 0);
      }
      return parseFloat(monto).toFixed(2);
    },
    distabledBtnGuardar() {
      if (!Object.keys(this.venta.cliente).length > 0) {
        return true;
      }
      if (!this.productos.length > 0) {
        return true;
      }
      return false;
    }
  }
};
</script>

<style scoped>
.clsTblProductos {
  /* border: 1px solid red; */
  overflow-x: auto;
  width: 100%;
  max-width: 100%;
}

.tblPRoductos {
  width: 100%;
  display: block;
  /* border: 1px solid blue; */
}

.table-responsive {
  overflow-x: auto;
}

.btnGuardar {
  display: block;
  width: 100%;
}

@media (max-width: 918px) {
  .btnGuardar {
    display: block;
    width: 100%;
  }
}
</style>
