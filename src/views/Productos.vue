<template>
  <div>
      <b-container fluid style="width: calc(80%) !important;" class="bv-example-row">
        <b-row>
          <b-col md="6" class="my-1">
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
          <b-col md="6" class="my-1">
            <b-row>
              <b-col md="4">

                <b-form-group horizontal label="Mostrar" class="mb-0">
                  <b-form-select :options="pageOptions" v-model="perPage" @input="currentPage = 1"/>
                </b-form-group>
              </b-col>
              <b-col md="3">
                <p style="margin-top: 6px;">de <b class="text-success">{{ this.totalRows }}</b> Artículos</p>
              </b-col>
              <b-col md="3">
                Tipo de Cambio
                <input type="number" step="0.001" v-model="tipocambio" class="form-control">

              </b-col>
            <b-col md="2">
                <codigo-multiple></codigo-multiple>
              </b-col>
            </b-row>
          </b-col>

          <b-table small
            ref="tablaProductos"
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
            <template slot="PosOferta" slot-scope="data">
              <h4><b-badge variant="success" style="width: 100px;" v-if="data.item.PosOferta < 10000">{{ data.item.PosOferta }}</b-badge></h4>
            </template>

            <template slot="MostrarApp" slot-scope="data">
              <h4><b-badge variant="info" style="width: 50px;" v-if="data.item.MostrarApp == 1">SI</b-badge></h4>
            </template>

            <template slot="PrecioCostoDolar" slot-scope="data">
              $ {{ parseFloat(data.item.PrecioCosto*tipocambio).toFixed(2) }}
            </template>
          
            <template slot="PrecioContadoDolar" slot-scope="data">
              $ {{ parseFloat(data.item.PrecioContado*tipocambio).toFixed(2) }}
            </template>
          
          </b-table>
            <b-row>
                <b-col md="6" class="my-1">
                    <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" />
                </b-col>
            </b-row>
        </b-row>
        <producto-form ref="productoForm" :producto-update="productoSelect" :cbGuardar="actualizarTabla"></producto-form>
        <br />
        <b-row align-h="center" class="text-center">
            <b-col cols="4">
              <b-button variant="success" size="lg" @click="nuevoProducto">Nuevo</b-button>
            </b-col>
        </b-row>
        <br /><br />
      </b-container>
  </div>
</template>

<script>
import config from "@/store/config.js";
import ProductoForm from "@/components/ProductoForm";
import CodigoMultiple from "@/components/CodigoMultiple";

export default {
  mounted() {
    this.obteneTipoCambio();
    this.axios.get(this.API + "productos/count").then(response => {
      this.totalRows = response.data.total;
    });
  },
  data() {
    return {
      tipocambio: 0,
      API: config.API,
      pageOptions: [5, 10, 15, 50, 100],
      filter: null,
      sortBy: "IdProducto",
      sortDesc: false,
      productoSelect: {},
      items: [],
      isBusy: false,
      fields: [
        { key: "CodigoBarra", label: "Codigo", sortable: true },
        { key: "Producto", sortable: true },
        { key: "ProductoCategoria", label: "Categoría", sortable: true },
        { key: "ProductoMarca", label: "Marca", sortable: true },
        { key: "ProductoMedicion", label: "Medición", sortable: true },
        { key: "ProductoPresentacion", label: "Presentación", sortable: true },
        { key: "PosOferta", label: "Orden Oferta", sortable: true },
        { key: "PrecioCosto", label: "P. Compra (S/.)", sortable: true },
        { key: "PrecioContado", label: "P. Venta (S/.)", sortable: true },
        { key: "PrecioCostoDolar", label: "P. Compra ($)", sortable: true },
        { key: "PrecioContadoDolar", label: "P. Venta ($)", sortable: true },
        { key: "PosOferta", label: "Orden Oferta", sortable: true },
        { key: "MostrarApp", label: "En App", sortable: true }
      ],
      totalRows: 50,
      perPage: 10,
      currentPage: 1
    };
  },
  methods: {
    async obteneTipoCambio() {
      await this.axios(`https://deperu.com/api/rest/cotizaciondolar.json`).then(
        res => {
          if (res.data) {
            this.tipocambio = res.data.Cotizacion[0].Venta;
          }
        }
      );
    },
    rowClick(item, index) {
      this.productoSelect = item;
      this.$refs.productoForm.show = true;
      //this.$refs.productoForm.producto.IdProducto = 'a'
      //console.log(this.$refs.productoForm.$refs.productoForm)
    },
    actualizarTabla() {
      this.$refs.tablaProductos.refresh();
    },
    nuevoProducto() {
      this.productoSelect = {};
      this.$refs.productoForm.show = true;
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
          this.items = result.data;

          this.items.map(r => {
            if (r.Anulado === 1) {
              r._rowVariant = "danger";
            }
          });
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
  components: {
    ProductoForm,
    CodigoMultiple
  }
};
</script>

<style>
</style>
