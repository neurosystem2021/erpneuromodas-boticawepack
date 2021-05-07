<template>
  <div>
      <b-container class="bv-example-row">
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
              <p style="margin-top: 6px;">de <b class="text-success">{{ this.totalRows }}</b> Artículos</p>
            </b-col>
            </b-row>
          </b-col>

          <b-table small
            ref="tablaRepartidores"
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
            <template slot="Anulado" slot-scope="data" >
              <b-badge v-if="items[data.index].Anulado==1" variant="danger">Si</b-badge>
              <b-badge v-if="items[data.index].Anulado==0" variant="success">No</b-badge>

            </template>
            </b-table>
            <b-row>
                <b-col md="6" class="my-1">
                    <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" />
                </b-col>
            </b-row>
        </b-row>
        <repartidor-form ref="repartidorForm" :repartidor-update="repartidorSelect" :cbGuardar="actualizarTabla"></repartidor-form>
        <br />
        <b-row align-h="center" class="text-center">
            <b-col cols="4">
              <b-button variant="success" size="lg" @click="nuevoRepartidor">Nuevo</b-button>
            </b-col>
        </b-row>
        <br /><br />
      </b-container>
  </div>
</template>

<script>
import config from '@/store/config.js'
import RepartidorForm from '@/components/RepartidorForm'

export default {
  mounted() {
      this.calcularCount()
  },
  data() {
    return {
      API: config.API,
      pageOptions: [ 5, 10, 15, 50, 100 ],
      filter: null,
      sortBy: 'IdRepartidor',
      sortDesc: false,
      repartidorSelect: {},
      items: [],
      isBusy: false,
      fields: [
            { key: 'IdRepartidor', label: 'Codigo', sortable: true },
            { key: 'Repartidor', sortable: true },
            { key: 'Anulado', label: 'Anulado', sortable: true}
      ],
      totalRows: 50,
      perPage: 10,
      currentPage: 1
    }
  },
  methods: {
      rowClick (item, index) {
        this.repartidorSelect = item
        this.$refs.repartidorForm.show = true;
      },
      calcularCount(){
        this.axios.get(this.API + 'repartidores/count').then((response) => {
        this.totalRows = response.data.total
        })
      },
      actualizarTabla() {
        this.calcularCount()
        this.$refs.tablaRepartidores.refresh()
      },
      nuevoRepartidor () {
          this.repartidorSelect = {}
          this.$refs.repartidorForm.show = true
      },
      myProvider (ctx) {
        // Here we don't set isBusy prop, so busy state will be handled by table itself
        // this.isBusy = true
        let url = this.API + 'repartidorescrud?limit='+ctx.perPage+'&page='+ctx.currentPage+'&filter='+ (ctx.filter||'')+'&sortBy='+ctx.sortBy
        url += '&sortDesc='+(ctx.sortDesc ? 1 : 0 )
        let promise = this.axios.get( url )

        return promise.then((result) => {
          this.items = result.data
          //this.totalRows = this.items.length
          // Here we could override the busy state, setting isBusy to false
          // this.isBusy = false
          return(this.items)
        }).catch(error => {
          // Here we could override the busy state, setting isBusy to false
          // this.isBusy = false
          // Returning an empty array, allows table to correctly handle busy state in case of error
          return []
        })
      }
  },
  components: {
      RepartidorForm
  }
}
</script>

<style>

</style>
