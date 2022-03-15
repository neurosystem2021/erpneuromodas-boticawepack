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
              <p style="margin-top: 6px;">de <b class="text-success">{{ this.totalRows }}</b> Clientes</p>
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
        <cliente-form ref="clienteForm" :clienteSelect="clienteSelect" @onAddCliente="actualizarTabla"></cliente-form>
        <br />
        <b-row align-h="center" class="text-center">
            <b-col cols="4">
              <b-button variant="success" size="lg" @click="nuevoCliente">Nuevo</b-button>
            </b-col>
        </b-row>
        <br /><br />
      </b-container>
  </div>
</template>

<script>
import config from '@/store/config.js'
import ClienteForm from '@/components/ClientesFormEdit'
export default {
  mounted() {
    this.axios.get(this.API + 'clientes/count').then((response) => {
      this.totalRows = response.data.total
    })
  },
  data() {
    return {
      API: config.API,
      pageOptions: [ 5, 10, 15, 50, 100 ],
      filter: null,
      sortBy: 'IdCliente',
      sortDesc: false,
      clienteSelect: {},
      items: [],
      isBusy: false,
      fields: [
            { key: 'Cliente', sortable: true },
            { key: 'DniRuc', sortable: true },
            { key: 'Direccion', sortable: true },
            { key: 'NombreComercial', sortable: true },
            { key: 'esComisionista', sortable: true },
            { key: 'EsVIP', sortable: true },
      ],
      totalRows: 50,
      perPage: 10,
      currentPage: 1
    }
  },
  methods: {
      rowClick (item, index) {
        this.clienteSelect = item
        this.$refs.clienteForm.show = true;
        //this.$refs.productoForm.producto.IdProducto = 'a'
        //console.log(this.$refs.productoForm.$refs.productoForm)
      },
      actualizarTabla() {
        this.$refs.tablaClientes.refresh()
      },
      nuevoCliente () {
          this.clienteSelect = {}
          this.$refs.clienteForm.show = true
      },
      myProvider (ctx) {
        // Here we don't set isBusy prop, so busy state will be handled by table itself
        // this.isBusy = true
        let url = this.API + 'clientes?limit='+ctx.perPage+'&page='+ctx.currentPage+'&q='+ (ctx.filter||'')+'&sortBy='+ctx.sortBy
        url += '&sortDesc='+(ctx.sortDesc ? 1 : 0 )
        let promise = this.axios.get( url )
        return promise.then((result) => {
          this.items = result.data
          this.items.map(r=>{
            if(r.EsVIP==1 || r.esComisionista==1){
                    r._rowVariant='info'
            }
          })
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
      ClienteForm
  }
}
</script>

<style>
</style>
