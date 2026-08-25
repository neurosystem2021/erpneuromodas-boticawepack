<template>
  <div class="pos-jobi">
    <b-container fluid class="px-4 px-md-5 mb-5 pos-container">
      <div class="pos-card">

        <b-breadcrumb :items="items" class="pos-breadcrumb"></b-breadcrumb>

        <!-- ================= FILTROS ================= -->
        <div class="pos-panel pos-panel-header">
          <b-row class="align-items-end pos-header-row">
            <b-col md="3">
              <b-form-group class="pos-field mb-0">
                <label><i class="fas fa-calendar-day pos-label-icon"></i> Fecha Inicio</label>
                <input type="date" class="form-control" v-model="fechaInicio">
              </b-form-group>
            </b-col>
            <b-col md="3">
              <b-form-group class="pos-field mb-0">
                <label><i class="fas fa-calendar-day pos-label-icon"></i> Fecha Fin</label>
                <input type="date" class="form-control" v-model="fechaFin">
              </b-form-group>
            </b-col>
            <b-col md="2">
              <b-form-group class="pos-field mb-0">
                <label><i class="fas fa-warehouse pos-label-icon"></i> Almacén</label>
                <b-form-select v-model="almacen"
                     value-field="IdAlmacen"
                     text-field="Almacen"
                    :options="lstAlmances"></b-form-select>
              </b-form-group>
            </b-col>
            <b-col md="1" class="pos-header-check">
              <div class="pos-mini-row-check">
                <b-form-checkbox
                  id="checkbox-1"
                  v-model="declarado"
                  name="checkbox-1"
                  value="1"
                  unchecked-value="0"
                >
                  Declarado
                </b-form-checkbox>
              </div>
            </b-col>
            <b-col md="3" class="pos-header-actions">
              <b-button class="pos-btn-generar" @click="generarReporte()"><i class="fas fa-search"></i> Generar</b-button>
              <b-button class="pos-btn-exportar" @click="exportarReporte()"><i class="fas fa-file-excel"></i> Exportar</b-button>
              <b-button class="pos-btn-exportar2" @click="exportarDetallado()"><i class="fa fa-file-excel" aria-hidden="true"></i> Contable</b-button>
            </b-col>
          </b-row>
        </div>

        <!-- ================= TABLA ================= -->
        <div class="pos-table-card">
          <div class="table-container">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                      <th>Id</th>
                      <th>Vendedor</th>
                      <th>Fecha Doc</th>
                      <th>Cod. Sunat</th>
                      <th>Tipo Doc.</th>
                      <th>Anulado</th>
                      <th>Serie</th>
                      <th>Número</th>
                      <th>SubTotal</th>
                      <th>IGV</th>
                      <th>Total</th>
                      <th></th>
                    </tr>
                </thead>
                <tbody class="scrollable-tbody">
                    <tr v-for="(reporte,index) in lstReporte" :key="index" @click="abrirModalDetalle(reporte)" :class="{'clsAnulado': reporte.Anulado == 1}">
                        <td style="text-align: center;">{{reporte.idDocVenta}}</td>
                        <td>{{reporte.UsuarioReg}}</td>
                        <td style="text-align: center;">{{reporte.FechaDoc}}</td>
                        <td style="text-align: center;">{{reporte.CodSunat}}</td>
                        <td style="text-align: center;">{{reporte.TipoDoc}}</td>
                        <td style="text-align: center;">
                          <span class="pos-badge-estado" :class="reporte.Anulado == 1 ? 'pos-badge-anulado' : 'pos-badge-activo'">
                            {{reporte.Anulado ==1 ? 'Anulado' :'Activo' }}
                          </span>
                        </td>
                        <td style="text-align: center;">{{reporte.Serie}}</td>
                        <td style="text-align: center;">{{reporte.Numero}}</td>
                        <td style="text-align: center;">{{reporte.SubTotal}}</td>
                        <td style="text-align: center;">{{reporte.Igv}}</td>
                        <td style="text-align: center;">{{reporte.Total}}</td>
                        <td style="text-align: center;"> <i v-if="reporte.Anulado ==0" class="fas fa-trash pos-icon-eliminar" aria-hidden="true" @click.stop="eliminarReg(reporte)"></i> </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="pos-tfoot-row">
                        <td colspan="10">Total</td>
                        <td style="text-align: center;">{{footerTotal}}</td>
                        <td colspan="1"></td>
                    </tr>
                </tfoot>
            </table>
          </div>
        </div>

      </div>
    </b-container>

    <b-modal v-model="dialogDetalle" size="lg" modal-class="modalRegVentaDetalle pos-jobi-modal" :title="title">
        <div class="pos-table-card pos-table-card-modal">
          <table class="table table-hover table-striped">
            <thead>
              <tr>
                <th>IdDocVenta</th>
                <th>Cod. Barra</th>
                <th>Producto</th>
                <th>Marca</th>
                <th>Medición</th>
                <th>Cantidad</th>
                <th>Precio</th>
                <th>Descuento</th>
                <th>Total</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(det,index) in detalle" :key="index" >
                <td>{{ det.IdDocVenta }}</td>
                <td>{{ det.CodigoBarra }}</td>
                <td>{{ det.Producto }}</td>
                <td>{{ det.ProductoMarca }}</td>
                <td>{{ det.ProductoMedicion }}</td>
                <td>{{ det.Cantidad }}</td>
                <td>{{ det.Precio }}</td>
                <td>{{ det.Descuento }}</td>
                <td>{{ obtenerTotal(det) }}</td>
              </tr>
            </tbody>
          </table>
        </div>
    </b-modal>
  </div>
</template>

<script>
import config from "@/store/config.js";
export default {
  data() {
    return {
      dialogDetalle: false,
      fechaInicio: "",
      fechaFin: "",
      almacen: 1,
      API: config.API,
      lstReporte: [],
      declarado: 1,
      title: "Detalle Reg. Ventas:",
      items: [
        {
          text: "VENTAS",
          href: "#"
        },
        {
          text: "REPORTE",
          href: "#"
        },
        {
          text: "REGISTRO VENTAS",
          active: true
        }
      ],

      lstAlmances: [],
      footer: {
        Producto: "TOTAL",
        Cantidad: 0,
        PrecioCosto: 0,
        PrecioVenta: 0,
        TotalVenta: 0,
        TotalCosto: 0,
        UtilidadBruta: 0,
        PorcentajeUti: 0
      },
      detalle: []
    };
  },
  mounted() {
    this.fechaInicio = this.$moment()
      .startOf("month")
      .format("YYYY-MM-DD");
    this.fechaFin = this.$moment()
      .endOf("month")
      .format("YYYY-MM-DD");
    this.obtenerAlmance();
    this.generarReporte();
  },
  methods: {
    async abrirModalDetalle(item) {
      this.title = `${this.title} ${item.Serie}-${item.Numero}`;
      await this.axios(`${this.API}reporte/ListarRegVenta/detalle`, {
        params: { idDocVenta: item.idDocVenta }
      }).then(res => {
        if (res.data) {
          this.detalle = res.data;
        }
      });
      this.dialogDetalle = true;
    },
    eliminarReg(item) {
      this.$swal({
        icon: "question",
        title: "",
        text: `Desea anular el registro: ${item.Serie}-${item.Numero}`,
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false,
        showCancelButton: true,
        cancelButtonColor: "#d33",
        confirmButtonText: "Si,Eliminar",
        cancelButtonText: "Cancelar"
      }).then(async response => {
        if (response.isConfirmed) {
          let data = {
            idDocVenta: item.idDocVenta
          };
          await this.axios
            .put(`${this.API}/reporte/ListarRegVenta/anular`, data)
            .then(res => {
              console.log(res);
              this.generarReporte();
            });
        }
      });
    },
    async obtenerAlmance() {
      await this.axios(`${this.API}almacenes`).then(res => {
        if (res.data) {
          this.lstAlmances = res.data;
          this.lstAlmances.unshift({
            Almacen: "TODOS",
            IdAlmacen: 0
          });
        }
      });
    },
    obtenerTotal(item) {
      let total =
        item.Cantidad * item.Precio - (item.Descuento ? item.Descuento : 0);
      console.log(total);
      return parseFloat(total).toFixed(2);
    },
    async exportarReporte() {
      let params = {
        fechaIni: this.fechaInicio,
        fechaFin: this.fechaFin,
        declarado: this.declarado,
        almacen: this.almacen
      };
      await this.axios(`${this.API}reporte/ListarRegVenta/excel`, {
        params: params
      }).then(res => {
        let url = res.data;
        url = url.replace(/\\/g, "");
        if (!url.startsWith("http://") && !url.startsWith("https://")) {
          url = "http://" + url; // O "https://" si es necesario
        }
        window.open(url, "_blank");
      });
      },
    async exportarDetallado() {
      let params = {
        fechaIni: this.fechaInicio,
        fechaFin: this.fechaFin,
        declarado: this.declarado,
        almacen: this.almacen
      };
      await this.axios(`${this.API}reporte/ListarRegVentaActivos/excel`, {
        params: params
      }).then(res => {
        let url = res.data;
        url = url.replace(/\\/g, "");
        if (!url.startsWith("http://") && !url.startsWith("https://")) {
          url = "http://" + url; // O "https://" si es necesario
        }
        window.open(url, "_blank");
      });
    },
    async generarReporte() {
      this.lstReporte = [];
      let params = {
        fechaIni: this.fechaInicio,
        fechaFin: this.fechaFin,
        declarado: this.declarado,
        almacen: this.almacen
      };
      await this.axios(`${this.API}reporte/ListarRegVenta`, {
        params: params
      }).then(res => {
        if (res.data) {
          let data = res.data;
          this.lstReporte = data;
          let Cantidad = 0;
          let PrecioCosto = 0;
          let PrecioVenta = 0;
          let TotalVenta = 0;
          let TotalCosto = 0;
          let UtilidadBruta = 0;
          let PorcentajeUti = 0;
          data.forEach(element => {
            Cantidad += element.Cantidad;
            PrecioCosto += element.PrecioCosto;
            PrecioVenta += element.PrecioVenta;
            TotalVenta += element.TotalVenta;
            TotalCosto += element.TotalCosto;
            UtilidadBruta += element.UtilidadBruta;
            PorcentajeUti += element.PorcentajeUti;
          });
          this.footer = {
            Producto: "TOTAL",
            Cantidad: parseFloat(Cantidad).toFixed(2),
            PrecioCosto: parseFloat(PrecioCosto).toFixed(2),
            PrecioVenta: parseFloat(PrecioVenta).toFixed(2),
            TotalVenta: parseFloat(TotalVenta).toFixed(2),
            TotalCosto: parseFloat(TotalCosto).toFixed(2),
            UtilidadBruta: parseFloat(UtilidadBruta).toFixed(2),
            PorcentajeUti: parseFloat(PorcentajeUti).toFixed(2)
          };
        }
      });
    }
  },
  computed: {
    footerTotal() {
      let total = 0;
      if (this.lstReporte.length > 0) {
        total = this.lstReporte.reduce((sum, reporte) => {
          return sum + reporte.Total;
        }, 0);
      }
      return parseFloat(total).toFixed(2);
    }
  }
};
</script>

<style>
/* Estilos globales (no scoped) porque el modal de Bootstrap-Vue
   se monta fuera del árbol del componente */
.modalRegVentaDetalle .modal-dialog {
  max-width: 90% !important;
  width: 90% !important;
}

.modalRegVentaDetalle .modal-content {
  border-radius: 16px !important;
  border: none !important;
  overflow: hidden;
}

.modalRegVentaDetalle .modal-header {
  background: linear-gradient(135deg, #0B2A4A 0%, #123A63 100%);
  color: #fff;
  border: none;
}

.modalRegVentaDetalle .modal-header .close {
  color: #fff;
  opacity: .8;
}

.clsAnulado {
  background: #ffcdd2 !important;
}
</style>

<style scoped>
/* =========================================================
   RegistroVentas.vue - mismo lenguaje visual JoBi que el
   resto de módulos (panel navy de filtros + tarjeta de tabla
   con thead/tfoot pegajosos).
   ========================================================= */
.pos-jobi {
    --jobi-navy: #0B2A4A;
    --jobi-navy-2: #123A63;
    --jobi-teal: #0F9B8E;
    --jobi-blue: #2F6FED;
    --jobi-green: #17A554;
    --jobi-red: #E5484D;
    --jobi-orange: #F5A623;
    --jobi-bg: #EEF3F8;
    background: var(--jobi-bg);
    min-height: 100vh;
    padding-bottom: 10px !important;
    font-family: -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    width: 100%;
}

.pos-container {
    padding-top: 10px !important;
}

.pos-card {
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 12px 30px rgba(11,42,74,.10);
    padding: 14px 18px 18px !important;
}

.pos-breadcrumb {
    background: transparent !important;
    padding: 0 0 10px 0 !important;
    margin-bottom: 6px !important;
    font-weight: 700;
}
.pos-breadcrumb >>> .breadcrumb-item.active {
    color: var(--jobi-blue);
    font-weight: 800;
}

/* -------- Panel de filtros navy -------- */
.pos-panel-header {
    background: linear-gradient(135deg, var(--jobi-navy) 0%, var(--jobi-navy-2) 100%);
    border-radius: 14px;
    padding: 16px 18px;
    margin-bottom: 18px;
    color: #fff;
}
.pos-panel-header label {
    color: rgba(255,255,255,.72);
    font-size: .70rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .05em;
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    gap: 5px;
}
.pos-label-icon { font-size: .82em; opacity: .85; }

.pos-field >>> input,
.pos-field >>> select,
.pos-field >>> .form-control,
.pos-field input.form-control {
    border-radius: 10px !important;
    background: #fff !important;
    color: var(--jobi-navy);
    font-weight: 700;
    height: 42px;
    border: none !important;
    box-shadow: 0 3px 8px rgba(0,0,0,.15);
}
.pos-field >>> input:focus,
.pos-field >>> .form-control:focus,
.pos-field input.form-control:focus {
    box-shadow: 0 0 0 3px rgba(47,111,237,.30);
}

.pos-header-check {
    display: flex;
    align-items: center;
}
.pos-mini-row-check {
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.14);
    border-radius: 10px;
    padding: 10px 12px;
    width: 100%;
}
.pos-mini-row-check >>> .custom-control-label {
    font-weight: 700;
    color: #fff;
}

.pos-header-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}
.pos-btn-generar,
.pos-btn-exportar {
    border-radius: 999px !important;
    border: none !important;
    height: 42px;
    padding: 0 16px !important;
    font-weight: 800 !important;
    text-transform: uppercase;
    font-size: .74rem;
    letter-spacing: .02em;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 6px 14px rgba(0,0,0,.18);
    color: #fff !important;
}
.pos-btn-exportar2 {
    border-radius: 999px !important;
    border: none !important;
    height: 42px;
    padding: 0 16px !important;
    font-weight: 800 !important;
    text-transform: uppercase;
    font-size: .74rem;
    letter-spacing: .02em;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 6px 14px rgba(0,0,0,.18);
    color: #fff !important;
    background-color: #d3ae0c;
}
.pos-btn-generar { background: var(--jobi-blue) !important; }
.pos-btn-exportar { background: var(--jobi-green) !important; }
.pos-btn-generar:hover,
.pos-btn-exportar:hover { color: #fff !important; transform: translateY(-1px); }

/* -------- Tarjeta que envuelve la tabla -------- */
.pos-table-card {
    background: #fff;
    border-radius: 14px;
    padding: 6px;
    box-shadow: 0 4px 14px rgba(11,42,74,.06);
    border: 1px solid #e7edf5;
}
.pos-table-card-modal {
    box-shadow: none;
    border: none;
    padding: 0;
}

.pos-table-card >>> table,
.pos-table-card-modal >>> table {
    margin-bottom: 0;
}

.pos-table-card >>> thead th,
.pos-table-card-modal >>> thead th {
    background: var(--jobi-navy) !important;
    color: #fff !important;
    text-transform: uppercase;
    font-size: .68rem;
    letter-spacing: .03em;
    font-weight: 800;
    border: none !important;
    padding: 10px 8px;
    white-space: nowrap;
}
.pos-table-card >>> thead th:first-child { border-top-left-radius: 10px; }
.pos-table-card >>> thead th:last-child { border-top-right-radius: 10px; }

.pos-table-card >>> tbody td,
.pos-table-card-modal >>> tbody td {
    vertical-align: middle;
    font-size: .84rem;
    padding: 8px;
    border-color: #eef2f7;
}
.pos-table-card >>> tbody tr {
    cursor: pointer;
}
.pos-table-card >>> tbody tr:hover {
    background-color: #f5f9ff !important;
}

/* Contenedor con scroll y thead/tfoot pegajosos, tal como el original */
.table-container {
    max-height: 680px;
    overflow-y: auto;
    position: relative;
}
.table-container >>> thead th {
    position: sticky;
    top: 0;
    z-index: 2;
}
.table-container >>> tfoot td {
    position: sticky;
    bottom: 0;
    z-index: 1;
}

.pos-tfoot-row td {
    background: var(--jobi-navy) !important;
    color: #fff !important;
    font-weight: 800;
}

/* Badges de estado */
.pos-badge-estado {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 999px;
    font-size: .72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .02em;
}
.pos-badge-activo {
    background: #e6f7ef;
    color: #0f6b3f;
}
.pos-badge-anulado {
    background: #fdeceb;
    color: #a3231f;
}

.pos-icon-eliminar {
    color: var(--jobi-red);
    cursor: pointer;
    font-size: 1rem;
    transition: transform .15s ease;
}
.pos-icon-eliminar:hover {
    transform: scale(1.15);
}

@media (max-width: 991px) {
    .pos-header-check,
    .pos-header-actions {
        margin-top: 10px;
    }
}
</style>