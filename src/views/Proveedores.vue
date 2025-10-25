<template>
  <div>
      <b-container class="bv-example-row">
        <b-row>
          <b-col class="text-center">
            <h1>LISTA DE PROVEEDORES</h1>
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
              <p style="margin-top: 6px;">de <b class="text-success">{{ this.totalRows }}</b> Proveedores</p>
            </b-col>
            </b-row>
          </b-col>

          <b-table small
            ref="tablaClientes"
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
            <b-row>
                <b-col md="6" class="my-1">
                    <b-pagination :total-rows="totalRows" :per-page="perPage" v-model="currentPage" class="my-0" />
                </b-col>
            </b-row>
        </b-row>
        <proveedor-form-edit ref="proveedorForm" :proveedorSelect="proveedorSelect" @onAddProveedor="actualizarTabla"></proveedor-form-edit>
        <br />
        <b-row align-h="center" class="text-center">
            <b-col cols="4">
              <b-button variant="success" size="lg" @click="nuevoProveedor">Nuevo</b-button>
            </b-col>
        </b-row>
        <br /><br />
      </b-container>
  </div>
</template>

<script>
import config from '@/store/config.js'
import ProveedorFormEdit from '@/components/ProveedorFormEdit'

export default {
  mounted() {
    this.axios.get(this.API + 'proveedores/count').then((response) => {
      this.totalRows = response.data.total
    })
  },
  data() {
    return {
      API: config.API,
      pageOptions: [ 5, 10, 15, 50, 100 ],
      filter: null,
      sortBy: 'IdProveedor',
      sortDesc: false,
      proveedorSelect: {},
      items: [],
      isBusy: false,
      fields: [
            { key: 'IdProveedor',label:'ID', sortable: true },
            { key: 'Proveedor', sortable: true },
            { key: 'Ruc', sortable: true },
            { key: 'Direccion', sortable: true },
            { key: 'Telefono', sortable: true },
      ],
      totalRows: 50,
      perPage: 10,
      currentPage: 1
    }
  },
  methods: {
      rowClick (item, index) {
        this.proveedorSelect = item
        this.$refs.proveedorForm.show = true;
      },
      actualizarTabla() {
        this.$refs.tablaClientes.refresh()
      },
      nuevoProveedor () {
          this.proveedorSelect = {}
          this.$refs.proveedorForm.show = true
      },
      myProvider (ctx) {
        let url = this.API + 'proveedores?limit='+ctx.perPage+'&page='+ctx.currentPage+'&q='+ (ctx.filter||'')+'&sortBy='+ctx.sortBy
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
      ProveedorFormEdit
  }
}
</script>

<style>

</style>
