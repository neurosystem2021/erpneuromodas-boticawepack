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
                <b-col cols="1">
                    Costo
                     <input type="number" class="form-control" v-model="precio">
                </b-col>
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
                <b-col cols="3">
                    <b-button @click="obtenerListado()" variant="success"> Generar </b-button>  | btn pdf  | btn excel
                </b-col>
            </b-row>
           
        </b-col>
        <b-col cols="12">
            <table width="100%" class="table table-hover">
                <tbody>
                    <tr v-for="(lst,index) in lstReporte.filter(v=> v.d1!=' ')" :key="index">
                        <td>{{ lst.d1 }}</td>
                        <td>{{ retornarDecimal(lst.d2) }}</td>
                        <td>{{ retornarDecimal(lst.d3) }}</td>
                        <td>{{ retornarDecimal(lst.d4) }}</td>
                        <td>{{ retornarDecimal(lst.d5) }}</td>
                        <td>{{ retornarDecimal(lst.d6) }}</td>
                        <td>{{ retornarDecimal(lst.d7) }}</td>
                        <td>{{ retornarDecimal(lst.d8) }}</td>
                        <td>{{ retornarDecimal(lst.d9) }}</td>
                        <td>{{ retornarDecimal(lst.d10) }}</td>
                        <td>{{ retornarDecimal(lst.d11) }}</td>
                    </tr>
                </tbody>
            </table>
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
        { value: "02", text: "FEBREO" },
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
      console.log(this.producto);
      await this.axios(`${this.API}producto/listarstockvalorizado`, {
        params: {
          producto: this.producto.Producto,
          stock: this.stock,
          precio: this.precio,
          anno: this.anno,
          Tipo: this.Tipo
        }
      }).then(response => {
        this.lstReporte = response.data;
        if (this.mes) {
          this.generarListado();
          return;
        }
        console.log(response.data);
      });
    },
    generarListado() {},
    retornarDecimal(valor) {
      const numero = parseFloat(valor);

      if (!isNaN(numero) && isFinite(numero)) {
        return parseFloat(numero.toFixed(2));
      }

      return valor;
    }
  },

  computed: {
    APIProductos() {
      return this.API + "producto/inv";
    }
  }
};
</script>

<style>
</style>
