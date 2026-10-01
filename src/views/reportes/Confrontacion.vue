<template>
  <div class="ops-page">
    <Menu v-if="false"></Menu>
    <header class="ops-hero"><div class="ops-hero__icon"><i class="fas fa-balance-scale"></i></div><div><small>CONTROL DE EXISTENCIAS</small><h1>Confrontación de inventario</h1><p>Compara el stock físico con el sistema y genera los ajustes correspondientes.</p></div><span><i class="fas fa-check-double"></i> Verificación física</span></header>
    <b-form @submit="guardarMovimiento" @reset="onReset" @keyup="afe" validated Target="_blank">
        <b-container fluid class="ops-card">
          <b-row class="confront-filter-row">
              <b-col md="4">
                    <b-form-group>
                      <label>Almacen a Confrontar:</label>
                      <autocomplete
                        ref="inputAlmacenOrigen"
                        required="required"
                        initValue=""
                        :url="API + 'almacenes'"
                        anchor="Almacen"
                        label="writer"
                        placeholder=""
                        :on-select="setAlmacenOrigen"
                        :onFocus="focusAutocomplete.bind(null, 'inputAlmacenOrigen')"
                        :classes="{input: 'form-control'}">
                      </autocomplete>
                    </b-form-group>
              </b-col>
              <b-col md="4" v-if="almacen.IdAlmacen" class="ops-actions confront-export-all">
                <b-button variant="success" :href="'/api/index.php/reporte/confrontacion/total?idAlmacen=' + almacen.IdAlmacen" target="_blank"><i class="fas fa-file-excel"></i> Reporte completo</b-button>
              </b-col>
          </b-row>
          <b-row class="confront-category-row">
            <b-col md="4">
              <b-form-group>
                <label>Agregar por categoria:</label>
                <b-form-select v-model="idCategoria" :options="categoriasSelect" v-on:change="setCategoria($event)" class="mb-3" size="md"/>
              </b-form-group>
            </b-col>
            <b-col md="4" v-if="idCategoria" class="confront-active-category">
              <small>CATEGORÍA EN PROCESO</small><strong><i class="fas fa-tag"></i> {{ categoriaActual }}</strong>
            </b-col>
          </b-row>
          <b-row>  
            <b-col  md="12">
              <carrito-confrontacion 
                ref="carritoc"
                tipo="mov" :idAlmacen="almacen.IdAlmacen" :fields="carrito.fields" :ordenNormal="true"></carrito-confrontacion>
            </b-col>
          </b-row>
          <b-row align-h="center" class="text-center">
            <b-col cols="4">
              <b-button variant="success" size="lg" type="submit" :disabled="inProgress">Confrontar Inventario</b-button>
              <b-button variant="primary" size="md" :disabled="inProgress" @click="preConfrontar">Pre Confrontar (Listado)</b-button>
            </b-col>
          </b-row>
        </b-container>
        <br><br>
    </b-form>

  </div>
</template>

<script>
import Menu from "@/components/Menu";
import CarritoConfrontacion from "@/components/CarritoConfrontacion";
import { mapState, mapActions } from "vuex";
import config from "@/store/config.js";

export default {
  mounted() {
    this.getCategorias();
  },
  data() {
    return {
      inProgress: false,
      API: config.API,
      carrito: {
        fields: [
          { key: "stock", label: "Stock sistema", class: "text-center" },
          { key: "CodigoBarra", label: "Codigo" },
          { key: "Producto" },
          { key: "ProductoCategoria", label: "Categoria" },
          { key: "ProductoMarca", label: "Marca" },
          { key: "ProductoModelo", label: "Modelo" },
          { key: "cantidad", label: "Cantidad Fisica" },
          { key: "acciones", label: "" }
        ]
      },
      almacen: {},
      idCategoria: 0,
      categoriasSelect: [{ value: 0, text: "SELECCIONE" }]
    };
  },
  computed: {
    categoriaActual() {
      const categoria = this.categoriasSelect.find(item => Number(item.value) === Number(this.idCategoria));
      return categoria ? categoria.text : "";
    },
    ...mapState({
      proveedoresFiltrados: state => state.proveedoresFiltrados,
      almacenes: state => state.almacenesFiltrados,
      productos: state => state.cart.productos
    })
  },
  methods: {
    ...mapActions({
      buscarProveedores: "buscarProveedores",
      buscarAlmacenes: "buscarAlmacenes",
      limpiar: "limpiarCarrito",
      obtenerStok: "obtenerStock",
      add: "agregarProducto"
    }),
    afe(e) {
      return false;
    },
    getCategorias() {
      this.categoriasSelect = [];
      this.categoriasSelect.push({ value: 0, text: "SELECCIONE" });
      this.axios.get(this.API + "categorias?limit=300&q=").then(r => {
        if (r.data) {
          r.data.map(cat => {
            this.categoriasSelect.push({
              value: cat.IdProductoCategoria,
              text: cat.ProductoCategoria
            });
          });
        }
      });
    },
    setCategoria(val) {
      this.$store.state.cart.productos = [];
      this.axios
        .get(this.API + "productos/categoria/carga", {
          params: {
            IdCategoria: val
          }
        })
        .then(response => {
          this.idCategoria = 0;
          response.data.map(item => {
            this.add([item, () => {}, { setPrecioCosto: true }, false, false]);
          });
          this.$nextTick(() => {
            if (this.almacen.IdAlmacen) {
              this.obtenerStok({ IdAlmacen: this.almacen.IdAlmacen });
            }
          });
        });
    },
    onReset(evt) {
      evt.preventDefault();
    },
    setAlmacenOrigen(obj) {
      this.almacen = obj;
      if (this.productos.length) {
        this.obtenerStok({ IdAlmacen: obj.IdAlmacen });
      }
    },
    guardarMovimiento(evt) {
      evt.preventDefault();
      this.inProgress = true;

      if (!this.productos.length) {
        this.$toasted.show("No ha seleccionado ningun producto");
        this.inProgress = false;
        return;
      }

      let faltaCantidad = false;
      this.productos.forEach((prod, index) => {
        if (!(prod.cantidad > 0)) {
          faltaCantidad = true;
        }
      });

      /*if (faltaCantidad) {
        this.$toasted.show('Complete la cantidad de los productos ingresados')
        this.inProgress = false
        return;
      }*/

      this.axios
        .post(this.API + "reporte/confrontacion", {
          Productos: this.productos.map(p => ({
            IdProducto: p.IdProducto,
            cantidad: p.cantidad > 0 ? p.cantidad : 0
          })),
          IdProductos: this.productos.map(prod => prod.IdProducto),
          IdAlmacen: this.almacen.IdAlmacen
        })
        .then(response => {
          if (response.data.error) {
            this.$toasted.show(response.data.error);
          } else {
            this.limpiar();
            if (this.$refs.inputAlmacenOrigen) {
              this.$refs.inputAlmacenOrigen.setValue("");
            }
            this.almacen = {};
            this.$toasted.success("Reporte de confrontacion generado", {
              duration: 3000
            });
            document.open();
            document.write(response.data);
            document.close();
            setTimeout(() => {
              document.location.reload();
            }, 1500);
          }
          this.inProgress = false;
        })
        .catch(error => {
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
          this.inProgress = false;
        });
    },
    preConfrontar() {
      this.inProgress = true;

      if (!this.productos.length) {
        this.$toasted.show("No ha seleccionado ningun producto", {
          duration: 3000
        });
        this.inProgress = false;
        return;
      }
      if (!(this.almacen.IdAlmacen > 0)) {
        this.$toasted.show("No ha seleccionado ningun almacen", {
          duration: 3000
        });
        this.inProgress = false;
        return;
      }

      this.axios
        .post(this.API + "reporte/preconfrontacion", {
          Productos: this.productos.map(p => ({
            Producto: p.Producto,
            ProductoCategoria: p.ProductoCategoria,
            ProductoMarca: p.ProductoMarca,
            ProductoModelo: p.ProductoModelo,
            CodigoBarra: p.CodigoBarra,
            cantidad: p.cantidad > 0 ? p.cantidad : 0
          })),
          IdAlmacen: this.almacen.IdAlmacen
        })
        .then(response => {
          if (response.data.error) {
            this.$toasted.show(response.data.error);
          } else {
            this.limpiar();
            if (this.$refs.inputAlmacenOrigen) {
              this.$refs.inputAlmacenOrigen.setValue("");
            }
            this.almacen = {};
            this.$toasted.success("Reporte de preconfrontacion generado", {
              duration: 3000
            });
            document.open();
            document.write(response.data);
            document.close();
            setTimeout(() => {
              document.location.reload();
            }, 1500);
          }
          this.inProgress = false;
        })
        .catch(error => {
          this.$toasted.show("Ha ocurrido un error, vuelva a intentarlo");
          this.inProgress = false;
        });
    },
    focusAutocomplete(ref) {
      //this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick();
    }
  },
  components: {
    Menu,
    CarritoConfrontacion
  }
};
</script>
<style>
.input-group {
  /*margin: 0.25rem 0 !important;*/
}
</style>
