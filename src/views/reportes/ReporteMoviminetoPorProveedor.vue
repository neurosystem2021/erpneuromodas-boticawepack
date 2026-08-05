<template>
  <b-container fluid class="supplier-report-page">
    <header class="supplier-report-hero"><div class="supplier-report-hero__icon"><i class="fas fa-truck-loading"></i></div><div><small>ANÁLISIS DE MOVIMIENTOS</small><h1>Ventas por proveedor</h1><p>Consulta productos, cantidades e importes asociados a cada proveedor.</p></div><span><i class="fas fa-file-excel"></i> Reporte exportable</span></header>
    <b-row class="supplier-filter-card">
        <b-col cols="3">
            <label><i class="far fa-calendar-alt"></i> Fecha desde</label>
            <input type="date" v-model="desde" class="form-control" >
        </b-col>
        <b-col cols="3">
            <label><i class="far fa-calendar-check"></i> Fecha hasta</label>
            <input type="date" v-model="hasta"  class="form-control" >
        </b-col>
        <b-col cols="3">
            <label><i class="fas fa-building"></i> Proveedor</label>
            <b-select :options="lstProveedores"
            text-field="Proveedor"
            value-field="IdProveedor"
            v-model="id_proveedor"
            />
        </b-col>
        <b-col cols="3" class="supplier-actions">
            <b-button variant="primary" @click="getObtenerReporte()"><i class="fas fa-search"></i> Generar</b-button>
            <b-button variant="success" :disabled="!lstReporte.length" @click="exportarReporte()"><i class="fas fa-file-excel"></i> Exportar Excel</b-button>
        </b-col>
        <b-col cols="12" class="supplier-summary">
            <b-row>
                <b-col cols="3" offset="9">
                    <small>TOTAL DEL REPORTE</small><strong>S/ {{ Number(total || 0).toFixed(2) }}</strong>
                </b-col>
            </b-row>
        </b-col>
        <b-col cols="12" class="supplier-results-card">
            <div class="supplier-results-title"><div><small>RESULTADOS</small><h2>Detalle de ventas</h2></div><span>{{ lstReporte.length }} registros</span></div>
            <b-table 
                striped
                hover
                :items="lstReporte"
                :fields="headers"
                :per-page="perPage"
                :current-page="currentPage"
                responsive
                show-empty
                empty-text="No se encontraron movimientos para los filtros seleccionados"
            ></b-table>
            <b-pagination
            v-model="currentPage"
            :total-rows="lstReporte.length"
            :per-page="perPage"
            aria-controls="my-table"
            ></b-pagination>
        </b-col>
       
    </b-row>
  </b-container>
</template>

<script>
import config from "@/store/config.js";
export default {
  data() {
    return {
      perPage: 10,
      currentPage: 1,
      lstProveedores: [],
      lstReporte: [],
      desde: "",
      hasta: "",
      id_proveedor: "",
      API: config.API,
      headers: [
        { key: "FechaReg", label: "Fecha del movimiento" },
        { key: "Producto", label: "Producto" },
        { key: "Precio", label: "Precio unitario", formatter: value => `S/ ${Number(value || 0).toFixed(2)}` },
        { key: "Cantidad", label: "Cantidad", formatter: value => Number(value || 0).toFixed(2) },
        { key: "Monto", label: "Importe total", formatter: value => `S/ ${Number(value || 0).toFixed(2)}` }
      ],
      total: 0
    };
  },
  mounted() {
    this.desde = this.$moment().startOf("month").format("YYYY-MM-DD");
    this.hasta = this.$moment().endOf("month").format("YYYY-MM-DD");
    this.cargarProveedores();
  },
  methods: {
    async cargarProveedores() {
      await this.axios.get(`${this.API}/proveedores`).then(response => {
        if (response.data) {
          this.lstProveedores = response.data;
        }
      });
    },
    async getObtenerReporte() {
      if (!this.desde || !this.hasta || !this.id_proveedor) {
        this.$toasted.show("Seleccione las fechas y un proveedor");
        return;
      }
      let data = {
        params: {
          id_proveedor: this.id_proveedor,
          fechaDesde: this.desde,
          fechaHasta: this.hasta
        }
      };
      await this.axios
        .get(`${this.API}reporte/movimiento/proveedores`, data)
        .then(response => {
          if (response.data) {
            this.lstReporte = response.data;
          }
        });
      this.total = this.lstReporte.reduce((sum, rep) => {
        return sum + parseFloat(rep.Monto);
      }, 0);
    },
    async exportarReporte() {
      if (!this.desde || !this.hasta || !this.id_proveedor) {
        this.$toasted.show("Seleccione las fechas y un proveedor");
        return;
      }
      let data = {
        params: {
          id_proveedor: this.id_proveedor,
          fechaDesde: this.desde,
          fechaHasta: this.hasta
        }
      };
      await this.axios
        .get(`${this.API}reporte/movimiento/proveedores/excel`, data)
        .then(response => {
          if (response.data) {
            window.open(response.data);
            // console.log(response.data);
          }
        });
      this.total = this.lstReporte.reduce((sum, rep) => {
        return sum + parseFloat(rep.Monto);
      }, 0);
    }
  }
};
</script>

<style>
.supplier-report-page{min-height:100vh;padding:14px 24px 34px!important;color:#173651;background:linear-gradient(145deg,#f3f8fc,#eaf3f9 60%,#f9fcfe)}.supplier-report-hero{display:flex;align-items:center;gap:14px;min-height:92px;margin-bottom:14px;padding:16px 22px;color:#fff;border-radius:20px;background:linear-gradient(112deg,#083562,#0b5787 65%,#078dac);box-shadow:0 14px 32px rgba(11,68,111,.18)}.supplier-report-hero__icon{display:grid;width:52px;height:52px;place-items:center;flex:0 0 52px;color:#a6ecff;border:1px solid rgba(255,255,255,.22);border-radius:15px;background:rgba(255,255,255,.1);font-size:20px}.supplier-report-hero small{color:#a9eaff;font-size:10px;font-weight:900;letter-spacing:1.4px}.supplier-report-hero h1{margin:0;font-size:27px;font-weight:900}.supplier-report-hero p{margin:2px 0 0;color:#d8edf8;font-size:12px}.supplier-report-hero>span{margin-left:auto;padding:9px 14px;border:1px solid rgba(255,255,255,.2);border-radius:999px;background:rgba(0,35,67,.2);font-size:11px;font-weight:800}.supplier-report-hero>span i{margin-right:6px;color:#75e5ae}
.supplier-filter-card{margin:0;padding:18px 14px 0;border:1px solid #d7e5ef;border-radius:19px;background:#fff;box-shadow:0 10px 28px rgba(28,70,105,.08)}.supplier-filter-card>.col-3{margin-bottom:17px}.supplier-filter-card label{display:block;margin-bottom:6px;color:#294961;font-size:12px;font-weight:900}.supplier-filter-card label i{width:18px;color:#0b77a8}.supplier-filter-card .form-control,.supplier-filter-card .custom-select{min-height:47px;color:#173651;border:1px solid #cedeea;border-radius:11px;background:#fbfdff;font-weight:700;box-shadow:none}.supplier-filter-card .form-control:focus,.supplier-filter-card .custom-select:focus{border-color:#168fc7;box-shadow:0 0 0 4px rgba(22,143,199,.12)}.supplier-actions{display:flex;align-items:end;gap:8px;padding-bottom:0}.supplier-actions .btn{min-height:47px;margin:0;border:0;border-radius:11px;font-size:12px;font-weight:900;box-shadow:0 7px 16px rgba(20,80,118,.14)}.supplier-actions .btn-primary{background:linear-gradient(135deg,#0a5688,#0787b2)}.supplier-actions .btn-success{background:linear-gradient(135deg,#078653,#18b46f)}
.supplier-summary{margin:0 0 14px}.supplier-summary>.row{margin:0}.supplier-summary .col-3{display:flex;align-items:center;justify-content:space-between;min-height:70px;padding:13px 18px;color:#fff;border-radius:15px;background:linear-gradient(120deg,#0a4f7e,#087b9f);box-shadow:0 9px 20px rgba(9,93,132,.18)}.supplier-summary small{font-size:9px;font-weight:900;letter-spacing:1.2px}.supplier-summary strong{font-size:22px;font-weight:900}.supplier-results-card{overflow:hidden;padding:0!important;border-top:1px solid #dfebf2}.supplier-results-title{display:flex;align-items:center;padding:15px 15px 12px}.supplier-results-title small{color:#0785bd;font-size:9px;font-weight:900;letter-spacing:1.3px}.supplier-results-title h2{margin:0;color:#153b5b;font-size:19px;font-weight:900}.supplier-results-title>span{margin-left:auto;padding:7px 12px;color:#0b638e;border-radius:999px;background:#e7f5fb;font-size:10px;font-weight:900}.supplier-results-card .table{margin:0}.supplier-results-card thead th{padding:12px;color:#fff;border:0;background:#0b5787;font-size:12px}.supplier-results-card tbody td{padding:12px;vertical-align:middle;border-color:#e2ebf1;font-size:12px}.supplier-results-card tbody tr:hover{background:#eaf7fc!important}.supplier-results-card .pagination{margin:15px}.supplier-results-card .page-link{color:#0b638e;border-color:#d7e5ef}.supplier-results-card .page-item.active .page-link{border-color:#0b638e;background:#0b638e}
@media(max-width:991px){.supplier-report-page{padding:10px!important}.supplier-report-hero>span{display:none}.supplier-filter-card>.col-3{max-width:50%;flex:0 0 50%}.supplier-summary .col-3{max-width:50%;flex:0 0 50%;margin-left:50%}}
@media(max-width:575px){.supplier-report-hero p{display:none}.supplier-report-hero h1{font-size:21px}.supplier-filter-card>.col-3{max-width:100%;flex:0 0 100%}.supplier-actions{align-items:stretch;flex-direction:column}.supplier-summary .col-3{max-width:100%;flex:0 0 100%;margin-left:0}}
</style>
