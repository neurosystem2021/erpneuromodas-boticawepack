<template>
<b-container fluid class="nst-screen nst-screen--reporteutilidadbrutaporusuario mb-5">
  <h1 class="my-3">Utilidad bruta de servicios</h1>
  <p>Venta menos descuentos y costo registrado. Incluye todos los productos marcados como servicio.</p>
  <section class="nst-panel nst-panel--filters">
    <b-row>
      <b-col cols="12" md="3"><label for="profit-start">Desde</label><input id="profit-start" type="date" v-model="fechaInicio" class="form-control"></b-col>
      <b-col cols="12" md="3"><label for="profit-end">Hasta</label><input id="profit-end" type="date" v-model="fechaFin" class="form-control"></b-col>
      <b-col cols="12" md="3"><label for="profit-user">Usuario</label><b-form-select id="profit-user" :options="lstUsuario" value-field="Usuario" text-field="NombreUsuario" v-model="usuarioReg" /></b-col>
      <b-col cols="12" md="3" class="pt-4"><b-form-checkbox value="1" unchecked-value="0" v-model="ServicioFlag">Servicios</b-form-checkbox><b-form-checkbox value="1" unchecked-value="0" v-model="MercaderiaFlag">Mercaderías</b-form-checkbox></b-col>
      <b-col cols="12" class="my-3"><b-button variant="info" :disabled="loading" @click="generar">{{ loading ? 'Consultando…' : 'Generar reporte' }}</b-button> <b-button variant="success" :disabled="loading || !lstReporte.length || dirty" @click="exportarCsv">Exportar Excel (CSV)</b-button></b-col>
    </b-row>
    <b-alert :show="!!error" variant="danger">{{ error }}</b-alert>
    <b-alert :show="dirty" variant="warning">Los filtros cambiaron. Genera el reporte para actualizar los resultados.</b-alert>
    <template v-if="generated">
      <p><strong>{{ periodo }}</strong></p>
      <b-row class="my-3">
        <b-col cols="12" md="4"><div class="profit-total"><span>Venta neta</span><strong>{{ money(totales.TotalVenta) }}</strong></div></b-col>
        <b-col cols="12" md="4"><div class="profit-total"><span>Costo</span><strong>{{ money(totales.TotalCosto) }}</strong></div></b-col>
        <b-col cols="12" md="4"><div class="profit-total"><span>Utilidad bruta</span><strong>{{ money(totales.UtilidadBruta) }}</strong></div></b-col>
      </b-row>
      <h2 class="h5">Resumen por usuario</h2>
      <b-table :fields="summaryFields" :items="porUsuario" responsive show-empty empty-text="No hay ventas para los filtros seleccionados." />
      <h2 class="h5 mt-4">Detalle por servicio, fecha y usuario</h2>
      <b-table id="service-profit-detail" :fields="fields" :items="lstReporte" :per-page="20" :current-page="currentPage" responsive show-empty empty-text="No hay ventas para los filtros seleccionados." />
      <b-pagination v-if="lstReporte.length > 20" v-model="currentPage" :total-rows="lstReporte.length" :per-page="20" aria-controls="service-profit-detail" />
      <p class="text-muted">Se excluyen ventas anuladas y documentos de tipo 11. Si el servicio no tiene costo registrado, la utilidad coincide con la venta neta. Los gastos generales no se descuentan en este reporte.</p>
    </template>
  </section>
</b-container>
</template>
<script>
import config from '@/store/config.js';
export default {
  data() {
    const currency = value => this.money(value);
    return {
      fechaInicio: this.$moment().startOf('month').format('YYYY-MM-DD'), fechaFin: this.$moment().format('YYYY-MM-DD'),
      usuarioReg: '', ServicioFlag: '1', MercaderiaFlag: '0', lstUsuario: [{ Usuario: '', NombreUsuario: 'TODOS LOS USUARIOS' }],
      lstReporte: [], currentPage: 1, loading: false, error: '', generated: false, applied: '', periodo: '',
      summaryFields: [{ key: 'UsuarioReg', label: 'Usuario' }, { key: 'TotalVenta', label: 'Venta neta', formatter: currency }, { key: 'TotalCosto', label: 'Costo', formatter: currency }, { key: 'UtilidadBruta', label: 'Utilidad bruta', formatter: currency }],
      fields: [{ key: 'FechaReg', label: 'Fecha' }, { key: 'UsuarioReg', label: 'Usuario' }, { key: 'Producto', label: 'Servicio / producto' }, { key: 'Cantidad', label: 'Cantidad' }, { key: 'TotalVenta', label: 'Venta neta', formatter: currency }, { key: 'TotalCosto', label: 'Costo', formatter: currency }, { key: 'UtilidadBruta', label: 'Utilidad bruta', formatter: currency }]
    };
  },
  computed: {
    filtros() { return { fechaInicio: this.fechaInicio, fechaFin: this.fechaFin, usuarioReg: this.usuarioReg, ServicioFlag: this.ServicioFlag, MercaderiaFlag: this.MercaderiaFlag }; },
    dirty() { return this.generated && this.applied !== JSON.stringify(this.filtros); },
    totales() { return this.sumar(this.lstReporte); },
    porUsuario() {
      const groups = {};
      this.lstReporte.forEach(row => { const user = row.UsuarioReg || '(Sin usuario)'; if (!groups[user]) groups[user] = []; groups[user].push(row); });
      return Object.keys(groups).sort().map(user => Object.assign({ UsuarioReg: user }, this.sumar(groups[user])));
    }
  },
  mounted() { this.cargarUsuarios(); this.generar(); },
  methods: {
    money(value) { return 'S/ ' + Number(value || 0).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); },
    sumar(rows) { return rows.reduce((sum, row) => { ['TotalVenta', 'TotalCosto', 'UtilidadBruta'].forEach(key => { sum[key] += Number(row[key]) || 0; }); return sum; }, { TotalVenta: 0, TotalCosto: 0, UtilidadBruta: 0 }); },
    async cargarUsuarios() {
      try { const response = await this.axios.get(config.API + 'lstusuarios'); if (Array.isArray(response.data)) this.lstUsuario = [{ Usuario: '', NombreUsuario: 'TODOS LOS USUARIOS' }].concat(response.data); }
      catch (e) { this.$toasted.error('No se pudo cargar la lista de usuarios.'); }
    },
    async generar() {
      if (this.loading) return;
      if (!this.fechaInicio || !this.fechaFin || this.fechaInicio > this.fechaFin) { this.error = 'Selecciona un rango de fechas válido.'; return; }
      if (String(this.ServicioFlag) !== '1' && String(this.MercaderiaFlag) !== '1') { this.error = 'Selecciona servicios o mercaderías.'; return; }
      this.loading = true; this.error = '';
      const filters = Object.assign({}, this.filtros);
      try {
        const response = await this.axios.get(config.API + 'reporte/utlidadbruta/usuario', { params: filters });
        if (!Array.isArray(response.data)) throw new Error('invalid report');
        this.lstReporte = response.data; this.currentPage = 1; this.generated = true; this.applied = JSON.stringify(filters);
        this.periodo = filters.fechaInicio + ' al ' + filters.fechaFin + ' · ' + (filters.usuarioReg || 'Todos los usuarios') + ' · ' + (String(filters.ServicioFlag) === '1' ? 'Servicios' : '') + (String(filters.MercaderiaFlag) === '1' ? ' Mercaderías' : '');
      } catch (e) { this.error = 'No se pudo generar el reporte. Revisa la conexión e inténtalo nuevamente.'; }
      finally { this.loading = false; }
    },
    exportarCsv() {
      const clean = value => '"' + String(value == null ? '' : value).replace(/^[=+@\-]/, "'").replace(/"/g, '""') + '"';
      const rows = [['UTILIDAD BRUTA', this.periodo], ['RESUMEN POR USUARIO'], ['Usuario', 'Venta neta', 'Costo', 'Utilidad bruta']];
      this.porUsuario.forEach(row => rows.push([row.UsuarioReg, row.TotalVenta.toFixed(2), row.TotalCosto.toFixed(2), row.UtilidadBruta.toFixed(2)]));
      rows.push(['TOTAL', this.totales.TotalVenta.toFixed(2), this.totales.TotalCosto.toFixed(2), this.totales.UtilidadBruta.toFixed(2)], [], ['DETALLE'], ['Fecha', 'Usuario', 'Servicio / producto', 'Cantidad', 'Venta neta', 'Costo', 'Utilidad bruta']);
      this.lstReporte.forEach(row => rows.push([row.FechaReg, row.UsuarioReg, row.Producto, row.Cantidad, row.TotalVenta, row.TotalCosto, row.UtilidadBruta]));
      const url = URL.createObjectURL(new Blob(['\uFEFF' + rows.map(row => row.map(clean).join(';')).join('\r\n')], { type: 'text/csv;charset=utf-8;' }));
      const link = document.createElement('a'); link.href = url; link.download = 'utilidad-servicios.csv'; document.body.appendChild(link); link.click(); link.remove(); setTimeout(() => URL.revokeObjectURL(url), 1000);
    }
  }
};
</script>
<style scoped>
.profit-total{padding:18px;margin-bottom:12px;border:1px solid #c9dfe5;border-radius:12px;background:#f0f9fa;display:flex;flex-direction:column;gap:8px}.profit-total strong{font-size:1.6rem;color:#00596b}
</style>