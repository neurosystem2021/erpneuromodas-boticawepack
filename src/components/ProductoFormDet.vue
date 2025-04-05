<template>
  <div v-if="show">
    <b-container fluid>
        <b-row>{{  }}
          <b-col cols="6">
          <b-form-group>
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
        </b-col>
        <b-col cols="6">
          <b-input-group-append>
          <b-input-group>
          
          <input 
                  id="txtBarcode"
                  v-model="loadCode"
                  @keydown.enter.prevent="loadBarcode"
                  type="text"
                  class="form-control"
                  placeholder="Codigo de Barra" />
                  <b-button variant="primary" @click="loadBarcode"><i class="fas fa-barcode"></i></b-button>
            </b-input-group>
          </b-input-group-append>
        </b-col>
      </b-row>
    </b-container>
    <table class="table b-table table-sm">
      <thead>
        <tr>
          <th>Id</th>
          <th>Producto</th>
          <th>Cantidad</th>
          <th>Precio Costo</th>
          <th>Precio</th>
          <th class="text-center">Quitar</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(prod, index) in productos" :key="index">
          <td>{{ prod.IdProductoDet }}</td>
          <td>{{ prod.Producto }}</td>
          <td><input type="number" class="form-control" id="cantidad0" v-model="prod.Cantidad" requiredv @input="emitUpdate" /></td>
          <td><input type="number" class="form-control" v-model="prod.PrecioCosto" disabled requiredv /></td>
          <td><input type="number" class="form-control"  :class="{ 'input-error': prod.PrecioCosto  >  prod.Precio }" v-model="prod.Precio" requiredv @input="emitUpdate" /></td>
          <td class="text-center"><b-button variant="danger" size="sm" @click="quitarProducto(prod)">X</b-button></td>
        </tr>
        <tr>
          <td colspan="2" style="text-align: center;"><b>Total</b></td>
          <td>
            G. T.: 
            {{ 
            parseFloat(productos.reduce((sum,pro)=>{
                  return sum + parseFloat(pro.Precio*pro.Cantidad)
                },0)).toFixed(2) -
              parseFloat(productos.reduce((sum,pro)=>{
                  return sum + parseFloat(pro.PrecioCosto*pro.Cantidad)
                },0)).toFixed(2)

                
              }}
          </td>
          <td> <b>
              {{ 
                parseFloat(productos.reduce((sum,pro)=>{
                  return sum + parseFloat(pro.PrecioCosto*pro.Cantidad)
                },0)).toFixed(2)
              }}
              </b>
          </td>
          <td>
            <b>
              {{ 
                parseFloat(productos.reduce((sum,pro)=>{
                  return sum + parseFloat(pro.Precio*pro.Cantidad)
                },0)).toFixed(2)
              }}
              </b>
          </td>
        </tr>
      </tbody>
    </table>
  </div>    
</template>
<script>
import config from "@/store/config.js";

export default {
  mounted() {
    this.getProductosDet();
  },
  data() {
    return {
      show: false,
      API: config.API,
      productos: [],
      loadCode: ""
    };
  },
  props: ["idProducto", "padre"],
  methods: {
    emitUpdate() {
      this.$emit("onUpdateProductos", this.productos);
    },
    getProductosDet() {
      if (this.idProducto) {
        this.axios
          .get(this.API + "productosdet", {
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
        el => el.IdProductoDet == data.IdProducto
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
            data.IdProductoDet = data.IdProducto;
            data.Cantidad = 1;
            data.Precio = data.PrecioContado;
            this.productos.unshift(data);
            this.emitUpdate();
            let input = document.getElementById("cantidad0");
            this.$nextTick(() => {
              input.focus();
              input.select();
            });
          }
        });

      /*if (index < 0 && this.idProducto !== data.IdProducto && !data.EsPadre ) {
        data.IdProductoDet = data.IdProducto
        this.productos.push(data)
      } else {
        this.$toasted.show('no puede agregar este producto')
      }*/
    },
    quitarProducto(prod) {
      let index = this.productos.findIndex(
        el => el.IdProductoDet == prod.IdProductoDet
      );
      this.productos.splice(index, 1);
      this.emitUpdate();
    },
    focusAutocomplete(ref) {
      this.$refs[ref].type = "";
      this.$refs[ref].handleDoubleClick();
    },
    loadBarcode(e) {
      if (e) {
        e.preventDefault();
      }
      let idProducto = this.loadCode;

      if (idProducto) {
        let params = { codigoBarra: idProducto };
        this.axios
          .get(this.API + "productos/id/" + idProducto, { params: params })
          .then(response => {
            if (response.data.IdProducto) {
              let data = response.data;
              let index = this.productos.findIndex(
                el => el.IdProductoDet == data.IdProducto
              );

              if (index >= 0) {
                this.$toasted.show("El producto ya está agregado");
                return;
              }
              data.IdProductoDet = data.IdProducto;
              data.Cantidad = 1;
              data.Precio = data.PrecioContado;
              this.productos.unshift(data);
              this.emitUpdate();
              let input = document.getElementById("cantidad0");
              this.$nextTick(() => {
                input.focus();
                input.select();
              });
              this.loadCode = "";
            } else {
              this.loadCode = "";
              this.$toasted.show("No se encontró ningun producto");
            }
          });
      }
    }
  }
};
</script>
<style>
.form-control:focus {
  border-color: #1976d2; /* Azul como en Vuetify */
  box-shadow: 0 0 4px rgba(25, 118, 210, 0.5);
}
.input-error {
  border: 2px solid red !important;
  box-shadow: 0 0 6px rgba(255, 0, 0, 0.5);
}
</style>
