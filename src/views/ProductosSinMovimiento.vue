<template>
<main class="slow-products">
  <section class="filter-panel">
    <div class="date-field">
      <label><i class="fa fa-calendar-minus-o"></i> Desde</label>
      <input v-model="fechaDesde" type="date">
    </div>
    <div class="date-field">
      <label><i class="fa fa-calendar-check-o"></i> Hasta</label>
      <input v-model="fechaHasta" type="date">
    </div>
    <div class="search-field">
      <label><i class="fa fa-search"></i> Producto, código, marca o categoría</label>
      <input v-model.trim="busqueda" type="search" placeholder="Escribe para filtrar..." @keyup.enter="cargarProductos">
    </div>
    <button class="btn-consultar" type="button" :disabled="cargando" @click="cargarProductos">
      <i :class="cargando ? 'fa fa-spinner fa-spin' : 'fa fa-bolt'"></i>
      {{ cargando ? 'Consultando' : 'Analizar' }}
    </button>
  </section>

  <section class="content-panel">
    <div class="content-top">
      <div class="state-tabs" role="group" aria-label="Estado de productos">
        <button type="button" :class="{ active: estado === 0 }" @click="cambiarEstado(0)">
          <i class="fa fa-cubes"></i> Sin movimiento <b>{{ estado === 0 ? productos.length : totalActivos }}</b>
        </button>
        <button type="button" :class="{ active: estado === 1 }" @click="cambiarEstado(1)">
          <i class="fa fa-trash-o"></i> Eliminados <b>{{ estado === 1 ? productos.length : totalEliminados }}</b>
        </button>
      </div>
      <div class="result-summary">
        <span><strong>{{ productos.length }}</strong> resultados</span>
        <span class="zero"><strong>{{ productosStockCero }}</strong> con stock cero</span>
        <span class="blocked"><strong>{{ productosConStock }}</strong> protegidos</span>
      </div>
    </div>

    <div class="rule-banner" v-if="estado === 0">
      <i class="fa fa-lock"></i>
      <div><strong>Protección de inventario activa</strong><span>Solo se pueden eliminar productos cuyo stock total sea exactamente 0.00.</span></div>
    </div>
    <div class="rule-banner restore" v-else>
      <i class="fa fa-history"></i>
      <div><strong>Papelera de productos</strong><span>Al reactivar, el producto volverá a aparecer en stock y punto de venta.</span></div>
    </div>

    <div class="table-shell">
      <table class="products-table">
        <thead>
          <tr>
            <th>Código</th>
            <th>Producto</th>
            <th>Categoría</th>
            <th>Marca</th>
            <th>Última venta</th>
            <th class="center">Stock total</th>
            <th class="right">Acción</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="producto in productosVisibles" :key="producto.IdProducto">
            <td><span class="code-pill">{{ producto.CodigoBarra || producto.IdProducto }}</span></td>
            <td class="product-name"><strong>{{ producto.Producto }}</strong><small>ID {{ producto.IdProducto }}</small></td>
            <td>{{ producto.ProductoCategoria || 'Sin categoría' }}</td>
            <td>{{ producto.ProductoMarca || 'Sin marca' }}</td>
            <td>
              <span v-if="producto.UltimaVenta" class="last-sale"><i class="fa fa-clock-o"></i> {{ formatearFecha(producto.UltimaVenta) }}</span>
              <span v-else class="never-sale">Sin ventas registradas</span>
            </td>
            <td class="center">
              <span :class="['stock-pill', stockEsCero(producto.Stock) ? 'zero' : 'with-stock']">
                {{ formatearStock(producto.Stock) }}
              </span>
            </td>
            <td class="right">
              <button
                v-if="estado === 0"
                type="button"
                class="action-button delete"
                :disabled="!stockEsCero(producto.Stock)"
                :title="stockEsCero(producto.Stock) ? 'Eliminar y ocultar' : 'Bloqueado: el producto tiene stock'"
                :aria-label="stockEsCero(producto.Stock) ? 'Eliminar producto' : 'Producto bloqueado por stock'"
                @click="eliminarProducto(producto)"
              >
                <i :class="stockEsCero(producto.Stock) ? 'fa fa-trash' : 'fa fa-lock'"></i>
                <span class="sr-only">{{ stockEsCero(producto.Stock) ? 'Eliminar producto' : 'Producto con stock' }}</span>
              </button>
              <button v-else type="button" class="action-button restore" title="Reactivar producto" aria-label="Reactivar producto" @click="reactivarProducto(producto)">
                <i class="fa fa-undo"></i><span class="sr-only">Reactivar producto</span>
              </button>
            </td>
          </tr>
          <tr v-if="!cargando && productos.length === 0">
            <td colspan="7" class="empty-state">
              <i class="fa fa-check-circle-o"></i>
              <strong>No hay productos en esta vista</strong>
              <span>Ajusta el rango de fechas o el término de búsqueda.</span>
            </td>
          </tr>
          <tr v-if="cargando">
            <td colspan="7" class="empty-state loading"><i class="fa fa-circle-o-notch fa-spin"></i><strong>Analizando inventario...</strong></td>
          </tr>
        </tbody>
      </table>
    </div>

    <footer v-if="productos.length" class="pagination-bar">
      <div class="pagination-summary">
        <strong>{{ registroDesde }}-{{ registroHasta }}</strong>
        <span>de {{ productos.length }} productos</span>
      </div>
      <label class="page-size">
        <span>Filas</span>
        <select v-model.number="tamanoPagina" @change="paginaActual = 1">
          <option :value="25">25</option>
          <option :value="50">50</option>
          <option :value="100">100</option>
        </select>
      </label>
      <nav class="page-controls" aria-label="Paginaci&oacute;n de productos">
        <button type="button" :disabled="paginaActual === 1" title="Primera p&aacute;gina" @click="irPagina(1)">
          <i class="fa fa-angle-double-left"></i>
        </button>
        <button type="button" :disabled="paginaActual === 1" title="P&aacute;gina anterior" @click="irPagina(paginaActual - 1)">
          <i class="fa fa-angle-left"></i>
        </button>
        <span><b>{{ paginaActual }}</b> / {{ totalPaginas }}</span>
        <button type="button" :disabled="paginaActual === totalPaginas" title="P&aacute;gina siguiente" @click="irPagina(paginaActual + 1)">
          <i class="fa fa-angle-right"></i>
        </button>
        <button type="button" :disabled="paginaActual === totalPaginas" title="&Uacute;ltima p&aacute;gina" @click="irPagina(totalPaginas)">
          <i class="fa fa-angle-double-right"></i>
        </button>
      </nav>
    </footer>
  </section>
</main>
</template>

<script>
import config from "@/store/config.js";

export default {
  data() {
    return {
      API: config.API,
      fechaDesde: "",
      fechaHasta: "",
      busqueda: "",
      estado: 0,
      productos: [],
      totalActivos: 0,
      totalEliminados: 0,
      paginaActual: 1,
      tamanoPagina: 100,
      cargando: false
    };
  },
  computed: {
    productosStockCero() {
      return this.productos.filter(item => this.stockEsCero(item.Stock)).length;
    },
    productosConStock() {
      return this.productos.length - this.productosStockCero;
    },
    totalPaginas() {
      return Math.max(1, Math.ceil(this.productos.length / this.tamanoPagina));
    },
    productosVisibles() {
      const pagina = Math.min(this.paginaActual, this.totalPaginas);
      const inicio = (pagina - 1) * this.tamanoPagina;
      return this.productos.slice(inicio, inicio + this.tamanoPagina);
    },
    registroDesde() {
      return this.productos.length ? ((this.paginaActual - 1) * this.tamanoPagina) + 1 : 0;
    },
    registroHasta() {
      return Math.min(this.paginaActual * this.tamanoPagina, this.productos.length);
    }
  },
  mounted() {
    const hoy = new Date();
    const inicio = new Date();
    inicio.setDate(inicio.getDate() - 90);
    this.fechaHasta = this.fechaLocal(hoy);
    this.fechaDesde = this.fechaLocal(inicio);
    this.cargarProductos();
  },
  methods: {
    fechaLocal(fecha) {
      const year = fecha.getFullYear();
      const month = String(fecha.getMonth() + 1).padStart(2, "0");
      const day = String(fecha.getDate()).padStart(2, "0");
      return `${year}-${month}-${day}`;
    },
    async cargarProductos() {
      if (!this.fechaDesde || !this.fechaHasta || this.fechaDesde > this.fechaHasta) {
        this.$swal({ icon: "warning", title: "Revisa las fechas", text: "Selecciona un rango de fechas válido." });
        return;
      }
      this.cargando = true;
      try {
        const response = await this.axios.get(`${this.API}productos/sin-movimiento`, {
          params: {
            fechaDesde: this.fechaDesde,
            fechaHasta: this.fechaHasta,
            estado: this.estado,
            q: this.busqueda
          }
        });
        this.productos = response.data && response.data.items ? response.data.items : [];
        this.paginaActual = 1;
        if (this.estado === 0) this.totalActivos = this.productos.length;
        else this.totalEliminados = this.productos.length;
      } catch (error) {
        const message = error.response && error.response.data && error.response.data.message
          ? error.response.data.message
          : "No se pudo consultar el inventario.";
        this.$swal({ icon: "error", title: "Error de consulta", text: message });
        this.productos = [];
      } finally {
        this.cargando = false;
      }
    },
    cambiarEstado(estado) {
      if (this.estado === estado) return;
      this.estado = estado;
      this.cargarProductos();
    },
    irPagina(pagina) {
      this.paginaActual = Math.min(Math.max(1, pagina), this.totalPaginas);
      this.$nextTick(() => {
        const table = this.$el.querySelector(".table-shell");
        if (table) table.scrollTop = 0;
      });
    },
    stockEsCero(stock) {
      return Math.abs(Number(stock || 0)) < 0.0001;
    },
    formatearStock(stock) {
      return Number(stock || 0).toFixed(2);
    },
    formatearFecha(fecha) {
      if (!fecha) return "";
      return String(fecha).slice(0, 10).split("-").reverse().join("/");
    },
    eliminarProducto(producto) {
      if (!this.stockEsCero(producto.Stock)) {
        this.$swal({ icon: "warning", title: "Producto protegido", text: `No se puede eliminar porque tiene stock ${this.formatearStock(producto.Stock)}.` });
        return;
      }
      this.$swal({
        icon: "warning",
        title: "Eliminar producto",
        html: `<b>${producto.Producto}</b><br>Dejará de mostrarse en stock y punto de venta. Podrás reactivarlo desde Eliminados.`,
        showCancelButton: true,
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
        confirmButtonColor: "#e11d48"
      }).then(result => {
        if (result.isConfirmed) this.cambiarEstadoProducto(producto, 1);
      });
    },
    reactivarProducto(producto) {
      this.$swal({
        icon: "question",
        title: "Reactivar producto",
        html: `<b>${producto.Producto}</b> volverá a estar disponible en stock y punto de venta.`,
        showCancelButton: true,
        confirmButtonText: "Reactivar",
        cancelButtonText: "Cancelar",
        confirmButtonColor: "#10b981"
      }).then(result => {
        if (result.isConfirmed) this.cambiarEstadoProducto(producto, 0);
      });
    },
    async cambiarEstadoProducto(producto, anulado) {
      try {
        const response = await this.axios.post(`${this.API}productos/estado`, {
          IdProducto: producto.IdProducto,
          Anulado: anulado
        });
        this.estado = anulado === 1 ? 1 : 0;
        await this.cargarProductos();
        this.$swal({
          icon: "success",
          title: anulado ? "Producto eliminado" : "Producto reactivado",
          text: anulado
            ? "Se abrió la papelera. Si fue un error, pulsa Reactivar junto al producto."
            : response.data.message,
          confirmButtonText: "Entendido",
          timer: anulado ? 3500 : 1800,
          showConfirmButton: anulado === 1
        });
      } catch (error) {
        const message = error.response && error.response.data && error.response.data.message
          ? error.response.data.message
          : "No se pudo actualizar el producto.";
        this.$swal({ icon: "error", title: "Operación bloqueada", text: message });
      }
    }
  }
};
</script>

<style scoped>
.slow-products { min-height: 100vh; padding: 10px 14px 14px; color: #102a43; background: linear-gradient(135deg, #eff9fc 0%, #f5f3ff 55%, #fff7ed 100%); }
.filter-panel, .content-panel { max-width: 1800px; margin-right: auto; margin-left: auto; }
.filter-panel { display: grid; grid-template-columns: 180px 180px minmax(280px,1fr) 150px; gap: 10px; padding: 10px 12px; border: 1px solid #d4e4f0; border-radius: 15px; background: #fff; box-shadow: 0 7px 20px rgba(15,23,42,.07); }
.filter-panel label { display: block; margin: 0 0 6px 2px; color: #4b647b; font-size: 11px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
.filter-panel label i { margin-right: 5px; color: #0891b2; }
.filter-panel input { width: 100%; height: 40px; padding: 0 12px; border: 1px solid #cbdceb; border-radius: 10px; outline: 0; background: #f9fcff; transition: border-color .12s, box-shadow .12s; }
.filter-panel input:focus { border-color: #22d3ee; box-shadow: 0 0 0 3px rgba(34,211,238,.11); background: #fff; }
.btn-consultar { align-self: end; height: 40px; border: 0; border-radius: 10px; color: #fff; font-weight: 900; background: linear-gradient(135deg,#06b6d4,#4f46e5); box-shadow: 0 6px 14px rgba(37,99,235,.16); cursor: pointer; }
.content-panel { margin-top: 10px; overflow: hidden; border: 1px solid #d6e4ef; border-radius: 16px; background: #fff; box-shadow: 0 10px 28px rgba(15,23,42,.08); }
.content-top { display: flex; justify-content: space-between; align-items: center; gap: 10px; padding: 10px 14px; border-bottom: 1px solid #e5edf5; }
.state-tabs { display: flex; gap: 8px; }
.state-tabs button { padding: 8px 12px; border: 1px solid #cbd9e7; border-radius: 10px; color: #4b647b; font-weight: 800; background: #f8fbfe; cursor: pointer; transition: border-color .12s, background-color .12s; }
.state-tabs button.active { border-color: #22d3ee; color: #fff; background: linear-gradient(135deg,#0891b2,#4f46e5); box-shadow: 0 5px 14px rgba(79,70,229,.16); }
.state-tabs b { display: inline-grid; place-items: center; min-width: 24px; height: 21px; margin-left: 5px; padding: 0 5px; border-radius: 9px; background: rgba(255,255,255,.2); }
.result-summary { display: flex; gap: 9px; flex-wrap: wrap; }
.result-summary span { padding: 7px 10px; border-radius: 9px; color: #49637a; font-size: 12px; background: #eef4f8; }
.result-summary .zero { color: #047857; background: #d1fae5; }
.result-summary .blocked { color: #b45309; background: #fef3c7; }
.rule-banner { display: flex; gap: 9px; align-items: center; margin: 8px 14px 0; padding: 7px 10px; border: 1px solid #fde68a; border-radius: 10px; color: #854d0e; background: #fffbeb; }
.rule-banner > i { font-size: 17px; }
.rule-banner strong { display: inline; margin-right: 7px; }
.rule-banner span { display: inline; color: #92631f; font-size: 12px; }
.rule-banner.restore { border-color: #a7f3d0; color: #047857; background: #ecfdf5; }
.rule-banner.restore span { color: #268166; }
.table-shell { height: calc(100vh - 285px); min-height: 440px; overflow: auto; padding: 6px 14px 2px; scrollbar-width: thin; scrollbar-color: #a6bfd2 #eff6fa; }
.products-table { width: 100%; min-width: 1000px; border-collapse: separate; border-spacing: 0; }
.products-table th { position: sticky; z-index: 2; top: 0; padding: 8px 9px; border-bottom: 2px solid #dce8f2; color: #587086; background: #fff; font-size: 11px; letter-spacing: .06em; text-align: left; text-transform: uppercase; }
.products-table td { padding: 6px 9px; border-bottom: 1px solid #e8eff5; color: #2b4358; vertical-align: middle; }
.products-table th:last-child, .products-table td:last-child { width: 58px; }
.products-table tbody tr { transition: background-color .1s; }
.products-table tbody tr:hover { background: linear-gradient(90deg,rgba(236,254,255,.8),rgba(245,243,255,.65)); }
.center { text-align: center !important; }.right { text-align: right !important; }
.code-pill { display: inline-block; padding: 4px 7px; border-radius: 7px; color: #075985; font-family: monospace; font-weight: 800; background: #e0f2fe; }
.product-name strong { display: inline; }.product-name small { display: inline; margin-left: 7px; color: #8aa0b3; white-space: nowrap; }
.last-sale { color: #536c82; white-space: nowrap; }.never-sale { color: #9f6b16; font-size: 12px; font-weight: 700; white-space: nowrap; }
.stock-pill { display: inline-block; min-width: 62px; padding: 5px 8px; border-radius: 8px; font-weight: 900; }
.stock-pill.zero { color: #047857; background: #d1fae5; }.stock-pill.with-stock { color: #b45309; background: #fef3c7; }
.action-button { display: inline-grid; place-items: center; width: 38px; height: 38px; min-width: 38px; padding: 0; border: 1px solid rgba(255,255,255,.42); border-radius: 11px; color: #fff; font-size: 15px; cursor: pointer; box-shadow: 0 4px 10px rgba(15,23,42,.12); transition: transform .12s, filter .12s; }
.action-button.delete { background: linear-gradient(135deg,#f43f5e,#be123c); }.action-button.restore { background: linear-gradient(135deg,#10b981,#047857); }
.action-button:hover:not(:disabled) { filter: brightness(1.08); transform: translateY(-1px); }
.action-button:disabled { color: #738497; background: #dce5ec; box-shadow: none; cursor: not-allowed; }
.empty-state { padding: 48px 20px !important; text-align: center; color: #7890a4 !important; }
.empty-state i, .empty-state strong, .empty-state span { display: block; }.empty-state i { margin-bottom: 8px; font-size: 34px; color: #34d399; }.empty-state span { margin-top: 4px; }
.empty-state.loading i { color: #06b6d4; }
.pagination-bar { display: flex; align-items: center; justify-content: flex-end; gap: 12px; min-height: 48px; padding: 7px 14px; border-top: 1px solid #e5edf5; background: linear-gradient(90deg,#f8fcfe,#f8f7ff); }
.pagination-summary { display: flex; gap: 5px; margin-right: auto; color: #61798d; font-size: 12px; }
.pagination-summary strong { color: #0e7490; }
.page-size { display: flex; align-items: center; gap: 6px; margin: 0; color: #61798d; font-size: 12px; font-weight: 800; }
.page-size select { height: 32px; padding: 0 25px 0 8px; border: 1px solid #cbdceb; border-radius: 8px; color: #23435e; background: #fff; }
.page-controls { display: flex; align-items: center; gap: 5px; }
.page-controls button { display: grid; place-items: center; width: 32px; height: 32px; padding: 0; border: 1px solid #c8d9e7; border-radius: 8px; color: #0e7490; background: #fff; cursor: pointer; }
.page-controls button:hover:not(:disabled) { border-color: #22b8d0; color: #fff; background: #0891b2; }
.page-controls button:disabled { opacity: .38; cursor: not-allowed; }
.page-controls span { min-width: 65px; color: #60788d; font-size: 12px; text-align: center; }
.page-controls b { color: #153a56; }
@media (max-width: 900px) { .filter-panel { grid-template-columns: 1fr 1fr; }.search-field { grid-column: 1 / -1; }.btn-consultar { grid-column: 1 / -1; }.content-top { align-items: stretch; flex-direction: column; }.table-shell { height: auto; min-height: 440px; } }
@media (max-width: 560px) { .slow-products { padding: 6px; }.filter-panel { grid-template-columns: 1fr; }.search-field,.btn-consultar { grid-column: auto; }.state-tabs { flex-direction: column; }.state-tabs button { width: 100%; }.result-summary { display: grid; grid-template-columns: 1fr; }.rule-banner strong,.rule-banner span { display: block; }.pagination-bar { align-items: stretch; flex-wrap: wrap; }.pagination-summary { width: 100%; }.page-controls { margin-left: auto; } }
</style>
