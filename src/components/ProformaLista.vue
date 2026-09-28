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
                body-text-variant="dark">           
                    <b-form-input v-model="filter" placeholder="Buscar" @input="currentPage = 1" />  
                    <div class="table-responsive-proformas">
                        <b-table 
                            v-if="show"
                            id="table-proformas" 
                            striped hover 
                            :busy.sync="isBusy" 
                            :filter="filter"
                            :items="myProvider" 
                            :fields="fields"
                            :per-page="perPage"
                            :current-page="currentPage">

                          <template slot="update" slot-scope="data">
                            <b-button @click="selectProformas(data.item)" variant="success"><i class="fas fa-pencil-alt"></i></b-button>
                          </template>

                          <template slot="ver" slot-scope="data">
                            <b-button :href="'/nuevo/index.html#/reportes/proforma/' + data.item.IdProforma" variant="primary" target="_blank">Ver</b-button>
                          </template>

                           <template slot="verTicket" slot-scope="data">
                            <b-button :href="'/imprimir/proforma.php?IdProforma=' + data.item.IdProforma" variant="primary" target="_blank">Ver Ticket</b-button>
                          </template>

                        </b-table>
                    </div>
                    <b-row>
                    <b-col cols="12" md="6" class="my-1 text-center-mobile">
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
    }
  },
  data() {
    return {
      API: config.API,
      show: false,
      filter: "",
      items: [],
      isBusy: false,
      fields: [
        { key: "IdProforma", label: "Id" },
        { key: "Anio", label: "Año" },
        "Numero",
        "Cliente",
        { key: "DniRuc", label: "Documento" },
        "FechaReg",
        { key: "UsuarioReg", label: "Empleado" },
        "update",
        "ver",
        "verTicket"
      ],
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
    selectProformas(proforma) {
      this.axios
        .get(this.API + "proformas/detalle", {
          params: {
            idProforma: proforma.IdProforma
          }
        })
        .then(response => {
          response.data.map(item => {
            this.add([
              item.IdProducto,
              () => {
                this.obtenerStok({ IdAlmacen: this.idAlmacen || 1 });
                this.$emit("onLoadMovimiento", item.IdProducto);
              },
              { precio: item.Precio, cantidad: item.Cantidad, proforma: true }
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
    }
  }
};
</script>
<style>
#modalProformas .modal-dialog {
  max-width: 65%;
}

/* Contenedor con scroll horizontal para que la tabla nunca se aplaste */
.table-responsive-proformas {
  overflow-x: auto;
  -webkit-overflow-scrolling: touch; /* scroll suave en iOS */
  width: 100%;
}

#table-proformas {
  min-width: 700px; /* fuerza el scroll horizontal cuando la pantalla es más angosta */
  margin-bottom: 0;
}

@media (max-width: 767.98px) {
  #modalProformas .modal-dialog {
    max-width: 95vw;
    width: 95vw;
    margin: 10px auto;
  }

  .text-center-mobile {
    display: flex;
    justify-content: center;
  }
}
</style>