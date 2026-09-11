<template>
  <div class="deuda-clientes">
    <!-- Header -->
    <div class="page-header">
      <div class="page-header-left">
        <div class="page-icon">
          <i class="fa fa-file-invoice-dollar"></i>
        </div>
        <div>
          <h2 class="page-title">Deuda de Clientes</h2>
          <p class="page-subtitle">Consulta y controla las cuentas por cobrar de tus clientes</p>
        </div>
      </div>
    </div>

    <!-- Tarjetas resumen (ahora con totales REALES del backend, no solo de la página actual) -->
    <b-row class="summary-row">
      <b-col md="4">
        <div class="summary-card summary-total" :class="{ 'summary-loading': totalesLoading }">
          <div class="summary-icon">
            <i class="fa fa-file-invoice"></i>
          </div>
          <div class="summary-body">
            <div class="summary-label">Total</div>
            <div class="summary-value">S/ {{ totales.total }}</div>
          </div>
        </div>
      </b-col>
      <b-col md="4">
        <div class="summary-card summary-pendiente" :class="{ 'summary-loading': totalesLoading }">
          <div class="summary-icon">
            <i class="fa fa-clock"></i>
          </div>
          <div class="summary-body">
            <div class="summary-label">Pendiente</div>
            <div class="summary-value">S/ {{ totales.pendiente }}</div>
          </div>
        </div>
      </b-col>
      <b-col md="4">
        <div class="summary-card summary-pagado" :class="{ 'summary-loading': totalesLoading }">
          <div class="summary-icon">
            <i class="fa fa-check-circle"></i>
          </div>
          <div class="summary-body">
            <div class="summary-label">Pagado</div>
            <div class="summary-value">S/ {{ totales.pagado }}</div>
          </div>
        </div>
      </b-col>
    </b-row>

    <!-- Filtros -->
    <div class="filters-card">
      <b-row class="align-items-end filters-row">
        <b-col md="3" sm="6">
          <label class="filter-label">Buscar</label>
          <b-input-group class="filter-search">
            <b-input-group-prepend>
              <span class="search-icon"><i class="fa fa-search"></i></span>
            </b-input-group-prepend>
            <b-form-input
              v-model="filter"
              placeholder="Cliente o DNI/RUC"
              @input="onFilterChange"
            />
            <b-input-group-append v-if="filter">
              <b-btn class="clear-btn" @click="clearFilter">
                <i class="fa fa-times"></i>
              </b-btn>
            </b-input-group-append>
          </b-input-group>
        </b-col>

        <b-col md="3" sm="6">
          <label class="filter-label">Almacén</label>
          <!--
            Por defecto NO hay almacén seleccionado (almacen.IdAlmacen = null),
            lo que el backend interpreta como "Todos los almacenes".
            El autocomplete muestra "Todos los almacenes" como valor inicial
            y placeholder. Si el usuario selecciona uno puntual, aparece el
            botón "x" para volver a la vista de todos los almacenes.
          -->
          <b-input-group class="filter-search">
            <autocomplete
              required="required"
              ref="inputAlmacen"
              :url="API + 'almacenes'"
              anchor="Almacen"
              initValue="Todos los almacenes"
              label="writer"
              placeholder="Todos los almacenes"
              :on-select="setAlmacen"
              :onFocus="focusAutocomplete.bind(null, 'inputAlmacen')"
              :classes="{ input: 'form-control filter-input' }"
            />
            <b-input-group-append v-if="almacen.IdAlmacen">
              <b-btn class="clear-btn" @click="clearAlmacen" title="Ver todos los almacenes">
                <i class="fa fa-times"></i>
              </b-btn>
            </b-input-group-append>
          </b-input-group>
        </b-col>

        <b-col md="2" sm="6">
          <label class="filter-label">Estado</label>
          <b-form-select
            v-model="estado"
            :options="estadoOptions"
            class="filter-input"
            @input="onEstadoChange"
          />
        </b-col>

        <b-col md="2" sm="3">
          <label class="filter-label">Desde</label>
          <b-form-input
            v-model="rango.fechaDesde"
            type="date"
            class="filter-input"
            @input="onRangoChange"
          />
        </b-col>

        <b-col md="2" sm="3">
          <label class="filter-label">Hasta</label>
          <b-form-input
            v-model="rango.fechaHasta"
            type="date"
            class="filter-input"
            @input="onRangoChange"
          />
        </b-col>
      </b-row>

      <div class="filters-footer">
        <div class="per-page-group">
          <label class="filter-label mb-0 mr-2">Mostrar</label>
          <b-form-select
            :options="pageOptions"
            v-model="perPage"
            class="per-page-select"
            @input="currentPage = 1"
          />
        </div>

        <div class="footer-actions">
          <!-- Leyenda de colores -->
          <div class="legend">
            <span class="legend-item"><i class="legend-dot dot-vencido"></i>Vencido</span>
            <span class="legend-item"><i class="legend-dot dot-por-vencer"></i>Por vencer</span>
            <span class="legend-item"><i class="legend-dot dot-pendiente"></i>Pendiente</span>
            <span class="legend-item"><i class="legend-dot dot-pagado"></i>Pagado</span>
          </div>
          <b-button class="btn-soft-danger" @click="imprimir()">
            <i class="fa fa-file-pdf mr-2"></i>Imprimir
          </b-button>
          <b-button
              class="btn-soft-success"
              :href="exportUrl"
              target="_blank"
          >
              <i class="fa fa-file-excel mr-2"></i>
              Exportar
          </b-button>
        </div>
      </div>
    </div>

    <!-- Tabla -->
    <div class="table-card">
      <b-table
        small
        responsive
        ref="tablaProductos"
        :filter="filter"
        :sort-by.sync="sortBy"
        :sort-desc.sync="sortDesc"
        hover
        id="my-table"
        :busy.sync="isBusy"
        :items="myProvider"
        :fields="fields"
        :per-page="perPage"
        :current-page="currentPage"
        show-empty
        :tbody-tr-class="rowClass"
        @row-clicked="myRowClickHandler"
      >
        <template slot="table-busy">
          <div class="state-block">
            <b-spinner class="state-spinner"></b-spinner>
            <strong>Cargando registros...</strong>
          </div>
        </template>

        <template slot="empty">
          <div class="state-block">
            <i class="fa fa-inbox state-empty-icon"></i>
            <strong>No se encontraron registros</strong>
            <span class="text-muted small">Ajusta los filtros e intenta nuevamente</span>
          </div>
        </template>

        <template slot="cell(Cliente)" slot-scope="data">
          <div class="cliente-cell">
            <div class="cliente-avatar">{{ (data.value || "?").charAt(0).toUpperCase() }}</div>
            <span>{{ data.value }}</span>
          </div>
        </template>

        <!--
          IMPORTANTE: la columna Estado YA NO depende de un slot para pintarse.
          El texto y el color vienen de "formatter" y "tdClass" definidos en
          "fields" (ver <script>), que son props normales de b-table, no slots.
          Si el slot de abajo llegara a no ejecutarse por algún motivo de
          versión de Vue/bootstrap-vue, la celda igual se ve coloreada porque
          tdClass ya puso el fondo/color/ícono vía CSS en el <td>.
        -->
        <template slot="cell(Estado)" slot-scope="data">
          {{ estadoInfo(data.item).text }}
        </template>

        <template slot="cell(Saldo)" slot-scope="data">
          <span class="saldo-value" :class="parseFloat(data.value) > 0 ? 'saldo-pendiente' : 'saldo-cero'">
            S/ {{ data.value | redondear(2) }}
          </span>
        </template>

        <template slot="cell(Letras)" slot-scope="data">
          <span v-if="data.value && data.value !== 'Ninguna'" class="letras-badge">
            <i class="fa fa-file-signature mr-1"></i>{{ data.value }}
          </span>
          <span v-else class="text-muted">—</span>
        </template>
      </b-table>

      <div class="table-footer">
        <span class="text-muted small">{{ totalRows }} registro(s)</span>
        <b-pagination
          :total-rows="totalRows"
          :per-page="perPage"
          v-model="currentPage"
          class="my-0"
        />
      </div>
    </div>

    <b-modal
      id="modalDetalleDeuda"
      size="lg"
      modal-class="modal-xl"
      v-model="showLetras"
      ok-only
      ok-title="Cerrar"
      title="Detalle de Deuda"
    >
      <detalle-deuda-cliente ref="detDeuCli"></detalle-deuda-cliente>
    </b-modal>
  </div>
</template>

<script>
import config from "@/store/config.js";
import DetalleDeudaCliente from "@/components/DetalleDeudaCliente.vue";

// Días de anticipación para considerar una deuda "Por vencer"
const DIAS_POR_VENCER = 5;

export default {
  mounted() {
    this.refreshTotales();
  },
  data() {
    return {
      API: config.API,
      pageOptions: [50, 100, 150, 200, 300],
      estadoOptions: [
        { value: "pendiente", text: "Pendientes" },
        { value: "pagado", text: "Pagadas" },
        { value: "todos", text: "Todas" }
      ],
      estado: "pendiente",
      imprimiendoTodo: false,
      filter: "",
      sortBy: "IdDocVenta",
      sortDesc: false,
      items: [],
      isBusy: false,
      fields: [
        { key: "DniRuc", label: "DNI/RUC", sortable: true },
        { key: "Cliente", label: "Cliente", sortable: true },
        { key: "Correlativo", label: "Serie", sortable: true },
        { key: "Total", label: "Total", sortable: true },
        { key: "Aplicado", label: "Aplicado", sortable: true },
        { key: "NotaCreApli", label: "Apli. Nota Cred.", sortable: true },
        { key: "Saldo", label: "Saldo", sortable: true },
        {
          key: "Estado",
          label: "Estado",
          sortable: true,
          // formatter: NO es un slot. Es la forma "clásica" de b-table de
          // decidir qué texto mostrar en la celda. Funciona en cualquier
          // versión de bootstrap-vue.
          formatter: (value, key, item) => this.estadoInfo(item).text,
          // tdClass: tampoco es un slot. b-table lo llama directamente y
          // aplica la(s) clase(s) devueltas al <td>. Aquí es donde entra
          // el color real.
          tdClass: (value, key, item) => ["status-cell", this.estadoInfo(item).class]
        },
        { key: "FechaDoc", label: "Emisión", sortable: true },
        { key: "FechaCredito", label: "Vencimiento", sortable: true },
        { key: "Letras", label: "Letras", sortable: true }
      ],
      totalRows: 0,
      perPage: 50,
      currentPage: 1,
      // IdAlmacen null/vacío = "Todos los almacenes". Solo se filtra por un
      // almacén puntual cuando el usuario elige uno en el autocomplete.
      almacen: {
        IdAlmacen: null
      },
      rango: {
        fechaDesde: this.$moment().format("YYYY-01-01"),
        fechaHasta: this.$moment().format("YYYY-MM-DD")
      },
      showLetras: false,
      urlImg: "/resources/images/logo.png",

      // --- Totales reales (independientes de la paginación) ---
      totales: {
        total: "0.00",
        pendiente: "0.00",
        pagado: "0.00"
      },
      totalesLoading: false,
      filterDebounceTimer: null
    };
  },
  computed: {
    exportUrl() {
        let params = [];
        if (this.estado) {
            params.push(`estado=${encodeURIComponent(this.estado)}`);
        }
        if (this.rango.fechaDesde) {
            params.push(`fechaDesde=${encodeURIComponent(this.rango.fechaDesde)}`);
        }
        if (this.rango.fechaHasta) {
            params.push(`fechaHasta=${encodeURIComponent(this.rango.fechaHasta)}`);
        }
        if (
            this.almacen &&
            this.almacen.IdAlmacen &&
            this.almacen.IdAlmacen !== 'todos'
        ) {
            params.push(
                `idalmacen=${encodeURIComponent(this.almacen.IdAlmacen)}`
            );
        }
        return `${this.API}reporte/deudaclientes2?${params.join('&')}`;
    }
  },
  methods: {
    refreshTotales() {
      this.totalesLoading = true;
      this.axios
        .get(`${this.API}clientes/deuda/totales`, {
          params: {
            idalmacen: this.almacen.IdAlmacen,
            estado: this.estado,
            q: this.filter || "",
            fechaDesde: this.rango.fechaDesde,
            fechaHasta: this.rango.fechaHasta
          }
        })
        .then(response => {
          const data = response.data || {};
          this.totales = {
            total: parseFloat(data.total || 0).toFixed(2),
            pendiente: parseFloat(data.pendiente || 0).toFixed(2),
            pagado: parseFloat(data.pagado || 0).toFixed(2)
          };
          this.totalRows = parseInt(data.registros || 0, 10);
        })
        .catch(() => {
          this.totales = { total: "0.00", pendiente: "0.00", pagado: "0.00" };
        })
        .finally(() => {
          this.totalesLoading = false;
        });
    },

    onFilterChange() {
      this.currentPage = 1;
      clearTimeout(this.filterDebounceTimer);
      this.filterDebounceTimer = setTimeout(() => {
        this.refreshTotales();
      }, 400);
    },

    clearFilter() {
      this.filter = "";
      this.onFilterChange();
    },

    onEstadoChange() {
      this.currentPage = 1;
      this.refreshTotales();
      this.$refs.tablaProductos.refresh();
    },

    onRangoChange() {
      this.currentPage = 1;
      this.refreshTotales();
      this.$refs.tablaProductos.refresh();
    },

    focusAutocomplete(ref) {
      this.$refs[ref].handleDoubleClick();
    },
    async imprimirTodo() {
      this.imprimiendoTodo = true;
      try {
        const res = await this.axios.get(
          `${this.API}cliente/imprimir/deudas_clientes`,
          {
            params: {
              idalmacen: this.almacen.IdAlmacen,
              estado: this.estado,
              fechaDesde: this.rango.fechaDesde,
              fechaHasta: this.rango.fechaHasta
            }
          }
        );
        if (res.data && res.data.url) {
          window.open(res.data.url, "_blank");
        }
      } catch (e) {
        console.log(e);
        if (this.$bvToast) {
          this.$bvToast.toast("No se pudo generar el PDF", { variant: "danger" });
        }
      } finally {
        this.imprimiendoTodo = false;
      }
    },
    async imprimir() {
      this.imprimiendoTodo = true;
      try {
        const res = await this.axios.get(
          `${this.API}cliente/imprimir/deudas_cliente`,
          {
            params: {
              idalmacen: this.almacen.IdAlmacen,
              estado: this.estado,
              fechaDesde: this.rango.fechaDesde,
              fechaHasta: this.rango.fechaHasta
            }
          }
        );
        if (res.data && res.data.url) {
          window.open(res.data.url, "_blank");
        }
      } catch (e) {
        console.log(e);
        if (this.$bvToast) {
          this.$bvToast.toast("No se pudo generar el PDF", { variant: "danger" });
        }
      } finally {
        this.imprimiendoTodo = false;
      }
    },

    setAlmacen(obj) {
      this.almacen = obj;
      this.currentPage = 1;
      this.refreshTotales();
      this.$nextTick(() => this.$refs.tablaProductos.refresh());
    },

    // Vuelve al estado "Todos los almacenes": limpia la selección y
    // recarga totales + tabla sin filtro de almacén.
    clearAlmacen() {
      this.almacen = { IdAlmacen: null };
      // Si el componente de autocomplete expone un setter de texto/valor,
      // lo usamos para reflejar "Todos los almacenes" en el input.
      // (Ajustar el nombre del método/prop según la implementación real
      // del componente <autocomplete> del proyecto si difiere.)
      const inputAlmacen = this.$refs.inputAlmacen;
      if (inputAlmacen) {
        if (typeof inputAlmacen.setValue === "function") {
          inputAlmacen.setValue("Todos los almacenes");
        } else if ("query" in inputAlmacen) {
          inputAlmacen.query = "Todos los almacenes";
        }
      }
      this.currentPage = 1;
      this.refreshTotales();
      this.$nextTick(() => this.$refs.tablaProductos.refresh());
    },

    myProvider(ctx) {
      const promise = this.axios.get(`${this.API}cliente/deudas`, {
        params: {
          // null => backend no filtra por almacén (Todos)
          idalmacen: this.almacen.IdAlmacen,
          estado: this.estado,
          q: ctx.filter || "",
          limit: ctx.perPage,
          page: ctx.currentPage,
          fechaDesde: this.rango.fechaDesde,
          fechaHasta: this.rango.fechaHasta,
          sortBy: ctx.sortBy || "FechaDoc",
          sortDesc: ctx.sortDesc ? 1 : 0
        }
      });

      return promise
        .then(response => {
          this.items = response.data;
          return this.items;
        })
        .catch(() => {
          return [];
        });
    },

    myRowClickHandler(record) {
      if (record.Letras && record.Letras !== "Ninguna") {
        this.showLetras = true;
        this.$refs.detDeuCli.cargarDeudasCliente(
          record.IdDocVenta,
          record.Total,
          record.DniRuc,
          record.Cliente,
          record.Correlativo
        );
      }
    },

    // --- Lógica de estado visual (color) ---
    // Devuelve texto y clase CSS según si la deuda está pagada, vencida,
    // por vencer (dentro de DIAS_POR_VENCER) o pendiente normal.
    // Esta función NO depende de bootstrap-vue: es JS puro y se llama
    // tanto desde "formatter"/"tdClass" (fields) como desde "rowClass".
    estadoInfo(record) {
      if (!record) {
        return { text: "-", class: "status-pendiente" };
      }

      if (record.Estado !== "Pendiente") {
        return {
          text: "Pagado",
          class: "status-pagado"
        };
      }

      const hoy = this.$moment().startOf("day");
      const vencimiento = this.$moment(record.FechaCredito).startOf("day");
      const diasRestantes = vencimiento.diff(hoy, "days");

      if (diasRestantes < 0) {
        return {
          text: "Vencido",
          class: "status-vencido"
        };
      }

      if (diasRestantes <= DIAS_POR_VENCER) {
        return {
          text: "Por vencer",
          class: "status-por-vencer"
        };
      }

      return {
        text: "Pendiente",
        class: "status-pendiente"
      };
    },

    // Resalta toda la fila cuando la deuda está vencida o por vencer.
    // :tbody-tr-class es una PROP de b-table (no un slot), así que
    // funciona igual sin importar la versión de bootstrap-vue/Vue.
    rowClass(item) {
      if (!item) return "";
      const info = this.estadoInfo(item);
      if (info.class === "status-vencido") return "row-vencido";
      if (info.class === "status-por-vencer") return "row-por-vencer";
      return "";
    }
  },
  components: {
    DetalleDeudaCliente
  }
};
</script>

<style scoped>
.deuda-clientes {
  padding: 28px;
  background: #f4f6fb;
  min-height: 100%;
}

/* Header */
.page-header {
  margin-bottom: 24px;
}

.page-header-left {
  display: flex;
  align-items: center;
  gap: 16px;
}

.page-icon {
  width: 52px;
  height: 52px;
  min-width: 52px;
  border-radius: 14px;
  background: linear-gradient(135deg, #4f6ef7, #7b8ef9);
  color: #fff;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 22px;
  box-shadow: 0 8px 18px rgba(79, 110, 247, 0.28);
}

.page-title {
  font-weight: 700;
  color: #1e2433;
  margin-bottom: 2px;
  font-size: 22px;
}

.page-subtitle {
  color: #8a94a6;
  margin-bottom: 0;
  font-size: 14px;
}

/* Summary cards */
.summary-row {
  margin-bottom: 22px;
}

.summary-card {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 20px 22px;
  border-radius: 16px;
  background: #fff;
  box-shadow: 0 4px 14px rgba(30, 36, 51, 0.06);
  border: 1px solid rgba(30, 36, 51, 0.04);
  transition: transform 0.18s ease, box-shadow 0.18s ease, opacity 0.15s ease;
  margin-bottom: 16px;
}

.summary-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 24px rgba(30, 36, 51, 0.1);
}

.summary-loading {
  opacity: 0.55;
}

.summary-icon {
  width: 50px;
  height: 50px;
  min-width: 50px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  color: #fff;
}

.summary-total .summary-icon {
  background: linear-gradient(135deg, #3b82f6, #6ba7f8);
}

.summary-pendiente .summary-icon {
  background: linear-gradient(135deg, #f59e0b, #fbbf5c);
}

.summary-pagado .summary-icon {
  background: linear-gradient(135deg, #10b981, #4fd8ab);
}

.summary-label {
  font-size: 12px;
  color: #8a94a6;
  text-transform: uppercase;
  letter-spacing: 0.6px;
  font-weight: 600;
}

.summary-value {
  font-size: 23px;
  font-weight: 700;
  color: #1e2433;
  margin-top: 2px;
}

/* Filters */
.filters-card {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 4px 14px rgba(30, 36, 51, 0.06);
  border: 1px solid rgba(30, 36, 51, 0.04);
  padding: 22px 24px 16px;
  margin-bottom: 22px;
}

.filters-row {
  margin-bottom: 4px;
}

.filter-label {
  font-size: 12px;
  font-weight: 600;
  color: #6b7688;
  text-transform: uppercase;
  letter-spacing: 0.4px;
  margin-bottom: 6px;
  display: block;
}

.filter-search .search-icon {
  background: #f4f6fb;
  border: 1px solid #e3e7f0;
  border-right: none;
  color: #9aa4b5;
  display: flex;
  align-items: center;
  padding: 0 12px;
  border-radius: 10px 0 0 10px;
}

.filter-search >>> .form-control,
.filter-input {
  border-radius: 10px !important;
  border: 1px solid #e3e7f0 !important;
  background: #f9fafc;
  font-size: 14px;
}

.filter-search >>> input {
  border-left: none !important;
  border-radius: 0 10px 10px 0 !important;
}

.clear-btn {
  border-radius: 0 10px 10px 0 !important;
  background: #f9fafc !important;
  border: 1px solid #e3e7f0 !important;
  border-left: none !important;
  color: #9aa4b5 !important;
}

.filters-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 12px;
  padding-top: 12px;
  margin-top: 10px;
  border-top: 1px solid #f0f2f7;
}

.per-page-group {
  display: flex;
  align-items: center;
}

.per-page-select {
  width: auto;
  border-radius: 10px !important;
  border: 1px solid #e3e7f0 !important;
  background: #f9fafc;
  font-size: 14px;
}

.footer-actions {
  display: flex;
  align-items: center;
  gap: 10px;
  flex-wrap: wrap;
}

/* Leyenda de colores de Estado */
.legend {
  display: flex;
  align-items: center;
  gap: 14px;
  margin-right: 8px;
  flex-wrap: wrap;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12.5px;
  color: #6b7688;
  font-weight: 600;
}

.legend-dot {
  width: 9px;
  height: 9px;
  border-radius: 50%;
  display: inline-block;
}

.dot-vencido {
  background: #e5484d;
}

.dot-por-vencer {
  background: #f59e0b;
}

.dot-pendiente {
  background: #4f6ef7;
}

.dot-pagado {
  background: #10b981;
}

.btn-soft-danger,
.btn-soft-success {
  border-radius: 10px;
  font-weight: 600;
  font-size: 14px;
  border: none;
  padding: 8px 18px;
}

.btn-soft-danger {
  background: #fdeceb;
  color: #e5484d;
}

.btn-soft-danger:hover {
  background: #f9d9d7;
  color: #d13d41;
}

.btn-soft-success {
  background: #e6f9f1;
  color: #10b981;
}

.btn-soft-success:hover {
  background: #cdf3e3;
  color: #0d9668;
}

/* Table */
.table-card {
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 4px 14px rgba(30, 36, 51, 0.06);
  border: 1px solid rgba(30, 36, 51, 0.04);
  overflow: hidden;
}

.table-card >>> table {
  margin-bottom: 0;
}

.table-card >>> thead th {
  background: #f9fafc;
  color: #6b7688;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.4px;
  font-weight: 700;
  border-top: none;
  border-bottom: 1px solid #eef0f5;
  padding: 14px 16px;
}

.table-card >>> tbody td {
  padding: 14px 16px;
  vertical-align: middle;
  font-size: 14px;
  color: #333c4d;
  border-top: 1px solid #f3f4f8;
}

.table-card >>> tbody tr {
  cursor: pointer;
  transition: background 0.15s ease;
}

.table-card >>> tbody tr:hover {
  background: #f7f9ff;
}

/* Filas resaltadas según urgencia (vía tbody-tr-class, no slot) */
.table-card >>> tbody tr.row-vencido {
  background: #fdf1f1;
  box-shadow: inset 3px 0 0 #e5484d;
}

.table-card >>> tbody tr.row-vencido:hover {
  background: #fbe4e3;
}

.table-card >>> tbody tr.row-por-vencer {
  background: #fef9ef;
  box-shadow: inset 3px 0 0 #f59e0b;
}

.table-card >>> tbody tr.row-por-vencer:hover {
  background: #fdf1da;
}

.cliente-cell {
  display: flex;
  align-items: center;
  gap: 10px;
}

.cliente-avatar {
  width: 30px;
  height: 30px;
  min-width: 30px;
  border-radius: 50%;
  background: linear-gradient(135deg, #4f6ef7, #7b8ef9);
  color: #fff;
  font-size: 13px;
  font-weight: 700;
  display: flex;
  align-items: center;
  justify-content: center;
}

/*
  Color de la celda Estado: se aplica directamente sobre el <td>
  mediante la clase que devuelve "tdClass" en el field (fields.Estado).
  Esto NO depende de ningún slot de bootstrap-vue.
*/
.table-card >>> td.status-cell {
  font-weight: 700;
  position: relative;
  padding-left: 26px !important;
}

.table-card >>> td.status-cell::before {
  content: "";
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  width: 8px;
  height: 8px;
  border-radius: 50%;
}

.table-card >>> td.status-vencido {
  color: #e5484d;
  background: #fdeceb;
}
.table-card >>> td.status-vencido::before {
  background: #e5484d;
}

.table-card >>> td.status-por-vencer {
  color: #b7791f;
  background: #fef3e2;
}
.table-card >>> td.status-por-vencer::before {
  background: #f59e0b;
}

.table-card >>> td.status-pendiente {
  color: #3b5bdb;
  background: #eaf0fe;
}
.table-card >>> td.status-pendiente::before {
  background: #4f6ef7;
}

.table-card >>> td.status-pagado {
  color: #0d9668;
  background: #e6f9f1;
}
.table-card >>> td.status-pagado::before {
  background: #10b981;
}

.saldo-value {
  font-weight: 700;
}

.saldo-pendiente {
  color: #e5484d;
}

.saldo-cero {
  color: #a3abbb;
}

.letras-badge {
  background: #eef1ff;
  color: #4f6ef7;
  padding: 4px 10px;
  border-radius: 8px;
  font-size: 12.5px;
  font-weight: 600;
}

.table-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px 20px;
}

/* States */
.state-block {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 48px 0;
  color: #6b7688;
}

.state-spinner {
  color: #4f6ef7;
}

.state-empty-icon {
  font-size: 34px;
  color: #c7cde0;
  margin-bottom: 4px;
}
</style>
