<template>
  <b-container fluid class="reporte-proveedor-wrapper">
    <b-card class="shadow-sm border-0 mb-4">
      <div class="d-flex align-items-center mb-3">
        <i class="fa fa-truck text-primary mr-2" style="font-size: 1.4rem;"></i>
        <h4 class="mb-0 font-weight-bold text-dark">Reporte de Ventas por Proveedor</h4>
      </div>

      <b-row class="align-items-end">
        <b-col md="3" sm="6" class="mb-3">
          <label class="filtro-label">Fecha Desde</label>
          <input type="date" v-model="desde" class="form-control shadow-sm" />
        </b-col>

        <b-col md="3" sm="6" class="mb-3">
          <label class="filtro-label">Fecha Hasta</label>
          <input type="date" v-model="hasta" class="form-control shadow-sm" />
        </b-col>

        <!-- Proveedor buscable -->
        <b-col md="3" sm="6" class="mb-3">
          <label class="filtro-label">Proveedor</label>
          <div class="proveedor-autocomplete">
            <input
              type="text"
              class="form-control shadow-sm"
              placeholder="Escribe o haz clic para ver todos..."
              v-model="proveedorTexto"
              @input="filtrarProveedores"
              @focus="onFocusProveedor"
              @blur="ocultarListaConDelay"
              autocomplete="off"
            />
            <ul
              v-if="mostrarLista && proveedoresFiltrados.length"
              class="proveedor-lista shadow"
            >
              <li
                v-for="prov in proveedoresFiltrados"
                :key="prov.IdProveedor"
                @mousedown.prevent="seleccionarProveedor(prov)"
              >
                {{ prov.Proveedor }}
              </li>
            </ul>
            <ul
              v-else-if="mostrarLista && !proveedoresFiltrados.length"
              class="proveedor-lista shadow"
            >
              <li class="text-muted sin-resultados">Sin resultados</li>
            </ul>
          </div>
        </b-col>

        <b-col md="3" sm="6" class="mb-3 d-flex align-items-end">
          <b-button
            class="mr-2 flex-fill"
            variant="success"
            @click="getObtenerReporte()"
          >
            <i class="fa fa-search mr-1"></i> Generar
          </b-button>
          <b-button
            class="flex-fill"
            variant="info"
            @click="exportarReporte()"
          >
            <i class="fa fa-file-excel mr-1"></i> Exportar
          </b-button>
        </b-col>
      </b-row>

      <b-row class="mb-2">
        <b-col cols="12" md="3" offset-md="9">
          <label class="filtro-label">Total</label>
          <input
            type="text"
            class="form-control text-right font-weight-bold shadow-sm total-input"
            readonly
            v-model="total"
          />
        </b-col>
      </b-row>

      <b-row>
        <b-col cols="12">
          <b-table
            striped
            hover
            responsive
            :items="lstReporte"
            :fields="headers"
            :per-page="perPage"
            :current-page="currentPage"
            small
            class="mb-2"
            show-empty
            empty-text="No hay datos para mostrar"
          >
            <template slot="Monto" slot-scope="data">
              {{ data.item.Monto.toFixed(2) }}
            </template>
            <template slot="Precio" slot-scope="data">
              {{ data.item.Precio.toFixed(2) }}
            </template>
          </b-table>

          <b-pagination
            v-model="currentPage"
            :total-rows="lstReporte.length"
            :per-page="perPage"
            aria-controls="my-table"
            align="right"
          ></b-pagination>
        </b-col>
      </b-row>
    </b-card>
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
      proveedoresFiltrados: [],
      proveedorTexto: "",
      mostrarLista: false,
      lstReporte: [],
      desde: "",
      hasta: "",
      id_proveedor: "",
      API: config.API,
      headers: [
        { key: "FechaReg", label: "Fecha" },
        { key: "Producto", label: "Producto" },
        { key: "Precio", label: "Precio" },
        { key: "Cantidad", label: "Cantidad" },
        { key: "Monto", label: "Monto" }
      ],
      total: 0
    };
  },
  mounted() {
    this.cargarProveedores();
    this.desde = this.$moment().startOf("month").format("YYYY-MM-DD");
    this.hasta = this.$moment().endOf("month").format("YYYY-MM-DD");
  },
  methods: {
    async cargarProveedores() {
      await this.axios.get(`${this.API}/proveedores`).then(response => {
        if (response.data) {
          this.lstProveedores = response.data;
        }
      });
    },

    onFocusProveedor() {
      this.mostrarLista = true;
      if (!this.proveedorTexto.trim()) {
        this.proveedoresFiltrados = this.lstProveedores.slice(0, 20);
      } else {
        this.filtrarProveedores();
      }
    },

    filtrarProveedores() {
      const texto = this.proveedorTexto.trim().toLowerCase();
      if (!texto) {
        this.id_proveedor = "";
        this.proveedoresFiltrados = this.lstProveedores.slice(0, 20);
        return;
      }
      this.proveedoresFiltrados = this.lstProveedores
        .filter(p => p.Proveedor.toLowerCase().includes(texto))
        .slice(0, 20);
    },

    seleccionarProveedor(prov) {
      this.proveedorTexto = prov.Proveedor;
      this.id_proveedor = prov.IdProveedor;
      this.mostrarLista = false;
    },

    ocultarListaConDelay() {
      setTimeout(() => {
        this.mostrarLista = false;
      }, 150);
    },

    async getObtenerReporte() {
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
      let total = this.lstReporte.reduce((sum, rep) => {
        return sum + parseFloat(rep.Monto);
      }, 0);
      this.total = parseFloat(total).toFixed(2);
    },

    async exportarReporte() {
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
          }
        });
      let total = this.lstReporte.reduce((sum, rep) => {
        return sum + parseFloat(rep.Monto);
      }, 0);
      this.total = parseFloat(total).toFixed(2);
    }
  }
};
</script>

<style scoped>
.reporte-proveedor-wrapper {
  padding: 1rem;
}

.filtro-label {
  font-size: 0.8rem;
  font-weight: 600;
  color: #6c757d;
  margin-bottom: 0.25rem;
  display: block;
}

.total-input {
  background-color: #eef7ee;
  border-color: #c3e6cb;
  color: #155724;
}

.proveedor-autocomplete {
  position: relative;
}

/* Altura fija calculada para mostrar 20 items sin scroll:
   cada <li> ocupa ~34px (padding 0.5rem arriba/abajo + texto),
   20 items * 34px = 680px aprox. Se agrega un pequeño margen. */
.proveedor-lista {
  position: absolute;
  z-index: 30;
  top: calc(100% + 2px);
  left: 0;
  right: 0;
  height: auto;
  max-height: none;
  overflow: visible;
  background: #fff;
  border: 1px solid #dee2e6;
  border-radius: 0.35rem;
  list-style: none;
  margin: 0;
  padding: 0.25rem 0;
}

.proveedor-lista li {
  padding: 0.5rem 0.9rem;
  cursor: pointer;
  font-size: 0.9rem;
  color: #212529;
  border-bottom: 1px solid #f1f3f5;
  transition: background-color 0.15s ease;
  line-height: 1.2;
}

.proveedor-lista li:last-child {
  border-bottom: none;
}

.proveedor-lista li:hover {
  background-color: #f1f5f9;
}

.proveedor-lista li.sin-resultados {
  cursor: default;
  text-align: center;
}
</style>
