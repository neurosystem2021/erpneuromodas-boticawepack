<template>
<div>
    <b-button @click="show=true" variant="primary" class="code-multiple-trigger">
        <i class="fa fa-barcode" aria-hidden="true"></i>
        <span>{{ compact ? 'Códigos' : 'Códigos Múltiples' }}</span>
    </b-button>

    <b-form @submit="imprimirCodigoMulti"> 
        <b-modal id="modalCodigoMulti" 
            title="Códigos de Barras Múltiples"
            size="lg" v-model="show"
            header-bg-variant="dark"
            headerTextVariant="light"
            body-bg-variant="light"
            body-text-variant="dark"
            no-close-on-backdrop modal-class="nst-dialog" body-class="nst-dialog-body" footer-class="nst-dialog-footer" centered="">

            <b-row>
                <b-col md="6">
                    <b-form-group>
                        <b-input-group>
                        <autocomplete
                            ref="autocompleteProductoCode"
                            :url="API + 'productos'"
                            :onShouldRenderChild="renderChild"
                            :customParams="{sortBy: 'IdProducto', sortDesc: 0}"
                            anchor="Producto"
                            label="writer"
                            placeholder="Agregar Producto"
                            :on-select="agregarPro"
                            :onFocus="focusAutocomplete.bind(null, 'autocompleteProductoCode')"
                            :classes="{input: 'form-control'}">
                        </autocomplete>
                        </b-input-group>
                    </b-form-group>
                </b-col>
                <b-col md="6">
                    <b-input-group>
                        
                        <input 
                        id="txtBarcode"
                        ref="barcodeInput"
                        v-model="loadCode"
                        @keydown.enter.prevent="loadBarcode"
                        type="text"
                        class="form-control"
                        placeholder="Codigo de Barra" />
                        <b-input-group-append>
                            <b-button variant="primary" @click="loadBarcode"><i class="fas fa-search"></i></b-button>
                        </b-input-group-append>
                    </b-input-group>
                </b-col>
            </b-row>
            <b-row>
                <b-col md="12">
                    <table class="table table-striped b-table table-sm">
                        <thead>
                            <tr>
                                <th style="width:6%">CANTIDAD</th>
                                <th class="text-left">PRODUCTO</th>
                                <th class="text-center">MARCA</th>
                                <th class="text-center">MODELO</th>
                                <th class="text-center">CATEGORIA</th>
                                <th></th>
                            </tr>

                        </thead>
                        <tbody>
                            <tr v-for="(prod, index) in productos" :key="index">
                                <td style="width:6%"><input required min="1" type="number" class="form-control" v-model="prod.cantidad"/></td>
                                <td class="text-left">{{prod.Producto}}</td>
                                <td class="text-center">{{prod.ProductoMarca}}</td>
                                <td class="text-center">{{prod.ProductoModelo}}</td>
                                <td class="text-center">{{prod.ProductoCategoria}}</td>
                                <td><b-button variant="danger" size="sm" @click="quitarProducto(index)"><i class="fas fa-times fa-lg"></i></b-button></td>
                            </tr>
                        </tbody>
                    </table>
                </b-col>
            </b-row>
            <b-row>
                <b-col md="12" class="text-center">
                    <b-button :disabled="productos.length==0" variant="success" type="submit">Imprimir Códigos de Barra</b-button>
                </b-col>
            </b-row>
            <div slot="modal-footer" >
                <b-btn size="sm" variant="secondary" @click="show=false">
                    Cerrar
                </b-btn>
            </div>
        </b-modal>
    </b-form>
</div>
</template>
<script>
import config from "@/store/config.js";
export default {
  props: {
    compact: {
      type: Boolean,
      default: false
    }
  },
  mounted() {},
  data() {
    return {
      API: config.API,
      show: false,
      loadCode: "",
      productos: [],
    };
  },
  watch: {
    show(val) {
      if (val == true) {
        this.productos = [];
        this.$refs.autocompleteProductoCode.setValue("");
      }
    }
  },
  methods: {
    renderChild(data) {
      return `
                    <b>${data.Producto}</b> / ${data.ProductoCategoria} / ${
        data.ProductoMarca
      } / ${data.ProductoModelo} / ${data.ProductoTalla} / ${data.Color} / ${
        data.Genero
      } / ${data.Botapie}
                `;
    },
    loadBarcode(e) {
      if (e) {
        e.preventDefault();
      }
      let idProducto = this.loadCode;
      this.loadCode = "";
      this.$nextTick(() => {
        if (this.$refs.barcodeInput) this.$refs.barcodeInput.focus();
      });

      if (idProducto) {
        let params = { codigoBarra: idProducto };
        this.axios
          .get(this.API + "productos/id/" + idProducto, { params: params })
          .then(response => {
            if (response.data) {
              let producto = response.data;
              if (
                !this.productos.some(v => v.IdProducto == producto.IdProducto)
              ) {
                producto.cantidad = 1;
                this.productos.push(producto);
                this.$toasted.show("Producto añadido correctamente");
              } else {
                this.$toasted.error("El producto ya se encuentra en la lista");
              }
            } else {
              this.$toasted.error("No se encontró ningun producto");
            }
            // console.log(response.data);
          })
          .catch(() => {
            this.$toasted.error("No se pudo buscar el producto. Intente nuevamente");
          });
      }
    },

    focusAutocomplete(ref) {
      this.$refs[ref].type = "";
      this.$refs[ref].handleDoubleClick();
    },
    agregarPro(obj) {
      let index = this.productos.findIndex(
        prod => prod.IdProducto == obj.IdProducto
      );
      if (index >= 0) {
        this.$toasted.show("El producto ya está agregado");
        return;
      }
      obj.cantidad = 1;
      this.productos.push(obj);
      this.$refs.autocompleteProductoCode.setValue("");
    },
    quitarProducto(index) {
      this.productos.splice(index, 1);
    },
    imprimirCodigoMulti(evt) {
      evt.preventDefault();
      let finalArray = this.productos.map(er => {
        return [parseInt(er.IdProducto), parseInt(er.cantidad)];
      });
      let stringJson = JSON.stringify(finalArray);
      window.open("/views/codigoBarra.php?array=" + stringJson, "_blank");
    }
  }
};
</script>
<style>
#modalCodigoMulti .modal-dialog {
  max-width: 65% !important;
}
</style>
