<template>
  <b-container fluid class="kardex-wrapper">
    <b-card class="shadow-sm border-0 mb-4">
      <div class="d-flex align-items-center mb-3">
        <i class="fa fa-boxes text-primary mr-2" style="font-size: 1.4rem;"></i>
        <h4 class="mb-0 font-weight-bold text-dark">Kardex Valorizado</h4>
      </div>

      <b-row class="align-items-end">
        <b-col md="1" sm="4" class="mb-3">
          <label class="filtro-label">Año</label>
          <input type="number" class="form-control shadow-sm" v-model="anno" min="2000" max="2099" placeholder="AAAA">
        </b-col>

        <b-col md="3" sm="8" class="mb-3">
          <label class="filtro-label">Producto</label>
          <autocomplete
            ref="autocompleteProducto"
            :url="APIProductos"
            :debounce="500"
            :onShouldRenderChild="renderChild"
            anchor="Producto"
            label="writer"
            placeholder="Buscar producto..."
            :on-select="setProducto"
            :onFocus="focusAutocomplete.bind(null, 'autocompleteProducto')"
            :classes="{input: 'form-control shadow-sm'}">
          </autocomplete>
          <small v-if="producto && producto.Producto" class="text-success">
            <i class="fa fa-check-circle"></i> {{ producto.Producto }}
          </small>
        </b-col>

        <b-col md="1" sm="4" class="mb-3">
          <label class="filtro-label">Stock Inicial</label>
          <input type="number" class="form-control shadow-sm" v-model="stock">
        </b-col>

        <b-col md="1" sm="4" class="mb-3">
          <label class="filtro-label">Costo</label>
          <input type="number" class="form-control shadow-sm" v-model="precio" step="0.01">
        </b-col>

        <b-col md="2" sm="4" class="mb-3">
          <label class="filtro-label">Mes</label>
          <select class="form-control shadow-sm" v-model="Mes">
            <option value="">-- Todos los meses --</option>
            <option :value="mes.value" v-for="(mes, index) in mounth" :key="index">
              {{ mes.text }}
            </option>
          </select>
        </b-col>

        <b-col md="4" class="mb-3 d-flex flex-wrap align-items-end">
          <b-button class="mr-2 mb-1" variant="info" :disabled="isLoading" @click="obtenerListado()">
            <i class="fa fa-spinner fa-spin" v-if="isLoading"></i>
            <i class="fa fa-search" v-else></i>
            Generar
          </b-button>

          <b-button class="mr-2 mb-1" variant="danger" :disabled="disableExport || isExportingPDF" @click="exportPDF()">
            <i class="fa fa-spinner fa-spin" v-if="isExportingPDF"></i>
            <i class="fa fa-file-pdf" v-else></i>
            PDF
          </b-button>

          <b-button class="mb-1" variant="success" :disabled="disableExport || isExportingExcel" @click="exportEXCEL()">
            <i class="fa fa-spinner fa-spin" v-if="isExportingExcel"></i>
            <i class="fa fa-file-excel" v-else></i>
            Excel
          </b-button>
        </b-col>
      </b-row>

      <b-row>
        <b-col cols="12" class="mt-2">
          <div class="table-responsive kardex-tabla-wrapper">
            <table width="100%" class="table table-hover table-sm mb-0">
              <thead>
                <tr class="clsHeader">
                  <th>Fecha</th>
                  <th>Documento</th>
                  <th>Tipo</th>
                  <th>Cliente/Proveedor</th>
                  <th class="text-right">Ingreso Cant.</th>
                  <th class="text-right">Ingreso Precio</th>
                  <th class="text-right">Salida Cant.</th>
                  <th class="text-right">Salida Precio</th>
                  <th class="text-right">Saldo Cant.</th>
                  <th class="text-right">Costo Prom.</th>
                  <th class="text-right">Saldo Valorizado</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(fila, index) in lstReporte" :key="index" :class="getClass(fila)">
                  <td>{{ fila.fecha ? formatearFecha(fila.fecha) : '-' }}</td>
                  <td>{{ fila.documento }}</td>
                  <td>
                    <span class="badge" :class="getBadgeClass(fila)">{{ fila.tipoMov }}</span>
                  </td>
                  <td>{{ fila.nombres }}</td>
                  <td class="text-right">{{ fila.ingresoCantidad ? formatearNumero(fila.ingresoCantidad) : '-' }}</td>
                  <td class="text-right">{{ fila.ingresoPrecio ? formatearNumero(fila.ingresoPrecio) : '-' }}</td>
                  <td class="text-right">{{ fila.salidaCantidad ? formatearNumero(fila.salidaCantidad) : '-' }}</td>
                  <td class="text-right">{{ fila.salidaPrecio ? formatearNumero(fila.salidaPrecio) : '-' }}</td>
                  <td class="text-right">{{ formatearNumero(fila.saldoCantidad) }}</td>
                  <td class="text-right">{{ formatearNumero(fila.saldoCostoUnitario) }}</td>
                  <td class="text-right font-weight-bold">{{ formatearNumero(fila.saldoCostoTotal) }}</td>
                </tr>
              </tbody>
            </table>

            <div v-if="!isLoading && lstReporte.length === 0" class="text-center text-muted py-5">
              <i class="fa fa-inbox" style="font-size: 2rem;"></i>
              <p class="mt-2 mb-0">
                {{ huboBusqueda ? "No se encontraron movimientos para los filtros seleccionados." : "Seleccione un producto y presione \"Generar\" para ver el reporte." }}
              </p>
            </div>

            <div v-if="isLoading" class="text-center text-muted py-5">
              <i class="fa fa-spinner fa-spin" style="font-size: 2rem;"></i>
              <p class="mt-2 mb-0">Generando reporte...</p>
            </div>
          </div>
        </b-col>
      </b-row>
    </b-card>
  </b-container>
</template>

<script>
import config from "@/store/config.js";
import debounce from 'lodash';
export default {
  data() {
    return {
      API: config.API,
      Tipo: 0,
      anno: "",
      Mes: "",
      mounth: [
        { value: "01", text: "ENERO" },
        { value: "02", text: "FEBRERO" },
        { value: "03", text: "MARZO" },
        { value: "04", text: "ABRIL" },
        { value: "05", text: "MAYO" },
        { value: "06", text: "JUNIO" },
        { value: "07", text: "JULIO" },
        { value: "08", text: "AGOSTO" },
        { value: "09", text: "SEPTIEMBRE" },
        { value: "10", text: "OCTUBRE" },
        { value: "11", text: "NOVIEMBRE" },
        { value: "12", text: "DICIEMBRE" }
      ],
      stock: 0,
      precio: 0,
      producto: {},
      lstReporte: [],
      isLoading: false,
      isExportingPDF: false,
      isExportingExcel: false,
      huboBusqueda: false
    };
  },
  mounted() {
    this.anno = this.$moment().format("YYYY");
  },
  methods: {
    renderChild(data) {
      return `<b>${data.Producto}</b>`;
    },
    setProducto(obj) {
      this.producto = obj;
    },
    focusAutocomplete(ref) {
      this.$refs[ref].type = "";
      this.$refs[ref].handleDoubleClick();
    },
    validarFiltros() {
      if (!this.producto || !Object.keys(this.producto).length > 0) {
        this.$toasted.show("Seleccione un producto.");
        return false;
      }
      if (!this.anno || !/^\d{4}$/.test(String(this.anno))) {
        this.$toasted.show("Ingrese un año válido (formato AAAA).");
        return false;
      }
      return true;
    },
    async obtenerListado() {
      if (!this.validarFiltros()) return;

      this.isLoading = true;
      this.huboBusqueda = true;
      this.lstReporte = [];
      try {
        const response = await this.axios(`${this.API}producto/listarstockvalorizado`, {
          params: {
            idProducto: this.producto.IdProducto,
            producto: this.producto.Producto,
            stock: this.stock,
            precio: this.precio,
            anno: this.anno,
            Tipo: this.Tipo,
            Mes: this.Mes || undefined
          }
        });
        this.lstReporte = (response.data && response.data.data) || [];
      } catch (error) {
        console.error(error);
        const mensaje = error.response && error.response.data && error.response.data.error
          ? error.response.data.error
          : "No se pudo generar el reporte. Intente de nuevo.";
        this.$toasted.error(mensaje);
      } finally {
        this.isLoading = false;
      }
    },
    formatearNumero(valor) {
      const numero = parseFloat(valor) || 0;
      return numero.toLocaleString("es-PE", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      });
    },
    formatearFecha(fecha) {
      return this.$moment(fecha).format("DD/MM/YYYY");
    },
    getClass(fila) {
      if (fila.tipoMov === "INICIAL") return "clsInicial";
      if (fila.tipoMov === "INGRESO") return "clsIngreso";
      if (fila.tipoMov === "DEVOLUCIÓN") return "clsDevolucion";
      return "clsSalida";
    },
    getBadgeClass(fila) {
      if (fila.tipoMov === "INICIAL") return "badge-secondary";
      if (fila.tipoMov === "INGRESO") return "badge-success";
      if (fila.tipoMov === "DEVOLUCIÓN") return "badge-warning";
      return "badge-danger";
    },
    normalizarUrl(url) {
      let urlLimpia = url.replace(/\\/g, "");
      if (!urlLimpia.startsWith("http://") && !urlLimpia.startsWith("https://")) {
        urlLimpia = "http://" + urlLimpia;
      }
      return urlLimpia;
    },
    async exportEXCEL() {
      this.isExportingExcel = true;
      try {
        const res = await this.axios.post(`${this.API}producto/listarstockvalorizado/excel`, {
          data: this.lstReporte,
          producto: this.producto.Producto,
          anno: this.anno
        });
        window.open(this.normalizarUrl(res.data), "_blank");
      } catch (error) {
        console.error(error);
        this.$toasted.error("No se pudo generar el Excel. Intente de nuevo.");
      } finally {
        this.isExportingExcel = false;
      }
    },
    async exportPDF() {
      this.isExportingPDF = true;
      try {
        const res = await this.axios(`${this.API}reporte/kardexvalorizadopdf`, {
          params: {
            idProducto: this.producto.IdProducto, // NUEVO
            producto: this.producto.Producto,
            stock: this.stock,
            precio: this.precio,
            anno: this.anno,
            Tipo: this.Tipo,
            Mes: this.Mes || undefined
          }
        });
        if (res.data && res.data.url) {
          window.open(this.normalizarUrl(res.data.url), "_blank");
        }
      } catch (error) {
        console.error(error);
        this.$toasted.error("No se pudo generar el PDF. Intente de nuevo.");
      } finally {
        this.isExportingPDF = false;
      }
    }
  },
  computed: {
    APIProductos() {
      return this.API + "producto/inv";
    },
    disableExport() {
      return this.lstReporte.length === 0;
    }
  }
};
</script>

<style scoped>
.kardex-wrapper { padding: 1rem; }
.filtro-label { font-size: 0.8rem; font-weight: 600; color: #6c757d; margin-bottom: 0.25rem; display: block; }
.kardex-tabla-wrapper { max-height: 65vh; overflow-y: auto; }
.clsHeader th { position: sticky; top: 0; background: #5c6bc0; color: white; z-index: 2; }
.clsInicial { background: #ede7f6; font-weight: bold; }
.clsIngreso { background: #e8f5e9; }
.clsSalida { background: #ffebee; }
.clsDevolucion { background: #fff8e1; }
</style>