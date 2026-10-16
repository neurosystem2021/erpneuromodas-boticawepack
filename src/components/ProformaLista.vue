<template>
<div>
  <b-button @click="show = true" variant="primary" size="sm" class="btnAction">Ver Proformas</b-button>

  <b-form ref="formProformaModal">


    <b-modal id="modalProformas" title="Buscar Proforma" class="mdlProforma" v-model="show" size="xl"
      header-bg-variant="dark" headerTextVariant="light" body-bg-variant="light" body-text-variant="dark" modal-class="nst-dialog" body-class="nst-dialog-body" footer-class="nst-dialog-footer" centered="">
      <b-tabs pills card>
        <b-tab title="Proformas" active><template slot="title"><i class="fas fa-layer-group nst-tab-icon" aria-hidden="true"></i><span>Proformas</span></template>
          <b-form-input v-model="filter" placeholder="Buscar" @input="currentPage = 1" />
          <div class="table-responsive table-proforma-scroll">
            <b-table :key="tableKey" v-if="show" id="table-proformas" ref="tableProformas" striped hover
              :busy.sync="isBusy" :filter="filter" :items="myProvider" :fields="fields" :per-page="100"
              :current-page="currentPage">

              <template slot="EsFavoritoFlag" slot-scope="data">
                <i @click="cambiarFavorito(data.item, 0)" class="fa fa-star" style="color: #F57F17; cursor: pointer;"
                  aria-hidden="true" v-if="data.item.EsFavoritoFlag"></i>
                <i @click="cambiarFavorito(data.item, 1)" class="far fa-star" style="cursor: pointer;"
                  aria-hidden="true" v-else></i>
              </template>
              <template slot="editarDocumento" slot-scope="data">
                <b-button @click="editId=data.item.IdProforma;editOpen=true" variant="warning" size="sm" title="Editar proforma"><i class="fas fa-edit"></i></b-button>
              </template>
              <template slot="update" slot-scope="data">
                <b-button @click="selectProformas(data.item)" variant="success"><i
                    class="fas fa-pencil-alt"></i></b-button>
              </template>

              <template slot="ver" slot-scope="data">
                <b-button :href="'/nuevo/index.html#/reportes/proforma/' + data.item.IdProforma" variant="primary"
                  target="_blank">Ver</b-button>
              </template>

              <template slot="Total" slot-scope="data">
                <b>{{ data.item.Total | moneda }}</b>
              </template>

              <template slot="Anular" slot-scope="data">
                <b-button @click="eliminarProf(data.item)" variant="danger"><i class="fas fa-times"></i></b-button>
              </template>

              <template slot="verTicket" slot-scope="data">
                <b-button :href="'/imprimir/proforma.php?IdProforma=' + data.item.IdProforma" variant="primary"
                  target="_blank">Ver Ticket</b-button>
              </template>

            </b-table>
          </div>
          <b-row>
            <b-col md="6" class="my-1">
              <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" />
            </b-col>
          </b-row>
          <div slot="modal-footer">
            <!--<b-btn size="sm" variant="success" type="submit">
                      Guardar
                  </b-btn>-->
            <b-btn size="sm" variant="secondary" @click="show = false">
              Cerrar
            </b-btn>
          </div>
        </b-tab>
        <b-tab title="Proformas Facturadas"><template slot="title"><i class="fas fa-layer-group nst-tab-icon" aria-hidden="true"></i><span>Proformas Facturadas</span></template>
          <b-row class="align-items-center mb-2">
            <b-col cols="8">
              <b-form-input v-model="filterFav" placeholder="Buscar" @input="currentPageFav = 1" />
            </b-col>
            <b-col cols="4">
              <b-input-group prepend="Mostrar">
                <b-form-select v-model="perPageFav" :options="opcionesPorPagina" @change="currentPageFav = 1" />
              </b-input-group>
            </b-col>
          </b-row>

          <div class="tablaScroll">
            <b-table v-if="show" id="table-proformas-facturadas" ref="tableProformasFacturadas" striped hover
              :busy.sync="isBusyFav" :filter="filterFav" :items="myProviderFacturadas" :fields="fieldsFacturadas"
              :per-page="perPageFav" :current-page="currentPageFacturadas">

              <template slot="ver" slot-scope="data">
                <b-button :href="'/nuevo/index.html#/reportes/proforma/' + data.item.IdProforma" variant="primary"
                  target="_blank">Ver</b-button>
              </template>

              <template slot="Total" slot-scope="data">
                <b>{{ data.item.Total | moneda }}</b>
              </template>

              <template slot="verTicket" slot-scope="data">
                <b-button :href="'/imprimir/proforma.php?IdProforma=' + data.item.IdProforma" variant="primary"
                  target="_blank">Ver Ticket</b-button>
              </template>
              <template slot="Estado" slot-scope="data">
                <b-button variant="danger" target="_blank">Facturado</b-button>
              </template>
              <template slot="Facturado" slot-scope="data">
                <template v-if="parseVentas(data.item.IdDocVenta).length">
                  <b-button v-for="id in ventasVisibles(data.item)" :key="id"
                    :href="'/imprimir/index.php?IdDocVenta=' + id" variant="info" size="sm" class="mr-1 mb-1"
                    target="_blank">
                    <i class="fas fa-file-invoice"></i> Venta #{{ id }}
                  </b-button>

                  <b-button v-if="parseVentas(data.item.IdDocVenta).length > 2" size="sm" variant="secondary"
                    class="mb-1" @click="toggleExpand(data.item.IdProforma)">
                    <i :class="expandedRows[data.item.IdProforma] ? 'fas fa-chevron-up' : 'fas fa-chevron-down'"></i>
                    {{ expandedRows[data.item.IdProforma]
                      ? 'Ver menos'
                      : '+' + (parseVentas(data.item.IdDocVenta).length - 2) + ' más' }}
                  </b-button>
                </template>
                <span v-else class="text-muted">Sin comprobante</span>
              </template>
            </b-table>
          </div>

          <b-pagination v-model="currentPageFacturadas" :total-rows="totalRowsFav"
            :per-page="perPageFav"></b-pagination>
        </b-tab>
        <b-tab title="Proformas Favoritas"><template slot="title"><i class="fas fa-layer-group nst-tab-icon" aria-hidden="true"></i><span>Proformas Favoritas</span></template>
          <b-input-group prepend="Buscar..." class="mt-2">
            <input class="form-control" v-model="search" />
          </b-input-group>
          <b-form-group>
          </b-form-group>
          <div class="table-responsive table-proforma-scroll">
            <b-table v-if="show" id="table-proformas" ref="tableProformas" :filter="filter" :items="filteredItems"
              :fields="fieldsFavoritas" :per-page="100" :current-page="currentPageFav">
              <!-- <template slot="EsFavoritoFlag" slot-scope="data">
                      <i @click="cambiarFavorito(data.item,0)" class="fa fa-star" style="color: #F57F17; cursor: pointer;" aria-hidden="true" v-if="data.item.EsFavoritoFlag"></i>
                      <i @click="cambiarFavorito(data.item,1)" class="far fa-star" style="cursor: pointer;" aria-hidden="true" v-else></i>
                    </template> -->
              <template slot="editarDocumento" slot-scope="data">
                <b-button @click="editId=data.item.IdProforma;editOpen=true" variant="warning" size="sm" title="Editar proforma"><i class="fas fa-edit"></i></b-button>
              </template>
              <template slot="update" slot-scope="data">
                <b-button @click="selectProformas(data.item)" variant="success"><i
                    class="fas fa-pencil-alt"></i></b-button>
              </template>

              <template slot="ver" slot-scope="data">
                <b-button :href="'/nuevo/index.html#/reportes/proforma/' + data.item.IdProforma" variant="primary"
                  target="_blank">Ver</b-button>
              </template>

              <template slot="Total" slot-scope="data">
                <b>{{ data.item.Total | moneda }}</b>
              </template>

              <template slot="Anular" slot-scope="data">
                <b-button @click="eliminarProf(data.item)" variant="danger"><i class="fas fa-times"></i></b-button>
              </template>

              <template slot="verTicket" slot-scope="data">
                <b-button :href="'/imprimir/proforma.php?IdProforma=' + data.item.IdProforma" variant="primary"
                  target="_blank">Ver Ticket</b-button>
              </template>

            </b-table>
          </div>
          <b-pagination v-model="currentPageFav" :total-rows="filteredItems.length" :per-page="5"
            aria-controls="my-table"></b-pagination>
        </b-tab>
      </b-tabs>


    </b-modal>

  </b-form>
  <b-modal v-model="editOpen" title="Editar Proforma" size="lg" hide-footer lazy modal-class="plastifull-quote-modal" header-bg-variant="dark" header-text-variant="light"><proforma-editor v-if="editOpen" :quote-id="editId" embedded @cancel="editOpen=false" @saved="proformaSaved" /></b-modal>
</div>
</template>
<script>
import ProformaEditor from '@/views/ProformaEditor.vue';
import config from "@/store/config.js";
import { mapActions } from "vuex";

export default {
  components:{ProformaEditor},
  mounted() {
    this.$emit("mounted", this.$refs.formProformaModal);
  },
  watch: {
    items() {
      this.axios.get(this.API + "proformas/count").then(response => {
        this.totalRows = response.data.total;
      });
    },
    show() {
      if (this.show) {
        this.cargarFavoritas();
      }
    }
  },
  data() {
    return {
      editOpen:false,editId:null,
      tableKey: 0,
      API: config.API,
      show: false,
      search: "",
      lstProformasFavoritas: [],
      filter: "",
      items: [],
      isBusy: false,
      fields: [
        { key: "ver", label: "Ver", thStyle: { width: "60px" } },
        { key: "verTicket", label: "Ticket", thStyle: { width: "90px" } },
        { key: "update", label: "Cargar para facturar", thStyle: { width: "90px" } },
        { key: "editarDocumento", label: "Editar proforma", thStyle: { width: "140px" } },
        { key: "Total", label: "Total", thStyle: { width: "90px" } },
        { key: "Cliente", label: "Cliente", thStyle: { width: "180px" } },
        { key: "Descripcion", label: "Desc.", thStyle: { width: "150px" }, tdClass: "td-descripcion" },
        { key: "EsFavoritoFlag", label: "Favorito", thStyle: { width: "80px" } },
        { key: "IdProforma", label: "Id", thStyle: { width: "60px" } },
        { key: "Anio", label: "Año", thStyle: { width: "60px" } },
        { key: "Numero", label: "Número", thStyle: { width: "80px" } },
        { key: "DniRuc", label: "Documento", thStyle: { width: "110px" } },
        { key: "FechaReg", label: "Fecha", thStyle: { width: "100px" } },
        { key: "UsuarioReg", label: "Empleado", thStyle: { width: "120px" } },
        { key: "Anular", label: "Anular", thStyle: { width: "70px" } }
      ],
      fieldsFavoritas: [
        // { key: "EsFavoritoFlag", label: "Favorito" },
        { key: "ver", label: "Ver", thStyle: { width: "60px" } },
        { key: "verTicket", label: "Ticket", thStyle: { width: "90px" } },
        { key: "update", label: "Cargar para facturar", thStyle: { width: "90px" } },
        { key: "editarDocumento", label: "Editar proforma", thStyle: { width: "140px" } },
        { key: "Total", label: "Total", thStyle: { width: "90px" } },
        { key: "Cliente", label: "Cliente", thStyle: { width: "180px" } },
        { key: "Descripcion", label: "Desc.", thStyle: { width: "150px" }, tdClass: "td-descripcion" },
        { key: "IdProforma", label: "Id", thStyle: { width: "60px" } },
        { key: "Anio", label: "Año", thStyle: { width: "60px" } },
        { key: "Numero", label: "Número", thStyle: { width: "80px" } },
        { key: "DniRuc", label: "Documento", thStyle: { width: "110px" } },
        { key: "FechaReg", label: "Fecha", thStyle: { width: "100px" } },
        { key: "UsuarioReg", label: "Empleado", thStyle: { width: "120px" } },
        { key: "Anular", label: "Anular", thStyle: { width: "70px" } }
      ],
      fieldsFacturadas: [
        { key: "ver", label: "Ver", thStyle: { width: "60px" } },
        { key: "verTicket", label: "Ticket", thStyle: { width: "90px" } },
        { key: "Estado", label: "Estado", thStyle: { width: "100px" } },
        { key: "Facturado", label: "Comprobante", thStyle: { width: "200px" } },
        { key: "Total", label: "Total", thStyle: { width: "90px" } },
        { key: "Cliente", label: "Cliente", thStyle: { width: "180px" } },
        { key: "IdProforma", label: "Id", thStyle: { width: "60px" } },
        { key: "Anio", label: "Año", thStyle: { width: "60px" } },
        { key: "Numero", label: "Número", thStyle: { width: "80px" } },
        { key: "DniRuc", label: "Documento", thStyle: { width: "110px" } },
        { key: "FechaReg", label: "Fecha", thStyle: { width: "100px" } },
        { key: "UsuarioReg", label: "Empleado", thStyle: { width: "120px" } }
      ],
      filterFav: "",
      isBusyFav: false,
      perPageFav: 10,
      opcionesPorPagina: [10, 25, 50, 100],
      currentPageFacturadas: 1,
      totalRowsFav: 0,
      expandedRows: {},
      currentPageFav: 1,
      totalRows: 50,
      perPage: 5,
      currentPage: 1
    };
  },
  props: ["idAlmacen"],
  methods: {
    proformaSaved(){this.editOpen=false;this.tableKey++;this.cargarFavoritas();},
    ...mapActions({
      add: "agregarProducto",
      obtenerStok: "obtenerStock",
      limpiarCarrito: "limpiarCarrito"
    }),
    cargarFavoritas() {
      this.lstProformasFavoritas = [];
      this.axios(`${this.API}proformas/favoritas`).then(response => {
        if (response.data) {
          this.lstProformasFavoritas = response.data;
        }
      });
    },
    async cambiarFavorito(item, EsFavoritoFlag) {
      item.EsFavoritoFlag = EsFavoritoFlag;
      console.log(item);
      await this.axios.put(`${this.API}proforma/cambiarfavorito`, item);
      console.log(EsFavoritoFlag);
      this.cargarFavoritas();
    },
    async selectProformas(proforma) {
      try {
        const response = await this.axios.get(this.API + 'proformas/id/' + proforma.IdProforma);
        const loaded = Object.assign({}, proforma, response.data);
        loaded.productos = (loaded.productos || []).map(item => Object.assign({}, item, {
          setPrecio: true, precio: Number(item.Precio), cantidad: Number(item.Cantidad),
          descuento: Number(item.Descuento || 0), disabledProf: Number(item.DisabledTotal || 0)
        }));
        this.$emit('onSelectProforma', loaded);
        this.filter = ''; this.show = false;
      } catch (error) { this.$toasted.error('No se pudo abrir la proforma. Intenta nuevamente.'); }
    },
    myProviderFacturadas(ctx) {
      let promise = this.axios.get(
        this.API +
        "proformas/facturadas?limit=" +
        ctx.perPage +
        "&page=" +
        ctx.currentPage +
        "&filter=" +
        (ctx.filter || "")
      );

      return promise
        .then(result => {
          this.actualizarTotalFacturadas(ctx.filter || "");
          return result.data;
        })
        .catch(error => {
          console.log(error);
          return [];
        });
    },
    actualizarTotalFacturadas(filter) {
      this.axios
        .get(this.API + "proformas/facturadas/count?filter=" + (filter || ""))
        .then(response => {
          this.totalRowsFav = response.data.total;
        });
    },
    parseVentas(idDocVenta) {
      if (!idDocVenta) return [];
      try {
        const parsed = typeof idDocVenta === "string" ? JSON.parse(idDocVenta) : idDocVenta;
        return Array.isArray(parsed) ? parsed : [];
      } catch (e) {
        return [];
      }
    },
    ventasVisibles(item) {
      const ventas = this.parseVentas(item.IdDocVenta);
      return this.expandedRows[item.IdProforma] ? ventas : ventas.slice(0, 2);
    },
    toggleExpand(idProforma) {
      this.$set(this.expandedRows, idProforma, !this.expandedRows[idProforma]);
    },
    myProvider(ctx) {
      // Here we don't set isBusy prop, so busy state will be handled by table itself
      // this.isBusy = true
      let promise = this.axios.get(
        this.API +
        "proformas?limit=" +
        ctx.perPage +
        "&page=" +
        ctx.currentPage +
        "&filter=" +
        ctx.filter
      );

      return promise
        .then(result => {
          this.items = result.data;
          //this.totalRows = this.items.length
          // Here we could override the busy state, setting isBusy to false
          // this.isBusy = false
          return this.items;
        })
        .catch(error => {
          // Here we could override the busy state, setting isBusy to false
          // this.isBusy = false
          // Returning an empty array, allows table to correctly handle busy state in case of error
          return [];
        });
    },
    eliminarProf(data) {
      this.$swal({
        target: document.getElementById("conten"),
        title:
          "Se anulará la proforma: <strong>" +
          data.Anio +
          " - " +
          data.Numero +
          "</strong> del  cliente <strong>" +
          data.Cliente +
          "</strong>",
        text: "¿Está seguro?",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Sí, anular",
        cancelButtonText: "No, atrás"
      }).then(result => {
        if (result.isConfirmed) {
          this.axios
            .delete(this.API + "proforma/anular/" + data.IdProforma)
            .then(res => {
              if (res.data) {
                this.$toasted.info("¡PROFORMA ANULADA!");
                this.tableKey++;
              }
            })
            .catch(err => {
              console.log(err);
              this.$toasted.error("Ha ocurrido un error");
            });
        }
      });
    }
  },
  computed: {
    filteredItems() {
      const searchTextLower = this.search.toLowerCase();
      return this.lstProformasFavoritas.filter(item => {
        return Object.values(item).some(value => {
          if (typeof value === "string") {
            return value.toLowerCase().includes(searchTextLower);
          } else if (typeof value === "number") {
            return value.toString().includes(searchTextLower);
          }
          return false;
        });
      });
    }
  }
};
</script>
<style>
css

/* ── Modal: ocupa toda la ventana ── */
#modalProformas .modal-dialog {
  max-width: 85vw !important;
  width: 98vw !important;
  margin: 1vh auto !important;
}

#modalProformas .modal-content {
  height: 96vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

#modalProformas .modal-header {
  flex-shrink: 0;
}

#modalProformas .modal-footer {
  flex-shrink: 0;
}

#modalProformas .modal-body {
  flex: 1;
  overflow: hidden;
  padding: 0.5rem;
  display: flex;
  flex-direction: column;
}

/* ── Tabs ── */
#modalProformas .tabs {
  flex: 1;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

#modalProformas .tab-content {
  flex: 1;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

#modalProformas .tab-pane.active {
  flex: 1;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

/* Bootstrap-Vue genera .card y .card-body dentro de b-tabs con card */
#modalProformas .card {
  flex: 1;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  border: none;
}

#modalProformas .card-body {
  flex: 1;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  padding: 0.5rem;
}

/* ── Buscador: no crece ── */
#modalProformas .form-control,
#modalProformas .input-group {
  flex-shrink: 0;
  margin-bottom: 0.5rem;
}

/* ── Paginación: no crece ── */
#modalProformas .pagination,
#modalProformas nav {
  flex-shrink: 0;
  margin-top: 0.5rem;
}

/* ── Contenedor tabla con ambos scrolls ── */
.table-proforma-scroll {
  flex: 1;
  overflow-x: auto !important;
  overflow-y: auto !important;
  -webkit-overflow-scrolling: touch;
  min-height: 0;
}

/* ── Tabla con layout fijo para respetar anchos de cabeceras ── */
.table-proforma-scroll>table,
.table-proforma-scroll .table {
  min-width: 1100px !important;
  width: 100% !important;
  table-layout: fixed !important;
}

/* ── Todas las celdas sin salto por defecto ── */
.table-proforma-scroll td,
.table-proforma-scroll th {
  white-space: normal !important;
  word-break: break-word;
  overflow: visible;
}

/* ── Descripción permite salto de línea ── */
.td-descripcion {
  white-space: normal !important;
  word-break: break-word !important;
  overflow: visible !important;
  text-overflow: unset !important;
}

/* ── Responsive móvil ── */
@media (max-width: 768px) {
  #modalProformas .modal-dialog {
    width: 100vw !important;
    max-width: 100vw !important;
    margin: 0 !important;
  }

  #modalProformas .modal-content {
    height: 100vh;
    border-radius: 0;
  }
}
</style>
<style>
.plastifull-quote-modal .modal-dialog{max-width:960px!important;width:95vw!important}.plastifull-quote-modal .modal-body{padding:12px!important;max-height:80vh;overflow:auto}.plastifull-quote-modal .quote-editor{padding:0!important;margin:0!important}.plastifull-quote-modal .quote-editor h2,.plastifull-quote-modal .quote-editor>p{display:none}
</style>
