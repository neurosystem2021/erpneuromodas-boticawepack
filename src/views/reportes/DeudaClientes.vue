<template>
  <div>
    <h2 align-h="center" class="text-center" >Reporte Deuda de Clientes</h2>
      <b-container fluid style="width: calc(100% - 50px)" class="">
        <b-row>
          <b-col md="4">
            <b-form-group>
                  <label>Buscar:</label>
              
              <b-input-group>
                <b-form-input v-model="filter" placeholder="Buscar" @input="currentPage = 1" />
                <b-input-group-append>
                  <b-btn :disabled="!filter" @click="filter = ''">Limpiar</b-btn>
                </b-input-group-append>
              </b-input-group>
            </b-form-group>
            <br />
          </b-col>
          <b-col md="4">
            <b-form-group>
                  <label>Almacen:</label>
                  <autocomplete
                      required="required"
                      ref="inputAlmacen"
                      :url="API + 'almacenes'"
                      anchor="Almacen"
                      initValue=""
                      label="writer"
                      placeholder=""
                      :on-select="setAlmacen" 
                      :onFocus="focusAutocomplete.bind(null, 'inputAlmacen')"
                      :classes="{input: 'form-control'}">
                  </autocomplete>
                </b-form-group>
          </b-col>
          <b-col md="4">
            <b-row>
            <b-col md="8">

              <b-form-group>
                  <label>Mostrar:</label>
                <b-form-select :options="pageOptions" v-model="perPage" @input="currentPage = 1"/>
              </b-form-group>
            </b-col>
            <b-col md="4">
             <!-- <p style="margin-top: 6px;">de <b class="text-success">{{ this.totalRows }}</b> Artículos</p>-->
            </b-col>
            </b-row>
          </b-col>
        <b-col md="3">
          <b-form-group>
            <label>Fecha Emisión Inicio:</label>
            <b-form-input
              v-model="rango.fechaDesde"
              type="date"
              step="1"
              placeholder="Fecha Inicio">
            </b-form-input>
          </b-form-group>
        </b-col>
        <b-col md="3">
          <b-form-group>
            <label>Fecha Emisión Fin:</label>
            <b-form-input
              v-model="rango.fechaHasta"
              type="date"
              step="1"
                placeholder="Fecha Inicio">
            </b-form-input>
          </b-form-group>
        </b-col>
        <b-col md="2" class="d-flex align-items-end mb-3">
          <b-button variant="primary" block @click="generarReporte">
            Generar Reporte
          </b-button>
        </b-col>
          <b-col md="4">
            <h4>
              <b-form-group class="mt-4">
              <span > Total: {{ calcularTotal() }} </span>
              <b-button variant="danger" @click="imprimir()" class="mx-5">Imprimir <i class="fa fa-file-pdf"></i> </b-button>
              </b-form-group>
            </h4>
          </b-col>
          <b-table small
            ref="tablaProductos"
            :filter="filter"
            :sort-by.sync="sortBy"
            :sort-desc.sync="sortDesc"
            striped
            hover
            id="my-table" 
            :busy.sync="isBusy" 
            :items="myProvider" 
            :fields="fields"
            :per-page="perPage"
            :current-page="currentPage"
            @row-clicked="myRowClickHandler">
            <template slot="Saldo" slot-scope="data">
                {{ data.item.Saldo | redondear(2) }}
            </template>
          </b-table>
            <b-row>
                <b-col md="6" class="my-1">
                    <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" />
                </b-col>
            </b-row>
        </b-row>
        <b-row align-h="center" class="text-center">
            <b-col cols="4">
              <b-button variant="success" size="lg" :href="'/api/index.php/reporte/deudaclientes2?idalmacen=' + almacen.IdAlmacen+'&limit='+perPage+'&fechaDesde='+rango.fechaDesde+'&fechaHasta='+rango.fechaHasta" target="_blank">Exportar</b-button>
            </b-col>
        </b-row>
        <br /><br />
      </b-container>
      <b-modal id="modalDetalleDeuda" size="lg" modal-class="modal-xl" v-model="showLetras" ok-only ok-title="Cerrar" title="Detalle de Deuda">
           <detalle-deuda-cliente ref="detDeuCli"></detalle-deuda-cliente>
      </b-modal>
  </div>
</template>

<script>
import config from "@/store/config.js";
import DetalleDeudaCliente from "@/components/DetalleDeudaCliente.vue";
// import ProductoForm from '@/components/ProductoForm'

export default {
  mounted() {
    this.axios
      .get(
        this.API + "clientes/deuda/count?idalmacen=" + this.almacen.IdAlmacen
      )
      .then(response => {
        console.log(response);
        this.totalRows = response.data.total;
      });
  },
  data() {
    return {
      API: config.API,
      pageOptions: [50, 100, 150, 200, 300],
      filter: "",
      sortBy: "IdDocVenta",
      sortDesc: false,
      items: [],
      isBusy: false,
      fields: [
        { key: "DniRuc", label: "DNI/RUC", sortable: true },
        { key: "Cliente", label: "Cliente", sortable: true },
        { key: "Correlativo", label: "Serie", sortable: true },
        { key: "Total", label: "Total", sortable: true },
        { key: "Aplicado", label: "Aplicado", sortable: true },
        { key: "NotaCreApli", label: "Apli. Nota Cred.", sortable: true },
        { key: "Saldo", label: "Saldo", sortable: true },
        { key: "FechaDoc", label: "Emision", sortable: true },
        { key: "FechaCredito", label: "Vencimiento", sortable: true },
        { key: "Letras", label: "Letras", sortable: true }
      ],
      totalRows: 50,
      perPage: 50,
      currentPage: 1,
      almacen: {
        IdAlmacen: 1
      },
      rango: {
        fechaDesde: this.$moment().format("YYYY-MM-01"),
        fechaHasta: this.$moment().format("YYYY-MM-DD")
      },
      mostrarTabla: false,
      showLetras: false,
      urlImg: "/resources/images/logo.png"
    };
  },
  methods: {
    generarReporte() {
    if (!this.rango.fechaDesde || !this.rango.fechaHasta) {
      this.$bvToast.toast('Debes seleccionar ambas fechas.', {
        title: 'Error',
        variant: 'danger',
        solid: true
      });
      return;
    }

    this.mostrarTabla = true;

    this.$nextTick(() => {
      if (this.$refs.tablaProductos && typeof this.$refs.tablaProductos.refresh === 'function') {
        this.$refs.tablaProductos.refresh();
      }
    });

    console.log('Generando reporte desde', this.rango.fechaDesde, 'hasta', this.rango.fechaHasta);
  },
    focusAutocomplete(ref) {
      // this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick();
    },
    calcularTotal() { 
      let sum = this.items.reduce((sum, item) => {
        return sum + parseFloat(item.Saldo);
      }, 0);
      return parseFloat(sum).toFixed(2);
    },
    async imprimir() {
      // http://localhost/api/index.php/cliente/imprimir/deudas_cliente?idalmacen=1&q=&limit=50&page=1&fechaDesde=2024-01-01&fechaHasta=2024-09-20
      let res = await this.axios(
        `${this.API}cliente/imprimir/deudas_cliente?idalmacen=${
          this.almacen.IdAlmacen
        }&limit=perPage&fechaDesde=${this.rango.fechaDesde}&fechaHasta=${
          this.rango.fechaHasta
        }"&q=${this.filter}`
      ).catch(e => console.log(e));
      if (res.data) {
        console.log(res.data.url);
        window.open(res.data.url, "_blank");
      }
    },
    setAlmacen(obj) {
      this.almacen = obj;
      Vue.nextTick(() => this.$refs.tablaProductos.refresh());
    },
    myProvider(ctx) {
      // Here we don't set isBusy prop, so busy state will be handled by table itself
      // this.isBusy = true
      // console.log(ctx.filter)
      // let url = this.API + 'productos/stock?idAlmacen='+this.almacen.IdAlmacen+'&limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter[producto]='+ (ctx.filter||'')+'&filter[minimo]=1&sortBy='+ctx.sortBy
      // url += '&sortDesc='+(ctx.sortDesc ? 1 : 0 )
      let promise = this.axios.get(
        this.API +
          "cliente/deudas?idalmacen=" +
          this.almacen.IdAlmacen +
          "&q=" +
          ctx.filter +
          "&limit=" +
          ctx.perPage +
          "&page=" +
          ctx.currentPage +
          "&fechaDesde=" +
          this.rango.fechaDesde +
          "&fechaHasta=" +
          this.rango.fechaHasta
      );

      return promise
        .then(response => {
          console.log(response);
          this.items = response.data;
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
    myRowClickHandler(record, index) {
      if (record.Letras != "Ninguna") {
        this.showLetras = true;
        this.$refs.detDeuCli.cargarDeudasCliente(
          record.IdDocVenta,
          record.Total,
          record.DniRuc,
          record.Cliente,
          record.Correlativo
        );
      }
    }
  },
  components: {
    // ProductoForm
    DetalleDeudaCliente
  }
};
</script>

<style>
</style>
