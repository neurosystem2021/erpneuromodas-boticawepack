<template>
  <div>
      <b-container class="bv-example-row px-5" fluid >
        <b-row>
          <!-- <b-col md="12" class="my-1">
            <b-form-select
              v-model="empresa"
              :options="$store.state.empresas"
              size="lg"
              style="font-weight: bold; width: 350px"
              class="form-control my-2"
              @change="cambiarEmpresa"
            ></b-form-select>
          </b-col> -->
             <b-col md="3" class="my-1">
            <b-form-group horizontal label="Buscar" class="mb-0">
             
              <b-input-group>
                <b-form-input v-model="filter" placeholder="Buscar" @input="currentPage = 1" />
                <b-input-group-append>
                  <b-btn :disabled="!filter" @click="filter = ''">Limpiar</b-btn>
                </b-input-group-append>
              </b-input-group>
            </b-form-group>
            <br />
          </b-col>
          <b-col md="3">
             <b-form-group horizontal label="Desde:" class="mb-0">
               <input type="date" v-model="fechaDesde" class="form-control">
              </b-form-group>
          </b-col>
          <b-col md="3">
             <b-form-group horizontal label="Hasta:" class="mb-0">
               <input type="date" v-model="fechaHasta" class="form-control">
              </b-form-group>
          </b-col>
          <b-col md="3" class="my-1">
            <b-row>
            <b-col md="8">

              <b-form-group horizontal label="Mostrar" class="mb-0">
                <b-form-select :options="pageOptions" v-model="perPage" @input="currentPage = 1"/>
              </b-form-group>
            </b-col>
            <b-col md="4">
              <p style="margin-top: 6px;">de <b class="text-success">{{ this.totalRows }}</b> Documentos</p>
            </b-col>
            </b-row>
          </b-col>

          <b-table small
            ref="tablaVentas"
            :filter="filter"
            :sort-by.sync="sortBy"
            :sort-desc.sync="sortDesc"
            striped
            hover
            @row-clicked="rowClick"
            id="my-table" 
            :busy.sync="isBusy" 
            :items="myProvider" 
            :fields="fields"
            :per-page="perPage"
            :current-page="currentPage">
          
              <template slot="Reimprimir" slot-scope="data">
                  <a class="btn btn-success" target="_blank"
                  
                   :href="`/imprimir/index.php?IdDocVenta=${data.item.idDocVenta}&preview=1&id_empresa=${empresa}`
                   
                   "
                   >{{data.item.TipoDoc}}</a>
              </template>
          </b-table>
            <b-row>
                <b-col md="6" class="my-1">
                    <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" />
                </b-col>
            </b-row>
        </b-row>
        <br />
        <br />
      </b-container>
  </div>
</template>

<script>
import config from "@/store/config.js";

export default {
  async beforeMount() {
    let empresa = sessionStorage.getItem("empresa");
    if (!empresa) {
      this.empresa = "imperiop2";
      sessionStorage.setItem("empresa", this.empresa);
    } else {
      this.empresa = empresa;
    }
    await this.axios
      .get(this.API + "ventas/count?id_empresa=" + this.empresa)
      .then(response => {
        this.totalRows = response.data.total;
      });
  },
  async mounted() {
    await this.axios
      .get(this.API + "ventas/count?id_empresa=" + this.empresa)
      .then(response => {
        this.totalRows = response.data.total;
      });
  },
  data() {
    return {
      empresa: "",
      API: config.API,
      pageOptions: [5, 10, 15, 50, 100],
      filter: null,
      sortBy: "idDocVenta",
      sortDesc: true,
      productoSelect: {},
      items: [],
      isBusy: false,
      fechaDesde: this.$moment().format("YYYY-MM-DD"),
      fechaHasta: this.$moment().format("YYYY-MM-DD"),
      fields: [
        { key: "idDocVenta", label: "ID", sortable: true },
        { key: "Correlativo", sortable: true },
        { key: "FechaDoc", sortable: true },
        { key: "DniRuc", sortable: true },
        { key: "TipoDoc", sortable: true },
        { key: "Cliente", sortable: true },
        { key: "Total", sortable: true },
        { key: "Reimprimir" }
      ],
      totalRows: 50,
      perPage: 10,
      currentPage: 1
    };
  },
  methods: {
    cambiarEmpresa() {
      setTimeout(() => {
        sessionStorage.setItem("empresa", this.empresa);
        window.location.reload();
        return false;
      }, 50);
    },
    rowClick(item, index) {
      this.productoSelect = item;
      //this.$refs.productoForm.producto.IdProducto = 'a'
      //console.log(this.$refs.productoForm.$refs.productoForm)
    },
    actualizarTabla() {
      this.$refs.tablaVentas.refresh();
    },
    nuevoProducto() {
      this.productoSelect = {};
      this.$refs.productoForm.show = true;
    },
    myProvider(ctx) {
      // Here we don't set isBusy prop, so busy state will be handled by table itself
      // this.isBusy = true
      let url = `${this.API}ventas?noFecha=0&limit=${ctx.perPage}&fechaDesde=${
        this.fechaDesde
      }&fechaHasta=${this.fechaHasta}&page=${
        ctx.currentPage
      }&filter=${ctx.filter || ""}&sortBy=${ctx.sortBy}&id_empresa=${
        this.empresa
      }&sortDesc=${ctx.sortDesc ? 1 : 0}`;
      // let urls =
      //   this.API +
      //   "ventas?noFecha=1&limit=" +
      //   ctx.perPage +
      //   "&page=" +
      //   ctx.currentPage +
      //   "&filter=" +
      //   (ctx.filter || "") +
      //   "&sortBy=" +
      //   ctx.sortBy;
      // url += "&sortDesc=" + (ctx.sortDesc ? 1 : 0);
      let promise = this.axios.get(url);

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
  },
  components: {},
  watch: {
    fechaDesde() {
      this.$refs.tablaVentas.refresh();
    },
    fechaHasta() {
      this.$refs.tablaVentas.refresh();
    }
  }
};
</script>

<style>
</style>
