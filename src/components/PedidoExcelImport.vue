<template>
  <span class="pedido-excel-import">
    <b-button variant="success" :disabled="busy" @click="open"><i class="fas fa-file-excel" aria-hidden="true"></i> Cargar pedido Excel</b-button>
    <b-modal v-model="show" title="Cargar pedido de reposición" size="lg" modal-class="nst-dialog" :no-close-on-backdrop="busy" :no-close-on-esc="busy" :hide-header-close="busy" hide-footer>
      <p>Selecciona el archivo .xlsx generado en Reposición / rotación. Los precios están en soles, sin IGV. Selecciona un proveedor para cargar su grupo de productos. Los productos que ya estén en el documento se omiten.</p>
      <input type="file" accept=".xlsx" :disabled="busy" aria-label="Archivo Excel de pedido" @change="readFile">
      <b-alert :show="!!error" variant="danger" class="mt-3">{{ error }}</b-alert>
      <b-form-group v-if="order && supplierOptions.length > 1" label="Proveedor del pedido" class="mt-3">
        <b-form-select v-model="supplierId" :options="supplierOptions" :disabled="busy" @change="preparePreview" />
      </b-form-group>
      <b-alert :show="!!supplierNote" variant="info" class="mt-3">{{ supplierNote }}</b-alert>
      <p v-if="busy" role="status" class="mt-3">Validando productos y existencias…</p>
      <template v-if="preview.length && !busy">
        <p class="mt-3"><strong>{{ preview.length }} productos listos para agregar.</strong> Revisa proveedor, cantidades y precios antes de generar el documento.</p>
        <b-table small responsive :items="preview" :fields="fields" />
        <b-button variant="primary" @click="apply">{{ destination === 'order' ? 'Agregar a la orden de compra' : 'Agregar productos a la compra' }}</b-button>
      </template>
      <b-button class="mt-2" variant="outline-secondary" :disabled="busy" @click="show = false">Cerrar</b-button>
    </b-modal>
  </span>
</template>
<script>
import config from '@/store/config';
import loadExcel from '@/utils/excelLoader';
const { readOrder, supplierRows } = require('@/utils/orderExcel');
export default {
  props: { movement: Object, igvflag: [Number, Boolean, String], destination: { type: String, default: 'purchase' } },
  computed: {
    supplierOptions() {
      if (!this.order) return [];
      const groups = {};
      this.order.rows.forEach(row => {
        if (row.supplierId) {
          if (!groups[row.supplierId]) groups[row.supplierId] = { value: row.supplierId, text: (row.Proveedor || 'Proveedor ' + row.supplierId) + (row.RucProveedor ? ' · ' + row.RucProveedor : ''), count: 0 };
          groups[row.supplierId].count++;
        }
      });
      return [{ value: 0, text: 'Selecciona un proveedor' }].concat(Object.keys(groups).map(id => ({ value: groups[id].value, text: groups[id].text + ' (' + groups[id].count + ' productos)' })));
    }
  },
  data() { return { show: false, busy: false, error: '', preview: [], order: null, supplierNote: '', supplierId: 0, fields: [{ key: 'CodigoBarra', label: 'Código' }, { key: 'Producto', label: 'Producto' }, { key: 'cantidad', label: 'Cantidad' }, { key: 'precio', label: 'Precio S/' }, { key: 'stock', label: 'Stock actual' }] }; },
  methods: {
    open() {
      this.error = ''; this.preview = []; this.supplierNote = '';
      this.supplierId = Number(this.movement.proveedor && this.movement.proveedor.IdProveedor) || 0;
      this.show = true;
      if (this.order && this.supplierId) this.preparePreview();
    },
    checkDestination(order) {
      if (this.destination === 'order') {
        if (Number(this.movement.almacen.IdAlmacen) !== order.warehouseId) throw new Error('Selecciona el almacén del pedido (ID ' + order.warehouseId + ').');
        return;
      }
      if (Number(this.movement.movimientoTipo.Tipo) !== 0) throw new Error('Selecciona primero un tipo de movimiento de ingreso / compra.');
      if (Number(this.movement.almacenDestino.IdAlmacen) !== order.warehouseId) throw new Error('Selecciona el almacén destino del pedido (ID ' + order.warehouseId + ').');
      if (this.movement.Moneda !== order.currency) throw new Error('Selecciona la moneda PEN (soles) en la compra para cargar los precios del pedido.');
    },
    async readFile(event) {
      const file = event.target.files[0];
      this.error = ''; this.preview = []; this.order = null; this.supplierNote = ''; this.supplierId = 0;
      if (!file) return;
      if (!/\.xlsx$/i.test(file.name) || file.size > 5 * 1024 * 1024) { this.error = 'Selecciona un archivo .xlsx de hasta 5 MB.'; return; }
      this.busy = true;
      try {
        const Excel = await loadExcel();
        const buffer = await new Promise((resolve, reject) => { const reader = new FileReader(); reader.onload = () => resolve(reader.result); reader.onerror = () => reject(new Error('No se pudo leer el archivo.')); reader.readAsArrayBuffer(file); });
        const order = await readOrder(Excel, buffer);
        this.order = order;
        if (this.destination === 'order' && !Number(this.movement.almacen.IdAlmacen)) {
          const response = await this.axios.get(config.API + 'almacenes');
          const warehouse = response.data.find(item => Number(item.IdAlmacen) === order.warehouseId);
          if (!warehouse) throw new Error('No se encontró el almacén del pedido.');
          this.$emit('select-warehouse', warehouse);
        }
        this.supplierId = Number(this.movement.proveedor && this.movement.proveedor.IdProveedor) || 0;
        if (!this.supplierId && this.supplierOptions.length === 2) this.supplierId = this.supplierOptions[1].value;
        if (this.supplierOptions.length > 1 && !this.supplierId) return;
        await this.preparePreview();
      } catch (error) { this.error = error.response ? 'Falló la consulta de productos. Intenta nuevamente.' : error.message; }
      finally { this.busy = false; event.target.value = ''; }
    },
    async preparePreview() {
      this.busy = true; this.error = ''; this.preview = []; this.supplierNote = '';
      try {
        const order = this.order;
        this.checkDestination(order);
        const selectedId = Number(this.movement.proveedor && this.movement.proveedor.IdProveedor) || 0;
        const targetId = Number(this.supplierId) || selectedId;
        if (targetId && targetId !== selectedId) {
          if (this.destination !== 'order') throw new Error('Selecciona ese proveedor en la compra antes de cargar el pedido.');
          if (this.$store.state.cart.productos.length) throw new Error('Genera o vacía la orden actual antes de cambiar de proveedor.');
          const row = order.rows.find(item => Number(item.supplierId) === targetId);
          const response = await this.axios.get(config.API + 'proveedores', { params: { q: row.RucProveedor || row.Proveedor || '', limit: 10000 } });
          const supplier = response.data.find(item => Number(item.IdProveedor) === targetId);
          if (!supplier) throw new Error('No se encontró el proveedor del pedido.');
          this.$emit('select-supplier', supplier);
        }
        const allRows = supplierRows(order, targetId);
        if (this.destination === 'order' && !targetId) throw new Error('Este archivo no identifica al proveedor. Selecciona un proveedor en la orden o vuelve a generar el Excel de reposición.');
        let selectedRows = allRows.filter(row => !this.$store.state.cart.productos.some(item => Number(item.IdProducto) === row.id));
        const inDocument = allRows.length - selectedRows.length;
        let pendingCount = 0;
        if (this.destination === 'order') {
          const pending = new Set();
          for (let start = 0; start < selectedRows.length; start += 100) {
            const response = await this.axios.get(config.API + 'productos/proveedores-reposicion', { params: { idAlmacen: order.warehouseId, idProductos: selectedRows.slice(start, start + 100).map(row => row.id) } });
            if (!Array.isArray(response.data)) throw new Error('No se pudieron consultar las órdenes pendientes.');
            response.data.forEach(row => { if (Number(row.OrdenesPendientes) > 0) pending.add(Number(row.IdProducto)); });
          }
          pendingCount = selectedRows.filter(row => pending.has(row.id)).length;
          selectedRows = selectedRows.filter(row => !pending.has(row.id));
        }
        this.supplierNote = inDocument + ' productos ya cargados y ' + pendingCount + ' con órdenes pendientes omitidos. ' + selectedRows.length + ' productos nuevos para este proveedor.';
        if (order.rows.some(row => row.supplierId)) {
          this.supplierId = targetId;
        }
        const products = {};
        for (let start = 0; start < selectedRows.length; start += 100) {
          const ids = selectedRows.slice(start, start + 100).map(row => row.id);
          const response = await this.axios.get(config.API + 'productos/stock/' + order.warehouseId, { params: { idProductos: ids, limit: 0 } });
          if (!Array.isArray(response.data)) throw new Error('No se pudieron validar los productos del pedido.');
          response.data.forEach(product => { products[Number(product.IdProducto)] = product; });
        }
        const preview = selectedRows.map(row => {
          const product = products[row.id];
          if (!product || Number(product.Anulado || 0) !== 0 || Number(product.ControlaStock) !== 1) throw new Error('El producto ID ' + row.id + ' no está activo o no controla stock.');
          if (String(product.CodigoBarra || '') !== row.code) throw new Error('El código del producto ID ' + row.id + ' no coincide con el catálogo. Vuelve a generar el pedido.');
          return Object.assign({}, product, { cantidad: row.quantity, precio: row.price, cantidadPres: 1, cantxPrecio: 1, estadoPxP: true,
            nuevoPrecioContado: Number(product.PrecioContado) || 0, descuento: 0, FLETE: 0, TieneIgv: Number(this.igvflag) || 0,
            Descripcion: [product.ProductoDesc, product.ProductoDesc2, product.ProductoDesc3].filter(Boolean).join('\n') });
        });
        this.order = order; this.preview = preview;
      } catch (error) { this.error = error.response ? 'Falló la consulta de productos. Intenta cargar el pedido nuevamente.' : error.message || 'No se pudo leer el pedido Excel.'; }
      finally { this.busy = false; }
    },
    apply() {
      try {
        this.checkDestination(this.order);
        if (this.supplierId && this.supplierId !== Number(this.movement.proveedor && this.movement.proveedor.IdProveedor)) throw new Error('Cambió el proveedor de la compra. Vuelve a cargar el pedido para ese proveedor.');
        const existing = this.$store.state.cart.productos;
        const pending = this.preview.filter(product => !existing.some(item => Number(item.IdProducto) === Number(product.IdProducto)));
        pending.forEach(product => {
          product.TieneIgv = Number(this.igvflag) || 0;
          this.$store.commit('AGREGAR_PRODUCTO', product);
        });
        this.show = false; this.$toasted.success(pending.length + ' productos cargados. Revisa el documento antes de guardar.');
      } catch (error) { this.error = error.message; }
    }
  }
};
</script>
