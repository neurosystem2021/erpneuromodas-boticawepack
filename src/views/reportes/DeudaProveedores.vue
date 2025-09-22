<template>
  <div>
    <h2 align-h="center" class="text-center" >Reporte Deuda de Proveedores</h2>
      <b-container class="bv-example-row">
        <b-row>
          <b-col md="4" class="my-1">
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
<!--             <b-form-group>
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
                </b-form-group> -->
          </b-col>
          <b-col md="4" class="my-1">
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
              </b-form-group>
            </h4>
          </b-col>

          <b-table
            ref="tabla"
            small
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
            :current-page="currentPage" @row-clicked="myRowClickHandler">
              <template slot="Saldo" slot-scope="data">
                {{data.item.Saldo?data.item.Saldo.toFixed(2):0}}
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
              <b-button
                variant="success"
                size="lg"
                :href="'/api/index.php/reporte/deudaproveedores'
                  + '?fechaDesde=' + rango.fechaDesde + ' 00:00:00'
                  + '&fechaHasta=' + rango.fechaHasta + ' 23:59:59'
                  + '&limit=' + perPage
                  + '&q=' + encodeURIComponent(filter)" 
                target="_blank">
                Exportar
              </b-button>
            </b-col>
        </b-row>
        <br /><br />
      </b-container>
      <b-modal id="modalDetalleDeuda" size="lg" modal-class="modal-xl" v-model="showLetras" ok-only ok-title="Cerrar" title="Detalle de Deuda">
        <detalle-deuda-proveedor ref="detDeuPro"></detalle-deuda-proveedor>
      </b-modal>
  </div>
</template>

<script>
import config from '@/store/config.js'
import DetalleDeudaProveedor from '@/components/DetalleDeudaProveedor.vue'
// import ProductoForm from '@/components/ProductoForm'

export default {
  mounted() {
    this.axios.get(this.API + 'proveedores/deuda/count').then((response) => {
      console.log(response)
      this.totalRows = response.data.total
    })
  },
  data() {
    return {
      API: config.API,
      pageOptions: [ 5, 10, 15, 50, 100 ],
      filter: '',
      sortBy: 'IdDocVenta',
      sortDesc: false,
      items: [],
      isBusy: false,
      fields: [
            { key: 'Ruc', label: 'RUC', sortable: true },
            { key: 'Proveedor', label: 'Cliente', sortable: true },
            { key: 'Correlativo', label: 'Serie', sortable: true },
            { key: 'Total', label: 'Total', sortable: true},
            { key: 'Aplicado', label: 'Aplicado', sortable: true},
            { key: 'Saldo', label: 'Saldo', sortable: true },
            { key: 'FechaDoc', label: 'Emision', sortable: true },
            { key: 'FechaCredito', label: 'Vencimiento', sortable: true },
            { key: 'Letras', label: 'Letras', sortable: true },
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
      showLetras: false,
    }
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

      console.log('Generando reporte desde', this.rango.fechaDesde, 'hasta', this.rango.fechaHasta);

      if (this.$refs.tabla) {
        this.$refs.tabla.refresh();
      }
    },
    calcularTotal() { 
      let sum = this.items.reduce((sum, item) => {
        return sum + parseFloat(item.Saldo);
      }, 0);
      return parseFloat(sum).toFixed(2);
    },
    focusAutocomplete (ref) {
      // this.$refs[ref].type = ""
      this.$refs[ref].handleDoubleClick()
    },
    setAlmacen (obj) {
       this.almacen = obj
       Vue.nextTick(() => this.$refs.tablaProductos.refresh())
    },
    myProvider(ctx) {
      let url = this.API + 'proveedores/deudas?' +
        'q=' + (ctx.filter || '') +
        '&limit=' + ctx.perPage +
        '&page=' + ctx.currentPage +
        '&fechaDesde=' + this.rango.fechaDesde + ' 00:00:00' +
        '&fechaHasta=' + this.rango.fechaHasta + ' 23:59:59';

      return this.axios.get(url).then((response) => {
        this.items = response.data;
        this.totalRows = this.items.length; // si tu API devuelve total, cámbialo aquí
        return this.items;
      }).catch(() => {
        return [];
      });
    },
    myRowClickHandler(record, index) {
      if(record.Letras!='Ninguna'){
        this.showLetras = true
        this.$refs.detDeuPro.cargarDeudasProveedor(record.Id,record.Total,record.Ruc,record.Proveedor,record.Correlativo);
      }
    }
  },
  components: {
      // ProductoForm
      DetalleDeudaProveedor
  }
}
</script>

<style>

</style>