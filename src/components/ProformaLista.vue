<template>
    <div>
        <b-button @click="show=true" variant="primary" size="sm" class="btnAction">Ver Proformas</b-button>
        
        <b-form ref="formProformaModal" >    
            

            <b-modal id="modalProformas" 
                title="Buscar Proforma"
                class="mdlProforma"
                v-model="show"
                size="xl"
                header-bg-variant="dark"
                headerTextVariant="light"
                body-bg-variant="light"
                body-text-variant="dark">     
                <b-tabs pills card>
                  <b-tab title="Proformas" active>
                    <b-form-input v-model="filter" placeholder="Buscar" @input="currentPage = 1" />  
                    <div class="table-responsive ">
                      <b-table 
                          v-if="show"
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
                <div slot="modal-footer" >
                    <!--<b-btn size="sm" variant="success" type="submit">
                        Guardar
                    </b-btn>-->
                    <b-btn size="sm" variant="secondary" @click="show=false">
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
                     <b-table 
                        v-if="show"
                        id="table-proformas" 
                        ref="tableProformas"
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
  max-width: 85% !important;
  margin: auto;
}
.table-responsive-proforma {
  width: 100%;
  overflow-x: auto !important;
}

.table-inner-scroll {
  min-width: 900px; /* Asegura que la tabla sea más grande */
  display: inline-block; /* Permite que overflow-x funcione */
}
.table-responsive td,
.table-responsive th,
#table-proformas td,
#table-proformas th {
  white-space: nowrap;
}
@media only screen and (max-width: 850px) {
  #modalProformas .modal-dialog {
    min-width: 100% !important;
  }

  .table-responsive {
    /* width: 300px !important; */
    overflow-x: auto !important;
    /* -webkit-overflow-scrolling: touch; */
  }
}
.mdlProforma{
  width: 80%;
}
</style>
