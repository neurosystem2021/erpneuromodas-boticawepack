<template>
  <div class="ops-page confront-page" :class="{ 'confront-mobile-collapsed': !mostrarCabeceraMovil }">
    <Menu v-if="false"></Menu>
    <button
      type="button"
      class="confront-mobile-header-toggle"
      :aria-expanded="mostrarCabeceraMovil ? 'true' : 'false'"
      @click="mostrarCabeceraMovil = !mostrarCabeceraMovil">
      <i :class="mostrarCabeceraMovil ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
      <span>{{ mostrarCabeceraMovil ? 'Ocultar cabecera y filtros' : 'Mostrar cabecera y filtros' }}</span>
      <i :class="mostrarCabeceraMovil ? 'fas fa-chevron-up' : 'fas fa-chevron-down'"></i>
    </button>
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
          <b-row align-h="center" class="text-center confront-submit-row">
            <b-col cols="12" md="8" lg="6" class="confront-submit-actions">
              <b-button class="confront-submit-primary" size="lg" type="submit" :disabled="inProgress"><i class="fas fa-check-double"></i><span class="confront-button-label-desktop">Confrontar Inventario</span><span class="confront-button-label-mobile">Confrontar</span></b-button>
              <b-button class="confront-submit-secondary" size="md" :disabled="inProgress" @click="preConfrontar"><i class="fas fa-clipboard-list"></i><span class="confront-button-label-desktop">Pre Confrontar (Listado)</span><span class="confront-button-label-mobile">Preconfrontar</span></b-button>
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
      mostrarCabeceraMovil: true,
      API: config.API,
      carrito: {
        fields: [
          { key: "CodigoBarra", label: "Codigo" },
          { key: "Producto" },
          { key: "cantidad", label: "Cantidad Fisica", class: "text-center" },
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
.confront-submit-row {
  margin-top: 24px;
}
.confront-submit-actions {
  display: flex;
  justify-content: center;
  gap: 10px;
}
.confront-submit-primary,
.confront-submit-secondary {
  border: 0 !important;
  color: #fff !important;
  background: linear-gradient(135deg, #043b35, #087363) !important;
  box-shadow: 0 8px 18px rgba(7, 82, 71, .2) !important;
  font-weight: 800 !important;
}
.confront-submit-actions .btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
}
.confront-button-label-mobile,
.confront-mobile-header-toggle {
  display: none;
}
@media (max-width: 767px) {
  .confront-mobile-header-toggle {
    display: flex;
    width: 100%;
    min-height: 42px;
    margin: 0 0 8px;
    padding: 8px 12px;
    align-items: center;
    justify-content: space-between;
    gap: 9px;
    border: 0;
    border-radius: 12px;
    color: #fff;
    background: linear-gradient(135deg, #043b35, #087363);
    box-shadow: 0 7px 17px rgba(7, 82, 71, .2);
    font-size: 12px;
    font-weight: 850;
  }
  .confront-mobile-header-toggle span {
    flex: 1;
    text-align: left;
  }
  .confront-mobile-collapsed > .ops-hero,
  .confront-mobile-collapsed .confront-filter-row,
  .confront-mobile-collapsed .confront-category-row {
    display: none !important;
  }
  .confront-mobile-collapsed .ops-card {
    padding-top: 10px !important;
  }
  .confront-submit-actions {
    flex-direction: row;
    gap: 7px;
  }
  .confront-submit-actions .btn {
    width: auto;
    min-width: 0;
    min-height: 46px;
    padding: 8px 7px;
    flex: 1 1 0;
    font-size: 11px !important;
    white-space: nowrap;
  }
  .confront-button-label-desktop {
    display: none;
  }
  .confront-button-label-mobile {
    display: inline;
  }
}
</style>
