<template>
  <div>
    <b-button @click="show = true" variant="primary" size="sm" class="btnAction">Ver Proformas</b-button>

    <b-form ref="formProformaModal">


      <b-modal id="modalProformas" title="Buscar Proforma" class="mdlProforma" v-model="show" size="xl"
        header-bg-variant="dark" headerTextVariant="light" body-bg-variant="light" body-text-variant="dark">
        <b-tabs pills card>
          <b-tab title="Proformas" active>
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
          <b-tab title="Proformas Facturadas">
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
          <b-tab title="Proformas Favoritas">
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
  </div>
</template>
<script>
import config from "@/store/config.js";
import { mapActions } from "vuex";

export default {
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
        { key: "update", label: "Editar", thStyle: { width: "70px" } },
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
        { key: "update", label: "Editar", thStyle: { width: "70px" } },
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
      currentPageFav: 1,
      totalRows: 50,
      perPage: 5,
      currentPage: 1
    };
  },
  props: ["idAlmacen"],
  methods: {
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
    selectProformas(proforma) {
      this.$emit("guardarDatosProforma", proforma.IdProforma);
      this.axios
        .get(this.API + "proformas/detalle", {
          params: {
            idProforma: proforma.IdProforma
          }
        })
        .then(response => {
          response.data.map(item => {
            item.setPrecio = true;
            item.precio = item.Precio;
            item.cantidad = item.Cantidad;
            item.descuento = item.Descuento;
            // this.add([
            //   item.IdProducto,
            //   () => {
            //     this.obtenerStok({ IdAlmacen: this.idAlmacen || 1 });
            //   },
            //   {
            //     setPrecio: true,
            //     setPreOr: item.Precio,
            //     cantidad: item.Cantidad,
            //     descuento: item.Descuento
            //   }
            // ]);

            this.filter = "";
            this.show = false;
          });
          proforma.productos = response.data;
          // console.log(proforma);

          // this.$emit('onSelectProforma', proforma.IdCliente, proforma)
          this.$emit("onSelectProforma", proforma);
          this.limpiarCarrito();
        });
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