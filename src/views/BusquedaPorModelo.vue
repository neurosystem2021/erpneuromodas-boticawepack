<template>
  <b-container fluid>
    <b-row>
        <b-col cols="12" class="my-1">
           <b-input-group>
            <autocomplete
              required="required"
              ref="inputCliente"
              :url="API + 'clientes'"
              anchor="ClienteDniRuc"
              label="writer"
              placeholder=""
              :onInput="(val) => newDniRuc = val"
              :on-select="setCliente" 
              :onFocus="focusAutocomplete.bind(null, 'inputCliente')"
              :classes="{input: 'form-control'}">
            </autocomplete>
            <clientes-form :newDniRuc="newDniRuc" @onAddCliente="setCliente"></clientes-form>
          </b-input-group>
        </b-col>
        <b-col cols="12" md="6" class="my-1">
            <b-input-group>
              <autocomplete
                required="required"
                :url="API + 'productos/modelos'"
                ref="inputModelo"
                anchor="ProductoModelo"
                placeholder="Modelo"
                label="writer"
                :customParams="{ limit: 10 }"
                :on-select="setModelo"        
                :onFocus="focusAutocomplete.bind(null, 'inputModelo')"
                :classes="{input: 'form-control upper'}">
              </autocomplete>
                <b-button 
                  variant="warning" 
                  :class="microActivado?'btn-parpadeante':''" 
                  @click="startRecognition">
                  <i class="fas fa-microphone"></i>
                </b-button>
            </b-input-group>
        </b-col>
        <b-col cols="12" md="6" class="my-1">
          <b-input-group>
            <input 
              v-model="loadCode"
              type="text"
              class="form-control"
              id="loadBarcode"
              placeholder="Codigo de Barra"   
              @keypress.enter="busquedaPorCodeBar"
            />
          <b-button variant="primary" @click="busquedaPorCodeBar"><i class="fas fa-search"></i></b-button>
            
          </b-input-group>
        </b-col>
        <b-col></b-col>
        <b-col cols="12" class="mt-3">
          <div class="clsTable">
             <b-table :items="productosCarrito" :fields="fieldsCarrito" >
              <template slot="Imagen" slot-scope="data"> 
                <b-img style="border-radius: 10px; width:50px" fluid
                      :src="data.item.Imagen == null || data.item.Imagen==''?'static/no-photo.png': '/resources/images/productos/' + data.item.Imagen" alt="" @error="setAltImg" ></b-img>	
              </template>
              <template slot="Producto" slot-scope="data">
                {{ data.item.Producto }} - {{ data.item.CodigoBarra }}
              </template>
              <template slot="cantidad" slot-scope="data">
                <input type="number" class="form-control" style="width: 120px;" v-model="data.item.cantidad">
              </template>
              <template slot="descuento" slot-scope="data">
                <input type="number" class="form-control" style="width: 120px;" v-model="data.item.descuento">
              </template>
              <template slot="precio" slot-scope="data">
                <input type="number" class="form-control" style="width: 120px;" v-model="data.item.precio">
              </template>
              <template slot="subTotal" slot-scope="data">
                  {{ 
                    (data.item.cantidad ? data.item.cantidad :0)  *
                    (data.item.precio ? data.item.precio :0)
                  }}

              </template>
              <template slot="Total" slot-scope="data">
                  {{ 
                    (data.item.cantidad ? data.item.cantidad :0)*
                    (data.item.precio ? data.item.precio :0) -
                    (data.item.descuento ? data.item.descuento :0) 
                  }}

              </template>
              <template slot="action" slot-scope="data">
                <b-button variant="danger" @click="eliminar(data.index)"><i class="fa fa-trash" aria-hidden="true"></i></b-button>
              </template>
            </b-table>
          </div>
        </b-col>
        <b-col cols="12" md="6" lg="6" style="text-align: center; align-items: center; align-content: center;">
            <b-button @click="guardarProforma" class="btnAction" variant="success">GENERAR PROFORMA</b-button>
        </b-col>
        <b-col cols="12" md="6" lg="6" style="align-items: center;">
            <table class="table table-striped table-hover ">
              <tr>
                <td class="clsSubTotal">SubTotal:</td>
                <td class="clsSubTotal">{{ subTotal() }}</td>
              </tr>
              <tr>
                <td class="clsDescuento">Descuento:</td>
                <td class="clsDescuento">{{ descuento() }}</td>
              </tr>
              <tr>
                <td class="clsTotal">Total:</td>
                <td class="clsTotal">{{ Total() }}</td>
              </tr>
            </table>
        </b-col>
    </b-row>
    <b-modal v-model="modalProducto" size="xl" hide-footer id="mdlProducto"> 
        <b-table :items="productos" :fields="fields" size="lg" :per-page="perPage" :current-page="currentPage" small  id="my-table">
            <template slot="action" slot-scope="data">
              <b-button variant="info" @click="andirProducto(data.item)"><i class="fa fa-cart-arrow-down" aria-hidden="true"></i></b-button>
            </template>
             <template slot="Imagen" slot-scope="data"> 
                <b-img style="border-radius: 10px; width:50px" fluid
                      :src="data.item.Imagen == null || data.item.Imagen==''?'static/no-photo.png': '/resources/images/productos/' + data.item.Imagen" alt="" @error="setAltImg" ></b-img>	
              </template>
              <template slot="Producto" slot-scope="data">
                {{ data.item.Producto }} - {{ data.item.CodigoBarra }}
              </template>
        </b-table>
        <b-row>
          <b-col cols="10">
             <b-pagination
                v-model="currentPage"
                :total-rows="productos.length"
                :per-page="perPage"
                aria-controls="my-table"
              ></b-pagination>
          </b-col>
          <b-col cols="2">
              <b-form-select v-model="perPage" :options="options"></b-form-select>
          </b-col>
        </b-row>
        

        
        
    </b-modal>
  </b-container>
</template>

<script>
import config from "@/store/config.js";
import ClientesForm from "@/components/ClientesForm";
export default {
  data() {
    return {
      newDniRuc: "",
      microActivado: false,
      cliente: {},
      loadCode: "",
      currentPage: 1,
      perPage: 10,
      API: config.API,
      productos: [],
      productosCarrito: [],
      options: [
        { value: 5, text: "5" },
        { value: 10, text: "10" },
        { value: 20, text: "20" },
        { value: 50, text: "50" },
        { value: 100, text: "100" }
      ],
      modalProducto: false,
      fieldsCarrito: [
        { key: "stock" },
        // { key: "CodigoBarra" },
        { key: "Producto" },
        { key: "ProductoModelo", label: "Modelo" },
        { key: "ProductoMarca", label: "Marca" },
        { key: "ProductoTalla", label: "Talla" },
        { key: "Color" },
        { key: "Genero" },
        { key: "Botapie" },
        { key: "ProductoTalla", label: "Talla" },
        { key: "Imagen" },
        { key: "cantidad" },
        { key: "precio" },
        { key: "subTotal", class: "text-center clsSubTotal" },
        { key: "descuento", class: "text-center clsDescuento" },
        { key: "Total", class: "text-center clsTotal" },
        { key: "action", label: "" }
      ],
      fields: [
        { key: "stock" },
        // { key: "CodigoBarra" },
        { key: "Producto" },
        { key: "ProductoModelo", label: "Modelo" },
        { key: "ProductoMarca", label: "Marca" },
        { key: "ProductoTalla", label: "Talla" },
        { key: "Color" },
        { key: "Genero" },
        { key: "Botapie" },
        { key: "ProductoTalla", label: "Talla" },
        { key: "Imagen" },
        { key: "action", label: "" }
      ]
    };
  },
  mounted() {
    this.$nextTick(() => {
      this.loadClienteVarios();
    });
  },
  methods: {
    setAltImg(event) {
      event.target.src = "static/no-photo.png";
    },
    subTotal() {
      let suma = 0;
      if (this.productosCarrito.length > 0) {
        suma = this.productosCarrito.reduce((a, b) => {
          return a + parseFloat(b.cantidad * b.precio);
        }, 0);
      }
      return parseFloat(suma).toFixed(2);
    },
    descuento() {
      let suma = 0;
      if (this.productosCarrito.length > 0) {
        suma = this.productosCarrito.reduce((a, b) => {
          return a + parseFloat(b.descuento);
        }, 0);
      }
      return parseFloat(suma).toFixed(2);
    },
    Total() {
      let suma = 0;
      if (this.productosCarrito.length > 0) {
        suma = this.productosCarrito.reduce((a, b) => {
          return a + parseFloat(b.cantidad * b.precio - b.descuento);
        }, 0);
      }
      return parseFloat(suma).toFixed(2);
    },
    focusAutocomplete(ref) {
      this.$refs[ref].type = "";
      this.$refs[ref].handleDoubleClick();
    },
    loadClienteVarios() {
      this.axios.get(this.API + "clientes/00000001").then(res => {
        if (res.data.IdCliente) {
          this.setCliente(res.data);
        }
      });
    },
    async setModelo(modelo) {
      this.productos = [];
      this.currentPage = 1;
      let prods = await this.axios(`${this.API}productos_modelo`, {
        params: modelo
      }).then(res => {
        return res.data;
      });
      const promesas = prods.map(prod => {
        return this.axios(`${this.API}productos/stock`, {
          params: {
            idAlmacen: 1,
            idProductos: [prod.IdProducto]
          }
        }).then(res => {
          return {
            ...prod,
            stock: res.data[0].stock
          };
        });
      });
      const resultados = await Promise.all(promesas);
      this.productos.push(...resultados);
      this.modalProducto = true;
    },
    andirProducto(item) {
      if (!this.productosCarrito.some(v => v.IdProducto == item.IdProducto)) {
        let prod = {
          ...item,
          cantidad: 1,
          precio: item.PrecioContado,
          descuento: 0
        };
        this.productosCarrito.unshift(prod);
        this.modalProducto = false;
      }
    },
    busquedaPorCodeBar() {
      let idProducto = this.loadCode;
      if (idProducto) {
        let params = { codigoBarra: idProducto };
        this.axios
          .get(this.API + "productos/id/" + idProducto, { params: params })
          .then(response => {
            if (response.data.IdProducto) {
              this.andirProducto(response.data);
              this.$toasted.show("Producto añadido correctamente");
            } else {
              this.loadCode = "";
              this.$toasted.show("No se encontró ningun producto");
            }
          });
      }
      this.loadCode = "";
    },
    startRecognition() {
      if (!("webkitSpeechRecognition" in window)) {
        alert("Lo siento, tu navegador no soporta la búsqueda por voz.");
        return;
      }
      this.microActivado = true;
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
          this.microActivado = false;
          this.search();
        }
      };

      this.recognition.onerror = event => {
        this.microActivado = false;
        console.error("Speech Recognition Error:", event);
        alert(`Error en el reconocimiento de voz: ${event.error}`);
      };

      this.recognition.onend = async () => {
        if (!this.query) {
          this.microActivado = false;
          alert("No se pudo captar ninguna palabra");
        }
      };

      try {
        this.recognition.start();
      } catch (error) {
        this.microActivado = false;
        console.error("Error starting recognition:", error);
        alert("No se pudo iniciar el reconocimiento de voz.");
      }
    },
    search() {
      const inputElement = this.$refs["inputModelo"].$el.querySelector("input");

      inputElement.focus();
      setTimeout(() => {
        inputElement.value = this.query;
        const event = new Event("input", { bubbles: true });
        inputElement.dispatchEvent(event);
      }, 100);
    },
    setCliente(cliente) {
      this.cliente = cliente;
      this.$refs.inputCliente.setValue(
        cliente.ClienteDniRuc
          ? cliente.ClienteDniRuc
          : cliente.Cliente.toUpperCase()
      );
    },
    eliminar(index) {
      this.productosCarrito.splice(index, 1);
    },
    async guardarProforma() {
      let venta = {
        cliente: { ...this.cliente },
        productos: [...this.productosCarrito],
        vendedor: { Usuario: sessionStorage.getItem("User") }
      };
      let productos = venta.productos;
      let totalPagos = 0;

      if (!venta.cliente.DniRuc) {
        //this.$toasted.show('Debe seleccionar un cliente.')
        this.$swal({
          position: "center",
          icon: "info",
          title: "Debe seleccionar un cliente",
          showConfirmButton: true,
          confirmButtonText: "Entiendo",
          target: document.getElementById("conten"),
          confirmButtonColor: "#0275d8"
        });

        return;
      }
      var esNumerico = Number.isInteger(parseInt(venta.cliente.DniRuc));
      if (!esNumerico) {
        //this.$toasted.show('El RUC debe ser numérico, editar cliente.')
        this.$swal({
          position: "center",
          icon: "info",
          title: "El RUC debe ser numérico, editar cliente.",
          showConfirmButton: true,
          confirmButtonText: "Entiendo",
          target: document.getElementById("conten"),
          confirmButtonColor: "#0275d8"
        });
        return;
      }

      if (!productos.length) {
        this.$swal({
          position: "center",
          icon: "info",
          title: "No hay productos para vender",
          showConfirmButton: true,
          confirmButtonText: "Entiendo",
          target: document.getElementById("conten"),
          confirmButtonColor: "#0275d8"
        });

        return;
      }

      await this.axios.post(this.API + "proformas", venta).then(response => {
        if (response.data.insertId) {
          this.$toasted.success("Proforma realizada con exito");
          window.open(
            "/nuevo/index.html#/reportes/proforma/" + response.data.insertId,
            "_blank"
          );
        }
      });
      this.loadClienteVarios();
      this.productosCarrito = [];
    }
  },
  components: {
    ClientesForm
  }
};
</script>

<style>
.btn-parpadeante {
  background-color: #ff4757;
  color: white;
  border: none;
  cursor: pointer;
  outline: none;
  transition: transform 0.2s;
  animation: parpadeo 1.5s infinite;
}

@keyframes parpadeo {
  0% {
    opacity: 1;
    box-shadow: 0 0 0 0 rgba(255, 71, 87, 0.7);
  }
  50% {
    opacity: 0.5;
    box-shadow: 0 0 20px 10px rgba(255, 71, 87, 0);
  }
  100% {
    opacity: 1;
    box-shadow: 0 0 0 0 rgba(255, 71, 87, 0);
  }
}

.clsTable {
  height: 520px;
  overflow-y: auto;
}

.clsSubTotal {
  background: #fff9c4 !important;
}
.clsDescuento {
  background: #ffebee !important;
}
.clsTotal {
  background: #e8f5e9 !important;
}

.modal-dialog .modal-lg {
  min-width: 80%;
}
</style>
