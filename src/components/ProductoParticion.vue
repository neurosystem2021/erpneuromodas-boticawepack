<template>
  <div v-if="show">
    <b-form-group>
      <label>Particionar:</label>
      <b-input-group>
        <autocomplete
          :url="API + 'productos'"
          ref="inputProducto"
          anchor="Producto"
          :onShouldRenderChild="renderChild"
          label="writer"
          placeholder="Seleccionar Producto"
          :on-select="addProducto"
          :onFocus="focusAutocomplete.bind(null, 'inputProducto')"
          :classes="{input: 'form-control upper'}">
        </autocomplete>
      </b-input-group>
    </b-form-group>
    <table class="table b-table table-sm">
      <thead>
        <tr>
          <th>Id</th>
          <th>Producto</th>
          <th>KG</th>
          <th>Precio Venta</th>
          <th class="text-center">Quitar</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(prod, index) in productos" :key="index">
          <td>{{ prod.IdProductoParte }}</td>
          <td>{{ prod.Producto }}</td>
          <td><input type="number" class="form-control" disabled step="0.001" value="1"  /></td>
          <td><input type="number" class="form-control" disabled step="0.001" v-model="prod.PrecioContado"  /></td>
          <td class="text-center"><b-button variant="danger" size="sm" @click="quitarProducto(prod)">X</b-button></td>
        </tr>
      </tbody>
    </table>
  </div>    
</template>
<script>
import config from "@/store/config.js";

export default {
  mounted() {
    this.$nextTick(() => {
      console.log("component cargado");
    });
  },
  data() {
    return {
      show: false,
      API: config.API,
      productos: []
    };
  },
  watch: {
    show() {
      if (this.show) {
        this.getProductosDet();
      }
      console.log("show", this.show);
    }
  },
  props: ["idProducto"],
  methods: {
    emitUpdate() {
      this.$emit("onUpdateProductos", this.productos);
    },
    async getProductosDet() {
      if (this.idProducto) {
        await this.axios
          .get(this.API + "productospartes", {
            params: { idProducto: this.idProducto }
          })
          .then(res => {
            this.productos = res.data;
          })
          .catch(err => {
            console.log(err);
          });
      }
    },
    renderChild(data) {
      return `
        <b>${data.IdProducto} - ${data.Producto} - ${data.CodigoBarra}</b>
      `;
    },
    addProducto(data) {
      let index = this.productos.findIndex(
        el => el.IdProductoParte == data.IdProducto
      );

      if (index >= 0) {
        this.$toasted.show("El producto ya está agregado");
        return;
      }

      if (this.idProducto == data.IdProducto) {
        this.$toasted.show(
          "No puede agregar el mismo producto que esta editando"
        );
        return;
      }

      this.axios
        .get(this.API + "productos/espadre", {
          params: {
            idProducto: data.IdProducto
          }
        })
        .then(res => {
          let esPadre = res.data.esPadre;
          if (esPadre) {
            this.$toasted.show("No puede agregar un producto padre");
          } else {
            data.IdProductoParte = data.IdProducto;
            this.productos.push(data);
            this.emitUpdate();
          }
        });

      /*if (index < 0 && this.idProducto !== data.IdProducto && !data.EsPadre ) {
        data.IdProductoParte = data.IdProducto
        this.productos.push(data)
      } else {
        this.$toasted.show('no puede agregar este producto')
      }*/
    },
    quitarProducto(prod) {
      let index = this.productos.findIndex(
        el => el.IdProductoParte == prod.IdProductoParte
      );
      this.productos.splice(index, 1);
      this.emitUpdate();
    },
    focusAutocomplete(ref) {
      // this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick();
    }
  }
};
</script>
<style>
</style>
