<template>
  <div>
      <b-container class="">
        <b-row>
          <b-col class="text-center">
            <h3>REGISTRO DE ORDENES DE COMPRA</h3>
          </b-col>
        </b-row>
        <hr>
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
            <b-col md="8">

              <b-form-group horizontal label="Mostrar" class="mb-0">
                <b-form-select :options="pageOptions" v-model="perPage" @input="currentPage = 1"/>
              </b-form-group>
            </b-col>
            <b-col md="4">
              <p style="margin-top: 6px;">de <b class="text-success">{{ this.totalRows }}</b> Ordenes</p>
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

              <template slot="Ver" slot-scope="data">
                <div class="text-center">
                  <b-button variant="primary" @click="mostrarDetalles(data.item)"><i class="fas fa-eye"></i> Ver</b-button>
                </div> 
              </template>

              <template slot="Reimprimir" slot-scope="data">
                <div class="text-center">
                  <a class="btn btn-success" target="_blank" :href="'/api/index.php/reporte/ordencompra?IdOrdenCompra=' + data.item.IdOrdenCompra"><i class="fas fa-file-excel"></i> Excel</a>
                </div> 
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
        <b-modal size="lg" id="modalDetalle" ref="modalDetalle" :title="'Orden de Compra: '+infoOrden.Correlativo" ok-only ok-title="Cerrar">
          <b-row>
            <b-col md="6">
              <table>
                <tbody>
                  <tr>
                    <td class="celda" style="font-weight:bold; width:35% !important;">RUC</td>
                    <td class="celda" style="text-transform: uppercase">: {{ infoOrden.Ruc }}</td>
                  </tr>
                  <tr>
                    <td class="celda" style="font-weight:bold; width:35% !important;">Proveedor</td>
                    <td class="celda" style="text-transform: uppercase">: {{ infoOrden.Proveedor }}</td>
                  </tr>
                  <tr>
                    <td class="celda" style="font-weight:bold; width:35% !important;">Dirección</td>
                    <td class="celda" style="text-transform: uppercase">: {{ infoOrden.Direccion }}</td>
                  </tr>
                  <tr>
                    <td class="celda" style="font-weight:bold; width:35% !important;">Forma de Pago</td>
                    <td class="celda" style="text-transform: uppercase">: {{ infoOrden.EsCredito==1?'CRÉDITO':'CONTADO'}}</td>
                  </tr>
                  <tr>
                    <td class="celda" style="font-weight:bold; width:35% !important;">Metodo de Pago</td>
                    <td class="celda" style="text-transform: uppercase">: {{ infoOrden.MovimientoMetodoPago}}</td>
                  </tr>
                </tbody>
              </table>
            </b-col>

            <b-col md="6">
              <table>
                <tbody>
                  <tr>
                    <td class="celda" style="font-weight:bold; width:35% !important;">Fecha</td>
                    <td class="celda" style="text-transform: uppercase">: {{ infoOrden.FechaOrdenCompra }}</td>
                  </tr>
                  <tr>
                    <td class="celda" style="font-weight:bold; width:35% !important;">Fecha a Recoger</td>
                    <td class="celda" style="text-transform: uppercase">: {{ infoOrden.FechaRecojo }}</td>
                  </tr>
                  <tr>
                    <td class="celda" style="font-weight:bold; width:35% !important;">Registrado Por</td>
                    <td class="celda" style="text-transform: uppercase">: {{ infoOrden.UsuarioRegOrden }}</td>
                  </tr>
                </tbody>
              </table>
            </b-col>
          </b-row>
          <br>
          <b-row>
            <b-col md="12">
              <table class="table b-table table-sm table-striped">
                  <thead>
                      <tr>
                          <th class="text-center">Codigo</th>
                          <th class="text-center">Producto</th>
                          <th class="text-center">Cantidad</th>
                          <th class="text-center">Precio</th>
                          <th class="text-center">Total</th>
                      </tr>
                  </thead>
                  <tbody>
                      <tr v-for="(prod, i) in detalle" :key="i">
                          <td class="text-center">{{ prod.CodigoBarra }}</td>
                          <td class="text-center">{{ prod.Producto }}</td>
                          <td class="text-center">{{ prod.Cantidad }}</td>
                          <td class="text-center">{{ prod.Precio | moneda }}</td>
                          <td class="text-center">{{ (prod.Precio *  prod.Cantidad) | moneda }}</td>
                      </tr>
                  </tbody>
                  <tfoot>
                  <tr>
                    <td colspan="3"></td>
                    <td class="text-center"><b>TOTAL</b></td>
                    <td class="table-success text-center" style="vertical-align: middle !important;">S/ {{infoOrden.Total | moneda}} </td>
                  </tr>
                </tfoot>
              </table>
            </b-col>
          </b-row>
        </b-modal>
      </b-container>
  </div>
</template>

<script>
import config from "@/store/config.js";

export default {
  beforeMount() {
    this.$store.state.esOrdenCompra = true;
  },
  mounted() {
    this.axios.get(this.API + "ordencompra/count").then(response => {
      this.totalRows = response.data.total;
    });
  },
  data() {
    return {
      API: config.API,
      pageOptions: [5, 10, 15, 50, 100],
      filter: null,
      sortBy: "IdOrdenCompra",
      sortDesc: true,
      items: [],
      isBusy: false,
      fields: [
        { key: "IdOrdenCompra", label: "ID", sortable: true },
        { key: "Correlativo", sortable: true },
        { key: "FechaOrdenCompra", label: "Fecha", sortable: true },
        { key: "Ruc", sortable: true },
        { key: "Proveedor", sortable: true },
        { key: "Total", sortable: true },
        { key: "Ver", label: "Ver", class: "text-center" },
        { key: "Reimprimir", label: "Reporte", class: "text-center" }
      ],
      totalRows: 50,
      perPage: 10,
      currentPage: 1,
      detalle: {},
      infoOrden: {
        Anio: null,
        Correlativo: null,
        Direccion: null,
        EsCredito: null,
        FechaCredito: null,
        FechaOrdenCompra: null,
        FechaRecojo: null,
        IdOrdenCompra: null,
        IdProveedor: null,
        NumCuenta: null,
        Numero: null,
        Proveedor: null,
        Ruc: null,
        Telefono: null,
        UsuarioReg: null
      }
    };
  },
  methods: {
    rowClick(item, index) {
      this.infoOrden = item;
      //this.$refs.productoForm.producto.IdProducto = 'a'
      //console.log(this.$refs.productoForm.$refs.productoForm)
    },
    actualizarTabla() {
      this.$refs.tablaVentas.refresh();
    },
    nuevoProducto() {
      this.infoOrden = {};
      this.$refs.productoForm.show = true;
    },
    mostrarDetalles(data) {
      this.axios
        .get(this.API + "ordencompra/detalle", {
          params: {
            IdOrdenCompra: data.IdOrdenCompra
          }
        })
        .then(response => {
          this.detalle = response.data;
          this.infoOrden = data;
          this.$refs["modalDetalle"].show();
        })
        .catch(error => {
          console.log(error);
        });
    },
    myProvider(ctx) {
      // Here we don't set isBusy prop, so busy state will be handled by table itself
      // this.isBusy = true
      let url =
        this.API +
        "ordencompra?noFecha=1&limit=" +
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
  components: {}
};
</script>

<style>
#modalDetalle .modal-dialog {
  max-width: 65% !important;
}
</style>
