<template>
  <div>
    <Menu v-if="false"></Menu>
    <br />
    <b-form @submit="guardarMovimiento" @reset="onReset" @keyup="afe" validated Target="_blank">
        <b-container fluid class="bv-example-row">
          <b-row>
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
              <b-col md="4" v-if="almacen.IdAlmacen">
                <b-button variant="success" @click="confrontarTodo">Confrontar Todo ({{ almacen.Almacen }})</b-button>
                <hr>
                
              </b-col>
          </b-row>
          <hr>
          <b-row>
            <b-col></b-col>
            <b-col></b-col>
            <b-col>
              <b-form-group>
                <label>Agregar por categoria:</label>
                <b-form-select v-model="idCategoria" :options="categoriasSelect" v-on:change="setCategoria($event)" class="mb-3" size="md"/>
              </b-form-group>
            </b-col>
          </b-row>
          <b-row>  
            <b-col  md="12">
              <carrito-confrontacion 
                ref="carritoc"
                tipo="mov" :fields="carrito.fields" :ordenNormal="true"></carrito-confrontacion>
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
          //{ key: 'manoobra', label: 'Mano de obra', class:'text-center' },
          //{ key: 'stock', label: 'Stock', variant: 'info', class:'text-center' },
          { key: "CodigoBarra", label: "Codigo" },
          { key: "Producto" },
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
    async confrontarTodo() {
      try {
        const response = await this.axios.get(
          `${this.API}reporte/confrontacion/total`,
          {
            params: { idAlmacen: this.almacen.IdAlmacen }
          }
        );

        if (response.data && response.data.file) {
          const fileUrl = window.location.origin + response.data.file;
          window.open(fileUrl, "_blank");
          console.log(response.data);
        } else {
          this.$toasted.show("No se pudo generar el reporte.");
        }
      } catch (error) {
        this.$toasted.show("Error al generar el reporte.");
        console.error(error);
      }
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
        });
    },
    onReset(evt) {
      evt.preventDefault();
    },
    setAlmacenOrigen(obj) {
      this.almacen = obj;
      /*this.obtenerStok({
        IdAlmacen: !this.isTraslado?(this.movimiento.almacenDestino.IdAlmacen > 0 ? this.movimiento.almacenDestino.IdAlmacen : 1): (this.movimiento.almacenOrigen.IdAlmacen > 0 ? this.movimiento.almacenOrigen.IdAlmacen : 1) 
      })*/
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
            this.$toasted.success("Reporte de confrontación generado", {
              duration: 3000
            });

            // Abrir el archivo en una nueva pestaña
            if (response.data.file) {
              window.open(response.data.file, "_blank");
            }
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
