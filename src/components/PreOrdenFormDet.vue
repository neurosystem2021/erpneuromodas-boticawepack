<template>
  <div>
    <b-form-group>
      <label>Agrega Productos:</label>
      <b-input-group>
        <autocomplete
          :url="API + 'productos'"
          ref="inputProducto"
          anchor="Producto"
          :onShouldRenderChild="renderChild"
          label="writer"
          style="width:200px"
          placeholder="Seleccionar Producto"
          :on-select="addProducto"
          :onFocus="focusAutocomplete.bind(null, 'inputProducto')"
          :classes="{input: 'form-control upper'}">
        </autocomplete>
        <input v-model="loadCode"
        @keydown.enter.prevent="loadBarcode"
        type="text"
        style="width:20px"
        class="form-control"
        placeholder="Codigo de Barra" />
      </b-input-group>
    </b-form-group>
    <table class="table b-table table-sm">
      <thead>
        <tr>
          <th>Mano de Obra</th>
          <th>Id</th>
          <th>Producto</th>
          <th>Precio</th>
          <th>Cantidad</th>
          <th>Total</th>
          <th>Pagado</th>
          <th>Deuda</th>
          <th class="text-center">Estado</th>
          <th class="text-center">Quitar</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(prod, index) in productos" v-show="!prod.isDisabled" :key="index" :class="{'bg-secondary text-white': prod.isDisabled,'bg-facturado':prod.Pagado}">
          <td>
            <input 
            @change="(e) => addManoDeObra(index, e)"
            v-if="prod.CodigoBarra != 'MANODEOBRA'" 
            v-model="prod.EsManoDeObra"
            :disabled="prod.Pagado"
            class="form-check-input" type="checkbox" />
          </td>
          <td>{{ prod.IdProducto }}</td>
          <td>{{ prod.Producto }}</td>
          <td>
            <span class="hideInModal">{{ prod.Precio }}</span>
            <input :disabled="prod.Pagado ==1" step="0.01" required type="number" class="form-control" v-model="prod.Precio" @input="emitUpdate" />
          </td>
          <td>
            <span class="hideInModal">{{ prod.Cantidad }}</span> 
            <input :disabled="prod.isDisabled ==1 || prod.Pagado==1" required type="number" class="form-control" v-model="prod.Cantidad" @input="emitUpdate" />
          </td>
          <td>
            <span>{{parseFloat(prod.Precio * prod.Cantidad).toFixed(2)}}</span>
            
          </td>
          <td>{{ prod.MontoPagado }}</td>
          <td>
            <span>{{parseFloat(prod.Precio * prod.Cantidad).toFixed(2) - prod.MontoPagado }}</span>
            
          </td>
          <td>
             {{ prod.Pagado==1 ? 'Facturado' : ''}}
          </td>
          <td class="text-center"><b-button :disabled="prod.isDisabled==1" variant="danger" size="sm" @click="quitarProducto(prod)">X</b-button></td>
        </tr>
      </tbody>
    </table>
  </div>    
</template>
<script>
import config from "@/store/config.js";

export default {
  mounted() {
    this.getProductos();
  },
  data() {
    return {
      API: config.API,
      productosACargar: [],
      productos: [],
      loadCode: ""
    };
  },
  props: ["idPreorden", "idProducto"],
  computed: {
    sumProductos() {
      var sum = 0;
      this.productos.map(prod => {
        if (prod.EsManoDeObra == 0) {
          sum += prod.Cantidad * prod.Precio || 0;
        }
      });
      return sum;
    },
    manoDeObraBruta() {
      let pManoObra = this.productos.find(p => p.CodigoBarra === "MANODEOBRA");
      let precio = pManoObra ? pManoObra.PrecioMO : 0;
      return precio;
    },
    manoDeObraMenosMat() {
      let pManoObra = this.productos.find(p => p.CodigoBarra === "MANODEOBRA");
      let precio = pManoObra ? pManoObra.PrecioMOmM : 0;
      return precio;
    }
  },
  watch: {
    productos(prods) {
      let pi = this.productos.findIndex(p => p.CodigoBarra === "MANODEOBRA");

      if (pi >= 0 && !this.productos[pi].Precio) {
        this.productos[pi].Precio = this.productos[pi].Precio;
      }
    }
  },
  methods: {
    emitUpdate() {
      this.$emit("onUpdateProductos", this.productos);
    },
    getProductos() {
      if (this.idPreorden) {
        this.axios
          .get(this.API + "preorden/detalle", {
            params: { idPreOrden: this.idPreorden, sortDesc: "DEC" }
          })
          .then(res => {
            this.productos = res.data.map(item => {
              item.PrecioMO = item.Precio;
              item.PrecioMOmM = item.Precio;
              item.isDisabled = 0;
              if (item.IdProducto === this.idProducto) {
                item.isDisabled = true;
              }
              return item;
            });

            this.productosACargar.map(pc => {
              let producto = this.productos.find(
                pr => pr.IdProducto === pc.IdProducto
              );
              if (!producto) {
                this.productos.push(pc);
              }
            });
            this.emitUpdate();
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
    loadBarcode(e) {
      e.preventDefault();
      let idProducto = this.loadCode;

      if (idProducto) {
        let params = { codigoBarra: idProducto };
        this.axios
          .get(this.API + "productos/id/" + idProducto, { params: params })
          .then(response => {
            if (response.data.IdProducto) {
              this.addProducto(response.data);
              this.loadCode = "";
            } else {
              this.loadCode = "";
              this.$toasted.show("No se encontró ningun producto");
            }
          });
      }
    },
    addProducto(data) {
      let index = this.productos.findIndex(
        el => el.IdProducto == data.IdProducto
      );

      if (index >= 0) {
        this.$toasted.show("El producto ya está agregado");
        return;
      }

      if (data.EsHabitacion) {
        this.$toasted.show("No puede ingresar este producto");
        return;
      }

      data.Precio = data.PrecioContado;
      data.cantidad = 1;
      data.Cantidad = 1;

      this.productos.push(data);
      this.$toasted.success("Producto agregado");
      this.emitUpdate();
    },
    quitarProducto(prod) {
      let index = this.productos.findIndex(
        el => el.IdProducto == prod.IdProducto
      );
      this.productos.splice(index, 1);
      this.emitUpdate();
    },
    focusAutocomplete(ref) {
      // this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick();
    },
    addManoDeObra(index, e) {
      let pi = this.productos.findIndex(p => p.CodigoBarra === "MANODEOBRA");
      if (pi < 0) {
        this.$toasted.error("Primero seleccione el producto MANODEOBRA");

        this.productos[index].EsManoDeObra = 0;
        return;
      }

      if (this.productos[index].EsManoDeObra) {
        e.target.parentNode.parentNode.style.backgroundColor = "#e7c4ff";
        this.productos[pi].PrecioMOmM -=
          parseFloat(this.productos[index].Precio) *
          parseFloat(this.productos[index].Cantidad);
        this.productos[index].disabled = true;
      } else {
        e.target.parentNode.parentNode.removeAttribute("style");
        this.productos[pi].PrecioMOmM +=
          parseFloat(this.productos[index].Precio) *
          parseFloat(this.productos[index].Cantidad);
        this.productos[index].disabled = false;
      }
      this.emitUpdate();
    },
    updateManoDeObraNeta(index) {
      let pManoObra = this.productos.find(p => p.CodigoBarra === "MANODEOBRA");
      let precio = pManoObra ? pManoObra.precio : 0;

      this.productos.filter(p => p.EsManoDeObra).map(p => {
        precio -= parseFloat(p.precio) * parseFloat(p.Cantidad);
      });

      let pi = this.productos.findIndex(p => p.CodigoBarra === "MANODEOBRA");

      if (pi >= 0 && index !== pi) {
        this.productos[pi].precio = precio;
      }
    }
  }
};
</script>
<style scoped>
.hideInModal {
  display: none;
}
.form-check-input {
  margin-left: 0.75rem !important;
}
.bg-facturado {
  background: #dcedc8 !important;
}
</style>
