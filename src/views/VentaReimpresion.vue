<template>
  <div class="pos-jobi">
    <b-container class="bv-example-row pos-container" fluid>
      <div class="pos-card">

        <!-- ================= CABECERA: Buscar / Mostrar ================= -->
        <div class="pos-panel pos-panel-header">
          <b-row class="align-items-end pos-header-row">
            <b-col md="6" class="pos-header-col-fields">
              <b-form-group class="pos-field mb-0">
                <label><i class="fas fa-search pos-label-icon"></i> Buscar</label>
                <b-input-group class="pos-search-group">
                  <span class="pos-cliente-pin"><i class="fas fa-file-invoice"></i></span>
                  <b-form-input v-model="filter" placeholder="Buscar documento..." @input="currentPage = 1" />
                  <b-input-group-append>
                    <b-btn class="pos-btn-limpiar" :disabled="!filter" @click="filter = ''"><i class="fas fa-times"></i> Limpiar</b-btn>
                  </b-input-group-append>
                </b-input-group>
              </b-form-group>
            </b-col>

            <b-col md="3" class="pos-header-col-fields">
              <b-form-group class="pos-field mb-0">
                <label><i class="fas fa-list-ol pos-label-icon"></i> Mostrar</label>
                <b-form-select :options="pageOptions" v-model="perPage" @input="currentPage = 1"/>
              </b-form-group>
            </b-col>

            <b-col md="3" class="d-flex align-items-center justify-content-end">
              <div class="pos-stat-mini">
                <div class="pos-stat-mini-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                <div>
                  <div class="pos-stat-mini-num">{{ totalRows }}</div>
                  <div class="pos-stat-mini-label">Documentos</div>
                </div>
              </div>
            </b-col>
          </b-row>
        </div>

        <!-- ================= TABLA ================= -->
        <div class="pos-table-card">
          <b-table small
            ref="tablaVentas"
            :filter="filter"
            :sort-by.sync="sortBy"
            :sort-desc.sync="sortDesc"
            striped
            hover
            responsive
            @row-clicked="rowClick"
            id="my-table"
            :busy.sync="isBusy"
            :items="myProvider"
            :fields="fields"
            :per-page="perPage"
            :current-page="currentPage">

            <template slot="Reimprimir" slot-scope="data">
                <a class="pos-btn-reimprimir" target="_blank" :href="'/imprimir/index.php?IdDocVenta=' + data.item.idDocVenta + '&preview=1'">
                  <i class="fas fa-print"></i> {{ data.item.TipoDoc }}
                </a>
            </template>

            <template slot="Ticket" slot-scope="data">
                <a
                    class="jobi-pill-btn jobi-btn-ticket"
                    title="Regenerar ticket"
                    @click.prevent="reimprimir(data.item.idDocVenta)">
                    <i class="fas fa-receipt"></i> Ticket
                </a>
            </template>

            <template slot="Pdf" slot-scope="data">
                <a
                    class="jobi-pill-btn jobi-btn-pdf"
                    title="Regenerar PDF"
                    @click="regenerarPDF(data.item)">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
            </template>

            <template slot="XML" slot-scope="data">
                <a
                    class="jobi-pill-btn jobi-btn-xml"
                    title="Descargar XML"
                    @click="descargarXML(data.item)">
                    <i class="fas fa-file-code"></i> XML
                </a>
            </template>

            <template slot="table-busy">
              <div class="pos-table-busy">
                <i class="fas fa-spinner fa-spin"></i>
                <span>Cargando ventas...</span>
              </div>
            </template>

            <template slot="empty">
              <div class="pos-table-empty">
                <i class="fas fa-file-invoice"></i>
                <span>No se encontraron documentos de venta.</span>
              </div>
            </template>
          </b-table>

          <b-row class="pos-pagination-row">
            <b-col md="6" class="my-1">
              <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" />
            </b-col>
          </b-row>
        </div>

      </div>
    </b-container>
  </div>
</template>

<script>
import config from '@/store/config.js'

export default {
  mounted() {
    this.axios.get(this.API + 'ventas/count').then((response) => {
      this.totalRows = response.data.total
    })
  },
  data() {
    return {
      API: config.API,
      pageOptions: [ 5, 10, 15, 50, 100 ],
      filter: null,
      sortBy: 'idDocVenta',
      sortDesc: true,
      productoSelect: {},
      items: [],
      isBusy: false,
      fields: [
            { key: 'idDocVenta', label: 'ID', sortable: true },
            { key: 'Correlativo', sortable: true },
            { key: 'FechaDoc', sortable: true },
            { key: 'DniRuc', sortable: true },
            { key: 'TipoDoc', sortable: true },
            { key: 'Cliente', sortable: true },
            { key: 'Total', sortable: true },
            { key: 'Reimprimir' },
            { key: 'Ticket' },
            { key: 'Pdf' },
            { key: 'XML' },
      ],
      totalRows: 50,
      perPage: 10,
      currentPage: 1
    }
  },
  methods: {
      rowClick (item, index) {
        this.productoSelect = item
        //this.$refs.productoForm.producto.IdProducto = 'a'
        //console.log(this.$refs.productoForm.$refs.productoForm)
      },
      actualizarTabla() {
        this.$refs.tablaVentas.refresh()
      },
      nuevoProducto () {
          this.productoSelect = {}
          this.$refs.productoForm.show = true

      },
      async regenerarPDF(doc) {
            // Tolerante a ambos casings del ID, según venga de este listado (idDocVenta)
            // o del módulo de Reimpresión (IdDocVenta).
            const id = doc.idDocVenta !== undefined ? doc.idDocVenta : doc.IdDocVenta;

            if (id === undefined || id === null) {
                this.$toasted.error("Id de venta no disponible");
                return;
            }

            const nombreArchivo = doc.NroComprobante || doc.Correlativo || id;

            this.loading = true;
            try {
                const res = await this.axios.get(
                    this.API + "imprimirpdf/" + id,
                    { responseType: 'blob' }
                );

                const url = window.URL.createObjectURL(
                    new Blob([res.data], { type: 'application/pdf' })
                );
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', `${nombreArchivo}.pdf`);
                document.body.appendChild(link);
                link.click();
                link.remove();
                window.URL.revokeObjectURL(url);

                this.$toasted.success("El PDF fue generado nuevamente", { duration: 7000 });
            } catch (err) {
                console.error(err);
                this.$toasted.error("No se pudo generar el PDF", { duration: 7000 });
            } finally {
                this.loading = false;
            }
      },

    reimprimir(idDocVenta) {
      if (idDocVenta === undefined || idDocVenta === null) {
        this.$toasted.error("Id de venta no disponible");
        return;
      }
      const url = '/imprimir/index.php?IdDocVenta=' + idDocVenta + '&preview=1'
      const iframe = document.createElement('iframe')
      iframe.style.position = 'absolute'
      iframe.style.width = '0px'
      iframe.style.height = '0px'
      iframe.style.border = 'none'
      iframe.src = url
      iframe.onload = function () {
        iframe.contentWindow.print()
        setTimeout(() => document.body.removeChild(iframe), 1000)
      }
      document.body.appendChild(iframe)
    },
    descargarXML: function (doc) {
      const id = doc.idDocVenta !== undefined ? doc.idDocVenta : doc.IdDocVenta;

      if (id === undefined || id === null) {
        this.$toasted.error("Id de venta no disponible");
        return;
      }

      this.axios.post(this.API + 'emitirelectronico', {
        generarXml: true,
        docVenta: doc
      })
        .then(function (response) {
          if (response.data.success) {
            this.$toasted.success(response.data.msj_sunat || "XML generado correctamente");

            this.axios.get(this.API + 'imprimirxml/' + id)
              .then(function (res) {
                if (res.data.success && res.data.xmlBase64) {
                  var blob = new Blob(
                    [Uint8Array.from(atob(res.data.xmlBase64), c => c.charCodeAt(0))],
                    { type: 'application/xml' }
                  );
                  var link = document.createElement('a');
                  link.href = window.URL.createObjectURL(blob);
                  link.download = res.data.nombreArchivo; // ← nombre exacto del backend
                  link.click();
                } else {
                  this.$toasted.error("No se pudo obtener el XML");
                }
              }.bind(this));
          } else {
            this.$toasted.error(response.data.msj_sunat || "Error generando XML");
          }
        }.bind(this))
        .catch(function (error) {
          console.error(error);
          this.$toasted.error("Error en la petición");
        }.bind(this));
    },
      myProvider (ctx) {
        // Here we don't set isBusy prop, so busy state will be handled by table itself
        // this.isBusy = true
        let url = this.API + 'ventas?noFecha=1&limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter='+ (ctx.filter||'')+'&sortBy='+ctx.sortBy
        url += '&sortDesc='+(ctx.sortDesc ? 1 : 0 )
        let promise = this.axios.get( url )

        return promise.then((result) => {
          this.items = result.data
          //this.totalRows = this.items.length
          // Here we could override the busy state, setting isBusy to false
          // this.isBusy = false
          return(this.items)
        }).catch(error => {
          // Here we could override the busy state, setting isBusy to false
          // this.isBusy = false
          // Returning an empty array, allows table to correctly handle busy state in case of error
          return []
        })
      }
  },
  components: {
  }
}
</script>

<style scoped>
/* =========================================================
   VentasLista.vue - mismo lenguaje visual JoBi que
   ProductosLista.vue / Venta.vue / Movimiento.vue
   ========================================================= */
.pos-jobi {
    --jobi-navy: #00666A;
    --jobi-navy-2: #00666A;
    --jobi-teal: #0F9B8E;
    --jobi-blue: #2F6FED;
    --jobi-green: #17A554;
    --jobi-red: #E5484D;
    --jobi-orange: #F5A623;
    --jobi-purple: #8B5CF6;
    --jobi-bg: #EEF3F8;
    background: var(--jobi-bg);
    min-height: 100vh;
    padding-bottom: 10px !important;
    font-family: -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    width: 100%;
}

.pos-container {
    padding-top: 10px !important;
    max-width: 100% !important;
}

.pos-card {
    background: #fff;
    border-radius: 18px;
    box-shadow: 0 12px 30px rgba(11,42,74,.10);
    padding: 12px 16px 16px !important;
}

/* -------- Panel de cabecera navy: Buscar / Mostrar / Total -------- */
.pos-panel-header {
    background: linear-gradient(135deg, var(--jobi-navy) 0%, var(--jobi-navy-2) 100%);
    border-radius: 14px;
    padding: 16px 18px;
    margin-bottom: 16px;
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

.pos-label-icon {
    font-size: .82em;
    opacity: .85;
}

.pos-field >>> input,
.pos-field >>> select,
.pos-field >>> .form-control {
    border-radius: 10px !important;
    background: #fff !important;
    color: var(--jobi-navy);
    font-weight: 700;
    height: 42px;
    border: none !important;
    box-shadow: 0 3px 8px rgba(0,0,0,.15);
}
.pos-field >>> input:focus,
.pos-field >>> .form-control:focus {
    box-shadow: 0 0 0 3px rgba(47,111,237,.30);
}

/* Buscador con pin/icono a la izquierda, estilo pill */
.pos-search-group {
    align-items: center;
}
.pos-cliente-pin {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 42px;
    height: 42px;
    border-radius: 10px;
    background: rgba(255,255,255,.12);
    color: #fff;
    margin-right: 6px;
    font-size: 1.05rem;
    flex: 0 0 auto;
}
.pos-btn-limpiar {
    border-radius: 10px !important;
    background: var(--jobi-red) !important;
    border: none !important;
    color: #fff !important;
    font-weight: 700;
    height: 42px;
    margin-left: 6px;
    box-shadow: 0 4px 10px rgba(229,72,77,.30);
}
.pos-btn-limpiar:disabled {
    opacity: .45;
}

/* Mini tarjeta con el total de documentos */
.pos-stat-mini {
    display: flex;
    align-items: center;
    gap: 10px;
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.14);
    border-radius: 12px;
    padding: 8px 14px;
}
.pos-stat-mini-icon {
    width: 34px;
    height: 34px;
    border-radius: 9px;
    background: #fff;
    color: var(--jobi-navy);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    box-shadow: 0 3px 8px rgba(0,0,0,.15);
    flex: 0 0 auto;
}
.pos-stat-mini-num {
    font-size: 1.15rem;
    font-weight: 900;
    line-height: 1.1;
    color: #fff;
}
.pos-stat-mini-label {
    font-size: .68rem;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: rgba(255,255,255,.65);
    font-weight: 800;
}

/* -------- Tarjeta que envuelve la tabla -------- */
.pos-table-card {
    background: #fff;
    border-radius: 14px;
    padding: 6px;
    box-shadow: 0 4px 14px rgba(11,42,74,.06);
    border: 1px solid #e7edf5;
    overflow-x: auto;
}

.pos-table-card >>> table {
    margin-bottom: 0;
}

.pos-table-card >>> thead th {
    background: var(--jobi-navy);
    color: #fff !important;
    text-transform: uppercase;
    font-size: .70rem;
    letter-spacing: .03em;
    font-weight: 800;
    border: none !important;
    white-space: nowrap;
    vertical-align: middle;
    padding: 10px 8px;
    cursor: pointer;
}
.pos-table-card >>> thead th:first-child { border-top-left-radius: 10px; }
.pos-table-card >>> thead th:last-child { border-top-right-radius: 10px; }

.pos-table-card >>> tbody td {
    vertical-align: middle;
    font-size: .84rem;
    padding: 9px 8px;
    border-color: #eef2f7;
    cursor: pointer;
}
.pos-table-card >>> tbody tr:hover {
    background-color: #f5f9ff !important;
}

.pos-table-busy,
.pos-table-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 34px 16px;
    color: #8296b0;
    text-align: center;
}
.pos-table-busy i,
.pos-table-empty i {
    font-size: 1.9rem;
    color: #c6d4e3;
}
.pos-table-busy span,
.pos-table-empty span {
    font-size: .85rem;
    font-weight: 600;
    max-width: 320px;
}

/* Botón "Reimprimir" dentro de la tabla — mismo estilo pill que
   Ticket/PDF/XML y que el modulo de Reimpresión */
.pos-btn-reimprimir {
    border: none;
    border-radius: 999px;
    color: #fff !important;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 0 14px;
    font-size: .78rem;
    font-weight: 600;
    text-decoration: none !important;
    cursor: pointer;
    white-space: nowrap;
    background: var(--jobi-green) !important;
    transition: box-shadow .2s ease;
}
.pos-btn-reimprimir:hover {
    box-shadow: 0 3px 10px rgba(0,0,0,.25);
    color: #fff !important;
    text-decoration: none !important;
}

/* =========================================================
   Botones de acción Ticket / PDF / XML — mismo estilo "pill"
   usado en el modulo de Reimpresión (jobi-pill-btn), para que
   ambos módulos se vean consistentes.
   ========================================================= */
.jobi-pill-btn {
    border: none;
    border-radius: 999px;
    color: #fff !important;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 0 14px;
    font-size: .78rem;
    font-weight: 600;
    text-decoration: none !important;
    cursor: pointer;
    user-select: none;
    white-space: nowrap;
    transition: box-shadow .2s ease;
}
.jobi-pill-btn:hover {
    box-shadow: 0 3px 10px rgba(0,0,0,.25);
    color: #fff !important;
    text-decoration: none !important;
}
.jobi-pill-btn i {
    font-size: .82em;
}

/* Ticket -> teal, igual que en el modulo de Reimpresión */
.jobi-btn-ticket {
    background: var(--jobi-teal) !important;
}

/* PDF -> naranja, igual que en el modulo de Reimpresión */
.jobi-btn-pdf {
    background: var(--jobi-orange) !important;
}

/* XML -> púrpura, para mantenerlo diferenciado (no existe en el otro módulo) */
.jobi-btn-xml {
    background: var(--jobi-purple) !important;
}

/* Las celdas de acciones se agrupan sin salto de línea y centradas */
.pos-table-card >>> td:has(.jobi-pill-btn) {
    white-space: nowrap;
}

.pos-pagination-row {
    padding: 10px 8px 4px;
}
.pos-pagination-row >>> .page-link {
    border-radius: 8px !important;
    margin: 0 2px;
    border: 1.5px solid #dbe6f1 !important;
    color: var(--jobi-navy);
    font-weight: 700;
}
.pos-pagination-row >>> .page-item.active .page-link {
    background: var(--jobi-blue) !important;
    border-color: var(--jobi-blue) !important;
    color: #fff !important;
}

@media (max-width: 767px) {
    .pos-header-col-fields { margin-bottom: 10px; }
}
</style>