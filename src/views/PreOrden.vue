<template>
  <b-container>
    <b-row>
        <b-col cols="4"> 
            Almacén
            <b-form-select v-model="idAlmacen" 
                 value-field="IdAlmacen"
                 text-field="Almacen"
                :options="lstAlmances"></b-form-select>
        </b-col>
        <b-col cols="4"> 
            Fecha:
            <input type="date" v-model="fecha" class="form-control" disabled>
        </b-col>
        <b-col>
            <b-button @click="guardar()" variant="success" :disabled="distabledBtnGuardar" class="mt-4">Guardar</b-button>
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
        <b-col cols="7" v-if="idAlmacen" class="mt-2"> 
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
        </b-col>
        <b-col cols="5" v-if="idAlmacen" class="mt-2">
            <input v-model="loadCode"
                  @keydown.enter.prevent="loadBarcode"
                   ref="barcodeInput"
                  type="text"
                  class="form-control"
                  placeholder="Codigo de Barra" />
        </b-col>
        <b-col cols="12">
            <table class="table mt-2" width="100%">
                <thead>
                    <tr>
                        <th>Producto </th>
                        <th>Cantidad </th>
                        <th>Precio </th>
                        <th>Tot. </th>
                        <th>Lote. </th>
                        <th>FechaVencimiento. </th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(producto,index) in productos" :key="index">
                        <td> {{ producto.Producto }}</td>
                        <td> 
                            <input
                                :id="`Cantidad${index}`"
                                type="number"
                                style="width: 90px"
                                min="0"
                                v-model="producto.Cantidad"
                                placeholder="Cantidad"
                                class="form-control"
                            >
                        </td>
                        <td> {{ producto.PrecioContado }}  </td>
                        <td>
                            {{ parseFloat(producto.Cantidad*producto.PrecioContado).toFixed(2) }}
                        </td>
                        <td></td>
                        <td></td>
                        <td>
                            <b-button variant="danger" @click="eliminarProducto(index)"><i class="fa fa-trash" aria-hidden="true"></i></b-button>
                        </td>

                    </tr>
                </tbody>
            </table>
        </b-col>
        <b-col cols="4" class="mt-5">
            <table class="table">
                <tr>
                    <td style="background: #1A237E; color: white; font-weight: bold;">Total</td>
                    <td style="background: #C5CAE9;  font-weight: bold;">{{ totalCarrito }}</td>
                </tr>
            </table>
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
      idAlmacen: "",
      lstAlmances: [],
      fecha: "",
      cliente: {},
      productos: [],
      loadCode: ""
    };
  },
  mounted() {
    this.fecha = this.$moment().format("YYYY-MM-DD");
    this.obtenerAlmance();
  },
  methods: {
    async obtenerAlmance() {
      await this.axios(`${this.API}almacenes`).then(res => {
        if (res.data) {
          this.lstAlmances = res.data;
        }
      });
    },
    focusAutocomplete(ref) {
      this.$refs[ref].type = "";
      this.$refs[ref].handleDoubleClick();
    },
    setCliente(obj) {
      console.log(obj);
      this.cliente = obj;
    },
    setProducto(obj) {
      if (this.productos.some(v => v.IdProducto == obj.IdProducto)) {
        this.$toasted.error("El producto ya se encuentra en la lista");
        return false;
      }
      obj.Cantidad = 1;
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
      let data = {
        Cliente: this.cliente,
        productos: this.productos
      };
      await this.axios.post(`${this.API}/preorden/insert`, data).then(res => {
        if (res.data) {
          window.open(
            "/imprimir/preorden.php?id=" + res.data.insertId,
            "_blank"
          );
          setTimeout(() => {
            window.location.reload();
          }, 100);
        }
      });
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
          return sum + parseFloat(producto.Cantidad * producto.PrecioContado);
        }, 0);
      }
      return parseFloat(monto).toFixed(2);
    },
    distabledBtnGuardar() {
      if (!this.idAlmacen) {
        return true;
      }
      if (!Object.keys(this.cliente).length > 0) {
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

<style>
</style>
