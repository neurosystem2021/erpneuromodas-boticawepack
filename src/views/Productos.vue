<template>
<div class="product-catalog">
  <section class="catalog-toolbar" aria-label="Herramientas de productos">
    <div class="catalog-search">
      <i class="fas fa-search" aria-hidden="true"></i>
      <input
        v-model="searchText"
        type="search"
        autocomplete="off"
        placeholder="Buscar producto, código, marca o categoría"
        aria-label="Buscar productos"
        @input="programarBusqueda"
        @keyup.enter="aplicarBusqueda"
      >
      <button
        v-if="searchText"
        type="button"
        class="search-clear"
        title="Limpiar búsqueda"
        @click="limpiarBusqueda"
      >
        <i class="fas fa-times" aria-hidden="true"></i>
      </button>
    </div>

    <div class="catalog-view-switch" role="group" aria-label="Filtrar productos">
      <button
        type="button"
        :class="{ active: !soloOfertas }"
        @click="cambiarVista(false)"
      >
        <i class="fas fa-cubes" aria-hidden="true"></i>
        Todos
      </button>
      <button
        type="button"
        :class="{ active: soloOfertas }"
        @click="cambiarVista(true)"
      >
        <i class="fas fa-star" aria-hidden="true"></i>
        Ofertas Pedi2.pe
      </button>
    </div>

    <button
      type="button"
      class="toolbar-button excel-button"
      :disabled="exportando"
      @click="exportarOfertas"
    >
      <i :class="exportando ? 'fas fa-spinner fa-spin' : 'fas fa-file-excel'" aria-hidden="true"></i>
      {{ exportando ? 'Preparando...' : 'Exportar Excel' }}
    </button>

    <div class="multiple-code-action">
      <codigo-multiple compact></codigo-multiple>
    </div>

    <button type="button" class="toolbar-button replenishment-button" @click="$router.push('/productos/reposicion')">
      <i class="fas fa-chart-line" aria-hidden="true"></i>
      Reposición / rotación
    </button>

    <button type="button" class="toolbar-button new-button" @click="nuevoProducto">
      <i class="fas fa-plus" aria-hidden="true"></i>
      Nuevo
    </button>
  </section>

  <section class="catalog-filters" aria-label="Filtros por categoría y marca">
    <div class="catalog-filter-control"><label for="filter-category"><i class="fas fa-layer-group" aria-hidden="true"></i> Categoría</label>
      <catalog-filter-search input-id="filter-category" v-model="categoriaId" :options="categoriasFiltro" id-key="IdProductoCategoria" label-key="ProductoCategoria" placeholder="Todas las categorías" @change="cambiarFiltros" />
    </div>
    <div class="catalog-filter-control"><label for="filter-brand"><i class="fas fa-tag" aria-hidden="true"></i> Marca</label>
      <catalog-filter-search input-id="filter-brand" v-model="marcaId" :options="marcasFiltro" id-key="IdProductoMarca" label-key="ProductoMarca" placeholder="Todas las marcas" @change="cambiarFiltros" />
    </div>
    <button v-if="categoriaId || marcaId" type="button" @click="limpiarFiltros"><i class="fas fa-times" aria-hidden="true"></i> Limpiar filtros</button>
  </section>
  <section class="catalog-meta" aria-label="Opciones del listado">
    <div class="result-summary">
      <span class="result-number">{{ totalRows }}</span>
      <span>{{ soloOfertas ? 'ofertas configuradas' : 'productos encontrados' }}</span>
    </div>

    <label class="rows-control">
      <span>Filas</span>
      <select v-model.number="perPage" @change="cambiarCantidadFilas">
        <option v-for="option in pageOptions" :key="option" :value="option">{{ option }}</option>
      </select>
    </label>

    <div class="exchange-control">
      <label for="product-exchange-rate">T. cambio</label>
      <input id="product-exchange-rate" v-model.number="tipocambio" type="number" min="0" step="0.01">
      <button type="button" title="Guardar tipo de cambio" @click="guardarTipoCambio">
        <i class="fas fa-save" aria-hidden="true"></i>
        <span>Guardar</span>
      </button>
    </div>
  </section>

  <section class="catalog-table-shell">
    <b-table
      id="product-catalog-table"
      ref="tablaProductos"
      :items="myProvider"
      :fields="fields"
      :filter="filter"
      :per-page="perPage"
      :current-page="currentPage"
      :sort-by.sync="sortBy"
      :sort-desc.sync="sortDesc"
      :busy.sync="isBusy"
      small
      hover
      responsive
      show-empty
      empty-text="No hay productos para mostrar"
      empty-filtered-text="No se encontraron productos con ese criterio"
      @row-clicked="rowClick"
    >
      <template slot="HEAD_Acciones">
        <span class="sr-only">Acciones</span>
      </template>

      <template slot="CodigoBarra" slot-scope="data">
        <span class="code-pill">{{ data.item.CodigoBarra || data.item.IdProducto }}</span>
      </template>

      <template slot="Producto" slot-scope="data">
        <div class="product-name">{{ data.item.Producto }}</div>
        <div class="product-detail">
          <span v-if="data.item.ProductoMedicion">{{ data.item.ProductoMedicion }}</span>
          <span v-if="data.item.ProductoPresentacion">{{ data.item.ProductoPresentacion }}</span>
        </div>
      </template>

      <template slot="ProductoCategoria" slot-scope="data">
        <span class="category-pill">{{ data.item.ProductoCategoria || 'Sin categoría' }}</span>
      </template>

      <template slot="ProductoMarca" slot-scope="data">
        <span class="brand-name">{{ data.item.ProductoMarca || 'S/M' }}</span>
      </template>

      <template slot="stock" slot-scope="data">
        <span v-if="Number(data.item.ControlaStock) !== 1" class="stock-pill neutral" title="Este producto no controla stock">—</span>
        <span v-else-if="data.item._stockLoading" class="stock-pill loading" title="Consultando stock">
          <i class="fas fa-spinner fa-spin" aria-hidden="true"></i>
        </span>
        <span v-else :class="['stock-pill', claseStock(data.item.stock)]">{{ formatearStock(data.item.stock) }}</span>
      </template>

      <template slot="StockMinimo" slot-scope="data">
        <span
          :class="['stock-pill', Number(data.item.ControlaStockMinimo) === 1 ? 'minimum' : 'neutral']"
          :title="Number(data.item.ControlaStockMinimo) === 1 ? 'Stock mínimo configurado' : 'Control de stock mínimo desactivado'"
        >{{ formatearStock(data.item.StockMinimo) }}</span>
      </template>

      <template slot="PrecioCosto" slot-scope="data">
        <span class="money secondary">S/ {{ formatearMoneda(data.item.PrecioCosto) }}</span>
      </template>

      <template slot="UtilidadPorcentaje" slot-scope="data"><span class="product-profit-percent" :class="{ 'is-loss': utilidadPorcentaje(data.item) !== null && utilidadPorcentaje(data.item) < 0 }" title="UTILIDAD SOBRE COSTO: (VENTA − COSTO) ÷ COSTO × 100">{{ utilidadPorcentaje(data.item) === null ? '—' : utilidadPorcentaje(data.item).toFixed(2) + '%' }}</span></template>
      <template slot="PrecioContado" slot-scope="data">
        <span class="money">S/ {{ formatearMoneda(data.item.PrecioContado) }}</span>
      </template>

      <template slot="OfertaPedi" slot-scope="data">
        <div v-if="esOferta(data.item)" class="offer-cell">
          <div class="offer-topline">
            <span class="offer-badge"><i class="fas fa-star" aria-hidden="true"></i> Oferta #{{ data.item.PosOferta }}</span>
            <span :class="['app-state', claseEstadoOferta(data.item)]">
              {{ textoEstadoOferta(data.item) }}
            </span>
          </div>
          <strong>S/ {{ formatearMoneda(data.item.PrecioApp) }}</strong>
          <small class="offer-dates"><i class="far fa-calendar-alt" aria-hidden="true"></i> {{ periodoOferta(data.item) }}</small>
        </div>
        <div v-else class="regular-cell">
          <span>Sin oferta</span>
          <small :class="Number(data.item.MostrarApp) === 1 ? 'online-text' : ''">
            {{ Number(data.item.MostrarApp) === 1 ? 'Visible en Pedi2.pe' : 'No publicado' }}
          </small>
        </div>
      </template>

      <template slot="Acciones" slot-scope="data">
        <div class="row-actions">
          <button
            v-if="esOferta(data.item)"
            type="button"
            class="row-action order-action"
            title="Subir una posición"
            aria-label="Subir oferta una posición"
            :disabled="Number(data.item.PosOferta) <= 1 || reordenandoId === Number(data.item.IdProducto)"
            @click.stop="cambiarOrden(data.item, 'subir')"
          >
            <i class="fas fa-arrow-up" aria-hidden="true"></i>
          </button>
          <button
            v-if="esOferta(data.item)"
            type="button"
            class="row-action order-action"
            title="Bajar una posición"
            aria-label="Bajar oferta una posición"
            :disabled="reordenandoId === Number(data.item.IdProducto)"
            @click.stop="cambiarOrden(data.item, 'bajar')"
          >
            <i class="fas fa-arrow-down" aria-hidden="true"></i>
          </button>
          <button
            type="button"
            class="row-action edit-action"
            title="Editar producto"
            aria-label="Editar producto"
            @click.stop="editarProducto(data.item)"
          >
            <i class="fas fa-pencil-alt" aria-hidden="true"></i>
          </button>
          <button
            v-if="esOferta(data.item)"
            type="button"
            class="row-action disable-offer-action"
            title="Desactivar oferta sin abrir el producto"
            aria-label="Desactivar oferta"
            :disabled="desactivandoId === Number(data.item.IdProducto)"
            @click.stop="desactivarOferta(data.item)"
          >
            <i :class="desactivandoId === Number(data.item.IdProducto) ? 'fas fa-spinner fa-spin' : 'fas fa-ban'" aria-hidden="true"></i>
          </button>
        </div>
      </template>

      <template slot="table-busy">
        <div class="catalog-loading">
          <i class="fas fa-spinner fa-spin" aria-hidden="true"></i>
          Cargando productos...
        </div>
      </template>
    </b-table>
  </section>

  <footer class="catalog-pagination">
    <span>Página {{ currentPage }} de {{ totalPaginas }}</span>
    <b-pagination
      v-model="currentPage"
      :total-rows="totalRows"
      :per-page="perPage"
      :limit="7"
      class="mb-0"
    />
  </footer>

  <producto-form
    ref="productoForm"
    :producto-update="productoSelect"
    :cbGuardar="actualizarTabla"
  ></producto-form>
</div>
</template>

<script>
import config from "@/store/config.js";
import ProductoForm from "@/components/ProductoForm";
import CodigoMultiple from "@/components/CodigoMultiple";
import CatalogFilterSearch from "@/components/CatalogFilterSearch";

export default {
  components: {
    CatalogFilterSearch,
    ProductoForm,
    CodigoMultiple
  },
  data() {
    return {
      API: config.API,
      tipocambio: 0,
      pageOptions: [10, 25, 50, 100],
      searchText: "",
      filter: "",
      searchTimer: null,
      categoriaId: "",
      marcaId: "",
      categoriasFiltro: [],
      marcasFiltro: [],
      soloOfertas: false,
      sortBy: "IdProducto",
      sortDesc: false,
      productoSelect: {},
      items: [],
      isBusy: false,
      exportando: false,
      desactivandoId: null,
      reordenandoId: null,
      stockRequestId: 0,
      fields: [
        { key: "CodigoBarra", label: "Código", sortable: true, tdClass: "code-column" },
        { key: "Producto", label: "Producto", sortable: true, tdClass: "product-column" },
        { key: "ProductoCategoria", label: "Categoría", sortable: true, tdClass: "category-column" },
        { key: "ProductoMarca", label: "Marca", sortable: true, tdClass: "brand-column" },
        { key: "stock", label: "Stock", thClass: "text-center", tdClass: "text-center stock-column" },
        { key: "StockMinimo", label: "Stock mínimo", thClass: "text-center", tdClass: "text-center minimum-stock-column" },
        { key: "PrecioCosto", label: "Costo", sortable: true, thClass: "text-right", tdClass: "text-right" },
        { key: "PrecioContado", label: "Venta", sortable: true, thClass: "text-right", tdClass: "text-right" },
        { key: "UtilidadPorcentaje", label: "Utilidad %", thClass: "text-right", tdClass: "text-right" },
        { key: "OfertaPedi", label: "Oferta Pedi2.pe", tdClass: "offer-column" },
        { key: "Acciones", label: "", thClass: "actions-column", tdClass: "actions-column" }
      ],
      totalRows: 0,
      perPage: 50,
      currentPage: 1
    };
  },
  computed: {
    totalPaginas() {
      return Math.max(1, Math.ceil(Number(this.totalRows || 0) / Number(this.perPage || 1)));
    }
  },
  mounted() {
    this.obtenerListado();
    this.obtnerTipoCambio();
    this.cargarOpcionesFiltro();
  },
  beforeDestroy() {
    clearTimeout(this.searchTimer);
  },
  methods: {
    cargarOpcionesFiltro() {
      [{ route: "categorias", target: "categoriasFiltro", label: "ProductoCategoria" }, { route: "marcas", target: "marcasFiltro", label: "ProductoMarca" }].forEach(option => {
        this.axios.get(this.API + option.route, { params: { limit: 10000 } }).then(response => {
          this[option.target] = (Array.isArray(response.data) ? response.data : []).filter(item => Number(item.Anulado || 0) === 0).sort((a, b) => String(a[option.label] || "").localeCompare(String(b[option.label] || ""), "es"));
        }).catch(() => this.$bvToast.toast("No se pudieron cargar las opciones de " + option.route, { title: "Filtros", variant: "danger", solid: true }));
      });
    },
    cambiarFiltros() {
      this.currentPage = 1;
      this.obtenerListado();
      this.actualizarTabla(false);
    },
    limpiarFiltros() {
      this.categoriaId = "";
      this.marcaId = "";
      this.cambiarFiltros();
    },
    utilidadPorcentaje(item) {
      const cost = Number(item.PrecioCosto), sale = Number(item.PrecioContado);
      return Number.isFinite(cost) && Number.isFinite(sale) && cost > 0 ? (sale - cost) / cost * 100 : null;
    },
    parametrosListado(extra) {
      return Object.assign(
        {
          filter: this.filter || "",
          categoriaId: this.categoriaId || "",
          marcaId: this.marcaId || "",
          soloOfertas: this.soloOfertas ? 1 : 0
        },
        extra || {}
      );
    },
    obtenerListado() {
      return this.axios
        .get(this.API + "productos/count", { params: this.parametrosListado() })
        .then(response => {
          this.totalRows = Number(response.data.total || 0);
          if (this.currentPage > this.totalPaginas) {
            this.currentPage = this.totalPaginas;
          }
        })
        .catch(() => {
          this.totalRows = 0;
        });
    },
    programarBusqueda() {
      clearTimeout(this.searchTimer);
      this.searchTimer = setTimeout(this.aplicarBusqueda, 280);
    },
    aplicarBusqueda() {
      clearTimeout(this.searchTimer);
      const nuevoFiltro = (this.searchText || "").trim();
      this.currentPage = 1;
      if (this.filter === nuevoFiltro) {
        this.actualizarTabla();
        return;
      }
      this.filter = nuevoFiltro;
      this.obtenerListado();
    },
    limpiarBusqueda() {
      this.searchText = "";
      this.filter = "";
      this.currentPage = 1;
      this.obtenerListado();
      this.actualizarTabla(false);
    },
    cambiarVista(soloOfertas) {
      const nuevoEstado = Boolean(soloOfertas);
      if (this.soloOfertas === nuevoEstado) return;
      this.soloOfertas = nuevoEstado;
      this.sortBy = nuevoEstado ? "PosOferta" : "IdProducto";
      this.sortDesc = false;
      this.currentPage = 1;
      this.obtenerListado();
      this.actualizarTabla(false);
    },
    cambiarCantidadFilas() {
      this.currentPage = 1;
      this.actualizarTabla(false);
    },
    rowClick(item) {
      this.editarProducto(item);
    },
    editarProducto(item) {
      this.productoSelect = item;
      this.$refs.productoForm.show = true;
    },
    nuevoProducto() {
      this.productoSelect = {};
      this.$refs.productoForm.show = true;
    },
    async obtnerTipoCambio() {
      try {
        const response = await this.axios(this.API + "tipocambio/usd");
        this.tipocambio = response.data;
      } catch (error) {
        this.tipocambio = 0;
      }
    },
    guardarTipoCambio() {
      this.$swal
        .fire({
          icon: "question",
          title: "Actualizar tipo de cambio",
          text: "El nuevo valor se aplicará al precio en dólares de los productos.",
          allowOutsideClick: false,
          allowEscapeKey: false,
          showCancelButton: true,
          cancelButtonText: "Cancelar",
          confirmButtonText: "Sí, actualizar",
          confirmButtonColor: "#0b9f76",
          cancelButtonColor: "#64748b"
        })
        .then(async result => {
          if (!(result.isConfirmed || result.value)) return;
          try {
            await this.axios.post(this.API + "tipocambio/usd", { tipoCambio: this.tipocambio });
            this.$toasted.success("Tipo de cambio actualizado", { duration: 2500 });
          } catch (error) {
            this.$toasted.error("No se pudo actualizar el tipo de cambio", { duration: 3000 });
          }
        });
    },
    actualizarTabla(actualizarTotal) {
      if (actualizarTotal !== false) this.obtenerListado();
      if (this.$refs.tablaProductos) this.$refs.tablaProductos.refresh();
    },
    esOferta(item) {
      const categoria = item && item.IdOfertaCategoria;
      const posicion = Number(item && item.PosOferta);
      return categoria !== null && categoria !== undefined && categoria !== "" && Number(categoria) > 0 && posicion > 0 && posicion < 10000;
    },
    fechaLocalISO() {
      const fecha = new Date();
      const year = fecha.getFullYear();
      const month = String(fecha.getMonth() + 1).padStart(2, "0");
      const day = String(fecha.getDate()).padStart(2, "0");
      return year + "-" + month + "-" + day;
    },
    textoEstadoOferta(item) {
      if (Number(item.MostrarApp) !== 1) return "Oculta";
      const hoy = this.fechaLocalISO();
      if (item.FechaOfertaInicio && item.FechaOfertaInicio > hoy) return "Programada";
      if (item.FechaOfertaFin && item.FechaOfertaFin < hoy) return "Vencida";
      return "Activa";
    },
    claseEstadoOferta(item) {
      const estado = this.textoEstadoOferta(item);
      if (estado === "Activa") return "online";
      if (estado === "Programada") return "scheduled";
      return "offline";
    },
    fechaBreve(value) {
      if (!value) return "";
      const partes = String(value).slice(0, 10).split("-");
      return partes.length === 3 ? partes[2] + "/" + partes[1] + "/" + partes[0] : value;
    },
    periodoOferta(item) {
      if (!item.FechaOfertaInicio && !item.FechaOfertaFin) return "Sin fecha límite";
      return this.fechaBreve(item.FechaOfertaInicio) + " — " + this.fechaBreve(item.FechaOfertaFin);
    },
    formatearMoneda(value) {
      const numero = Number(value);
      return Number.isFinite(numero) ? numero.toFixed(2) : "0.00";
    },
    formatearStock(value) {
      const numero = Number(value);
      if (!Number.isFinite(numero)) return "0";
      return Math.abs(numero % 1) < 0.001 ? numero.toFixed(0) : numero.toFixed(2);
    },
    claseStock(value) {
      const stock = Number(value || 0);
      if (stock <= 0) return "danger";
      if (stock <= 5) return "warning";
      return "success";
    },
    parametrosStock(ids) {
      return { idProductos: ids };
    },
    solicitarStock(ids) {
      if (!ids.length) return Promise.resolve([]);
      const almacenSesion = Number(sessionStorage.getItem("IdAlmacen"));
      const idAlmacen = almacenSesion > 0 ? almacenSesion : 1;
      return this.axios
        .get(this.API + "productos/stock/" + idAlmacen, { params: this.parametrosStock(ids) })
        .then(response => (Array.isArray(response.data) ? response.data : []));
    },
    cargarStockPagina(items, requestId) {
      const controlados = items.filter(item => Number(item.ControlaStock) === 1);
      if (!controlados.length) return;
      this.solicitarStock(controlados.map(item => Number(item.IdProducto)))
        .then(stockItems => {
          if (requestId !== this.stockRequestId) return;
          const stockMap = {};
          stockItems.forEach(item => {
            stockMap[Number(item.IdProducto)] = Number(item.stock || 0);
          });
          controlados.forEach(item => {
            this.$set(item, "stock", stockMap[Number(item.IdProducto)] || 0);
            this.$set(item, "_stockLoading", false);
          });
        })
        .catch(() => {
          if (requestId !== this.stockRequestId) return;
          controlados.forEach(item => this.$set(item, "_stockLoading", false));
        });
    },
    myProvider(ctx) {
      const requestId = ++this.stockRequestId;
      const params = this.parametrosListado({
        limit: ctx.perPage,
        page: ctx.currentPage,
        sortBy: ctx.sortBy || "IdProducto",
        sortDesc: ctx.sortDesc ? 1 : 0
      });

      return this.axios
        .get(this.API + "productos", { params })
        .then(result => {
          const items = Array.isArray(result.data) ? result.data : [];
          items.forEach(item => {
            this.$set(item, "_stockLoading", Number(item.ControlaStock) === 1);
          });
          this.items = items;
          this.cargarStockPagina(items, requestId);
          return items;
        })
        .catch(() => []);
    },
    desactivarOferta(item) {
      this.$swal
        .fire({
          icon: "warning",
          title: "Desactivar oferta",
          html: "Se quitará <b>" + String(item.Producto || "este producto") + "</b> de la sección de ofertas de Pedi2.pe.<br>El producto seguirá publicado.",
          showCancelButton: true,
          confirmButtonText: "Sí, desactivar",
          cancelButtonText: "Cancelar",
          confirmButtonColor: "#e45735",
          cancelButtonColor: "#64748b"
        })
        .then(async result => {
          if (!(result.isConfirmed || result.value)) return;
          this.desactivandoId = Number(item.IdProducto);
          try {
            await this.axios.post(this.API + "productos/oferta/desactivar", {
              IdProducto: item.IdProducto
            });
            this.$set(item, "IdOfertaCategoria", null);
            this.$set(item, "PosOferta", 10000);
            this.$toasted.success("Oferta desactivada; el producto continúa visible en Pedi2.pe", { duration: 3500 });
            await this.obtenerListado();
            this.actualizarTabla(false);
          } catch (error) {
            const message = error.response && error.response.data && error.response.data.message
              ? error.response.data.message
              : "No se pudo desactivar la oferta";
            this.$toasted.error(message, { duration: 3500 });
          } finally {
            this.desactivandoId = null;
          }
        });
    },
    cambiarOrden(item, direccion) {
      if (this.reordenandoId !== null) return;
      this.reordenandoId = Number(item.IdProducto);
      this.axios
        .post(this.API + "productos/oferta/orden", {
          IdProducto: item.IdProducto,
          Direccion: direccion
        })
        .then(response => {
          if (response.data && response.data.changed === false) {
            this.$toasted.show("La oferta ya está en el límite de la lista", { duration: 2200 });
          }
          this.actualizarTabla();
        })
        .catch(error => {
          const message = error.response && error.response.data && error.response.data.message
            ? error.response.data.message
            : "No se pudo cambiar el orden de la oferta";
          this.$toasted.error(message, { duration: 3200 });
        })
        .then(() => {
          this.reordenandoId = null;
        });
    },
    async cargarStockParaExportar(items) {
      const controlados = items.filter(item => Number(item.ControlaStock) === 1);
      const stockMap = {};
      const chunkSize = 75;
      for (let index = 0; index < controlados.length; index += chunkSize) {
        const lote = controlados.slice(index, index + chunkSize);
        try {
          const stockItems = await this.solicitarStock(lote.map(item => Number(item.IdProducto)));
          stockItems.forEach(stockItem => {
            stockMap[Number(stockItem.IdProducto)] = Number(stockItem.stock || 0);
          });
        } catch (error) {
          // El archivo continúa generándose aunque el stock no esté disponible.
        }
      }
      return stockMap;
    },
    csvValue(value) {
      const valueText = value === null || value === undefined ? "" : String(value);
      return '"' + valueText.replace(/"/g, '""') + '"';
    },
    async exportarOfertas() {
      if (this.exportando) return;
      this.exportando = true;
      try {
        const response = await this.axios.get(this.API + "productos", {
          params: {
            limit: 10000,
            page: 1,
            filter: this.filter || "",
            soloOfertas: 1,
            sortBy: "PosOferta",
            sortDesc: 0
          }
        });
        const ofertas = Array.isArray(response.data) ? response.data : [];
        if (!ofertas.length) {
          this.$toasted.show("No hay ofertas de Pedi2.pe para exportar", { duration: 3000 });
          return;
        }

        const stockMap = await this.cargarStockParaExportar(ofertas);
        const rows = [[
          "Código",
          "Producto",
          "Categoría",
          "Marca",
          "Stock",
          "Stock mínimo",
          "Precio venta",
          "Precio Pedi2.pe",
          "Orden oferta",
          "Inicio oferta",
          "Fin oferta",
          "Estado oferta",
          "Visible en Pedi2.pe"
        ]];
        ofertas.forEach(item => {
          rows.push([
            item.CodigoBarra || item.IdProducto,
            item.Producto,
            item.ProductoCategoria,
            item.ProductoMarca,
            Number(item.ControlaStock) === 1 ? (stockMap[Number(item.IdProducto)] || 0) : "No controla",
            this.formatearStock(item.StockMinimo),
            this.formatearMoneda(item.PrecioContado),
            this.formatearMoneda(item.PrecioApp),
            item.PosOferta,
            item.FechaOfertaInicio || "Sin límite",
            item.FechaOfertaFin || "Sin límite",
            this.textoEstadoOferta(item),
            Number(item.MostrarApp) === 1 ? "Sí" : "No"
          ]);
        });

        const csv = "\ufeff" + rows.map(row => row.map(this.csvValue).join(";")).join("\r\n");
        const blob = new Blob([csv], { type: "application/vnd.ms-excel;charset=utf-8" });
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement("a");
        const date = new Date().toISOString().slice(0, 10);
        link.href = url;
        link.download = "ofertas-pedi2-" + date + ".csv";
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);
        this.$toasted.success(ofertas.length + " ofertas exportadas para Excel", { duration: 3000 });
      } catch (error) {
        this.$toasted.error("No se pudieron exportar las ofertas", { duration: 3500 });
      } finally {
        this.exportando = false;
      }
    }
  }
};
</script>

<style>
.product-catalog {
  --catalog-petrol: #083f52;
  --catalog-petrol-2: #0b6070;
  --catalog-cyan: #10a9cb;
  --catalog-blue: #3e55e8;
  --catalog-green: #10b981;
  --catalog-ink: #18324a;
  min-height: calc(100vh - 12px);
  padding: 12px 18px 16px;
  color: var(--catalog-ink);
  background: linear-gradient(135deg, #f4fbfd 0%, #f6f8ff 58%, #fff8f2 100%);
}

.catalog-toolbar,
.catalog-meta,
.catalog-table-shell,
.catalog-pagination {
  max-width: 1900px;
  margin-right: auto;
  margin-left: auto;
}

.catalog-toolbar {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px;
  border: 1px solid #cae2eb;
  border-radius: 18px 18px 0 0;
  background: rgba(255, 255, 255, 0.97);
  box-shadow: 0 10px 28px rgba(13, 65, 85, 0.1);
}

.catalog-search {
  position: relative;
  display: flex;
  align-items: center;
  flex: 1 1 360px;
  min-width: 240px;
}

.catalog-search > i {
  position: absolute;
  left: 16px;
  z-index: 1;
  color: #0698b7;
}

.catalog-search input {
  width: 100%;
  height: 44px;
  padding: 0 42px;
  border: 1px solid #c9dce8;
  border-radius: 12px;
  outline: none;
  background: #f8fbfd;
  color: #18324a;
  font-size: 0.95rem;
  transition: border-color 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
}

.catalog-search input:focus {
  border-color: var(--catalog-cyan);
  background: #fff;
  box-shadow: 0 0 0 3px rgba(16, 169, 203, 0.13);
}

.search-clear {
  position: absolute;
  right: 8px;
  width: 30px;
  height: 30px;
  border: 0;
  border-radius: 9px;
  background: #eaf2f6;
  color: #587083;
  cursor: pointer;
}

.catalog-view-switch {
  display: flex;
  flex: 0 0 auto;
  gap: 4px;
  padding: 4px;
  border: 1px solid #d3e1ea;
  border-radius: 13px;
  background: #eef4f7;
}

.catalog-view-switch button,
.toolbar-button {
  height: 38px;
  border: 0;
  border-radius: 9px;
  font-weight: 800;
  white-space: nowrap;
  cursor: pointer;
}

.catalog-view-switch button {
  padding: 0 13px;
  background: transparent;
  color: #536b7e;
}

.catalog-view-switch button.active {
  background: linear-gradient(135deg, var(--catalog-petrol-2), var(--catalog-blue));
  color: #fff;
  box-shadow: 0 5px 12px rgba(37, 83, 157, 0.22);
}

.toolbar-button {
  padding: 0 15px;
  color: #fff;
  box-shadow: 0 6px 14px rgba(20, 60, 80, 0.15);
}

.toolbar-button:disabled {
  opacity: 0.65;
  cursor: wait;
}

.excel-button { background: linear-gradient(135deg, #0b9f76, #15bd82); }
.new-button { background: linear-gradient(135deg, #13a6cc, #4056e8); }

.toolbar-button i,
.catalog-view-switch i { margin-right: 6px; }

.multiple-code-action > div > .btn {
  height: 38px;
  padding: 0 14px;
  border: 0;
  border-radius: 9px;
  background: linear-gradient(135deg, #0b6070, #108ea5);
  font-size: 14px;
  font-weight: 800;
  box-shadow: 0 6px 14px rgba(20, 60, 80, 0.15);
}

.multiple-code-action .code-multiple-trigger i { margin-right: 7px; }

.catalog-meta {
  display: flex;
  align-items: center;
  gap: 16px;
  min-height: 54px;
  padding: 7px 12px;
  border-right: 1px solid #cae2eb;
  border-left: 1px solid #cae2eb;
  background: rgba(246, 251, 253, 0.96);
}

.result-summary {
  display: flex;
  align-items: center;
  gap: 7px;
  margin-right: auto;
  color: #60788b;
  font-size: 0.84rem;
  font-weight: 700;
}

.result-number {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 38px;
  height: 30px;
  padding: 0 9px;
  border-radius: 9px;
  background: #dff6f1;
  color: #087c65;
  font-size: 0.95rem;
}

.rows-control,
.exchange-control {
  display: flex;
  align-items: center;
  gap: 7px;
  margin: 0;
  color: #4b6478;
  font-size: 0.8rem;
  font-weight: 800;
}

.rows-control select,
.exchange-control input {
  height: 34px;
  border: 1px solid #cbdde7;
  border-radius: 9px;
  background: #fff;
  color: #203a50;
}

.rows-control select { min-width: 70px; padding: 0 8px; }
.exchange-control input { width: 82px; padding: 0 9px; }

.exchange-control button {
  height: 34px;
  padding: 0 11px;
  border: 0;
  border-radius: 9px;
  background: var(--catalog-petrol-2);
  color: #fff;
  font-weight: 800;
  cursor: pointer;
}

.exchange-control button i { margin-right: 4px; }

.catalog-table-shell {
  min-height: 280px;
  overflow: hidden;
  border: 1px solid #cae2eb;
  border-radius: 0 0 15px 15px;
  background: #fff;
  box-shadow: 0 14px 32px rgba(13, 65, 85, 0.1);
}

#product-catalog-table {
  min-width: 1320px;
  margin: 0;
}

#product-catalog-table thead th {
  height: 45px;
  padding: 10px 12px;
  border: 0;
  background: var(--catalog-petrol);
  color: #fff;
  font-size: 0.74rem;
  font-weight: 900;
  letter-spacing: 0.045em;
  text-transform: uppercase;
  vertical-align: middle;
  white-space: nowrap;
}

#product-catalog-table tbody tr { cursor: pointer; transition: background 0.16s ease, transform 0.16s ease; }
#product-catalog-table tbody tr:nth-child(even) { background: #f8fbfd; }
#product-catalog-table tbody tr:hover { background: #ebf9fc; }

#product-catalog-table tbody td {
  height: 62px;
  padding: 8px 12px;
  border-top: 1px solid #e1ebf1;
  color: #29445a;
  font-size: 0.83rem;
  vertical-align: middle;
}

.code-column { width: 105px; }
.product-column { min-width: 320px; }
.stock-column { width: 78px; }
.replenishment-button { background: #0b6478; color: #fff; white-space: nowrap; }
.minimum-stock-column { width: 100px; white-space: nowrap; }
.stock-pill.minimum { background: #e3f2fa; color: #17657f; }
.offer-column { width: 210px; }
.actions-column { width: 184px; min-width: 184px; text-align: center; }

.code-pill {
  display: inline-flex;
  align-items: center;
  max-width: 100px;
  min-height: 30px;
  padding: 5px 9px;
  overflow: hidden;
  border-radius: 9px;
  background: #e1f3fb;
  color: #08658b;
  font-weight: 900;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.product-name {
  color: #17354f;
  font-size: 0.88rem;
  font-weight: 900;
  line-height: 1.25;
}

.product-detail,
.product-detail {
  display: flex;
  gap: 10px;
  margin-top: 4px;
  color: #8497a7;
  font-size: 0.72rem;
  font-weight: 700;
}

.category-column { min-width: 150px; }
.brand-column { min-width: 105px; }

.category-pill {
  display: inline-flex;
  align-items: center;
  min-height: 27px;
  padding: 4px 8px;
  border-radius: 8px;
  background: #e7f5f8;
  color: #28566a;
  font-size: 0.73rem;
  font-weight: 850;
  line-height: 1.25;
}

.brand-name {
  color: #62798c;
  font-size: 0.76rem;
  font-weight: 850;
  text-transform: uppercase;
}

.stock-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  min-width: 50px;
  height: 31px;
  padding: 0 8px;
  border-radius: 10px;
  font-weight: 900;
}

.stock-pill.success { background: #d4f7e7; color: #087754; }
.stock-pill.warning { background: #fff0bd; color: #93610a; }
.stock-pill.danger { background: #ffe0e5; color: #bf2f46; }
.stock-pill.neutral,
.stock-pill.loading { background: #edf3f6; color: #718598; }

.money { color: #0a725e; font-size: 0.88rem; font-weight: 900; white-space: nowrap; }
.money.secondary { color: #657c8f; }

.offer-cell,
.regular-cell { display: flex; flex-direction: column; gap: 4px; }
.offer-topline { display: flex; align-items: center; gap: 6px; }

.offer-badge,
.app-state {
  display: inline-flex;
  align-items: center;
  min-height: 23px;
  padding: 3px 7px;
  border-radius: 7px;
  font-size: 0.67rem;
  font-weight: 900;
  line-height: 1;
  white-space: nowrap;
}

.offer-badge { background: #fff0ba; color: #956000; }
.offer-badge i { margin-right: 4px; }
.app-state.online { background: #d8f8e9; color: #08765a; }
.app-state.scheduled { background: #e9e5ff; color: #5843b8; }
.app-state.offline { background: #edf1f4; color: #718191; }
.offer-cell strong { color: #0a765f; font-size: 0.92rem; }
.offer-dates {
  color: #6d8293;
  font-size: 0.67rem;
  font-weight: 750;
  white-space: nowrap;
}
.offer-dates i { margin-right: 4px; color: #0f91ac; }
.regular-cell > span { color: #7d8e9d; font-weight: 800; }
.regular-cell small { color: #9aa9b5; }
.regular-cell small.online-text { color: #0b8a6d; font-weight: 800; }

.row-actions { display: flex; justify-content: center; gap: 7px; }
.row-action {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border: 0;
  border-radius: 10px;
  color: #fff;
  cursor: pointer;
  box-shadow: 0 6px 12px rgba(26, 62, 84, 0.18);
  transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.row-action:hover { transform: translateY(-1px); box-shadow: 0 8px 15px rgba(26, 62, 84, 0.24); }
.row-action:disabled { opacity: 0.65; cursor: wait; }
.edit-action { background: linear-gradient(135deg, #159fc2, #3d5ce6); }
.order-action { background: linear-gradient(135deg, #0d6d7d, #6758dd); }
.disable-offer-action { background: linear-gradient(135deg, #ed7f2d, #e34b3e); }

.catalog-loading {
  padding: 44px 12px;
  color: var(--catalog-petrol-2);
  font-weight: 800;
  text-align: center;
}
.catalog-loading i { margin-right: 7px; }

.catalog-pagination {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 10px 4px 0;
  color: #657d90;
  font-size: 0.8rem;
  font-weight: 700;
}

.catalog-pagination .page-link {
  min-width: 34px;
  border-color: #d3e2ea;
  color: #0c6f84;
  text-align: center;
}

.catalog-pagination .page-item.active .page-link {
  border-color: #0c7f93;
  background: linear-gradient(135deg, var(--catalog-petrol-2), var(--catalog-blue));
}

@media (max-width: 1180px) {
  .catalog-toolbar { flex-wrap: wrap; }
  .catalog-search { flex-basis: calc(100% - 10px); }
}

@media (max-width: 720px) {
  .product-catalog { padding: 7px; }
  .catalog-toolbar { gap: 7px; padding: 8px; border-radius: 14px 14px 0 0; }
  .catalog-search { flex-basis: 100%; min-width: 0; }
  .catalog-view-switch { order: 2; flex: 1 1 100%; }
  .catalog-view-switch button { flex: 1; padding: 0 7px; font-size: 0.75rem; }
  .toolbar-button { flex: 1 1 auto; padding: 0 10px; font-size: 0.78rem; }
  .multiple-code-action { flex: 1 1 auto; }
  .multiple-code-action > div > .btn { width: 100%; }
  .catalog-meta { flex-wrap: wrap; gap: 8px 12px; }
  .result-summary { flex: 1 1 100%; }
  .exchange-control { margin-left: auto; }
  .exchange-control button span { display: none; }
  .catalog-table-shell { min-height: 360px; }
  #product-catalog-table { min-width: 1240px; }
  .catalog-pagination { align-items: flex-start; flex-direction: column; }
}
</style>

<style scoped>
.product-profit-percent{display:inline-block;padding:5px 8px;border-radius:8px;background:#e8f7ef;color:#08784e;font-size:12px;font-weight:800;white-space:nowrap;font-variant-numeric:tabular-nums}.product-profit-percent.is-loss{background:#fff0f2;color:#bd2341}
</style>

<style scoped>
.catalog-filters { display:flex; align-items:center; flex-wrap:wrap; gap:10px 18px; padding:10px 15px; background:#fff; border-bottom:1px solid #d5e6ee; }
.catalog-filters label { display:flex; align-items:center; gap:9px; margin:0; color:#31566b; font-weight:700; }
.catalog-filters label span { white-space:nowrap; }
.catalog-filters label i { color:#0c8298; margin-right:4px; }
.catalog-filters select { width:240px; max-width:100%; height:38px; padding:5px 10px; border:1px solid #c8dfe9; border-radius:10px; color:#21485e; background:#f8fbfd; }
.catalog-filters button { border:0; border-radius:8px; padding:8px 12px; color:#a2213b; background:#fff0f3; font-weight:700; }
@media(max-width:600px) { .catalog-filters { gap:8px; } .catalog-filters label { flex:1 1 140px; flex-direction:column; align-items:stretch; gap:3px; } .catalog-filters select { width:100%; } }
</style>

<style scoped>
.catalog-filter-control { display:flex; align-items:center; gap:9px; }
.catalog-filter-control label { white-space:nowrap; }
@media(max-width:600px) { .catalog-filter-control { flex:1 1 145px; flex-direction:column; align-items:stretch; gap:4px; min-width:0; } }
</style>
