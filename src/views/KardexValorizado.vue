<template>
  <b-container fluid>
    <b-row>
        <b-col cols="12">
            <b-row>
                <b-col cols="1">
                    Año
                    <input type="number" class="form-control" v-model="anno">
                </b-col>
                <b-col cols="2">
                    Producto
                    <autocomplete
                        ref="autocompleteProducto"
                        :url="APIProductos"
                        :onShouldRenderChild="renderChild"
                        anchor="Producto"
                        label="writer"
                        placeholder="Agregar Producto"
                        :on-select="setProducto"
                        :onFocus="focusAutocomplete.bind(null, 'autocompleteProducto')"
                        :classes="{input: 'form-control'}">
                    </autocomplete>
                </b-col>
                <b-col cols="1">
                    Stock Inicial
                     <input type="number" class="form-control" v-model="stock">
                </b-col>
                <!-- <b-col cols="1">
                    Costo
                     <input type="number" class="form-control" v-model="precio">
                </b-col> -->
                <b-col cols="2">
                    Mes:
                    <select class="form-control" v-model="Mes">
                        <option value="" selected disabled> -- Seleccione un mes -- </option>
                        <option :value="mes.value" v-for="(mes,index) in mounth" :key="index">
                            {{ mes.text }}
                        </option>
                    </select>
                    <!-- {{ mounth }} -->
                </b-col>
                <b-col cols="3" class="py-3">
                    <b-button @click="obtenerListado()" variant="info">
                      <i class="fa fa-search" aria-hidden="true"></i> Generar
                    </b-button> 
                    
                    <b-button @click="exportPDF()" variant="danger" :disabled="disableEXCEL">
                      <i class="fa fa-file-pdf" aria-hidden="true"></i> Generar PDF
                    </b-button> 

                    <b-button @click="exportEXCEL()" variant="success" :disabled="disableEXCEL">
                      <i class="fa fa-file-excel" aria-hidden="true"></i> Generar EXCEL
                    </b-button> 
                </b-col>
            </b-row>
           
        </b-col>
        <b-col cols="12" class="my-3">
            <b-table :items="lstReporte" :fields="fields">
                <template slot="TotalMovimiento" slot-scope="data">
                  {{ parseFloat(data.item.CantidadMovimiento * data.item.PrecioMovimiento).toFixed(2) }}
                </template>
                <template slot="TotalVenta" slot-scope="data">
                  {{ parseFloat(data.item.CantidadVenta * data.item.PrecioVenta).toFixed(2) }}
                </template>
            </b-table>
            <!-- {{ lstReporte }} -->
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
      Tipo: 0,
      anno: "",
      Mes: "",
      mounth: [
        { value: "01", text: "ENERO" },
        { value: "02", text: "FEBRERO" },
        { value: "03", text: "MARZO" },
        { value: "04", text: "ABRIL" },
        { value: "05", text: "MAYO" },
        { value: "06", text: "JUNIO" },
        { value: "07", text: "JULIO" },
        { value: "08", text: "AGOSTO" },
        { value: "09", text: "SEPTIEMBRE" },
        { value: "10", text: "OCTUBRE" },
        { value: "11", text: "NOVIEMBRE" },
        { value: "12", text: "DICIEMBRE" }
      ],
      stock: 0,
      precio: 0,
      idAlmacen: 1,
      producto: {},
      fields: [
        { key: "Producto", label: "Producto" },
        { key: "TipoDoc", label: "Tipo Doc" },
        { key: "Serie", label: "Serie" },
        { key: "Numero", label: "Numero" },
        { key: "Numero", label: "Numero" },
        { key: "CantidadMovimiento", label: "Entrada" },
        { key: "PrecioMovimiento", label: "Precio" },
        { key: "TotalMovimiento", label: "Total", class: "clsTotalMovimiento" },
        { key: "CantidadVenta", label: "Salida" },
        { key: "PrecioVenta", label: "Precio" },
        { key: "TotalVenta", label: "Total", class: "clsTotalVenta" },
        { key: "Saldo", label: "Saldo", class: "clsSaldo" }
      ],
      lstReporte: []
    };
  },
  mounted() {
    this.anno = this.$moment().format("YYYY");
  },
  methods: {
    renderChild(data) {
      return `
          <b>${data.Producto}</b>
        `;
    },
    setProducto(obj) {
      this.producto = obj;
    },
    focusAutocomplete(ref) {
      this.$refs[ref].type = "";
      this.$refs[ref].handleDoubleClick();
    },
    async obtenerListado() {
      if (!Object.keys(this.producto).length > 0) {
        this.$toasted.show("Seleccione un producto.");
        return;
      }
      await this.axios(`${this.API}producto/listarstockvalorizado`, {
        params: {
          producto: this.producto.Producto,
          stock: this.stock,
          precio: this.precio,
          anno: this.anno,
          mes: this.Mes,
          Tipo: this.Tipo
        }
      }).then(response => {
        this.generarListado(response.data);
      });
    },
    generarListado(datos) {
      this.lstReporte = [];
      // Asegúrate de que this.stock sea un número desde el inicio
      let val = parseFloat(this.stock) || 0;

      datos.forEach(element => {
        // 1. Manejo de Movimiento (Suma)
        if (parseFloat(element.CantidadMovimiento) > 0) {
          val += parseFloat(element.CantidadMovimiento); // Suma numérica pura

          this.lstReporte.push({
            ...element,
            Saldo: val.toFixed(2) // Solo aquí convertimos a string para la vista
          });
        }

        // 2. Manejo de Venta (Resta)
        if (parseFloat(element.CantidadVenta) > 0) {
          val -= parseFloat(element.CantidadVenta); // Resta numérica pura

          this.lstReporte.push({
            ...element,
            Saldo: val.toFixed(2) // Solo aquí convertimos a string para la vista
          });
        }
      });
    },
    retornarDecimal(valor) {
      if (typeof valor === "number") {
        return parseFloat(valor.toFixed(2));
      }

      if (typeof valor === "string" && /^-?\d+(\.\d+)?$/.test(valor.trim())) {
        const numero = parseFloat(valor);
        return parseFloat(numero.toFixed(2));
      }

      return valor;
    },
    getClass(item) {
      let clasName = "";
      let value = item.d1.trim();
      switch (value) {
        case "KARDEX VALORIZADO":
          clasName = "clsTitle";
          break;
        case "PRODUCTO":
          clasName = "clsProducto";
          break;
        case "AÑO":
          clasName = "clsAnno";
          break;
        case "DESDE":
          console.log(item.d1);
          clasName = "clsDesde";
          break;
        case "HASTA":
          console.log(item.d1);
          clasName = "clsHasta";
          break;
        case "FECHA":
          clasName = "clsFecha";
          break;

        default:
          clasName = "";
          break;
      }
      return clasName;
    },
    async exportEXCEL() {
      let data = {
        data: this.lstReporte,
        producto: this.producto,
        anno: this.anno,
        Mes: this.Mes,
        stock: this.stock
      };
      await this.axios
        .post(`${this.API}producto/listarstockvalorizado/excel`, data)
        .then(res => {
          let url = res.data;
          url = url.replace(/\\/g, "");
          if (!url.startsWith("http://") && !url.startsWith("https://")) {
            url = "http://" + url; // O "https://" si es necesario
          }
          window.open(url, "_blank");
        });
    },
    async exportPDF() {
      let data = {
        data: this.lstReporte,
        stock: this.stock,
        precio: this.precio,
        anno: this.anno,
        mes: this.Mes,
        Tipo: this.Tipo
      };
      await this.axios
        .post(`${this.API}reporte/kardexvalorizadopdf`, data)
        .then(res => {
          if (res.data) {
            let url = res.data.url;
            url = url.replace(/\\/g, "");
            if (!url.startsWith("http://") && !url.startsWith("https://")) {
              url = "http://" + url; // O "https://" si es necesario
            }
            console.log(url);
            window.open(url, "_blank");
          }
        });
    }
  },

  computed: {
    APIProductos() {
      return this.API + "producto/inv";
    },
    disableEXCEL() {
      if (this.lstReporte.length == 0) {
        return true;
      }
      return false;
    }
  }
};
</script>

<style scoped>
/* .clsTitle {
  background: #5c6bc0;
  font-weight: bold;
  color: white;
}
.clsProducto {
  background: #7986cb;
}
.clsAnno {
  background: #9fa8da;
}
.clsDesde {
  background: #c5cae9;
}
.clsHasta {
  background: #c5cae9;
}
.clsFecha {
  background: #5c6bc0;
  font-weight: bold;
  color: white;
} */
</style>
<style>
.clsTotalMovimiento {
  background: #e8f5e9 !important;
}
.clsTotalVenta {
  background: #fff9c4 !important;
}
.clsSaldo {
  background: #e3f2fd !important;
}
</style>
