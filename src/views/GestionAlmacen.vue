<template>
  <div>
      <b-container class="bv-example-row">
        <h3 align-h="center" class="text-center" >Gestión de Almacenes</h3>
        <b-row>
          <b-table small
            ref="tablaAlmacenes"
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
            :current-page="currentPage"></b-table>
        </b-row>
        <almacen-form ref="almacenForm" :almacen-update="almacenSelect" :cbGuardar="actualizarTabla"></almacen-form>
        <br />
        <b-row align-h="center" class="text-center">
            <b-col cols="4">
              <b-button variant="success" size="lg" @click="nuevoAlmacen">Nuevo</b-button>
            </b-col>
        </b-row>
        <br /><br />
      </b-container>
  </div>
</template>

<script>
import config from '@/store/config.js'
import AlmacenForm from '@/components/AlmacenForm'

export default {
  mounted() {
    // this.axios.get(this.API + 'productos/count').then((response) => {
    //   this.totalRows = response.data.total
    // })
  },
  data() {
    return {
      API: config.API,
      pageOptions: [ 5, 10, 15, 50, 100 ],
      filter: null,
      sortBy: 'IdAlmacen',
      sortDesc: false,
      almacenSelect: {},
      items: [],
      isBusy: false,
      fields: [
            { key: 'IdAlmacen', label: 'Id', sortable: true },
            { key: 'Almacen', label: 'Nombre', sortable: true },
            { key: 'Anulado', label: 'Estado', sortable: true},
            // { key: 'Direccion', label: 'Direccion', sortable: true },
      ],
      totalRows: 10,
      perPage: 1,
      currentPage: 1
    }
  },
  methods: {
      rowClick (item, index) {
        this.almacenSelect = item
        this.$refs.almacenForm.show = true;
      },
      actualizarTabla() {
        this.$refs.tablaAlmacenes.refresh()
      },
      nuevoAlmacen () {
          this.almacenSelect = {}
          this.$refs.almacenForm.show = true

      },
      myProvider (ctx) {
        let url = this.API + "gestion/almacenes"
        let promise = this.axios.get( url )

        return promise.then((result) => {
          this.items = result.data
          return(this.items)
        }).catch(error => {
          return []
        })
      }
  },
  components: {
      AlmacenForm
  }
}
</script>

<style>

</style>
