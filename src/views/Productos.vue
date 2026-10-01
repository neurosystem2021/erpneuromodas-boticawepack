<template>
  <div class="product-catalog-page">
    <b-container fluid class="product-catalog-shell">
      <section class="product-catalog-card">
        <header class="product-catalog-header">
          <div class="product-catalog-heading">
            <span class="product-catalog-heading__icon"><i class="fas fa-boxes"></i></span>
            <div>
              <span class="product-catalog-eyebrow">LLANTAS Y BATERÍAS</span>
              <h1>Catálogo de productos</h1>
              <p>Consulta categoría, marca, modelo y stock en un solo lugar.</p>
            </div>
          </div>
          <div class="product-catalog-header__actions">
            <div class="product-catalog-counter">
              <strong>{{ totalRows }}</strong>
              <span>artículos registrados</span>
            </div>
            <b-button class="product-catalog-new" @click="nuevoProducto">
              <i class="fas fa-plus"></i>
              <span>Nuevo producto</span>
            </b-button>
          </div>
        </header>

        <div class="product-catalog-toolbar">
          <div class="product-catalog-search">
            <span class="product-catalog-search__icon"><i class="fas fa-search"></i></span>
            <b-form-input
              v-model="filter"
              placeholder="Buscar por código, producto, categoría, marca o modelo..."
              @input="currentPage = 1"
            />
            <button type="button" class="product-catalog-search__clear"
              :disabled="!filter" @click="filter = ''" aria-label="Limpiar búsqueda">
              <i class="fas fa-times"></i><span>Limpiar</span>
            </button>
          </div>
          <div class="product-catalog-limit">
            <label for="catalog-page-size">Mostrar</label>
            <b-form-select id="catalog-page-size" :options="pageOptions"
              v-model="perPage" @input="currentPage = 1"/>
            <span>por página</span>
          </div>
          <div class="product-catalog-multiple">
            <codigo-multiple></codigo-multiple>
          </div>
        </div>

        <div class="product-catalog-table-wrap">
          <b-table small
            ref="tablaProductos"
            :filter="filter"
            :sort-by.sync="sortBy"
            :sort-desc.sync="sortDesc"
            striped hover
            @row-clicked="rowClick"
            id="my-table"
            :busy.sync="isBusy"
            :items="myProvider"
            :fields="fields"
            :per-page="perPage"
            :current-page="currentPage"
            class="product-catalog-table">
            <template slot="stock" slot-scope="data">
              <span v-if="data.item.stock === null" class="text-muted">—</span>
              <b-badge v-else :variant="Number(data.item.stock) > 0 ? 'success' : 'danger'">
                {{ data.item.stock }}
              </b-badge>
            </template>
            <template slot="empty">
              <div class="product-catalog-empty">
                <span><i class="fas fa-box-open"></i></span>
                <strong>No encontramos productos</strong>
                <small>Prueba con otra palabra o registra un nuevo artículo.</small>
              </div>
            </template>
          </b-table>
        </div>

        <footer class="product-catalog-footer">
          <div class="product-catalog-footer__summary">
            Página <strong>{{ currentPage }}</strong> · {{ totalRows }} artículos
          </div>
          <b-pagination :total-rows="totalRows" :per-page="perPage"
            v-model="currentPage" class="product-catalog-pagination my-0" />
        </footer>
      </section>
      <producto-form ref="productoForm" :producto-update="productoSelect" :cbGuardar="actualizarTabla"></producto-form>
    </b-container>
  </div>
</template>

<script>
import config from "@/store/config.js";
import ProductoForm from "@/components/ProductoForm";
import CodigoMultiple from "@/components/CodigoMultiple.vue";

export default {
  mounted() {
    this.axios.get(this.API + "productos/count").then(response => {
      this.totalRows = response.data.total;
    });
  },
  data() {
    return {
      API: config.API,
      pageOptions: [5, 10, 15, 50, 100],
      filter: null,
      sortBy: "IdProducto",
      sortDesc: false,
      productoSelect: {},
      items: [],
      isBusy: false,
      fields: [
        { key: "CodigoBarra", label: "Código", sortable: true },
        { key: "Producto", sortable: true },
        { key: "ProductoCategoria", label: "Categoría", sortable: true },
        { key: "ProductoMarca", label: "Marca", sortable: true },
        { key: "ProductoModelo", label: "Modelo", sortable: true },
        { key: "stock", label: "Stock", sortable: false, class: "text-center" }
      ],
      totalRows: 50,
      perPage: 10,
      currentPage: 1
    };
  },
  methods: {
    rowClick(item, index) {
      this.productoSelect = item;
      this.$refs.productoForm.show = true;
      //this.$refs.productoForm.producto.IdProducto = 'a'
      //console.log(this.$refs.productoForm.$refs.productoForm)
    },
    actualizarTabla() {
      this.$refs.tablaProductos.refresh();
    },
    nuevoProducto() {
      this.productoSelect = {};
      this.$refs.productoForm.show = true;
    },
    myProvider(ctx) {
      // Here we don't set isBusy prop, so busy state will be handled by table itself
      // this.isBusy = true
      let url =
        this.API +
        "productos?limit=" +
        ctx.perPage +
        "&page=" +
        ctx.currentPage +
        "&filter=" +
        (ctx.filter || "") +
        "&sortBy=" +
        ctx.sortBy;
      url += "&sortDesc=" + (ctx.sortDesc ? 1 : 0);
      let promise = this.axios.get(url);

      return promise
        .then(result => {
          const productos = Array.isArray(result.data) ? result.data : [];
          if (!productos.length) {
            this.items = [];
            return this.items;
          }

          return this.axios.get(this.API + "productos/stock", {
            params: {
              idProductos: productos.map(item => item.IdProducto),
              idAlmacen: Number(sessionStorage.getItem("IdAlmacen")) || 1
            }
          }).then(stockResult => {
            const stockPorProducto = (Array.isArray(stockResult.data) ? stockResult.data : []).reduce((stock, item) => {
              stock[item.IdProducto] = item.stock;
              return stock;
            }, {});
            this.items = productos.map(item => ({
              ...item,
              stock: stockPorProducto[item.IdProducto] == null ? null : stockPorProducto[item.IdProducto]
            }));
            return this.items;
          }).catch(() => {
            this.items = productos.map(item => ({ ...item, stock: null }));
            return this.items;
          });
        })
        .catch(error => {
          // Here we could override the busy state, setting isBusy to false
          // this.isBusy = false
          // Returning an empty array, allows table to correctly handle busy state in case of error
          return [];
        });
    }
  },
  components: {
    ProductoForm,
    CodigoMultiple
  }
};
</script>

<style>
</style>
