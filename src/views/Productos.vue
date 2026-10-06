<template>
  <div class="product-catalog-page">
    <b-container fluid class="product-catalog-shell">
      <section class="product-catalog-card">
        <header class="product-catalog-header">
          <div class="product-catalog-heading">
            <span class="product-catalog-heading__icon"><i class="fas fa-boxes"></i></span>
            <div>
              <span class="product-catalog-eyebrow">LLANTAS Y BATERÍAS</span>
              <h1>Catálogo de productos</h1>
              <p>Consulta categoría, marca, modelo y stock en un solo lugar.</p>
            </div>
          </div>
          <div class="product-catalog-header__actions">
            <div class="product-catalog-counter">
              <strong>{{ totalRows }}</strong>
              <span>artículos registrados</span>
            </div>
            <b-button v-if="idPerfil === 1" class="product-catalog-import" @click="abrirImportacion">
              <i class="fas fa-file-excel"></i>
              <span>Importar Excel</span>
            </b-button>
            <b-button class="product-catalog-new" @click="nuevoProducto">
              <i class="fas fa-plus"></i>
              <span>Nuevo producto</span>
            </b-button>
          </div>
        </header>

        <div class="product-catalog-toolbar">
          <div class="product-catalog-search">
            <span class="product-catalog-search__icon"><i class="fas fa-search"></i></span>
            <b-form-input
              v-model="filter"
              placeholder="Buscar por código, producto, categoría, marca o modelo..."
              @input="currentPage = 1"
            />
            <button type="button" class="product-catalog-search__clear"
              :disabled="!filter" @click="filter = ''" aria-label="Limpiar búsqueda">
              <i class="fas fa-times"></i><span>Limpiar</span>
            </button>
          </div>
          <div class="product-catalog-limit">
            <label for="catalog-page-size">Mostrar</label>
            <b-form-select id="catalog-page-size" :options="pageOptions"
              v-model="perPage" @input="currentPage = 1"/>
            <span>por página</span>
          </div>
          <div class="product-catalog-multiple">
            <codigo-multiple></codigo-multiple>
          </div>
        </div>

        <div class="product-catalog-table-wrap">
          <b-table small
            ref="tablaProductos"
            :filter="filter"
            :sort-by.sync="sortBy"
            :sort-desc.sync="sortDesc"
            striped hover
            @row-clicked="rowClick"
            id="my-table"
            :busy.sync="isBusy"
            :items="myProvider"
            :fields="fields"
            :per-page="perPage"
            :current-page="currentPage"
            class="product-catalog-table">
            <template slot="stock" slot-scope="data">
              <span v-if="data.item.stock === null" class="text-muted">—</span>
              <b-badge v-else :variant="Number(data.item.stock) > 0 ? 'success' : 'danger'">
                {{ data.item.stock }}
              </b-badge>
            </template>
            <template slot="empty">
              <div class="product-catalog-empty">
                <span><i class="fas fa-box-open"></i></span>
                <strong>No encontramos productos</strong>
                <small>Prueba con otra palabra o registra un nuevo artículo.</small>
              </div>
            </template>
          </b-table>
        </div>

        <footer class="product-catalog-footer">
          <div class="product-catalog-footer__summary">
            Página <strong>{{ currentPage }}</strong> · {{ totalRows }} artículos
          </div>
          <b-pagination :total-rows="totalRows" :per-page="perPage"
            v-model="currentPage" class="product-catalog-pagination my-0" />
        </footer>
      </section>
      <producto-form ref="productoForm" :producto-update="productoSelect" :cbGuardar="actualizarTabla"></producto-form>

      <b-modal
        v-model="mostrarImportacion"
        size="lg"
        centered
        hide-footer
        no-close-on-backdrop
        modal-class="product-import-modal"
        content-class="product-import-content"
        body-class="product-import-body">
        <template slot="modal-title">
          <div class="product-import-title">
            <span><i class="fas fa-file-excel"></i></span>
            <div>
              <strong>Importar productos desde Excel</strong>
              <small>Revisa la información antes de actualizar el catálogo.</small>
            </div>
          </div>
        </template>

        <input
          ref="archivoExcel"
          type="file"
          class="d-none"
          accept=".xls,.xlsx,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
          @change="seleccionarArchivo" />

        <button
          type="button"
          class="product-import-dropzone"
          :class="{ 'product-import-dropzone--selected': archivoImportacion }"
          @click="elegirArchivo"
          @dragover.prevent
          @drop.prevent="soltarArchivo">
          <span class="product-import-dropzone__icon"><i class="fas fa-cloud-upload-alt"></i></span>
          <span class="product-import-dropzone__copy">
            <strong>{{ archivoImportacion ? archivoImportacion.name : 'Seleccionar archivo XLS o XLSX' }}</strong>
            <small>{{ archivoImportacion ? formatoPeso(archivoImportacion.size) : 'También puedes arrastrarlo aquí · Máximo 12 MB' }}</small>
          </span>
          <span class="product-import-dropzone__action">{{ archivoImportacion ? 'Cambiar' : 'Examinar' }}</span>
        </button>

        <div v-if="analizandoImportacion" class="product-import-loading">
          <b-spinner small></b-spinner>
          <span>Analizando columnas y productos...</span>
        </div>

        <div v-if="errorImportacion" class="product-import-alert product-import-alert--error">
          <i class="fas fa-exclamation-circle"></i>
          <span>{{ errorImportacion }}</span>
        </div>

        <template v-if="analisisImportacion">
          <div class="product-import-stats">
            <div><strong>{{ analisisImportacion.total_filas }}</strong><span>Filas leídas</span></div>
            <div><strong>{{ analisisImportacion.productos_nuevos }}</strong><span>Nuevos</span></div>
            <div><strong>{{ analisisImportacion.productos_actualizados }}</strong><span>Actualizar</span></div>
            <div><strong>{{ analisisImportacion.categorias_nuevas }}</strong><span>Categorías</span></div>
            <div><strong>{{ analisisImportacion.marcas_nuevas }}</strong><span>Marcas</span></div>
          </div>

          <div class="product-import-alert product-import-alert--notice">
            <i class="fas fa-box-open"></i>
            <span>Solo se registrarán o actualizarán los datos de los productos. El stock actual no será modificado.</span>
          </div>

          <div v-if="analisisImportacion.errores && analisisImportacion.errores.length" class="product-import-errors">
            <strong><i class="fas fa-times-circle"></i> Corrige estas filas antes de importar</strong>
            <ul>
              <li v-for="error in analisisImportacion.errores.slice(0, 8)" :key="error.fila">
                Fila {{ error.fila }}<span v-if="error.codigo"> · {{ error.codigo }}</span>: {{ error.mensaje }}
              </li>
            </ul>
          </div>

          <div v-if="analisisImportacion.muestra && analisisImportacion.muestra.length" class="product-import-preview">
            <div class="product-import-preview__heading">
              <strong>Vista previa</strong>
              <span>{{ analisisImportacion.validas }} productos válidos</span>
            </div>
            <div class="product-import-preview__table-wrap">
              <table>
                <thead>
                  <tr>
                    <th>Código</th><th>Producto</th><th>Categoría</th><th>Marca</th><th>Modelo</th><th>Costo</th><th>Venta</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="fila in analisisImportacion.muestra" :key="fila.fila">
                    <td>{{ fila.codigo }}</td>
                    <td>{{ fila.producto }}</td>
                    <td>{{ fila.categoria }}</td>
                    <td>{{ fila.marca }}</td>
                    <td>{{ fila.modelo }}</td>
                    <td>S/ {{ numeroImportacion(fila.precio_costo) }}</td>
                    <td>S/ {{ numeroImportacion(fila.precio_contado) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </template>

        <div class="product-import-actions">
          <button type="button" class="product-import-cancel" :disabled="guardandoImportacion" @click="mostrarImportacion = false">
            Cerrar
          </button>
          <button
            type="button"
            class="product-import-confirm"
            :disabled="!puedeImportar || guardandoImportacion || analizandoImportacion"
            @click="confirmarImportacion">
            <b-spinner v-if="guardandoImportacion" small></b-spinner>
            <i v-else class="fas fa-file-import"></i>
            <span>{{ guardandoImportacion ? 'Importando...' : 'Importar productos' }}</span>
          </button>
        </div>
      </b-modal>
    </b-container>
  </div>
</template>

<script>
import config from "@/store/config.js";
import ProductoForm from "@/components/ProductoForm";
import CodigoMultiple from "@/components/CodigoMultiple.vue";

export default {
  mounted() {
    this.cargarTotal();
  },
  data() {
    return {
      API: config.API,
      idPerfil: Number(sessionStorage.getItem("IdPerfil") || sessionStorage.getItem("idPerfil") || 0),
      pageOptions: [5, 10, 15, 50, 100],
      filter: null,
      sortBy: "IdProducto",
      sortDesc: false,
      productoSelect: {},
      items: [],
      isBusy: false,
      fields: [
        { key: "CodigoBarra", label: "Código", sortable: true },
        { key: "Producto", sortable: true },
        { key: "ProductoCategoria", label: "Categoría", sortable: true },
        { key: "ProductoMarca", label: "Marca", sortable: true },
        { key: "ProductoModelo", label: "Modelo", sortable: true },
        { key: "stock", label: "Stock", sortable: false, class: "text-center" }
      ],
      totalRows: 50,
      perPage: 10,
      currentPage: 1,
      mostrarImportacion: false,
      archivoImportacion: null,
      analizandoImportacion: false,
      guardandoImportacion: false,
      analisisImportacion: null,
      errorImportacion: ""
    };
  },
  computed: {
    puedeImportar() {
      return Boolean(this.archivoImportacion && this.analisisImportacion && this.analisisImportacion.puede_importar);
    }
  },
  methods: {
    rowClick(item, index) {
      this.productoSelect = item;
      this.$refs.productoForm.show = true;
      //this.$refs.productoForm.producto.IdProducto = 'a'
      //console.log(this.$refs.productoForm.$refs.productoForm)
    },
    actualizarTabla() {
      if (this.$refs.tablaProductos) this.$refs.tablaProductos.refresh();
      this.cargarTotal();
    },
    cargarTotal() {
      this.axios.get(this.API + "productos/count").then(response => {
        this.totalRows = Number(response.data.total) || 0;
      }).catch(() => {
        this.totalRows = 0;
      });
    },
    nuevoProducto() {
      this.productoSelect = {};
      this.$refs.productoForm.show = true;
    },
    abrirImportacion() {
      this.archivoImportacion = null;
      this.analisisImportacion = null;
      this.errorImportacion = "";
      this.mostrarImportacion = true;
      this.$nextTick(() => {
        if (this.$refs.archivoExcel) this.$refs.archivoExcel.value = "";
      });
    },
    elegirArchivo() {
      if (this.$refs.archivoExcel) this.$refs.archivoExcel.click();
    },
    soltarArchivo(evento) {
      const archivo = evento.dataTransfer && evento.dataTransfer.files ? evento.dataTransfer.files[0] : null;
      if (archivo) this.prepararArchivo(archivo);
    },
    seleccionarArchivo(evento) {
      const archivo = evento.target.files && evento.target.files[0] ? evento.target.files[0] : null;
      if (archivo) this.prepararArchivo(archivo);
    },
    prepararArchivo(archivo) {
      const extension = (archivo.name.split(".").pop() || "").toLowerCase();
      this.errorImportacion = "";
      this.analisisImportacion = null;
      if (["xls", "xlsx"].indexOf(extension) === -1) {
        this.archivoImportacion = null;
        this.errorImportacion = "Seleccione un archivo en formato XLS o XLSX.";
        return;
      }
      if (archivo.size > 12 * 1024 * 1024) {
        this.archivoImportacion = null;
        this.errorImportacion = "El archivo supera el máximo permitido de 12 MB.";
        return;
      }
      this.archivoImportacion = archivo;
      this.analizarExcel();
    },
    urlImportacion() {
      return this.API.replace(/index\.php\/?$/, "productos-importar.php");
    },
    datosImportacion(modo) {
      const datos = new FormData();
      datos.append("archivo", this.archivoImportacion);
      datos.append("modo", modo);
      return datos;
    },
    analizarExcel() {
      if (!this.archivoImportacion) return;
      this.analizandoImportacion = true;
      this.errorImportacion = "";
      this.axios.post(this.urlImportacion(), this.datosImportacion("vista_previa"), {
        headers: { "Content-Type": "multipart/form-data" }
      }).then(response => {
        this.analisisImportacion = response.data.analisis;
      }).catch(error => {
        const respuesta = error.response && error.response.data ? error.response.data : {};
        this.analisisImportacion = respuesta.analisis || null;
        this.errorImportacion = respuesta.error || "No se pudo analizar el archivo Excel.";
      }).finally(() => {
        this.analizandoImportacion = false;
      });
    },
    confirmarImportacion() {
      if (!this.puedeImportar) return;
      this.guardandoImportacion = true;
      this.errorImportacion = "";
      this.axios.post(this.urlImportacion(), this.datosImportacion("importar"), {
        headers: { "Content-Type": "multipart/form-data" }
      }).then(response => {
        const resultado = response.data.resultado || {};
        this.mostrarImportacion = false;
        this.currentPage = 1;
        this.filter = "";
        this.actualizarTabla();
        this.$swal({
          title: "Importación completada",
          text: (resultado.productos_creados || 0) + " productos creados y " + (resultado.productos_actualizados || 0) + " actualizados.",
          type: "success",
          confirmButtonText: "Entendido"
        });
      }).catch(error => {
        const respuesta = error.response && error.response.data ? error.response.data : {};
        this.analisisImportacion = respuesta.analisis || this.analisisImportacion;
        this.errorImportacion = respuesta.error || "No se pudo completar la importación.";
      }).finally(() => {
        this.guardandoImportacion = false;
      });
    },
    formatoPeso(bytes) {
      if (!bytes) return "0 KB";
      if (bytes < 1024 * 1024) return Math.max(1, Math.round(bytes / 1024)) + " KB";
      return (bytes / (1024 * 1024)).toFixed(1) + " MB";
    },
    numeroImportacion(valor) {
      return Number(valor || 0).toFixed(2);
    },
    myProvider(ctx) {
      // Here we don't set isBusy prop, so busy state will be handled by table itself
      // this.isBusy = true
      let url =
        this.API +
        "productos?limit=" +
        ctx.perPage +
        "&page=" +
        ctx.currentPage +
        "&filter=" +
        (ctx.filter || "") +
        "&sortBy=" +
        ctx.sortBy;
      url += "&sortDesc=" + (ctx.sortDesc ? 1 : 0);
      let promise = this.axios.get(url);

      return promise
        .then(result => {
          const productos = Array.isArray(result.data) ? result.data : [];
          if (!productos.length) {
            this.items = [];
            return this.items;
          }

          return this.axios.get(this.API + "productos/stock", {
            params: {
              idProductos: productos.map(item => item.IdProducto),
              idAlmacen: Number(sessionStorage.getItem("IdAlmacen")) || 1
            }
          }).then(stockResult => {
            const stockPorProducto = (Array.isArray(stockResult.data) ? stockResult.data : []).reduce((stock, item) => {
              stock[item.IdProducto] = item.stock;
              return stock;
            }, {});
            this.items = productos.map(item => ({
              ...item,
              stock: stockPorProducto[item.IdProducto] == null ? null : stockPorProducto[item.IdProducto]
            }));
            return this.items;
          }).catch(() => {
            this.items = productos.map(item => ({ ...item, stock: null }));
            return this.items;
          });
        })
        .catch(error => {
          // Here we could override the busy state, setting isBusy to false
          // this.isBusy = false
          // Returning an empty array, allows table to correctly handle busy state in case of error
          return [];
        });
    }
  },
  components: {
    ProductoForm,
    CodigoMultiple
  }
};
</script>

<style>
</style>
