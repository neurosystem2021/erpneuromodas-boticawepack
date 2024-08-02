<template>
    <section ref="lstProducto" class="lstProducto">
        <div class="search">
            <input type="text" class="input" placeholder="Agregar Productos" v-model="search" @focus="buscarProductos()">
            <b-button @click="startRecognition">
                <i class="fa fa-microphone"></i>
            </b-button>
        </div>
        <div class="lstBusqueda" v-if="lstProductos.length >0 ">
            <table class="tblBusqueda">
                <tbody>
                    <tr :class="obtenerClaseStock(producto)" v-for="(producto,index) in lstProductos" :key="index" @click="abrirModalSelect(producto)">
                        <td :class="obtenerClaseStock(producto)">
                        <!-- <td :class="{'tdstock': true, 'stockSuccess': producto.stock > 0, 'stockDanger': producto.stock <= 0}"> -->
                          {{ producto.stock }}
                        </td>
                        <td class="tdproducto">{{producto.Producto}}</td>
                        <td class="tdcategoria">{{producto.ProductoCategoria}}</td>
                        <td class="tdmarca">{{producto.ProductoMarca}}</td>
                        <td class="img">
                          
                          <b-img style="border-radius: 10px; width:24px" fluid :src="producto.Imagen == null || producto.Imagen==''?'static/no-photo.png': '/resources/images/productos/' + producto.Imagen" alt="" @error="setAltImg" class="img" ></b-img>	
                        </td>
                        <!-- <td class="table-primary">
                        </td> -->
                        <!-- <td class="tdimg">Img</td> -->
                    </tr>
                </tbody>
            </table>
        </div>
        
    </section>
</template>

<script>
import config from "@/store/config.js";
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
  },
  destroyed() {
    document.removeEventListener("click", this.handleClickOutside);
  },
  methods: {
    async buscarProductos() {
      let config = {
        url: `${this.API}/productos/puntoventa/stock/1?q=${
          this.search
        }&sortBy=stock&sortDesc=1&limit=10`,
        method: "get"
      };
      await this.axios(config).then(response => {
        let res = response;
        if (res.data === false) {
          this.lstProductos = [];
        } else {
          this.lstProductos = res.data;
        }
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
      this.lstProductos = [];
      let producto = { ...item };

      if (
        this.$store.state.cart.productos.some(
          v => v.IdProducto == producto.IdProducto
        )
      ) {
        this.$toasted.error(
          `El producto ${producto.Producto} ya se encuentra en la lista`
        );
        return false;
      }

      if (producto.PreciosPorProducto) {
        producto.PreciosPorProducto = JSON.parse(producto.PreciosPorProducto);
        producto.estadoPxP = false;
        producto.tienepresentacionflag = true;
      } else {
        producto.estadoPxP = true;
        producto.PreciosPorProducto = [];
        producto.tienepresentacionflag = false;
      }
      this.$store.state.producto = producto;

      this.$store.state.modalNuevoProducto = true;
      this.$emit("monto", "");
      this.$emit("modal", false);
      // console.log(item);
    },
    obtenerClaseStock(producto) {
      let classStock = "tdstock ";
      if (producto.stock > (producto.StockMinimo ? producto.StockMinimo : 0)) {
        return classStock + " stockSuccess";
      }
      if (producto.stock <= 0) {
        return classStock + " stockDanger";
      }
      if (producto.stock < (producto.StockMinimo ? producto.StockMinimo : 0)) {
        return classStock + " stockWarning";
      }
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

      this.recognition.onstart = () => {
        console.log("Speech recognition service has started");
      };

      this.recognition.onresult = event => {
        if (event.results.length > 0) {
          this.search = event.results[0][0].transcript;
        }
      };

      this.recognition.onerror = event => {
        console.error("Speech Recognition Error:", event);
        alert(`Error en el reconocimiento de voz: ${event.error}`);
      };

      this.recognition.onend = async () => {
        console.log("Speech recognition service disconnected");
        console.log("query", this.query);
        // if (this.query) {
        //   alert(`Dijiste: ${this.query}`);
        // } else {
        //   alert("No se pudo captar ninguna palabra");
        // }
      };

      try {
        this.recognition.start();
      } catch (error) {
        console.error("Error starting recognition:", error);
        // alert("No se pudo iniciar el reconocimiento de voz.");
      }
    }
  },
  watch: {
    search() {
      this.buscarProductos();
    }
  }
};
</script>

<style scope>
section {
  margin: 0 5px;
  /* background-color: #fff !important; */
  width: 830px;
  /* z-index: 1; */
}
.lstBusqueda {
  width: 800px;
  position: absolute;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  z-index: 99;
}
.img:hover {
  transform: scale(1.8);
}
.input {
  min-width: 100%;
  height: 38px;
  border: 1px solid #ced4da;
  border-radius: 5px;
  padding: 2px 10px;
}
.tblBusqueda {
  width: 800px;
  text-align: center;
  border: 1px solid #ced4da;

  font-size: 13px;
  margin-bottom: 5px;
  margin-top: 5px;
  background: #fff;
}
.stockSuccess {
  color: #1a237e !important;
  /* color: white; */
}
.stockDanger {
  color: #b71c1c !important;
  /* color: white; */
}
.stockWarning {
  color: orange !important;
  /* color: white; */
}
.tblBusqueda > tbody > tr {
  font-weight: bold;
  padding: 10px !important;
  border: 1px solid #eaeaea;
}
.tblBusqueda > tbody > tr:hover {
  background: #c3d5f5;
  border: 1px solid #eaeaea;
  /* color: #fff; */
}
.tblBusqueda > tbody > tr > td {
  border: 1px solid #eaeaea;
  padding: 5px 10px;
}
.tdstock {
  align-items: center;
  width: 60px;
}
.tdproducto {
  align-items: center;
  width: 400px;
}
.tdcategoria {
  align-items: center;
  width: 100px;
}
.tdmarca {
  align-items: center;
  width: 100px;
}
.tdprecio {
  align-items: center;
  /* background: #82b1ff; */
  width: 65px;
}
.tdimg {
  align-items: center;
}
.lstProducto {
  position: relative; /* Asegura que el posicionamiento sea relativo */
  z-index: 1; /* Ajusta el z-index para estar por encima de otros elementos */
  padding: 0 0;
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
</style>
