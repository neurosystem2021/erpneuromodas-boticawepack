<template>
  <div class="producto-list-wrapper">
    <b-container class="bv-example-row" fluid>

      <!-- Barra de herramientas: búsqueda + mostrar N + total -->
      <b-row class="toolbar align-items-center">
        <b-col md="6" class="my-1">
          <b-form-group class="mb-0">
            <b-input-group class="search-group">
              <b-input-group-prepend>
                <span class="input-group-text search-icon"><i class="fa fa-search"></i></span>
              </b-input-group-prepend>
              <b-form-input
                v-model="filter"
                placeholder="Buscar por nombre, código, laboratorio..."
                class="search-input"
                @input="currentPage = 1"
              />
              <b-input-group-append>
                <b-btn
                  :disabled="!filter"
                  variant="outline-secondary"
                  class="clear-btn"
                  @click="filter = ''"
                >
                  <i class="fa fa-times"></i> Limpiar
                </b-btn>
              </b-input-group-append>
            </b-input-group>
          </b-form-group>
        </b-col>

        <b-col md="6" class="my-1">
          <b-row class="align-items-center">
            <b-col md="7">
              <b-form-group horizontal label="Mostrar" class="mb-0 show-select">
                <b-form-select
                  :options="pageOptions"
                  v-model="perPage"
                  class="rounded-pill-select"
                  @input="currentPage = 1"
                />
              </b-form-group>
            </b-col>
            <b-col md="5" class="text-md-right">
              <span class="total-badge">
                <b>{{ totalRows }}</b> artículos
              </span>
            </b-col>
          </b-row>
        </b-col>
      </b-row>

      <!-- Tabla -->
      <b-row>
        <b-col cols="12">
          <b-table
            ref="tablaProductos"
            :filter="filter"
            :sort-by.sync="sortBy"
            :sort-desc.sync="sortDesc"
            striped
            hover
            responsive
            show-empty
            empty-text="No se encontraron productos"
            empty-filtered-text="No hay resultados para tu búsqueda"
            class="tabla-custom shadow-sm"
            @row-clicked="rowClick"
            id="my-table"
            :busy.sync="isBusy"
            :items="myProvider"
            :fields="fields"
            :per-page="perPage"
            :current-page="currentPage"
          >
            <!-- Loading overlay -->
            <template slot="table-busy">
              <div class="text-center text-secondary my-3">
                <b-spinner class="align-middle mr-2"></b-spinner>
                <strong>Cargando productos...</strong>
              </div>
            </template>

            <!-- Producto: nombre en negrita -->
            <template slot="Producto" slot-scope="data">
              <span class="producto-nombre">{{ data.item.Producto }}</span>
            </template>

            <!-- Dosis -->
            <template slot="Dosis" slot-scope="data">
              <span class="text-muted">{{ data.item.Dosis || '—' }}</span>
            </template>

            <!-- Precios formateados como moneda -->
            <template slot="PrecioCosto" slot-scope="data">
              <span class="precio precio-costo">{{ formatMoney(data.item.PrecioCosto) }}</span>
            </template>
            <template slot="PrecioContado" slot-scope="data">
              <span class="precio precio-venta">{{ formatMoney(data.item.PrecioContado) }}</span>
            </template>
            <template slot="PrecioPorMayor" slot-scope="data">
              <span class="precio">{{ formatMoney(data.item.PrecioPorMayor) }}</span>
            </template>

            <!-- Stock con badge de color -->
            <template slot="Stock" slot-scope="data">
              <b-badge :variant="stockVariant(data.item.Stock)" class="stock-badge">
                {{ formatStock(data.item.Stock) }}
              </b-badge>
            </template>
          </b-table>
        </b-col>
      </b-row>

      <!-- Paginación -->
      <b-row>
        <b-col cols="12" class="d-flex justify-content-center my-2">
          <b-pagination
            :total-rows="totalRows"
            :per-page="perPage"
            v-model="currentPage"
            class="my-0 pagination-modern"
          />
        </b-col>
      </b-row>

      <producto-form
        ref="productoForm"
        :producto-update="productoSelect"
        :cbGuardar="actualizarTabla"
      ></producto-form>

      <br />
      <b-row align-h="center" class="text-center">
        <b-col cols="4">
          <b-button variant="success" size="lg" class="btn-nuevo" @click="nuevoProducto">
            <i class="fa fa-plus"></i> Nuevo
          </b-button>
        </b-col>
      </b-row>
      <br /><br />
    </b-container>
  </div>
</template>

<script>
import config from "@/store/config.js";
import ProductoForm from "@/components/ProductoForm";

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
      sortBy: "Codigo",
      sortDesc: false,
      productoSelect: {},
      items: [],
      isBusy: false,
      // ORDEN VISIBLE: Codigo, Producto, Laboratorio, Medicion, Dosis,
      // Precio Costo, Precio Contado, Precio Por Mayor, Stock.
      // El resto de campos (ProductoFormaFarmaceutica, ProductoCategoria,
      // ProductoDesc, ProductoDescCorto, CodigoBarra, StockPorMayor,
      // StockMinimo, ControlaStock, Bloque, VentaEstrategica,
      // PorcentajeUtilidad, Presentaciones, Oferta, Vencer, NotaVenta, etc.)
      // NO se listan aquí, pero siguen llegando completos en cada "item" de
      // la API, así que el modal (rowClick -> productoSelect) los recibe
      // todos con normalidad; solo se ocultan de la tabla.
      fields: [
        { key: "Codigo", label: "Codigo", sortable: true },
        { key: "Producto", label: "Producto", sortable: true },
        { key: "ProductoMarca", label: "Laboratorio", sortable: true },
        { key: "ProductoMedicion", label: "Medición", sortable: true },
        { key: "Dosis", label: "Dosis", sortable: true },
        { key: "PrecioCosto", label: "Precio Costo", sortable: true },
        { key: "PrecioContado", label: "Precio Contado", sortable: true },
        { key: "PrecioPorMayor", label: "Precio Por Mayor", sortable: true },
        { key: "Stock", label: "Stock", sortable: true }
      ],
      totalRows: 50,
      perPage: 10,
      currentPage: 1
    };
  },
  methods: {
    rowClick(item) {
      // "item" trae TODAS las columnas del producto (incluidas las que no
      // se muestran en la tabla), por lo que el formulario modal sigue
      // funcionando igual que antes sin ningún ajuste adicional.
      this.productoSelect = item;
      this.$refs.productoForm.show = true;
    },
    actualizarTabla() {
      this.$refs.tablaProductos.refresh();
    },
    nuevoProducto() {
      this.productoSelect = {};
      this.$refs.productoForm.show = true;
    },
    formatMoney(value) {
      var n = parseFloat(value);
      if (isNaN(n)) n = 0;
      return "S/ " + n.toFixed(2);
    },
    formatStock(value) {
      var n = parseFloat(value);
      if (isNaN(n)) n = 0;
      return n.toFixed(2);
    },
    stockVariant(value) {
      var n = parseFloat(value) || 0;
      if (n <= 0) return "danger";
      if (n <= 5) return "warning";
      return "success";
    },
    myProvider(ctx) {
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
          this.items = result.data;
          return this.items;
        })
        .catch(() => {
          return [];
        });
    }
  },
  components: {
    ProductoForm
  }
};
</script>

<style scoped>
.producto-list-wrapper {
  padding-top: 8px;
}

.toolbar {
  margin-bottom: 4px;
}

.search-group .search-icon {
  background: #fff;
  border-right: none;
  color: #6c757d;
}

.search-input {
  border-left: none;
}

.search-input:focus {
  box-shadow: none;
  border-color: #ced4da;
}

.clear-btn {
  border-top-right-radius: 0.375rem !important;
  border-bottom-right-radius: 0.375rem !important;
}

.rounded-pill-select {
  border-radius: 999px;
}

.total-badge {
  display: inline-block;
  padding: 6px 14px;
  border-radius: 999px;
  background: #eef6ff;
  color: #2c6ecb;
  font-size: 0.9rem;
}

.tabla-custom {
  border-radius: 10px;
  overflow: hidden;
  background: #fff;
}

.tabla-custom >>> thead th {
  background-color: #17a2b8;
  color: #fff;
  border: none;
  white-space: nowrap;
  vertical-align: middle;
}

.tabla-custom >>> tbody tr {
  cursor: pointer;
  transition: background-color 0.15s ease-in-out;
}

.tabla-custom >>> tbody tr:hover {
  background-color: #f1f9fb !important;
}

.producto-nombre {
  font-weight: 600;
  color: #212529;
}

.precio {
  font-variant-numeric: tabular-nums;
}

.precio-costo {
  color: #6c757d;
}

.precio-venta {
  font-weight: 600;
  color: #198754;
}

.stock-badge {
  font-size: 0.85rem;
  padding: 5px 10px;
  border-radius: 999px;
}

.pagination-modern >>> .page-link {
  border-radius: 999px;
  margin: 0 3px;
  border: none;
  color: #17a2b8;
}

.pagination-modern >>> .page-item.active .page-link {
  background-color: #17a2b8;
  color: #fff;
}

.btn-nuevo {
  border-radius: 999px;
  padding: 10px 30px;
  box-shadow: 0 4px 10px rgba(25, 135, 84, 0.25);
}
</style>