<template>
  <div class="remision-module">
    <b-container fluid class="px-4 py-3">

      <!-- Header / Filtros -->
      <b-card class="filtros-card mb-3" no-body>
        <b-card-body class="py-3">
          <b-row class="align-items-end">
            <b-col md="4">
              <label class="filtro-label">
                <i class="fas fa-calendar-alt mr-1"></i> Fecha Referencia
              </label>
              <b-form-input
                v-model="fechaReferencia"
                type="date"
                class="filtro-input"
                @input="setFechaReferencia">
              </b-form-input>
            </b-col>
            <b-col md="3">
              <label class="filtro-label">
                <i class="fas fa-filter mr-1"></i> Estado
              </label>
              <b-form-select
                v-model="estadoVenta"
                :options="optionsEstado"
                class="filtro-input"
                @input="$refs.tablaVentas.refresh()" />
            </b-col>
            <b-col md="5" class="text-md-right mt-3 mt-md-0">
              <span class="badge-total">
                <i class="fas fa-truck mr-1"></i>
                {{ totalRows }} guía(s)
              </span>
            </b-col>
          </b-row>
        </b-card-body>
      </b-card>

      <!-- Tabla -->
      <b-card class="tabla-card" no-body>
        <div class="table-responsive-remision">
          <b-table small
            ref="tablaVentas"
            :filter="filter"
            :sort-by.sync="sortBy"
            :sort-desc.sync="sortDesc"
            striped
            hover
            id="tabla-ventas"
            :busy.sync="isBusy"
            :items="myProvider"
            :fields="fields"
            :per-page="perPage"
            :current-page="currentPage"
            class="tabla-remision mb-0">

            <template #table-busy>
              <div class="text-center my-4">
                <b-spinner variant="primary" class="align-middle mr-2"></b-spinner>
                <strong>Cargando guías...</strong>
              </div>
            </template>

            <template slot="detalles" slot-scope="row">
              <b-button
                size="sm"
                class="btn-toggle-detalle"
                @click.stop="row.toggleDetails">
                <i :class="row.detailsShowing ? 'fas fa-minus' : 'fas fa-plus'"></i>
              </b-button>
            </template>

            <template slot="FechaTraslado" slot-scope="row">
              <span class="text-nowrap">
                <i class="far fa-clock mr-1 text-muted"></i>
                {{ row.item.FechaTraslado !== '0000-00-00 00:00:00' ? $moment(row.item.FechaTraslado, 'YYYY-MM-DD hh:mm:ss').format('YYYY-MM-DD') : '—' }}
              </span>
            </template>

            <template slot="PesoBruto" slot-scope="row">
              <span class="badge-peso">{{ row.item.PesoBruto }} kg</span>
            </template>

            <template slot="row-details" slot-scope="row">
              <b-card class="detalle-card" no-body>
                <b-card-body class="py-3 px-4">
                  <table class="table table-sm prod-detalle mb-2">
                    <thead>
                      <tr>
                        <th><i class="fas fa-barcode mr-1"></i> Código</th>
                        <th><i class="fas fa-box mr-1"></i> Producto</th>
                        <th class="text-center">Cantidad</th>
                        <th class="text-center">Unidad</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="(item, index) in getDetalles(row.item.IdDocVentaRemision)" :key="index">
                        <td>{{ item.CodigoBarra }}</td>
                        <td>{{ item.Producto }}</td>
                        <td class="text-center"><b>{{ item.Cantidad }}</b></td>
                        <td class="text-center">{{ item.CodigoMedicion }}</td>
                      </tr>
                    </tbody>
                  </table>
                  <b-button
                    v-if="row.item.Estado != '2'"
                    variant="primary"
                    size="sm"
                    class="btn-mover"
                    @click="moverAEmitidos(row.item)">
                    <i class="fas fa-arrow-right mr-1"></i> Mover a emitidos
                  </b-button>
                </b-card-body>
              </b-card>
            </template>

            <template slot="estado" slot-scope="data">
              <div class="d-flex align-items-center flex-wrap acciones-estado">
                <span
                  v-if="data.item.Estado == 2"
                  v-b-tooltip.hover
                  :title="data.item.Msj_sunat"
                  class="estado-badge estado-ok">
                  <i class="fas fa-check-circle"></i> Emitido
                </span>
                <div v-if="data.item.Msj_sunat && data.item.Estado != 2" class="d-inline-block">
                  <span
                    :id="'tooltip-error-' + data.item.IdDocVentaRemision"
                    class="estado-badge estado-error">
                    <i class="fas fa-exclamation-triangle"></i> Error
                  </span>

                  <b-tooltip
                    :target="'tooltip-error-' + data.item.IdDocVentaRemision"
                    placement="top"
                    triggers="hover"
                    container="body"
                    boundary="window">
                    {{ data.item.Msj_sunat }}
                  </b-tooltip>
                </div>

                <b-button
                  v-if="estadoVenta != 2"
                  size="sm"
                  class="btn-accion btn-emitir"
                  @click="emitirElectronico(data.item)">
                  <i class="fas fa-paper-plane mr-1"></i> Emitir
                </b-button>
                <b-button
                  size="sm"
                  class="btn-accion btn-pdf"
                  @click="regenerarPDF(data.item)">
                  <i class="fas fa-file-pdf mr-1"></i> PDF
                </b-button>
                <b-button
                  size="sm"
                  class="btn-accion btn-descarga"
                  @click="regenerarDescarga(data.item)">
                  <i class="fas fa-download mr-1"></i> Descargar
                </b-button>
              </div>
            </template>

          </b-table>
        </div>
      </b-card>

    </b-container>
  </div>
</template>

<script>
import config from "@/store/config.js";
import ProductoKardex from "@/components/ProductoKardex";
export default {
  beforeMount() {
    this.generateFilter("fechaReferencia", this.fechaReferencia);
  },
  data() {
    return {
      API: config.API,
      loading: false,
      estadoVenta: 1,
      optionsEstado: [
        { value: "1", text: "PENDIENTE" },
        { value: "2", text: "EMITIDOS" }
      ],
      fechaInicio: this.$moment().format("YYYY-MM-DD"),
      fechaFin: this.$moment().format("YYYY-MM-DD"),
      fechaReferencia: this.$moment().format("YYYY-MM-DD"),
      filters: {},
      filter: "",
      sortBy: "FechaReg",
      sortDesc: false,
      items: [],
      itemsDetalle: [],
      isBusy: false,
      fields: [
        { key: "detalles", label: "" },
        { key: "IdDocVentaRemision", label: "ID", sortable: true },
        { key: "Serie", label: "Serie", sortable: true },
        { key: "Numero", sortable: true },
        { key: "FechaTraslado", label: "Fecha Traslado", sortable: true },
        { key: "PesoBruto", label: "Peso Total", sortable: true },
        {
          key: "RazonSocialCliente",
          label: "Razón Social Cliente",
          sortable: true
        },
        { key: "DireccionOrigen", label: "Dirección Origen" },
        { key: "DireccionDestino", label: "Dirección Destino" },
        { key: "estado", label: "Acciones", sortable: false, class: "text-center" }
      ],
      totalRows: 50,
      perPage: 10,
      currentPage: 1,
      itemsStatus: {},
      isBoleta: false
    };
  },
  watch: {
    items(items) {
      if (items.length) {
        this.axios
          .get(this.API + "remision/detalle", {
            params: {
              idRemisiones: items.map(item => item.IdDocVentaRemision)
            }
          })
          .then(response => {
            this.itemsDetalle = response.data;
            this.items.map(item => {
              item.productos = this.getDetalles(item.IdDocVentaRemision);
              return item;
            });
          })
          .catch(error => {
            console.log(error);
          });
      }
    }
  },
  methods: {
    async regenerarPDF(doc) {
      this.$refs.tablaVentas.refresh();
      this.loading = true;
      await this.axios
        .post(this.API + "generarpdfguiaelectronica/" + doc.IdDocVentaRemision)
        .then(res => {
          console.log(res);
          this.$toasted.success("El PDF fue generado nuevamente", {
            duration: 7000
          });
          this.loading = false;
        });
    },

    regenerarDescarga(doc) {
      this.$refs.tablaVentas.refresh();
      this.loading = true;

      this.axios.get(this.API + 'imprimirguiapdf/' + doc.IdDocVentaRemision, { responseType: 'blob' })
        .then(res => {

          const url = window.URL.createObjectURL(new Blob([res.data], { type: 'application/pdf' }));

          const link = document.createElement('a');
          link.href = url;
          link.setAttribute('download', 'Guia-' + doc.IdDocVentaRemision + '.pdf');
          document.body.appendChild(link);
          link.click();

          document.body.removeChild(link);
          window.URL.revokeObjectURL(url);

          this.$toasted.success('El PDF fue generado y descargado', { duration: 7000 });
          this.loading = false;
        })
        .catch(err => {
          console.error(err);
          this.$toasted.error('Hubo un error al generar el PDF');
          this.loading = false;
        });
      },

    setFechaReferencia() {
      this.generateFilter("fechaReferencia", this.fechaReferencia);

      this.$nextTick(() => {
        this.$refs.tablaVentas.refresh();
      });
    },
    getStatus(idDocVenta) {
      let obj = this.itemsStatus[idDocVenta] || {};
      if (obj.Estado) {
        obj.image = obj.Estado == 2 ? "success.png" : "error.png";
      }

      return obj;
    },
    async emitirElectronico(doc) {
      this.loading = true;
      this.axios
        .post(this.API + "emitirguiaelectronica/" + doc.IdDocVentaRemision)
        .then(response => {
          if (response.data.Estado == 1) {
            this.$toasted.error(response.data.msj_sunat);
            this.loading = false;
          } else {
            this.$toasted.success(response.data.msj_sunat);
            this.loading = false;
          }
        });
    },
    getDetalles(idDocVentaRemision) {
      let detalles = this.itemsDetalle.filter(
        item => item.IdDocVentaRemision == idDocVentaRemision
      );
      return detalles;
    },
    focusAutocomplete(ref) {
      this.$refs[ref].handleDoubleClick();
    },
    setAlmacen(obj) {
      this.almacen = obj;
      this.limpiarFiltros();
      Vue.nextTick(() => this.$refs.tablaVentas.refresh());
    },
    actualizarTabla() {
      this.$refs.tablaVentas.refresh();
    },
    myProvider(ctx) {
      let url =
        this.API +
        "remision?estado=" +
        this.estadoVenta +
        "&filter=" +
        (ctx.filter || "") +
        "&sortBy=" +
        ctx.sortBy;
      url += "&sortDesc=" + (ctx.sortDesc ? 1 : 0);
      let promise = this.axios.get(url);
      return promise
        .then(result => {
          this.items = result.data.map(el => {
            if (el.Anulado === 1) {
              el._rowVariant = "warning";
            }
            return el;
          });
          this.totalRows = this.items.length;
          return this.items;
        })
        .catch(error => {
          return [];
        });
    },
    limpiarFiltros() {
      this.filters = {};
      this.filter = "";
      this.fechaInicio = this.$moment().format("YYYY-MM-DD");
      this.fechaFin = this.$moment().format("YYYY-MM-DD");
    },
    generateFilter(key, value) {
      this.filters[key] = value;
      let filter = "";
      for (var key in this.filters) {
        filter += "&filter[" + key + "]=" + this.filters[key];
      }
      this.filter = filter;
    },
    exportarProductos() {},
    moverAEmitidos(doc) {
      this.loading = true;
      this.axios
        .post(this.API + "moveraemitidosguia", {
          idDocVentaRemision: doc.IdDocVentaRemision
        })
        .then(response => {
          this.$refs.tablaVentas.refresh();
          this.loading = false;
        });
    },
    cambiarAAnonimo(doc) {
      let r = confirm("¿Desea Cambiar a Cliente Anónimo?");
      if (r == true) {
        this.loading = true;
        this.axios
          .post(this.API + "cambiaraanonimo", {
            idDocVenta: doc.idDocVenta
          })
          .then(response => {
            this.$refs.tablaVentas.refresh();
            this.loading = false;
          });
      }
    }
  },
  components: {
    ProductoKardex
  }
};
</script>

<style scoped>
.remision-module {
  background-color: #f4f6f9;
  min-height: 100%;
}

/* --- Card de filtros --- */
.filtros-card {
  border: none;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}
.filtro-label {
  font-size: 0.8rem;
  font-weight: 600;
  color: #495057;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  margin-bottom: 4px;
  display: block;
}
.filtro-input {
  border-radius: 8px;
  border: 1px solid #dee2e6;
}
.badge-total {
  display: inline-block;
  background-color: #010067;
  color: #fff;
  font-weight: 600;
  padding: 8px 16px;
  border-radius: 20px;
  font-size: 0.9rem;
}

/* --- Card de tabla --- */
.tabla-card {
  border: none;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
  overflow: hidden;
}
.table-responsive-remision {
  overflow-x: auto;
  width: 100%;
}
.tabla-remision >>> thead th {
  background-color: #010067;
  color: #fff;
  font-size: 0.78rem;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  font-weight: 600;
  border: none;
  padding: 12px 10px;
  white-space: nowrap;
}
.tabla-remision >>> tbody td {
  vertical-align: middle;
  font-size: 0.88rem;
  padding: 10px;
}
.tabla-remision >>> tbody tr:hover {
  background-color: #eef1ff !important;
}

/* Botón toggle detalle */
.btn-toggle-detalle {
  background-color: #010067;
  border: none;
  border-radius: 6px;
  width: 32px;
  height: 32px;
  font-weight: bold;
}
.btn-toggle-detalle:hover {
  background-color: #fd8100;
}

/* Badge de peso */
.badge-peso {
  background-color: #eef1ff;
  color: #010067;
  font-weight: 600;
  padding: 3px 10px;
  border-radius: 12px;
  font-size: 0.8rem;
}

/* --- Detalle expandido --- */
.detalle-card {
  border: none;
  background-color: #fafbff;
  border-left: 4px solid #fd8100;
  border-radius: 0 8px 8px 0;
  margin: 4px 0;
}
.prod-detalle th,
.prod-detalle td {
  background-color: transparent;
  font-size: 0.85rem;
}
.prod-detalle thead th {
  color: #010067;
  border-bottom: 2px solid #dee2e6;
}
.btn-mover {
  border-radius: 6px;
  font-weight: 600;
}

/* --- Estado / Acciones --- */
.acciones-estado {
  gap: 6px;
}
.estado-badge {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  padding: 5px 10px;
  border-radius: 14px;
  font-size: 0.78rem;
  font-weight: 600;
  white-space: nowrap;
}
.estado-ok {
  background-color: #e6f7ec;
  color: #1e8449;
}
.estado-error {
  background-color: #fdecea;
  color: #c0392b;
}

.btn-accion {
  border: none;
  border-radius: 6px;
  font-size: 0.78rem;
  font-weight: 600;
  padding: 6px 12px;
}
.btn-emitir {
  background-color: #28a745;
  color: #fff;
}
.btn-emitir:hover {
  background-color: #218838;
}
.btn-pdf {
  background-color: #17a2b8;
  color: #fff;
}
.btn-pdf:hover {
  background-color: #138496;
}
.btn-descarga {
  background-color: #010067;
  color: #fff;
}
.btn-descarga:hover {
  background-color: #fd8100;
}

/* --- General --- */
.total-ventas {
  font-weight: bold;
  text-align: center;
  color: #ffffff;
  background-color: #ff7675;
  padding: 10px;
  font-size: 40px;
}
#tabla-productos tr {
  cursor: pointer;
}
.table-detalle {
  height: 80px;
}
.table-detalle th {
  color: #fff;
  background-color: #6c757d;
}
.table-detalle tbody td {
  font-size: 18px;
  font-weight: bold;
  color: #2980b9;
}
.table-detalle thead th:first-child {
  max-width: 300px;
}
@media (min-width: 992px) {
  .modal-xl .modal-dialog {
    max-width: 1200px;
  }
}
.tooltip {
  pointer-events: none !important;
}
@media print {
  body .printable,
  body .modal {
    display: block;
  }
  .modal {
    overflow: visible !important;
    position: absolute;
  }
}
</style>