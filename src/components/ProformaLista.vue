<template>
    <div>
        <b-button block @click="show=true" class="btnAction" variant="warning" size="sm" style="font-weight: bold; font-size: 1.2rem" >Ver Proformas</b-button>

        <b-form ref="formProformaModal" >    

            <b-modal id="modalProformas" 
                title="Buscar Proforma"
                size="lg" v-model="show"
                header-bg-variant="dark"
                headerTextVariant="light"
                body-bg-variant="light"
                body-text-variant="dark"
                no-enforce-focus>
                <b-tabs pills card>
                  <b-tab title="Proformas" active>
                    <b-row class="align-items-center mb-2">
                      <b-col md="8">
                        <b-form-input v-model="filter" placeholder="Buscar" @input="currentPage = 1" />
                      </b-col>
                      <b-col md="4">
                        <b-form-select v-model="perPage" :options="opcionesPerPage" @change="currentPage = 1" />
                      </b-col>
                    </b-row>
                    <div class="table-responsive-proformas">
                    <b-table
                        v-if="show"
                        id="table-proformas"
                        ref="tableProformas"
                        striped hover
                        :busy.sync="isBusy"
                        :filter="filter"
                        :items="myProviderPendientes"
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
                <div slot="modal-footer" >
                    <!--<b-btn size="sm" variant="success" type="submit">
                        Guardar
                    </b-btn>-->
                    <b-btn size="sm" variant="secondary" @click="show=false">
                        Cerrar
                    </b-btn>
                </div>
                  </b-tab>
                  <b-tab title="Facturadas">
                    <b-row class="align-items-center mb-2">
                      <b-col md="8">
                        <b-form-input v-model="filter" placeholder="Buscar" @input="currentPage = 1" />
                      </b-col>
                      <b-col md="4">
                        <b-form-select v-model="perPage" :options="opcionesPerPage" @change="currentPage = 1" />
                      </b-col>
                    </b-row>
                    <div class="table-responsive-proformas">
                    <b-table v-if="show" id="table-proformas" ref="tableProformas" striped hover :busy.sync="isBusy"
                      :filter="filter" :items="myProviderFacturadas" :fields="fieldsFacturadas" :per-page="perPage"
                      :current-page="currentPage">

                      <template slot="EsFavoritoFlag" slot-scope="data">
                        <i @click="cambiarFavorito(data.item, 0)" class="fa fa-star" style="color: #F57F17; cursor: pointer;"
                          aria-hidden="true" v-if="data.item.EsFavoritoFlag"></i>
                        <i @click="cambiarFavorito(data.item, 1)" class="far fa-star" style="cursor: pointer;" aria-hidden="true"
                          v-else></i>
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

                      <template slot="verTicketFactura" slot-scope="data">
                        <b-button
                          v-if="data.item.IdDocVenta"
                          :href="'/imprimir/index.php?IdDocVenta=' + data.item.IdDocVenta"
                          variant="info"
                          target="_blank">
                          Ver Comprobante
                        </b-button>
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

                  <b-tab title="Anulados">
                    <b-row class="align-items-center mb-2">
                      <b-col md="8">
                        <b-form-input v-model="filter" placeholder="Buscar" @input="currentPage = 1" />
                      </b-col>
                      <b-col md="4">
                        <b-form-select v-model="perPage" :options="opcionesPerPage" @change="currentPage = 1" />
                      </b-col>
                    </b-row>
                    <div class="table-responsive-proformas">
                    <b-table v-if="show" id="table-proformas" ref="tableProformas" striped hover :busy.sync="isBusy"
                      :filter="filter" :items="myProviderAnuladas" :fields="fieldsAnuladas" :per-page="perPage"
                      :current-page="currentPage">

                      <template slot="EsFavoritoFlag" slot-scope="data">
                        <i @click="cambiarFavorito(data.item, 0)" class="fa fa-star" style="color: #F57F17; cursor: pointer;"
                          aria-hidden="true" v-if="data.item.EsFavoritoFlag"></i>
                        <i @click="cambiarFavorito(data.item, 1)" class="far fa-star" style="cursor: pointer;" aria-hidden="true"
                          v-else></i>
                      </template>

                      <template slot="update" slot-scope="data">
                        <b-button @click="confirmarPasswordEditarAnulada(data.item)" variant="success"><i
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

                  <b-tab title="Proformas Favoritas">
                    <b-input-group prepend="Buscar..." class="mt-2">
                      <input class="form-control" v-model="search" />
                      </b-input-group>
                    <b-form-group>
                    </b-form-group>
                    <div class="table-responsive-proformas">
                     <b-table
                        v-if="show"
                        id="table-proformas"
                        ref="tableProformasFavoritas"
                       :filter="filter"
                        :items="filteredItems"
                        :fields="fieldsFavoritas"
                        :per-page="5"
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
                    </div>
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
      items: [],
      isBusy: false,
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
        "Anular"
      ],
      opcionesPerPage: [
        { value: 10, text: "10 por página" },
        { value: 20, text: "20 por página" },
        { value: 40, text: "40 por página" },
        { value: 50, text: "50 por página" }
      ],
      fieldsFacturadas: [
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
        "verTicketFactura"
      ],
      fieldsAnuladas: [
        { key: "IdProforma", label: "Id" },
        { key: "Anio", label: "Año" },
        "Numero",
        "Cliente",
        { key: "DniRuc", label: "Documento" },
        "FechaReg",
        { key: "UsuarioReg", label: "Empleado" },
        { key: "Total", label: "Total" },
        "update"
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
        "verTicket"
        // "Anular"
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
    async selectProformas(proforma) {
      this.$emit("guardarDatosProforma", proforma.IdProforma);
      try {
        const response = await this.axios.get(this.API + "proformas/detalle", {
          params: { idProforma: proforma.IdProforma }
        });

        const detalles = response.data;
        if (!detalles || !detalles.length) return;

        const idsProductos = detalles.map(d => d.IdProducto);
        const stockResponse = await this.axios.get(this.API + "productos/puntoventa/stock", {
          params: {
            idProductos: idsProductos,
            idAlmacen: this.idAlmacen || 1,
            noLimit: 1
          }
        });

        const stockMap = {};
        (stockResponse.data || []).forEach(p => {
          stockMap[p.IdProducto] = p;
        });

        const productosEnriquecidos = detalles.map(item => {
          const stockInfo = stockMap[item.IdProducto] || {};
          let pres = [];
          if (stockInfo.PreciosPorProducto) {
            try {
              pres = JSON.parse(stockInfo.PreciosPorProducto).filter(v => v.cantidad > 0);
            } catch (e) { pres = []; }
          }
          let CantidadPresentacion = null;
          let bloquearInputPrecio = false;
          let presentacion = null;

          if (item.Presentacion) {
            try {
              let presObj = typeof item.Presentacion === 'string'
                ? JSON.parse(item.Presentacion)
                : item.Presentacion;
              CantidadPresentacion = presObj.cantidad ? parseFloat(presObj.cantidad) : null;
              bloquearInputPrecio = true;
              presentacion = item.Presentacion; // el string JSON original
            } catch (e) {
              CantidadPresentacion = null;
              bloquearInputPrecio = false;
              presentacion = null;
            }
          }

          return {
            ...stockInfo,
            ...item,
            Descripcion: item.Descripcion || item.ProductoPresentacion || "UNIDAD",
            ProductoPresentacion: item.Descripcion || item.ProductoPresentacion || "UNIDAD",
            cantidad: item.Cantidad,
            precio: item.Precio,
            descuento: item.Descuento || 0,
            stock: stockInfo.stock || 0,
            PreciosPorProducto: pres,
            tienePresentacion: pres.length > 0,
            CantidadPresentacion,
            bloquearInputPrecio,
            presentacion,
          };
        });

        proforma.productos = productosEnriquecidos;

        this.filter = "";
        this.show = false;

          // this.$emit('onSelectProforma', proforma.IdCliente, proforma)
          this.$emit("onSelectProforma", proforma);
        } catch (err) {
          console.error("Error cargando proforma:", err);
          this.$toasted.error("Error al cargar la proforma");
        }
    },
    myProviderPendientes(ctx) {
      return this.fetchProformas(ctx, 'pendientes');
    },
    myProviderFacturadas(ctx) {
      return this.fetchProformas(ctx, 'facturadas');
    },
    myProviderAnuladas(ctx) {
      return this.fetchProformas(ctx, 'anuladas');
    },
    fetchProformas(ctx, estado) {
      let IdAlmacen = sessionStorage.getItem("IdAlmacen");
      let promise = this.axios.get(this.API + "proformas", {
        params: {
          limit: ctx.perPage,
          page: ctx.currentPage,
          IdAlmacen,
          filter: ctx.filter,
          estado
        }
      });

      return promise
        .then(result => {
          this.items = result.data;
          this.axios
            .get(this.API + "proformas/count", { params: { IdAlmacen, estado } })
            .then(r => (this.totalRows = r.data.total));
          return this.items;
        })
        .catch(() => []);
      },
      async confirmarPasswordEditarAnulada(item) {
        await this.$swal.fire({
          icon: "info",
          title: "Escriba la contraseña del administrador",
          html:
            '<p style="margin-bottom:10px">Esta proforma está <strong>ANULADA</strong>. ' +
            'Ingrese usuario y contraseña de administrador para poder editarla.</p>' +
            '<input id="swal-input1" class="swal2-input" placeholder="Usuario">' +
            '<input id="swal-input2" type="password" class="swal2-input" placeholder="Contraseña">',
          focusConfirm: false,
          showCloseButton: true,
          showCancelButton: true,
          confirmButtonText: "Verificar",
          cancelButtonText: "Cancelar",
          preConfirm: () => {
            const input1 = document.getElementById("swal-input1").value;
            const input2 = document.getElementById("swal-input2").value;
            if (!input1 || !input2) {
              this.$swal.showValidationMessage("Por favor, complete ambos campos");
            }
            return { usuario: input1, clave: input2 };
          }
        }).then(async result => {
          if (!result.isConfirmed) return;

          try {
            const response = await this.axios.post(this.API + "login", {
              user: result.value.usuario,
              password: result.value.clave
            });

            if (response.data) {
              if (response.data.IdUsuarioPerfil == 1) {
                this.selectProformas(item);
              } else {
                this.$swal.fire({
                  icon: "error",
                  title: "Aviso",
                  text: "El usuario no cuenta con los privilegios de administrador."
                });
              }
            } else {
              this.$swal.fire(
                "Contraseña incorrecta",
                "La contraseña que ingresó es incorrecta.",
                "error"
              );
            }
          } catch (error) {
            console.log(error);
          }
        }).catch(e => {
          console.log(e);
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
                if (this.$refs.tableProformas) {
                  this.$refs.tableProformas.refresh();
                  this.$refs.tableProformasFavoritas.refresh();
                }
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
  max-width: 95vw !important;
  width: 1400px !important;
}

/* El modal-body ya NO controla el scroll, solo limita el alto general */
#modalProformas .modal-body {
  max-height: 75vh;
  overflow: hidden; /* importante: quitamos el scroll de aquí */
  display: flex;
  flex-direction: column;
}

/* Aquí es donde vive el scroll de verdad */
.table-responsive-proformas {
  overflow-y: auto;
  overflow-x: auto;
  width: 100%;
  max-height: 55vh; /* ajusta este valor a tu gusto */
  position: relative;
}

/* Encabezados fijos (sticky) dentro del contenedor con scroll */
.table-responsive-proformas table thead th {
  position: sticky;
  top: 0;
  z-index: 2;
  background-color: #fff; /* necesario, si no el texto de abajo se transparenta */
}

/* Si usas striped/hover de bootstrap-vue, asegúrate que el thead
   mantenga su propio fondo sólido para que no se vea el contenido detrás */
.table-responsive-proformas table thead tr th {
  background-clip: padding-box;
}
</style>
