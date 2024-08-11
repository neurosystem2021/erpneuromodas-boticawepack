<template>
  <div>
    <h2 align-h="center" class="text-center" >Reporte Deuda de Clientes</h2>
      <b-container fluid style="width: calc(100% - 50px)" class="bv-example-row">
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
          <b-col md="4">
            <b-form-group>
              <label>Fecha Emisión Inicio:</label>
              <b-form-input
                v-model="rango.fechaDesde"
                type="date"
                step="1"
                @input="val => $refs.tablaProductos.refresh()"
                placeholder="Fecha Inicio">
              </b-form-input>
            </b-form-group>
          </b-col>
          <b-col md="4">
            <b-form-group>
              <label>Fecha Emisión Fin:</label>
              <b-form-input
                v-model="rango.fechaHasta"
                type="date"
                step="1"
                @input="val =>$refs.tablaProductos.refresh() "
                placeholder="Fecha Inicio">
              </b-form-input>
            </b-form-group>
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
        fechaDesde: this.$moment().format("YYYY-01-01"),
        fechaHasta: this.$moment().format("YYYY-MM-DD")
      },
      showLetras: false,
      urlImg: "http://boticav1.local/resources/images/logo-ticket.png"
    };
  },
  methods: {
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
    imprimir() {
      let html = `
      <table>
        <thead>
          <tr>
             <th>DNI/RUC</th>
             <th>Cliente</th>
             <th>Serie</th>
             <th>Total</th>
             <th>Aplicado</th>
             <th>Apli. Nota Cred</th>
             <th>Saldo</th>
             <th>Emision</th>
             <th>Vencimiento</th>
             <th>Letras</th>
          </tr>
        </thead>
        <tbody>
        `;

      this.items.forEach(element => {
        html += `<tr>
          <td>${element.DniRuc}</td>
          <td>${element.Cliente}</td>
          <td>${element.Correlativo}</td>
          <td>${element.Total}</td>
          <td>${element.Aplicado}</td>
          <td>${element.NotaCreApli}</td>
          <td>${element.Saldo}</td>
          <td>${element.FechaDoc}</td>
          <td>${element.FechaCredito}</td>
          <td>${element.Letras}</td>
        </tr>`;
      });
      html += `</tbody></table>`;

      const printWindow = window.open("", "_blank");
      if (!printWindow) {
        alert(
          "No se pudo abrir la ventana de impresión. Por favor, habilita las ventanas emergentes."
        );
        return;
      }

      printWindow.document.write(`
        <html>
          <head>
            <style>
              body { 
                font-family: Arial, sans-serif; 
                padding: 20px 20px;
              }
              .table-success {
                background: #c3e6cb !important;
              }
              .text-center {
                text-align: center;
              }
              .imgPrint{
                max-height: 100px;
                margin: 10px 10px;
              }
              .mt-2{
                margin: 4% 0 !important;
              }
            </style>
          </head>
          <body>
          
            <img id="printImage" src="${
              this.urlImg
            }" onload="window.print(); window.close();" class="imgPrint">
            <h1>Reporte Deuda de Clientes</h1>
            <h2> Deuda Total: ${this.calcularTotal()} </h2>
            ${html}
          </body>
        </html>
      `);
      printWindow.document.close();
      printWindow.focus();
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
