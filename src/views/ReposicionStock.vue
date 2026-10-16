<template>
  <div class="nst-screen replenishment-page">
    <section class="nst-panel replenishment-toolbar">
      <b-button variant="outline-secondary" @click="$router.push('/productos')"><i class="fas fa-arrow-left" aria-hidden="true"></i> Productos</b-button>
      <strong>{{ mode === 'rotation' ? 'Reposición de productos con mayor rotación' : 'Reposición por stock mínimo' }}</strong>
      <b-button variant="outline-secondary" :disabled="loading || !visibleRows.length" @click="exportRows">Informe CSV</b-button>
      <b-button variant="success" :disabled="loading || exporting || !orderRows.length" @click="exportOrder"><i class="fas fa-file-excel" aria-hidden="true"></i> {{ exporting ? 'Generando…' : 'Generar pedido Excel' }}</b-button>
    </section>
    <section class="nst-panel">
      <b-button-group class="mb-2" aria-label="Tipo de reposición">
        <b-button :variant="mode === 'rotation' ? 'primary' : 'outline-primary'" :disabled="loading" :aria-pressed="mode === 'rotation'" @click="setMode('rotation')"><i class="fas fa-chart-line" aria-hidden="true"></i> Mayor rotación</b-button>
        <b-button :variant="mode === 'minimum' ? 'primary' : 'outline-primary'" :disabled="loading" :aria-pressed="mode === 'minimum'" @click="setMode('minimum')"><i class="fas fa-boxes" aria-hidden="true"></i> Stock mínimo</b-button>
      </b-button-group>
      <b-form @submit.prevent="load">
        <b-row>
          <b-col cols="12" md="3"><b-form-group label="Almacén" label-for="replenishment-store"><b-form-select id="replenishment-store" v-model="warehouseId" :options="warehouses" value-field="IdAlmacen" text-field="Almacen" :disabled="loading" required /></b-form-group></b-col>
          <b-col cols="6" md="2"><b-form-group label="Ventas desde" label-for="replenishment-from"><b-form-input id="replenishment-from" v-model="from" type="date" :max="to" :disabled="loading" required /></b-form-group></b-col>
          <b-col cols="6" md="2"><b-form-group label="Ventas hasta" label-for="replenishment-to"><b-form-input id="replenishment-to" v-model="to" type="date" :min="from" :max="today" :disabled="loading" required /></b-form-group></b-col>
          <b-col cols="6" md="2"><b-form-group label="Cobertura objetivo (días)" label-for="replenishment-days"><b-form-input id="replenishment-days" v-model.number="coverageDays" type="number" min="1" max="365" :disabled="loading" required /></b-form-group></b-col>
          <b-col cols="6" md="3" class="replenishment-load"><b-button type="submit" variant="primary" :disabled="loading || !warehouseId"><i :class="loading ? 'fas fa-spinner fa-spin' : 'fas fa-sync-alt'" aria-hidden="true"></i> {{ loading ? 'Consultando...' : 'Consultar / actualizar' }}</b-button></b-col>
        </b-row>
      </b-form>
      <div class="replenishment-options">
        <label for="replenishment-top">Ranking de ventas</label>
        <b-form-select id="replenishment-top" v-model.number="top" :options="topOptions" :disabled="mode === 'minimum'" />
        <b-form-checkbox v-model="alertsOnly">Solo alertas de reposición y mínimos</b-form-checkbox>
        <b-form-input v-model="search" type="search" aria-label="Buscar producto en la rotación" placeholder="Buscar producto, código o marca" />
      </div>
    </section>
    <b-alert :show="!!error" variant="danger" role="alert">{{ error }}</b-alert>
    <template v-if="snapshot && !error">
      <div class="replenishment-summary" aria-live="polite">
        <div><span>{{ mode === 'rotation' ? 'Productos con mayor rotación' : 'Productos con mínimo activo' }}</span><strong>{{ topRows.length }}</strong></div>
        <div class="is-urgent"><span>Sin stock</span><strong>{{ count('urgent') }}</strong></div>
        <div class="is-warning"><span>Reponer / en mínimo</span><strong>{{ count('reorder') + count('minimum') }}</strong></div>
        <div><span>Sin mínimo configurado</span><strong>{{ topRows.filter(row => !row.configured).length }}</strong></div>
      </div>
      <div class="replenishment-note">
        Ventas del {{ displayDate(snapshot.from) }} al {{ displayDate(snapshot.to) }} · {{ snapshot.warehouse }} · Stock actual consultado a las {{ snapshot.time }}.
        <br>{{ mode === 'rotation' ? 'Solo productos con ventas, ordenados de mayor a menor rotación.' : 'Todos los productos con mínimo activo, aunque no tengan ventas; primero los que necesitan reposición.' }} Se alerta al llegar al mínimo activo. Objetivo = mínimo + demanda de {{ snapshot.coverageDays }} días (al menos una unidad sobre el mínimo). Reponer = objetivo − stock actual, sin valores negativos.
      </div>
    </template>
    <section class="nst-panel">
      <div v-if="loading" class="nst-empty-state" role="status"><i class="fas fa-spinner fa-spin" aria-hidden="true"></i> Consultando ventas y existencias actuales…</div>
      <b-table v-else small responsive hover :items="visibleRows" :fields="fields" show-empty :empty-text="error ? 'No se pudo completar la consulta. Vuelve a actualizar.' : snapshot ? 'No hay productos que coincidan con estos filtros.' : 'Selecciona un almacén y consulta la rotación.'">
        <template slot="ProveedorReposicion" slot-scope="data"><strong>{{ data.item.ProveedorReposicion || 'Sin proveedor registrado' }}</strong><small class="replenishment-product-detail">{{ data.item.RucProveedorReposicion }}</small><small v-if="Number(data.item.OrdenesPendientes) > 0" class="replenishment-product-detail">Ya tiene orden pendiente · no se incluye en un nuevo pedido</small></template>
        <template slot="Producto" slot-scope="data"><strong>{{ data.item.Producto }}</strong><small class="replenishment-product-detail">{{ data.item.CodigoBarra }} · {{ data.item.ProductoMarca }}</small></template>
        <template slot="CantidadVentas" slot-scope="data">{{ number(data.item.CantidadVentas) }}</template>
        <template slot="current" slot-scope="data"><b>{{ data.item.current === null ? 'Sin dato' : number(data.item.current) }}</b></template>
        <template slot="minimum" slot-scope="data">{{ number(data.item.minimum) }}<small v-if="!data.item.configured" class="replenishment-product-detail">Sin mínimo activo</small></template>
        <template slot="target" slot-scope="data">{{ number(data.item.target) }}</template>
        <template slot="coverage" slot-scope="data">{{ data.item.coverage === null ? '—' : number(data.item.coverage) + ' días' }}</template>
        <template slot="suggested" slot-scope="data"><strong>{{ data.item.suggested === null ? '—' : number(data.item.suggested) }}</strong></template>
        <template slot="state" slot-scope="data"><span :class="['replenishment-status', data.item.state]">{{ statusLabel(data.item.state) }}</span></template>
        <template slot="actions" slot-scope="data"><b-button size="sm" variant="info" @click="edit(data.item)"><i class="fas fa-pencil-alt" aria-hidden="true"></i> Mínimo</b-button></template>
      </b-table>
      <p v-if="snapshot && !loading" class="replenishment-footer">{{ visibleRows.length }} productos visibles. {{ mode === 'rotation' ? 'Desmarca “Solo alertas” para ver todos los productos del Top.' : 'En Stock mínimo, “Solo alertas” muestra existencias iguales o inferiores al mínimo y los stocks sin confirmar. Desmárcalo para ver todos los mínimos activos.' }} El pedido Excel incluye los productos visibles que necesitan reposición.</p>
    </section>
    <producto-form ref="productEditor" :show-button="false" :producto-update="selectedProduct" :cb-guardar="reloadAfterEdit" />
  </div>
</template>

<script>
import config from '@/store/config';
import loadExcel from '@/utils/excelLoader';
import ProductoForm from '@/components/ProductoForm';
const { periodDays, rankedProducts, minimumProducts, calculate, finite } = require('@/utils/replenishment');
const { createOrder } = require('@/utils/orderExcel');

export default {
  components: { ProductoForm },
  data() {
    return {
      warehouses: [], warehouseId: null, mode: 'rotation',
      today: this.$moment().format('YYYY-MM-DD'), from: this.$moment().subtract(29, 'days').format('YYYY-MM-DD'), to: this.$moment().format('YYYY-MM-DD'),
      coverageDays: 14, top: 50, topOptions: [{ value: 20, text: 'Top 20' }, { value: 50, text: 'Top 50' }, { value: 100, text: 'Top 100' }, { value: 0, text: 'Todos con ventas' }],
      alertsOnly: false, search: '', loading: false, exporting: false, error: '', rows: [], snapshot: null, selectedProduct: {}, requestId: 0,
      fields: [{ key: 'rank', label: '#' }, { key: 'ProveedorReposicion', label: 'Proveedor (A-Z)' }, { key: 'Producto', label: 'Producto' }, { key: 'CantidadVentas', label: 'Unid. vendidas' }, { key: 'current', label: 'Stock actual' }, { key: 'minimum', label: 'Stock mínimo' }, { key: 'coverage', label: 'Cobertura actual' }, { key: 'target', label: 'Objetivo' }, { key: 'suggested', label: 'Reponer' }, { key: 'state', label: 'Alerta' }, { key: 'actions', label: '' }]
    };
  },
  computed: {
    orderRows() { return this.visibleRows.filter(row => row.suggested !== null && row.suggested > 0); },
    topRows() { return this.mode === 'rotation' && this.top ? this.rows.slice(0, this.top) : this.rows; },
    visibleRows() {
      const query = this.search.trim().toLowerCase();
      return this.topRows.filter(row => (!this.alertsOnly || (this.mode === 'minimum' ? row.current === null || row.current <= row.minimum : row.state !== 'normal' || !row.configured)) && (!query || [row.Producto, row.CodigoBarra, row.ProductoMarca, row.ProveedorReposicion, row.RucProveedorReposicion].join(' ').toLowerCase().includes(query))).sort((a, b) => Number(!a.ProveedorReposicion) - Number(!b.ProveedorReposicion) || String(a.ProveedorReposicion || '').localeCompare(String(b.ProveedorReposicion || ''), 'es', { sensitivity: 'base' }) || Number(a.IdProveedorReposicion || 0) - Number(b.IdProveedorReposicion || 0) || String(a.Producto || '').localeCompare(String(b.Producto || ''), 'es'));
    }
  },
  async mounted() {
    try {
      const response = await this.axios.get(config.API + 'almacenes');
      if (!Array.isArray(response.data)) throw new Error('Respuesta de almacenes no válida.');
      this.warehouses = response.data;
      const preferred = this.warehouses.find(store => Number(store.IdAlmacen) === Number(sessionStorage.getItem('IdAlmacen'))) || this.warehouses[0];
      if (!preferred) throw new Error('No hay almacenes disponibles.');
      this.warehouseId = preferred.IdAlmacen;
      await this.load();
    } catch (error) { this.error = error.message || 'No se pudieron consultar los almacenes.'; }
  },
  beforeDestroy() { this.requestId += 1; },
  methods: {
    setMode(mode) { if (this.mode === mode || this.loading) return; this.mode = mode; this.alertsOnly = mode === 'minimum'; this.load(); },
    async load() {
      if (this.loading) return;
      const request = ++this.requestId;
      this.error = ''; this.rows = []; this.snapshot = null;
      try {
        const days = periodDays(this.from, this.to);
        if (this.to > this.today) throw new Error('El periodo de ventas no puede terminar en una fecha futura.');
        if (!this.warehouseId || !Number.isInteger(this.coverageDays) || this.coverageDays < 1 || this.coverageDays > 365) throw new Error('Selecciona un almacén y una cobertura entre 1 y 365 días.');
        this.loading = true;
        const query = { idAlmacen: this.warehouseId, fechaDesde: this.from, fechaHasta: this.to, limit: 0 };
        const response = await this.axios.get(config.API + 'productos/masrotacion', { params: query });
        if (!Array.isArray(response.data)) throw new Error('No se recibió el listado de rotación.');
        const products = this.mode === 'rotation' ? rankedProducts(response.data) : minimumProducts(response.data);
        const stock = {};
        for (let start = 0; start < products.length; start += 100) {
          if (request !== this.requestId) return;
          const ids = products.slice(start, start + 100).map(product => Number(product.IdProducto));
          const current = await this.axios.get(config.API + 'productos/stock/' + this.warehouseId, { params: { idProductos: ids, limit: 0 } });
          if (!Array.isArray(current.data)) throw new Error('No se pudieron confirmar las existencias actuales.');
          current.data.forEach(product => { stock[Number(product.IdProducto)] = finite(product.stock); });
        }
        for (let start = 0; start < products.length; start += 100) {
          const batch = products.slice(start, start + 100);
          const result = await this.axios.get(config.API + 'productos/proveedores-reposicion', { params: { idAlmacen: this.warehouseId, idProductos: batch.map(row => row.IdProducto) } });
          if (!Array.isArray(result.data)) throw new Error('No se pudieron consultar los proveedores.');
          batch.forEach(product => {
            const supplier = result.data.find(row => Number(row.IdProducto) === Number(product.IdProducto));
            if (!supplier) throw new Error('No se pudo confirmar el proveedor de un producto.');
            Object.assign(product, supplier);
          });
        }
        if (request !== this.requestId) return;
        this.rows = products.map((product, index) => calculate(product, stock[Number(product.IdProducto)], days, this.coverageDays, index + 1));
        if (this.mode === 'minimum') {
          this.rows.sort((a, b) => {
            const priority = row => row.current === null ? 0 : row.current <= row.minimum ? 1 : 2;
            return priority(a) - priority(b) || (a.current / a.minimum) - (b.current / b.minimum) || Number(b.CantidadVentas || 0) - Number(a.CantidadVentas || 0);
          });
          this.rows.forEach((row, index) => { row.rank = index + 1; });
        }
        const store = this.warehouses.find(item => String(item.IdAlmacen) === String(this.warehouseId));
        this.snapshot = { from: this.from, to: this.to, coverageDays: this.coverageDays, warehouseId: this.warehouseId, warehouse: store ? store.Almacen : '', time: this.$moment().format('HH:mm:ss') };
      } catch (error) {
        if (request === this.requestId) { this.error = error.response ? 'No se pudo completar la consulta de ventas y stock. Vuelve a actualizar.' : error.message; this.rows = []; }
      } finally { if (request === this.requestId) this.loading = false; }
    },
    count(state) { return this.topRows.filter(row => row.state === state).length; },
    number(value) { return Number(value).toLocaleString('es-PE', { maximumFractionDigits: 4 }); },
    displayDate(value) { return value.split('-').reverse().join('/'); },
    statusLabel(state) { return { urgent: 'Sin stock', reorder: 'Reponer', minimum: 'En mínimo', configure: 'Configurar mínimo', normal: 'Stock suficiente', unknown: 'Verificar stock' }[state]; },
    edit(product) { this.selectedProduct = product; this.$refs.productEditor.show = true; },
    reloadAfterEdit() { this.load(); },
    async exportOrder() {
      this.exporting = true;
      try {
        const snapshot = Object.assign({}, this.snapshot);
        let rows = this.orderRows.map(row => Object.assign({}, row));
        for (let start = 0; start < rows.length; start += 100) {
          const batch = rows.slice(start, start + 100);
          const response = await this.axios.get(config.API + 'productos/proveedores-reposicion', { params: { idAlmacen: snapshot.warehouseId, idProductos: batch.map(row => row.IdProducto) } });
          if (!Array.isArray(response.data)) throw new Error('No se pudieron consultar los proveedores.');
          const suppliers = {};
          response.data.forEach(row => { suppliers[Number(row.IdProducto)] = row; });
          batch.forEach(row => {
            if (!suppliers[Number(row.IdProducto)]) throw new Error('Falta confirmar el proveedor de un producto.');
            Object.assign(row, suppliers[Number(row.IdProducto)]);
          });
        }
        snapshot.pendingRows = rows.filter(row => Number(row.OrdenesPendientes) > 0);
        const pendingCount = snapshot.pendingRows.length;
        rows = rows.filter(row => !(Number(row.OrdenesPendientes) > 0));
        if (pendingCount) this.$toasted.show(pendingCount + ' productos omitidos por tener órdenes pendientes.');
        const Excel = await loadExcel();
        const buffer = await createOrder(Excel, rows, snapshot);
        const url = URL.createObjectURL(new Blob([buffer], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' }));
        const anchor = document.createElement('a'); anchor.href = url; anchor.download = 'pedido-reposicion-' + this.today + '.xlsx'; document.body.appendChild(anchor); anchor.click(); anchor.remove(); setTimeout(() => URL.revokeObjectURL(url), 1000);
        this.$toasted.success(rows.length + ' productos en Pedido; ' + pendingCount + ' en la hoja Pendientes. Excel agrupado por proveedor.');
      } catch (error) { this.$toasted.error(error.response ? 'No se pudo consultar proveedores y órdenes pendientes.' : error.message || 'No se pudo generar el pedido Excel.'); }
      finally { this.exporting = false; }
    },
    exportRows() {
      const rows = [['Almacén', 'Desde', 'Hasta', 'Cobertura objetivo (días)', 'Proveedor', 'RUC proveedor', 'Ordenes pendientes', 'Ranking', 'Código', 'Producto', 'Unidades vendidas', 'Stock actual', 'Stock mínimo', 'Mínimo activo', 'Objetivo', 'Reponer', 'Alerta']];
      this.visibleRows.forEach(row => rows.push([this.snapshot.warehouse, this.snapshot.from, this.snapshot.to, this.snapshot.coverageDays, row.ProveedorReposicion || 'Sin proveedor registrado', row.RucProveedorReposicion || '', Number(row.OrdenesPendientes) || 0, row.rank, row.CodigoBarra, row.Producto, row.CantidadVentas, row.current === null ? 'Sin dato' : row.current, row.minimum, row.configured ? 'Sí' : 'No', row.target, row.suggested === null ? '' : row.suggested, this.statusLabel(row.state)]));
      const escape = value => '"' + String(value == null ? '' : value).replace(/^[=+@-]/, "'$&").replace(/"/g, '""') + '"';
      const url = URL.createObjectURL(new Blob(['\ufeff' + rows.map(row => row.map(escape).join(';')).join('\r\n')], { type: 'text/csv;charset=utf-8' }));
      const anchor = document.createElement('a'); anchor.href = url; anchor.download = 'reposicion-stock-' + this.today + '.csv'; document.body.appendChild(anchor); anchor.click(); anchor.remove(); setTimeout(() => URL.revokeObjectURL(url), 1000);
    }
  }
};
</script>

<style>
.replenishment-toolbar { display: flex; align-items: center; gap: 12px; }
.replenishment-toolbar strong { flex: 1; color: #174a5f; font-size: 14px; }
.replenishment-load { display: flex; align-items: center; padding-top: 12px; }
.replenishment-options { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; padding-top: 8px; border-top: 1px solid #e2ecf1; }
.replenishment-options > .custom-select { max-width: 155px; }
.replenishment-options > .form-control { margin-left: auto; max-width: 330px; }
.replenishment-summary { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 10px; }
.replenishment-summary > div { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 12px 15px; border: 1px solid #d3e7ed; border-radius: 10px; background: #edf8fa; color: #24657b; font-size: 12px; }
.replenishment-summary strong { font-size: 24px; }
.replenishment-summary .is-urgent { color: #a5334c; background: #ffedf1; border-color: #f5d5df; }
.replenishment-summary .is-warning { color: #856110; background: #fff6de; border-color: #eee1bc; }
.replenishment-note, .replenishment-footer { padding: 5px 2px 10px; color: #607f91; font-size: 11px; line-height: 1.6; }
.replenishment-footer { margin: 5px 0 0; padding-bottom: 0; }
.replenishment-product-detail { display: block; margin-top: 3px; color: #78909e; font-size: 10px; }
.replenishment-status { display: inline-block; padding: 4px 8px; border-radius: 7px; background: #e0f4ed; color: #15735c; white-space: nowrap; font-size: 11px; font-weight: 700; }
.replenishment-status.urgent, .replenishment-status.unknown { color: #b3334f; background: #ffe5ec; }
.replenishment-status.reorder, .replenishment-status.minimum, .replenishment-status.configure { color: #8b6415; background: #fff0c9; }
@media(max-width: 767px) { .replenishment-toolbar { flex-wrap: wrap; } .replenishment-toolbar strong { order: 3; flex-basis: 100%; } .replenishment-summary { grid-template-columns: repeat(2, 1fr); } .replenishment-options > .form-control { max-width: none; } .replenishment-load { padding-top: 0; padding-bottom: 8px; } }
</style>
