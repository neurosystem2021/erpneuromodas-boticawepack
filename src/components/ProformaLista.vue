<template>
    <div>
        <b-button block @click="show=true" class="btnAction" variant="warning" size="sm" style="font-weight: bold; font-size: 1.2rem" >Ver Proformas</b-button>
               
        <b-form ref="formProformaModal" >    
        
            <b-modal id="modalProformas" 
                title="Buscar Proforma"
                class="modalOrdenCompra"
                size="lg" v-model="show"
                header-bg-variant="dark"
                headerTextVariant="light"
                body-bg-variant="light"
                body-text-variant="dark">     
                <b-tabs pills card>
                  <b-tab title="Proformas" active>
                    <b-row class="align-items-center mb-2">
                      <b-col cols="8">
                        <b-form-input v-model="filter" placeholder="Buscar" @input="currentPage = 1" />
                      </b-col>
                      <b-col cols="4">
                        <b-input-group prepend="Mostrar">
                          <b-form-select
                            v-model="perPage"
                            :options="opcionesPorPagina"
                            @change="currentPage = 1"
                          />
                        </b-input-group>
                      </b-col>
                    </b-row>

                    <div class="tablaScroll">
                      <b-table 
                          id="table-proformas" 
                          ref="tableProformas"
                          striped hover 
                          :busy.sync="isBusy" 
                          :filter="filter"
                          :items="myProvider" 
                          :fields="fields"
                          :per-page="perPage"
                          :current-page="currentPage">

                        <template slot="EsFavoritoFlag" slot-scope="data">
                          <i @click="cambiarFavorito(data.item,0)" class="fa fa-star" style="color: #F57F17; cursor: pointer;" aria-hidden="true" v-if="data.item.EsFavoritoFlag"></i>
                          <i @click="cambiarFavorito(data.item,1)" class="far fa-star" style="cursor: pointer;" aria-hidden="true" v-else></i>
                        </template>
                        <template slot="update" slot-scope="data">
                          <b-button @click="selectProformas(data.item)" variant="success"><i class="fas fa-pencil-alt"></i></b-button>
                        </template>

                        <template slot="ver" slot-scope="data">
                          <b-button :href="'/nuevo/index.html#/reportes/proforma/' + data.item.IdProforma" variant="primary" target="_blank">Ver</b-button>
                        </template>

                        <template slot="Total" slot-scope="data">
                          <b>{{ data.item.Total | moneda }}</b>
                        </template>

                        <template slot="Anular" slot-scope="data">
                          <b-button @click="eliminarProf(data.item)" variant="danger"><i class="fas fa-times"></i></b-button>
                        </template>

                        <template slot="verTicket" slot-scope="data">
                          <b-button :href="'/imprimir/proforma.php?IdProforma=' + data.item.IdProforma" variant="primary" target="_blank">Ver Ticket</b-button>
                        </template>

                      </b-table>
                    </div>
                    <b-row>
                      <b-col md="6" class="my-1">
                        <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" />
                      </b-col>
                    </b-row>
                    <div slot="modal-footer">
                      <b-btn size="sm" variant="secondary" @click="show=false">
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
                          <b-form-select
                            v-model="perPageFav"
                            :options="opcionesPorPagina"
                            @change="currentPageFav = 1"
                          />
                        </b-input-group>
                      </b-col>
                    </b-row>

                    <div class="tablaScroll">
                      <b-table
                        v-if="show"
                        id="table-proformas-facturadas"
                        ref="tableProformasFacturadas"
                        striped hover
                        :busy.sync="isBusyFav"
                        :filter="filterFav"
                        :items="myProviderFacturadas"
                        :fields="fieldsFacturadas"
                        :per-page="perPageFav"
                        :current-page="currentPageFav">

                        <template slot="ver" slot-scope="data">
                          <b-button :href="'/nuevo/index.html#/reportes/proforma/' + data.item.IdProforma" variant="primary" target="_blank">Ver</b-button>
                        </template>

                        <template slot="Total" slot-scope="data">
                          <b>{{ data.item.Total | moneda }}</b>
                        </template>

                        <template slot="verTicket" slot-scope="data">
                          <b-button :href="'/imprimir/proforma.php?IdProforma=' + data.item.IdProforma" variant="primary" target="_blank">Ver Ticket</b-button>
                        </template>
                        <template slot="Estado" slot-scope="data">
                          <b-button variant="danger" target="_blank">Facturado</b-button>
                        </template>
                        <template slot="Facturado" slot-scope="data">
                        <template v-if="parseVentas(data.item.IdDocVenta).length">
                          <b-button
                            v-for="id in ventasVisibles(data.item)"
                            :key="id"
                            :href="'/imprimir/index.php?IdDocVenta=' + id"
                            variant="info"
                            size="sm"
                            class="mr-1 mb-1"
                            target="_blank"
                          >
                            <i class="fas fa-file-invoice"></i> Venta #{{ id }}
                          </b-button>

                          <b-button
                            v-if="parseVentas(data.item.IdDocVenta).length > 2"
                            size="sm"
                            variant="secondary"
                            class="mb-1"
                            @click="toggleExpand(data.item.IdProforma)"
                          >
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

                    <b-pagination
                      v-model="currentPageFav"
                      :total-rows="totalRowsFav"
                      :per-page="perPageFav"
                    ></b-pagination>
                  </b-tab>
                  <b-tab title="Proformas Favoritas">
                  <b-row class="align-items-center mb-2 mt-2">
                    <b-col cols="8">
                      <b-input-group prepend="Buscar...">
                        <input class="form-control" v-model="search" />
                      </b-input-group>
                    </b-col>
                    <b-col cols="4">
                      <b-input-group prepend="Mostrar">
                        <b-form-select
                          v-model="perPageFavoritas"
                          :options="opcionesPorPagina"
                          @change="currentPageFav = 1"
                        />
                      </b-input-group>
                    </b-col>
                  </b-row>
                  <b-table 
                      id="table-proformas" 
                      ref="tableProformas"
                    :filter="filter"
                      :items="filteredItems" 
                      :fields="fieldsFavoritas"
                      :per-page="perPageFavoritas"
                      :current-page="currentPageFav"
                    >
                       <!-- <template slot="EsFavoritoFlag" slot-scope="data">
                        <i @click="cambiarFavorito(data.item,0)" class="fa fa-star" style="color: #F57F17; cursor: pointer;" aria-hidden="true" v-if="data.item.EsFavoritoFlag"></i>
                        <i @click="cambiarFavorito(data.item,1)" class="far fa-star" style="cursor: pointer;" aria-hidden="true" v-else></i>
                      </template> -->
                      <template slot="update" slot-scope="data">
                        <b-button @click="selectProformas(data.item)" variant="success"><i class="fas fa-pencil-alt"></i></b-button>
                      </template>

                      <template slot="ver" slot-scope="data">
                        <b-button :href="'/nuevo/index.html#/reportes/proforma/' + data.item.IdProforma" variant="primary" target="_blank">Ver</b-button>
                      </template>

                      <template slot="Total" slot-scope="data">
                        <b>{{ data.item.Total | moneda }}</b>
                      </template>

                      <template slot="Anular" slot-scope="data">
                        <b-button @click="eliminarProf(data.item)" variant="danger"><i class="fas fa-times"></i></b-button>
                      </template>

                      <template slot="verTicket" slot-scope="data">
                        <b-button :href="'/imprimir/proforma.php?IdProforma=' + data.item.IdProforma" variant="primary" target="_blank">Ver Ticket</b-button>
                      </template>

                    </b-table>

                    <b-pagination
                      v-model="currentPageFav"
                      :total-rows="filteredItems.length"
                      :per-page="5"
                      aria-controls="my-table"
                    ></b-pagination>
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
      API: config.API,
      show: false,
      search: "",
      lstProformasFavoritas: [],
      filter: "",
      filterFav: "",
      items: [],
      isBusy: false,
      isBusyFav: false,
      expandedRows: {},
      fields: [
        { key: "EsFavoritoFlag", label: "Favorito" },
        { key: "IdProforma", label: "Id" },
        { key: "Anio", label: "Año" },
        "Numero",
        "Cliente",
        { key: "DniRuc", label: "Documento" },
        "FechaReg",
        { key: "UsuarioReg", label: "Empleado" },
        { key: "Total", label: "Total" },
        "update",
        "ver",
        "verTicket",
        ,
        "Anular"
      ],
      fieldsFavoritas: [
        // { key: "EsFavoritoFlag", label: "Favorito" },
        { key: "IdProforma", label: "Id" },
        { key: "Descripcion" },
        { key: "Anio", label: "Año" },
        "Numero",
        "Cliente",
        { key: "DniRuc", label: "Documento" },
        "FechaReg",
        { key: "UsuarioReg", label: "Empleado" },
        { key: "Total", label: "Total" },
        "update",
        "ver",
        "verTicket",
        ,
        "Anular"
      ],
      fieldsFacturadas: [
        { key: "IdProforma", label: "Id" },
        { key: "Anio", label: "Año" },
        { key: "Numero", label: "Numero" },
        { key: "Cliente", label: "Cliente" },
        { key: "DniRuc", label: "Documento" },
        { key: "FechaReg", label: "Fecha Reg" },
        { key: "UsuarioReg", label: "Empleado" },
        { key: "Total", label: "Total" },
        { key: "ver", label: "Ver" },
        { key: "verTicket", label: "Ver Ticket" },
        { key: "Facturado", label: "Comprobante" },
        { key: "Estado", label: "Estado" }
      ],
      currentPageFav: 1,
      totalRows: 50,
      totalRowsFav: 0,
      perPage: 10,
      perPageFav: 10,
      perPageFavoritas: 10,
      opcionesPorPagina: [10, 15, 25, 50],
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
            idProforma: proforma.IdProforma,
            IdAlmacen: sessionStorage.getItem("IdAlmacen")
          }
        })
        .then(response => {
          response.data.map(item => {
            this.add([
              item.IdProducto,
              () => {
                this.obtenerStok({ IdAlmacen: this.idAlmacen || 1 });
              },
              {
                setPrecio: true,
                setPreOr: item.Precio,
                cantidad: item.Cantidad,
                descuento: item.Descuento
              }
            ]);

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
          this.axios.get(this.API + "proformas/facturadas/count").then(res => {
            this.totalRowsFav = res.data.total;
          });
          return result.data;
        })
        .catch(() => []);
    },

    parseVentas(idDocVenta) {
      if (!idDocVenta) return [];
      try {
        const parsed = JSON.parse(idDocVenta);
        return Array.isArray(parsed) ? parsed : [];
      } catch (e) {
        return [];
      }
    },
    myProvider(ctx) {
      // Here we don't set isBusy prop, so busy state will be handled by table itself
      // this.isBusy = true
      let IdAlmacen = sessionStorage.getItem("IdAlmacen");
      let promise = this.axios.get(
        this.API +
          "proformas?limit=" +
          ctx.perPage +
          "&page=" +
          ctx.currentPage +
          "&IdAlmacen="+IdAlmacen +
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
    toggleExpand(idProforma) {
    this.$set(this.expandedRows, idProforma, !this.expandedRows[idProforma]);
    },
    ventasVisibles(item) {
      const ventas = this.parseVentas(item.IdDocVenta);
      if (this.expandedRows[item.IdProforma] || ventas.length <= 2) {
        return ventas;
      }
      return ventas.slice(0, 2);
    },
    eliminarProf(data) {
      this.$swal({
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
                this.$refs.tableProformas.refresh();
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
#modalProformas .modal-dialog {
  max-width: 1700px !important;
  width: 95vw !important;
  margin: 20px auto;
}

#modalProformas .modal-content {
  max-height: 95vh;                /* 👈 subido de 90vh a 95vh para dar más espacio real */
  display: flex;
  flex-direction: column;
}

#modalProformas .modal-body {
  overflow-y: auto;
  flex: 1;
}

.tablaScroll {
  min-height: 600px;               /* 👈 fuerza que la tabla ocupe AL MENOS 700px */
  max-height: 600px;               /* 👈 y no crezca más allá de 700px */
  overflow-y: auto;
  overflow-x: hidden;
}

.tablaScroll table thead th {
  position: sticky;
  top: 0;
  z-index: 2;
  background-color: #fff;
}

.tablaScroll table.b-table.table-striped thead th {
  background-color: #f4f4f4;
}
</style>