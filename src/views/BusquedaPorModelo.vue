<template>
  <b-container>
    <b-row>
        <b-col cols="12" md="4">
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
        <b-col cols="12" md="4">
          <b-input-group>
            <input 
              v-model="loadCode"
              type="text"
              class="form-control"
              id="loadBarcode"
              placeholder="Codigo de Barra"   
            />
            <!-- <b-button variant="success" @click="abrirCodigoBarra"><i class="fas fa-barcode"></i></b-button> -->
            <b-button variant="primary" @click="busquedaPorCodeBar"><i class="fas fa-search"></i></b-button>
            
          </b-input-group>
        </b-col>
        <b-col></b-col>
        <b-col cols="12" class="mt-3">
          <div class="clsTable">
             <b-table :items="productosCarrito" :fields="fieldsCarrito" >
              <template slot="cantidad" slot-scope="data">
                <input type="number" class="form-control" style="width: 120px;" v-model="data.item.cantidad">
              </template>
              <template slot="precio" slot-scope="data">
                <input type="number" class="form-control" style="width: 120px;" v-model="data.item.precio">
              </template>
            </b-table>
          </div>
        </b-col>
        <b-col cols="12" style="text-align: center; align-items: center;">
            <b-button variant="success">GENERAR PROFORMA</b-button>
        </b-col>
    </b-row>
    <b-modal v-model="modalProducto" hide-footer size="lg" > 
        <b-table :items="productos" :fields="fields" size="lg" :per-page="perPage" :current-page="currentPage" small  id="my-table">
            <template slot="action" slot-scope="data">
              <b-button variant="success" @click="andirProducto(data.item)"><i class="fa fa-plus" aria-hidden="true"></i></b-button>
            </template>
        </b-table>

         <b-pagination
          v-model="currentPage"
          :total-rows="productos.length"
          :per-page="perPage"
          aria-controls="my-table"
        ></b-pagination>
        
    </b-modal>
  </b-container>
</template>

<script>
import config from "@/store/config.js";
export default {
  data() {
    return {
      microActivado: false,
      loadCode: "",
      currentPage: 1,
      perPage: 15,
      API: config.API,
      productos: [],
      productosCarrito: [],
      modalProducto: false,
      fieldsCarrito: [
        { key: "stock" },
        { key: "CodigoBarra" },
        { key: "Producto" },
        { key: "ProductoTalla", label: "Talla" },
        { key: "cantidad" },
        { key: "precio" },
        { key: "action", label: "" }
      ],
      fields: [
        { key: "stock" },
        { key: "CodigoBarra" },
        { key: "Producto" },
        { key: "ProductoTalla", label: "Talla" },
        { key: "action", label: "" }
      ]
    };
  },
  methods: {
    focusAutocomplete(ref) {
      this.$refs[ref].type = "";
      this.$refs[ref].handleDoubleClick();
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
        let prod = { ...item, cantidad: 1, precio: item.PrecioContado };
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
      const inputElement = this.$refs["ProductoModelo"].$el.querySelector(
        "input"
      );
      inputElement.focus();
      setTimeout(() => {
        inputElement.value = this.query;
        const event = new Event("input", { bubbles: true });
        inputElement.dispatchEvent(event);
      }, 100);
    }
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
  min-height: 420px;
}
</style>
