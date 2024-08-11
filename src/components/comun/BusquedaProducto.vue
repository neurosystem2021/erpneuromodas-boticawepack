<template>
  <section ref="lstProducto" class="lstProducto">
    <div class="search">
      <input 
        type="text" 
        class="input" 
        placeholder="Agregar Productos" 
        v-model="search" 
        @focus="buscarProductos"
      />
      <b-button @click="startRecognition">
        <i class="fa fa-microphone"></i>
      </b-button>
    </div>
    <div class="lstBusqueda" v-if="lstProductos.length > 0">
      <table class="tblBusqueda">
        <tbody>
          <tr 
            
            v-for="(producto, index) in lstProductos" 
            :key="index" 
            @click="abrirModalSelect(producto)"
          >
            <td :class="obtenerClaseStock(producto)">
              {{ producto.stock }}
            </td>
            <td class="tdproducto">{{ producto.Producto }}</td>
            <td class="tdcategoria">{{ producto.ProductoCategoria }}</td>
            <td class="tdmarca">{{ producto.ProductoMarca }}</td>
            <td class="tprecio">{{ 
             $store.state.esOrdenCompra
                ? producto.PrecioCosto
                  ? producto.PrecioCosto
                  : 0
                : producto.PrecioContado
                  ? producto.PrecioContado
                  : 0
              
               }}</td>
            <td>
              <b-img 
                style="border-radius: 10px; width: 24px" 
                fluid 
                :src="producto.Imagen == null || producto.Imagen == '' ? 'static/no-photo.png' : '/resources/images/productos/' + producto.Imagen" 
                alt="" 
                @error="setAltImg" 
                class="img"
              ></b-img>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </section>
</template>


<script>
import config from "@/store/config.js";
import { mapState } from "vuex";

export default {
  data() {
    return {
      modalShow: "",
      search: "",
      API: config.API,
      lstProductos: [],
      producto: []
    };
  },
  mounted() {
    document.addEventListener("click", this.handleClickOutside);
    console.log(this.productos);
  },
  destroyed() {
    document.removeEventListener("click", this.handleClickOutside);
  },
  methods: {
    buscarProductos() {
      const config = {
        url: `${this.API}/productos/puntoventa/stock/1?q=${
          this.search
        }&sortBy=stock&sortDesc=1&limit=50`,
        method: "get"
      };
      this.axios(config).then(response => {
        this.lstProductos = response.data || [];
      });
    },
    setAltImg(event) {
      event.target.src = "static/no-photo.png";
    },
    handleClickOutside(event) {
      if (!this.$refs.lstProducto.contains(event.target)) {
        this.lstProductos = [];
      }
    },
    abrirModalSelect(item) {
      let prod = { ...item };
      if (this.productos.some(v => v.IdProducto == prod.IdProducto)) {
        this.$toasted.error("El producto ya se encuentra en la lista");
        return false;
      }
      const precio = this.$store.state.esOrdenCompra
        ? prod.PrecioCosto
          ? prod.PrecioCosto
          : 0
        : prod.PrecioContado
          ? prod.PrecioContado
          : 0;
      prod.PreciosPorProducto = prod.PreciosPorProducto
        ? JSON.parse(prod.PreciosPorProducto)
        : [];

      console.log(prod.PreciosPorProducto.length);
      prod.estadoPxP = prod.PreciosPorProducto.length == 0;
      prod.tienepresentacionflag = prod.PreciosPorProducto.length > 0;
      prod.cantidad = 1;
      prod.cantxPrecio = 1;
      prod.subtotal = precio;
      prod.total = precio;
      prod.precio = precio;
      prod.tipo = "UNIDAD";
      prod.descuento = 0;
      this.$store.commit("AGREGAR_PRODUCTO", prod);
      this.$nextTick(() => {
        this.lstProductos = [];
      });
    },
    obtenerClaseStock(producto) {
      const classStock = "tdstock ";
      if (producto.stock > (producto.StockMinimo || 0))
        return classStock + "stockSuccess";
      if (producto.stock <= 0) return classStock + "stockDanger";
      return classStock + "stockWarning";
    },
    startRecognition() {
      if (!("webkitSpeechRecognition" in window)) {
        alert("Lo siento, tu navegador no soporta la búsqueda por voz.");
        return;
      }

      this.recognition = new webkitSpeechRecognition();
      this.recognition.lang = "es-ES";
      this.recognition.continuous = false;
      this.recognition.interimResults = false;

      this.recognition.onstart = () =>
        console.log("Speech recognition service has started");

      this.recognition.onresult = event => {
        if (event.results.length > 0) {
          this.search = event.results[0][0].transcript;
        }
      };

      this.recognition.onerror = event => {
        console.error("Speech Recognition Error:", event);
        alert(`Error en el reconocimiento de voz: ${event.error}`);
      };

      this.recognition.onend = () =>
        console.log("Speech recognition service disconnected");

      try {
        this.recognition.start();
      } catch (error) {
        console.error("Error starting recognition:", error);
      }
    }
  },
  watch: {
    search() {
      this.buscarProductos();
    }
  },
  computed: {
    ...mapState({ productos: state => state.cart.productos })
  }
};
</script>


<style scoped>
section {
  margin: 0 5px;
  width: 900px;
}
.lstBusqueda {
  width: 900px;
  position: absolute;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  z-index: 99;
}
.img {
  min-width: 50px !important;
}
.img:hover {
  min-width: 100px !important;
}
.input {
  min-width: 100%;
  height: 38px;
  border: 1px solid #ced4da;
  border-radius: 5px;
  padding: 2px 10px;
}
.tblBusqueda {
  width: 900px;
  text-align: center;
  border: 2px solid #eaeaea !important;
  font-size: 16px;
  margin: 5px 0;
  padding: 20px 0;
  background: #fff;
}
.tblBusqueda > tbody {
  border: 1px solid #eaeaea !important;
}
.tblBusqueda > tbody > tr {
  padding: 10px !important;
  margin: 10px !important;
  border: 1px solid #eaeaea !important;
}

.tblBusqueda > tbody > tr:hover {
  background: #c3d5f5;
}
.tblBusqueda > tbody > tr > td {
  margin: 50px !important;
  border: 1px solid #eaeaea;
  padding: 10px 10px;
}
.tdstock {
  align-items: center;
  width: 60px;
  padding: 0 50px;
  font-weight: bold;
  border: 1px solid #eaeaea;
  margin: 5px !important;
}
.tdproducto {
  align-items: center;
  width: 400px;
  font-weight: bold;
}
.tdcategoria {
  align-items: center;
  width: 100px;
}
.tdmarca {
  align-items: center;
  width: 100px;
}

.tdimg {
  align-items: center;
}
.tprecio {
  background: rgba(185, 217, 255);
  align-items: center;
  font-weight: bold !important;
  padding: 0 50px !important;
  width: 65px;
}
.lstProducto {
  position: relative;
  z-index: 1;
  padding: 0;
}
.search {
  display: flex;
  align-items: center;
}
.search > .input {
  flex: 1;
  max-width: 100px;
  padding: 10px;
}
.search > b-button {
  margin-left: 10px;
}
.stockSuccess {
  background: rgb(196, 230, 204) !important;
}
.stockDanger {
  background: #b71c1c !important;
  color: #fff;
}
.stockWarning {
  background: orange !important;
}
</style>
